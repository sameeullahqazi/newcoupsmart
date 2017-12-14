<?php

// require_once(dirname(__DIR__) . '/classes/Common.class.php');

/*
require_once(dirname(__DIR__) . '/includes/SQLQueryBuilder.php');

global $query_views, $query_claims, $query_redeems, $query_shares, $query_referrals;

$query_views = new SQLSelect(
	new SQLTableReference("items_view", "iv")
);
$query_claims = new SQLSelect(
	new SQLTableReference("user_items", "ui"),
	"*",
	new SQLWhere("ui.date_claimed", "is", "not null")
);
$query_redeems = clone $query_claims;
$query_redeems->whereAnd(new SQLWhere("ui.date_redeemed", "is", "not null"));

$query_shares = new SQLSelect(
	new SQLTableReference("referrals", "r")
);

$query_referrals = clone $query_claims;
$query_referrals->whereAnd(new SQLWhere("ui.referral_id", "is", "not null"));
*/



global $lindt_id, $powered_by_logo, $mypub_id, $striderite_id, $striderite_campaign_id, $roomations_id;
$mypub_id = array(734, 527);
$striderite_id = array(713, 745);
$striderite_campaign_id = 1635;
$lindt_id = array(756, 760);
$roomations_id = 79;

$powered_by_logo = '/img/apps/smart_deals/poweredbyCS.jpg';

global $db_pref, $API_key, $consumer_key, $consumer_secret, $twitter_key, $twitter_secret;
global $request_token_URL, $access_token_URL, $authorize_URL, $registered_OAuth_callback_URL;
global $facebook_api_key, $facebook_api_secret, $facebook_app_id, $gmaps_key, $api_key;
global $mail_chimp_api_key;
global $campaign_monitor_client_id, $campaign_monitor_api_key;
global $bucket, $upc_salt, $aws_key, $aws_secret_key, $upload_bucket, $sgs_bucket;
global $app_id, $app_key, $app_secret, $app_url, $app_ns; // Coupsmart App
global $connect_app_id, $connect_app_key, $connect_app_secret; // Coupsmart Connect
global $arr_global_campaign_sharing_images, $salt_walk_in_complete;
global $add_camp_routes, $add_camp_fields, $upgrade_paths, $item_status_priority, $arr_email_templates;
global $socgift_app_id, $socgift_app_secret, $socgift_app_ns, $socgift_app_url;
global $countmein_app_id, $countmein_app_secret, $countmein_app_ns, $countmein_app_url;
global $server_prefix, $mofuse_up, $lookup_domain, $test_accounts;
global $socialbooking_app_id, $socialbooking_app_secret, $socialbooking_app_url, $socialboooking_app_ns;
global $apps, $apps_info;
global $coupt_url;
global $app_version;
global $stripe_test_secret_key, $stripe_test_publishable_key, $stripe_live_secret_key, $stripe_live_publishable_key, $stripe_secret_key, $stripe_publishable_key;
global $currencies;
global $self_service_yearly_licence_fee, $self_service_monthly_claim_fee;
global $default_fb_listing_css;
global $us_time_zones;

$default_fb_listing_css = 'li.featured {
width: 810px !important;
height: 250px !important;
}

#detailsmodal .coupon_image {
height: 205px !important;
}

/* CSS NEEDED FOR LIKEBAR */
div.overlay {
top: 66px !important;
height: 85% !important;
display: none !important;
}


#topbar {
margin: 0 0 0 0 !important;
}

#coupon-container {
width: 810px;
height: 100%;
}

#loading {
width: 810px;
}

div#more-savings {
display: none !important;
}';


global $ses_smtp_username, $ses_smtp_password, $ses_smtp_host;
$ses_smtp_username = "AKIAJUQFNZ5SMR6EEKVA";
$ses_smtp_password = "AlUM4RtxOnVfVQWqs6zj0RjlvSKmJ+YJPA42/jlqD8JY";
$ses_smtp_host = "email-smtp.us-east-1.amazonaws.com"; // "email-smtp.us-west-2.amazonaws.com";

$stripe_test_secret_key = 'sk_test_8nbUMQEU1UXz1yBssEDQPRE0';
$stripe_test_publishable_key = 'pk_test_GsMbApItYvphcXuL8mX4uYIO';
$stripe_live_secret_key = 'sk_live_PiAWzuJ1mmHxHtpuICZ9Fzlu';
$stripe_live_publishable_key = 'pk_live_yDgHsYdyL8hpe2qOlZ3NPcua';

