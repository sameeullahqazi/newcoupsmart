<?php
	$item_id = $_GET['item_id'];
	$user_id = $_GET['user_id'];
	$items_views_id = $_GET['items_views_id'];
	$redeem_coupon_code = $_GET['redeem_coupon_code'];
	$signature	= $_GET['sig'];
	
	// error_log("GET: " . var_export($_GET, true));
	$csapi = new CSAPI();
	$params = $_GET;
	if(!$csapi->checkCSAPISignature($params))
	{
		print("Signature does not match. Invalid request!");
		exit();
	}
	
	
	$user = new User($user_id);
	$item_info = Item::getMOItemInfo($item_id);
	
	$company_id			= $item_info['company_id'];
	$deal_id			= $item_info['deal_id'];
	$campaign_id		= $item_info['campaign_id'];
	$mo_header_caption	= $item_info['mo_header_caption'];
	$company_name		= $item_info['company_name'];
	$bg_img 			= $item_info['default_coupon_image'];
	$white_label_css	= $item_info['white_label_css_2'];
	$details			= $item_info['small_type'];
	$offer_value		= $item_info['offer_value'];
	$expiry				= $item_info['expiry'];
	$csc_email_from		= $item_info['instore_email_from'];
	
	
	$is_mobile = Common::isMobileESP();
	$coupon_code_content = $is_mobile ? $item_info['csc_reveal_deal_content_mobile'] : $item_info['csc_reveal_deal_content'];

	$btn_done_width = 100;
	$btn_done_height = 30;
	$btn_done_top = $is_mobile ? 500 : 480;
	$btn_done_left = $is_mobile ? 130 : 90;
/*

	if(!Item::canUserPrintItem($user_id, $item_id)) {
		$coupon_denied_page = $is_mobile ? 'print-coupon-denied-mobile.html' : 'print-coupon-denied.html';
		header("Location: " . $coupon_denied_page);
		exit();
	}
	else if(Item::hasItemRunOutOfStock($item_id))
	{
		$response = "Sorry! There are no more prints available for this coupon; it has run out of stock!";
		echo $response;
		exit();
	}
	
	
	
	
if(!empty($company->is_silverpop_company))
	if(empty($company->sp_is_ubx))
		$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $company_id);
	
if($company->is_et_company == '1')
	ExactTarget::checkAndUpsertSubscriber($user_id, $company_id, array('action' => 'claim', 'item_id' => $item_id));

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
	
	
	$user_item_id = UserItems::claimItem($item_id, $user_id, $items_views_id, $company_id); //update the analytics for this user
	// error_log("user item id" . $user_item_id);
	$custom_code = PrintMethods::getCustomData($deal_id, $user_item_id, $user_details['id'], $csc_custom_code); //assign them a code
	$count = PrintMethods::countRemainingCodes($deal_id);
	if($count == 1){
		//there was only one code left? we just gave it away, so end the campaign
		PrintMethods::end_campaign($campaign_id); //end the campaign
	}

	if(!empty($csc_email_from))
	{
		if(!empty($user->email))
		{
			if(!Item::hasUserPrintedAnItem($user_id, $item_id))
				EmailTemplates::sendEmailAlert(EmailTemplates::$csc_email_template, $user_item_id);
		}
	}
	*/
?>