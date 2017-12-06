<?php


require_once(dirname(__DIR__) . '/includes/email_parsing.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');
require_once(dirname(__DIR__) . '/helpers/states.php');
require_once(dirname(__DIR__) . '/helpers/address-verify.php');
/*
require_once(dirname(__DIR__) . '/classes/BasicDataObject.class.php');
require_once(dirname(__DIR__) . '/classes/Common.class.php');
*/

/**
* User Class
*/


class User extends BasicDataObject
{
	var $facebook_id;
	var $fb_app_scoped_user_id;
	var $twitter_id;

	var $id;
	var $username;
	var $email;
	var $firstname;
	var $lastname;
	var $address1;
	var $address2;
	var $city;
	var $state;
	var $zip;
	var $phone;

	var $date_of_birth;
	var $age_group;
	var $gender;
	var $charity;
	// User defender preferences
	var $email_notify = 0;
	var $sms_notify = 0;
	var $push_notify = 0;
	var $send_replacement_items;
	
	var $facebook_location_id;
	var $facebook_location_name;
	var $relationship_status;
	var $fb_friend_count;

	var $salt;
	var $time_allotted;
	var $status;
	
	var $unsubscribed;
	var $agreed_to_terms;
	
	var $capi_key;
	var $capi_secret;
	
	var $sp_recipient_id; // Silverpop Recipient ID
	var $et_subscriber_id; // ExactTarget Subscriber ID
	
	var $fb_interests;
	var $fb_likes;
	
	var $created;
	var $modified;

	function __construct($id = null)
	{
		$this->salt = '9201340522657012';

		if(!empty($id))
		{
			$id = Database::mysqli_real_escape_string($id);
			$sql = "SELECT * FROM users WHERE `id` = '$id'";
			$result = Database::mysqli_query($sql);
			if($result)
			{
				$row = Database::mysqli_fetch_assoc($result);
				$this->id = $row['id'];
				$this->facebook_id = $row['facebook_id'];
				$this->fb_app_scoped_user_id = $row['fb_app_scoped_user_id'];
				$this->twitter_id = $row['twitter_id'];
				$this->username = $row['username'];
				$this->email = $row['email'];
				$this->firstname = $row['firstname'];
				$this->lastname = $row['lastname'];
				$this->gender = $row['gender'];
				$this->date_of_birth = !empty($row['date_of_birth']) ? $row['date_of_birth'] : null;
				$this->age_group = !empty($row['age_group']) ? $row['age_group'] : null;
				$this->address1 = $row['address1'];
				$this->address2 = $row['address2'];
				$this->city = $row['city'];
				$this->state = $row['state'];
				$this->zip = $row['zip'];
				$this->phone = $row['phone'];
				$this->address_lat = $row['address_lat'];
				$this->address_lon = $row['address_lon'];
				$this->address_last_verified = $row['address_last_verified'];
				$this->delivery_status = $row['delivery_status'];
				$this->created = $row['created'];
				$this->modified = $row['modified'];
				$this->email_notify = $row['email_notify'];
				$this->sms_notify = $row['sms_notify'];
				$this->push_notify = $row['push_notify'];
				$this->send_replacement_items = !empty($row['send_replacement_items']) ? $row['send_replacement_items'] : null;
				$this->time_allotted = $row['time_allotted'];
				$this->facebook_location_id = $row['facebook_location_id'];
				$this->facebook_location_name = $row['facebook_location_name'];
				$this->relationship_status = $row['relationship_status'];
				$this->fb_friend_count = $row['fb_friend_count'];
				$this->status = $row['status'];
				$this->unsubscribed = $row['unsubscribed'];
				$this->agreed_to_terms = $row['agreed_to_terms'];
				
				$this->capi_key = $row['capi_key'];
				$this->capi_secret = $row['capi_secret'];
				
				$this->sp_recipient_id = $row['sp_recipient_id'];
				$this->et_subscriber_id = $row['et_subscriber_id'];
				
				$this->created = $row['created'];
				$this->modified = $row['modified'];
				}
		}
	}
	
	function construct_i($id = null)
	{
		$this->salt = '9201340522657012';

		if(!empty($id))
		{
			$id = Database::mysqli_real_escape_string($id);
			$sql = "SELECT * FROM users WHERE `id` = '$id'";
			$result = Database::mysqli_query($sql);
			if($result)
			{
				$row = Database::mysqli_fetch_assoc($result);
				$this->id = $row['id'];
				$this->facebook_id = $row['facebook_id'];
				$this->fb_app_scoped_user_id = $row['fb_app_scoped_user_id'];
				$this->twitter_id = $row['twitter_id'];
				$this->username = $row['username'];
				$this->email = $row['email'];
				$this->firstname = $row['firstname'];
				$this->lastname = $row['lastname'];
				$this->gender = $row['gender'];
				$this->date_of_birth = !empty($row['date_of_birth']) ? $row['date_of_birth'] : null;
				$this->age_group = !empty($row['age_group']) ? $row['age_group'] : null;
				$this->address1 = $row['address1'];
				$this->address2 = $row['address2'];
				$this->city = $row['city'];
				$this->state = $row['state'];
				$this->zip = $row['zip'];
				$this->phone = $row['phone'];
				$this->address_lat = $row['address_lat'];
				$this->address_lon = $row['address_lon'];
				$this->address_last_verified = $row['address_last_verified'];
				$this->delivery_status = $row['delivery_status'];
				$this->created = $row['created'];
				$this->modified = $row['modified'];
				$this->email_notify = $row['email_notify'];
				$this->sms_notify = $row['sms_notify'];
				$this->push_notify = $row['push_notify'];
				$this->send_replacement_items = !empty($row['send_replacement_items']) ? $row['send_replacement_items'] : null;
				$this->time_allotted = $row['time_allotted'];
				$this->facebook_location_id = $row['facebook_location_id'];
				$this->facebook_location_name = $row['facebook_location_name'];
				$this->relationship_status = $row['relationship_status'];
				$this->fb_friend_count = $row['fb_friend_count'];
				$this->status = $row['status'];
				$this->unsubscribed = $row['unsubscribed'];
				$this->agreed_to_terms = $row['agreed_to_terms'];
				
				$this->capi_key = $row['capi_key'];
				$this->capi_secret = $row['capi_secret'];
				
				$this->created = $row['created'];
				$this->modified = $row['modified'];
				}
		}
	}
		
