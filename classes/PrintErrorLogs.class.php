<?php
Class PrintErrorLogs{
	public static function logError($user_details, $error_type, $item_id = null){ //needs the user info from the person trying to print and the type of error

	//error_log("user_detail: " . var_export($user_details,true));
		$query = "INSERT
				INTO `user_print_error_logs`
				VALUES (NULL,".Database::mysqli_real_escape_string($user_details['id']).", '".Database::mysqli_real_escape_string($user_details['email']).
				"', NULL, "
				.Database::mysqli_real_escape_string($error_type) . ", "
				. $item_id
				.")";
		//error_log($query);
		$rs = Database::mysqli_query($query); //insert log into db
		//error_log($rs);
	}


	public static function logReprintError($user, $error_type, $item_id){ //needs the user info from the person trying to print and the type of error

	//error_log("user_detail: " . var_export($user,true));
		$query = "INSERT
				INTO `user_print_error_logs` (user_id, user_email, log_time, error_type, item_id)
				VALUES ("
				. Database::mysqli_real_escape_string($user->facebook_id) .", '"
				. Database::mysqli_real_escape_string($user->email).
				"', NULL, "
				. Database::mysqli_real_escape_string($error_type) . ", "
				. $item_id
				.")";
		//error_log($query);
		$rs = Database::mysqli_query($query); //insert log into db
		//error_log($rs);
	}

	public static function getFacebookId($item_id){
		$query = "select companies.facebook_page_id
					from items
					inner join companies 
					on items.manufacturer_id = companies.id
					where items.id = ".Database::mysqli_real_escape_string($item_id)."";
		// error_log('sql for getting facebook id: ' . $query);
		$rs = Database::mysqli_query($query);
		$company = Database::mysqli_fetch_assoc($rs);

		return $company['facebook_page_id'];
	}

	public static function getCampaignStatus($item_id){
		$campaign = false; //default that the campaign is not over
		$query="select items.`status`
				from items
				where items.id = ".Database::mysqli_real_escape_string($item_id).";";
		$rs = Database::mysqli_query($query);
		$status = Database::mysqli_fetch_assoc($rs);
		error_log("status: " . var_export($status,true));
		error_log("status: " . $status['status']);
		if($status['status'] == 'finished'){
			$campaign = true;
		}
		else{
			error_log("hit hit");
			//do nothing
		}
		return $campaign;
	}
}
?>