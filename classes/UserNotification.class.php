<?php

require_once(dirname(__DIR__) . '/includes/app_config.php');

/*
* User Notification Class
* These are notifications for different apps that are sent out when the app becomes
* available for use.
*
* Author: Frank Bertsch
* May 23, 2013
*/

class UserNotification extends BasicDataObject
{
	var $id;
	var $user_id;
	var $fb_id;
	var $company_id;
	
	var $notify;
	var $date_notification_sent;
	var $active;
	var $app_index;
	
	var $access_token;
	var $permissions;
	var $expires_on;
	
	var $created;
	var $modified;
	
	
	//THIS WILL BE REMOVED FOR SMART EMAILS
	PUBLIC STATIC $MESSAGE = "The deals are on! Visit our page to get a sweet deal.";

	function __construct($id = null)
	{
		if(!empty($id))
		{
			$id = Database::mysqli_real_escape_string($id);
			$this->Select("id='".$id."'");
		}
		return $this;
	}
	
	public static function getAppIndex($app_name)
	{
		global $apps_info;
		$info = $apps_info[$app_name];
		return $info['index'];
	}
	
	public function is_notified()
	{
		if($this->notify == '1'){
			return true;
		} else {
			return false;
		}
	}
	
	/*
	REMOVED
	a notification becomes inactive after any deals for that company become live. this
	happens whether or not the user is notified. The user will only have one
	active notification with notify set to '1' at a time.
	public static function getActiveNotificationsByFBIDAndCompanyId($fb_id, $cid, $app_index)
	{
		$sql = "
			select * from users_notifications
			where fb_id = '" . Database::mysqli_real_escape_string($fb_id) . "'
			and company_id = '" . Database::mysqli_real_escape_string($cid) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			and active = 1;
		";
		
		
		$table = BasicDataObject::getDataTable($sql);
		$active = array();
		foreach($table as $row){
			$active[] = UserNotification::fillUserNotification($row);
		}
		return $active;
	}
	*/
	
	/*
		There will only be one active notification at a time.
	*/
	public static function getActiveNotificationByFBIDAndCompanyId($fb_id, $cid, $app_index)
	{
		$sql = "
			select * from users_notifications
			where fb_id = '" . Database::mysqli_real_escape_string($fb_id) . "'
			and company_id = '" . Database::mysqli_real_escape_string($cid) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			and active = 1;
		";
		
		
		$row = BasicDataObject::getDataRow($sql);
		$active = null;
		if(!empty($row['id'])){
			$active = UserNotification::fillUserNotification($row);
		}
		return $active;
	}
	
	public static function turnOffNotification($fb_id, $cid, $app_index)
	{
		$sql = "
			update users_notifications
			set notify = 0, modified = NOW()
			where fb_id = '" . Database::mysqli_real_escape_string($fb_id) . "'
			and company_id = '" . Database::mysqli_real_escape_string($cid) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			and active = 1
			and notify = 1;
		";
		
		//error_log($sql);
		
		if(!Database::mysqli_query($sql)){
			error_log("Error turning off deals notification at line " . __LINE__ . " in " . __FILE__ . ": ".Database::mysqli_error() . '\n' . 'SQL: ' . $sql);
		}
	}
	
	public static function turnOnNotification($user_id, $fb_id, $cid, $access_token, $permissions, $expires, $app_index)
	{
		$active = UserNotification::getActiveNotificationByFBIDAndCompanyId($fb_id, $cid, $app_index);
		//error_log(var_export($active, true));
		if(!empty($active)){
		
			$active->notify = "1";
			$active->modified = "NOW()";
			$active->Update();
			
		} else {
		
			$sql = "insert into `users_notifications` (user_id, fb_id, company_id, notify, app_index, access_token, permissions, expires_on, active)
				values ( '" . Database::mysqli_real_escape_string($user_id) . "',
						 '" . Database::mysqli_real_escape_string($fb_id) . "',
						 '" . Database::mysqli_real_escape_string($cid) . "',
						 1, 
						 '" . Database::mysqli_real_escape_string($app_index) . "', 
						 '" . Database::mysqli_real_escape_string($access_token) . "', 
						 '" . Database::mysqli_real_escape_string($permissions) . "', 
						 '" . Database::mysqli_real_escape_string($expires) . "', 
						 1);";
			
			//error_log($sql);
		
			if(!Database::mysqli_query($sql)){
				error_log("Error turning on notification at line " . __LINE__ . " in " . __FILE__ . ": ".Database::mysqli_error() . '\n' . 'SQL: ' . $sql);
			}
			
		}
	}
	
