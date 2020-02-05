<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User_id;
use Illuminate\Support\Facades\DB as DB;

class VerifikasiController extends Controller
{
    public function __construct()
    {
        //
    }

    private function _is_valid_code($username,$kode)
    {
        $vcode = User_id::select('verification')->where('id_user', $username)->first();
        
        if($kode == $vcode->verification){
            return true;
        }

        return false;
    }

    private function _acak($panjang)
    {
        $karakter= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
        $string = '';
        for ($i = 0; $i < $panjang; $i++) {
      $pos = rand(0, strlen($karakter)-1);
      $string .= $karakter{$pos};
        }
        return $string;
    }

    private function _verifikasi($username)
    {
        $token = $this->_acak(10);
        User_id::where('id_user', $username)->update(['token' => $token]);
        User_id::where('id_user', $username)->update(['active_status' => 1]);
        $res = array();
        $res['status'] = '200';
        $res['msg'] = 'Verifikasi Berhasil dilakukan';
        
        return $res;
    }

    public function index(Request $request)
    {
        $res=array();
        $kode = $request->kode;
        $username = $request->username;
        $is_valid=false;
        $is_valid = $this->_is_valid_code($username,$kode);

        if($is_valid){
            $res = $this->_verifikasi($username);
        } else {
            $res['status'] = '301';
            $res['msg'] = 'Verifikasi Gagal Dilakukan, silahkan periksa kembali Kode Verifikasi';
        }
        echo json_encode($res);
    }
}