	public static function username_exists($username)
	{
		$username = Database::mysqli_real_escape_string($username);

		$sql = "SELECT username FROM users WHERE `username` = '$username'";
		$result = Database::mysqli_query($sql);

		if($result)
		{
			if(Database::mysqli_num_rows($result) > 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}

	}
	
	
	public static function findByUsername($username)
	{
		$sql = "select id from users where username = '" . Database::mysqli_real_escape_string($username) . "' AND username IS NOT NULL and username != ''";
		$result = Database::mysqli_query($sql);
		if($result)
		{
			$row = Database::mysqli_fetch_assoc($result);
			$user = new User($row['id']);
			return $user;
		}
		else
			return false;

	}
	
	public static function findByFacebookId($fb_id)
	{
		$user = false;
		if (is_numeric($fb_id) && ($fb_id > 0)) {
			$fb_id = Database::mysqli_real_escape_string($fb_id);
			$sql = "select id from users where facebook_id = '$fb_id'";
			$result = Database::mysqli_query($sql);
			if($result && Database::mysqli_num_rows($result) > 0)
			{
				$row = Database::mysqli_fetch_assoc($result);
				$user = new User($row['id']);
			}
		}
		return $user;
	}
	
	public static function findByEmail($email_address)
	{
		$email_address = Database::mysqli_real_escape_string($email_address);
		$sql = "select id from users where email = '$email_address'";
		//error_log("query inside the user class "  . var_export($sql,true)	);
		$result = Database::mysqli_query($sql);
		if($result && Database::mysqli_num_rows($result) > 0)
		{
			$row = Database::mysqli_fetch_assoc($result);
			//error_log("row " . var_export($row,true));
			$user = new User($row['id']);
			return $user;
		}
		else
			return false;

	}

	
	public function get_group()
	{
		$query = "SELECT group_id FROM user_groups WHERE user_id = '".$this->id."'";
		$result = Database::mysqli_query($query);
		//error_log("sql in User:get_group: ".$query);
		
		if (!$result)
		{
			error_log("mysql error: " . Database::mysqli_error());
			Errors::show500();
		}
		else
		{
			if (Database::mysqli_num_rows($result) > 1) {
				$groups = array();
				while($row = Database::mysqli_fetch_assoc($result))
				{
					$groups[] = $row['group_id'];
				}
				return $groups;
			} elseif (Database::mysqli_num_rows($result) == 1) {
				$row = Database::mysqli_fetch_assoc($result);
				return $row['group_id'];
			} else {
				return false;
			}
		}
	}
	
	public static function quick_register_facebook_authenticate($facebook_user_details, $url = null)
	{
		//error_log('facebook user details: '. var_export($facebook_user_details, true));
		$errors = array();
		if(is_object($facebook_user_details)){
			//error_log("it was an object!!!!!!! " . __LINE__) ;
			$facebook_user_details = (array)$facebook_user_details;
		}
			
		$quick_email = isset($facebook_user_details['email']) ? $facebook_user_details['email'] : null;
		// error_log("facebook user details " . __LINE__ . var_export($facebook_user_details, true));
		// Email validation
		if(!empty($quick_email)){
			if (is_rfc3696_valid_email_address($quick_email)){// nada
			} else {
				$errors['quick_email'] = 'Please enter your email in a correct format';
			}//check to see if it's already in the DB
		} else {
			$errors['quick'] = 'Please enter your email address';
		}
		// error_log("errors in quick_register_facebook_authenticate() : " . var_export($errors, true));
		if(empty($errors))
		{
			//error_log("there were no errors! " . __LINE__) ;
			$counter = 0;
			// Generating username
			$username = explode('@', $quick_email);
			$username = $username[0];

			while(User::username_exists($username))
			{
				$username .= $counter;
				$counter++;
			}
			// Generating password
			$password = "";
			$characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstvwxyz";
			for($i = 0; $i < 7; $i++)
				$password .= $characters[rand(0, strlen($characters) - 1)];

			$user = new User();
			
			$md5_password = md5($password . $user->salt);



			// Check if user email exists
			// if($user = User::findbyEmail($quick_email)){}elseif($user = User::findByFacebookId($facebook_user_details['id'])){	}
			// By Samee, 2013-05-22, Find user by facebook Id first, then by email
			if($user = User::findByFacebookId($facebook_user_details['id'])){}elseif($user = User::findbyEmail($quick_email)){	}
			
			$facebook_id = $facebook_user_details['id'];
			$firstname = $facebook_user_details['first_name'];
			$lastname = $facebook_user_details['last_name'];
			$gender = !empty($facebook_user_details['gender']) && $facebook_user_details['gender'] == 'male' ? 'M' : 'F';
			
			$date_of_birth = null;
			if(isset($facebook_user_details['birthday']))
			{
				//error_log("the birthday was set! " . __LINE__) ;
				$date_of_birth = $facebook_user_details['birthday'];
				$date_of_birth = explode('/', $date_of_birth);
				$date_of_birth[2] = isset($date_of_birth[2]) ? $date_of_birth[2] : '0000';
				$date_of_birth = $date_of_birth[2] . '-'. $date_of_birth[0] . '-'.  $date_of_birth[1];
			}
			
			if(is_object($facebook_user_details)){
				//error_log("it thinks its an object! " . __LINE__) ;
				$facebook_user_details['location'] = (array)$facebook_user_details['location'];
			}
				
			
			if(is_object($facebook_user_details['location'])){
				$facebook_location_id = !empty($facebook_user_details['location']->id) ? $facebook_user_details['location']->id : null;
				$facebook_location_name = !empty($facebook_user_details['location']->id) ? $facebook_user_details['location']->name : null;
			}else{
				$facebook_location_id = !empty($facebook_user_details['location']['id']) ? $facebook_user_details['location']['id'] : null;
         		$facebook_location_name = !empty($facebook_user_details['location']['name']) ? $facebook_user_details['location']['name'] : null;
				$relationship_status = !empty($facebook_user_details['relationship_status']) ? $facebook_user_details['relationship_status'] : null;
			}
			
			$fb_friends = json_decode(json_encode($facebook_user_details['friends']), true);
			$fb_friend_count = !empty($fb_friends['summary']['total_count']) ? $fb_friends['summary']['total_count'] : (!empty($fb_friends['data']) ? count($fb_friends['data']) : 0);
			// error_log('fb_friend_count: ' . $fb_friend_count);
			// error_log("User object in quick_register_facebook_authenticate: ".var_export($user, true));
			if(!empty($user->id))
			{
				// error_log("user: " . __LINE__ . var_export($user, true));
				//Check existing record under given facebook id
				$strGetEsixtingRecordSQL = "SELECT facebook_id, firstname, lastname, gender, date_of_birth,";
				$strGetEsixtingRecordSQL.= "facebook_location_id, facebook_location_name, relationship_status, email, fb_friend_count FROM users WHERE email= '" . Database::mysqli_real_escape_string($quick_email) . "'".";";
				$result = Database::mysqli_query($strGetEsixtingRecordSQL);
				
				if (!$result)
					{
						// error_log('!!!!!!!! ----- !RESULT -------!!!!!!!');
						$message  = 'Invalid query: ' . Database::mysqli_error() . "\n";
						$message .= 'Whole query: ' . $strGetEsixtingRecordSQL;
						die($message);
					}
					
				$arrExistingRecord = array();
				while ($row = Database::mysqli_fetch_assoc($result))
					{
						//assign results to an array
						$arrExistingRecord[1]=$row['facebook_id'];
						$arrExistingRecord[2]=$row['firstname'];
						$arrExistingRecord[3]=$row['lastname'];
						$arrExistingRecord[4]=$row['gender'];
						$arrExistingRecord[5]=$row['date_of_birth'];
						$arrExistingRecord[6]=$row['facebook_location_id'];
						$arrExistingRecord[7]=$row['facebook_location_name'];
						$arrExistingRecord[8]=$row['relationship_status'];
						$arrExistingRecord[9]=$row['email'];
						$arrExistingRecord[10]=$row['fb_friend_count'];
					}
				Database::mysqli_free_result($result);
	
					
				$arrIncomingData = array();
				$arrIncomingData[1] = Database::mysqli_real_escape_string($facebook_id);
				$arrIncomingData[2] = Database::mysqli_real_escape_string($firstname);
				$arrIncomingData[3] = Database::mysqli_real_escape_string($lastname);
				$arrIncomingData[4] = Database::mysqli_real_escape_string($gender);
				$arrIncomingData[5] = Database::mysqli_real_escape_string($date_of_birth);
				$arrIncomingData[6] = Database::mysqli_real_escape_string($facebook_location_id);
				$arrIncomingData[7] = Database::mysqli_real_escape_string($facebook_location_name);
				$arrIncomingData[8] = Database::mysqli_real_escape_string($relationship_status);
				$arrIncomingData[9] = Database::mysqli_real_escape_string($quick_email);
				$arrIncomingData[10] = Database::mysqli_real_escape_string($fb_friend_count);
				
				 // error_log('!!!!!!EXISTING DATA' . var_export($arrExistingRecord, true));

				 // error_log('!!!!!!INCOMING DATA' . var_export($arrIncomingData, true));
				// error_log("count(arrExistingRecord): " . count($arrExistingRecord));
				$i = 1;
				WHILE ($i <= count($arrExistingRecord))
				{
					// error_log("arrExistingRecord[$i] != arrIncomingData[$i]: " . var_export($arrExistingRecord[$i] != $arrIncomingData[$i], true));
					// error_log("arrExistingRecord[$i] != arrIncomingData[$i]: " . var_export($arrExistingRecord[$i] !== $arrIncomingData[$i], true));
					if ($arrExistingRecord[$i] != $arrIncomingData[$i])
					    {
						//error_log('######### ARRAYS NOT EQUAL#########');
						//update 'User' table with new data
						$strUpdateSQL = "UPDATE users set facebook_id = '" .Database::mysqli_real_escape_string($facebook_id)."',";
						$strUpdateSQL .= "firstname = '".Database::mysqli_real_escape_string($firstname)."',";
						$strUpdateSQL .= "lastname = '".Database::mysqli_real_escape_string($lastname)."',";
						$strUpdateSQL .= "gender = '".Database::mysqli_real_escape_string($gender)."',";
						$strUpdateSQL .= "date_of_birth = '".Database::mysqli_real_escape_string($date_of_birth)."',";
						$strUpdateSQL .= "facebook_location_id = '".Database::mysqli_real_escape_string($facebook_location_id)."',";
						$strUpdateSQL .= "facebook_location_name = '".Database::mysqli_real_escape_string($facebook_location_name)."',";
						$strUpdateSQL .= "relationship_status = '".Database::mysqli_real_escape_string($relationship_status)."',";
						$strUpdateSQL .= "fb_friend_count = '".Database::mysqli_real_escape_string($fb_friend_count)."',";
						$strUpdateSQL .= "modified = now()";
						$strUpdateSQL .= " WHERE email = '" .Database::mysqli_real_escape_string($quick_email)."';";
						
						
						/* following block no longer used as the function in question is now performed by SQL trigger(s)
						  (note: trigger(s) activate on update/insert and audit changes from 'Users' table into 'Users_history')
						  
						$strUserHistorySQL = "INSERT INTO users_history SET facebook_id = '".Database::mysqli_real_escape_string($arrExistingRecord[1])."',";
						$strUserHistorySQL .= "firstname = '".Database::mysqli_real_escape_string($arrExistingRecord[2])."',";
						$strUserHistorySQL .= "lastname = '".Database::mysqli_real_escape_string($arrExistingRecord[3])."',";
						$strUserHistorySQL .= "gender = '".Database::mysqli_real_escape_string($arrExistingRecord[4])."',";
						$strUserHistorySQL .= "date_of_birth = '".Database::mysqli_real_escape_string($arrExistingRecord[5])."',";
						$strUserHistorySQL .= "facebook_location_id = '".Database::mysqli_real_escape_string($arrExistingRecord[6])."',";
						$strUserHistorySQL .= "facebook_location_name = '".Database::mysqli_real_escape_string($arrExistingRecord[7])."',";
						$strUserHistorySQL .= "relationship_status = '".Database::mysqli_real_escape_string($arrExistingRecord[8])."',";
						$strUserHistorySQL .= "email = '".Database::mysqli_real_escape_string($arrExistingRecord[9])."',";
						$strUserHistorySQL .= "last_modified = NOW();";
						
						Database::mysqli_query($strUserHistorySQL);
						
						*/
						// error_log("strUpdateSQL: " . $strUpdateSQL);
						Database::mysqli_query($strUpdateSQL);
						
						
						//error_log('SQL for update statment is :'.$strUpdateSQL);
						//error_log("SQL INSERT STATMENT IS *******************:" . $strUserHistorySQL);
						break;
					    }
					    
					$i++;
				}
						
				// Free the resources associated with the result set
				// This is done automatically at the end of the script
				

				
				//-------------------------------------------------------------------------------------
				/*
				//check if empty or different from last entry
				$sql_update_string = "";
				$sql_update_string .= "facebook_id='" . Database::mysqli_real_escape_string($facebook_id) . "',";
				if(empty($user->firstname))
					$sql_update_string .= "`firstname`='" . Database::mysqli_real_escape_string($firstname) . "',";
				if(empty($user->lastname))
					$sql_update_string .= "`lastname`='" . Database::mysqli_real_escape_string($lastname) . "',";
				if(empty($user->gender))
					$sql_update_string .= "`gender`='" . Database::mysqli_real_escape_string($gender) . "',";
				if((empty($user->date_of_birth) || $user->date_of_birth == '0000-00-00') && !empty($date_of_birth))
					$sql_update_string .= "`date_of_birth`='" . Database::mysqli_real_escape_string($date_of_birth) . "',";
				if(empty($user->facebook_location_id))
					$sql_update_string .= "`facebook_location_id`='" . Database::mysqli_real_escape_string($facebook_location_id) . "',";
				if(empty($user->facebook_location_name))
					$sql_update_string .= "`facebook_location_name`='" . Database::mysqli_real_escape_string($facebook_location_name) . "',";
				if(empty($user->relationship_status))
					$sql_update_string .= "`relationship_status`='" . Database::mysqli_real_escape_string($relationship_status) . "',";
				
				$sql = "UPDATE users set ".$sql_update_string." `modified`=NOW() where `email`= '" . Database::mysqli_real_escape_string($quick_email) . "'";
				// error_log("SQL update in quick_register_facebook_authenticate(): ".$sql);
				$results = Database::mysqli_query($sql);
				
				*/
				$user = new User($user->id);
				return $user;
			}
			else
			{
				//error_log("checking for a user that looks like this guy.... " . __LINE__) ;
				$check_sql = "SELECT email, username FROM users WHERE email = '$quick_email' OR username = '$username'";
				$result = Database::mysqli_query($check_sql);

				if($result && Database::mysqli_num_rows($result) > 0)
				{
					$row = Database::mysqli_fetch_assoc($result);
					$row2 = Database::mysqli_fetch_assoc($customer_result);
					if($username == $row['username'])
					{
						$errors_site['quick'] = 'Duplicate username; please try again';
						$errors['quick'] = 'Duplicate username; please try again';
					}
					else
					{
						$errors_site['quick'] = 'Duplicate email address; please try again';
						$errors['quick'] = 'Duplicate email address; please try again';
					}
					
					Database::mysqli_free_result($result);

					return $errors;
				}else{
					$email = Database::mysqli_real_escape_string($quick_email);
				}
				// If it doesn't create a new user
				$user = new User();
				//$user->id = $user_id;
				// Inserting to DB
				//error_log("creating a new user! " . __LINE__) ;
				$sql = "INSERT INTO users (`username`, `facebook_id`,  `email`, `password`, `firstname`, `lastname`, `gender`, `date_of_birth`,`facebook_location_id`, `facebook_location_name`, `relationship_status`, `fb_friend_count`, `created`, `modified`) VALUES ('" . Database::mysqli_real_escape_string($username) . "', '" . Database::mysqli_real_escape_string($facebook_id) . "', '" . Database::mysqli_real_escape_string($email) . "', '" . Database::mysqli_real_escape_string($md5_password) . "', '" . Database::mysqli_real_escape_string($firstname) . "', '" . Database::mysqli_real_escape_string($lastname) . "', '" . Database::mysqli_real_escape_string($gender) . "', '" . Database::mysqli_real_escape_string($date_of_birth) . "','" . Database::mysqli_real_escape_string($facebook_location_id) . "','" . Database::mysqli_real_escape_string($facebook_location_name) . "', '" . Database::mysqli_real_escape_string($relationship_status) . "', $fb_friend_count, NOW(), NOW())";
				$results = Database::mysqli_query($sql);
				if(!$results)
					error_log("SQL Insert error in User::quick_register_facebook_authenticate(): ".Database::mysqli_error(). "\nSQL: " . $sql);
				$user_id = Database::mysqli_insert_id();
				$_SESSION['new_user_claim'] = '1';

				if ($results) {
					
					$user = new User($user_id);
					/* old relic from our scanning app days, so it needs to be removed
					$mailer = new Mailer();
					$result = $mailer->user_signup_alert($user);
					*/
					Database::mysqli_free_result($results);

					return $user;
				}
				else
				{

				}
			}
			//error_log("SQL in quick_register_facebook_authenticate(): ".$sql);



		}
		else
		{
			return $errors;
		}
		return $errors;
	}
	
	public static function getAndStoreUserInterestsAndLikes($user_id, $access_token, $facebook_id = null, $item_id = null, $company_id = null)
	{
		global $app_version;
		if(!empty($access_token))
		{
			if(empty($facebook_id))
			{
				$user = new User($user_id);
				$facebook_id = $user->facebook_id;
			}
			
			$limit_likes = 10000;
	
			// $start_time = time();
			$likes = array();
			// $url = "https://graph.facebook.com/v" . $app_version . "/$facebook_id?fields=interests.offset(0).limit(999999999),likes.offset(0).limit($limit_likes)&access_token=" . $access_token;
			$url = "https://graph.facebook.com/v" . $app_version . "/$facebook_id?fields=likes.offset(0).limit($limit_likes)&access_token=" . $access_token;
			do
			{
				$interests_likes = json_decode(file_get_contents($url), true);
				// if(!empty($interests_likes['interests']))
				//	User::check_and_set_user_fb_interests($user_id, $interests_likes['interests'], $item_id);
				if(!isset($interests_likes['likes']))
					$interests_likes = array('likes' => $interests_likes);
				// error_log("interests_likes: " . var_export($interests_likes, true));
				$likes = array_merge($likes, $interests_likes['likes']['data']);
				$url = $interests_likes['likes']['paging']['next'];
				// error_log("url: " . $url);
			}
			while(!empty($url));
			// error_log("Time taken to retrieve and store likes: " . (time() - $start_time));
			// error_log("likes: " . var_export($likes, true));
			if(!empty($likes))
				User::check_and_set_user_fb_likes($user_id, $likes, $item_id, $company_id);
		}
	}
	
	public static function check_and_set_user_fb_likes($user_id, $new_fb_likes, $item_id = null, $company_id = null)
	{
		if(!empty($new_fb_likes))
		{
			$sql_item_id = !empty($item_id) ? " and ufl.item_id = '$item_id'" : "";
			$item_id = !empty($item_id) ? "'" . $item_id . "'" : "NULL";
			$company_id = !empty($company_id) ? "'" . $company_id . "'" : "NULL";
			
			$start_time = time();
			// error_log('new_fb_likes: ' . var_export($new_fb_likes, true));
			$arr_items_liked 	= array();
			$arr_items_unliked 	= array();
		
		
			/******************************* CONTAINS LIKES DATA RETRIEVED FROM FB *********************************/
			$fb_data = array();
			foreach($new_fb_likes as $i => $like)
			{
				$like_id = $like['id'];
				$fb_data[$like_id] = $like;
			}
			// error_log('fb_data: ' . var_export($fb_data, true));
		
			$fb_likes_and_ids = array(); // Contains the like id and fb_like_id
		
			/******************************	FB LIKE IDS RETRIEVED FROM FB *****************************************/
			$fb_data_ids = array_keys($fb_data);
			// error_log('fb_data_ids: ' . var_export($fb_data_ids, true));
		
		
		
			/******************************	FB LIKE IDS PRESENT IN fb_likes TABLE *****************************************/
			$like_ids_in_fb_likes = array(); // FB Like IDs present in fb_likes table
			if(!empty($fb_data_ids))
			{
				$sql = "select fb_id, id
				from fb_likes 
				where fb_id in ('" . implode("','", $fb_data_ids). "')";
				$rs = Database::mysqli_query($sql);
				if(!$rs)
				{
					error_log("SQL error in User::check_and_set_user_fb_likes(): " . Database::mysqli_error() . "\nSQL: " . $sql);
				}
				else
				{
					while($row = Database::mysqli_fetch_assoc($rs))
					{
						$fb_likes_and_ids[$row['fb_id']] = $row['id'];
					}
					$like_ids_in_fb_likes = array_keys($fb_likes_and_ids);
				}
			}
		
			/******************************	FB LIKE IDS NOT PRESENT IN fb_likes TABLE *******************************************/
			$like_ids_not_in_fb_likes = array_values(array_diff($fb_data_ids, $like_ids_in_fb_likes));
			// error_log('like_ids_in_fb_likes: ' . var_export($like_ids_in_fb_likes, true));
			// error_log('like_ids_not_in_fb_likes: ' . var_export($like_ids_not_in_fb_likes, true));
		
		
		
			/******************************	FB LIKE IDS PRESENT IN fb_likes TABLE 
											AND ALSO PRESENT IN user_fb_likes TABLE **********************************************/
			$like_ids_in_user_fb_likes = array();
			if(!empty($like_ids_in_fb_likes))
			{
				$sql = "select fl.fb_id, ufl.id, ufl.date_last_seen, ufl.date_removed
				from user_fb_likes ufl 
				inner join fb_likes fl on ufl.fb_like_id = fl.id
				where ufl.user_id = '$user_id'
				$sql_item_id
				and fl.fb_id in ('" . implode("','", $like_ids_in_fb_likes) . "')";
				// error_log("SQL for like_ids_in_user_fb_likes in check_and_set_user_fb_likes(): " . $sql);
				
				$rs = Database::mysqli_query($sql);
				if(!$rs)
				{
					error_log("SQL error in User::check_and_set_user_fb_likes(): " . Database::mysqli_error() . "\nSQL: " . $sql);
				}
				else
				{
					while($row = Database::mysqli_fetch_assoc($rs))
					{
						$like_ids_in_user_fb_likes[$row['id']] = $row['fb_id'];
						if(strtotime($row['date_removed']) > strtotime($row['date_last_seen']))
							$arr_items_liked[$row['id']] = $row['fb_id'];
					}
				}
			}
		
			/******************************	FB LIKE IDS PRESENT IN fb_likes TABLE 
											BUT NOT PRESENT IN user_fb_likes TABLE **********************************************/
			$like_ids_not_in_user_fb_likes = array_values(array_diff($like_ids_in_fb_likes, $like_ids_in_user_fb_likes));
			// error_log('like_ids_in_user_fb_likes: ' . var_export($like_ids_in_user_fb_likes, true));
			// error_log('like_ids_not_in_user_fb_likes for user_id ' . $user_id . ': ' . var_export($like_ids_not_in_user_fb_likes, true));
		
	
			/**************************	FB LIKE IDS PRESENT IN user_fb_likes TABLE 
			
											BUT NOT PRESENT IN THE DATA RETRIEVED FROM FACEBOOK *************************/
		
			$user_fb_likes_not_in_like_ids = array();	
			if(!empty($fb_data_ids))
			{
				$sql = "select fl.fb_id, ufl.id, ufl.date_last_seen, ufl.date_removed
				from user_fb_likes ufl 
				inner join fb_likes fl on ufl.fb_like_id = fl.id
				where ufl.user_id = '$user_id' 
				$sql_item_id
				and  fl.fb_id not in ('" . implode("','", $fb_data_ids) . "')";
				// error_log("SQL for finding user_fb_likes_not_in_like_ids: " . $sql);
				$rs = Database::mysqli_query($sql);
		
				if(!$rs)
				{
					error_log("SQL error in User::check_and_set_user_fb_likes(): " . Database::mysqli_error() . "\nSQL: " . $sql);
				}
				else
				{
					while($row = Database::mysqli_fetch_assoc($rs))
					{
						$user_fb_likes_not_in_like_ids[$row['id']] = $row['fb_id'];
						if(empty($row['date_removed']) || (strtotime($row['date_removed']) < strtotime($row['date_last_seen'])))
							$arr_items_unliked[$row['id']] = $row['fb_id'];
					}
				}
			}
			// error_log('user_fb_likes_not_in_like_ids for user_id ' . $user_id . ': ' . var_export($user_fb_likes_not_in_like_ids, true));
		
		
			/************************************************************************************************
							1.	For all FB likes NOT present in fb_likes:
								Insert a row in fb_likes and a corresponding row in user_fb_likes
			************************************************************************************************/
			$sql = "";
			$loop_entered = false;
			foreach($like_ids_not_in_fb_likes as $like_id)
			{
				if(!$loop_entered)
					$loop_entered = true;
				else
					$sql .= ", ";
				
				$like_name = Database::mysqli_real_escape_string($fb_data[$like_id]['name']);
				$like_category = Database::mysqli_real_escape_string($fb_data[$like_id]['category']);
				$sql .= "('$like_id', '$like_name', '$like_category', $item_id)";
			
			}
		
			if(!empty($like_ids_not_in_fb_likes))
			{
				$sql = "insert into `fb_likes` (`fb_id`, `name`, `category`, `item_id`) values " . $sql;
				if(!Database::mysqli_query($sql))
					error_log("Insert SQL error when inserting data to fb_likes: " . Database::mysqli_error() . "\nSQL: " . $sql);
				else
					$last_insert_id = Database::mysqli_insert_id();
				
				$num_rows = count($like_ids_not_in_fb_likes);
				$last_fb_like_id = $last_insert_id + $num_rows - 1;
			
				$sql = "";
				$loop_entered = false;
				for($fb_like_id = $last_insert_id; $fb_like_id <= $last_fb_like_id; $fb_like_id++)
				{
					if(!$loop_entered)
						$loop_entered = true;
					else
						$sql .= ", ";
					
					$sql .= "('$fb_like_id', '$user_id', $item_id, $company_id)";
			
				}
			
				$sql = "insert into `user_fb_likes` (`fb_like_id`, `user_id`, `item_id`, `company_id`) values " . $sql;
				if(!Database::mysqli_query($sql))
					error_log("Insert SQL error when inserting data to user_fb_likes: " . Database::mysqli_error() . "\nSQL: " . $sql);
			
			}





			/****************************************************************************************
					2.	For all FB likes present in fb_likes and NOT present in user_fb_likes:
						Insert a row in user_fb_likes
			*****************************************************************************************/
		
			if(!empty($like_ids_not_in_user_fb_likes))
			{
				$sql = "";
				$loop_entered = false;
				foreach($like_ids_not_in_user_fb_likes as $like_id)
				{
					if(!$loop_entered)
						$loop_entered = true;
					else
						$sql .= ", ";
					
					$fb_like_id = $fb_likes_and_ids[$like_id];
					$sql .= "('$fb_like_id', '$user_id', $item_id, $company_id)";
			
				}
			
				$sql = "insert into `user_fb_likes` (`fb_like_id`, `user_id`, `item_id`, `company_id`) values " . $sql;
				if(!Database::mysqli_query($sql))
					error_log("Insert SQL error when inserting data to user_fb_likes: " . Database::mysqli_error() . "\nSQL: " . $sql);
			}







			/************************************************************************
				3.	For all FB likes present in fb_likes and present in user_fb_likes:
				Update the date_last_seen column in user_fb_likes
			**************************************************************************/
			if(!empty($like_ids_in_user_fb_likes))
			{
				$sql = "update user_fb_likes set date_last_seen = now() where id in (" . implode(',', array_keys($like_ids_in_user_fb_likes)) . ")";
				if(!Database::mysqli_query($sql))
					error_log("Update SQL error when updating date_last_seen: " . Database::mysqli_error() . "\nSQL: " . $sql);

			}
	
	
	
	
	
			/*****************************************************
			4.	For all user_fb_likes not present in FB Likes
				Update the date_last_removed column in user_fb_likes
			**********************************************/
			if(!empty($user_fb_likes_not_in_like_ids))
			{
				$sql = "update user_fb_likes set date_removed = now() where id in (" . implode(',', array_keys($user_fb_likes_not_in_like_ids)) . ")";
				// Commenting this out since it possible creates reporting issues in the backend
				if(!Database::mysqli_query($sql))
					error_log("Update SQL error when updating date_removed: " . Database::mysqli_error() . "\nSQL: " . $sql);

			}
			/*
			// error_log("FB LIKE IDS PRESENT IN fb_likes TABLE: " . var_export($like_ids_in_fb_likes, true));
			error_log("FB LIKE IDS NOT PRESENT IN fb_likes TABLE: " . var_export($like_ids_not_in_fb_likes, true));
			// error_log("FB LIKE IDS PRESENT IN fb_likes TABLE AND ALSO PRESENT IN user_fb_likes TABLE: " . var_export($like_ids_in_user_fb_likes, true));
			error_log("FB LIKE IDs whose date_removed was greater than date_last_seen: " . var_export($arr_items_liked, true));
			error_log("FB LIKE IDS PRESENT IN fb_likes TABLE BUT NOT PRESENT IN user_fb_likes TABLE: " . var_export($like_ids_not_in_user_fb_likes, true));
			// error_log("FB LIKE IDS PRESENT IN user_fb_likes TABLE BUT NOT PRESENT IN THE DATA RETRIEVED FROM FACEBOOK: " . var_export($user_fb_likes_not_in_like_ids, true));
			error_log("FB LIKE IDS PRESENT IN user_fb_likes TABLE BUT NOT PRESENT IN THE DATA RETRIEVED FROM FACEBOOK whose date_removed was less than date_last_seen: " . var_export($arr_items_unliked, true));
			*/
		
			$arr_items_liked = array_unique(array_merge(array_values($like_ids_not_in_fb_likes), array_values($arr_items_liked), array_values($like_ids_not_in_user_fb_likes)));
			$arr_items_unliked = array_values($arr_items_unliked);

			error_log("Time taken to run User::check_and_set_user_fb_likes(): " . (time() - $start_time));
			Database::mysqli_free_result($rs);
			return array($arr_items_liked, $arr_items_unliked);
		}
	}

	public static function log_claimed_attempts($user_id = 'null' , $item_id = 'null', $claimed = '0', $source = 'null', $facebook_id = 'null', $distributor_id = 'null', $shared_referral_id = 'null', $sgs_uiid = 'null')
	{

		$user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$ip = Common::GetUserIp();
		$session_id = session_id();

		$sql = "insert into claim_attempts (user_id, item_id, sgs_uiid, ip, user_agent, session_id, claimed, source, facebook_id, distributor_id, shared_referral_id, created)
		values
		('" . Database::mysqli_real_escape_string($user_id) . "',
		'" . Database::mysqli_real_escape_string($item_id) . "',
		'" . Database::mysqli_real_escape_string($sgs_uiid) . "',
		'" . Database::mysqli_real_escape_string($ip) . "',
		'" . Database::mysqli_real_escape_string($user_agent) . "',
		'" . Database::mysqli_real_escape_string($session_id) . "',
		'". Database::mysqli_real_escape_string($claimed). "',
		'". Database::mysqli_real_escape_string($source). "',
		'" . Database::mysqli_real_escape_string($facebook_id) . "',
		'". Database::mysqli_real_escape_string($distributor_id). "',
		'". Database::mysqli_real_escape_string($shared_referral_id). "',
		now() );";

		Database::mysqli_query($sql);
		$insert_id = Database::mysqli_insert_id();

		//if (Database::mysqli_error()) {
		//	error_log('mysql error in log_claimed_attempts() ' . Database::mysqli_errno() . ": " . Database::mysqli_error());
		//	throw new Exception('mysql error ' . Database::mysqli_errno() . ": " . Database::mysqli_error());
		//}

		return $insert_id;
	}
	
	public static function check_for_fraudulent_activity($user_id, $item_id)
	{
		$limit = 2;
		$time_span = 5;

		$user_agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$ip = Common::GetUserIp();
		$session_id = session_id();
		
		$result = false;
		$sql = "select * from claim_attempts where ip = '$ip' and user_agent = '$user_agent' and item_id = '$item_id' and claimed != 1 order by id desc limit $limit";
		// error_log("SQL in User::check_for_fraudulent_activity(): " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		// error_log("rows in User::check_for_fraudulent_activity(): " . var_export($rows, true));
		if(count($rows) == $limit)
		{
			$start_time = $rows[0]['created'];
			$end_time = $rows[$limit - 1]['created'];
			$row_user_id = $rows[0]['user_id'];
			
			if($row_user_id == $user_id)
			{
				$time_diff_sec = abs(strtotime($start_time) - strtotime($end_time));
				$time_diff_min = $time_diff_sec / 60;
				// error_log("In User::check_for_fraudulent_activity(): row_user_id: " . $row_user_id . ", start_time: " . $start_time . ", end_time: " . $end_time . ", time_diff_sec: " . $time_diff_sec . ", time_diff_min: " . $time_diff_min);
				if($time_diff_min <= $time_span)
					$result = true;
			}
		}
		return $result;
	}
	
	public static function blockAppUser($user_id, $app_name, $reason, $facebook_id = null, $automatically_banned = 1)
	{
		$facebook_id = !empty($facebook_id) ? "'" . $facebook_id . "'" : 'NULL';
		$reason = Database::mysqli_real_escape_string($reason);
		$automatically_banned = !empty($automatically_banned) ? $automatically_banned : 0;

		if($app_name == 'convercial')
			$app_name = 'instore';
		
		$sql = "insert into `blocked_app_users` (`user_id`, `app_name`, `reason`, `facebook_id`, `automatically_banned`) values ('$user_id', '$app_name', '$reason', $facebook_id, '$automatically_banned')";
		if(!Database::mysqli_query($sql))
			return false;
		
		$sql = "update users set status = 'suspended' where id = '$user_id'";	
		Database::mysqli_query($sql);
		return true;
		
	}
	
	public static function add_suspicious_user_activity($user_id, $item_id, $reason)
	{
		$descriptions = array('no_friends' => 'This user has no facebook friends and seems fake.', 'fraudulent_activity' => 'This user was trying to commit fraudulent activity.');
		$description = Database::mysqli_real_escape_string($descriptions[$reason]);
		
		$table_data = array(
			'user_id' => $user_id,
			'item_id' => $item_id,
			'reason' => $reason,
			'description' => $description,
		);
		
		BasicDataObject::InsertTableData("suspicious_user_activity", $table_data);
		
		// Mark User as suspicious
		$sql = "update users set `status` = 'suspicious' where id = '$user_id'";
		Database::mysqli_query($sql);
	}
	
}

?>