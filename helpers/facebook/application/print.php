<?php
error_reporting(E_ALL);
require_once(dirname(dirname(dirname(__DIR__))) . '/includes/app_config.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/Database.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/BasicDataObject.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/Errors.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/Company.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/Item.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/User.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/UserItems.class.php');
// require_once(dirname(dirname(dirname(__DIR__))) . '/classes/UserActivityLog.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/ClickReferral.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/PrintErrorLogs.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/PrintMethods.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/Common.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/MobileESP.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/Location.class.php');
require_once(dirname(dirname(dirname(__DIR__))) . '/classes/CSAPI.class.php');

global $lindt_id, $powered_by_logo, $app_id, $app_version;

session_start();
// $start_time = array_sum(explode(" ", microtime()));

function parse_signed_request($signed_request, $secret) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        // decode the data
        $sig = base64_url_decode($encoded_sig);
        $data = json_decode(base64_url_decode($payload), true);

        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
                error_log('Unknown algorithm. Expected HMAC-SHA256');
                return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
                error_log('Bad Signed JSON signature at ' . __FILE__ . ' line ' . __LINE__ . '!');
                return null;
        }

        return $data;
}


function base64_url_decode($input) {
	return base64_decode(strtr($input, '-_', '+/'));
}

$db = new Database();
try{
	$db->connect();
} catch(Exception $e) {
	error_log("Exception in helpers/facebook/application/print.php: " . var_export($e, true));
	Errors::show500();
}

global $db;
global $app_id, $app_key, $app_secret;

/*
$device				= new MobileESP();
$is_iphone_ipod		= $device->DetectIphoneOrIpod();
$is_android_phone	= $device->DetectAndroidPhone();
$is_ipad			= $device->DetectIpad();
$is_android_tablet	= $device->DetectAndroidTablet();
// error_log("is_iphone_ipod: " . var_export($is_iphone_ipod, true) . ", is_android_phone: " . var_export($is_android_phone, true) . ", is_ipad: " . var_export($is_ipad, true) . ", is_android_tablet: " . var_export($is_android_tablet, true));
$is_mobile = ( !empty($is_iphone_ipod) || !empty($is_android_phone))  && empty($is_ipad) && empty($is_android_tablet);
*/
$is_mobile = Common::isMobileESP();

// error_log("REQUEST in helpers/facebook/application/print.php: ".var_export($_REQUEST, true));
$coupon_id		= !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '';
$distributor	= !empty($_REQUEST['d']) ? $_REQUEST['d']: '';
$ptype			= !empty($_GET['ptype']) ? $_GET['ptype']: '';
$user_id			= !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : '';
$sgs_uiid		= !empty($_REQUEST['sgs_uiid']) ? $_REQUEST['sgs_uiid'] : '';
$referral_id 	= !empty($_REQUEST['referral_id']) ? $_REQUEST['referral_id'] : 0;
$claimed_attempt_id = !empty($_REQUEST['claimed_attempt_id']) ? $_REQUEST['claimed_attempt_id'] : null;
$company_url	= !empty($_REQUEST['company_url']) ? $_REQUEST['company_url'] : '';
$app_name		= !empty($_REQUEST['app_name']) ? $_REQUEST['app_name']: '';
$existing_uiids = !empty($_REQUEST['existing_uiids']) ? $_REQUEST['existing_uiids']: '';
$reprint 		= !empty($_REQUEST['reprint']) && $_REQUEST['reprint'] == 1 ? $_REQUEST['reprint'] : 0;
$view_code 		= !empty($_REQUEST['view_code']) ? $_REQUEST['view_code'] : '';
$items_views_id = !empty($_REQUEST['items_views_id']) ? $_REQUEST['items_views_id'] : '';

$claimed_attempt_id = User::log_claimed_attempts($user_id , $coupon_id, '0', 'null', 'null', $distributor, 'null', $sgs_uiid);

// error_log('reprint: '. var_export($reprint, true));
$sig_parts	= '';
$user_pass	= '';
$username	= '';
$item_id = $coupon_id;

$resize_voucher = ($is_mobile || $app_name == 'web'); //  && $item_id == 2438;
$is_web_deal = $app_name == 'web';
// $resize_voucher = false;

if($is_mobile || $resize_voucher)
{
	$coupon_denied_page = "print-coupon-denied-mobile.html";
	$zoom_style = ""; // "style='zoom:3.0;'";
}
else
{
	$coupon_denied_page = "print-coupon-denied.html";
	$zoom_style = "";
}

if ($coupon_id != '') {
	$item = Item::getCouponInfo($coupon_id);
	$company_id = $item[0]['manufacturer_id'];
} elseif ($sgs_uiid != '') {
	$field = Item::GetSGSItemInfo($sgs_uiid);
	$company_id = $field['company_id'];
}
$company = new Company($company_id);
// Get 
if(empty($company_url) || $company_url == 'undefined')
	$company_url = Common::getCompanyFBAppPageLink($company_id, $fb_app_name);

$back_url = $company_url;

