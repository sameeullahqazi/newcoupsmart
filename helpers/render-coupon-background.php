<?php
//Set no caching
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
header("Cache-Control: no-store, no-cache, must-revalidate"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once(dirname(__DIR__) . '/classes/Database.class.php');
require_once(dirname(__DIR__) . '/classes/BasicDataObject.class.php');
require_once(dirname(__DIR__) . '/classes/Common.class.php');
require_once(dirname(__DIR__) . '/classes/Session.class.php');
require_once(dirname(__DIR__) . '/classes/CacheImage.class.php');
require_once(dirname(__DIR__) . '/classes/CoupsmartS3.class.php');
require_once(dirname(__DIR__) . '/classes/Item.class.php');
require_once(dirname(__DIR__) . '/classes/Campaign.class.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');

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

session_start();


define("UPC_BACKGROUND_WIDTH", 1999);
define("UPC_BACKGROUND_HEIGHT", 301);
define("UPC_BACKGROUND_X", 6);
define("UPC_BACKGROUND_Y", 638);
// error_log("REQUEST: ".var_export($_REQUEST, true));

$item_id = !empty($_REQUEST['item_id']) ? $_REQUEST['item_id'] : '';
// Get Coupon Info
$coupon_info = Item::getCouponInfo($item_id);
$coupon = $coupon_info[0];
// error_log("coupon in render-coupon-background: ".var_export($coupon, true));


$img_prefix 			= !empty($coupon['img_prefix']) ? $coupon['img_prefix'] : "";
$img_path 				= dirname(__DIR__) . '/images/tmp_render_coupon/temp-' . $img_prefix. time() . '.png';

// $bg_path 				= dirname(__DIR__) . '/images/coupon/bg-omega.png';
// old treat 'em right logo
// $barcode_substitute 	= dirname(__DIR__) . '/images/coupon/CoupSmartMemberTreatEmRight.png';
// new coupcheck logo
$barcode_substitute 	= dirname(__DIR__) . '/' . $upload_bucket . '/coupcheck_logo.jpg';

$font_bold 				= dirname(__DIR__) . '/fonts/VeraBd.ttf';
$font_plain 			= dirname(__DIR__) . '/fonts/Vera.ttf';
$font_upca 				= dirname(__DIR__) . '/fonts/CarolinaBarUPC_Normal.ttf';
$font_code128 			= dirname(__DIR__) . '/fonts/code128L.ttf';
$font_georgia_bold 	= dirname(__DIR__) . '/fonts/Georgia Bold.ttf';
$font_futura_bk 		= dirname(__DIR__) . '/fonts/FtraBk__.ttf';
$font_arial 			= dirname(__DIR__) . '/fonts/Arial.ttf';
$font_myriad			= dirname(__DIR__) . '/fonts/MyriadPro-Regular.otf';

$background = null;



// Get campaign coupon layout and layout parts
$campaign_id = $coupon['campaign_id'];
$voucher_layout_parts = Campaign::GetCampaignVoucherLayoutParts($campaign_id);

if(!is_null($coupon)){
	$barcode_count = 0;
	$barcode_printed = false;
	foreach($voucher_layout_parts as $i => $part)
	{
		$type 	= $part['type'];
		$height	= $part['height'];
		$width	= $part['width'];
		$x			= $part['x'];
		$y			= $part['y'];
		$layer	= $part['layer'];
		$bg_color= $part['bg_color'];
		$default_content	= $part['default_content'];
		$layout_width = $part['layout_width'];
		$layout_height = $part['layout_height'];
		
		switch($type)
		{
			//	BACKGROUND
			case 'background':
				
				$arr_file_name_parts = explode('/', $default_content);
				$file_name = end($arr_file_name_parts);
				
				$img_voucher_background = CoupsmartS3::get_image_file($file_name);
				
				$img_voucher_background = dirname(__DIR__) . '/images/downloaded/' . $file_name;
				$background = new Imagick($img_voucher_background);
				break;
			
			//	IMAGE
			case 'image':
				$logo_path = "";
				if($barcode_printed){
					$width = $part['alt_width'];
					$height = $part['alt_height'];
				}
				/*
				if(!empty($coupon['logo_file_name']))
				{
					$logo_path = CacheImage::getImg($coupon['logo_file_name'], $width, $height);
				}
				else if(!empty($coupon['default_coupon_image']))
				{
					$logo_path = CacheImage::getImg($coupon['default_coupon_image'], $width, $height);
				}
				*/
				$logo_path = '';
				switch($coupon['use_deal_voucher_image'])
				{
					case 'yes_own':
						$logo_path = dirname(__DIR__) . '/' . $upload_bucket . "/" . CacheImage::getImg($coupon['image_file'], $width, $height);
						break;
			
					case 'yes_fb_photo':
						$logo_path = "https://graph.facebook.com/v" . $app_version . "/". $coupon['facebook_page_id']."/picture?width=$width&height=$height";
						if(empty($coupon['facebook_page_id']))
							$logo_path = $default_content;
							
						break;
			
					case 'yes_company_logo':
						$logo_path = "http://uploads.coupsmart.com/" . CacheImage::getImg($coupon['default_coupon_image'], $width, $height);
						if(empty($coupon['default_coupon_image']))
							$logo_path = $default_content;
					
						
						break;
				
					default:
					
				}
				// error_log("logo path in render-coupon-background.php: ". $logo_path);
				if (!empty($logo_path)) {
					// $logo_image = file_get_contents('http://uploads.coupsmart.com/' . $logo_path);
					$logo_image = file_get_contents($logo_path);
					$customer_logo = null;
					if ($logo_image) {
						$customer_logo = new Imagick();
						$customer_logo->readImageBlob($logo_image);
					}
					$d = $customer_logo->getImageGeometry();
					$left_offset = round(($width - $d['width']) / 2);
					$top_offset = round(($height - $d['height']) / 2);
					$background->compositeImage($customer_logo, imagick::COMPOSITE_OVER, $x + $left_offset, $y + $top_offset);
				}
				break;
			
			// BARCODE
			case 'barcode':
				
				switch($barcode_count)
				{
					case 0:
						$bc_pt1 = "5" .substr($coupon['upc'], 1, 5). $coupon['barcode_family_code'] . $coupon['offer_code'];
						$upc = $bc_pt1;
						if (!empty($upc) && Common::verify_upca($upc)) 
						{
							Common::renderText('', $font_upca, $background, UPC_BACKGROUND_WIDTH, UPC_BACKGROUND_HEIGHT, UPC_BACKGROUND_X, UPC_BACKGROUND_Y, $bg_color);
							Common::renderText(Common::upc2CarolinaTxt($upc), $font_upca, $background, $width, $height, $x, $y, 'transparent');
									
							$barcode_printed = true;
							$barcode_count++;
						} else {
							if($barcode_count == 0){
								/*
									 old layout had a barcode substitute. this
									 one doesn't have any in the design, so
									 commenting this out.  We will probably need
									 to keep the logic in here so that the background 
									 gets rendered correctly
								
									$background->compositeImage(new Imagick($barcode_substitute), imagick::COMPOSITE_OVER, $x, $y);
								*/
									 
								
							}
							
						}
						break;
					
					case 1:
						$bc_pt2 = "(8101)" . substr($coupon["barcode_co_prfx"], 0, 1) . " " . $coupon['barcode_offer_code'] . " " . sprintf('%02d', $coupon['expire_month']) . sprintf('%02d', $coupon['expire_year']);
						$offer_barcode_info = $bc_pt2;
						if (!empty($offer_barcode_info)) {
							include_once(dirname(__DIR__) . "/scripts/barcode-encoder.php");
		
							$offer_barcode_info_clean = preg_replace('/[^0-9]/', '', $offer_barcode_info);
							$uccean128 = strval(utf8_encode(UCCEAN128($offer_barcode_info)));
		
							Common::renderCode128($uccean128, $font_code128, $background, $width, $height, $x, $y, 'transparent');
		
							// And the human-readable number.
							Common::renderText($offer_barcode_info, $font_arial, $background, $width, 40, $x, $y + ($height /2), 'transparent');
							$barcode_count++;
						}
						break;
				}
				
				break;
			case 'text1':
				$text1 	= $coupon['name'];
				Common::renderText($text1, $font_georgia_bold, $background, $width, $height, $x, $y, $bg_color);
				break;
			case 'text2':
				$text2 	= $coupon['offer_value'];
				Common::renderText($text2, $font_georgia_bold, $background, $width, $height, $x, $y, $bg_color);
				break;
			
			case 'text3':
				$text3 	= $coupon['small_type'];
				Common::renderText($text3, $font_myriad, $background, $width, $height, $x, $y, $bg_color);
				break;
			case 'expiry':
				if(!is_null($coupon['expires'])){
					$expiry	= 'EXP ' . $coupon['expires'];
					Common::renderText($expiry, $font_futura_bk, $background, $width, $height, $x, $y, $bg_color);
				}
				
				break;
			case 'coupcheck_logo':

				// $coupcheck_blob = file_get_contents('http://uploads.coupsmart.com/' . $logo_path);
				// error_log('barcode_substitute in the case coupcheck_logo: '.$barcode_substitute);
				// $coupcheck_blob = file_get_contents($barcode_substitute);
				$coupcheck_blob = file_get_contents($default_content);
            $coupcheck_logo = null;
				$coupcheck_logo = new Imagick();
				$coupcheck_logo->readImageBlob($coupcheck_blob);

				$background->compositeImage($coupcheck_logo, imagick::COMPOSITE_OVER, $x, $y, $bg_color);

				break;	
		}
		
	}
	header('Content-Type: image/png');
	$background->writeImage($img_path);
	readfile($img_path);
	unlink($img_path);
}else{
    throw new Exception('NO ITEM INFO FOUND INSIDE ' . (__FILE__));
}
?>