	public static function fillUserNotification($user_deal_notification_arr)
	{
		$notification = new UserNotification();
		
		foreach($user_deal_notification_arr as $column => $val){
			$notification->$column = $val;
		}
		
		return $notification;
	}
	
	public static function getAllNotifications($fb_id, $cid, $app_index)
	{
		$sql = "
			select * from users_notifications
			where fb_id = '" . Database::mysqli_real_escape_string($fb_id) . "'
			and company_id = '" . Database::mysqli_real_escape_string($cid) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "';
		";
		
		
		$table = BasicDataObject::getDataTable($sql);
		$notifications = array();
		foreach($table as $row){
			$notifications[] = UserNotification::fillUserNotification($row);
		}
		return $active;
	}
	
	public static function getAllTrueNotificationsBetweenDates($cid, $app_index, $only_active = false, $from_date = null, $to_date = null)
	{
		$sql = "
			select * from users_notifications
			where company_id = '" . Database::mysqli_real_escape_string($cid) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
		";
		
		if(!is_null($from_date)){
			$sql .= "and (created > '" . $from_date . "' or modified > '" . $from_date . "')
			";
		}
			
		if(!is_null($from_date)){
			$sql .= "and (created < '" . $to_date . "' or modified < '" . $to_date . "')
			";
		}
		
		if($only_active){
			$sql .= "and active = 1
			";
		}
		
		$sql .= "and notify = 1;";
		// error_log("SQL in UserNotification::getAllTrueNotificationsBetweenDates(): " . $sql);
		$table = BasicDataObject::getDataTable($sql);
		$notifications = array();
		foreach($table as $row){
			$notifications[] = UserNotification::fillUserNotification($row);
		}
		return $notifications;
	}
	
	public static function getMostRecentNotification($fb_id, $company_id, $app_index)
	{
		$sql = "
			select * from users_notifications
			where fb_id = '" . Database::mysqli_real_escape_string($fb_id) . "'
			and company_id = '" . Database::mysqli_real_escape_string($company_id) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			ORDER BY id DESC LIMIT 0, 1; 
		";
		
		$row = BasicDataObject::getDataRow($sql);
		
		$notification = null;
		if(!empty($row['id'])){
			$notification = UserNotification::fillUserNotification($row);
		}
		
		return $notification;
	}
	
	public static function getAccessInfoIfExists($fb_id, $cid, $app_index)
	{
		$recent_noti = UserNotification::getMostRecentNotification($fb_uid, $cid, $app_index);
		
		$access_token_exists = false;
		$access_token = '';
		$expire_time = '';
		$permissions = '';
		
		if($recent_noti != null){
			$expire = strtotime($recent_noti->expires_on);
			$now = time();
			if($expire > $now){
				$access_token_exists = true;
				$access_token = $recent_noti->access_token;
				$expire_time = $recent_noti->expires_on;
				$permissions = $recent_noti->permissions;
			}
		}
		
		return array(
			'access_token_exists' 	=> $access_token_exists,
			'access_token' 			=> $access_token,
			'expire_time' 			=> $expire_time,
			'permissions' 			=> $permissions,
		);
	}
	
