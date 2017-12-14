<?php

require_once(dirname(__DIR__) . '/includes/email_parsing.php');
require_once(dirname(__DIR__) . '/classes/BasicDataObject.class.php');
require_once(dirname(__DIR__) . '/classes/Common.class.php');
require_once(dirname(__DIR__) . '/classes/Deal.class.php');
require_once(dirname(__DIR__) . '/classes/EmailTemplates.class.php');

/**
* UserFavoriteStores Class
*/
class Company extends BasicDataObject
{
	var $id;
	var $use_defender;
	var $created;
	var $status;
	var $display_name;
	var $facebook_page_id;
	var $initial_fan_count;
	var $current_fan_count;
	var $require_like;
	var $subdomain;
	var $img_no_coupons_exist;
	var $mobile_placeholder_image;
	var $likebar_image;
	var $img_page_not_liked;
	var $default_coupon_image;
	var $fb_listing_css;
	var $no_coupons_running;
	var $open_sgs_store_when;
	var $close_sgs_store_when;
	var $location_finder_link;
	var $max_synch_campaigns;
	var $base_price;
	var $total_prints;
	var $cost_overage_prints;
	var $approved_overage;
	var $packages_id;
	var $sent_email_80;
	var $sent_email_90;
	var $sent_email_full;
	var $sent_payment_email;
	var $affiliate_id;
	var $trial_end_date;
	var $platforms;
	var $locations;
	var $profile_id;
	var $notify_date;
	var $fan_page_auto_posting;
	var $access_token;
	var $app_access_token;
	var $shown_payment_options;
	var $sgs_header_img;
	var $sgs_open_date;
	var $sgs_close_date;
	var $sgs_teaser_img;
	var $sgs_closed_img;
	var $sgs_out_of_stock_img;
	var $sgs_email_header_img;
	var $sgs_css;
	var $sgs_message_to_post;
	var $sgs_giftshop_status;
	var $countmein_denied_img;
	var $countmein_redeemed_img;
	var $countmein_expired_img;
	var $countmein_email_template;
	var $countmein_email_subject;
	var $convercial_css;
	var $countmein_css;
	var $countmein_title;
	var $countmein_subtitle;
	var $coupcheck_css;
	var $coupcheck_domain;
	var $white_label;
	var $tax_exempt_id;
	var $tax_exempt_information;
	var $sgs_show_out_of_stock_items;

	var $pdf_graph_colors;
	var $pdf_background;

	var $open_hotel_id;
	var $open_hotel_username;
	var $open_hotel_password;

	var $booking_css;
	var $unsubscribe_css;

	var $magento_running;
	var $magento_url;
	var $grace_days;
	var $phone;
	var $email;
	var $website;
	var $discount_months;
	var $industry_id;
	
	var $smart_deals_layout; // vertical / grid
	var $smart_deals_styled_buttons; // 1 / 0
	
	var $support_footer_content;
	
	var $smart_emails_template;
	var $show_fb_comments;
	
	var $copyright;
	var $receive_message; 
	var $analytics_report_recipients;
	var $analytics_report_frequency;
	var $sgs_report_recipients;
	var $sgs_order_receipt_recipients;
	var $source_tracking_report_recipients;
	var $instore_background_img;
	var $mo_header_caption;
	
	var $sgs_fee_rate;
	var $sgs_cc_fee_rate;
	var $send_order_notification;

	var $time_zone;
	var $sgs_cart_timeout;
	var $sgs_email_css;
	var $sgs_terms;
	var $sgs_allow_po_box_delivery;
	var $sgs_currency;
	var $sgs_language;
	var $sgs_fixed_shipping_cost;
	var $sgs_fixed_country;
	
	var $demo;
		
	var $enable_ftp_upload;
	var $ftp_hostname;
	var $ftp_username;
	var $ftp_password;
	var $ftp_sub_folder;
	var $ftp_is_ssl;
	
	// Silverpop access token info and base url index
	var $is_silverpop_company;
	var $sp_access_token;
	var $sp_access_token_expire_time;
	var $sp_endpoint;
	var $sp_app_name;
	var $sp_client_id;
	var $sp_client_secret;
	var $sp_refresh_token;
	var $sp_username;
	var $sp_password;
	var $sp_api_host;
	var $sp_list_id;
	var $sp_contact_list_id;
	var $sp_contact_interests_id;
	var $sp_contact_likes_id;
	var $sp_contact_shares_id;
	var $sp_contact_qr_codes_id;
	var $sp_user_mapped_columns;
	var $sp_is_ubx;
	var $sp_ubx_auth_key;
	var $sp_ubx_api_url;
	var $sp_ubx_app_name;
	var $sp_ubx_app_desc;
	
