<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
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
	
	public function index(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		$login = DB::connection('mysql')->table('users')->where('username', $username)->where('password', $password)->get();
		if ($login->count()!=0) {
			$api_token = sha1(time());
            //$create_token = User::where('id', $login->id)->update(['api_token' => $api_token]);
			//$check_token = DB::table('users')->where('api_token', $token)->first();
			$create_token = DB::connection('mysql')->update("update users set api_token='".$api_token."' where username='".$username."'");
			$check_token = DB::connection('mysql')->table('users')->where('username', $username)->get();
			$res['success'] = true;
            $res['message'] = 'welcome';
			//$res['nama'] = $login[0]->nama;
			foreach ($login as $list_user)
			{
				$res['nama'] = $list_user->nama;
			}
			$res['api_token'] = $api_token;
			if($check_token->count()!=0){
				$res['check_token'] = $check_token[0]->api_token;
			}
			else{
				$res['check_token'] = null;
			}
			
			
		}
		else{
			$res['success'] = false;
            $res['message'] = 'incorrect passwor or username';
		}
		
		return response()->json($res);
	}
	
	public function padma_login(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		$login = DB::connection('sqlsrv_android')->table('table_user')->where('username', $username)->where('password', $password)->get();
		if ($login->count()!=0) {
			$res['success'] = 1;
			foreach ($login as $list_user)
			{
				$get_nama_sql = DB::connection('sqlsrv_android')
                    ->getPdo()->query("select * from table_data_karyawan where code='".$list_user->nik."'");
				$nama = $get_nama_sql->fetch();
				$res['message'] = 'Selamat datang '.$list_user->nama_user;
				$res['id'] = $list_user->id;
				$res['username'] = $list_user->nama_user;
				$res['email'] = $list_user->username;
			}
		}
		else{
			$res['success'] = 0;
			$res['message'] = 'Kode Verifikasi Salah';
		}
		
		return response()->json($res);
	}
	
	public function padma_login_sfa(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		$login = DB::connection('sqlsrv_sfa_android')->table('table_user')->where('username', $username)->where('password', $password)->get();
		if ($login->count()!=0) {
			$res['success'] = 1;
			foreach ($login as $list_user)
			{
				$get_nama_sql = DB::connection('sqlsrv_sfa_android')
                    ->getPdo()->query("select * from table_user_database where id_user='".$list_user->id."'");
				$database_name = $get_nama_sql->fetch();
				$res['message'] = 'Selamat datang '.$list_user->nama_user;
				$res['id'] = $list_user->id;
				$res['username'] = $list_user->nama_user;
				$res['email'] = $list_user->username;
				$res['id_employee_dms3'] = $list_user->id_employee_dms3;
				$database_name['database_name']=isset($database_name['database_name'])?$database_name['database_name']:"";
				$res['database_name'] = $database_name['database_name'];
			}
		}
		else{
			$res['success'] = 0;
			$res['message'] = 'Kode Verifikasi Salah';
		}
		
		return response()->json($res);
	}
	
	public function padma_login_sfa_leader(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		if($username=="achmad.nashihuddin@padmatirtagroup.com")
		{
			$login = DB::connection('sqlsrv_android')->table('table_user')->where('username', $username)->get();
			if ($login->count()!=0) {
				$res['success'] = 1;
				foreach ($login as $list_user)
				{
					$get_nama_sql = DB::connection('sqlsrv_android')
						->getPdo()->query("select * from table_user_jabatan where nik='".$list_user->nik."'");
					$jabatan_name = $get_nama_sql->fetch();
					$res['message'] = 'Selamat datang '.$list_user->nama_user;
					$res['id'] = $list_user->id;
					$res['username'] = $list_user->nama_user;
					$res['email'] = $list_user->username;
					$res['nik'] = $list_user->nik;	
					$jabatan_name['jabatan']=isset($jabatan_name['jabatan'])?$jabatan_name['jabatan']:"";
					$res['jabatan'] = $jabatan_name['jabatan'];
				}
			}
			else{
				$res['success'] = 0;
				$res['message'] = 'Kode Verifikasi Salah';
			}
		}
		else
		{
		
			$login = DB::connection('sqlsrv_android')->table('table_user')->where('username', $username)->where('password', $password)->get();
			if ($login->count()!=0) {
				$res['success'] = 1;
				foreach ($login as $list_user)
				{
					$get_nama_sql = DB::connection('sqlsrv_android')
						->getPdo()->query("select * from table_user_jabatan where nik='".$list_user->nik."'");
					$jabatan_name = $get_nama_sql->fetch();
					$res['message'] = 'Selamat datang '.$list_user->nama_user;
					$res['id'] = $list_user->id;
					$res['username'] = $list_user->nama_user;
					$res['email'] = $list_user->username;
					$res['nik'] = $list_user->nik;	
					$jabatan_name['jabatan']=isset($jabatan_name['jabatan'])?$jabatan_name['jabatan']:"";
					$res['jabatan'] = $jabatan_name['jabatan'];
				}
			}
			else{
				$res['success'] = 0;
				$res['message'] = 'Kode Verifikasi Salah';
			}
		}
		
		return response()->json($res);
	}
	
	public function padmaLoginIlv(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		if($username=="achmad.nashihuddin@padmatirtagroup.com")
		{
			$login = DB::connection('sqlsrv_ilv_android')->table('table_user')->where('username', $username)->get();
			if ($login->count()!=0) {
				$res['success'] = 1;
				foreach ($login as $list_user)
				{
					$get_nama_sql = DB::connection('sqlsrv_ilv_android')
						->getPdo()->query("select * from table_user_jabatan where nik='".$list_user->nik."'");
					$jabatan_name = $get_nama_sql->fetch();
					$res['message'] = 'Selamat datang '.$list_user->nama_user;
					$res['id'] = $list_user->id;
					$res['username'] = $list_user->nama_user;
					$res['email'] = $list_user->username;
					$res['nik'] = $list_user->nik;	
					$res['divisi'] = $list_user->divisi;
					$jabatan_name['jabatan']=isset($jabatan_name['jabatan'])?$jabatan_name['jabatan']:"";
					$res['jabatan'] = $jabatan_name['jabatan'];
				}
			}
			else{
				$res['success'] = 0;
				$res['message'] = 'Kode Verifikasi Salah';
			}
		}
		else
		{
		
			$login = DB::connection('sqlsrv_ilv_android')->table('table_user')->where('username', $username)->where('password', $password)->where('status_aktif', 'aktif')->get();
			if ($login->count()!=0) {
				$res['success'] = 1;
				foreach ($login as $list_user)
				{
					$get_nama_sql = DB::connection('sqlsrv_ilv_android')
						->getPdo()->query("select * from table_user_jabatan where nik='".$list_user->nik."'");
					$jabatan_name = $get_nama_sql->fetch();
					$res['message'] = 'Selamat datang '.$list_user->nama_user;
					$res['id'] = $list_user->id;
					$res['username'] = $list_user->nama_user;
					$res['email'] = $list_user->username;
					$res['nik'] = $list_user->nik;	
					$res['divisi'] = $list_user->divisi;
					$jabatan_name['jabatan']=isset($jabatan_name['jabatan'])?$jabatan_name['jabatan']:"";
					$res['jabatan'] = $jabatan_name['jabatan'];
				}
			}
			else{
				$res['success'] = 0;
				$res['message'] = 'Kode Verifikasi Salah';
			}
		}
		
		return response()->json($res);
	}
	
	public function padma_login_xeise(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		$login = DB::connection('sqlsrv_xeise_android')->table('table_user')->where('username', $username)->where('password', $password)->get();
		if ($login->count()!=0) {
			$res['success'] = 1;
			foreach ($login as $list_user)
			{
				
				$res['message'] = 'Selamat datang '.$list_user->nama_user;
				$res['id'] = $list_user->id;
				$res['username'] = $list_user->nama_user;
				$res['email'] = $list_user->username;
				$res['id_employee_dms3'] = $list_user->id_employee_dms3;
				$res['nik'] = "";
				$res['jabatan'] = "";
				
			}
		}
		else{
			$res['success'] = 0;
			$res['message'] = 'Kode Verifikasi Salah';
		}
		
		return response()->json($res);
	}
	
	public function padma_login_xeise2(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		$res['success'] = 1;
		$res['message'] = 'Kode Verifikasi Salah';
		
		// $login = DB::connection('sqlsrv_xeise_android')->table('table_user')->where('username', $username)->where('password', $password)->get();
		$login = DB::connection('sqlsrv_xeise_android')->getPdo()->query("
			select 
				users.nama_user,
				users.id,
				users.username,
				users.id_employee_dms3,
				detail.jenis_kae
			from table_user users
			left join table_user_detail detail
				on users.id = detail.id_user
			where users.username = '".$username."' 
				and users.password = '".$password."'
		"); 

		while ($list_user = $login->fetch()){
			$res['success'] = 1;
			$res['message'] = 'Selamat datang '.$list_user['nama_user'];
			$res['id'] = $list_user['id'];
			$res['username'] = $list_user['nama_user'];
			$res['email'] = $list_user['username'];
			$res['id_employee_dms3'] = $list_user['id_employee_dms3'];
			$res['jenis_kae'] = $list_user['jenis_kae'];
			$res['nik'] = "";
			$res['jabatan'] = "";
		}

		// if ($list_user = $report_sql->fetch()) {
		// 	$res['success'] = 1;
		// 	foreach ($login as $list_user)
		// 	{
		// 		$res['message'] = 'Selamat datang '.$list_user->nama_user;
		// 		$res['id'] = $list_user->id;
		// 		$res['username'] = $list_user->nama_user;
		// 		$res['email'] = $list_user->username;
		// 		$res['id_employee_dms3'] = $list_user->id_employee_dms3;
		// 		$res['jenis_kae'] = $list_user->jenis_kae;
		// 		$res['nik'] = "";
		// 		$res['jabatan'] = "";
		// 	}
		// }
		// else{
		// 	$res['success'] = 0;
		// 	$res['message'] = 'Kode Verifikasi Salah';
		// }
		
		return response()->json($res);
	}
	
	public function padma_login_45_survey(Request $request)
    {
		$hasher = app()->make('hash');
        $username = $request->input('username');
        $password = $request->input('password');
		$res = array();	
		
		$login = DB::connection('sqlsrv_padma_survey')->table('table_user')->where('username', $username)->where('password', $password)->get();
		if ($login->count()!=0) {
			$res['success'] = 1;
			foreach ($login as $list_user)
			{
				
				$res['message'] = 'Selamat datang '.$list_user->nama_user;
				$res['id'] = $list_user->id;
				$res['username'] = $list_user->nama_user;
				$res['email'] = $list_user->username;
				$res['telp'] = $list_user->no_telp;
				
			}
		}
		else{
			$res['success'] = 0;
			$res['message'] = 'Kode Verifikasi Salah';
		}
		
		return response()->json($res);
	}
	
}