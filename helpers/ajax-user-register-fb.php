<?php

function autoload_classes($class_name)
{
    $file = dirname(__DIR__) . '/classes/'.$class_name.'.class.php';
    
    if (file_exists($file))
    {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');

require_once('../includes/email_parsing.php');
require_once ('../includes/facebook-php-sdk/src/facebook.php');
require_once ('../includes/app_config.php');
require_once('../includes/UUID.php');

global $socgift_app_id, $app_id;

date_default_timezone_set('UTC');
$db = new Database();
try{
	$db->connect();
} catch(Exception $e) {
	Errors::show500();
}
global $db;

//start the session
session_start();

header('Content-Type: application/json; charset=utf-8');

// error_log('REQUEST in ajax-user-register-fb.php: '. var_export($_REQUEST, true));

$edit_terms 		= !empty($_REQUEST['edit_terms']) ? true : false;
$item_id 			= !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : 0;
$distributor 		= !empty($_REQUEST['distributor']) ? $_REQUEST['distributor'] : '';
// $str_user_details 		= !empty($_REQUEST['user_details']) ? $_REQUEST['user_details'] : '';
$user_details 		= !empty($_REQUEST['user_details']) ? $_REQUEST['user_details'] : '';
// $user_interests		= isset($_REQUEST['user_interests']) ? $_REQUEST['user_interests'] : null;
// $user_likes			= isset($_REQUEST['user_likes']) ? $_REQUEST['user_likes'] : null;
$items_views_id 		= !empty($_REQUEST['items_views_id']) ? $_REQUEST['items_views_id'] : 0;
$new_referral_id 	= !empty($_REQUEST['new_referral_id']) ? $_REQUEST['new_referral_id'] : 0;
$mode 				= !empty($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$user = null;

// Commented out by Samee. Tue Nov 6th, 2012. We want to remove the function calls that trigger anything based on user data changes.
// $change_or_event_deals = SmartEmail::getSmartEmailChangeOrEventBasedDeals();
// $user_details = json_decode($str_user_details, true);
// error_log('USER DETAILS in helpers/ajax-user-register-fb.php: ' . var_export($user_details, true));

// $existing_user = User::findByEmail($user_details['email']);
// error_log('existing user: '. var_export($existing_user, true));
// if ($existing_user) {
	// error_log('HIT HERE LINE: '. (__LINE__));
	// $user = $existing_user;
	// $user = User::quick_register_facebook_authenticate($user_details, null);
// } else {
	// error_log('HIT HERE LINE: '. (__LINE__));
	//	$user = User::quick_register_facebook_authenticate($user_details, null);
// }
/*
// Store FB Interests and Likes
if($mode == 'fb_interests_and_likes')
{
	$user_id = $_POST['user_id'];
	$user_interests = $user_details['interests'];
	$user_likes = $user_details['likes'];

	// for($i = 0; $i < 10; $i++)
	//	$user_interests['data'][] = array('id' => '1234567890' . $i, 'category' => 'category' . $i, 'name' => 'interest' . $i, 'created_time' => time() . $i);

	if(!empty($user_interests))
		User::check_and_set_user_fb_interests($user_id, $user_interests);

	// for($i = 0; $i < 10; $i++)
		// $user_likes['data'][] = array('id' => '1234567890' . $i, 'category' => 'category' . $i, 'name' => 'like' . $i, 'created_time' => time() . $i);

	if(!empty($user_likes))
		User::check_and_set_user_fb_likes($user_id, $user_likes);
	
	print json_encode('1');
	exit();
}
*/

/*
$existing_user = User::findUsersByFacebookIDOrEmail($user_details['id'], isset($user_details['email']) ? $user_details['email'] : null);
if(empty($existing_user->id))
	$_SESSION['new_user_claim'] = '1';
*/

$item = new Item($item_id);
$company_id = $item->manufacturer_id;

// error_log("item->coupon_age_limit in ajax-user-register-fb: " . $item->coupon_age_limit);
if(!empty($item->coupon_age_limit))
{
	// error_log("user_details['birthday'] in ajax-user-register-fb: " . $user_details['birthday']);
	if(!empty($user_details['birthday']))
	{
		$user_age = Common::calculateAgeFromBirthday(Common::parse_fb_date($user_details['birthday']));
		// error_log("user_age in ajax-user-register-fb: " . $user_age);
		if($user_age < $item->coupon_age_limit)
		{
			echo json_encode(array('redirect' => '/coupon_age_limit.html', 'error'=>'denied'));
			exit();
		}
	}
}

// $user = User::quick_register_facebook_authenticate($user_details, null);
if(!empty($user_details['email']))
	$user = User::quick_register_facebook_authenticate($user_details, null);
else
	$user = User::quick_register_facebook_authenticate_no_email($user_details);

// Item::updatePermissionsRejected($item_id, null, true);
/////////////////////////////////////////////////////////////////////////////////
/////////	MARKING A USER AS HAVING ZERO FRIENDS
/////////////////////////////////////////////////////////////////////////////////

// error_log("user_details['friends'] in helpers/ajax-user-register-fb.php: " . var_export($user_details['friends'], true));
if(isset($user_details['friends']['summary']['total_count']))
	if(is_numeric($user_details['friends']['summary']['total_count']))
		if(empty($user_details['friends']['summary']['total_count']))
			$_SESSION['fake_fb_account'] = 1;

// $_SESSION['fake_fb_account'] = 1;


$user_id = $user->id;
$facebook_id = $user_details['id'];

/////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////	CHECKING IF THE USER JUST MADE A DONATION
/////////////////////////////////////////////////////////////////////////////////////////
if(!empty($_SESSION['donation_charge_id']))
{
	$sql = "update donations set user_id = '$user_id', facebook_id = '$facebook_id' where charge_id = '" . $_SESSION['donation_charge_id'] . "'";
	Database::mysqli_query($sql);

	unset($_SESSION['donation_charge_id']);
	
}

//uncomment this to implement terms agreement for user
// if($edit_terms){
// 	$sql = "UPDATE users SET agreed_to_terms=1 WHERE id='" . Database::mysqli_real_escape_string($user->id) . "'";
// 	Database::mysqli_query($sql);
// }

// Update Item View Id if present
if(!empty($items_views_id))
	Item::update_user_id_in_item_view($user->id, $items_views_id);

if(!empty($new_referral_id))
	Item::update_user_id_in_referrals($user->id, $new_referral_id);
	


// Storing User Access Token if available
if(!empty($_REQUEST['access_token']))
{
	$access_token	= $_REQUEST['access_token'];
	$app_name		= $_REQUEST['app_name'];
	$facebook_id	= $user_details['id'];
	$permissions	= $_REQUEST['permissions'];
	$object_type 	= 'user';
	$object_id		= $user_id;
	
	User::getAndStoreUserInterestsAndLikes($user_id, $access_token, $facebook_id, $item_id, $company_id);
	
	$access_token_data = array(
		'access_token'	=> $access_token,
		'app_id'		=> $app_id,
		'app_name'		=> $app_name,
		'facebook_id'	=> $facebook_id,
		'permissions'	=> $permissions,
		'object_type'	=> $object_type,
		'object_id'		=> $object_id,
	);
	Common::checkAndStoreFBAccessToken($access_token_data);
}



$comp = new Company($company_id);
$continue = true;
$return = "";

// error_log("item in ajax-user-register-fb: " . var_export($item, true));
if(!empty($item->use_bundled_coupons))
{
	$user->{'bundled_coupons_code'} = UUID::v4();
	$_SESSION['bundled_coupons_code'] = $user->{'bundled_coupons_code'};
}

//check if sgs discount
if($discount_code = Item::isSGSDiscount($item_id)){
	$continue = false;
	$discount = SGS_Discount::constructByCode($discount_code);
	
	//check if usage is too much
	$allowed = $discount->checkUsage($user->facebook_id);
	if($allowed['allowed']){
		$code_arr = array(SGS_Item::$DISCOUNT_ID => $discount->id, SGS_Item::$FBID => $user->facebook_id);
		
		//redirect to sgs store with discount applied
		$redirect = "https://www.facebook.com/pages/Test-Marketer-Business/". $comp->facebook_page_id ."?id=". $comp->facebook_page_id ."&sk=app_". $socgift_app_id ."&app_data=" . serialize($code_arr);
		
		// UserActivityLog::log_user_activity($user_id, 'claimed', 'fan_deals', $item_id);
		
		$is_regular_coupon = '0';
		$uiid = UserItems::generate_unique_uiid();
		$insert = "insert into user_items (item_id, user_id, uiid, is_regular_coupon, date_committed, delivery_center_arrival, date_sent, expected_delivery_date, date_claimed) VALUES ('" . Database::mysqli_real_escape_string($item_id) . "', '" . Database::mysqli_real_escape_string($user->id) . "', '" . Database::mysqli_real_escape_string($uiid) . "', '".Database::mysqli_real_escape_string($is_regular_coupon)."', NOW(), NOW(), NOW(), NOW(), NOW())";
		
		$result = Database::mysqli_query($insert);
		
		if (Database::mysqli_error())
		{
			error_log('mysql error ' . Database::mysqli_errno() . ' on user_items insert: ' . Database::mysqli_error() . ' ---- Query was: ' . $insert);
		}
		
		
		$error = 'none';
	} else {
		$redirect = '/print-coupon-denied.html';
		$error = 'denied';
	}
	
	$return = array('redirect' => $redirect, 'error'=>$error);
}

//Check if Magento Plugin Redirect, then figure out where to redirect to
if($item->delivery_method == '9' && $continue){
	
	if(Item::canUserClaimMagentoItem($user->id, $item_id) && ($comp->magento_running == '1')){
		$user_id = $user->id;
		
		$uiid = UserItems::generate_unique_uiid();
		$redirect = $comp->magento_url . 'coup?uiid=' . $uiid;
		$error = 'none';
		
		$is_regular_coupon = '0';
		
		// UserActivityLog::log_user_activity($user_id, 'claimed', 'fan_deals', $item_id);
		
		$insert = "insert into user_items (item_id, user_id, uiid, is_regular_coupon, date_committed, delivery_center_arrival, date_sent, expected_delivery_date, date_claimed, has_hit_magento_website) VALUES ('" . Database::mysqli_real_escape_string($item_id) . "', '" . Database::mysqli_real_escape_string($user_id) . "', '" . Database::mysqli_real_escape_string($uiid) . "', '".Database::mysqli_real_escape_string($is_regular_coupon)."', NOW(), NOW(), NOW(), NOW(), NOW(), '0')";
		
		$result = Database::mysqli_query($insert);
		
		if (Database::mysqli_error())
		{
			error_log('mysql error ' . Database::mysqli_errno() . ' on user_items insert: ' . Database::mysqli_error() . ' ---- Query was: ' . $insert);
		}
		else
		{
			Item::checkAndUpdateOutOfStockItems($item_id);
		}	
		
	} else {
		$redirect = '/print-coupon-denied.html';
		$error = 'denied';
	}
	
	$return = array('redirect' => $redirect, 'error'=>$error);
} else if($continue){
	// error_log('username: '.$user->username);
	switch($mode){
		case "login":
			$dashboard = Common::getLoginRedirect($user->id);
			
			$redirect[] = $dashboard;
			$redirect[] = urlencode($user->username);
			$return = $redirect;
			break;
		default:
			// if(Item::canUserPrintItem($user->id, $item->id))
				$return = $user;
			// else
			//	$return = array('print_access_denied' => 1);
	
	}
}

echo json_encode($return);

?>