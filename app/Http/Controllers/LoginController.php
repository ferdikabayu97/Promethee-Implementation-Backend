<?php

namespace App\Http\Controllers;
use App\User_id;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        //
    }

    private function _login($id, $pass)
    {    
        $res = User_id::select('nama','token','email','id_user')->where('id_user', $id)->where('password', $pass)->first();
        return $res;
    }
    
    private function _is_valid_login($id,$pass)
    {
        return User_id::where('id_user', $id)->where('password', $pass)->exists();
    }

    private function _is_active($id){
        return User_id::where('id_user', $id)->where('active_status', 1)->exists();
    }

    public function index(Request $request)
    {
       $id = $request->id;
       $pass = $request->pass;
       $active = $this->_is_active($id);

       $is_valid = $this->_is_valid_login($id,$pass);

       if(!$is_valid){
           $res = array('status' => 401, 'msg' => 'Login gagal, silahkan periksa username dan password anda');
       } else {
        if(!$active){
            $res = array('status' => 401, 'msg' => 'Login gagal, akun belum aktif, silahkan melakukan registrasi kembali');
           }else {
            $res = array();
            $res['status'] = 200;
            $res['data'] = $this->_login($id,$pass);
           }  
       }
       
       echo json_encode($res);
}
}
