<?php
//PHP REQUIRED CLASS Engage.php in subdirectory /lib
class Engage {
	protected $apiHost = null;
	protected $username = null; 
	protected $password = null;
	protected $sessionId = null;
	protected $lastRequest = null; 
	protected $lastResponse = null; 
	protected $lastFault = null;
	
	public function __construct($apiHost) {
		$this->apiHost = $apiHost; 
	}
	
	public function executeJSON($request)
	{
		$company_id = 19;
		$access_token = Silverpop::getAccessToken($company_id);
		error_log("access_token in Engage::executeJSON(): " . $access_token);
		$requestJSON = '{"events":[{"eventTypeCode":"28","eventTimestamp":"2014-01-21T11:13:43.000Z","attributes":[{ "name":"Silverpop Contact Id","value":"7116971"},{"name":"Coupsmart Contact Id","value":"18931"}]}]}';
		$url = "https://apipilot.silverpop.com/rest/events/submission";

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
		error_log("responseXml in executeJSON(): " . var_export($responseXml, true));
	}
	
	public function execute($request)
	{
		if ($request instanceof SimpleXMLElement) {
			$requestXml = $request->asXML(); 
		} else {
			$requestXml = "<?xml version=\"1.0\"?>\n<Envelope><Body>{$request}</Body></Envelope>"; 
		}
		// NOTE: Make sure that your request string uses UTF-8 encoding
		$this->lastRequest = $requestXml; 
		$this->lastResponse = null;
		$this->lastFault = null;
		$curl = curl_init();
		
		curl_setopt($curl, CURLOPT_URL, $this->getApiUrl());
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $requestXml);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: text/xml; charset=UTF-8', 'Content-Length: ' . strlen($requestXml)));
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 60);
		curl_setopt($curl, CURLOPT_TIMEOUT, 180);
		
		$responseXml = @curl_exec($curl);

		if ($responseXml === false) 
		{
			throw new Exception('CURL error: ' . curl_error($curl));
		}
		curl_close($curl);
		if ($responseXml === true || !trim($responseXml)) 
		{ 
			throw new Exception('Empty response from Engage');
		}
		$this->lastResponse = $responseXml;
		
		// NOTE: You may want to check that the Engage response is in valid UTF-8 encoding before parsing the XML 
		$response = @simplexml_load_string('<?xml version="1.0"?>' . $responseXml);
		if ($response === false) 
		{
			throw new Exception('Invalid XML response from Engage');
		}
		if (!isset($response->Body)) 
		{
			throw new Exception('Engage response contains no Body');
		}
		$response = $response->Body; 
		$this->checkResult($response);
		return $response; 
	}

	public function getApiUrl() 
	{
		$url = "https://{$this->apiHost}/XMLAPI";
		if ($this->sessionId !== null) {
			$url .= ';jsessionid=' . urlencode($this->sessionId);
		}
		return $url; 
	}
	
	public function checkResult($xml) {
		if (!isset($xml->RESULT)) {
			throw new Exception('Engage XML response body does not contain RESULT');
		}
		if (!isset($xml->RESULT->SUCCESS)) {
			throw new Exception('Engage XML response body does not contain RESULT/SUCCESS');
		}
		$success = strtoupper($xml->RESULT->SUCCESS); 
		if (in_array($success, array('TRUE', 'SUCCESS'))) {
			return true; 
		}
		if ($xml->Fault) {
			$this->lastFault = $xml->Fault;
			$code = (string)$xml->Fault->FaultCode;
			$error = (string)$xml->Fault->FaultString;
			throw new Exception("Engage fault '{$error}'" . ($code ? "(code: {$code})" : ''));
		}
		throw new Exception('Unrecognized Engage API response'); 
	}
	
	public function getLastRequest() {
		return $this->lastRequest; 
	}
	
	public function getLastResponse() {
		return $this->lastResponse; 
	}
	
	public function getLastFault() {
		return $this->lastFault; 
	}
	
	public function login($username, $password) 
	{
		$this->username = $username; 
		$this->password = $password; 
		$this->sessionId = null;
		$request = "<Login><USERNAME><![CDATA[{$username}]]></USERNAME><PASSWORD><![CDATA[{$password}]]></PASSWORD></Login>";
		try {
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Login failed: ' . $e->getMessage());
		}
		if (!isset($response->RESULT->SESSIONID)) {
			throw new Exception('Login response did not include SESSIONID');
		}
		$this->sessionId = $response->RESULT->SESSIONID; 
	}
	
	public function logout()
	{
		$request = "<Logout/>";
		try {
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Logout failed: ' . $e->getMessage());
		}
		$result = $response->RESULT->SUCCESS;
		error_log("result in logout(): " . var_export($result, true));
	}
	
	
	public function getDatabaseDetails($list_id)
	{
		$request = "<GetListMetaData>";
		$request .= "<LIST_ID>$list_id</LIST_ID>";
		$request .= "</GetListMetaData>";
	
		try {
			// error_log("request string in Engage::getDatabaseDetails(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getDatabaseDetails(): ' . $e->getMessage());
		}
	
		// error_log("response before json_decoding in Engage::getDatabaseDetails(): " . var_export($response, true));
		// $recipient_id = $response->RESULT->RecipientId;
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::getDatabaseDetails(): " . var_export($response, true));

		return $response;
	}
	
	// Gets a List of all existing Databases 
	public function getLists($visibilty, $list_type)
	{
		$request = "<GetLists>";
		$request .= "<VISIBILITY>$visibilty</VISIBILITY>";
		$request .= "<LIST_TYPE>$list_type</LIST_TYPE>";
		$request .= "<INCLUDE_ALL_LISTS>true</INCLUDE_ALL_LISTS>";
		$request .= "</GetLists>";
	
		try {
			// error_log("request string in Engage::getLists(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getLists(): ' . $e->getMessage());
		}
	
		// error_log("response before json_decoding in Engage::getLists(): " . var_export($response, true));
		// $recipient_id = $response->RESULT->RecipientId;
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::getLists(): " . var_export($response, true));
		
		// 1.	No relational tables found
		if(!isset($response['RESULT']['LIST']))
		{
			$response['RESULT']['LIST'] = array();
		}
		else if(isset($response['RESULT']['LIST']['ID']))	// 2.	Only 1 relational table found
		{
			$response['RESULT']['LIST'] = array($response['RESULT']['LIST']);
		}
		else		// 3.	2 or more relational tables found
		{
		
		}
		return $response['RESULT']['LIST'];
	}
	
	public function addContact($list_id, $created_from, $data, $send_auto_reply = null, $update_if_found = null, $allow_html = null, 
		$visitor_key = null, $contact_list_ids = null, $sync_fields = null)
	{
		$request = "<AddRecipient><LIST_ID>$list_id</LIST_ID><CREATED_FROM>$created_from</CREATED_FROM>";
		
		if(!is_null($send_auto_reply))
		{
			$send_auto_reply = ($send_auto_reply) ? 'true' : 'false';
			$request .= "<SEND_AUTOREPLY>$send_auto_reply</SEND_AUTOREPLY>";
		}
		
		if(!is_null($update_if_found))
		{
			$update_if_found = ($update_if_found) ? 'true' : 'false';
			$request .= "<UPDATE_IF_FOUND>$update_if_found</UPDATE_IF_FOUND>";
		}
		
		if(!is_null($allow_html))
		{
			$allow_html = ($allow_html) ? 'true' : 'false';
			$request .= "<ALLOW_HTML>$allow_html</ALLOW_HTML>";
		}
		
		if(!is_null($visitor_key))
		{
			$visitor_key = ($visitor_key) ? 'true' : 'false';
			$request .= "<VISITOR_KEY>$visitor_key</VISITOR_KEY>";
		}
		
		
		// Specifying one or more contact list ids
		if(!empty($contact_list_ids))
		{
			$request .= "<CONTACT_LISTS>";
			foreach($contact_list_ids as $contact_list_id)
				$request .= "<CONTACT_LIST_ID>$contact_list_id</CONTACT_LIST_ID>";
			$request .= "</CONTACT_LISTS>";
		}
		
		// Specifying one or more sync fields
		if(!empty($sync_fields))
		{
			$request .= "<SYNC_FIELDS>";
			foreach($sync_fields as $key => $val)
				$request .= "<SYNC_FIELD><NAME>$key</NAME><VALUE>$val</VALUE></SYNC_FIELD>";
			$request .= "</SYNC_FIELDS>";
		}
		
		// Adding Data
		if(!empty($data))
		{
			foreach($data as $key => $val)
			{
				$request .= "<COLUMN><NAME>$key</NAME><VALUE>$val</VALUE></COLUMN>";
			}
		}
		$request .= "</AddRecipient>";
		
		try {
			// error_log("request string in Engage::addContact(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::addContact(): ' . $e->getMessage());
		}
		
		// error_log("response before json_decoding in Engage::addContact(): " . var_export($response, true));
		// $recipient_id = $response->RESULT->RecipientId;
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::addContact(): " . var_export($response, true));
		
		$recipient_id = $response['RESULT']['RecipientId'];
		error_log("recipient_id in Engage::addContact(): " . var_export($recipient_id, true));
		return $recipient_id;
	}
	
	public function getContactDetailsByEmail($email, $sp_list_id, $is_email_key = true)
	{
		$request = "<SelectRecipientData>";
		$request .= "<LIST_ID>$sp_list_id</LIST_ID>";
		$request .= "<EMAIL>$email</EMAIL>";
		// if(!$is_email_key)
		{
			$request .= "<COLUMN><NAME>Email</NAME><VALUE>$email</VALUE></COLUMN>";
		}
		$request .= "</SelectRecipientData>";
		
		try {
			// error_log("request string in Engage::getContactDetailsByEmail(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getContactDetailsByEmail(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::getContactDetailsByEmail(): " . var_export($response, true));
		return $response;
	}
	
	public function insertUpdateRelationalTableData($table_id, $rows_group)
	{
		foreach($rows_group as $rows)
		{
			$request = "<InsertUpdateRelationalTable><TABLE_ID>$table_id</TABLE_ID>";
			if(!empty($rows))
			{
				$request .= "<ROWS>";
				foreach($rows as $i => $row)
				{
					$request .= "<ROW>";
					foreach($row as $name => $value)
					{				
						$request .= '<COLUMN name="' . $name. '"><![CDATA[' . $value . ']]></COLUMN>';
					}
					$request .= "</ROW>";
				}
				$request .= "</ROWS>";
			}
			$request .= "</InsertUpdateRelationalTable>";
		
			try {
				// error_log("request string in Engage::insertUpdateRelationalTableData(): " . var_export($request, true));
				$response = $this->execute($request);
			} catch (Exception $e) {
				throw new Exception('Exception in Engage::insertUpdateRelationalTableData(): ' . $e->getMessage());
			}
		
			// error_log("response before json_decoding in Engage::insertUpdateRelationalTableData(): " . var_export($response, true));
			// $recipient_id = $response->RESULT->RecipientId;
			$response = json_decode(json_encode($response), true);
			// error_log("response after json_decoding in Engage::insertUpdateRelationalTableData(): " . var_export($response, true));
		}
	}
	
	public function insertUpdateRelationalTableRow($table_id, $row)
	{
		
		$request = "<InsertUpdateRelationalTable><TABLE_ID>$table_id</TABLE_ID>";		
		$request .= "<ROWS>";
		$request .= "<ROW>";
		foreach($row as $name => $value)
		{				
			$request .= '<COLUMN name="' . $name. '"><![CDATA[' . $value . ']]></COLUMN>';
		}
		$request .= "</ROW>";
		$request .= "</ROWS>";
		$request .= "</InsertUpdateRelationalTable>";

		try {
			// error_log("request string in Engage::insertUpdateRelationalTableRow(): " . var_export($request, true));
			$response = $this->execute($request);
			// error_log("response in insertUpdateRelationalTableRow: " . var_export($response, true));
			// $success = strtoupper($response->RESULT->SUCCESS);
			// if (in_array($success, array('TRUE', 'SUCCESS'))) 
			if(count($response->RESULT->FAILURES->FAILURE) == 0)
			{ 
				return true;
			}
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::insertUpdateRelationalTableRow(): ' . $e->getMessage());
		}

		// error_log("response before json_decoding in Engage::insertUpdateRelationalTableRow(): " . var_export($response, true));
		// $recipient_id = $response->RESULT->RecipientId;
		// $response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::insertUpdateRelationalTableRow(): " . var_export($response, true));
		
	
	}
	
	public function createTable($table_name, $columns)
	{
		$request = "<CreateTable><TABLE_NAME>$table_name</TABLE_NAME>";
		if(!empty($columns))
		{
			$request .= "<COLUMNS>";
			foreach($columns as $i => $column)
			{
				$request .= "<COLUMN>";
				foreach($column as $attr => $col_name)
				{
					$request .= "<$attr>$col_name</$attr>";
				}
				$request .= "</COLUMN>";
			}
			$request .= "</COLUMNS>";
		}
		$request .= "</CreateTable>";
		try {
			// error_log("request string in Engage::createTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::createTable(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::createTable(): " . var_export($response, true));
		$table_id = $response['RESULT']['TABLE_ID'];
		return $table_id;
	}

	public function deleteRelationalTableData($table_id, $rows, $key1 = null, $key2 = null, $key3 = null)
	{
		// foreach($rows_group as $rows)
		// {
			$request = "<DeleteRelationalTableData><TABLE_ID>$table_id</TABLE_ID>";
			if(!empty($rows))
			{
				$request .= "<ROWS>";
				foreach($rows as $i => $row)
				{
					$request .= "<ROW>";
					
					if(!empty($key1))
						$request .= '<KEY_COLUMN name="' . $key1. '"><![CDATA[' . $row[$key1] . ']]></KEY_COLUMN>';
					
					if(!empty($key2))
						$request .= '<KEY_COLUMN name="' . $key2. '"><![CDATA[' . $row[$key2] . ']]></KEY_COLUMN>';
					
					if(!empty($key3))
						$request .= '<KEY_COLUMN name="' . $key3. '"><![CDATA[' . $row[$key3] . ']]></KEY_COLUMN>';
					
					$request .= "</ROW>";
				}
				$request .= "</ROWS>";
			}
			$request .= "</DeleteRelationalTableData>";

			try {
				// error_log("request string in Engage::deleteRelationalTableData(): " . var_export($request, true));
				$response = $this->execute($request);
			} catch (Exception $e) {
				throw new Exception('Exception in Engage::deleteRelationalTableData(): ' . $e->getMessage());
			}
		
			// error_log("response before json_decoding in Engage::deleteRelationalTableData(): " . var_export($response, true));
			// $recipient_id = $response->RESULT->RecipientId;
			$response = json_decode(json_encode($response), true);
			// error_log("response after json_decoding in Engage::deleteRelationalTableData(): " . var_export($response, true));
		// }
	}

	public function purgeTable($table_name, $table_id = null, $table_visibility = null, $delete_before = null)
	{
		$request = "<PurgeTable>";
		if(!empty($table_name))
			$request .= "<TABLE_NAME>$table_name</TABLE_NAME>";
		
		if(!empty($table_id))
			$request .= "<TABLE_ID>$table_id</TABLE_ID>";
			
		if(!is_null($table_visibility))
			$request .= "<TABLE_VISIBILITY>$table_visibility</TABLE_VISIBILITY>";
		
		if(!empty($delete_before))
		{
			$delete_before = date('m/d/Y H:i:s', strtotime($delete_before . ' UTC'));
			$request .= "<DELETE_BEFORE>$delete_before</DELETE_BEFORE>";
		}
		$request .= "</PurgeTable>";
		
		try {
			// error_log("request string in Engage::purgeTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::purgeTable(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::purgeTable(): " . var_export($response, true));
	}


	public function joinTable($map_fields, $table_name = null, $list_name = null, $table_id = null, $list_id = null, $table_visibility = null, $list_visibility = null, $remove = false)
	{
		$request = "<JoinTable>";
		if(!empty($table_name))
			$request .= "<TABLE_NAME>$table_name</TABLE_NAME>";
		
		if(!empty($table_id))
			$request .= "<TABLE_ID>$table_id</TABLE_ID>";
		
		if(!empty($list_name))
			$request .= "<LIST_NAME>$list_name</LIST_NAME>";
		
		if(!empty($list_id))
			$request .= "<LIST_ID>$list_id</LIST_ID>";
		
		if(!is_null($table_visibility))
			$request .= "<TABLE_VISIBILITY>$table_visibility</TABLE_VISIBILITY>";
		
		if(!is_null($list_visibility))
			$request .= "<LIST_VISIBILITY>$list_visibility</LIST_VISIBILITY>";
		
		if($remove)
			$request .= "<REMOVE>true</REMOVE>";

		if(!empty($map_fields))
		{
			foreach($map_fields as $i => $map_field)
			{	
				$request .= "<MAP_FIELD>";
				foreach($map_field as $attr => $col_name)
				{
					$request .= "<$attr>$col_name</$attr>";
				}
				$request .= "</MAP_FIELD>";
			}
		}
		
		$request .= "</JoinTable>";
		
		try {
			// error_log("request string in Engage::joinTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::joinTable(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		error_log("response after json_decoding in Engage::joinTable(): " . var_export($response, true));
		
		return $response['RESULT']['SUCCESS'] == 'TRUE';
	}
		
	public function deleteTable($table_name, $table_id = null, $table_visibility = 0)
	{
		$request = "<DeleteTable>";
		
		if(!empty($table_name))
			$request .= "<TABLE_NAME>$table_name</TABLE_NAME>";
			
		if(!empty($table_id))
			$request .= "<TABLE_ID>$table_id</TABLE_ID>";
			
		if(!empty($table_visibility) || $table_visibility == 0)
			$request .= "<TABLE_VISIBILITY>$table_visibility</TABLE_VISIBILITY>";
			
		$request .= "</DeleteTable>";
		
		try {
			// error_log("request string in Engage::deleteTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::deleteTable(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		error_log("response after json_decoding in Engage::deleteTable(): " . var_export($response, true));
		
		return $response['RESULT']['SUCCESS'] == 'TRUE';
	}
	
	
	public function getSentMailingsForOrg($start_date, $end_date, $exclude_test_emailings = false, $sent = false)
	{
		
		$request = "<GetSentMailingsForOrg><DATE_START>$start_date</DATE_START><DATE_END>$end_date</DATE_END>";
		
		
		if($exclude_test_emailings)
			$request .= "<EXCLUDE_TEST_MAILINGS>true</EXCLUDE_TEST_MAILINGS>";
		
		if($sent)
			$request .= "<SENT>true</SENT>";
			
		$request .= "</GetSentMailingsForOrg>";
		
		
		try {
			error_log("request string in Engage::getSentMailingsForOrg(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getSentMailingsForOrg(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::getSentMailingsForOrg(): " . var_export($response, true));
		
		return $response;
	}
	
	public function getSentMailingsForUser($start_date, $end_date, $sent = false)
	{
		
		$request = "<GetSentMailingsForUser><DATE_START>$start_date</DATE_START><DATE_END>$end_date</DATE_END>";

		if($sent)
			$request .= "<SENT>true</SENT>";
			
		$request .= "</GetSentMailingsForUser>";
		
		try {
			error_log("request string in Engage::getSentMailingsForUser(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getSentMailingsForUser(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::getSentMailingsForOrg(): " . var_export($response, true));
		
		return $response;
	}
	
	public function getSentMailingsForList($list_id, $start_date, $end_date, $include_children = true, $exclude_test_emailings = false, $sent = false)
	{
		
		$request = "<GetSentMailingsForList><DATE_START>$start_date</DATE_START><DATE_END>$end_date</DATE_END><LIST_ID>$list_id</LIST_ID>";
		
		if($include_children)
			$request .= "<INCLUDE_CHILDREN>true</INCLUDE_CHILDREN>";
		
		if($exclude_test_emailings)
			$request .= "<EXCLUDE_TEST_MAILINGS>true</EXCLUDE_TEST_MAILINGS>";
		
		if($sent)
			$request .= "<SENT>true</SENT>";
			
		$request .= "</GetSentMailingsForList>";
		
		
		try {
			error_log("request string in Engage::getSentMailingsForList(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getSentMailingsForList(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		// error_log("response after json_decoding in Engage::getSentMailingsForList(): " . var_export($response, true));
		
		return $response;
	}
	
	public function createEmailBehaviorQuery($query_name, $parent_list_id, $visibility, $option_operator, $type_operator, $mailing_id = null, $report_id = null)
	{
		$request = "<CreateQuery>
<QUERY_NAME>$query_name</QUERY_NAME><PARENT_LIST_ID>$parent_list_id</PARENT_LIST_ID><VISIBILITY>$visibility</VISIBILITY>";
		$request .= "<BEHAVIOR><OPTION_OPERATOR>$option_operator</OPTION_OPERATOR> <TYPE_OPERATOR>$type_operator</TYPE_OPERATOR>";
		if(!empty($mailing_id))
			$request .= "<MAILING_ID>$mailing_id</MAILING_ID>";
			
		if(!empty($report_id))
			$request .= "<REPORT_ID>$report_id</REPORT_ID>";
		
		$request .= "</BEHAVIOR></CreateQuery>";
		
		try {
			// error_log("request string in Engage::deleteTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::createEmailBehaviorQuery(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		error_log("response after json_decoding in Engage::createEmailBehaviorQuery(): " . var_export($response, true));
		
		return $response['RESULT']['SUCCESS'] == 'TRUE';
	}
	
	public function calculateQuery($query_id, $email = null)
	{
		$request = "<CalculateQuery> <QUERY_ID>$query_id</QUERY_ID>";
		if(!empty($email))
			$request .= "<EMAIL>$email</EMAIL>";
		
		$request .= "</CalculateQuery>";
		
		try {
			// error_log("request string in Engage::deleteTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::calculateQuery(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		error_log("response after json_decoding in Engage::calculateQuery(): " . var_export($response, true));
		
		return $response['RESULT']['SUCCESS'] == 'TRUE';
	}
	
	public function getJobStatus($job_id)
	{
		$request = "<GetJobStatus><JOB_ID>$job_id</JOB_ID></GetJobStatus>";
		
		try {
			// error_log("request string in Engage::deleteTable(): " . var_export($request, true));
			$response = $this->execute($request);
		} catch (Exception $e) {
			throw new Exception('Exception in Engage::getJobStatus(): ' . $e->getMessage());
		}
		$response = json_decode(json_encode($response), true);
		error_log("response after json_decoding in Engage::getJobStatus(): " . var_export($response, true));
		
		return $response['RESULT']['SUCCESS'] == 'TRUE';
	}

}
?>