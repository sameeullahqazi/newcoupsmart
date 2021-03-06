<?php

class Errors
{
	
	public static function show404()
	{
		header("HTTP/1.1 404 Not Found");
		require_once(dirname(__DIR__) ."/404.html");
		exit;
	}
	
	public static function show500()
	{
		header("HTTP/1.1 500 Server Error");
		require_once(dirname(__DIR__) ."/500.html");
		exit;
	}
	
	public static function show600()
	{
		header("HTTP/1.1 600 Server Error");
		require_once(dirname(__DIR__) ."/600.html");
		exit;
	}
	
	public static function show700()
	{
		header("HTTP/1.1 700 Server Error");
		require_once(dirname(__DIR__) ."/700.html");
		exit;
	}
}

?>
