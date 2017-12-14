<?php
require_once(dirname(__DIR__) . '/classes/Database.class.php');
require_once(dirname(__DIR__) . '/classes/BasicDataObject.class.php');
require_once(dirname(__DIR__) . '/classes/Errors.class.php');
// require_once(dirname(__DIR__) . '/classes/Mailer.class.php');
require_once(dirname(__DIR__) . '/classes/PrintMethods.class.php');
require_once(dirname(__DIR__) . '/classes/UserItems.class.php');
require_once(dirname(__DIR__) . '/classes/User.class.php');
require_once(dirname(__DIR__) . '/classes/Item.class.php');
// require_once(dirname(__DIR__) . '/classes/Session.class.php');
require_once(dirname(__DIR__) . '/classes/Common.class.php');
require_once(dirname(__DIR__) . '/classes/Company.class.php');
require_once(dirname(__DIR__) . '/classes/SilverPop.class.php');
require_once(dirname(__DIR__) . '/classes/UBX.class.php');
// Temporarily removed
// require_once(dirname(__DIR__) . '/classes/ExactTarget.class.php');
// require_once(dirname(__DIR__) . '/classes/MailChimp.class.php');
// require_once(dirname(__DIR__) . '/classes/CampaignMonitor.class.php');
// require_once(dirname(__DIR__) . '/classes/EmailTemplates.class.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');

// error_reporting(0);

$db = new Database();
try{
	$db->connect();
} catch(Exception $e) {
	Errors::show500();
}

global $db;
global $app_id;

session_start();

header('Content-Type: application/json; charset=utf-8');
// error_log("REQUEST in ajax-customer-supplied-code.php: " . var_export($_REQUEST, true));

// $campaign_id	=	$_REQUEST['campaign_id'];
$user_details	=	!empty($_REQUEST['user_details']) ? $_REQUEST['user_details'] : null;
$user_interests=	!empty($_REQUEST['user_interests']) ? $_REQUEST['user_interests'] : null;
// $expired_date	=	$_REQUEST['expired_date'];
// $expired_date 	=	date("Y-m-d H:i:s", $expired_date/(1000 ));
$user_id			=	$_REQUEST['user_id'];
$item_id			=	$_REQUEST['item_id'];
$items_views_id		=	$_REQUEST['items_views_id'];
$new_referral_id 	= !empty($_REQUEST['new_referral_id']) ? $_REQUEST['new_referral_id'] : 0;
$access_token		=	!empty($_REQUEST['access_token']) ? $_REQUEST['access_token'] : null;
$permissions		=	!empty($_REQUEST['permissions']) ? $_REQUEST['permissions'] : null;
$app_name 			= !empty($_REQUEST['app_name']) ? $_REQUEST['app_name'] : 'fan_deals';
$perform_user_registration = empty($_REQUEST['skip_user_registration']);
$redeem_coupon_code	= !empty($_REQUEST['redeem_coupon_code']);


$item_info = Item::getMOItemInfo($item_id);

$campaign_id		= $item_info['campaign_id'];
$deal_id			= $item_info['deal_id'];
$company_id			= $item_info['company_id'];
$csc_custom_code	= $item_info['csc_custom_code'];
$csc_email_from		= $item_info['instore_email_from'];
$csc_email_subject	= $item_info['instore_email_subject'];
$csc_email_template	= $item_info['csc_email_template'];

$is_silverpop_company = $item_info['is_silverpop_company'];
$is_et_company		= $item_info['is_et_company'];
$sp_is_ubx			= $item_info['sp_is_ubx'];
$is_mailchimp_company	= $item_info['is_mailchimp_company'];
$mc_list_id			= $item_info['mc_list_id'];
$mc_api_key			= $item_info['mc_api_key'];
$is_campaign_monitor_company = $item_info['is_campaign_monitor_company'];
$cm_client_id		= $item_info['cm_client_id'];
$cm_api_key			= $item_info['cm_api_key'];
$cm_list_id			= $item_info['cm_list_id'];
$cm_list_name		= $item_info['cm_list_name'];

$enable_user_blocking	= $item_info['enable_user_blocking'];
$delivery_method	= $item_info['delivery_method'];
$expires			= $item_info['expiry'];
$expire_month		= $item_info['expire_month'];
$expire_year		= $item_info['expire_year'];

/*
$item			= new Item($item_id);
$campaign_id	= $item->campaign_id;
$deal_id		= $item->deal_id;
$company_id		= $item->manufacturer_id;
$company		= new Company($company_id);
$csc_custom_code = $item->csc_custom_code;
$is_silverpop_company = $company->is_silverpop_company;
$is_et_company = $company->is_et_company;
$sp_is_ubx = $company->sp_is_ubx;
$enable_user_blocking = $company->enable_user_blocking;
*/



