<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
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

    public function GetUser($id_user){ 
        $userList_array =array();
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_android')
        ->getPdo()->query("select a.* from table_data_karyawan as a left join table_user as b on a.code = b.nik where b.id='".$id_user."'"); 
        $jml_user = $user_sql->rowCount();
		if($jml_user!=0){
        while ($list_user = $user_sql->fetch())
			{
				$user_array['success'] = 1;
				$user_array['nik'] = $list_user['code'];
				$user_array['name'] = $list_user['name'];			
				$user_array['depo'] = $list_user['nama_company'];
				$user_array['bagian'] = $list_user['nama_bagian'];
				$user_array['jabatan'] = $list_user['nama_jabatan'];
				$user_array['divisi'] = $list_user['nama_divisi'];
				//array_push($userList_array,$user_array);
			}
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			//array_push($userList_array,$user_array);
		}
		return response()->json($user_array);
	}
	
	public function userspinner(){
        $userList_array =array();
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_ilv_android')->table('table_user')->orderBy('username', 'asc')->get();
		foreach ($user_sql as $list_user)
		{
			$user_array[] = array(
				'user_id' => $list_user->id,
				'nama_user' => $list_user->nama_user,
			);
			//array_push($userList_array,$user_array);
		}
		return response()->json($user_array);
	}
	
	public function ListTracAktifitas($user_id, $id_company, $date){
		$data = array();	
		$sql = "select * from table_tracking_activity where create_by = '".$user_id."' and cast(date_create as date) = '".date("Y-m-d",strtotime($date))."' and id_company = '".$id_company."' order by date_create asc";
        $stmt = DB::connection('sqlsrv_ilv_android')->getPdo()->query($sql);
		while ($row = $stmt->fetch())
		{
			$data[] = array(
				//'id' => $row['id'],
				'kategori' => $row['kategori'],
				'detail_aktifitas' => $row['detail_aktifitas'],
				'id_relasi' => $row['id_relasi'],
				'id_company' => $row['id_company'],
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude'],
				'address' => $row['address'],
				'time' => date("H:i", strtotime($row['date_create'])),
			);
		}
		// $data[] = ['query' => $sql];
		return response()->json($data);
	}
	
	public function GetWebUser($username){ 
        $userList_array =array();
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from table_user where username = '".$username."'"); 
        $jml_user = $user_sql->rowCount();
		if($jml_user!=0){
        while ($list_user = $user_sql->fetch())
			{
				$user_array['success'] = 1;
				$user_array['username'] = $list_user['username'];
				//$user_array['kode_user'] = $list_user['kode_user'];
				$user_array['kode_user'] = "******";	
				
				//get last login;
				$web_user = DB::connection('mysql_ilv_web')->table('user')->where('username', $username)->get();
				if($web_user->count()!=0){
					$user_array['last_login'] = $web_user[0]->user_lastlogin;;
				}
				else{
					$user_array['last_login'] = 0;
				}
			}
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			//array_push($userList_array,$user_array);
		}
		return response()->json($user_array);
	}
	
	public function UpdateDetailUser(Request $request){ 
		$hasher = app()->make('hash');
        $nik = $request->input('nik');
        $phone = $request->input('phone');		
		$email_atasan = $request->input('email_atasan');
        $ktp = $request->input('ktp');
		$alamat = $request->input('alamat');
		$email_user = $request->input('email_user');
		$tgl_lahir = $request->input('tgl_lahir');	
		
		
				
		$return = DB::connection('sqlsrv_ilv_android')->table('table_user')->where('username', $email_user)->update(                        
				  array(
						'nik'   => $nik,												
						'telp'   => $phone,
						'email_atasan'   => $email_atasan,
						'ktp'   => $ktp,
						'tgl_lahir'   => $tgl_lahir,
						'alamat'   => $alamat
				 )
				);
				
		if($return != 0){
			
			$id_company = "";
			if($request->has('id_company')){
				
				$id_company = $request->input('id_company');
				$id_user = $request->input('id_user');
				$cek_code = DB::connection('sqlsrv_ilv_android')->table('table_company')->where('id', $id_company)->get();
				$code_company = "";
				if($cek_code->count()!=0)
				{
					$code_company = $cek_code[0]->code_company;
					$update_email_atasan = DB::connection('sqlsrv_ilv_android')->table('table_hak_akses_company')->where('code_company', $code_company)->where('id_user', $id_user)->update(                        
					  array(
							'email_atasan'   => $email_atasan,
							'date_create'   => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +7 hour"))	
						)
					);
				}
				else{
					
				}
			}
			else{
				
			}
			
					
			return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data User Updated"]);
		}else{
			return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Update Data User"]);
		}
	}
	
	public function UpdateDetailUserSFA(Request $request){ 
		$hasher = app()->make('hash');
        $email_user = $request->input('email_user');
        $alamat = $request->input('alamat');		
		$phone = $request->input('phone');
        $nmr_wa = $request->input('nmr_wa');
		if($request->has('salesid_dms3')){
			$salesid_dms3 = $request->input('salesid_dms3');
		}
		else{
			$salesid_dms3 ="";
		}
		
		$cek_user = DB::connection('sqlsrv_sfa_android')->table('table_detail_user')->where('email_user', $email_user)->get();
		if ($cek_user->count()==0) {
			$return = DB::connection('sqlsrv_sfa_android')->insert('INSERT INTO table_detail_user 
                    (email_user,alamat,phone,nmr_wa,date_create) 
                    values (?,?,?,?,getdate())',
                    [$email_user,$alamat,$phone,$nmr_wa]);
					
			if($return == 1){
				//update sales id dms3
				if($request->has('salesid_dms3')){
					$update_id_dms3 = DB::connection('sqlsrv_sfa_android')->table('table_user')->where('username', $email_user)->update(                        
					  array(
							'id_employee_dms3'   => $salesid_dms3
							
						)
					);
				}
				//
				$last_id = DB::connection('sqlsrv_sfa_android')->getPdo()->lastInsertId();
				return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Saved",'last_id' => $last_id]);
			}else{
				return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Savedd"]);
			}
		}
		else{
			$return = DB::connection('sqlsrv_sfa_android')->update("update table_detail_user set alamat ='".$alamat."' ,phone ='".$phone."' ,
				nmr_wa='".$nmr_wa."' where email_user ='".$email_user."' ");
				
			if($return != 0){
				//update sales id dms3
				if($request->has('salesid_dms3')){
					$update_id_dms3 = DB::connection('sqlsrv_sfa_android')->table('table_user')->where('username', $email_user)->update(                        
					  array(
							'id_employee_dms3'   => $salesid_dms3
							
						)
					);
				}
				//
				$last_id = $cek_user[0]->id;
				return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Saved",'last_id' => $last_id]);
			}else{
				return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Saveed"]);
			}
		}
		
		
		
		
	}
	
	public function GetDetailUser($id_user){ 
        $userList_array =array();
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select a.*,b.foto_user as foto_pribadi,c.tgl_lahir as tgl_lahir2 from table_user as a left join table_user_photo as b on a.id = b.id_user
				left join table_karyawan as c on a.nik = c.nik
				where a.id ='".$id_user."'"); 
        $jml_user = $user_sql->rowCount();
		
		if($jml_user!=0){
        while ($list_user = $user_sql->fetch())
			{
				$user_array['success'] = 1;
				$user_array['email_user'] = $list_user['username'];
				$user_array['nama_user'] = $list_user['nama_user'];
				$user_array['alamat'] = $list_user['alamat'];
				$user_array['phone'] = $list_user['telp'];			
				$user_array['ktp'] = $list_user['ktp'];
				$user_array['kode_user'] = "";//$list_user['kode_user'];
				$user_array['email_atasan'] = $list_user['email_atasan'];
				
				if($list_user['foto_pribadi']!=null)
				{
					$user_array['foto_pribadi'] = $list_user['foto_pribadi'];
				}
				else{
					$user_array['foto_pribadi'] = "";
				}
				
				$user_array['nik'] = $list_user['nik'];
				
				if($list_user['tgl_lahir']!=null)
				{
					$user_array['tgl_lahir'] = $list_user['tgl_lahir'];
				}
				else{
					if($list_user['tgl_lahir2']!=null)
					{
						$user_array['tgl_lahir'] = $list_user['tgl_lahir2'];
					}
					else{
						$user_array['tgl_lahir'] = "";
					}
				}
				
				
				//array_push($userList_array,$user_array);
			}
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			//array_push($userList_array,$user_array);
		}
		return response()->json($user_array);
	}
	
	public function GetDetailUser2($id_user,$id_company){ 
        $userList_array =array();
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select a.*,b.foto_user as foto_pribadi,c.tgl_lahir as tgl_lahir2 from table_user as a left join table_user_photo as b on a.id = b.id_user
				left join table_karyawan as c on a.nik = c.nik
				where a.id ='".$id_user."'"); 
        $jml_user = $user_sql->rowCount();
		
		if($jml_user!=0){
        while ($list_user = $user_sql->fetch())
			{
				$user_array['success'] = 1;
				$user_array['email_user'] = $list_user['username'];
				$user_array['nama_user'] = $list_user['nama_user'];
				$user_array['alamat'] = $list_user['alamat'];
				$user_array['phone'] = $list_user['telp'];			
				$user_array['ktp'] = $list_user['ktp'];
				$user_array['kode_user'] = "";//$list_user['kode_user'];
				//$user_array['email_atasan'] = $list_user['email_atasan'];
				
				$email_atasan_sql = DB::connection('sqlsrv_ilv_android')
								->getPdo()->query("select a.* from table_hak_akses_company as a left join table_company as b on a.code_company = b.code_company
											where a.id_user = '".$id_user."' and b.id = '".$id_company."'");
				$row_email_atasan = $email_atasan_sql->fetch();
				$user_array['email_atasan'] = $row_email_atasan['email_atasan'];
				$user_array['email_atasan2'] = $list_user['email_atasan'];
				
				if($list_user['foto_pribadi']!=null)
				{
					$user_array['foto_pribadi'] = $list_user['foto_pribadi'];
				}
				else{
					$user_array['foto_pribadi'] = "";
				}
				
				$user_array['nik'] = $list_user['nik'];
				
				if($list_user['tgl_lahir']!=null)
				{
					$user_array['tgl_lahir'] = $list_user['tgl_lahir'];
				}
				else{
					if($list_user['tgl_lahir2']!=null)
					{
						$user_array['tgl_lahir'] = $list_user['tgl_lahir2'];
					}
					else{
						$user_array['tgl_lahir'] = "";
					}
				}
				
				
				//array_push($userList_array,$user_array);
			}
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			//array_push($userList_array,$user_array);
		}
		return response()->json($user_array);
	}
	
	public function GetDetailUserSFA($id_user){ 
        $userList_array =array();
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_sfa_android')
        ->getPdo()->query("select b.*,a.kode_user,a.id_employee_dms3 from table_user as a left join table_detail_user as b on a.username = b.email_user
				where a.id ='".$id_user."'"); 
        $jml_user = $user_sql->rowCount();
		//cek foto_ktp
		$cek_foto_ktp = DB::connection('sqlsrv_android')->table('table_image_ktp')->where('id_user', $id_user)->get();
		//
		if($jml_user!=0){
        while ($list_user = $user_sql->fetch())
			{
				$user_array['success'] = 1;
				$user_array['alamat'] = $list_user['alamat'];
				$user_array['phone'] = $list_user['phone'];			
				$user_array['nmr_wa'] = $list_user['nmr_wa'];
				$user_array['kode_user'] = $list_user['kode_user'];
				$user_array['foto_pribadi'] = $list_user['foto_pribadi'];
				$user_array['id_employee_dms3'] = $list_user['id_employee_dms3'];
				$user_array['jml_foto_ktp'] = $cek_foto_ktp->count();
				//array_push($userList_array,$user_array);
			}
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			//array_push($userList_array,$user_array);
		}
		return response()->json($user_array);
	}
	
	public function GetFotoKtp($id_user){ 
		$ktpList_array =array();
		$ktp_array = array();	
        $ktp_sql = DB::connection('sqlsrv_android')
        ->getPdo()->query("select * from table_image_ktp where id_user='".$id_user."' order by date_create desc"); 
        
		
        while ($list_ktp = $ktp_sql->fetch())
		{
			$ktp_array['id'] = $list_ktp['id'];
			$ktp_array['id_user'] = $list_ktp['id_user'];
			$ktp_array['nama_file'] = $list_ktp['file_image'];
			$ktp_array['date_create'] = date('Y-m-d', strtotime($list_ktp['date_create']));
			array_push($ktpList_array,$ktp_array);
		}
		
		
		return response()->json($ktpList_array);
	}
	
	public function GetFotoKtpSFA($id_user){ 
		$ktpList_array =array();
		$ktp_array = array();	
        $ktp_sql = DB::connection('sqlsrv_sfa_android')
        ->getPdo()->query("select * from table_image_ktp where id_user='".$id_user."' order by date_create desc"); 
        
		
        while ($list_ktp = $ktp_sql->fetch())
		{
			$ktp_array['id'] = $list_ktp['id'];
			$ktp_array['id_user'] = $list_ktp['id_user'];
			$ktp_array['nama_file'] = $list_ktp['file_image'];
			$ktp_array['date_create'] = date('Y-m-d', strtotime($list_ktp['date_create']));
			array_push($ktpList_array,$ktp_array);
		}
		
		
		return response()->json($ktpList_array);
	}
	
	public function SetNetworkSFA(Request $request){ 
		$hasher = app()->make('hash');
        $id_user = $request->input('id_user');
        $network = $request->input('network');		
		$date_create = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +7 hour"));		
		$date_update = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +7 hour"));	
		
		$cek_user = DB::connection('sqlsrv_sfa_android')->table('table_user_sfa_network')->where('id_user', $id_user)->get();
		if ($cek_user->count()==0) {
			$return = DB::connection('sqlsrv_sfa_android')->insert('INSERT INTO table_user_sfa_network 
                    (id_user,network,date_create,date_update) 
                    values (?,?,?,?)',
                    [$id_user,$network,$date_create,$date_update]);
					
			if($return != 0){				
				
				return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Saved"]);
			}else{
				return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Saved"]);
			}
		}
		else{
			$return = DB::connection('sqlsrv_sfa_android')->table('table_user_sfa_network')->where('id_user', $id_user)
				->update(                        
				  array(
						'network'   => $network,
						'date_update'   => $date_update
					)
				);
				
			if($return != 0){
				//update sales id dms3
				
				return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Updated"]);
			}else{
				return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Updated"]);
			}
		}
		
		
		
		
	}
	
	public function PostTimeline(Request $request){ 
		$hasher = app()->make('hash');
		$kategori = $request->input('kategori');
		$isi_timeline = $request->input('isi_timeline');
		$id_relasi = $request->input('id_relasi');
		$id_company = $request->input('id_company');
		$photo_timeline = $request->input('photo_timeline');
		$create_by = $request->input('create_by');
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');
		$address = $request->input('address');
		
		$jenis_file = "";
		if($photo_timeline!="")
		{
			$jenis_file = "image";
		}
		
		$return = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_timeline 
					(kategori,isi_timeline,id_relasi,id_company,photo_timeline,create_by,jenis_file,date_create) 
					values (?,?,?,?,?,?,?,getdate())",
					[$kategori,$isi_timeline,$id_relasi,$id_company,$photo_timeline,$create_by,$jenis_file]);
					
		if($return != 0){

			$add_location = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_tracking_activity 
					(kategori,detail_aktifitas,id_relasi,id_company,latitude,longitude,address,create_by,date_create) 
					values (?,?,?,?,?,?,?,?,getdate())",
					[$kategori,$isi_timeline,$id_relasi,$id_company,$latitude,$longitude,$address,$create_by]);
								
			return response()->json(['success' =>  "1",'status' =>  "S",'message' => "Save success"]);
			
		}else{
			return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Save failed"]);
		}
	}

	public function PostTimeline2(Request $request){ 
		$hasher = app()->make('hash');
		$kategori = $request->input('kategori');
		$jenis_post = $request->input('jenis_post');
		$isi_timeline = $request->input('isi_timeline');
		$id_relasi = $request->input('id_relasi');
		$id_company = $request->input('id_company');
		$photo_timeline = $request->input('photo_timeline');
		$create_by = $request->input('create_by');
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');
		$address = $request->input('address');
		
		$jenis_file = "";
		if($photo_timeline!="")
		{
			$jenis_file = "image";
		}
		
		$return = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_timeline 
					(kategori,isi_timeline,id_relasi,id_company,photo_timeline,create_by,jenis_file,date_create) 
					values (?,?,?,?,?,?,?,getdate())",
					[$kategori,$isi_timeline,$id_relasi,$id_company,$photo_timeline,$create_by,$jenis_file]);

		$last_id_timeline_jawaban = 0;
		if($jenis_post != "other"){
			$get_id_timeline = DB::connection('sqlsrv_ilv_android')
			->getPdo()->query("
				select top 1
					*
				from table_timeline
				where kategori = '".$kategori."'
					and isi_timeline like '".$isi_timeline."'
					and id_relasi = '".$id_relasi."'
					and id_company = ".$id_company."
					and photo_timeline = '".$photo_timeline."'
					and create_by = ".$create_by."
					and jenis_file = '".$jenis_file."'
				order by id desc
			");
			$last_id_timeline = $get_id_timeline->fetch();

			$timeline_jawaban = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_timeline_jawaban
						(id_timeline,kategori,jenis_post,create_by,date_create,update_by,date_update) 
						values (?,?,?,?,getdate(),?,getdate())",
						[$last_id_timeline['id'],$kategori,$jenis_post,$create_by,$create_by]);

			$get_id_timeline_jawaban = DB::connection('sqlsrv_ilv_android')
			->getPdo()->query("
				select top 1
					*
				from table_timeline_jawaban
				where id_timeline = '".$last_id_timeline['id']."'
					and kategori = '".$kategori."'
					and jenis_post = '".$jenis_post."'
					and create_by = ".$create_by."
					and update_by = '".$create_by."'
				order by id_timeline_jawaban desc
			");
			$id_timeline_jawaban = $get_id_timeline_jawaban->fetch();
			$last_id_timeline_jawaban = $id_timeline_jawaban['id_timeline_jawaban'];
		}

		if($return != 0){
			$add_location = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_tracking_activity 
					(kategori,detail_aktifitas,id_relasi,id_company,latitude,longitude,address,create_by,date_create) 
					values (?,?,?,?,?,?,?,?,getdate())",
					[$kategori,$isi_timeline,$id_relasi,$id_company,$latitude,$longitude,$address,$create_by]);

			return response()->json(['success' =>  "1",'status' =>  "S",'id_timeline_jawaban' => $last_id_timeline_jawaban,'message' => "Save success"]);
			
		}else{
			return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Save failed"]);
		}
	}
	
	public function PostEditTimeline(Request $request){ 
		$hasher = app()->make('hash');
		$kategori = $request->input('kategori');
		$isi_timeline = $request->input('isi_timeline');
		$id_relasi = $request->input('id_relasi');
		$id_company = $request->input('id_company');
		$photo_timeline = $request->input('photo_timeline');
		$create_by = $request->input('create_by');
		$id_timeline = $request->input('id_timeline');
		$image_post = $request->input('image_post');
		
		$jenis_file = "";
		$img_timeline = $photo_timeline;
		if($photo_timeline!="")
		{
			$jenis_file = "image";
			$img_timeline = $photo_timeline;
		}
		else{
			$jenis_file = "";
			$img_timeline = $image_post;
		}		
		
		
		$return = DB::connection('sqlsrv_ilv_android')->table('table_timeline')->where('id', $id_timeline)->update(                        
				  array(
						'isi_timeline'   => $isi_timeline,
						'photo_timeline'   => $img_timeline,
						'date_create'   => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +7 hour"))
					)
				);
					
		if($return != 0){
								
			return response()->json(['success' =>  "1",'status' =>  "S",'message' => "Save success"]);
			
		}else{
			return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Save failed"]);
		}
	}

	public function PostEditTimeline2(Request $request){ 
		$hasher = app()->make('hash');
		$kategori = $request->input('kategori');
		$jenis_post = $request->input('jenis_post');
		$isi_timeline = $request->input('isi_timeline');
		$id_relasi = $request->input('id_relasi');
		$id_company = $request->input('id_company');
		$photo_timeline = $request->input('photo_timeline');
		$create_by = $request->input('create_by');
		$id_timeline = $request->input('id_timeline');
		$image_post = $request->input('image_post');
		
		$jenis_file = "";
		$img_timeline = $photo_timeline;
		if($photo_timeline!="")
		{
			$jenis_file = "image";
			$img_timeline = $photo_timeline;
		}
		else{
			$jenis_file = "";
			$img_timeline = $image_post;
		}		
		
		
		$return = DB::connection('sqlsrv_ilv_android')->table('table_timeline')->where('id', $id_timeline)->update(                        
			array(
				'isi_timeline'   => $isi_timeline,
				'photo_timeline'   => $img_timeline,
				'date_create'   => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +7 hour"))
			)
		);

		$get_id_timeline_jawaban = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("
			select
				* 
			from table_timeline_jawaban
			where id_timeline = ".$id_timeline."
		");
		$jumlah_row = $get_id_timeline_jawaban->fetchAll();
		$jumlah_jawaban = count($jumlah_row);

		$get_id_timeline_jawaban2 = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("
			select
				* 
			from table_timeline_jawaban
			where id_timeline = ".$id_timeline."
		");
		$id_timeline_jawaban = $get_id_timeline_jawaban2->fetch();
		
		if($jenis_post != 'other'){
			if($jumlah_jawaban > 0){
				$update_timeline_jawaban = DB::connection('sqlsrv_ilv_android')->table('table_timeline_jawaban')->where('id_timeline', $id_timeline)->update(                        
					array(
						'kategori'   => $kategori,
						'jenis_post'   => $jenis_post,
						'update_by' => $create_by,
						'date_update'   => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s')." +7 hour"))
					)
				);

				$delete_jawaban_detail = DB::connection('sqlsrv_ilv_android')->table('table_timeline_jawaban_detail')->where('id_timeline_jawaban', $id_timeline_jawaban['id_timeline_jawaban'])->delete();

				$return_id_timeline_jawaban = $id_timeline_jawaban['id_timeline_jawaban'];
			} else {
				$timeline_jawaban = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_timeline_jawaban
					(id_timeline,kategori,jenis_post,create_by,date_create,update_by,date_update) 
					values (?,?,?,?,getdate(),?,getdate())",
					[$id_timeline,$kategori,$jenis_post,$create_by,$create_by]);

				$get_id_timeline_jawaban = DB::connection('sqlsrv_ilv_android')
				->getPdo()->query("
					select top 1
						*
					from table_timeline_jawaban
					where id_timeline = '".$id_timeline."'
						and kategori = '".$kategori."'
						and jenis_post = '".$jenis_post."'
						and create_by = ".$create_by."
						and update_by = '".$create_by."'
				");
				$last_id_timeline_jawaban = $get_id_timeline_jawaban->fetch();
				$return_id_timeline_jawaban = $last_id_timeline_jawaban['id_timeline_jawaban'];
			}
		} else {
			$delete_jawaban = DB::connection('sqlsrv_ilv_android')->table('table_timeline_jawaban')->where('id_timeline', $id_timeline)->delete();
			$delete_jawaban_detail = DB::connection('sqlsrv_ilv_android')->table('table_timeline_jawaban_detail')->where('id_timeline_jawaban', $id_timeline_jawaban['id_timeline_jawaban'])->delete();

			$return_id_timeline_jawaban = 0;
		}

		if($return != 0){
			return response()->json(['success' =>  "1",'status' =>  "S",'id_timeline_jawaban' => $return_id_timeline_jawaban,'message' => "Save success"]);
		}else{
			return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Save failed"]);
		}
	}
	
	public function DelPostEverything(Request $request){
		
		$hasher = app()->make('hash');
		$id_post = $request->input('id_post');
		
		$return = DB::connection('sqlsrv_ilv_android')->table('table_timeline')->where('id', $id_post)->delete();

		$get_id_timeline_jawaban = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("
			select
				* 
			from table_timeline_jawaban
			where id_timeline = ".$id_post."
		");
		$jumlah_row = $get_id_timeline_jawaban->fetchAll();
		$jumlah_jawaban = count($jumlah_row);

		if($jumlah_jawaban > 0){
			$get_id_timeline_jawaban = DB::connection('sqlsrv_ilv_android')
			->getPdo()->query("
				select top 1
					*
				from table_timeline_jawaban
				where id_timeline = ".$id_post."
			");
			$last_id_timeline_jawaban = $get_id_timeline_jawaban->fetch();
			$return2 = DB::connection('sqlsrv_ilv_android')->table('table_timeline_jawaban')->where('id_timeline', $id_post)->delete();
			$return3 = DB::connection('sqlsrv_ilv_android')->table('table_timeline_jawaban_detail')->where('id_timeline_jawaban', $last_id_timeline_jawaban['id_timeline_jawaban'])->delete();
		}

		if($return == 1){
				
			return response()->json(['success' =>  1,'status' =>  "S",'message' => "Report Telah Dihapus "]);
		}
		else{
			return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Delete Report "]);
		}
	}
	
	public function GetTimeLineUser($start,$id_company,$tanggal,$id_user){ 
        $ReportList_array =array();
		$report_array = array();
		
		$max_item = $start+10;

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where kategori != 'post_everything' and cast(a.date_create as date) = '".$tanggal."' and id_company = '".$id_company."' and a.create_by = '".$id_user."'
							order by a.date_create desc
						) as a where a.RowID > ".$start." AND a.RowID <= ".$max_item.""); 
        $jml_report = $report_sql->rowCount();
		$nmr=1;
		while ($list_user = $report_sql->fetch())
		{			
			$report_array['nmr'] = $nmr;
			$report_array['id_timeline'] = $list_user['id'];
			$report_array['kategori'] = $list_user['kategori'];
			//$report_array['isi_timeline'] = $list_user['isi_timeline'];
			if($list_user['kategori']=="aktifitas")
			{
				// query aktifitas 
				$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
								->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
											left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
											where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
											order by b.date_create desc");
				$row_aktifitas = $aktifitas_sql->fetch();
				$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
			}
			else{
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
			}
			
			if($list_user['id_relasi']!="")
			{
				$report_array['id_relasi'] = $list_user['id_relasi'];		
			}
			else{
				$report_array['id_relasi'] = "0";
			}
			
			$report_array['id_company'] = $list_user['id_company'];
			$report_array['photo_timeline'] = $list_user['photo_timeline'];			
			$report_array['nama_user'] = $list_user['nama_user'];
			$report_array['id_user'] = $list_user['create_by'];
			
			if($tanggal==date('Y-m-d')){
				$report_array['date_create'] = date('H:i', strtotime($list_user['date_create']));
			}
			else{
				$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			}
			
			$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
			
			if($list_user['foto_user']!= null){
				$report_array['foto_user'] = $list_user['foto_user'];
			}
			else{
				$report_array['foto_user'] = "";
			}
			
			//$report_array['jml_komen'] = "";
			$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($hitung_komen->count()!=0){
				$report_array['jml_komen'] = $hitung_komen->count();
			}
			else{
				$report_array['jml_komen'] = 0;
			}
			
			//$report_array['status_like'] = "";
			//get like
			$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->where('create_by', $id_user)->get();
			if($get_like->count()!=0){
				$report_array['status_like'] = 1;
			}
			else{
				$report_array['status_like'] = 0;
			}
			
			//$report_array['jml_like'] = "";
			//jml like
			$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($jml_like->count()!=0){
				$report_array['jml_like'] = $jml_like->count();
			}
			else{
				$report_array['jml_like'] = 0;
			}
			
			array_push($ReportList_array,$report_array);
			$nmr++;
		}
		
		return response()->json($ReportList_array);
		
	}
	
	public function listTimeLineComment($start,$nmr_relasi1,$nmr_relasi2){ 
		$ReportList_array =array();
		$report_array = array();	
		
		$max_item = $start+10;
		
		$relasi2="";
		if($nmr_relasi2!="0")
		{
			$relasi2 = $nmr_relasi2;
		}
		else{
			$relasi2="0";
		}
		
		$report_sql = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("select * from (
					select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY date_create desc) AS RowID, a.*,b.nama_user from table_komentar_timeline as a
					left join table_user as b on a.create_by = b.id
					where  nmr_relasi1='".$nmr_relasi1."' and nmr_relasi2='".$relasi2."' ORDER BY date_create desc
				) as a where a.RowID > ".$start." AND a.RowID <= ".$max_item.""); 
		$jml_report = $report_sql->rowCount();
		
		while ($list_user = $report_sql->fetch())
		{
			$report_array['id'] = $list_user['id'];
			$report_array['kategori'] = $list_user['jenis_komen'];			
			$report_array['isi_komen'] = $list_user['isi_komen'];
			$report_array['from'] = $list_user['nama_user'];
			$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			
			
			array_push($ReportList_array,$report_array);
		}
		
		
		return response()->json($ReportList_array);
	}
	
	public function SaveTimeLineComment(Request $request){
		$hasher = app()->make('hash');
        $nmr_relasi1 = $request->input('nmr_relasi1');
		$nmr_relasi2 = $request->input('nmr_relasi2');
		$isi_komen = $request->input('isi_komen');		
		$jenis_komen = $request->input('jenis_komen');
		$create_by = $request->input('create_by');
		
		//for notif
		$kategori = "tl_komen";
		$read_status = "0";
		$sent_status = "0";
		
		$return = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_komentar_timeline 
					(nmr_relasi1,nmr_relasi2,isi_komen,jenis_komen,create_by,date_create) 
					values (?,?,?,?,?,getdate())",
					[$nmr_relasi1,$nmr_relasi2,$isi_komen,$jenis_komen,$create_by]);
					
		if($return == 1){
			
						
			return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Comment Saved"]);
		}else{
			return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Saved Data Comment"]);
		}
		
	}
	
	public function SaveTimeLineComment2(Request $request){
		$hasher = app()->make('hash');
        $nmr_relasi1 = $request->input('nmr_relasi1');
		$nmr_relasi2 = $request->input('nmr_relasi2');
		$isi_komen = $request->input('isi_komen');		
		$jenis_komen = $request->input('jenis_komen');
		$create_by = $request->input('create_by');
		
		//for notif
		$kategori = "tl_komen";
		$read_status = "0";
		$sent_status = "0";
		$nama_user = $request->input('nama_user');
		
		$return = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_komentar_timeline 
					(nmr_relasi1,nmr_relasi2,isi_komen,jenis_komen,create_by,date_create) 
					values (?,?,?,?,?,getdate())",
					[$nmr_relasi1,$nmr_relasi2,$isi_komen,$jenis_komen,$create_by]);
					
		if($return == 1){
			
			/* insert notifikasi */
			$notif_sql = DB::connection('sqlsrv_ilv_android')
						->getPdo()->query("select a.*,b.nama_user from table_komentar_timeline as a left join table_user as b on a.create_by = b.id 
											where nmr_relasi1 = '".$nmr_relasi1."' and a.create_by !='".$create_by."'"); 
			$jml_notif = $notif_sql->rowCount();		   
			if($jml_notif!=0){	
			
				while ($list_notif = $notif_sql->fetch())
				{

					$insert_notification = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_notification 
						(notification_for,create_by,kategori,status_read,status_sent,id_relasi,date_create) 
						values (?,?,?,?,?,?,getdate())",
						[$list_notif['nama_user'],$create_by,$kategori,$read_status,$sent_status,$nmr_relasi1]);

				}
			}
			else{
				
				$insert_notification = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_notification 
						(notification_for,create_by,kategori,status_read,status_sent,id_relasi,date_create) 
						values (?,?,?,?,?,?,getdate())",
						[$nama_user,$create_by,$kategori,$read_status,$sent_status,$nmr_relasi1]);
			}
			
			/* end insert notifikasi */
			
			return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Comment Saved"]);
		}else{
			return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Saved Data Comment"]);
		}
		
	}
	
	public function listTimeLineLike($start,$nmr_relasi1,$nmr_relasi2){ 
		$ReportList_array =array();
		$report_array = array();	
		
		$max_item = $start+10;
		
		$relasi2="";
		if($nmr_relasi2!="0")
		{
			$relasi2 = $nmr_relasi2;
		}
		else{
			$relasi2="0";
		}
		
		
		$report_sql = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("select * from (
					select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY date_create desc) AS RowID, a.*,b.nama_user from table_timeline_like as a
					left join table_user as b on a.create_by = b.id
					where  nmr_relasi1='".$nmr_relasi1."' and nmr_relasi2='".$relasi2."' ORDER BY date_create desc
				) as a where a.RowID > ".$start." AND a.RowID <= ".$max_item.""); 
		$jml_report = $report_sql->rowCount();
		
		while ($list_user = $report_sql->fetch())
		{
			$report_array['id'] = $list_user['id'];
			$report_array['kategori'] = "";			
			$report_array['isi_komen'] = "";
			$report_array['from'] = $list_user['nama_user'];
			$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			
			
			array_push($ReportList_array,$report_array);
		}
		
		
		return response()->json($ReportList_array);
	}
	
	public function SaveTimeLineLike(Request $request){
		$hasher = app()->make('hash');
        $nmr_relasi1 = $request->input('nmr_relasi1');
		$nmr_relasi2 = $request->input('nmr_relasi2');
		$create_by = $request->input('create_by');
		
		$cek_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $nmr_relasi1)
		->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $create_by)->get();
		if($cek_like->count()==0)
		{
		
			$return = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_timeline_like 
						(nmr_relasi1,nmr_relasi2,create_by,date_create) 
						values (?,?,?,getdate())",
						[$nmr_relasi1,$nmr_relasi2,$create_by]);
						
			if($return == 1){
			
				return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data Like Saved",'act' => "like"]);
			}else{
				return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Saved Data Like"]);
			}
		
		}
		else{
			
			$return = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $nmr_relasi1)
						->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $create_by)->delete();
			if($return == 1){
				
				return response()->json(['success' =>  1,'status' =>  "S",'message' => "Like Removed",'act' => "unlike"]);
			}
			else{
				return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Remove Like"]);
			}
		}
		
	}
	
	public function GetTimeLineByAnakBuah($start,$id_company,$tanggal,$id_user,$username){ 
        $ReportList_array =array();
		$report_array = array();
		
		$max_item = $start+10;

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where kategori != 'post_everything' and cast(a.date_create as date) = '".$tanggal."' and id_company = '".$id_company."'
							and a.create_by in (
								/* awal in */
								select distinct id_user from (
									select distinct id_user,ket from (
									select 
									convert(varchar(50), id_l1, 101) id_l1, 
									convert(varchar(50), l1, 101) l1, 
									convert(varchar(50), id_l2, 101) id_l2, 
									convert(varchar(50), l2, 101) l2,
									convert(varchar(50), id_l3, 101) id_l3, 
									convert(varchar(50), l3, 101) l3,
									convert(varchar(50), id_l4, 101) id_l4, 
									convert(varchar(50), l4, 101) l4,
									convert(varchar(50), id_l5, 101) id_l5, 
									convert(varchar(50), l5, 101) l5,
									convert(varchar(50), id_l6, 101) id_l6,  
									convert(varchar(50), l6, 101) l6,
									convert(varchar(50), id_l7, 101) id_l7, 
									convert(varchar(50), l7, 101) l7,
									convert(varchar(50), id_l8, 101) id_l8, 
									convert(varchar(50), l8, 101) l8 from (
										   select top 100 percent 
												  atasan.*,
												  bawahan2.id as id_l3,
												  bawahan2.nama_user as l3,
												  bawahan3.id as id_l4,
												  bawahan3.nama_user as l4,
												  bawahan4.id as id_l5,
												  bawahan4.nama_user as l5,
												  bawahan5.id as id_l6,
												  bawahan5.nama_user as l6,
												  bawahan6.id as id_l7,
												  bawahan6.nama_user as l7,
												  bawahan7.id as id_l8,
												  bawahan7.nama_user as l8
										   from (
												  select 
														 divisi.nama_divisi,
														 '".$id_user."' as id_l1,
														 '".$username."' as l1,
														 atasan.id as id_l2,
														 atasan.username,
														 atasan.nama_user as l2
												  from table_user atasan
												  left join table_hak_akses_company hak_akses
														 on atasan.id = hak_akses.id_user
												  left join table_company company
														 on hak_akses.code_company = company.code_company
												  left join table_master_divisi divisi
														 on atasan.divisi = divisi.id
												  where company.id = '".$id_company."' and atasan.email_atasan = '".$username."'
										   ) atasan
										   LEFT OUTER JOIN table_user bawahan2
												  on atasan.username = bawahan2.email_atasan
										   LEFT OUTER JOIN table_user bawahan3
												  on bawahan2.username = bawahan3.email_atasan
										   LEFT OUTER JOIN table_user bawahan4
												  on bawahan3.username = bawahan4.email_atasan
										   LEFT OUTER JOIN table_user bawahan5
												  on bawahan4.username = bawahan5.email_atasan
										   LEFT OUTER JOIN table_user bawahan6
												  on bawahan5.username = bawahan6.email_atasan
										   LEFT OUTER JOIN table_user bawahan7
												  on bawahan6.username = bawahan7.email_atasan
										   order by atasan.nama_divisi, 
												  bawahan2.nama_user, 
												  bawahan3.nama_user, 
												  bawahan4.nama_user,
												  bawahan5.nama_user,
												  bawahan6.nama_user,
												  bawahan7.nama_user
									) as a)as b 
									UNPIVOT (
										id_user for ket IN (id_l1, id_l2, id_l3, id_l4, id_l5, id_l6, id_l7, id_l8)
									)AS up
									) as final

								/* akhir in */
							)
							order by a.date_create desc
						) as a where a.RowID > ".$start." AND a.RowID <= ".$max_item.""); 
        $jml_report = $report_sql->rowCount();
		$nmr=1;
		while ($list_user = $report_sql->fetch())
		{			
			$report_array['nmr'] = $nmr;
			$report_array['id_timeline'] = $list_user['id'];
			$report_array['kategori'] = $list_user['kategori'];
			if($list_user['kategori']=="aktifitas")
			{
				// query aktifitas 
				$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
								->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
											left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
											where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
											order by b.date_create asc");
				$row_aktifitas = $aktifitas_sql->fetch();
				$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
			}
			else{
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
			}
			
			if($list_user['id_relasi']!="")
			{
				$report_array['id_relasi'] = $list_user['id_relasi'];		
			}
			else{
				$report_array['id_relasi'] = "0";
			}
			
			$report_array['id_company'] = $list_user['id_company'];
			$report_array['photo_timeline'] = $list_user['photo_timeline'];			
			$report_array['nama_user'] = $list_user['nama_user'];
			$report_array['id_user'] = $list_user['create_by'];
			
			if($tanggal==date('Y-m-d')){
				$report_array['date_create'] = date('H:i', strtotime($list_user['date_create']));
			}
			else{
				$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			}
			
			$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
			
			if($list_user['foto_user']!= null){
				$report_array['foto_user'] = $list_user['foto_user'];
			}
			else{
				$report_array['foto_user'] = "";
			}
			
			//$report_array['jml_komen'] = "";
			$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($hitung_komen->count()!=0){
				$report_array['jml_komen'] = $hitung_komen->count();
			}
			else{
				$report_array['jml_komen'] = 0;
			}
			
			//$report_array['status_like'] = "";
			//get like
			$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->where('create_by', $id_user)->get();
			if($get_like->count()!=0){
				$report_array['status_like'] = 1;
			}
			else{
				$report_array['status_like'] = 0;
			}
			
			//$report_array['jml_like'] = "";
			//jml like
			$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($jml_like->count()!=0){
				$report_array['jml_like'] = $jml_like->count();
			}
			else{
				$report_array['jml_like'] = 0;
			}
			
			array_push($ReportList_array,$report_array);
			$nmr++;
		}
		
		return response()->json($ReportList_array);
		
	}
	
	public function GetTimeLineByAnakBuah2($id_company,$tanggal,$id_user,$username){ 
        $ReportList_array =array();
		$report_array = array();		

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where kategori != 'post_everything' and cast(a.date_create as date) = '".$tanggal."' and id_company = '".$id_company."'
							and (
								a.create_by = '".$id_user."' or a.create_by in (
								/* awal in */
								select distinct id_user from (
									select distinct id_user,ket from (
									select 
									convert(varchar(50), id_l1, 101) id_l1, 
									convert(varchar(50), l1, 101) l1, 
									convert(varchar(50), id_l2, 101) id_l2, 
									convert(varchar(50), l2, 101) l2,
									convert(varchar(50), id_l3, 101) id_l3, 
									convert(varchar(50), l3, 101) l3,
									convert(varchar(50), id_l4, 101) id_l4, 
									convert(varchar(50), l4, 101) l4,
									convert(varchar(50), id_l5, 101) id_l5, 
									convert(varchar(50), l5, 101) l5,
									convert(varchar(50), id_l6, 101) id_l6,  
									convert(varchar(50), l6, 101) l6,
									convert(varchar(50), id_l7, 101) id_l7, 
									convert(varchar(50), l7, 101) l7,
									convert(varchar(50), id_l8, 101) id_l8, 
									convert(varchar(50), l8, 101) l8 from (
										   select top 100 percent 
												  atasan.*,
												  bawahan2.id as id_l3,
												  bawahan2.nama_user as l3,
												  bawahan3.id as id_l4,
												  bawahan3.nama_user as l4,
												  bawahan4.id as id_l5,
												  bawahan4.nama_user as l5,
												  bawahan5.id as id_l6,
												  bawahan5.nama_user as l6,
												  bawahan6.id as id_l7,
												  bawahan6.nama_user as l7,
												  bawahan7.id as id_l8,
												  bawahan7.nama_user as l8
										   from (
												  select 
														 divisi.nama_divisi,
														 '".$id_user."' as id_l1,
														 '".$username."' as l1,
														 atasan.id as id_l2,
														 atasan.username,
														 atasan.nama_user as l2
												  from table_user atasan
												  left join table_hak_akses_company hak_akses
														 on atasan.id = hak_akses.id_user
												  left join table_company company
														 on hak_akses.code_company = company.code_company
												  left join table_master_divisi divisi
														 on atasan.divisi = divisi.id
												  where company.id = '".$id_company."' and atasan.email_atasan = '".$username."'
										   ) atasan
										   LEFT OUTER JOIN table_user bawahan2
												  on atasan.username = bawahan2.email_atasan
										   LEFT OUTER JOIN table_user bawahan3
												  on bawahan2.username = bawahan3.email_atasan
										   LEFT OUTER JOIN table_user bawahan4
												  on bawahan3.username = bawahan4.email_atasan
										   LEFT OUTER JOIN table_user bawahan5
												  on bawahan4.username = bawahan5.email_atasan
										   LEFT OUTER JOIN table_user bawahan6
												  on bawahan5.username = bawahan6.email_atasan
										   LEFT OUTER JOIN table_user bawahan7
												  on bawahan6.username = bawahan7.email_atasan
										   order by atasan.nama_divisi, 
												  bawahan2.nama_user, 
												  bawahan3.nama_user, 
												  bawahan4.nama_user,
												  bawahan5.nama_user,
												  bawahan6.nama_user,
												  bawahan7.nama_user
									) as a)as b 
									UNPIVOT (
										id_user for ket IN (id_l1, id_l2, id_l3, id_l4, id_l5, id_l6, id_l7, id_l8)
									)AS up
									) as final

									/* akhir in */
								)
							)
							order by a.date_create desc
						) as a where nama_user != ''"); 
        $jml_report = $report_sql->rowCount();
		$nmr=1;
		while ($list_user = $report_sql->fetch())
		{			
			$report_array['nmr'] = $nmr;
			$report_array['id_timeline'] = $list_user['id'];
			$report_array['kategori'] = $list_user['kategori'];
			if($list_user['kategori']=="aktifitas")
			{
				// query aktifitas 
				$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
								->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
											left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
											where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
											order by b.date_create asc");
				$row_aktifitas = $aktifitas_sql->fetch();
				$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
			}
			else{
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
			}
			
			if($list_user['id_relasi']!="")
			{
				$report_array['id_relasi'] = $list_user['id_relasi'];		
			}
			else{
				$report_array['id_relasi'] = "0";
			}
			
			$report_array['id_company'] = $list_user['id_company'];
			$report_array['photo_timeline'] = $list_user['photo_timeline'];			
			$report_array['nama_user'] = $list_user['nama_user'];
			$report_array['id_user'] = $list_user['create_by'];
			
			if($tanggal==date('Y-m-d')){
				$report_array['date_create'] = date('H:i', strtotime($list_user['date_create']));
			}
			else{
				$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			}
			
			$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
			
			if($list_user['foto_user']!= null){
				$report_array['foto_user'] = $list_user['foto_user'];
			}
			else{
				$report_array['foto_user'] = "";
			}
			
			//$report_array['jml_komen'] = "";
			$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($hitung_komen->count()!=0){
				$report_array['jml_komen'] = $hitung_komen->count();
			}
			else{
				$report_array['jml_komen'] = 0;
			}
			
			//$report_array['status_like'] = "";
			//get like
			$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->where('create_by', $id_user)->get();
			if($get_like->count()!=0){
				$report_array['status_like'] = 1;
			}
			else{
				$report_array['status_like'] = 0;
			}
			
			//$report_array['jml_like'] = "";
			//jml like
			$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($jml_like->count()!=0){
				$report_array['jml_like'] = $jml_like->count();
			}
			else{
				$report_array['jml_like'] = 0;
			}
			
			array_push($ReportList_array,$report_array);
			$nmr++;
		}
		
		return response()->json($ReportList_array);
		
	}
	
	public function GetTimeLineByAnakBuah3($id_company,$tanggal,$id_user,$username,$jenis_aktifitas,$nama_user){ 
        $ReportList_array =array();
		$report_array = array();

		$nama_user2="";
		if($nama_user!="00"){
			$nama_user2 =" and b.nama_user like '%".str_replace("%20"," ",$nama_user)."%' ";
		}
		
		$jenis_aktifitas2="";
		if($jenis_aktifitas!="All"){
			if($jenis_aktifitas =="agenda"){
				$jenis_aktifitas2 =" and a.kategori = 'aktifitas' ";
			}
			else if($jenis_aktifitas =="tiket"){
				$jenis_aktifitas2 =" and a.kategori = 'complain' ";
			}
		}

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where kategori != 'post_everything' and cast(a.date_create as date) = '".$tanggal."' and id_company = '".$id_company."'
							and (
								a.create_by = '".$id_user."' or a.create_by in (
								/* awal in */
								select distinct id_user from (
									select distinct id_user,ket from (
									select 
									convert(varchar(50), id_l1, 101) id_l1, 
									convert(varchar(50), l1, 101) l1, 
									convert(varchar(50), id_l2, 101) id_l2, 
									convert(varchar(50), l2, 101) l2,
									convert(varchar(50), id_l3, 101) id_l3, 
									convert(varchar(50), l3, 101) l3,
									convert(varchar(50), id_l4, 101) id_l4, 
									convert(varchar(50), l4, 101) l4,
									convert(varchar(50), id_l5, 101) id_l5, 
									convert(varchar(50), l5, 101) l5,
									convert(varchar(50), id_l6, 101) id_l6,  
									convert(varchar(50), l6, 101) l6,
									convert(varchar(50), id_l7, 101) id_l7, 
									convert(varchar(50), l7, 101) l7,
									convert(varchar(50), id_l8, 101) id_l8, 
									convert(varchar(50), l8, 101) l8 from (
										   select top 100 percent 
												  atasan.*,
												  bawahan2.id as id_l3,
												  bawahan2.nama_user as l3,
												  bawahan3.id as id_l4,
												  bawahan3.nama_user as l4,
												  bawahan4.id as id_l5,
												  bawahan4.nama_user as l5,
												  bawahan5.id as id_l6,
												  bawahan5.nama_user as l6,
												  bawahan6.id as id_l7,
												  bawahan6.nama_user as l7,
												  bawahan7.id as id_l8,
												  bawahan7.nama_user as l8
										   from (
												  select 
														 divisi.nama_divisi,
														 '".$id_user."' as id_l1,
														 '".$username."' as l1,
														 atasan.id as id_l2,
														 atasan.username,
														 atasan.nama_user as l2
												  from table_user atasan
												  left join table_hak_akses_company hak_akses
														 on atasan.id = hak_akses.id_user
												  left join table_company company
														 on hak_akses.code_company = company.code_company
												  left join table_master_divisi divisi
														 on atasan.divisi = divisi.id
												  where company.id = '".$id_company."' and atasan.email_atasan = '".$username."'
										   ) atasan
										   LEFT OUTER JOIN table_user bawahan2
												  on atasan.username = bawahan2.email_atasan
										   LEFT OUTER JOIN table_user bawahan3
												  on bawahan2.username = bawahan3.email_atasan
										   LEFT OUTER JOIN table_user bawahan4
												  on bawahan3.username = bawahan4.email_atasan
										   LEFT OUTER JOIN table_user bawahan5
												  on bawahan4.username = bawahan5.email_atasan
										   LEFT OUTER JOIN table_user bawahan6
												  on bawahan5.username = bawahan6.email_atasan
										   LEFT OUTER JOIN table_user bawahan7
												  on bawahan6.username = bawahan7.email_atasan
										   order by atasan.nama_divisi, 
												  bawahan2.nama_user, 
												  bawahan3.nama_user, 
												  bawahan4.nama_user,
												  bawahan5.nama_user,
												  bawahan6.nama_user,
												  bawahan7.nama_user
									) as a)as b 
									UNPIVOT (
										id_user for ket IN (id_l1, id_l2, id_l3, id_l4, id_l5, id_l6, id_l7, id_l8)
									)AS up
									) as final

									/* akhir in */
								)
							) ".$nama_user2." ".$jenis_aktifitas2."
							order by a.date_create desc
						) as a where nama_user != ''"); 
        $jml_report = $report_sql->rowCount();
		$nmr=1;
		while ($list_user = $report_sql->fetch())
		{			
			$report_array['nmr'] = $nmr;
			$report_array['id_timeline'] = $list_user['id'];
			$report_array['kategori'] = $list_user['kategori'];
			if($list_user['kategori']=="aktifitas")
			{
				// query aktifitas 
				$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
								->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
											left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
											where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
											order by b.date_create asc");
				
				$jml_aktifitas = $aktifitas_sql->rowCount();
				//$report_array['isi_timeline'] = "";
				if($jml_aktifitas!=0)
				{
					$row_aktifitas = $aktifitas_sql->fetch();
					$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
				}
				else{
					$report_array['isi_timeline'] = $list_user['isi_timeline'];
				}
			}
			else{
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
			}
			
			if($list_user['id_relasi']!="")
			{
				$report_array['id_relasi'] = $list_user['id_relasi'];		
			}
			else{
				$report_array['id_relasi'] = "0";
			}
			
			$report_array['id_company'] = $list_user['id_company'];
			$report_array['photo_timeline'] = $list_user['photo_timeline'];			
			$report_array['nama_user'] = $list_user['nama_user'];
			$report_array['id_user'] = $list_user['create_by'];
			
			if($tanggal==date('Y-m-d')){
				$report_array['date_create'] = date('H:i', strtotime($list_user['date_create']));
			}
			else{
				$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			}
			
			$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
			
			if($list_user['foto_user']!= null){
				$report_array['foto_user'] = $list_user['foto_user'];
			}
			else{
				$report_array['foto_user'] = "";
			}
			
			//$report_array['jml_komen'] = "";
			$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($hitung_komen->count()!=0){
				$report_array['jml_komen'] = $hitung_komen->count();
			}
			else{
				$report_array['jml_komen'] = 0;
			}
			
			//$report_array['status_like'] = "";
			//get like
			$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->where('create_by', $id_user)->get();
			if($get_like->count()!=0){
				$report_array['status_like'] = 1;
			}
			else{
				$report_array['status_like'] = 0;
			}
			
			//$report_array['jml_like'] = "";
			//jml like
			$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $list_user['id_relasi'])->get();
			if($jml_like->count()!=0){
				$report_array['jml_like'] = $jml_like->count();
			}
			else{
				$report_array['jml_like'] = 0;
			}
			
			array_push($ReportList_array,$report_array);
			$nmr++;
		}
		
		return response()->json($ReportList_array);
		
	}
	
	public function CheckAnakBuah($id_company,$id_user,$username){ 
        
		$report_array = array();

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select distinct id_user from (
									select distinct id_user,ket from (
									select 
									convert(varchar(50), id_l1, 101) id_l1, 
									convert(varchar(50), l1, 101) l1, 
									convert(varchar(50), id_l2, 101) id_l2, 
									convert(varchar(50), l2, 101) l2,
									convert(varchar(50), id_l3, 101) id_l3, 
									convert(varchar(50), l3, 101) l3,
									convert(varchar(50), id_l4, 101) id_l4, 
									convert(varchar(50), l4, 101) l4,
									convert(varchar(50), id_l5, 101) id_l5, 
									convert(varchar(50), l5, 101) l5,
									convert(varchar(50), id_l6, 101) id_l6,  
									convert(varchar(50), l6, 101) l6,
									convert(varchar(50), id_l7, 101) id_l7, 
									convert(varchar(50), l7, 101) l7,
									convert(varchar(50), id_l8, 101) id_l8, 
									convert(varchar(50), l8, 101) l8 from (
										   select top 100 percent 
												  atasan.*,
												  bawahan2.id as id_l3,
												  bawahan2.nama_user as l3,
												  bawahan3.id as id_l4,
												  bawahan3.nama_user as l4,
												  bawahan4.id as id_l5,
												  bawahan4.nama_user as l5,
												  bawahan5.id as id_l6,
												  bawahan5.nama_user as l6,
												  bawahan6.id as id_l7,
												  bawahan6.nama_user as l7,
												  bawahan7.id as id_l8,
												  bawahan7.nama_user as l8
										   from (
												  select 
														 divisi.nama_divisi,
														 '".$id_user."' as id_l1,
														 'iwan.setiawan@padmatirtawisesa.com' as l1,
														 atasan.id as id_l2,
														 atasan.username,
														 atasan.nama_user as l2
												  from table_user atasan
												  left join table_hak_akses_company hak_akses
														 on atasan.id = hak_akses.id_user
												  left join table_company company
														 on hak_akses.code_company = company.code_company
												  left join table_master_divisi divisi
														 on atasan.divisi = divisi.id
												  where company.id = '".$id_company."' and atasan.email_atasan = '".$username."'
										   ) atasan
										   LEFT OUTER JOIN table_user bawahan2
												  on atasan.username = bawahan2.email_atasan
										   LEFT OUTER JOIN table_user bawahan3
												  on bawahan2.username = bawahan3.email_atasan
										   LEFT OUTER JOIN table_user bawahan4
												  on bawahan3.username = bawahan4.email_atasan
										   LEFT OUTER JOIN table_user bawahan5
												  on bawahan4.username = bawahan5.email_atasan
										   LEFT OUTER JOIN table_user bawahan6
												  on bawahan5.username = bawahan6.email_atasan
										   LEFT OUTER JOIN table_user bawahan7
												  on bawahan6.username = bawahan7.email_atasan
										   order by atasan.nama_divisi, 
												  bawahan2.nama_user, 
												  bawahan3.nama_user, 
												  bawahan4.nama_user,
												  bawahan5.nama_user,
												  bawahan6.nama_user,
												  bawahan7.nama_user
									) as a)as b 
									UNPIVOT (
										id_user for ket IN (id_l1, id_l2, id_l3, id_l4, id_l5, id_l6, id_l7, id_l8)
									)AS up
									) as final")->fetchAll(); 
		/*
		// and somewhere later: https://phpdelusions.net/pdo_examples/select
		foreach ($report_sql as $row) {
			echo $row['name']."<br />\n";
		}
		*/
        $jml_report = count($report_sql);
		if($jml_report!=0){
			$report_array['success'] = 1;
			$report_array['jml_anak_buah'] = $jml_report;
			$report_array['message'] = "Anda memiliki anak buah";
		}
		else{
			$report_array['success'] = 0;
			$report_array['jml_anak_buah'] = $jml_report;
			$report_array['message'] = "Anda tidak memiliki anak buah";
		}
		
		
		return response()->json($report_array);
		
	}
	
	public function GetTimeLinePostEverything($id_company,$tanggal,$id_user){ 
        $ReportList_array =array();
		$report_array = array();
		
		$pieces = explode("-", $id_user);
		$jml_piece = count($pieces);
		$idUser = $id_user;
		$myPost = "";
		if($jml_piece==1)
		{
			$idUser = $id_user;
			$myPost = "";
		}
		else{
			$idUser = $pieces[0];
			if($pieces[1]=="All"){
				$myPost = "";
			}
			else{
				$myPost = " and a.create_by = '".$idUser."' ";
			}
		}

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where kategori = 'post_everything' and cast(a.date_create as date) = '".$tanggal."' and id_company = '".$id_company."' ".$myPost." 
							order by a.date_create desc
						) as a where a.id !=''"); 
						
		$report_sql2 = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select top 20 * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where kategori = 'post_everything' and id_company = '".$id_company."' ".$myPost." 
							order by a.date_create desc
						) as a where a.id !=''"); 
						
        $jml_report = $report_sql->rowCount();
		if($jml_report!=0)
		{
			$nmr=1;
			while ($list_user = $report_sql->fetch())
			{			
				$report_array['nmr'] = $nmr;
				$report_array['id_timeline'] = $list_user['id'];
				$report_array['kategori'] = $list_user['kategori'];
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
				/*
				if($list_user['kategori']=="aktifitas")
				{
					// query aktifitas 
					$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
									->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
												left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
												where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
												order by b.date_create desc");
					$row_aktifitas = $aktifitas_sql->fetch();
					$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
				}
				else{
					$report_array['isi_timeline'] = $list_user['isi_timeline'];
				}
				*/
				
				$nmr_relasi2 = "0";
				if($list_user['id_relasi']!="")
				{
					$report_array['id_relasi'] = $list_user['id_relasi'];
					$nmr_relasi2 = $list_user['id_relasi'];
				}
				else{
					$report_array['id_relasi'] = "0";
					$nmr_relasi2 = "0";
				}
				
				$report_array['id_company'] = $list_user['id_company'];
				$report_array['photo_timeline'] = $list_user['photo_timeline'];			
				$report_array['nama_user'] = $list_user['nama_user'];
				$report_array['id_user'] = $list_user['create_by'];
				
				if($list_user['jenis_file']=="video")
				{
					$report_array['jenis_file'] = "video";
				}
				else{
					$report_array['jenis_file'] = "non_video";
				}
				
				if($tanggal==date('Y-m-d')){
					$report_array['date_create'] = date('H:i', strtotime($list_user['date_create']));
				}
				else{
					$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
				}
				
				$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
				
				if($list_user['foto_user']!= null){
					$report_array['foto_user'] = $list_user['foto_user'];
				}
				else{
					$report_array['foto_user'] = "";
				}
				
				//$report_array['jml_komen'] = "";
				$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($hitung_komen->count()!=0){
					$report_array['jml_komen'] = $hitung_komen->count();
				}
				else{
					$report_array['jml_komen'] = 0;
				}
				
				//$report_array['status_like'] = "";
				//get like
				$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $idUser)->get();
				if($get_like->count()!=0){
					$report_array['status_like'] = 1;
				}
				else{
					$report_array['status_like'] = 0;
				}
				
				//$report_array['jml_like'] = "";
				//jml like
				$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($jml_like->count()!=0){
					$report_array['jml_like'] = $jml_like->count();
				}
				else{
					$report_array['jml_like'] = 0;
				}
				
				array_push($ReportList_array,$report_array);
				$nmr++;
			}
		}
		else{
			
			$nmr=1;
			while ($list_user = $report_sql2->fetch())
			{			
				$report_array['nmr'] = $nmr;
				$report_array['id_timeline'] = $list_user['id'];
				$report_array['kategori'] = $list_user['kategori'];
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
				/*
				if($list_user['kategori']=="aktifitas")
				{
					// query aktifitas 
					$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
									->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
												left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
												where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
												order by b.date_create desc");
					$row_aktifitas = $aktifitas_sql->fetch();
					$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
				}
				else{
					$report_array['isi_timeline'] = $list_user['isi_timeline'];
				}
				*/
				
				$nmr_relasi2 = "0";
				if($list_user['id_relasi']!="")
				{
					$report_array['id_relasi'] = $list_user['id_relasi'];
					$nmr_relasi2 = $list_user['id_relasi'];
				}
				else{
					$report_array['id_relasi'] = "0";
					$nmr_relasi2 = "0";
				}
				
				$report_array['id_company'] = $list_user['id_company'];
				$report_array['photo_timeline'] = $list_user['photo_timeline'];			
				$report_array['nama_user'] = $list_user['nama_user'];
				$report_array['id_user'] = $list_user['create_by'];
				
				if($list_user['jenis_file']=="video")
				{
					$report_array['jenis_file'] = "video";
				}
				else{
					$report_array['jenis_file'] = "non_video";
				}
				
				$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
				
				$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
				
				if($list_user['foto_user']!= null){
					$report_array['foto_user'] = $list_user['foto_user'];
				}
				else{
					$report_array['foto_user'] = "";
				}
				
				//$report_array['jml_komen'] = "";
				$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($hitung_komen->count()!=0){
					$report_array['jml_komen'] = $hitung_komen->count();
				}
				else{
					$report_array['jml_komen'] = 0;
				}
				
				//$report_array['status_like'] = "";
				//get like
				$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $idUser)->get();
				if($get_like->count()!=0){
					$report_array['status_like'] = 1;
				}
				else{
					$report_array['status_like'] = 0;
				}
				
				//$report_array['jml_like'] = "";
				//jml like
				$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($jml_like->count()!=0){
					$report_array['jml_like'] = $jml_like->count();
				}
				else{
					$report_array['jml_like'] = 0;
				}
				
				array_push($ReportList_array,$report_array);
				$nmr++;
			}
		}
		
		return response()->json($ReportList_array);
		
	}

	public function GetTimeLinePostEverything2($id_company,$tanggal,$id_user){
		$timeline = array();
		$report_array = array();
		
		$pieces = explode("-", $id_user);
		$jml_piece = count($pieces);
		$idUser = $id_user;
		$myPost = "";
		if($jml_piece==1)
		{
			$idUser = $id_user;
			$myPost = "";
		}
		else{
			$idUser = $pieces[0];
			if($pieces[1]=="All"){
				$myPost = "";
			}
			else{
				$myPost = " and a.create_by = '".$idUser."' ";
			}
		}

		$report_sql = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("
			select 
				* 
			from (
				select TOP 100 PERCENT 
					ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
					a.*,
					b.nama_user,
					c.foto_user,
					case when jawaban.jenis_post is null then 'other' else jawaban.jenis_post end as jenis_post
				from table_timeline as a 
				left join table_user as b 
					on a.create_by = b.id 
				left join table_user_photo as c 
					on b.id = c.id_user
				left join table_timeline_jawaban jawaban
					on jawaban.id_timeline = a.id
				where a.kategori = 'post_everything' 
					and cast(a.date_create as date) = '".$tanggal."' 
					and a.id_company = '".$id_company."' 
					".$myPost." 
				order by a.date_create desc
			) as a 
			where a.id !=''
		");

		$report_sql2 = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("
			select top 20 
				* 
			from (
				select TOP 100 PERCENT 
					ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
					a.*,
					b.nama_user,
					c.foto_user,
					case when jawaban.jenis_post is null then 'other' else jawaban.jenis_post end as jenis_post
				from table_timeline as a 
				left join table_user as b 
					on a.create_by = b.id 
				left join table_user_photo as c 
					on b.id = c.id_user
				left join table_timeline_jawaban jawaban
					on jawaban.id_timeline = a.id
				where a.kategori = 'post_everything' 
					and a.id_company = '".$id_company."' 
					".$myPost." 
				order by a.date_create desc
			) as a where a.id !=''
		"); 
						
        $jml_report = $report_sql->rowCount();
		if($jml_report!=0){
			$nmr=1;
			while ($list_user = $report_sql->fetch()){
				$report_array['nmr'] = $nmr;
				$report_array['id_timeline'] = $list_user['id'];
				$report_array['kategori'] = $list_user['kategori'];
				$report_array['jenis_post'] = $list_user['jenis_post'];
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
				/*
				if($list_user['kategori']=="aktifitas")
				{
					// query aktifitas 
					$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
									->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
												left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
												where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
												order by b.date_create desc");
					$row_aktifitas = $aktifitas_sql->fetch();
					$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
				}
				else{
					$report_array['isi_timeline'] = $list_user['isi_timeline'];
				}
				*/
				
				$nmr_relasi2 = "0";
				if($list_user['id_relasi']!="")
				{
					$report_array['id_relasi'] = $list_user['id_relasi'];
					$nmr_relasi2 = $list_user['id_relasi'];
				}
				else{
					$report_array['id_relasi'] = "0";
					$nmr_relasi2 = "0";
				}
				
				$report_array['id_company'] = $list_user['id_company'];
				$report_array['photo_timeline'] = $list_user['photo_timeline'];			
				$report_array['nama_user'] = $list_user['nama_user'];
				$report_array['id_user'] = $list_user['create_by'];
				
				if($list_user['jenis_file']=="video")
				{
					$report_array['jenis_file'] = "video";
				}
				else{
					$report_array['jenis_file'] = "non_video";
				}
				
				if($tanggal==date('Y-m-d')){
					$report_array['date_create'] = date('H:i', strtotime($list_user['date_create']));
				}
				else{
					$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
				}
				
				$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
				
				if($list_user['foto_user']!= null){
					$report_array['foto_user'] = $list_user['foto_user'];
				}
				else{
					$report_array['foto_user'] = "";
				}
				
				//$report_array['jml_komen'] = "";
				$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($hitung_komen->count()!=0){
					$report_array['jml_komen'] = $hitung_komen->count();
				}
				else{
					$report_array['jml_komen'] = 0;
				}
				
				//$report_array['status_like'] = "";
				//get like
				$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $idUser)->get();
				if($get_like->count()!=0){
					$report_array['status_like'] = 1;
				}
				else{
					$report_array['status_like'] = 0;
				}
				
				//$report_array['jml_like'] = "";
				//jml like
				$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($jml_like->count()!=0){
					$report_array['jml_like'] = $jml_like->count();
				}
				else{
					$report_array['jml_like'] = 0;
				}

				$nmr++;

				//timeline_pertanyaan
				$list_timeline = array();
				$report_array_pertanyaan = array();

				$report_pertanyaan = DB::connection('sqlsrv_ilv_android')
				->getPdo()->query("
					select 
						jawaban.id_timeline_jawaban,
						jawaban.id_timeline,
						jawaban.kategori,
						jawaban.jenis_post,
						jawaban_detail.id_timeline_pertanyaan,
						pertanyaan.judul_pertanyaan,
						pertanyaan.pertanyaan,
						pertanyaan.tipe_pertanyaan,
						pertanyaan.keterangan_system,
						jawaban_detail.jawaban
					from table_timeline_jawaban jawaban
					left join table_timeline_jawaban_detail jawaban_detail
						on jawaban.id_timeline_jawaban = jawaban_detail.id_timeline_jawaban
					left join table_timeline_pertanyaan pertanyaan
						on pertanyaan.id_timeline_pertanyaan = jawaban_detail.id_timeline_pertanyaan
					where jawaban.id_timeline = ".$list_user['id']."
					order by pertanyaan.nomor_pertanyaan
				");
				$nomor = 1;
				while ($list_pertanyaan = $report_pertanyaan->fetch()){
					$report_array_pertanyaan['nmr'] = $nomor;
					$report_array_pertanyaan['id_timeline_jawaban'] = $list_pertanyaan['id_timeline_jawaban'];
					$report_array_pertanyaan['id_timeline'] = $list_pertanyaan['id_timeline'];
					$report_array_pertanyaan['kategori'] = $list_pertanyaan['kategori'];
					$report_array_pertanyaan['jenis_post'] = $list_pertanyaan['jenis_post'];
					$report_array_pertanyaan['id_timeline_pertanyaan'] = $list_pertanyaan['id_timeline_pertanyaan'];
					$report_array_pertanyaan['judul_pertanyaan'] = $list_pertanyaan['judul_pertanyaan'];
					$report_array_pertanyaan['pertanyaan'] = $list_pertanyaan['pertanyaan'];
					$report_array_pertanyaan['tipe_pertanyaan'] = $list_pertanyaan['tipe_pertanyaan'];
					$report_array_pertanyaan['keterangan_system'] = $list_pertanyaan['keterangan_system'];
					$report_array_pertanyaan['jawaban'] = $list_pertanyaan['jawaban'];
					array_push($list_timeline,$report_array_pertanyaan);
					$nomor++;
				}
				$report_array['data_pertanyaan'] = $list_timeline;
				array_push($timeline,$report_array);
			}
		} else{
			$nmr=1;
			while ($list_user = $report_sql2->fetch()){
				$report_array['nmr'] = $nmr;
				$report_array['id_timeline'] = $list_user['id'];
				$report_array['kategori'] = $list_user['kategori'];
				$report_array['jenis_post'] = $list_user['jenis_post'];
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
				/*
				if($list_user['kategori']=="aktifitas")
				{
					// query aktifitas 
					$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
									->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
												left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
												where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
												order by b.date_create desc");
					$row_aktifitas = $aktifitas_sql->fetch();
					$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
				}
				else{
					$report_array['isi_timeline'] = $list_user['isi_timeline'];
				}
				*/
				
				$nmr_relasi2 = "0";
				if($list_user['id_relasi']!="")
				{
					$report_array['id_relasi'] = $list_user['id_relasi'];
					$nmr_relasi2 = $list_user['id_relasi'];
				}
				else{
					$report_array['id_relasi'] = "0";
					$nmr_relasi2 = "0";
				}
				
				$report_array['id_company'] = $list_user['id_company'];
				$report_array['photo_timeline'] = $list_user['photo_timeline'];			
				$report_array['nama_user'] = $list_user['nama_user'];
				$report_array['id_user'] = $list_user['create_by'];
				
				if($list_user['jenis_file']=="video")
				{
					$report_array['jenis_file'] = "video";
				}
				else{
					$report_array['jenis_file'] = "non_video";
				}
				
				$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
				
				$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
				
				if($list_user['foto_user']!= null){
					$report_array['foto_user'] = $list_user['foto_user'];
				}
				else{
					$report_array['foto_user'] = "";
				}
				
				//$report_array['jml_komen'] = "";
				$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($hitung_komen->count()!=0){
					$report_array['jml_komen'] = $hitung_komen->count();
				}
				else{
					$report_array['jml_komen'] = 0;
				}
				
				//$report_array['status_like'] = "";
				//get like
				$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $idUser)->get();
				if($get_like->count()!=0){
					$report_array['status_like'] = 1;
				}
				else{
					$report_array['status_like'] = 0;
				}
				
				//$report_array['jml_like'] = "";
				//jml like
				$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
					->where('nmr_relasi2', $nmr_relasi2)->get();
				if($jml_like->count()!=0){
					$report_array['jml_like'] = $jml_like->count();
				}
				else{
					$report_array['jml_like'] = 0;
				}
				$nmr++;

				//timeline_pertanyaan
				$list_timeline = array();
				$report_array_pertanyaan = array();

				$report_pertanyaan = DB::connection('sqlsrv_ilv_android')
				->getPdo()->query("
					select 
						jawaban.id_timeline_jawaban,
						jawaban.id_timeline,
						jawaban.kategori,
						jawaban.jenis_post,
						jawaban_detail.id_timeline_pertanyaan,
						pertanyaan.pertanyaan,
						jawaban_detail.jawaban
					from table_timeline_jawaban jawaban
					left join table_timeline_jawaban_detail jawaban_detail
						on jawaban.id_timeline_jawaban = jawaban_detail.id_timeline_jawaban
					left join table_timeline_pertanyaan pertanyaan
						on pertanyaan.id_timeline_pertanyaan = jawaban_detail.id_timeline_pertanyaan
					where jawaban.id_timeline = ".$list_user['id']."
					order by pertanyaan.nomor_pertanyaan
				");
				$nomor = 1;
				while ($list_pertanyaan = $report_pertanyaan->fetch()){
					$report_array_pertanyaan['nmr'] = $nomor;
					$report_array_pertanyaan['id_timeline_jawaban'] = $list_pertanyaan['id_timeline_jawaban'];
					$report_array_pertanyaan['id_timeline'] = $list_pertanyaan['id_timeline'];
					$report_array_pertanyaan['kategori'] = $list_pertanyaan['kategori'];
					$report_array_pertanyaan['jenis_post'] = $list_pertanyaan['jenis_post'];
					$report_array_pertanyaan['id_timeline_pertanyaan'] = $list_pertanyaan['id_timeline_pertanyaan'];
					$report_array_pertanyaan['pertanyaan'] = $list_pertanyaan['pertanyaan'];
					$report_array_pertanyaan['jawaban'] = $list_pertanyaan['jawaban'];
					array_push($list_timeline,$report_array_pertanyaan);
					$nomor++;
				}
				$report_array['data_pertanyaan'] = $list_timeline;
				array_push($timeline,$report_array);
			}
		}

		return response()->json($timeline);
	}
	
	public function GetIsiPostEverything($id_timeline){ 
       
		$user_array = array();	
        $user_sql = DB::connection('sqlsrv_ilv_android')
					->getPdo()->query("select isi_timeline,photo_timeline from table_timeline where id='".$id_timeline."'"); 
        $row_user = $user_sql->fetchAll();
		$jml_user = count($row_user);
		
		if($jml_user!=0){
        
			
			$user_array['success'] = 1;
			$user_array['isi_timeline'] = $row_user[0]['isi_timeline'];				
			$user_array['photo_timeline'] = $row_user[0]['photo_timeline'];						
			
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			
		}
		return response()->json($user_array);
	}

	public function GetIsiPostEverything2($id_timeline){ 
		$user_array = array();	
		$user_sql = DB::connection('sqlsrv_ilv_android')
		->getPdo()->query("
			select 
				timeline.isi_timeline,
				timeline.photo_timeline,
				case when jawaban.jenis_post is null then 'other' else jawaban.jenis_post end as jenis_post
			from table_timeline timeline
			left join table_timeline_jawaban jawaban
				on timeline.id = jawaban.id_timeline
			where timeline.id = '".$id_timeline."'
		"); 
		$row_user = $user_sql->fetchAll();
		$jml_user = count($row_user);
		
		if($jml_user!=0){
			$user_array['success'] = 1;
			$user_array['isi_timeline'] = $row_user[0]['isi_timeline'];
			$user_array['photo_timeline'] = $row_user[0]['photo_timeline'];
			$user_array['jenis_post'] = $row_user[0]['jenis_post'];

			//timeline_pertanyaan
			$list_timeline = array();
			$report_array_pertanyaan = array();

			$report_pertanyaan = DB::connection('sqlsrv_ilv_android')
			->getPdo()->query("
				select 
					jawaban.id_timeline_jawaban,
					jawaban.id_timeline,
					jawaban.kategori,
					jawaban.jenis_post,
					jawaban_detail.id_timeline_pertanyaan,
					pertanyaan.judul_pertanyaan,
					pertanyaan.pertanyaan,
					pertanyaan.tipe_pertanyaan,
					pertanyaan.keterangan_system,
					jawaban_detail.jawaban
				from table_timeline_jawaban jawaban
				left join table_timeline_jawaban_detail jawaban_detail
					on jawaban.id_timeline_jawaban = jawaban_detail.id_timeline_jawaban
				left join table_timeline_pertanyaan pertanyaan
					on pertanyaan.id_timeline_pertanyaan = jawaban_detail.id_timeline_pertanyaan
				where jawaban.id_timeline = ".$id_timeline."
				order by pertanyaan.nomor_pertanyaan
			");
			$nomor = 1;
			while ($list_pertanyaan = $report_pertanyaan->fetch()){
				$report_array_pertanyaan['nmr'] = $nomor;
				$report_array_pertanyaan['id_timeline_jawaban'] = $list_pertanyaan['id_timeline_jawaban'];
				$report_array_pertanyaan['id_timeline'] = $list_pertanyaan['id_timeline'];
				$report_array_pertanyaan['kategori'] = $list_pertanyaan['kategori'];
				$report_array_pertanyaan['jenis_post'] = $list_pertanyaan['jenis_post'];
				$report_array_pertanyaan['id_timeline_pertanyaan'] = $list_pertanyaan['id_timeline_pertanyaan'];
				$report_array_pertanyaan['judul_pertanyaan'] = $list_pertanyaan['judul_pertanyaan'];
				$report_array_pertanyaan['pertanyaan'] = $list_pertanyaan['pertanyaan'];
				$report_array_pertanyaan['tipe_pertanyaan'] = $list_pertanyaan['tipe_pertanyaan'];
				$report_array_pertanyaan['keterangan_system'] = $list_pertanyaan['keterangan_system'];
				$report_array_pertanyaan['jawaban'] = $list_pertanyaan['jawaban'];
				array_push($list_timeline,$report_array_pertanyaan);
				$nomor++;
			}
			$user_array['data_pertanyaan'] = $list_timeline;
		}
		else{
			$user_array['success'] = 0;
			$user_array['message'] = "Data Tidak Ditemukan";
			
		}
		return response()->json($user_array);
	}
	 
	public function GetTimeLineByNotif($id_timeline,$id_user){ 
        $ReportList_array =array();
		$report_array = array();
		
		

		$report_sql = DB::connection('sqlsrv_ilv_android')
        ->getPdo()->query("select * from (
							select TOP 100 PERCENT ROW_NUMBER() OVER(ORDER BY a.date_create desc) AS RowID,
							a.*,b.nama_user,c.foto_user from table_timeline as a left join table_user as b on a.create_by = b.id 
							left join table_user_photo as c on b.id = c.id_user
							where a.id = '".$id_timeline."' 
							order by a.date_create desc
						) as a where a.id !=''"); 
        $jml_report = $report_sql->rowCount();
		$nmr=1;
		while ($list_user = $report_sql->fetch())
		{			
			$report_array['nmr'] = $nmr;
			$report_array['id_timeline'] = $list_user['id'];
			$report_array['kategori'] = $list_user['kategori'];
			//$report_array['isi_timeline'] = $list_user['isi_timeline'];
			
			if($list_user['kategori']=="aktifitas")
			{
				// query aktifitas 
				$aktifitas_sql = DB::connection('sqlsrv_ilv_android')
								->getPdo()->query("select top 1 a.nmr_aktifitas,b.detail_aktifitas from table_aktifitas as a 
											left join table_aktifitas_detail as b on a.nmr_aktifitas = b.nmr_aktifitas
											where  a.nmr_aktifitas = '".$list_user['id_relasi']."'
											order by b.date_create desc");
				$jml_aktifitas = $aktifitas_sql->rowCount();
				//$report_array['isi_timeline'] = "";
				if($jml_aktifitas!=0)
				{
					$row_aktifitas = $aktifitas_sql->fetch();
					$report_array['isi_timeline'] = substr($row_aktifitas['detail_aktifitas'],0,100);
				}
				else{
					$report_array['isi_timeline'] = $list_user['isi_timeline'];
				}
			}
			else{
				$report_array['isi_timeline'] = $list_user['isi_timeline'];
			}
			
			
			$nmr_relasi2 = "0";
			if($list_user['id_relasi']!="")
			{
				$report_array['id_relasi'] = $list_user['id_relasi'];
				$nmr_relasi2 = $list_user['id_relasi'];
			}
			else{
				$report_array['id_relasi'] = "0";
				$nmr_relasi2 = "0";
			}
			
			$report_array['id_company'] = $list_user['id_company'];
			$report_array['photo_timeline'] = $list_user['photo_timeline'];			
			$report_array['nama_user'] = $list_user['nama_user'];
			$report_array['id_user'] = $list_user['create_by'];
			
			if($list_user['jenis_file']=="video")
			{
				$report_array['jenis_file'] = "video";
			}
			else{
				$report_array['jenis_file'] = "non_video";
			}
			
			$report_array['date_create'] = date('Y-m-d H:i', strtotime($list_user['date_create']));
			
			$report_array['tgl_timeline'] = date('Y-m-d', strtotime($list_user['date_create']));
			
			if($list_user['foto_user']!= null){
				$report_array['foto_user'] = $list_user['foto_user'];
			}
			else{
				$report_array['foto_user'] = "";
			}
			
			//$report_array['jml_komen'] = "";
			$hitung_komen = DB::connection('sqlsrv_ilv_android')->table('table_komentar_timeline')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $nmr_relasi2)->get();
			if($hitung_komen->count()!=0){
				$report_array['jml_komen'] = $hitung_komen->count();
			}
			else{
				$report_array['jml_komen'] = 0;
			}
			
			//$report_array['status_like'] = "";
			//get like
			$get_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $nmr_relasi2)->where('create_by', $id_user)->get();
			if($get_like->count()!=0){
				$report_array['status_like'] = 1;
			}
			else{
				$report_array['status_like'] = 0;
			}
			
			//$report_array['jml_like'] = "";
			//jml like
			$jml_like = DB::connection('sqlsrv_ilv_android')->table('table_timeline_like')->where('nmr_relasi1', $list_user['id'])
				->where('nmr_relasi2', $nmr_relasi2)->get();
			if($jml_like->count()!=0){
				$report_array['jml_like'] = $jml_like->count();
			}
			else{
				$report_array['jml_like'] = 0;
			}
			
			array_push($ReportList_array,$report_array);
			$nmr++;
		}
		
		return response()->json($ReportList_array);
		
	}

	public function GetPertanyaanTimeline($jenis_post){
		$list_pertanyaan_array =array();
		$pertanyaan_array = array();

		$connection = DB::connection("sqlsrv_ilv_android")->getPdo()->query("
			select
				*
			from table_timeline_pertanyaan pertanyaan
			where pertanyaan.kategori = 'post_everything'
				and pertanyaan.jenis_post = '".$jenis_post."'
				and pertanyaan.status_pertanyaan = 'aktif'
			order by pertanyaan.nomor_pertanyaan
		");
		while ($row = $connection->fetch()){
			$pertanyaan_array['id_pertanyaan'] = $row['id_timeline_pertanyaan'];
			$pertanyaan_array['nomor_pertanyaan'] = $row['nomor_pertanyaan'];
			$pertanyaan_array['judul_pertanyaan'] = $row['judul_pertanyaan'];
			$pertanyaan_array['pertanyaan'] = $row['pertanyaan'];
			$pertanyaan_array['tipe_pertanyaan'] = $row['tipe_pertanyaan'];
			$pertanyaan_array['keterangan_system'] = $row['keterangan_system'];
			
			if($jenis_post == "ops_gwp"){
				$pertanyaan_array['sub_question'] = array();
				
				$sub_question_sql = DB::connection('sqlsrv_ilv_android')->getPdo()->query("
					SELECT 
						* 
					FROM table_timeline_pertanyaan_item_gwp 
					WHERE id_timeline_pertanyaan = '".$row['id_timeline_pertanyaan']."'
					ORDER BY nomor_pertanyaan
				"); 
				while ($list_sub_question = $sub_question_sql->fetch())
				{
					$items['nomor_pertanyaan'] = $list_sub_question['nomor_pertanyaan'];
					$items['jawaban'] = $list_sub_question['jawaban'];
					$items['score'] = $list_sub_question['score'];
					array_push($pertanyaan_array['sub_question'],$items);
				}
			}

			array_push($list_pertanyaan_array,$pertanyaan_array);
		}

		return response()->json($list_pertanyaan_array);
	}

	public function PostTimeline2JawabanDetail(Request $request){
		$hasher = app()->make('hash');
		$id_timeline_jawaban = $request->input('id_timeline_jawaban');
		$id_timeline_pertanyaan = $request->input('id_timeline_pertanyaan');
		$jawaban = $request->input('jawaban');
		
		$return = DB::connection('sqlsrv_ilv_android')->insert("INSERT INTO table_timeline_jawaban_detail 
					(id_timeline_jawaban,id_timeline_pertanyaan,jawaban)
					values (?,?,?)",
					[$id_timeline_jawaban,$id_timeline_pertanyaan,$jawaban]);

		if($return != 0){
			return response()->json(['success' =>  "1",'status' =>  "S",'message' => "Save success"]);
		} else {
			return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Save failed"]);
		}
	}
}

?>