<?php
require_once(dirname(__DIR__) . '/includes/app_config.php');
/*
require_once(dirname(__DIR__) . '/classes/Mailer.class.php');
require_once(dirname(__DIR__) . '/classes/CacheImage.class.php');
require_once(dirname(__DIR__) . '/classes/CampaignSharingImage.class.php');
require_once(dirname(__DIR__) . '/classes/Company.class.php');
require_once(dirname(__DIR__) . '/classes/Common.class.php');
*/
/**
* Item Class
*/
class Item extends BasicDataObject
{
	// Database fields as class attributes
	var $id;
	var $type_id;
	var $manufacturer_id;
	var $deal_id;
	var $upc;
	var $short_name;
	var $small_type;
	var $small_type_preview;
	var $name;
	var $description;
	var $details;
	var $details_preview;
	var $gmap;
	var $us_time_zone;
	var $start_date;
	var $end_date;
	var $expires;
	var $delivery_method;
	var $limit_per_person;
	var $retail_price;
	var $savings;
	var $offer_code;
	var $offer_value;
	var $offer_value_preview;
	var $social_offer_service_name;
	var $social_offer_code;
	var $social_offer_value;
	var $social_small_type;
	var $new_fan_offer;
	var $num_friends;
	var $use_coupon_barcode;
	var $barcode_co_prfx;
	var $barcode_family_code;
	var $barcode_social_family_code;
	var $barcode_offer_code;
	var $barcode_social_offer_code;
	var $barcode_social_offer_service_name;
	var $expire_month;
	var $expire_year;
	var $inventory_count;
	var $controlled_printable_image;
	var $created;
	var $admin_approval;
	var $supplied;
	var $committed;
	var $shipped;
	var $social_print_count;
	var $status;
	var $needs_clearinghouse;
	var $needs_clearinghouse_barcode;
	var $campaign_name;
	var $campaign_id;
	var $view_count;
	var $platform_social_offer_service_name;
	var $platform_social_offer_code;
	var $platform_social_offer_value;
	var $platform_social_offer_small_type;
	var $platform_num_friends;
	var $redirect_url;
	var $static_fulfillment_html;
	var $hotel_discount_id;
	var $hotel_discount_percent;
	var $hotel_discount_amount;
	var $share_own_wall;
	var $share_friends_wall;
	var $share_send_request;
	var $magento_email_check;
	var $magento_landing_page;
	var $magento_landing_page_url;
	var $magento_landing_page_setup_header;
	var $magento_landing_page_setup_body;
	// var $require_like;
	// var $require_sharing;
	
	var $instore_main_text;
	var $instore_btn_view_offers_text;
	var $instore_email_print_btn;
	var $instore_email_onscreen_btn;
	var $instore_email_footer_content;
	
	var $white_label_css;
	var $white_label_css_1;
	var $white_label_css_2;
	var $white_label_css_3;
	var $white_label_css_4;
	
	var $button_color;
	var $button_text_color;
	var $button_details_color;
	var $mo_headline_bg;
	var $mo_headline_text_color;
	var $mo_header_color;
	var $mo_body_color;
	var $company_sdw_unique_codes_id;
	
	var $show_print_options;
	var $csc_reveal_deal_content;
	var $csc_reveal_deal_content_mobile;
	var $csc_cta_heading;
	var $csc_cta_url;
	var $csc_custom_code;
	var $csc_email_from;
	var $csc_email_subject;
	var $csc_email_template;
	var $csc_email_header_image;
	var $csc_email_store_url;
	
	var $instore_email_from;
	var $instore_email_subject;
	var $instore_email_header_img;
	var $instore_email_header_caption;
	var $banner_image_link_url;

	var $footer_content;
	var $parent_item_id;
	
	var $app_id;
	var $deliverable_id;
	
	var $e_commerce_code;
	var $use_rolling_expiry_date;
	var $days_rolling_expiry_date;
	
	var $unique_email_code;		//	For delivery method 13
	var $email_code_snippet;	//	For delivery method 13
	var $email_code_integration_type;
	var $email_code_content_type;
	var $email_code_service_url;
	var $email_code_color;
	var $email_code_size;
	
