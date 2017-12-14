<?php
require_once(dirname(dirname(__DIR__)) . '/includes/facebook-php-sdk/src/facebook.php');
//global $connect_app_id, $connect_app_secret;
global $socialbooking_app_id, $socialbooking_app_secret, $socialbooking_app_url, $socialboooking_app_ns, $app_ns;
global $lindt_id, $powered_by_logo, $mypub_id, $striderite_id, $striderite_campaign_id, $roomations_id, $current_app;
global $app_version, $upload_bucket;

// error_log("GET in canvas/coupsmart controller: " . var_export($_GET, true));
$bees_on = (isset($_GET['bees']) && $_GET['bees'] == true) ? true : false;

$meta_for_layout = '';
$start_time = time();
$trigger_sp_event_viewed = false;
$csc_reveal_deal_content = '';
$loc_zip_code = '';
$loc_dma = '';
$booking_installed = '';
$permissions = '';
$expire_time = '';
$share_message_reveal = false;
$source = '';
$smart_link_id = 0;


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

function is_share_bonus_coupon($coupon)
{
	$source = '';
	$sharing_bonus = false;
	if (isset($coupon['use_share_bonus']) && 
		$coupon['use_share_bonus'] == 'Y' && ($coupon['delivery_method'] == 1 
			|| $coupon['delivery_method'] == 3 
			|| $coupon['delivery_method'] == 7 
			|| $coupon['delivery_method'] = 8
			|| $coupon['delivery_method'] == 10
			|| $coupon['delivery_method'] == 11
			|| $coupon['delivery_method'] == 12
			)
		// && !empty($coupon['num_friends'])
		// && $coupon['num_friends'] > 0
		&& (
			!empty($coupon['social_offer_code'])
			||
			!empty($coupon['social_offer_value'])
		)
	) {
		$sharing_bonus = true;
	}elseif($source == 'share'){
		$sharing_bonus = true;
	}
	// error_log('sharing_bonus = ' . ($sharing_bonus ? 'true' : 'false'));
	return $sharing_bonus;
}

$facebook = null;
global $app_id, $app_secret, $app_url;
$facebook = new Facebook(array(
	'appId'  => $app_id,
	'secret' => $app_secret,
	'cookie' => true,
	'version' => 'v' . $app_version
));

$facebook_user_id = $facebook->getUser();


// error_log('facebook_user in canvas/controllers: '.var_export($facebook_user_id, true));

$user_id = !empty($facebook_user_id) ? User::getUserIdByFacebookIdOrAppScopedUserId($facebook_user_id) : 0;
// $reason_app_user_blocked = User::getBlockedAppUserReason('fan_deals', $facebook_user_id);
$user_blocked = User::isBlockedAppUser('fan_deals', $facebook_user_id);
if($user_blocked)
{
	$blocked_content = file_get_contents(Common::getBaseURL(true) . "/blocked");
	// error_log("blocked_content in coupsmart controller: " . var_export($blocked_content, true));
	echo $blocked_content;
	exit();
}

$item_views = array();


$page_request = parse_signed_request($_REQUEST['signed_request'],$app_secret);
// error_log('page_request: ' . var_export($page_request, true));

if(empty($page_request))
{
	if(!empty($_SESSION['page_request']))
		$page_request = $_SESSION['page_request'];
}
else
{
	$_SESSION['page_request'] = $page_request;
}

$session_id = session_id();

$page_id = null;
if (isset($page_request['profile_id'])) {
	$page_id = $page_request['profile_id'];
} else if (isset($page_request['page']['id'])) {
	$page_id = $page_request['page']['id'];
}

if($bees_on){
	$liked = true;
	$page_id = '242314195778846';
	$page_id = '179324715436841';
}
// error_log("page_id in sd controller: " . $page_id);
$company_id = Company::GetCompanyIdByFacebookPageId($page_id);
// error_log("company_id: " . $company_id);
$company = new Company($company_id);
$page_access_token = $company->access_token;

