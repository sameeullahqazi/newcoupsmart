<?php

	require_once (dirname(__DIR__) . '/includes/app_config.php');
	require_once (dirname(__DIR__) . '/includes/Engage.php');

	class SilverPop
	{		
		public static $event_type_code_viewed = 28;
		public static $event_type_code_claimed = 29;
		public static $event_type_code_redeemed = 30;
		
		public static $relational_table_likes = "Likes";
		public static $relational_table_shares = "Shares";
		public static $relational_table_email_codes = "Email Codes";
		
		public static $date_swap_new_method = '2014-09-01 00:00:00'; // Date to swap to the new JSON format
		/*
		public static function decodeEncodedData($data)
		{
			return base64_decode(strtr($data, '_-', '+/')); 
		}
		*/
		public static function decodeEncodedData($erid)
		{
			$res = preg_match('#^([a-z0-9A-Z+/]{4})*([a-z0-9A-Z+/]{4}S0|[a-z0-9A-Z+/]{3}S1|[a-z0-9A-Z+/]{2}S2)$#', $erid);
 
			if ($res === false) {
				throw new Exception('SilverPop::decodeRecipientId(): Regular expression evaluation error');
			}
 
			if (!$res) {
				return false;
			}
 
			return base64_decode(substr($erid, 0, -2) . str_repeat('=', (int)substr($erid, -1, 1)));
		}
	
		public static function getSilverPopClickInfo()
		{
			$ip = Common::GetUserIp();
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
			$session_id = session_id();
			$sql = "select * 
			from silver_pop_clicks 
			where ip = '$ip' 
			and user_agent = '$user_agent' 
			and session_id = '$session_id'
			order by id desc limit 1";
			// error_log("sql in Item::getSmartLinkClickInfo(): " . $sql);
			return BasicDataObject::getDataRow($sql);
		}
	
		public static function updateSilverPopClickInfo($silver_pop_click_id)
		{
			$sql = "update silver_pop_clicks set viewed = '1' where id = '$silver_pop_click_id'";
			if(!Database::mysqli_query($sql))
				error_log("Update SQL error: " . Database::mysqli_error() . "\nSQL: " . $sql);
		}
		
		
		
		public static function getCommonAttributes($silver_pop_click_id, $user_id = null)
		{
			$sql = "select * from silver_pop_clicks where id = '" . Database::mysqli_real_escape_string($silver_pop_click_id). "'";
			$row = BasicDataObject::getDataRow($sql);

			$common_attributes = array(
				array(
					'name' => 'Silverpop Contact Id',
					'value' => $row['sp_contact_id_unencoded'],
				),
				array(
					'name' => 'Primary User Id',
					'value' => "anonymous_user_0@silverpop.com",
				),
			);
			if(!empty($user_id))
			{
				$common_attributes[] = array(
					'name' => 'Coupsmart Contact Id',
					'value' => $user_id,
				);
			}
			$common_attributes[] = array(
				'name' => 'Silverpop Mailing Id',
				'value' => $row['sp_mailing_id'],
			);
			$common_attributes[] = array(
				'name' => 'Silverpop Mailing Name',
				'value' => $row['sp_mailing_name'],
			);
		
			return $common_attributes;
		}
		
		
		public static function triggerViewedOffer($company_id, $sp_contact_id, $user_id, $page_id, $facebook_page_name)
		{
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			// INDEX ATTRIBUTES
			if(!empty($user_id))
			{
				$key_attributes[] = array(
					'name' => 'Coupsmart Contact Id', 
					'value' => $user_id,
				);
			}
		
			$index_attributes = array(
				array(
					'name' => 'Facebook Page Id',
					'value' => $page_id,
				),
			);
			if(!empty($facebook_page_name))
			{
				$index_attributes[] = array(
					'name' => 'Facebook Page Name', 
					'value' => $facebook_page_name,
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_viewed, $company_id, $sp_contact_id, $key_attributes, $index_attributes);
		}
		
		public static function triggerClaimedOffer($company_id, $sp_contact_id, $user_id, $item_id, $claim_id, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			$item = new Item($item_id);

			// INDEX ATTRIBUTES
			$index_attributes = array(
				array(
					'name' => 'Campaign Id',
					'value' => $item->campaign_id,
				),
				array(
					'name' => 'Campaign Name',
					'value' => $item->campaign_name,
				),
				array(
					'name' => 'Item Name',
					'value' => $item->name,
				),
			);
			
			// EXTRA ATTRIBUTES
			$extra_attributes = array();
			if(!empty($claim_id))
			{
				$extra_attributes[] = array(
					'name' => 'Claim Id',
					'value' => $claim_id
				);
			}
			if(!empty($item->small_type))
			{
				$extra_attributes[] = array(
					'name' => 'Item Description',
					'value' => $item->small_type,
				);
			}
			$item_img_url = Item::getItemImageUrl($item_id);
			if(!empty($item_img_url))
			{
				$extra_attributes[] = array(
					'name' => 'Item Image Url',
					'value' => $item_img_url,
				);
			}
			if(!empty($sp_mailing_id))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Id',
					'value' => $sp_mailing_id
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Name',
					'value' => $sp_mailing_name
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_claimed, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes);
		}
		
		public static function triggerViewedQRCode($company_id, $sp_contact_id, $unique_code, $sp_code_snippet_id, $sp_mailing_id, $sp_mailing_name)
		{
			$company = new Company($company_id);
			$page_id = $company->facebook_page_id;
			$facebook_page_name = $company->display_name;
			
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			// INDEX ATTRIBUTES
			$index_attributes = array(
				array(
					'name' => 'Facebook Page Id',
					'value' => $page_id,
				),
			);
			if(!empty($facebook_page_name))
			{
				$index_attributes[] = array(
					'name' => 'Facebook Page Name', 
					'value' => $facebook_page_name,
				);
			}
			
			// Extra Attributes
			$extra_attributes = array();
			/*
			if(!empty($unique_code))
			{
				$extra_attributes[] = array(
					'name' => 'Coupon Code', 
					'value' => $unique_code,
				);
			}
			if(!empty($sp_code_snippet_id))
			{
				$extra_attributes[] = array(
					'name' => 'Code Snippet Id', 
					'value' => $sp_code_snippet_id,
				);
			}
			*/
			
			if(!empty($sp_mailing_id))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Id',
					'value' => $sp_mailing_id
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Name',
					'value' => $sp_mailing_name
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_viewed, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes);
		}
		
		public static function triggerClaimedQRCode($company_id, $sp_contact_id, $qr_code_id, $qr_code_text, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			$item = new Item($item_id);

			// INDEX ATTRIBUTES
			$index_attributes = array(
			/*
				array(
					'name' => 'Campaign Id',
					'value' => $qr_code_id,
				),
				array(
					'name' => 'Campaign Name',
					'value' => $qr_code_text,
				),*/
				array(
					'name' => 'Item Name',
					'value' => $qr_code_text,
				),
			);
			
			// EXTRA ATTRIBUTES
			$extra_attributes = array();
			if(!empty($qr_code_id))
			{
				$extra_attributes[] = array(
					'name' => 'Claim Id',
					'value' => $qr_code_id
				);
			}
			
			if(!empty($sp_mailing_id))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Id',
					'value' => $sp_mailing_id
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Name',
					'value' => $sp_mailing_name
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_claimed, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes);
		}
		
		public static function triggerClaimedEmailCode($item_id, $sp_contact_id, $qr_code_id, $qr_code_text, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			$item = new Item($item_id);
			$company_id = $item->manufacturer_id;

			// INDEX ATTRIBUTES
			$index_attributes = array(
			/*
				array(
					'name' => 'Campaign Id',
					'value' => $qr_code_id,
				),
				array(
					'name' => 'Campaign Name',
					'value' => $qr_code_text,
				),*/
				array(
					'name' => 'Item Name',
					'value' => $qr_code_text,
				),
			);
			
			// EXTRA ATTRIBUTES
			$extra_attributes = array();
			if(!empty($qr_code_id))
			{
				$extra_attributes[] = array(
					'name' => 'Claim Id',
					'value' => $qr_code_id
				);
			}
			
			if(!empty($sp_mailing_id))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Id',
					'value' => $sp_mailing_id
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$extra_attributes[] = array(
					'name' => 'Silverpop Mailing Name',
					'value' => $sp_mailing_name
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_claimed, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes);
		}
		
		public static function triggerRedeemedQRCode($company_id, $sp_contact_id, $qr_code_id, $qr_code_text, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			// INDEX ATTRIBUTES
			$index_attributes = array(
				/*
				array(
					'name' => 'Campaign Id',
					'value' => $campaign_id,
				),		
				array(
					'name' => 'Campaign Name',
					'value' => $campaign_name,
				),*/
				array(
					'name' => 'Item Name',
					'value' => $qr_code_text,
				)
			);
			
			// EXTRA ATTRIBUTES
			$extra_attributes = array();
			if(!empty($qr_code_id))
			{
				$extra_attributes[] = array(
					'name' => 'Redemption Id',
					'value' => $qr_code_id,
				);
			}
			
			if(!empty($sp_mailing_id))
			{
				$extra_attributes[] = array(
					'name' => 'Mailing Id',
					'value' => $sp_mailing_id
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$extra_attributes[] = array(
					'name' => 'Mailing Name',
					'value' => $sp_mailing_name
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_redeemed, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes);
		}
		
		public static function triggerRedeemedOffer($company_id, $sp_contact_id, $user_id, $campaign_id, $campaign_name, $item_name, $user_item_id, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			// KEY ATTRIBUTES
			$key_attributes = array(
				array(
					'name' => 'Silverpop Contact Id', 
					'value' => $sp_contact_id,
				)
			);
			
			// INDEX ATTRIBUTES
			$index_attributes = array(
				array(
					'name' => 'Campaign Id',
					'value' => $campaign_id,
				),		
				array(
					'name' => 'Campaign Name',
					'value' => $campaign_name,
				),
				array(
					'name' => 'Item Name',
					'value' => $item_name,
				)
			);
			
			// EXTRA ATTRIBUTES
			$extra_attributes = array();
			if(!empty($user_item_id))
			{
				$extra_attributes[] = array(
					'name' => 'Redemption Id',
					'value' => $user_item_id,
				);
			}
			
			if(!empty($sp_mailing_id))
			{
				$extra_attributes[] = array(
					'name' => 'Mailing Id',
					'value' => $sp_mailing_id
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$extra_attributes[] = array(
					'name' => 'Mailing Name',
					'value' => $sp_mailing_name
				);
			}
			
			SilverPop::executeAPICallUsingJSON(SilverPop::$event_type_code_redeemed, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes);
		}
		
		public static function getCompanyAccessToken($company_id)
		{
			$sql = "select sp_access_token from companies where id = '$company_id' and sp_access_token_expire_time > now()";
			$row = BasicDataObject::getDataRow($sql);
			
			if(!empty($row['sp_access_token']))
				return $row['sp_access_token'];
				
			return null;
		}
		
		public static function updateCompanyAccessTokenInfo($company_id, $access_token, $expires_in)
		{
			$sql = "update companies set sp_access_token = '$access_token', sp_access_token_expire_time = date_add(now(), interval " . $expires_in . " second) where id = '$company_id'";
			if(!Database::mysqli_query($sql))
				error_log("SQL Update error in SilverPop::updateCompanyAccessTokenInfo(): " . Database::mysqli_error() . "\nSQL: " . $sql);
			
		}
		
		public static function getBaseURL($company_id)
		{
			
			
			$sql = "select sp_endpoint from companies where id = '" . Database::mysqli_real_escape_string($company_id) . "'";
			$row = BasicDataObject::getDataRow($sql);
			$sp_endpoint = !empty($row['sp_endpoint']) || ($row['sp_endpoint'] == '0' && is_numeric($row['sp_endpoint'])) ? $row['sp_endpoint'] : 'pilot'; // Set Default to 'pilot'
			
			$baseurl = "api" . $sp_endpoint . ".silverpop.com";
			return $baseurl;
		}
		
		public static function getAccessToken($company_id)
		{
			$access_token = NULL;
			error_log("COOKIE in SilverPop::getAccessToken(): " . var_export($_COOKIE['sp_access_token_' . $company_id], true));
			// 1. First check if access token exists in cookie
			if(!empty($_COOKIE['sp_access_token_' . $company_id]))
			{
				error_log('Getting Silverpop access token from cookie.');
				$access_token = $_COOKIE['sp_access_token_' . $company_id];
			}
			else	// 2. Otherwise search in database
			{
				error_log('Getting Silverpop access token from database.');
				// $access_token = SilverPop::getCompanyAccessToken($company_id);
				$sp_credentials = SilverPop::getSPCredentials($company_id);
				$access_token = $sp_credentials['access_token'];
				$sp_app_name = $sp_credentials['sp_app_name'];
				$sp_client_id = $sp_credentials['sp_client_id'];
				$sp_client_secret = $sp_credentials['sp_client_secret'];
				$sp_refresh_token = $sp_credentials['sp_refresh_token'];
				
				// 3. If still not found, generate it and store it to both the cookie and the database.
				if(empty($access_token))
				{
					// global $sp_app_name, $sp_client_id, $sp_client_secret, $sp_refresh_token;
					$params = array(
						'grant_type' 	=> 'refresh_token',
						'client_id' 	=> urlencode($sp_client_id),
						'client_secret' => urlencode($sp_client_secret),
						'refresh_token' => urlencode($sp_refresh_token),
					);
					$postdata = http_build_query($params);
					
					$baseurl = SilverPop::getBaseURL($company_id);
					$url = "https://" . $baseurl . "/oauth/token";
					// error_log("url in SilverPop::getAccessToken(): " . $url);
			
					$opts = array('http' =>
						array(
							'method'  => 'POST',
							'header'  => 'Content-type: application/x-www-form-urlencoded',
							'content' => $postdata
						)
					);
					$context  = stream_context_create($opts);

					$response = file_get_contents($url, false, $context);
					// $response = file_get_contents($url);
					// error_log("response in SilverPop::getAccessToken(): " . var_export($response, true));
					$access_token_info = json_decode($response, true);
					error_log("Generating new access token ..... access_token_info in SilverPop::getAccessToken(): " . var_export($access_token_info, true));
					$access_token = $access_token_info['access_token'];
					$expires_in = $access_token_info['expires_in'];
					
					if(!empty($access_token))
					{
						// Store this in Cookie
						setcookie("sp_access_token_'" . $company_id, $access_token, time() + $expires_in, "/");
					
						// And in the companies table
						SilverPop::updateCompanyAccessTokenInfo($company_id, $access_token, $expires_in);
					}
				}
			}
			
			
			return $access_token;
		}
		
		
		public static function submitEvent($company_id, $event_type_code, $attributes)
		{
			$access_token = SilverPop::getAccessToken($company_id); // '95a74ea8-a877-4deb-9f3b-220620346979';
			$data = array(
				'events' => array(
					0 => array(
						'eventTypeCode' 	=> $event_type_code,
						'eventTimestamp' 	=> date(DATE_ATOM),
						'attributes' 		=> $attributes,
					),
				)
			);
			$data_string = json_encode($data);
			error_log('data_string in SilverPop::submitEvent(): ' . $data_string);
			
			$baseurl = SilverPop::getBaseURL($company_id);
			$url = "https://" . $baseurl . "/rest/events/submission";
			$ch = curl_init($url);
			
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($data_string),
					'Authorization: Bearer '.$access_token
				)
			);                                                                                                                   
 
			$response = curl_exec($ch);
			curl_close($ch);
			$response_data = json_decode($response, true);
			error_log("response_data in SilverPop::submitEvent(): " . var_export($response_data, true));
		}
		
		public static function getSPCredentials($company_id)
		{
			$sql = "select display_name, sp_app_name, sp_client_id, sp_client_secret, sp_refresh_token, sp_username, sp_password, sp_api_host, sp_list_id, sp_contact_list_id, sp_contact_interests_id, sp_contact_likes_id, sp_contact_shares_id, sp_contact_qr_codes_id, sp_user_mapped_columns, sp_access_token, sp_access_token_expire_time, now() as curtime, sp_endpoint from companies where id = '". Database::mysqli_real_escape_string($company_id). "'";
			$row = BasicDataObject::getDataRow($sql);
			$row['access_token']	= null;
			if(!empty($row['sp_access_token_expire_time']))
			{
				if(strtotime($row['sp_access_token_expire_time']) > strtotime($row['curtime']))
				{
					$row['access_token'] = $row['sp_access_token'];
				}
			}
			
			return $row;
		}
		
		public static function getSPInfo($company_id)
		{
			$sql = "select sp_access_token, sp_access_token_expire_time, now() as curtime, sp_endpoint as sp_endpoint from companies where id = '$company_id'";
			$row = BasicDataObject::getDataRow($sql);
			$access_token	= null;
			if(!empty($row['sp_access_token_expire_time']))
			{
				if(strtotime($row['sp_access_token_expire_time']) > strtotime($row['curtime']))
				{
					$access_token = $row['sp_access_token'];
				}
			}
			$sp_endpoint	= empty($row['sp_endpoint']) && $row['sp_endpoint'] != '0' ? 'pilot' : $row['sp_endpoint'];
			return array($sp_endpoint, $access_token);
		}
		
		public static function getSPEventTimeStamp($str_date = null)
		{
			if(empty($str_date))
				$str_date = Common::getDBCurrentDate(); // date('Y-m-d H:i:s');
				
			$timestampVal = strtotime($str_date . ' UTC');
			$str_date = date('Y-m-d', $timestampVal) . 'T'. date('H:i:s', $timestampVal);
			$comp = explode(' ', microtime());
			$str_micro_sec = "." . str_pad(round($comp[0] * 1000), 3, '0', STR_PAD_LEFT) . "Z";
			$event_time_stamp = $str_date . $str_micro_sec;
			return $event_time_stamp;
		}
		
		public static function executeAPICallUsingJSON($event_type_code, $company_id, $sp_contact_id, $key_attributes, $index_attributes, $extra_attributes = null)
		{
			list($sp_endpoint, $access_token) = SilverPop::getSPInfo($company_id);
			// $company_id = 19;
			if(empty($access_token))
				$access_token = Silverpop::getAccessToken($company_id);
			
			$event_time_stamp = SilverPop::getSPEventTimeStamp();
			// $event_time_stamp = "2014-01-21T11:13:43.000Z";
			error_log("access_token in SilverPop::executeAPICallUsingJSON(): " . $access_token .", sp_endpoint: " . $sp_endpoint);
			/*
			// Create a request object in PHP
			$requestPHP = array(
				'events' => array(
					array(
						'eventTypeCode' => $event_type_code,
						'eventTimestamp' => $event_time_stamp,
						'attributes' => array(
						),
					),
				),
			);
			// Checking if swap date has been reached
			$current_timestamp_val = strtotime(date('Y-m-d H:i:s'). ' UTC');
			$date_swap_timestamp_val = strtotime(Silverpop::$date_swap_new_method. ' UTC');
			// if($sp_endpoint == 'pilot')
			if($current_timestamp_val >= $date_swap_timestamp_val)
			{
				if(!empty($sp_contact_id))
				{
					$requestPHP = array(
						'events' => array(
							array(
								'eventTypeCode' => $event_type_code,
								'eventTimestamp' => $event_time_stamp,
								'contactId' => $sp_contact_id,
								'attributes' => array(
								),
							),
						),
					);
				}
			}
			*/
			
			$arr_events = array(
				'eventTypeCode' => $event_type_code,
				'eventTimestamp' => $event_time_stamp,
			);
			// if($sp_endpoint == 'pilot')
			// Checking if swap date has been reached
			$current_timestamp_val = strtotime(date('Y-m-d H:i:s'). ' UTC');
			$date_swap_timestamp_val = strtotime(Silverpop::$date_swap_new_method. ' UTC');
			// if($current_timestamp_val >= $date_swap_timestamp_val)
			$arr_events['contactId'] = $sp_contact_id;
			$arr_events['attributes'] = array();
			
			$requestPHP = array('events' => array($arr_events));
			
			if(!empty($key_attributes))
			{
				foreach($key_attributes as $name_value_pair)
				{
					$requestPHP['events'][0]['attributes'][] = $name_value_pair;
				}
				// $requestPHP['events'][0]['attributes'] = array_merge($requestPHP['events'][0]['attributes'], $key_attributes);
			}
			if(!empty($index_attributes))
			{
				foreach($index_attributes as $name_value_pair)
				{
					$requestPHP['events'][0]['attributes'][] = $name_value_pair;
				}
				//$requestPHP['events'][0]['attributes'] = array_merge($requestPHP['events'][0]['attributes'], $index_attributes);
			}
				
			if(!empty($extra_attributes))
			{
				foreach($extra_attributes as $name_value_pair)
				{
					$requestPHP['events'][0]['attributes'][] = $name_value_pair;
				}
				// $requestPHP['events'][0]['attributes'] = array_merge($requestPHP['events'][0]['attributes'], $extra_attributes);
			}
			
			/*
			$requestJSON = '{"events":[{"eventTypeCode":"' . $event_type_code . '","eventTimestamp":"' . $event_time_stamp . '","attributes":[{ "name":"Silverpop Contact Id","value":"' . $sp_contact_id . '"}';
			if(!empty($coupsmart_contact_id))
				$requestJSON .= ',{"name":"Coupsmart Contact Id","value":"' . $coupsmart_contact_id . '"}';
			$requestJSON .=  ']}]}';*/
			
			
			$requestJSON = json_encode($requestPHP);
			// error_log("requestJSON in SilverPop::executeAPICallUsingJSON(): " . var_export($requestJSON, true));
			
			// error_log("requestJSON after json encoding in SilverPop::executeAPICallUsingJSON(): " . var_export($requestJSON, true));

			$url = "https://api" . $sp_endpoint . ".silverpop.com/rest/events/submission";

			$curl = curl_init();			
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $requestJSON);
			curl_setopt($curl, CURLINFO_HEADER_OUT, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=UTF-8', 
				'Content-Length: ' . strlen($requestJSON),
				'Authorization: Bearer '.$access_token,
			));
			curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt($curl, CURLOPT_TIMEOUT, 180);
		
			$responseXml = @curl_exec($curl);
			curl_close($curl);
			// error_log("responseXml in SilverPop::executeAPICallUsingJSON(): " . var_export($responseXml, true));
			/*Common::log_error(__FILE__, __LINE__, 'request, response',
					json_encode(array(
						'request' => $requestJSON,
						'response' => $responseXml,
					)), "SP request and response when triggering an event.");*/
			
			// SilverPop::getErrors($access_token, $company_id);
		}
		
		public static function getErrors($access_token, $company_id)
		{
			$baseurl = SilverPop::getBaseURL($company_id);
			$url = "https://" . $baseurl . "/rest/events/errors";
			error_log("url in SilverPop::getErrors(): " . $url);
			
			$opts = array('http' =>
				array(
					'method'  => 'GET',
					'header'  => 'Authorization: Bearer '.$access_token,
				)
			);
			$context  = stream_context_create($opts);
			$response = file_get_contents($url, false, $context);
			$response_data = json_decode($response, true);
			// error_log("response_data in SilverPop::getErrors(): " . var_export($response_data, true));
			if(!empty($response_data['data']) && is_array($response_data['data']))
			{
				// Common::log_error(__FILE__, __LINE__, 'SP UB error',
				// json_encode($response_data['data']), "SP errors when triggering an event.");
			}
		}
		
		public static function getSilverPopInfo($silver_pop_click_id)
		{
			$sql = "select * from silver_pop_clicks where id = '$silver_pop_click_id'";
			$row = BasicDataObject::getDataRow($sql);
			return $row;
		}
		
		public static function isSilverPopCompany($company_id)
		{
			$sql = "select is_silverpop_company from companies where id = '" . Database::mysqli_real_escape_string($company_id) . "'";
			$row = BasicDataObject::getDataRow($sql);
			$res = !empty($row['is_silverpop_company']);
			return $res;
		}
		
		public static function getIsSPCompanyInfoByItemId($item_id)
		{
			$sql = "select c.is_silverpop_company, c.sp_is_ubx, c.is_et_company, c.is_mailchimp_company, c.mc_list_id, c.mc_api_key, c.id as company_id, c.is_campaign_monitor_company, c.cm_client_id, c.cm_api_key, d.cm_list_id, d.cm_list_name, i.deal_id
			from items i 
			inner join deals d on i.deal_id = d.id
			inner join companies c on i.manufacturer_id = c.id where i.id = '" . Database::mysqli_real_escape_string($item_id) . "'";
			$row = BasicDataObject::getDataRow($sql);
			return $row;
		}
		
		//	Checks if the company_id is contained in a silver-pop URL
		//	UPSERTs (i.e. inserts/updates) a new/existing silverpop contact
		public static function checkAndUpsertContact($user_id, $company_id = null)
		{
			$sp_recipient_id = null;
			// if(SilverPop::isSilverPopCompany($company_id))
			// {
			if(!empty($user_id))
			{
				// UPSERT the recipient to the Silverpop database
				$user = new User($user_id);

				if(!empty($user->email))
				{
					// I've removed this check since there might be a case where the contact 
					// somehow gets manually removed from the silverpop database. 
					// This may lead to a serious data inconsistency issue!
					
					// if(empty($user->sp_recipient_id))
					// {
						$sp_recipient_id = Silverpop::addRecipient($user, $company_id);

						// Update the new recipient id in the Users Table
						if(!empty($sp_recipient_id))
						{
							// $tmp_user = new User();
							// $tmp_user->id = $user_id;
							// $tmp_user->sp_recipient_id = $sp_recipient_id;
							// $tmp_user->Update();
							$update_sql = "update users set sp_recipient_id = '$sp_recipient_id' where id = '$user_id'";
							Database::mysqli_query($update_sql);
							
						}
						
					// }
					// else
					// {
					//	$sp_recipient_id = $user->sp_recipient_id;
					// }
				}
			}
			// }
			return $sp_recipient_id;
		}
		
		
		public static function addRecipient($user, $company_id, $add_interests = true, $add_likes = true, $add_shares = true, $opt_in_date = null, $date_modified = null)
		{
			$email = $user->email;
			$user_id = $user->id;
			
			$sp_credentials = SilverPop::getSPCredentials($company_id);
			$sp_app_name 		= $sp_credentials['sp_app_name'];
			$sp_client_id 		= $sp_credentials['sp_client_id']; 
			$sp_client_secret 	= $sp_credentials['sp_client_secret'];
			$sp_refresh_token 	= $sp_credentials['sp_refresh_token'];
			$sp_username 		= $sp_credentials['sp_username'];
			$sp_password 		= $sp_credentials['sp_password'];
			$sp_api_host 		= $sp_credentials['sp_api_host'];
			$sp_list_id 		= $sp_credentials['sp_list_id'];
			$sp_contact_list_id 		= $sp_credentials['sp_contact_list_id'];
			$sp_contact_interests_id = $sp_credentials['sp_contact_interests_id'];
			$sp_contact_likes_id = $sp_credentials['sp_contact_likes_id'];
			$sp_contact_shares_id = $sp_credentials['sp_contact_shares_id'];
			$sp_user_mapped_columns = $sp_credentials['sp_user_mapped_columns'];
			
			try {
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
	
				// Adding a new Contact
				list($list_id, $created_from, $data, $send_auto_reply, $update_if_found, $allow_html, 
					$visitor_key, $contact_list_ids, $sync_fields) = array(
					$sp_list_id, 	//	Silverpop Database ID
					1, 				// CREATED_FROM
					array(			// COLUMNS / DATA
						'EMAIL' 		=> $email,
					),
					null,		//	SEND_AUTO_REPLY
					true,		//	UPDATE IF FOUND
					true,		//	ALLOW_HTML
					null,		//	VISITOR_KEY
					!empty($sp_contact_list_id) ? array($sp_contact_list_id) : null,		//	CONTACT_LIST_IDs
					array(		// SYNC FIELDS
						'EMAIL' 		=> $email,
					),
				);
				if(!empty($opt_in_date))
				{
					// $data['Opted In'] = SilverPop::getSPEventTimeStamp($opt_in_date);
						$data['Opt In Date'] = date('m/d/y h:i:s A', strtotime($opt_in_date . ' UTC'));
				}
				
				if(!empty($date_modified))
				{
					// $data['Last Modified'] = SilverPop::getSPEventTimeStamp($date_modified);
					$data['Last Modified Date'] = date('m/d/y h:i:s A', strtotime($date_modified . ' UTC'));
				}
				
				$arr_sp_user_mapped_columns = unserialize($sp_user_mapped_columns);
				// error_log('arr_sp_user_mapped_columns: ' . var_export($arr_sp_user_mapped_columns, true));
				foreach($arr_sp_user_mapped_columns as $sp_col_name => $col_info)
				{
					$sp_col_type = $col_info['sp_col_type'];
					$user_col_name = $col_info['user_col_name'];
					$op = !empty($col_info['op']) ? $col_info['op'] : '';
					
					// error_log('col_info: ' . var_export($col_info, true));
					switch($sp_col_type)
					{
						case 'Date':
							$data[$sp_col_name] = date('m/d/Y', strtotime($user->{$user_col_name} . ' UTC'));
							break;
							
						default:
							switch($op)
							{
								case 'concat':
									$arr_sp_col_data = array();
									$arr_col_names = explode(',', $user_col_name);
									foreach($arr_col_names as $col_name)
									{
										if(!empty($user->{$col_name}))
											$arr_sp_col_data[] = $user->{$col_name};
									}
									$data[$sp_col_name] = implode(',', $arr_sp_col_data);
									break;
								
								case 'split':
									$arr_col_info = explode(',', $user_col_name);
									$user_col_val = $user->{trim($arr_col_info[0])};
									$col_index = trim($arr_col_info[1]);
									$arr_user_col_val = explode(',', $user_col_val);
									$data[$sp_col_name] = trim($arr_user_col_val[$col_index]);
									break;
								
								case 'age':
									if(!empty($user->{$user_col_name}) && $user->{$user_col_name} != '0000-00-00')
										$data[$sp_col_name] = Common::calculateAgeFromBirthday($user->{$user_col_name});			
									break;
								
								case 'gender':
									$gender = strtolower($user->{$user_col_name});
									$data[$sp_col_name] = ($gender == 'm' ? 'Male' :
															($gender == 'f' ? 'Female' : ''));
									break;
									
								case 'format_date':
									$data[$sp_col_name] = date('m/d/Y', strtotime($user->{$user_col_name} . ' UTC'));
									break;
									
								default:
									$data[$sp_col_name] = $user->{$user_col_name};
									break;
							}
							break;
					}
				}
				
				
				// error_log('data in SilverPop::addRecipient(): ' . var_export($data, true));

				$engage->login($sp_username, $sp_password);
				$recipient_id = $engage->addContact($list_id, $created_from, $data, $send_auto_reply, $update_if_found, $allow_html, $visitor_key, $contact_list_ids, $sync_fields);
				
				// Adding Contact Interests
				if($add_interests && !empty($sp_contact_interests_id))
				{
					list($user_fb_interests, $interests_to_delete) = User::getUserFBInterests($user_id, $user->email);
					// Delete any interests that have been removed from facebook
					if(!empty($interests_to_delete))
						$engage->deleteRelationalTableData($sp_contact_interests_id, $interests_to_delete, 'Email', 'Interest');
						
					$engage->insertUpdateRelationalTableData($sp_contact_interests_id, $user_fb_interests);
				}
				
				// Adding Contact Likes
				if($add_likes && !empty($sp_contact_likes_id))
				{
					list($user_fb_likes, $likes_to_delete) = User::getUserFBLikes($user_id, $user->email);
					// Delete any likes that have been removed from facebook
					if(!empty($likes_to_delete))
						$engage->deleteRelationalTableData($sp_contact_likes_id, $likes_to_delete, 'Email', 'Like');
						
					$engage->insertUpdateRelationalTableData($sp_contact_likes_id, $user_fb_likes);
				}
				
				if($add_shares && !empty($sp_contact_shares_id))
				{
					$user_num_shares = User::getItemsShared($company_id, $user->facebook_id, $user->email);
					$engage->insertUpdateRelationalTableData($sp_contact_shares_id, $user_num_shares);
				}
				
				$engage->logout();
				
				return $recipient_id;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
				
				Common::log_error(__FILE__, __LINE__, "Error: SP request and response when upserting a contact.",
				json_encode(array(
					'request' => $engage->getLastRequest(),
					'response' => $engage->getLastResponse(),
					'fault' => $engage->getLastFault(),
				)), 'Error adding the contact to SP having id: ' . $user_id. ', email: ' . $email);
			}
		}
		
		public static function addQRCodesRow($qr_code_row, $company_id, $sp_email)
		{
			try {
				$sp_credentials = SilverPop::getSPCredentials($company_id);
				$sp_app_name 		= $sp_credentials['sp_app_name'];
				$sp_client_id 		= $sp_credentials['sp_client_id']; 
				$sp_client_secret 	= $sp_credentials['sp_client_secret'];
				$sp_refresh_token 	= $sp_credentials['sp_refresh_token'];
				$sp_username 		= $sp_credentials['sp_username'];
				$sp_password 		= $sp_credentials['sp_password'];
				$sp_api_host 		= $sp_credentials['sp_api_host'];
				$sp_list_id 		= $sp_credentials['sp_list_id'];
				$sp_contact_qr_codes_id = $sp_credentials['sp_contact_qr_codes_id'];
			
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);
				$contact_details = $engage->getContactDetailsByEmail($sp_email, $sp_list_id);
				$contact_id = $contact_details['RESULT']['RecipientId'];
				$qr_code_row['ContactId'] = $contact_id;
				$result = $engage->insertUpdateRelationalTableRow($sp_contact_qr_codes_id, $qr_code_row);
				$engage->logout();

				return $result ? $contact_id : $result;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		}
		
		public static function updateQRCodesRow($qr_code_row, $company_id)
		{
			try {
				$sp_credentials = SilverPop::getSPCredentials($company_id);
				$sp_username 		= $sp_credentials['sp_username'];
				$sp_password 		= $sp_credentials['sp_password'];
				$sp_api_host 		= $sp_credentials['sp_api_host'];
				$sp_list_id 		= $sp_credentials['sp_list_id'];
				$sp_contact_qr_codes_id = $sp_credentials['sp_contact_qr_codes_id'];
			
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);
				
				$result = $engage->insertUpdateRelationalTableRow($sp_contact_qr_codes_id, $qr_code_row);
				$engage->logout();

				return $result;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		}
		
		public static function getContactIdByEmail($sp_email, $company_id)
		{
			try {
				$sp_credentials = SilverPop::getSPCredentials($company_id);
				$sp_app_name 		= $sp_credentials['sp_app_name'];
				$sp_client_id 		= $sp_credentials['sp_client_id']; 
				$sp_client_secret 	= $sp_credentials['sp_client_secret'];
				$sp_refresh_token 	= $sp_credentials['sp_refresh_token'];
				$sp_username 		= $sp_credentials['sp_username'];
				$sp_password 		= $sp_credentials['sp_password'];
				$sp_api_host 		= $sp_credentials['sp_api_host'];
				$sp_list_id 		= $sp_credentials['sp_list_id'];
				$sp_contact_qr_codes_id = $sp_credentials['sp_contact_qr_codes_id'];
			
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);
				$contact_details = $engage->getContactDetailsByEmail($sp_email, $sp_list_id);
				$contact_id = $contact_details['RESULT']['RecipientId'];
				$engage->logout();

				return $contact_id;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		}
		
		public static function checkAndAddRelationalTables($company_id)
		{
			$messages = array();
			
			//	1.	Get Silverpop credentials
			$sp_credentials = SilverPop::getSPCredentials($company_id);
			$sp_app_name 		= $sp_credentials['sp_app_name'];
			$sp_client_id 		= $sp_credentials['sp_client_id']; 
			$sp_client_secret 	= $sp_credentials['sp_client_secret'];
			$sp_refresh_token 	= $sp_credentials['sp_refresh_token'];
			$sp_username 		= $sp_credentials['sp_username'];
			$sp_password 		= $sp_credentials['sp_password'];
			$sp_api_host 		= $sp_credentials['sp_api_host'];
			$sp_list_id 		= $sp_credentials['sp_list_id'];

				//	2.	Login to Engage
			try {
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);

				// 	3.	Get a list of relational tables
				$lists = $engage->getLists(1, 15); // 1 stands for shared, 15 stands for relational tables
			
				//	4.	Store IDs and Names of the relational tables
				$arr_relational_tables = array();
				foreach($lists as $i => $list)
				{
					$arr_relational_tables[] = $list['NAME'];
				}
				// error_log("arr_relational_tables in SilverPop::checkAndAddRelationalTables(): " . var_export($arr_relational_tables, true));
			
				$map_fields = array(
					array(
						'LIST_FIELD' => 'email',
						'TABLE_FIELD' => 'Email',
					),
				);
				
				$columns = array(
					array(
						'NAME' => 'Email',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => '', // Interest or Like
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'Created',
						'TYPE' => 'DATE_TIME',
						'IS_REQUIRED' => 'false',
					),
				);
				
			
				//	5.	If Contact Interests does not exist
				$table_name = 'Contact Interests'; // Or Contact Likes
				if(!in_array($table_name, $arr_relational_tables))
				{
					$columns[1]['NAME'] = 'Interest';
					
					//		i.	Add it
					$sp_contact_interests_id = $engage->createTable($table_name, $columns);
					
					if(!empty($sp_contact_interests_id))
					{
						$messages[] = array('green' => 'Successfully created the Contact Interests table');
						
						//		ii.	Update ID in database
						$company = new Company();
						$company->id = $company_id;
						$company->sp_contact_interests_id = $sp_contact_interests_id;
						$company->Update();
					
						//	iii. Join with Primary table
						$res = $engage->joinTable($map_fields, null, null, $sp_contact_interests_id, $sp_list_id, 1, 1);
						if($res)
						{
							$messages[] = array('green' => 'Successfully associated the Contact Interests table with the Marketing database');
						}
						else
						{
							$messages[] = array('red' => 'Failed to associate the Contact Interests table with the Marketing database');
						}
					}
					else
					{
						$messages[] = array('red' => "Failed to create the Contact Interests table");
					}
				}
				else
				{
					$messages[] = array('orange' => "Contact Interests table already exists.");
				}

				//	6.	If Contact Likes does not exist
				$table_name = 'Contact Likes'; // Or Contact Likes
				if(!in_array($table_name, $arr_relational_tables))
				{
					$columns[1]['NAME'] = 'Like';
					
					//		i.	Add it
					$sp_contact_likes_id = $engage->createTable($table_name, $columns);
					if(!empty($sp_contact_likes_id))
					{
						$messages[] = array('green' => 'Successfully created the Contact Likes table');
						
						//		ii.	Update ID in database
						$company = new Company();
						$company->id = $company_id;
						$company->sp_contact_likes_id = $sp_contact_likes_id;
						$company->Update();
					
						//	iii. Join with Primary table
						$res = $engage->joinTable($map_fields, null, null, $sp_contact_likes_id, $sp_list_id, 1, 1);
						if($res)
						{
							$messages[] = array('green' => 'Successfully associated the Contact Likes table with the Marketing database');
						}
						else
						{
							$messages[] = array('red' => 'Failed to associate the Contact Likes table with the Marketing database');
						}
						
					}
					else
					{
						$messages[] = array('red' => "Failed to create the Contact Likes table");
					}
				}
				else
				{
					$messages[] = array('orange' => "Contact Likes table already exists.");
				}
				
				//	7.	If Contact Referrals did not Exist
				$columns = array(
					array(
						'NAME' => 'Email',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'ItemId',
						'TYPE' => 'NUMERIC',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'Campaign',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'NumShares',
						'TYPE' => 'NUMERIC',
						'IS_REQUIRED' => 'false',
					),
				);
				$table_name = 'Contact Shares';
				if(!in_array($table_name, $arr_relational_tables))
				{					
					//		i.	Add it
					$sp_contact_shares_id = $engage->createTable($table_name, $columns);
					if(!empty($sp_contact_shares_id))
					{
						$messages[] = array('green' => 'Successfully created the Contact Shares table');
						
						//		ii.	Update ID in database
						$company = new Company();
						$company->id = $company_id;
						$company->sp_contact_shares_id = $sp_contact_shares_id;
						$company->Update();
					
						//	iii. Join with Primary table
						$res = $engage->joinTable($map_fields, null, null, $sp_contact_shares_id, $sp_list_id, 1, 1);
						if($res)
						{
							$messages[] = array('green' => 'Successfully associated the Contact Shares table with the Marketing database');
						}
						else
						{
							$messages[] = array('red' => 'Failed to associate the Contact Shares table with the Marketing database');
						}
						
					}
					else
					{
						$messages[] = array('red' => "Failed to create the Contact Shares table");
					}
				}
				else
				{
					$messages[] = array('orange' => "Contact Shares table already exists.");
				}

				//	8.	Logout of Engage
				$engage->logout();
				
				
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
				
				$messages[] = array('red' => $e->getMessage());
			}
			return $messages;
		}
		
		public static function addContactLikesRelationalTable($company_id, $table_name)
		{
			$sp_credentials = SilverPop::getSPCredentials($company_id);

			$sp_username 		= $sp_credentials['sp_username'];
			$sp_password 		= $sp_credentials['sp_password'];
			$sp_api_host 		= $sp_credentials['sp_api_host'];
			$sp_list_id 		= $sp_credentials['sp_list_id'];

			// error_log("sp_credentials: " . var_export($sp_credentials, true));
			// exit();
			try {
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);
					
				$columns = array(
					array(
						'NAME' => 'Email',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'Like', // Interest or Like
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'Created',
						'TYPE' => 'DATE_TIME',
						'IS_REQUIRED' => 'false',
					),
				);
				
				$table_id = $engage->createTable($table_name, $columns);
				if(!empty($table_id))
				{
					$sql = "update companies set sp_contact_likes_id = '$table_id' where id = '$company_id'";
					Database::mysqli_query($sql);
				}

				$engage->logout();
				return $table_id;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		}
		
		public static function addContactSharesRelationalTable($company_id, $table_name)
		{
			$sp_credentials = SilverPop::getSPCredentials($company_id);

			$sp_username 		= $sp_credentials['sp_username'];
			$sp_password 		= $sp_credentials['sp_password'];
			$sp_api_host 		= $sp_credentials['sp_api_host'];
			$sp_list_id 		= $sp_credentials['sp_list_id'];

			// error_log("sp_credentials: " . var_export($sp_credentials, true));
			// exit();
			try {
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);
					
				$columns = array(
					array(
						'NAME' => 'Email',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'ItemId',
						'TYPE' => 'NUMERIC',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'Campaign',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'NumShares',
						'TYPE' => 'NUMERIC',
						'IS_REQUIRED' => 'false',
					),
				);
				
				$table_id = $engage->createTable($table_name, $columns);
				if(!empty($table_id))
				{
					$sql = "update companies set sp_contact_shares_id = '$table_id' where id = '$company_id'";
					Database::mysqli_query($sql);
				}

				$engage->logout();
				return $table_id;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		}
		
		public static function deleteEmailCodesRelationalTable($company_id)
		{
			$sp_credentials = SilverPop::getSPCredentials($company_id);

			$sp_username 		= $sp_credentials['sp_username'];
			$sp_password 		= $sp_credentials['sp_password'];
			$sp_api_host 		= $sp_credentials['sp_api_host'];
			$sp_list_id 		= $sp_credentials['sp_list_id'];
			$sp_contact_qr_codes_id = $sp_credentials['sp_contact_qr_codes_id'];

			// error_log("sp_credentials: " . var_export($sp_credentials, true));
			// exit();
			try {
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				
				$table_visibility = 1;
				$engage->login($sp_username, $sp_password);
				
				$engage->joinTable(array(), null, null, $sp_contact_qr_codes_id, $sp_list_id, 1, 1, true); // true is for remove
				$engage->deleteTable("", $sp_contact_qr_codes_id, $table_visibility);
				
				$sql = "update companies set sp_contact_qr_codes_id = null where id = '$company_id'";
				Database::mysqli_query($sql);

				$engage->logout();
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		
		}
		
		public static function addEmailCodesTable($company_id, $table_name)
		{
			$sp_credentials = SilverPop::getSPCredentials($company_id);

			$sp_username 		= $sp_credentials['sp_username'];
			$sp_password 		= $sp_credentials['sp_password'];
			$sp_api_host 		= $sp_credentials['sp_api_host'];
			$sp_list_id 		= $sp_credentials['sp_list_id'];
			$sp_contact_qr_codes_id = $sp_credentials['sp_contact_qr_codes_id'];

			// error_log("sp_credentials: " . var_export($sp_credentials, true));
			// exit();
			try {
				// error_log("Logging into Engage API on {$sp_api_host} as {$sp_username}\n");
				$engage = new Engage($sp_api_host);
				$engage->login($sp_username, $sp_password);
				$columns = array(
					array(
						'NAME' => 'Email_Coupon',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
					),
					array(
						'NAME' => 'ContactId',
						'TYPE' => 'NUMERIC',
						'IS_REQUIRED' => 'true',
					),
					array(
						'NAME' => 'MailingId_Coupon',
						'TYPE' => 'NUMERIC',
					),
					array(
						'NAME' => 'MailingName',
						'TYPE' => 'TEXT',
					),
					array(
						'NAME' => 'UniqueCode',
						'TYPE' => 'TEXT',
						'IS_REQUIRED' => 'true',
						'KEY_COLUMN' => 'true',
					),
					array(
						'NAME' => 'DateClaimed',
						'TYPE' => 'DATE',
					),
					array(
						'NAME' => 'DateRedeemed',
						'TYPE' => 'DATE',
					),
					array(
						'NAME' => 'Created',
						'TYPE' => 'DATE',
					),
				);
	
				$map_fields = array(
					array(
						'LIST_FIELD' => 'email',
						'TABLE_FIELD' => 'Email_Coupon',
					),
				);
				$table_visibility = 1;
				$table_id = $engage->createTable($table_name, $columns);
				// error_log("table_id of new relational table $table_name: " . $table_id);
				$engage->joinTable($map_fields, null, null, $table_id, $sp_list_id, 1, 1, false); // true is for remove
				
				$sql = "update companies set sp_contact_qr_codes_id = '$table_id' where id = '$company_id'";
				Database::mysqli_query($sql);

				$engage->logout();
				return $table_id;
			}
			catch (Exception $e) {
				error_log($e->getMessage() . "\n"); 
				error_log("Last Request: " . var_export($engage->getLastRequest(), true)); 
				error_log("Last Response: " . var_export($engage->getLastResponse(), true));
				error_log("Last Fault: " . var_export($engage->getLastFault(), true));
			}
		}
		
		public static function getCodeSnippetByUniqueCode($unique_code, $integration_type = 'sp')
		{
			$table_name = $integration_type . '_companies_code_snippets';
			$sql = "select * from $table_name where unique_code = '" .Database::mysqli_real_escape_string($unique_code). "'";
			$row = BasicDataObject::getDataRow($sql);
			return $row;
		}
		
		public static function getUserQRCodeRow($qr_code_text)
		{
			$sql = "select * from sp_users_qr_codes where qr_code_text = '" . Database::mysqli_real_escape_string($qr_code_text) . "'";
			return BasicDataObject::getDataRow($sql);
		}
		
		public static function getUsedQRCode($sp_email, $sp_mailing_id, $company_id)
		{
			$sql = "select * from sp_users_qr_codes where email = '$sp_email' and sp_mailing_id = '$sp_mailing_id' and date_claimed is not null and company_id = '$company_id'";
			return BasicDataObject::getDataRow($sql);
		}
		
		
		public static function getLastUnusedQRCode($company_id)
		{
			$sql = "select * from sp_users_qr_codes where date_claimed is null and company_id = '$company_id' order by id limit 1";
			return BasicDataObject::getDataRow($sql);
		}
		
		public static function updateLastUnusedQRCodeEntry($sp_code_snippet_id, $sp_email, $sp_contact_id_unencoded, $sp_mailing_id, $sp_mailing_name, $last_unused_code_id)
		{
			$sql = "update sp_users_qr_codes set sp_code_snippet_id = '$sp_code_snippet_id', sp_recipient_id = '$sp_contact_id_unencoded', email = '$sp_email', sp_mailing_id = '$sp_mailing_id', sp_mailing_name = '$sp_mailing_name', date_claimed = now() where id = '$last_unused_code_id'";
			if(!Database::mysqli_query($sql))
				error_log("Update SQL error in SilverPop::updateLastUnusedQRCodeEntry(): " . Database::mysqli_error() . "\nSQL: " . $sql);
		}
		
		public static function redeemQRCodeProcess($sp_email, $sp_contact_id, $sp_mailing_id, $company_id)
		{
			$sp_qr_code_row = array(
				// 'ContactId' =>  $sp_contact_id_unencoded,
				'DateRedeemed' => SilverPop::getSPEventTimeStamp(),
				'Email1' => $sp_email,
				'MailingId1' => $sp_mailing_id,
			);
			// error_log("calling SilverPop::addQRCodesRow()...");
			$sp_contact_id = SilverPop::addQRCodesRow($sp_qr_code_row, $company_id, $sp_email);
			
			$sql = "select * from sp_users_qr_codes where sp_recipient_id = '$sp_contact_id' and email = '$sp_email' and sp_mailing_id = '$sp_mailing_id'";
			$row = BasicDataObject::getDataRow($sql);
			$qr_code_id = $row['id'];
			$sp_mailing_name = $row['sp_mailing_name'];
			$qr_code_text = $row['qr_code_text'];
			
			$sql = "update sp_users_qr_codes set date_redeemed = now() where id = '" . $qr_code_id. "'";
			Database::mysqli_query($sql);
			
			SilverPop::triggerRedeemedQRCode($company_id, $sp_contact_id, $qr_code_id, $qr_code_text, $sp_mailing_id, $sp_mailing_name);
			
			
		}
		
		public static function redeemQRCodeByQRCodeText($qr_code_text)
		{
			$sql = "select * from sp_users_qr_codes where qr_code_text = '$qr_code_text'";
			$row = BasicDataObject::getDataRow($sql);
			
			if(!empty($row['date_claimed']))
				throw new Exception("Error redeeming $qr_code_text: QR Code not yet claimed!");
				
			list($sp_email, $sp_contact_id, $sp_mailing_id, $company_id) = array($row['email'], $row['sp_recipient_id'], $row['sp_mailing_id'], $row['company_id']);
			$sp_qr_code_row = array(
				// 'ContactId' =>  $sp_contact_id_unencoded,
				'DateRedeemed' => SilverPop::getSPEventTimeStamp(),
				'Email1' => $sp_email,
				'MailingId1' => $sp_mailing_id,
			);
			// error_log("calling SilverPop::addQRCodesRow()...");
			$sp_contact_id = SilverPop::addQRCodesRow($sp_qr_code_row, $company_id, $sp_email);
			
			
			$qr_code_id = $row['id'];
			$sp_mailing_name = $row['sp_mailing_name'];
			$qr_code_text = $row['qr_code_text'];
			
			$sql = "update sp_users_qr_codes set date_redeemed = now() where id = '" . $qr_code_id. "'";
			Database::mysqli_query($sql);
			
			SilverPop::triggerRedeemedQRCode($company_id, $sp_contact_id, $qr_code_id, $qr_code_text, $sp_mailing_id, $sp_mailing_name);
			
			
		}
		
		public static function redeemQRCode($code_id)
		{
			$sql = "update sp_users_qr_codes set date_redeemed = now() where id = '$code_id'";
			if(!Database::mysqli_query($sql))
				error_log("Update SQL error in SilverPop::redeemQRCode(): " . Database::mysqli_error() . "\nSQL: " . $sql);
		}
		
		public static function checkAndGenerateUniqueCode($code_snippet_id, $email, $integration_type = 'sp')
		{
			$table_name = $integration_type . '_integration_codes';
			$sql = "select * from $table_name where code_snippet_id = '$code_snippet_id' and email = '$email'";
			$row = BasicDataObject::getDataRow($sql);
			if(!empty($row['id']))
			{
				$unique_code = $row['unique_code'];
			}
			else
			{
				do
				{
					$unique_code = sprintf("%011d", rand(0, 99999999999));
					// Never start our codes with 5 or 99, too easy to confuse POS systems
					while (substr($unique_code, 0, 1) == '5' || substr($unique_code, 0, 2) == '99') {
						$unique_code = sprintf("%011d", rand(0, 99999999999));
					}
					$insert = "insert into $table_name (code_snippet_id, email, unique_code) VALUES ('" . Database::mysqli_real_escape_string($code_snippet_id) . "', '" . Database::mysqli_real_escape_string($email) . "', '" . Database::mysqli_real_escape_string($unique_code) . "')";
					Database::mysqli_query($insert);
			
					if (Database::mysqli_error())
						error_log('SQL Insert error in SilverPop::checkAndGenerateUniqueCode(): mysql error ' . Database::mysqli_errno() . ' on ' . $table_name . ' insert: ' . Database::mysqli_error() . ' ---- Query was: ' . $insert);
					// error_log('mysql errno: ' . Database::mysqli_errno());
				}
				while(Database::mysqli_errno() == 1062);
			}
			return $unique_code;
			
		}
		
		
		public static function getContactInsight($company_id, $recipient_email)
		{
			list($sp_endpoint, $access_token) = SilverPop::getSPInfo($company_id);

			// if(empty($access_token))
			$access_token = Silverpop::getAccessToken($company_id);
			// error_log("sp_access_token in SilverPop::getContactInsight(): " . $access_token);
			$sp_credentials = SilverPop::getSPCredentials($company_id);
			$sp_list_id		= $sp_credentials['sp_list_id'];
			$sp_username 	= $sp_credentials['sp_username'];
			$sp_password 	= $sp_credentials['sp_password'];
			$sp_api_host 	= $sp_credentials['sp_api_host'];

			$engage = new Engage($sp_api_host);
			$engage->login($sp_username, $sp_password);
			$contact_details = $engage->getContactDetailsByEmail($recipient_email, $sp_list_id);
			$recipient_id = $contact_details['RESULT']['RecipientId'];
			$engage->logout();

			$url = "https://engage$sp_endpoint.silverpop.com/mashup/oauth/contact/contactInsight?recipientId=$recipient_id&listId=$sp_list_id&access_token=$access_token";
			// error_log("url in SilverPop::getContactInsight(): " . $url);
			$response = file_get_contents($url);
			// error_log("response in SilverPop::getContactInsight(): " . $response);
			
			// Return either the response or the URL itself
			return $response; // $url
		}
		
		public static function getSentMailings($company_id)
		{
			$sp_credentials = SilverPop::getSPCredentials($company_id);
			$sp_list_id		= $sp_credentials['sp_list_id'];
			$sp_username 	= $sp_credentials['sp_username'];
			$sp_password 	= $sp_credentials['sp_password'];
			$sp_api_host 	= $sp_credentials['sp_api_host'];
			
			$engage = new Engage($sp_api_host);
			$engage->login($sp_username, $sp_password);
			$start_date = '01/01/2000 00:00:00';
			$end_date = Common::getUTCDate(null, 'm/d/Y H:i:s');
			$mailings = $engage->getSentMailingsForUser($start_date, $end_date);
			// error_log('mailings in SilverPop::getSentMailings(): ' . var_export($mailings, true));
			
			$engage->logout();
			
			$num_mailings = 0;
			foreach($mailings['RESULT']['Mailing'] as $mailing)
			{
				$num_sent = $mailing['NumSent'];
				$num_mailings += $num_sent;
			}
			return $num_mailings;
		}
	}
?>