	public static function notifyUsers($company_id, $app_index)
	{
		//send notifications
		$sql = "
			select * from users_notifications
			where company_id = '" . Database::mysqli_real_escape_string($company_id) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			and notify = 1
			and active = 1;
		";
		
		$table = BasicDataObject::getDataTable($sql);
		$notifications = array();
		
		foreach($table as $row){
			$notifications[] = UserNotification::fillUserNotification($row);
		}
		
		foreach($notifications as $notification){
			UserNotification::notifyUser($notification);
		}
		
		UserNotification::updateAsSent($company_id, $app_index);
		UserNotification::updateAsInactive($company_id, $app_index);
	}
	
	//TODO: CHANGE NOTIFICATION, CAN'T POST AS PAGE TO USER WALL/TIMELINE 
	public static function notifyUser($notification)
	{
		/*
		$company = new Company($notification->company_id);
		
		$company_fbid = $company->facebook_page_id;
		$link = Company::getCompanyFacebookPageURL($company_fbid, 'promotions');
		
		
		list($app_id, $app_secret, $facebook) = Common::CreateFacebookObject("promotions");
		$query_params =  array(
			'access_token' => 'CAAB4uhS2nRoBADROnLPlTWmVtDuZAxUrVAeaZBKNjZAM2yMXESD1UG5Iolht5NdJZBTCH9Jvik7AIQlSnui5RBPfctHnxKbIANdgqPC0hZAdng07FPvgp2lGXF8x8bJlXWLABZAA1HTZBJHFETZAce3ZA',//$company->access_token,
			'message' => UserNotification::$MESSAGE,
			'link' => $link,
			'method' => 'post',
			'description' => 'A Description',
		);
		
		
		$response = $facebook->api('/' . $notification->fb_id . '/feed', 'POST', $query_params);
		*/
		error_log("TRYING TO NOTIFY USER");
	}
	
	public static function sendFBAppNotification($recipient_fb_id, $message, $app_name)
	{
		global $app_version;
		list($app_check_id, $app_check_secret, $facebook) = Common::CreateFacebookObject($app_name);
		
		$token_url = "https://graph.facebook.com/v" . $app_version . "/oauth/access_token?" .
			 "client_id=" . $app_check_id .
			 "&client_secret=" . $app_check_secret .
			 "&grant_type=client_credentials";
		$app_access_token = file_get_contents($token_url);
		// error_log('app_access_token: ' . var_export($app_access_token, true));
	
		$apprequest_url ="https://graph.facebook.com/v" . $app_version . "/" . $recipient_fb_id . "/notifications?$app_access_token&template=" . urlencode($message) . "&method=post";		 
		$response = file_get_contents($apprequest_url);
		// error_log('response: ' . var_export($response, true));
		return json_decode($response, true);
	}
	
	public static function updateAsSent($company_id, $app_index)
	{
		//update any requiring notification as sent
		$sql = "
			update users_notifications
			set date_notification_sent = NOW()
			where company_id = '" . Database::mysqli_real_escape_string($company_id) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			and active = 1
			and notify = 1;
		";
	
		if(!Database::mysqli_query($sql)){
			error_log("Error updating users as sent at line " . __LINE__ . " in " . __FILE__ . ": ".Database::mysqli_error() . '\n' . 'SQL: ' . $sql);
		}
	}
	
	public static function updateAsInactive($company_id, $app_index)
	{
		//update all as inactive
		$sql = "
			update users_notifications
			set active = 0
			where company_id = '" . Database::mysqli_real_escape_string($company_id) . "'
			and app_index = '" . Database::mysqli_real_escape_string($app_index) . "'
			and active = 1
		";
		
		if(!Database::mysqli_query($sql)){
			error_log("Error updating users as inactive at line " . __LINE__ . " in " . __FILE__ . ": ".Database::mysqli_error() . '\n' . 'SQL: ' . $sql);
		}
	}
	
	public static function isUserSignedUpForSmartDealWebNotifications($user_id, $company_id)
	{
		$sql = "select id from smart_deals_web_notifications where user_id = '$user_id' and company_id = '$company_id' and notify = '1'";
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row['id']);
	}
}

?>