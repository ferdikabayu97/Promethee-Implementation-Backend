<?php

namespace App\Http\Controllers;
use App\Alternatif;
use App\Data_kode_lokasi;
use Illuminate\Http\Request;
use App\User_id;
use App\Data_rekomendasi;
use App\Nilai_alternatif;
use App\rek_harga;
use Illuminate\Support\Facades\DB as DB;
class RekomendasiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function _weighting($table, $id_lokasi, $arr_field)
    {
        $alternatives = Alternatif::all();
        $table_data = DB::table($table)->select('*')->where('id_lokasi', $id_lokasi)->first();
        $table_data_alt = DB::table($table.'_al')->select('*')->get();

        $data_alt = array();

        // assign nilai2 alternatif
        $alternatives_arr = array();
        foreach($alternatives as $alternative){
            $alternatives_arr[] = $alternative->id_alternatif;
        }
        // ternyata nilai ini ga kepake di akhir, bisa dihapus
        

        // ini biar jadi matriks array key=>value, dengan key adalah id_alternatif
        foreach($table_data_alt as $alt){
            $data_alt[$alt->id_alternatif] = array();
            foreach($arr_field as $field){
                $data_alt[$alt->id_alternatif][$field] = $alt->{$field};
            }
        }
        // wadah array untuk hasil perkalian
        $multiplying_result = array();

        // memilih jumlah penduduk dalam kode lokasi
        $people = Data_kode_lokasi::select('banyak_penduduk')->where('id_lokasi', $id_lokasi)->first();
        foreach($data_alt as $key=>$val){
            // inisiasi nilai sigma
            $sig = 0;

            // perhitungan nilai sigma
            foreach($arr_field as $field){
                $sig += $table_data->{$field} * $data_alt[$key][$field];
            }
            $sample = Alternatif::select('banyak_sample')->where('id_alternatif', $key)->first();

            // perhitungan sigma / jumlah penduduk * sample
            $value = $sig / ($people->banyak_penduduk * $sample->banyak_sample);

            // tempatin ke wadah dengan key sebagai id_alternatif
            $multiplying_result[$key] = $value;
        }
        return $multiplying_result;
    }

    private function _normalisasi($res){
        $minimus = array();
       $maximus = array();
       $pembagi = array();
       $out =array();
        foreach($res as $key=>$val){
            $max= $res[$key]['A01'];//ngambil data ke 1
            $min= $res[$key]['A01'];
            foreach($res[$key] as $keys=>$val){
                if($max < $res[$key][$keys]){
                    $max = $res[$key][$keys];
                }
                if($min > $res[$key][$keys]){
                    $min = $res[$key][$keys];
                }
                $maximus[$key] = $max;
                $minimus[$key] = $min;
                $pembagi[$key] = $maximus[$key]-$minimus[$key];
            }
        }
        foreach($res as $key=>$val){
            foreach($res[$key] as $keys=>$val){
                $out[$key][$keys] = ($res[$key][$keys]-$minimus[$key])/$pembagi[$key];
            }
        }

        return $out;
    }

    private function _MonajMathew($res){
       $i=1;
       $j=1;
       $k=1;
       $const = array();
       $count = Alternatif::count();
       $mj=array();
       $mjj=array();
       $ress=array();
        foreach($res as $key=>$var){
        for ($k=1 ; $k <= $count; $k++){        
            foreach($res[$key] as $keys=>$var){
            if($j == $k){
                $const[$i][$k][$j] = $res[$key][$keys];
            }else{
                $mj[$i][$k][$j] = $res[$key][$keys];
            }       
                $j=$j+1;
            }
            $j=1;
        }
            $i=$i+1; 
        }
        $i=1;
       $j=1;
       $k=1;
        foreach($res as $key=>$var){
            for ($k=1 ; $k <= $count; $k++){        
                foreach($res[$key] as $keys=>$var){
                // if($j == $k){
                //     $const[$i][$k][$j] = $res[$key][$keys];
                // }else{
                //     $mj[$i][$k][$j] = $res[$key][$keys];
                // }
                if($j == $k){
                }else{
                    $ress[$i][$k][$j] = $const[$i][$k][$k] - $mj[$i][$k][$j];
                }       
                    $j=$j+1;
                }
                $j=1;
            }
                $i=$i+1; 
            }



    return $ress;
    }

    private function _preference_func($res){
        $pref = array();
        $count = Alternatif::count();      
        $i=1;
        $j=1;
        $k=1;
        foreach($res as $key=>$var){
            for ($k=1 ; $k <= $count; $k++){        
                foreach($res[$key] as $keys=>$var){
                if($j == $k){
                }else{
                if ($res[$i][$k][$j]>=0){
                    $pref[$i][$k][$j] = $res[$i][$k][$j]; 
                }else{
                    $pref[$i][$k][$j] = 0;
                }
                }       
                    $j=$j+1;
                }
                $j=1;
            }
                $i=$i+1; 
            }
        
        return $pref;
    }

    private function _agregate_pref($res){
        $weight = array();
        $z=0;
        $count = Alternatif::count();        
        $i=1;
        $j=1;
        $k=1;
        $sum = array();
        $wd = array();
        $wadah=0;
        foreach($res as $key=>$var){
            $z=$z+1; 
        }
        foreach($res as $key=>$var){
            $weight[] = 1/$z; 
        }
        
        foreach($res as $key=>$var){
            for ($k=1 ; $k <= $count; $k++){        
                foreach($res[$key] as $keys=>$var){
                if($j == $k){
                }else{
                    $wd[$i][$k][$j] = $res[$i][$k][$j] * $weight[$i-1];
                }       
                    $j=$j+1;
                }
                $j=1;
            }
                $i=$i+1; 
            }
            $i=1;
            $ink=0;
            // $sum[1][2] = $wd[1][1][2] + $wd[2][1][2] + $wd[3][1][2] + $wd[4][1][2] + $wd[5][1][2]
    for ($k=1 ; $k <=$count; $k++){
        for ($j=1; $j <=$count ; $j++){

        
        foreach($wd as $key=>$var){
            if($k == $j){

            }else{
                $sum[$k][$j] = $wd[$i][$k][$j] + $wadah;
                $wadah = $sum[$k][$j];
                
            }
            $i=$i+1;
        }
        $i=1;
        $wadah=0;
        }
    }


        return $sum  ;
    }

    private function _entering_func($res){
        $ent=array();
        $tot=array();
        $j=1;        
        $i=1;
        $wadah=0;
        $count = Alternatif::count();

        foreach($res as $key=>$var){
            for($k=1 ; $k <=$count; $k++){
                if($i == $j){

                }else{

                    $ent[$j]= $res[$j][$i];
                    $wadah= $wadah + $ent[$j];
                    
                }
            $j=$j+1;
            }
            $tot[$i] = $wadah/($count-1);
            $i=$i+1;        
            $j=1;
            $wadah=0;
        }
        $i=1;
       return $tot;
    }

    private function _leaving_func($res){
        $lea=array();
        $j=1;        
        $i=1;
        $count = Alternatif::count();
        $wadah = 0;
        foreach($res as $key=>$var){
            for($k=1 ; $k <=$count; $k++){
                if($i == $j){

                }else{

                    $lea[$j]= $res[$i][$j];
                    $wadah= $wadah + $lea[$j];
                    
                }
            $j=$j+1;
            }
            $tot[$i] = $wadah / ($count-1);
            $i=$i+1;        
            $j=1;
            $wadah=0;
        }
        $i=1;
        
        return $tot;
        
    }

    private function _net_outranking($res1,$res2,$id_user,$id_lokasi){

        $val=array();
        $net=array();
        $count = Alternatif::count();
        $no = Data_rekomendasi::count();
        $no_alternatif = $no+1;
        $data_rekomendasi = new Data_rekomendasi;
        $data_rekomendasi->no = $no+1;
        $data_rekomendasi->id_user = $id_user;
        $data_rekomendasi->id_lokasi = $id_lokasi;
        $data_rekomendasi->save();
        foreach($res1 as $key=>$var){
            $net[$key][0] = $res1[$key] - $res2[$key];
            $nilai_alternatif = new Nilai_alternatif;
            $nilai_alternatif->no = $no_alternatif;
            $nilai_alternatif->id_alternatif = "A".str_pad($key,2,"0",STR_PAD_LEFT);
            $nilai_alternatif->peringkat = 0;
            $nilai_alternatif->skor = $net[$key][0];
            $nilai_alternatif->save();
        }
        $val=$net;
        foreach($res1 as $key=>$var){
            for ($j = 1; $j <= $count-1; $j++){
                if ($val[$j][0] < $val[$j+1][0]){
              $temp = $val[$j][0];
              $val[$j][0] = $val[$j+1][0];
              $val[$j+1][0] = $temp;
                } 
            }
        DB::table('nilai_alternatif')->where('skor', $val[$count])->update(['peringkat' => $count]);            
            $count=$count-1;
        }
        
        
    
        
        $hasil = array();
        $hasil['status'] = "200";
        $hasil['hasil'] = DB::table('nilai_alternatif')
        ->join('alternatif', 'nilai_alternatif.id_alternatif', '=', 'alternatif.id_alternatif')
        ->select('alternatif.nama_alternatif','nilai_alternatif.peringkat','nilai_alternatif.skor')
        ->where('nilai_alternatif.no',$no_alternatif)->orderBy('nilai_alternatif.peringkat', 'asc')->get();
        
            $max = 0;
        $str = array();
        $leng = 0 ;
        $stringg = "";
        // $cont = rek_harga::select('id_rharga')->get();
        $countrek = rek_harga::count();

        $cont = DB::table('rek_harga')
        ->join('alternatif', 'rek_harga.id_rharga', '=', 'alternatif.id_rharga')
        ->join('nilai_alternatif','alternatif.id_alternatif','=','nilai_alternatif.id_alternatif')
        ->select('rek_harga.id_rharga')
        ->where('nilai_alternatif.no',$no_alternatif)->orderBy('nilai_alternatif.peringkat', 'asc')->get();
        
        // $countrek = rek_harga::count();

        $rek = DB::table('rek_harga')
        ->join('alternatif', 'rek_harga.id_rharga', '=', 'alternatif.id_rharga')
        ->join('nilai_alternatif','alternatif.id_alternatif','=','nilai_alternatif.id_alternatif')
        ->select('rek_harga.id_rharga','rek_harga.h5_10','rek_harga.h10_15','rek_harga.h15_20','rek_harga.h20_25','rek_harga.h25_30','rek_harga.h30_abv')
        ->where('nilai_alternatif.no',$no_alternatif)->orderBy('nilai_alternatif.peringkat', 'asc')->get();   

        // $rek = rek_harga::all();      
        for($r = 0; $r <=$countrek-1; $r++){
        $id = $cont[$r]->id_rharga;  
        $arrr = array(
                'h5_10','h10_15','h15_20','h20_25','h25_30','h30_abv'
            );
            foreach($arrr as $keys){
            if ($rek[$r]->$keys > $max){
                $max = $rek[$r]->$keys;
                $stringg = $keys;
            }
            }
        $max = 0;
            $stringg = substr($stringg,1);
            $leng = strlen($stringg);
            for ($l=0;$l<=$leng-1;$l++){
                if($stringg[$l] == "_"){
                    $stringg[$l] = "-";
                }
            }
             $str[$r] = $stringg." Ribu";
             $stringg = "";
             $hasil['hasil'][$r]->rharga = $str[$r];
        }


        return $hasil;
           }
    private function _insert($token){
        $id = User_id::select('id_user')->where('token', $token)->first();
        
        return $id['id_user'];
    }

    private function _umur()
    {
        $count = 16;
        $start = 0;
        $end = 4;
        $result = array();

        for($i = 0 ; $i < $count; $i++){
            $result[] = 'u'.$start.'_'.$end;
            $start = $start + 5;
            if($i < $count - 2){
                $end = $end + 5;
            } else {
                $end = 'above';
            }
        }
        return $result;
    }

    private function _is_valid_token($token)
    {
        return User_id::where('token', $token)->exists();
    }

    public function index(Request $request)
    {
       $kecamatan = $request->kecamatan;
       $kelurahan = $request->kelurahan;
       $api_key = $request->token;

       $is_valid = $this->_is_valid_token($api_key);

       if(!$is_valid){
           $res =  array('error' => 301, 'msg' => 'Akses ditolak, token salah');
           echo json_encode($res);
       } else {
        $kode_lokasi = Data_kode_lokasi::where('kecamatan', $kecamatan)->where('kelurahan',$kelurahan)->first();
        $arr_field = array(
            'jk' => array(
                'pria','wanita'
            ),
            'sk' => array(
                'belum_kawin','kawin','cerai_hidup','cerai_mati'
            ),
            'pendidikan' => array(
             'belum_sekolah','belum_tamat_sd','tamat_sd','smp','sma','di_dii','diii','s1','s2','s3'
             ),
             'pekerjaan' => array(
                 'tidak_bekerja','aparat_pejabat_negara','tenaga_pengajar','wiraswasta','pertanian','nelayan','bidang_keagamaan','pelajar_dan_mahasiswa','tenaga_kesehatan','pensiunan','lainnya'
                 ),
            'umur' => $this->_umur()
             );
 
 
        $bobot = array();
 
        foreach($arr_field as $key=>$val){
         $weighting = $this->_weighting($key, $kode_lokasi->id_lokasi, $arr_field[$key]);
         $bobot[$key] = $weighting;
        }
        $normal = $this->_normalisasi($bobot);
        $monaj = $this->_MonajMathew($normal);
        $pref = $this->_preference_func($monaj);
        $apref = $this->_agregate_pref($pref);
        $lea = $this->_leaving_func($apref);
        $ent = $this->_entering_func($apref);
        $insert = $this->_insert($api_key);
 
        $net = $this->_net_outranking($lea,$ent,$insert,$kode_lokasi->id_lokasi);
 
        
 
 
        echo json_encode($net);
       }

       


    }
    //
}