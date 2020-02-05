<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User_id;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Mail;
class BuatAkunController extends Controller
{
    public function __construct()
    {
        //
    }

    private function _is_valid_info($username,$email)
    {
        $check = User_id::where('id_user', $username)->where('email', $email)->exists();
        $actall = User_id::where('id_user', $username)->where('email', $email)->where('active_status', 1)->exists();
        $actusr = User_id::where('id_user', $username)->where('active_status', 1)->exists();
        $acteml = User_id::where('email', $email)->where('active_status', 1)->exists();
        $existusr = User_id::where('id_user', $username)->exists();
        $existeml = User_id::where('email', $email)->exists();

        if($actall){
           return array('status' => 301, 'msg' => 'Akun sudah terdaftar');
    
        }else if ($actusr){            
            return array('status' => 301, 'msg' => 'Username Sudah Terdaftar');
            
        }else if ($acteml){
            return array('status' => 301, 'msg' => 'Email Sudah Terdaftar');
            
        }else {
            if($existusr){
                if($existeml){
                    User_id::where('email', $email)->delete();
                }
                User_id::where('id_user', $username)->delete();
                return array('status' => 200, 'msg' => 'Data Terdaftar, Silahkan Periksa Email untuk kode Verifikasi');
            
            } else if($existeml){
                if($existusr){
                User_id::where('id_user', $username)->delete();
                }
                User_id::where('email', $email)->delete();
                return array('status' => 200, 'msg' => 'Data Terdaftar, Silahkan Periksa Email untuk kode Verifikasi');
                
            } else if(!$check){
                return array('status' => 200, 'msg' => 'Data Terdaftar, Silahkan Periksa Email untuk kode Verifikasi');
                
            }
        }
    }

    private function _insertinfo($email,$nama,$password,$username){
        $idx = DB::table('table_idx')->count();
        $user_id = new User_id;
        $user_id->id_user = $username;
        $user_id->nama = $nama;
        $user_id->password = $password;
        $user_id->email = $email;
        $user_id->idx_table = $idx+1;
        $user_id->active_status = 0;
        $user_id->verification = rand(10000,99999);
        $user_id->save();
        return true;
    }

    private function _kirimemail($nama,$email){
        
        $to_name = $nama;
        $to_email = $email;
        $code = User_id::select('verification')->where('email',$email)->first();
        $from_email = "cs@rekusaha.com";
        $data = array('name'=>$nama, "body" => "Kode Verifikasi anda Adalah ", "verivy" => $code->verification, "endline" => " silahkan melakukan Verifikasi, Dan temukan usaha Rekomendasimu!");
        Mail::send('emails.mail', $data, function($message) use ($to_name, $to_email,$from_email) {
            $message->to($to_email, $to_name)->subject('Selangkah Lagi Melakukan Registrasi');
            $message->from($from_email,'Rek Usaha');
        });
        return "success";
    }

    public function index(Request $request)
    {
        $nama = $request->nama;
        $email = $request->email;
        $username = $request->username;
        $password = $request->password;
        $send = false;
        $is_valid_info = $this->_is_valid_info($username,$email);
        if($is_valid_info['status']==200){
            $send = $this->_insertinfo($email,$nama,$password,$username);
        }

        if($send){
            $this->_kirimemail($nama,$email);
        } 
        $send=false;
        echo json_encode($is_valid_info);
    }

}