$self_service_yearly_licence_fee	= 4188;
$self_service_monthly_claim_fee		= 0.08;

global $from;	// Default sender address when sending emails
$from = "support@coupsmart.com";

// Zendesk Account Info (Please set these below once our account is created with Zendesk API)
global $zendesk_account, $zendesk_username, $zendesk_password;
$zendesk_account = 'coupsmart';
$zendesk_username = 'techsupport@coupsmart.com';
$zendesk_password = '!TechiesUnite2nite';


// Coupsmart Test Emails for the Lindt Smart Email Process
global $coupsmart_test_emails, $coupsmart_test_emails_pw;
global $phantomjs;
global $credit_card_captions;
global $currency_symbols;
global $client_billing_product_types;

$currency_symbols = array('USD' => '$', 'ZAR' => 'R');

$credit_card_captions = array(
	'visa' => 'Visa', 
	'mastercard' => 'Mastercard', 
	'amex' => 'American Express', 
	'discover' => 'Discover',
);

$client_billing_product_types = array(
	'license_fee'			=> 'CoupSmart Promotion License',
	'service_package_fee'	=> 'CoupSmart Monthly Services',
	'processing_fee_voucher_claims' => 'CoupSmart Monthly Claim Fees',
	'processing_fee_email_claims'	=> 'CoupSmart Monthly Claim Fees',
);

$us_time_zones = array(
	'EST' => 'America/New_York',
	'PST' => 'America/Los_Angeles',
	'CST' => 'America/Chicago',
	'MST' => 'America/Denver',
	// 'MST-NODST' => 'America/Phoenix',
	'AKST' => 'America/Anchorage',
	// 'HST-DST' => 'America/Adak',
	'HST' => 'Pacific/Honolulu',
	'Asia/Karachi' => 'Asia/Karachi',
);

$currencies = array('usd', 'aed', 'afn', 'all', 'amd', 'ang', 'aoa', 'ars', 'aud', 'awg', 'azn', 'bam', 'bbd', 'bdt', 'bgn', 'bif', 'bmd', 'bnd', 'bob', 'brl', 'bsd', 'bwp', 'bzd', 'cad', 'cdf', 'chf', 'clp', 'cny', 'cop', 'crc', 'cve', 'czk', 'djf', 'dkk', 'dop', 'dzd', 'eek', 'egp', 'etb', 'eur', 'fjd', 'fkp', 'gbp', 'gel', 'gip', 'gmd', 'gnf', 'gtq', 'gyd', 'hkd', 'hnl', 'hrk', 'htg', 'huf', 'idr', 'ils', 'inr', 'isk', 'jmd', 'jpy', 'kes', 'kgs', 'khr', 'kmf', 'krw', 'kyd', 'kzt', 'lak', 'lbp', 'lkr', 'lrd', 'lsl', 'ltl', 'lvl', 'mad', 'mdl', 'mga', 'mkd', 'mnt', 'mop', 'mro', 'mur', 'mvr', 'mwk', 'mxn', 'myr', 'mzn', 'nad', 'ngn', 'nio', 'nok', 'npr', 'nzd', 'pab', 'pen', 'pgk', 'php', 'pkr', 'pln', 'pyg', 'qar', 'ron', 'rsd', 'rub', 'rwf', 'sar', 'sbd', 'scr', 'sek', 'sgd', 'shp', 'sll', 'sos', 'srd', 'std', 'svc', 'szl', 'thb', 'tjs', 'top', 'try', 'ttd', 'twd', 'tzs', 'uah', 'ugx', 'uyu', 'uzs', 'vnd', 'vuv', 'wst', 'xaf', 'xcd', 'xof', 'xpf', 'yer', 'zar', 'zmw', 'vef');
$phantomjs = "phantomJSLinux";
$coupsmart_test_emails = array(
	'coup.sender@gmail.com',
	'coup.consumer@gmail.com',
	'coup.consumer2@gmail.com',
	'coup.tester@gmail.com',
	'sqazi@coupsmart.com',
	'khoeffer@coupsmart.com',
	'amurray@coupsmart.com',
);
$coupsmart_test_emails_pw = "coupconsumer1022";


$mofuse_up = false;

