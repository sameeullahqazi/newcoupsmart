<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

function __autoload($class_name) {
	if(file_exists('../classes/'.$class_name.'.class.php'))
    	require_once '../classes/'.$class_name.'.class.php';
}

$db = new Database();
try{
	$db->connect();
} catch(Exception $e) {
	Errors::show500();
}
global $db;


header('Content-Type: application/json; charset=utf-8');

$src = 			!empty($_POST['src'])		?	$_POST['src']		: '';
$w =			!empty($_POST['w'])			?	$_POST['w']			: '96';
$h =			!empty($_POST['h'])			?	$_POST['h']			: '96';
$bucket_name =	!empty($_POST['bucket'])	?	$_POST['bucket']	: null;

//error_log("bucket: " . var_export($bucket_name, true));
	
if ($src != '') {
	$newImg = CacheImage::getImg($src, $w, $h, $bucket_name);
	echo json_encode($newImg);
}

?>