// if(!$bees_on){
	// $booking_installed = Common::is_app_installed('booking', $page_id);
	$access_token = $facebook->getAccessToken();
	$company_url = "https://graph.facebook.com/v" . $app_version . "/$page_id?access_token=$page_access_token";
	$response = null;
	try {
		$response= $facebook->api("/v" . $app_version . "/$page_id?access_token=$page_access_token");
		$facebook_page_name = $response['name'];
	}
	catch(Exception $e)
	{
		error_log("Exception when calling facebook->api: " . $e->getMessage());
	}
	if(!is_null($response) && isset($response['link'])){
		$company_page_link = $response['link'];
		$booking_link = $response['link'] . '?sk=app_' . $socialbooking_app_id;
		$app_link = $response['link'] . '?sk=app_' . $app_id;
	}else{
		$company_page_link = 'http://facebook.com/' . $page_id;
		$booking_link = 'http://facebook.com/' . $page_id . '?sk=app_' . $socialbooking_app_id;
		$app_link = 'http://facebook.com/' . $page_id . '?sk=app_' . $app_id;
	}
	$liked = true; // false;
	if (isset($page_request['page']['liked']) && $page_request['page']['liked'] == 1) {
		$liked = true;
	}
	
	$referral_id = '';
	$shortened_url_hit_id = '';
	$page_authentication = Company::checkForPageAuthentication($company_id, $page_request['app_data'], $company->auth_salt_value);
	
	if(isset($page_request['app_data']))
	{
		$app_data = $page_request['app_data'];
		
		$arr_app_data = explode('_', $app_data, 2); // 
		if($arr_app_data[0] == 'referralcode')
		{
			$referral_code = $arr_app_data[1];
			if(!empty($referral_code))
			{
				$referral_info = Item::getReferralInfoByCode($referral_code);
				$referral_id = $referral_info['id'];
				$_SESSION['referral_id_link'] = $referral_id;
				print "<script type='text/javascript'>top.location.href='$app_link'</script>";
				exit();
			}
		}
		else if($arr_app_data[0] == 'suhi')	//	Shortened URL Hit Id
		{
			$shortened_url_hit_id = $arr_app_data[1];
			if(!empty($shortened_url_hit_id))
			{
				$_SESSION['shortened_url_hit_id'] = $shortened_url_hit_id;
				print "<script type='text/javascript'>top.location.href='$app_link'</script>";
				exit();
			}
		}
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
	
	
	// error_log('app_link: ' . $app_link);
	$url_to_share = $app_link;
	
	$referral_code = md5(UUID::v4());
	// parse $app_link to see if it contains a query string
	$arr_parsed_url = parse_url($url_to_share);
	$url_to_share .= empty($arr_parsed_url['query']) ? "?" : "&";
	// $url_to_share .= 'app_data={"referral_code": "' . $referral_code. '"}';
	$url_to_share .= 'app_data=referralcode_' . $referral_code;
	$url_to_share = Common::getBaseURL(true) . "/page_redirects.php?" . base64_encode($url_to_share);


	if(!empty($page_request['app_data']))
	{
		$arr_parsed_url = parse_url($app_link);
		$app_link .= empty($arr_parsed_url['query']) ? "?" : "&";
		$app_link .= "app_data=" . $page_request['app_data'];
	}
	
	if (!empty($company->fb_listing_css)) {
		$meta_for_layout .= "<style type='text/css'>\n" . $company->fb_listing_css . "\n</style>";
	}
	
	/////////////////////////////////////////////////////////////////////////////////////////
	//					CHECKING TO SEE DONATION BASED DEALS
	/////////////////////////////////////////////////////////////////////////////////////////
	if(!empty($company->use_donation_based_deals))
	{
		// if(empty($_SESSION['donation_charge_id']))
		//	{
			if(!User::donationAlreadyMadeByUser($facebook_user_id, $company_id, 'SD'))
			{
			if(!empty($meta_for_layout)) print $meta_for_layout;
			global $stripe_test_publishable_key, $stripe_live_publishable_key, $stripe_publishable_key;
			
			if($company->demo == '1')
				$stripe_key = $stripe_test_publishable_key;
			else
				$stripe_key = !empty($company->stripe_publishable_key) ? $company->stripe_publishable_key : $stripe_publishable_key;

			
			print '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
			<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
			<script src="//connect.facebook.net/en_US/sdk.js"></script>
			<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
			<script type="text/javascript">
			  // This identifies your website in the createToken call below
			  Stripe.setPublishableKey("' . $stripe_key. '");
			  // ...
			</script>
			<script type="text/javascript" src="/js/country-state-drop-downs.js"></script>
			<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
			<link rel="stylesheet" href="/css/foundation.css" />
			<link rel="stylesheet" href="/css/2014_default.css" />';
		
			print "<script type='text/javascript'>
					var company_id = '" . $company->id. "';
					
					function stripeResponseHandler(status, response) {
					  var form = $('#frm_donation');
					  // console.log('response in stripeResponseHandler(): ');
					  // console.log(response);

					  if (response.error) {
						// Show the errors on the form
						var error_fields = {
							'invalid_number': 'donation-card-number', 
							'incorrect_number': 'donation-card-number', 
							'invalid_expiry_year': 'donation-expiry', 
							'invalid_expiry_month': 'donation-expiry', 
							'invalid_cvc': 'donation-cvc'
						};
						var error_code = response.error.code;
						var error_msg, error_field;
						if(response.error.type == 'invalid_request_error')
						{
							error_msg = response.error.message;
							error_field = 'donation-card-number';
						}
						else
						{
							error_msg = response.error.message;
							error_field = error_fields[error_code];
						}
						
						$('#' + error_field + '-error').html(error_msg);
						$('#' + error_field + '-error').show();
						
						$('#donation-main-error').html('Invalid input. Please see below');
						$('#donation-main-error').show();
						window.scrollTo(0, 0);
						$('#btn_make_your_donation').show();
						$('#div-img-loader').hide();
					  } else {
						// response contains id and card, which contains additional card details
						var token = response.id;
						// Insert the token into the form so it gets submitted to the server
						form.append($('<input type=\'hidden\' name=\'stripeToken\' />').val(token));
						// and submit
						
						
						var form_data = $('#frm_donation').serialize();
						$.ajax({
							type: 'post',
							data: {'op': 'submit', 'company_id': company_id, 'form_data': form_data, 'app_name': 'SD', 'facebook_user_id': '$facebook_user_id'},
							dataType: 'json',
							url: '/helpers/ajax-donation-based-deals.php',
							success: function(data) {
								// console.log('data upon success');
								// console.log(data);
								if(data['errors'] != undefined)
								{
									for(i in data['errors'])
									{
										$('#' + i + '-error').html(data['errors'][i]);
										$('#' + i + '-error').show();
									
									}
									window.scrollTo(0, 0);
									$('#btn_make_your_donation').show();
									$('#div-img-loader').hide();
								}
								else
								{
									top.location.href='" . $app_link . "';
								}
							},
							error: function(err) {
								console.log('error:');
								console.log(err);									
							},
						});
						
					  }
					}

					$(document).ready(function() {
						$('.error').hide();
						
						// alert('donation form loaded... with company id: ' + company_id);
						$('#btn_make_your_donation').click(function() {
							$('.error').hide();
							$('#btn_make_your_donation').hide();
							$('#div-img-loader').show();
						
							var form = $('#frm_donation');

							// Disable the submit button to prevent repeated clicks

							Stripe.card.createToken(form, stripeResponseHandler);
							
						});
					});
					</script>";
			print '<div>
				<small id="donation-main-error" class="error" style="font-weight:bold;">Invalid Entry</small>
				<div class="row">
				<div id="donation-banner" style="width: 100%;height: 100px;"></div>
				</div>
				<div class="row">
				<div class="small-12 columns">
				<h3 id="donation-headline" style="font-family: Open Sans, sans-serif; color: #228acb;">Make a difference with your donation today</h3>
				<!--<p id="donation-subheadline" style="font-family: Open Sans, sans-serif;">...plus get rewarded with store savings!</p>-->
				</div>
				</div>
				<form id="frm_donation" name="frm_donation" method="post">
				<div class="row">
				<div class="small-12 medium-5 large-5 columns">
				<div class="row">
					<div class="small-12 large-8 columns">
						<label for="demodonationAmount">Donation Amount
						<input type="text" placeholder="Numbers Only" class="requiredTextField" id="donation-amount" name="donation-amount" style="margin-bottom:0px;" />
						</label>
						<small id="donation-amount-error" class="error">Invalid Entry</small>
						<p id="minimum-donation-amount" style="font-family: Open Sans, sans-serif; margin-top:10px;">A minimum donation amount of $' . $company->donation_min_val . ' is required.<br/><em style="font-size: 12px;">Numbers only. No need to add $ sign.</em></p>
					</div>
				</div>
				</div>
				<div class="small-12 medium-7 large-7 columns">
					<div class="row">
						<div class="small-12 large-8 columns">
							<label name="demoEmailAddress">Email Address
							<input type="text" placeholder="Email Address (For Donation Receipt)" class="requiredTextField"  id="donation-email-receipt" name="donation-email-receipt" style="margin-bottom:0px;" />
							</label>
							<small id="donation-email-receipt-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label name="demoCardName" style="margin-top:10px;">Name On Card
							<input type="text" placeholder="Name on Card" class="requiredTextField"  id="donation-card-name" name="donation-card-name" style="margin-bottom:0px;" />
							</label>
							<small id="donation-card-name-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="demoCardNumber" style="margin-top:10px;">Card Number
							<input type="text" placeholder="Card Number" class="requiredTextField" id="donation-card-number" style="margin-bottom:0px;" data-stripe="number" />
							</label>
							<small id="donation-card-number-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">CVC
							<input type="text" placeholder="CVC" class="requiredTextField" id="donation-cvc" style="margin-bottom:0px;" data-stripe="cvc" />
							</label>
							<small id="donation-cvc-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">Expiration date (MM/YYYY)
							<div class="row">
							<div class="small-6 columns">
							<input type="text" placeholder="Expiration month" class="requiredTextField" id="donation-exp-month" style="margin-bottom:0px;" data-stripe="exp-month" /> 
							</div>
							<div class="small-6 columns">
							<input type="text" placeholder="Expiration year" class="requiredTextField" id="donation-exp-year" style="margin-bottom:0px;" data-stripe="exp-year" /> 
							</div>
							</label>
							</div>
							<small id="donation-expiry-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">Billing Address
							<input type="text" placeholder="Billing Address" class="requiredTextField" id="donation-billing-address" name="donation-billing-address" style="margin-bottom:0px;" />
							</label>
							<small id="donation-billing-address-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">Apt/Suite
							<input type="text" placeholder="Apt/Suite" class="requiredTextField" id="donation-suite" name="donation-suite" style="margin-bottom:0px;" />
							</label>
							<small id="donation-suite-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">City
							<input type="text" placeholder="City" class="requiredTextField" id="donation-city" name="donation-city" style="margin-bottom:0px;" />
							</label>
							<small id="donation-city-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">State
							<select id="donationCountry" name="donation-country" style="display:none; margin-bottom:0px;"></select>
							<select id="donationState" name="donation-state" style="margin-bottom:0px;"></select>
							</label>
							<small id="donation-state-error" class="error">Invalid Entry</small>
							<script type="text/javascript">initCountry("US", "donationCountry", "donationState");</script>
						</div>
					</div>
					<div class="row">
						<div class="small-12 large-8 columns">
							<label for="" style="margin-top:10px;">Zip
							<input type="text" placeholder="Zip" class="requiredTextField" id="donation-zip" name="donation-zip" style="margin-bottom:0px;" />
							</label>
							<small id="donation-zip-error" class="error">Invalid Entry</small>
						</div>
					</div>
					<div class="row" style="margin-top: 20px;">
						<div class="small-12 large-8 columns">
							<a href="javascript:;" class="button greenbtn" style="width: 100%;" id="btn_make_your_donation" name="btn_make_your_donation" >DONATE NOW</a><div id="div-img-loader" style="display:none;"><img src="http://siteimg.coupsmart.com.s3.amazonaws.com/backend/backend_load.gif" /></div>
						</div>
					</div>
				</div>
				</div>
				</form>
			</div>';
			exit();
			}
		// }
	}
	
	
	
	
	
	
	///////////////////////////////////////////////////////////////////////////////////////////
	


	/////////////////////////////////////////////////////////////////////////////////////////
	//					CHECKING TO SEE LOCATION BASED DEALS
	/////////////////////////////////////////////////////////////////////////////////////////
	Common::getLocationBasedDealsContent($company, $meta_for_layout, $app_link);
	

// }



/*
if(!isset($_COOKIE['PHPSESSID']))
{
	if(Common::isSafari())
	{
		print Common::safariBugFixHTMLContent($base_url, $app_link);
		exit();
	}
	else
	{
		$redirect_url = $base_url. "/safari_cookie_fix.php?redirect_url=" . urlencode($app_link);
		header("Location: $redirect_url");
		// print "Either Cookies are disabled or you have blocked this application from saving cookies. Please either enable cookies or allow the app to set cookies from your browser and refresh the page.";
		// header("Location: https://www.facebook.com/pages/Al-Burooj-Medical-Center/177479482288153?sk=app_135042073221492");
		exit();
	}

}
*/

if(!isset($_COOKIE['PHPSESSID']) && Common::isSafari())
{
	print Common::safariBugFixHTMLContent($base_url, $app_link);
	exit();
}
	
// if(!$bees_on)
//	error_log("Time taken to run Company::GetCompanyIdByFacebookPageId(): ".array_sum(explode(" ", microtime())) - $start_time . ' ' . __FILE__ . ' line ' . __LINE__);

// if(!$bees_on)
//	error_log("Time taken to create new Company object: ".array_sum(explode(" ", microtime())) - $start_time . ' ' . __FILE__ . ' line ' . __LINE__);
if(empty($facebook_page_name))
	$facebook_page_name = $company->display_name;
	


$require_like = ($company->require_like == 1) ? true : false;

// $coming_soon_jpg = "coming_soon.jpg";
$coming_soon_jpg = "Coming+Soon+-+FB.jpg";

$no_coupons_txt = '<div id="no_coupons_txt_message"><img src="//s3.amazonaws.com/uploads.coupsmart.com/' . $coming_soon_jpg . '" alt="not liked image" style="width:800px;"/></div>';

$like_required_txt = '<div id="like_required_txt_message"><img src="//s3.amazonaws.com/uploads.coupsmart.com/like_gate.jpg" alt="not liked image" style="width:800px;"/></div>';

$page_number = !empty($_GET['p']) ? $_GET['p']: '1';
$num_items = !empty($_GET['i']) ? $_GET['i']: '6';  	// items per page


if(!empty($_SESSION['loc_zip_code']))
{
	// error_log("_SESSION['loc_zip_code'] is set to: " . $_SESSION['loc_zip_code']);
	// Set appropriate criteria for getting location based coupons
	$loc_zip_code = $_SESSION['loc_zip_code'];
	unset($_SESSION['loc_zip_code']);
}

if(!empty($_SESSION['loc_dma']))
{
	// error_log("_SESSION['loc_zip_code'] is set to: " . $_SESSION['loc_dma']);
	// Set appropriate criteria for getting location based coupons
	$loc_dma = $_SESSION['loc_dma'];
	unset($_SESSION['loc_dma']);
}

if(!empty($_SESSION['loc_company_id']))
	unset($_SESSION['loc_company_id']);
	
/*
if(!empty($_SESSION['donation_charge_id']))
{
	$charge_id = $_SESSION['donation_charge_id'];
	unset($_SESSION['donation_charge_id']);

	// Validate Charge ID if needed.
	if(!Company::validateDonation($charge_id))
	{
		print "<div>INVALID ATTEMPT TO ACCESS DEALS! YOU MUST MAKE A DONATION FIRST!!</div>";
		exit();
	}
}
*/

list($coupons, $num_rows, $offset, $limit, $num_pages) = UserItems::getFacebookCompanyCoupons($company_id, $num_items, $page_number, $loc_zip_code, $loc_dma);
// error_log('coupons in coupsmart controller: ' . var_export($coupons, true));


$lang = Common::fblocale_to_locale($page_request['user']['locale']);
$phrases_to_translate = array('Click here for locations.', 'Expires', 'Details', 'Share', 'Share to Claim Deal', 'Print Deal', 'Share to Print Deal', 'Use Deal', 'Share to Use Deal', 'How do you want to share this deal?', 'Post To', 'Your Wall', 'Friend\'s Wall', 'Send Request', 'To Friends', 'Who would you like to receive this deal?', 'Type a name... ', 'What would you like to say?', 'Submit', 'Cancel', 'Thank you for sharing!', 'Book Now', 'Share and Book Now', 'Privately send message');
foreach ($coupons as $coupon) {
	if (isset($coupon['claim_button_text']) && !empty($coupon['claim_button_text'])) {
		$phrases_to_translate[] = $coupon['claim_button_text'];
	}
}
$translations = Common::translate_to($lang, $phrases_to_translate);



//uncomment this to redo user permissions
// try{
// 	$permissions = $facebook->api("/v" . $app_version . "/me/permissions");
// 	} catch(FacebookApiException $e){
// 		error_log($e);
// 	}
// 	
// if($permissions != NULL){
// 	$user_id = $facebook->getUser();
// 	$user = User::findByFacebookId($user_id);
// 	$permissions = true;
// 	$user_data = (array) $user;
// 	//error_log($user_data);
// 	} else {
// 	$permissions = false;
// 	}


$share_methods = array();
$requires_sharing = array();
foreach($coupons as $i => $coupon)
{
	$item_id = $coupon['id'];
	if(!isset($share_methods[$item_id]))
		$share_methods[$item_id] = array();
	if($coupon['share_own_wall'] == '1')
		$share_methods[$item_id][] = 0;
		
	if($coupon['share_friends_wall'] == '1')
		$share_methods[$item_id][] = 1;
		
	if($coupon['share_send_request'] == '1')
		$share_methods[$item_id][] = 2;
	if(isset($coupon['use_share_bonus']) && $coupon['use_share_bonus'] == 'Y') {
		$requires_sharing[$coupon['id']] = true;
	} else {
		$requires_sharing[$coupon['id']] = false;
	}
}
$json_share_methods = json_encode($share_methods);
// error_log('share_methods in coupsmart controller: '. var_export($share_methods, true));
// $base_url = Common::getBaseURL();
$base_url = "//".$_SERVER['SERVER_NAME'];
$code = isset($_REQUEST["code"]) ? $_REQUEST["code"] : '';
$print_url = "";

// Getting Saved Deals that are to be printed later
// adding a check to see if cookie exists so it doesn't cause fatal error
if(isset($_COOKIE['smart_deals_user_id'])){
	$smart_deals_user_id = $_COOKIE['smart_deals_user_id'];
	// error_log("_COOKIE: " . var_export($_COOKIE, true));
	$saved_deals_for_later = Item::getSavedDealsForLater($smart_deals_user_id);

}

// Grid Layout
$smart_deals_layout = !empty($company->smart_deals_layout) ? $company->smart_deals_layout : 'vertical'; // Either 'vertical' or 'grid'

// Button Style
$smart_deals_styled_buttons = !empty($company->smart_deals_styled_buttons) ? $company->smart_deals_styled_buttons : '0';



$support_footer_content = !empty($company->support_footer_content) ? $company->support_footer_content : DEAL_FOOTER_CONTENT;


global $service_status;
$service_status_social_deals = $service_status['social_deals'];

if(!empty($_POST['hdn_print_button_clicked'])) {
	$item_id = $_POST['hdn_item_id'];
	
	$redirect_url = $base_url."/canvas/coupsmart/tab?item_id=$item_id";
	
	if(empty($code)) {	//	If the request has not yet been made to the facebook oauth dialog
		$_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
		$dialog_url = "//www.facebook.com/dialog/oauth?client_id=" 
		 . $app_id . "&redirect_uri=" . urlencode($redirect_url) . "&scope=email,read_stream,publish_actions&state="
		 . $_SESSION['state'];

		
		echo("<script>top.location.href='" . $dialog_url . "'</script>");
		exit();
	}
}

$smart_link_click_info = Item::getSmartLinkClickInfo();
// error_log("smart_link_click_info in coupsmart controller: " . var_export($smart_link_click_info, true));
if(!empty($smart_link_click_info['id']))
	Item::updateSmartLinkClickInfo($smart_link_click_info['id']);


// Silver Pop Views
// error_log("SESSION in coupsmart controller['from_silver_pop']: " . var_export($_SESSION['from_silver_pop'], true));
$silver_pop_click_info = array();
if(!empty($_SESSION['from_silver_pop']))
{
	unset($_SESSION['from_silver_pop']);
	$silver_pop_click_info = SilverPop::getSilverPopClickInfo();
}



/////////// NOTIFICATIONS ///////////
$no_coupons = false;
$access_token_exists = false;
$user_registered = false;
$is_notified = false;

if($user_id != 0){
	$user_registered = true;
	$existing_user = new User($user_id);
}
// error_log('EXISTING USER: ' . var_export($existing_user, true));

if(!empty($existing_user) && !empty($existing_user->email))
{
	$user_email = $existing_user->email;
	$is_unsubscribed = EmailTemplates::isUnsubscribed($user_email, null, $company_id);
	$unsubscribe_url = Common::getBaseURL(true) . '/unsubscribe?email=' . urlencode($user_email) . ';company_id=' . $company_id;
	$subscribe_url = Common::getBaseURL(true) . '/subscribe?email=' . urlencode($user_email) . ';company_id=' . $company_id;
}

if(empty($coupons)){
	$fb_uid = $facebook_user_id;
	$no_coupons = true;
	
	// $app_index = UserNotification::getAppIndex($socgift_app_ns);
	// Shouldn't this be $app_ns (for Smart Deals) instead
	$app_index = UserNotification::getAppIndex($app_ns);
	$deals_notification = UserNotification::getActiveNotificationByFBIDAndCompanyId($fb_uid, $company_id, $app_index);
	// error_log("deals_notification in canvas/coupsmart controller: " . var_export($deals_notification, true));
	if(!is_null($deals_notification)){
		if($deals_notification->is_notified()){
			$is_notified = true;
		}
	}
	
	$access_info = UserNotification::getAccessInfoIfExists($fb_uid, $company_id, $app_index);

	$access_token_exists 	= $access_info['access_token_exists'];
	$access_token 			= $access_info['access_token'];
	$expire_time 			= $access_info['expire_time'];
	$permissions 			= $access_info['permissions'];
	
}
else
{
	// Checking and Triggering SilverPop Event 'Viewed Offer'
	if(!empty($silver_pop_click_info['id'])) // && $silver_pop_click_info['viewed'] == '0')
	{
		$trigger_sp_event_viewed = true;
	}
}

/*
// Logging FB like via app
if(!$liked){
	$nodeal = new UserNoDeal();
	$nodeal->service_type = 'deals';
	$nodeal->companies_id = $company_id;
	$nodeal->session_id	  = session_id();
	$nodeal->fb_user_id	  = $facebook_user_id;
	if(!empty($user_id))
		$nodeal->users_id = $user_id;
	$nodeal->Insert();
	

}
else {
	UserNoDeal::updateLastRowsLike('deals');
}
*/

?>