$test_accounts = array(
	'100003742350896' => 'http://www.facebook.com/frank.bertsch.3', // Frank Bertsch
	'100004057331878' => 'http://www.facebook.com/oneal.testerton', // Alexanderia Heflin
	'100004104851069' => 'http://facebook.com/imheretotest', // Andrew Murray
	'105335846278765' => 'http://www.facebook.com/erica.lynn.5203577', // Erica Conroy
	'100003581933455' => 'http://www.facebook.com/james.burkhardt.357', // Sean Grace
	'100003230705103' => 'http://www.facebook.com/champ.pion.507', // Adam McCosham
	'100002125901105' => 'https://www.facebook.com/bob.biz.1', // Nick Sweeney
	'100003835482279' => 'http://www.facebook.com/whitera.venmedia.7', // Lisa Weitgraven
	'100003371169225' => 'http://www.facebook.com/kara.lootest', // Kara Loo
	'100002181280008' => 'http://www.facebook.com/profile.php?id=100002181280008', // Blake Shipley
	'118642291614217' => 'http://www.facebook.com/profile.php?id=118642291614217', // Jacob Fox
	'100002374117347' => 'http://www.facebook.com/profile.php?id=100002374117347', // Troy Davis
	'100004089220665' => 'http://www.facebook.com/krystal.siemontest' // Krystal Seimen
);

DEFINE('TEAMCOUP_MANAGER', 4);
DEFINE('TEAMCOUP_WORKER', 5);

// Coupons Types
DEFINE('WEB', '1,19,14,17');	//	SDW
DEFINE('MOBILE', '6,18,12,16');	//	MO
DEFINE('FACEBOOK', '3,7,8,9,10,11,15');	//	SD
DEFINE('DELIVERED', 4);
DEFINE('BIRTHDAY', 5);
DEFINE('WALKIN', 6);
DEFINE('EMAIL', 13);


// Location/National
DEFINE('NATIONAL', 0);
DEFINE('LOCAL', 1);

// Widths for different image types defined
DEFINE('FACEBOOK_IMG_WIDTH', 504);
DEFINE('PREVIEW_IMG_WIDTH', 251);
DEFINE('SELECTOR_IMG_WIDTH', 96);
DEFINE('SELECTOR_IMG_HEIGHT', 96);

// SGS Session expire time in seconds
DEFINE('SGS_EXPIRE_TIME', 7200); // Set this to 2 hours

// SGS Giftshop variables
DEFINE('VOUCHER', 'voucher');
DEFINE('PHYSICAL', 'physical');
DEFINE('IMMEDIATE', 'immediately');
DEFINE('DELAYED', 'delayed');
DEFINE('ANONYMOUS', 'anonymous');
DEFINE('FRIEND', 'friend');
DEFINE('SELF', 'self');
DEFINE('WITH_ADDRESS', 'yes');
DEFINE('WITHOUT_ADDRESS', 'no');
DEFINE('SENDER', 'sender');
DEFINE('RECIPIENT', 'recipient');

// DEAL FOOTER CONTENT
DEFINE('DEAL_FOOTER_CONTENT', "&nbsp;");

// Info for coupsmart.com, www.coupsmart.com, etc.

// Setting the campaign sharing images in a global array
$arr_global_campaign_sharing_images = array(
'1' => array(
	'item_id'		=> '1', 
	'file_name'		=> 'baby.jpg', 
	'sharing_text'	=> 'Some funny text',
	'name'			=> 'Baby',
	'description'	=> 'Description1'
	),
'2' => array(
	'item_id'		=> '2', 
	'file_name'		=> 'crabs.jpg', 
	'sharing_text'	=> 'Some funny text 2',
	'name'			=> 'Name2',
	'description'	=> 'Description2'
	),
'3' => array(
	'item_id'		=> '3', 
	'file_name'		=> 'psychic.jpg', 
	'sharing_text'	=> 'Some funny text 3',
	'name'			=> 'Name3',
	'description'	=> 'Description3'
	),
'4' => array(
	'item_id'		=> '4', 
	'file_name'		=> 'rich-clean.jpg', 
	'sharing_text'	=> 'Some funny text 4',
	'name'			=> 'Name4',
	'description'	=> 'Description4'
	),
'5' => array(
	'item_id'		=> '5', 
	'file_name'		=> 'rich.jpg', 
	'sharing_text'	=> 'Some funny text 5',
	'name'			=> 'Name5',
	'description'	=> 'Description5'
	),
'6' => array(
	'item_id'		=> '6', 
	'file_name'		=> 'zombie.jpg', 
	'sharing_text'	=> 'Some funny text 6',
	'name'			=> 'Name6',
	'description'	=> 'Description6'
	),
);

