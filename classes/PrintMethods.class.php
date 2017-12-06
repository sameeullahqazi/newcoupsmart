<?php
Class PrintMethods{

	//grabs the custom code for the customer associated with $campaign_id
	//the code is stored in $code
	public static function get_custom_data($campaign_id, $user_item_id, $user_fb_id){
		//error_log("inside get_custom_data");
		//first grabs the custom code associated with the user_item_id's campaign
		$query = "select custom_code
				from customer_supplied_code cs
				where cs.campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "'
				and cs.issued_status = 0
				order by RAND()
				limit 1";
		//error_log("QUERY " . $query);

		$rs = Database::mysqli_query($query);
		$code = Database::mysqli_fetch_assoc($rs);
		//error_log("code: ". var_export($code,true));
		$query = "update customer_supplied_code set user_item_id = '" . Database::mysqli_real_escape_string($user_item_id) . "', facebook_id = '" . Database::mysqli_real_escape_string($user_fb_id) . "', issued_status = 1, date_printed = NOW() where campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "' and custom_code = '" . Database::mysqli_real_escape_string($code['custom_code']) . "'";
		Database::mysqli_query($query);
		return $code['custom_code'];
	}
	
	public static function getCustomData($deal_id, $user_item_id, $user_fb_id, $csc_custom_code = null){
		
		if(!empty($csc_custom_code) && $csc_custom_code != 'customCode')
			return $csc_custom_code;
			
		//error_log("inside get_custom_data");
		//first grabs the custom code associated with the user_item_id's campaign
		$query = "select custom_code
				from customer_supplied_code cs
				where cs.deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "'
				and cs.issued_status = 0
				order by RAND()
				limit 1";
		//error_log("QUERY " . $query);

		$rs = Database::mysqli_query($query);
		$code = Database::mysqli_fetch_assoc($rs);
		//error_log("code: ". var_export($code,true));
		$query = "update customer_supplied_code set user_item_id = '" . Database::mysqli_real_escape_string($user_item_id) . "', facebook_id = '" . Database::mysqli_real_escape_string($user_fb_id) . "', issued_status = 1, date_printed = NOW() where deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "' and custom_code = '" . Database::mysqli_real_escape_string($code['custom_code']) . "'";
		Database::mysqli_query($query);
		return $code['custom_code'];
	}

	public static function end_campaign($campaign_id){
		$query = "UPDATE `items` SET `status`='finished' WHERE (`campaign_id`= '".Database::mysqli_real_escape_string($campaign_id)."');"; //change status to finish
		//error_log("Query: " . $query);
		Database::mysqli_query($query); //update db
		
		$query = "UPDATE `campaigns` SET `status`='finished' WHERE (`id`= '".Database::mysqli_real_escape_string($campaign_id)."');"; //change status to finish
		//error_log("Query: " . $query);
		Database::mysqli_query($query); //update db
	}

	public static function update_issued_status($customCode){
		$query = "update customer_supplied_code set issued_status = 1 where custom_code = '".Database::mysqli_real_escape_string($customCode)."';";
		//error_log("QUERY: " . $query);
		$rs = Database::mysqli_query($query);

		if ($rs){
			//error_log("Update was a success");
		}
		else{
			//error_log("Error in issuing the code!");
		}
	}


	public static function update_user_redemption($user, $customCode){
		$query = "update customer_supplied_code set user_item_id = ".Database::mysqli_real_escape_string($user)." where custom_code = '".Database::mysqli_real_escape_string($customCode)."';";
		$rs = Database::mysqli_query($query);
		//error_log("redemption: " . $query);
		if ($rs){
			//error_log("Update was a success");
		}
		else{
			//error_log("Error in issuing the code!");
		}
	}

	public static function check_user($user, $campaign_id)
	{
		$query = "select count(*) as num_rows
				from customer_supplied_code
				where facebook_id = '" . Database::mysqli_real_escape_string($user) . "'
				and campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "'";
		// error_log("SQL in PrintMethods::check_user(): " . $query);
		$rs = Database::mysqli_query($query);
		if(!$rs)
			error_log("SQL error in PrintMethods::check_user(): ".Database::mysqli_error(). "\nSQL: ".$query);
		$count = Database::mysqli_fetch_assoc($rs);
		//error_log("count(*)" . $count['count(*)']);
		
		if ($count['num_rows'] < 1){
			error_log("hit hit:");
			return true;
		}
		//error_log("hit again");
		return false;
	}
	
	public static function checkUser($user, $deal_id, $csc_custom_code = null, $user_id = null, $item_id = null)
	{
		if(!empty($csc_custom_code) && $csc_custom_code != 'customCode')
		{
			$sql = "select '$csc_custom_code' as custom_code, id as user_item_id from user_items where item_id = '$item_id' and user_id = '$user_id'";
			return BasicDataObject::getDataRow($sql);
		}

		$query = "select *
				from customer_supplied_code
				where facebook_id = '" . Database::mysqli_real_escape_string($user) . "'
				and deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "'";
		// error_log("SQL in PrintMethods::checkUser(): " . $query);
		return BasicDataObject::getDataRow($query);
	}

	public static function count_remaining_codes($campaign_id){
		$query = "select count(*) as num_rows
				from customer_supplied_code
				where campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "'
				and issued_status = 0";
		$rs = Database::mysqli_query($query); //how many are left
		if(!$rs)
			error_log("SQL error in PrintMethods::count_remaining_codes(): ".Database::mysqli_error(). "\nSQL: ".$query);
		$count = Database::mysqli_fetch_assoc($rs); //return the number of codes left
		$realCount = $count['num_rows'];

		return $realCount;
	}
	
	public static function countRemainingCodes($deal_id){
		$query = "select count(*) as num_rows
				from customer_supplied_code
				where deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "'
				and issued_status = 0";
		$rs = Database::mysqli_query($query); //how many are left
		if(!$rs)
			error_log("SQL error in PrintMethods::count_remaining_codes(): ".Database::mysqli_error(). "\nSQL: ".$query);
		$count = Database::mysqli_fetch_assoc($rs); //return the number of codes left
		$realCount = $count['num_rows'];

		return $realCount;
	}

	public static function gather_codes($campaign_id){
		$query = "select id, custom_code, issued_status, campaign_id, user_item_id, date_printed from customer_supplied_code
					where campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "'
					and issued_status = 1
					order by date_printed DESC";
		$rs = Database::mysqli_query($query);
		$results = array();
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while ($row = Database::mysqli_fetch_assoc($rs)) {
				$results[] = $row;
			}
		}
		return $results;
	}
	
	public static function gatherCodes($deal_id){
		$query = "select id, custom_code, issued_status, campaign_id, deal_id, user_item_id, date_printed from customer_supplied_code
					where deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "'
					and issued_status = 1
					order by date_printed DESC";
		$rs = Database::mysqli_query($query);
		$results = array();
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while ($row = Database::mysqli_fetch_assoc($rs)) {
				$results[] = $row;
			}
		}
		return $results;
	}

	public static function get_user_code($user, $campaign_id){
		$query = "select customer_supplied_code.custom_code
				from customer_supplied_code
				where campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "'
				and user_item_id = '" . Database::mysqli_real_escape_string($user) . "'";
		//error_log($query);
		$rs = Database::mysqli_query($query);
		$code = Database::mysqli_fetch_assoc($rs);
		//error_log("code: " . var_export($code, true));
		return $code['custom_code'];
	}
	
	public static function getUserCode($user, $deal_id){
		$query = "select customer_supplied_code.custom_code
				from customer_supplied_code
				where deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "'
				and user_item_id = '" . Database::mysqli_real_escape_string($user) . "'";
		//error_log($query);
		$rs = Database::mysqli_query($query);
		$code = Database::mysqli_fetch_assoc($rs);
		//error_log("code: " . var_export($code, true));
		return $code['custom_code'];
	}

	public static function get_issued_date($user){
		$query = "select customer_supplied_code.date_printed
					from customer_supplied_code
					where user_item_id = '".Database::mysqli_real_escape_string($user)."'";
		$rs = Database::mysqli_query($query);
		$date = Database::mysqli_fetch_assoc($rs);
		//error_log("date: " . var_export($date, true));
		return $date['date_printed'];
	}

	public static function get_coup_id($id){
		$query = "select users.id
					from users
					where users.facebook_id = '".Database::mysqli_real_escape_string($id)."'";
		//error_log("QUERY GET COUP ID: " . $query);
		$rs = Database::mysqli_query($query);
		$id = Database::mysqli_fetch_assoc($rs);
		//error_log("ID: " . var_export($id, true));
		return $id['id'];
	}

	public static function get_user_item_id($user_id, $item_id){
		$query = "select user_items.id
					from user_items
					where user_id = ".Database::mysqli_real_escape_string($user_id)."
					and item_id = ".Database::mysqli_real_escape_string($item_id)."
					order by id desc
					limit 1";
		//error_log("query: " . $query);
		$rs = Database::mysqli_query($query);
		$id = Database::mysqli_fetch_assoc($rs);
		// error_log("id: " . $id);
		return $id['id'];
	}

	public static function update_facebook_id($user, $customCode, $campaign_id){
		$query = "update customer_supplied_code set facebook_id = ".Database::mysqli_real_escape_string($user)." where campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "' and custom_code = '".Database::mysqli_real_escape_string($customCode)."'";
		Database::mysqli_query($query);
		
	}
	
	public static function updateFacebookId($user, $customCode, $deal_id){
		$query = "update customer_supplied_code set facebook_id = ".Database::mysqli_real_escape_string($user)." where deal_id = '" . Database::mysqli_real_escape_string($deal_id) . "' and custom_code = '".Database::mysqli_real_escape_string($customCode)."'";
		Database::mysqli_query($query);
		
	}
}
?>