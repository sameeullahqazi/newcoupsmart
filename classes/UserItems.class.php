<?php

require_once(dirname(__DIR__) . '/includes/app_config.php');
require_once(dirname(__DIR__) . '/includes/UUID.php');

/*
require_once(dirname(__DIR__) . '/classes/Item.class.php');
require_once(dirname(__DIR__) . '/classes/UserActivityLog.class.php');
require_once(dirname(__DIR__) . '/classes/SilverPop.class.php');
require_once(dirname(__DIR__) . '/classes/MailChimp.class.php');
require_once(dirname(__DIR__) . '/classes/CampaignMonitor.class.php');
*/
/**
* UserFavoriteStores Class
*/
class UserItems
{

	var $id;
	var $company_id;
	var $user_id;
	var $item_id;
	var $uiid;
	var $date_committed;
	var $date_redeemed;
	var $date_sent;
	var $coupchecked;
	var $has_hit_magento_website;
	var $allow_reprint;
	var $reprint_code;
	var $reprint_url_sent;
	var $distribution_method;
	var $is_new_user_claim;
	

	function __construct($id = null)
	{
		if(!empty($id)){

			$sql = "SELECT * FROM user_items WHERE `id` = '$id'";
			$result = Database::mysqli_query($sql);
			if($result)
			{
				$row = Database::mysqli_fetch_assoc($result);
				$this->id = $row['id'];
				$this->user_id = $row['user_id'];
				$this->item_id = $row['item_id'];
				$this->uiid = $row['uiid'];
				$this->date_committed = $row['date_committed'];
				$this->date_redeemed = $row['date_redeemed'];
				$this->coupchecked = $row['coupchecked'];
				$this->date_sent = $row['date_sent'];
				$this->has_hit_magento_website = $row['has_hit_magento_website'];
			}
		}
	}
	
	

	public static function getFacebookCoupons($fbid, $zipcode = null, $page_id = null, $page_number = 1) {
		// return file_get_contents('../../../views/coupon-facebook.view.php');
		//global $api_url;
		//return file_get_contents( $api_url );
		return UserItems::getCouponsForPublicDisplay(null, null, array(1, 3), null, 6, $page_number);
	}

	public static function getUserItemsQueuedForPrinting($user_id)
	{
		$strUserItems =  "<table class=\"account-info\"><tbody>";
		$sql = "SELECT items.id, items.name
				FROM user_items user_items
				INNER JOIN items items on user_items.item_id = items.id
				WHERE user_id = '$user_id'
				AND items.status <> 'deleted'
				AND date_sent is NULL";//date_sent is NULL for the items queued for printing

		$result = Database::mysqli_query($sql);
		$count = 0;
		while($row = mysql_fetch_array($result))
		{
			$item_id = $row['id'];
			$item_name = $row['name'];
			$strUserItems .= "<tr id=\"" . $item_id . "\"><td>" . $item_name . "</td></tr>";
			$count++;
		}
		$strUserItems .= "</tbody></table>";
		if($count == 0)
			return "(No items queued for printing)";

		return $strUserItems;
	}

	public static function getUnfulfilledUserItems()
	{
		$result = array();
		$sql = "select ui.id, i.name, u.id, u.username, ui.date_committed, c.name as reason, cw.created as awarded_on
				from user_items ui
				inner join items i on ui.item_id = i.id
				inner join users u on ui.user_id = u.id
				left join contest_prizes cp ON cp.item_id = i.id
				left join contests c ON cp.contest_id = c.id
				left join contest_winners cw ON cw.claimed_user_item_id = ui.id
				where ui.date_sent is null and cw.date_sent is null
				and (i.controlled_printable_image is null or i.controlled_printable_image = '') and i.status <> 'deleted'";

		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
			$result[] = $row;

		return $result;
	}

	public static function fulfillUserItem($id)
	{
		$sql = "update user_items set
				date_sent = date_add(now(), interval 3 day),
				expected_delivery_date = date_add(now(), interval 11 day)
				where id = '$id'";

		Database::mysqli_query($sql);
	}

	public static function generateCSVFileContents($user_item_ids)
	{
		$file_name = ''.date('Y-m-d H-i-s').'.csv';
		$file_content = "";

		$sql = "select ui.id as user_item_id,
				i.id as item_id, i.name as item_name,
				prize.id as contest_prize_id, sent.id as items_sent_id,
				u.id as user_id, u.username, u.firstname, u.lastname, u.address1, u.address2, u.city, u.state, u.zip, u.email
				from user_items ui
				inner join items i on ui.item_id = i.id
				inner join users u on ui.user_id = u.id
				left join contest_prizes prize on ui.item_id = prize.item_id
				left join items_sent sent on ui.item_id = sent.item_id
				where ui.id in ($user_item_ids)";

		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// Generate header for first row
			if($file_content == '')
				$file_content .= implode(',', array_keys($row));
			else // And generate actual content for the rest
				$file_content .= implode(',', array_values($row));

			$file_content .= "\n";
		}