$add_camp_routes = array(
	'default' => array('platform', 'company', 'campaign', 'dealstyle', 'finalize'),
	'web' => array('platform', 'company', 'campaign', 'dealstyle', 'finalize'),
	'facebook' => array('platform', 'company', 'campaign', 'dealstyle', 'finalize'),
	'convercial' => array('platform', 'company', 'convercial', 'finalize'),
	'birthday' => array('platform', 'company', 'birthday', 'finalize')
);

$add_camp_fields = array(
	'company' 	=> 
		array(
			'manufacturer' 	=> 'customer_companies_campaigns.companies_id',
			'locations' 	=> array('locations.id' => array())
		),
	'platform' 	=> 
		array(
			'platform' 		=> array('items.delivery_methods' => array())
		),
	'campaign' 	=> 
		array(
			'campaign_name' 	=> 'campaigns.name',
			'start_date' 		=> 'items.start_date',
			'end_date' 			=> 'items.end_date',
			'auto_renew' 		=> 'campaigns.auto_renew',
			'inventory_count' 	=> 'items.inventory_count',
			'limit_per_person' 	=> 'items.limit_per_person'
		),
	'dealstyle' => 
		array(
			'use_coupon_barcode' 				=> 'campaigns.use_coupon_barcode',
			'disable_expire'					=> 'campaigns.disable_expire',
			'deal_name'				 			=>	'items.name',
			'product_upc'						=> 'items.upc',
			'offer_value'						=> 	'items.offer_value',
			'retail_price'						=>	'items.retail_price',
			'offer_code'						=>	'items.offer_code',
			'expire_date'						=> 	'items.expires',
			'small_type'						=>	'items.small_type',
			'coupon_ext_code'					=>	'',
			'barcode_co_prfx'					=>	'items.barcode_co_prfx',
			'barcode_family_code'				=> 	'items.barcode_family_code',
			'barcode_social_family_code' 		=> 'items.barcode_social_family_code',
			'use_offer_code'					=> 'campaigns.use_offer_code',
			'barcode_offer_code'				=>	'items.barcode_offer_code',
			'barcode_social_offer_code'			=> 'barcode_social_offer_code',
			'hdn_logo_file_name' 				=>	'campaigns.logo_file_name', 
			'hdn_preview_file_name'				=>  'hdn_preview_logo_file_name',
			'expire_month'						=>  'items.expire_month',
			'expire_year'						=>	'items.expire_year',
			'num_friends'						=>	'items.num_friends',
			'use_share_bonus' 					=>	'campaigns.use_share_bonus',
			
		),
	'share' 	=> 
		array(
			'use_share_bonus' 					=>	'campaigns.use_share_bonus',
			'barcode_social_offer_service_name' =>  'items.barcode_social_offer_service_name',
			'social_offer_code'					=>	'items.social_offer_code',
			'social_offer_value' 				=>	'items.social_offer_value',
			'num_friends'						=>	'items.num_friends',
			'social_small_type'					=>	'items.social_small_type',
			'hdn_image_types_shown' 			=>	'images.type',
			'img_name' 							=>  'images.name',
			'img_description' 					=>  'images.description',
			'logo_img' 							=>  'images.file_name',
			'preview_logo_img'					=>	array('images.id' => array())
			
		),
	'birthday' 	=> 
		array(
			'barcode_social_offer_service_name' => 'items.barcode_social_offer_service_name',
			'social_offer_value' 				=> 'items.social_offer_value',
			'social_small_type'					=> 'items.social_small_type',
			'birthday_coupon_day_week_month' 	=> 'campaigns.birthday_coupon_day_week_month',
			'hdn_image_types_shown' 			=> 'images.type',
			'img_name' 							=> 'images.name',
			'img_description' 					=> 'images.description',
			'hdn_file_name_image' 				=> 'images.file_name',
			'hdn_selected_images' 				=> array('campaign_images.image_id' => array())
			
		),
	'convercial' =>
		array(
			'barcode_social_offer_service_name' => 'items.platform_social_offer_service_name',
			'social_offer_value' 				=> 'items.platform_social_offer_value',
			'new_fan_bonus' 					=> 'campaigns.new_fan_bonus',
			'social_small_type' 				=> 'items.platform_social_offer_small_type',
			'new_fan_offer' 					=> 'campaigns.new_fan_offer'
		)
);

$upgrade_paths = array(
	1 => array(
		'options' => array(
			'package_ids' => array(2, 3, 4),
			'addons' => null,
		)
	),
	2 => array(
		'options' => array(
			'package_ids' => array(3, 4),
			'addons' => array(1, 2),
		)
	),
	3 => array(
		'options' => array(
			'package_ids' => array(4),
			'addons' => array(1, 2),
		)
	),
	4 => array(
		'options' => array(
			'package_ids' => null,
			'addons' => array(1, 2),
		)
	)
);

