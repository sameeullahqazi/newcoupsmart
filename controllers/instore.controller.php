<?php
require_once(dirname(__DIR__) . '/includes/email_parsing.php');
require_once(dirname(__DIR__) . '/includes/UUID.php');
	
global $connect_app_id, $connect_app_secret, $app_id, $app_secret, $app_version;
global $salt_walkin_complete;


// serror_log("GET in instore controller: " . var_export($_GET, true));
$load_testing_enabled = $_GET['mode'] == 'load_testing';
$called_by_clicking_done = false;
if(!empty($_GET['btn_done']) && $_GET['btn_done'] == '1')
{
	
	$items_views_session_id = Item::getItemsViewsSessionId($_GET['items_views_id']);
	if($items_views_session_id != session_id())
		die("Invalid page access attempt or session expired!");
	$called_by_clicking_done = true;
}

$item_id = $_GET['p'];

$permissions = 'email,user_birthday,user_location,user_relationships,user_likes,user_friends';
$field_list = "id,first_name,last_name,gender,email,birthday,location,relationship_status,friends.offset(0).limit(999999)"; 
$access_token = '';
$no_deal = '';
$items_views_id = '';
$json_user_details = '';
$liked_page = false;
$facebook_user_id = '';
$img = '';
$fb_authentication_valid = 0;
$user_id = '';
$signature = '';

$code = $_REQUEST["code"];
$redirect_uri = Common::getBaseURL(true) . "/instore?p=" . $item_id;
$app_link = $redirect_uri;

$referral_id = '';
$shortened_url_hit_id = '';
if(isset($_GET['app_data']))
{
	
	$app_data = $_GET['app_data'];
	$arr_app_data = explode('_', $app_data, 2); // 
	if($arr_app_data[0] == 'referralcode')
	{
		$referral_code = $arr_app_data[1];
		if(!empty($referral_code))
		{
			$referral_info = Item::getReferralInfoByCode($referral_code);
			$referral_id = $referral_info['id'];
			$_SESSION['referral_id_link'] = $referral_id;
			print "<script type='text/javascript'>top.location.href='$redirect_uri'</script>";
			exit();
		}
	}
	else if($arr_app_data[0] == 'suhi')	//	Shortened URL Hit Id
	{
		$shortened_url_hit_id = $arr_app_data[1];
		if(!empty($shortened_url_hit_id))
		{
			$_SESSION['shortened_url_hit_id'] = $shortened_url_hit_id;
			print "<script type='text/javascript'>top.location.href='$redirect_uri'</script>";
			exit();
		}
	}
	$url_query_string .= "&app_data=" . $_GET['app_data'];
}

if(!empty($_SESSION['referral_id_link']))
{
	$referral_id = $_SESSION['referral_id_link'];
	unset($_SESSION['referral_id_link']);
}

if(!empty($_SESSION['shortened_url_hit_id']))
{
	$shortened_url_hit_id = $_SESSION['shortened_url_hit_id'];
	unset($_SESSION['shortened_url_hit_id']);
}






$claim_code = 1;

////////////////////////////////////////////////////////////
//	1.	GET COUPON INFO
/////////////////////////////////////////////////////////////

$item_info 			= Item::getMOItemInfo($item_id);
// error_log("item_info in instore controller: " . var_export($item_info, true));

$deal_id			= $item_info['deal_id'];
$company_id			= $item_info['company_id'];
$mo_header_caption	= $item_info['mo_header_caption'];
$company_name		= $item_info['company_name'];
$bg_img 			= $item_info['default_coupon_image'];
$white_label_css	= $item_info['white_label_css_2'];
$details			= $item_info['small_type'];
$deal_name			= $item_info['deal_name'];
$offer_value		= $item_info['offer_value'];
$expiry				= $item_info['expiry'];
$delivery_method	= $item_info['delivery_method'];
$facebook_page_id	= $item_info['facebook_page_id'];
$status				= $item_info['status'];
$csc_email_template	= $item_info['csc_email_template'];
$mobile_placeholder_image	= $item_info['mobile_placeholder_image'];
$instore_email_print_btn	= $item_info['instore_email_print_btn'];
$trigger_url		= $item_info['trigger_url'];

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
$enable_pixel_tracking = $item_info['enable_pixel_tracking'];
$use_bundled_coupons	= $item_info['use_bundled_coupons'];
$use_location_based_deals	= $item_info['use_location_based_deals'];


