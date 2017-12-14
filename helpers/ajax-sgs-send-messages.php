<?php

require_once (dirname(__DIR__) . '/includes/app_config.php');
require_once (dirname(__DIR__) . '/includes/email_parsing.php');
require_once (dirname(__DIR__) . '/includes/facebook-php-sdk/src/facebook.php');
require_once (dirname(__DIR__) . '/includes/UUID.php');
// error_reporting(0);
function cs_autoloader($class) {
	if (file_exists(dirname(__DIR__) . '/classes/' . $class . '.class.php')) {
		require_once(dirname(__DIR__) . '/classes/' . $class . '.class.php');
	} else {
		error_log('class ' . $class . ' not found in ' . __FILE__ . ' on line ' . __LINE__);
	}
}
spl_autoload_register('cs_autoloader');

/*
require_once ('../classes/Common.class.php');
require_once ('../classes/SGS_Item.class.php');
require_once ('../classes/SGS_Order_Recipient.class.php');
require_once ('../classes/SGS_Order.class.php');
require_once ('../classes/User.class.php');
require_once ('../classes/UserActivityLog.class.php');
*/


$db = new Database();
try{
	$db->connect();
} catch(Exception $e) {
	Errors::show500();
}
global $db;
global $app_version;

header('Content-Type: application/json; charset=utf-8');

// error_log("POST in ajax-sgs-send-messages: ".var_export($_POST, true));

$form_data 		= !empty($_POST['form_data'])		? $_POST['form_data'] : '';
$app_link		= !empty($_POST['app_link'])		? $_POST['app_link'] : '';
$referral_code	= !empty($_POST['referral_code'])	? $_POST['referral_code'] : '';
$user_id		= !empty($_POST['user_id'])			? $_POST['user_id'] : '';


$arr_form_data = array();
parse_str($form_data, $arr_form_data);
// error_log('arr_form_data: ' . var_export($arr_form_data, true));

$access_token		= $arr_form_data['hdn_access_token'];
$op 					= $arr_form_data['hdn_share_op'];
$message 			= $arr_form_data['txt_share_message'];
$item_id 			= !empty($arr_form_data['hdn_item_id']) ? $arr_form_data['hdn_item_id'] : (!empty($_POST['item_id']) ? $_POST['item_id'] : '');
$item_name 			= $arr_form_data['hdn_sgs_item_name'];
$item_description	= $arr_form_data['hdn_sgs_item_description'];

$company_id 		= !empty($_POST['company_id']) ? $_POST['company_id'] : $arr_form_data['hdn_company_id'];
$app_name 			= !empty($_POST['app_name']) ? $_POST['app_name'] : $arr_form_data['hdn_app_name'];



// Getting facebook object for the SGS App
$app_names = array('fan_deals' => 'promotions', 'sgs' => 'social_gift_shop', 'convercial' => 'promotions', 'web' => 'promotions');
list($app_id, $app_secret, $facebook) = Common::CreateFacebookObject($app_names[$app_name]);

$sender_id 			= !empty($_POST['sender_id']) ? $_POST['sender_id'] : $arr_form_data['hdn_facebook_user_id'];
if($sender_id == '0' || empty($sender_id))
{
	// if(!empty($access_token)) // op 
	{
		// error_log("Getting sender id via access_token....");
		$sender_id = $facebook->getUser();
	}
}
// error_log('sender_id: ' . var_export($sender_id, true));

$parent_id = !empty($arr_form_data['hdn_referral_id']) ? $arr_form_data['hdn_referral_id'] : 0;
$level = 0;
if(!empty($parent_id))
{
	$referral_info = Item::getReferralInfoById($parent_id);
	$level = $referral_info['level'];
	if(!empty($sender_id) && $sender_id != $referral_info['sender_id'])
		$level++;
}


$picture = $arr_form_data['hdn_picture'];
if($app_name != 'web')
{
	// $referral_code = md5(UUID::v4());
	// $app_link .= '&app_data={"referral_code": "' . $referral_code. '"}';
}
$params = array(
	'method'			=> 'post',
	'link' 			=> $app_link,
	'message' 		=> $message,
	// 'access_token' => $access_token,
	'name' 			=> $item_name,
	'description'	=> $item_description,
	'caption' 		=> $app_link,
);