/*
if($app_name == 'web')
{
	$back_url = Common::getBaseURL(true) . "/smart-deals-web?company_id=" . $company->id;
	if(!empty($company->sdw_unique_code))
		$back_url .= "&c=" . $company->sdw_unique_code;
}
*/

// Setting default coupon id = 925 for testing with bees
if(isset($_REQUEST['bees']) && $_REQUEST['bees'] == 'true') $coupon_id = 925;

$log_item_id = $coupon_id; // item_id / sgs_item_id to log
$session_id = session_id();


/***************************************
*	CAMPAIGN SPECIFIC LOCATIONS
***************************************/

$location_specific_vouchers = $item[0]['location_specific_vouchers'];
$bg_img = "";
// error_log("location_specific_vouchers: " . $location_specific_vouchers);
$do_not_print = '';
if(!empty($location_specific_vouchers))
{
	
	// $location_specific_vouchers = $item->location_specific_vouchers;
	$tmp_user = new User($user_id);
	
	$facebook_location_id = $tmp_user->facebook_location_id;
	// error_log("facebook_location_id: " . $facebook_location_id);
	
	//	1.	Find User Location (State or City) using their facebook location id
	$state = Location::getAreaByFacebookLocationId($facebook_location_id, $location_specific_vouchers);
	// error_log("state: " . $state);
	$do_not_print = '1';
	if(!empty($state))
	{
		//	2.	Check whether the campaign exists for that location and the respective image voucher
		$location_specific_voucher = Item::getLocationSpecificVouchers($item_id, $state);
		// error_log("location_specific_voucher: " . var_export($location_specific_voucher, true));
	
		//	3	
	
		//	a.	If yes, set $bg_img to the above image voucher
	
		//	b.	Otherwise set $bg_img to the placeholder image (img_placeholder) and unset $voucher_layout_parts
	
		if(!empty($location_specific_voucher['bg_img']))
		{
			$bg_img = $location_specific_voucher['bg_img'];
			$do_not_print = '';
		}
	}
}

///////////////////////////////////////////////////////////////////////
//////////// CHECKING FOR BOTH EMPTY FRIENDS AND FRAUDULENT ACTIVITY

$fake_fb_account = false;
if(!empty($_SESSION['fake_fb_account']))
{
	$fake_fb_account = true;
	unset($_SESSION['fake_fb_account']);	
}

if(!empty($company->enable_user_blocking))
{
	$fraudulent_activity = User::check_for_fraudulent_activity($user_id, $coupon_id);
	
	// error_log("fake_fb_account: " . var_export($fake_fb_account, true) . ", fraudulent_activity: " . var_export($fraudulent_activity, true));
	if($fake_fb_account && $fraudulent_activity)
	{
		//	MARK USER FOR BLOCKING
		$tmp_user = new User($user_id);
		User::blockAppUser($user_id, $app_name, 'This user has no facebook friends (seems fake) and was trying to commit fraudulent activity.', $tmp_user->facebook_id);
		// And tell them they have been blocked from using the app.
		// TODOS: Change this design to show a proper message.
		$content = Common::getUserBlockedMessageContent();
		print $content;
		exit();
	}
	else if($fraudulent_activity)
	{
		User::add_suspicious_user_activity($user_id, $coupon_id, 'fraudulent_activity');
	}
	else if($fake_fb_account)
	{
		User::add_suspicious_user_activity($user_id, $coupon_id, 'no_friends');
	}
	
}

///////////////////////////////////////////////////////////////////////




//////////////////////////////////CHECKING COUPON AGE LIMIT ////////////////////////
// error_log("item->coupon_age_limit in ajax-user-register-fb: " . $item->coupon_age_limit);
$coupon_age_limit = $item[0]['coupon_age_limit'];
// error_log("coupon_age_limit in print.php: " . $coupon_age_limit);
$trigger_url = $item[0]['trigger_url'];
if(!empty($coupon_age_limit))
{
	$tmp_user = new User($user_id);
	$date_of_birth = $tmp_user->date_of_birth;
	error_log("date_of_birth in print.php: " . $date_of_birth);
	if(!empty($date_of_birth))
	{
		$user_age = Common::calculateAgeFromBirthday($date_of_birth);
		error_log("user_age in print.php: " . $user_age);

		if($user_age < $coupon_age_limit)
		{
			echo "<div $zoom_style>" . file_get_contents("../../../coupon_age_limit.html"). "</div>";
			exit();
		}
	}
}