$apps = array(
	'booking',
	'countmein',
	'promotions',
	'social_gift_shop',
	'birthday',
);

// Item status priority
$item_status_priority = array('running', 'pending', 'paused', 'finished', 'stopped','deleted');


// Email templates
$arr_email_templates = array(
	'free_active' => array(
		0 => array(
			'template' => 'customer_free_w1.html',
			'subject' => 'CoupSmart Tips For Your Campaign',
			'week' => 1
		),
		1 => array(
			'template' => 'customer_free_w2.html',
			'subject' => 'An offer you can’t refuse.',
			'week' => 2
		),
		2 => array(
			'template' => 'customer_free_w3.html',
			'subject' => 'CoupSmart Tips For Your Campaign',
			'week' => 3
		),
		3 => array(
			'template' => 'customer_free_w4.html',
			'subject' => 'Did you miss the email I sent you?',
			'week' => 4
		),
		4 => array(
			'template' => 'customer_free_w5.html',
			'subject' => '',
			'week' => 5
		),
	),
	'free_inactive' => array(
		1 => array(
			'template' => 'customer_free_inactive_w1.html',
			'subject' => 'Checking In',
			'week' => 1
		),
		2 => array(
			'template' => 'customer_free_inactive_w2.html',
			'subject' => 'Need help getting started?',
			'week' => 2
		),
		3 => array(
			'template' => 'customer_free_inactive_w3.html',
			'subject' => 'Where have you been?',
			'week' => 3
		),
		4 => array(
			'template' => 'customer_free_inactive_w4.html',
			'subject' => 'What are you waiting for?',
			'week' => 4
		),
	),
);

global $app_codes;
$app_codes = array(
	'is_claimed'	=> 'A',
	'is_redeemed'	=> 'B',
	'is_shared'	=> 'C',
	'is_bought'	=> 'D',
	'is_received'	=> 'E',
	'fan_deals'	=> 'F',
	'sgs'		=> 'G',
	'birthday'	=> 'H',
	'coupcheck'	=> 'I',
	'convercial'	=> 'J'
);

global $arr_voucher_layout_types;
/*
		$arr_voucher_layout_types = array(
			'background',
			'image',
			'text1',
			'text2',
			'text3',
			'expiry',
			'coupcheck_logo',
			'coupcheck_barcode',
			'cashier_code',
			'user_image',
			'user_name',
		);
*/
$arr_voucher_layout_types = array(
	'Background' => array(
		'type' => 'background',
		'is_dynamic' => 0,
		'name' => 'Background'
	),
	'Image' => array(
		'type' => 'image',
		'is_dynamic' => 0,
		'name' => 'Image'
	),
	'Coupcheck Logo' => array(
		'type' => 'coupcheck_logo',
		'is_dynamic' => 0,
		'name' => 'Coupcheck Logo'
	),
	'Expiration Date' => array(
		'type' => 'expiry',
		'is_dynamic' => 0,
		'name' => 'Expiration Date'
	),
	'Expiration' => array(
		'type' => 'expiry',
		'is_dynamic' => 0,
		'name' => 'Expiration'
	),
	'Body' => array(
		'type' => 'text3',
		'is_dynamic'=> 0,
		'name' => 'Body'
	),
	'Sub Heading' => array(
		'type' => 'text2',
		'is_dynamic' => 0,
		'name' => 'Sub Heading'
	),
	'Sub Header' => array(
		'type' => 'text2',
		'is_dynamic' => 0,
		'name' => 'Sub Header'
	),
	'Heading' => array(
		'type' => 'text1',
		'is_dynamic' => 0,
		'name' => 'Heading'
	),
	'Cashier Code' => array(
		'type' => 'cashier_code',
		'is_dynamic' => 1,
		'name' => 'Cashier Code'
	),
	'Coupcheck Barcode' => array(
		'type' => 'coupcheck_barcode',
		'is_dynamic' => 1,
		'name' => 'Coupcheck Barcode'
	),
	'User Name' => array(
		'type' => 'user_name',
		'is_dynamic' => 1,
		'name' => 'User Name'
	),
	'User Image' => array(
		'type' => 'user_image',
		'is_dynamic' => 1,
		'name' => 'User Image'
	),
);

