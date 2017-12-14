<?php
/**
* Session Handler
*/
class Session
{
	
	var $table_name;
	var $session_length;
	
	
	function __construct()
	{
		$this->table_name = 'sessions';
		$this->session_length = 3600 * 60;
		
		/*
		// set this classes methods for the session handler
		session_set_save_handler( 
		  	array( &$this, "open" ), 
			array( &$this, "close" ),
			array( &$this, "read" ),
			array( &$this, "write"),
			array( &$this, "destroy"),
			array( &$this, "gc" )
		);
		*/
	}
	
	function open( $save_path, $session_name ) 
	{

        global $sess_save_path;
        $sess_save_path = $save_path;

		return true;

	 }

	function close() 
	{
		return true;
	}
	
	function read($id) 
	{
		$session_data = '';

		$time = time();

		$newid = Database::mysqli_real_escape_string($id);
		$sql = "SELECT `session_data` FROM `$this->table_name` WHERE `session_id` = '$newid' AND `expires` > $time";

		
		$result = Database::mysqli_query($sql);   
        
		if($result)
			$session_rows = Database::mysqli_num_rows($result);
		else
			$session_rows = 0;

		if($session_rows > 0) 
		{
			$row = Database::mysqli_fetch_assoc($result);
			$session_data = $row['session_data'];
		}

		return $session_data;
	}
	
	function write($id, $data) 
	{
              
		$time = time() + $this->session_length;

		$newid = Database::mysqli_real_escape_string($id);
		$newdata = Database::mysqli_real_escape_string($data);

		$sql = "REPLACE `$this->table_name` (`session_id`,`session_data`,`expires`) VALUES('$newid','$newdata', $time)";

		$rs = Database::mysqli_query($sql);

		return TRUE;

	}

	function destroy($id) 
	{

		$newid = Database::mysqli_real_escape_string($id);
		$sql = "DELETE FROM `$this->table_name` WHERE `session_id` = '$newid'";

		Database::mysqli_query($sql);

		return TRUE;

	}
	
	function gc() 
	{

		// Delete expired sessions
		$sql = 'DELETE FROM `$this->table_name` WHERE `expires` < UNIX_TIMESTAMP()';

		Database::mysqli_query($sql);

		return true;

	}
	
}

?>