if (!empty($sgs_uiid)) {
	$sgs_item_id = Item::GetSGSItemIdByUiid($sgs_uiid);
	$log_item_id = $sgs_item_id;
	if (!Item::SGSValidatePrint($sgs_item_id)) {
		// header("Location: /print-coupon-denied.html");
		echo "<div $zoom_style>" . file_get_contents("../../../" . $coupon_denied_page). "</div>";
		exit();
	}
} else if(!empty($coupon_id)) {
	if(empty($existing_uiids))
	{
		
		if($reprint == 1){
			$error_type = 5;
			PrintErrorLogs::logError($user_details, $error_type, $log_item_id);
		}else{
			if(!Item::canUserPrintItem($user_id, $coupon_id)) {
				// header("Location: /print-coupon-denied.html");
				echo "<div $zoom_style>" . file_get_contents("../../../" . $coupon_denied_page). "</div>";
				exit();
			}
			else if(Item::hasItemRunOutOfStock($coupon_id))
			{
				print("Sorry! There are no more prints available for this coupon; it has run out of stock!");
				exit();
			}
		}
	}
	else
	{
		// error_log("SESSION['existing_uiids'] in print: " . $_SESSION['existing_uiids']);
		// if(!isset($_SESSION['existing_uiids']))

		// error_log("COOKIE['existing_uiids'] at the top of the page: " . $_COOKIE['existing_uiids']);
		if(!isset($_COOKIE['existing_uiids']))
		{
			echo "<div style='width:600px;margin:10px auto; position: relative;'><h2>Sorry! We don't see you in our records as printing the coupon before.</h2><p>Head back to the <a href='$company_url'>" . $company->display_name. " Coupon App</a> and print your coupon from there.</p></div>";

			//log this error in user_print_logs
			$error_type = 4;
			$this_user = new User($user_id);
			PrintErrorLogs::logReprintError($this_user, $error_type, $log_item_id);
			exit();
		}
		else
		{
			// unset($_SESSION['existing_uiids']);
			// session_end();
			setcookie("existing_uiids", "", time()-3600);
			
			// error_log("COOKIE['existing_uiids'] after being supposedly unset: " . $_COOKIE['existing_uiids']);

			// Check if the coupon printing has already been retried
			$reprinted = UserItems::getNumTimesReprinted($existing_uiids);
			// error_log('reprinted: '.$reprinted);
			if($reprinted >= 1)
			{
				echo "<div style='width:600px;margin:10px auto; position: relative;'><h2>Sorry! This coupon has already been reprinted.</h2><p>If you feel you have received this in error, please proceed to the <a href='//support.coupsmart.com/'>CoupSmart Support Page</a>.</p><br /></div>";
				// log this error in user_print_logs
				$error_type = 3;
				$this_user = new User($user_id);
				PrintErrorLogs::logReprintError($this_user, $error_type, $log_item_id);
				exit();
			}
		}
	}
}

$request = null;
if (!empty($_REQUEST['signed_request'])) {
	$request =  parse_signed_request($_REQUEST['signed_request'],$app_secret);
}

$pass_qry = "select username, password from users where id = '" . Database::mysqli_real_escape_string($user_id) . "'";
$pass_rs = Database::mysqli_query($pass_qry);
if ($pass_rs && Database::mysqli_num_rows($pass_rs) == 1) {
	$pass_row = Database::mysqli_fetch_assoc($pass_rs);
	$user_pass = $pass_row['password'];
	$username = $pass_row['username'];
}
$user = new User();
$support_footer_content = DEAL_FOOTER_CONTENT;