if($perform_user_registration)
{
	// Check and Register the user

	// error_log("item_info['coupon_age_limit'] in instore controller: " . $item_info['coupon_age_limit']);
	if(!empty($item_info['coupon_age_limit']))
	{
		// error_log("user_details['birthday'] in instore controller: " . $user_details['birthday']);
		if(!empty($user_details['birthday']))
		{
			$user_age = Common::calculateAgeFromBirthday(Common::parse_fb_date($user_details['birthday']));
			// error_log("user_age in instore controller: " . $user_age);
			if($user_age < $item_info['coupon_age_limit'])
			{
				echo json_encode(array('redirect' => '/coupon_age_limit.html', 'error'=>'denied'));
				exit();
			}
		}
	}


	if(!empty($user_details['email']))
	{
		$user = User::quick_register_facebook_authenticate($user_details, null);
	}
	else
	{
		$user = User::quick_register_facebook_authenticate_no_email($user_details);
	}


	// error_log("user_details: " . var_export($user_details, true));
	$user_id	= $user->id;
	$facebook_id = $user_details['id'];




	/////////////////////////////////////////////////////////////////////////////////
	/////////	MARKING A USER AS HAVING ZERO FRIENDS
	/////////////////////////////////////////////////////////////////////////////////

	// error_log("user_details['friends'] in helpers/ajax-user-register-fb.php: " . var_export($user_details['friends'], true));
	if(isset($user_details['friends']['summary']['total_count']))
		if(is_numeric($user_details['friends']['summary']['total_count']))
			if(empty($user_details['friends']['summary']['total_count']))
				$_SESSION['fake_fb_account'] = 1;

	// $_SESSION['fake_fb_account'] = 1;



	/////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////	CHECKING IF THE USER JUST MADE A DONATION
	/////////////////////////////////////////////////////////////////////////////////////////
	if(!empty($_SESSION['donation_charge_id']))
	{
		$sql = "update donations set user_id = '$user_id', facebook_id = '$facebook_id' where charge_id = '" . $_SESSION['donation_charge_id'] . "'";
		Database::mysqli_query($sql);

		unset($_SESSION['donation_charge_id']);
	}

	$claimed_attempt_id = User::log_claimed_attempts($user_id , $item_id, '0', 'null', 'null', 1, 'null', null);

	$user_item_id = '';

	if(!empty($items_views_id))
		Item::update_user_id_in_item_view($user_id, $items_views_id);
	
	if(!empty($new_referral_id))
		Item::update_user_id_in_referrals($user_id, $new_referral_id);


	// if($delivery_method == '11')	// Which means that this helper was called from canvas/coupsmart.controller page
	// {
		User::getAndStoreUserInterestsAndLikes($user_id, $access_token, $facebook_id, $item_id, $company_id);
	
		$access_token_data = array(
			'access_token'	=> $access_token,
			'app_id'		=> $app_id,
			'app_name'		=> $app_name,
			'facebook_id'	=> $facebook_id,
			'permissions'	=> $permissions,
			'object_type'	=> 'user',
			'object_id'		=> $user_id,
		);
		Common::checkAndStoreFBAccessToken($access_token_data);
	// }
}
else
{

}

if(!Item::canUserPrintItem($user_id, $item_id)) {
	$is_mobile = Common::isMobileESP();
	$coupon_denied_page = $is_mobile ? 'print-coupon-denied-mobile.html' : 'print-coupon-denied.html';
	$response = file_get_contents('../' . $coupon_denied_page);
	// error_log("response: " . $response);
	echo json_encode(array('error' => $response));
	exit();
}
else if(Item::hasItemRunOutOfStock($item_id))
{
	$response = "Sorry! There are no more prints available for this coupon; it has run out of stock!";
	echo json_encode(array('error' => $response));
	exit();
}

// $user_id = 			PrintMethods::get_coup_id($user_details['id']); //need to get the user_id associated with this facebook id




$sp_recipient_id = '';


// Temporarily removed for demo purposes - Remember to uncomment when this functionality is needed
/*
if(!empty($is_silverpop_company))
	if(empty($sp_is_ubx))
		$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $company_id);

if($is_et_company == '1')
	ExactTarget::checkAndUpsertSubscriber($user_id, $company_id, array('action' => 'claim', 'item_id' => $item_id));

if($is_mailchimp_company == '1' && !empty($mc_list_id))
	MailChimp::checkAndUpsertMember($user_id, $mc_list_id, $mc_api_key, $company_id);

if($is_campaign_monitor_company == '1' && !empty($cm_list_id))
	CampaignMonitor::checkAndUpsertMember($user_id, $cm_list_id, $cm_client_id, $cm_api_key, $deal_id, $company_id);
*/
///////////////////////////////////////////////////////////////////////
//////////// CHECKING FOR BOTH EMPTY FRIENDS AND FRAUDULENT ACTIVITY

$fake_fb_account = false;
if(!empty($_SESSION['fake_fb_account']))
{
	$fake_fb_account = true;
	unset($_SESSION['fake_fb_account']);	
}