Common::getLocationBasedDealsContentMO($company_id, $use_location_based_deals, $app_link, $delivery_method, $app_link);

$btn_captions = array(
	'btn_get_offer_code' => array(
		'6' => 'Get Offer',
		'12' => 'Get Code',
	), 
);
$caption_get_offer = $delivery_method == '12' ? 'Get Code' : 'Get Offer';


//	If coupon is not running, just redirect it to the company facebook page
if($status != 'running')
{
	// exit();
}


///////////////////////////////////////////////////////////////
//	2.	GET FACEBOOK INFO
/////////////////////////////////////////////////////////
$isPermissionRejected = false;

$hdn_form_submit = !empty($_POST['hdn_form_submit']) ? $_POST['hdn_form_submit']: '';
$passed_state = "ASdi2g3ugjvhgfdsiuwetyfykajsg";
// $user_facebook_id = $facebook->getUser();
// error_log("user_facebook_id: " . $user_facebook_id);
	
try
{
	
	if($hdn_form_submit == '1')
	{
		// $login_url = $facebook->getLoginUrl(array('redirect_uri' => $redirect_uri, 'scope' => $permissions));
		// header("Location: " . $login_url);
		
		
		$login_url = "https://www.facebook.com/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . $redirect_uri ."&scope=" . $permissions. "&state=" . $passed_state; 
		// header("Location: " . $auth_url);
		// error_log("should redirect to: " . $auth_url);
		// return Redirect::to($auth_url); // Redirect::to($auth_url);
		print '<script type="text/javascript">top.location.href="' . $login_url . '"</script>';
		exit();

	}
	else if($_GET['error'] == 'access_denied' && $_GET['error_reason'] == 'user_denied')
	{
		$_SESSION['isPermissionRejected'] = 1;
		// Item::updatePermissionsRejected($item_id);
		error_log("items_views_id before rejecting app: " . $_SESSION['items_views_id']);
		header("Location: " . $redirect_uri);
		exit();
	}
	else if(!empty($_GET['state']))
	{
		// $access_token = $facebook->getAccessToken(); // Gives you current user's access_token
		// error_log("State in $_GET: " . $_GET['state']);
		$access_token_url = 'https://graph.facebook.com/v' . $app_version . '/oauth/access_token?client_id=' . $app_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $app_secret . '&code=' . $_GET['code'] . '';
		
		$response = file_get_contents($access_token_url);
		$response = json_decode($response, true);
		// error_log ("RESPONSE obtained for access token: " . var_export($response, true));
		$access_token = $response['access_token'];
		// error_log ("access_token obtained: " . var_export($access_token, true));
		$_SESSION['access_token'] = $access_token;
		// $granted_permissions = $facebook->api('/v' . $app_version . '/me/permissions?access_token=' . $access_token);
		$granted_permissions_url = 'https://graph.facebook.com/v' . $app_version . '/me/permissions?access_token=' . $access_token;
		$granted_permissions_response = file_get_contents($granted_permissions_url);
		$granted_permissions = json_decode($granted_permissions_response, true);
		// error_log("granted_permissions: " . var_export($granted_permissions, true));
		$required_permissions = explode(',', $permissions);
		$isPermissionRejected = Common::isPermissionRejected($granted_permissions, $required_permissions);
		// error_log("isPermissionRejected: " . $isPermissionRejected);
		if($isPermissionRejected)
		{
			// $deleted_permissions = $facebook->api("/v" . $app_version . "/me/permissions?access_token=$access_token","DELETE");
			$deleted_permissions_url = 'https://graph.facebook.com/v' . $app_version . '/me/permissions?method=DELETE&access_token=' . $access_token;
			$deleted_permissions_response = file_get_contents($deleted_permissions_url);
			$deleted_permissions = json_decode($deleted_permissions_response, true);
			// error_log("deleted_permissions: " . var_export($deleted_permissions, true));
			error_log("items_views_id before rejecting permissions: " . $_SESSION['items_views_id']);
			// Item::updatePermissionsRejected($item_id);
			$_SESSION['isPermissionRejected'] = 1;
			header("Location: " . $redirect_uri);
			exit();
		}
		else
		{
			$_SESSION['isPermissionRejected'] = 0;
			// Item::updatePermissionsRejected($item_id, null, true);
			header("Location: " . $redirect_uri);
			exit();
		}
	}
}
catch(Exception $e)
{
	error_log("Exception when gettin Facebook Login URL: " . var_export($e->getMessage(), true));
	
}		