global $task_manager_users, $task_manager_projects;
$task_manager_users = array(
	// 'bshipley@coupsmart.com' => 'Blake Shipley',
	'sqazi@coupsmart.com' => 'Samee Qazi', 
	'froghay@coupsmart.com' => 'Fahad Roghay',
	'khoeffer@coupsmart.com' => 'Kristina Hoeffer',
);

global $account_manager_info;
$account_manager_info = array(
	'firstname' => 'Kristi',
	'lastname' => 'Hoeffer',
	'email' => 'khoeffer@coupsmart.com',
);


$production_domains = array('coupsmart.com', 'www.coupsmart.com', 'cou.pt', 'www.cou.pt', 'api.coupsmart.com', 'beanstalk.coupsmart.com');
$dev_domains = array('dev.coupsmart.com', 'dev.cou.pt');
$local_domains = array('coupsmart.local', 'www.coupsmart.local', 'cou.local');
$local_dev_domains = array('dev.coupsmart.local', 'dev.cou.local', 'api.coupsmart.local' );

$use_settings = 'local_dev';
$lookup_domain = false;

if (in_array($_SERVER['SERVER_NAME'], $production_domains)) {
    // It's production, no white label domain lookup
    $use_settings = 'production';
} else if (in_array($_SERVER['SERVER_NAME'], $dev_domains)) {
    // It's our dev server, no white label domain lookup
    $use_settings = 'dev';
} else if (in_array($_SERVER['SERVER_NAME'], $local_domains)) {
    // Local production copy, no white label domain lookup
    $use_settings = 'local_production';
} else if (in_array($_SERVER['SERVER_NAME'], $local_dev_domains)) {
    // Local dev domain, no white label domain lookup
} else if (preg_match('/\.local$/', $_SERVER['SERVER_NAME'])) {
    // Local testing of white label domain, look it up, use local dev settings
    $lookup_domain = true;
} else {
    // Production white lable domain, use production settings and look it up
    $use_settings = 'production';
    $lookup_domain = true;
}

// Database
$db_pref = 'coupsmart';

// Twitter integration info
$request_token_URL = 'https://api.twitter.com/oauth/request_token';
$access_token_URL = 'https://api.twitter.com/oauth/access_token';
$authorize_URL = 'https://api.twitter.com/oauth/authorize';
$oauth_token = '163992750-Ph7jPzruJVqUcugnr1psmX83ERLCNxXwVIvIWeo';
$oauth_token_secret = 'Rw8D5R3qsA4Pei5q3p3ECwqSxtNT9MKWmWHCsujY0';
$registered_OAuth_callback_URL = 'http://' . $_SERVER['SERVER_NAME'] . '/signup';
$API_key = 'AplGvva48bbxu7ECjkeTw';
$consumer_key = 'AplGvva48bbxu7ECjkeTw';
$consumer_secret = 'BRDYVgiQjMui5usXB4gYfVw4frquY670lxc1WDwKM';
$twitter_key = 'AplGvva48bbxu7ECjkeTw';
$twitter_secret = 'BRDYVgiQjMui5usXB4gYfVw4frquY670lxc1WDwKM';

// Amazon S3 Cloud variables
$upc_salt = '9201340522657012';
$aws_key = 'AKIAIKWVSEYVFTEMFMWQ'; // 'AKIAIV4DVOMO25MOE3FA';
$aws_secret_key = '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'; // 'dIM21K/9SJhZ/gb0K1vX4AcdDubON0B/wGIeGkfb';
// New Access Key ID:
// AKIAI6NXDB4URMC7AOZA
// New Secret Access Key:
// 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

$bucket = 'p.coupsmart.com';
$upload_bucket = 'images/uploads/s3bucket'; // 'uploads.coupsmart.com';
$sgs_bucket = 'sgsimg.coupsmart.com';


// Facebook App - Coupsmart
$app_id = "182968635067837";
$api_key = "9816ccbfdea5d1f2acad349f4c6da8a5";
$app_secret = "5f2e58b37ad83b31bd01fcb941e95ec8";
$app_url = 'http://apps.facebook.com/coupsmart';
$app_ns = 'coupsmart';
$app_version = '2.3';

// Facebook CoupSmart Connect App (customer registration, page / company association, consumer coupon listing)
$connect_app_id = "122924377750799";
$connect_api_key = "4fefe6576cfc9914263bcc3db3b520e9";
$connect_app_secret = "1a25f91ce7eaaab60254ad03ad0ff3cf";

$salt_walkin_complete = "nbnsmvfkertjgBDTFSXaybdbgsa786dh";

