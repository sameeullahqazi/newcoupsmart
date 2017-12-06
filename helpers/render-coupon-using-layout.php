<?php
function autoload_classes($class_name)
{
    $file = dirname(__DIR__) . '/classes/'.$class_name.'.class.php';
    
    if (file_exists($file))
    {
        require_once($file);
    }
}

spl_autoload_register('autoload_classes');

require_once(dirname(__DIR__) . '/includes/app_config.php');

date_default_timezone_set('UTC');

$base_url = Common::getBaseURL();

$db = new Database();
try {
	$db->connect();
} catch(Exception $e) {
	Errors::show500();
}
global $db;
global $app_version;
global $upload_bucket;


//start the session
session_start();

// $begin_time = array_sum(explode(" ", microtime()));
// error_log("begin time: ".$begin_time);
$upload_bucket_path = dirname(__DIR__) . '/' . $upload_bucket . '/';

$user = new User();
$data = $_REQUEST;
// error_log("REQUEST data in render-coupon-using-layout: ".var_export($data, true));
// error_log("GET in render-coupon-using-layout.php: " . var_export($_GET, true));

$claimed_attempt_id = isset($data['claimed_attempt_id']) ? $data['claimed_attempt_id'] : null;
$existing_uiids = !empty($_REQUEST['existing_uiids']) ? $_REQUEST['existing_uiids']: '';
$reprint_code = isset($_COOKIE['reprint_code']) ? $_COOKIE['reprint_code'] : null;

$item_id = !empty($data['item_id']) ? $data['item_id'] : '';
$items_views_id = !empty($data['items_views_id']) ? $data['items_views_id'] : '';
if(isset($_REQUEST['bees']) && $_REQUEST['bees'] == 'true') $item_id = 925;
$is_preview = !empty($data['is_preview']) ? true : false;
$location_specific_vouchers = !empty($data['location_specific_vouchers']) ? $data['location_specific_vouchers'] : '';
$bg_img = !empty($data['bg_img']) ? $data['bg_img'] : '';

$errors = array();
if(!$is_preview)
{
	if(empty($reprint_code)) {
		if(!Item::canUserPrintItem($data['user_id'], $item_id)) {
			$errors['user_limit_reached'] = "You have reached your coupon print limit. Sorry, you cannot print this coupon anymore.";
		}
		else if(Item::hasItemRunOutOfStock($item_id))
		{
			$errors['out_of_stock'] = "Sorry, this coupon has run out of stock!";
		}
	}
	
	$csapi = new CSAPI();
	$is_sig_valid = $csapi->checkCSAPISignature($_GET);
	// error_log("is_sig_valid in helpers/render-coupon-using-layout: " . var_export($is_sig_valid, true));
	if(!$is_sig_valid)
	{
		$msg = "Invalid Signature! You're not authorized to view this Coupon!";
		// error_log($msg);
		$errors['unauthorized_access'] = $msg;
	}
}
// error_log("errors in render-coupon-using-layout: " . var_export($errors, true));


if(!empty($errors))
{
	header('Content-Type: application/json; charset=utf-8');
	print json_encode(array('errors' => $errors));
	exit();
}


$item = new Item($item_id);
$data['item_id'] = $item_id;

$campaign_id = $item->campaign_id;

$app_name = !empty($data['app_name']) ? $data['app_name'] : '';


$font_bold 				= dirname(__DIR__) . '/fonts/VeraBd.ttf';
$font_plain 			= dirname(__DIR__) . '/fonts/Vera.ttf';
$font_upca 				= dirname(__DIR__) . '/fonts/CarolinaBarUPC_Normal.ttf';
$font_code128 			= dirname(__DIR__) . '/fonts/code128L.ttf';
$font_georgia_bold 	= dirname(__DIR__) . '/fonts/Georgia Bold.ttf';
$font_futura_bk 		= dirname(__DIR__) . '/fonts/FtraBk__.ttf';
$font_futura_condensed 	= dirname(__DIR__) . '/fonts/FtraCondensed.ttf';
$font_arial 			= dirname(__DIR__) . '/fonts/Arial.ttf';
$font_myriad			= dirname(__DIR__) . '/fonts/MyriadPro-Regular.otf';

