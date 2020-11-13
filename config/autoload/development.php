<?php
return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
				'params' => array(
					'host' => '159.164.180.61',
					'port' => '3306',
					'user' => 'ricardo',
					'password' =>  'ricardo',
					'dbname' => 'zuni_dev',
					'charset' => 'utf8',
					'driverOptions' => array(
						\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
					)
				)
			),
			'odm_default' => array(
				'server'           => '159.164.180.61',
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
		'upload_temp_dir' => '/var/www/storage/uploadfiles4/tmp/',
		//'upload_temp_dir' => 'R:\uploadfiles\tmp\\',
		'upload_dir' => '/var/www/storage/uploadfiles4/',
		//'upload_dir' => 'R:\uploadfiles\\',
		'upload_url_temp' => 'http://159.164.180.61:8087/uploadfiles4/tmp/',
		'upload_url' => 'http://159.164.180.61:8087/uploadfiles4/',
		'thumbnail_url' => 'http://159.164.180.61:8888/unsafe/size/smart/',
		'valid_extensions' => array('jpg', 'jpeg', 'png', 'gif'),
		'itensPerPage' => 20,
		'max_time_connection' => 48, //Horas
		'max_time_persistent_connection' => 168 //Horas
	)
);