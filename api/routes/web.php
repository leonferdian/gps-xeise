<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->group(['prefix' => 'gps-xeise'], function () use ($app) {
	$app->get('device_position/{position_id}','GPSController@DevicePosition');
    $app->get('list_device','GPSController@ListDevice');
	$app->post('login','GPSController@LoginUser');
	$app->post('tracking_report','GPSController@TrackingReport');
	$app->post('tracking_report2','GPSController@TrackingReport2');
	$app->post('list_device_user','GPSController@ListDeviceUser');
	$app->post('device_location','GPSController@DeviceLocation');
	$app->post('list_device_position','GPSController@ListDevicePosition');
	$app->post('save_address','GPSController@SaveAddress');
	$app->post('list_route','GPSController@ListRoute');
});