// Facebook Social Gift Shop App (tabs / shopping)
// $socgift_app_id = '285460911486045';
// $socgift_app_secret = '17cc689481099721f9f5a926c09d3d4b';
// $socgift_app_url = 'http://www.facebook.com/apps/application.php?id=285460911486045';
$socgift_app_id = '303846093016286';
$socgift_app_secret = '1527aaf292dac7c4e1a659f3b8a33539';
$socgift_app_url = 'http://www.facebook.com/apps/application.php?id=303846093016286';
$socgift_app_ns = 'smart-gifts';

// Facebook Count Me In App (tabs / email newsletters/birthday emails)
$countmein_app_id = '253185288080836';
$countmein_app_secret = '428903240453c4226524a1732e9e6f49';
$countmein_app_ns = 'count_me_in';
$countmein_app_url = 'http://apps.facebook.com/count_me_in/';

$socialbooking_app_id = '407556729260943';
$socialbooking_app_secret = '07a9be8e5814877b9589a3b83ccfbaeb';
$socialboooking_app_ns = 'smart_booking';
$socialbooking_app_url = 'http://apps.facebook.com/social_booking/';

// Google Maps key
$gmaps_key = 'ABQIAAAAmp2AHJRerQhtdMJgLU8JhxR2RAxyowTKTixJDxOVlDRgRTBh8BRj6Sis-uRmqPPp6xr5qXNFtccxMQ';


// Mail Chimp API Key
$mail_chimp_api_key = '46a45702d1654539e03aff951ab85f0d-us2';


// Campaign Monitor API Key
$campaign_monitor_client_id	= 'ca5d046aa1b4553c4f9ae068e89eabf3';
$campaign_monitor_api_key	= 'b6aa1e94203b45dff05328d600baca0d';

$server_prefix = "";
$coupt_url = "cou.pt";

$fb_realtime_updates_verify_token = 'production_verify_token';


$stripe_secret_key = $stripe_live_secret_key;
$stripe_publishable_key = $stripe_live_publishable_key;