if ($coupon_id != '') {
	// $item = Item::getCouponInfo($coupon_id);
	// $company_id = $item[0]['manufacturer_id'];
	// $company = new Company($company_id);
	$fb_app_name = 'promotions';
	
	if(!empty($item[0]['footer_content']))
		$support_footer_content = $item[0]['footer_content'];
	else if(!empty($company->support_footer_content))
		$support_footer_content = $company->support_footer_content;
	
	/*
	if ($item[0]['delivery_method'] == 7) {
		// URL forward for claim
		$user_item_id = UserItems::claimItem($coupon_id, $user_id);
		if ($user_item_id) {
			echo "<html><body><script type='text/javascript'>top.location.href = '" . $item[0]['redirect_url'] . "';</script></body></html>";
			exit();
		} else {
			// error_log('hit here line: '. (__LINE__));
			header("Location: /print-coupon-denied.html");
			exit();
		}
	}
	*/
	
	if ($item[0]['delivery_method'] == 7 || $item[0]['delivery_method'] == 15 || $item[0]['delivery_method'] == 16 || $item[0]['delivery_method'] == 17) {
		$redirect_url = $item[0]['redirect_url'];
		// URL forward for claim
		if($item[0]['delivery_method'] == 7)
		{
			$user_item_id = UserItems::claimItem($coupon_id, $user_id, NULL, $company_id);
		}
		else if($item[0]['delivery_method'] == 15 || $item[0]['delivery_method'] == 16 || $item[0]['delivery_method'] == 17)
		{
			$tmp_user = new User($user_id);
			$campaign_id = $item[0]['campaign_id'];
			$deal_id = $item[0]['deal_id'];
			
			// $validation = PrintMethods::check_user($tmp_user->facebook_id, $campaign_id);
			$validation = PrintMethods::checkUser($tmp_user->facebook_id, $deal_id);
			if(empty($validation)){
				// error_log("They haven't received one yet");
				$user_item_id = UserItems::claimItem($item_id, $user_id, $items_views_id, $company_id); //update the analytics for this user
				// error_log("user item id" . $user_item_id);
				// $custom_code = PrintMethods::get_custom_data($campaign_id, $user_item_id, $tmp_user->facebook_id); //assign them a code
				$custom_code = PrintMethods::getCustomData($deal_id, $user_item_id, $tmp_user->facebook_id);
				// error_log("custom_code" . $custom_code);
				$redirect_url = str_replace("%%promoCode%%", urlencode($custom_code), $redirect_url);
			}
			else
			{
				// error_log("They have received a code already.");
			}
		}
		if ($user_item_id) {
			echo "<html><body><script type='text/javascript'>top.location.href = '" . $redirect_url . "';</script></body></html>";
			
			if(!empty($company->is_silverpop_company))
			{
				require_once(dirname(dirname(dirname(__DIR__))) . '/classes/SilverPop.class.php');
				require_once(dirname(dirname(dirname(__DIR__))) . '/classes/UBX.class.php');
				
				if(empty($company->sp_is_ubx))
				{
					// And if the Upsert was successful, POST a UB Event
					$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $company_id);
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
	
			exit();
		} else {
			// error_log('hit here line: '. (__LINE__));
			// header("Location: /print-coupon-denied.html");
			echo "<div $zoom_style>" . file_get_contents("../../../" . $coupon_denied_page). "</div>";
			exit();
		}
	}

	$src_URL = Common::getBaseURL();

	$sig_URL = $src_URL . '/helpers/render-coupon-using-layout.php?';
	if (isset($item) && !empty($item)) {
		foreach ($item as $i=>$field){
			$bc_pt1 = "5" .substr($field['upc'], 1, 5). $field['barcode_family_code'] . $field['offer_code'];
			$bc_pt2 = "(8101)" . substr($field["barcode_co_prfx"], 0, 1) . " " . $field['barcode_offer_code'] . " " . sprintf('%02d', $field['expire_month']) . sprintf('%02d', $field['expire_year']);

			$use_share_bonus = $field['use_share_bonus'];

			// Fields specific to share coupon
			$name = $field['name'];
			$small_type = $field['small_type'];
			$offer_code = $field['offer_code'];
			$offer_value = $field['offer_value'];

			$sig_parts .= 'item_id=' . urlencode($field['item_id']) .
				';prod_name=' . urlencode($name) . // should be barcode_social_offer_service_name in case of share coupon or ptype = 2
				';customer_id=' . urlencode($field['id']) .
				';description=' . urlencode($field['description']) .
				';details=' . urlencode($field['details']) .
				';small_print=' . urlencode($small_type) . // should be social_small_type in case of share coupon or ptype = 2
				';expiration_date=' . urlencode($field['expires']) .
				';expire_month=' . urlencode($field['expire_month']) .
				';expire_year=' . urlencode($field['expire_year']) .
				';upc=' . urlencode($field['upc']) .
				';offer_code=' . urlencode($offer_code) .// should be social_offer_code in case of share coupon or ptype = 2
				';value=' . urlencode($offer_value ) .// should be social_offer_value in case of share coupon or ptype = 2
				';logo_file_name=' . urlencode($field['logo_file_name']) .
				';default_coupon_image=' . urlencode($field['default_coupon_image']) .
				';bc_pt1=' . urlencode($bc_pt1) .
				';bc_pt2=' . urlencode($bc_pt2) .
				';session_id=' . urlencode($session_id) .
				';existing_uiids=' . urlencode($existing_uiids) .
				';items_views_id=' . urlencode($items_views_id) .
				';location_specific_vouchers=' . urlencode($location_specific_vouchers) .
				';bg_img=' . urlencode($bg_img) .
				';user_id=' . urlencode($user_id);

			if (!empty($referral_id) && $referral_id != 0)
				$sig_parts .= ';referral_id=' . urlencode($referral_id);
			if ($distributor != '')
				$sig_parts .= ';d=' . urlencode($distributor);
			if (!empty($claimed_attempt_id))
				$sig_parts .= ';claimed_attempt_id=' . urlencode($claimed_attempt_id);
			
			if(!empty($view_code))
				$sig_parts .= ';view_code=' . $view_code;

			// if the coupon is not a sharing bonus coupon, then pass in an extra parameter
			if($use_share_bonus != 'Y' || $ptype == '1'){
				$sig_parts .= ';s=1';
			}
			$sig_parts .= ';sgs=0';
		}
		$sig_parts .= ';app_name=' . $app_name;

		

	}
} elseif ($sgs_uiid != '') {
	 // $field = Item::GetSGSItemInfo($sgs_uiid);
	 // $company_id = $field['company_id'];
	 $fb_app_name = 'social_gift_shop';
	 
	if(!empty($field['footer_content']))
		$support_footer_content = $field['footer_content']; // Item level footer content
	else if(!empty($field['support_footer_content']))
		$support_footer_content = $field['support_footer_content']; // Company level footer content

	 
	// error_log('FIELD: ' . var_export($field, true));
	if($field['name'] != null){
		$src_URL = Common::getBaseURL();
		$sig_URL = $src_URL . '/helpers/render-sgs-item.php?';

		$sig_parts .= ';user_id='.urlencode($user_id);
		if (!empty($claimed_attempt_id))
			$sig_parts .= ';claimed_attempt_id=' . urlencode($claimed_attempt_id);

		$sig_parts .= ';sgs=1';
		$sig_parts .= ';sgs_order_recipient_id='.urlencode($field['sgs_order_recipient_id']);
		$sig_parts .= ';sgs_uiid='.urlencode($sgs_uiid);

		$sig_parts .= ';app_name=' . $app_name;
		// $sig = md5(Database::mysqli_real_escape_string($user_pass . $username . $sig_URL . $sig_parts . $user->salt));

		// $src_URL = $sig_URL  . $sig_parts . ';sig=' . $sig;

	}else{
		// header("Location: /print-coupon-denied.html");
		echo "<div $zoom_style>" . file_get_contents("../../../" . $coupon_denied_page). "</div>";
		exit();
	 }


} else {
	echo "There was an error";
}
// $sig = md5(Database::mysqli_real_escape_string($user_pass . $username . $sig_URL . $sig_parts . $user->salt));
$arr_sig_parts = array();
parse_str($sig_parts, $arr_sig_parts);
// error_log("arr_sig_parts: " . var_export($arr_sig_parts, true));
$csapi = new CSAPI();
$sig = $csapi->generateCSAPISignature($arr_sig_parts);
$src_URL = $sig_URL  . $sig_parts . ';sig=' . $sig;
error_log("src_URL in print.php: " . $src_URL);

$browser_info = get_browser(null, true);

$ie8_or_less = 0;
if($browser_info['browser'] == 'IE' && (int)$browser_info['version'] < 9)
	$ie8_or_less = 1;

// error_log('ie8_or_less: '.var_export($ie8_or_less, true));


// Check for Tracking Info in Cookie; add entry to click_referrals if found
if(isset($_COOKIE['tracking_info']) && !empty($_COOKIE['tracking_info']))
{
	$tracking_info = $_COOKIE['tracking_info'];
	$arr_tracking_info = array();
	parse_str($tracking_info, $arr_tracking_info);
	$click_referral_id = $arr_tracking_info['click_referral_id'];
	ClickReferral::update_click_referral($click_referral_id, $user_id, $coupon_id, 'claim');

	// Wipe out the cookie as the claim is made
	setcookie("tracking_info", "", time() - (60 * 60 * 24 * 30), "/");
	unset($_COOKIE['tracking_info']);
}

// Log activity
// UserActivityLog::log_user_activity($user_id, 'claimed', $app_name, $log_item_id);
// $end_time = array_sum(explode(" ", microtime()));
//error_log("total print time: " . ($end_time - $start_time));

list($app_check_id, $app_check_secret, $facebook) = Common::CreateFacebookObject($app_name);
// error_log("helpers/appplication/print.php: app_check_id: $app_check_id, app_name: $app_name");
?>

<!-- checks to see if there needs to be custom code inserted; 1 is arbitrary so is $campaign_id -->
	<!-- load our normal HTML -->

	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" moznomarginboxes mozdisallowselectionprint>
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Please wait while your coupon is being generated below...</title>
		
		<link rel="stylesheet" href="/css/coupon_layouts.css" type="text/css" charset="utf-8" />
		<link rel="stylesheet" href="/css/coupon-print.css" media="print" type="text/css" charset="utf-8" />
		<!--[if IE]>
			<link rel="stylesheet" href="/css/coupon_layouts_ie.css" type="text/css" charset="utf-8" />
			<link rel="stylesheet" href="/css/coupon-print-ie.css" media="print" type="text/css" charset="utf-8" />
		<![endif]-->
		
		<!-- <script src="//connect.facebook.net/en_US/sdk.js"></script> -->
		

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="/js/jquery.curvycorners.min.js" charset="utf-8" type="text/javascript"></script>
		<!-- script src="//ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js" type="text/javascript" charset="utf-8"></script -->
		<script src="/js/jquery.cycle.lite.min.js" type="text/javascript" charset="utf-8"></script>
		<script src="/js/jquery.imagesloaded.min.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" src="/js/jquery.treeview.js"></script>
		<script type="text/javascript" src="/js/trigger-tracking-pixel.js?rnd=<?php print md5(uniqid());?>"></script>
		<script src="//connect.facebook.net/en_US/sdk.js"></script>
		<?php 
		$enable_pixel_tracking = $company->enable_pixel_tracking;
		if(!empty($enable_pixel_tracking)) {
			print '<script src="/js/tracking-pixel.js?rnd=' . md5(uniqid()) . '></script>';
		} 
		 ?>
		<meta http-equiv="CACHE-CONTROL" content="no-cache" />
		<?php if (isset($company) && !empty($company->fb_listing_css)) {
			echo "<style type='text/css'>\n" . $company->fb_listing_css . "\n</style>";
		} ?>
		<style type="text/css" media="print">
			@page 
			{
				size: auto;   /* auto is the initial value */
				margin: 0mm;  /* this affects the margin in the printer settings */
			}
			
			body 
			{
				background-color:#FFFFFF; 
				border: solid 1px black ;
				margin: 0px;  /* this affects the margin on the content before sending to printer */
		   	}
		</style>
	</head>
	<body>
	<div id="fb-root"></div>
	<script>(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.8&appId=<?php print $app_id; ?>";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));</script>
	<?php if ($item[0]['delivery_method'] == 10) {
		$user_item_id = UserItems::claimItem($coupon_id, $user_id);
		echo $item[0]['static_fulfillment_html'];
	} else { ?>
	
		<script type="text/javascript">
			var ie8_or_less;
			var timeout;
			var div_growing = null;
			var prev_div_height = null;
			var prev_div_width = null;
			var print_attempts = 0;
			
			var screenWidth = $(window).width();
			var screenHeight = $(window).height();
			// console.log("screenWidth: " + screenWidth + ", screenHeight: " + screenHeight);
			
			
			
			FB.init({
			  appId   : '<?php echo $app_check_id; ?>',
			  // session : <!-- ?php echo json_encode($session_user); ? -->, // don't refetch the session when PHP already has it
			  status  : true, // check login status
			  cookie  : true, // enable cookies to allow the server to access the session
			  xfbml   : true, // parse XFBML
			  oauth   : true,
			  version    : 'v<?php print $app_version; ?>'
			});
			
			var load_testing = "<?php print $company->load_testing; ?>" === "1" ? true : false;


			$(document).ready(function() {
				var is_web_deal = '<?php print $is_web_deal; ?>';
				console.log("is_web_deal: " + is_web_deal + ", screenWidth: " + screenWidth + ", screenHeight: " + screenHeight);
				if(is_web_deal == '1')
				{
					console.log('Setting new width of loading...');
					$('#loading').css('width', screenWidth + 'px');
				}
				console.log($('#loading').css('width'));
				$.ajax({
					  type: "POST",
					  url: "<?php echo $src_URL; ?>",
					 //url: "/cgi-bin/magickwand.cgi",
					  dataType: 'json',
					success: function(data) {
						console.log('data returned when printing coupon:');
						console.log(data);
						
							  // alert('data: ' + data);
						if(data != 0) {
							if(data['errors'] != undefined)
							{
								$("#loading").css('display', 'none');
								$("#loaded").css('display', 'inline');
								var err_msg = "<ul>";
								var counter = 1;
								for(err in data['errors'])
								{
									err_msg += '<li>' + data['errors'][err] + '</li>';
								}
								err_msg += "</ul>";
								$('#coupon-container').html(err_msg);
							}
							else
							{
								$('#coupon').attr('src', '/images/uploads/s3bucket/' + data);
								
								ie8_or_less = '<?php echo $ie8_or_less; ?>';
								if (ie8_or_less == '0') {
									//$("#coupon").load( function() {
										$("#loading").css('display', 'none');
										$("#loaded").css('display', 'inline');
										$("#loaded").css('height', '0px');
										$("#loaded").css('margin', '0px');
										$("#loaded").css('padding', '0px');
										$("#loaded").css('top', '0px');
									$("#coupon").load( function() {
										<?php if(!empty($company->load_testing)) { ?>
										if(top.opener)
										{
											top.opener.num_claims++;
											top.opener.time_for_claim = (Date.now() - top.opener.current_time) / 1000;
											html = 'Views: ' + top.opener.num_views + ', Claims: ' + top.opener.num_claims + ', Viewed Time: ' + top.opener.time_for_view + ' sec, Claimed Time: ' + top.opener.time_for_claim + ' sec<br />';
											top.opener.document.getElementById('div_stats').innerHTML += html;
												
											window.setTimeout(function() {
												top.opener.current_time = Date.now();
												$('#back').trigger('click');
											}, 500);
										}
										<?php } else { ?>
										{
											framePrint(window.name);
											$('#print').show();
										}
										<?php } ?>
									 });
								} else {
									// $('#loaded').imagesLoaded() {
										$("#loading").css('display', 'none');
										$("#loaded").css('display', 'inline');
										$("#loaded").css('height', '0px');
										$("#loaded").css('margin', '0px');
										$("#loaded").css('padding', '0px');
										$("#loaded").css('top', '0px');
									$('#loaded').imagesLoaded() 
									{
										framePrint(window.name);
										$('#print').show();
									}
								}
								
							}
						}
					}
				});

				// using the company url to figure the redirect due to facebook's login issue
				// this might be a more reliable method anyway
				$("#back").click(function(){
					var back_url = '<?php print $back_url;?>';
					// alert('back_url: ' + back_url);
					if(back_url == '' || back_url == undefined || back_url == null)
					{
						var sgs_uiid = '<?php print $sgs_uiid?>';
						if(sgs_uiid == '')
							self.location.href = "/canvas/coupsmart/tab";
						else
							self.location.href = "/canvas/socialgiftshop";
					}
					else
					{
						top.location.href = back_url;
					}
				});

				function framePrint(whichFrame) {
					var do_not_print = '<?php print $do_not_print?>';
					console.log('do_not_print: ', do_not_print);
					if(do_not_print == '1')
						return false;
					// parent[whichFrame].focus();
					// parent[whichFrame].print();
					
					// if(navigator.userAgent.match('CriOS'))
					//	alert('This is Chrome iOS');
						
					print_attempts++;
					
					if(navigator.userAgent.match('CriOS'))
					{
						alert("It looks like you're using the Chrome browser on an iOS device. We recommend that you use iOS Safari instead.");
						top.print();
					}
					else
					{
						window.print();
					}
					
					
					<?php if($is_web_deal) { ?>
						window.setTimeout(function() {
							console.log('resizing...');
							if(navigator.userAgent.match('CriOS'))
							{
								/*$('#coupon-container').attr('style', 'width:330px;height:200px;');
								$('#div_likebar_content').css('background-size', '315px');
								$('#div_likebar_content').css('background-position', '30px 5px');*/
								
								window.scrollTo(0, 0);
							}
							else
							{
								$('#coupon-container').attr('style', 'width:' + screenWidth + 'px;height:200px;');
								$('#div_likebar_content').css('background-size', '315px');
								<?php if($is_mobile) { ?>
								$('#div_likebar_content').css('background-position', '30px 5px');
								<?php } else { ?>
								$('#div_likebar_content').css('background-position', '35px 15px');
								<?php } ?>
							}
						},
						1000);
					<?php } ?>
					
				}

				$("#print").click(function(){
					var distributor = 0;
					var ptype = 1;
					var item_id = '<?php print $coupon_id?>';
					var user_id = '<?php print $user_id?>';
					var sgs_uiid = '<?php print $sgs_uiid?>';
					/*
					if(sgs_uiid == '') {
						window.location.reload();
					} else {
						framePrint(window.name);
					}
					*/
					//Trigger the Print Dialog Box before redering another voucher
					if(sgs_uiid == '') {
						if(print_attempts == 0) {
							print_attempts++;
							window.print();
						} else {
							window.location.reload();
						}
					} else {
						framePrint(window.name);
					}
				});
				
				/*
				$('#coupon').load(function() {
					// console.log('Coupon has been loaded!');
					var field_list = "interests.offset(0).limit(999999999),likes.offset(0).limit(999999999)";
					FB.api("/me?fields=" + field_list, function(user_details){
						$.ajax({
							type: "POST",
							url: "/helpers/ajax-user-register-fb.php",
							dataType: 'json',
							// data: {'user_details': JSON.stringify(user_details), 'item_id': item_id},
							data: {'user_details': user_details, user_id: '<?php print $user_id?>', 'mode': 'fb_interests_and_likes'},
							success: function(data){

							}
						});
					});
				});
				*/
			});
			
			/*
			function triggerURL(trigger_url)
			{
				// console.log("trigger_url in triggerURL(): " + trigger_url);
				if(trigger_url != '')
				{
					// $.ajax({
					//	url: trigger_url,
					// });
					$('#trigger_tracking_pixel').attr("src", trigger_url);
				}
			}*/
			
		</script>
		<div id="topbar" style="margin: 10px 1px;">
			<div id="loading" <?php print $is_web_deal ? " style='width:280px;height:170px;'" : "";?>>
				<div class="loading_block">
				<h1>Please wait, your voucher is loading...</h1>
				<img src="//s3.amazonaws.com/siteimg.coupsmart.com/general/loading_gray.gif" alt="loading..." />
				</div>
				<?php
					// Lindt customization
				  if (in_array($company_id, $lindt_id)) {
					echo '<br />
					<div class="lindt_footer lindt_footer_sizeoverride">
					<div class="lindt_disclaimer">
					Please be patient, during times of heavy traffic coupons may take up to 60 seconds to load.<br />
					Should you have any technical issues, please contact <a href="mailto:support@coupsmart.com">support@coupsmart.com</a><br />
					For questions or instructions about this deal, visit the <a href="//www.lindtusa.com/lindtcoupontroubleshooting" target="_blank">Lindt Troubleshooting Guide</a>.
					</div>
					<div class="powered_by_canvas_block">
					<img class="powered_by_canvas" src="' . $powered_by_logo . '" alt="Powered by CoupSmart" />
					</div>
					</div>';
					} ?>
			</div>
			<div id="loaded">
				<?php
					//	BANNER IMAGE COMES HERE
					
				?>
				<div id="coupon-container">
					<?php 
					if(!empty($item[0]['add_likebar']))
						print $is_mobile ? Common::getLikebarContentForMobile($item[0]['img_likebar'], $item[0]['facebook_page_id']) : Common::getLikebarContent($item[0]['img_likebar'], $item[0]['facebook_page_id'], $app_name);
						// print $item[0]['likebar_content'];
					?>
					<!--<div class="overlay"></div>-->
					<img alt="coupon" id="coupon" height="219" width="520" />
					<div id="divMain"></div>
				</div>
				<div class="clear"></div>

				<br />
				<!-- <img src="/images/small_logo.jpg" />
				<br>
				<h1>Thank you for using CoupSmart.</h1> -->
				<?php if(!isset($_COOKIE['existing_uiids'])) { 
						if(empty($_GET['email'])) { ?>
						<button id="back" name="back" data-items-views-id="<?php print $items_views_id;?>">Back</button>
						<?php }
						unset($_COOKIE['existing_uiids']); ?>
				<?php 	} ?>

				<?php // Lindt customization
				if (!in_array($company_id, $lindt_id)) { ?>
					<button style="display:none;" id="print" name="print" onclick="javascript:triggerURL('<?php print $trigger_url;?>');" data-items-views-id="<?php print $items_views_id;?>">Print</button><span id="print-disclaimer" style="font-size: 12px;">&nbsp;&nbsp;&nbsp;Do not close this window until you have successfully printed.</span>
				<?php } else { ?>
					<div class="lindt_footer"><a class="lindt_banner_link" href="//www.facebook.com/LindtChocolate/app_292524714092254" target="_top"><img class="lindt_banner" src="//s3.amazonaws.com/uploads.coupsmart.com/LindtBrandInteractBanner.jpg" width="555" height="79" alt="" /></a></div>

					<br />
					<div class="lindt_footer lindt_footer_sizeoverride">
					<div class="lindt_disclaimer">
					Please keep this window open until printing has completed.
					Should you have any technical issues, please stay on this page and contact <a href="mailto:support@coupsmart.com">support@coupsmart.com</a><br />
					For questions or instructions about this deal, visit the <a href="//www.lindtusa.com/lindtcoupontroubleshooting" target="_blank">Lindt Troubleshooting Guide</a>.
					</div>
					<div class="powered_by_canvas_block"><img class="powered_by_canvas" src="<?php echo $powered_by_logo; ?>" alt="Powered by CoupSmart" /></div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php } // end else for if $item[0]['delivery_method'] == 10 (static html fulfillment)
		?>

		<!-- 2015-04-03 - Adding interest drop down -->
		
		<?php if(false) {  // BY SAMEE JAN 23RD, 2016 - DISABLING THIS FOR ALL CAMPAIGNS UNTIL FURTHER NOTICE?>
		<div id="more-savings">
			<p>
					<?php
					/*foreach (User::getUserFBInterests($user_id)[0][0] as $interest)
					{
						echo "<option name='" . $interest['Interest'] . "'>" . $interest['Interest'] . "</option>";
					}*/
					$like_categories = User::getUserFBLikeCategoriesForRunningCampaigns($user_id);//getUserFBLikeCategories($user_id);
					if(empty($like_categories) || count($like_categories)<2)
					{
						$like_categories = Campaign::getRunningCampaignCategories();//User::getUserFBLikeCategories($user_id);
					}
					
					if(empty($like_categories))
					{
						?>
							<b>Want more savings?</b> Select a category to view more deals: 
							<select id="user_fb_like_category">
								<optgroup label="You have no Facebook likes"></optgroup>
							</select>
						<?php
					}
					else if(count($like_categories)==1)
					{
						?>
							<b>Want more savings?</b> Click here to view more deals: 
							<input id="user_fb_like_category" hidden="hidden" value=<?php echo "\"" . $like_categories[0] . "\"" ?> />
						<?php
					}
					else
					{
						?>
							<b>Want more savings?</b> Select a category to view more deals: 
							<select id="user_fb_like_category">
								<?php
								for($i=0; $i<count($like_categories); $i++) {
									if($i==0) {
										echo "<option name=\"" . $like_categories[$i] . "\" selected=\"selected\">" . htmlentities($like_categories[$i]) . "</option>";
									} else {
										echo "<option name=\"" . $like_categories[$i] . "\">" . htmlentities($like_categories[$i]) . "</option>";
									}
								} ?>
							</select>
						<?php
					}
					?>
				<?php error_log('app_url = ' . $app_url); ?>
				<a id="interests-deals-btn" class='footer-privacy-policy'><b>More Deals!</b></a>
				<script type="text/javascript">
					$(document).ready(function(){
						$('#interests-deals-btn').click(function(){
							var url = "/helpers/ajax-session-deals-by-like-category.php?like_category=" + encodeURIComponent($('#user_fb_like_category').val());
							$.get(url, function(result){
								console.log(result);
								if (top != self)
								{
									top.window.location = '<?php echo $app_url; ?>';
								}
							});
							//top.window.location = '<?php echo $app_url; ?>/?like_category=' + encodeURIComponent($('#user_fb_like_category').val());
							// redirect the user to the dashboard with the selected interest as an argument
						});
					});
				</script>
			</p>
		</div>
		<?php } ?>
		<!-- END 2015-04-03 - Adding interest drop down -->
		
		<div id='support-footer'>
			<?php print $support_footer_content;?>
			<!--
			<span class="vitalicious">To learn more, please visit <a href="http://bit.ly/VitaCoup" target="_blank" style="color:#B80200;">Vitalicious.com</a>.<br></span>
			<span class="striderite">Watch the <a href="http://youtu.be/WlcEj6mhVSA" target="_blank">Stride Rite Made 2 Play Collection Video</a> now!<br></span>
			<span>Need help getting this deal? Visit our <a href='http://support.coupsmart.com' target='_blank' id='support-link'>Support Center</a>.</span>
			<span style="float: right;"><a href='//coupsmart.com/privacy#toc' target='_blank' id='toc-link'>Terms Of Use</a></span -->
			<?php include dirname(dirname(dirname(__DIR__))) . '/components/app-support-footer.php'; ?>
		</div>
		<!--<img id="trigger_tracking_pixel" style="width:1px; height:1px; visibility: hidden;" />-->
	</body>
</html>
