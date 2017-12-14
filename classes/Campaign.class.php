<?php

require_once (dirname(__DIR__) . '/includes/app_config.php');

/*
require_once (dirname(__DIR__) . '/classes/BasicDataObject.class.php');
require_once(dirname(__DIR__) . "/sdks/phpexcel/Classes/PHPExcel.php");
*/

/**
* UserFavoriteStores Class
*/
class Campaign extends BasicDataObject
{
	var $name;
	var $total_methods;
	var $allow_overage;
	var $logo_file_name;
	var $logo_preview_file_name;
	var $product_selling_options;
	var $other_business_options;
	var $use_product_barcode;
	var $use_coupon_barcode;
	var $use_offer_code;
	var $use_share_bonus;
	var $campaign_email_80;
	var $campaign_email_90;
	var $campaign_email_full;
	var $auto_renew;
	var $num_auto_renewals;
	var $num_renewals_performed;
	var $birthday_coupon_image;
	var $birthday_coupon_text;
	var $birthday_coupon_num_days_weeks_months;
	var $birthday_coupon_day_week_month;
	var $disable_expire;
	var $status;
	var $stats_campaign;
	var $stats_user;
	var $stats_social;
	var $is_featured;
	var $featured_image;
	var $voucher_layout_id;
	var $img_voucher_background;
	var $email_layout_id;
	var $email_file;
	var $convercial_type;
	var $turn_on_deal_when;
	var $turn_off_deal_when;
	var $expire_when;
	var $use_deal_voucher_image;
	var $use_preview_deal_voucher_image;
	var $post_text;
	var $auto_post;
	var $created;
	var $require_like;
	var $require_info;
	var $require_share;
	var $hide_share_button;
	var $img_fan_deals;
	var $img_instore_deals;
	var $img_sharing;
	var $img_placeholder;
	var $csc_report_recipients;
	var $img_expired_used_up;
	var $add_likebar;
	var $img_likebar;
	var $likebar_content;
	
	public static $platform_names = array(WEB => "web", FACEBOOK => "facebook", BIRTHDAY => "birthday", WALKIN => "convercial");
	public static $step_names = array(1 => "company", 2 => "platform", 3 => "campaign", 4 => "birthday", 5 => "convercial", 6 => "dealstyle", 7 => "share");

	function __construct($id = null)
	{
		if(!empty($id))
		{
			$id = Database::mysqli_real_escape_string($id);
			$this->Select("id='".$id."'");
		}
		return $this;
	}
	
	public static function getCompanyCampaigns($company_id)
	{
		$sql = "select i.campaign_id, i.id as item_id, i.campaign_name as campaign_name, i.short_name, i.deal_id, i.delivery_method, i.status from items i where i.manufacturer_id = '$company_id'";
		return BasicDataObject::getDataTable($sql);
	}

	public static function UpdateCampaignVoucherBackground($item_id, $campaign_id){
	
		// Render background image and upload it to S3
		$background_img_content = file_get_contents(Common::getBaseURL()."/helpers/render-coupon-background.php?item_id=".$item_id);
		// error_log("background_img_content in Campaign::UpdateCampaignVoucherBackground(): ".var_export($background_img_content, true));
		
		// Proceed and update img_voucher_background only if the background image was created successfully.
		if($background_img_content)
		{
			$CS3 = new CoupsmartS3();

			// connect to S3 cloud
			$CS3->s3_connect();

			// Upload to S3
			list($expiry_date, $img_coupon_background) = $CS3->add_private_obj($background_img_content);
		
			// Update campaigns row
			Campaign::update_coupon_background_img($img_coupon_background, $campaign_id);
		}
	
	}
	
	public static function update_coupon_background_img($img_coupon_background, $campaign_id)
	{
		$sql = "update campaigns set `img_voucher_background` = '$img_coupon_background' where id = '$campaign_id'";
		if(!Database::mysqli_query($sql))
			error_log("SQL update error in Campaign::update_coupon_background_img(): ".Database::mysqli_error() . "\nSQL: ".$sql);
		
	}
	
	public static function GetCampaignVoucherLayoutParts($campaign_id, $is_dynamic = 0)
	{
		$sql = "select vlp.*, vl.width as layout_width, vl.height as layout_height, 
			vl.bg_color as layout_bg_color, vl.name as layout_name, alt_width, alt_height
		from campaigns c 
		inner join voucher_layouts vl on c.voucher_layout_id = vl.id
		inner join voucher_layout_parts vlp on vlp.voucher_layout_id = vl.id
		where c.id = '".Database::mysqli_real_escape_string($campaign_id)."'
		and is_dynamic = '$is_dynamic'
		order by vlp.layer, vlp.id";
		// error_log("sql in GetCampaignVoucherLayoutParts(): ".$sql);
		
		return BasicDataObject::getDataTable($sql);
	}
}
?>