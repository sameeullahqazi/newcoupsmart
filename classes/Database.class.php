<?php
class Database
{
	
	var $web_host, $db_host, $user, $pass, $db, $link;
	
	public function __construct($is_super_user = false, $is_read_replica = false)
	{
		$http_user_agent = (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
		if ($_SERVER['SERVER_NAME'] == 'dev.coupsmart.com' || $http_user_agent == 'AWS Elastic Beanstalk Host Manager - Health Check') {
			$this->db_host = 'coupsmartdev.ctkwe3a7glb1.us-east-1.rds.amazonaws.com';
			$this->user = 'cs_db_user';
			$this->pass = 'zJjhng75htAVfD5YTXmHl3od';
			$this->db = 'coupsmart';
			if($is_super_user)
			{
				$this->user = 'coupsmart';
				$this->pass = 'csrocks13';
			}
		} else {
			$this->db_host = 'localhost';
			$this->user = 'cs_db_user';
			$this->pass = 'cs_db_p@$$';
			$this->db = 'newcoupsmart';
		}
		
		global $db_pref;
		if(isset($db_pref))
		{
			$this->db = $db_pref;

		}
	}
	/*
	public function connect()
	{
		$this->link = mysql_connect($this->db_host, $this->user, $this->pass);
		if (!$this->link)
		{
			Throw new Exception("Error connecting to database: ".Database::mysqli_error());
		}
		
		if (!mysql_select_db($this->db))
		{
			Throw new Exception("Error selecting database: ".Database::mysqli_error());
		}
		Database::mysqli_query("SET NAMES 'utf8'");
		return;
	}

	public function close()
	{
		mysql_close($this->link);
	}
	*/
	
	/*************************** THE FOLLOWING FUNCTIONS USE THE MYSQLI EXTENSION *********************************/
	public function connect()
	{
		global $mysqli_link, $test_var;
		$mysqli_link = mysqli_connect($this->db_host, $this->user, $this->pass);
		$test_var = "Some value in test_var";
		if (!$mysqli_link)
		{
			Throw new Exception("Error connecting to database: ".Database::mysqli_error());
		}
		
		if (!mysqli_select_db($mysqli_link, $this->db))
		{
			Throw new Exception("Error selecting database: ".Database::mysqli_error());
		}
		mysqli_query($mysqli_link, "SET NAMES 'utf8'");
		return;
	}

	public function close()
	{
		global $mysqli_link;
		mysqli_close($mysqli_link);
	}
	
	public static function mysqli_query($sql)
	{
		global $mysqli_link, $test_var;
		// error_log("Database::mysqli_query(): mysqli_link: " . var_export($mysqli_link, true));
		// error_log("Database::mysqli_query(): test_var: " . var_export($test_var, true));
		if(empty($mysqli_link))
			error_log("Empty mysqli_link in Database::mysqli_query(): SQL: " . $sql);
		
		$limit_seconds = 5;
		$start_time = time();
		
		$rs = mysqli_query($mysqli_link, $sql);
		
		$time_taken = time() - $start_time;
		if($time_taken > $limit_seconds)
		{
			$msg = "The following SQL took $time_taken seconds to execute: " . $sql;
			// error_log($msg);
			Common::log_error(__FILE__, __LINE__, "SQL Query Execution Time", $msg);
		}
		
		if(!$rs)
			error_log("SQL error: " . mysqli_error($mysqli_link) . "\nSQL: " . $sql);
		return $rs;
	}
	
	public static function mysqli_fetch_assoc($rs)
	{
		if($rs)
			return mysqli_fetch_assoc($rs);
		return null;
	}
	
	public static function mysqli_fetch_array($rs)
	{
		if($rs)
			return mysqli_fetch_array($rs);
		return null;
	}
	
	public static function mysqli_real_escape_string($str)
	{
		global $mysqli_link, $test_var;
		// error_log("Database::mysqli_real_escape_string(): mysqli_link: " . var_export($mysqli_link, true));
		// error_log("Database::mysqli_real_escape_string(): test_var: " . var_export($test_var, true));
		if(empty($mysqli_link))
			error_log("Empty mysqli_link in Database::mysqli_real_escape_string(): str: " . $str);
		return mysqli_real_escape_string($mysqli_link, $str);
	}
	
	public static function mysqli_affected_rows()
	{
		global $mysqli_link;
		return mysqli_affected_rows($mysqli_link);
	}
	
	
	public static function mysqli_errno()
	{
		global $mysqli_link;
		return mysqli_errno($mysqli_link);
	}
	
	public static function mysqli_error()
	{
		global $mysqli_link;
		if(empty($mysqli_link))
			error_log("Empty mysqli_link in Database::mysqli_error(): error: " . mysqli_error());
		return mysqli_error($mysqli_link);
	}
	
	public static function mysqli_insert_id()
	{
		global $mysqli_link, $test_var;
		return mysqli_insert_id($mysqli_link);
	}
	
	public static function mysqli_num_rows($rs)
	{
		return mysqli_num_rows($rs);
	}
	
	
	public static function mysqli_data_seek($rs, $index)
	{
		return mysqli_data_seek($rs, $index);
	}
	
	public static function mysqli_free_result($rs)
	{
		if($rs && !is_bool($rs))
			mysqli_free_result($rs);
	}
	
	////////////////////////////////////////////////////////////
	/////////////////	DATABASE TRANSACTION FUNCTIONS
	////////////////////////////////////////////////////////
	public static function mysqli_begin_transaction()
	{
			global $mysqli_link;
			// return mysqli_begin_transaction($mysqli_link);
			return Database::mysqli_query("start transaction");
	}
	
	public static function mysqli_commit()
	{
		global $mysqli_link;
		return mysqli_commit($mysqli_link);
	}
	
	public static function mysqli_rollback()
	{
		global $mysqli_link;
		return mysqli_rollback($mysqli_link);
	}
	
	public static function mysqli_autocommit()
	{
		global $mysqli_link;
		return mysqli_autocommit($mysqli_link);
	}
	
	public static function start_transaction()
	{
		
	}
}
?>