	var $use_bundled_coupons;
	var $bundled_coupon_copy;
	
	var $for_consultants;
	var $deal_upon_out_of_stock;
	var $out_of_stock_deal;
	
	var $coupon_age_limit;
	var $location_specific_vouchers;
	var $trigger_url;
	
	function __construct($id = null, $read_only_mode = true){
		if(!empty($id))
		{
			$id = "id='" . $id . "'";
			$this->Select($id, $read_only_mode);
		}
	}
	
	public static function getItemsViewsSessionId($items_views_id)
	{
		$sql = "select session_id from items_views where id = '$items_views_id'";
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row['session_id']) ? $row['session_id'] : null;
	}
	
	public static function getReferralInfoByCode($referral_code)
	{
		$sql = "select * from referrals where referral_code = '$referral_code'";
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function getReferralInfoById($referral_id)
	{
		$sql = "select * from referrals where id = '$referral_id'";
		return BasicDataObject::getDataRow($sql);
	}
	
	
	public static function updateItemViewsUserId($claimed_item_id, $user_id)
	{		
		$ip = Common::GetUserIp();
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$session_id = session_id();
		
		$sql = "update items_views set user_id = '$user_id'
			where items_id='$claimed_item_id' 
			and session_id='$session_id' 
			and ip='$ip' 
			and user_agent='$user_agent' 
			and (user_id = 0 or user_id is null)";
		// error_log("SQL in Item::updateItemViewsUserId(): " . $sql);
		if(!Database::mysqli_query($sql))
			error_log("SQL Update error in Item::updateItemViewsUserId(): " . Database::mysqli_error() . "\nSQL: " . $sql);
		
		return Database::mysqli_affected_rows();
		
	}
	
	public static function updatePermissionsRejected($item_id, $share_permissions = null, $permissions_accepted = false)
	{
		$session_id = session_id();
		$sql = "update items_views set permissions_rejected = ifnull(permissions_rejected, 0) + 1 where items_id = '$item_id' and session_id = '$session_id' order by id desc limit 1";
		if($permissions_accepted)
			$sql = "update items_views set permissions_rejected = ifnull(permissions_rejected, 0) - 1 where items_id = '$item_id' and session_id = '$session_id' order by id desc limit 1";
				
		if(!empty($share_permissions))
		{
			$sql = "update items_views set share_permissions_rejected = ifnull(share_permissions_rejected, 0) + 1 where items_id = '$item_id' and session_id = '$session_id' order by id desc limit 1";
			if($permissions_accepted)
				$sql = "update items_views set share_permissions_rejected = ifnull(share_permissions_rejected, 0) - 1 where items_id = '$item_id' and session_id = '$session_id' order by id desc limit 1";
		}	
		Database::mysqli_query($sql);
	}
	
	public static function getItemsViewsIdByItemId($item_id)
	{
		$session_id = session_id();
		$sql = "select id from items_views where items_id = '$item_id' and session_id = '$session_id' order by id desc limit 1";
		$row = BasicDataObject::getDataRow($sql);
		return $row['id'];
	}
	
	public static function getMOItemInfo($item_id)
	{
	
		$loc_zip_code	= !empty($_SESSION['loc_zip_code']) ? $_SESSION['loc_zip_code'] : "";
		$loc_dma		= !empty($_SESSION['loc_dma']) ? $_SESSION['loc_dma'] : "";
		
		$loc_inner_join = "";
		$where_clause = "";
		if(!empty($loc_zip_code))
		{
			$loc_inner_join = " inner join campaigns_locations cl on i.campaign_id = cl.campaigns_id inner join locations l on cl.locations_id = l.id";
			$where_clause = " and ((l.zip = '$loc_zip_code' and cl.is_backup_deal = 0) or (l.zip != '$loc_zip_code' and cl.is_backup_deal = 1))";
		}
		
		if(!empty($loc_dma))
		{
			$loc_inner_join = " inner join campaigns_locations cl on i.campaign_id = cl.campaigns_id inner join locations l on cl.locations_id = l.id";
			// To be customized later
			// $where_clause = " and ((lcase(l.city) = '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 0) or (lcase(l.city) != '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 1))";
			$where_clause = " and ((lcase(l.state) = '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 0) or (lcase(l.state) != '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 1))";
		}
		
		$sql = "select i.manufacturer_id as company_id, i.deal_id, i.campaign_id, i.name as deal_name, i.delivery_method, i.status, i.white_label_css_2, i.small_type, i.offer_value, date(i.expires) as expiry, i.expire_month, i.expire_year, i.csc_reveal_deal_content, i.csc_reveal_deal_content_mobile, i.csc_email_from, i.csc_email_subject, csc_email_header_image, i.csc_email_template, i.csc_custom_code, i.instore_email_from, i.instore_email_subject, i.instore_email_header_img, i.instore_email_print_btn, i.use_bundled_coupons, i.coupon_age_limit, i.trigger_url, 
		c.display_name as company_name, c.mo_header_caption, c.default_coupon_image, c.facebook_page_id, c.is_silverpop_company, c.is_et_company, c.sp_is_ubx, c.is_mailchimp_company, c.mc_list_id, c.mc_api_key, c.is_campaign_monitor_company, c.cm_client_id, c.cm_api_key, d.cm_list_id, d.cm_list_name, c.enable_user_blocking, c.mobile_placeholder_image, c.enable_pixel_tracking, c.use_location_based_deals, c.use_donation_based_deals
		from items i 
		inner join deals d on i.deal_id = d.id
		inner join companies c on i.manufacturer_id = c.id
		$loc_inner_join
		where i.id = '$item_id'
		$where_clause";
		// error_log("SQL in getMOItemInfo(): " . $sql);
		$item_info = BasicDataObject::getDataRow($sql);
		return $item_info;
	}
	
	
	public static function updateItemsViewsColumn($column_name, $column_value, $items_views_id)
	{
		$update_sql = "update items_views set `$column_name` = '$column_value' where id = '$items_views_id'";
		Database::mysqli_query($update_sql);
	}
	
	public static function dealHasViews($deal_id, $company_id)
	{
		$sql = "select iv.id from items_views iv inner join items i on iv.items_id = i.id where i.deal_id = '$deal_id' and i.manufacturer_id = '$company_id' limit 1";
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row['id']);
	}
	
	public static function update_user_id_in_item_view($user_id, $item_view_id)
	{
		$sql = "update items_views set user_id = '$user_id' where id = '$item_view_id'";
		if(!Database::mysqli_query($sql))
			error_log("Update SQL error in Item::update_user_id_in_item_view(): ".Database::mysqli_error()."\nSQL: ".$sql);
	}
	
	public static function update_user_id_in_referrals($user_id, $new_referral_id)
	{
		$sql = "update referrals set user_id = '$user_id' where id = '$new_referral_id'";
		if(!Database::mysqli_query($sql))
			error_log("Update SQL error in Item::update_user_id_in_referrals(): ".Database::mysqli_error()."\nSQL: ".$sql);
	}
	
	public static function addItemView($item_id, $company_id, $user_id = 0, $smart_link_id = NULL, $silver_pop_click_id = NULL, $referral_id = NULL, $shortened_url_hit_id = null, $distributor = null)
	{
		$is_click_referral = '0';
		if(ClickReferral::click_referral_exists())
			$is_click_referral = '1';

		$smart_link_id = empty($smart_link_id)	? 'NULL' : "'" . $smart_link_id . "'";
		$silver_pop_click_id = !empty($silver_pop_click_id) ? "'" . $silver_pop_click_id . "'" : 'NULL';
		$referral_id = !empty($referral_id) ? "'" . $referral_id . "'" : 'NULL';
		$shortened_url_hit_id = !empty($shortened_url_hit_id) ? "'" . $shortened_url_hit_id . "'" : 'NULL';
		
		if (is_null($distributor)) {
			$sql = "insert into items_views (items_id, user_id, user_agent, company_id, ip, session_id, is_click_referral, smart_link_id, silver_pop_click_id, referral_id, shortened_url_hit_id) values(" . Database::mysqli_real_escape_string($item_id) . ", '$user_id', '" . $_SERVER['HTTP_USER_AGENT'] . "', " . Database::mysqli_real_escape_string($company_id) . ", '" . Common::GetUserIp() . "', '" . session_id() . "', '$is_click_referral', $smart_link_id, $silver_pop_click_id, $referral_id, $shortened_url_hit_id);";
		}else{
			$sql = "insert into items_views (items_id, user_id, distributors_id, user_agent, company_id, ip, session_id, is_click_referral, smart_link_id, silver_pop_click_id, referral_id) values(" . Database::mysqli_real_escape_string($item_id) . ", '$user_id', '" .Database::mysqli_real_escape_string($distributor). "', '" . $_SERVER['HTTP_USER_AGENT'] . "'," . Database::mysqli_real_escape_string($company_id) . ", '" . Common::GetUserIp() . "', '" . session_id() . "', '$is_click_referral', $smart_link_id, $silver_pop_click_id, $referral_id, $shortened_url_hit_id);";
		}
		// error_log('Insert SQL in Item::addItemView(): ' . $sql);
	
		if(!Database::mysqli_query($sql))
			error_log("SQL Errror in function Item::addItemView(): ".Database::mysqli_error() . "\nSQL: ".$sql);
	
		$insert_id = Database::mysqli_insert_id();
		return $insert_id;
	}
	
	public static function canUserPrintItem($user_id, $item_id)
	{
		$result = true;
		$sql = "select i.id, i.limit_per_person, count(ui.id) as num_prints_used, u.status
				from items i
				left join user_items ui on (i.id = ui.item_id and ui.user_id = '" . Database::mysqli_real_escape_string($user_id) . "' and ui.date_claimed is not null)
				left join users u on u.id = '" . Database::mysqli_real_escape_string($user_id) . "'
				where i.id = '" . Database::mysqli_real_escape_string($item_id) . "'
				group by i.id";
		
		$sql = "select i.deal_id, i.limit_per_person, count(ui.id) as num_prints_used, u.status
			from items i
			left join user_items ui on (i.id = ui.item_id and ui.user_id = '$user_id' and ui.date_claimed is not null)
			left join users u on u.id = '$user_id'
			inner join (
			select deal_id from items where id = '$item_id'
			) as t on i.deal_id = t.deal_id
			group by i.deal_id";
		
		//error_log($sql);
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$limit_per_person = $row['limit_per_person'];
			$num_prints_used = $row['num_prints_used'];
			$status = $row['status'];

			if( $num_prints_used  >= $limit_per_person || $status == 'suspended') {
				$result = false;
			}
		} else {
			$result = false;
		}
		Database::mysqli_free_result($rs);
		return $result;
	}
	
	public static function checkAndUpdateOutOfStockItems($item_id)
	{
		$sql = "select i.id, i.inventory_count, count(ui.id) as num_claims from items i left join user_items ui on (i.id = ui.item_id and ui.date_claimed is not null) where i.id = '$item_id' group by ui.item_id";
		// error_log("SQL in Item::checkAndUpdateOutOfStockItems(): " . $sql);
		$row = BasicDataObject::getDataRow($sql);

		$sql = "update items set committed = ifnull(committed, 0) + 1 where id = '$item_id'";
		if(!empty($row['id']))
		{
			if($row['num_claims'] >= $row['inventory_count'])
			{
				$sql = "update items set `status` = 'finished', committed = '" . $row['num_claims'] . "' where id = '$item_id'";	
			}
		}
	
		if(!Database::mysqli_query($sql))
			error_log("SQL update error in Item::checkAndUpdateOutOfStockItems(): " . Database::mysqli_error() . "\nSQL: " . $sql);
	}
	
	// Checks if another deal is to start runnin once the item gets out of stock
	public static function checkAndRunAnotherDealUponOutOfStock($deal_upon_out_of_stock, $item_id, $deal_id)
	{
		error_log("func args: " . var_export(func_get_args(), true));
		
		if(!empty($deal_upon_out_of_stock))
		{
			if(Item::hasItemRunOutOfStock($item_id))
			{
				/*$update_sql = "update items i 
				inner join campaigns c on i.campaign_id = c.id 
				set i.status = 'running', c.status = 'running'
				where i.deal_id = '$deal_id'";*/
				
				$update_sql = "update items i
				inner join campaigns c on i.campaign_id = c.id
				set i.status = 'running', c.status = 'running'
				where i.out_of_stock_deal = '$deal_id'";
				
				Database::mysqli_query($update_sql);
			}
		}
	}
	
	public static function hasItemRunOutOfStock($item_id)
	{
		// $sql = "select i.id, i.inventory_count, count(ui.id) as num_claims from items i left join user_items ui on (i.id = ui.item_id and ui.date_claimed is not null) where i.id = '$item_id' group by ui.item_id";
		
		$sql = "select i.deal_id, i.id, i.inventory_count, count(ui.id) as num_claims 
			from items i 
			left join user_items ui on (i.id = ui.item_id and ui.date_claimed is not null) 
			inner join (
			select deal_id from items where id = '$item_id'
			) as t on i.deal_id = t.deal_id
			group by i.deal_id";
		// error_log("SQL in Item::hasItemRunOutOfStock(): " . $sql);
		
		$row = BasicDataObject::getDataRow($sql);
		if(!empty($row['id']))
		{
			if($row['num_claims'] >= $row['inventory_count'])
				$has_run_out = true;
			else
				$has_run_out = false;
		}
		
		return $has_run_out;
	}
	
	public static function getItemImageDimensions($item_id)
	{
		list($width, $height) = array(1172, 922); // Taken from Default Voucher Layout
		$voucher_layout_id = 1;
		$sql = "select vlp.width, vlp.height, vlp.alt_width, vlp.alt_height
		from voucher_layout_parts vlp
		inner join voucher_layouts vl on vlp.voucher_layout_id = vl.id
		inner join campaigns c on vl.id = c.voucher_layout_id
		inner join items i on c.id = i.campaign_id
		where i.id = '$item_id'
		and vlp.type='image'";
		$row = BasicDataObject::getDataRow($sql);
		if(!empty($row['width']))
			$width = $row['width'];
		if(!empty($row['height']))
			$height = $row['height'];
		
		return array($width, $height);
	}
	
	public static function getItemImageUrl($item_id)
	{
		global $app_version;
		list($width, $height) = Item::getItemImageDimensions($item_id);
		$coupon_info = Item::getCouponInfo($item_id);
		$coupon = $coupon_info[0];
		// error_log('coupon_info in Item::getItemImageUrl(): ' . var_export($coupon_info, true));
		$image_url = '';
		switch($coupon['use_deal_voucher_image'])
		{
			case 'yes_own':
				if(!empty($coupon['image_file']))
					$image_url = "http://uploads.coupsmart.com/" . CacheImage::getImg($coupon['image_file'], $width, $height);
				break;
	
			case 'yes_fb_photo':
				if(!empty($coupon['facebook_page_id']))
					$image_url = "https://graph.facebook.com/v" . $app_version . "/".$coupon['facebook_page_id']."/picture?width=$width&height=$height";
				if(empty($coupon['facebook_page_id']))
					$image_url = $default_content;
					
				break;
	
			case 'yes_company_logo':
				if(!empty($coupon['default_coupon_image']))
					$image_url = "http://uploads.coupsmart.com/" . CacheImage::getImg($coupon['default_coupon_image'], $width, $height);
				if(empty($coupon['default_coupon_image']))
					$image_url = $default_content;
			
				
				break;
		
			default:
			
		}
		return $image_url;
	}
	
	public static function getCouponInfo($item_id){
		$sql = "select distinct i.id as item_id, i.manufacturer_id as 'comp_id', 
		ccc.users_id as 'customer_id', i.*, camp.logo_file_name, camp.logo_file_name as 'image_file', 
		com.display_name, com.default_coupon_image, com.location_finder_link, 
		camp.use_share_bonus as use_share_bonus, camp.is_featured, camp.use_deal_voucher_image, com.facebook_page_id, camp.likebar_content, camp.hide_share_button, camp.img_likebar, camp.add_likebar
		from items i
		left join companies com on com.id = i.manufacturer_id
		left join users_companies_campaigns ccc on i.manufacturer_id = ccc.companies_id
		left join users c on ccc.users_id = c.id
		left join campaigns camp on i.campaign_id = camp.id
		where i.id = '" . Database::mysqli_real_escape_string($item_id) . "'  and camp.status <> 'deleted' group by c.id";

		$result = Database::mysqli_query($sql);
		// error_log('COUPON INFO: ' . $sql);
		$item = array();

		while($row = Database::mysqli_fetch_assoc($result)) {
			$item[] = $row;
		}
		return $item;
	}
	
	public static function getLocationSpecificVouchers($item_id, $location = null)
	{
		$sql = "select * from location_specific_vouchers where item_id = '$item_id'";
		if(!empty($location))
			$sql .= " and lcase(location) = '$location'";
			
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function getItemViewsInfoByItemId($claimed_item_id)
	{
		$ip = Common::GetUserIp();
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$session_id = session_id();
		
		$sql = "select iv.id as items_views_id, iv.smart_link_id, iv.silver_pop_click_id, iv.referral_id, iv.session_id, iv.company_id, comp.is_silverpop_company, comp.is_et_company, comp.sp_is_ubx, comp.is_mailchimp_company, comp.mc_list_id, comp.mc_api_key, comp.is_campaign_monitor_company, comp.cm_client_id, comp.cm_api_key, d.cm_list_id, d.cm_list_name, i.deal_id 
		from items_views iv
		inner join companies comp on iv.company_id = comp.id 
		inner join items i on iv.items_id = i.id
		inner join deals d on i.deal_id = d.id
		where items_id='$claimed_item_id' and session_id='$session_id' and ip='$ip' and user_agent='$user_agent' 
		order by iv.id desc limit 1";
		// error_log('select sql in Item::getItemViewsInfoByItemId(): ' . $sql);
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function getItemViewsInfoByEmailCode($email_code)
	{
		$sql = "select iv.id as items_views_id, iv.smart_link_id, iv.silver_pop_click_id, iv.referral_id, iv.session_id, iv.company_id, comp.is_silverpop_company, comp.is_et_company, comp.sp_is_ubx, comp.is_mailchimp_company, comp.mc_list_id, comp.mc_api_key, comp.is_campaign_monitor_company, comp.cm_client_id, comp.cm_api_key, d.cm_list_id, d.cm_list_name, i.deal_id
		from items_views iv
		inner join companies comp on iv.company_id = comp.id 
		inner join items i on iv.items_id = i.id
		inner join deals d on i.deal_id = d.id
		where iv.instore_email_code = '" .Database::mysqli_real_escape_string($email_code). "'";
		// error_log('select sql in Item::getItemViewsInfoByEmailCode(): ' . $sql);
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function get_cashier_code($campaign_id, $is_regular_coupon = true)
	{
		$sql = "select count(ui.id) as committed from user_items ui inner join items i on ui.item_id = i.id where i.campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "' and i.status <> 'deleted' and ui.date_claimed is not null";
		// error_log("sql in Item::get_cashier_code(): ".$sql);
		
		$rs = Database::mysqli_query($sql);
		$cc_committed = 0;
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			$row = Database::mysqli_fetch_assoc($rs);
			$cc_committed = $row['committed'];
		}
		$cashier_code = $campaign_id . '-' . $cc_committed;
		Database::mysqli_free_result($rs);
		return $cashier_code;
	}
	
	
	public static function UpdateSGSPrintDate($sgs_recipient_id){
		// Updating last printed date
		$sql = "update sgs_order_recipients set status='printed', date_last_printed = now() where id = '".Database::mysqli_real_escape_string($sgs_recipient_id)."'";
		if(!Database::mysqli_query($sql)){
			error_log("Error updating date_last_printed in render-coupon! ".Database::mysqli_error());
			return false;
		}else{
			return true;
		}
	}
	
	public static function SendSGSPrintNotification($sgs_recipient_id){
		$sgs_order_info = Item::GetSGSOrderInfo($sgs_recipient_id);
		
		if($sgs_order_info['sent_print_notification'] != '1')
		{
			// Send email to buyer if not already sent
			$email_sent_successfully = Mailer::email_sgs_item_printed($sgs_order_info['username'], $sgs_order_info['email'], $sgs_order_info['date_claimed'], $sgs_order_info['company_name']);
			
			// Update sent_print_notification
			if($email_sent_successfully)
			{
				$sql = "update sgs_order_recipients set sent_print_notification = '1' where id = '".Database::mysqli_real_escape_string($sgs_recipient_id)."'";
				if(!Database::mysqli_query($sql))
					error_log("Error updating sent_print_notification in render-coupon.php ".Database::mysqli_error());
			}
		
		}
	}
	
	public static function getSavedDealsForLater($smart_deals_user_id)
	{
		// $sql = "select id as saved_for_later_id, item_id from items_saved_for_later where `user_fb_id` = '".Database::mysqli_real_escape_string($user_fb_id)."' and `printed` = '0'";
		$sql = "select id as saved_for_later_id, item_id, created, modified, smart_deals_user_id
				from items_saved_for_later 
				where `smart_deals_user_id` = '".Database::mysqli_real_escape_string($smart_deals_user_id)."' 
				and `printed` = '0'";
		
		// error_log("SQL in Item::getSavedDealsForLater(): ".$sql);
		$rows = BasicDataObject::getDataTable($sql);
		
		if(count($rows) > 0)
		{
			$days_passed = ( ( time() - strtotime($rows[0]['modified']) ) / (3600 * 24) );
			// error_log('time: ' . time() . ', modified: ' . strtotime($rows[0]['modified']) . ', days_passed: ' . $days_passed);
			
			// Updating cookie data in case 21 days have passed
			if( $days_passed > 21) // 3 weeks have passed since the cookie was last modified
			{
				$cookie_id = $rows[0]['smart_deals_user_id'];
				$expire_time = time() + 60 * 60 * 24 * 30; // Set expire time to a month
				setcookie("smart_deals_user_id", $cookie_id, $expire_time, "/");
				$update_sql = "update `items_saved_for_later` set `modified` = now() where `smart_deals_user_id` = '".$cookie_id."'";
				Database::mysqli_query($update_sql);
			}
		}
		
		return $rows;
	}
	
	public static function getSmartLinkClickInfo()
	{
		$ip = Common::GetUserIp();
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$session_id = session_id();
		$sql = "select * from smart_link_clicks where ip = '$ip' and user_agent = '$user_agent' and session_id = '$session_id' and viewed = 0 order by id desc limit 1";
		// error_log("sql in Item::getSmartLinkClickInfo(): " . $sql);
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function updateSmartLinkClickInfo($smart_link_click_id)
	{
		$sql = "update smart_link_clicks set viewed = '1' where id = '$smart_link_click_id'";
		if(!Database::mysqli_query($sql))
			error_log("Update SQL error: " . Database::mysqli_error() . "\nSQL: " . $sql);
	}
	
	public static function isSGSDiscount($item_id)
	{
		$sql = '
			select code
			from sgs_discounts
			where smart_deal_id = "' . $item_id . '";
		';
		$is_discount = BasicDataObject::getDataRow($sql);
		
		return empty($is_discount) ? false : $is_discount['code'];
		
	}
	
	public static function canUserClaimMagentoItem($user_id, $item_id)
	{
		$result = true;
		$sql = "select i.id, i.limit_per_person, count(ui.id) as num_prints_used
				from items i, user_items ui
				left join users u on u.id = ui.user_id
				where i.id = '" . Database::mysqli_real_escape_string($item_id) . "'
				and ui.`item_id` = '" . Database::mysqli_real_escape_string($item_id) . "'
				and ui.user_id = '" . Database::mysqli_real_escape_string($user_id) . "'
				and ui.has_hit_magento_website = '1'
				and u.status = 'active';
		";
		
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$limit_per_person = $row['limit_per_person'];
			$num_prints_used = $row['num_prints_used'];

			if( $num_prints_used  >= $limit_per_person) {
				$result = false;
			}
		}
		Database::mysqli_free_result($rs);
		return $result;
	}
}

?>