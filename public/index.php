<?php

date_default_timezone_set('America/Sao_Paulo');

// Define application environment
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
$env = getenv('APPLICATION_ENV');
if ($env == 'development' || $env == 'local') {
    ini_set("display_errors", 1);
    error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
} else {
    ini_set("display_errors", 0);
    //error_reporting(0);
}

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
