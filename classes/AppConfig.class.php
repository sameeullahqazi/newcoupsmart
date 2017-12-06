<?php
class AppConfig extends BasicDataObject
{
	var $email_queue_locked;
	var $smart_email_lindt_offset;
	var $last_locked;
	var $enable_fb_realtime_updates;
	var $short_url_domain;
	var $srv_portal_script_last_updated;
	var $enable_sp_column_mapping;
	
	function __construct($read_only = true){
		$this->Select('1=1', $read_only);
	}
	
	function update_email_queue_locked($email_queue_locked)
	{
		try
		{
			$sql = "update `app_config` set `email_queue_locked` = '$email_queue_locked'";
			if($email_queue_locked == '1')
				$sql .= ", `last_locked` = now()";
			if(!Database::mysqli_query($sql))
				throw new Exception ("SQL update error in AppConfig::update_email_queue_locked(): ".Database::mysqli_error() . "\nSQL: $sql");
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	function lock_email_queue()
	{
		$res = false;
		try
		{
			$app_config = new AppConfig(false);
			if($app_config->email_queue_locked == 0)
			{
				$app_config->email_queue_locked = 1;
				$app_config->last_locked = 'now()';
				$app_config->Update();
				
				$res = true;
			}
			return $res;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	function unlock_email_queue()
	{
		try
		{
			$app_config = new AppConfig(false);
			$app_config->email_queue_locked = 0;
			$app_config->Update();
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
}

?>
