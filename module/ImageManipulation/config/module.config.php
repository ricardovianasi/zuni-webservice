<?php
return array(
	'service_manager' => array(
		'factories' => array(
			'imageFiles' => function ($sm) {
        		$config = $sm->get('Configuration');
        		$obj = new \ImageManipulation\Service\ImageFiles($config['zuni']);
        		return $obj;
        	}
		),
	),
);