		return array($file_name, $file_content);
	}

	/*
	RETRIEVES COUPONS FOR PUBLIC DISPLAY
	-----------------------------------------
	1.	$national_or_local: NATIONAL => All national coupons which are not associated with any location, LOCAL => All local coupons with locations associated
	2.	$zip_code:			Optionally Used for local coupons (if $national_or_local = LOCAL)
	3.	$delivery_method:	The deliverable method for the coupon. Possible values: WEB, MOBILE, FACEBOOK and DELIVERABLE
	4.	$company_id:		Company coupons associated with that company
	5.	$page_info:			Comma delimeted page info, of the format, 'page_number,page_size')
	*/
	public static function getCouponsForPublicDisplay($location_id, $zip_code, $delivery_method, $company_id, $page_size = 12, $page_number = 1, $loc_zip_code = '', $loc_dma = '', $company_sdw_unique_codes_id = null) {
		$page_size		= 999;
		$result			= array();
		$where_clause	= "";
		$num_rows		= 0;
		$offset			= 0;
		$limit			= 0;
		$num_pages		= 0;
		
		// If no location is specified (i.e. national coupons)
		if(empty($location_id))
		{
			// If zip code is provided, then add the local coupons to the list
			if(!empty($zip_code))
				$where_clause .=  " and
							(
								i.manufacturer_id not in (select companies_id from companies_locations)
												or
								i.manufacturer_id in
								(
									select companies_locations.companies_id from companies_locations
									inner join locations on companies_locations.location_id = locations.id
									where locations.zip = '$zip_code'
								)
							)";
			else {// otherwise select coupons having no location
				//$where_clause .=  " and i.manufacturer_id not in (select companies_id from companies_locations)";
			}
		}
		else // if location is specified
		{
			$where_clause .= " and i.manufacturer_id in (select companies_id from companies_locations where location_id = '$location_id')";
		}

		// If delivery method is selected, then filter coupons by delivery method
		if(!empty($delivery_method))
		{
			if(strstr($delivery_method, ','))
			{
				$where_clause .= " and i.delivery_method in (".Database::mysqli_real_escape_string($delivery_method). ")";
				
				if(strstr($delivery_method, '9')){
					//magento coupon, magento extension must be running
					$where_clause .=" and (i.delivery_method <> '9' or comp.magento_running = '1')";
				}
			}
			else
			{
				$where_clause .= " and i.delivery_method = '".Database::mysqli_real_escape_string($delivery_method)."'";
				
				if($delivery_method == '9'){
					//magento coupon, magento extension must be running
					$where_clause .=" and comp.magento_running = '1'";
				}
			}
		}

		$str_faceboook_coupons = "";
		if(strstr($delivery_method, ',') && is_null($location_id)  && is_null($zip_code) )
			$str_faceboook_coupons = " join (select max(delivery_method) as delivery_method, campaign_id from items group by campaign_id) as i2 on (i2.delivery_method=i.delivery_method and i2.campaign_id = i.campaign_id) ";

		// And if company is specified
		if(!empty($company_id))
		{
			$where_clause .= " and i.manufacturer_id = '$company_id'";
		}
		
		$loc_inner_join = '';
		if(!empty($loc_zip_code))
		{
			$loc_inner_join = " inner join campaigns_locations cl on i.campaign_id = cl.campaigns_id inner join locations l on cl.locations_id = l.id";
			$where_clause .= " and ((l.zip = '$loc_zip_code' and cl.is_backup_deal = 0) or (l.zip != '$loc_zip_code' and cl.is_backup_deal = 1))";
		}
		
		if(!empty($loc_dma))
		{
			$loc_inner_join = " inner join campaigns_locations cl on i.campaign_id = cl.campaigns_id inner join locations l on cl.locations_id = l.id";
			
			// To be customized later
			// $where_clause .= " and ((lcase(l.city) = '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 0) or (lcase(l.city) != '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 1))";
			$where_clause .= " and ((lcase(l.state) = '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 0) or (lcase(l.state) != '" . strtolower($loc_dma) . "' and cl.is_backup_deal = 1))";
		}
		
		
		// For SDW
		if(!empty($company_sdw_unique_codes_id))
		{
			$where_clause .= " and i.company_sdw_unique_codes_id = '$company_sdw_unique_codes_id'";
		}



		//4.  Execute the Query now that all parameters are set!
		$sql = "select distinct i.*, i.delivery_method, i.static_fulfillment_html, c.logo_file_name as 'image_file', comp.id as 'comp_id', c.id as 'campaign_id',
				comp.display_name, comp.total_prints, comp.default_coupon_image, comp.location_finder_link, comp.facebook_page_id, comp.is_silverpop_company, comp.sp_list_id, comp.sp_contact_list_id, comp.sp_is_ubx, comp.is_et_company, comp.et_subscriber_list_id, comp.is_mailchimp_company, comp.mc_list_id, comp.mc_api_key, comp.is_campaign_monitor_company, comp.cm_client_id, comp.cm_api_key, 
				c.use_share_bonus, c.is_featured, c.featured_image, c.logo_preview_file_name as 'image_file_preview', c.img_sharing, c.use_deal_voucher_image, c.use_preview_deal_voucher_image, c.img_fan_deals, c.img_instore_deals,  i.show_print_options as show_print_options, c.hide_share_button, d.cm_list_id, d.cm_list_name 
				from items i
				inner join deals d on i.deal_id = d.id
				inner join campaigns c on i.campaign_id = c.id
				inner join companies comp on i.manufacturer_id = comp.id
				$loc_inner_join
				$str_faceboook_coupons
				where comp.status = 'active'
				 and c.status <> 'deleted'
				and comp.total_prints > 0
				and i.start_date <= NOW()
				and (i.end_date >= NOW() or i.end_date is null or i.end_date = '0000-00-00')
				AND (i.inventory_count > i.shipped or i.shipped is null)
				and (i.expires >= CURDATE() or i.expires is null or i.expires = '0000-00-00')
				and i.status = 'running' $where_clause order by i.sort_order, i.id";
		
		// error_log("SQL for displaying coupons in getCouponsForPublicDisplay(): \n".$sql);
		
		$rs = Database::mysqli_query($sql);
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while($row = Database::mysqli_fetch_assoc($rs)) {
				$result[] = $row;
			}
		}
		// error_log("results: " . var_export($result,true));
		return array($result, $num_rows, $offset, $limit, $num_pages);
	}

	// Get all National coupons
	public static function getNationalCoupons($zip_code, $num_items, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay(null, $zip_code, null, null, $num_items, $page_number);
	}

	// Get all Local coupons, sepcifiying zip code if needed
	public static function getCouponsByLocation($location_id, $zip_code, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay($location_id, $zip_code, null, null, $page_number);
	}

	// Get all national, web coupons
	public static function getNationalWebCoupons($zip_code, $num_items, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay(null, $zip_code, WEB, null, $num_items, $page_number);
	}

	// Get all national, mobile coupons
	public static function getNationalMobileCoupons($zip_code, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay(null, $zip_code, MOBILE, null, $num_items, $page_number);
	}

	// Get all national, facebook coupons
	public static function getNationalFacebookCoupons($zip_code, $page_number)
	{
		//error_log("getNationalFacebookCoupons()");
		return UserItems::getCouponsForPublicDisplay(null, $zip_code, FACEBOOK, null, $num_items, $page_number);
	}
	
	// Get all national, walkin coupons
	public static function getNationalBirthdayCoupons($zip_code, $page_number)
	{
		//error_log("getNationalFacebookCoupons()");
		return UserItems::getCouponsForPublicDisplay(null, $zip_code, BIRTHDAY, null, $num_items, $page_number);
	}
	
	// Get all national, walkin coupons
	public static function getNationalWalkinCoupons($zip_code, $page_number)
	{
		//error_log("getNationalFacebookCoupons()");
		return UserItems::getCouponsForPublicDisplay(null, $zip_code, WALKIN, null, $num_items, $page_number);
	}

	// Get all web coupons by location
	public static function getWebCouponsByLocation($location_id, $zip_code, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay($location_id, $zip_code, WEB, null, $page_number);
	}

	// Get all mobile coupons by location
	public static function getMobileCouponsByLocation($location_id, $zip_code, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay($location_id, $zip_code, MOBILE, null, $page_number);
	}

	// Get all facebook coupons by location
	public static function getFacebookCouponsByLocation($location_id, $zip_code, $page_number)
	{
		//error_log("getFacebookCouponsByLocation()");
		return UserItems::getCouponsForPublicDisplay($location_id, $zip_code, FACEBOOK, null, $page_number);
	}
	
	// Get all facebook coupons by location
	public static function getBirthdayCouponsByLocation($location_id, $zip_code, $page_number)
	{
		//error_log("getFacebookCouponsByLocation()");
		return UserItems::getCouponsForPublicDisplay($location_id, $zip_code, BIRTHDAY, null, $page_number);
	}
	
	// Get all facebook coupons by location
	public static function getWalkinCouponsByLocation($location_id, $zip_code, $page_number)
	{
		//error_log("getFacebookCouponsByLocation()");
		return UserItems::getCouponsForPublicDisplay($location_id, $zip_code, WALKIN, null, $page_number);
	}

	// Get all company coupons
	public static function getCompanyCoupons($company_id, $num_items, $page_number)
	{
		return UserItems::getCouponsForPublicDisplay(null, null, null, $company_id, $num_items, $page_number);
	}

	// Get all company coupons
	public static function getWebCompanyCoupons($company_id, $num_items, $page_number, $company_sdw_unique_codes_id = null, $loc_zip_code = '', $loc_dma = '')
	{
		return UserItems::getCouponsForPublicDisplay(null, null, WEB, $company_id, $num_items, $page_number, $loc_zip_code, $loc_dma, $company_sdw_unique_codes_id);
	}

	// Get all company coupons
	public static function getMobileCompanyCoupons($company_id, $num_items = 4, $page_number = 1)
	{
		return UserItems::getCouponsForPublicDisplay(null, null, MOBILE, $company_id, $num_items, $page_number);
	}

	// Get all company coupons
	public static function getFacebookCompanyCoupons($company_id, $num_items = 6, $page_number = 1, $loc_zip_code = '', $loc_dma = '')
	{
		//error_log("getFacebookCompanyCoupons(), num_items:".var_export($num_items, true));
		return UserItems::getCouponsForPublicDisplay(null, null, FACEBOOK, $company_id, $num_items, $page_number, $loc_zip_code, $loc_dma);
	}
	
	// Get all company coupons
	public static function getBirthdayCompanyCoupons($company_id, $num_items = 6, $page_number = 1)
	{
		//error_log("getFacebookCompanyCoupons(), num_items:".var_export($num_items, true));
		return UserItems::getCouponsForPublicDisplay(null, null, BIRTHDAY, $company_id, $num_items, $page_number);
	}
	
	// Get all company coupons
	public static function getWalkinCompanyCoupons($company_id, $num_items = 6, $page_number = 1)
	{
		//error_log("getFacebookCompanyCoupons(), num_items:".var_export($num_items, true));
		return UserItems::getCouponsForPublicDisplay(null, null, WALKIN, $company_id, $num_items, $page_number);
	}
	
	public static function get_item_id_by_device_id($device_id)
	{
		$item_id = null;
		
		$sql = "select i.id as item_id
			from coupcheck_devices d
			inner join items i on d.company_id = i.manufacturer_id
			where d.id = '".Database::mysqli_real_escape_string($device_id)."'
			and i.delivery_method='6' and i.status <> 'deleted'";
			
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$item_id = $row['item_id'];
		}
		return $item_id;
	}
	
	public static function get_sgs_item_id_by_device_id($device_id)
	{
		$item_id = null;
		
		$sql = "select i.id as sgs_item_id
			from coupcheck_devices d
			inner join sgs_items i on d.company_id = i.company_id
			where d.id = '".Database::mysqli_real_escape_string($device_id)."'";
			
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$item_id = $row['sgs_item_id'];
		}
		return $item_id;
	}
	
	public static function get_walkin_item_id_by_location_id($location_id)
	{
		$item_id = null;
		
		$sql = "select i.id as item_id from locations l
					inner join items i on l.companies_id = i.manufacturer_id
					where l.id = '".Database::mysqli_real_escape_string($location_id)."'
					and i.delivery_method in (" . MOBILE . ") and i.status = 'running'
					order by i.id desc";
		//error_log($sql);
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$item_id = $row['item_id'];
		}
		return $item_id;
	}
	
	public static function get_uiid_by_id($user_item_id)
	{
		$uiid = null;
		
		$sql = "select uiid from user_items where id = '".Database::mysqli_real_escape_string($user_item_id)."'";
			
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$uiid = $row['uiid'];
		}
		return $uiid;
	}
	
	public function get_user_item_id_by_uiid($uiid)
	{
		$sql = '
			select id
			from user_items
			where uiid = ' . $uiid . ';
		';
		
		$row = BasicDataObject::getDataRow($sql);
		
		return $row['id'];
	}
	
	public static function get_user_id_by_id($user_item_id)
	{
		$user_id = null;
		
		$sql = "select user_id from user_items where id = '".Database::mysqli_real_escape_string($user_item_id)."'";
			
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$user_id = $row['user_id'];
		}
		return $user_id;
	}
	
	/*
	Called when a barcode is scanned either from temp-barcode-location or walk-in-complete
	 *	returns -1 if the barcode doesn't exist
	 *	returns 0 if coupchecked is 1
	 *	returns 1 if coupchecked is 0
	*/
	public static function check_and_set_unused_items($barcode, $device_id)
	{
		$result = 2; // Any positive single digit integer other than 0 or 1 can be used
		
		// Get location against the device
		$device_info = Coupcheck::getDeviceInfo($device_id);
		$location_id = $device_info['location_id'];
		
		// Check if item_id and user_id are equal to zero
		//$sql = "select id, item_id, user_id from user_items where uiid = '".Database::mysqli_real_escape_string($barcode)."'";
		$sql = "select id, item_id, user_id, null as is_sgs, null as status from user_items where uiid = '".Database::mysqli_real_escape_string($barcode)."'
				union all
				select id, null, null, 1 as is_sgs, status from sgs_order_recipients where sgs_uiid = '".Database::mysqli_real_escape_string($barcode)."'";
				
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			$is_sgs = $row['is_sgs'];
			
			if(empty($is_sgs))
			{
				// Regular Coupcheck process //
				if( (empty($row['item_id']) || $row['item_id'] == '0') && (empty($row['user_id']) || $row['user_id'] == '0') )
				{
					$user_item_id = $row['id'];
				
					// Get Item Id against device_id
					$item_id = UserItems::get_item_id_by_device_id($device_id);
				
					// Set Item Id and Walkin status in the user_items table
					$sql = "update user_items set item_id = '".$item_id."', walk_in_status = '0', walkin_device_id = '$device_id', walkin_location_id = '$location_id' where id='".Database::mysqli_real_escape_string($user_item_id)."'";
					Database::mysqli_query($sql);
				
					$result = 3;
				}
				else // Location based Coupcheck process //
				{
					$sql = "select ui.id as user_item_id, i.id as item_id, i.limit_per_person, ui.num_scans, ui.coupchecked, i.upc, ui.uiid as uiid,
						cd.company_id, cd.customer_id, comp.status as company_status, ui.user_id
						from user_items ui
						inner join items i on ui.item_id = i.id
						inner join companies comp on i.manufacturer_id = comp.id
						inner join coupcheck_devices cd on i.manufacturer_id = cd.company_id
						where
						ui.uiid = '".Database::mysqli_real_escape_string($barcode)."' AND
						cd.id = '".Database::mysqli_real_escape_string($device_id)."' AND
						comp.status = 'active' AND
						i.status <> 'deleted'";

					// error_log('sql in function check_and_set_unused_items() = ' . $sql);
					$rs = Database::mysqli_query($sql);
					// error_log('num_rows = ' . Database::mysqli_num_rows($rs));
					if($rs && Database::mysqli_num_rows($rs) > 0)
					{
						$row = Database::mysqli_fetch_assoc($rs);

						$coupchecked = $row['coupchecked'];
						$limit_per_person = $row['limit_per_person'];
						$num_scans = $row['num_scans'];
						$item_id = $row['item_id'];
						$user_item_id = $row['user_item_id'];
						$user_id = $row['user_id'];
					
						// Update the walkin-device-id here
						$sql = "update user_items set walkin_device_id = '$device_id', walkin_location_id = '$location_id' where id='". Database::mysqli_real_escape_string($user_item_id) . "'";
						if(!Database::mysqli_query($sql))
							error_log('mysql error ' . Database::mysqli_errno() . ' on user_items update: ' . Database::mysqli_error() . ' ---- Query was: ' . $sql);
					
						/*
						if(Item::validateCouponRedemption($user_item_id))
						{
							if($coupchecked == '1')
							{
								$result = 0;
							}
							else
							{
								$num_scans++;
								$sql = "update user_items set num_scans = '$num_scans'";
				
								if($num_scans >= $limit_per_person)
									$sql .= ", coupchecked = '1'";

								$sql .= " where id = '$user_item_id'";
								Database::mysqli_query($sql);

								$result = 1;
							}
						}*/
						if(Item::validateCouponRedemption($user_item_id))
						{
							if(UserItems::redeemCoupon($user_item_id))
							{
								$result = 1;
								
								// Log Redeemed Activity
								if(!empty($user_id))
								{
									// UserActivityLog::log_user_activity($user_id, 'redeemed', 'convercial', $item_id);
								}
							}
							else
							{
								$result = 0;
							}
						}
						else
						{
							$result = 0;
						}
					}
				}
			}
			else	// In case of an SGS item
			{
				$sgs_order_recipient_id = $row['id'];
				$status = $row['status'];
				// if the status is not already redeemed
				if($status != 'redeemed')
				{
					$result = UserItems::redeemSGSItem($sgs_order_recipient_id) ? 1 : 0;
					// Log Redeemed Activity
					if(!empty($user_id))
					{
						// UserActivityLog::log_user_activity($user_id, 'redeemed', 'convercial,sgs', $item_id);
					}
				}
				else
				{
					$result = 0;
				}
			}
		}
		else
		{
		
		}

		
		return $result;
	}
	
	public static function redeemCoupon($user_item_id)
	{
		$num_rows_affected = -1;
		$result = false;
		
		$sql = "select ui.coupchecked, ui.num_scans, i.limit_per_person, i.manufacturer_id, i.delivery_method, ui.user_id, ui.item_id, ui.uiid, ui.silver_pop_click_id, i.campaign_id, i.campaign_name, i.name as item_name, c.is_silverpop_company, c.is_et_company, c.sp_is_ubx, c.is_mailchimp_company, c.mc_list_id, c.mc_api_key, c.is_campaign_monitor_company, c.cm_client_id, c.cm_api_key, d.cm_list_id, d.cm_list_name, i.deal_id 
		from user_items ui 
		inner join items i on ui.item_id = i.id 
		inner join deals d on i.deal_id = d.id
		inner join companies c on i.manufacturer_id = c.id 
		where ui.id = '".Database::mysqli_real_escape_string($user_item_id)."'";
		// error_log("sql in redeemCoupon(): ".$sql);
		
		$rs = Database::mysqli_query($sql);
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			$row = Database::mysqli_fetch_assoc($rs);
			
			$coupchecked = $row['coupchecked'];
			$limit_per_person = $row['limit_per_person'];
			$num_scans = $row['num_scans'];
			$company_id = Database::mysqli_real_escape_string($row['manufacturer_id']);
	
			if($coupchecked != '1')
			{
				// By Samee: Commenting out this check for now. Do we really need it?
				// if($num_scans >= $limit_per_person)
				{
					$channel = Common::getUBXChannelType($row['delivery_method']);
					
					$sql = "update user_items set coupchecked = '1', date_redeemed=now() where id = '$user_item_id'";
					Database::mysqli_query($sql);
					$num_rows_affected = Database::mysqli_affected_rows();
					// error_log("update query runs: " . $num_rows_affected);
					
					// Update cache_resellers_stats with the new redeemed value
					$sql = "update cache_resellers_stats set redeemed = ifnull(redeemed, 0) + 1 where companies_id = '$company_id'";
					Database::mysqli_query($sql);
					
					$result = true;
					
					// If user redeemed this coupon via clicking a Silverpop Email link
					if(!empty($row['silver_pop_click_id'])) // && session_id() == $items_views_info['session_id'])
					{
						// Trigger Silver Pop Redeemed Event
						$silver_pop_click_info = SilverPop::getSilverPopInfo($row['silver_pop_click_id']);
						
						// Check if this is a Silverpop Company
						if(!empty($row['is_silverpop_company']))
						{
							if(empty($row['sp_is_ubx']))
							{
								// Check for user email and Upsert the contact here
								$sp_recipient_id = SilverPop::checkAndUpsertContact($row['user_id'], $company_id);
								// And if the Upsert was successful, POST a UB Event
								$tmp_sp_recipient_id = !empty($sp_recipient_id) ? $sp_recipient_id : $silver_pop_click_info['sp_contact_id_unencoded'];
							
								SilverPop::triggerRedeemedOffer($company_id, $tmp_sp_recipient_id, $row['user_id'], $row['campaign_id'], $row['campaign_name'], $row['item_name'], $user_item_id, $silver_pop_click_info['sp_mailing_id'], $silver_pop_click_info['sp_mailing_name']);
							}
							else
							{
								//	TODOS: Call UBX version of the Event here
								UBX::triggerRedeemedOffer($company_id, $channel, $row['user_id'], $row['campaign_id'], $row['campaign_name'], $row['item_name'], $user_item_id, $silver_pop_click_info['sp_mailing_id'], $silver_pop_click_info['sp_mailing_name']);
							}
							
						}
						
						if($row['is_et_company'] == '1')
						{
							ExactTarget::checkAndUpsertSubscriber($row['user_id'], $company_id, array('action' => 'redeem', 'item_id' => $row['item_id'], 'uiid' => $row['uiid'], 'item_name' => $row['item_name']));
						}
						
						if($row['is_mailchimp_company'] == '1' && !empty($row['mc_list_id']))
							MailChimp::checkAndUpsertMember($row['user_id'], $row['mc_list_id'], $row['mc_api_key'], $company_id);
					
						if($row['is_campaign_monitor_company'] == '1' && !empty($row['cm_list_id']))
							CampaignMonitor::checkAndUpsertMember($user_id, $row['cm_list_id'], $row['cm_client_id'], $row['cm_api_key'], $row['deal_id'], $company_id);
		
					}
					else // If user redeemed this coupon on their own
					{
						// Check if this is a Silverpop Company
						if(!empty($row['is_silverpop_company']))
						{
							if(empty($row['sp_is_ubx']))
							{
								// Check for user email and Upsert the contact here
								$sp_recipient_id = SilverPop::checkAndUpsertContact($row['user_id'], $company_id);
								// And if the Upsert was successful, POST a UB Event
							
							
								if(!empty($sp_recipient_id))
									SilverPop::triggerRedeemedOffer($company_id, $sp_recipient_id, $row['user_id'], $row['campaign_id'], $row['campaign_name'], $row['item_name'], $user_item_id, null, null);
							}
							else
							{
								//	TODOS: Call UBX version of the Event here
								UBX::triggerRedeemedOffer($company_id, $channel, $row['user_id'], $row['campaign_id'], $row['campaign_name'], $row['item_name'], $user_item_id, null, null);
							}
						}
						
						if($row['is_et_company'] == '1')
							ExactTarget::checkAndUpsertSubscriber($row['user_id'], $company_id, array('action' => 'redeem', 'item_id' => $row['item_id'], 'uiid' => $row['uiid'], 'item_name' => $row['item_name']));
						
						if($row['is_mailchimp_company'] == '1' && !empty($row['mc_list_id']))
							MailChimp::checkAndUpsertMember($row['user_id'], $row['mc_list_id'], $row['mc_api_key'], $company_id);
							
						if($row['is_campaign_monitor_company'] == '1' && !empty($row['cm_list_id']))
							CampaignMonitor::checkAndUpsertMember($row['user_id'], $row['cm_list_id'], $row['cm_client_id'], $row['cm_api_key'], $row['deal_id'], $company_id);
					}
				}
				
			}
		}
		// error_log("num_rows_affected : ".$num_rows_affected);
		return $result;
	}
	
	public static function redeemSGSItem($sgs_order_recipient_id)
	{
		$sql = "update sgs_order_recipients set status='redeemed', date_redeemed=now() where id='".Database::mysqli_real_escape_string($sgs_order_recipient_id)."'";
		if(!Database::mysqli_query($sql))
		{
			error_log("SQL error in function UserItems::redeemSGSItem(): ".Database::mysqli_error().", SQL: ".$sql);
			return false;
		}
		return true;
	}


	/*
	public static function getNationalCoupons(){
		$result = array();
		$sql = "select distinct i.id, i.expires, comp.display_name, i.name, i.description, i.details, i.offer_value, i.retail_price, i.controlled_printable_image, cu.total_prints from customers_companies_campaigns ccc
			inner join companies comp on ccc.companies_id = comp.id
			inner join customers cu on ccc.customers_id = cu.id
			inner join campaigns c on ccc.campaigns_id = c.id
			inner join items i on c.id = i.campaign_id
			where comp.status = 'active'
			and cu.total_prints > 0
			and i.start_date <= CURDATE()
			and i.end_date >= CURDATE()
			AND (i.inventory_count > i.shipped or i.shipped is null)
			and (i.expires >= CURDATE() or i.expires is null)
			and i.status = 'running'";

		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
			$result[] = $row;

		return $result;

	}

	public static function getNationalCouponsByMethod($delivery_method){
		$result = array();
		$sql = "select distinct i.id, comp.display_name, i.offer_value, i.retail_price, i.controlled_printable_image, cu.total_prints from customers_companies_campaigns ccc
			inner join companies comp on ccc.companies_id = comp.id
			inner join customers cu on ccc.customers_id = cu.id
			inner join campaigns c on ccc.campaigns_id = c.id
			inner join items i on c.id = i.campaign_id
			where comp.status = 'active'
			and cu.total_prints > 0
			and i.start_date <= CURDATE()
			and i.end_date >= CURDATE()
			AND (i.inventory_count > i.shipped or i.shipped is null)
			and (i.expires >= CURDATE() or i.expires is null)
			and i.delivery_method = $delivery_method";

		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
			$result[] = $row;

		return $result;

	}
	*/
	
		
	public static function create_new_user_item()
	{
		$sql = "insert into user_items (id) values (NULL)";
		if(!Database::mysqli_query($sql))
		{
			error_log("Error creating new user item: ".Database::mysqli_error());
			return 0;
		}
		return Database::mysqli_insert_id();
	}
	
	public function update_user_id($id, $user_id, $sgs_uiid = null)
	{
		if(empty($sgs_uiid))
			$sql = "update user_items set user_id = '".Database::mysqli_real_escape_string($user_id)."', walk_in_status='1' where id = '".Database::mysqli_real_escape_string($id)."'";
		else
			$sql = "update sgs_order_recipients set walkin_user_id = '".Database::mysqli_real_escape_string($user_id)."', walk_in_status='1' where id = '".Database::mysqli_real_escape_string($id)."'";
		// error_log("update sql in update_user_id(): ".$sql);
		if(!Database::mysqli_query($sql))
		{
			error_log("Error updating user_id in the user_items table: ".Database::mysqli_error());
			return 0;
		}
		return Database::mysqli_affected_rows();
	}
	
	public function update_item_id($user_item_id, $item_id)
	{
		$sql = "update user_items set item_id = '".Database::mysqli_real_escape_string($item_id)."' where id = '".Database::mysqli_real_escape_string($user_item_id)."'";
		if(!Database::mysqli_query($sql))
		{
			error_log("Error updating item_id in the user_items table: ".Database::mysqli_error());
			return 0;
		}
		return Database::mysqli_affected_rows();
	}
	
	public static function walkin_user_item_info($item_id)
	{
		$sql = "select
			i.id as item_id,
			i.name,
			i.description,
			i.small_type,
			i.offer_code,
			i.offer_value,
			i.social_offer_code,
			i.social_offer_value,
			i.new_fan_offer,
			i.platform_social_offer_service_name,
			i.platform_social_offer_value,
			i.platform_social_offer_small_type,
			i.white_label_css_2,
			i.white_label_css_3,
			i.white_label_css_4,
			c.value as coupon_code_value,
			sc.value as social_coupon_code_value,
			camp.use_share_bonus,
			camp.convercial_type,
			comp.instore_background_img,
			i.campaign_id,
			camp.img_instore_deals
		from items i
			inner join campaigns camp
				on i.campaign_id = camp.id
			inner join companies comp
				on i.manufacturer_id = comp.id
			left join coupon_code_values c
				on i.offer_code = c.code
		left join coupon_code_values sc
			on i.social_offer_code = sc.code
		where i.id = '" . Database::mysqli_real_escape_string($item_id) . "'";
		// error_log("sql in UserItem::walkin_user_item_info(): ".$sql);
		$rs = Database::mysqli_query($sql);
		
		if($rs && Database::mysqli_num_rows($rs) > 0)
		{
			if($row = Database::mysqli_fetch_assoc($rs))
			{
				return $row;
			}
		}
		return null;
	}
	
	public function update_new_fan_first_flag($val, $user_item_id)
	{
		$sql = "update user_items set new_fan_first_flag = '$val' where id = '".Database::mysqli_real_escape_string($user_item_id)."'";
		Database::mysqli_query($sql);
	}
	
	public function is_new_fan_first($user_id, $company_id)
	{
		$sql = "select ui.id from user_items ui inner join items i ON i.id=ui.item_id where ui.user_id = '" . Database::mysqli_real_escape_string($user_id) . "' AND i.manufacturer_id='" . Database::mysqli_real_escape_string($company_id) . "' AND ui.new_fan_first_flag=1 and i.status <> 'deleted'";
		// error_log("sql in is_new_fan_first(): ".$sql);
		$rs = Database::mysqli_query($sql);
		if ($rs && Database::mysqli_num_rows($rs) > 0)
			return false;
		else
			return true;
	}
	
	public function getRedeemedItemsCount($company_id) {
		$sql = "select shipped, campaign_id from items where manufacturer_id = '" . Database::mysqli_real_escape_string($company_id) . "' order by campaign_id";
		$rs = Database::mysqli_query($sql);
		$theCount = 0;
		$alreadyCountedCampaigns = array();
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while ($row = Database::mysqli_fetch_assoc($rs)) {
				if (!isset($alreadyCountedCampaigns[$row['campaign_id']])) {
					$theCount += $row['shipped'];
					$alreadyCountedCampaigns[$row['campaign_id']] = 1;
				}
			}
		}
		return $theCount;
	}
	
	public static function add_user_wallet_item($user_id, $item_id)
	{
		$user_id = Database::mysqli_real_escape_string($user_id);
		$item_id = Database::mysqli_real_escape_string($item_id);
		
		$sql = "insert into user_wallet_items (user_id, item_id, created) values ('$user_id', '$item_id', now())";
		if(!Database::mysqli_query($sql))
		{
			error_log("Error adding new user wallet item: ".Database::mysqli_error());
		}
	}
	
	public static function generate_unique_uiid()
	{
		$uiid = '';
		$num_rows = 0;
		
		do
		{
			$uiid = sprintf("%011d", rand(0, 99999999999));
			// Never start our codes with 5 or 99, too easy to confuse POS systems
			while (substr($uiid, 0, 1) == '5' || substr($uiid, 0, 2) == '99') {
				$uiid = sprintf("%011d", rand(0, 99999999999));
			}
			$sql = "select uiid as uiid from user_items where uiid = '$uiid' union all select sgs_uiid as uiid from sgs_order_recipients where sgs_uiid = '$uiid'";
			// $timer_start = array_sum(explode(" ", microtime()));
			$rs = Database::mysqli_query($sql);
			// $timer_end = array_sum(explode(" ", microtime()));
			// error_log("QUERY in " . __LINE__ .  " " . __FILE__ ." : " . ($timer_end-$timer_start));

			$num_rows = Database::mysqli_num_rows($rs);
		}
		while($num_rows > 0);
		return $uiid;
	}
	
	public static function insert_unique_uiid($uiid, $item_id, $user_id, $is_regular_coupon, $distributor_id, $is_click_referral, $view_code = null, $items_views_id = null, $company_id = null)
	{
		$company_id = !empty($company_id) ? "'" . $company_id . "'" : 'NULL';
		$is_new_user_claim = !empty($_SESSION['new_user_claim']) ? '1' : '0';
		unset($_SESSION['new_user_claim']);
		
		// $start_time = time();
		
		if(empty($view_code))
			$items_views_info = Item::getItemViewsInfoByItemId($item_id);
		else
			$items_views_info = Item::getItemViewsInfoByEmailCode(urldecode($view_code));
		// error_log("items_views_info in UserItems::insert_unique_uiid() for uiid $uiid item_id $item_id with user_id $user_id with view_code $view_code" . var_export($items_views_info, true));
		// error_log("time taken to get views info in UserItems::insert_unique_uiid(): " . (time() - $start_time));
		if(empty($items_views_info))
		{
			Common::log_error(__FILE__, __LINE__, "items_views_info empty!",
				json_encode(array(
					'items_views_info' => $items_views_info,
				)), "$user_id, item_id: $item_id, uiid: $uiid");
		}
		
		$smart_link_id = !empty($items_views_info['smart_link_id']) ? "'" . $items_views_info['smart_link_id'] . "'" : 'NULL';
		
		// items_views_id would be passed to this function only in the case when the coupon is claimed via Mobile Offers Email
		if(empty($items_views_id))
			$items_views_id = !empty($items_views_info['items_views_id']) ? "'" . $items_views_info['items_views_id'] . "'" : 'NULL';
		else
			$items_views_id = "'" . $items_views_id . "'";
		
		$silver_pop_click_id = !empty($items_views_info['silver_pop_click_id']) ? "'" . $items_views_info['silver_pop_click_id'] . "'" : 'NULL';
		$referral_id = !empty($items_views_info['referral_id']) ? "'" . $items_views_info['referral_id'] . "'" : 'NULL';

		do
		{
			$uiid = sprintf("%011d", rand(0, 99999999999));
			// Never start our codes with 5 or 99, too easy to confuse POS systems
			while (substr($uiid, 0, 1) == '5' || substr($uiid, 0, 2) == '99') {
				$uiid = sprintf("%011d", rand(0, 99999999999));
			}
			$insert = "insert into user_items (company_id, item_id, user_id, uiid, is_regular_coupon, date_committed, delivery_center_arrival, date_sent, expected_delivery_date, date_claimed, distributors_id, is_click_referral, items_views_id, smart_link_id, silver_pop_click_id, referral_id, is_new_user_claim) VALUES ($company_id, '" . Database::mysqli_real_escape_string($item_id) . "', '" . Database::mysqli_real_escape_string($user_id) . "', '" . Database::mysqli_real_escape_string($uiid) . "', '".Database::mysqli_real_escape_string($is_regular_coupon)."', NOW(), NOW(), NOW(), NOW(), NOW(), " . $distributor_id . ", '$is_click_referral', $items_views_id, $smart_link_id, $silver_pop_click_id, $referral_id, $is_new_user_claim)";
			Database::mysqli_query($insert);
			
			if (Database::mysqli_error())
				error_log('UserItems::insert_unique_uiid(): mysql error ' . Database::mysqli_errno() . ' on user_items insert: ' . Database::mysqli_error() . ' ---- Query was: ' . $insert);
			// error_log('mysql errno: ' . Database::mysqli_errno());
		}
		while(Database::mysqli_errno() == 1062);
		$insert_id = Database::mysqli_insert_id();
		
		// If User is claiming a coupon via clicking the Silverpop Email link
		if(!empty($items_views_info['silver_pop_click_id'])) // && session_id() == $items_views_info['session_id'])
		{
			$silver_pop_click_info = SilverPop::getSilverPopInfo($items_views_info['silver_pop_click_id']);
			// $recipient_id = $silver_pop_click_info['sp_contact_id_unencoded'];
			
			// Check if this is a Silverpop Company
			if(!empty($items_views_info['is_silverpop_company']))
			{
				if(empty($items_views_info['sp_is_ubx']))
				{
					// Check for user email and Upsert the contact here
					$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $items_views_info['company_id']);
					// And if the Upsert was successful, POST a UB Event
					$tmp_sp_recipient_id = !empty($sp_recipient_id) ? $sp_recipient_id : $silver_pop_click_info['sp_contact_id_unencoded'];
				
				
					SilverPop::triggerClaimedOffer($items_views_info['company_id'], $tmp_sp_recipient_id, $user_id, $item_id, $insert_id, $silver_pop_click_info['sp_mailing_id'], $silver_pop_click_info['sp_mailing_name']);
				}
				else
				{
					//	TODOS: Call UBX version of the Event here
					UBX::triggerClaimedOffer($items_views_info['company_id'], $tmp_sp_recipient_id, $user_id, $item_id, $insert_id, $silver_pop_click_info['sp_mailing_id'], $silver_pop_click_info['sp_mailing_name']);
				}
			}
			
			if($items_views_info['is_et_company'] == '1')
				ExactTarget::checkAndUpsertSubscriber($user_id, $items_views_info['company_id'], array('action' => 'claim', 'item_id' => $item_id, 'uiid' => $uiid));
			
			if($items_views_info['is_mailchimp_company'] == '1' && !empty($items_views_info['mc_list_id']))
				MailChimp::checkAndUpsertMember($user_id, $items_views_info['mc_list_id'], $items_views_info['mc_api_key'], $items_views_info['company_id']);
			
			if($items_views_info['is_campaign_monitor_company'] == '1' && !empty($items_views_info['cm_list_id']))
				CampaignMonitor::checkAndUpsertMember($user_id, $items_views_info['cm_list_id'], $items_views_info['cm_client_id'], $items_views_info['cm_api_key'], $items_views_info['deal_id'], $items_views_info['company_id']);
		}
		else // If User is claiming a coupon on their own; i.e. not via clicking the Silverpop email link
		{
			// Check if this is a Silverpop Company
			// if(!empty($items_views_info['is_silverpop_company']))
			$is_sp_info = SilverPop::getIsSPCompanyInfoByItemId($item_id);
			if(empty($is_sp_info))
			{
				Common::log_error(__FILE__, __LINE__, "is_sp_info empty!",
					json_encode(array(
						'is_sp_info' => $is_sp_info,
					)), "$user_id, item_id: $item_id, uiid: $uiid");
			}
			if(!empty($is_sp_info['is_silverpop_company']))
			{
				if(empty($is_sp_info['sp_is_ubx']))
				{
					// Check for user email and Upsert the contact here
					// $sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $items_views_info['company_id']);
					$sp_recipient_id = SilverPop::checkAndUpsertContact($user_id, $is_sp_info['company_id']);
					// And if the Upsert was successful, POST a UB Event
				
					if(!empty($sp_recipient_id))
					{
						SilverPop::triggerClaimedOffer($is_sp_info['company_id'], $sp_recipient_id, $user_id, $item_id, $insert_id, null, null);
					}
				}
				else
				{
					//	TODOS: Call UBX version of the Event here
					UBX::triggerClaimedOffer($is_sp_info['company_id'], $sp_recipient_id, $user_id, $item_id, $insert_id, null, null);
				}
				
			}
			
			if($is_sp_info['is_et_company'] == '1')
				ExactTarget::checkAndUpsertSubscriber($user_id, $is_sp_info['company_id'], array('action' => 'claim', 'item_id' => $item_id, 'uiid' => $uiid));
			
			if($is_sp_info['is_mailchimp_company'] == '1' && !empty($is_sp_info['mc_list_id']))
				MailChimp::checkAndUpsertMember($user_id, $is_sp_info['mc_list_id'], $is_sp_info['mc_api_key'], $is_sp_info['company_id']);
			
			if($is_sp_info['is_campaign_monitor_company'] == '1' && !empty($is_sp_info['cm_list_id']))
				CampaignMonitor::checkAndUpsertMember($user_id, $is_sp_info['cm_list_id'], $is_sp_info['cm_client_id'], $is_sp_info['cm_api_key'], $is_sp_info['deal_id'], $is_sp_info['company_id']);
		}
		return $uiid;
	}
	
	public static function getUserItem($item_id, $user_id){
		$sql = "select id from user_items where item_id = " . Database::mysqli_real_escape_string($item_id)
				. " and user_id = " . Database::mysqli_real_escape_string($user_id) . " order by id desc, date_redeemed desc limit 1";
		$result = Database::mysqli_query($sql);
		
		if($result && Database::mysqli_num_rows($result) > 0){
			$row = Database::mysqli_fetch_assoc($result);
		}
		
		return $row['id'];
	}
	
	public static function getUserIdByUserItemId($uiid) {
		$sql = 'select user_id from user_items where id = "' . Database::mysqli_real_escape_string($uiid) . '"';
		$user_id = null;
		$rs = Database::mysqli_query($sql);
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			$row = Database::mysqli_fetch_assoc($rs);
			$user_id = $row['user_id'];
		}
		return $user_id;
	}
	
	public static function getUserItemCount($user_id)
	{
		$sql = "SELECT COUNT(id) AS total_items FROM user_items WHERE user_id='" . $user_id . "'";
		$items_result = Database::mysqli_query($sql);
		$total_items = 0;
		if($items_result)
		{
			$row = Database::mysqli_fetch_assoc($items_result);
			$total_items = $row['total_items'];
		}
		return $total_items;
	}
	
	public static function claimItem($item_id, $user_id, $items_views_id = null, $company_id = null) {
		$company_id = !empty($company_id) ? "'" . $company_id . "'" : 'NULL';
		$is_new_user_claim = !empty($_SESSION['new_user_claim']) ? '1' : '0';
		unset($_SESSION['new_user_claim']);
		if (Item::canUserPrintItem($user_id, $item_id)) {
			// claim item
			$items_views_id_val = !empty($items_views_id) ? "'" . $items_views_id . "'"  : 'NULL';
			$insert = "insert into user_items (company_id, item_id, user_id, date_committed, delivery_center_arrival, date_sent, expected_delivery_date, date_claimed, items_views_id, is_new_user_claim) VALUES ($company_id, '" . Database::mysqli_real_escape_string($item_id) . "', '" . Database::mysqli_real_escape_string($user_id) . "', NOW(), NOW(), NOW(), NOW(), NOW(), $items_views_id_val, $is_new_user_claim)";
			
			if(!Database::mysqli_query($insert))
			{
				error_log("SQL Insert error: " . Database::mysqli_error() . "\nSQL: ".$insert);
			}
			else
			{
				$last_insert_id = Database::mysqli_insert_id();
				// Log this activity in the user_activity_log table if the insert operation was successful
				// UserActivityLog::log_user_activity($user_id, 'claimed', 'fan_deals', $item_id);
				
				/*
				$item = new Item($item_id);
				$sql = "update items set committed = ifnull(committed, 0) + 1 ";
				if($item->committed >= ($item->inventory_count - 1))
					$sql .= ", status = 'finished' ";
				$sql .= "WHERE id = '" . Database::mysqli_real_escape_string($item_id) . "'";
				if(!Database::mysqli_query($sql))
					error_log("Error updaing shipped, committed and social_print_count in UserItems::claimItem() ".Database::mysqli_error());
				*/
				Item::checkAndUpdateOutOfStockItems($item_id);
				
			}
			
			return $last_insert_id;
		} else {
			// claim denied
			return false;
		}
	}
	
	public static function updateUserItemMagentoHit($user_item) {
		$sql = '
			Update user_items
			Set has_hit_magento_website = "1"
			Where id = ' . $user_item->id . ';
		';
		
		Database::mysqli_query($sql);
		
	}
	
	public static function getExistingUIIDs($user_id, $item_id)
	{
		$sql = "select uiid from user_items where user_id = '".Database::mysqli_real_escape_string($user_id)."' and item_id = '".Database::mysqli_real_escape_string($item_id)."' and uiid is not null and uiid != '' order by id desc ";
		// error_log('uiid sql: ' . $sql);
		$row = BasicDataObject::getDataRow($sql);
		$uiids = "";
		if(isset($row['uiid']))
			$uiids = $row['uiid'];
		
		// error_log('ui ids: ' . var_export($uiids, true));
		return $uiids;
	}
	
	public static function getNumTimesReprinted($existing_uiids)
	{
		$arr_existing_uiids = explode(',', $existing_uiids);
		$uiid = end($arr_existing_uiids);
		$sql = "select reprinted from user_items where uiid = '$uiid'";
		// error_log('SQL in UserItems::getNumTimesReprinted(): '.$sql);
		$row = BasicDataObject::getDataRow($sql);
		$reprinted = isset($row['reprinted']) ? $row['reprinted'] : 0;
		return $reprinted;
	}
	
	public static function markAsRedeemed($user_item_id)
	{
		$result = null;
		// Not sure whether to call UserItems::redeemCoupon() instead of performing a straight update()
		
		$sql = "update user_items set date_redeemed = now(), coupchecked = '1', num_scans = num_scans + 1 where id = '".$user_item_id."'";
		if(Database::mysqli_query($sql))
		{
			$result = true;
			
			$sql = "update cache_resellers_stats set redeemed = ifnull(redeemed, 0) + 1 where companies_id = (select i.manufacturer_id from user_items ui inner join items i on ui.item_id = i.id where ui.id = '".Database::mysqli_real_escape_string($user_item_id)."')";
			if(!Database::mysqli_query($sql))
				error_log("SQL update error in UserItems::markAsRedeemed(): ".Database::mysqli_error()."\nSQL: ".$sql);
		}
		else
		{
			error_log("Update SQL error in function markAsRedeemed in recover-redeemed-coupon-data controller: ".Database::mysqli_error()."\nSQL: " . $sql);
			$result = false;
		}
		
		// $result = UserItems::redeemCoupon($user_item_id);
		return $result;
	}

	// Gets user data for the admin redemption UI when a user name is entered
	public static function getUserDataForRedemptionRecovery($firstname, $lastname)
	{
		/*
		$user_data = array(
			array(
				'id' => '18931',
				'firstname' => 'Samee',
				'lastname' => 'Qazi',
				'facebook_id' => '524947534',
				'campaign_data' => array(
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
				),
			),
			array(
				'id' => '18932',
				'firstname' => 'Samee2',
				'lastname' => 'Qazi2',
				'facebook_id' => '524947534',
				'campaign_data' => array(
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
				),
			),
			array(
				'id' => '18933',
				'firstname' => 'Samee3',
				'lastname' => 'Qazi3',
				'facebook_id' => '524947534',
				'campaign_data' => array(
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
					array('id' => '1420', 'name' => 'Lindt Truffles', 'num_claimed' => '25', 'num_redeemed' => '4'),
				),
			),
		);*/
		
		$user_data = array();
		$arr_sql_username = array();
			
		if(!empty($firstname))
			$arr_sql_username[] = "firstname like '%".Database::mysqli_real_escape_string($firstname)."%'";
		
		if(!empty($lastname))
			$arr_sql_username[] = "lastname like '%".Database::mysqli_real_escape_string($lastname)."%'";
			
		$sql_username = "";
		if(!empty($arr_sql_username))
			$sql_username = " where ".implode(' and ', $arr_sql_username);
		
		$sql = "select id, facebook_id, firstname, lastname from users $sql_username order by id limit 100";
		
		$rows = BasicDataObject::getDataTable($sql);
		
		$user_ref_table = array();
		$user_ids = array();
		
		foreach($rows as $i => $row)
		{
			$user_id = $row['id'];
			$user_ids[] = $user_id;
			
			$data = array();
			
			// Filling in User Data			
			$data['id'] 				= $user_id;
			$data['firstname'] 		= $row['firstname'];
			$data['lastname'] 		= $row['lastname'];
			$data['facebook_id'] 	= $row['id'];

			
			$user_ref_table[$user_id] = $data;

		}

		if(!empty($user_ids))
		{
			// $sql = "select id, firstname, lastname, facebook_id from users $sql_username order by id limit 100";
			$sql = "select ui.user_id, i.campaign_id, i.campaign_name,
				count(ui.id) as num_claimed, sum(ui.coupchecked) as num_redeemed
				from user_items ui
				inner join items i on ui.item_id = i.id
				where user_id in (".implode(',', $user_ids).")
				group by ui.user_id, i.campaign_id
				order by ui.id";
			
			error_log("SQL in UserItems::getUserDataForRedemptionRecovery: ".$sql);
			
			$rows = BasicDataObject::getDataTable($sql);
			foreach($rows as $i => $row)
			{
				$user_id = $row['user_id'];
				
				$data = array();
				
				$campaign_data = array();
				
				$campaign_data['id'] = $row['campaign_id'];
				$campaign_data['name'] = $row['campaign_name'];
				$campaign_data['num_claimed'] = $row['num_claimed'];
				$campaign_data['num_redeemed'] = $row['num_redeemed'];
				
				$user_data[$user_id]['id'] = $user_ref_table[$user_id]['id'];
				$user_data[$user_id]['firstname'] = $user_ref_table[$user_id]['firstname'];
				$user_data[$user_id]['lastname'] = $user_ref_table[$user_id]['lastname'];
				$user_data[$user_id]['facebook_id'] = $user_ref_table[$user_id]['facebook_id'];
				
				if(!isset($user_data[$user_id]['campaign_data']))
					$user_data[$user_id]['campaign_data'] = array();
					
				$user_data[$user_id]['campaign_data'][] = $campaign_data;
			}
		}
		// error_log("user_data in UserItems::getUserDataForRedemptionRecovery(): ".var_export($user_data, true));
		
		return $user_data;
	}
	
	public static function getUserItemDataForRedemptionRecovery($campaign_id, $cc_committed)
	{
		/*
		$user_item_data = array(
			array(
				'user_id' => '18931',
				'firstname' => 'Samee',
				'lastname' => 'Qazi',
				'facebook_id' => '524947534',
				'campaign_id' => '1420',
				'campaign_name' => 'Lindt Truffles',
				'num_claimed' => '25',
				'num_redeemed' => '4',
				'campaign_data' => array(
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
				),
			),
			array(
				'user_id' => '18931',
				'firstname' => 'Samee',
				'lastname' => 'Qazi',
				'facebook_id' => '524947534',
				'campaign_id' => '1420',
				'campaign_name' => 'Lindt Truffles',
				'num_claimed' => '25',
				'num_redeemed' => '4',
				'campaign_data' => array(
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
					array('user_item_id' => '1234', 'cashier_code' => '1420-01', 'redeemed' => 'yes'),
				),
			),
		);
		*/
		
		$user_item_data = array();
		
		$limit_value = 20; // 20 rows above and below
		$lower_limit = 0;
		$upper_limit = $limit_value * 2;
		if(is_numeric($cc_committed))
		{
			if($cc_committed > $limit_value)
				$lower_limit = $cc_committed - $limit_value;
		}
		
		$sql_campaign = !empty($campaign_id) ? " and i.campaign_id = '".Database::mysqli_real_escape_string($campaign_id)."'" : "";
		// Get the
		$sql = "select ui.id as user_item_id, u.facebook_id, u.firstname, u.lastname, ui.date_redeemed, ui.user_id, i.campaign_id, i.campaign_name, 
				ui.date_claimed, ui.date_redeemed, ui.coupchecked
					from user_items ui
					inner join items i on ui.item_id = i.id 
					left join users u on ui.user_id = u.id
					where ui.date_committed is not null
					$sql_campaign
					order by ui.id
					limit $lower_limit, $upper_limit";
					
		error_log("SQL in UserItems::getUserItemDataForRedemptionRecovery: ".$sql);
		
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			$user_id = $row['user_id'];
			$index = $user_id . '-' . $row['campaign_id'];
			
			$user_item_data[$index]['user_id'] 		= $user_id;
			$user_item_data[$index]['firstname'] 		= $row['firstname'];
			$user_item_data[$index]['lastname'] 		= $row['lastname'];
			$user_item_data[$index]['facebook_id'] 	= $row['facebook_id'];
			$user_item_data[$index]['campaign_id'] 		= $row['campaign_id'];
			$user_item_data[$index]['campaign_name'] 		= $row['campaign_name'];
			
			if(!isset($user_item_data[$index]['num_claimed']))
				$user_item_data[$index]['num_claimed'] = 0;
			
			if(!isset($user_item_data[$index]['num_redeemed']))
				$user_item_data[$index]['num_redeemed'] = 0;
			
			if(!empty($row['date_claimed']))
				$user_item_data[$index]['num_claimed']++;
				
			if(!empty($row['date_redeemed']))
				$user_item_data[$index]['num_redeemed']++;
			
			if(!isset($cashier_code_count[$row['campaign_id']]))
				$cashier_code_count[$row['campaign_id']] = 0;
			
			$cashier_code_count[$row['campaign_id']]++;
			
			$campaign_data = array();
			$campaign_data['user_item_id'] = $row['user_item_id'];
			$campaign_data['cashier_code'] = $row['campaign_id'] . '-' . ($cashier_code_count[$row['campaign_id']]);
			$campaign_data['redeemed'] = !empty($row['date_redeemed']) ? 'Yes' : 'No';
			
			$user_item_data[$index]['campaign_data'][] = $campaign_data;
		}
		error_log("user_item_data in UserItems::getUserItemDataForRedemptionRecovery(): ".var_export($user_item_data, true));
		return $user_item_data;
	}
	
	public static function checkAndRedeemByBarcode($uiid)
	{
		// Simply Match the barcode
		$sql = "select * from user_items where uiid = '".Database::mysqli_real_escape_string($uiid)."'";
		$row = BasicDataObject::getDataRow($sql);
		if(!empty($row['id']))
		{
			$user_item_id = $row['id'];
			$result = UserItems::markAsRedeemed($user_item_id);
			if($result)
			{
				$msg = array("Barcode found and successfully marked as 'redeemed'", "success");
			}
			else
			{
				$msg = array("Couldn't mark the barcode record as redeemed!", "failure");
			}
		}
		else
		{
			$msg = array("Barcode not found!", "failure");
		}
		return $msg;
	}
	
	public static function checkAndRedeemByUser($user_id, $campaign_id = '')
	{
		$sql_campaign = !empty($campaign_id) ? " and i.campaign_id = '".Database::mysqli_real_escape_string($campaign_id) . "'" : "";
		
		$sql = "select ui.* 
				from user_items ui 
				inner join items i on ui.item_id = i.id 
				where ui.user_id = '".$user_id."' 
				$sql_campaign
				and (ui.date_redeemed is null or ui.date_redeemed = '')
				and ui.coupchecked != '1' 
				order by ui.id desc limit 1";
				// error_log("SQL for matching users: ".$sql);
				
		$row = BasicDataObject::getDataRow($sql);
		if(!empty($row['id']))
		{
			$user_item_id = $row['id'];
			$result = UserItems::markAsRedeemed($user_item_id);
			if($result)
			{
				$msg = array("User record found and successfully marked as 'redeemed'", "success");
			}
			else
			{
				$msg = array("Couldn't mark the user record as redeemed!", "failure");
			}
		}
		else
		{
			$msg = array("User record not found in the user_items table", "failure");
		}
		return $msg;
	}
	
	public static function checkAndRedeemByUserItem($user_item_id)
	{
		$result = UserItems::markAsRedeemed($user_item_id);
		if($result)
		{
			$msg = array("User Item Record has been successfully marked as 'redeemed'", "success");
		}
		else
		{
			$msg = array("Couldn't mark the User Item Record as redeemed!", "failure");
		}
		
		return $msg;
	}
	
	
	public static function checkAndRedeemByCashierCode($campaign_id, $cc_committed) {
		$sql = "select ui.id, ui.is_regular_coupon, ui.num_scans, i.manufacturer_id, i.limit_per_person from user_items ui inner join items i on ui.item_id = i.id where i.campaign_id = '" . Database::mysqli_real_escape_string($campaign_id) . "' and ui.date_committed is not null order by ui.id";
		// error_log("sql in function get_user_item_by_cashier_code(): ".$sql);
		$rs = Database::mysqli_query($sql);
		$user_item_counter = 0;
		//$social_print_counter = 0;
		
		if ($rs && Database::mysqli_num_rows($rs) > 0) {
			while($row = Database::mysqli_fetch_assoc($rs)) {
				$user_item_counter++;
				if($user_item_counter == $cc_committed) {
					$user_item_id = $row['id'];
					$result = UserItems::markAsRedeemed($user_item_id);
					if($result) {
						if (($row['num_scans'] + 1) > $row['limit_per_person']) {
							$msg = array("User Item Record has been marked as 'redeemed' more times than allowed", "failure");
						} else {
							$msg = array("User Item Record has been successfully marked as 'redeemed'", "success");
						}
					} else {
						$msg = array("Couldn't mark the Cashier Code Record as redeemed!", "failure");
					}
					return $msg;
				}
			}
		}
		$msg = array("Couldn't find the given cashier code!", "failure");
		return $msg;
	}
	
	public static function getUserCompanyUnsubscribes($user_id) {
		$user_companies = array();
		$sql = "select DISTINCT(ui.item_id), c.id as company_id, c.display_name, ucu.id as unsubscribe_id 
			from user_items ui
			left join items i on ui.item_id = i.id
			left join companies c on i.manufacturer_id = c.id
			left join user_company_unsubscribe ucu on (ucu.company_id = c.id AND ucu.user_id = ui.user_id)
			where ui.user_id = '" . Database::mysqli_real_escape_string($user_id) . "'
			order by c.display_name";
		$rs = Database::mysqli_query($sql);
		if ($rs &&  Database::mysqli_num_rows($rs) > 0) {
			$row = Database::mysqli_fetch_assoc($rs);
			$user_companies[$row['company_id']] = $row;
		}
		return $user_companies;
	}
	
	public static function getUserItemIdByUIID($uiid)
	{
		$sql = "select id from user_items where uiid = '".Database::mysqli_real_escape_string($uiid) . "'";
		$row = BasicDataObject::getDataRow($sql);
		$user_item_id = null;
		if(!empty($row['id']))
			$user_item_id = $row['id'];
		
		return $user_item_id;
	}
	
	public static function getReprintInfoByCode($reprint_code)
	{
		$sql = "select * from user_items where reprint_code = '".Database::mysqli_real_escape_string($reprint_code)."'";
		return BasicDataObject::getDataRow($sql);
	}
	
	public static function getUserItemsByEmail($email)
	{
		
		$sql = "select ui.id, ui.uiid, ui.item_id, i.name as item_name, ui.allow_reprint, ui.reprint_code, ui.reprint_url_sent, ui.reprinted, 
			count(uir1.id) as allowed_reprints, count(uir2.id) as denied_reprints
			from user_items ui
			inner join users u on ui.user_id = u.id
			inner join items i on ui.item_id = i.id
			left join user_item_reprints uir1 on (ui.id = uir1.user_item_id and uir1.status = 'allowed')
			left join user_item_reprints uir2 on (ui.id = uir2.user_item_id and uir2.status = 'not_allowed')
			where u.email = '".Database::mysqli_real_escape_string($email). "'
			and ui.uiid is not null 
			and ui.uiid != ''
			group by ui.id
			order by id desc";
		
		$rows = BasicDataObject::getDataTable($sql);
		$url = Common::getBaseURL(true) . "/support-request?c=";
		foreach($rows as $i => $row)
			$rows[$i]['reprint_url'] = $url . urlencode($row['reprint_code']);
		
		return $rows;
	}
	
	public static function generateAndUpdateReprintInfo($user_item_id)
	{
		$reprint_code = UUID::v4();
		$sql = "update user_items set reprint_code = '$reprint_code', allow_reprint = '1' where id = '" . Database::mysqli_real_escape_string($user_item_id) . "'";
		if(!Database::mysqli_query($sql))
			error_log("SQL Update error in UserItems::generateAndUpdateReprintInfo(): ".Database::mysqli_error() . "\nSQL: " . $sql);
	}
	
	public static function getReprintInfoById($user_item_id)
	{
		$sql = "select * from user_items where id = '".Database::mysqli_real_escape_string($user_item_id)."'";
		return BasicDataObject::getDataRow($sql);
	}
}


?>