<?php

namespace App\Http\Controllers;
use App\User_id;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Http\Request;
use App\UMKM;
use App\Data_kode_lokasi;

class PesaingUsahaController extends Controller
{
    public function __construct()
    {
        //
    }

    private function _is_valid_token($token)
    {
        return User_id::where('token', $token)->exists();
    }

    private function _data_umkm($kec,$kel,$ju)
    {
        $val1 = UMKM::where('kecamatan', $kec)->where('kelurahan',$kel)->where('jenis_usaha', 'like', '%'.$ju.'%')->get();
        $val2 = UMKM::where('kecamatan', $kec)->where('kelurahan','')->where('jenis_usaha', 'like', '%'.$ju.'%')->get();
        $val['pesaing_tetap'] = $val1;
        $val['pesaing_keliling'] = $val2;
        return $val;
        
    }

    public function index(Request $request)
    {
        $kecamatan = $request->kecamatan;
        $kelurahan = $request->kelurahan;
        $api_key = $request->token;
        $jenisusaha = $request->jenisusaha;
 
        $is_valid = $this->_is_valid_token($api_key);

        if($is_valid){
            $res = array();
            $res['status'] = 200;
            $res['data'] = $this->_data_umkm($kecamatan,$kelurahan,$jenisusaha);
        }else{
            $res = array('status' => 301, 'msg' => 'Input gagal, Akses ditolak');
        }



       
       echo json_encode($res);
}
}