// error_log("_SESSION['isPermissionRejected']: " . var_export($_SESSION['isPermissionRejected'], true));
if(isset($_SESSION['isPermissionRejected']))
{
	$session_is_permission_rejected = $_SESSION['isPermissionRejected'];
	unset($_SESSION['isPermissionRejected']);
	if(!empty($session_is_permission_rejected))
	{
		$isPermissionRejected = true;
		Item::updateItemsViewsColumn('permissions_rejected', '1', $_SESSION['items_views_id']);
	}
	else
	{
		Item::updateItemsViewsColumn('permissions_rejected', '0', $_SESSION['items_views_id']);
		// $access_token = $facebook->getAccessToken(); // Gives you current user's access_token
		$access_token = $_SESSION['access_token'];
		// error_log ("access_token from session: " . var_export($access_token, true));
		unset($_SESSION['access_token']);
		// $me = $facebook->api('/v' . $app_version . '/me?access_token=' . $access_token . '&fields=' . $field_list); // Gets User's information based on permissions the user has granted to your application.
		$fb_data_url = 'https://graph.facebook.com/v' . $app_version . '/me?access_token=' . $access_token . '&fields=' . $field_list;
		$me_response = file_get_contents($fb_data_url);
		$me = json_decode($me_response, true);
		// error_log("me in instore: " . var_export($me, true));	
		$fb_authentication_valid = 1;
		$json_user_details = json_encode($me);
		if(empty($json_user_details)) $json_user_details = '[]';
		$log_data = array_intersect_key($me, array_flip(array('id', 'name', 'first_name', 'last_name', 'email')));
		$quick_user = new User();
		
		// error_log("item_info['coupon_age_limit'] in instore controller: " . $item_info['coupon_age_limit']);
		if(!empty($item_info['coupon_age_limit']))
		{
			// error_log("me['birthday'] in instore controller: " . $me['birthday']);
			if(!empty($me['birthday']))
			{
				$user_age = Common::calculateAgeFromBirthday(Common::parse_fb_date($me['birthday']));
				// error_log("user_age in instore controller: " . $user_age);
				if($user_age < $item_info['coupon_age_limit'])
				{
					// echo json_encode(array('redirect' => '/coupon_age_limit.html', 'error'=>'denied'));
					header("Location: coupon_age_limit.html");
					exit();
				}
			}
		}
		
		if(!empty($me['email']))
			$user = $quick_user->quick_register_facebook_authenticate($me, null);
		else
			$user = $quick_user->quick_register_facebook_authenticate_no_email($me);
	
		$user_id = $user->id;
		
		// error_log('user if the user has already liked the page= ' . var_export($user, true));
			
		if(!empty($use_bundled_coupons))
		{
			$_SESSION['bundled_coupons_code'] = UUID::v4();
		}

		/////////////////////////////////////////////////////////////////////////////////
		/////////	MARKING A USER AS HAVING ZERO FRIENDS
		/////////////////////////////////////////////////////////////////////////////////

		// error_log("user_details['friends'] in helpers/ajax-user-register-fb.php: " . var_export($user_details['friends'], true));
		if(isset($me['friends']['summary']['total_count']))
			if(is_numeric($me['friends']['summary']['total_count']))
				if(empty($me['friends']['summary']['total_count']))
					$_SESSION['fake_fb_account'] = 1;

		if (is_object($user) && !empty($user) ) {

			$facebook_id = $user->facebook_id;
					
			User::getAndStoreUserInterestsAndLikes($user_id, $access_token, $me['id'], $item_id, $company_id);
			$access_token_data = array(
				'access_token'	=> $access_token,
				'app_id'		=> $app_id,
				'app_name'		=> 'convercial',
				'facebook_id'	=> $me['id'],
				'permissions'	=> $permissions,
				'object_type'	=> 'user',
				'object_id'		=> $user_id,
			);
			Common::checkAndStoreFBAccessToken($access_token_data);
		}
		
		if(!empty($_SESSION['items_views_id']))
		{
			$items_views_id = $_SESSION['items_views_id'];
			unset($_SESSION['items_views_id']);
			
			Item::update_user_id_in_item_view($user_id, $items_views_id);
		}
		
		$csapi = new CSAPI();
		$query_string_params = array('item_id' => $item_id, 'user_id' => $user_id, 'items_views_id' => $items_views_id, 'redeem_coupon_code' => 1, 'r' => uniqid());
		$signature = $csapi->generateCSAPISignature($query_string_params);
		$query_string_params['sig'] = $signature;
		// error_log("query_string_params: " . var_export($query_string_params, true));
		$str_query_string = http_build_query($query_string_params);
		// error_log("items_views_id when permissions granted: " . $items_views_id);
		
		
	}
	
}
else
{
	// $user_facebook_id = $facebook->getUser();
	if(!empty($user_facebook_id))
	{
		$user = new User();
		$user->Select("facebook_id='$user_facebook_id'");
		$user_id = $user->id;
	}
	
	if($load_testing_enabled)
	{
		$user_id = $_GET['load_testing_user_id'];
	}
	
	$fb_authentication_valid = 0;
	if($called_by_clicking_done)
		$items_views_id = $_GET['items_views_id'];
	else
		$items_views_id = Item::addItemView($item_id, $company_id, $user_id, null, null, $referral_id, $shortened_url_hit_id);
	$_SESSION['items_views_id'] = $items_views_id;
	// error_log("items_views_id when page loaded: " . $items_views_id);
	// error_log("user_id when page loaded in instore: " . $user_id);
	
	if(!empty($is_silverpop_company))
		if(empty($sp_is_ubx))
			$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $company_id);

	if($is_et_company == '1')
		ExactTarget::checkAndUpsertSubscriber($user_id, $company_id, array('action' => 'claim', 'item_id' => $item_id));

	if($is_mailchimp_company == '1' && !empty($mc_list_id))
		MailChimp::checkAndUpsertMember($user_id, $mc_list_id, $mc_api_key, $company_id);

	if($is_campaign_monitor_company == '1' && !empty($cm_list_id))
		CampaignMonitor::checkAndUpsertMember($user_id, $cm_list_id, $cm_client_id, $cm_api_key, $deal_id, $company_id);
}

if(!empty($_SESSION['loc_zip_code']))
	unset($_SESSION['loc_zip_code']);

if(!empty($_SESSION['loc_dma']))
	unset($_SESSION['loc_dma']);
	
if(!empty($_SESSION['loc_company_id']))
	unset($_SESSION['loc_company_id']);
// error_log("fb_authentication_valid: " . $fb_authentication_valid);

?>