if(!empty($enable_user_blocking))
{
	$fraudulent_activity = User::check_for_fraudulent_activity($user_id, $item_id);
	
	// error_log("fake_fb_account: " . var_export($fake_fb_account, true) . ", fraudulent_activity: " . var_export($fraudulent_activity, true));
	if($fake_fb_account && $fraudulent_activity)
	{
		//	MARK USER FOR BLOCKING
		$tmp_user = new User($user_id);
		User::blockAppUser($user_id, $app_name, 'This user has no facebook friends (seems fake) and was trying to commit fraudulent activity.', $tmp_user->facebook_id);
		// And tell them they have been blocked from using the app.
		// TODOS: Change this design to show a proper message.
		$content = Common::getUserBlockedMessageContent();
		print json_encode(array('error' => $content));
		exit();
	}
	else if($fraudulent_activity)
	{
		User::add_suspicious_user_activity($user_id, $item_id, 'fraudulent_activity');
	}
	else if($fake_fb_account)
	{
		User::add_suspicious_user_activity($user_id, $item_id, 'no_friends');
	}
	
}

///////////////////////////////////////////////////////////////////////

// Check if the user has exceeded their print limit
// $validation = PrintMethods::check_user($user_details['id'], $campaign_id);
// $user_info = PrintMethods::checkUser($user_details['id'], $deal_id, $csc_custom_code, $user_id, $item_id);

// if(empty($user_info)){
// 	error_log("They haven't received one yet");

	$user_item_id = UserItems::claimItem($item_id, $user_id, $items_views_id, $company_id); //update the analytics for this user
	error_log("user item id" . $user_item_id);
	// $custom_code = PrintMethods::get_custom_data($campaign_id, $user_item_id, $user_details['id']); //assign them a code
	$custom_code = PrintMethods::getCustomData($deal_id, $user_item_id, $user_details['id'], $csc_custom_code); //assign them a code

	
	
	if(!empty($is_silverpop_company))
	{
		if(empty($sp_is_ubx))
		{
			// And if the Upsert was successful, submit a UB Event
			if(!empty($sp_recipient_id))
			{
				// SilverPop::triggerClaimedOffer($items_views_info['company_id'], $sp_recipient_id, $user_id, $item_id, $insert_id, null, null);
				SilverPop::triggerClaimedOffer($company_id, $sp_recipient_id, $user_id, $item_id, $user_item_id, null, null);
			}
		}
		else
		{
			UBX::triggerClaimedOffer($company_id, null, $user_id, $item_id, $user_item_id, null, null);
		}
	}
	
	if(!empty($claimed_attempt_id))
	{

		$sql = "update claim_attempts set claimed = '1' where id = '".Database::mysqli_real_escape_string($claimed_attempt_id)."'";
		Database::mysqli_query($sql);
	}
	
	//	Redeeming the coupon code if specified
	if($redeem_coupon_code)
	{
		UserItems::redeemCoupon($user_item_id);
	}
	
// }
/*
else{
	error_log("They have received one, already");
	$user_item_id = $user_info['user_item_id'];
	error_log("user item id" . $user_item_id);
	// $custom_code = PrintMethods::get_user_code($user_item_id, $campaign_id);
	$custom_code = $user_info['custom_code'];
	//error_log("custom code:" . $custom_code);
}
*/
error_log("Custom code: " . $custom_code);

$issued_date = PrintMethods::get_issued_date($user_item_id); //issued date
// $expired_date = date("Y-m-d H:i:s", strtotime($issued_date . "+7 days")); //reformatting to get expired date
// $expired_date = "2013-04-30 11:59:59";
if(!empty($expires))
	$expired_date = date('m/d/Y', $expires);
else if(!empty($expire_year) && !empty($expire_month))
	$expired_date = date('M, Y', strtotime($expire_year. '-' . $expire_month . '-1'));
else 
	$expired_date = "N/A";

//after the content is loaded, send an email to the user with their custom code

// $count = PrintMethods::count_remaining_codes($campaign_id);
$count = PrintMethods::countRemainingCodes($deal_id);
if(empty($custom_code)){
	//error_log("THERE ARE NO MORE CODES TO GIVE OUT"); //this error should redirect to another page
	//add error page here in case this get here (which they shouldn't be able to)
}
// Company::checkAndUploadCodesToFTP($campaign_id);


//there are codes left

// Changes by Samee: Jul 27th, 2016: The email is now to be sent externally
// If Email is opted to be sent for CSC campaign
/*
if(!empty($csc_email_from))
{
	// This info should be retrieved dynamically from the database
	$from_name	=	$csc_email_from;
	$subject	=	$csc_email_subject;
	$template	=	$csc_email_template;
	// error_log("email params; " . var_export(array($from_name, $subject, $template), true));

	if(!empty($user_details['email']))
	{
		// Mailer::SendCustomCustomerCode($user_details, $custom_code, $expired_date, $from_name, $subject, $template);
		if(!Item::hasUserPrintedAnItem($user_id, $item_id))
			EmailTemplates::sendEmailAlert(EmailTemplates::$csc_email_template, $user_item_id);
	}
}
*/

//change the table so that the code isn't used again
if($count == 1){
	//there was only one code left? we just gave it away, so end the campaign
	PrintMethods::end_campaign($campaign_id); //end the campaign
}
$results = array();
$results[0] = $custom_code;
$results[1] = $expired_date;
// error_log('results in ajax-customer-supplied-code.php: ' . var_export($results, true));
echo json_encode($results);
?>