if(!empty($picture) && $picture != 'none')
	$params['picture'] = $picture;

if(!empty($access_token))
	$params['access_token'] = $access_token;

// This deals with the case when the response is returned from the FB dialog along with the form data
$response = !empty($_POST['response']) ? $_POST['response'] : null;

switch($op)
{

	//	1.		POSTING TO MY OWN WALL
	case 'own_wall':
		try
		{
			$response = $facebook->api('/v' . $app_version . '/me/feed', 'POST', $params);
			// error_log("response after posting to own wall: ".var_export($response, true));
		}
		catch(Exception $e)
		{
			error_log("An exception occurred while posting to own wall: " . $e->getMessage());
		}
		break;


	//	2.		SHARING TO MY OWN PAGE
	case 'own_page':
		$my_page_id = $arr_form_data['hdn_selected_object_id'];
		try
		{
			$response = $facebook->api('/v' . $app_version . '/' . $my_page_id . '/feed', 'POST', $params);
			// error_log("response after posting to page $my_page_id: ".var_export($response, true));
		}
		catch(Exception $e)
		{
			error_log("An exception occurred while posting to page $my_page_id: " . $e->getMessage());
		}
		break;


	//	3.		SHARING TO MY GROUP
	case 'own_group':
		$my_group_id = $arr_form_data['hdn_selected_object_id'];
		try
		{
			$response = $facebook->api('/v' . $app_version . '/' . $my_group_id . '/feed', 'POST', $params);
			// error_log("response after posting to group $my_group_id: ".var_export($response, true));
		}
		catch(Exception $e)
		{
			error_log("An exception occurred while posting to group $my_group_id: " . $e->getMessage());
		}
		break;
	
	//	4.		SHARING TO FRIEND'S WALL
	/*
	case 'friend_wall':
		$my_friend_id = $arr_form_data['hdn_selected_object_id'];
		try
		{
			$response = $facebook->api('/v' . $app_version . '/' . $my_friend_id . '/feed', 'POST', $params);
			error_log("response after posting to friend $my_friend_id: ".var_export($response, true));
		}
		catch(Exception $e)
		{
			error_log("An exception occurred while posting to friend $my_friend_id: " . $e->getMessage());
		}
		break;
	*/

}
$result = '0';
// error_log('response in ajax-sgs-send-messages: ' . var_export($response, true));
if(isset($response['post_id']) || isset($response['id']) || $response['success'] == 'true')
{
	$result = '1';
	
	// Insert a referral row
	$referral_data = array();
	
	$referral_data['sender_id'] 	= $sender_id;
	if(!empty($arr_form_data['hdn_selected_object_id']))
		$referral_data['receipient_id'] = $arr_form_data['hdn_selected_object_id'];
	
	$referral_data['request_id'] 		= empty($response['post_id']) ? (!empty($response['id']) ? $response['id'] : '') : $response['post_id'];
	$referral_data['item_shared'] 	= $item_id;
	$referral_data['created'] 			= date('Y-m-d H:i:s');
	$referral_data['status'] 			= 'pending';
	$referral_data['share_method'] 	= $op;
	$referral_data['share_msg'] 		= Database::mysqli_real_escape_string($message);
	
	// list($parent_id, $level) = Stats::getReferralParentIDAndLevel($item_id, $sender_id);
	$referral_data['parent_id'] 		= $parent_id;
	$referral_data['level'] 			= $level;
	$referral_data['company_id'] 		= $company_id;
	$referral_data['app_name']			= $app_name;
	$referral_data['url_shared']		= $app_link;
	$referral_data['referral_code']		= $referral_code;
	
	if(empty($user_id))
	{
		$facebook_user = User::findUserByFacebookId($sender_id);
		if(!empty($facebook_user->id))
		{
			// UserActivityLog::log_user_activity($facebook_user->id, 'shared', $app_name, $item_id);
			$referral_data['user_id']	= $facebook_user->id;
		}
	}
	else
	{
		$referral_data['user_id']	= $user_id;
	}
	
	$referral_id = BasicDataObject::InsertTableData("referrals", $referral_data);
	$result = !empty($referral_id) ? $referral_id : 0;
	// Logging to the user_activity_log table
	
}




echo json_encode($result);
exit();
?>
