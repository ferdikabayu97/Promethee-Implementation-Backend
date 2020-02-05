<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User_id;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Mail;
class LupaPasswordController extends Controller
{
    public function __construct()
    {
        //
    }

    private function _is_valid_info($username,$email)
    {
        $check = User_id::where('id_user', $username)->where('email', $email)->exists();
        
        if($check){
            return true;
        } else {
            return false;
        }
       
    }

    private function _kirimemail($username,$email){
        $nama = User_id::select('nama')->where('email',$email)->first();
        $to_name = $nama->nama;
        $to_email = $email;
        $Pass = User_id::select('password')->where('email',$email)->first();
        $from_email = "cs@rekusaha.com";
        $data = array('name'=>$nama->nama, "body" => "Password Anda adalah ", "verivy" => $Pass->password, "endline" => " silahkan melakukan login!");
        Mail::send('emails.mail', $data, function($message) use ($to_name, $to_email,$from_email) {
            $message->to($to_email, $to_name)->subject('Pemulihan Akun untuk Lupa Password');
            $message->from($from_email,'Rek Usaha');
        });
        return "success";
    }

    public function index(Request $request)
    {
        $email = $request->email;
        $username = $request->username;
       
        $send = false;
        $send = $this->_is_valid_info($username,$email);
        if(!$send){
         $res = array('status' => 301, 'msg' => 'email atau username yang anda masukan tidak cocok');
         }else {
           
                $this->_kirimemail($username,$email);
                $res=array();
                $res['status'] = '200';
                $res['msg'] = 'Pemulihan berhasil, silahkan liat email anda';
    
           
         }

         
        $send=false;
        echo json_encode($res);
    }

}
