<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class GPSController extends Controller
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
	
	public function DevicePosition($position_id)
	{
		$data = array();
        $row = DB::connection('mysql_gpsxeise')->table('tc_positions')
		->select('tc_devices.id', 'tc_devices.name', 'tc_devices.category', 'tc_devices.status','tc_positions.latitude', 'tc_positions.longitude', 'tc_positions.devicetime')
		->leftJoin('tc_devices', 'tc_positions.deviceid', '=', 'tc_devices.id')
		->where('tc_positions.id', '=' , $position_id)
		->first();

		$data[] = array(
			'id' => $row->id,
			'name' => $row->name,
			'category' => $row->category,
			'status' => $row->status,
			'latitude' => $row->latitude,
			'longitude' => $row->longitude,
			'devicetime' => date('d-m-Y H:i', strtotime($row->devicetime. ' +7 hours')),
		);
		// $data[] = ['query' => $sql];
		return response()->json($data);
	}

	public function DeviceLocation(Request $request)
	{
		$data = array();
		$device_id = $request->input('device_id');
        $row = DB::connection('mysql_gpsxeise')->table('tc_devices')
		->select('tc_devices.id', 'tc_devices.name', 'tc_devices.category', 'tc_devices.status', 'tc_devices.positionid','tc_positions.latitude', 'tc_positions.longitude', 'tc_positions.devicetime')
		->leftJoin('tc_positions', 'tc_positions.id', '=', 'tc_devices.positionid')
		->where('tc_devices.id', '=' , $device_id)
		->first();

		$data[] = array(
			'id' => $row->id,
			'name' => $row->name,
			'category' => $row->category,
			'status' => $row->status,
			'positionid' => $row->positionid,
			'latitude' => $row->latitude,
			'longitude' => $row->longitude,
			'devicetime' => date('d-m-Y H:i', strtotime($row->devicetime. ' +7 hours')),
		);
		// $data[] = ['query' => $sql];
		return response()->json($data);
	}

	public function ListDevicePosition(Request $request)
	{
		$user_id = $request->input('userid');
		$data = array();

		// if ($user_id == "7") {
		// 	$result = DB::connection('mysql_gpsxeise')->table('tc_devices')
		// 	->select('tc_devices.name', 'tc_devices.category', 'tc_devices.status', 'tc_positions.latitude', 'tc_positions.longitude', 'tc_positions.devicetime')
		// 	->leftJoin('tc_positions', 'tc_positions.id', '=', 'tc_devices.positionid')
		// 	->orderBy('tc_devices.id', 'asc')
		// 	->get();
		// 	foreach ($result as $result) {
				
		// 		$data[] = array(
		// 			'name' => $result->name,
		// 			'category' => $result->category,
		// 			'latitude' => $result->latitude,
		// 			'longitude' => $result->longitude,
		// 			'status' => $result->status,
		// 			'devicetime' => date('d-m-Y H:i', strtotime($result->devicetime. ' +7 hours')),
		// 		);
				
		// 	}
		// 	// $data[] = ['query' => $sql];
		// 	return response()->json($data);
		// } else {
			$result = DB::connection('mysql_gpsxeise')->table('tc_users')
			->select('tc_devices.name', 'tc_devices.category', 'tc_devices.status', 'tc_positions.latitude', 'tc_positions.longitude', 'tc_positions.devicetime')
			->leftJoin('tc_user_device', 'tc_users.id', '=', 'tc_user_device.userid')
			->leftJoin('tc_devices', 'tc_user_device.deviceid', '=', 'tc_devices.id')
			->leftJoin('tc_positions', 'tc_positions.id', '=', 'tc_devices.positionid')
			->where('tc_users.id', '=' , $user_id) 
			->orderBy('tc_devices.id', 'asc')
			->get();
			foreach ($result as $result) {
				
				$data[] = array(
					'name' => $result->name,
					'category' => $result->category,
					'latitude' => $result->latitude,
					'longitude' => $result->longitude,
					'status' => $result->status,
					'devicetime' => date('d-m-Y H:i', strtotime($result->devicetime. ' +7 hours')),
				);
				
			}
			// $data[] = ['query' => $sql];
			return response()->json($data);
		// }
	}

	public function TrackingReport(Request $request)
	{
		$data = array();
        $device_id = $request->input('device_id');
		$tanggal = $request->input('tanggal');

		$result = DB::connection('mysql_gpsxeise')
		->getPdo()
		->query("SELECT a.name, a.category, a.status, b.latitude, b.longitude, b.devicetime
		FROM tc_devices a
		LEFT JOIN tc_positions b
		ON b.deviceid = a.id
		WHERE a.id = ".$device_id."
		AND DATE(b.devicetime) = '".$tanggal."'
		GROUP BY a.name, a.category, a.status, b.latitude, b.longitude, b.devicetime
		ORDER BY b.devicetime ASC");

		while ($row = $result->fetch()) {
			$data[] = array(
				'name' => $row['name'],
				'category' => $row['category'],
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude'],
				'devicetime' => date("d-m-Y H:i", strtotime($row['devicetime'] . ' +7 hours')),
				'type' => $row['status'],
			);
		}

		// $data[] = ['query' => $sql];
		return response()->json($data);
	}

	public function TrackingReport2(Request $request)
	{
		$data = array();
        $device_id = $request->input('device_id');
		$tanggal = $request->input('tanggal');
		$jam_awal = $request->input('jam_awal');
		$jam_akhir = $request->input('jam_akhir');
		$tanggal1 = date("Y-m-d H:i:s", strtotime($tanggal." ".$jam_awal. ' -7 hours'));
		$tanggal2 = date("Y-m-d H:i:s", strtotime($tanggal." ".$jam_akhir. ' -7 hours'));
		
		$result = DB::connection('mysql_gpsxeise')
		->getPdo()
		->query("SELECT
					a.name,
					a.category,
					a.status,
					b.latitude,
					b.longitude,
					c.name AS tag,
				MIN(b.servertime) AS devicetime,
				COUNT(b.latitude) AS durasi
				FROM
				tc_devices a
				LEFT JOIN tc_positions b
					ON b.deviceid = a.id
				LEFT JOIN gps_traccar_alt.position_address c
					ON b.latitude = c.latitude
					AND b.longitude = c.longitude
					AND a.id = c.deviceid
				WHERE a.id = ".$device_id."
				AND b.servertime BETWEEN '".$tanggal1."' AND '".$tanggal2."'
				AND b.latitude != 0 AND b.longitude != 0
				GROUP BY a.name,
				a.category,
				a.status,
				b.latitude,
				b.longitude,
				c.name");

		while ($row = $result->fetch()) {
			$data[] = array(
				'name' => $row['name'],
				'category' => $row['category'],
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude'],
				'devicetime' => date("d-m-Y H:i", strtotime($row['devicetime'] . ' +7 hours')),
				'type' => $row['status'],
				'durasi' => $row['durasi'],
				'tag' => $row['tag'],
			);
		}

		// $data[] = ['query' => $sql];
		return response()->json($data);
	}

	public function ListDevice()
	{
		$data = array();
        $result = DB::connection('mysql_gpsxeise')->table('tc_devices')->orderBy('id', 'asc')->get();
		foreach ($result as $row) {
			$data[] = array(
				'id' => $row->id,
				'name' => $row->name,
			);
		}
		// $data[] = ['query' => $sql];
		return response()->json($data);
	}

	public function ListDeviceUser(Request $request)
	{
		$user_id = $request->input('userid');
		$data = array();

		// if ($user_id == "7") {
		// 	$result = DB::connection('mysql_gpsxeise')
		// 	->getPdo()
		// 	->query("SELECT c.id, c.name,  c.lastupdate, c.category, c.positionid, c.status
		// 		FROM tc_devices c
		// 		ORDER BY c.id"); 

		// 		while ($row = $result->fetch()) {
		// 			$data[] = array(
		// 				'id' => $row['id'],
		// 				'name' => $row['name'],
		// 				'lastupdate' =>  date('Y-m-d H:i:s', strtotime($row['lastupdate'] . ' +7 hours')),
		// 				'category' => $row['category'],
		// 				'positionid' => $row['positionid'],
		// 				'status' => $row['status'],
		// 			);
		// 		}
		// 	// $data[] = ['query' => $sql];
		// 	return response()->json($data);
		// } else {
			$result = DB::connection('mysql_gpsxeise')
			->getPdo()
			->query("SELECT c.id, c.name,  c.lastupdate, c.category, c.positionid, c.status
				FROM tc_users a
				LEFT JOIN tc_user_device b
				ON a.id = b.userid
				LEFT JOIN tc_devices c
				ON b.deviceid = c.id
				WHERE a.id = ".$user_id."
				ORDER BY c.id"); 

				while ($row = $result->fetch()) {
					$data[] = array(
						'id' => $row['id'],
						'name' => $row['name'],
						'lastupdate' =>  date('Y-m-d H:i:s', strtotime($row['lastupdate'] . ' +7 hours')),
						'category' => $row['category'],
						'positionid' => $row['positionid'],
						'status' => $row['status'],
					);
				}
			// $data[] = ['query' => $sql];
			return response()->json($data);
		// }
	}

	public function SaveAddress(Request $request)
	{
		$positionid = $request->input('positionid');
		$address = $request->input('address');
		$deviceid = $request->input('deviceid');
		$latitude = $request->input('latitude');
		$longitude = $request->input('longitude');
		$name = $request->input('name');
		$data = array();

		

		// if (!$latitude && !$longitude) {
			// $row = DB::connection('mysql_gpsxeise')->table('tc_positions')
			// 		->select('latitude', 'longitude')
			// 		->where('id', '=' , $positionid)
			// 		->first();

			// $latitude = $row->latitude;
			// $longitude = $row->longitude;
		// }

		$check = DB::connection('mysql_gpsxeise')
			->getPdo()
			->query("SELECT * FROM position_address WHERE latitude = '".$latitude."' and longitude = '".$longitude."' and deviceid = '".$deviceid."'");
		

		if ($check->rowCount() == 0) {
			$result = DB::connection('mysql_gpsxeise')
			->getPdo()
			->query("INSERT INTO position_address (positionid, address, deviceid, name, latitude, longitude) VALUES ('".$positionid."', '".$address."', '".$deviceid."', '".$name."', '".$latitude."','".$longitude."')");

			if($result->rowCount() != 0){
				return response()->json(['success' =>  "1",'status' =>  "S",'message' => "Save success"]);
			} else {
				return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Save failed"]);
			}
	
		} else {
			return response()->json(['success' =>  "0",'status' =>  "E",'message' => "Data already exist"]);
		}
		
		return response()->json($data);
	}

	public function ListRoute(Request $request)
	{
		$data = array();
		$device_id = $request->input('device_id');
		$tanggal = $request->input('tanggal');
		$jam_awal = $request->input('jam_awal');
		$jam_akhir = $request->input('jam_akhir');
		$tanggal1 = date("Y-m-d H:i:s", strtotime($tanggal." ".$jam_awal. ' -7 hours'));
		$tanggal2 = date("Y-m-d H:i:s", strtotime($tanggal." ".$jam_akhir. ' -7 hours'));

        $result = DB::connection('mysql_gpsxeise')
				->getPdo()
				->query("SELECT a.latitude,
						a.longitude,
						a.deviceid,
						b.name,
						MIN(a.servertime) AS devicetime,
						COUNT(a.latitude) AS durasi
						FROM tc_positions a
						LEFT JOIN gps_traccar_alt.position_address b
							ON a.deviceid = b.deviceid
							AND a.latitude = b.latitude
							AND a.longitude = b.longitude
						WHERE a.deviceid = ".$device_id." 
						AND a.servertime BETWEEN '".$tanggal1."' AND '".$tanggal2."'
						AND a.latitude != 0 AND a.longitude != 0
						GROUP BY a.latitude,
						a.longitude,
						a.deviceid,
						b.name");

		while ($row = $result->fetch()) {
			$data[] = array(
				'latitude' => $row['latitude'],
				'longitude' => $row['longitude'],
				'deviceid' => $row['deviceid'],
				'name' => $row['name'],
				'devicetime' => date("d-m-Y H:i", strtotime($row['devicetime'] . ' +7 hours')),
				'durasi' => $row['durasi'],
			);
		}

		// $data[] = ['query' => $sql];
		return response()->json($data);
	}

	public function LoginUser(Request $request)
	{
		$hasher = app()->make('hash');
		$server = $request->input('server');
		$username = $request->input('username');
		$password = $request->input('password');
		$data = array();

        $result = DB::connection('mysql_gpsxeise')->table('tc_users')->where('email', $username)->get();

        if ($result->count() > 0) {
			foreach ($result as $result) {
				$data['success'] = 1; 
				$data['message'] = "login success";
				$data['id'] = $result->id;
				$data['name'] = $result->name;
				$data['username'] = $result->email;
			}
        } else {
			$data['success'] = 0;
			$data['message'] = "login failed";
        }

		return response()->json($data);
	}

	// public function PostPanel(Request $request)
	// { 
    //     $id = $request->input('id');
    //     $title = $request->input('title');		
	// 	$url = $request->input('url');
		
	// 	if ($id == "") {
	// 		$return = DB::connection('sqlsrv_jotform_android')->table('table_panel_view')->insert(                        
	// 			array(
	// 				'title'   => $title,												
	// 				'url'   => $url,
	// 		   	)
	// 		);
	// 	} else {
	// 		$return = DB::connection('sqlsrv_jotform_android')->table('table_panel_view')->where('id', $id)->update(                        
	// 			array(
	// 				'title'   => $title,												
	// 				'url'   => $url,
	// 		   )
	// 		);
	// 	}
				
	// 	if($return != 0){
	// 		return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data change saved"]);
	// 	}else{
	// 		return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed to handle request"]);
	// 	}
	// }

	// public function DelItem(Request $request)
	// { 
    //     $id = $request->input('id');
    //     $purpose = $request->input('purpose');
		
	// 	$return = DB::connection('sqlsrv_jotform_android')->table('table_'.$purpose.'_view')->where('id', $id)->delete();
				
	// 	if($return != 0){
	// 		return response()->json(['success' =>  1,'status' =>  "S",'message' => "Data has been deleted"]);
	// 	}else{
	// 		return response()->json(['success' =>  0,'status' =>  "E",'message' => "Failed To Delete Data"]);
	// 	}
	// }
}
