<?php

	// Excel class for reading in Excel documents
	// and turning them into XML.
	require_once(dirname(__DIR__) . '/includes/app_config.php');
	// require_once(dirname(__DIR__) . '/includes/sdk-1.5.15/sdk.class.php');
	require_once(dirname(__DIR__) . '/includes/UUID.php');
	
	class CoupsmartS3 {
		private $s3;
		
		public function __construct()
		{
			
		}
		
		public function s3_connect()
		{
		
		}
		
		public function add_voucher_obj($obj, $bucket_name = "csvouchers")
		{
			global $upload_bucket;
			
			$file_name = md5(uniqid()) . ".jpg";
			$file_path =  $upload_bucket . '/' . $file_name;
			$s3_upload_path = dirname(__DIR__) . '/' . $file_path;
			$res = copy($obj, $s3_upload_path);
			if($res)
			{
				return $file_name;
			}
			else
			{
				error_log("could not copy file in CoupsmartS3::add_voucher_obj()");
			}
			return null;
		}
	}

?>