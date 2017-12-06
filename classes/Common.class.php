<?php

	require_once (dirname(__DIR__) . '/includes/app_config.php');
	require_once (dirname(__DIR__) . '/includes/facebook-php-sdk/src/facebook.php');
	require_once(dirname(__DIR__) . '/includes/UUID.php');
	/*
	require_once (dirname(__DIR__) . '/classes/Customer.class.php');
	require_once (dirname(__DIR__) . '/classes/User.class.php');
	require_once (dirname(__DIR__) . '/classes/Stats.class.php');
	require_once (dirname(__DIR__) . '/classes/MobileESP.class.php');
	require_once (dirname(__DIR__). '/classes/UserItems.class.php');
	require_once (dirname(__DIR__). '/classes/UBX.class.php');
	require_once (dirname(__DIR__). '/classes/SilverPop.class.php');
	require_once (dirname(__DIR__). '/classes/ExactTarget.class.php');
*/

	class Common
	{

		public static $default_sgs_item_img = "https://s3.amazonaws.com/sgsimg.coupsmart.com/birthday_plat.png";
		public static $receive_message = "You received this email because you purchased an item in our Facebook Gift Shop, this email is just to confirm your purchase. If you did not make a purchase, or believe this email is in error, let us know by emailing our <a href=\"mailto:support@coupsmart.com\">Support Team</a>.";
		
		public static $conversion_base = "abcdefghijklmnopqrstuvwxyz";
		
		public static $cc_billing_yearly_license_id 	= '1';
		public static $cc_billing_monthly_claims_id		= '2';

		public static function isMobileESP()
		{
			$device				= new MobileESP();
			$is_iphone_ipod		= $device->DetectIphoneOrIpod();
			$is_android_phone	= $device->DetectAndroidPhone();
			$is_ipad			= $device->DetectIpad();
			$is_android_tablet	= $device->DetectAndroidTablet();
			// error_log("in Common::isMobileESP(): is_iphone_ipod: " . var_export($is_iphone_ipod, true) . ", is_android_phone: " . var_export($is_android_phone, true) . ", is_ipad: " . var_export($is_ipad, true) . ", is_android_tablet: " . var_export($is_android_tablet, true));
			$is_mobile = ( !empty($is_iphone_ipod) || !empty($is_android_phone))  && empty($is_ipad) && empty($is_android_tablet);
	
			return $is_mobile;

		}

		public function is_browser_obsolete() {
			$result = false;
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				// Chrome iOS detection
				$is_chrome_ios = preg_match('/CriOS/', $_SERVER['HTTP_USER_AGENT']) || preg_match('/Chrome/', $_SERVER['HTTP_USER_AGENT']);
				$browser_info = get_browser(null, true);
				$browser = $browser_info['browser'];
				$version = intval($browser_info['version']);
				switch($browser)
				{
					case "Firefox":	// Firefox 3
						$result = $version < 3;
						break;

					case "IE":	// IE 8
						$result = $version < 8;
						break;

					case "Safari":	// Safari 4
						if (!$is_chrome_ios) {
							// $result = $version < 4; // Can't check Safari version with outdated browscap.ini  :-(
							break;
						}

					case "Chrome":	// Chrome
						break;

					case "Magento_API":
						$result = false;
						break;

					default:
						break;
				}
			}
			//error_log("browser in is_browser_obsolete(): ".var_export($browser, true));
			return $result;
		}
		
		public static function getDBCurrentDate($interval = 0, $type = '', $date_format = '', $default_date = null)
		{
			$str_date = !empty($default_date) ? "'" . $default_date . "'" : 'now()';
			
			if(!empty($interval) && !empty($type))
				$str_date = "date_add(" . $str_date . ", interval $interval $type)";
			
			if(!empty($date_format))
				$str_date = "date_format(" . $str_date . ", '" . $date_format . "')";
				
			$sql = "select $str_date as `current_date`";
			// error_log("SQL in Common::getDBCurrentDate(): " . $sql);
			$row = BasicDataObject::getDataRow($sql);
			$current_date = $row['current_date'];
			return $current_date;
		}
		
		public static function getBaseURL($bDiscardEndingSlash = false) {
			$pageURL = 'http';
			if ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")) {$pageURL .= "s";}
			$pageURL .= "://";
			$server_name = $_SERVER["SERVER_NAME"];
			if ($_SERVER["SERVER_NAME"] == 'www.coupsmart.com') {
				$server_name = 'coupsmart.com';
			}
			if (isset($_SERVER['SERVER_PORT']) && $_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $server_name.":".$_SERVER["SERVER_PORT"];
			} else {
				$pageURL .= $server_name;
			}
			if($bDiscardEndingSlash)
				$pageURL = rtrim($pageURL, "/");

			return $pageURL;
		}
		
		public static function getURI($bDiscardBeginningSlash = false)
		{
			$uri = $_SERVER['REQUEST_URI'];
			if($bDiscardBeginningSlash)
				$uri = ltrim($uri, "/");
			
			return $uri;
		}
	
		public static function getPageURL()
		{
			return Common::getBaseURL(true) . '/'. Common::getURI(true);
		}

		public static function getCouptBaseURL($bDiscardEndingSlash = false) {
			$pageURL = 'http';
			if ((!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")) {$pageURL .= "s";}
			$pageURL .= "://";
			$server_name = $_SERVER["SERVER_NAME"];
			if ($_SERVER["SERVER_NAME"] == 'www.coupsmart.com') {
				$server_name = 'cou.pt';
			}elseif($_SERVER['SERVER_NAME'] == 'dev.coupsmart.com'){
				$server_name = 'dev.cou.pt';
			}elseif($_SERVER['SERVER_NAME'] == 'dev.coupsmart.local'){
				$server_name = 'dev.cou.local';
			}
			if (isset($_SERVER['SERVER_PORT']) && $_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= $server_name.":".$_SERVER["SERVER_PORT"];
			} else {
				$pageURL .= $server_name;
			}
			if($bDiscardEndingSlash)
				$pageURL = rtrim($pageURL, "/");

			return $pageURL;
		}
		
		public static function getRootPath($bDiscardEndingSlash = false) {
			$root_path = $_SERVER['DOCUMENT_ROOT'];
			if($bDiscardEndingSlash)
				$root_path = rtrim($root_path, "/");

			return $root_path;
		}
		
		public static function getLocationBasedDealsContentMO($company_id, $use_location_based_deals, $my_url, $delivery_method, $app_link)
		{
			global $app_id, $app_secret, $app_url;
			global $app_version;
			$permissions = "user_location";
			$field_list = "id,name,location";
			$passed_state = "2h3g4jh2g342gf3h4gfh";
			if(!empty($use_location_based_deals))
			{
				$company = new Company($company_id);
				if(empty($_SESSION['loc_zip_code']) && empty($_SESSION['loc_dma']))
				{
					$loc_dmagate_error_nodeal = "<div><h1 class='no-deal-headline'>WE'RE SORRY</h1><p class='no-deal-bodycopy'>It looks like there aren't any deals in your area. Please check back later for more savings!</p></div>";

					if(!empty($_REQUEST['hdn_check_now']))
					{
						$new_redirect_uri = urlencode($my_url . "&hdn_check_now=". $_REQUEST['hdn_check_now']);
						if(empty($_REQUEST['state']))
						{
							try
							{

								$login_url = "https://www.facebook.com/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . $new_redirect_uri ."&scope=" . $permissions. "&state=" . $passed_state; 
								// print '<script type="text/javascript">top.location.href="' . $login_url . '"</script>';
								header("Location: " . $login_url);
								
							}
							catch (Exception $e)
							{
								error_log("Exception caught when calling getLoginURL(): " . $e->getMessage());
							}
						}
						else
						{
							try 
							{
						
								// $company = new Company($company_id);
								$cities = Location::getAllCities($company_id);
								$options_cities = "<option value='-1'>None of these</option>";
								$arr_cities = array();
								foreach($cities as $city)
								{
									$options_cities .= "<option value='" . strtolower($city['city']). "'>" . ucfirst($city['city']). "</option>";
									// $arr_cities[] = $city['city'];
								}
								
								// To be customized later
								$city_or_state = "state";
								$content_dmagate_error_no_dma = !empty($company->loc_dmagate_error_nodma_mo) ? $company->loc_dmagate_error_nodma_mo : "<div><h1 class='no-deal-headline'>WE'RE SORRY</h1><p class='no-deal-bodycopy'>It looks like we can't determine your area. Please type in the $city_or_state closest to yours from the textfield below</p>
										<div><select style='width:120px;' id='loc_city' name='loc_city'><option></option></select><input type='button' class='button greenbtn' id='btn_loc_city' value='Check Now' /></div>
										</div>";
								$content_dmagate_error_no_dma = str_replace("<option></option>", $options_cities, $content_dmagate_error_no_dma);

								// Get Location
								$access_token_url = 'https://graph.facebook.com/v' . $app_version . '/oauth/access_token?client_id=' . $app_id . '&redirect_uri=' . $new_redirect_uri . '&client_secret=' . $app_secret . '&code=' . $_GET['code'] . '';
		
								$response = file_get_contents($access_token_url);
								$response = json_decode($response, true);
								$access_token = $response['access_token'];
								
								$fb_data_url = 'https://graph.facebook.com/v' . $app_version . '/me?access_token=' . $access_token . '&fields=' . $field_list;
								$me_response = file_get_contents($fb_data_url);
								$location_info = json_decode($me_response, true);
								if(!empty($location_info['location']['id']))
								{
									$fb_location_id = $location_info['location']['id'];
									// Get City for the location
									$city = Location::getCityByFacebookLocationId($fb_location_id);
									// Check if one or more deals exist for the DMA
									if(!empty($city))
									{
										$deals = Location::getRunningDealsByCity($city, $company_id, $delivery_method);
										$no_deals_found = count($deals) == 0;
										if($no_deals_found)
										{
											////////////////////////////////////////////////////////
											//////	DMAGATE-ERROR-NODEAL
											////////////////////////////////////////////////////////
											$content = !empty($company->loc_dmagate_error_nodeal_mo) ? $company->loc_dmagate_error_nodeal_mo : $loc_dmagate_error_nodeal;
										}
										else
										{
											$_SESSION['loc_dma'] = $city;
											$_SESSION['loc_company_id'] = $company_id;
											// print "<script type='script/javascript'>top.location.href='" . $app_link . "';</script>";
											header("Location: " . $app_link);
											exit();
											// unset($_SESSION['loc_dma']);
											// $content = $app_link;
											// exit();
									
										}
									}
									else
									{
			
										////////////////////////////////////////////////////////
										//////	DMAGATE-ERROR-NODMA
										////////////////////////////////////////////////////////
										$content = $content_dmagate_error_no_dma;
									}
								}
								else
								{
									////////////////////////////////////////////////////////
									//////	DMAGATE-ERROR-NODMA
									////////////////////////////////////////////////////////
									$content = $content_dmagate_error_no_dma;
								}
								print '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
				<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
				<script src="//connect.facebook.net/en_US/sdk.js"></script>
				<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
				<link rel="stylesheet" href="/css/foundation.css" />
				<link rel="stylesheet" href="/css/2014_default.css" />';
								print "<script type='text/javascript'>
									var company_id = '" . $company_id. "';
									$(document).ready(function() {
						
								$('#btn_loc_city').live('click', function() {
									var loc_city = $('#loc_city').val();
									$.ajax({
										type: 'post',
										data: {'op': 'loc_city', 'loc_city': loc_city, 'company_id': company_id, 'delivery_method': '" . MOBILE . "'},
										dataType: 'json',
										url: '/helpers/ajax-smart-deals.php',
										success: function(data) {
											// console.log('data upon success');
											// console.log(data);
											if(data == '1')
											{
												top.location.href='" . $app_link . "';
											}
											else
											{
												$('#div-outer-gate').html(data);
											}
										},
										error: function(err) {
											console.log('error:');
											console.log(err);
										},
									});
								});
							});
							FB.init({appId: '" . $app_id . "', status: true, cookie: true, xfbml: true, version: 'v" .  $app_version . "'});
				
							function isPermissionRejected(granted_permissions, required_permissions)
							{
								var permissions_rejected = false;
	
								// Checking for Basic Permissions
								if(granted_permissions['error'] != undefined) // || granted_permissions['data'][0]['installed'] != 1)
								{
									// console.log('Basic Permissions not granted!');
									permissions_rejected = true;
								}
								else
								{
									var tmp_granted_permissions = {};
									for(i in granted_permissions['data'])
									{
										var permission = granted_permissions['data'][i]['permission'];
										var status = granted_permissions['data'][i]['status'];
	
										if(status == 'granted')
											tmp_granted_permissions[permission] = 1;
									}
									// console.log(tmp_granted_permissions);
		
									// Checking for Required Permissions
									for(i in required_permissions)
									{
										// if(granted_permissions['data'][0][required_permissions[i]] != 1)
										if(tmp_granted_permissions[required_permissions[i]] != 1)
										{
											// console.log('One or more Required Permissions not granted!');
											permissions_rejected = true;
											break;
										}
									}
								}
								return permissions_rejected;
							}
						</script>";
								print "<div id='div-outer-gate'>" . $content . "</div>";
								exit();

							}
							catch(Exception $e)
							{
								error_log("Exception caught when calling getting calling FB API(): " . $e->getMessage());
								print ("Exception caught when calling getting calling FB API(): " . $e->getMessage());
							}
						}
						exit();
					}
			
					print '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js" type="text/javascript" charset="utf-8"></script>
				<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js" type="text/javascript" charset="utf-8"></script>
				<script src="//connect.facebook.net/en_US/sdk.js"></script>
				<link href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />
				<link rel="stylesheet" href="/css/foundation.css" />
				<link rel="stylesheet" href="/css/2014_default.css" />';
					print "<script type='text/javascript'>
						var company_id = '" . $company->id. "';
						$(document).ready(function() {
							$('#btn_loc_zip_code').click(function() {
								var loc_zip_code = $('#loc_zip_code').val();
								$.ajax({
									type: 'post',
									data: {'op': 'loc_zip_code', 'loc_zip_code': loc_zip_code, 'company_id': company_id, 'delivery_method': '" . MOBILE . "'},
									dataType: 'json',
									url: '/helpers/ajax-smart-deals.php',
									success: function(data) {
										// console.log('data upon success');
										// console.log(data);
										if(data == 'INVALID')
										{
											$('#span_err_msg_loc_zip_code').html('Please input a valid zip code!');
											$('#span_err_msg_loc_zip_code').addClass('error');
										}
										else if(data == '1')
										{
											top.location.href='" . $app_link . "';
										}
										else
										{
											$('#div-outer-gate').html(data);
										}
									},
									error: function(err) {
										console.log('error:');
										console.log(err);
									},
								});
							});
					
							$('#btn_loc_dma').click(function() {
								$('#frm_check_now').submit();
								return false;
								var required_permissions = ['email','user_birthday','user_location','user_relationships', 'user_likes', 'user_friends'];
								var str_required_permissions = required_permissions.join(',');
								// alert('checking area for deals...');
						
								FB.login(function(response){
									if (response.authResponse) {
										var permissions_rejected;
										var access_token = response.authResponse.accessToken;

										FB.api('/v" . $app_version . "/me/permissions', function(granted_permissions){

											permissions_rejected = isPermissionRejected(granted_permissions, required_permissions);
				
											if(permissions_rejected)
											{
												FB.api('/v" . $app_version . "/me/permissions','DELETE',function(response){});
												alert('Please provide permissions....');
												// updatePermissionsRejected(item_id);
												return false;
											}
											else
											{
												$.ajax({
													type: 'post',
													data: {'op': 'loc_dma', 'company_id': company_id, 'delivery_method': '" . MOBILE . "'},
													dataType: 'json',
													url: '/helpers/ajax-smart-deals.php',
													success: function(data) {
														// console.log('data upon success');
														// console.log(data);
														if(data == '1')
														{
															top.location.href='" . $app_link . "';
														}
														else
														{
															$('#div-outer-gate').html(data);
														}
													},
													error: function(err) {
														console.log('error:');
														console.log(err);
													},
												});
											}

										});
									}
									else
									{alert('Please provide permissions....');
										// updatePermissionsRejected(item_id);
									}
								},{scope: str_required_permissions});						
							});
					
							$('#loc_city').live('keyup.autocomplete', function(){
								$(this).autocomplete({
									disabled: false,
									delay: 100,
									source: arr_cities,
									minLength: 1,
									messages: {
										noResults: '',
										results: function() {}
									},
									focus: function (event, ui) {
										   this.value = ui.item.label;
										   event.preventDefault(); // Prevent the default focus behavior.
									},
									select: function( event, ui ) {
										event.preventDefault();
										// console.log(event);
										$(this).val(ui.item.label);
									},
								})
							});
				
							$('#btn_loc_city').live('click', function() {
								var loc_city = $('#loc_city').val();
								$.ajax({
									type: 'post',
									data: {'op': 'loc_city', 'loc_city': loc_city, 'company_id': company_id, 'delivery_method': '" . MOBILE . "'},
									dataType: 'json',
									url: '/helpers/ajax-smart-deals.php',
									success: function(data) {
										// console.log('data upon success');
										// console.log(data);
										if(data == '1')
										{
											top.location.href='" . $app_link . "';
										}
										else
										{
											$('#div-outer-gate').html(data);
										}
									},
									error: function(err) {
										console.log('error:');
										console.log(err);
									},
								});
							});
						});
						FB.init({appId: '" . $app_id . "', status: true, cookie: true, xfbml: true, version: 'v" .  $app_version . "'});
				
						function isPermissionRejected(granted_permissions, required_permissions)
						{
							var permissions_rejected = false;
	
							// Checking for Basic Permissions
							if(granted_permissions['error'] != undefined) // || granted_permissions['data'][0]['installed'] != 1)
							{
								// console.log('Basic Permissions not granted!');
								permissions_rejected = true;
							}
							else
							{
								var tmp_granted_permissions = {};
								for(i in granted_permissions['data'])
								{
									var permission = granted_permissions['data'][i]['permission'];
									var status = granted_permissions['data'][i]['status'];
	
									if(status == 'granted')
										tmp_granted_permissions[permission] = 1;
								}
								// console.log(tmp_granted_permissions);
		
								// Checking for Required Permissions
								for(i in required_permissions)
								{
									// if(granted_permissions['data'][0][required_permissions[i]] != 1)
									if(tmp_granted_permissions[required_permissions[i]] != 1)
									{
										// console.log('One or more Required Permissions not granted!');
										permissions_rejected = true;
										break;
									}
								}
							}
							return permissions_rejected;
						}
					</script>";
					if($company->use_location_based_deals == '1')
					{
						//////////////////////////////////////////////////////////////////////////
						//////	SHOW ZIPGATE-NORM
						//////////////////////////////////////////////////////////////////////////
				
						$loc_zipgate_norm = !empty($company->loc_zipgate_norm_mo) ? $company->loc_zipgate_norm_mo : "<div style='background-image:url(http://uploads.coupsmart.com.s3.amazonaws.com/zipgate-norm-example.jpg);width:810px;height:350px;' id='zipgate-norm'><div style='vertical-align:middle; position:relative; top:200px; left:400px;' id='div_loc_zip_code'><input id='loc_zip_code' name='loc_zip_code' placeholder='Enter zipcode' type='text' style='width:250px; height:49px; margin-bottom:0px; display: inline;' /><input type='button' id='btn_loc_zip_code' value='Check Now' class='button greenbtn' style='height:49px; margin-bottom:0px;' /><small id='span_err_msg_loc_zip_code' style='width:250px; margin-right:0px;'></small></div></div>";
						print "<div id='div-outer-gate'>" . $loc_zipgate_norm . "</div>";
					}
					else if($company->use_location_based_deals == '2')
					{
						//////////////////////////////////////////////////////////////////////////
						//////	SHOW DMAGATE-NORM
						//////////////////////////////////////////////////////////////////////////
						$loc_dmagate_norm = !empty($company->loc_dmagate_norm_mo) ? $company->loc_dmagate_norm_mo : "<div style='background-image:url(http://uploads.coupsmart.com.s3.amazonaws.com/dmagate-norm-example.jpg);width:100%;height:350px;' id='dmagate-norm'><div style='vertical-align:middle; position:relative; top: 280px; left:580px;' id='div_loc_zip_code'><input type='button' id='btn_loc_dma' value='Check My Area For Deals' class='button greenbtn' /></div></div>";
						print "<div id='div-outer-gate'>" . $loc_dmagate_norm . "</div>";
					}
					print "<form method='post' name='frm_check_now' id='frm_check_now'><input type='hidden' name='hdn_check_now' id='hdn_check_now' value='1' /></form>";
					exit();
				}
			}
		}
		
		public static function isPermissionRejected($granted_permissions, $required_permissions)
		{
			// error_log("granted_permissions: " . var_export($granted_permissions, true));
			// error_log("required_permissions: " . var_export($required_permissions, true));
			$permissions_rejected = false;
	
			// Checking for Basic Permissions
			if(!empty($granted_permissions['error']))
			{
				// console.log('Basic Permissions not granted!');
				$permissions_rejected = true;
			}
			else
			{
			
				$tmp_granted_permissions = array();
				foreach($granted_permissions['data'] as $tmp)
				{
					$permission = $tmp['permission'];
					$status = $tmp['status'];
	
					if($status == 'granted')
						$tmp_granted_permissions[$permission] = 1;
				}

				// Checking for Required Permissions
				foreach($required_permissions as $i => $permission)
				{
					// if($granted_permissions['data'][0][$required_permissions[$i]] != 1)
					if($tmp_granted_permissions[$permission] != 1)
					{
						// error_log('Common::isPermissionRejected() : One or more Required Permissions not granted!');
						$permissions_rejected = true;
						break;
					}
				}
			}
			return $permissions_rejected;
		}
		
		//calculate years of age (input string: YYYY-MM-DD)
		public static function calculateAgeFromBirthday($birthday){
			list($year,$month,$day) = explode("-",$birthday);
			$year_diff  = date("Y") - $year;
			$month_diff = date("m") - $month;
			$day_diff   = date("d") - $day;
			if ($day_diff < 0 || $month_diff < 0)
				$year_diff--;

			return $year_diff;
		}
		
		//calculate years of age (input string: YYYY-MM-DD)
		public static function calculateAge($date_of_birth){
			return floor((time() - strtotime($date_of_birth))/31556926);
		}

		public static function convertDateToMySQLFormat($date_format, $date)
		{
			// error_log("date in Common::convertDateToMySQLFormat(): ".$date);
			$date = DateTime::createFromFormat($date_format, $date);
			if ($date) {
				return $date->format('Y-m-d');
			} else {
				return false;
			}
		}

		public static function convertMySQLDateToSpecifiedFormat($date_format, $date)
		{
			return date($date_format, strtotime($date));
		}

		public static function buildPromotionsURL($app_name, $method){
			$url = Common::getBaseURL();

			switch($app_name){
				case "countmein":
					if($method == 'print'){
						$url .= "/helpers/" . $app_name . "_" . $method . ".php";
					}
					break;
				case "promotion";

					break;
			}

			return $url;
		}

		public static function multiSortArrayByColumn(&$result, $sort_by, $sort_order)
		{
			$sortArray = array();

			if(strtolower($sort_order) == 'desc')
				$sort_order = SORT_DESC;
			else
				$sort_order = SORT_ASC;

			foreach($result as $row){
				 foreach($row as $key=>$value){
					  if(!isset($sortArray[$key])){
						   $sortArray[$key] = array();
					  }
					  $sortArray[$key][] = $value;
				 }
			}
			$orderby = $sort_by;
			array_multisort($sortArray[$orderby], $sort_order, $result);
		}
		
		public static function parse_fb_date($date_of_birth, $day_before_month = null)
		{
			$date_of_birth = explode('/', $date_of_birth);
			$date_of_birth[2] = isset($date_of_birth[2]) ? $date_of_birth[2] : '0000';
			
			if(!empty($day_before_month))
				$date_of_birth = $date_of_birth[2] . '-'. $date_of_birth[1] . '-'.  $date_of_birth[0];
			else
				$date_of_birth = $date_of_birth[2] . '-'. $date_of_birth[0] . '-'.  $date_of_birth[1];
				
			return $date_of_birth;
		}

		public static function get_next_camp_route($platform, $step)
		{
			global $add_camp_routes;

			// if the platform is not defined in array, then use default;
			if(!array_key_exists($platform, $add_camp_routes))
				$platform = "default";

			// if the step is not defined in the platform array, then return the first element;
			if(!in_array($step, $add_camp_routes[$platform]))
				return $add_camp_routes[$platform][0];

			$index = array_search($step, $add_camp_routes[$platform]);
			if($index < count($add_camp_routes[$platform]) - 1)
				$index++;

			return $add_camp_routes[$platform][$index];
		}

		public static function get_prev_camp_route($platform, $step)
		{
			global $add_camp_routes;

			// if the platform is not defined in array, then use default;
			if(!array_key_exists($platform, $add_camp_routes))
				$platform = "default";

			// if the step is not defined in the platform array, then return the first element;
			if(!in_array($step, $add_camp_routes[$platform]))
				return $add_camp_routes[$platform][0];

			$index = array_search($step, $add_camp_routes[$platform]);
			if($index > 0)
				$index--;

			return $add_camp_routes[$platform][$index];
		}

		public static function get_header_content($url)
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

			$content = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close ($ch);
			if ($info) {
				return $info;
			} else {
				return null;
			}
		}

		public static function encodeURIComponent($str) {
    			$revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    			return strtr(rawurlencode($str), $revert);
		}
		
		// This function checks whether an access token exists
		//	for a FB App and Object, and adds/updates a row to the fb_access_tokens table accordingly
		public static function checkAndStoreFBAccessToken($access_token_data)
		{
			$arr_permissions = explode(',', $access_token_data['permissions']);
			$arr_permissions = array_unique($arr_permissions);
			sort($arr_permissions);
			$access_token_data['permissions'] = implode(',', $arr_permissions);

			//	1.	Check whether the access token exists for the app
			$sql = "select * from fb_access_tokens";
	
			$arr_where_clause = array();
			// if(!empty($access_token_data['app_name']))
			//	$arr_where_clause[] = " app_name = '" . $access_token_data['app_name'] . "'";
		
			if(!empty($access_token_data['facebook_id']))
				$arr_where_clause[] = " facebook_id = '" . $access_token_data['facebook_id'] . "'";
	
			if(!empty($access_token_data['app_id']))
				$arr_where_clause[] = " app_id = '" . $access_token_data['app_id'] . "'";
	
			// if(!empty($access_token_data['permissions']))
			//	$arr_where_clause[] = " permissions = '" . $access_token_data['permissions'] . "'";
		
			if(!empty($arr_where_clause))
				$sql .= " where " . implode(' and ', $arr_where_clause);
	
			$sql .= " order by id desc limit 1";
	
			// error_log("Select SQL in Common::checkAndStoreFBAccessToken(): " . $sql);
			$row = BasicDataObject::getDataRow($sql);
			if(empty($row['id']))
			{
				// Access Token not found, extend the time period and store it
				list($app_id, $app_secret, $facebook)	= Common::CreateFacebookObject($access_token_data['app_name']);
				$extended_access_token_info				= Common::extendAccessTokenExpiryDate($access_token_data['access_token'], $app_id, $app_secret);
		
				// Proceed only if the extended access token is successfully retrieved
				if(!empty($extended_access_token_info['access_token']))
				{
					$access_token_data['access_token'] 	=	$extended_access_token_info['access_token'];
					$expires_in 	= 	$extended_access_token_info['expires_in'];
		
					$arr_field_names = array();
					$arr_field_values = array();
		
					foreach($access_token_data as $field_name => $field_value)
					{
						$field_value = Database::mysqli_real_escape_string($field_value);
						if(!empty($field_value))
						{
							$arr_field_names[] = "`" . $field_name. "`";
							$arr_field_values[] = "'" . $field_value. "'";
						}
					}
		
					// Storing the Expiry Time
					if(!empty($expires_in))
					{
						$arr_field_names[] = "`expire_time`";
						$arr_field_values[] = "date_add(now(), interval " . $expires_in . " second)";
					}
			
					$arr_field_names[] = "`modified`";
					$arr_field_values[] = "now()";
				
					if(!empty($arr_field_names))
					{
						$sql = "insert into `fb_access_tokens` (" . implode(',', $arr_field_names) . ") values (" . implode(',', $arr_field_values) . ")";
						// error_log('Insert SQL in Common::checkAndStoreFBAccessToken(): ' . $sql);
						if(!Database::mysqli_query($sql))
							error_log("SQL Insert error in Common::checkAndStoreFBAccessToken(): " . Database::mysqli_error() . "\nSQL: " . $sql);
					}
				}
			}
			else
			{
				// Access Token not found, extend the time period and store it
				list($app_id, $app_secret, $facebook)	= Common::CreateFacebookObject($access_token_data['app_name']);
				$extended_access_token_info				= Common::extendAccessTokenExpiryDate($access_token_data['access_token'], $app_id, $app_secret);
		
				// Proceed only if the extended access token is successfully retrieved
				if(!empty($extended_access_token_info['access_token']))
				{
					$access_token_data['access_token'] 	=	$extended_access_token_info['access_token'];
					$expires_in 	= 	$extended_access_token_info['expires_in'];
					$expire_time = !empty($expires_in) ? "date_add(now(), interval " . $expires_in . " second)" : "null";
					$sql = "update `fb_access_tokens` set 
					`access_token` = '" . $access_token_data['access_token']. "',
					`expire_time` = " . $expire_time . ",
					`modified` = now()
					where id = '" . $row['id']. "'";
					// error_log('Update SQL in Common::checkAndStoreFBAccessToken(): ' . $sql);
					if(!Database::mysqli_query($sql))
						error_log("SQL Update error in Common::checkAndStoreFBAccessToken(): " . Database::mysqli_error() . "\nSQL: " . $sql);
				}	
			}
		}
		
				
		public static function getCompanyFBAppPageLink($company_id, $app_name, $use_access_token = true)
		{
			global $app_version;
			$company = new Company($company_id);
			$page_id = $company->facebook_page_id;
			list($app_id, $app_secret, $facebook) = Common::CreateFacebookObject($app_name);
			$app_link = "http://www.facebook.com"; // Initialize to any value
			if (!empty($page_id)) {
				try
				{
					if($use_access_token && !empty($company->access_token))
						$page_info = $facebook->api("/v" . $app_version . "/" . $page_id, array('access_token' => $company->access_token));
					else
						$page_info = $facebook->api("/v" . $app_version . "/" . $page_id);
					$page_link = $page_info['link'];
					// error_log('fb page_info = ' . var_export($page_info, true));

					$app_link = $page_link . '?sk=app_' . $app_id;
					return $app_link;
				}
				catch(Exception $e)
				{
					error_log("Error getting company facebook page info in Common::getCompanyFBAppPageLink(): " . $e->getMessage());
				}
			}
			else
			{
				error_log("Common::getCompanyFBAppPageLink(): Company Facebook Page ID not found in the DB!");
			}
		}

		public static function CreateFacebookObject($app_name){

			global $app_id, $app_secret, $app_url;
			global $socgift_app_id, $socgift_app_secret;
			global $countmein_app_id, $countmein_app_secret;
			global $socialbooking_app_id, $socialbooking_app_secret;
			global $connect_app_id, $connect_app_secret;
			global $app_version;
	
			switch($app_name){
				case "countmein":
					$app_check_id = $countmein_app_id;
					$app_check_secret = $countmein_app_secret;
					break;
		
				case "social_gift_shop":
				case "sgs":
					$app_check_id = $socgift_app_id;
					$app_check_secret = $socgift_app_secret;
					break;
		
				case "promotions":
				case "fan_deals":
				case "web":
					$app_check_id = $app_id;
					$app_check_secret = $app_secret;
					break;
		
				case "birthday":
					$app_check_id = $countmein_app_id;
					$app_check_secret = $countmein_app_secret;
					break;
		
				case "booking":
					$app_check_id = $socialbooking_app_id;
					$app_check_secret = $socialbooking_app_secret;
					break;
		
				case "connect":
				case "instore":
				case "mobile":
				case "convercial":
					$app_check_id = $app_id; // $connect_app_id;
					$app_check_secret = $app_secret; // $connect_app_secret;
					break;
			}

			$facebook = new Facebook(array(
			  'appId'  => $app_check_id,
			  'secret' => $app_check_secret,
			  'cookie' => true,
			  'version' => 'v' . $app_version
			));

			return array($app_check_id, $app_check_secret, $facebook);
		}

		public static function extendAccessTokenExpiryDate($access_token, $app_id, $app_secret)
		{
			global $app_version;
			// Getting an access token with extended expiry date (of 60 days)
			$extended_access_token_url = "https://graph.facebook.com/v" . $app_version . "/oauth/access_token?client_id=$app_id&client_secret=$app_secret&grant_type=fb_exchange_token&fb_exchange_token=$access_token";
			$response = file_get_contents($extended_access_token_url);
			$params = json_decode($response, true); // array();
			// parse_str($response, $params);
	
			return $params;
		}
		
		public static function getFileExtension($filename)
		{
			$ext = substr(strrchr($filename, '.'), 1);
			return $ext;
		}

		public static function generate_unique_sig($table_name, $field_name, $limit = 99999999999)
		{
			$sig = '';
			$num_rows = 0;
			$num_digits = strlen((string) $limit);

			do
			{
				$sig = sprintf("%0" . $num_digits . "d", rand(0, $limit));
				$sql = "select $field_name from $table_name where $field_name = '$sig'";
				$rs = Database::mysqli_query($sql);
				$num_rows = Database::mysqli_num_rows($rs);
			}
			while($num_rows > 0);
			Database::mysqli_free_result($rs);

			return $sig;
		}

		public static function GetUserIp() {
		    // check for shared servers
			if(isset($_SERVER['HTTP_CLIENT_IP']))
			{
					$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
		    // check for proxy servers
			elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];

			} else {
				  // general method
					$ip = $_SERVER['REMOTE_ADDR'];
			}
			list($ip) = explode(',',$ip);
			return $ip;
		}

		public static function scaleProportional($img, $maxWidth, $maxHeight) {
			$geometry	= $img->getImageGeometry();
			$width		= $geometry['width'];
			$height		= $geometry['height'];

			$scale 		= min($maxWidth / $width, $maxHeight / $height);
			$newWidth 	= round($width * $scale);
			$newHeight 	= round($height * $scale);

			/*
			$scale 		= min($width / $maxWidth , $height / $maxHeight );
			$newWidth 	= round($maxWidth * $scale);
			$newHeight 	= round($maxHeight * $scale);
			*/

			return array('height' => $newHeight, 'width' => $newWidth);
		}
		
		public static function getUserBlockedMessageContent()
		{
			$content = '<div id="userBlockedMessage" style="color:#000;padding:10%;display:block;font-family:verdana, sans-serif;position:fixed;z-index: 5;">
			<h1>Your Account Has Been Blocked From This App</h1>
			<p>We are sorry, but you are no longer allowed to access this application.</p>
			<p>Should you wish to dispute this status, you may contact our Support Team at our <a href="http://support.coupsmart.com" target="_blank">Help Center</a> and open a new ticket.</p>
			<p>Thanks,<br/>The CoupSmart Team</p>
			</div>';
			return $content;
		}
		
		public static function log_error($file_name, $line_number, $var_name, $var_data, $description = null)
		{
			$var_data = str_replace("'", "''", $var_data);
			$table_data = array(
				'file_name' => $file_name,
				'line_number' => $line_number,
				'var_name' => $var_name,
				'var_data' => $var_data,
			);
			if(!empty($description))
				$table_data['description'] = Database::mysqli_real_escape_string($description);
			BasicDataObject::InsertTableData('error_log', $table_data);
		}
		
		public static function renderText($text, $font, $image, $width = 500, $height = 500, $left = 0, $top = 0, $bgcolor = 'transparent', $opacity = null) {
			$text = str_replace('%', '%%', $text);
			$bgcolor = !empty($bgcolor) ? $bgcolor : 'transparent';
			$mod_img = new Imagick();
			$mod_img->setFont($font);
			$mod_img->setBackgroundColor($bgcolor);
			$mod_img->newPseudoImage($width, $height, "caption:" . $text);
			if(!empty($opacity))
				$mod_img->setImageOpacity($opacity);
			$image->compositeImage($mod_img, imagick::COMPOSITE_OVER, $left, $top);
		}
		
		public static function upc2CarolinaTxt($upc) {
			$num_system = substr($upc, 0, 1);
			$mfg_num = substr($upc, 1, 5);
			$mfg_nums = str_split($mfg_num);
			$prod_code = substr($upc, 6, 5);
			$prod_nums = str_split($prod_code);
			$check_digit = null;
			if (strlen($upc) == 12) {
				$check_digit = substr($upc, -1);
			} elseif (strlen($upc) == 11) {
				$check_digit = Common::generate_upc_checkdigit($upc);
			}

			$mfg_map = array(
				1 => 'q',
				2 => 'w',
				3 => 'e',
				4 => 'r',
				5 => 't',
				6 => 'y',
				7 => 'u',
				8 => 'i',
				9 => 'o',
				0 => 'p'
			);

			$prod_map = array(
				1 => 'a',
				2 => 's',
				3 => 'd',
				4 => 'f',
				5 => 'g',
				6 => 'h',
				7 => 'j',
				8 => 'k',
				9 => 'l',
				0 => ';'
			);

			$chk_map = array(
				1 => 'z',
				2 => 'x',
				3 => 'c',
				4 => 'v',
				5 => 'b',
				6 => 'n',
				7 => 'm',
				8 => ',',
				9 => '.',
				0 => '/'
			);

			$ctxt = $num_system;

			foreach ($mfg_nums as $num) {
				$ctxt .= $mfg_map[$num];
			}

			$ctxt .= '-';

			foreach ($prod_nums as $num) {
				$ctxt .= $prod_map[$num];
			}

			$ctxt .= $chk_map[$check_digit];

			return $ctxt;
		}
		
		public static function generate_upc_checkdigit($upc_code) {
			$odd_total  = 0;
			$even_total = 0;
			for($i=0; $i<11; $i++) {
				if((($i+1)%2) == 0) {
					/* Sum even digits */
					$even_total += $upc_code[$i];
				} else {
					/* Sum odd digits */
					$odd_total += $upc_code[$i];
				}
			}
			$sum = (3 * $odd_total) + $even_total;
			/* Get the remainder MOD 10*/
			$check_digit = $sum % 10;
			/* If the result is not zero, subtract the result from ten. */
			return ($check_digit > 0) ? 10 - $check_digit : $check_digit;
		}
		
		public static function drawRectangle($image, $x1, $y1, $x2, $y2, $strokeColor = 'black', $fillColor = 'transparent', $backgroundColor = 'transparent')
		{
			$draw = new \ImagickDraw();
			$strokeColor = new \ImagickPixel($strokeColor);
			$fillColor = new \ImagickPixel($fillColor);

			$draw->setStrokeColor($strokeColor);
			$draw->setFillColor($fillColor);
			$draw->setStrokeOpacity(1);
			$draw->setStrokeWidth(2);

			$draw->rectangle($x1, $y1, $x2, $y2);
			
			$image->drawImage($draw);
		}
	}
?>