<?php
require_once(dirname(__DIR__) . '/includes/email_parsing.php');
require_once(dirname(__DIR__) . '/includes/UUID.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');

/*
require_once(dirname(__DIR__) . '/classes/Common.class.php');
require_once(dirname(__DIR__) . '/classes/CSAPI.class.php');
require_once(dirname(__DIR__) . '/classes/SGS_Calculator.class.php');
require_once(dirname(__DIR__) . '/classes/AppConfig.class.php');
*/

class EmailTemplates extends BasicDataObject
{
	var $id;
	var $template;
	var $subject;
	var $description;
	var $days_to_send;
	var $group;
	var $exempt;
	var $reason_receiving_email;
	var $defaults;
	var $verify;

	// SGS EMAIL TEMPLATE DEFINITIONS
	public static $sgs_template 					= 'smartgift0313_template';
	public static $sgs_receipt 						= 'smartgift0313_receipt';
	public static $sgs_address_needed				= 'smartgift0313_shippingAddressNeeded';
	public static $sgs_recipient_notification		= 'smartgift0313_recipientNotification';
	public static $sgs_notify_order					= 'smartgift0313_notifyOrderNew';
	public static $sgs_notify_updated_address		= 'smartgift0313_notifyOrderUpdatedAddress';
	public static $sgs_notify_purchaser_updated_address	= 'smartgift0313_notifyPurchaserOrderUpdatedAddress';
	public static $sgs_notify_purchaser_cancellation	= 'smartgift0313_notifyPurchaserOfCancellation';
	public static $instore_save_later 				= 'mobileoffers_0513';
	public static $mobile_offer_sent 				= 'mobileoffers_0513';
	public static $csc_mobile_offers 				= 'csc_mobile_offers';
	public static $csc_email_template 				= 'csc_email_template';
	public static $csc_email_template_with_claim_URL= 'csc_email_template_with_claim_URL';
	public static $send_payment_request 			= 'send_payment_request';
	public static $payment_confirmation 			= 'payment_confirmation';
	public static $manage_refunds					= 'manage_refunds_email';
	public static $fb_notify_reminder				= 'smartgift0313_notifyPurchaserSendNotification';
	
	// Sent to the client whenever a refund/cancellation request is made by the purchaser
	public static $sgs_notify_client_cancellation	= 'smartgift0313_notifyClientOfCancellation';
	
	
	// The client action sent to the purchaser whenever their request is read and submitted. 
	// This is an intermediate emailsent only if the request is neither approved nor denied.
	public static $sgs_notify_purchaser_of_clients_action	= 'smartgift0313_notifyPurchaserOfClientsAction';
	
	// Sent to the purchaser as soon as their refund/cancellation request is approved 
	public static $sgs_notify_order_cancelled		= 'smartgift0313_notifyUserOrderCancelled';

	// Sent to the purchaser as soon as their refund/cancellation request is declined
	public static $sgs_notify_order_not_cancelled	= 'smartgift0313_notifyUserOrderNotCancelled';
	
	
	// Smart Email templates
	public static $smart_email_layout_1 		= 'target_email_layout1';
	public static $smart_email_layout_2 		= 'target_email_layout2';
	public static $smart_email_layout_3 		= 'target_email_layout3';
	
	
	// Lindt Users
	public static $smart_email_lindt 			= 'smart_email_lindt';
	public static $smart_email_lindt2 			= 'smart_email_lindt2';
	
	// Acess Token Renewal
	public static $token_renewal 				= 'company_renewtoken';
	
	// Notifying Users when a Smart Deal is created or Starts
	public static $notify_smart_deal 		=	'notify_smart_deal';
	
	
	// Notifying users when they print a coupon via Smart Deals
	public static $notify_coupon_printed 		=	'notify_coupon_printed';
	
	// Notifying users when they make a donation
	public static $donation_receipt 	=	'donationReceipt';
	
	public static $client_bug_report 	=	'client_bug_report';
	public static $cc_billing_signup 	=	'cc-billing-signup';
	public static $cc_payment_successful=	'cc-payment-successful';
	public static $cc_payment_failed 	=	'cc-payment-failed';
	public static $cc_payment_reminder 	=	'cc-payment-reminder';
	public static $mc_demo_request_email =	'mc_demo_request_email';
	public static $client_setup			=	'client-setup-confirmation';
	public static $user_setup			=	'user-setup-confirmation';
	public static $client_credit_card_setup		=	'client-credit-card-setup';
	public static $client_invoice_setup			=	'client-invoice-setup';
	public static $client_billing_contact_setup	=	'client-billing-contact-setup';
	public static $client_payment_notification	=	'client-payment-notification';
	public static $client_invoice		=	'client-invoice';
	public static $client_receipt	=	'client-receipt';
	
	public static $daily_campaign_report	=	'daily-campaign-report';
	public static $weekly_campaign_report	=	'weekly-campaign-report';
	

	function __construct($template = null, $read_only_mode = true){
		if(!empty($template)){
			$template = Database::mysqli_real_escape_string($template) .".html";
			$this->Select("template='".$template."'", $read_only_mode);
		}
		return $this;
	}

	/* Returns an array containing the email templates starting with 'customer_free_w': */
	public static function get_free_active_email_templates()
	{
		return self::get_email_templates('customer_free_w');
	}

	/* Returns an array containing the email templates starting with 'customer_free_inactive_w': */
	public static function get_free_inactive_email_templates()
	{
		return self::get_email_templates('customer_free_inactive_w');
	}
	
	/* Returns an array containing the email templates starting with 'customer_upgrade_':  */
	public static function get_upgrade_email_templates()
	{
		return self::get_email_templates('customer_upgrade_');
	}
	
	/* Returns an array containing the email templates starting with 'customer_notice_':  */
	public static function get_notice_email_templates()
	{
		return self::get_email_templates('customer_notice_');
	}
	
	/* Returns an array containing  email templates specified in the above functions  */ 
	public static function get_email_templates($prefix1)
	{
		$arr_email_templates = array();
		
		$prefix1 = Database::mysqli_real_escape_string($prefix1);
		
		$sql = "select template, subject, '$prefix1' as prefix1, replace(replace(template, '$prefix1', ''), '.html', '') as prefix2, days_to_send from email_templates where template like '$prefix1%' order by id";
		//error_log("sql in EmailTemplates::get_email_templates(): ".$sql);
		
		$rs = Database::mysqli_query($sql);
		
		if(!$rs)
		{
			error_log("SQL error in EmailTemplates::get_email_templates(): ".Database::mysqli_error());
		}
		else
		{
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_email_templates[] = $row;
		}
		return $arr_email_templates;
	}
	
	
	/************************************************* FUNCTIONS FOR SENDING EMAIL ALERTS *******************************************************************/
	
	/* Sends customer notice email alerts*/
	public static function customer_free_active_alert($user, $prefix2)
	{
		self::send_email_alert($user, 'customer_free_w', $prefix2);
	}
	
	/* Sends customer free inactive email alerts*/
	public static function customer_free_inactive_alert($user, $prefix2)
	{
		self::send_email_alert($user, 'customer_free_inactive_w', $prefix2);
	}
	
	/* Sends customer notice email alerts*/
	public static function customer_notice_alert($user, $prefix2, $rate = 0)
	{
		self::send_email_alert($user, 'customer_notice_', $prefix2, $rate);
	}
	
	/* Sends customer upgrade email alerts*/
	public static function customer_upgrade_alert($user, $prefix2)
	{
		self::send_email_alert($user, 'customer_upgrade_', $prefix2);
	}
	
	// Sends social gift shop alerts
	/*
		mail_buyer_receipt ==> /emails/social_gift_receipt.html
		
		sgs_email_company_owner ==> /emails/social_gift_company_receipt.html
		
		email_sgs_item ==> /emails/social_gift.html
		
		email_sgs_redeemed ==> /emails/social_gift_redeemed.html
		
		email_sgs_confirmation ==> /emails/social_gift_confirmation.html
	*/
	public static function social_gift_alert($user, $prefix2, $email, $sgs_order, $sgs_order_recipient = null, $company_name = null)
	{
		self::SendGiftShopEmail($user, 'social_gift', $prefix2, $email, $sgs_order, $sgs_order_recipient, $company_name);
	}
	