	var $sdw_unique_code;
	var $sdw_url_to_share;
	var $send_email_after_printing;
	var $srv_portal_script_last_updated;
	
	var $is_et_company;
	var $et_data_extension_claims;
	var $et_subscriber_list_id;
	var $et_data_extension_behaviours;
	
	var $is_mailchimp_company;
	var $mc_list_id;
	var $mc_list_name;
	var $mc_api_key;
	
	var $is_campaign_monitor_company;
	var $cm_client_id;
	var $cm_api_key;
	
	var $use_location_based_deals;
	var $loc_zipgate_norm;
	var $loc_zipgate_error_nodeal;
	var $loc_dmagate_norm;
	var $loc_dmagate_error_nodma;
	var $loc_dmagate_error_nodeal;
	
	var $loc_zipgate_norm_mo;
	var $loc_zipgate_error_nodeal_mo;
	var $loc_dmagate_norm_mo;
	var $loc_dmagate_error_nodma_mo;
	var $loc_dmagate_error_nodeal_mo;

	var $enable_user_blocking;
	
	var $use_donation_based_deals;
	var $donation_min_val;
	var $donation_email_subject;
	var $donation_charity;
	
	var $consultant_of;
	var $is_corporate;
	
	var $webhook_url;
	var $webhook_data_last_posted;
	var $load_testing;
	
	var $stripe_publishable_key;
	var $stripe_secret_key;
	var $self_service_type;
	var $enable_pixel_tracking;
	var $auth_salt_value;
	
	var $license_start_date;
	var $license_grace_period;
	var $license_period_months;
	var $account_type;
	var $dependent_dropdown;
	var $license_currency;
	var $license_rate;
	var $claim_credits;
	var $service_package;
	var $service_package_rate;
	var $service_package_day_of_month;
	var $license_expire_date;
	var $accounting_contact;
	var $accounting_contact_name;
	var $accounting_contact_email;
	var $payment_method_type;
	var $default_credit_card_id;
	var $auto_process_credit_card;
	var $email_invoice_to;
	var $name_another_invoice;
	var $email_another_invoice;
	

	function __construct($id = null, $read_only_mode = true)
	{
		if(!empty($id))
		{
			$id = Database::mysqli_real_escape_string($id);
			$this->Select("id='".$id."'", $read_only_mode);
		}
		return $this;
	}

