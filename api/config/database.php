<?php 
return array(

    'default' => 'mysql',

    'connections' => array(

        'sqlsrv' => array(

            'driver' => 'sqlsrv',
            'host' => '139.255.62.131',	 //10.50.1.230	139.255.62.131	
			'port'      => '4545', //1433 4545
            'database' => 'db_pic2_replika',
            'username' => 'sa',
            'password' => 'P4dm4t1rt4Gr0up',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		'sqlsrv_android' => array(

            'driver' => 'sqlsrv',
            'host' => '10.100.100.21',			
			'port'      => '1433',
            'database' => 'db_padma_android',
            'username' => 'sa',
            'password' => 'padm4.4',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		'sqlsrv_ilv_android' => array(

            'driver' => 'sqlsrv',
            'host' => '10.100.100.20',			
			'port'      => '1433',
            'database' => 'db_ilv_padma',
            'username' => 'sa',
            'password' => 'padm4.4',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
        'sqlsrv_jotform_android' => array(

            'driver' => 'sqlsrv',
            'host' => '10.100.100.20',			
			'port'      => '1433',
            'database' => 'db_jotform',
            'username' => 'sa',
            'password' => 'padm4.4',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		'mysql_ilv_web' => array(

            'driver' => 'mysql',
            'host' => '10.100.100.20',
			'port'      => '3306',
            'database' => 'dashboard_ilv',
            'username' => 'iwan',
            'password' => 'i021172sIS',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		'sqlsrv_sfa_android' => array(

            'driver' => 'sqlsrv',
            'host' => '10.100.100.21',			
			'port'      => '1433',
            'database' => 'db_padma_sfa_android',
            'username' => 'sa',
            'password' => 'padm4.4',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		'sqlsrv_xeise_android' => array(

            'driver' => 'sqlsrv',
            'host' => '10.100.100.16',			
			'port'      => '1433',
            'database' => 'xeise_self_audit',
            'username' => 'sa',
            'password' => 'padm4.4',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),

        'mysql' => array(

            'driver' => 'mysql',
            'host' => '139.255.62.132',
			'port'      => '3306',
            'database' => 'android_test',
            'username' => 'vehicle',
            'password' => '98578ii0gr',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		'mysql_dms3' => array(

            'driver' => 'mysql',
            'host' => '10.100.100.111',
			'port'      => '3308',
            'database' => 'dms',
            'username' => 'iwan',
            'password' => 'i021172sIS',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),		
		
		'mysql_dms3_padma' => array(

            'driver' => 'mysql',
            'host' => '10.100.100.111',
			'port'      => '3306',
            'database' => 'dms',
            'username' => 'sfa_sales',
            'password' => 'P4dm4t1rt4Gr0up',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
			
				
		'mysql_dms3_ata' => array(

            'driver' => 'mysql',
            'host' => '10.100.100.111',
			'port'      => '3307',
            'database' => 'dms',
            'username' => 'sfa_sales',
            'password' => 'P4dm4t1rt4Gr0up',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
				
				
		'mysql_dms3_masking' => array(

            'driver' => 'mysql',
            'host' => '10.100.100.230',
			'port'      => '3308',
            'database' => 'dms_masking',
            'username' => 'sfa_sales',
            'password' => 'P4dm4t1rt4Gr0up',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),

        		
		'sqlsrv_padma_survey' => array(

            'driver' => 'sqlsrv',
            'host' => '127.0.0.1',			
			'port'      => '1433',
            'database' => 'db_padma_survey',
            'username' => 'sa',
            'password' => 'padm4.4',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		'mysql_beliaqua' => array(

            'driver' => 'mysql',
            'host' => '139.255.62.132',
			'port'      => '3306',
            'database' => 'beliaqua',
            'username' => 'vehicle',
            'password' => '98578ii0gr',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
        
        'mysql_gpsxeise' => array(

            'driver' => 'mysql',
            'host' => '10.100.100.10',
			'port'      => '3306',
            'database' => 'gps_traccar',
            'username' => 'iwan',
            'password' => 'i021172sIS',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),

        'mysql_ftm' => array(

            'driver' => 'mysql',
            'host' => '10.50.1.22',
			'port'      => '3306',
            'database' => 'ftm',
            'username' => 'it',
            'password' => 'it.45',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),

        'mysql_fp' => array(

            'driver' => 'mysql',
            'host' => '10.50.1.23',
			'port'      => '3309',
            'database' => 'fin_pro',
            'username' => 'it',
            'password' => 'it.45',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),
		
		/*'postgre' => array(

            'driver' => 'pgsql',
            'host' => '10.45.1.139',
			'port'      => '5432',
            'database' => 'test_padma',
            'username' => 'padma',
            'password' => 'padma123',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',

        ),*/

    ),

);
?>