<?php

	class ClickReferral extends BasicDataObject
	{
		var $user_id;
		var $tracking_info;
		var $company_id;
		var $item_id;
		var $action;
		var $session_id;

		var $ip;
		var $user_agent;
		var $created;
		
		var $date_viewed;
		var $date_button_clicked;
		var $date_permission_requested;
		var $date_permission_granted;
		var $date_claimed;
		var $date_redeemed;
		
		function __construct($id = null, $read_only_mode = true){

			if(!empty($id))

			{

				$id = "id='" . $id . "'";

				$this->Select($id, $read_only_mode);

			}

		}
		
		public static function insert_click_referral($user_id, $tracking_info, $company_id, $action='referral')
		{
			$user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
			$ip = Common::GetUserIp();
			$session_id = session_id();
			
			$click_referral = new ClickReferral();
			
			if(!empty($user_id))
				$click_referral->user_id 		= $user_id;
				
			$click_referral->tracking_info= $tracking_info;
			$click_referral->company_id	= $company_id;	
			$click_referral->user_agent 	= $user_agent;
			$click_referral->ip 				= $ip;
			$click_referral->session_id 	= $session_id;
			
			$click_referral->Insert();

			return $click_referral->id;
		}

		public static function update_click_referral($click_referral_id, $user_id, $item_id, $action)
		{
			
			$click_referral = new ClickReferral();
			$click_referral->id = $click_referral_id;
			$click_referral->user_id = $user_id;
			$click_referral->item_id = $item_id;
			$click_referral->action = $action;

			$click_referral->Update();
			
			
		}
		
			
		public static function getClickReferralsReport($company_id)
		{
			$company_id = Database::mysqli_real_escape_string($company_id);
		
			// Get User Log Info
			$user_log_info = array();
			$sql = "select ual.user_id, ual.fullname, ual.age, ual.location, ual.relationship, group_concat(ual.activity separator ',') as activity, ual.interests, 
			sum(ual.is_claimed) as num_claimed, sum(ual.is_redeemed) as num_redeemed
			from user_activity_log ual
			where ual.company_id = '".Database::mysqli_real_escape_string($company_id)."'
			group by ual.user_id";
			// error_log("sql for user log info: ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$user_log_info[$row['user_id']] = $row;
				}
			}
			else
			{
				error_log("SQL error in ClickReferral::getClickReferralsReport(): ".$Database::mysqli_error . "\nSQL: ".$sql);
			}
			
			
			$sql = "select cr.* from click_referrals cr where cr.company_id = '".Database::mysqli_real_escape_string($company_id)."'";
			
			// error_log("SQL in ClickReferral::getClickReferralsReport(): ".$sql);
			
			$click_referrals = BasicDataObject::getDataTable($sql);
			foreach($click_referrals as $i => $row)
			{
				$tracking_info = $row['tracking_info'];
				
				$c0 = $c1 = $c2 = '';
				if(!empty($tracking_info))
				{
					$arr_tracking_info = array();
					parse_str($tracking_info, $arr_tracking_info);
					
					$c0 = isset($arr_tracking_info['c0']) ? (string)$arr_tracking_info['c0'] : '';
					$c1 = isset($arr_tracking_info['c1']) ? (string)$arr_tracking_info['c1'] : '';
					$c2 = isset($arr_tracking_info['c2']) ? (string)$arr_tracking_info['c2'] : '';
				}
								
				$row['c0'] = $c0;
				$row['c1'] = $c1;
				$row['c2'] = $c2;
				
				// Storing user info
				$user_id = $row['user_id'];				
				if(isset($user_log_info[$user_id]))
				{
					$row['fullname']		= $user_log_info[$user_id]['fullname'];
					$row['age']				= $user_log_info[$user_id]['age'];
					$row['location']		= $user_log_info[$user_id]['location'];
					$row['relationship']	= $user_log_info[$user_id]['relationship'];
					$row['activity']		= $user_log_info[$user_id]['activity'];
					$row['interests']		= $user_log_info[$user_id]['interests'];
					$row['num_claimed']	= $user_log_info[$user_id]['num_claimed'];
					$row['num_redeemed'] = $user_log_info[$user_id]['num_redeemed'];
				}
				
				$click_referrals[$i] = $row;
			}
			return $click_referrals;
		}
		
		public static function getClickReferralsData($company_id)
		{
			$click_referrals_data = array();
			
			$sql = "select count(id) as num_click_referrals, date(created) as date 
					from click_referrals 
					where company_id = '".Database::mysqli_real_escape_string($company_id)."'
					group by date(created)";
			// error_log("sql in ClickReferral::getClickReferralsData(): ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$date						= strtotime($row['date'] . ' UTC');
					$num_click_referrals	= $row['num_click_referrals'];
					
					$click_referrals_data[$date] = $num_click_referrals;
				}
			}
			return $click_referrals_data;
		}
		
		public static function getItemViewsData($company_id)
		{
			$item_views_data = array();
			$sql = "select count(iv.id) as num_item_views, date(created) as date
						from items_views iv
						where iv.company_id = '".Database::mysqli_real_escape_string($company_id)."'
						and created >= (select min(created) from click_referrals)
						and is_click_referral = '1'
						group by date(created)";
						
			// error_log("sql in ClickReferral::getItemViewsData(): ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$date					= strtotime($row['date'] . ' UTC');
					$num_item_views	= $row['num_item_views'];
					
					$item_views_data[$date] = $num_item_views;
				}
			}
			return $item_views_data;
		}
		
		public static function getButtonClickedData($company_id)
		{
			$button_clicked_data = array();
			$sql = "select count(ua.id) as num_button_clicked, date(ua.created) as date
						from user_action_process_log ua
						inner join items i on ua.item_id = i.id
						where i.manufacturer_id = '".Database::mysqli_real_escape_string($company_id)."'
						and is_click_referral = '1'
						group by date(ua.created)";
						
			// error_log("sql in ClickReferral::getButtonClickedData(): ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$date					= strtotime($row['date'] . ' UTC');
					$num_button_clicked	= $row['num_button_clicked'];
					
					$button_clicked_data[$date] = $num_button_clicked;
				}
			}
			return $button_clicked_data;
		}
		
		public static function getPermissionsRequestData($company_id)
		{
			$permissions_requests_data = array();
			$sql = "select count(ua.id) as num_permissions_requests, date(ua.created) as date
						from user_action_process_log ua
						inner join items i on ua.item_id = i.id
						where i.manufacturer_id = '".Database::mysqli_real_escape_string($company_id)."'
						and ua.permission_granted is not null
						and is_click_referral = '1'
						group by date(ua.created)";
						
			// error_log("sql in ClickReferral::getPermissionsRequestData(): ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$date					= strtotime($row['date'] . ' UTC');
					$num_permissions_requests	= $row['num_permissions_requests'];
					
					$permissions_requests_data[$date] = $num_permissions_requests;
				}
			}
			return $permissions_requests_data;
		}
		
		public static function getPermissionsGrantedData($company_id)
		{
			$permissions_granted_data = array();
			$sql = "select count(ua.id) as num_permissions_granted, date(ua.created) as date
						from user_action_process_log ua
						inner join items i on ua.item_id = i.id
						where i.manufacturer_id = '".Database::mysqli_real_escape_string($company_id)."'
						and ua.permission_granted = '1'
						and is_click_referral = '1'
						group by date(ua.created)";
						
			// error_log("sql in ClickReferral::getPermissionsRequestData(): ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$date								= strtotime($row['date'] . ' UTC');
					$num_permissions_granted	= $row['num_permissions_granted'];
					
					$permissions_granted_data[$date] = $num_permissions_granted;
				}
			}
			return $permissions_granted_data;
		}
		
		public static function getItemClaimsData($company_id)
		{
			$item_claims_data = array();
			
			$sql = "select count(ui.id) as num_item_claims, date(date_claimed) as date
						from user_items ui
						inner join items i on ui.item_id = i.id
						where i.manufacturer_id = '".Database::mysqli_real_escape_string($company_id)."'
						and ui.is_click_referral = '1'
						group by date(date_claimed)";
						
			// error_log("sql in ClickReferral::getItemClaimsData(): ".$sql);
			
			$rs = Database::mysqli_query($sql);
			if($rs)
			{
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$date					= strtotime($row['date'] . ' UTC');
					$num_item_claims	= $row['num_item_claims'];
					
					$item_claims_data[$date] = $num_item_claims;
				}
			}
			return $item_claims_data;
		}
		
		// Returns the id of any click_referral that is associated with the user
		public static function click_referral_exists($session_id = null)
		{			
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$ip 			= Common::GetUserIp();
			
			if(empty($session_id))
				$session_id = session_id();
		
		
			if(isset($_COOKIE['tracking_info']) && !empty($_COOKIE['tracking_info']))
			{
				$tracking_info_cookie = $_COOKIE['tracking_info'];
				$arr_tracking_info_cookie = array();
				parse_str($tracking_info_cookie, $arr_tracking_info_cookie);
				$click_referral_id = $arr_tracking_info_cookie['click_referral_id'];
				
				$click_referral = new ClickReferral($click_referral_id);
				
				
				
				// Matching the session ID, browser and IP
				if($user_agent == $click_referral->user_agent && $ip == $click_referral->ip 
					&& $session_id == $click_referral->session_id
				)
				{
					$tracking_info_click_referral = $click_referral->tracking_info;
					$arr_tracking_info_click_referral = array();
					parse_str($tracking_info_click_referral, $arr_tracking_info_click_referral);
					
					// Matching the tracking info
					foreach($arr_tracking_info_click_referral as $key => $val)
					{
						if(isset($arr_tracking_info_cookie[$key]) && $arr_tracking_info_cookie[$key] == $val)
						{}
						else
						{ return 0; }
					}
					return $click_referral_id;
				}
			}
			return 0;
		}
		
	}

?>