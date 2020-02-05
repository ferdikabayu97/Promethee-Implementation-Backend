<?php

namespace App\Http\Controllers;
use App\Alternatif;
use App\Data_kode_lokasi;
use Illuminate\Http\Request;
use App\User_id;
use Illuminate\Support\Facades\DB as DB;

class PendudukController extends Controller
{
    public function __construct()
    {
        //
    }

    private function _data_penduduk($table, $id_lokasi)
    {
        $table_data = DB::table($table)->select('*')->where('id_lokasi', $id_lokasi)->first();        
        $people = Data_kode_lokasi::select('banyak_penduduk')->where('id_lokasi', $id_lokasi)->first();
        $resp = array();
        $resp['data kriteria'] = $table_data;
        $resp['jumlah penduduk'] = $people['banyak_penduduk'];
        return $resp;
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
           $res = array('status' => 301, 'msg' => 'Akses ditolak, token salah');
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
 
 
        $res = array();
 
        foreach($arr_field as $key=>$val){
         $pend = $this->_data_penduduk($key, $kode_lokasi->id_lokasi);
         $res['data penduduk'][$key] = $pend;
        }
        $res['status'] = 200;
        $res['lokasi']['kecamatan'] = $kecamatan;
        $res['lokasi']['kelurahan'] = $kelurahan;
       }

       

       

       echo json_encode($res);
}
}