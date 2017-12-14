<?php
class Resubscribe{
	public static function getUserCampaigns($user_id, $company_id){
		
		$query = "select  DISTINCT(items.manufacturer_id)
					from items
					inner join user_items 
					on items.id= user_items.item_id
					where user_items.user_id = ".$user_id."";
		//error_log("query " . $query);
		$rs = Database::mysqli_query($query);
		$companies_ids = Array();
		while($row = Database::mysqli_fetch_assoc($rs)){
			$companies_ids[]=$row['manufacturer_id'];
		}
		//error_log("companies you have views" . var_export($companies_ids,true));
		$companies = Array();
		foreach($companies_ids as $company_id=>$val){
			$subscribed_status = "<span style='color:#e52121;'>Emails Blocked</span>";
			//error_log(var_export($company_id,true));
			$query = "select count(*)
				from user_company_unsubscribe
				where user_id = ".$user_id."
				and company_id = ".$val.";";
			//error_log("query: " . $query);
			$rs = Database::mysqli_query($query);
			$count = Database::mysqli_fetch_assoc($rs);
			//error_log("count " . $count['count(*)']);
			//error_log("num rows" . Database::mysqli_num_rows($rs));

			$query2 = "select display_name from companies where id  =".$val.";";
			$rs2 = Database::mysqli_query($query2);
			$name = Database::mysqli_fetch_assoc($rs2);
			//error_log("name" . $name['display_name']);
			if($rs && $count['count(*)'] == 0){
				//error_log("The count was greater than zero");
				$subscribed_status = "Subscribed";
			}
			$companies[$name['display_name']] = $subscribed_status;
		}
		//error_log("companies" . var_export($companies,true));
		return array($companies,$companies_ids);
	}

	public static function resubscribeUser($user_id, $company_id){
		$query = "delete 
			from user_company_unsubscribe
			where user_id = ".$user_id."
			and company_id = ".$company_id.";";
		//error_log("query " . $query);
		$rs = Database::mysqli_query($query);
	}
	
	// This function returns true if the user is unsubscribed
	public static function isUnsubscribed($company_id, $email){
		//error_log("that " . var_export($that,true));
		$unsubscribed = false;
		$query = "select ucc.id
			from user_company_unsubscribe ucc
			left join users u on u.id = ucc.user_id
			where ucc.company_id = '" . Database::mysqli_real_escape_string($company_id) . "'
			and u.email = '" . Database::mysqli_real_escape_string($email) . "'";
		//error_log("query" . $query);
		$rs = Database::mysqli_query($query);
		if($rs && Database::mysqli_num_rows($rs)>0){
			$unsubscribed = true;
		}
		return $unsubscribed;
	}
}
?>