// Infor for other domains, where different
if ($use_settings == 'dev' || $use_settings == 'local_production') {
	// Info for dev.coupsmart.com
	$API_key = "5KPeSO7fZ8qXdB0fV0A";
	$consumer_key = '5KPeSO7fZ8qXdB0fV0A';
	$consumer_secret = 'hoWNSG02SMolEd7U2ZcSNJB334BJsUjpRhw6F30Cs';
	$twitter_key = '5KPeSO7fZ8qXdB0fV0A';
	$twitter_secret = 'hoWNSG02SMolEd7U2ZcSNJB334BJsUjpRhw6F30Cs';
	$db_pref = 'coupsmart';
	$bucket = 'pdev.coupsmart.com';

	// Facebook App - Coupsmart (Dev)
	$app_id = "135042073221492";
	$api_key = "731714b2a6e695562ed90357c518a053";
	$app_secret = "b889c13ddea2ead8fb7c49ebe052747a";
	$app_url = 'http://apps.facebook.com/coupsmart_dev';
	$app_ns = 'coupsmart_dev';

	// Facebook App - Coupsmart Connect (Dev)
	$connect_app_id = "266807427920";
	$connect_api_key = "4aab4b18f19dd2f54817adbc6a04a492";
	$connect_app_secret = "c055c26abfc31ecf4a0f657664660c7f";
	
	// Facebook Social Gift Shop App (tabs / shopping)
	
	
	// $socgift_app_id = '227371933994556';
	// $socgift_app_secret = 'e8f77adccd1f527eb6dd9d0bebc323c7';
	// $socgift_app_ns = 'smartgifts_dev';
	// $socgift_app_url = 'http://www.facebook.com/apps/application.php?id=227371933994556';
	
	$socgift_app_id = '290043074350123';
	$socgift_app_secret = '31315fcb1cb9a963f346903f32afb9a1';
	$socgift_app_ns = 'smartgifts_dev';
	$socgift_app_url = 'http://www.facebook.com/apps/application.php?id=290043074350123';
	
	
	
	// Facebook Count Me In App (tabs / email newsletters/birthday emails)
	$countmein_app_id = '318812638152541';
	$countmein_app_secret = '12d245a60754a3f6786274323fc6ec14';
	$countmein_app_ns = 'count_me_in_dev';
	$countmein_app_url = 'http://apps.facebook.com/count_me_in_dev';
	
	$server_prefix = "dev_";
	
	$coupt_url = "dev.cou.pt";
	$fb_realtime_updates_verify_token = 'dev_verify_token';
	
	$stripe_secret_key = $stripe_test_secret_key;
	$stripe_publishable_key = $stripe_test_publishable_key;
	$ses_smtp_host = "email-smtp.us-east-1.amazonaws.com";

} elseif ($use_settings == 'local_dev') {
	// Info for dev.coupsmart.local
	$API_key = "TstYQCNHd9pXNIOCpNM7w";
	$consumer_key = 'TstYQCNHd9pXNIOCpNM7w';
	$consumer_secret = 'DbRRN5ZGBLte9h62cfvGId0vMQoCD300GBywHFAJs';
	$twitter_key = 'TstYQCNHd9pXNIOCpNM7w';
	$twitter_secret = 'DbRRN5ZGBLte9h62cfvGId0vMQoCD300GBywHFAJs';
	$gmaps_key = 'ABQIAAAAmp2AHJRerQhtdMJgLU8JhxSfbWVt_MkE0bgUJ7XySAaJHa7pdBRII0d7I7wOirlccpaZ7CvBTcxNBg';
	$db_pref = 'newcoupsmart';
	$bucket = 'pdev.coupsmart.com';
	
	// Facebook App - Coupsmart (Local Dev)
	$app_id = "171818396196950";
	$api_key = "590ac7c96e95874e664ea3319f46c5f8";
	$app_secret = "a356960e8916584eb8561a327e25730f";
	$app_url = 'http://apps.facebook.com/coupsmart_dev_local';
	$app_ns = 'coupsmart_dev_local';

	// Facebook App - Coupsmart Connect (Local Dev)
	$connect_app_id = "132740606762266";
	$connect_api_key = "64464e6f8f32d2978b24e2bd9729ab42";
	$connect_app_secret = "56a1a95cf54749ded3ba4d2928f5530d";
	
	// Facebook Social Gift Shop App (tabs / shopping)
	$socgift_app_id = '290043074350123';
	$socgift_app_secret = '31315fcb1cb9a963f346903f32afb9a1';
	$socgift_app_ns = 'smartgifts_dev_local';
	$socgift_app_url = 'http://www.facebook.com/apps/application.php?id=290043074350123';
	
	
	// Facebook Count Me In App (tabs / email newsletters/birthday emails)
	$countmein_app_id = '245831028821014';
	$countmein_app_secret = 'f1a646b0b7349057c2acdd672936b0e4';
	$countmein_app_ns = 'count_me_in_loc_dev';
	$countmein_app_url = 'http://apps.facebook.com/count_me_in_loc_dev';
	
	$socialbooking_app_id = '141146966021773';
	$socialbooking_app_secret = '2aefa5e26dd2048b57839692d31a56a3';
	$socialboooking_app_ns = 'smart_booking_locdev';
	$socialbooking_app_url = 'http://apps.facebook.com/social_booking_local/';
	
	$server_prefix = "dev_";
	
	$coupt_url = "dev.cou.local";
	$fb_realtime_updates_verify_token = 'my_test_verify_token';
	
	/*
	$os = Common::getOS();
	$os_type = $os[1];
	if($os_type == 'Win')
		$phantomjs = "phantomJSWin.exe";
	else if($os_type == 'Linux')
		$phantomjs = "phantomJSLinux";
	else if($os_type == 'Mac')
		$phantomjs = "phantomjs";
	*/

	$stripe_secret_key = $stripe_test_secret_key;
	$stripe_publishable_key = $stripe_test_publishable_key;
	$ses_smtp_host = "email-smtp.us-east-1.amazonaws.com";
}

$apps_info = array(
	$app_ns => array(
		'id' => $app_id,
		'key' => $api_key,
		'secret' => $app_secret,
		'url' => $app_url,
		'index' => 1,
		'title' => 'coupsmart',
	),
	$socgift_app_ns => array(
		'id' => $socgift_app_id,
		'key' => $api_key,
		'secret' => $socgift_app_secret,
		'url' => $socgift_app_url,
		'index' => 2,
		'title' => 'socialgiftshop',
	),
	$countmein_app_ns => array(
		'id' => $countmein_app_id,
		'key' => $api_key,
		'secret' => $countmein_app_secret,
		'url' => $countmein_app_url,
		'index' => 3,
		'title' => 'countmein',
	),
	$socialboooking_app_ns => array(
		'id' => $socialbooking_app_id,
		'key' => $api_key,
		'secret' => $socialbooking_app_secret,
		'url' => $socialbooking_app_url,
		'index' => 4,
		'title' => 'socialbooking',
	),
);

?>
