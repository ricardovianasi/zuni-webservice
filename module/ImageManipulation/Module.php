<?php
namespace ImageManipulation;

class Module {
	
	public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        	'Zend\Loader\ClassMapAutoloader' => array(
        		__DIR__ . '/autoload_classmap.php'
        	)
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }
}