$session_id = !empty($data['session_id']) ? $data['session_id'] : null;

/************************************************
 *		New logic for updated render coupon			*
 *		Check for background image, 					*
 *		if it doesn't exist, create it, upload 	*
 *		it to S3 and save its entry.
 ***********************************************/
$campaign_obj = new Campaign($campaign_id);
if(is_null($campaign_obj->img_voucher_background) || empty($campaign_obj->img_voucher_background)){
	// $start_time = array_sum(explode(" ", microtime()));
	Campaign::UpdateCampaignVoucherBackground($item_id, $campaign_id);
	// need to instantiate the campaign obj due to just changing it
	$campaign_obj = new Campaign($campaign_id);
	// error_log("Time taken to update campaign voucher background and then calling the campaign object: ". array_sum(explode(" ", microtime())) - $start_time . ' ' . __FILE__ . ' line ' . __LINE__);

}
// error_log("campaign_obj: ".var_export($campaign_obj, true));

// $start_time = array_sum(explode(" ", microtime()));
$s3 = new CoupsmartS3();
$s3->s3_connect();


$img_voucher_background = $upload_bucket_path . $campaign_obj->img_voucher_background;



/*********************************************
*	Getting the dynamic voucher layout parts	*
**********************************************/
// $t1 = array_sum(explode(" ", microtime()));
$voucher_layout_parts = Campaign::GetCampaignVoucherLayoutParts($campaign_id, 1);
// $t2 = array_sum(explode(" ", microtime()));
// error_log("Time taken to call Campaign::GetCampaignVoucherLayoutParts: " . ($t2-$t1) . ' ' . __FILE__ . ' line ' . __LINE__);

/***********************
*   Get user info      *
***********************/
$uiid = '';
$user_id = '';
if(isset($data['user_id'])){
	$user = new User($data['user_id']);
	$user_id = $data['user_id'];
}



/***************************************
*	CAMPAIGN SPECIFIC LOCATIONS
***************************************/


// error_log("location_specific_vouchers in render-coupon-using-layout: " . $location_specific_vouchers . ", bg_img: " . $bg_img);
if(!empty($location_specific_vouchers))
{
	if(!empty($bg_img))
	{
		$img_voucher_background = $upload_bucket_path . $bg_img;
	}
	else
	{
		$img_voucher_background = $upload_bucket_path . $campaign_obj->img_placeholder;
		$voucher_layout_parts = array();
	}
	// error_log("img_voucher_background in render-coupon-using-layout: " . $img_voucher_background);
}

$background = new Imagick($img_voucher_background);
// $background = imagecreatefromjpeg($img_voucher_background);

// $t2 = array_sum(explode(" ", microtime()));
// error_log("Time taken to call Imagick() constructor: " . ($t2-$t1) . ' ' . __FILE__ . ' line ' . __LINE__);
// $background->writeImage(dirname(__DIR__) . '/images/downloaded/background-' . $campaign_obj->img_voucher_background);

/******************************************************
*	Generating the coupcheck barcode	and cashier code	*
*******************************************************/
$coucheck_upc = '';
$cashier_code = '';




/******************************************************************
*   Getting Coupcheck Barcode dimensions for later use      *
*******************************************************************/

$coupcheck_barcode_x = 0;
$coupcheck_barcode_width = 0;
foreach($voucher_layout_parts as $i => $part)
{
	if($part['type'] == 'coupcheck_barcode')
	{
		$coupcheck_barcode_x 		= $part['x'];
		$coupcheck_barcode_width 	= $part['width'];
	}
}

// DETERMINE EXPIRY DATE
if($item->use_rolling_expiry_date == '1')
{
	
	$days_rolling_expiry_date = $item->days_rolling_expiry_date;
	
	$expiry_date = Common::getDBCurrentDate($days_rolling_expiry_date, 'day', '%m/%d/%Y');
	$expiry = ' EXPIRES ' . $expiry_date . ' ';
}
else if(!empty($data['expiration_date']))
{

	$expiry = ' EXPIRES ' . Common::getDBCurrentDate(null, '', '%m/%d/%Y', $data['expiration_date']) . ' ';
}

