<?php
// error_reporting(0);

// require_once(dirname(__DIR__) . "/helpers/address-verify.php");
require_once(dirname(__DIR__) . '/includes/app_config.php');
require_once(dirname(__DIR__) . '/includes/email_parsing.php');
function autoload_classes($class_name)
{
    $file = dirname(__DIR__) . '/classes/'.$class_name.'.class.php';
    
    if (file_exists($file))
    {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');

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
// error_log('post in ajax-instore: '. var_export($_POST, true));
$operation = !empty($_POST['operation']) ? $_POST['operation'] : '';
$item_id = !empty($_POST['item_id']) ? $_POST['item_id'] : '';
$company_id = !empty($_POST['company_id']) ? $_POST['company_id'] : '';
$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : '';
$location_id = !empty($_POST['location_id']) ? $_POST['location_id'] : '';
$delivery_method = !empty($_POST['delivery_method']) ? $_POST['delivery_method'] : '';
$items_views_id = !empty($_POST['items_views_id']) ? $_POST['items_views_id'] : '';
$new_referral_id 	= !empty($_POST['new_referral_id']) ? $_POST['new_referral_id'] : 0;

switch($operation)
{
	case 'user_register':
		global $connect_app_id, $connect_app_secret, $app_id, $app_version, $app_secret;
		$facebook = new Facebook(array(
			'appId' => $app_id, // $connect_app_id,
			'secret' => $app_secret, // $connect_app_secret,
			'cookie' => true,
			'version' => 'v' . $app_version
		));

		$permissions = $_POST['permissions'];
		$access_token = $_POST['access_token'];
		try {
			$me = $facebook->api('/me'); // Gets User's information based on permissions the user has granted to your application.
			
			$quick_user = new User();
			if(!empty($me['email']))
				$quick_result = $quick_user->quick_register_facebook_authenticate($me, null);
			else
				$quick_result = $quick_user->quick_register_facebook_authenticate_no_email($me);


			//error_log('quick_result if the user has already liked the page= ' . var_export($quick_result, true));

			if (is_object($quick_result) && !empty($quick_result) ) {

				$user_id = $quick_result->id;
				if(!empty($items_views_id))
					Item::update_user_id_in_item_view($user_id, $items_views_id);
				
				if(!empty($new_referral_id))
					Item::update_user_id_in_referrals($user_id, $new_referral_id);
					
				$has_email = is_rfc3696_valid_email_address($quick_result->email) ? '1' : '0';
				$start_time = time();
				User::getAndStoreUserInterestsAndLikes($user_id, $access_token, $me['id'], $item_id, $company_id);
				$time_interests_likes = time() - $start_time;
				// error_log("time to store interests and likes: " . (time() - $start_time));
			
				$access_token_data = array(
					'access_token'	=> $access_token,
					'app_id'		=> $app_id,
					'app_name'		=> 'convercial',
					'facebook_id'	=> $me['id'],
					'permissions'	=> $permissions,
					'object_type'	=> 'user',
					'object_id'		=> $user_id,
				);
			
				$start_time = time();
				Common::checkAndStoreFBAccessToken($access_token_data);
				$time_access_token = time() - $start_time;
				error_log("time to store access token: " . (time() - $start_time));
				
				// Update user_id in the items_views table here.
				Item::updateItemViewsUserId($item_id, $user_id);
				
				echo json_encode(array('quick_result' => $quick_result, 'has_email' => $has_email));

			}
		} catch (Exception $e) {
			echo(json_encode(array('error' => "Exception when trying to get FB user info: " . $e->getMessage())));
		}
		
		break;
		
	case 'email':
		// $user = new User($user_id);
		// error_log('session p: '. var_export($item_id, true));
		// $success_email_sent = Mailer::email_coupon($user, $item_id, $location_id);
		$param = array('user_id' => $user_id, 'item_id' => $item_id, 'location_id' => $location_id, 'smart_link_id' => $_POST['smart_link_id'], 'items_views_id' => $items_views_id);
		$new_email = !empty($_POST['email_different']) ? $_POST['email_different'] : null;
		if($delivery_method == '6')
		{
			$email_template = new EmailTemplates(EmailTemplates::$mobile_offer_sent);
	 		EmailTemplates::sendEmailAlert(EmailTemplates::$instore_save_later, $param, false, $new_email, $email_template);
	 	}
	 	else if($delivery_method == '12')
	 	{
	 		$user_details = !empty($_POST['user_details']) ? $_POST['user_details'] : '';
	 		$access_token = !empty($_POST['access_token']) ? $_POST['access_token'] : '';
	 		$permissions = !empty($_POST['permissions']) ? $_POST['permissions'] : '';
	 		$items_views_id = !empty($_POST['items_views_id']) ? $_POST['items_views_id'] : '';
	 		list($custom_code, $expiry_date) = User::getCustomerSuppliedCode($user_details, $item_id, $access_token, $permissions, $items_views_id);
	 		
	 		$param['custom_code'] = $custom_code;
	 		$param['expiry_date'] = $expiry_date;
	 		EmailTemplates::sendEmailAlert(EmailTemplates::$csc_mobile_offers, $param, false, $new_email);
	 	}
		// unset($_SESSION['p']);
		// header("Location: walkin-thankyou?email_sent=1");
		// Just sending an email shouldn't result in a claim!
		// $img_url = generateUiid($user_id, $item_id, $location_id);
		echo json_encode(true);
		
		break;

	case 'instore_email':
		try{
			$email				= !empty($_POST['email']) ? $_POST['email'] : '';
			$csc_email_template	= !empty($_POST['csc_email_template']) ? $_POST['csc_email_template'] : EmailTemplates::$csc_email_template;
			Item::updateItemsViewsColumn('proceeded_with_print_email', '1', $items_views_id);
			if($delivery_method == '6')
			{
				$param = array('user_id' => $user_id, 'item_id' => $item_id, 'location_id' => $location_id, 'smart_link_id' => $_POST['smart_link_id'], 'items_views_id' => $items_views_id);
				$new_email = !empty($_POST['email_different']) ? $_POST['email_different'] : null;
				$email_template = new EmailTemplates(EmailTemplates::$mobile_offer_sent);
				$email_sent = EmailTemplates::sendEmailAlert(EmailTemplates::$instore_save_later, $param, false, $new_email, $email_template);
			}
			else if($delivery_method == '12')
			{
				$email_params = array('user_id' => $user_id, 'item_id' => $item_id, 'email' => $email, 'items_views_id' => $items_views_id);
				$email_sent = EmailTemplates::sendEmailAlert($csc_email_template, $email_params);
			}
			if(!$email_sent)
			{
				echo json_encode(array('error' => "Sorry! Email couldn't be sent successfully. (Perhaps you're not running this on Production or coupsmart.com)"));
				exit();
			}
			else
			{
				echo json_encode(array('msg' => "Email successfully sent"));
				exit();
			}
		}
		catch(Exception $e)
		{
			echo json_encode(array('error' => $e->getMessage()));
			exit();
		}
		break;
		
	case 'SAVE_BARCODE':
		$qualify_seek_scan = !empty($_POST['qualify_seek_scan']) ? $_POST['qualify_seek_scan'] : NULL;
		$product_name = !empty($_POST['product_name']) ? $_POST['product_name'] : '';
		$size = !empty($_POST['size']) ? $_POST['size'] : '';
		$upc = !empty($_POST['upc']) ? $_POST['upc'] : '';
		$user_id = $_POST['user_id'];

		$rows_affected = ProductLookup::AddUnrecognizedBarcodeToQueue($user_id, $upc, $product_name, $size, $qualify_seek_scan);
		echo json_encode($rows_affected);

		break;
	case 'add_item':
		
		$img_url = generateUiid($user_id, $item_id, $location_id);
		echo json_encode($img_url);
		break;
	
	case 'track_button_click':
		$column_name = $_POST['column_name'];
		$column_value = $_POST['column_value'];
		Item::updateItemsViewsColumn($column_name, $column_value, $items_views_id);
		break;
	
	case 'submit-info':
		// $form_data = array();
		// parse_str($_POST['form_data'], $form_data);
		$item_id = $_POST['item_id'];
		$user_id = $_POST['user_id'];
		$form_data = $_POST['form_data'];
		$errors = Item::ValidateInstoreFormData($form_data);
		if(empty($errors))
		{
			$table_data = array(
				'user_id' => $user_id,
				'item_id' => $item_id,
				'form_data' => json_encode($form_data),
				'session_id' => session_id(),
			);
			BasicDataObject::InsertTableData('instore_form_data', $table_data);
			print json_encode(1);
		}
		else
		{
			print json_encode(array('errors' => $errors));
		}
		break;
		

}

function generateUiid($user_id, $item_id, $location_id){
	error_log('hit here line: ' . (__LINE__));
	$uiid = UserItems::generate_unique_uiid();
	$user_id = Database::mysqli_real_escape_string($user_id);
	$insert = "insert into user_items (uiid, item_id, user_id, walkin_location_id, date_committed, delivery_center_arrival, date_sent, expected_delivery_date) VALUES ('" . Database::mysqli_real_escape_string($uiid) . "', '$item_id', $user_id, '$location_id', NOW(), NOW(), NOW(), NOW() )";
	$result = Database::mysqli_query($insert);
	if (Database::mysqli_error())
	{
		error_log('mysql error ' . Database::mysqli_errno() . ' on user_items insert: ' . Database::mysqli_error() . ' ---- Query was: ' . $insert);
	}
	else
	{
		$user_item_id = Database::mysqli_insert_id();
	
		/*
		$sql = "update items set committed = ifnull(committed, 0) + 1 where id='". Database::mysqli_real_escape_string($item_id) . "'";
		if(!Database::mysqli_query($sql))
			error_log('mysql error ' . Database::mysqli_errno() . ' on user_items update: ' . Database::mysqli_error() . ' ---- Query was: ' . $sql);
		*/
		Item::checkAndUpdateOutOfStockItems($item_id);
	}

	$base_url = Common::getBaseURL();
	// UserItems::update_user_id($user_item_id, $user_id);
	$img_url = $base_url . '/helpers/render-barcode.php?upc=' . $uiid; 
	return $img_url;
}

?>
