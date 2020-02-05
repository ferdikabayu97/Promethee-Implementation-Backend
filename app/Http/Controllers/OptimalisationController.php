<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Jk_al;
use App\User_id;
use App\Pekerjaan_al;
use App\Alternatif;
use App\Sk_al;
use App\Umur_al;
use App\Pendidikan_al;
use Illuminate\Support\Facades\DB as DB;

class OptimalisationController extends Controller
{
    public function __construct()
    {
        //
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

    private function _insert($table,$id_alternatif,$field){
        $sub_kalimat = substr($field,0,1);
        if($sub_kalimat != "h"){
        $add = DB::table($table)->select($field)->where('id_alternatif', $id_alternatif)->first();
        DB::table($table)->where('id_alternatif', $id_alternatif)->update([$field => $add->$field+1]);
        $res = array();
        $res[] = "Data ".$table." sudah terupdate";}
        else{
        $gabung = ucfirst($sub_kalimat).substr($id_alternatif,1,2);
        $add = DB::table($table)->select($field)->where('id_rharga', $gabung)->first();
        DB::table($table)->where('id_rharga', $gabung)->update([$field => $add->$field+1]);
        $res = array();
        $res[] = "Data ".$table." sudah terupdate";
        }
        return $res;
    }
    
    public function index(Request $request)
    {
       $res = array();
       $api_key = $request->token;       
       $is_valid = $this->_is_valid_token($api_key);
       $nama_al = $request->nama_al;

       $field = array();
       $field1 = $request->field_al_jk;
       $field2 = $request->field_al_sk;
       $field3 = $request->field_al_pend;
       $field4 = $request->field_al_peker;
       $field5 = $request->field_al_umur;
       $field6 = $request->field_rek_harga;
       $field[] = $field1;
       $field[] = $field2;
       $field[] = $field3;
       $field[] = $field4;
       $field[] = $field5;
       $field[] = $field6;




       if(!$is_valid){
           $res = array('status' => 301, 'msg' => 'Input gagal, silahkan periksa nilai input anda');
       } else {
        $alternatif = Alternatif::select('id_alternatif')->where('nama_alternatif', $nama_al)->first();
        $id_alternatif = $alternatif['id_alternatif'];
        $arr_field = array(
         'jk_al' => array(
             'pria','wanita'
         ),
         'sk_al' => array(
             'belum_kawin','kawin','cerai_hidup','cerai_mati'
         ),
         'pendidikan_al' => array(
          'belum_sekolah','belum_tamat_sd','tamat_sd','smp','sma','di_dii','diii','s1','s2','s3'
          ),
          'pekerjaan_al' => array(
              'tidak_bekerja','aparat_pejabat_negara','tenaga_pengajar','wiraswasta','pertanian','nelayan','bidang_keagamaan','pelajar_dan_mahasiswa','tenaga_kesehatan','pensiunan','lainnya'
              ),
         'umur_al' => $this->_umur(),
         'rek_harga' => array(
             'h5_9','h10_14','h15_19','h20_24','h25_29','h30_abv'
         )
          );
          
          $i=0;
          foreach($arr_field as $key=>$val){
             $upd = $this->_insert($key, $id_alternatif, $field[$i]);
             $res[$i] = $upd;
             $i=$i+1;
            }
       }
       $res['status'] = 200;
       $res['msg'] = 'Data berhasil ditambahkan, Terimakasih';


       echo json_encode($res);
}


}
