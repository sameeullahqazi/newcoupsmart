<?php
	/*
	require_once(dirname(__DIR__) . '/classes/MobileESP.class.php');
	*/
	class UBX
	{
		protected $account = "coupsmart";
		protected $username = "coupsmart_admin";
		protected $password = "AADhM32MiTY65RoyI9rp";
		protected $url = "https://api-pilot.ubx.ibmmarketingcloud.com/v1"; // "https://pilot-ubx-api.adm01.com/v1";
		
		public static $event_type_code_viewed = 'viewedCoupon';
		public static $event_type_code_claimed = 'claimedCoupon';
		public static $event_type_code_redeemed = 'redeemedCoupon';
		
		public static $channel_social = 'Social';
		public static $channel_mobile = 'Mobile';
		public static $channel_web = 'Web';
		
		public function login()
		{
			
		}
		
		public function logout()
		{
		
		}
		
		public function executeUBXEndpointAPI($api_url, $name, $auth_key, $provider_name = 'Coupsmart', $description = '', $source = true, $destination = false, $url = null)
		{
			$event = array();
			if($source)
				$event['source'] = array(
					'enabled' => true,
				);
			
			if($destination)
				$event['destination'] = array(
					'enabled' => true,
				);	
			
			// $requestPHP = array();
			$params = array(
				'providerName' => $provider_name,
				'name' => $name,
				'description' => $description,
				'endpointTypes' => array(
					'event' => $event,
				),
				'marketingDatabasesDefinition' => array (
				  'marketingDatabases' => array (
					array (
					  'id' => 0,
					  'name' => 'Coupon Marketing',
					  'identifiers' => 
					  array (
						0 => 
						array (
						  'isRequired' => false,
						  'name' => 'email',
						  'type' => 'email',
						),
						1 => 
						array (
						  'isRequired' => false,
						  'name' => 'facebookId',
						  'type' => 'facebookid',
						),
						2 => 
						array (
						  'isRequired' => true,
						  'name' => 'coupsmartContactId',
						  'type' => 'coupsmartid',
						),
					  ),
					),
				  ),
				)
			);
			$data_json = json_encode($params);
			error_log("data_json in UBX::executeUBXEndpointAPI(): " . var_export($data_json, true));
			
			// error_log("data_json after json encoding in UBX::executeAPICallUsingJSON(): " . var_export($data_json, true));
			$op = "endpoint";
			$url = $api_url . "/" . $op; // $this->url . "/" . $op;
			error_log("URL: " . $url);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_json),
				'Authorization: Bearer '.$auth_key,
			));
			$response  = curl_exec($ch);
			curl_close($ch);
			
			
			error_log("response in UBX::executeUBXEndpointAPI(): " . var_export($response, true));
			return $response;
		}
		
		public function executeUBXEventtypeAPI($api_url, $code, $name, $auth_key, $description = '')
		{
			// $requestPHP = array();
			$params = array(
				'code' => $code,
				'name' => $name,
				'description' => $description,
			);
			$data_json = json_encode($params);
			error_log("data_json in UBX::executeUBXEventtypeAPI(): " . var_export($data_json, true));
			
			// error_log("data_json after json encoding in UBX::executeAPICallUsingJSON(): " . var_export($data_json, true));
			$op = "eventtype";
			$url = $api_url . "/" . $op; // $this->url . "/" . $op;
			error_log("URL: " . $url);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_json),
				'Authorization: Bearer '.$auth_key,
			));
			$response  = curl_exec($ch);
			curl_close($ch);
			
			
			error_log("response in UBX::executeUBXEventtypeAPI(): " . var_export($response, true));
			return $response;
		}
		
		public function executeUBXEventAPI($code, $identifiers, $multiple_event_attributes, $company_id, $channel)
		{
			$device = new MobileESP();
			
			$deviceType = "Desktop";
			if($device->DetectMobileLong())
				$deviceType = "Mobile";
				
			 if($device->DetectTierTablet())
			 	$deviceType = "Tablet";
			 
			 
		
			$ubx_credentials = UBX::getUBXCredentials($company_id);
			$auth_key = $ubx_credentials['sp_ubx_auth_key'];
			$api_url = $ubx_credentials['sp_ubx_api_url'];
			$timestamp = Common::getDBTimeStampISO8601();

			$events = array();
			foreach($multiple_event_attributes as $attributes)
			{
				$attributes[] = array(
					'name' => 'deviceType',
					'value' => $deviceType,
					'type' => 'string'
				);
				$events[] = array(
					'code' => $code,
					'timestamp' => $timestamp,
					'attributes' => $attributes
				);
			}
			
			$params = array(
				'channel' => $channel,
				'identifiers' => $identifiers,
				'events' => $events,
			);
			$data_json = json_encode($params);
			error_log("data_json in UBX::executeUBXMultipleEventsAPI(): " . var_export(Common::json_format($data_json), true));
			
			// error_log("data_json after json encoding in UBX::executeUBXMultipleEventsAPI(): " . var_export($data_json, true));
			$op = "event";
			$url = $api_url . "/" . $op; // $this->url . "/" . $op;
			error_log("URL: " . $url);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_json),
				'Authorization: Bearer '.$auth_key,
			));
			$response  = curl_exec($ch);
			curl_close($ch);
			
			$response = json_decode($response, true);
			error_log("response in UBX::executeUBXMultipleEventsAPI(): " . var_export($response, true));
			
			if(isset($response['message']))
			{
				Common::log_error(__FILE__, __LINE__, 'UBX Event Error', $data_json, $response['message']);
			}
			return $response;
		}
		
		public static function triggerViewedOffer($company_id, $channel, $user_id, $page_id, $facebook_page_name, $coupons)
		{
			$ubx = new UBX();
			$identifiers = UBX::getIdentifierAttributes($user_id);
			
			$attributes = array();
			
			foreach($coupons as $coupon)
			{
				$item_id = $coupon['id'];
				$item_name = $coupon['name'];
				$campaign_id = $coupon['campaign_id'];
				$campaign_name = $coupon['campaign_name'];
				if(count($attributes) == 0)	
					$attributes[] = array(
					array(
						'name' => 'facebookPageId',
						'value' => $page_id,
						'type' => 'string',
					),
					array(
						'name' => 'facebookPageName',
						'value' => $facebook_page_name,
						'type' => 'string',
					),
					/*array(
						'name' => 'itemId',
						'value' => $item_id,
						'type' => 'string', // number
					),
					array(
						'name' => 'campaignId',
						'value' => $campaign_id,
						'type' => 'string', // number
					),
					array(
						'name' => 'campaignName',
						'value' => $campaign_name,
						'type' => 'string',
					),
					array(
						'name' => 'itemName',
						'value' => $item_name,
						'type' => 'string',
					),*/
				);
			}
			$response = $ubx->executeUBXEventAPI(UBX::$event_type_code_viewed, $identifiers, $attributes, $company_id, $channel);
			return $response;
		}
		
		public static function triggerClaimedOffer($company_id, $channel, $user_id, $item_id, $claim_id, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			$ubx = new UBX();
			
			$identifiers = UBX::getIdentifierAttributes($user_id);
			$item = new Item($item_id);
			$channel = Common::getUBXChannelType($item->delivery_method);
			$attributes = array(
				array(
					'name' => 'campaignId',
					'value' => $item->campaign_id,
					'type' => 'string', // number
				),
				array(
					'name' => 'campaignName',
					'value' => $item->campaign_name,
					'type' => 'string',
				),
				array(
					'name' => 'itemName',
					'value' => $item->name,
					'type' => 'string',
				),
			);
			
			if(!empty($claim_id))
			{
				$attributes[] = array(
					'name' => 'claimId',
					'value' => $claim_id,
					'type' => 'string', // number
				);
			}
			
			/*
			if(!empty($item->small_type))
			{
				$attributes[] = array(
					'name' => 'itemDescription',
					'value' => $item->small_type,
					'type' => 'string', // number
				);
			}
			*/
			
			$item_img_url = Item::getItemImageUrl($item_id);
			if(!empty($item_img_url))
			{
				$attributes[] = array(
					'name' => 'itemImageUrl',
					'value' => $item_img_url,
					'type' => 'string',
				);
			}
			
			if(!empty($sp_mailing_id))
			{
				$attributes[] = array(
					'name' => 'mailingId',
					'value' => $sp_mailing_id,
					'type' => 'string', // number
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$attributes[] = array(
					'name' => 'mailingName',
					'value' => $sp_mailing_name,
					'type' => 'string',
				);
			}
			$attributes = array($attributes); // Attributes per Event
			$response = $ubx->executeUBXEventAPI(UBX::$event_type_code_claimed, $identifiers, $attributes, $company_id, $channel);
			return $response;
		}
		
		public static function triggerRedeemedOffer($company_id, $channel, $user_id, $campaign_id, $campaign_name, $item_name, $user_item_id, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			$ubx = new UBX();
			
			$identifiers = UBX::getIdentifierAttributes($user_id);
			$attributes = array(
				array(
					'name' => 'campaignId',
					'value' => $campaign_id,
					'type' => 'string', // number
				),
				array(
					'name' => 'campaignName',
					'value' => $campaign_name,
					'type' => 'string',
				),
				array(
					'name' => 'itemName',
					'value' => $item_name,
					'type' => 'string',
				),
			);
			
			if(!empty($sp_mailing_id))
			{
				$attributes[] = array(
					'name' => 'mailingId',
					'value' => $sp_mailing_id,
					'type' => 'string', // number
				);
			};
			
			if(!empty($sp_mailing_name))
			{
				$attributes[] = array(
					'name' => 'mailingName',
					'value' => $sp_mailing_name,
					'type' => 'string',
				);
			};
			
			if(!empty($user_item_id))
			{
				$attributes[] = array(
					'name' => 'redemptionId',
					'value' => $user_item_id,
					'type' => 'string', // number
				);
			};
			$attributes = array($attributes); // Attributes per Event
			$response = $ubx->executeUBXEventAPI(UBX::$event_type_code_redeemed, $identifiers, $attributes, $company_id, $channel);
			return $response;
		}
		
		public static function triggerClaimedEmailCode($email, $company_id, $item_id, $channel, $qr_code_id, $qr_code_text, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			$ubx = new UBX();
			$identifiers = UBX::getIdentifierAttributes(null, $email);
			$attributes = array(
				array(
					'name' => 'itemName',
					'value' => $qr_code_text,
					'type' => 'string',
				),
			);
			
			if(!empty($qr_code_id))
			{
				$attributes[] = array(
					'name' => 'claimId',
					'value' => $qr_code_id,
					'type' => 'string', // number
				);
			}
			
			
			if(!empty($sp_mailing_id))
			{
				$attributes[] = array(
					'name' => 'mailingId',
					'value' => $sp_mailing_id,
					'type' => 'string', // number
				);
			}
			
			if(!empty($sp_mailing_name))
			{
				$attributes[] = array(
					'name' => 'mailingName',
					'value' => $sp_mailing_name,
					'type' => 'string',
				);
			}
			$attributes = array($attributes); // Attributes per Event
			$response = $ubx->executeUBXEventAPI(UBX::$event_type_code_claimed, $identifiers, $attributes, $company_id, $channel);
			return $response;
		}
		
		public static function triggerRedeemedQRCode($email, $company_id, $channel, $qr_code_id, $qr_code_text, $sp_mailing_id = null, $sp_mailing_name = null)
		{
			$ubx = new UBX();
			
			$identifiers = UBX::getIdentifierAttributes(null, $email);
			$attributes = array(
				array(
					'name' => 'itemName',
					'value' => $qr_code_text,
					'type' => 'string',
				),
			);
			
			if(!empty($qr_code_id))
			{
				$attributes[] = array(
					'name' => 'redemptionId',
					'value' => $qr_code_id,
					'type' => 'string', // number
				);
			};
			
			if(!empty($sp_mailing_id))
			{
				$attributes[] = array(
					'name' => 'mailingId',
					'value' => $sp_mailing_id,
					'type' => 'string', // number
				);
			};
			
			if(!empty($sp_mailing_name))
			{
				$attributes[] = array(
					'name' => 'mailingName',
					'value' => $sp_mailing_name,
					'type' => 'string',
				);
			};
			
			$attributes = array($attributes); // Attributes per Event
			$response = $ubx->executeUBXEventAPI(UBX::$event_type_code_redeemed, $identifiers, $attributes, $company_id, $channel);
			return $response;
		}
		
		public static function getUBXCredentials($company_id)
		{
			$sql = "select sp_ubx_auth_key, sp_ubx_api_url from companies where id = '$company_id'";
			$row = BasicDataObject::getDataRow($sql);
			return $row;
		}
		
		public static function getIdentifierAttributes($user_id, $email = '')
		{
			if(!empty($email))
			{
				$user = new User();
				$user->Select("email='" . Database::mysqli_real_escape_string($email) . "'");
			}
			else
			{
				$user = new User($user_id);
			}
			
			

			$user_id = !empty($user_id) ? $user_id : $user->id;
			$email = !empty($email) ? $email : $user->email;
			$facebook_id = $user->facebook_id;
			
			$identifiers = array(
				array(
					'name' => 'coupsmartContactId',
					'value' => $user_id,
					'isOriginal' => true,
				),
			);
		
			if(!empty($email))
			{
				$identifiers[] = array(
					'name' => 'email',
					'value' => $email,
					'isOriginal' => false,
				);
			}
			
			if(!empty($facebook_id))
			{
				$identifiers[] = array(
					'name' => 'facebookId',
					'value' => $facebook_id,
					'isOriginal' => false,
				);
			}
			
			return $identifiers;
		}
	}
?>