// error_log('voucher layout parts: '. var_export($voucher_layout_parts, true));
foreach($voucher_layout_parts as $i => $part)
{
	$start_time = array_sum(explode(" ", microtime()));

	$type 	= $part['type'];
	$height	= $part['height'];
	$width	= $part['width'];
	$x			= $part['x'];
	$y			= $part['y'];
	$layer	= $part['layer'];
	$bg_color= !empty($part['bg_color']) ? $part['bg_color'] : 'transparent';
	$default_content	= $part['default_content'];
	$layout_width = $part['layout_width'];
	$layout_height = $part['layout_height'];
	$opacity	= $part['opacity'];
	$style	= $part['style'];
	
	switch($type)
	{
		//	COUPCHECK LOGO
		case 'coupcheck_logo':
			$coupcheck_logo_path = $default_content;
			$coupcheck_logo = file_get_contents($default_content);
			if ($coupcheck_logo) {
				//error_log('resizing fb image');
				$ccl_binary = new Imagick();
				$ccl_binary->readImageBlob($coupcheck_logo);
				$background->compositeImage($ccl_binary, imagick::COMPOSITE_OVER, $x, $y, $bg_color);
			}
			// error_log("Time taken to render coupcheck_logo: ".array_sum(explode(" ", microtime())) - $start_time . ' ' . __FILE__ . ' line ' . __LINE__);
			break;
		
		case 'coupcheck_barcode':
			# Unique ID
			# 11 digits, upc2CarolinaTxt will add a checksum on the end
			$is_click_referral = 0;
			if(ClickReferral::click_referral_exists($session_id))
				$is_click_referral = 1;
			// error_log('existing_uiids: '.$existing_uiids);
			
			if(!empty($reprint_code))
			{
				$sql = "update `user_items` set reprinted = reprinted + 1, allow_reprint = '0' where reprint_code = '" . Database::mysqli_real_escape_string($reprint_code). "'";
				if(!Database::mysqli_query($sql))
					error_log("SQL update error in render-coupon-using-layout.php: " . Database::mysqli_error() . "\nSQL: " . $sql);
				else if(Database::mysqli_affected_rows() == 0)
					error_log("No rows were affected by the update query in reprint mode: ".$sql);
				
				setcookie("reprint_code", "", time()-3600);
				unset($_COOKIE['reprint_code']);
				
				// Get barcode
				$arr_existing_uiids = explode(',', $existing_uiids);
				$uiid = end($arr_existing_uiids);
			}
			else
			{
				$distributor_id = !empty($data['d']) ? ("'" . Database::mysqli_real_escape_string($data['d']) . "'") : 'NULL';
				// $timer_start = array_sum(explode(" ", microtime()));
				if(!$is_preview && ($item_id != 1420))
				{
					$view_code = isset($data['view_code']) ? $data['view_code'] : null;
					$uiid = UserItems::insert_unique_uiid($uiid, $item_id, $user_id, 1, $distributor_id, $is_click_referral, $view_code, $items_views_id, $item->manufacturer_id);
					error_log("   Time taken to generate uiid: ".(array_sum(explode(" ", microtime())) - $start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
					
					Item::checkAndRunAnotherDealUponOutOfStock($item->deal_upon_out_of_stock, $item_id, $item->deal_id);

					// Check whether the user has to be notified of the print via email
					if($item->delivery_method == '3')
					{
						$company = new Company($item->manufacturer_id);
						if($company->send_email_after_printing == '1')
						{
							// Send Email here
							EmailTemplates::sendEmailAlert(EmailTemplates::$notify_coupon_printed, $uiid);
						}	
					}
					
					if(!empty($company->is_mailchimp_company) && !empty($company->mc_list_id) && $company->id == '68')
					{
						$mailchimp_template = EmailTemplates::$mc_demo_request_email;
						// Send Mailchimp template
						// EmailTemplates::sendEmailAlert($mailchimp_template, array('sendEmailTo' => $user->email, 'companyId' => $company->id, 'userId' => $user_id));
					}
					error_log("   Time taken to send email: ".(array_sum(explode(" ", microtime())) - $start_time) . ' ' . __FILE__ . ' line ' . __LINE__);

				}
			}
			// $timer_start = array_sum(explode(" ", microtime()));
			$style = strtolower(str_replace(' ', '', $style));
			if($style != 'display:none;')
				Common::renderText(Common::upc2CarolinaTxt($uiid), $font_upca, $background, $width, $height, $x, $y, 'transparent');
			// error_log("   Time taken to render text: ".(array_sum(explode(" ", microtime())) - $start_time) . ' ' . __FILE__ . ' line ' . __LINE__);

			// $timer_end = array_sum(explode(" ", microtime()));
			// error_log("RenderText: " . ($timer_end - $timer_start));
			// Log Claimed Activity
			// UserActivityLog::log_user_activity($user->id, 'claimed', $app_name, $item_id);
			$now = array_sum(explode(" ", microtime()));
			// error_log("Time taken to render coupcheck_barcode: " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
			break;
			
		case 'cashier_code':
			
			if(empty($data['sgs']) || $data['sgs'] == '0'){
				if(empty($existing_uiids)) // This means that the user had already tried printing the coupon
				{
					Item::checkAndUpdateOutOfStockItems($item_id);
				}
				// Update claimed_attempts
				if(!empty($claimed_attempt_id))
				{
					// $timer_start = array_sum(explode(" ", microtime()));
					$sql = "update claim_attempts set claimed = '1' where id = '".Database::mysqli_real_escape_string($claimed_attempt_id)."'";
					Database::mysqli_query($sql);
					// $timer_end = array_sum(explode(" ", microtime()));
					// error_log("CLAIMED ID: " . ($timer_end - $timer_start));
				}
			
				$item = new Item($item_id);   // reload the item from the DB
				
				// Get its parent campaign
				// $timer_start = array_sum(explode(" ", microtime()));
				$campaign_rs = Database::mysqli_query("select * from campaigns where id = '" . Database::mysqli_real_escape_string($item->campaign_id) . "'");
				// $timer_end = array_sum(explode(" ", microtime()));
				// error_log("Parent Campaign Query: " . ($timer_end - $timer_start));
				$campaign = null;
				if ($campaign_rs && Database::mysqli_num_rows($campaign_rs) > 0) {
					$campaign = Database::mysqli_fetch_assoc($campaign_rs);
				}
			
				// Update cache_resellers_stats with the new committed value
				$sql = "update cache_resellers_stats set prints = ifnull(prints, 0) + 1 where companies_id = '".Database::mysqli_real_escape_string($item->manufacturer_id)."'";
				if(!$is_preview)
				{
					// $timer_start = array_sum(explode(" ", microtime()));
					Database::mysqli_query($sql);
					// $timer_end = array_sum(explode(" ", microtime()));
					// error_log("Update Cache: " . ($timer_end - $timer_start));
				}
				
				$cashier_code = Item::get_cashier_code($item->campaign_id);
			}elseif($data['sgs'] == '1'){
				$cashier_code = $data['sgs_order_recipient_id'];
				// Updating last printed date
				Item::UpdateSGSPrintDate($sgs_recipient_id);				
				Item::SendSGSPrintNotification($data['sgs_order_recipient_id']);
			}
			
			$style = strtolower(str_replace(' ', '', $style));
			if($style != 'display:none;')
				Common::renderText($cashier_code, $font_plain, $background, $width, $height, $x, $y, $bg_color);
			// $now = array_sum(explode(" ", microtime()));
			// error_log("Time taken to render cashier_code: " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
			break;
		
		case 'user_image':
			
			$fb_binary = null;
			if (!empty($user->facebook_id) && $user->facebook_id != '') {
				$facebook_image_url = "http://graph.facebook.com/v" . $app_version . "/" . $user->facebook_id . "/picture?width=$width&height=$height";
				
				$fbimage = file_get_contents($facebook_image_url);
				
				
				if ($fbimage) {
					//error_log('resizing fb image');
					$fb_binary = new Imagick();
					$fb_binary->readImageBlob($fbimage);
					
					$fb_binary->setImageColorspace($background->getImageColorspace());
					
					$resized_dimensions = Common::scaleProportional($fb_binary, $width, $height);
					// error_log("resized_dimensions: " . var_export($resized_dimensions, true));
					$fb_binary->resizeImage($resized_dimensions['width'], $resized_dimensions['height'], imagick::FILTER_MITCHELL, 0.9, false);
				}
				
			}
			
			if ($fb_binary) {
				$background->compositeImage($fb_binary, imagick::COMPOSITE_OVER, $x, $y);
			} else {
				// Use the anonymous image.
				$anon_blob = file_get_contents($default_content);
				$anon_img = null;
				$anon_img = new Imagick();
				$anon_img->readImageBlob($anon_blob);
				$background->compositeImage($anon_img, imagick::COMPOSITE_OVER, $x, $y);
			}
			 $now = array_sum(explode(" ", microtime()));
			error_log("Time taken to render user image: " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
			break;
		
		case 'user_name':
			Common::renderText($user->firstname . ' ' . $user->lastname, $font_plain, $background, $width, $height, $x, $y, $bg_color);
			 // $now = array_sum(explode(" ", microtime()));
			 // error_log("Time taken to render user_name: " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
			break;
		
		case 'manufacturer_caption':
			Common::renderText(' MANUFACTURER COUPON ', $font_futura_condensed, $background, $width, $height, $x, $y, $bg_color);
			 // $now = array_sum(explode(" ", microtime()));
			 // error_log("Time taken to render manufacturer_coupon: " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
			 $border_offset = -2;
			 Common::drawRectangle($background, $x + $border_offset, $y + $border_offset, $x + $width - $border_offset, $y + $height - $border_offset - 1);
			 
			break;
		
		case 'expiry_caption':
			if(!empty($expiry))
				Common::renderText($expiry, $font_futura_condensed, $background, $width, $height, $x, $y, $bg_color);
			 // $now = array_sum(explode(" ", microtime()));
			 // error_log("Time taken to render manufacturer_coupon: " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);
			 $border_offset = -2;
			 Common::drawRectangle($background, $x + $border_offset, $y + $border_offset, $x + $width - $border_offset, $y + $height - $border_offset - 1);
			break;
		
		case 'expiry':
			
				
				if(!empty($expiry))
					Common::renderText($expiry, $font_plain, $background, $width, $height, $x, $y, $bg_color);
			
			 // $now = array_sum(explode(" ", microtime()));
			 // error_log("Time taken to render expiry date: ". $now-$start_time . ' ' . __FILE__ . ' line ' . __LINE__);
			break;

	}
}
$img_name = md5(uniqid() . $user_id . time() .rand(0, 9999999));
// error_log("img_name: ".$img_name);

$img_voucher_background = dirname(__DIR__) . '/images/downloaded/'.$img_name.'.jpg'; // Or try to generate a unique file name

// error_log("img_voucher_background: ".var_export($img_voucher_background, true));

// $start_time = array_sum(explode(" ", microtime()));
$background->writeImage($img_voucher_background); // about half the time for this layout happens here
// $now = array_sum(explode(" ", microtime()));
// error_log("Time taken to run background->writeimage(): " . ($now-$start_time) . ' ' . __FILE__ . ' line ' . __LINE__);

if ($is_preview) {
	//Set no caching
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header('Content-Type: image/jpeg');
	$output = readfile($img_voucher_background);
	// error_log("output: ".var_export($output, true));
} else {
	$CS3 = new CoupsmartS3();
	$CS3->s3_connect();
	$s3_image_file = $CS3->add_voucher_obj($img_voucher_background);
	header('Content-Type: application/json; charset=utf-8');
	echo json_encode($s3_image_file);
}

unlink($img_voucher_background);

// $final_time = array_sum(explode(" ", microtime()));
// error_log("total time: " . ($final_time-$begin_time));
?>