	public static function GetAllCompanies()
	{
		$result = array();
		$sql = "select * from companies order by display_name";
		$rs = Database::mysqli_query($sql);
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while ($row = Database::mysqli_fetch_assoc($rs)) {
				$result[] = $row;
			}
		}
		Database::mysqli_free_result($rs);
		return $result;
	}
	
	public static function GetFacebookPageId($company_id) {
		$sql = "select facebook_page_id from companies where id = '" . Database::mysqli_real_escape_string($company_id) . "'";
		$rs = Database::mysqli_query($sql);
		if ($rs && Database::mysqli_num_rows($rs) == 1) {
			$row = Database::mysqli_fetch_assoc($rs);
			Database::mysqli_free_result($rs);
			return $row['facebook_page_id'];
		} else {
			return null;
		}
	}

	public static function GetFacebookLink($app_id, $facebook_page_id){
		$company_url = "https://graph.facebook.com/$facebook_page_id";

		$response = file_get_contents($company_url);
		// if(!$response)
		//	throw new Exception("Error retrieving company info: ");

		$response = json_decode($response, true);

		if(!is_null($response) && isset($response['link'])){
			$link = $response['link'] . '?sk=app_' . $app_id;
		}else{
			$link = 'http://facebook.com/' . facebook_page_id . '?sk=app_' . $app_id;
		}

		return $link;
	}

	public static function GetCompanyIdByFacebookPageId($facebook_page_id) {
		$sql = "select id from companies where facebook_page_id = '" . Database::mysqli_real_escape_string($facebook_page_id) . "'";
		//error_log("SQL in GetCompanyIdByFacebookPageId(): ".$sql);
		$rs = Database::mysqli_query($sql);
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			$row = Database::mysqli_fetch_assoc($rs);
			Database::mysqli_free_result($rs);
			return $row['id'];
		} else {
			return null;
		}
	}

	public static function GetCompanyByFacebookId($fb_page_id) {
		$sql = "select * from companies where facebook_page_id = '" . Database::mysqli_real_escape_string($fb_page_id) . "'";
		$rs = Database::mysqli_query($sql);
		// error_log('company id lookup: '. $sql);
		if($rs && Database::mysqli_num_rows($rs)>0){
			$row = Database::mysqli_fetch_assoc($rs);
			$company = new self();
			foreach($row as $var => $data){
				$company->$var = $data;
			}
			Database::mysqli_free_result($rs);
			return $company;
		} else {
			return null;
		}
	}

	public static function getCompanyInfo($company_id){
		$sql = "Select default_coupon_image from companies where id = " . Database::mysqli_real_escape_string($company_id);
		$result = Database::mysqli_query($sql);
		$row = null;
		if($result && Database::mysqli_num_rows($result) > 0 ){
			$row = mysql_fetch_row($result);

		}
		return $row;
	}
	
	public static function checkForPageAuthentication($company_id, $app_data, $auth_salt_value = null)
	{	
		$json_decoded_app_data = json_decode($app_data, true);
		// error_log("func args in Company::checkForPageAuthentication(): ". var_export(func_get_args(), true));
		// error_log("json_decoded_app_data in Company::checkForPageAuthentication(): " . var_export($json_decoded_app_data, true));
		
		try
		{
			if(empty($auth_salt_value))
			{
				$company = new Company($company_id);
				$auth_salt_value = $company->auth_salt_value;
			}

			if(!empty($auth_salt_value))
			{
				if($json_decoded_app_data['is_auth'] != '1')
					throw new Exception("Invalid parameter value for 'is_auth'!"); 
				
				if($json_decoded_app_data['company_id'] != $company_id)
					throw new Exception("Invalid parameter value for 'company_id'!"); 
				
				$sig_to_check = $json_decoded_app_data['sig'];
				
				/*
				$auth_salt_cookie_name = 'isAuthEnabled';
				$auth_salt_cookie_value = md5($auth_salt_value . ($company_id + 3) . 'Sdfg34DFGd');
				$cookie_expire_time = 3600 * 24 * 30;
				
			
				//	check if user has downloaded the app previously at some point in time. use a cookie to check this.
				
				if(!empty($_COOKIE[$auth_salt_cookie_name]))
				{
					if($_COOKIE[$auth_salt_cookie_name] != $auth_salt_cookie_value)
					{
						error_log("Invalid cookie value in Company::checkIfAuthEnabled(): ");
						throw new Exception("Invalid cookie value. It seems to have been tampered with!");
					}
					else
					{
						//	if cookie is set then extend the expire time of the cookie and proceed with the page load
						$is_cookie_set = setcookie($auth_salt_cookie_name, $auth_salt_cookie_value, time() + $cookie_expire_time, '/');
						if(!$is_cookie_set)
							throw new Exception("Couldn't extend cookie expire time. Maybe you have cookies disabled in your browser?");
					}
				}
				else	//	else
				{
					//	calculate signature to check to see if the user has been redirected from the client's page just following the app download
					//	if the signature is invalid show an error
					
					$is_signature_valid = Common::checkAuthSaltSignature($company_id, $auth_salt_value, $sig_to_check);
					if(!$is_signature_valid)
					{
						throw new Exception("Invalid signature. Authentication failed.");
					}
					else	//	else if the signature is valid,
					{
						//	set a cookie to expire after one year and proceed with the page load.
						$is_cookie_set = setcookie($auth_salt_cookie_name, $auth_salt_cookie_value, time() + $cookie_expire_time, '/');
						error_log("is_cookie_set in Company::checkForPageAuthentication(): " . var_export($is_cookie_set, true));
						if(!$is_cookie_set)
							throw new Exception("Couldn't set cookie. Maybe you have cookies disabled in your browser?");
					}
				}
				*/		
				$is_signature_valid = Common::checkAuthSaltSignature($company_id, $auth_salt_value, $sig_to_check);
				if(!$is_signature_valid)
				{
					throw new Exception("Invalid signature. Authentication failed.");
				}
			}
		}
		catch(Exception $e)
		{
			die ("Error: " . $e->getMessage());
			// return array('error' => $e->getMessage());
		}
	}
	
}
?>