	public static function GetEmailTemplates($template){
		
		$sql = "select subject, exempt from email_templates where template = '" . Database::mysqli_real_escape_string($template) . "' order by id";
		$rs = Database::mysqli_query($sql);
		if(!$rs)
		{
			error_log("SQL error in EmailTemplates::send_mail_alert(): ".Database::mysqli_error());
			return false;
		}
		else
		{
			return $rs;
		}
		
	}
	public static function SendGiftShopEmail($buyer, $prefix1, $prefix2, $email_address, $sgs_order, $sgs_order_recipient = null, $company_name = null){
		$template = $prefix1 . $prefix2 . "_email.html";
		$rs = self::GetEmailTemplates($template);
		//error_log("inside SendGiftEmail");
		if(!$rs)
		{
			error_log("SQL error in EmailTemplates::send_mail_alert(): ".Database::mysqli_error());
		}
		else
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$subject = $row['subject'];
			$exempt = $row['exempt'];
			//error_log('here is the result from them templates: '. var_export($row, true));
			$user_unsubscribed = User::isUnsubscribed($buyer->id);
			
			// send email if either the user has not unsubscribed or email is exempted
			if(!$user_unsubscribed || $exempt == '1' || $template != 'social_gift_receipt_email.html')
			{
				//error_log('EMAIL ADDRESS: ' . var_export($email_address, true));
				// error_log('USER: ' . var_export($user, true));	
				$mailer = new Mailer();
				//error_log("buyer: " . var_export($buyer,true));
				//error_log("temp: " . var_export($template,true));
				
				//error_log("email: " . var_export($email_address,true));
				//error_log("sgs order: " . var_export($sgs_order,true));
				//error_log("recipient: " . var_export($sgs_order_recipient,true));
				//error_log("company: " . var_export($company_name,true));
				$subject = "You've received a gift!";
				//error_log("subject: " . var_export($subject,true));
				$mailer->SGSEmailWithTemplate($buyer, $template, $subject, $email_address, $sgs_order, $sgs_order_recipient, $company_name);
			}
			else 
			{
				error_log("Cannot send email since user is unsubscribed and email is not exempted (unsubscribe): ". $user_unsubscribed . ", (exempt): ".$exempt);
			}
		}
	}
	
	/* Sends the email, specifying user info, email template and the subject*/
	public static function send_email_alert($user, $prefix1, $prefix2, $rate = 0, $method = 'customer_alerts'){
		//error_log("inside send_email_alert" . var_export($user, true));
		$template = $prefix1 . $prefix2 . ".html";
		$sql = "select subject, exempt from email_templates where template = '" . Database::mysqli_real_escape_string($template) . "' order by id";
		$rs = Database::mysqli_query($sql);
		//error_log("sql: ".$sql);
		if(!$rs) {
			error_log("SQL error in EmailTemplates::send_mail_alert(): ".Database::mysqli_error());
		} else {
			$row = Database::mysqli_fetch_assoc($rs);
			$subject = $row['subject'];
			$exempt = $row['exempt'];
			
			$user_unsubscribed = User::isUnsubscribed($user->id); //check to see if the user is unsubscribed from everything
			//error_log("user unsubscribed" . var_export($user_unsubscribed,true));
			
			// send email if either the user has not unsubscribed or email is exempted
			if(!$user_unsubscribed || $exempt == '1'){
				//error_log("company id" . var_export($company_id,true));
				$mailer = new Mailer();
				if($method == 'customer_alerts'){
					if(!$mailer->emailAlreadySent($user->email, $template)){
						$mailer->emailWithTemplate($user, $template, $subject, $exempt, $rate);
					}
				}elseif($method == 'social_gift_shop'){
					$mailer->SGSEmailWithTemplate($user, $template, $subject, $email_address, $price, $quantity, $item, $exempt);
				}
			} else {
				error_log("Cannot send email since user is unsubscribed and email is not exempted (unsubscribe): ". $user_unsubscribed . ", (exempt): ".$exempt);
			}
		}
	}

	public static function SendCountmeinEmail($user, $company, $campaign, $item, $sig){
			$template = $company->countmein_email_template;
			$exempt = "1";
			$subject = $company->countmein_email_subject;
			
			// error_log('here is the result from them templates: '. var_export($row, true));
			$user_unsubscribed = User::isUnsubscribed($user->id);
			
			// send email if either the user has not unsubscribed or email is exempted
			if(!$user_unsubscribed){
				// error_log('EMAIL ADDRESS: ' . var_export($email_address, true));
				// error_log('USER: ' . var_export($user, true));
				$mailer = new Mailer();
				$mailer->sendCountMeInEmail($user, $template, $subject, $item, $campaign, $sig, $exempt);
				
			}
			else 
			{
				error_log("Cannot send email since user is unsubscribed and email is not exempted (unsubscribe): ". $user_unsubscribed . ", (exempt): ".$exempt);
			}
		
	}
	
	public static function sendBookingEmail($email, $item, $company, $url, $expires){
			
			
			$template = 'social_booking_save_email.html';
			$exempt = "1";
			$subject = 'Your Smart Booking Deal!';
			
			// error_log('here is the result from them templates: '. var_export($row, true));
			// $user_unsubscribed = User::isUnsubscribed($user->id);
			
			// send email if either the user has not unsubscribed or email is exempted
			
				// error_log('EMAIL ADDRESS: ' . var_export($email_address, true));
				// error_log('USER: ' . var_export($user, true));	
			$mailer = new Mailer();
			$success = $mailer->sendBookingSavedEmail($template, $subject, $email, $item, $company, $url, $expires, $exempt);
			return $success;
	}
	
	public static function get_email_color_theme($campaign_id)
	{
		$sql = "select se.email_bg_color, se.deal_bg_color, se.text_color, se.text_bg_color, se.img_bg_color from smart_emails se inner join campaigns c on se.campaign_id = c.id where c.id = '".Database::mysqli_real_escape_string($campaign_id)."'";
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function getEmailTemplateInfo($template)
	{
		$sql = "select id as email_template_id, subject, reason_receiving_email, exempt from email_templates where template = '".Database::mysqli_real_escape_string($template)."'";
		return BasicDataObject::getDataRow($sql);
	}

	/*******************************************************************
	// FUNCTIONS FOR SENDING SGS EMAIL ALERTS USING BUILT IN TEMPLATES
		BEGIN HERE */
	
	
	public static function sendEmailAlert($template, $param, $view_in_browser = false, $new_email = null, $email_template_obj = null, $useSES = false)
	{
		//error_log("email template obj: " . var_export($email_template_obj, true));
		//error_log("defaults: "  . var_export(json_decode($email_template_obj->defaults, true), true));
		//error_log("param: " . var_export($param, true));
		try
		{
			//	Get Email Data
			$sgs_email_data 			= EmailTemplates::getEmailAlertData($template, $param, $email_template_obj);
			//error_log(print_r($sgs_email_data));
			// Get Template Info (Subject, reason for receiving email, etc)
			$template_info 				= EmailTemplates::getEmailTemplateInfo($template . ".html"); //SOMEONE DOUBLE CHECK BUT DO WE NEED THIS?, $sgs_email_data['sendEmailTo']);			
			$subject 					= !empty($sgs_email_data['subject']) ? $sgs_email_data['subject'] : $template_info['subject'];
			$reason_receiving_email		= $template_info['reason_receiving_email'];
			$exempt 					= $template_info['exempt'];
			$email_template_id 			= $template_info['email_template_id'];
			
			
			// Set new recipient email address if explicitly specified
			if(!empty($new_email))
				$sgs_email_data['sendEmailTo'] = $new_email;
				
			// Check if the email is unsubscribed
			$is_unsubscribed = EmailTemplates::isUnsubscribed($sgs_email_data['sendEmailTo'], $email_template_id, $sgs_email_data['companyId']);
			if($is_unsubscribed && $exempt == '0' && !$view_in_browser)
			{
				// Do not send the email in this case
				$err_msg = "The email address '" . $sgs_email_data['sendEmailTo'] . "' is unsubscribed from this email list.";
				throw new exception($err_msg);
				error_log($err_msg);
				return;
			}
			
			// Setting unsubscribe url
			$sgs_email_data['unsubscribe_link'] = Common::getBaseURL(true) . '/unsubscribe?email=' . urlencode($sgs_email_data['sendEmailTo']) . ';email_template_id=' . $email_template_id . ';company_id=' . $sgs_email_data['companyId'];
			
			//	Setting reason for receivng email in email data
			$sgs_email_data['receiveMessage'] = str_replace("'", "&#039;", $reason_receiving_email); // Replacing single quotes with HTML specific characters so that rain tpl does not place backslash.

			// Setting the 'View in your Browser' link
			$url_view_in_your_browser = Common::getBaseURL(true) . '/view-email-in-browser?template=' .$template . '&param=' . urlencode(json_encode($param));
			$sgs_email_data['viewInBrowser'] = $url_view_in_your_browser;
			
			// Include and Configure Rain Template Engine
			require_once(dirname(__DIR__) . '/includes/rain.tpl.class.php');

			raintpl::configure("base_url", Common::getBaseURL(true) . "/" );

			raintpl::configure("tpl_dir", "templates/" );
			raintpl::configure("cache_dir", dirname(__DIR__) . "/helpers/tmp/" );
			// raintpl::configure("check_template_update", false );
			raintpl::configure( 'path_replace_list', array('link', 'script' ) );
			
			// Setting this to false since our cache directory has no write permission
			// raintpl::configure( 'check_template_update', false );
			
			//initialize a Rain TPL object

			$tpl = new RainTPL;
			
			// error_log("sgs_email_data in EmailTemplates::sendEmailAlert(): " . var_export($sgs_email_data, true));
			// Set template Data
			foreach($sgs_email_data as $key => $value){
				$tpl->assign($key, $value);
			}
			
			$template_override_info = EmailTemplates::getTemplateOverrideContent($sgs_email_data['companyId'], $sgs_email_data['itemId'], $template);
			//error_log("template_override_info: " . var_export($template_override_info, true));
			
			
			if(!empty($template_override_info['content']))
			{
				// $userName = !empty($sgs_email_data['firstName']) ? $sgs_email_data['firstName'] : 'user';
				// $template_override_info['content'] = str_replace('<!--username-->', $userName, $template_override_info['content']);
				$new_template_name = "new_template_name";
				$save_path = dirname(__DIR__) . "/templates/" . $new_template_name . ".html";
				file_put_contents($save_path, $template_override_info['content']);
				$template = $new_template_name;
			} else {
				$save_path = '';
			}
			//USING RAINTPL AND EMOGRIFER TO CREATE TEMPLATED EMAIL
			//1.)	Instead of moving all template css to a file, use regex to grab everything inside style tag
			
			$css = '';
			
			//2.)	Check to see if they have custom css that they would rather use; if it is, replace
			if(!empty($sgs_email_data['customCSS'])){ 
				$css	= $sgs_email_data['customCSS'];
			}

			//3.)	Draw the HTML
			$html = $tpl->draw($template, $return_string = true );
			// error_log("html: " .var_export($html, true));
			
			//4.)	Get the rendered template HTML with inline styles
			$emo = new emogrifier($html, $css);
			$html = $emo->emogrify();
			


			// error_log("html: " . var_export($html, true));
			if(file_exists($save_path))
				unlink($save_path);
				
			// error_log("rendered template html for template $template: " . var_export($html, true));
			
			// If it has to be viewed in a browser, simply return the html
			if($view_in_browser)
			{
				return $html;
			}
			
			// Otherwise, Send the Email
			if(!empty($sgs_email_data['sendEmailTo']))
			{
				$mailer = new Mailer();
				
				// Setting the From Name and Email
				if(!empty($sgs_email_data['from']))
				{
					if(is_rfc3696_valid_email_address($sgs_email_data['from']))
					{
						$mailer->from_email = $sgs_email_data['from'];
						$mailer->from_name = $sgs_email_data['from']; // Set both the name and email to the email address
					}
					else
					{
						$mailer->from_name = $sgs_email_data['from'];
					}
				}
				
				if(!empty($sgs_email_data['fromName']))
					$mailer->from_name = $sgs_email_data['fromName'];
				
				$mailer->to = $sgs_email_data['sendEmailTo'];
				if(!empty($sgs_email_data['sendEmailCC']))
					$mailer->cc = $sgs_email_data['sendEmailCC'];
				if(!empty($sgs_email_data['sendEmailBCC']))
					$mailer->bcc = $sgs_email_data['sendEmailBCC'];
				// $mailer->bcc = 'archive@coupsmart.com';
				
				if(!empty($sgs_email_data['attachments']))	//	This must be an array
				{
					if(!is_array($sgs_email_data['attachments']))
						$sgs_email_data['attachments'] = array($sgs_email_data['attachments']);
						
					$mailer->attachment_paths = $sgs_email_data['attachments'];
				}
				
				$mailer->subject = $subject;
				$mailer->message = $html;
				if(isset($sgs_email_data['userId']))
					$mailer->user_id = $sgs_email_data['userId'];
				$mailer->email_template = $template . ".html";
				
				$companyId = !empty($sgs_email_data['companyId']) ? $sgs_email_data['companyId'] : null;
				//error_log("mailer: "  .var_export($mailer, true));
				if($useSES){
					//error_log("send2");
					return $mailer->send2('html', null, $companyId, $exempt);
				} else {
					//error_log("send");
					return $mailer->send('html', null, $companyId, $exempt);
				}
				if($sgs_email_data['deleteAttachmentsAfterEmail'])
				{
					foreach($sgs_email_data['attachments'] as $attachment_file)
					{
						if(file_exists($attachment_file))
							unlink($attachment_file);
					}
				}
			}
			else
			{
				error_log("Email send error! Cannot send email in function EmailTemplates::sendEmailAlert($template, $param). The email address is empty!");
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	// $params could be:
	//	1.	company_id
	//	2.	sgs_order_id
	//	3.	sgs_order_recipient_id
	public static function getEmailAlertData($template, $param, $email_template_obj)
	{
		try
		{
			$data = array();
			switch($template)
			{
				// 1.	SGS RECEIPT
				case EmailTemplates::$sgs_receipt:
					$data = EmailTemplates::getSGSEmailReceiptData($param); // param = sgs_order_id
					break;
			
				// 2.	SGS ADDRESS REQUIRED
				case EmailTemplates::$sgs_address_needed:
					$data = EmailTemplates::getSGSAddressNeededEmailData($param); // param = $sgs_order_recipient_id
					break;
			
				// 3.	SGS RECIPIENT NOTIFICATION
				case EmailTemplates::$sgs_recipient_notification:
					$data = EmailTemplates::getSGSRecipientNotificationEmailData($param); // param = $sgs_order_recipient_id
					break;
			
				// 4.	SGS NOTIFY ORDER
				case EmailTemplates::$sgs_notify_order:
					$data = EmailTemplates::getSGSNotifyOrderEmailData($param); // param = $sgs_order_id
					$data['sendEmailCC'] = array('giftshop@coupsmart.com', 'khoeffer@coupsmart.com');
					
					break;
				
				// 5.	SGS NOTIFY UPDATED ADDRESS
				case EmailTemplates::$sgs_notify_updated_address:
					$data = EmailTemplates::getSGSNotifyUpdatedAddressData($param); // param = $sgs_order_recipient_id
					break;
				
				// 6.	SGS NOTIFY PURCHASER ABOUT UPDATED ADDRESS
				case EmailTemplates::$sgs_notify_purchaser_updated_address:
					$data = EmailTemplates::getSGSNotifyPurchaserUpdatedAddressData($param); // param = $sgs_order_recipient_id
					break;
				
				
				// 7.	INSTORE SAVE LATER
				case EmailTemplates::$instore_save_later:
				 	// param = array('user_id' => $user_id, 'item_id' => $item_id, 'location_id' => $location_id)
					$data = EmailTemplates::getInstoreSaveLaterData($param);
					break;
				
				// 8.	INSTORE SAVE LATER
				case EmailTemplates::$mobile_offer_sent:
				 	// param = array('user_id' => $user_id, 'item_id' => $item_id)
					$data = EmailTemplates::getMobileOfferSentData($param);
					break;
				// 7.	CUSTOMER SUPPLIED CODE - INSTORE SAVE LATER
				case EmailTemplates::$csc_mobile_offers:
				 	// param = array('user_id' => $user_id, 'item_id' => $item_id, 'location_id' => $location_id)
					$data = EmailTemplates::getInstoreSaveLaterData($param);
					break;
				
				// 7.	CUSTOMER SUPPLIED CODE - INSTORE SAVE LATER
				case EmailTemplates::$csc_email_template:
				case EmailTemplates::$csc_email_template_with_claim_URL;
				 	// param = array('user_id' => $user_id, 'item_id' => $item_id, 'location_id' => $location_id)
					$data = EmailTemplates::getCSCEmailTemplateData($param);
					break;
					
				// 9.	SEND PAYMENT REQUEST
				case EmailTemplates::$send_payment_request:
					// param = $payment_request_id
					$data = EmailTemplates::getPaymentRequestData($param);
					break;
				
				//	10.	CONFIRM PAYMENT
				case EmailTemplates::$payment_confirmation:
					// param = $payment_request_id
					$data = EmailTemplates::getPaymentConfirmationData($param);
					break;
				
				//	11.	SMART EMAILS
				case EmailTemplates::$smart_email_layout_1:
				case EmailTemplates::$smart_email_layout_2:
				case EmailTemplates::$smart_email_layout_3:
					$data = EmailTemplates::getSmartEmailLayoutData($param); // Param should be $smart_email_info
					break;
				
				// SMART EMAILS SENT TO LINDT USERS
				case EmailTemplates::$smart_email_lindt:
					$data = $param;
					break;

				case EmailTemplates::$smart_email_lindt2:
					$data = $param;
					break;

				//	12. MANAGE REFUNDS
				case EmailTemplates::$manage_refunds:
					$data = EmailTemplates::getRefundData($param);
					break;
					
				//	13. TOKEN RENEWAL
				case EmailTemplates::$token_renewal:
					$data = EmailTemplates::getTokenRenewalData($param);
					break;
				
				// 14. FB Notification Reminder
				case EmailTemplates::$fb_notify_reminder:
					$data = EmailTemplates::getFBNotifyReminderData($param);
					break;
				
				// 15. Notify Purchaser of Cancellation
				case EmailTemplates::$sgs_notify_purchaser_cancellation:
					$data = EmailTemplates::getNotifyPurchaserCancellationData($param);
					break;
				
				// 16.	Notify Client Of Cancellation
				case EmailTemplates::$sgs_notify_client_cancellation:
					$data = EmailTemplates::getNotifyClientCancellationData($param); // Param should be sgs_refund_id
					break;
				
				case EmailTemplates::$sgs_notify_order_cancelled:
					$data = EmailTemplates::getNotifyOrderCancelledData($param); // Param should be sgs_refund_id
					break;
					
				case EmailTemplates::$sgs_notify_order_not_cancelled:
					$data = EmailTemplates::getNotifyOrderNotCancelledData($param); // Param should be sgs_refund_id
					break;
					
				case EmailTemplates::$sgs_notify_purchaser_of_clients_action:
					$data = EmailTemplates::getNotifyPurchaserOfClientsAction($param); // Param should be sgs_refund_id
					break;
				
				case EmailTemplates::$notify_smart_deal:
					$data = EmailTemplates::getNotifySmartDealUserEmails($param);	// param['company_id'], param['to']
					break;
				
				case EmailTemplates::$notify_coupon_printed:
					$data = EmailTemplates::getNotifyCouponPrinted($param);			// user_items.uiid
					break;
				
				case EmailTemplates::$donation_receipt:
					$data = EmailTemplates::getDonationReceipt($param);				// charge_id
					break;
				
				case EmailTemplates::$client_bug_report:
					$data = EmailTemplates::getClientBugReport($param);				// client_bug_report_id
					break;	
				
				case EmailTemplates::$cc_billing_signup:
					$data = EmailTemplates::getBillingSignupInfo($param);			// billing_signup_id
					break;
				
				case EmailTemplates::$client_setup:
				case EmailTemplates::$user_setup:
				case EmailTemplates::$client_credit_card_setup:
				case EmailTemplates::$client_invoice_setup:
				case EmailTemplates::$client_billing_contact_setup:
					$data = EmailTemplates::getClientSetupInfo($param);	
					break;
				
				case EmailTemplates::$client_payment_notification:
				case EmailTemplates::$client_receipt:
					$data = EmailTemplates::getClientPaymentNotificationInfo($param);
					break;
				
				case EmailTemplates::$client_invoice:
					$data = EmailTemplates::getClientInvoiceInfo($param);
					break;
				
				case EmailTemplates::$cc_payment_successful:
					$data = EmailTemplates::getBillingPaymentSuccessful($param);	// billing_signup_id
					break;
				
				case EmailTemplates::$cc_payment_failed:
					$data = EmailTemplates::getBillingPaymentFailed($param);		// billing_signup_id
					break;
				
				case EmailTemplates::$cc_payment_reminder:
					$data = EmailTemplates::getBillingPaymentReminder($param);		// billing_signup_id
					break;
					
				case EmailTemplates::$mc_demo_request_email:
					$data = EmailTemplates::getMCDemoRequestData($param);
					break;
					
				case EmailTemplates::$daily_campaign_report:
					$data = EmailTemplates::getDailyCampaignReportData($param);
					break;
				
				case EmailTemplates::$weekly_campaign_report:
					$data = EmailTemplates::getWeeklyCampaignReportData($param);
					break;
			}

			// Getting company specific data (including location address) if applicable
			// error_log('data before' . var_export($data, true));
			if(!empty($data['companyId']))
			{
				$company_template_data = EmailTemplates::getSGSEmailTemplateData($data['companyId'], $email_template_obj);
				$data = array_merge($data, $company_template_data);
			}

			// error_log("data in EmailTemplates::getEmailAlertData() for template $template: ".var_export($data, true));
			return $data;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	public static function getTokenRenewalData($company_id){
	
	$sql = "
		SELECT facebook_admin_email, facebook_page_id, display_name FROM companies WHERE id = '" . Database::mysqli_real_escape_string($company_id) ."'";
		$row = BasicDataObject::getDataRow($sql);
		error_log('row = ' . var_export($row, true));
		$data['sendEmailTo'] = $row['facebook_admin_email'];
		$data['facebook_page_id'] = $row['facebook_page_id'];
		$data['display_name'] = $row['display_name'];
		$data['company_id'] = $company_id;
		$data['renewal_url'] = Common::getBaseURL(true) . '/manage_apps?company_id=' . $company_id;
		return $data;
	}
	
	public static function getRefundData($refund){
		$sql = "
				SELECT sgs_item_id
				FROM sgs_order_recipients
				WHERE id = ".Database::mysqli_real_escape_string($refund['sgs_order_recipients_id'])."
		";
		$row 					= BasicDataObject::getDataRow($sql);
		$sgs_order_recipient 	= new SGS_Order_Recipient($refund['sgs_order_recipients_id']);
		$sgs_order 				= new SGS_Order($sgs_order_recipient-> sgs_order_id);
		$sgs_item 				= new SGS_Item($row['sgs_item_id']);
		$company 				= new Company($sgs_item->company_id);

		$data 					= array();
		$data['name']			= $sgs_item 								-> name;
		$data['image']			= 'http://sgsimg.coupsmart.com/'.  $sgs_item-> image_url;
		$data['status']			= $refund 										['status'];
		$data['message']		= $refund 										['message'];
		$data['amount_refunded']= $sgs_item 								-> price;
		$data['company_name']	= $company 									-> display_name;
		$data['sendEmailTo']	= $sgs_order 								-> bill_email;
		return $data;
	}
	
	
	// Gets Email Data for SGS Default Template
	public static function getSGSEmailTemplateData($company_id, $email_template_obj = null) {
		$data		= array();
		$company	= new Company($company_id);
		$location	= Location::get_locations_by_company_id($company -> id);
		$location	= new Location($location[0]['id']);

		/*assigning these based on the company makes this easier to read and easier to access other properties*/
		$data['headerImage']			=
			!empty($company -> sgs_header_img)
				? 'http://s3.amazonaws.com/sgsimg.coupsmart.com/' . $company -> sgs_header_img
				: '';
		$data['emailHeaderImage']		=
			!empty($company -> sgs_email_header_img)
				? 'http://s3.amazonaws.com/sgsimg.coupsmart.com/' . $company -> sgs_email_header_img
				: '';
		$data['clientUnsubscribeName']	= "<a href='unsubscribe'>Please Click here to unsubscribe.</a>";
		$data['clientCopyName'] 			= $company->display_name;
		$data['clientCopyRight']			= $company->display_name;
		$data['compName']				= $company->display_name;
		$data['compAdd1']				= $location->address1;
		$data['compAdd2']				= $location->address2;
		$data['compCity']				= $location->city;
		$data['compState']				= $location->state;
		$data['compZip']				= $location->zip;
		$data['compCountry']			= $location->country;
		$data['facebook_page_id']		= $company->facebook_page_id;
		$data['compEmail']				= $company->email;
		$data['compTel']				= $company->phone;
		$data['receiveMessage']			= $company->receive_message;
		$data['customCSS']				= $company->sgs_email_css;
		

		/***CHECK IF ANY OF THESE ARE EMPTY; IF THEY ARE, FILL IN DEFAULT INFORMATION ***/
		foreach($data as $key => $val){
			$new_val = $val;
			if(!empty($email_template_obj) && empty($val)){ //if an email template was provided and there no value
				$new_val = $email_template_obj->getDefault($key);
				if(empty($new_val)){
					error_log("Passing in an empty value for an SGS email on line ".__LINE__);
				}
			}
			$data[$key] = $new_val;
		}
		
		/*
		 * trying to make this a little more clear
		 * 	$sql = "
			SELECT
				c.sgs_header_img as headerImage, c.sgs_email_header_img as emailHeaderImage, c.support_footer_content as clientCustomText, c.display_name as compName, c.facebook_page_id, l.address1 as compAdd1, l.address2 as compAdd2, c.phone as compTel, c.email as compEmail, c.copyright as clientCopyright, c.receive_message as receiveMessage
			FROM
				companies c
			LEFT JOIN
				locations l on (c.id = l.companies_id and l.can_spam_address = '1')
			WHERE
				c.id = '".Database::mysqli_real_escape_string($company_id)."'"
		;
		$data = BasicDataObject::getDataRow($sql);
		$data['headerImage'] = !empty($data['headerImage']) ? 'http://s3.amazonaws.com/sgsimg.coupsmart.com/' . $data['headerImage'] : $data['headerImage'];
		$data['emailHeaderImage'] = !empty($data['emailHeaderImage']) ? 'http://s3.amazonaws.com/sgsimg.coupsmart.com/' . $data['emailHeaderImage'] : $data['emailHeaderImage'];
		$data['clientUnsubscribeName'] = "<a href='unsubscribe'>Please Click here to unsubscribe.</a>";
		$data['clientCopyName'] = $data['compName'];
		$data['clientCopyRight'] = $data['compName'];
		$data['receiveMessage'] = !empty($data['receiveMessage']) ? $data['receiveMessage'] : Common::$receive_message;*/
		//error_log("the default template data: " . var_export($data, true));
		return $data;
	}
	
	// Gets Email Data for SGS Receipt
	public static function getSGSEmailReceiptData($sgs_order_id)
	{
		require_once(dirname(dirname(__FILE__)) . "/includes/currencies.php");
		
		global $socgift_app_url;
		global $arr_currency_data;

		// SGS Order Data
		$sql = "select u.id as sgsBuyerId, u.firstname as sgsBuyerFirstName, u.lastname as sgsBuyerLastName, so.bill_address1 as sgsBuyerAddress1, so.bill_address2 as sgsBuyerAddress2, so.bill_city as sgsBuyerCity, so.bill_state as sgsBuyerState, so.bill_zip as sgsBuyerZip, so.bill_country as sgsBuyerCountry, u.email as sgsBuyerEmail, so.paid_date_time as sgsBuyerOrderDate, so.credit_card as sgsBuyerCardDigits, so.transaction_amount as sgsBuyerTotalAmount, so.discount_amount as sgsBuyerDiscountAmount, so.shipping_total as sgsBuyerShippingAmount, so.sub_total as sgsBuyerSubtotalAmount, so.gateway_trans_id as sgsBuyerTransID, sd.name as sgsBuyerDiscountName, sd.conditions as discount_conditions, sd.amount as discount_amount, sd.type as discount_type, so.selected_language
		from sgs_orders so 
		inner join users u on so.buyer_id = u.id
		left join sgs_user_discounts sud on (so.id = sud.sgs_order_id)
		left join sgs_discounts sd on sud.sgs_discount_id = sd.id
		where so.id = '".Database::mysqli_real_escape_string($sgs_order_id)."'";
		
		$data = BasicDataObject::getDataRow($sql);
		$data['sgsBuyerOrderDate'] = date('F jS, Y', strtotime($data['sgsBuyerOrderDate']));
		// $data['sgsBuyerSubtotalAmount'] = 0.00;
		// $data['sgsBuyerShippingAmount'] = 0.00;
		$data['sgsBuyerAddress2'] = !empty($data['sgsBuyerAddress2']) && $data['sgsBuyerAddress2'] != 'Address 2' ? $data['sgsBuyerAddress2'] : '';
		
		// The item_id (if any) that the discount has been applied on
		$discount_item_id = 0;
		if(!empty($data['discount_conditions']))
		{
			$arr_conditions = unserialize($data['discount_conditions']);
			if(!empty($arr_conditions['item_id']))
				$discount_item_id = $arr_conditions['item_id'];
		}
		
		// SGS Order Recipients Data
		$order_details = array();
		$sql = "select si.image_url, si.display_name as sgsItemName, si.price as sgsItemPrice, sor.delivery_date as sgsItemDelivery, si.shipping_price as sgsItemShippingCost, sor.recipient_first_name as sgsItemRecipientFirstName, sor.recipient_last_name sgsItemRecipientLastName, sor.recipient_email, sor.delivery_address as sgsItemRecipientAddress, sor.delivery_city as sgsItemRecipientCity, sor.delivery_state as sgsItemRecipientState, sor.delivery_zip as sgsItemRecipientZip, sor.delivery_country as sgsItemRecipientCountry, sor.when_to_deliver, sor.delivery_method, sor.delivery_type, sor.sgs_item_id, si.company_id as companyId, c.sgs_order_receipt_recipients as sgsOrderReceiptRecipients, c.sgs_currency, c.sgs_language, c.display_name as company_name,
		sio0.label as sgsItemOption0, siv0.value as sgsItemOption0Value, sio1.label as sgsItemOption1, siv1.value as sgsItemOption1Value, siv0.id as sgsItemOptionValueId0, siv1.id as sgsItemOptionValueId1
		from sgs_order_recipients sor
		inner join sgs_items si on sor.sgs_item_id = si.id
		inner join companies c on si.company_id = c.id
		left join sgs_item_options_data sid on sor.sgs_item_option_data_id = sid.id
		left join sgs_item_option_values siv0 on sid.sgs_item_option_value_0 = siv0.id
		left join sgs_item_option_values siv1 on sid.sgs_item_option_value_1 = siv1.id
		left join sgs_item_options sio0 on siv0.sgs_item_option_id = sio0.id
		left join sgs_item_options sio1 on siv1.sgs_item_option_id = sio1.id
		where sor.sgs_order_id = '".Database::mysqli_real_escape_string($sgs_order_id)."'
		order by sor.recipient_first_name, sor.recipient_last_name";
		$recipient_data = BasicDataObject::getDataTable($sql);
		$companyId = 0;
		$sgs_currency = 'USD';
		$company_name = '';
		
		foreach($recipient_data as $i => $row)
		{
			$row['sgsItemShippingCost'] = $data['sgsBuyerShippingAmount'] > 0 ? $row['sgsItemShippingCost'] : 0;
				
			$row['sgsItemRecipient'] = $row['delivery_method'] == 'anonymous' ? $row['sgsItemRecipient'] . ' (' . $row['recipient_email'] . ')' : $row['sgsItemRecipient'];
			$row['sgsItemImage'] = !empty($row['image_url']) ? "http://sgsimg.coupsmart.com/" . $row['image_url'] : ""; // Common::$default_sgs_item_img;
			$row['sgsItemDelivery'] = date('F jS, Y', strtotime($row['sgsItemDelivery']));
			$row['sgsItemDiscountName'] = $data['sgsBuyerDiscountName'];
			$companyId = $row['companyId'];
			$sgs_currency = $row['sgs_currency'];
			$company_name = $row['company_name'];
		
			//	Setting any SGS Item Option Data if it was selected
			$arr_sgs_item_option_data = array();
			// SGS Item Option 0
			if(!empty($row['sgsItemOption0']))
				$arr_sgs_item_option_data[] = $row['sgsItemOption0'] . ": " . $row['sgsItemOption0Value'];
			// SGS Item Option 1
			if(!empty($row['sgsItemOption1']))
				$arr_sgs_item_option_data[] = $row['sgsItemOption1'] . ": " . $row['sgsItemOption1Value'];
		
			if(!empty($arr_sgs_item_option_data))
				$row['sgsItemName'] .= " (" . implode(', ', $arr_sgs_item_option_data). ")";
			
			// Calculating the discount per item if any
			$sgsItemDiscountAmount = 0.00;
			if($row['sgs_item_id'] == $discount_item_id)
			{
				if($data['discount_type'] == '0')
					$sgsItemDiscountAmount = $row['sgsItemPrice'] * $data['discount_amount'] * 0.01;
				else if($data['discount_type'] == '2')
					$sgsItemDiscountAmount = $data['discount_amount'];
			}
			$row['sgsItemDiscountAmount'] = $sgsItemDiscountAmount;

			//forgot to add price variances here
			if(!empty($row['sgsItemOptionValueId0']) || !empty($row['sgsItemOptionValueId1'])){
				$price_variance = SGS_Calculator::countItemOptionPrices($row['sgs_item_id'], $row['sgsItemOptionValueId0'], $row['sgsItemOptionValueId1']);
			}
			
			// $data['sgsBuyerSubtotalAmount'] += $row['sgsItemPrice'] + $price_variance;
			// $data['sgsBuyerShippingAmount'] += $row['sgsItemShippingCost'];
			
			// Formatting the prices
			$row['sgsItemPriceWithPriceVariance'] = number_format(($row['sgsItemPrice'] + $price_variance), 2, '.', ',');
			$row['priceVariance'] = number_format($price_variance, 2, '.', ',');
			$row['sgsItemShippingCost'] = number_format($row['sgsItemShippingCost'], 2, '.', ',');
			$row['sgsItemDiscountAmount'] = number_format($row['sgsItemDiscountAmount'], 2, '.', ',');
			
			
			$order_details[] = $row;
			
			// Setting SGS Order Receipt Recipients
			$data['sgsOrderReceiptRecipients'] = $row['sgsOrderReceiptRecipients'];
		}
		
		$sgs_currency_symbol = !empty($arr_currency_data[$sgs_currency]['currency_symbol']) ? $arr_currency_data[$sgs_currency]['currency_symbol'] : $sgs_currency . ' ';
		$phrases_to_translate = array(
			'Thank You For Shopping At The [] Gift Shop', 
			'Purchaser Receipt', 
			'Sent To', 
			'Price', 
			'Transaction ID', 
			'View the status of your gifts on Facebook by clicking here',
			'Shipping', 
			'Subtotal', 
			'Discount',
			'Billing address',
			'Grand Total',
			'Payment Info',
			'Visit our shop at the [] Facebook Page',
			'Click here to access your voucher for printing',
		);

		$selected_language = $data['selected_language'];
		if(empty($selected_language))
			$selected_language = 'en';
			
		// $translations = Common::translate_to($selected_language, $phrases_to_translate);
		$translations = Common::getTranslations($selected_language, 'sgs', $phrases_to_translate);
		error_log("translations in EmailTemplates::getSGSEmailReceiptData(): " . var_export($translations, true));
		
		$data['sgsOrderDetails'] = $order_details;
		
		$translations['ThankYou'] = $translations['Thank You For Shopping At The [] Gift Shop'];
		$translations['ThankYou'] = str_replace('[]', '<br /><span class="fbPageName">' . $company_name. '</span>', $translations['ThankYou']);
		
		$translations['PurchaserReceipt'] = $translations['Purchaser Receipt'];
		$translations['SentTo'] = $translations['Sent To'];
		$translations['TransactionID'] = $translations['Transaction ID'];
		$translations['ViewStatus'] = $translations['View the status of your gifts on Facebook by clicking here'];
		$translations['BillingAddress'] = $translations['Billing address'];
		$translations['GrandTotal'] = $translations['Grand Total'];
		$translations['PaymentInfo'] = $translations['Payment Info'];
		$translations['VisitOurShop'] = $translations['Visit our shop at the [] Facebook Page'];
		$translations['VisitOurShop'] = str_replace('[]', '<a href="https://www.facebook.com/{$facebook_page_id}" target="_blank">' . $company_name . '</a>', $translations['VisitOurShop']);
		$translations['VoucherPrintURL'] = $translations['Click here to access your voucher for printing'];

		unset($translations['Thank You For Shopping At The [] Gift Shop']);
		unset($translations['Purchaser Receipt']);
		unset($translations['Sent To']);
		unset($translations['Transaction ID']);
		unset($translations['View the status of your gifts on Facebook by clicking here']);
		unset($translations['Billing address']);
		unset($translations['Grand Total']);
		unset($translations['Payment Info']);
		unset($translations['Visit our shop at the [] Facebook Page']);
		unset($translations['Click here to access your voucher for printing']);
		
		$data['translations'] = $translations;
		
		// Formatting the numeric values
		$data['sgsBuyerSubtotalAmount'] = number_format($data['sgsBuyerSubtotalAmount'], 2, '.', ',');
		$data['sgsBuyerShippingAmount'] = number_format($data['sgsBuyerShippingAmount'], 2, '.', ',');
		
		// SGS App URL
		$data['appURL'] = $socgift_app_url;
		$data['sgsCurrencySymbol'] = $sgs_currency_symbol;

		$sendEmailTo = array($data['sgsBuyerEmail']);
		
		$sgs_order_receipt_recipients = array();
		if (!empty($data['sgsOrderReceiptRecipients'])) {
			$r_emails = explode(',', $data['sgsOrderReceiptRecipients']);
			foreach ($r_emails as $r_email) {
				$r_email = trim($r_email);
				if (!empty($r_email) && $r_email != null && is_rfc3696_valid_email_address($r_email)) {
					$sgs_order_receipt_recipients[] = $r_email;
				}
			}
		}
		
		$sendEmailTo = array_values(array_unique(array_merge($sendEmailTo, $sgs_order_receipt_recipients)));
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		
		$data['sendEmailTo'] = $sendEmailTo;
		$data['companyId'] = $companyId;
		$data['userId'] = $data['sgsBuyerId'];
		
		$company = new Company($companyId);
		$data['emailHeaderImage'] = !empty($company->sgs_email_header_img) ? 'http://s3.amazonaws.com/sgsimg.coupsmart.com/' . $company->sgs_email_header_img : '';
		$data['subject'] = $company->display_name . ' - Your Social Gift Shop Receipt';
		
		return $data;
	}
	
	// Gets Email Data for SGS Alert where sender is notified of the new Order details
	public static function getSGSNotifyOrderEmailData($sgs_order_id, $updated_sgs_order_recipient_id = null)
	{
		require_once(dirname(dirname(__FILE__)) . "/includes/currencies.php");
		global $arr_currency_data;
		// SGS Order Data
		$sql = "select u.firstname as sgsBuyerFirstName, u.lastname as sgsBuyerLastName, so.bill_address1 as sgsBuyerAddress1, so.bill_address2 as sgsBuyerAddress2, so.bill_city as sgsBuyerCity, so.bill_state as sgsBuyerState, so.bill_zip as sgsBuyerZip, so.bill_country as sgsBuyerCountry, u.email as sgsBuyerEmail, so.paid_date_time as sgsBuyerOrderDate, so.credit_card as sgsBuyerCardDigits, so.transaction_amount as sgsBuyerTotalAmount, so.discount_amount as sgsBuyerDiscountAmount,  so.shipping_total as sgsBuyerShippingAmount, so.sub_total as sgsBuyerSubtotalAmount, so.gateway_trans_id as sgsBuyerTransID, sd.name as sgsBuyerDiscountName, sd.conditions as discount_conditions, sd.amount as discount_amount, sd.type as discount_type
		from sgs_orders so 
		inner join users u on so.buyer_id = u.id
		left join sgs_user_discounts sud on (so.id = sud.sgs_order_id)
		left join sgs_discounts sd on sud.sgs_discount_id = sd.id
		where so.id = '".Database::mysqli_real_escape_string($sgs_order_id)."'";
		// error_log("SQL in EmailTemplates::getSGSNotifyOrderEmailData(): " . $sql);
		
		$data = BasicDataObject::getDataRow($sql);
		$data['sgsBuyerOrderDate'] = date('F jS, Y', strtotime($data['sgsBuyerOrderDate']));
		// $data['sgsBuyerSubtotalAmount'] = 0.00;
		// $data['sgsBuyerShippingAmount'] = 0.00;
		$data['sgsBuyerAddress2'] = !empty($data['sgsBuyerAddress2']) && $data['sgsBuyerAddress2'] != 'Address 2' ? $data['sgsBuyerAddress2'] : '';
		
		$data['sgsOrderRecipients'] = array();
		
		// The item_id (if any) that the discount has been applied on
		$discount_item_id = 0;
		if(!empty($data['discount_conditions']))
		{
			$arr_conditions = unserialize($data['discount_conditions']);
			if(!empty($arr_conditions['item_id']))
				$discount_item_id = $arr_conditions['item_id'];
		}
		
		// SGS Order Recipients Data

		$sql = "select si.image_url, si.display_name as sgsItemName, si.price as sgsItemPrice, sor.id as sgsOrderRecipientId, sor.delivery_date as sgsItemDelivery, si.shipping_price as sgsItemShippingCost, sor.recipient_first_name as sgsOrderRecipientFirstName, sor.recipient_last_name as sgsOrderRecipientLastName, sor.recipient_email, sor.delivery_address as sgsOrderRecipientAddress, sor.delivery_city as sgsOrderRecipientCity, sor.delivery_state as sgsOrderRecipientState, sor.delivery_zip as sgsOrderRecipientZip, sor.delivery_country as sgsOrderRecipientCountry, sor.when_to_deliver, sor.delivery_method, sor.delivery_type, sor.sgs_item_id, u.email as recipient_user_email, c.id as companyId, c.email as companyEmail, c.sgs_report_recipients as sgsReportRecipients, c.sgs_currency,
sio0.label as sgsItemOption0, siv0.value as sgsItemOption0Value, sio1.label as sgsItemOption1, siv1.value as sgsItemOption1Value, siv0.id as sgsItemOptionValueId0, siv1.id as sgsItemOptionValueId1
		from sgs_order_recipients sor
		inner join sgs_items si on sor.sgs_item_id = si.id
		inner join companies c on si.company_id = c.id
		left join users u on sor.recipient_user_id = u.id
		left join sgs_item_options_data sid on sor.sgs_item_option_data_id = sid.id
		left join sgs_item_option_values siv0 on sid.sgs_item_option_value_0 = siv0.id
		left join sgs_item_option_values siv1 on sid.sgs_item_option_value_1 = siv1.id
		left join sgs_item_options sio0 on siv0.sgs_item_option_id = sio0.id
		left join sgs_item_options sio1 on siv1.sgs_item_option_id = sio1.id
		where sor.sgs_order_id = '".Database::mysqli_real_escape_string($sgs_order_id)."'
		order by sor.recipient_first_name, sor.recipient_last_name";		
		$recipient_data = BasicDataObject::getDataTable($sql);
		//error_log("query data in notidy order email data " . var_export($recipient_data, true));
		$companyId = 0;
		$companyEmail = '';
		$sgsReportRecipients = '';
		$sgs_currency = 'USD';
		foreach($recipient_data as $i => $row)
		{

			$recipient_name = $row['sgsOrderRecipientFirstName'] . ' ' . $row['sgsOrderRecipientLastName'];
			$row['sgsItemShippingCost'] = $data['sgsBuyerShippingAmount'] > 0 ? $row['sgsItemShippingCost'] : 0;
			
			$existing_recipient_names = array();
			
			$i = 0;
			$free_name_found = false;
			while(!$free_name_found) {
				$matched = false;
				foreach ($data['sgsOrderRecipients'] as $this_recipient_name => $recipient_data) {
					if ($this_recipient_name == $recipient_name) {
						$matched == true;
					}
				}
				if (!$matched) {
					$free_name_found = true;
				} else {
					$i++;
					$recipient_name = $row['sgsOrderRecipientFirstName'] . ' ' . $row['sgsOrderRecipientLastName'] . ' ' . $i;
				}
			}
			$row['sgsItemDelivery'] = date('F jS, Y', strtotime($row['sgsItemDelivery']));
			$row['sgsItemImage'] = !empty($row['image_url']) ? "http://sgsimg.coupsmart.com/" . $row['image_url'] : ""; // Common::$default_sgs_item_img;
			
			//	Setting any SGS Item Option Data if it was selected
			$arr_sgs_item_option_data = array();
			// SGS Item Option 0
			if(!empty($row['sgsItemOption0']))
				$arr_sgs_item_option_data[] = $row['sgsItemOption0'] . ": " . $row['sgsItemOption0Value'];
			// SGS Item Option 1
			if(!empty($row['sgsItemOption1']))
				$arr_sgs_item_option_data[] = $row['sgsItemOption1'] . ": " . $row['sgsItemOption1Value'];
		
			if(!empty($arr_sgs_item_option_data))
				$row['sgsItemName'] .= " (" . implode(', ', $arr_sgs_item_option_data). ")";
			

			//forgot to add price variances here
			$price_variance = 0;
			if(!empty($row['sgsItemOptionValueId0']) || !empty($row['sgsItemOptionValueId1'])){
				$price_variance = SGS_Calculator::countItemOptionPrices($row['sgs_item_id'], $row['sgsItemOptionValueId0'], $row['sgsItemOptionValueId1']);
			}
			
			// Getting Recipient Email
			$recipient_email = '';
			if(!empty($row['recipient_email']))
				$recipient_email = $row['recipient_email'];
			else if(!empty($data['recipient_user_email']))
				$recipient_email = $row['recipient_user_email'];
			$row['sgsOrderRecipientEmail'] = $recipient_email;
			
			// Calculating the discount per item if any
			$sgsItemDiscountAmount = 0.00;
			if($row['sgs_item_id'] == $discount_item_id)
			{
				if($data['discount_type'] == '0')
					$sgsItemDiscountAmount = $row['sgsItemPrice'] * $data['discount_amount'] * 0.01;
				else if($data['discount_type'] == '2')
					$sgsItemDiscountAmount = $data['discount_amount'];
			}
			
			$companyId = $row['companyId'];
			$companyEmail = $row['companyEmail'];
			$sgs_currency = $row['sgs_currency'];
			$data['sgsReportRecipients'] = $row['sgsReportRecipients'];
			
			$row['sgsOrderRecipientDiscountName'] = $data['sgsBuyerDiscountName'];
			
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientFirstName'] = $row['sgsOrderRecipientFirstName'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientLastName'] = $row['sgsOrderRecipientLastName'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipient'] = $recipient_name;
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientEmail'] = $row['sgsOrderRecipientEmail'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientAddress'] = $row['sgsOrderRecipientAddress'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientCity'] = $row['sgsOrderRecipientCity'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientState'] = $row['sgsOrderRecipientState'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientZip'] = $row['sgsOrderRecipientZip'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientCountry'] = $row['sgsOrderRecipientCountry'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsBuyerTransID'] = $data['sgsBuyerTransID'];
			
			$data['sgsOrderRecipients'][$recipient_name]['delivery_type'] = $row['delivery_type'];
			$data['sgsOrderRecipients'][$recipient_name]['delivery_method'] = $row['delivery_method'];
			
			// if(!empty($updated_sgs_order_recipient_id) && $row['sgsOrderRecipientId'] == $updated_sgs_order_recipient_id)
			//	$data['sgsOrderRecipients'][$recipient_name]['addressUpdated'] = 1;
			
			// Recipient Item Count
			if(!isset($data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientItemCount']))
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientItemCount'] = 0;
				
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientItemCount']++;
			if(!isset($data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientSubtotal']))
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientSubtotal'] = 0;
				
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientSubtotal'] += $row['sgsItemPrice'] + $price_variance;
			
			if(!isset($data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientDiscountAmount']))
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientDiscountAmount'] = 0;
				
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientDiscountAmount'] += $sgsItemDiscountAmount;
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientDiscountName'] = $row['sgsOrderRecipientDiscountName'];
			
			if(!isset($data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount']))
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount'] = 0;
			
			if($row['delivery_type'] == 'physical')
			{
				if(!isset($data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientShippingCost']))
					$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientShippingCost'] = 0;
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientShippingCost'] += $row['sgsItemShippingCost'];
				
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount'] += $row['sgsItemShippingCost'];
			}

			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount'] += $row['sgsItemPrice'];
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount'] += $price_variance;
			
			// $data['sgsBuyerSubtotalAmount'] += $row['sgsItemPrice'] + $price_variance;
			// $data['sgsBuyerShippingAmount'] += $row['sgsItemShippingCost'];

			//change to money format
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientSubtotal']
								= money_format('%i',$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientSubtotal']);
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientDiscountAmount']
								= money_format('%i',$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientDiscountAmount']);
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount']
								= money_format('%i',$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientTotalAmount']);
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientShippingCost']
								= money_format('%i',$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientShippingCost']);
			
			
			// Order Recipient Items
			if(!isset($data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientItems']))
				$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientItems'] = array();
				
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientItems'][] = array(
				'sgsItemImage' 			=> $row['sgsItemImage'],
				'sgsItemName'			=> $row['sgsItemName'],
				'sgsItemPrice'			=> money_format('%i',$row['sgsItemPrice']),
				'sgsItemPriceVariance'	=> money_format('%i',$price_variance),
				'sgsTotalItemPrice'		=> money_format('%i', ($price_variance + $row['sgsItemPrice'])),
				'sgsItemDelivery'		=> $row['sgsItemDelivery'],
				'sgsItemShippingCost'	=> money_format('%i',$row['sgsItemShippingCost']),
				'sgsItemType'			=> $row['delivery_type'] == "physical" ? "Mail" : "Electronic",
				'delivery_type'			=> $row['delivery_type'],
				'sgsItemDiscountAmount'	=> money_format('%i',$sgsItemDiscountAmount)
			);
			
			
		}
		$sgs_currency_symbol = !empty($arr_currency_data[$sgs_currency]['currency_symbol']) ? $arr_currency_data[$sgs_currency]['currency_symbol'] : $sgs_currency . ' ';
		$data['sgsCurrencySymbol'] = $sgs_currency_symbol;
		$data['sgsBuyerItemCount']		= count($recipient_data);

		//change to money format
		$data['sgsBuyerShippingAmount']			= money_format('%i',$data['sgsBuyerShippingAmount']);
		$data['sgsBuyerSubtotalAmount']			= money_format('%i',$data['sgsBuyerSubtotalAmount']);
		$data['sgsBuyerDiscountAmount']			= ''.money_format('%i',$data['sgsBuyerDiscountAmount']) .'';
		$data['sgsBuyerTotalAmount']			= money_format('%i',$data['sgsBuyerTotalAmount']);

		
		
		// Getting email addresses of the company owners to send the email to
		$company_owner_emails = array();
		$sql = "select u.email from users_companies uc inner join users u on uc.users_id = u.id where uc.companies_id = '$companyId'";
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $row)
			$company_owner_emails[] = $row['email'];

		// Getting email addresses of the company owners to send the email to
		if (!empty($companyEmail)) {
			$c_emails = explode(',', $companyEmail);
			foreach ($c_emails as  $c_email) {
				$c_email = trim($c_email);
				if (!empty($c_email) && $c_email != null && is_rfc3696_valid_email_address($c_email)) {
					$company_owner_emails[] = $c_email;
				}
			}
		}

		$company_owner_emails = array_values(array_unique($company_owner_emails));
		
		$report_emails = array();
		if (!empty($data['sgsReportRecipients'])) {
			$r_emails = explode(',', $data['sgsReportRecipients']);
			foreach ($r_emails as $r_email) {
				$r_email = trim($r_email);
				if (!empty($r_email) && $r_email != null && is_rfc3696_valid_email_address($r_email)) {
					$report_emails[] = $r_email;
				}
			}
		}
		
		$sendEmailTo = array_values(array_unique(array_merge($company_owner_emails, $report_emails)));
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo'] = $sendEmailTo;
		$data['companyId'] = $companyId;

		return $data;
	}
	
		
	// Gets Email Data for SGS Alert where Shipping Address is needed
	public static function getSGSAddressNeededEmailData($sgs_order_recipient_id)
	{
		$sql = "select c.facebook_page_id, concat(sor.recipient_first_name, ' ' , sor.recipient_last_name) as recipient_name, sor.recipient_email, 
		sor.delivery_type, sor.delivery_method, sor.delivery_address, date_format(date_add(so.paid_date_time, interval 14 day), '%m/%d/%Y') as expDate,  sor.sgs_item_id as itemId, si.image_url, si.display_name as sgsItemName, u.email as recipient_user_email, c.id as companyId, b.email as buyer_email, b.id as user_id,
		concat(so.bill_firstname, ' ', so.bill_lastname) as bill_name, so.bill_address1, so.bill_address2, so.bill_city, so.bill_state, so.bill_country, so.bill_zip, so.bill_phone, so.bill_email,
sio0.label as sgsItemOption0, siv0.value as sgsItemOption0Value, sio1.label as sgsItemOption1, siv1.value as sgsItemOption1Value
			from sgs_order_recipients sor
			inner join sgs_items si on sor.sgs_item_id = si.id
			inner join companies c on si.company_id = c.id
			inner join sgs_orders so on sor.sgs_order_id = so.id
			inner join users b on so.buyer_id = b.id
			left join users u on sor.recipient_user_id = u.id
			left join sgs_item_options_data sid on sor.sgs_item_option_data_id = sid.id
			left join sgs_item_option_values siv0 on sid.sgs_item_option_value_0 = siv0.id
			left join sgs_item_option_values siv1 on sid.sgs_item_option_value_1 = siv1.id
			left join sgs_item_options sio0 on siv0.sgs_item_option_id = sio0.id
			left join sgs_item_options sio1 on siv1.sgs_item_option_id = sio1.id
			where sor.id = '$sgs_order_recipient_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		
		global $socgift_app_url;
		global $app_version;
		$data['appURL'] = $socgift_app_url;
		// Getting company facebook page name
		$company_fb_data = json_decode(file_get_contents("https://graph.facebook.com/v" . $app_version . "/".$data['facebook_page_id']), true);
		$data['fbPageName'] = !empty($company_fb_data['name']) ? $company_fb_data['name'] : '';
		$data['sgsItemImage'] = !empty($data['image_url']) ? "http://sgsimg.coupsmart.com/" . $data['image_url'] : Common::$default_sgs_item_img;
		$recipient_email = "";
		if(!empty($data['recipient_email']))
			$recipient_email = ' (' . $data['recipient_email'] . ')';
		else if(!empty($data['recipient_user_email']))
			$recipient_email = ' (' . $data['recipient_user_email'] . ')';
		
			
		$data['sgsItemRecipient'] = $data['recipient_name'] . $recipient_email;
		
		//	Setting any SGS Item Option Data if it was selected
		$arr_sgs_item_option_data = array();
		// SGS Item Option 0
		if(!empty($data['sgsItemOption0']))
			$arr_sgs_item_option_data[] = $data['sgsItemOption0'] . ": " . $data['sgsItemOption0Value'];
		// SGS Item Option 1
		if(!empty($data['sgsItemOption1']))
			$arr_sgs_item_option_data[] = $data['sgsItemOption1'] . ": " . $data['sgsItemOption1Value'];
		
		if(!empty($arr_sgs_item_option_data))
			$data['sgsItemName'] .= " (" . implode(', ', $arr_sgs_item_option_data). ")";
		
		// Keeping this static for now as suggested by Murray
		if($data['delivery_type'] == 'physical' && empty($data['delivery_address']))
		{
			$data['error'] = "Recipient has not filled out their address, please go to Facebook to enter an address for the item to ship.";
		
		}
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo'] = $data['buyer_email'];
		$data['companyId'] = $data['companyId'];
		$data['userId'] = $data['user_id'];
		
		return $data;
	}
	
	public static function getSGSNotifyUpdatedAddressData($updated_sgs_order_recipient_id)
	{
		$sgs_order_recipient = new SGS_Order_Recipient($updated_sgs_order_recipient_id);
		$sgs_order_id = $sgs_order_recipient->sgs_order_id;
		$data = EmailTemplates::getSGSNotifyOrderEmailData($sgs_order_id, $updated_sgs_order_recipient_id);
		//error_log(var_export($data, true));
		// Setting the addressUpdated and sgs
		$recipient_name = $sgs_order_recipient->recipient_first_name . ' ' .  $sgs_order_recipient->recipient_last_name;
		//error_log('recipient_name: ' . $recipient_name);
		$data['sgsOrderRecipients'][$recipient_name]['addressUpdated'] = 1;
		$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientAddress'] = $sgs_order_recipient->delivery_address;
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientCity'] = $sgs_order_recipient->delivery_city;
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientState'] = $sgs_order_recipient->delivery_state;
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientZip'] = $sgs_order_recipient->delivery_zip;
			$data['sgsOrderRecipients'][$recipient_name]['sgsOrderRecipientCountry'] = $sgs_order_recipient->delivery_country;
		
		// Getting email addresses of the company owners to send the email to
		if (!empty($data['sgsReportRecipients'])) {
			$c_emails = explode(',', $data['sgsReportRecipients']);
			foreach ($c_emails as  $c_email) {
				$c_email = trim($c_email);
				if (!empty($c_email) && $c_email != null && is_rfc3696_valid_email_address($c_email)) {
					$data['sendEmailTo'][] = $c_email;
				}
			}
			$data['sendEmailTo'] = array_values(array_unique($data['sendEmailTo']));
		}
		return $data;
	}
	
	public static function getSGSNotifyPurchaserUpdatedAddressData($sgs_order_recipient_id)
	{
		$data = EmailTemplates::getSGSNotifyUpdatedAddressData($sgs_order_recipient_id);
		
		$sql = "select sor.sgs_order_id as orderNumber, sor.delivery_date as sgsItemDelivery, sor.recipient_first_name as sgsItemRecipientFirstName, sor.recipient_last_name sgsItemRecipientLastName, sor.recipient_email, sor.delivery_address as sgsItemRecipientAddress, sor.delivery_city as sgsItemRecipientCity, sor.delivery_state as sgsItemRecipientState, sor.delivery_zip as sgsItemRecipientZip
		from sgs_order_recipients sor
		where sor.id = '$sgs_order_recipient_id'";
		
		$order_data = BasicDataObject::getDataRow($sql);
		$data = array_merge($data, $order_data);
		return $data;
		error_log('SGS OrderID' . var_export($data, true));
	}
	
	// Gets Email Data for SGS Alert where a recipient is notifified of the gift received
	public static function getSGSRecipientNotificationEmailData($sgs_order_recipient_id)
	{
		$sql = "select c.facebook_page_id, concat(u.firstname, ' ', u.lastname) as sgsBuyerName, si.image_url, c.id as companyId, sor.recipient_email, 
			r.id as recipient_user_id, sor.delivery_method, sor.delivery_type, sor.delivery_address, sor.email_code
			from sgs_order_recipients sor
			inner join sgs_items si on sor.sgs_item_id = si.id
			inner join sgs_orders so on sor.sgs_order_id = so.id
			inner join users u on so.buyer_id = u.id
			inner join companies c on si.company_id = c.id
			left join users r on sor.recipient_email = r.email
			where sor.id = '$sgs_order_recipient_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		
		// Getting company facebook page name
		$company_fb_data = json_decode(file_get_contents("https://graph.facebook.com/".$data['facebook_page_id']), true);
		$data['fbPageName'] = !empty($company_fb_data['name']) ? $company_fb_data['name'] : '';
		
		// Keeping the image static for now as suggested by Murray
		$data['sgsGiftImage'] = "http://s3.amazonaws.com/sgsimg.coupsmart.com/giftwrap.png";
		// $data['sgsGiftImage'] = !empty($data['image_url']) ? "http://sgsimg.coupsmart.com/" . $data['image_url'] : Common::$default_sgs_item_img;
		
		// Redirect URL
		if($data['delivery_method'] == 'anonymous')
		{
			$redirect_url = Common::getBaseURL() . "/sgs-gifts-via-email?c=" . urlencode($data['email_code']);
			$data['redirectMessage'] = "Click <a href='$redirect_url' target='_blank'>here</a> to find out what you";
			if($data['delivery_type'] == 'voucher')
				$data['redirectMessage'] .= " received and claim your gift.";
			else if($data['delivery_type'] == 'physical')
			{
				if(!empty($data['delivery_address']))
					$data['redirectMessage'] .= "'ve received.";
				else
					$data['redirectMessage'] .= "'ve received and enter your mailing address for shipment.";
			}
			
		}
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo'] = $data['recipient_email'];
		$data['companyId'] = $data['companyId'];
		$data['userId'] = $data['recipient_user_id'];
		
		return $data;
	}
	
	// Gets Instore Deal Data for the email to be sent to the user
	public static function getInstoreSaveLaterData($param)
	{
		list($user_id, $item_id, $items_views_id) = array($param['user_id'], $param['item_id'], $param['items_views_id']);
		$location_id = !empty($param['location_id']) ? $param['location_id'] : null;
		$product_id = !empty($param['product_id']) ? $param['product_id'] : null;
		
		$user = new User($user_id);
		
		$sql = "select c.display_name as company_name, c.copyright, c.email, c.phone, i.id as itemId, i.platform_social_offer_value, i.name, i.expires, i.instore_email_print_btn, i.instore_email_onscreen_btn, i.instore_email_footer_content, c.default_coupon_image, c.id as companyId, i.e_commerce_code, camp.convercial_type, i.instore_email_from as `from`, i.instore_email_subject as subject, i.instore_email_header_img as emailHeaderImageNew, i.instore_email_header_caption as emailHeaderCaption, i.use_bundled_coupons, c.mo_header_caption 
			from items i
			inner join companies c on i.manufacturer_id = c.id
			inner join campaigns camp on i.campaign_id = camp.id
			where i.id = '".Database::mysqli_real_escape_string($item_id)."'";
		
		$data = BasicDataObject::getDataRow($sql);
		// Deal Name
		$deal_name = !empty($data['platform_social_offer_value']) ? $data['platform_social_offer_value'] : $data['name'];
		$data['emailHeaderImageNew'] = 'http://s3.amazonaws.com/uploads.coupsmart.com/' . $data['emailHeaderImageNew'];
		
		if(empty($data['emailHeaderCaption']))
			$data['emailHeaderCaption'] = !empty($data['mo_header_caption']) ? $data['mo_header_caption'] : $data['company_name'];
			
		// Print URL
		$back_url = '';
		// if(!empty($data['convercial_type'])){
			if($data['convercial_type'] == 'location'){
				$back_url = 'instore?l=' . $location_id;
			} else {
				$back_url = 'instore?p=' . $item_id;
			}
		// }
		// $items_views_id = Item::getItemsViewsIdByItemId($item_id);
		
		// $print_url = Common::getBaseURL() . "/print?item_id=" . urlencode($item_id) . ";user_id=" . urlencode($user_id) . ";back_url=$back_url;items_views_id=$items_views_id&email=1&app_name=convercial";
		
		$print_url = Common::getBaseURL(true) . "/helpers/facebook/application/print.php?item_id=" . urlencode($item_id) . "&user_id=" . urlencode($user_id) . "&back_url=$back_url&items_views_id=" . urlencode($items_views_id) . "&email=1&app_name=convercial";
		
		if(!empty($data['use_bundled_coupons']))
			$print_url = Common::getBaseURL() . "/bundled-coupons?item_id=" . urlencode($item_id) . ";user_id=" . urlencode($user_id) . ";back_url=$back_url;items_views_id=$items_views_id&email=1";
			
		if(!empty($param['smart_link_id']))
		{
			$view_code = UUID::v4();
			Item::updateInstoreEmailCode($item_id, $view_code);
			$print_url .= ";view_code=" . urlencode($view_code);
		}
		
		// Screen URL (if applicable)
		$screen_url = !empty($location_id) ? Common::getCouptBaseURL(true) . "/instore?l=" . $location_id : '';
		
		// Expiry Message (if applicable)
		$exp_message = !empty($data['expires']) ? 'You have until <span class="expirationdate" style="font-weight:bold;">' 
					. Common::convertMySQLDateToSpecifiedFormat('m/d/Y', $data['expires']) 
					. '</span> to use this offer.' : '';
					
		// Default Coupon Image
		$default_coupon_image = !empty($data['default_coupon_image']) ? "http://s3.amazonaws.com/uploads.coupsmart.com/" . $data['default_coupon_image'] : "http://s3.amazonaws.com/uploads.coupsmart.com/default_coupons.png";
		
		$data['deal_name'] 			= $deal_name;
		$data['print_url'] 			= $print_url;
		$data['screen_url'] 			= $screen_url;
		$data['exp_message'] 		= $exp_message;
		//$data['unsubscribe_link'] 	= Common::getBaseURL(true) . "/unsubscribe";
		$data['default_coupon_image'] = $default_coupon_image;
		
		if(!empty($param['custom_code']))
			$data['customCode'] = $param['custom_code'];
		if(!empty($param['expiry_date']))
			$data['expiryDate'] = $param['expiry_date'];
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo'] = $user->email;
		$data['companyId'] 	= $data['companyId'];
		$data['userId'] 		= $user_id;
		$data['compName'] 	= $data['company_name'];	
		return $data;
	}
	/*
	public static function getCSCEmailTemplateData($user_item_id)
	{
		
		$sql = "select ui.user_id, ui.item_id, u.email as sendEmailTo, c.id as companyId, c.display_name as compName, i.csc_custom_code as custom_code, i.csc_email_header_image as email_header_image, i.instore_email_header_caption as emailHeaderCaption, i.csc_email_store_url as emailStoreURL 
		from user_items ui 
		inner join items i on ui.item_id = i.id
		inner join companies c on i.manufacturer_id = c.id
		inner join users u on ui.user_id = u.id
		where ui.id = '$user_item_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		$csc_custom_code = $data['custom_code'];
		
		if(!empty($csc_custom_code) && $csc_custom_code == 'customCode')
		{
			$sql = "select custom_code from customer_supplied_code where user_item_id = '$user_item_id'";
			$row = BasicDataObject::getDataRow($sql);
			$data['custom_code'] = $row['custom_code'];
		}
		$data['emailStoreURL'] = empty($data['emailStoreURL']) ? "http://coupsmart.com" : $data['emailStoreURL'];
			
		return $data;
	}
	*/
	
	public static function getCSCEmailTemplateData($param)
	{
		list($user_id, $item_id, $email, $items_views_id) = array($param['user_id'], $param['item_id'], $param['email'], $param['items_views_id']);
		$sql = "select '$email' as sendEmailTo, c.id as companyId, c.display_name as compName, i.csc_custom_code as custom_code, i.csc_email_header_image as email_header_image, i.instore_email_header_img, c.mo_header_caption, i.csc_email_store_url as emailStoreURL, i.instore_email_from as fromName, i.instore_email_header_caption as emailHeaderCaption
		from items i
		inner join companies c on i.manufacturer_id = c.id
		where i.id = '$item_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		$csc_custom_code = $data['custom_code'];
		
		if(empty($data['emailHeaderCaption']))
			$data['emailHeaderCaption'] = !empty($data['mo_header_caption']) ? $data['mo_header_caption'] : $data['compName'];
		
		if(!empty($csc_custom_code) && $csc_custom_code == 'customCode')
		{
			$sql = "select custom_code from customer_supplied_code where user_item_id = '$user_item_id'";
			$row = BasicDataObject::getDataRow($sql);
			$data['custom_code'] = $row['custom_code'];
		}
		// TODOs: Pass in a signature for security purposes
		$csapi = new CSAPI();
		$query_string_params = array('item_id' => $item_id, 'user_id' => $user_id, 'items_views_id' => $items_views_id, 'r' => uniqid());
		$signature = $csapi->generateCSAPISignature($query_string_params);
		$query_string_params['sig'] = $signature;
		// error_log("query_string_params: " . var_export($query_string_params, true));
		$str_query_string = http_build_query($query_string_params);
		$claimCodeURL = Common::getBaseURL(true) . "/print-coupon-code?$str_query_string";
		$data['claimCodeURL'] = $claimCodeURL;
		
		$csc_params = array(
			'user_id' => $user_id,
			'item_id' => $item_id,
			'items_views_id' => $items_views_id,
			'app_name' => 'convercial',
			'skip_user_registration' => 1,
		);
		$str_csc_params = http_build_query($csc_params);
		$customerSuppliedCodeURL = Common::getBaseURL(true) . "/helpers/ajax-customer-supplied-code.php?$str_csc_params";
		$data['customerSuppliedCodeURL'] = $customerSuppliedCodeURL;
		
		return $data;
	}
	
	public static function getMobileOfferSentData($param)
	{
		list($user_id, $item_id) = array($param['user_id'], $param['item_id']);

		$data = array();
		$user = new User($user_id);
		
		$sql = "select c.display_name as company_name, i.id as itemId, c.id as companyId, c.facebook_page_id, camp.img_instore_deals, i.instore_email_from as `from`, i.instore_email_subject as subject, i.instore_email_header_img as emailHeaderImageNew, i.instore_email_header_caption as emailHeaderCaption, i.use_bundled_coupons, c.mo_header_caption 
			from items i
			inner join companies c on i.manufacturer_id = c.id
			inner join campaigns camp on i.campaign_id = camp.id
			where i.id = '".Database::mysqli_real_escape_string($item_id)."'";
		
		$data = BasicDataObject::getDataRow($sql);
		$data['emailHeaderImageNew'] = 'http://s3.amazonaws.com/uploads.coupsmart.com/' . $data['emailHeaderImageNew'];
		$items_views_id = Item::getItemsViewsIdByItemId($item_id);
		
		if(empty($data['emailHeaderCaption']))
			$data['emailHeaderCaption'] = !empty($data['mo_header_caption']) ? $data['mo_header_caption'] : $data['company_name'];
		
		// Print URL
		$print_url = Common::getBaseURL() . "/print?item_id=" . urlencode($item_id) . ";user_id=" . urlencode($user_id) . ";items_views_id=" . urlencode($items_views_id) . "&email=1";
		
		// $print_url = Common::getBaseURL(true) . "/helpers/facebook/application/print.php?item_id=" . urlencode($item_id) . "&user_id=" . urlencode($user_id) . "&back_url=$back_url&items_views_id=" . urlencode($items_views_id) . "&email=1";
		
		if(!empty($data['use_bundled_coupons']))
			$print_url = Common::getBaseURL() . "/bundled-coupons?item_id=" . urlencode($item_id) . ";user_id=" . urlencode($user_id) . ";back_url=$back_url;items_views_id=$items_views_id&email=1";
			
		if(!empty($param['smart_link_id']))
		{
			$view_code = UUID::v4();
			Item::updateInstoreEmailCode($item_id, $view_code);
			$print_url .= ";view_code=" . urlencode($view_code);
		}
		
		$data['print_url'] 			= $print_url;

		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo'] = $user->email;
		$data['companyId'] 	= $data['companyId'];
		$data['userId'] 		= $user_id;
		
		return $data;
	}
	
	
	public static function getPaymentRequestData($payment_request_id)
	{
		$sql = "select pr.email, pr.message, pr.amount, pr.signature, u.id as user_id, concat(sp.firstname, ' ', sp.lastname) as salesPersonName, sp.email as salesPersonEmail
			from payment_requests pr 
			left join users u on pr.email = u.email
			left join users sp on pr.sales_person_id = sp.id
			where pr.id = '".Database::mysqli_real_escape_string($payment_request_id)."'";
			
		$data = BasicDataObject::getDataRow($sql);
		$data['paymentURL'] = Common::getBaseURL(true) . "/payment?c=" . urlencode($data['signature']);
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo']	= $data['email'];
		$data['userId'] 		= $data['user_id'];
		$data['sendEmailCC'] = array('khoeffer@coupsmart.com', $data['salesPersonEmail']);
		
		return $data;
	}
	
	
	public static function getPaymentConfirmationData($payment_request_id)
	{
		$sql = "select pr.amount, pr.cardname, pr.bill_firstname, pr.bill_lastname, pr.bill_address1, pr.bill_address2, bill_country, bill_state, bill_city, bill_zip, bill_phone, sp.email as salesPersonEmail, pr.gateway_trans_id as transaction_id
			from payment_requests pr 
			left join users sp on pr.sales_person_id = sp.id
			where pr.id = '".Database::mysqli_real_escape_string($payment_request_id)."'";
			
		$data = BasicDataObject::getDataRow($sql);
		
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo']	= $data['salesPersonEmail'];
		$data['userId'] 		= $data['user_id'];
		$data['sendEmailCC'] = 'khoeffer@coupsmart.com';
		
		return $data;
	}
	
	public static function getSmartEmailLayoutData($smart_email_info)
	{
		$data = array();
		
		$data['include_deal']	= $smart_email_info['include_deal'];
		$data['txt_body_copy'] 	= $smart_email_info['body_copy'];
		$data['txt_signature'] 	= $smart_email_info['signature'];
		$data['header_img'] 		= "http://uploads.coupsmart.com/".$smart_email_info['header_img'];
		
		// Styles
		$data['style_body_copy'] 		= '';
		if(!empty($smart_email_info['text_color']))
			$data['style_body_copy'] .= "color:" . $smart_email_info['text_color'] . ";";
		if(!empty($smart_email_info['text_bg_color']))
			$data['style_body_copy'] .= "background-color:" . $smart_email_info['text_bg_color'] . ";";
		
		$data['style_header_img'] 		= '';
		if(!empty($smart_email_info['img_bg_color']))
			$data['style_header_img'] .= "background-color:" . $smart_email_info['img_bg_color'] . ";";
			
		$data['style_deal_preview']	= '';
		if(!empty($smart_email_info['deal_bg_color']))
			$data['style_deal_preview'] .= "background-color:" . $smart_email_info['deal_bg_color'] . ";";
		
		$base_url = Common::getBaseURL(true);
		if($data['include_deal'] == '1')
		{
			$expiration_date = !empty($smart_email_info['expires']) ? date('m/d/Y', strtotime($smart_email_info['expires'])) : '-';
			$str_params = "item_id=" . $smart_email_info['item_id'] . "&is_preview=1";
			$data['deal_preview'] =  $base_url . "/helpers/render-coupon-using-layout.php?" . $str_params;
			
			$user_email_code = Common::generate_unique_sig("user_emails", "user_email_code");
			$deal_url = $base_url . "/smart-email-print-coupon?c=".$user_email_code;
			$data['deal_url'] =  $deal_url;
		}
		
		$data['subject'] 		= $smart_email_info['subject_line'];
		$data['userId'] 		= $smart_email_info['user_id'];
		$data['sendEmailTo'] = $smart_email_info['email'];
		$data['companyId'] 	= $smart_email_info['company_id'];
		return $data;
	}
	
	public static function getFBNotifyReminderData($sgs_order_recipient_id)
	{
		$data = array();
		global $socgift_app_url;
		$sql = "select concat(sor.recipient_first_name, ' ' , sor.recipient_last_name) as recipientName, 
			c.id as companyId, b.email as buyer_email, concat(b.firstname, ' ', b.lastname) as userName, 
			so.gateway_trans_id as orderNumber, so.bill_email as sendEmailTo,
			si.image_url as sgsItemImage, si.display_name as sgsItemName,
			sio0.label as sgsItemOption0, 
			siv0.value as sgsItemOption0Value, sio1.label as sgsItemOption1, siv1.value as sgsItemOption1Value
			from sgs_order_recipients sor
			inner join sgs_items si on sor.sgs_item_id = si.id
			inner join companies c on si.company_id = c.id
			inner join sgs_orders so on sor.sgs_order_id = so.id
			inner join users b on so.buyer_id = b.id
			left join users u on sor.recipient_user_id = u.id
			left join sgs_item_options_data sid on sor.sgs_item_option_data_id = sid.id
			left join sgs_item_option_values siv0 on sid.sgs_item_option_value_0 = siv0.id
			left join sgs_item_option_values siv1 on sid.sgs_item_option_value_1 = siv1.id
			left join sgs_item_options sio0 on siv0.sgs_item_option_id = sio0.id
			left join sgs_item_options sio1 on siv1.sgs_item_option_id = sio1.id
			where sor.id = '". Database::mysqli_real_escape_string($sgs_order_recipient_id). "'";
		$data = BasicDataObject::getDataRow($sql);
		
		$data['sgsItemImage'] = !empty($data['sgsItemImage']) ? 
			"http://sgsimg.coupsmart.com/" . $data['sgsItemImage'] : Common::$default_sgs_item_img;
		//	Setting any SGS Item Option Data if it was selected
		$arr_sgs_item_option_data = array();
		// SGS Item Option 0
		if(!empty($data['sgsItemOption0']))
			$arr_sgs_item_option_data[] = $data['sgsItemOption0'] . ": " . $data['sgsItemOption0Value'];
		// SGS Item Option 1
		if(!empty($data['sgsItemOption1']))
			$arr_sgs_item_option_data[] = $data['sgsItemOption1'] . ": " . $data['sgsItemOption1Value'];
		
		if(!empty($arr_sgs_item_option_data))
			$data['sgsItemName'] .= " (" . implode(', ', $arr_sgs_item_option_data). ")";
		
		$data['appURL'] = $socgift_app_url;
		return $data;
	}
	
	public static function getNotifyPurchaserCancellationData($sgs_order_id)
	{
		$sql = "select so.bill_email as userEmailAddress, concat(so.bill_firstname, ' ', so.bill_lastname) as userName, 
			so.bill_address1, so.bill_address2, so.bill_city, so.bill_state, so.bill_country, so.bill_zip,
			so.gateway_trans_id as userOrderNumber, count(sor.id) as num_gifts, c.display_name as companyName, c.id as companyId, si.id as itemId, so.bill_email as sendEmailTo
			from sgs_orders so
			inner join sgs_order_recipients sor on so.id = sor.sgs_order_id
			inner join sgs_items si on sor.sgs_item_id = si.id
			inner join companies c on si.company_id = c.id
			where so.id = '".Database::mysqli_real_escape_string($sgs_order_id)."'
			group by so.id";
			// error_log($sql);
		$data = BasicDataObject::getDataRow($sql);
		$arr_address = array();
		$arr_address[] = $data['bill_address1'];
		if(!empty($data['bill_address2']))
			$arr_address[] = $data['bill_address2'];
		if(!empty($data['bill_address2']))
			$arr_address[] = $data['bill_address2'];
		if(!empty($data['bill_city']))
			$arr_address[] = $data['bill_city'];
		if(!empty($data['bill_state']))
			$arr_address[] = $data['bill_state'];
		if(!empty($data['bill_country']))
			$arr_address[] = $data['bill_country'];
		if(!empty($data['bill_zip']))
			$arr_address[] = $data['bill_zip'];
		$data['userAddress'] = implode(', ', $arr_address);
		return $data;
	}
	
	
	public static function getNotifyClientCancellationData($sgs_refund_id)
	{
		$data = array();

		$sql = "select sr.request_email_sig, sr.request_reason, sr.request_reason_other, sr.client_action_taken, sr.client_action_text, concat(sor.recipient_first_name, ' ' , sor.recipient_last_name) as recipientName, 
			c.id as companyId, c.email as companyEmail, b.email as buyer_email, so.bill_email as userEmailAddress, concat(so.bill_firstname, ' ', so.bill_lastname) as userName, 
			so.gateway_trans_id as userOrderNumber, so.bill_address1, so.bill_address2, so.bill_city, so.bill_state, so.bill_country, so.bill_zip, si.image_url as sgsItemImage, si.display_name as sgsItemName, si.id as itemId,
			sio0.label as sgsItemOption0, 
			siv0.value as sgsItemOption0Value, sio1.label as sgsItemOption1, siv1.value as sgsItemOption1Value
			from sgs_refunds sr
			inner join sgs_order_recipients sor on sr.sgs_order_recipients_id = sor.id
			inner join sgs_items si on sor.sgs_item_id = si.id
			inner join companies c on si.company_id = c.id
			inner join sgs_orders so on sor.sgs_order_id = so.id
			inner join users b on so.buyer_id = b.id
			left join users u on sor.recipient_user_id = u.id
			left join sgs_item_options_data sid on sor.sgs_item_option_data_id = sid.id
			left join sgs_item_option_values siv0 on sid.sgs_item_option_value_0 = siv0.id
			left join sgs_item_option_values siv1 on sid.sgs_item_option_value_1 = siv1.id
			left join sgs_item_options sio0 on siv0.sgs_item_option_id = sio0.id
			left join sgs_item_options sio1 on siv1.sgs_item_option_id = sio1.id
			where sr.id = '". Database::mysqli_real_escape_string($sgs_refund_id). "'";
		$data = BasicDataObject::getDataRow($sql);
		
		$data['sgsItemImage'] = !empty($data['sgsItemImage']) ? 
			"http://sgsimg.coupsmart.com/" . $data['sgsItemImage'] : Common::$default_sgs_item_img;
		//	Setting any SGS Item Option Data if it was selected
		$arr_sgs_item_option_data = array();
		// SGS Item Option 0
		if(!empty($data['sgsItemOption0']))
			$arr_sgs_item_option_data[] = $data['sgsItemOption0'] . ": " . $data['sgsItemOption0Value'];
		// SGS Item Option 1
		if(!empty($data['sgsItemOption1']))
			$arr_sgs_item_option_data[] = $data['sgsItemOption1'] . ": " . $data['sgsItemOption1Value'];
		
		if(!empty($arr_sgs_item_option_data))
			$data['sgsItemName'] .= " (" . implode(', ', $arr_sgs_item_option_data). ")";
		
		$arr_address = array();
		$arr_address[] = $data['bill_address1'];
		if(!empty($data['bill_address2']))
			$arr_address[] = $data['bill_address2'];
		if(!empty($data['bill_address2']))
			$arr_address[] = $data['bill_address2'];
		if(!empty($data['bill_city']))
			$arr_address[] = $data['bill_city'];
		if(!empty($data['bill_state']))
			$arr_address[] = $data['bill_state'];
		if(!empty($data['bill_country']))
			$arr_address[] = $data['bill_country'];
		if(!empty($data['bill_zip']))
			$arr_address[] = $data['bill_zip'];
		$data['userAddress'] = implode(', ', $arr_address);
		
		// URL of the client action page (I've named it sgs-refund-cancellation-action for now)
		$data['actionPageURL'] = Common::getBaseURL(true) . "/sgs-refund-cancellation-action?c=" . urlencode($data['request_email_sig']);
		
		// Getting email addresses of the company owners to send the email to
		$company_owner_emails = array($data['companyEmail']);
		$sql = "select u.email from users_companies uc inner join users u on uc.users_id = u.id where uc.companies_id = '" . $data['companyId'] . "'";
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $row)
			$company_owner_emails[] = $row['email'];

		$company_owner_emails = array_values(array_unique($company_owner_emails));
		
		// Setting Client Action Taken
		if(!empty($data['client_action_taken']))
			$data['client_action_taken'] = SGS_Refund::$refund_actions[$data['client_action_taken']][2];
			
		// Note: These 2 attributes MUST be set in each of the 4 functions that retrieve the email data
		$data['sendEmailTo'] = $company_owner_emails;
		
		return $data;
	}
	
	public static function getNotifyPurchaserOfClientsAction($sgs_refund_id)
	{
		$data = EmailTemplates::getNotifyClientCancellationData($sgs_refund_id);
		$data['sendEmailTo'] = $data['userEmailAddress'];
		return $data;
	}
	
	public static function getNotifyOrderCancelledData($sgs_refund_id)
	{
		$data = EmailTemplates::getNotifyClientCancellationData($sgs_refund_id);
		$data['sendEmailTo'] = $data['userEmailAddress'];
		return $data;
	}
	
	public static function getNotifyOrderNotCancelledData($sgs_refund_id)
	{
		$data = EmailTemplates::getNotifyClientCancellationData($sgs_refund_id);
		$data['sendEmailTo'] = $data['userEmailAddress'];
		return $data;
	}
	
	public static function getNotifySmartDealUserEmails($params)
	{
		global $app_url;
		
		$data = array();
		$data['appURL'] = $app_url;
		$data['sendEmailTo'] = $params['to'];
		$data['companyId'] 	= $params['company_id'];
		
		return $data;
	}
	
	public static function getNotifyCouponPrinted($uiid)
	{
		global $app_url;
		
		$data = array();
		
		$sql = "select ui.id, ui.item_id, ui.user_id, u.email, i.manufacturer_id as company_id
		from user_items ui
		inner join users u on ui.user_id = u.id
		inner join items i on ui.item_id = i.id
		where ui.uiid = '" . Database::mysqli_real_escape_string($uiid). "'";
		
		$row = BasicDataObject::getDataRow($sql);

		$data['sendEmailTo'] = $row['email'];
		$data['companyId'] 	= $row['company_id'];
		
		return $data;
	}
	
	public static function getDonationReceipt($charge_id)
	{

		
		$sql = "select d.user_id, d.facebook_id, d.company_id as companyId, d.card_name as firstName, d.amount as donationAmount, d.email as sendEmailTo, c.donation_email_subject as subject, c.sgs_email_header_img, c.facebook_page_id as facebookPageId, c.display_name as facebookPageName, c.display_name as `from`, c.donation_charity as donationCharity
		from donations d 
		inner join companies c on d.company_id = c.id
		where d.charge_id = '" . Database::mysqli_real_escape_string($charge_id) . "'";
		
		$data = BasicDataObject::getDataRow($sql);
		
		
		return $data;
	}
	
	public static function getClientBugReport($bug_report_id)
	{
		$sql = "select cbr.*
		from client_bug_report cbr 
		where cbr.id = '" . Database::mysqli_real_escape_string($bug_report_id) . "'";		
		$data = BasicDataObject::getDataRow($sql);
		$data['sendEmailTo'] = "support@coupsmart.com";
		return $data;
	}
	
	public static function getBillingSignupInfo($billing_signup_id)
	{
		require_once(dirname(__DIR__) . '/includes/app_config.php');
		global $account_manager_info;
		
		$sql = "select u.email as sendEmailTo, u.firstname as firstName
		from cc_billing_signups bs 
		inner join users u on bs.user_id = u.id
		where bs.id = '$billing_signup_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		$data['accountManagerFirstname'] = $account_manager_info['firstname'];
		$data['accountManagerLastname'] = $account_manager_info['lastname'];
		$data['accountManagerEmail'] = $account_manager_info['email'];
		$data['sendEmailBCC'] = $account_manager_info['email'];
		return $data;
	}
	
	public static function getClientSetupInfo($params)
	{
		list($email, $company_id, $company_name, $first_name) = array($params['email'], $params['company_id'], $params['company_name'], $params['firstname']);
		
		
		$csapi = new CSAPI();
		$query_string_params = array('company_id' => $company_id, 'r' => md5(uniqid()));
		$query_string_params['email'] = $params['email'];
		if(!empty($params['uniqueKey']))
			$query_string_params['unique_key'] = $params['uniqueKey'];
		$confirmation_page = !empty($params['confirmationPage']) ? $params['confirmationPage'] : 'client-setup-finalize';
		$signature = $csapi->generateCSAPISignature($query_string_params);
		$query_string_params['sig'] = $signature;
		// error_log("query_string_params: " . var_export($query_string_params, true));
		$str_query_string = http_build_query($query_string_params);
		
		$confirmation_url = Common::getBaseURL(true) . '/' . $confirmation_page. '?' . $str_query_string;
		
		$data['companyName'] = $company_name;
		$data['firstName'] = $first_name;
		if(!empty($params['lastname']))
			$data['lastName'] = $params['lastname'];
		$data['confirmationURL'] = $confirmation_url;
		$data['sendEmailTo'] = $email;
		$data['companyId'] 	= $company_id;
		return $data;
	}
	
	public static function getClientPaymentNotificationInfo($invoice_id)
	{
		require_once(dirname(__DIR__) . '/includes/app_config.php');
		global $credit_card_captions, $currency_symbols, $client_billing_product_types;
		$data = array();
		$sql = "select inv.amount, inv.company_id as companyId, inv.company_credit_card_id as companyCreditCardId, inv.invoice_id as invoiceNumber, date_format(now(), '%m/%d/%Y') as currentDate 
		from company_invoices inv where inv.id = '$invoice_id'";
		// error_log("sql for invoice: " . $sql);
		
		$row = BasicDataObject::getDataRow($sql);
		$data['amount']			= $row['amount'];
		$data['companyId']		= $row['companyId'];
		$data['companyCreditCardId']= $row['companyCreditCardId'];
		$data['invoiceNumber'] 	= $row['invoiceNumber'];
		$data['currentDate'] 	= $row['currentDate'];
		
		
		$sql = "select * from company_invoice_details where company_invoice_id = '$invoice_id'";
		$row = BasicDataObject::getDataRow($sql);
		$product_type = $row['product_type'];
		$invoice_description = $client_billing_product_types[$product_type];
		$data['description'] = $invoice_description;
		
		
		$sql = "select u.firstname, u.email, c.display_name as companyName, c.license_currency as currency
		from users_companies uc
		inner join users u on uc.users_id = u.id
		inner join companies c on uc.companies_id = c.id
		where uc.companies_id = '" . $data['companyId']. "'";
		$row = BasicDataObject::getDataRow($sql);
		$data['firstName'] 		= $row['firstname'];
		$data['sendEmailTo']	= $row['email'];
		$data['companyName']	= $row['companyName'];
		$data['currency']		= $row['currency'];
		$data['currencySymbol']	= $currency_symbols[$row['currency']];
		
		if(!empty($data['companyCreditCardId']))
		{
			$sql = "select card_type, card_number from company_credit_cards where id = '" . $data['companyCreditCardId'] . "'";
			// error_log("sql for company_credit_card: " . $sql);
			$row = BasicDataObject::getDataRow($sql);
			$data['cardType'] = $credit_card_captions[$row['card_type']];
			$data['cardNumber'] = substr($row['card_number'], -4);
		}
		// error_log('data in EmailTemplates::getClientPaymentNotificationInfo(): ' . var_export($data, true));
		return $data;
	}
	
	
	public static function getClientInvoiceInfo($params)
	{
		$invoice_id = $params['invoice_id'];
		
		$pdf_file_path = Company::GenerateInvoicePDF($invoice_id);
		$data['attachments'] = array($pdf_file_path);
		$data['deleteAttachmentsAfterEmail'] = true;
		$data['sendEmailTo'] = $params['email'];
		$data['sendEmailCC'] = 'khoeffer@coupsmart.com';
		$data['sendEmailBCC'] = 'sqazi@coupsmart.com';
		return $data;
	}
	
	
	public static function getBillingPaymentSuccessful($params)
	{
		require_once(dirname(__DIR__) . '/includes/app_config.php');
		global $account_manager_info;
		
		$billing_signup_id = $params['billing_signup_id'];
		$billing_item_id = $params['billing_item_id'];
		$card_number = $params['card_number'];
		
		$sql = "select u.email as sendEmailTo, u.firstname as first_name, concat(u.firstname, ' ', u.lastname) as client_name, date_format(now(), '%M %D, %Y') as date_today, bs.company_id, u.id as user_id
		from cc_billing_signups bs 
		inner join users u on bs.user_id = u.id
		where bs.id = '$billing_signup_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		
		$company_id	= $data['company_id'];
		$user_id	= $data['user_id'];
		
		$data['payment_type'] = $params['payment_type']; // monthly or annual license
		$data['desc_line1'] = $params['desc_line1']; // Descipription on line 1
		$data['desc_line2'] = $params['desc_line2']; // Descipription on line 2
		$data['desc_line3'] = $params['desc_line3']; // Descipription on line 3
		
		$amount_due = $params['amount_due'];
		
		$current_balance = $total_balance = $paid_balance = $amount_due;
		$final_balance = $total_balance - $paid_balance;
		
		$data['current_balance'] = money_format('%i', $current_balance);
		$data['total_balance'] = money_format('%i', $total_balance);
		$data['paid_balance'] = money_format('%i', $paid_balance);
		$data['final_balance'] = money_format('%i', $final_balance);
		
		$data['card_last_four_digits'] = substr($card_number, -4);
		
		$csapi = new CSAPI();
		$params = array('company_id' => $company_id, 'user_id' => $user_id, 'redirect' => 'billing');
		$sig = $csapi->generateCSAPISignature($params);
		$params['sig'] = $sig;
		
		$data['link_to_billing_section'] = Common::getBaseURL(true) . "/page_redirects.php?" . http_build_query($params);// "/manager/accountsettings?selected_company_id=$company_id&section=billing";
		$data['sendEmailBCC'] = $account_manager_info['email'];
		return $data;
	}
	
	public static function getBillingPaymentFailed($params)
	{
		require_once(dirname(__DIR__) . '/includes/app_config.php');
		global $account_manager_info;
		
		$billing_signup_id = $params['billing_signup_id'];
		$billing_item_id = $params['billing_item_id'];
		$card_number = $params['card_number'];
		$business_days_5 = $params['business_days_5'];
		
		$sql = "select u.email as sendEmailTo, u.firstname as first_name, concat(u.firstname, ' ', u.lastname) as cardName, bs.company_id, u.id as user_id
		from cc_billing_signups bs 
		inner join users u on bs.user_id = u.id
		where bs.id = '$billing_signup_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		$company_id = $data['company_id'];
		$user_id = $data['user_id'];
		
		$csapi = new CSAPI();
		$params = array('company_id' => $company_id, 'user_id' => $user_id, 'redirect' => 'billing');
		$sig = $csapi->generateCSAPISignature($params);
		$params['sig'] = $sig;
		
		$data['link_to_billing_section'] = Common::getBaseURL(true) . "/page_redirects.php?" . http_build_query($params);// "/manager/accountsettings?selected_company_id=$company_id&section=billing";
		$data['card_last_four_digits'] = substr($card_number, -4);
		$data['business_days_5'] = $business_days_5;
		$data['sendEmailBCC'] = $account_manager_info['email'];

		return $data;
	}
	
	public static function getBillingPaymentReminder($params)
	{
		require_once(dirname(__DIR__) . '/includes/app_config.php');
		global $account_manager_info;
		
		$billing_signup_id = $params['billing_signup_id'];
		
		$sql = "select u.email as sendEmailTo, u.firstname as first_name, concat(u.firstname, ' ', u.lastname) as cardName, bs.company_id, u.id as user_id
		from cc_billing_signups bs 
		inner join users u on bs.user_id = u.id
		where bs.id = '$billing_signup_id'";
		
		$data = BasicDataObject::getDataRow($sql);
		$company_id = $data['company_id'];
		$user_id = $data['user_id'];
		$data['renewalDate'] = $params['renewal_date'];
		
		$csapi = new CSAPI();
		$params = array('company_id' => $company_id, 'user_id' => $user_id, 'redirect' => 'billing');
		$sig = $csapi->generateCSAPISignature($params);
		$params['sig'] = $sig;
		
		$data['link_to_billing_section'] = Common::getBaseURL(true) . "/page_redirects.php?" . http_build_query($params);// "/manager/accountsettings?selected_company_id=$company_id&section=billing";
		$data['sendEmailBCC'] = $account_manager_info['email'];

		return $data;
	}
	
	public static function getMCDemoRequestData($param)
	{
		$data = $param;
		return $data;
	}
	
	
	public static function getDailyCampaignReportData($params)
	{
		$company_id = $params['company_id'];
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		
		$csapi = new CSAPI();	
		$views = $csapi->getCompanyViews(array('companyId' => $company_id), $arr_test_user_account_ids);
		$claims = $csapi->getCompanyClaims(array('companyId' => $company_id), $arr_test_user_account_ids);
		$v2c = round(($claims / $views) * 100, 2);
		
		$current_date = Common::getDBCurrentDate(0, 'day', '%Y-%m-%d');
		$yesterdays_date = Common::getDBCurrentDate(-1, 'day', '%Y-%m-%d');
		
		$data['currentDate']= Common::getDBCurrentDate(null, null, '%m/%d/%Y');
		$data['numViews']	= number_format($views, 0);
		$data['numClaims']	= number_format($claims, 0);
		$data['v2c']		= number_format($v2c, 2);
		
		
		
		$numViewsYesterday = $csapi->getCompanyViews(array('companyId' => $company_id, 'startDate' => $yesterdays_date, 'endDate' => $yesterdays_date, 'useDate' => 1), $arr_test_user_account_ids);
		$numViewsToday = $csapi->getCompanyViews(array('companyId' => $company_id, 'startDate' => $current_date, 'endDate' => $current_date, 'useDate' => 1), $arr_test_user_account_ids);
		
		$numClaimsYesterday = $csapi->getCompanyClaims(array('companyId' => $company_id, 'startDate' => $yesterdays_date, 'endDate' => $yesterdays_date, 'useDate' => 1), $arr_test_user_account_ids);
		$numClaimsToday = $csapi->getCompanyClaims(array('companyId' => $company_id, 'startDate' => $current_date, 'endDate' => $current_date, 'useDate' => 1), $arr_test_user_account_ids);
		
		$v2cRateToday = !empty($numClaimsToday) ? $numViewsToday / $numClaimsToday : 0;
		$v2cRateYesterday = !empty($numClaimsYesterday) ? $numViewsYesterday / $numClaimsYesterday : 0;
		
		$numViewsDifference = $numViewsToday - $numViewsYesterday;
		$numClaimsDifference = $numClaimsToday - $numClaimsYesterday;
		$v2cDifference = number_format($v2cRateToday - $v2cRateYesterday, 2);

		
		if($numViewsDifference > 0)
		{
			$signViewsDifference = '+';
			$colorViewsDifference = 'green';
		}
		else if($numViewsDifference < 0)
		{
			$signViewsDifference = '';
			$colorViewsDifference = 'red';
		}
		else
		{
			$signViewsDifference = '';
			$colorViewsDifference = '';
		}
		
		if($numClaimsDifference > 0)
		{
			$signClaimsDifference = '+';
			$colorClaimsDifference = 'green';
		}
		else if($numClaimsDifference < 0)
		{
			$signClaimsDifference = '';
			$colorClaimsDifference = 'red';
		}
		else
		{
			$signClaimsDifference = '';
			$colorClaimsDifference = '';
		}
		
		if($v2cDifference > 0)
		{
			$signV2CDifference = '+';
			$colorV2CDifference = 'green';
		}
		else if($v2cDifference < 0)
		{
			$signV2CDifference = '';
			$colorV2CDifference = 'red';
		}
		else
		{
			$signV2CDifference = '';
			$colorV2CDifference = '';
		}
		
		$data['numViewsDifference'] = number_format($numViewsDifference, 0);
		$data['signViewsDifference'] = $signViewsDifference;
		$data['colorViewsDifference'] = $colorViewsDifference;
		
		$data['numClaimsDifference'] = number_format($numClaimsDifference, 0);
		$data['signClaimsDifference'] = $signClaimsDifference;
		$data['colorClaimsDifference'] = $colorClaimsDifference;
		
		$data['v2cDifference'] = number_format($v2cDifference, 0);
		$data['signV2CDifference'] = $signV2CDifference;
		$data['colorV2CDifference'] = $colorV2CDifference;
		
		$data['sendEmailTo'] = $params['email'];
		$data['sendEmailCC'] = 'khoeffer@coupsmart.com';
		$data['sendEmailBCC'] = 'sqazi@coupsmart.com';
		
		return $data; 
		
	}
	
	public static function getWeeklyCampaignReportData($params)
	{
		$company_id = $params['company_id'];
		$csapi = new CSAPI();

		$data = array();

		$data['currentDate']= Common::getDBCurrentDate(null, null, '%m/%d/%Y');
		
		$param = array('companyId' => $company_id);
		$weekly_campaign_activity = $csapi->getWeeklyCampaignActivity($param);
		$img = CSAPI::createPDFChartImage('weekly_campaign_activity', $param, true, $weekly_campaign_activity);
		$arr_img = explode('/', $img);
		// $img = Common::getBaseURL(true) . "/images/downloaded/" . end($arr_img);
		$img = "https://s3.amazonaws.com/uploads.coupsmart.com/" . end($arr_img);
		$data['imgWeeklyActivitySummary'] = $img;


		$param = array('companyId' => $company_id, 'graphType' => 'permissions');
		$img = CSAPI::createPDFChartImage('app_permissions', $param, true);
		$arr_img = explode('/', $img);
		// $img = Common::getBaseURL(true) . "/images/downloaded/" . end($arr_img);
		$img = "https://s3.amazonaws.com/uploads.coupsmart.com/" . end($arr_img);
		$data['imgAppPermissions'] = $img;
		
		$activity_summary_views = array_sum($weekly_campaign_activity['series'][0]['data']);
		$activity_summary_claims = array_sum($weekly_campaign_activity['series'][1]['data']);
		$activity_summary_V2C = !empty($activity_summary_views) ? number_format(($activity_summary_claims / $activity_summary_views) * 100, 2) : 0.00;
		$data['activitySummaryV2C'] = $activity_summary_V2C;
		
		$short_url_activity = array();
		$response = $csapi->getActivityStatsGroupedByShortenedURLs($param);
		foreach($response['graph_data'] as $short_url => $tmp)
		{
			$campaign_name = $tmp['campaign_name'];
			$num_views = $tmp['num_views'];
			$num_claims = $tmp['num_claims'];
			$short_url = $tmp['short_url'];
			
			$tmp['formatted_views'] = number_format($num_views, 0);
			$tmp['formatted_claims'] = number_format($num_claims, 0);
			
			if(!isset($short_url_activity[$campaign_name]['short_urls']))
				$short_url_activity[$campaign_name]['short_urls'] = array();
			
			$short_url_activity[$campaign_name]['num_views'] += $num_views;
			$short_url_activity[$campaign_name]['num_claims'] += $num_claims;
			$short_url_activity[$campaign_name]['short_urls'][] = $tmp;
			
			$short_url_activity[$campaign_name]['v2c'] = !empty($short_url_activity[$campaign_name]['num_views']) ? number_format(($short_url_activity[$campaign_name]['num_claims'] / $short_url_activity[$campaign_name]['num_views']) * 100, 2) : 0.00;
			$short_url_activity[$campaign_name]['formatted_views'] = number_format($short_url_activity[$campaign_name]['num_views'], 0);
			$short_url_activity[$campaign_name]['formatted_claims'] = number_format($short_url_activity[$campaign_name]['num_claims'], 0);

		}
		$data['short_url_activity'] = $short_url_activity;

		$data['sendEmailTo'] = $params['email'];
		$data['sendEmailCC'] = 'khoeffer@coupsmart.com';
		$data['sendEmailBCC'] = 'sqazi@coupsmart.com';
		return $data;
	}


	/*AND END HERE
	********************************************************************/
	
	
	public static function isUnsubscribed($email, $email_template_id = null, $company_id = null)
	{
		$sql = "select * from unsubscribed_emails where email = '$email'";
		if(!empty($email_template_id))
			$sql .= " and email_template_id = '$email_template_id'";
		if(!empty($company_id))
			$sql .= " and company_id = '$company_id'";
	
		$row = BasicDataObject::getDataRow($sql);
		$is_unsubscribed = !empty($row['id']) ? true : false;
		return $is_unsubscribed;
	}
	
	public static function getTemplateOverrideContent($company_id, $item_id, $template)
	{
		$sql = "select * from email_template_override where template = '$template'";
		if(!empty($item_id))
			$sql .= " and item_id = '$item_id'";
		
		if(!empty($company_id))
			$sql .= " and company_id = '$company_id'";
			
		return BasicDataObject::getDataRow($sql);
	}

	private function getDefault($key){
		$defaults	=	json_decode($this->defaults, true);
		$default	=	$defaults[$key];
		return $default;
	}
	
	public static function addEmailToOutboundQueue($email_template, $params)
	{
		$serialized_params = serialize($params);
		$sql = "insert into `outbound_email_queue` (`email_template`, `params`) values ('" . $email_template. "', '" . $serialized_params . "')";
		if(!Database::mysqli_query($sql))
			error_log("Insert SQL error in EmailTemplates::addEmailToOutboundQueue() : " . Database::mysqli_error());
	}
	
	public static function processOutboundEmailQueue()
	{
		// Check and unlock the Queue if it has been locked far too long
		$waiting_time_limit = 3600 * 24; // Wait for one day
		$sql = "update app_config set email_queue_locked = 0 where email_queue_locked = 1 and (now() - last_locked) > '$waiting_time_limit'";
		Database::mysqli_query($sql);
	
		$app_config = new AppConfig(false);
		// Proceed only if the email queue is unlocked
		if($app_config->email_queue_locked == '0')
		{
			// Lock the Email Queue
			$app_config->email_queue_locked = '1';
			$app_config->last_locked = 'now()';
			$app_config->Update();
			error_log("Outbound Email Queued locked for Processing.");
			
			$sql = "select * from `outbound_email_queue` where `status` = 'queued'";
			$rows = BasicDataObject::getDataTable($sql);
			// error_log('rows from EmailTemplates::processOutboundEmailQueue(): ' . var_export($rows, true));
		
			foreach($rows as $i => $row)
			{
				// sleep(1);
				$params = unserialize($row['params']);
				$str_update_fields = "set last_attempt = now()";
				$email_template_obj = new EmailTemplates($row['email_template']);
				$result = EmailTemplates::sendEmailAlert($row['email_template'], $params, false, null, $email_template_obj);
				if($result == true || (isset($result[0]) && $result[0] == true) )
				{
					$str_update_fields .= ", `status` = 'sent'";
				}
				else 
				{
					$str_update_fields .= ", `send_failures` = `send_failures` + 1";
				}
				$sql = "update outbound_email_queue $str_update_fields where id = '" . $row['id'] . "'";
				if(!Database::mysqli_query($sql))
					error_log("Update SQL error in EmailTemplates::processOutboundEmailQueue() : ". Database::mysqli_error());
			}
			
			// After process completion, unlock the email queue;
			$app_config->email_queue_locked = '0';
			unset($app_config->last_locked);
			$app_config->Update();
			error_log("Processing complete. Outbound Email Queue unlocked now.");
		}
		else
		{
			error_log("Cannot process email queue since it is locked, so it's already being processed!");
		}
	
		
	}

}
?>
