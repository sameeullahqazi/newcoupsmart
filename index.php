<?php

DEFINE('DOC_ROOT', __DIR__);
if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);
	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID >= 50300) {
	error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
} else {
	error_reporting(-1);
}

function autoload_classes($class_name)
{
    $file = 'classes/' . $class_name. '.class.php';
    if (file_exists($file))
    {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');
global $db;

$request = !empty($_GET['request']) ? $_GET['request'] : '';
$arr_request = explode('/', $request);

$arr_read_replica_sections = array(
	'manager' => 1,
);


$arr_read_replica_controllers = array(
	// 'csapi' => 1,
	// 'test' => 1,
);

$arr_discard_read_replica_controllers = array(
	'accountsettings' => 1,
	'employeemanagement' => 1,
	'appmanagement' => 1,
	'integrations' => 1,
	'js' => 1,
	'cc-dealsmanager' => 1,
	'cc-main' => 1,
);


$is_read_replica = (isset($arr_read_replica_sections[$arr_request[0]]) && !isset($arr_discard_read_replica_controllers[$arr_request[1]]) ) || isset($arr_read_replica_controllers[$arr_request[1]]);
$is_super_user = false;
$db = new Database($is_super_user, $is_read_replica);
try{
	$db->connect();
} catch(Exception $e)
{
	error_log("Error connecting to database: " . $e->getMessage());
	Errors::show500();
}
// error_log("GET in index in newcoupsmart: " . var_export($_GET, true));

// $session = new Session();
//start the session
session_start();
date_default_timezone_set('UTC');

FrontController::render();
?>
