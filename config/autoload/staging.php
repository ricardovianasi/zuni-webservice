<?php
return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
				'params' => array(
					'host' => 'saturno.adm-serv.ufmg.br',
					'port' => '3306',
					'user' => 'ne_cedecom',
					'password' =>  '_c3d3C0M_UFMG',
					'dbname' => 'app_des_ne_cedecom_zuni',
					'charset' => 'utf8',
					'driverOptions' => array(
<<<<<<< HEAD
						PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
=======
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
>>>>>>> develop
					)
				)
			),
			'odm_default' => array(
<<<<<<< HEAD
				'server'           => '150.164.80.212',
=======
				'server'           => '159.164.180.61',
>>>>>>> develop
				'port'             => '27017',
				'connectionString' => null,
				'user'             => 'zuni',
				'password'         => 'zuni',
				'dbname'           => 'zuni',
				'options'          => array()
			),
		),
		'configuration' => array(
			'odm_default' => array(
				'default_db' => 'zuni',
			)
		),

	),
	'zuni' => array(
		'upload_temp_dir' => '/storage /opt/zuni-ds/tmp/',
		//'upload_temp_dir' => 'R:\uploadfiles\tmp\\',
		'upload_dir' => '/storage /opt/zuni-ds/',
		//'upload_dir' => 'R:\uploadfiles\\',
<<<<<<< HEAD
		'upload_url_temp' => 'http://150.164.80.212:8087/uploadfiles/tmp/',
		'upload_url' => 'http://150.164.80.212:8087/uploadfiles/',
=======
		'upload_url_temp' => 'http://159.164.180.61:8087/uploadfiles/tmp/',
		'upload_url' => 'http://159.164.180.61:8087/uploadfiles/',
>>>>>>> develop
		'thumbnail_url' => 'https://app-des.adm-serv.ufmg.br/unsafe/smart/',
		'valid_extensions' => array('jpg', 'jpeg', 'png', 'gif'),
		'itensPerPage' => 20,
		'max_time_connection' => 48, //Horas
		'max_time_persistent_connection' => 168 //Horas
	)
);