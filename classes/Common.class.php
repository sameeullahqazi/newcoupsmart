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
		
		public static function getLocationBasedDealsContent($company, $meta_for_layout, $app_link, $str_delivery_method = '')
		{
			global $app_id, $app_secret, $app_url;
			global $app_version;
			if(!empty($company->use_location_based_deals))
			{
				if(empty($_SESSION['loc_zip_code']) && empty($_SESSION['loc_dma']))
				{
					if(!empty($meta_for_layout)) print $meta_for_layout;
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
									data: {'op': 'loc_zip_code', 'loc_zip_code': loc_zip_code, 'company_id': company_id, 'delivery_method': '" . $str_delivery_method  . "'},
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
								var required_permissions = ['user_location'];
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
													data: {'op': 'loc_dma', 'company_id': company_id, 'delivery_method': '" . $str_delivery_method  . "', 'access_token': access_token},
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
									data: {'op': 'loc_city', 'loc_city': loc_city, 'company_id': company_id, 'delivery_method': '" . $str_delivery_method  . "'},
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
			
						$loc_zipgate_norm = !empty($company->loc_zipgate_norm) ? $company->loc_zipgate_norm : "<div style='background-image:url(http://uploads.coupsmart.com.s3.amazonaws.com/zipgate-norm-example.jpg);width:810px;height:350px;' id='zipgate-norm'><div style='vertical-align:middle; position:relative; top:200px; left:400px;' id='div_loc_zip_code'><input id='loc_zip_code' name='loc_zip_code' placeholder='Enter zipcode' type='text' style='width:250px; height:49px; margin-bottom:0px; display: inline;' /><input type='button' id='btn_loc_zip_code' value='Check Now' class='button greenbtn' style='height:49px; margin-bottom:0px;' /><small id='span_err_msg_loc_zip_code' style='width:250px; margin-right:0px;'></small></div></div>";
						print "<div id='div-outer-gate'>" . $loc_zipgate_norm . "</div>";
					}
					else if($company->use_location_based_deals == '2')
					{
						//////////////////////////////////////////////////////////////////////////
						//////	SHOW DMAGATE-NORM
						//////////////////////////////////////////////////////////////////////////
						$loc_dmagate_norm = !empty($company->loc_dmagate_norm) ? $company->loc_dmagate_norm : "<div style='background-image:url(http://uploads.coupsmart.com.s3.amazonaws.com/dmagate-norm-example.jpg);width:100%;height:350px;' id='dmagate-norm'><div style='vertical-align:middle; position:relative; top: 280px; left:580px;' id='div_loc_zip_code'><input type='button' id='btn_loc_dma' value='Check My Area For Deals' class='button greenbtn' /></div></div>";
						print "<div id='div-outer-gate'>" . $loc_dmagate_norm . "</div>";
					}
					exit();
				}
			}
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
		
		public static function getUBXChannelType($delivery_method)
		{
			$arr_facebook = array_flip(explode(',', FACEBOOK));
			$arr_web = array_flip(explode(',', WEB));
			$arr_mobile = array_flip(explode(',', MOBILE));
			
			if(isset($arr_facebook[$delivery_method]))
				return UBX::$channel_social;
			
			if(isset($arr_web[$delivery_method]))
				return UBX::$channel_web;
				
			if(isset($arr_mobile[$delivery_method]))
				return UBX::$channel_mobile;
		}
		
		public static function generateAuthSaltSignature($company_id, $rnd, $utcTimeStamp, $auth_salt_value)
		{
			// error_log("func args in Company::generateAuthSaltSignature(): ". var_export(func_get_args(), true));
			$str = $auth_salt_value;
			$sig = md5($rnd . ($rnd + 67) .  $auth_salt_value . $rnd . $company_id); // . $utcTimeStamp);
			return $sig;
		}
		
		public static function checkAuthSaltSignature($company_id, $auth_salt_value = false, $sig_to_check = false)
		{
			// error_log("func args in Company::checkAuthSaltSignature(): ". var_export(func_get_args(), true));
			if(empty($auth_salt_value))
			{
				$company = new Company($company_id);
				$auth_salt_value = $company->auth_salt_value;
			}
			
			$num_waiting_seconds = 30;  // 30 seconds seems like a reasonable amount of delay to check against
			$rnd_num_range = 1000;
			
			// $utcTimeStampStart = Common::getUTCTimestampValue(); // <UTC Timestamp numeric value >. (May need to omit this later; just specified for additional security)
			// $utcTimeStampEnd = $utcTimeStampStart - $num_waiting_seconds;
			$i = 0;
			for($rnd = 0; $rnd <= $rnd_num_range; $rnd++)
			{
				//for($i = $utcTimeStampStart; $i >= $utcTimeStampEnd; $i--) 
				// {
					$sig = Common::generateAuthSaltSignature($company_id, $rnd, $i, $auth_salt_value);
					if($sig == $sig_to_check)
						return true;
				// }
			}
			// error_log("sig in CSAPI::checkCSAPISignature(): " . $sig);
			return false;
		}
		
		
		public static function isSafari()
		{
			$safari = false;
			$ua = $_SERVER["HTTP_USER_AGENT"];      // Get user-agent of browser

			$safariorchrome = strpos($ua, 'Safari') ? true : false;     // Browser is either Safari or Chrome (since Chrome User-Agent includes the word 'Safari')
			$chrome = strpos($ua, 'Chrome') ? true : false;             // Browser is Chrome

			if($safariorchrome == true AND $chrome == false){ $safari = true; }     // Browser should be Safari, because there is no 'Chrome' in the User-Agent

			return $safari;
		}
		
		public static function safariBugFixHTMLContent($base_url, $app_link)
		{
			$content = '';
			$content .= '<script type="text/javascript">';
			$redirect_url = $base_url. "/safari_cookie_fix.php?redirect_url=" . urlencode($app_link);
			$content .= 'top.location.href="' . $redirect_url . '";';
			$content .=	'</script>';
			return $content;
		
			$content = '';
			$content .= '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>';		
			$content .= '<script type="text/javascript">';
			$content .= 'var popupBlockerChecker = {
			check: function(popup_window){
			var _scope = this;
			if (popup_window) {
				if(/chrome/.test(navigator.userAgent.toLowerCase())){
					setTimeout(function () {
						_scope._is_popup_blocked(_scope, popup_window);
					 },200);
				}else{
					popup_window.onload = function () {
						_scope._is_popup_blocked(_scope, popup_window);
					};
				}
			}else{
				_scope._displayError();
			}
			},
			_is_popup_blocked: function(scope, popup_window){
			if ((popup_window.innerHeight > 0)==false){ scope._displayError(); }
			},
			_displayError: function(){
			alert("Popup Blocker is enabled! Please add this site to your exception list.");
			$("#div-popup-blocked").css("display", "normal");
			return true;
			}
			};';

			$content .= 'var popup = window.open("' . $base_url. '/safari_cookie_fix.php", "");
			var popup_blocked = popupBlockerChecker.check(popup);
			console.log("popup_blocked: " + popup_blocked);
			if(popup_blocked == true){
				document.write("Pop blocker is turned on. Please turn it off to proceed.");
			} else { 
				window.setTimeout(function(){top.location.href="' . $app_link . '";}, 2000);
			}';
			$content .=	'</script>';
			return $content;
		}
		
		public static function fblocale_to_locale($fb_locale) {
			$locale = str_replace('_', '-', strtolower($fb_locale));
			return $locale;
		}
		
		public static function translate_to($lang, $phrases) {
			$result = array();
			$str_ary = array();
			foreach ($phrases as $phrase) {
				$str_ary[] = "'" . Database::mysqli_real_escape_string($phrase) . "'";
				$result[$phrase] = $phrase;
			}

			if ($lang != 'en' && $lang != 'en-us') {
				$sql = "select * from translations where statement IN (" . implode(", ", $str_ary) . ") and lang = '" . Database::mysqli_real_escape_string($lang) . "'";
				$rs = Database::mysqli_query($sql);
				if ($rs && Database::mysqli_num_rows($rs) > 0) {
					while ($row = Database::mysqli_fetch_assoc($rs)) {
						$result[$row['statement']] = $row['translation'];
					}
				}
				Database::mysqli_free_result($rs);
			}
			return $result;
		}
		
		public static function getLikebarContent($likebar_image, $facebook_page_id, $platform = "fan_deals")
		{
			global $app_id;
			$heights_platform = array("fan_deals" => 50, "convercial" => 50, "web" => 50);
			$height = $heights_platform[$platform];
			
			$likebar_content = '<div id="div_likebar_content" style="width: 100%; height: 50px; background-image: url(\'http://uploads.coupsmart.com.s3.amazonaws.com/' . $likebar_image. '\');background-repeat: no-repeat;">
	<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2F' . $facebook_page_id . '&amp;width&amp;layout=button&amp;action=like&amp;size=large&amp;show_faces=false&amp;share=false&amp;height=35&amp;appId=' . $app_id . '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:35px; position: absolute; top: 14px; left: 15px;" allowTransparency="true"></iframe>
	</div>
	<br/>';
			$likebar_content = '<div id="div_likebar_content" style="width: 100%; height:' . $height . 'px; background-image: url(\'http://uploads.coupsmart.com.s3.amazonaws.com/' . $likebar_image. '\');background-repeat: no-repeat;background-size:100%">
	<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2F' . $facebook_page_id . '&amp;width&amp;layout=button&amp;action=like&amp;size=small&amp;show_faces=false&amp;share=false&amp;height=35&amp;appId=' . $app_id . '" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:35px; position: absolute; top: 14px; left: 5px;" allowTransparency="true"></iframe>
	</div>
	<br />';
			return $likebar_content;

		}
		
		public static function getLikebarContentForMobile($likebar_image, $facebook_page_id, $platform = "convercial")
		{
		
			$heights_platform = array("fan_deals" => 37, "convercial" => 50, "web" => 50);
			$height = $heights_platform[$platform];
			
			// $likebar_image = CacheImage::getImg($likebar_image, 320, 50);
			$likebar_content = '<div id="div_likebar_content" style="width: 100%; height: 50px; background-image: url(\'http://uploads.coupsmart.com.s3.amazonaws.com/' . $likebar_image . '\');background-repeat: no-repeat;">
<div style="margin-top: 14px; margin-left: 10px;" class="fb-like no-print" data-href="https://www.facebook.com/' . $facebook_page_id. '" data-layout="button" data-action="like" data-show-faces="false" data-share="false"></div>
</div>
<br/>';

			$likebar_content = '<div id="div_likebar_content" style="width: 100%; height: ' . $height . 'px; background-image: url(\'http://uploads.coupsmart.com.s3.amazonaws.com/' . $likebar_image . '\');background-repeat: no-repeat; background-size:100%;">
<div class="fb-like" data-href="https://www.facebook.com/' . $facebook_page_id . '" data-layout="button" data-action="like" data-size="large" data-show-faces="false" data-share="false"></div>
</div>
<br/>';
			return $likebar_content;

		}
		
		public static function getCSCRevealDealContent($csc_custom_code, $csc_background_image, $csc_cta_heading, $csc_cta_url, $likebar_content)
		{
			$csc_cta_url_content = "";
			if(!empty($csc_cta_url))
			{
				$csc_cta_url_content	= '<div id="txlink" style="position: absolute; top: 37%; left: 10%;"><a href="' . $csc_cta_url. '" target="_blank" style="font-family: arial; color: #0f243f; font-size: 30px;">' . $csc_cta_heading. '</a></div>';
			}
			/*$csc_reveal_deal_content = $likebar_content . '<div style="position:relative;">
	<img  style="max-width: 810px;width: 100%;" src="http://uploads.coupsmart.com.s3.amazonaws.com/' . $csc_background_image . '" alt="" style="z-index: -1"/>
	<h2 style="position: absolute; bottom: 43%; left: 60%; font-size: 40px; color: #0f243f; ">' . $csc_custom_code . '</h2>
</div>' . $csc_cta_url_content;*/

			$csc_reveal_deal_content = $likebar_content . '<div style="position:relative;top:100px;height:600px;">
	<img src="http://uploads.coupsmart.com.s3.amazonaws.com/' . $csc_background_image . '" alt="" style="z-index: -1"/>
	<h2 style="position: absolute; top: 235px; left: 10px; font-size: 25px; color: black; background-color:white;border: 1px solid black; padding: 5px; font-weight: normal;">' . $csc_custom_code . '</h2>
</div>' . $csc_cta_url_content;
			return $csc_reveal_deal_content;
		}
		
		public static function getCSCRevealDealContentForMobile($csc_custom_code, $csc_background_image, $csc_cta_heading, $csc_cta_url, $likebar_content)
		{
			$csc_cta_url_content = "";
			if(!empty($csc_cta_url))
			{
				$csc_cta_url_content	= '<div id="txlink" style="position: absolute; top: 37%; left: 10%;"><a href="' . $csc_cta_url. '" target="_blank" style="font-family: arial; color: #0f243f; font-size: 30px;">' . $csc_cta_heading. '</a></div>';
			}
			$csc_reveal_deal_content = $likebar_content . '<div style="position:relative;top:100px;height:600px;">
	<img src="http://uploads.coupsmart.com.s3.amazonaws.com/' . $csc_background_image . '" alt="" style="z-index: -1"/>
	<h2 style="position: absolute; top: 235px; left: 10px; font-size: 25px; color: black; background-color:white;border: 1px solid black; padding: 5px; font-weight: normal;">' . $csc_custom_code . '</h2>
</div>' . $csc_cta_url_content;
			return $csc_reveal_deal_content;
		}
		
		public static function getWhiteLabelCSS($mobile_preview_image, $mo_button_text_color, $mo_button_color, $mo_headline_text_color, $mo_headline_bg, $button_details_color, $mo_header_color, $mo_body_color)
		{
			$mo_button_text_color	= empty($mo_button_text_color)	? '#ffffff'	: $mo_button_text_color;
			$mo_button_color		= empty($mo_button_color)		? '#0000ff'	: $mo_button_color;
			$mo_headline_text_color	= empty($mo_headline_text_color)? '#ffffff'	: $mo_headline_text_color;
			$mo_headline_bg			= empty($mo_headline_bg)		? '#0000ff'	: $mo_headline_bg;
			$button_details_color	= empty($button_details_color)	? '#ababab'	: $button_details_color;
			$mo_header_color		= empty($mo_header_color)	? '#999999'		: $mo_header_color;
			$mo_body_color			= empty($mo_body_color)	? '#ffffff'			: $mo_body_color;
			
			$white_label_css = 'div#div-deal-background-image {
			display: none !important
			}

			div.overlay {
			display: none !important;
			}

			#company_logo{
				border:0px solid red; 
				width:100%; 
				display:block;
			}


			.ui-btn-text{
				color: #111;
				width: 200px;
				font-size: 10px;
				margin-top: -19px;
				margin-left: -10px;
			}

			#logoblock{
				display:table-cell; 
				max-height:80px;
				max-width:80px;
				float:left; 
				vertical-align:middle;
				background-repeat:no-repeat;
				background-size:100%;
				margin:8px;
			}

			#location{
				display:table-cell; 
				height:100%;
				width:170px;
			}
			/* Header */
			.dealheader {
				background-color: ' . $mo_headline_bg . ';
				color: ' . $mo_headline_text_color . ';
			}
			/* Use Now Button */
			#btn_print_now {
				border: 1px solid #145072;
				color: ' . $mo_button_text_color . ';
				background: ' . $mo_button_color . ';
				background-image: -moz-linear-gradient(top, #4E89C5, #2567AB);
				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #5F9CC5), color-stop(1, #396B9E));
				border-radius:10px;
				margin-top:10px;
			}

			/* Use Now Button Hover*/
			#btn_print_now:hover {
				background: orange;
			}

			/* Use Now Button Text*/
			#btn_print_now span {
				padding: .6em 25px;
				display: block;
				height: 100%;
				text-overflow: ellipsis;
				overflow: hidden;
				white-space: nowrap;
				position: relative;
			}

			/* Terms Details Text */
			p[name=\'p_instore_discount_instructions\'] {
				font-size: 8px;
			}

			/* Terms Button */
			.terms_button {
				text-align: center;
				border: 1px solid gray;
				background: #FDFDFD;
				border-radius:10px;
				background-image: -moz-linear-gradient(top, #EEE, #FDFDFD);
				background-image: -webkit-gradient(linear,left top,left bottom, color-stop(0, #EEE), color-stop(1, #FDFDFD));}

			.banner-row {background: ' . $mo_header_color . ';}
			.companyname {text-shadow: none;}
			a.button.success,.button.success:active,.button.success:hover,.button.success:focus {background-color:' . $mo_button_color . '; color: ' . $mo_button_text_color . '}
			body {background:' . $mo_body_color . ';}
			a.button.details,.button.details:active,.button.details:hover,.button.details:focus {background-color:' . $button_details_color . '; color: ' . $mo_button_text_color . ';}
			.offerimage div {background-image: none !important;}
			div.offerimage {background-image:url(\'http://uploads.coupsmart.com.s3.amazonaws.com/' . $mobile_preview_image . '\')!important;background-position: center center;background-size:contain;background-repeat:no-repeat;height: 300px;}
			a#change_email {color:#D60000;text-decoration:underline;}
			div#loaded button#print {display:none !important}';
			return $white_label_css;
		}
		
		public static function curl_request($url, $method = 'GET', $params = array(), $content_type = 'application/json', $auth_key = '')
		{
			$response = null;
			try
			{
				// Get cURL resource
				
				error_log("URL in Common::curl_request(): " . $url);
				
				$arr_curl_options = array(
					'Connection: keep-alive',
					'Keep-Alive: timeout=15, max=92',
					'Access-Control-Allow-Origin: *',
				);
				
				
				if(!empty($content_type))
					$arr_curl_options[] = 'Content-Type: ' . $content_type;
				
				$post_fields = $params;
				if($content_type == 'application/json')
				{
					$data_json = json_encode($params);
					$post_fields = $data_json;
					$arr_curl_options[] = 'Content-Length: ' . strlen($data_json);
				}
					
				if(!empty($auth_key))
					$arr_curl_options[] = 'Authorization: Bearer ' . $auth_key;
				
				error_log("post_fields in Common::curl_request(): " . var_export($post_fields, true));
				
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_FAILONERROR, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
				if(strtoupper($method) == 'POST')
				{
					curl_setopt($ch, CURLOPT_POST, 1); // or maybe set to true instead of 1
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $arr_curl_options);
				$response  = curl_exec($ch);
				$errno = curl_errno($ch);
				error_log("errno: " . var_export($errno, true));
				if($errno) {
					$error_message = curl_strerror($errno);
					$error = curl_error($ch);
					error_log("error number: $errno, msg: $error_message, curl error: $error");
				}
				curl_close($ch);
				error_log("response before decoding in Common::curl_request(): " . var_export($response, true));
				$response = json_decode($response, true);
				error_log("response after decoding in Common::curl_request(): " . var_export($response, true));
				return $response;
				
			}
			catch(Exception $e)
			{
				error_log("Error in Common::curl_request(): " . $e->getMessage());
			}
			return $response;
		}
		
		public static function getTemplateContent($template, $data)
		{
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
			
			// error_log("data in EmailTemplates::sendEmailAlert(): " . var_export($data, true));
			// Set template Data
			foreach($data as $key => $value){
				$tpl->assign($key, $value);
			}
			
			
			//USING RAINTPL AND EMOGRIFER TO CREATE TEMPLATED EMAIL
			//1.)	Instead of moving all template css to a file, use regex to grab everything inside style tag
			
			$css = '';
			
			//2.)	Check to see if they have custom css that they would rather use; if it is, replace
			

			//3.)	Draw the HTML
			$html = $tpl->draw($template, $return_string = true );
			//error_log("html: " .var_export($html, true));
			
			//4.)	Get the rendered template HTML with inline styles
			if(!empty($data['customCSS'])){ 
				$css	= $data['customCSS'];
				$emo = new emogrifier($html, $css);
				$html = $emo->emogrify();
			}
			
			
			return $html;
		}
		
		public static function getLoginRedirect($user_id){
			$logged_in_user = new User($user_id);

			$group = $logged_in_user->get_group();


			$redirect = "?user=true";

			if((is_array($group) && in_array(10, $group)) || $group == 10){
				$redirect = "coupcheck-input";
			}

			if ((is_array($group) && in_array(7, $group)) || $group == 7){
				$redirect = "customer";
			}

			if((is_array($group) && in_array(9, $group)) || $group == 9){
				$redirect = "affiliates";
			}

			if((is_array($group) && in_array(1, $group)) || $group == 1){
				$redirect = "admin";
			}
			if((is_array($group) && in_array(8, $group)) || $group == 8){
				$redirect = "reseller";
			}
			
			if((is_array($group) && in_array(11, $group)) || $group == 11){
				$redirect = "sales";
			}

			return $redirect;
		}

	}
?>