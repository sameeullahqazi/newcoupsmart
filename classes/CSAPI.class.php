<?php
require_once(dirname(__DIR__) . "/includes/SQLQueryBuilder.php");
class CSAPI
{

	////////////////// BEGIN - SQL OBJECTS FOR ALL MAIN STATS /////////////////////////
	function getViewsSQLObj($params = null)
	{
		$query = new SQLSelect("views iv");

		if(!empty($params['companyId']))
			$query->whereAnd("iv.company_id = '" . $params['companyId']. "'");
		
		if(!empty($params['campaignId']))
			$query->whereAnd("iv.item_id in (" . $params['campaignId']. ")");
		
		if(!empty($params['campaignId']))
			$query->whereAnd("iv.item_id = '" . $params['campaignId']. "'");
		
		if(!empty($params['deliveryMethods']))
		{
			$query->innerJoin("items i on iv.items_id = i.id");
			$query->whereAnd("i.delivery_method in (" . $params['deliveryMethods']. ")");
		}
		
		if(!empty($params['deliveryMethod']))
		{
			$query->innerJoin("items i on iv.items_id = i.id");
			$query->whereAnd("i.delivery_method = '" . $params['deliveryMethod']. "'");
		}
		
		if(!empty($params['startDate']) && !empty($params['endDate']))
			$query->whereAnd("iv.created between '" . $params['startDate'] . "' and '" . $params['endDate'] . "'");
		else if(!empty($params['startDate']))
			$query->whereAnd("iv.created >= '" . $params['startDate'] . "'");
		else if(!empty($params['endDate']))
			$query->whereAnd("iv.created <= '" . $params['endDate'] . "'");
		
		return $query;
	}
	
	
	/////////////////////// END - SQL OBJECTS FOR ALL MAIN STATS /////////////////////////
	
	function AuthenticateUser($params) {
		$errors = array();
		
		if(!isset($params['api_key']))
		{
			$errors[] = array(
				'message' => 'Parameter api_key not found. A valid API key must be provided.',
				'code' => 'NoAPIKey'
			);
		}
		
		if(!$this->validate_capi_key($params['api_key']))
		{
			$errors[] = array(
				'message' => 'The API key provided is invalid',
				'code' => 'InvalidAPIKey'
			);
		}
		
		
		if(!isset($params['sig']))
		{
			$errors[] = array(
				'message' => 'Parameter sig not found. A valid signature must be provided',
				'code' => 'NoSig'
			);
		}
		
		// if(!StatsAPI::validate_capi_signature($params))
		$calc_sig = $this->CalcCSAPISig($params);
		// error_log("calc_sig: " . $calc_sig);
		if($params['sig'] != $calc_sig)
		{
			$errors[] = array(
				'message' => 'The signature is invalid',
				'code' => 'InvalidSig'
			);
		}
		
		
		return $errors;
	}
	
	function validate_capi_key($capi_key)
	{
		$sql = "select capi_key from users where capi_key = '".Database::mysqli_real_escape_string($capi_key)."'";
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row);
	}
	
	function CalcCSAPISig($params)
	{
		$sig = $params['sig'];
		unset($params['sig']);
		unset($params['request']);
	
		// Get api secret and append it to the data
		// error_log('params: '.var_export($params, true));
		
		// Getting the API Secret
		$sql = "select capi_secret from users where capi_key='".$params['api_key']."'";
		$row = BasicDataObject::getDataRow($sql);
		$capi_secret = isset($row['capi_secret']) ? $row['capi_secret'] : '';		
		// $params['api_secret'] = $capi_secret;
		
		
		
		sort($params);
		$str_data = implode('', $params) . $capi_secret;
		// error_log("str_data in StatsAPI::calculate_capi_signature(): ".$str_data);
		$calc_sig = md5($str_data);
		return $calc_sig;
	}
	
	
	function generateCSAPISignature($params)
	{
		$sig = isset($params['sig']) ? $params['sig'] : null;
		$secret = isset($params['secret']) ? $params['secret'] : null;
		
		unset($params['secret']);
		unset($params['sig']);
		unset($params['request']);
		
		
		$i = rand(0, 1000);
		// $i = 1000;
		$salt = 'ASdabjhgjgehwjrhwerofdsu9fdsfkkejSDfmncxbv';
		ksort($params);
		// error_log("params in CSAPI::checkCSAPISignature(): " . var_export($params, true));
		$sig = md5($i.$salt.($i + 1).$i.$salt.implode('', $params).$secret);
		// error_log("sig generated in CSAPI::generateCSAPISignature(): " . var_export($sig, true));
		return $sig;
	}
	
	function checkCSAPISignature($params, $sig_to_check = false)
	{
		$secret = isset($params['secret']) ? $params['secret'] : null;
		unset($params['secret']);
		if(empty($sig_to_check))
			$sig_to_check = isset($params['sig']) ? $params['sig'] : null;
		unset($params['sig']);
		unset($params['request']);
		
		$salt = 'ASdabjhgjgehwjrhwerofdsu9fdsfkkejSDfmncxbv';
		ksort($params);
		// error_log("params in CSAPI::checkCSAPISignature(): " . var_export($params, true));
		$str_data = implode('', $params).$secret;
		// error_log("sig_to_check in CSAPI::checkCSAPISignature(): " . var_export($sig_to_check, true));
		for($i = 0; $i <= 1000; $i++)
		{
			$sig = md5($i.$salt.($i + 1).$i.$salt.$str_data);
			// error_log("sig: " . var_export($sig, true));
			if($sig === $sig_to_check)
				return true;
		}
		// error_log("sig in CSAPI::checkCSAPISignature(): " . $sig);
		return false;
	}
	
	function AuthorizeUser($params)
	{
		$errors = array();
		
		$capi_key = $params['api_key'];
		// User must be a customer or reseller
		$sql = "select ug.group_id from user_groups ug inner join users u on ug.user_id = u.id where ug.group_id in (7, 8) and u.capi_key='".Database::mysqli_real_escape_string($capi_key)."'";
		// error_log("sql: ".$sql);
		$rows = BasicDataObject::getDataTable($sql);
		if(count($rows) == 0)
		{
			$errors[] = array(
				'message' => 'This user is not authorized to view the data. User must either be a reseller or a customer',
				'code' => 'AuthErr'
			);
		}
		
		// Company must belong to the user		
		if(!empty($params['company_id']))
		{
			$company_id = $params['company_id'];
			$companies = StatsAPI::get_company_ids_by_capi_key($capi_key);
			
			if(!isset($companies[$company_id]))
			{
				$errors[] = array(
					'message' => 'This user is not authorized to view the data. The specified company id does not belong to the user',
					'code' => 'AuthErr'
				);
			}
		}
		
		
		return $errors;
	}
	
	/***********************************************************
	getCumulativeEntrantCount

	Total Unique People – Wireframe page 1 - Dashboard
	Target chart type:  http://www.highcharts.com/demo/line-log-axis
	DB tables:  tmp_entrant_campaign, tmp_entrant_action
	Description:  Return a sorted list of time intervals (default days) and cumulative entrant count for the selected campaigns in the company.  This cumulative data should continue to grow each day when displayed on a graph. 
	@param INTEGER companyId  required
	@param INTEGER campaignId  optional – default action is to show data for all campaigns in the company
	@param STRING startDate  - optional – default is to show all entrants from the first relevant entry
	@param STRING endDate  - optional – default is to show all entrants up until yesterday
	@param STRING timeIntervalSeconds – optional – seconds of timetime interval, defaults to 24 x 60 x 60 = 86400 seconds = 1 day. 
	@param STRING timezone – optional – defaults to ‘-0500’
	@param STRING activityType – optional – defaults to ‘views’ 
	@return ARRAY settings -  the given and default values for each parameter value.  
	@return ARRAY columns  - the column names of the data (‘time_period_name, ‘cumulative_entrant_count’)
	@return ARRAY of ARRAYS  data ((STRING, INTEGER), (STRING INTEGER))

	STRING time_period_name the end-time of the time period (-1 second), a parse-able datetime string (i.e. ‘2014-11-01T23:59:59-0500’)
	INTEGER cumulative_entrant_count – count of cumulative entrants associated with campaigns of that company up to that date

	
	
	***********************************************************/
	
	
	function getCumulativeEntrantCount($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : (!empty($params['timeintervalseconds']) ? $params['timeintervalseconds'] :86400 );
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		//	1.	Calculating Interval specific variables
		switch($interval)
		{
			case 60:	//	Minute
				$mysql_date_format = "%m/%d/%Y\T%H:%i:00$time_zone";
				$php_date_format = "m/d/Y\TH:i:00$time_zone";
				break;
	
			case 3600:	//	Hour
				$mysql_date_format = "%m/%d/%Y\T%H:00:00$time_zone";
				$php_date_format = "m/d/Y\TH:00:00$time_zone";
				break;
	
			case 86400:	//	Day
				$mysql_date_format = "%Y-%m-%d\T00:00:00$time_zone";
				$php_date_format = "Y-m-d\T00:00:00$time_zone";
				break;
		}
		
		
		
		$arr_where_clause = array();
		$arr_where_clause_views = array();
		$arr_where_clause_claims = array();
		$arr_where_clause_shares = array();
		$arr_where_clause_redeems = array();
		
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			/*$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);*/
 			$arr_where_clause_views[] = "iv.company_id = '$company_id'";
 			$arr_where_clause_claims[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.company_id = '$company_id'";
			/*
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
			*/
		}
		else
		{
			$arr_where_clause_views[] = "iv.items_id in (" . $campaign_ids . ")";
 			$arr_where_clause_claims[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.item_shared in (" . $campaign_ids . ")";
		}
		
		if(!empty($activity_type))
		{
			// $arr_where_clause[] = " ea.activity_type = '$activity_type'";
		}
		
		
		if(!empty($start_date) && !empty($end_date))
		{
			$arr_where_clause_views[] = " ". $str_date_sub_start. "iv.created". $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_claims[] = " ". $str_date_sub_start. "ui.date_claimed". $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_redeems[] = " ". $str_date_sub_start. "ui.date_redeemed". $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_shares[] = " ". $str_date_sub_start. "r.created between". $str_date_sub_end . " '$start_date' and '$end_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_views[] = " ". $str_date_sub_start. "iv.created". $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_claims[] = " ". $str_date_sub_start. "ui.date_claimed". $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_redeems[] = " ". $str_date_sub_start. "ui.date_redeemed". $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_shares[] = " ". $str_date_sub_start. "r.created". $str_date_sub_end . " >= '$start_date'";
			
			
	
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_views[] = " ". $str_date_sub_start. "iv.created". $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_claims[] = " ". $str_date_sub_start. "ui.date_claimed". $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_redeems[] = " ". $str_date_sub_start. "ui.date_redeemed". $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_shares[] = " ". $str_date_sub_start. "r.created". $str_date_sub_end . " <= '$end_date'";
		}
		$str_test_user_ids = User::getStrTestUserAccountsIds();
		
		// SQL for Views
		$sql_views = "select user_id, ". $str_date_sub_start. "iv.created". $str_date_sub_end . " as date from items_views iv where user_id > 0";
		if(!empty($arr_where_clause_views))
			$sql_views .= " and " . implode(" and ", $arr_where_clause_views);
		
		// SQL for Claims
		$sql_claims = "select ui.user_id, ". $str_date_sub_start. "ui.date_claimed". $str_date_sub_end . " as date from user_items ui where user_id > 0 and date_claimed is not null";
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " and " . implode(" and ", $arr_where_clause_claims);
		
		// SQL for redeems
		$sql_redeems = "select ui.user_id, ". $str_date_sub_start. "ui.date_redeemed". $str_date_sub_end . " as date from user_items ui where user_id > 0 and date_redeemed is not null";
		if(!empty($arr_where_clause_redeems))
			$sql_redeems .= " and " .implode(" and ", $arr_where_clause_redeems);
			
		// SQL for Shares
		$sql_shares = "select r.user_id, ". $str_date_sub_start. "r.created". $str_date_sub_end . " as date from referrals r where user_id > 0 ";
		if(!empty($arr_where_clause_shares))
			$sql_shares .= " and " .implode(" and ", $arr_where_clause_shares);
		
		// SQL for Referrals
		$sql_referrals = "select ui.user_id, ". $str_date_sub_start. "ui.date_claimed". $str_date_sub_end . " as date from user_items ui where user_id > 0 and date_claimed is not null and referral_id > 0";
		if(!empty($arr_where_clause_claims))
			$sql_referrals .= " and " .implode(" and ", $arr_where_clause_claims);
		
		
		if(!empty($str_test_user_ids))
		{
			$sql_views .= " and user_id not in ($str_test_user_ids)";
			$sql_claims .= " and user_id not in ($str_test_user_ids)";
			$sql_redeems .= " and user_id not in ($str_test_user_ids)";
			$sql_shares .= " and user_id not in ($str_test_user_ids)";
			$sql_referrals .= " and user_id not in ($str_test_user_ids)";
		}
		
		switch($activity_type)
		{
			case 'view':
				$sql = $sql_views;
				break;
			
			case 'claim':
				$sql = $sql_claims;
				break;
				
			case 'redeem':
				$sql = $sql_redeems;
				break;
			
			case 'share':
				$sql = $sql_shares;
				break;
			
			case 'referral':
				$sql = $sql_referrals;
				break;
			
			case 'all':
				$sql = implode(' union all ', array($sql_views, $sql_claims, $sql_shares));
				break;
			
		}
		
		$sql = "select t2.date, count(distinct(t2.user_id)) as num_unique_ppl from (select t.user_id, date_format(min(t.date), '$mysql_date_format') as date  from (" . $sql . ") as t group by t.user_id) as t2 group by t2.date";
		
		// $sql .= " order by ea.created";
		// error_log("SQL in CSAPI::getCumulativeEntrantCount(): " . $sql);
		
		
		$rs = Database::mysqli_query($sql);
	
	
		$tmp_data = array();

		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_count = $row['num_unique_ppl'] + 0;
			$tmp_date = $row['date'];
			$tmp_data[$tmp_date] = $entrant_count;
		}
		// error_log("tmp_data: " . var_export($tmp_data, true));
	
		// Getting start date
		if(empty($start_date))
		{
			Database::mysqli_data_seek($rs, 0);
			$row = Database::mysqli_fetch_assoc($rs);
			$start_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($start_date);
			$start_date = date_format($obj_dt, $php_date_format);
		}

		
		// Getting End Date
		if(empty($end_date))
		{
			Database::mysqli_data_seek($rs, Database::mysqli_num_rows($rs) - 1);
			$row = Database::mysqli_fetch_assoc($rs);
			$end_date = $row['date'];
		}	
		else
		{
			$obj_dt = new DateTime($end_date);
			$end_date = date_format($obj_dt, $php_date_format);
		}
		
		$start_date_val = strtotime($start_date . " $str_time_zone");
		$end_date_val = strtotime($end_date . " $str_time_zone");
		// error_log("CSAPI::getCumulativeEntrantCount() start_date: " . $start_date . ", start_date_val: " . $start_date_val . ", end_date: " . $end_date . ", start_date_val: " . $end_date_val);
		$response = array(
			'settings' => $params,
			'data' => array(),
			'graph_data' => array (
			  'xAxis' => 
			  array (
				'type' => 'datetime',
				'dateTimeLabelFormats' => 
				array (
				  'day' => '%m/%d',
				),
				'title' => 
				array (
				  'text' => 'Time',
				),
			  ),
			  'yAxis' => 
			  array (
				'title' => 
				array (
				  'text' => 'User Count',
				),
			  ),
			  'series' => 
			  array (
				0 => 
				array (
				  'data' => array(),
				  'name' => 'Users',
				  'pointStart' => $start_date_val * 1000,
				  'pointInterval' => $interval * 1000,
				),
			  ),
			)
		);
		
		$cumm_entrant_count = 0;
		
		$time_interval = new DateInterval('PT' . $interval . 'S');
		$tmp_date = $start_date;
		for($i = $start_date_val; $i <= $end_date_val; $i += $interval)
		{
			$tmp_date = date($php_date_format, $i);
			if(isset($tmp_data[$tmp_date]))
			{
				$cumm_entrant_count += $tmp_data[$tmp_date];
			}
			// $response['data'][$tmp_date] = $cumm_entrant_count;
			$response['graph_data']['series'][0]['data'][] = $cumm_entrant_count;
			$dt = DateTime::CreateFromFormat($php_date_format, $tmp_date);
			$tmp_date = date_format($dt->add($time_interval), $php_date_format);
		}
		Database::mysqli_free_result($rs);

		if(!empty($errors))
			return $errors;
		
		return $response;
	}
	
	
	
	
	function getUniqueEntrantIDs($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		
		$sql_views_campaign_ids = "";
		$sql_referral_campaign_ids = "";
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			/*
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
			*/
		}
		else
		{
			$sql_views_campaign_ids = " and items_id in ($campaign_ids)";
			$sql_referral_campaign_ids = " and item_shared in ($campaign_ids)";
		}
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 4 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		$sql_date_range_views = "";
		$sql_date_range_claims = "";
		$sql_date_range_referrals = "";
		
		if(!empty($start_date) && !empty($end_date))
		{
			$sql_date_range_views = " and " . $str_date_sub_start . "created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$sql_date_range_claims = " and " . $str_date_sub_start . "date_claimed" . $str_date_sub_end. " between '$start_date' and '$end_date'";
			$sql_date_range_referrals = " and " . $str_date_sub_start. "created" . $str_date_sub_end. " between '$start_date' and '$end_date'";
		}
		else if(!empty($start_date))
		{
			$sql_date_range_views = " and " . $str_date_sub_start. "created" . $str_date_sub_end. " >= '$start_date'";
			$sql_date_range_claims = " and " . $str_date_sub_start. "date_claimed" . $str_date_sub_end. " >= '$start_date'";
			$sql_date_range_referrals = " and " . $str_date_sub_start. "created" . $str_date_sub_end . " >= '$start_date'";
		}
		else if(!empty($end_date))
		{
			$sql_date_range_views = " and " . $str_date_sub_start. "created" . $str_date_sub_end. " <= '$end_date'";
			$sql_date_range_claims = " and " . $str_date_sub_start. "date_claimed" . $str_date_sub_end. " <= '$end_date'";
			$sql_date_range_referrals = " and " . $str_date_sub_start. "created" . $str_date_sub_end. " <= '$end_date'";
		}

		
		$str_test_user_ids = User::getStrTestUserAccountsIds();
		
		
		// SQL to find the entrants
		$sql = "select distinct(t.user_id) as user_id from
		(select user_id from items_views where company_id = '$company_id' and user_id > 0 $sql_views_campaign_ids $sql_date_range_views
		union all 
		select user_id from user_items where user_id > 0 and item_id in ($campaign_ids) $sql_date_range_claims
		union all 
		select user_id from referrals where company_id = '$company_id' and user_id > 0 $sql_referral_campaign_ids $sql_date_range_referrals
		) as t";
		
		if(!empty($str_test_user_ids))
			$sql .= " where user_id not in ($str_test_user_ids)";
			
		// error_log("SQL for getting unique entrants  in CSAPI::getUniqueEntrantIDs(): "  . $sql);
		
		
		$arr_entrants = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_entrants[] = $row['user_id'];
		}
		Database::mysqli_free_result($rs);
		return $arr_entrants;
	}
	
	function getEntrants($params)
	{
		$limit = $params['limit'];
		$limit_likes = !empty($params['likesLimit']) ? $params['likesLimit'] : (!empty($params['likeslimit']) ? $params['likeslimit'] : 100);
		$entrant_ids = $this->getUniqueEntrantIDs($params);

		/*$sql = "select ufl.user_id, fl.fb_id, fl.name 
		from user_fb_likes ufl 
		inner join fb_likes fl on ufl.fb_like_id = fl.id
		where user_id in (" . implode(',', array_values($entrant_ids)). ")";
		error_log("SQL for user fb likes: " . $sql);
		
		$rs = Database::mysqli_query($sql);
		$arr_user_fb_likes = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$user_id = $row['user_id'];
			$arr_user_fb_likes[$user_id][] = array('id' => $row['fb_id'], 'name' => $row['name']);
		}
		// error_log("arr_user_fb_likes: " . var_export($arr_user_fb_likes, true));
		*/
		$sql = "select u.id as user_id, concat(u.firstname, ' ', u.lastname) as `name`, u.email, u.gender, u.date_of_birth, u.relationship_status as `relationship`, u.facebook_location_name as `location`, concat('[', group_concat(concat('{\"id\":\"', fl.fb_id, '\", \"name\":\"', REPLACE(fl.name, '\\\"', '\\\\\\\"'), '\"}') separator ', '), ']') as `likes`
			from users u 
			left join user_fb_likes ufl on u.id = ufl.user_id
			left join fb_likes fl on ufl.fb_like_id = fl.id
			where u.id in (" . implode(',', array_values($entrant_ids)). ") group by u.id";
		if(!empty($limit))
			$sql .= " limit $limit";
			
		// error_log("SQL in CSAPI::getEntrants(): " . $sql);
		$response['data'] = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$user_id = $row['user_id'];
			$date_of_birth = $row['date_of_birth'];
			$gender = $row['gender'];
			
			if(!empty($gender))
				$row['gender'] = $row['gender'] == 'M' ? 'Male' : ($row['gender'] == 'F' ? 'Female' : '');
				
			if(!empty($date_of_birth) && $date_of_birth != '0000-00-00')
				$row['age'] = Common::calculateAge($date_of_birth);
			
			
			if(!empty($row['likes']))
			{
				// error_log("row['likes'] before decoding: " . $row['likes']);
				$row['likes'] = json_decode($row['likes'], true);
				// error_log("row['likes'] after decoding: " . var_export($row['likes'], true));
				if(!empty($limit_likes) && !empty($row['likes']))
					$row['likes'] = array_slice($row['likes'], 0, $limit_likes);
			}
			else
			{
				unset($row['likes']);
			}
				
			if(empty($row['location'])) 
				unset($row['location']);
			
			if(empty($row['relationship'])) 
				unset($row['relationship']);

			unset($row['user_id']);
			unset($row['date_of_birth']);
			$response['data'][] = $row;
		}

		Database::mysqli_free_result($rs);
		return $response;
	}
	
	/*********************************************************
	GetCampaignInterests

	Top Likes & Interests – Wireframe page 1 - Dashboard 
	Target chart type:  custom data table
	DB tables:  tmp_campaign_interest
	Description:  returns the top interests/likes for this company or campaign. 
	@param INTEGER companyId  - required
	@param INTEGER campaignId  - optional – default action is to show data for all campaigns in the company
	@param INTEGER limit – optional – defaults to 100.  Controls the # of rows in the response.  Default Sort is # of entrants in descending order. 
	@param INTEGER min-group-size – optional – defaults to 3.  Limits the search to fan pages and interests that have a minimum number of relevant campaign entrants as fans. 
	@param INTEGER min-interest-fan-base – optional – defaults to 0.  Limits the search to fan pages and interest that have a minimum number of fans on Facebook.
	@param STRING sort – default sort is ‘entrants’  other values include ‘interest_name’ and ‘fb_fanbase’
	@param STRING sortOrder – default is ‘desc’
	@return ARRAY settings -  the given and default values for each parameter value.  
	@return ARRAY columns  - the column names of the data (‘interest_name’, ‘interest_id’, ‘fb_fanbase’, ‘entrant_count’)
	@return ARRAY of ARRAYS  data ( (STRING, INTEGER, INTEGER, INTEGER), (STRING, INTEGER, INTEGER, INTEGER) ) 

	STRING  interest_name 
	INTEGER interest_id 
	INTEGER fb_fanbase –the number of total fans who like this page in Facebook
	INTEGER entrant_count - the count of entrants who have entered the relevant campaigns and have liked the interest 
	
	***********************************************************/
	
	function getCampaignInterestsTmp($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$limit = !empty($params['limit']) ? $params['limit'] : 100;
		$min_group_size = !empty($params['min-group-size']) ? $params['min-group-size'] : 3;
		$min_interest_fan_base = !empty($params['min-interest-fan-base']) ? $params['min-interest-fan-base'] : 0;
		$sort_column = !empty($params['sort']) ? $params['sort'] : 't.num_entrants' ; // 'entrants';
		//$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] :'desc');

		
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		$response = array(
			'settings' => array(
				'companyId' => $company_id,
				'limit' => $limit,
				'min-group-size' => $min_group_size,
				'min-interest-fan-base' => $min_interest_fan_base,
				'sort' => $sort_column,
				'sortOrder' => $sort_order,
			),
			'data' => array(),
		);
		
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		// Finding Likes
		$sql = "select fb_like_id, like_count from 
		(
		select ufl.fb_like_id, count(distinct(ufl.user_id)) as like_count from user_fb_likes ufl where ufl.item_id in ($campaign_ids) group by ufl.fb_like_id
		) as t 
		order by like_count desc limit $limit";
		// error_log("SQL for likes in getCampaignInterests(): " . $sql);
		$rs = Database::mysqli_query($sql);
		$fb_likes_data = array();
		$fb_likes = array();
		
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$fb_likes_data[$row['fb_like_id']] = $row['like_count'];
		}
		// error_log("fb_likes_data: " . var_export($fb_likes_data, true));
		$sql = "select id, name as `like` from fb_likes where id in (" . implode(',', array_keys($fb_likes_data)). ")";
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$fb_likes[$row['id']] = $row['like'];
		}

		// error_log("fb_likes: " . var_export($fb_likes, true));
		$tmp_data = array();
		foreach($fb_likes_data as $like_id => $like_count)
		{
			if(isset($fb_likes[$like_id]))
			{
				$like = $fb_likes[$like_id];
				$tmp_data[$like] = $like_count;
			}
		}
		
		// Finding Interests
		$sql = "select fb_interest_id, like_count from
		(
		select ufl.fb_interest_id, count(ufl.user_id) as like_count from user_fb_interests ufl where ufl.item_id is not null and ufl.item_id in ($campaign_ids) group by ufl.fb_interest_id
		) as t
		order by like_count desc limit $limit";
		
		// error_log("SQL for interests in getCampaignInterests(): " . $sql);
		$rs = Database::mysqli_query($sql);
		$fb_interests_data = array();
		$fb_interests = array();
		
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$fb_interests_data[$row['fb_interest_id']] = $row['like_count'];
		}
		// error_log("fb_interests_data: " . var_export($fb_interests_data, true));
		
		$sql = "select id, name as `interest` from fb_interests where id in (" . implode(',', array_keys($fb_interests_data)). ")";
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$fb_interests[$row['id']] = $row['interest'];
		}
		// error_log("fb_interests: " . var_export($fb_interests, true));
		
		
		foreach($fb_interests_data as $interest_id => $like_count)
		{
			if(isset($fb_interests[$interest_id]))
			{
				$interest = $fb_interests[$interest_id];
				$tmp_data[$interest] = $like_count;
			}
		}
		// error_log("tmp_data before sorting: " . var_export($tmp_data, true));
		arsort($tmp_data);
		// error_log("tmp_data after sorting: " . var_export($tmp_data, true));
		$tmp_data = array_slice($tmp_data, 0, $limit);
		// error_log("tmp_data after slicing: " . var_export($tmp_data, true));
		$response['data'] = $tmp_data;
		
		if(!empty($errors))
			return $errors;
		
		return $response;
	}
	
	
	
	
	
	/*
	getCampaignActivity

	Campaign Activity Summary -  Wireframe page 3

	Target chart type:  http://www.highcharts.com/demo/line-time-series  http://www.highcharts.com/demo/line-basic
	DB tables:  tmp_entrant_activity
	Returns counts for each activity in relevant campaigns for consecutive time periods in a date range.  
	@param INTEGER companyId  required
	@param INTEGER campaignId  optional – default action is to show data for all campaigns in the company
	@param STRING startDate  - optional – default is to show all entrants from the first relevant activity
	@param STRING endDate  - optional – default is to show all entrants up until yesterday
	@param STRING timeIntervalSeconds – optional – seconds of timetime interval, defaults to 60 x 60 = 3600 seconds = 1 hour. 
	@param STRING timezone – optional – defaults to ‘-0500’
	@param STRING activityType – optional – defaults to ‘all’ which returns counts for EACH action separately.   Other values ‘view’, ‘claim’, ‘redeem’, ‘share’, ‘referral’ 
	@param STRING source– optional – defaults to ‘all’ which returns counts for all sources combined or does not filter on source.   Available options (‘all’, ‘facebook’, ‘shortcode’)

	@return ARRAY settings -  the given and default values for each parameter value.  
	@return ARRAY columns  - the column names of the data (‘time_period_name’, ‘activity_count’)
	@return ARRAY of ARRAY of ARRAYS  results ( name => STRING activity_type, data=> ( ( ‘STRING’, INTEGER), (STRING’, INTEGER) ) ) 

	STRING time_period_name the end-time of the time period (-1 second), a parse-able datetime string (i.e. ‘2014-11-01T23:59:59-0500’)
	INTEGER activity_count – count of cumulative entrants associated with campaigns of that company up to that date
	*/
	
	function getCampaignActivity($params)
	{
		// error_log("params in getCampaignActivity(): " . var_export($params, true));
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : 3600;
		$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : (!empty($params['timeintervalseconds']) ? $params['timeintervalseconds'] : 3600 );
		//$in_the_last = !empty($params['inTheLast']) ? $params['inTheLast'] : 0;
		$in_the_last = !empty($params['inTheLast']) ? $params['inTheLast'] : (!empty($params['inthelast']) ? $params['inthelast'] :0 );
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'all';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'all');
		$source = !empty($params['source']) ? $params['source'] : 'all';
		$show_date_labels = !empty($params['showDateLabels']) ? $params['showDateLabels'] : '';
		$use_date	= !empty($params['useDate']) ? $params['useDate'] : '';
		
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		if(!empty($use_date))
		{
			$str_date_sub_start = "date(" . $str_date_sub_start;
			$str_date_sub_end .= ")";
		}
		
		
		//	1.	Calculating Interval specific variables
		switch($interval)
		{
			case 60:	//	Minute
				$mysql_date_format = empty($show_date_labels) ? "%m/%d/%Y\T%H:%i:00$time_zone" : "%Y-%m-%d %H:%i:00";
				$php_date_format = empty($show_date_labels) ? "m/d/Y\TH:i:00$time_zone" : "Y-m-d H:i:00";
				$interval_type = 'minute';
				break;
	
			case 3600:	//	Hour
				$mysql_date_format = empty($show_date_labels) ? "%m/%d/%Y\T%H:00:00$time_zone" : "%Y-%m-%d %H:00:00";
				$php_date_format = empty($show_date_labels) ? "m/d/Y\TH:00:00$time_zone" : "Y-m-d H:00:00";
				$interval_type = 'hour';
				break;
	
			case 86400:	//	Day
				// $mysql_date_format = '%m/%d/%Y';
				// $php_date_format = 'm/d/Y';
				$mysql_date_format = empty($show_date_labels) ? "%m/%d/%Y\T00:00:00$time_zone" : "%Y-%m-%d";
				$php_date_format = empty($show_date_labels) ? "m/d/Y\T00:00:00$time_zone" : "Y-m-d";
				$interval_type = 'day';
				break;
		}
		if(!empty($in_the_last))
		{
			$sql = "select " . $str_date_sub_start . "now()" . $str_date_sub_end . " as end_date, date_sub(" . $str_date_sub_start . "now()" . $str_date_sub_end . ", interval $in_the_last $interval_type) as start_date";
			// error_log("sql for finding start date and end date: " . $sql);
			$row = BasicDataObject::getDataRow($sql);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
		}
		
		$arr_where_clause = array();
		$arr_where_clause_views = array();
		$arr_where_clause_claims = array();
		$arr_where_clause_shares = array();
		$arr_where_clause_redeems = array();
		
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			/*$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);*/
 			$arr_where_clause_views[] = "iv.company_id = '$company_id'";
 			$arr_where_clause_claims[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.company_id = '$company_id'";
			/*
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
			*/
		}
		else
		{
			$arr_where_clause_views[] = "iv.items_id in (" . $campaign_ids . ")";
 			$arr_where_clause_claims[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.item_shared in (" . $campaign_ids . ")";
		}
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
		{
			$arr_where_clause_views[] = " iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_claims[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_redeems[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_shares[] = " r.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		}
		
		if(!empty($activity_type))
		{
			// $arr_where_clause[] = " ea.activity_type = '$activity_type'";
		}
		
		
		if(!empty($start_date) && !empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end. " between '$start_date' and '$end_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. " between '$start_date' and '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. " between '$start_date' and '$end_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end. " between '$start_date' and '$end_date'";

		}
		else if(!empty($start_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end. " >= '$start_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. " >= '$start_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. " >= '$start_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end. " >= '$start_date'";
	
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end. " <= '$end_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. " <= '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. " <= '$end_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end. " <= '$end_date'";
		}
		
		
		
		// SQL for Views
		$sql_views = "select date_format(" . $str_date_sub_start . "iv.created" . $str_date_sub_end. ", '$mysql_date_format') as date, iv.created as sort_date, 'view' as activity_type, count(id) as activity_count from items_views iv ";
		if(!empty($arr_where_clause_views))
			$sql_views .= " where " . implode(" and ", $arr_where_clause_views);
		$sql_views .= " group by date_format(" . $str_date_sub_start . "iv.created" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		// SQL for Claims
		$sql_claims = "select date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. ", '$mysql_date_format') as date, ui.date_claimed as sort_date, 'claim' as activity_type, count(id) as activity_count from user_items ui where user_id > 0 and date_claimed is not null";
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " and " . implode(" and ", $arr_where_clause_claims);
		$sql_claims .= " group by date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		// SQL for redeems
		$sql_redeems = "select date_format(" . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. ", '$mysql_date_format') as date, ui.date_redeemed as sort_date, 'redeem' as activity_type, count(id) as activity_count from user_items ui where user_id > 0 and date_redeemed is not null";
		if(!empty($arr_where_clause_redeems))
			$sql_redeems .= " and " .implode(" and ", $arr_where_clause_redeems);
		$sql_redeems .= " group by date_format(" . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		// SQL for Shares
		$sql_shares = "select date_format(" . $str_date_sub_start . "r.created" . $str_date_sub_end. ", '$mysql_date_format') as date, r.created as sort_date, 'share' as activity_type, count(id) as activity_count from referrals r ";
		if(!empty($arr_where_clause_shares))
			$sql_shares .= " where " .implode(" and ", $arr_where_clause_shares);
		$sql_shares .= " group by date_format(" . $str_date_sub_start . "r.created" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		// SQL for Referrals
		$sql_referrals = "select date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. ", '$mysql_date_format') as date, ui.date_claimed as sort_date, 'referral' as activity_type, count(id) as activity_count from user_items ui where user_id > 0 and date_claimed is not null and referral_id > 0";
		if(!empty($arr_where_clause_claims))
			$sql_referrals .= " and " .implode(" and ", $arr_where_clause_claims);
		$sql_referrals .= " group by date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		switch($activity_type)
		{
			case 'view':
				$sql = $sql_views;
				break;
			
			case 'claim':
				$sql = $sql_claims;
				break;
				
			case 'redeem':
				$sql = $sql_redeems;
				break;
			
			case 'share':
				$sql = $sql_shares;
				break;
			
			case 'referral':
				$sql = $sql_referrals;
				break;
			
			case 'all':
				$sql = implode(' union all ', array($sql_views, $sql_claims, $sql_redeems, $sql_shares, $sql_referrals));
				break;
			
		}
		$sql = "select t.* from ( 
		" . $sql . "
		) as t 
		order by t.sort_date";
		
		// error_log("SQL in CSAPI::getCampaignActivity(): " . $sql);
		
		$timer_start = array_sum(explode(" ", microtime()));
		$rs = Database::mysqli_query($sql);
		// error_log("CSAPI::getCampaignActivity(): Time taken to run the SQL: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		$response = array(
			'settings' => $params,
			'data' => array(),
			'graph_data' => array(),
			'totals' => array(),
		);
		
		$tmp_data = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$activity_type = $row['activity_type'];
			$tmp_date = $row['date'];
			$activity_count = $row['activity_count'] + 0;
			$tmp_data[$activity_type][$tmp_date] = $activity_count;
			$response['totals'][$activity_type] += $activity_count;
		}
		// error_log("CSAPI::getCampaignActivity(): Time taken to iterate through the records: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		// Getting start date
		if(empty($start_date))
		{
			Database::mysqli_data_seek($rs, 0);
			$row = Database::mysqli_fetch_assoc($rs);
			$start_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($start_date);
			$start_date = date_format($obj_dt, $php_date_format);
		}
		
		// Getting End Date
		if(empty($end_date))
		{
			Database::mysqli_data_seek($rs, Database::mysqli_num_rows($rs) - 1);
			$row = Database::mysqli_fetch_assoc($rs);
			$end_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($end_date);
			$end_date = date_format($obj_dt, $php_date_format);
		}
		// error_log("tmp_data: " . var_export($tmp_data, true));
		
		// error_log("CSAPI::getCampaignActivity(): Time taken to set the start and end pointers: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		
		$start_date_val = strtotime($start_date . " $str_time_zone");
		$end_date_val = strtotime($end_date . " $str_time_zone");
		
		// error_log("start_date: " . $start_date . ", start_date_val: " . $start_date_val . ", end_date: " . $end_date . ", start_date_val: " . $end_date_val);
		
		$current_date = Common::getDBCurrentDate(null, null, $mysql_date_format);
		$yesterdays_date = Common::getDBCurrentDate(-1, 'day', $mysql_date_format);
		
		$time_interval = new DateInterval('PT' . $interval . 'S');
		$pointStart = $start_date_val * 1000;
		$pointInterval = $interval * 1000;
		$activity_titles = array('view' => 'Views', 'claim' => 'Claims', 'redeem' => 'Redeems', 'share' => 'Shares', 'referral' => 'Referrals');
		foreach($tmp_data as $activity_type => $activity_data)
		{
			$total_activity_count = !empty($response['totals'][$activity_type]) ? $response['totals'][$activity_type] : 0;
			$activity_name = $activity_titles[$activity_type] . " (Total: " . $total_activity_count . ")";
			// $response['data'][$activity_type] = array();
			$cumm_entrant_count = 0;
			$tmp_date = $start_date;
			$series_data = array(
				'data' => array(),
				'name' => $activity_name,
				'pointStart' => $pointStart,
				'pointInterval' => $pointInterval,
				// 'activityType' => $activity_type,
			);
			if(!empty($show_date_labels))
				$series_data['activityType'] = $activity_type;
			
			for($i = $start_date_val; $i <= $end_date_val; $i += $interval)
			{
				// $tmp_date = date($php_date_format, $i);
				if(isset($activity_data[$tmp_date]))
				{
					$cumm_entrant_count += $activity_data[$tmp_date];
				}
				
				// $response['data'][$activity_type][$tmp_date] = $cumm_entrant_count;
				if(empty($show_date_labels))
				{
					$series_data['data'][] = !empty($activity_data[$tmp_date]) ? $activity_data[$tmp_date] + 0 : 0; //$cumm_entrant_count;
				}
				else
				{
					$series_data['data'][$tmp_date] = !empty($activity_data[$tmp_date]) ? $activity_data[$tmp_date] + 0 : 0;
				}
				
				$dt = DateTime::CreateFromFormat($php_date_format, $tmp_date);
				if(!$dt)
					error_log("failed to created date object! using format $php_date_format having date $tmp_date");

				$tmp_date = date_format($dt->add($time_interval), $php_date_format);
			}
			
			if(empty($show_date_labels))
				$response['graph_data'][] = $series_data;
			else
				$response['graph_data'][$activity_type] = $series_data;
		}
		
		// error_log("CSAPI::getCampaignActivity(): Time taken to set the final data: " . (array_sum(explode(" ", microtime())) - $timer_start));
		Database::mysqli_free_result($rs);

		if(!empty($errors))
			return $errors;
			
		return $response;
		
	}
	
	/**************************************************************
	
	Dropoff Funnel - Wireframe page - 3
	Target chart type:  http://www.highcharts.com/demo/funnel
	Example data format used by highcharts:
	data: [
					['Views',   15654],
					['Claims',  11432],
					['Redeems', 7943],
					['Shares',  245],
					['Referrals', 436]
				]

	DB tables:  tmp_entrant_campaign + traffic sources
	(need developer feedback on whether or not traffic source data is available and can be included)
	Description: Report the total activity statistics for a campaign (or all campaigns in a company), calculate the campaign ratio for each part of the funnel (view2claim, claim2share, claim2redeem, etc) report the average CoupSmart value for each ratio.  
	@param INTEGER companyId  required
	@param INTEGER campaignId  optional – default action is to show data for all campaigns in the company
	@param STRING startDate  - optional – default is to show all entrants from the first relevant activity
	@param STRING endDate  - optional – default is to show all entrants up until yesterday
	@param STRING timezone – optional – defaults to ‘-0500’
	@param STRING source   - optional filter – returns results that are specific to a particular source type – available options (‘all’, ‘facebook’, ‘shortcode’)

	@return ARRAY results (‘key’ => ‘val’, ‘key’ => ‘val’,… )

	
	******************************************************************/
	
	function getCumulativeCampaignActivity($params)
	{
		// error_log("params in getCumulativeCampaignActivity() : " . var_export($params, true));
		$graph_type = !empty($params['graphType']) ? $params['graphType'] : (!empty($params['graphtype']) ? $params['graphtype'] : 'normal');
		if($graph_type == 'normal')
		{
			$views = $this->getCompanyViews($params);
			$claims = $this->getCompanyClaims($params);
			$redeems = $this->getCompanyRedeems($params);
			$shares = $this->getCompanyShares($params);
			$referrals = $this->getCompanyReferrals($params);
			$graph_data = array(
				array('Views', $views),
				array('Claims', $claims),
				array('Redeems', $redeems),
				array('Shares', $shares),
				array('Referrals', $referrals),
			);
		}
		else if($graph_type == 'permissions')
		{
			/*
			$params['rejectedPermissions'] = 1;
			$rejected_permissions = $this->getCompanyViews($params);
			unset($params['rejectedPermissions']);
		
			$params['isNewUserClaim'] = 1;
			$accepted_permissions = $this->getCompanyClaims($params);
			unset($params['isNewUserClaim']);
			*/
			$accepted_rejected_permissions = $this->getAcceptedRejectedPermissions($params);
			$rejected_permissions = $accepted_rejected_permissions['Rejected'] + 0;
			$accepted_permissions = $accepted_rejected_permissions['Accepted'] + 0;
			$requested_permissions = $rejected_permissions + $accepted_permissions;
			
			$graph_data = array(
				// array('Requested', $requested_permissions),
				array('Rejected', $rejected_permissions),
				array('Accepted', $accepted_permissions),
			);
		}
		$response = array(
			'settings' => $params,
			'graph_data' => $graph_data
		);
		
		
		return $response;
	}
	
	/*
	ACCEPTED PERMISSIONS:	All distinct users who were either
		i.	Shown the facebook permissions dialog and accepted the permissions
		ii.	Had already accepted app permissions at some earlier point in time and so were registered Coupsmart users
		
	REJECTED PERMISSIONS Permissions rejected by unknown users + Permissions rejected by known users: 
		Permissions rejected by unknown users:	Number of all distinct user sessions where permissions are known to be rejected
		(Note: It wouldn't make much sense to include a count of all rejected permissions for the following reason: If some user hits Print, then rejects the permission, hits print again then rejects the permissions again and repeats the process once more, the number of permissions rejected should really just be 1, not 3.)
		
		Permissions rejected by known users: 	Number of all distinct known users where permissions are known to be rejected
		(Note: Question arises as to how could known users be shown the permissions dialog in the first place? The reason is that after they accepted our App at some earlier point in time, they may very well have removed it later from FB)
		
		
	*/
	function getAcceptedRejectedPermissions($params)
	{
	/*
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		$sql_test_accounts = !empty($arr_test_user_account_ids) ? " and user_id not in (" . implode(',', $arr_test_user_account_ids). ")" : "";
		$sql_company_id = !empty($company_id) ? "and iv.company_id = '$company_id'" : "";
		$sql_campaign_ids = !empty($campaign_ids) ? " and iv.items_id in ($campaign_ids)" : "";
		$inner_join_companies = !empty($company_id) ? "" : " inner join companies c on (c.status = 'active' and c.demo != '1' and iv.company_id = c.id)";
		
		
		
		$sql = "select count(distinct(if((user_id = 0 or user_id is null) and permissions_rejected > 0, session_id, null))) as UnknownUsersWhoRejected, count(distinct(if(user_id > 0 and permissions_rejected = 0, user_id, null))) as Accepted, count(distinct(if(user_id > 0 and permissions_rejected > 0, user_id, null))) as KnownUsersWhoRejected
		from items_views iv
		$inner_join_companies
		where (permissions_rejected > 0 or user_id > 0)
		and print_clicked = 1
		$sql_company_id
		$sql_campaign_ids
		$sql_test_accounts";
		// error_log("SQL in CSAPI::getAcceptedRejectedPermissions(): " . $sql);
		$permissions = BasicDataObject::getDataRow($sql);
		$permissions['Rejected'] = $permissions['UnknownUsersWhoRejected'] + $permissions['KnownUsersWhoRejected'];
		*/
		// TODOs: The users have to be unique and fetched from user_fb_likes
		
		$permissions = array(
			'Accepted' => $this->getAcceptedPermissions($params), 
			'Rejected' => $this->getRejectedPermissions($params)
		);
		
		
		
		return $permissions;
	}
	
	
	function getRejectedPermissions($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		$sql_test_accounts = !empty($arr_test_user_account_ids) ? " and user_id not in (" . implode(',', $arr_test_user_account_ids). ")" : "";
		$sql_company_id = !empty($company_id) ? "and iv.company_id = '$company_id'" : "";
		$sql_campaign_ids = !empty($campaign_ids) ? " and iv.items_id in ($campaign_ids)" : "";
		$inner_join_companies = !empty($company_id) ? "" : " inner join companies c on (c.status = 'active' and c.demo != '1' and iv.company_id = c.id)";
		$sql = "select count(distinct(session_id)) as Rejected 
		from items_views iv
		$inner_join_companies
		where permissions_rejected > 0
		and print_clicked = 1
		$sql_company_id
		$sql_campaign_ids
		$sql_test_accounts";
		error_log("SQL in CSAPI::getRejectedPermissions(): " . $sql);
		$permissions = BasicDataObject::getDataRow($sql);
		return $permissions['Rejected'];
		
	}
	
	function getAcceptedPermissions($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		$sql_test_accounts = !empty($arr_test_user_account_ids) ? " and user_id not in (" . implode(',', $arr_test_user_account_ids). ")" : "";
		$sql_company_id = !empty($company_id) ? "where ufl.company_id = '$company_id'" : "";
		$sql_campaign_ids = !empty($campaign_ids) ? " and ufl.item_id in ($campaign_ids)" : "";
		$inner_join_companies = !empty($company_id) ? "" : " inner join companies c on (c.status = 'active' and c.demo != '1' and ufl.company_id = c.id)";
		$sql = "select count(distinct(user_id)) as Accepted 
		from user_fb_likes ufl
		$inner_join_companies
		$sql_company_id
		$sql_campaign_ids
		$sql_test_accounts";
		error_log("SQL in CSAPI::getAcceptedPermissions(): " . $sql);
		$permissions = BasicDataObject::getDataRow($sql);
		return $permissions['Accepted'];
	}
	
	function getOptInRate($params)
	{
		$permissions = $this->getAcceptedRejectedPermissions($params);
		$total_permissions = $permissions['Accepted'] + $permissions['Rejected'];
		$opt_in_rate = round(($permissions['Accepted'] / $total_permissions) * 100, 2);
		return $opt_in_rate;
	}
	
	function getCampaignAnalyticsRecipients($company_id)
	{
		$sql = "select analytics_report_recipients from companies where id = '$company_id'";
		$row = BasicDataObject::getDataRow($sql);
		$analytics_report_recipients = $row['analytics_report_recipients'];
		$arr_recipient_emails = explode(',', $analytics_report_recipients);
		$res = array();
		foreach($arr_recipient_emails as $email)
		{
			$email = trim($email);
			if(!empty($email))
				$res[] = $email;
		}
		return $res;
	}
	
	function setCampaignAnalyticsRecipients($company_id, $arr_recipient_emails)
	{
		$analytics_report_recipients = implode(',', $arr_recipient_emails);
		$update_sql = "update companies set analytics_report_recipients = '" . Database::mysqli_real_escape_string($analytics_report_recipients) . "' where id = '$company_id'";
		Database::mysqli_query($update_sql);
	}
	
	
	
	
	function getCampaignActivityDayHour($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'view';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		
		$response['graph_data'] = array(
			'xAxis' => array(
				'categories' => array('12AM', '1AM', '2AM', '3AM', '4AM', '5AM', '6AM', '7AM', '8AM', '9AM', '10AM', '11AM', '12PM', '1PM', '2PM', '3PM', '4PM', '5PM', '6PM', '7PM', '8PM', '9PM', '10PM', '11PM')
			),
			'yAxis' => array(
				'categories' => array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')
			),
			'data' => array(),
		);
		
		
		
		
		$mysql_date_format = "%k,%w";
		$php_date_format = "G,w";
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		$arr_where_clause = array();
		$arr_where_clause_views = array();
		$arr_where_clause_claims = array();
		$arr_where_clause_shares = array();
		$arr_where_clause_redeems = array();
		
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			/*$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);*/
 			$arr_where_clause_views[] = "iv.company_id = '$company_id'";
 			$arr_where_clause_claims[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.company_id = '$company_id'";
			/*
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
			*/
		}
		else
		{
			$arr_where_clause_views[] = "iv.items_id in (" . $campaign_ids . ")";
 			$arr_where_clause_claims[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.item_shared in (" . $campaign_ids . ")";
		}
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
		{
			$arr_where_clause_views[] = " iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_claims[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_redeems[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_shares[] = " r.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		}
		
		if(!empty($start_date) && !empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " >= '$start_date'";

		}
		else if(!empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " <= '$end_date'";
		}
	
		
		// SQL for Views
		$sql_views = "select date_format(" . $str_date_sub_start . "iv.created" . $str_date_sub_end . ", '$mysql_date_format') as date, count(id) as activity_count from items_views iv";
		if(!empty($arr_where_clause_views))
			$sql_views .= " where " . implode(" and ", $arr_where_clause_views);
		$sql_views .= " group by date_format(" . $str_date_sub_start . "iv.created" . $str_date_sub_end . ", '$mysql_date_format')";
		
		// SQL for Claims
		$sql_claims = "select date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . ", '$mysql_date_format') as date, count(id) as activity_count from user_items ui where user_id > 0 and date_claimed is not null";
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " and " . implode(" and ", $arr_where_clause_claims);
		$sql_claims .= " group by date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . ", '$mysql_date_format')";
		
		// SQL for redeems
		$sql_redeems = "select date_format(" . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . ", '$mysql_date_format') as date, count(id) as activity_count from user_items ui where user_id > 0 and date_redeemed is not null";
		if(!empty($arr_where_clause_redeems))
			$sql_redeems .= " and " .implode(" and ", $arr_where_clause_redeems);
		$sql_redeems .= " group by date_format(" . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . ", '$mysql_date_format')";
		
		// SQL for Shares
		$sql_shares = "select date_format(" . $str_date_sub_start . "r.created" . $str_date_sub_end . ", '$mysql_date_format') as date, count(id) as activity_count from referrals r";
		if(!empty($arr_where_clause_shares))
			$sql_shares .= " where " .implode(" and ", $arr_where_clause_shares);
		$sql_shares .= " group by date_format(" . $str_date_sub_start . "r.created" . $str_date_sub_end . ", '$mysql_date_format')";
		
		// SQL for Referrals
		$sql_referrals = "select date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . ", '$mysql_date_format') as date, count(id) as activity_count from user_items ui where user_id > 0 and date_claimed is not null and referral_id > 0";
		if(!empty($arr_where_clause_claims))
			$sql_referrals .= " and " .implode(" and ", $arr_where_clause_claims);
		$sql_referrals .= " group by date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . ", '$mysql_date_format')";
		
		switch($activity_type)
		{
			case 'view':
				$sql = $sql_views;
				break;
			
			case 'claim':
				$sql = $sql_claims;
				break;
				
			case 'redeem':
				$sql = $sql_redeems;
				break;
			
			case 'share':
				$sql = $sql_shares;
				break;
			
			case 'referral':
				$sql = $sql_referrals;
				break;
			
			case 'all':
				$sql = "select t.date, sum(t.activity_count) as activity_count from (" . implode(' union all ', array($sql_views, $sql_claims, $sql_redeems, $sql_shares, $sql_referrals)) . ") as t group by t.date";
				break;
		}
		
		// error_log("SQL in CSAPI::getCampaignActivityDayHour(): " . $sql);
		$rs = Database::mysqli_query($sql);
		$tmp_data = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$date = $row['date'];
			$activity_count = $row['activity_count'] + 0;
			$data = explode(',', $date);
			$i = $data[0];
			$j = $data[1];
			
			// $data[] = $activity_count;
			// $response[] = $data;
			// $response['graph']['data'][$i . ',' . $j] = array($i, $j, $activity_count);
			$tmp_data[$i][$j] = $activity_count;
		}
		
		foreach($response['graph_data']['xAxis']['categories'] as $i => $hour_of_day)
		{
			foreach($response['graph_data']['yAxis']['categories'] as $j => $day_of_week)
			{
				$activity_count = isset($tmp_data[$i][$j]) ? $tmp_data[$i][$j] : 0;
				$response['graph_data']['data'][] = array($i, $j, $activity_count);
			}
		}
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	/****************************************
	
	DEMOGRAPHICS SECION - FUNCTIONS
	
	
	******************************************/
	
	function getEntrantLocations($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'view';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		$location_type = !empty($params['locationType']) ? $params['locationType'] : (!empty($params['locationtype']) ? $params['locationtype'] :'dma');
		
		$arr_location_types = array('dma' => 'dma_region', 'state' => 'state');
		$location_type = !empty($arr_location_types[$location_type]) ? $arr_location_types[$location_type] : 'dma_region';
		
		$response['graph_data'] = array(
		);

		$mysql_date_format = "%l,%w";
		$php_date_format = "g,w";
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		$arr_where_clause = array();
		$arr_where_clause_views = array();
		$arr_where_clause_claims = array();
		$arr_where_clause_shares = array();
		$arr_where_clause_redeems = array();
		
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			// $arr_campaign_ids = array(-1);
			// $rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			// while($row = Database::mysqli_fetch_assoc($rs))
				// $arr_campaign_ids[] = $row['campaign_id'];
			// $campaign_ids = implode(',', $arr_campaign_ids);
 			$arr_where_clause_views[] = "iv.company_id = '$company_id'";
 			$arr_where_clause_claims[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.company_id = '$company_id'"; // "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.company_id = '$company_id'";
			/*
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
			*/
		}
		else
		{
			$arr_where_clause_views[] = "iv.items_id in (" . $campaign_ids . ")";
 			$arr_where_clause_claims[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_shares[] = "r.item_shared in (" . $campaign_ids . ")";
		}
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
		{
			$arr_where_clause_views[] = " iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_claims[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_redeems[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$arr_where_clause_shares[] = " r.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		}
		
		if(!empty($start_date) && !empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " >= '$start_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " >= '$start_date'";

		}
		else if(!empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " <= '$end_date'";
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " <= '$end_date'";
		}
	
		
		// SQL for Views
		$sql_views = "select dma.$location_type, dma.dma_region_code, dma.id as dma_location_id, count(iv.id) as activity_count from items_views iv inner join users u on iv.user_id = u.id inner join fb_locations_dma_regions fbl on u.facebook_location_id = fbl.fb_location_id inner join cities_dma_regions dma on fbl.city_dma_region_id = dma.id where iv.user_id > 0 ";
		if(!empty($arr_where_clause_views))
			$sql_views .= " and " . implode(" and ", $arr_where_clause_views);
		$sql_views .= " group by dma.$location_type";
		
		// SQL for Claims
		$sql_claims = "select dma.$location_type, dma.dma_region_code, dma.id as dma_location_id, count(ui.id) as activity_count from user_items ui inner join users u on ui.user_id = u.id inner join fb_locations_dma_regions fbl on u.facebook_location_id = fbl.fb_location_id inner join cities_dma_regions dma on fbl.city_dma_region_id = dma.id where ui.date_claimed is not null and ui.user_id > 0";
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " and " . implode(" and ", $arr_where_clause_claims);
		$sql_claims .= " group by dma.$location_type";
		
		// SQL for redeems
		$sql_redeems = "select dma.$location_type, dma.dma_region_code, dma.id as dma_location_id, count(ui.id) as activity_count from user_items ui inner join users u on ui.user_id = u.id inner join fb_locations_dma_regions fbl on u.facebook_location_id = fbl.fb_location_id inner join cities_dma_regions dma on fbl.city_dma_region_id = dma.id where ui.date_redeemed is not null and ui.user_id > 0";
		if(!empty($arr_where_clause_redeems))
			$sql_redeems .= " and " .implode(" and ", $arr_where_clause_redeems);
		$sql_redeems .= " group by dma.$location_type";
		
		// SQL for Shares
		$sql_shares = "select dma.$location_type, dma.dma_region_code, dma.id as dma_location_id, count(r.id) as activity_count from referrals r inner join users u on r.user_id = u.id inner join fb_locations_dma_regions fbl on u.facebook_location_id = fbl.fb_location_id inner join cities_dma_regions dma on fbl.city_dma_region_id = dma.id where r.user_id > 0";
		if(!empty($arr_where_clause_shares))
			$sql_shares .= " and " .implode(" and ", $arr_where_clause_shares);
		$sql_shares .= " group by dma.$location_type";
		
		// SQL for Referrals
		$sql_referrals = "select dma.$location_type, dma.dma_region_code, dma.id as dma_location_id, count(ui.id) as activity_count from user_items ui inner join users u on ui.user_id = u.id inner join fb_locations_dma_regions fbl on u.facebook_location_id = fbl.fb_location_id inner join cities_dma_regions dma on fbl.city_dma_region_id = dma.id where ui.referral_id is not null and ui.date_claimed is not null and ui.user_id > 0";
		if(!empty($arr_where_clause_claims))
			$sql_referrals .= " and " .implode(" and ", $arr_where_clause_claims);
		$sql_referrals .= " group by dma.$location_type";
		
		switch($activity_type)
		{
			case 'view':
				$sql = $sql_views;
				break;
			
			case 'claim':
				$sql = $sql_claims;
				break;
				
			case 'redeem':
				$sql = $sql_redeems;
				break;
			
			case 'share':
				$sql = $sql_shares;
				break;
			
			case 'referral':
				$sql = $sql_referrals;
				break;
			
			case 'all':
				$sql = "select t.$location_type, t.dma_region_code, t.dma_location_id, sum(t.activity_count) as activity_count from (" . implode(' union all ', array($sql_views, $sql_claims, $sql_redeems, $sql_shares, $sql_referrals)) . ") as t group by t.$location_type";
				break;
		}
		
		// error_log("SQL in CSAPI::getEntrantLocations(): " . $sql);
		// require_once(dirname(__DIR__) . '/includes/highmaps-us-dma-data.php');
		// global $us_dma_data_json;
		// $dma_data = json_decode($us_dma_data_json, true);
		
		$tmp_data = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$activity_count = $row['activity_count'] + 0;
			$location_name = $row[$location_type];

			$tmp_data[$location_name] = $activity_count;
			
		}
		
		$sql = "select * from cities_dma_regions group by $location_type";
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$dma_region_code = $row['dma_region_code'];
			$location_name = $row[$location_type];
			$dma_location_id = $row['id'];
			$activity_count = isset($tmp_data[$location_name]) ? $tmp_data[$location_name] : 0;
			$response['graph_data'][] = array(
				'code' =>'US-' . $dma_region_code,
				'value' => $activity_count,
				'name' => $location_name,
				'id' => $dma_location_id,
			);
		}
		
		// error_log("dma_data: " . var_export($dma_data, true));
		
		// $response['graph_data'] = $tmp_data;
		$response['settings'] = $params;
		// error_log("response in getEntrantLocations(): " . var_export($response, true));
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	
	
	/**********************************************************************
	DEMOGRAPHICS - DRILL DOWN TABLE
	
	***********************************************************************/
	function getDemographicsDrillDownTableLocation($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		
		$sort = !empty($params['sort']) ? $params['sort'] : '';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] : 'asc');
		
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			
			$where_clause_views = " and iv.company_id = '$company_id'";
			$where_clause_shares = " and r.company_id = '$company_id'";
		}
		else
		{
			$where_clause_views = " and iv.items_id in (" . $campaign_ids. ")";
			$where_clause_shares = " and r.item_shared in (" . $campaign_ids. ")";
		}
		$where_clause_claims = " and ui.item_id in (" . $campaign_ids. ")";
		
		
		// SQL to find the number of entrants
		
		$sql = "select dma.dma_region, count(t2.user_id) as num_users from
		(
			select distinct(t.user_id) as user_id from
			(select iv.user_id from items_views iv where iv.user_id > 0 $where_clause_views
			union all 
			select ui.user_id from user_items ui where ui.user_id > 0 $where_clause_claims 
			union all 
			select r.user_id from referrals r where r.user_id > 0 $where_clause_shares
			) as t
		) as t2
		inner join users u on t2.user_id = u.id
		inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id 
		inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id
		where u.is_test_account != 1
		 group by dma.dma_region ";
		 // error_log("SQL for finding num users in CSAPI::getDemographicsDrillDownTableLocation(): " . $sql);
		$arr_location_users = array();
		$total_num_users = 0;
		$response['data'] = array(); // Data
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$dma_region = $row['dma_region'];
			$num_users = $row['num_users'] + 0;
			$arr_location_users[$dma_region] = $num_users;
			$total_num_users += $num_users;
		}
		
		
		

		$sql =	"select concat(`city`, ', ', `state_code`) as dma_region, sum(views) as views, sum(claims) as claims, sum(redeems) as redeems, sum(shares) as shares, sum(referrals) as referrals from
		(select dma.dma_region, dma.city, dma.state_code, count(iv.id) as views, 0 as claims, 0 as redeems, 0 as shares, 0 as referrals from items_views iv inner join users u on iv.user_id = u.id inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id where iv.user_id > 0 and u.is_test_account != 1 $where_clause_views group by dma.dma_region 
		union all 
		select dma.dma_region, dma.city, dma.state_code, 0 as views, count(ui.id) as claims, 0 as redeems, 0 as shares, 0 as referrals from user_items ui inner join users u on ui.user_id = u.id inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id where ui.date_claimed is not null and ui.user_id > 0 and u.is_test_account != 1 $where_clause_claims group by dma.dma_region
		union all
		select dma.dma_region, dma.city, dma.state_code, 0 as views, 0 as claims, count(ui.id) as redeems, 0 as shares, 0 as referrals from user_items ui inner join users u on ui.user_id = u.id inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id where ui.date_redeemed is not null and ui.user_id > 0 and u.is_test_account != 1 $where_clause_claims group by dma.dma_region
		union all
		select dma.dma_region, dma.city, dma.state_code, 0 as views, 0 as claims, 0 as redeems, count(r.id) as shares, 0 as referrals from referrals r inner join users u on r.user_id = u.id inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id where r.user_id > 0 and u.is_test_account != 1 $where_clause_shares group by dma.dma_region 
		union all 
		select dma.dma_region, dma.city, dma.state_code, 0 as views, 0 as claims, 0 as redeems, 0 as shares, count(ui.id) as referrals from user_items ui inner join users u on ui.user_id = u.id inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id where ui.referral_id is not null and ui.date_claimed is not null and ui.user_id > 0 and u.is_test_account != 1 $where_clause_claims group by dma.dma_region) as t
		 group by dma_region";
		// error_log("SQL in CSAPI::getDemographicsDrillDownTableLocation() : " . $sql);
		
		$rs = Database::mysqli_query($sql);
		$dma_cities = array();
		
		$total_views	= 0;
		$total_claims	= 0;
		$total_redeems	= 0;
		$total_shares	= 0;
		$total_referrals = 0;

		
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $activity_count = $row['activity_count'] + 0;
			// $activity_type = $row['activity_type'];
			$dma_region = $row['dma_region'];
			// if(!isset($response[$age_range_name][$gender][$activity_type]))
			//	$response[$age_range_name][$gender][$activity_type] = 0;
			//	$response[$age_range_name][$gender][$activity_type] += $activity_count;
			
			$users = isset($arr_location_users[$dma_region]) ? $arr_location_users[$dma_region] : 0;
			$views = $row['views'] + 0;
			$claims = $row['claims'] + 0;
			$redeems = $row['redeems'] + 0;
			$shares = $row['shares'] + 0;
			$referrals = $row['referrals'] + 0;
			
			$total_views	+= $views;
			$total_claims	+= $claims;
			$total_redeems	+= $redeems;
			$total_shares	+= $shares;
			$total_referrals += $referrals;
			
			$tmp_data = array(
				'dma_region' => $dma_region,
				'users' => $users,
				'views' => $views,
				'claims' => $claims,
				'redeems' => $redeems,
				'shares' => $shares,
				'referrals' => $referrals,
			);
			
			$response['data'][] = $tmp_data;
			
		}
		
		foreach($response['data'] as $i => $tmp_data) 
		{
			$response['data'][$i]['percent_users'] = !empty($total_num_users) ? round(($response['data'][$i]['users'] * 100) / $total_num_users, 1) : 0;
			$response['data'][$i]['percent_views'] = !empty($total_views) ? round(($response['data'][$i]['views'] * 100) / $total_views, 1) : 0;
			$response['data'][$i]['percent_claims'] = !empty($total_claims) ? round(($response['data'][$i]['claims'] * 100) / $total_claims, 1) : 0;
			$response['data'][$i]['percent_redeems'] = !empty($total_redeems) ? round(($response['data'][$i]['redeems'] * 100) / $total_redeems, 1) : 0;
			$response['data'][$i]['percent_shares'] = !empty($total_shares) ? round(($response['data'][$i]['shares'] * 100) / $total_shares, 1) : 0;
			$response['data'][$i]['percent_referrals'] = !empty($total_referrals) ? round(($response['data'][$i]['referrals'] * 100) / $total_referrals, 1) : 0;
		}
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['data'], $sort, $sort_order);
		
		$response['categories'] = array(
				'dma_region',
				'users',
				'views',
				'claims',
				'redeems',
				'shares',
				'referrals',
				'percent_users',
				'percent_views',
				'percent_claims',
				'percent_redeems',
				'percent_shares',
				'percent_referrals'
		);
		
		$response['settings'] = $params;
		/*
		$html = '<table role="grid" style="width: 100%; margin-bottom: 0px;">
		  <thead>
		    <tr>
		      <th width="20%">Market Area</th>
		      <th width="10%">Country</th>
		      <th width="20%">City</th>
		      <th width="10%">Users</th>
		      <th width="10%">Views</th>
		      <th width="10%">Claims</th>
		      <th width="10%">Shares</th>
		      <th width="10%">Redeems</th>
	    	</tr>
		  </thead>
		  <tbody>';
		foreach($response as $dma_region => $data)
		{
			$country = 'US'; // Hard coded for now
			$views =	!empty($data['view']) ? $data['view'] : 0;
			$claims =	!empty($data['claim']) ? $data['claim'] : 0;
			$redeems =	!empty($data['redeem']) ? $data['redeem'] : 0;
			$shares =	!empty($data['share']) ? $data['share'] : 0;
			$referrals = !empty($data['referral']) ? $data['referral'] : 0;
			
			$users = isset($arr_location_users[$dma_region]) ? $arr_location_users[$dma_region] : 0;
			$percent_users = round(($users * 100) / $total_num_users, 1);
			$percent_views = round(($views * 100) / $total_num_users, 1);
			$percent_claims = round(($claims * 100) / $total_num_users, 1);
			$percent_redeems = round(($redeems * 100) / $total_num_users, 1);
			$percent_shares = round(($shares * 100) / $total_num_users, 1);
			$percent_referrals = round(($referrals * 100) / $total_num_users, 1);
			
			$city = isset($dma_cities[$dma_region]) ? $dma_cities[$dma_region] : '';

			$html .= "<tr>
		      <td>$dma_region</td>
		      <td>$country</td>
		      <td>$city</td>
		      <td>$users ($percent_users%)</td>
		      <td>$views ($percent_views%)</td>
		      <td>$claims ($percent_claims%)</td>
		      <td>$shares ($percent_shares%)</td>
		      <td>$redeems ($percent_redeems%)</td>
		    </tr>";
		}
		$html .= '</tbody>
		</table>';
		$response['html']=$html;
		*/
		Database::mysqli_free_result($rs);

		return $response;
	}
	
	/**********************************************************************
	DEMOGRAPHICS - DRILL DOWN TABLE
	
	***********************************************************************/
	function getDemographicsDrillDownTableRelationship($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		
		$sort = !empty($params['sort']) ? $params['sort'] : '';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] : 'asc');
		
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			
			$where_clause_views = " and iv.company_id = '$company_id'";
			$where_clause_shares = " and r.company_id = '$company_id'";
		}
		else
		{
			$where_clause_views = " and iv.items_id in (" . $campaign_ids. ")";
			$where_clause_shares = " and r.item_shared in (" . $campaign_ids. ")";
		}
		$where_clause_claims = " and ui.item_id in (" . $campaign_ids. ")";
		
		// SQL to find the number of entrants
		$sql = "select u.relationship_status, count(t2.user_id) as num_users from
		(
			select distinct(t.user_id) as user_id from
			(select iv.user_id from items_views iv where iv.user_id > 0 $where_clause_views
			union all 
			select ui.user_id from user_items ui where ui.user_id > 0 $where_clause_claims 
			union all 
			select r.user_id from referrals r where r.user_id > 0 $where_clause_shares
			) as t
		) as t2
		inner join users u on t2.user_id = u.id
		 group by u.relationship_status ";
		 // error_log("SQL for finding num users inarr_users CSAPI::getDemographicsDrillDownTableRelationship(): " . $sql);
		$arr_users = array();
		$total_num_users = 0;
		$response['data'] = array(); // Data
		
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$relationship_status = $row['relationship_status'];
			$num_users = $row['num_users'] + 0;
			$arr_users[$relationship_status] = $num_users;
			$total_num_users += $num_users;
		}
		
		
		

		$sql =	" select relationship_status, sum(views) as views, sum(claims) as claims, sum(redeems) as redeems, sum(shares) as shares, sum(referrals) as referrals from
		(select u.relationship_status, 'view' as activity_type, count(iv.id) as views, 0 as claims, 0 as redeems, 0 as shares, 0 as referrals from items_views iv inner join users u on iv.user_id = u.id where iv.user_id > 0 and u.is_test_account != 1 $where_clause_views group by u.relationship_status 
		union all 
		select u.relationship_status, 'claim' as activity_type, 0 as views, count(ui.id) as claims, 0 as redeems, 0 as shares, 0 as referrals from user_items ui inner join users u on ui.user_id = u.id where ui.date_claimed is not null and ui.user_id > 0 and u.is_test_account != 1 $where_clause_claims group by u.relationship_status
		union all
		select u.relationship_status, 'redeem' as activity_type, 0 as views, 0 as claims, count(ui.id) as redeems, 0 as shares, 0 as referrals from user_items ui inner join users u on ui.user_id = u.id where ui.date_redeemed is not null and ui.user_id > 0 and u.is_test_account != 1 $where_clause_claims group by u.relationship_status
		union all
		select u.relationship_status, 'share' as activity_type, 0 as views, 0 as claims, 0 as redeems, count(r.id) as shares, 0 as referrals from referrals r inner join users u on r.user_id = u.id where r.user_id > 0 and u.is_test_account != 1 $where_clause_shares group by u.relationship_status
		union all 
		select u.relationship_status, 'referral' as activity_type, 0 as views, 0 as claims, 0 as redeems, 0 as shares, count(ui.id) as referrals from user_items ui inner join users u on ui.user_id = u.id where ui.referral_id is not null and ui.date_claimed is not null and ui.user_id > 0 and u.is_test_account != 1 $where_clause_claims group by u.relationship_status
		) as t
		 group by relationship_status";
		// error_log("SQL in CSAPI::getDemographicsDrillDownTableRelationship() : " . $sql);
		
		$rs = Database::mysqli_query($sql);
		
		$total_views	= 0;
		$total_claims	= 0;
		$total_redeems	= 0;
		$total_shares	= 0;
		$total_referrals = 0;
		
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $activity_count = $row['activity_count'] + 0;
			// $activity_type = $row['activity_type'];
			$relationship_status = $row['relationship_status'];
		// if(!isset($response[$age_range_name][$gender][$activity_type]))
			//	$response[$age_range_name][$gender][$activity_type] = 0;
			//	$response[$age_range_name][$gender][$activity_type] += $activity_count;
			
			$users = isset($arr_users[$relationship_status]) ? $arr_users[$relationship_status] : 0;
			$views = $row['views'] + 0;
			$claims = $row['claims'] + 0;
			$redeems = $row['redeems'] + 0;
			$shares = $row['shares'] + 0;
			$referrals = $row['referrals'] + 0;
			
			$total_views	+= $views;
			$total_claims	+= $claims;
			$total_redeems	+= $redeems;
			$total_shares	+= $shares;
			$total_referrals += $referrals;
		
			
			$relationship_status = !empty($row['relationship_status']) ? $row['relationship_status'] : 'Not Provided';
			$tmp_data = array(
				'relationship_status' => $relationship_status,
				'users' => $users,
				'views' => $views,
				'claims' => $claims,
				'redeems' => $redeems,
				'shares' => $shares,
				'referrals' => $referrals,
			);
			
			$response['data'][] = $tmp_data;
			
		}
		
		foreach($response['data'] as $i => $tmp_data) 
		{
			$response['data'][$i]['percent_users'] = !empty($total_num_users) ? round(($response['data'][$i]['users'] * 100) / $total_num_users, 1) : 0;
			$response['data'][$i]['percent_views'] = !empty($total_views) ? round(($response['data'][$i]['views'] * 100) / $total_views, 1) : 0;
			$response['data'][$i]['percent_claims'] = !empty($total_claims) ? round(($response['data'][$i]['claims'] * 100) / $total_claims, 1) : 0;
			$response['data'][$i]['percent_redeems'] = !empty($total_redeems) ? round(($response['data'][$i]['redeems'] * 100) / $total_redeems, 1) : 0;
			$response['data'][$i]['percent_shares'] = !empty($total_shares) ? round(($response['data'][$i]['shares'] * 100) / $total_shares, 1) : 0;
			$response['data'][$i]['percent_referrals'] = !empty($total_referrals) ? round(($response['data'][$i]['referrals'] * 100) / $total_referrals, 1) : 0;
		}
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['data'], $sort, $sort_order);
		
		$response['categories'] = array(
				'relationship_status',
				'users',
				'views', 
				'claims', 
				'redeems', 
				'shares',
				'referrals',
				'percent_users',
				'percent_views',
				'percent_claims',
				'percent_redeems',
				'percent_shares',
				'percent_referrals'
		); 
				
		$response['settings'] = $params;
		/*
		$html = '<table role="grid" style="width: 100%; margin-bottom: 0px;">
		  <thead>
		    <tr>
		      <th width="50%">Relationship Status</th>
		      <th width="10%">Users</th>
		      <th width="10%">Views</th>
		      <th width="10%">Claims</th>
		      <th width="10%">Shares</th>
		      <th width="10%">Redeems</th>
	    	</tr>
		  </thead>
		  <tbody>';
		foreach($response as $relationship_status => $data)
		{
			$country = 'US'; // Hard coded for now
			$views =	!empty($data['view']) ? $data['view'] : 0;
			$claims =	!empty($data['claim']) ? $data['claim'] : 0;
			$redeems =	!empty($data['redeem']) ? $data['redeem'] : 0;
			$shares =	!empty($data['share']) ? $data['share'] : 0;
			$referrals = !empty($data['referral']) ? $data['referral'] : 0;
			
			$users = isset($arr_users[$relationship_status]) ? $arr_users[$relationship_status] : 0;
			$percent_users = round(($users * 100) / $total_num_users, 1);
			$percent_views = round(($views * 100) / $total_num_users, 1);
			$percent_claims = round(($claims * 100) / $total_num_users, 1);
			$percent_redeems = round(($redeems * 100) / $total_num_users, 1);
			$percent_shares = round(($shares * 100) / $total_num_users, 1);
			$percent_referrals = round(($referrals * 100) / $total_num_users, 1);

			$html .= "<tr>
		      <td>$relationship_status</td>
		      <td>$users ($percent_users%)</td>
		      <td>$views ($percent_views%)</td>
		      <td>$claims ($percent_claims%)</td>
		      <td>$shares ($percent_shares%)</td>
		      <td>$redeems ($percent_redeems%)</td>
		    </tr>";
		}
		$html .= '</tbody>
		</table>';

		$response['html']=$html;
		*/
		Database::mysqli_free_result($rs);
		
		return $response;
	}
	
	function getDemographicsDrillDownTableAgeGender($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		
		$sort = !empty($params['sort']) ? $params['sort'] : '';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] : 'asc');
		
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			
			$where_clause_views = " and iv.company_id = '$company_id'";
			$where_clause_shares = " and r.company_id = '$company_id'";
		}
		else
		{
			$where_clause_views = " and iv.items_id in (" . $campaign_ids. ")";
			$where_clause_shares = " and r.item_shared in (" . $campaign_ids. ")";
		}
		$where_clause_claims = " and ui.item_id in (" . $campaign_ids. ")";
		
		
		// SQL to find the number of entrants
		
		$sql = "select ar.age_range_name, t3.gender, count(t3.user_id) as num_users from
		(
		select DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender, u.id as user_id from
		(
			select distinct(t.user_id) as user_id from
			(select iv.user_id from items_views iv where iv.user_id > 0 $where_clause_views
			union all 
			select ui.user_id from user_items ui where ui.user_id > 0 $where_clause_claims 
			union all 
			select r.user_id from referrals r where r.user_id > 0 $where_clause_shares
			) as t
		) as t2
		inner join users u on t2.user_id = u.id
		) as t3
		inner join api_age_range ar on t3.age between ar.age_min and ar.age_max
		 group by ar.age_range_name, t3.gender ";
		 // error_log("SQL for finding num users inarr_users CSAPI::getDemographicsDrillDownTableRelationship(): " . $sql);
		$arr_users = array();
		$total_num_users = 0;
		$users_by_gender = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$age_range_name = $row['age_range_name'];
			$gender = !empty($row['gender']) ? $row['gender'] : "Unknown";
			$num_users = $row['num_users'] + 0;
			$arr_users[$age_range_name][$gender] = $num_users;
			$total_num_users += $num_users;
			
			$users_by_gender[$gender] += $num_users;
		}
		$total_num_males = $users_by_gender['Male'];
		$total_num_females = $users_by_gender['Female'];

		$sql =	"select age_range_name, gender, sum(views) as views, sum(claims) as claims, sum(redeems) as redeems, sum(shares) as shares, sum(referrals) as referrals from
		(
		select ar.age_range_name, t.gender, count(t.id) as views, 0 as claims, 0 as redeems, 0 as shares, 0 as referrals
		from
		(
		select DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender, iv.id from items_views iv inner join users u on iv.user_id = u.id where iv.user_id > 0 and u.date_of_birth is not null and u.date_of_birth != '0000-00-00' and u.is_test_account != 1 $where_clause_views
		) as t
		inner join api_age_range ar on t.age between ar.age_min and ar.age_max
		group by ar.age_range_name, t.gender

		union all 

		select ar.age_range_name, t.gender, 0 as views, count(t.id) as claims, 0 as redeems, 0 as shares, 0 as referrals
		from
		(
		select DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender, ui.id from user_items ui inner join users u on ui.user_id = u.id where ui.date_claimed is not null and ui.user_id > 0 and u.date_of_birth is not null and u.date_of_birth != '0000-00-00' and u.is_test_account != 1 $where_clause_claims
		) as t
		inner join api_age_range ar on t.age between ar.age_min and ar.age_max
		group by ar.age_range_name, t.gender

		union all

		select ar.age_range_name, t.gender, 0 as views, 0 as claims, count(t.id) as redeems, 0 as shares, 0 as referrals
		from
		(
		select DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender, ui.id from user_items ui inner join users u on ui.user_id = u.id where ui.date_redeemed is not null and ui.user_id > 0 and u.date_of_birth is not null and u.date_of_birth != '0000-00-00' and u.is_test_account != 1 $where_clause_claims
		) as t
		inner join api_age_range ar on t.age between ar.age_min and ar.age_max
		group by ar.age_range_name, t.gender

		union all

		select ar.age_range_name, t.gender, 0 as views, 0 as claims, 0 as redeems, count(t.id) as shares, 0 as referrals
		from
		(
		select DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender, r.id from referrals r inner join users u on r.user_id = u.id where r.user_id > 0 and u.date_of_birth is not null and u.date_of_birth != '0000-00-00' and u.is_test_account != 1 $where_clause_shares
		) as t
		inner join api_age_range ar on t.age between ar.age_min and ar.age_max
		group by ar.age_range_name, t.gender

		union all 

		select ar.age_range_name, t.gender, 0 as views, 0 as claims, 0 as redeems, 0 as shares, count(t.id) as referrals
		from
		(
		select DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender, ui.id from user_items ui inner join users u on ui.user_id = u.id where ui.date_claimed is not null and ui.referral_id is not null and ui.user_id > 0 and u.date_of_birth is not null and u.date_of_birth != '0000-00-00' and u.is_test_account != 1 $where_clause_claims
		) as t
		inner join api_age_range ar on t.age between ar.age_min and ar.age_max
		group by ar.age_range_name, t.gender
		) as t2
		 group by age_range_name, gender";
		// error_log("SQL in CSAPI::getDemographicsDrillDownTableAgeGEnder() : " . $sql);
		
		
		$response['data'] = array(); // Data
		$response['gender_totals'] = $users_by_gender;	//	Gender Totals
		
		$rs = Database::mysqli_query($sql);
		
		$total_views	= 0;
		$total_claims	= 0;
		$total_redeems	= 0;
		$total_shares	= 0;
		$total_referrals = 0;
		
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $activity_count = $row['activity_count'] + 0;
			// $activity_type = $row['activity_type'];
			$age_range_name = $row['age_range_name'];
			$gender = $row['gender'];
			// if(!isset($response[$age_range_name][$gender][$activity_type]))
			//	$response[$age_range_name][$gender][$activity_type] = 0;
			//	$response[$age_range_name][$gender][$activity_type] += $activity_count;
			
			$users = isset($arr_users[$age_range_name][$gender]) ? $arr_users[$age_range_name][$gender] : 0;
			$views = $row['views'] + 0;
			$claims = $row['claims'] + 0;
			$redeems = $row['redeems'] + 0;
			$shares = $row['shares'] + 0;
			$referrals = $row['referrals'] + 0;
			
			$total_views	+= $views;
			$total_claims	+= $claims;
			$total_redeems	+= $redeems;
			$total_shares	+= $shares;
			$total_referrals += $referrals;

			$tmp_data = array(
				'age_range' => $age_range_name,
				'gender' => $gender,
				'users' => $users,
				'views' => $views,
				'claims' => $claims,
				'redeems' => $redeems,
				'shares' => $shares,
				'referrals' => $referrals,
			);
			
			$response['data'][] = $tmp_data;
			
		}
		
		foreach($response['data'] as $i => $tmp_data) 
		{
			$response['data'][$i]['percent_users'] = !empty($total_num_users) ? round(($response['data'][$i]['users'] * 100) / $total_num_users, 1) : 0;
			$response['data'][$i]['percent_views'] = !empty($total_views) ? round(($response['data'][$i]['views'] * 100) / $total_views, 1) : 0;
			$response['data'][$i]['percent_claims'] = !empty($total_claims) ? round(($response['data'][$i]['claims'] * 100) / $total_claims, 1) : 0;
			$response['data'][$i]['percent_redeems'] = !empty($total_redeems) ? round(($response['data'][$i]['redeems'] * 100) / $total_redeems, 1) : 0;
			$response['data'][$i]['percent_shares'] = !empty($total_shares) ? round(($response['data'][$i]['shares'] * 100) / $total_shares, 1) : 0;
			$response['data'][$i]['percent_referrals'] = !empty($total_referrals) ? round(($response['data'][$i]['referrals'] * 100) / $total_referrals, 1) : 0;
		}
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['data'], $sort, $sort_order);
		
		
		$response['categories'] = array(
			'age_range',
			'gender',
			'users',
			'views',
			'claims',
			'redeems',
			'shares',
			'referrals',
			'percent_users',
			'percent_views',
			'percent_claims',
			'percent_redeems',
			'percent_shares',
			'percent_referrals'
		);
		
		$response['settings'] = $params;
		/*
		$html = '<table role="grid" style="width: 100%; margin-bottom: 0px;">
		  <thead>
		    <tr>
		      <th width="25%">Age Range</th>
		      <th width="25%">Gender</th>
		      <th width="10%">Users</th>
		      <th width="10%">Views</th>
		      <th width="10%">Claims</th>
		      <th width="10%">Shares</th>
		      <th width="10%">Redeems</th>
	    	</tr>
		  </thead>
		  <tbody>';
		foreach($response as $age_range => $gender_data)
		{
			
			foreach($gender_data as $gender => $data)
			{
				$views =	!empty($data['view']) ? $data['view'] : 0;
				$claims =	!empty($data['claim']) ? $data['claim'] : 0;
				$redeems =	!empty($data['redeem']) ? $data['redeem'] : 0;
				$shares =	!empty($data['share']) ? $data['share'] : 0;
				$referrals = !empty($data['referral']) ? $data['referral'] : 0;
			
				$users = isset($arr_users[$age_range][$gender]) ? $arr_users[$age_range][$gender] : 0;
				$percent_users = round(($users * 100) / $total_num_users, 1);
				$percent_views = round(($views * 100) / $total_num_users, 1);
				$percent_claims = round(($claims * 100) / $total_num_users, 1);
				$percent_redeems = round(($redeems * 100) / $total_num_users, 1);
				$percent_shares = round(($shares * 100) / $total_num_users, 1);
				$percent_referrals = round(($referrals * 100) / $total_num_users, 1);

				$html .= "<tr>
				  <td>$age_range</td>
				  <td>$gender</td>
				  <td>$users ($percent_users%)</td>
				  <td>$views ($percent_views%)</td>
				  <td>$claims ($percent_claims%)</td>
				  <td>$shares ($percent_shares%)</td>
				  <td>$redeems ($percent_redeems%)</td>
				</tr>";
			}
		}
		$html .= '<tr><th>Total Males: ' . $total_num_males . '</th><th>Total Females: ' . $total_num_females . '</th></tr>';
		$html .= '</tbody>
		</table>';

		$response['html']=$html;
		*/
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	
	/********************************************************
	OVERALL STATS:
	VIEW-TO-CLAIM RATIO
	LIKES INCREASE
	VIEW-TO-SHARE RATIO
	REDEMPTION RATE
	***********************************************************/
	
	function getCompanyViews($params, $arr_test_user_account_ids = null)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$rejected_permissions = !empty($params['rejectedPermissions']) ? $params['rejectedPermissions'] : (!empty($params['rejectedpermissions']) ? $params['rejectedpermissions'] :'');
		
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		if(!empty($params['useDate']))
		{
			$str_date_sub_start = "date(" . $str_date_sub_start;
			$str_date_sub_end .= ")";
		}
		
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
		
		// Total Views
		$arr_where_clause_views = array();
		$sql_views = "select count(iv.id) as num_views from items_views iv";
		if(empty($campaign_ids))
			$arr_where_clause_views[] = " iv.company_id = '$company_id'";
		else
			$arr_where_clause_views[] = " iv.items_id in ($campaign_ids)";
		
		if(!empty($start_date) && !empty($end_date))
		{
			if($start_date != $end_date)
				$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			else
				$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " = '$start_date'";
				
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " >= '$start_date'";

		}
		else if(!empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end . " <= '$end_date'";
		}
		
		if(!empty($rejected_permissions))
			$arr_where_clause_views[] = " (iv.permissions_rejected = 1 or iv.share_permissions_rejected = 1)";
		
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_views[] = "iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($arr_where_clause_views))
			$sql_views .= " where " . implode(' and ', $arr_where_clause_views);
		// error_log("Views SQL for getCompanyViews() : " . $sql_views);
		$rs = Database::mysqli_query($sql_views);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_views = $row['num_views'] + 0;
		Database::mysqli_free_result($rs);
		return $num_views;
	}
	
	function getCompanyClaims($params, $arr_test_user_account_ids = null)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		$is_new_user_claim = !empty($params['isNewUserClaim']) ? $params['isNewUserClaim'] : (!empty($params['isnewuserclaim']) ? $params['isnewuserclaim'] :'');
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		if(!empty($params['useDate']))
		{
			$str_date_sub_start = "date(" . $str_date_sub_start;
			$str_date_sub_end .= ")";
		}
		
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
		
		// Total Claims
		$arr_where_clause_claims = array("ui.date_claimed is not null", "ui.user_id > 0");
		
		if(empty($campaign_ids))
		{
			/*
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			$arr_where_clause_claims[] = " item_id in ($campaign_ids)";
			*/
			$arr_where_clause_claims[] = " company_id = '$company_id'";
		}
		else
		{
			$arr_where_clause_claims[] = " item_id in ($campaign_ids)";
		}
		
		if(!empty($start_date) && !empty($end_date))
		{
			if($start_date != $end_date)
				$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			else
				$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " = '$start_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " >= '$start_date'";
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " <= '$end_date'";
		}
		
		if(!empty($is_new_user_claim))
			$arr_where_clause_claims[] = " is_new_user_claim = 1";
		
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_claims[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		$sql_claims = "select count(ui.id) as num_claims from user_items ui";
		
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " where " . implode(' and ', $arr_where_clause_claims);
		
		// error_log("Claims SQL for getCompanyClaims() : " . $sql_claims);
		$rs = Database::mysqli_query($sql_claims);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_claims = $row['num_claims'] + 0;
		Database::mysqli_free_result($rs);
		
		return $num_claims;
	}
	
	function getCompanyRedeems($params, $arr_test_user_account_ids = null)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');

		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		if(!empty($params['useDate']))
		{
			$str_date_sub_start = "date(" . $str_date_sub_start;
			$str_date_sub_end .= ")";
		}
		
		// Total Claims
		$arr_where_clause_claims = array("date_redeemed is not null");
		
		
		if(empty($campaign_ids))
		{
			/*
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			$arr_where_clause_claims[] = " item_id in ($campaign_ids)";
			*/
			$arr_where_clause_claims[] = " company_id = '$company_id'";
		}
		else
		{
			$arr_where_clause_claims[] = " item_id in ($campaign_ids)";
		}
		
		if(!empty($start_date) && !empty($end_date))
		{
			if($start_date != $end_date)
				$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			else
				$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " = '$start_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " >= '$start_date'";
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end . " <= '$end_date'";
		}
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
			
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_claims[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		$sql_claims = "select count(ui.id) as num_redeems from user_items ui";
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " where " . implode(' and ', $arr_where_clause_claims);
		
		// error_log("Redeems SQL for getCompanyRedeems() : " . $sql_claims);
		$rs = Database::mysqli_query($sql_claims);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_redeems = $row['num_redeems'] + 0;
		Database::mysqli_free_result($rs);
		return $num_redeems;
	}
	
	function getCompanyShares($params, $arr_test_user_account_ids = null)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');

		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		// Total Views
		$arr_where_clause_shares = array();
		$sql_shares = "select count(id) as num_shares from referrals r";
		if(empty($campaign_ids))
			$arr_where_clause_shares[] = " company_id = '$company_id'";
		else
			$arr_where_clause_shares[] = " item_shared in ($campaign_ids)";
		
		if(!empty($start_date) && !empty($end_date))
		{
			if($start_date != $end_date)
				$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			else
				$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " = '$start_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " >= '$start_date'";

		}
		else if(!empty($end_date))
		{
			$arr_where_clause_shares[] = " " . $str_date_sub_start . "r.created" . $str_date_sub_end . " <= '$end_date'";
		}
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
			
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_shares[] = " r.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($arr_where_clause_shares))
			$sql_shares .= " where " . implode(' and ', $arr_where_clause_shares);
		
		// error_log("Shares SQL for getCompanyShares() : " . $sql_claims);
		$rs = Database::mysqli_query($sql_shares);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_shares = $row['num_shares'] + 0;
		Database::mysqli_free_result($rs);
		return $num_shares;
	}
	
	function getCompanyReferrals($params, $arr_test_user_account_ids = null)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		// Total Claims
		$arr_where_clause_claims = array("ui.date_claimed is not null", "ui.referral_id is not null");
		
		if(empty($campaign_ids))
		{
			/*
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
			$arr_where_clause_claims[] = " item_id in ($campaign_ids)";
			*/
			$arr_where_clause_claims[] = " company_id = '$company_id'";
		}
		else
		{
			$arr_where_clause_claims[] = " item_id in ($campaign_ids)";
		}
		
		if(!empty($start_date) && !empty($end_date))
		{
			if($start_date != $end_date)
				$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " between '$start_date' and '$end_date'";
			else
				$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " = '$start_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " >= '$start_date'";
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end . " <= '$end_date'";
		}
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
			
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_claims[] = " ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		$sql_claims = "select count(ui.id) as num_referrals from user_items ui";
		
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " where " . implode(' and ', $arr_where_clause_claims);
		
		// error_log("Claims SQL for getViewToClaimRatio() : " . $sql_claims);
		$rs = Database::mysqli_query($sql_claims);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_referrals = $row['num_referrals'] + 0;
		Database::mysqli_free_result($rs);
		
		return $num_referrals;
	}
	
	function getIncreaseInCompanyFanCount($company_id)
	{
		$sql = "select (current_fan_count - initial_fan_count) as increase_in_fan_count from companies where id = '$company_id'";
		$rs = Database::mysqli_query($sql);
		$row = Database::mysqli_fetch_assoc($rs);
		$increase_in_fan_count = $row['increase_in_fan_count'] + 0;
		Database::mysqli_free_result($rs);
		return $increase_in_fan_count;
		
	}
	
	function getAverageIncreaseInFanCount()
	{
		$sql = "select avg(increase_in_fan_count) as increase_in_fan_count from (select (current_fan_count - initial_fan_count) as increase_in_fan_count from companies) as t";
		$rs = Database::mysqli_query($sql);
		$row = Database::mysqli_fetch_assoc($rs);
		$increase_in_fan_count = $row['increase_in_fan_count'] + 0;
		Database::mysqli_free_result($rs);
		return $increase_in_fan_count;
	}
	
	function getIncreaseInFanCount()
	{
		$sql = "select sum(increase_in_fan_count) as increase_in_fan_count from (select (current_fan_count - initial_fan_count) as increase_in_fan_count from companies) as t";
		$rs = Database::mysqli_query($sql);
		$row = Database::mysqli_fetch_assoc($rs);
		$increase_in_fan_count = $row['increase_in_fan_count'] + 0;
		Database::mysqli_free_result($rs);
		return $increase_in_fan_count;
	}
	
	function getAllViews($item_ids_to_include = null, $item_ids_to_exclude = null, $arr_test_user_account_ids = null)
	{
		/*
		$arr_company_ids = array();
		$rs = Database::mysqli_query("select id from companies where demo = '0'");
		while($row = Database::mysqli_fetch_assoc($rs))
			$arr_company_ids[] = $row['id'];
		$company_ids = implode(',', $arr_company_ids);
		
		$arr_campaign_ids = array();
		$rs = Database::mysqli_query("select campaign_id from items where manufacturer_id in ($company_ids)");
		while($row = Database::mysqli_fetch_assoc($rs))
			$arr_campaign_ids[] = $row['campaign_id'];
		$campaign_ids = implode(',', $arr_campaign_ids);
		*/
					
		// Total Views
		$arr_where_clause_views = array();
		$sql_views = "select count(iv.id) as num_views from items_views iv inner join companies c on iv.company_id = c.id and c.demo != 1 and c.status = 'active'";
		
		if(!empty($item_ids_to_include))
			$arr_where_clause_views[] = " iv.items_id in ($item_ids_to_include)";
		
		if(!empty($item_ids_to_exclude))
			$arr_where_clause_views[] = " iv.items_id not in ($item_ids_to_exclude)";
		
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_views[] = " iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";

		
		if(!empty($arr_where_clause_views))
			$sql_views .= " and " . implode(" and ", $arr_where_clause_views);
			
		
		// error_log("Views SQL for getAverageViewToClaimRatio() : " . $sql_views);
		$rs = Database::mysqli_query($sql_views);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_views = $row['num_views'] + 0;
		Database::mysqli_free_result($rs);
		
		return $num_views;
	}
	
	function getAllClaims($item_ids_to_include = null, $item_ids_to_exclude = null, $arr_test_user_account_ids = null)
	{
		// Total Claims		
		$sql_claims = "select count(ui.id) as num_claims from user_items ui inner join companies c on ui.company_id = c.id where c.demo != 1 and c.status = 'active' and ui.date_claimed is not null";
		
		if(!empty($item_ids_to_include))
			$sql_claims .= " and ui.item_id in ($item_ids_to_include)";
		
		if(!empty($item_ids_to_exclude))
			$sql_claims .= " and ui.item_id not in ($item_ids_to_exclude)";
		
		if(!empty($arr_test_user_account_ids))
			$sql_claims .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			
		// error_log("Claims SQL for getAverageViewToClaimRatio() : " . $sql_claims);
		$rs = Database::mysqli_query($sql_claims);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_claims = $row['num_claims'] + 0;
		Database::mysqli_free_result($rs);
		
		return $num_claims;
	}
	
	function getAllRedeems($item_ids_to_include = null, $item_ids_to_exclude = null, $arr_test_user_account_ids = null)
	{
		// Total Claims		
		$sql_redeems = "select count(ui.id) as num_redeems from user_items ui inner join companies c on ui.company_id = c.id where c.demo != 1 and c.status = 'active' and ui.date_redeemed is not null";
		
		if(!empty($item_ids_to_include))
			$sql_redeems .= " and ui.item_id in ($item_ids_to_include)";
		
		if(!empty($item_ids_to_exclude))
			$sql_redeems .= " and ui.item_id not in ($item_ids_to_exclude)";
		
		if(!empty($arr_test_user_account_ids))
			$sql_redeems .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			
		// error_log("Redeems SQL for getAllRedeems() : " . $sql_redeems);
		$rs = Database::mysqli_query($sql_redeems);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_redeems = $row['num_redeems'] + 0;
		Database::mysqli_free_result($rs);
		
		return $num_redeems;
	}
	
	function getRedeemedCampaignIDs()
	{
		$sql = "select group_concat(distinct(item_id) separator ',') as item_ids from user_items where date_redeemed is not null";
		$row = BasicDataObject::getDataRow($sql);
		return $row['item_ids'];
	}
	
	function getAllShares($item_ids_to_include = null, $item_ids_to_exclude = null, $arr_test_user_account_ids = null)
	{
		// Total Referrals	
		$sql_shares = "select count(r.id) as num_shares from referrals r inner join companies c on r.company_id = c.id where c.demo != 1 and c.status = 'active'";
		
		$arr_sql_shares = array();
		if(!empty($item_ids_to_include))
			$arr_sql_shares[] = " item_shared in ($item_ids_to_include)";
		
		if(!empty($item_ids_to_exclude))
			$arr_sql_shares[] = " item_shared not in ($item_ids_to_exclude)";
		
		if(!empty($arr_test_user_account_ids))
			$arr_sql_shares[] = " user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($arr_sql_shares))
			$sql_shares .= " where " . implode(" and ", $arr_sql_shares);
			
		
		// error_log("Shares SQL for getAverageViewToClaimRatio() : " . $sql_shares);
		$rs = Database::mysqli_query($sql_shares);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_shares = $row['num_shares'] + 0;
		Database::mysqli_free_result($rs);
		return $num_shares;
	}
	
	function getAllReferrals($item_ids_to_include = null, $item_ids_to_exclude = null, $arr_test_user_account_ids = null)
	{
		// Total Referrals		
		$sql_claims = "select count(ui.id) as num_referrals from user_items ui inner join companies c on ui.company_id = c.id where c.demo != 1 and c.status = 'active' and ui.date_claimed is not null and ui.referral_id is not null";
		
		if(!empty($item_ids_to_include))
			$sql_claims .= " and ui.item_id in ($item_ids_to_include)";
		
		if(!empty($item_ids_to_exclude))
			$sql_claims .= " and ui.item_id not in ($item_ids_to_exclude)";
		
		if(!empty($arr_test_user_account_ids))
			$sql_claims .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			
		// error_log("Claims SQL for getAverageViewToClaimRatio() : " . $sql_claims);
		$rs = Database::mysqli_query($sql_claims);
		$row = Database::mysqli_fetch_assoc($rs);
		$num_referrals = $row['num_referrals'] + 0;
		Database::mysqli_free_result($rs);
		return $num_referrals;
	}
	
	
	function getGroupedCampaigns($company_id, $status = null, $is_corporate = false)
	{
		$arr_where_clause = array();
		if(!empty($company_id))
			$arr_where_clause[] = "manufacturer_id = '$company_id'";
		
		if(!empty($status))
			$arr_where_clause[] = "status in (" . $status . ")";
			
		$sql = "select short_name as `name`, group_concat(concat('\"', id, '\":\"', delivery_method, '\"') separator ', ') as id from items ";
		if(!empty($arr_where_clause))
			$sql .= " where " . implode(' and ', $arr_where_clause);
		$sql .= " group by short_name";
		// error_log("SQL in CSAPI::getGroupedCampaigns(): " . $sql);
		$campaigns = BasicDataObject::getDataTable($sql);
		
		if($is_corporate)
		{
			$arr_where_clause = array();
		
			if(!empty($status))
				$arr_where_clause[] = "i.status in (" . $status . ")";
			
			if(!empty($company_id))
				$arr_where_clause[] = "consultant.consultant_of = '$company_id'";
			
			$sql = "select i.short_name as `name`, group_concat(concat('\"', i.id, '\":\"', i.delivery_method, '\"') separator ', ') as id from items i inner join companies consultant on i.manufacturer_id = consultant.id";
			if(!empty($arr_where_clause))
				$sql .= " where " . implode(' and ', $arr_where_clause);
			// Grouping should be done by deal_id NOT short_name!
			$sql .= " group by i.deal_id"; // " group by i.short_name";
			// error_log("Consultant SQL in CSAPI::getGroupedCampaigns(): " . $sql);
		
			$rs = Database::mysqli_query($sql);
			$tmp_campaigns = array();
			$all_consultant_item_ids = '';
			while($campaign = Database::mysqli_fetch_assoc($rs))
			{
				$campaign['name'] .= " - Consultants";
				$tmp_campaigns[] = $campaign;
				if(!empty($all_consultant_item_ids))
					$all_consultant_item_ids .= ', ';
					
				$all_consultant_item_ids .= $campaign['id'];
			}
			if(!empty($tmp_campaigns))
			{
				$campaigns[] = array(
					'id' => $all_consultant_item_ids,
					'name' => 'All Consultants'
				);
				$campaigns = array_merge($campaigns, $tmp_campaigns);
			}
			Database::mysqli_free_result($rs);
		}
		

		return $campaigns;
	}
	
	
	function getGroupedCampaignsData($company_id, $parent_consultant = false, $status = null)
	{
		$response = array();
		$inner_join = $parent_consultant ? " inner join companies c on i.manufacturer_id = c.id" : "";
		
		$arr_where_clause = empty($status) ? array("i.status='running'") : array("i.status in (" . $status . ")");
		if(!empty($company_id))
			$arr_where_clause[] = $parent_consultant ? "c.consultant_of = '$company_id'" : "i.manufacturer_id = '$company_id'";
		$sql = "select i.name as campaign_name, group_concat(concat('\"', i.id, '\":\"', i.delivery_method, '\"') separator ', ') as  campaign_ids, group_concat(distinct(i.delivery_method) separator ',') as delivery_methods, group_concat(i.id separator ',') as ids, min(i.start_date) as start_date, max(i.end_date) as end_date, max(i.expires) as expiry_date from items i";
		$sql .= $inner_join;
		
		if(!empty($arr_where_clause))
			$sql .= " where " . implode(' and ', $arr_where_clause);
			
		// Grouping should be done by deal_id NOT name!
		$sql .= " group by i.deal_id"; // " group by i.name";
		// error_log("SQL in CSAPI::getGroupedCampaignsData(): " . $sql);
		$rs = Database::mysqli_query($sql);
		while($campaign = Database::mysqli_fetch_assoc($rs))
		{
			$params = array('companyId' => $company_id, 'campaignId' => $campaign['ids']);
			$campaign['views'] = $this->getCompanyViews($params);
			$campaign['claims'] = $this->getCompanyClaims($params);
			$response[] = $campaign;
		}
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	function getGroupedCampaignsViews($company_id, $arr_test_user_account_ids = null, $status = 'running')
	{
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
			
		$sql = "select i.deal_id, i.short_name, i.name, count(iv.id) as num_views
		from items_views iv
		inner join items i on iv.items_id = i.id
		where i.manufacturer_id = '$company_id'";
		
		if(!empty($status))
			$sql .= " and i.status = '$status'";
			
		if(!empty($arr_test_user_account_ids))
			$sql .= " and iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		$sql .= "group by i.deal_id";
		
		$rows = BasicDataObject::getDataTable($sql);
		$data = array();
		foreach($rows as $row)
			$data[$row['deal_id']] = $row;
			
		return $data;
	}
	
	function getGroupedCampaignsClaims($company_id, $arr_test_user_account_ids = null, $status = 'running')
	{
		if(empty($arr_test_user_account_ids))
			$arr_test_user_account_ids = User::getTestUserAccountsIds();
			
		$sql = "select i.deal_id, i.short_name, i.name, count(ui.id) as num_claims
		from user_items ui
		inner join items i on ui.item_id = i.id
		where i.manufacturer_id = '$company_id'";
		
		if(!empty($status))
			$sql .= " and i.status = '$status'";
			
		if(!empty($arr_test_user_account_ids))
			$sql .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		$sql .= "group by i.deal_id";
		
		$rows = BasicDataObject::getDataTable($sql);
		$data = array();
		foreach($rows as $row)
			$data[$row['deal_id']] = $row;
			
		return $data;
	}
	
	function getGroupedCampaignsV2CRatio($company_id)
	{
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		$views_data = $this->getGroupedCampaignsViews($company_id, $arr_test_user_account_ids);
		$claims_data = $this->getGroupedCampaignsClaims($company_id, $arr_test_user_account_ids);
		
		$v2c_data = array();
		foreach($views_data as $deal_id => $views)
		{
			$name = $views['name'];
			$deal_name = $views['short_name'];
			$num_views = $views['num_views'];
			$num_claims = !empty($claims_data[$deal_id]) ? $claims_data[$deal_id]['num_claims'] : 0;
			$v2c = round(($num_claims / $num_views) * 100, 2);
			
			$v2c_data[$deal_id] = array(
				'name'		=> $name,
				'deal_name'		=> $deal_name,
				'num_views'		=> $num_views,
				'num_claims'	=> $num_claims,
				'v2c'			=> $v2c,
			);
			
		}
		return $v2c_data;
	}
	
	function getGroupedCampaignNames($company_id, $status = null, $is_corporate = false)
	{
		$response = array();
		
		$arr_where_clause = array();
		
		if(!empty($status))
			$arr_where_clause[] = "status in (" . $status . ")";
			
		if(!empty($company_id))
			$arr_where_clause[] = "manufacturer_id = '$company_id'";
		$sql = "select short_name as short_campaign_name, group_concat(id separator ',') as campaign_ids, min(date(start_date)) as start_date, max(date(end_date)) as end_date from items ";
		if(!empty($arr_where_clause))
			$sql .= " where " . implode(' and ', $arr_where_clause);
		$sql .= " group by short_name";
		// error_log("SQL in CSAPI::getPreiousGroupedCampaignNames(): " . $sql);
		$rs = Database::mysqli_query($sql);
		while($campaign = Database::mysqli_fetch_assoc($rs))
		{
			$response[] = $campaign;
		}
		
		
		if($is_corporate)
		{
			$arr_where_clause = array();
		
			if(!empty($status))
				$arr_where_clause[] = "i.status in (" . $status . ")";
			
			if(!empty($company_id))
				$arr_where_clause[] = "consultant.consultant_of = '$company_id'";
			
			$sql = "select i.short_name as short_campaign_name, group_concat(i.id separator ',') as campaign_ids, min(date(i.start_date)) as start_date, max(date(i.end_date)) as end_date from items i inner join companies consultant on i.manufacturer_id = consultant.id";
			if(!empty($arr_where_clause))
				$sql .= " where " . implode(' and ', $arr_where_clause);
			$sql .= " group by i.short_name";
			// error_log("Consultant SQL in CSAPI::getGroupedCampaignNames(): " . $sql);
		
			$rs = Database::mysqli_query($sql);
			$tmp_campaigns = array();
			$all_consultant_item_ids = '';
			while($campaign = Database::mysqli_fetch_assoc($rs))
			{
				// $campaign['campaign_name'] .= " - Consultants";
				$campaign['short_campaign_name'] .= " - Consultants";
				
				$tmp_campaigns[] = $campaign;
				if(!empty($all_consultant_item_ids))
					$all_consultant_item_ids .= ',';
					
				$all_consultant_item_ids .= $campaign['campaign_ids'];
				
			}
			if(!empty($tmp_campaigns))
			{
				$response[] = array(
					'campaign_ids' => $all_consultant_item_ids,
					// 'campaign_name' => 'All Consultants',
					'short_campaign_name' => 'All Consultants',
				);
				$response = array_merge($response, $tmp_campaigns);
			}
		}
		
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	
	
	/*****************************
	
	Age and Sex Breakdown – Wireframe page 4 
	Target chart type:  http://www.highcharts.com/demo/bar-negative-stack
	DB tables:  tmp_entrant, api_rel_status
	Example of Data used by Highcharts
	categories = ['0-4', '5-9', '10-14', '15-19',
				'20-24', '25-29', '30-34', '35-39', '40-44',
				'45-49', '50-54', '55-59', '60-64', '65-69',
				'70-74', '75-79', '80-84', '85-89', '90-94',
				'95-99', '100 + '];
	series: [{
					name: 'Male',
					data: [-1746181, -1884428, -2089758, -2222362, -2537431, -2507081, -2443179, -2664537, -3556505, -3680231, -3143062, -2721122, -2229181, -2227768, -2176300, -1329968, -836804, -354784, -90569, -28367, -3878]
				}, {
					name: 'Female',
					data: [1656154, 1787564, 1981671, 2108575, 2403438, 2366003, 2301402, 2519874, 3360596, 3493473, 3050775, 2759560, 2304444, 2426504, 2568938, 1785638, 1447162, 1005011, 330870, 130632, 21208]
				}]

	Description:  Returns age ranges, segmented by sex/gender for the target entrants.  
	@param INTEGER companyId  required
	@param INTEGER campaignId  optional – default action is to show data for all campaigns in the company
	@param STRING countryCode  optional filter – default action is to show data for all countries in the world.
	@param STRING locationId  optional filter – if provided, shows only information for users in that location.  countryCode will be ignored if this filter is set.  
	@param INTEGER relStatusId  optional – default ‘all’ shows entrants for all relationship status. If set, returns values only for entrants who have that relationship status id. 
	@param INTEGER interestFbId  optional – default ‘all’ shows all entrant.  If set, returns values only for entrants who like the interest with the assigned FB_id. 
	@param STRING negativeAxisGender option – default ‘M’ charts ‘male’ entrants on the negative(left) x axis.  A value of ‘F’ will flip the chart and sort ‘female’ entrants on the negative x axis. . 


	@return ARRAY settings -  the given and default values for each parameter value.  
	@return ARRAY columns  - the names of each age range matching the order of the data results (‘0-12’, ’13-17’, ’18-20’,…)
	@return ARRAY of ARRAYS  data-male (INTEGER value, INTEGER, value)) returns the number of male entrants in each age range for the result set.  All the values are negative unless the param negativeAxisGender is set to ‘F’. 
	@return ARRAY of ARRAYS  data-female (INTEGER value, INTEGER, value)) returns the number of female entrants in each age range for the result set.  All the values are positive unless the param negativeAxisGender is set to ‘F’.
	@return ARRAY of ARRAYS  data-other (INTEGER value, INTEGER, value)) returns the number of entrants in each age range for the result set for which the sex is unknown. All the values are positive.  This data will not be visualized in the chart, but may be useful to explain discrepancies.  

	*****************************/

	function getEntrantAgeGender($params)
	{
		$negativeAxisGender = !empty($params['negativeAxisGender']) ? $params['negativeAxisGender'] : 'M';
		
		$response = array(
			'graph_data' => array(
				'categories' => array(),
				'series' => array(
					array('name' => 'Male', 'data' => array()),
					array('name' => 'Female', 'data' => array()),
				),
			)
		);
		
		$arr_gender_indexes = array(
			'Male' => array('index' => 0, 'sign' => -1),
			'Female' => array('index' => 1, 'sign' => 1),
		);
		
		if(strtoupper($negativeAxisGender) == 'F')
		{
			$arr_gender_indexes = array(
				'Male' => array('index' => 0, 'sign' => 1),
				'Female' => array('index' => 1, 'sign' => -1),
			);
		}
			
		$arr_unique_entrant_ids = $this->getUniqueEntrantIDs($params);
		
		$sql = "select * from api_age_range";
		$rs = Database::mysqli_query($sql);
		$sql_age_range = "";
		$num_age_ranges = Database::mysqli_num_rows($rs);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			if(!empty($sql_age_range))
				$sql_age_range .= " union all ";
			$sql_age_range .= " select '" . $row['age_range_name'] . "' as age_range_name, " . $row['age_min'] . " as age_min, " . $row['age_max'] . " as age_max ";
			
			$response['graph_data']['categories'][] = $row['age_range_name'];
		}
		
		$response['graph_data']['series'][0]['data'] = $response['graph_data']['series'][1]['data'] = array_fill(0, $num_age_ranges - 1, 0);
		$arr_age_range_indexes = array_flip($response['graph_data']['categories']);
		
		if(empty($arr_unique_entrant_ids))
			$arr_unique_entrant_ids = array(-1);
		$sql =	"select ar.age_range_name, t.gender, count(t.id) as num_users
			from
			(
			select u.id, DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 as age, if(lcase(u.gender) = 'm', 'Male', if(lcase(u.gender) = 'f', 'Female', '')) as gender from users u where u.date_of_birth is not null and u.date_of_birth != '0000-00-00' 
			and u.id in (" . implode(',', $arr_unique_entrant_ids). ")
			) as t
			inner join (" . $sql_age_range . ") ar on t.age between ar.age_min and ar.age_max
			group by ar.age_range_name, t.gender";
		// error_log("SQL in CSAPI::getEntrantAgeGender(): " . $sql);
		
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$gender = $row['gender'];
			$age_range_name = $row['age_range_name'];
			$num_users = $row['num_users'];
			
			if(!empty($gender))
			{
				$data_index = $arr_age_range_indexes[$age_range_name];
				$gender_index = $arr_gender_indexes[$gender]['index'];
				$sign = $arr_gender_indexes[$gender]['sign'];
				
				$response['graph_data']['series'][$gender_index]['data'][$data_index] = $num_users * $sign;
				$response['totals'][$gender_index] += $num_users;
			}
			
		}
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	function getEntrantRelationshipStatus($params)
	{
		$limit = !empty($params['limit']) ? $params['limit'] : 5;
		
		$response = array(
			'graph_data' => array(
				'series' => array(
					'type' => 'pie',
					'name' => 'Relationship Status',
					'data' => array()
				)
			)
		);
		
		
			
		$arr_unique_entrant_ids = $this->getUniqueEntrantIDs($params);
		if(empty($arr_unique_entrant_ids))
			$arr_unique_entrant_ids = array(-1);
			
		
			
		$sql =	"select relationship_status, num_users from
			(
			select u.relationship_status, count(u.id) as num_users
			from users u where u.id in (" . implode(',', $arr_unique_entrant_ids). ")
			group by u.relationship_status
			) as t
			order by num_users desc limit $limit";
		
		// error_log("SQL in CSAPI::getEntrantRelationshipStatus(): " . $sql);
		
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$num_users = $row['num_users'] + 0;
			// $relationship_status = !empty($row['relationship_status']) ? $row['relationship_status'] : 'Other';
			$relationship_status = !empty($row['relationship_status']) ? $row['relationship_status'] : 'Not Provided';
			// if(!empty($relationship_status))
			// {
				$response['graph_data']['series']['data'][] = array($relationship_status, $num_users);
			// }
		}
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	function getCampaignUniqueVisits($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : 3600;
		$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : (!empty($params['timeintervalseconds']) ? $params['timeintervalseconds'] : 3600 );
		//$in_the_last = !empty($params['inTheLast']) ? $params['inTheLast'] : 0;
		$in_the_last = !empty($params['inTheLast']) ? $params['inTheLast'] : (!empty($params['inthelast']) ? $params['inthelast'] :0 );
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'all';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'all');
		$source = !empty($params['source']) ? $params['source'] : 'all';
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		
		
		//	1.	Calculating Interval specific variables
		switch($interval)
		{
			case 60:	//	Minute
				$mysql_date_format = "%m/%d/%Y\T%H:%i:00$time_zone";
				$php_date_format = "m/d/Y\TH:i:00$time_zone";
				$interval_type = 'minute';
				break;
	
			case 3600:	//	Hour
				$mysql_date_format = "%m/%d/%Y\T%H:00:00$time_zone";
				$php_date_format = "m/d/Y\TH:00:00$time_zone";
				$interval_type = 'hour';
				break;
	
			case 86400:	//	Day
				// $mysql_date_format = '%m/%d/%Y';
				// $php_date_format = 'm/d/Y';
				$mysql_date_format = "%m/%d/%Y\T00:00:00$time_zone";
				$php_date_format = "m/d/Y\T00:00:00$time_zone";
				$interval_type = 'day';
				break;
		}
		if(!empty($in_the_last))
		{
			$sql = "select " . $str_date_sub_start . "now()" . $str_date_sub_end . " as end_date, date_sub(" . $str_date_sub_start . "now()" . $str_date_sub_end . ", interval $in_the_last $interval_type) as start_date";
			// error_log("sql for finding start date and end date: " . $sql);
			$row = BasicDataObject::getDataRow($sql);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
		}
		
		$arr_where_clause = array();
		$arr_where_clause_views = array();
		
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
 			$arr_where_clause_views[] = "iv.company_id = '$company_id'";
		}
		else
		{
			$arr_where_clause_views[] = "iv.items_id in (" . $campaign_ids . ")";
		}
		
		if(!empty($activity_type))
		{
			// $arr_where_clause[] = " ea.activity_type = '$activity_type'";
		}
		
		if($source == 'shortcode')
		{
			$arr_where_clause_views[] = " iv.shortened_url_hit_id > 0";
		}
		else if($source == 'facebook')
		{
			$arr_where_clause_views[] = " iv.referral_id > 0";
		}
		else if($source == 'direct')
		{
			$arr_where_clause_views[] = " (iv.shortened_url_hit_id is null and iv.referral_id is null)";
		}
		
		
		if(!empty($start_date) && !empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end. " between '$start_date' and '$end_date'";
		}
		else if(!empty($start_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end. " >= '$start_date'";
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_views[] = " " . $str_date_sub_start . "iv.created" . $str_date_sub_end. " <= '$end_date'";
		}
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
			$arr_where_clause_views[] = " iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		// SQL for Views
		$sql_views = "select date_format(" . $str_date_sub_start . "iv.created" . $str_date_sub_end. ", '$mysql_date_format') as date, iv.created as sort_date, (case when referral_id > 0 then 'facebook' when shortened_url_hit_id > 0 then 'shortcode' else 'direct' end) as `source`, count(id) as activity_count from items_views iv ";
		if(!empty($arr_where_clause_views))
			$sql_views .= " where " . implode(" and ", $arr_where_clause_views);
		$sql_views .= " group by date_format(" . $str_date_sub_start . "iv.created" . $str_date_sub_end. ", '$mysql_date_format'), source order by iv.created";
		
		$sql = $sql_views;
		
		// error_log("SQL in CSAPI::getCampaignUniqueVisits(): " . $sql);
		
		$timer_start = array_sum(explode(" ", microtime()));
		$rs = Database::mysqli_query($sql);
		// error_log("CSAPI::getCampaignUniqueVisits(): Time taken to run the SQL: " . (array_sum(explode(" ", microtime())) - $timer_start));
	
		$tmp_data = array();
		$response = array(
			'settings' => $params,
			'data' => array(),
			'graph_data' => array(),
			'cumulative_data' => array(),
		);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$source = $row['source'];
			$tmp_date = $row['date'];
			$activity_count = $row['activity_count'] + 0;
			$tmp_data[$source][$tmp_date] = $activity_count;
			if(!isset($response['cumulative_data'][$source]))
				$response['cumulative_data'][$source] = 0;
			$response['cumulative_data'][$source] += $activity_count;
		}
		// error_log("CSAPI::getCampaignUniqueVisits(): Time taken to iterate through the records: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		// Getting start date
		if(empty($start_date))
		{
			Database::mysqli_data_seek($rs, 0);
			$row = Database::mysqli_fetch_assoc($rs);
			$start_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($start_date);
			$start_date = date_format($obj_dt, $php_date_format);
		}
		
		// Getting End Date
		if(empty($end_date))
		{
			Database::mysqli_data_seek($rs, Database::mysqli_num_rows($rs) - 1);
			$row = Database::mysqli_fetch_assoc($rs);
			$end_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($end_date);
			$end_date = date_format($obj_dt, $php_date_format);
		}
		// error_log("tmp_data: " . var_export($tmp_data, true));
		
		// error_log("CSAPI::getCampaignUniqueVisits(): Time taken to set the start and end pointers: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		$start_date_val = strtotime($start_date . " $str_time_zone");
		$end_date_val = strtotime($end_date . " $str_time_zone");
		
		// error_log("start_date: " . $start_date . ", start_date_val: " . $start_date_val . ", end_date: " . $end_date . ", start_date_val: " . $end_date_val);
		
		
		
		$time_interval = new DateInterval('PT' . $interval . 'S');
		$pointStart = $start_date_val * 1000;
		$pointInterval = $interval * 1000;
		
		foreach($tmp_data as $source => $activity_data)
		{
			// $response['data'][$source] = array();
			$cumm_entrant_count = 0;
			$tmp_date = $start_date;
			$series_data = array(
				'data' => array(),
				'name' => $source,
				'pointStart' => $pointStart,
				'pointInterval' => $pointInterval,
			);
			for($i = $start_date_val; $i <= $end_date_val; $i += $interval)
			{
				// $tmp_date = date($php_date_format, $i);
				if(isset($activity_data[$tmp_date]))
				{
					$cumm_entrant_count += $activity_data[$tmp_date];
				}
				// $response['data'][$source][$tmp_date] = $cumm_entrant_count;
				$series_data['data'][] = !empty($activity_data[$tmp_date]) ? $activity_data[$tmp_date] + 0 : 0; //$cumm_entrant_count;
				$dt = DateTime::CreateFromFormat($php_date_format, $tmp_date);
				if(!$dt)
					error_log("failed to created date object! using format $php_date_format having date $tmp_date");
				$tmp_date = date_format($dt->add($time_interval), $php_date_format);
			}
			$response['graph_data'][] = $series_data;
		}
		
		// error_log("CSAPI::getCampaignUniqueVisits(): Time taken to set the final data: " . (array_sum(explode(" ", microtime())) - $timer_start));
		Database::mysqli_free_result($rs);

		if(!empty($errors))
			return $errors;
			
		return $response;
	}
	
	
	function getCumulativeCampaignSources($params)
	{
		$response = array();
		$response['graph_data'] = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'view';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		
		$source = !empty($params['source']) ? $params['source'] : 'all';
		
		
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		$str_where_clause = !empty($campaign_ids) ? "items_id in ($campaign_ids)" : "company_id = '$company_id'";
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
			$where_clause_views = " and user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		// SQL for Referrals
		$sql_referrals = "select count(id) as num_views from items_views where $str_where_clause and referral_id > 0 $where_clause_views";
		
		// SQL for Shortened URLs
		$sql_shortened_url = "select count(id) as num_views from items_views where $str_where_clause and shortened_url_hit_id > 0 $where_clause_views";
		// SQL for Smart Link Clicks
		$sql_smart_links = "select count(id) as num_views from items_views where $str_where_clause and smart_link_id > 0 $where_clause_views";
		
		// SQL for All Views
		$sql_all = "select count(id) as num_views from items_views where $str_where_clause $where_clause_views";
		
		// SQL for Direct
		$sql_direct = "select count(id) as num_views from items_views where $str_where_clause and (shortened_url_hit_id is null and referral_id is null) $where_clause_views";
		
		switch($source)
		{
			case 'all':
				//	Referrals shared on Facebook
				$row = BasicDataObject::getDataRow($sql_referrals);
				$num_fb_views = $row['num_views'];
				$response['graph_data'][] = array('name' => 'Facebook', 'y' => floatval($num_fb_views));
				
				// Shortened URLs
				$row = BasicDataObject::getDataRow($sql_shortened_url);
				$num_cupn_views = $row['num_views'];
				$response['graph_data'][] = array('name' => 'CUPN', 'y' => floatval($num_cupn_views));
				
				// Direct
				$row = BasicDataObject::getDataRow($sql_all);
				$num_all_views = $row['num_views'];
				$num_direct_views = $num_all_views - ($num_cupn_views + $num_fb_views);
				$response['graph_data'][] = array('name' => 'Direct', 'y' => floatval($num_direct_views));
				
				break;
			
			case 'facebook':
				//	Referrals shared on Facebook
				$row = BasicDataObject::getDataRow($sql_referrals);
				$num_fb_views = $row['num_views'];
				$response['graph_data'][] = array('name' => 'Facebook', 'y' => $num_fb_views);
				break;
			
			case 'shortcode':
				// Shortened URLs
				$row = BasicDataObject::getDataRow($sql_shortened_url);
				$num_cupn_views = $row['num_views'];
				$response['graph_data'][] = array('name' => 'CUPN', 'y' =>  $num_cupn_views);
				break;
			
			case 'direct':
				//	Referrals shared on Facebook
				$row = BasicDataObject::getDataRow($sql_direct);
				$num_direct_views = $row['num_views'];
				$response['graph_data'][] = array('name' => 'Direct', 'y' => $num_direct_views);
				break;
		}
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	
	function getActivityStatsGroupedByShortenedURLs($params)
	{
		$response = array();
		$response['graph_data'] = array();
		
		// error_log("params in CSAPI::getActivityStatsGroupedByShortenedURLs(): " . var_export($params, true));
		
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'view';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		
		$source = !empty($params['source']) ? $params['source'] : 'all';
		$sort = !empty($params['sort']) ? $params['sort'] : 'num_views';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		
		/*
		if(empty($campaign_ids))
		{
			
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		*/
		
		// Getting all shortened URLs
		$sql = "select su.short_url from shortened_urls su where su.companies_id = '$company_id'";
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			$short_url		= $row['short_url'];
			$response['graph_data'][$short_url] = array(
				'short_url'		=> $short_url, 
				'num_url_hits'	=> 0, 
				'num_views'		=> 0, 
				'click_rate'	=> 0, 
				'num_claims'	=> 0,
				'claim_rate'	=> 0,
				'num_redeems'	=> 0,
				'redeem_rate'	=> 0,
			);
		}
		
		$str_where_clause = !empty($campaign_ids) ? "iv.items_id in ($campaign_ids)" : "iv.company_id = '$company_id'";
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		$where_clause_views = "";
		if(!empty($arr_test_user_account_ids))
		{
			$where_clause_views = " and iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			$where_clause_claims = " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		}
			
		// VIEWS
		$sql = "select su.short_url, i.name as campaign_name, count(suh.id) as num_url_hits, count(iv.id) as num_views
				from items_views iv
				inner join shortened_url_hits suh on iv.shortened_url_hit_id = suh.id
				inner join shortened_urls su on suh.shortened_url_id = su.id
				inner join items i on iv.items_id = i.id
				where $str_where_clause
				$where_clause_views
				group by su.short_url";
		// error_log("SQL for views: " . $sql);
		
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			
			$short_url		= $row['short_url'];
			$num_url_hits	= $row['num_url_hits'];
			$num_views		= $row['num_views'];
			$campaign_name	= $row['campaign_name'];
			
			$click_rate = !empty($num_url_hits) ? round(($num_views / $num_url_hits) * 100, 2) : 0;
			
			$response['graph_data'][$short_url] = array(
				'short_url'		=> $short_url, 
				'campaign_name' => $campaign_name,
				'num_url_hits'	=> $num_url_hits, 
				'num_views'		=> $num_views, 
				'click_rate'	=> $click_rate, 
				'num_claims'	=> 0,
				'claim_rate'	=> 0,
				'num_redeems'	=> 0,
				'redeem_rate'	=> 0,
			);
		}
		
		$str_where_clause = !empty($campaign_ids) ? " and ui.item_id in ($campaign_ids)" : " and ui.company_id = '$company_id'";
		// CLAIMS & REDEEMS
		$sql = "select su.short_url, count(ui.id) as num_claims, sum(if(ui.date_redeemed is null, 0, 1)) as num_redeems
			from user_items ui
			inner join items_views iv on ui.items_views_id = iv.id
			inner join shortened_url_hits suh on iv.shortened_url_hit_id = suh.id
			inner join shortened_urls su on suh.shortened_url_id = su.id
			where ui.date_claimed is not null
			$str_where_clause
			$where_clause_claims
			group by su.short_url";
			
		// error_log("SQL for claims: " . $sql);
		
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			$short_url		= $row['short_url'];
			$num_claims		= $row['num_claims'];
			$num_redeems	= $row['num_redeems'];
			
			
			if(isset($response['graph_data'][$short_url]))
			{
				$num_views = $response['graph_data'][$short_url]['num_views'];
				$claim_rate = !empty($num_views) ? round(($num_claims / $num_views) * 100, 2) : 0;
				$redeem_rate = !empty($num_claims) ? round(($num_redeems / $num_claims) * 100, 2) : 0;	
				
				$response['graph_data'][$short_url]['num_claims']	= $num_claims;
				$response['graph_data'][$short_url]['claim_rate']	= $claim_rate;
				$response['graph_data'][$short_url]['num_redeems']	= $num_redeems;
				$response['graph_data'][$short_url]['redeem_rate']	= $redeem_rate;
			}
		}
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['graph_data'], $sort, $sort_order);
			
		$response['settings'] = $params;
		return $response;		
		
	}
	
	function getInterestActions($params)
	{
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		
		$response = array();
		$response['graph_data'] = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		
		$min_group_size = !empty($params['minGroupSize']) ? $params['minGroupSize']: (!empty($params['mingroupsize']) ? $params['mingroupsize'] : 1);
		
		$limit = !empty($params['limit']) ? $params['limit']: 20;
		
		$min_interest_fan_base = !empty($params['minInterestFanbase']) ?  $params['minInterestFanbase'] : (!empty($params['mininterestfanbase']) ? $params['mininterestfanbase'] : 0);
		
		$sort = !empty($params['sort']) ? $params['sort'] : 'view2claim_ratio';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		
		//	1.	Get Top Interest IDs
		// Get Top interests
		
		$timer_start = array_sum(explode(" ", microtime()));
		// $sql = "select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) and entrant_like_count >= '$min_group_size' group by interest_id order by sum(entrant_like_count) desc limit $limit";
		
		$sql = "select interest_id, entrant_like_count from
		(
		select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) and entrant_like_count >= '$min_group_size' group by interest_id
		) as t
		order by entrant_like_count desc limit $limit";
		
		$sql = "select interest_id, count(distinct(entrant_id)) as entrant_like_count from tmp_entrant_interest where campaign_id in ($campaign_ids) and entrant_id not in (" . implode(',', $arr_test_user_account_ids). ") group by interest_id having entrant_like_count >= '$min_group_size' order by entrant_like_count desc limit $limit";
		
		// error_log("SQL0 in CSAPI::getInterestActions(): " . $sql);
		$arr_campaign_interest_likes = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_id = $row['interest_id'];
			$entrant_like_count = $row['entrant_like_count'] + 0;
			$arr_campaign_interest_likes[$interest_id] = $entrant_like_count;
		}
		
		// error_log("CSAPI::getInterestActions(): Time taken to get the campaign interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		//	2.	Get Names for those Interests
		$sql = "select fb_id, name, total_followers from tmp_like_interest_1 where fb_id in (" . implode(',', array_keys($arr_campaign_interest_likes)). ") and total_followers > '$min_interest_fan_base' order by total_followers desc limit $limit";
		$sql = "select fb_id, name, total_followers from tmp_like_interest_1 where fb_id in (" . implode(',', array_keys($arr_campaign_interest_likes)). ") and campaign_id in ($campaign_ids) ";
		// error_log("SQL1 in CSAPI::getInterestActions(): " . $sql);
		$arr_top_interests = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $entrant_count = $row['total_followers'];
			$interest = $row['name'];
			$interest_id = $row['fb_id'];
			$entrant_count = $arr_campaign_interest_likes[$interest_id];
			$arr_top_interests[$interest_id] = array($entrant_count, $interest);
		}
		// arsort($arr_top_interests);
		// error_log("top interest ids: " . implode(',', array_keys($arr_top_interests)));
		//	3.	Get Interest Stats
		$sql = "select t.*, if(t.view_count > 0, round((t.claim_count * 100 / t.view_count), 2), 0) as view2claim_ratio, 
if(t.claim_count > 0, round((t.redeem_count * 100 / t.claim_count), 2), 0) as claim2redeem_ratio, 
if(t.claim_count > 0, round((t.share_count * 100 / t.claim_count), 2), 0) as claim2share_ratio, 
if(t.share_count > 0, round((t.refer_count * 100 / t.share_count), 2), 0) as share2share_claim_ratio,
if(t.share_count > 0, round((t.refer_redeem_count * 100 / t.share_count), 2), 0) as share2share_redeem_ratio
from 
(
select ei.interest_id, sum(tec.view_count) as view_count, sum(tec.claim_count) as claim_count, sum(tec.redeem_count) as redeem_count, sum(tec.share_count) as share_count, sum(tec.refer_count) as refer_count, sum(tec.refer_redeem_count) as  refer_redeem_count
from tmp_entrant_campaign tec
inner join 
(
select entrant_id, interest_id from tmp_entrant_interest where interest_id in (" . implode(',', array_keys($arr_top_interests)). ") and campaign_id in ($campaign_ids)
) as ei
on tec.entrant_id = ei.entrant_id
where tec.campaign_id in ($campaign_ids)
group by ei.interest_id
) as t";
		
		
		// error_log("SQL in CSAPI::getInterestActions(): " . $sql);
		
		$cum_view_count = 0;
		$cum_claim_count = 0;
		$cum_redeem_count = 0;
		$cum_share_count = 0;
		$cum_refer_count = 0;
		$cum_refer_redeem_count = 0;
		
		$rs = Database::mysqli_query($sql);
		if(!$rs)
			error_log("SQL error in CSAPI::getInterestActions(): " . Database::mysqli_error() . "\nSQL: " . $sql);
			
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_fb_id = $row['interest_id'];
			$interest_name = $arr_top_interests[$interest_fb_id][1];
			$group_size = $arr_top_interests[$interest_fb_id][0];
			
			$view_count = $row['view_count'] + 0;
			$claim_count = $row['claim_count'] + 0;
			$redeem_count = $row['redeem_count'] + 0;
			$share_count = $row['share_count'] + 0;
			$refer_count = $row['refer_count'] + 0;
			
			$refer_redeem_count = $row['refer_redeem_count'] + 0;

			$view2claim_ratio = $row['view2claim_ratio'] + 0;
			$claim2redeem_ratio = $row['claim2redeem_ratio'] + 0;
			$claim2share_ratio = $row['claim2share_ratio'] + 0;
			$share2share_claim_ratio = $row['share2share_claim_ratio'] + 0;
			$share2share_redeem_ratio = $row['share2share_redeem_ratio'] + 0;
			
			
			$response['graph_data'][] = array(
				'interest_name' => $interest_name,
				'interest_fb_id' => $interest_fb_id,
				'group_size' => $group_size,
				// 'total_followers' => $total_followers,
				'view_count' => $view_count,
				'claim_count' => $claim_count,
				'redeem_count' => $redeem_count,
				'share_count' => $share_count,
				'share_claim_count' => $refer_count,
				'view2claim_ratio' => $view2claim_ratio,
				'claim2redeem_ratio' => $claim2redeem_ratio,
				'claim2share_ratio' => $claim2share_ratio,
				'share2share_claim_ratio' => $share2share_claim_ratio,
				'share2share_redeem_ratio' => $share2share_redeem_ratio,
			);
			
			$cum_group_size += $group_size;
			$cum_view_count += $view_count;
			$cum_claim_count += $claim_count;
			$cum_redeem_count += $redeem_count;
			$cum_share_count += $share_count;
			$cum_refer_count += $refer_count;
			$cum_refer_redeem_count += $refer_redeem_count;
			
		}
		
		$cum_view2claim_ratio = !empty($cum_view_count) ? round(($cum_claim_count * 100) / $cum_view_count, 2) : 0.00;
		$cum_claim2redeem_ratio = !empty($cum_claim_count) ? round(($cum_redeem_count * 100) / $cum_claim_count, 2) : 0.00;
		$cum_claim2share_ratio = !empty($cum_claim_count) ? round(($cum_share_count * 100) / $cum_claim_count, 2) : 0.00;
		$cum_share2share_claim_ratio = !empty($cum_share_count) ? round(($cum_refer_count * 100) / $cum_share_count, 2) : 0.00;
		$cum_share2share_redeem_ratio = !empty($cum_share_count) ? round(($cum_refer_redeem_count * 100) / $cum_share_count, 2) : 0.00;

		$response['cumulative_data'] = array(
			'cum_group_size' => $cum_group_size,
			'cum_view_count' => $cum_view_count,
			'cum_claim_count' => $cum_claim_count,
			'cum_redeem_count' => $cum_redeem_count,
			'cum_share_count' => $cum_share_count,
			'cum_refer_count' => $cum_refer_count,
			'cum_refer_redeem_count' => $cum_refer_redeem_count,
			// 'cum_view2claim_ratio' => $cum_view2claim_ratio,
			// 'cum_claim2redeem_ratio' => $cum_claim2redeem_ratio,
			// 'cum_claim2share_ratio' => $cum_claim2share_ratio,
			// 'cum_share2share_claim_ratio' => $cum_share2share_claim_ratio,
			// 'cum_share2share_redeem_ratio' => $cum_share2share_redeem_ratio,
		);
		
		$response['categories'] = array(
			'interest_name',
			'interest_fb_id',
			'group_size',
			'view_count',
			'claim_count',
			'redeem_count',
			'share_count',
			'share_claim_count',
			'view2claim_ratio',
			'claim2redeem_ratio',
			'claim2share_ratio',
			'share2share_claim_ratio',
			'share2share_redeem_ratio',	
		);
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['graph_data'], $sort, $sort_order);
		
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	/*
	function getInterestActions($params)
	{
		$response = array();
		$response['graph_data'] = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		
		$min_group_size = !empty($params['minGroupSize']) ? $params['minGroupSize']: (!empty($params['mingroupsize']) ? $params['mingroupsize'] : 3);
		
		$limit = !empty($params['limit']) ? $params['limit']: 20;
		
		$min_interest_fan_base = !empty($params['minInterestFanbase']) ?  $params['minInterestFanbase'] : (!empty($params['mininterestfanbase']) ? $params['mininterestfanbase'] : 0);
		
		$sort = !empty($params['sort']) ? $params['sort'] : 'view2claim_ratio';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		
		
		$sql = "select t.* from
		(
		select fl.fb_id, fl.name, count(ufl.user_id) as group_size, count(iv.id) as view_count, count(ui.id) as claim_count, count(r.id) as share_count, count(redeem.id) as redeem_count, count(refer.id) as refer_count, count(refer_redeem.id) as refer_redeem_count
		from user_fb_likes ufl
		inner join fb_likes fl on ufl.fb_like_id = fl.id
		left join items_views iv on (ufl.user_id = iv.user_id and iv.items_id = ufl.item_id)
		left join user_items ui on (ufl.user_id = ui.user_id and ui.item_id = ufl.item_id and ui.date_claimed is not null)
		left join user_items redeem on (ufl.user_id = redeem.user_id and redeem.item_id = ufl.item_id and redeem.date_redeemed is not null)
		left join user_items refer on (ufl.user_id = refer.user_id and refer.item_id = ufl.item_id and refer.referral_id is not null)
		left join user_items refer_redeem on (ufl.user_id = refer_redeem.user_id and refer_redeem.item_id = ufl.item_id and refer_redeem.referral_id is not null and refer_redeem.date_redeemed is not null)
		left join referrals r on (ufl.user_id = r.user_id and r.item_shared = ufl.item_id)
		where ufl.item_id in ($campaign_ids)
		group by fl.fb_id
		) as t
		order by t.group_size desc limit $limit";
		
		// error_log("SQL in CSAPI::getInterestActions(): " . $sql);
		
		$cum_view_count = 0;
		$cum_claim_count = 0;
		$cum_redeem_count = 0;
		$cum_share_count = 0;
		$cum_refer_count = 0;
		$cum_refer_redeem_count = 0;
		
		$rs = Database::mysqli_query($sql);
		if(!$rs)
			error_log("SQL error in CSAPI::getInterestActions(): " . Database::mysqli_error() . "\nSQL: " . $sql);
			
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_fb_id = $row['fb_id'];
			$interest_name = $row['name'];
			$group_size = $row['group_size'] + 0;
			
			$view_count			= $row['view_count'] + 0;
			$claim_count		= $row['claim_count'] + 0;
			$redeem_count		= $row['redeem_count'] + 0;
			$share_count		= $row['share_count'] + 0;
			$refer_count		= $row['refer_count'] + 0;
			$refer_redeem_count = $row['refer_redeem_count'] + 0;

			$view2claim_ratio = !empty($view_count) ? round($claim_count / $view_count, 2) : 0; //  $row['view2claim_ratio'] + 0;
			$claim2redeem_ratio = !empty($claim_count) ? round($redeem_count / $claim_count, 2) : 0; //$row['claim2redeem_ratio'] + 0;
			$claim2share_ratio = !empty($claim_count) ? round($share_count / $claim_count, 2) : 0; // $row['claim2share_ratio'] + 0;
			$share2share_claim_ratio = !empty($share_count) ? round($refer_count / $share_count, 2) : 0; // $row['share2share_claim_ratio'] + 0;
			$share2share_redeem_ratio = !empty($share_count) ? round($refer_redeem_count / $share_count, 2) : 0; // $row['share2share_redeem_ratio'] + 0;
			
			
			$response['graph_data'][] = array(
				'interest_name' => $interest_name,
				'interest_fb_id' => $interest_fb_id,
				'group_size' => $group_size,
				// 'total_followers' => $total_followers,
				'view_count' => $view_count,
				'claim_count' => $claim_count,
				'redeem_count' => $redeem_count,
				'share_count' => $share_count,
				'share_claim_count' => $refer_count,
				'view2claim_ratio' => $view2claim_ratio,
				'claim2redeem_ratio' => $claim2redeem_ratio,
				'claim2share_ratio' => $claim2share_ratio,
				'share2share_claim_ratio' => $share2share_claim_ratio,
				'share2share_redeem_ratio' => $share2share_redeem_ratio,
			);
			
			$cum_group_size += $group_size;
			$cum_view_count += $view_count;
			$cum_claim_count += $claim_count;
			$cum_redeem_count += $redeem_count;
			$cum_share_count += $share_count;
			$cum_refer_count += $refer_count;
			$cum_refer_redeem_count += $refer_redeem_count;
			
		}
		
		$cum_view2claim_ratio = !empty($cum_view_count) ? round(($cum_claim_count * 100) / $cum_view_count, 2) : 0.00;
		$cum_claim2redeem_ratio = !empty($cum_claim_count) ? round(($cum_redeem_count * 100) / $cum_claim_count, 2) : 0.00;
		$cum_claim2share_ratio = !empty($cum_claim_count) ? round(($cum_share_count * 100) / $cum_claim_count, 2) : 0.00;
		$cum_share2share_claim_ratio = !empty($cum_share_count) ? round(($cum_refer_count * 100) / $cum_share_count, 2) : 0.00;
		$cum_share2share_redeem_ratio = !empty($cum_share_count) ? round(($cum_refer_redeem_count * 100) / $cum_share_count, 2) : 0.00;

		$response['cumulative_data'] = array(
			'cum_group_size' => $cum_group_size,
			'cum_view_count' => $cum_view_count,
			'cum_claim_count' => $cum_claim_count,
			'cum_redeem_count' => $cum_redeem_count,
			'cum_share_count' => $cum_share_count,
			'cum_refer_count' => $cum_refer_count,
			'cum_refer_redeem_count' => $cum_refer_redeem_count,
			// 'cum_view2claim_ratio' => $cum_view2claim_ratio,
			// 'cum_claim2redeem_ratio' => $cum_claim2redeem_ratio,
			// 'cum_claim2share_ratio' => $cum_claim2share_ratio,
			// 'cum_share2share_claim_ratio' => $cum_share2share_claim_ratio,
			// 'cum_share2share_redeem_ratio' => $cum_share2share_redeem_ratio,
		);
		
		$response['categories'] = array(
			'interest_name',
			'interest_fb_id',
			'group_size',
			'view_count',
			'claim_count',
			'redeem_count',
			'share_count',
			'share_claim_count',
			'view2claim_ratio',
			'claim2redeem_ratio',
			'claim2share_ratio',
			'share2share_claim_ratio',
			'share2share_redeem_ratio',	
		);
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['graph_data'], $sort, $sort_order);
		
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	*/
	function getTopInterestOverlapCount($params)
	{
		$response = array();
		$response['graph_data'] = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$entrant_id = !empty($params['entrantId']) ? $params['entrantId'] : (!empty($params['entrantid']) ? $params['entrantid'] : '');
		$limit = !empty($params['limit']) ? $params['limit']: 10;
		$min_interest_fan_base = !empty($params['minInterestFanbase']) ?  $params['minInterestFanbase'] : (!empty($params['mininterestfanbase']) ? $params['mininterestfanbase'] : 0);
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		// Get Top interests
		
		//	1.	Get Top Interest IDs
		// Get Top interests
		
		$timer_start = array_sum(explode(" ", microtime()));
		$sql = "select interest_id, entrant_like_count
		from
		(
		select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) and entrant_like_count > $min_interest_fan_base group by interest_id
		) as t
		order by t.entrant_like_count desc limit $limit";
		// error_log("SQL0 in CSAPI::getTopInterestOverlapCount(): " . $sql);
		$arr_campaign_interest_likes = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_id = $row['interest_id'];
			$entrant_like_count = $row['entrant_like_count'] + 0;
			$arr_campaign_interest_likes[$interest_id] = $entrant_like_count;
		}
		
		// error_log("CSAPI::getTopInterestOverlapCount(): Time taken to get the campaign interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		//	2.	Get Names for those Interests
		
		
		// error_log("CSAPI::getTopInterestOverlapCount(): Time taken to get the campaign interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		$sql = "select distinct(fb_id) as fb_id, name, total_followers from tmp_like_interest_1 where fb_id in (" . implode(',', array_keys($arr_campaign_interest_likes)). ")";
		// error_log("SQL1 in CSAPI::getTopInterestOverlapCount(): " . $sql);
		$arr_top_interests = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_count = $arr_campaign_interest_likes[$interest_id];
			$row['entrant_count'] = $entrant_count;
			$arr_top_interests[$row['fb_id']] = $row;
		}
		// error_log("CSAPI::getTopInterestOverlapCount(): Time taken to get the top interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		$arr_interest_entrants = array();
		$sql = "select interest_id, entrants from
		(
		select interest_id, group_concat(distinct(entrant_id) order by entrant_id separator ',') as entrants, count(entrant_id) as num_entrants from tmp_entrant_interest where interest_id in ('" . implode("','", array_keys($arr_top_interests)) . "') group by interest_id
		) as t
		order by num_entrants desc";
		// error_log("SQL2 in CSAPI::getTopInterestOverlapCount(): " . $sql);
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $num_entrants = $row['num_entrants'];
			$entrants = $row['entrants'];
			// $arr_entrants = explode(',', $entrants);
			$interest_id = $row['interest_id'];
			// error_log("interest_id: " . $interest_id . ", num_entrants: " . $num_entrants . ", count(entrants) : " . count($arr_entrants));
			$arr_interest_entrants[] = array($interest_id, $entrants);
		}
		// error_log("CSAPI::getTopInterestOverlapCount(): Time taken to get the interests entrants: " . (array_sum(explode(" ", microtime())) - $timer_start));
		$num_interests = count($arr_interest_entrants);
		$offset = $num_interests / 2;
		$arr_x_axis = array();
		$arr_y_axis = array();
		
		// for($i = 0; $i < $offset; $i++)
		for($i = 0; $i < $num_interests; $i++)
		{
			$interest1_data = $arr_interest_entrants[$i];
			$interest1 = $arr_top_interests[$interest1_data[0]]['name'];
			$entrants1 = explode(',', $interest1_data[1]);
			
			$arr_x_axis[$i] = $interest1;
				
			$j_count = 0;
			// for($j = $offset; $j < $num_interests; $j++)
			for($j = 0; $j < $num_interests; $j++)
			{

				$interest2_data = $arr_interest_entrants[$j];
				$interest2 = $arr_top_interests[$interest2_data[0]]['name'];
				$entrants2 = explode(',', $interest2_data[1]);
				$arr_y_axis[$j] = $interest2;
				
				$unique_entrants = array_intersect($entrants1, $entrants2);
				$entrant_count = count($unique_entrants);
				$response['graph_data'][] = array($i, $j_count, $entrant_count);
				
				$j_count++;
			}
		}
		
		// error_log("CSAPI::getTopInterestOverlapCount(): Time taken to get the final data: " . (array_sum(explode(" ", microtime())) - $timer_start));
		$response['xAxis'] = array_values($arr_x_axis);
		$response['yAxis'] = array_values($arr_y_axis);
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	function getEntrantInfo($params)
	{
		$response = array();
		$response['graph_data'] = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$entrant_id = !empty($params['entrantId']) ? $params['entrantId'] : (!empty($params['entrantid']) ? $params['entrantid'] : '');
		
		$interest_limit = !empty($params['interestLimit']) ? $params['interestLimit'] : (!empty($params['interestlimit']) ? $params['interestlimit'] : 3);
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] : 'desc');
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		// Getting top interests
		$sql = "select til.fb_id as interest_id, til.name as interest, til.total_followers from tmp_entrant_interest tei inner join tmp_like_interest_1 til on tei.interest_id = til.fb_id where entrant_id = '$entrant_id' group by til.fb_id order by til.total_followers $sort_order limit $interest_limit";
		$rs = Database::mysqli_query($sql);
		$entrant_interests = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_id = $row['interest_id'];
			$interest = $row['interest'];
			
			if(!isset($entrant_interests[$interest_id]))
				$entrant_interests[$interest_id] = $interest;
		}
		
		$sql = "select id as `entrant_id`, facebook_id as `entrant_fb_id`, concat(firstname, ' ', lastname) as `name`, email, date_of_birth as dob, gender as sex, relationship_status as rel_status from users where id = '$entrant_id'";
		$row = BasicDataObject::getDataRow($sql);
		$response['graph_data'] = $row;
		$response['graph_data']['top_interests'] = $entrant_interests;
		
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	function getCompanyAdvocateActivity($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$activity_type_1 = !empty($params['activityType1']) ? $params['activityType1'] : (!empty($params['activitytype1']) ? $params['activitytype1'] : 'share');
		$activity_type_2 = !empty($params['activityType2']) ? $params['activityType2'] : (!empty($params['activitytype2']) ? $params['activitytype2'] : 'referral');
		$activity_min_1 = !empty($params['activity1min']) ? $params['activity1min'] : 1;
		$activity_min_2 = !empty($params['activity2min']) ? $params['activity2min'] : 1;
		$limit = !empty($params['limit']) ? $params['limit'] : 20;
		
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] : 'desc');
		$gender_code = !empty($params['genderCode']) ? $params['genderCode'] : (!empty($params['gendercode']) ? $params['gendercode'] : '');
		$rel_status_id = !empty($params['relStatusId']) ? $params['relStatusId'] : (!empty($params['relstatusid']) ? $params['relstatusid'] : '');
		
		


		$column_names = array('view' => 'view_count', 'claim' => 'claim_count', 'redeem' => 'redeem_count', 'share' => 'share_count', 'referral' => 'refer_count', 'as' => 'advocacy_score');
		$column1 = $column_names[$activity_type_1];
		$column2 = $column_names[$activity_type_2];
		
		$sort_1 = !empty($params['sort1']) ? $column_names[$params['sort1']] : $column1;
		$sort_2 = !empty($params['sort2']) ? $column_names[$params['sort2']] : $column2;

		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		

		$sql = "select entrant_id, $column1, $column2, advocacy_score from
			(
			select tec.entrant_id, sum(view_count) as `view_count`, sum(claim_count) as `claim_count`, sum(redeem_count) as `redeem_count`, sum(share_count) as `share_count`, sum(refer_count) as `refer_count`, round(avg(advocacy_score), 2) as advocacy_score
			from tmp_entrant_campaign tec
			inner join users u on tec.entrant_id = u.id
			where tec.campaign_id in ($campaign_ids)
			and u.is_test_account != 1
			and tec.entrant_id > 0
			group by tec.entrant_id 
			) as t
			order by $sort_1 $sort_order, $sort_2 $sort_order
			limit $limit";
		// error_log("SQL in CSAPI::getCompanyAdvocateActivity(): " . $sql);
		
		$rs = Database::mysqli_query($sql);
		$tmp_data = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_id			= $row['entrant_id'];
			$activity_1_count	= $row[$column1] + 0;
			$activity_2_count	= $row[$column2] + 0;
			$advocacy_score		= Round($row['advocacy_score'] + 0, 2);
			$row['name'] = '';
			
			$tmp_data[$entrant_id] = array($activity_1_count, $activity_2_count, $advocacy_score, '', $entrant_id);
		}
		
		
		$sql = "select id as user_id, concat(firstname, ' ', lastname) as entrant_name from users where id in (" . implode(',', array_keys($tmp_data)). ")";
		// error_log("SQL2 in CSAPI::getCompanyAdvocateActivity(): " . $sql);
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_id = $row['user_id'];
			if(isset($tmp_data[$entrant_id]))
			{
				$tmp_data[$entrant_id][3] = $row['entrant_name'];
			}
		}
		$response['graph_data'] = $tmp_data;
		
		$response['settings'] = $params;
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	function getCompanyAdvocateScores($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$limit = !empty($params['limit']) ? $params['limit'] : 20;
		$interest_limit = !empty($params['interestLimit']) ? $params['interestLimit'] : (!empty($params['interestlimit']) ? $params['interestlimit'] : 3);
		$sort = !empty($params['sort']) ? $params['sort'] : 'as_score';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] : 'desc');
		$gender_code = !empty($params['genderCode']) ? $params['genderCode'] : (!empty($params['gendercode']) ? $params['gendercode'] : 'all');
		$rel_status_id = !empty($params['relStatusId']) ? $params['relStatusId'] : (!empty($params['relstatusid']) ? $params['relstatusid'] : '');
		$location_id = !empty($params['locationId']) ? $params['locationId'] : (!empty($params['locationid']) ? $params['locationid'] : '');
		
		$age_range_id = !empty($params['ageRangeId']) ? $params['ageRangeId'] : (!empty($params['agerangeid']) ? $params['agerangeid'] : '');
		
		$interest_id = !empty($params['interestId']) ? $params['interestId'] : (!empty($params['interestid']) ? $params['interestid'] : '');
		
		$dma_code = !empty($params['dmaCode']) ? $params['dmaCode'] : (!empty($params['dmacode']) ? $params['dmacode'] : '');
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		$where_clause = "";
		$arr_where_clause = array("tec.campaign_id in ($campaign_ids)", "u.is_test_account != 1", "u.facebook_id > 0");
		
		
		if(!empty($gender_code) && $gender_code != 'all')
			$arr_where_clause[] = "u.gender = '" . $gender_code. "'";
			
		if(!empty($rel_status_id))
			$arr_where_clause[] = "u.relationship_status = '" . $rel_status_id. "'";
			
		if(!empty($location_id))
			$arr_where_clause[] = "u.facebook_location_id = '" . $location_id. "'";
		
		$join_clause = "inner join users u on tec.entrant_id = u.id";
		if(!empty($age_range_id))
		{
			$join_clause .= " inner join api_age_range ar on DATE_FORMAT(FROM_DAYS(TO_DAYS(now()) - TO_DAYS(u.date_of_birth)), '%Y') + 0 between ar.age_min and ar.age_max ";
			$arr_where_clause[] = "ar.age_range_id = '" . $age_range_id. "'";
		}
		
		if(!empty($interest_id))
			$arr_where_clause[] = "u.id in (select entrant_id from tmp_entrant_interest where interest_id = '$interest_id')";
		
		if(!empty($dma_code))
		{
			$join_clause .= " inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id";
			$arr_where_clause[] = "dma.dma_region_code = '" . $dma_code. "'";
		}
		
		if(!empty($arr_where_clause))
			$where_clause = " where " . implode(" and ", $arr_where_clause);
		

		// Getting top entrants
			
		$sql = "select entrant_id, advocacy_score from
			(
			select tec.entrant_id, round(avg(advocacy_score), 2) as advocacy_score
			from tmp_entrant_campaign tec
			$join_clause
			$where_clause
			group by tec.entrant_id
			) as t
			order by advocacy_score $sort_order
			limit $limit";
		// error_log("SQL in CSAPI::getCompanyAdvocateScores(): " . $sql);
		
		$rs = Database::mysqli_query($sql);
		$tmp_data = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_id			= $row['entrant_id'];
			$advocacy_score		= Round($row['advocacy_score'] + 0, 2);
			$tmp_data[$entrant_id] = $advocacy_score;
		}
		
		// error_log("tmp_data: " . var_export($tmp_data, true));

		// Getting top interests
		$sql = "select tei.entrant_id, til.fb_id as interest_id, til.name as interest, til.total_followers from tmp_entrant_interest tei inner join tmp_like_interest_1 til on tei.interest_id = til.fb_id where tei.entrant_id in (" . implode(',', array_keys($tmp_data)). ") group by tei.entrant_id, til.fb_id order by til.total_followers desc";
		
		error_log("SQL3 in CSAPI::getCompanyAdvocateScores(): " . $sql);
		$rs = Database::mysqli_query($sql);
		$entrant_interests = array();
		$interests = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_id = $row['entrant_id'];
			$interest_id = $row['interest_id'];
			$interest = $row['interest'];
			$total_followers = $row['total_followers'] + 0;
			
			if(!isset($entrant_interests[$entrant_id][$interest_id]))
				$entrant_interests[$entrant_id][$interest_id] = $total_followers;
			
			if(!isset($interests[$interest_id]))
				$interests[$interest_id] = $interest;
		}
		
		foreach($entrant_interests as $entrant_id => $tmp)
		{
			// arsort($entrant_interests[$entrant_id]);
			$entrant_interests[$entrant_id] = array_slice($entrant_interests[$entrant_id], 0, $interest_limit, true);
		}
		// error_log("entrant_interests after sorting: " . var_export($entrant_interests, true));
		
		foreach($entrant_interests as $entrant_id => $interest_data)
		{
			foreach($interest_data as $interest_id => $total_followers)
				$entrant_interests[$entrant_id][$interest_id] = $interests[$interest_id];
		}
		// error_log("entrant_interests after renaming: " . var_export($entrant_interests, true));
		
		$sql = "select id as `entrant_id`, facebook_id as entrant_fb_id, concat(firstname, ' ', lastname) as `name`, email, date_of_birth as dob, gender as sex, relationship_status as rel_status from users where id in (" . implode(',', array_keys($tmp_data)). ")";
		
		$rs = Database::mysqli_query($sql);
		$response['graph_data'] = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$entrant_id = $row['entrant_id'];
			$row['as_score'] = isset($tmp_data[$entrant_id]) ? $tmp_data[$entrant_id] : 0;
			$row['top_interests'] = isset($entrant_interests[$entrant_id])? $entrant_interests[$entrant_id] : array();
			$response['graph_data'][] = $row;
		}
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['graph_data'], $sort, $sort_order);

		
		$response['settings'] = $params;
		
		//POPULATE SETTINGS WITH DEFAULT VALUES 
		$response['settings']['limit'] = $limit;
		$response['settings']['interestLimit'] = $interest_limit;
		$response['settings']['sort'] = $sort;
		$response['settings']['sortOrder'] = $sort_order;
		$response['settings']['genderCode'] = $gender_code;
		$response['settings']['relStatusId'] = $rel_status_id;
		$response['settings']['locationId'] = $location_id;
		$response['settings']['ageRangeId'] = $age_range_id;
		$response['settings']['interestId'] = $interest_id;
		$response['settings']['dmaCode'] = $dma_code;
		Database::mysqli_free_result($rs);
	
		return $response;

	}

	function getAllReferralRedemptions()
	{
		$sql = "select count(ui.id) as num_redeems from user_items ui inner join companies c on ui.company_id = c.id where c.demo != 1 and c.status = 'active' and ui.date_redeemed is not null and ui.referral_id is not null";
		$row = BasicDataObject::getDataRow($sql);
		$num_redeems = $row['num_redeems'] + 0;
		return $num_redeems;
	}
	
	
	function getAllReferralClaims()
	{
		$sql = "select count(ui.id) as num_claims from user_items ui inner join companies c on ui.company_id = c.id where c.demo != 1 and c.status = 'active' and date_claimed is not null and referral_id is not null";
		$row = BasicDataObject::getDataRow($sql);
		$num_claims = $row['num_claims'] + 0;
		return $num_claims;
	}
	
	public static function getSQLForAllEntrants($company_id = null, $item_id = null, $get_count = false)
	{
		$where_clause = "";
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
			$where_clause = " where t.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
			
		$sql_views = "select distinct(user_id) as user_id from items_views where user_id > 0";		
		$sql_claims = "select distinct(user_id) as user_id from user_items where user_id > 0";
		$sql_referrals = "select distinct(user_id) as user_id from referrals where user_id > 0";
		
		if(!empty($company_id))
		{
			$sql_views .= " and company_id = '$company_id'";
			$sql_claims .= " and company_id = '$company_id'";
			$sql_referrals .= " and company_id = '$company_id'";
		}
		
		if(!empty($item_id)) 
		{
			$sql_views .= " and items_id = '$item_id'";
			$sql_claims .= " and item_id = '$item_id'";
			$sql_referrals .= " and item_shared = '$item_id'";
		}

		
		$sql = "select distinct(t.user_id) as user_id from (" . $sql_views . " union " . $sql_claims . " union ". $sql_referrals . ") as t" . $where_clause;
		
		if($get_count)
			$sql = "select count(distinct(t.user_id)) as num_entrants from (" . $sql_views . " union " . $sql_claims . " union ". $sql_referrals . ") as t" . $where_clause;
		
		// error_log("SQL in CSAPI::getSQLForAllEntrants(): " . $sql);
		return $sql;
	}
	
	function getAllEntrants($company_id = null)
	{
		// $arr_test_user_account_ids = User::getTestUserAccountsIds();
		// if(!empty($arr_test_user_account_ids))
		// 	$where_clause = " where user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		// $sql = "select count(distinct(user_id)) as num_entrants from user_items where user_id > 0";
		// $sql = "select count(id) as num_entrants from users";
		// $sql = "select count(t.user_id) as num_entrants from (select user_id from items_views where user_id > 0 union all select distinct(user_id) as user_id from user_items where user_id > 0 union all select user_id from referrals where user_id > 0) as t $where_clause";
		
		$sql = CSAPI::getSQLForAllEntrants(null, null, true);
		
		$row = BasicDataObject::getDataRow($sql);
		$num_entrants = $row['num_entrants'] + 0;
		return $num_entrants;
	}
	
	function getAllFBFriends()
	{
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		if(!empty($arr_test_user_account_ids))
			$where_clause = " and id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		$sql = "select sum(fb_friend_count) as fb_friend_count from users where fb_friend_count > 0 $where_clause";
		$row = BasicDataObject::getDataRow($sql);
		$fb_friend_count = $row['fb_friend_count'] + 0;
		return $fb_friend_count;
	}
	/*
	
	function getCumulativeEntrantCount2($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$start_date = $params['startDate'];
		$end_date = $params['endDate'];
		// $activity_type = !empty($params['activityType']) ? $params['activityType'] : 'view';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		// $interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : 86400;
		$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : (!empty($params['timeintervalseconds']) ? $params['timeintervalseconds'] : 3600 );
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		//	1.	Calculating Interval specific variables
		switch($interval)
		{
			case 60:	//	Minute
				$mysql_date_format = "%m/%d/%Y\T%H:%i:00$time_zone";
				$php_date_format = "m/d/Y\TH:i:00$time_zone";
				break;
	
			case 3600:	//	Hour
				$mysql_date_format = "%m/%d/%Y\T%H:00:00$time_zone";
				$php_date_format = "m/d/Y\TH:00:00$time_zone";
				break;
	
			case 86400:	//	Day
				// $mysql_date_format = '%m/%d/%Y';
				// $php_date_format = 'm/d/Y';
				$mysql_date_format = "%Y-%m-%d\T00:00:00$time_zone";
				$php_date_format = "Y-m-d\T00:00:00$time_zone";
				break;
		}
		
		
		
		$arr_where_clause = array();
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			//$arr_campaign_ids = array(-1);
			//$rs = Database::mysqli_query("select campaign_id from tmp_campaign where company_id = '$company_id'");
			//while($row = Database::mysqli_fetch_assoc($rs))
			//	$arr_campaign_ids[] = $row['campaign_id'];
			//$campaign_ids = implode(',', $arr_campaign_ids);
			
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
		}
		else
		{
		
			$arr_where_clause[] = " ea.campaign_id in (" . $campaign_ids . ") ";
		}
		
		if(!empty($activity_type))
			$arr_where_clause[] = " ea.activity_type = '$activity_type'";
		
		
		//	2.	Determining values for start and end dates
		$start_date_val = 0;
		$end_date_val = 0;
		if(empty($start_date))
		{
			$tmp_sql = "select min(ea.created) as start_date from tmp_entrant_activity ea $join_clause";
			if(!empty($arr_where_clause))
			{
				$tmp_sql .= " where " . implode(" and ", $arr_where_clause);
			}
			// error_log("tmp_sql: " . $tmp_sql);
			$rs = Database::mysqli_query($tmp_sql);
			$row = Database::mysqli_fetch_assoc($rs);
			if(!empty($row['start_date']))
			{
				$start_date_val =  strtotime($row['start_date'] . " UTC");
				$start_date = $row['start_date'];
			}
			else	//	Otherwise Set dummy values for start and end dates so that the loop doesn't run
			{
				$start_date_val = 1;
				$end_date_val = 0;
			}
		}
		else
		{
			$start_date_val = strtotime($start_date . " UTC");
		}
		
		if($start_date_val == 1 && empty($end_date_val)) //  Just in case start_date_val could not be set
		{
			error_log("start_date_val = 1 and end_date_val = 0");
		}
		else
		{
			if(empty($end_date))
			{
				$tmp_sql = "select max(ea.created) as end_date from tmp_entrant_activity ea $join_clause";
				if(!empty($arr_where_clause))
				{
					$tmp_sql .= " where " . implode(" and ", $arr_where_clause);
				}
				$rs = Database::mysqli_query($tmp_sql);
				$row = Database::mysqli_fetch_assoc($rs);
				if(empty($row['end_date']) || $row['end_date'] == '0000-00-00')
				{
					$end_date_val =  strtotime("yesterday UTC");	
					$end_date = date('Y-m-d', $end_date_val);
				}
				else	//	Otherwise Set dummy values for start and end dates so that the loop doesn't run
				{
					$end_date_val =  strtotime($row['end_date'] . " UTC");
					$end_date = $row['end_date'];
				}
			}
			else
			{
				$end_date_val = strtotime($end_date . " UTC");
			}
		
		
			// error_log("start_date_val: $start_date_val or " . date($php_date_format, $start_date_val));
			// error_log("end_date_val: $end_date_val or " . date($php_date_format, $end_date_val));
		
		
		
			$sql = "select date_format(ea.created, '$mysql_date_format') as date, count(ea.activity_id) as entrant_count 
				from tmp_entrant_activity ea $join_clause";
		
			if(!empty($start_date) && !empty($end_date))
				$arr_where_clause[] = " ea.created between '$start_date' and '$end_date'";
			else if(!empty($start_date))
				$arr_where_clause[] = " ea.created >= '$start_date'";
			else if(!empty($end_date))
				$arr_where_clause[] = " ea.created <= '$end_date'";
		
		
			if(!empty($arr_where_clause))
			{
				$sql .= " where " . implode(" and ", $arr_where_clause);
			}
			$sql .= " group by date_format(ea.created, '$mysql_date_format')";
			// $sql .= " order by ea.created";
			// error_log("SQL in CSAPI::getCumulativeEntrantCount(): " . $sql);
			$rs = Database::mysqli_query($sql);
		
		
			$tmp_data = array();
			while($row = Database::mysqli_fetch_assoc($rs))
			{
				$entrant_count = $row['entrant_count'];
				$tmp_date = $row['date'];
				//if(empty($tmp_data) && empty($start_date))
				//	$start_date_val = strtotime($tmp_date . " UTC");
				//else if(empty($end_date))
				//	$end_date_val = strtotime($tmp_date . " UTC");
				$tmp_data[$tmp_date] = $entrant_count;
			}
		}
		
		
		
		$response = array(
			'settings' => array(
				'companyId' => $company_id,
				'startDate' => $start_date,
				'endDate' => $end_date,
				'timeIntervalSeconds' => $interval,
				'timezone' => $time_zone,
				'activityType' => $activity_type,
			),
			'data' => array(),
		);
		
		$cumm_entrant_count = 0;
		for($i = $start_date_val; $i <= $end_date_val; $i += $interval)
		{
			// $tmp_date = date('Y-m-d H:i:s', strtotime($start_time . '+ ' . $counter++. ' minute'));
			// $tmp_date = date('Y-m-d H:i:s', strtotime($end_time . '- ' . $counter++. ' minute'));
			$tmp_date = date($php_date_format, $i);
			if(isset($tmp_data[$tmp_date]))
			{
				$cumm_entrant_count += $tmp_data[$tmp_date];
			}
			$response['data'][$tmp_date] = $cumm_entrant_count;
		}


		if(!empty($errors))
			return $errors;
			
		return $response;
	}

	*/
	
	function getCampaignInterests($params)
	{
		$response = array();
		// error_log("params in CSAPI::getCampaignInterests(): " . var_export($params, true));
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$limit = !empty($params['limit']) ? $params['limit'] : 100;
		$min_group_size = !empty($params['min-group-size']) ? $params['min-group-size'] : 
			( 
				!empty($params['minGroupSize']) ? $params['minGroupSize'] : 
				(
					!empty($params['mingroupsize']) ? $params['mingroupsize'] : 1
				)
			);
		$min_interest_fan_base = !empty($params['minInterestFanBase']) ? $params['minInterestFanBase'] : 
			( 
				!empty($params['mininterestfanbase']) ? $params['mininterestfanbase'] : 
				(
					!empty($params['min-interest-fan-base']) ? $params['min-interest-fan-base'] : 0
				)
			);
		/*  TODO fix sorting parameters so that default is "entrants"   */
		$sort = !empty($params['sort']) ? $params['sort'] : 'entrants' ; // 'entrants';
		//$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] :'desc');
		
		
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		// Get Top interests
		
		$timer_start = array_sum(explode(" ", microtime()));
		// $sql = "select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) and entrant_like_count >= '$min_group_size' group by interest_id order by sum(entrant_like_count) desc limit $limit";
		
		$sql = "select interest_id, entrant_like_count from
		(
		select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) and entrant_like_count >= '$min_group_size' group by interest_id
		) as t
		order by entrant_like_count desc limit $limit";
		// error_log("SQL0 in CSAPI::getCampaignInterests(): " . $sql);
		$arr_campaign_interest_likes = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_id = $row['interest_id'];
			$entrant_like_count = $row['entrant_like_count'] + 0;
			$arr_campaign_interest_likes[$interest_id] = $entrant_like_count;
		}
		
		// error_log("CSAPI::getCampaignInterests(): Time taken to get the campaign interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		$sql = "select fb_id, name, total_followers from tmp_like_interest_1 where fb_id in (" . implode(',', array_keys($arr_campaign_interest_likes)). ") and total_followers >= '$min_interest_fan_base'";
		// error_log("SQL1 in CSAPI::getCampaignInterests(): " . $sql);
		$arr_top_interests = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $entrant_count = $row['total_followers'];
			$interest = $row['name'];
			$interest_id = $row['fb_id'];
			$total_followers = $row['total_followers'];
			$entrant_count = $arr_campaign_interest_likes[$interest_id];
			$arr_top_interests[$interest] = array(
				'interest_id' => $interest_id,
				'fb_fanbase' => $total_followers,
				'entrant_count' => $entrant_count,
			);
		}
		arsort($arr_top_interests);
		$total_entrants = 0;
		// $sql = "select count(distinct(user_id)) as num_entrants from user_items where item_id in ($campaign_ids) and date_claimed is not null and user_id > 0";
		$sql = "select count(distinct(entrant_id)) as num_entrants from tmp_entrant_interest where campaign_id in ($campaign_ids)";
		$row = BasicDataObject::getDataRow($sql);
		$total_entrants = $row['num_entrants'];
		$response['graph_data'] = array();
		foreach($arr_top_interests as $interest => $data)
		{
			$interest_id = $data['interest_id'];
			$fb_fanbase = $data['fb_fanbase'];
			$entrant_count = $data['entrant_count'];
			
			$percent = !empty($total_entrants) ? round(($entrant_count * 100) / $total_entrants, 2) : 0;
			// $response['graph_data'][$interest] = array($entrant_count, $percent);
			$response['graph_data'][] = array(
				'interest_name' => $interest,
				'interest_id' => $interest_id,
				'fb_fanbase' => $fb_fanbase,
				'entrants' => $entrant_count, 
				'enrant_percent_of_total' => $percent,
			);
		}
		
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['graph_data'], $sort, $sort_order);
			
		// error_log("CSAPI::getCampaignInterests(): Time taken to get the top interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		Database::mysqli_free_result($rs);
		if(!empty($errors))
			return $errors;

		$response['settings'] = $params;
		return $response;
	}
	
	/*
	function getCampaignActivity2($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$start_date = $params['startDate'];
		$end_date = $params['endDate'];
		// $interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : 3600;
		$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : (!empty($params['timeintervalseconds']) ? $params['timeintervalseconds'] : 3600 );
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		// $activity_type = !empty($params['activityType']) ? $params['activityType'] : 'all';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'view');
		$source = !empty($params['source']) ? $params['source'] : 'all';
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		//	1.	Calculating Interval specific variables
		switch($interval)
		{
			case 60:	//	Minute
				$mysql_date_format = "%m/%d/%Y\T%H:%i:00$time_zone";
				$php_date_format = "m/d/Y\TH:i:00$time_zone";
				break;
	
			case 3600:	//	Hour
				$mysql_date_format = "%m/%d/%Y\T%H:00:00$time_zone";
				$php_date_format = "m/d/Y\TH:00:00$time_zone";
				break;
	
			case 86400:	//	Day
				// $mysql_date_format = '%m/%d/%Y';
				// $php_date_format = 'm/d/Y';
				$mysql_date_format = "%Y-%m-%d\T00:00:00$time_zone";
				$php_date_format = "Y-m-d\T00:00:00$time_zone";
				break;
		}
		
		
		
		$arr_where_clause = array();
		$join_clause = "";
		if(empty($campaign_ids))
		{
			
			//$arr_campaign_ids = array(-1);
			//$rs = Database::mysqli_query("select campaign_id from tmp_campaign where company_id = '$company_id'");
			//while($row = Database::mysqli_fetch_assoc($rs))
			//	$arr_campaign_ids[] = $row['campaign_id'];
			//$campaign_ids = implode(',', $arr_campaign_ids);
			
			$join_clause = "inner join tmp_campaign c on ea.campaign_id = c.campaign_id";
			$arr_where_clause[] = " c.company_id = '$company_id'";
		}
		else
		{
		
			$arr_where_clause[] = " ea.campaign_id in (" . $campaign_ids . ") ";
		}
		
		if(!empty($activity_type) && $activity_type != 'all')
			$arr_where_clause[] = " ea.activity_type = '$activity_type'";
		
		
		//	2.	Determining values for start and end dates
		$start_date_val = 0;
		$end_date_val = 0;
		if(empty($start_date))
		{
			$tmp_sql = "select min(ea.created) as start_date from tmp_entrant_activity ea $join_clause";
			if(!empty($arr_where_clause))
			{
				$tmp_sql .= " where " . implode(" and ", $arr_where_clause);
			}
			error_log("tmp_sql: " . $tmp_sql);
			$rs = Database::mysqli_query($tmp_sql);
			$row = Database::mysqli_fetch_assoc($rs);
			if(!empty($row['start_date']))
			{
				$start_date_val =  strtotime($row['start_date'] . " UTC");
				$start_date = $row['start_date'];
			}
			else	//	Otherwise Set dummy values for start and end dates so that the loop doesn't run
			{
				$start_date_val = 1;
				$end_date_val = 0;
			}
		}
		else
		{
			$start_date_val = strtotime($start_date . " UTC");
		}
		
		if($start_date_val == 1 && empty($end_date_val)) //  Just in case start_date_val could not be set
		{
			error_log("start_date_val = 1 and end_date_val = 0");
		}
		else
		{
			if(empty($end_date))
			{
				$tmp_sql = "select max(ea.created) as end_date from tmp_entrant_activity ea $join_clause";
				if(!empty($arr_where_clause))
				{
					$tmp_sql .= " where " . implode(" and ", $arr_where_clause);
				}
				error_log("tmp_sql: " . $tmp_sql);
				$rs = Database::mysqli_query($tmp_sql);
				$row = Database::mysqli_fetch_assoc($rs);
				if(empty($row['end_date']) || $row['end_date'] == '0000-00-00')
				{
					$end_date_val =  strtotime("yesterday UTC");	
					$end_date = date('Y-m-d', $end_date_val);
				}
				else	//	Otherwise Set dummy values for start and end dates so that the loop doesn't run
				{
					$end_date_val =  strtotime($row['end_date'] . " UTC");
					$end_date = $row['end_date'];
				}
			}
			else
			{
				$end_date_val = strtotime($end_date . " UTC");
			}
		
		
			error_log("start_date_val: $start_date_val or " . date($php_date_format, $start_date_val));
			error_log("end_date_val: $end_date_val or " . date($php_date_format, $end_date_val));
		
		
		
			// $sql = "select date_format(ea.created, '$mysql_date_format') as date, count(ea.activity_id) as entrant_count 
			// 	from tmp_entrant_activity ea $join_clause";
			
			$sql = "select ea.activity_type, date_format(ea.created, '$mysql_date_format') as date, count(ea.activity_id) as activity_count from tmp_entrant_activity ea $join_clause";
			 
		
			if(!empty($start_date) && !empty($end_date))
				$arr_where_clause[] = " ea.created between '$start_date' and '$end_date'";
			else if(!empty($start_date))
				$arr_where_clause[] = " ea.created >= '$start_date'";
			else if(!empty($end_date))
				$arr_where_clause[] = " ea.created <= '$end_date'";
		
		
			if(!empty($arr_where_clause))
			{
				$sql .= " where " . implode(" and ", $arr_where_clause);
			}
		
			$sql .= " group by ea.activity_type, date_format(ea.created, '$mysql_date_format')";
			// $sql .= " order by ea.created";
			error_log("SQL in CSAPI::getCampaignActivity(): " . $sql);
			
			$rs = Database::mysqli_query($sql);
		
		
			$tmp_data = array();
			while($row = Database::mysqli_fetch_assoc($rs))
			{
				$activity_type = $row['activity_type'];
				$tmp_date = $row['date'];
				$activity_count = $row['activity_count'];
				$tmp_data[$activity_type][$tmp_date] = $activity_count;
			}
		}
		
		
		
		$response = array(
			'settings' => array(
				'companyId' => $company_id,
				'startDate' => $start_date,
				'endDate' => $end_date,
				'timeIntervalSeconds' => $interval,
				'timezone' => $time_zone,
				'activityType' => $activity_type,
			),
			'data' => array(),
		);
		foreach($tmp_data as $activity_type => $activity_data)
		{
			$response['data'][$activity_type] = array();
			$cumm_entrant_count = 0;
			for($i = $start_date_val; $i <= $end_date_val; $i += $interval)
			{
				$tmp_date = date($php_date_format, $i);
				if(isset($activity_data[$tmp_date]))
				{
					$cumm_entrant_count += $activity_data[$tmp_date];
				}
				$response['data'][$activity_type][$tmp_date] = $cumm_entrant_count;
			}
		}

		if(!empty($errors))
			return $errors;
			
		return $response;
	}
	*/
	
	public static function fillManagerCompanies($post_data, $get_data)
	{
		// global $selected_company_id;
		// error_log("POST in CSAPI::fillManagerCompanies(): " . var_export($post_data, true));
		$manager_id = $_SESSION['user']->id;
		$manager_companies = Company::getManagerCompanies($manager_id);
		// $selected_company_id = !empty($post_data['cmb-main-company']) ? $post_data['cmb-main-company'] : (isset($manager_companies[0]) ? $manager_companies[0]['company_id'] : '0');
		/*
		if(!empty($post_data['cmb-main-company']))
			$_SESSION['selected_company_id'] = $post_data['cmb-main-company'];
		error_log("SESSION['selected_company_id'] in CSAPI::fillManagerCompanies() : " . $_SESSION['selected_company_id']);
		
		$selected_company_id = !empty($_SESSION['selected_company_id']) ? $_SESSION['selected_company_id'] :
			 (
			 !empty($post_data['cmb-main-company']) ? 
			 	$post_data['cmb-main-company'] : 
			 		(isset($manager_companies[0]) ? $manager_companies[0]['company_id'] : '')
			 );
		*/
		$selected_company_id = !empty($post_data['cmb-main-company']) ? $post_data['cmb-main-company'] : 
			(!empty($get_data['selected_company_id']) ? $get_data['selected_company_id'] : 
				(isset($manager_companies[0]) ? $manager_companies[0]['company_id'] : ''));
				
		$company = new Company($selected_company_id);
		$is_corporate = !empty($company->is_corporate);
		$self_service_type = $company->self_service_type;
		$consultant_of = $company->consultant_of;
		return array($manager_companies, $selected_company_id, $is_corporate, $self_service_type, $consultant_of);
	}
	
	
	
	
	public static function checkAndDownloadReport($post_data)
	{
		$csapi = new CSAPI();
		
		// error_log("post_data: " . var_export($post_data, true));
		$company_id = $post_data['hdn-company-id'];
		$campaign_ids = $post_data['campaignSelect'] == 'campaign' ? $post_data['cmb-individual-campaign']: '';
		$campaign_name = $post_data['campaignSelect'] == 'campaign' ? $post_data['hdn-campaign-name']: '';
		$company_obj = new Company($company_id);
		$company_name = $company_obj->display_name;
		
		if(!empty($post_data['hdn-manage-offers-corporate']))
		{
			$campaign_ids = Company::getCompanyConsultantCampaignIDs($company_id);
		}
		
		if($post_data['hdn-download-report'] == '1')
		{
			
			$company = array(
				'company_id' 			=> $company_obj->id,
				'company_name' 			=> $company_obj->display_name,
				'facebook_page_id' 		=> $company_obj->facebook_page_id,
				'default_coupon_image'	=> $company_obj->default_coupon_image,
			);
			
			$start_date	= !empty($post_data['start_date']) ? $post_data['start_date'] : Item::getMinimumCampaignDate($company_id); // date('Y-m-d');
			$end_date	= !empty($post_data['end_date']) ? $post_data['end_date'] : Common::getDBCurrentDate(null, null, '%Y-%m-%d'); // date('Y-m-d', strtotime($start_date. '-1 month UTC'));
			// 2.	Reorder/Swap the dates if necessary
			if(strtotime($start_date) > strtotime($end_date))
			{
				$tmp = $start_date;
				$start_date = $end_date;
				$end_date = $tmp;
			}
			$start_time = time();
			$report_type = $post_data['styleSelect'];
			
			if($report_type == 'Excel')
			{
				$analytics_data		= Stats::getCampaignAnalyticsXLSData($company, $start_date, $end_date, $campaign_ids);
				error_log("Time taken to generate Campaign Analytics Data: " . (time() - $start_time));
				// error_log("campaign_analytics_data: " . var_export($analytics_data, true));
			
				$demographics_data	= CSAPI::getDemographicsStatsReportData($company_id, $start_date, $end_date, $campaign_ids);
				error_log("Time taken to generate Demographics Stats Data: " . (time() - $start_time));
				// error_log("demographics_data: " . var_export($demographics_data, true));
			
				//	5.	Render Report Data
				// $file_name = CSAPI::createXLSReport($analytics_data, $demographics_data, true);
				$file_name = Stats::createCampaignAnalyticsXLSSheet($analytics_data, true, $demographics_data);
				
				error_log("Time taken to generate the report: " . (time() - $start_time));

				exit();
			}
			else if($report_type == 'PDF')
			{
				
				// define('FPDF_FONTPATH', dirname(__DIR__) . '/fonts');
				define('FPDF_FONTPATH', dirname(__DIR__) . '/sdks/fpdf17/font');
				require_once(dirname(__DIR__) . '/sdks/fpdf17/fpdf.php');
				require_once(dirname(__DIR__) . '/sdks/tcpdf/config/lang/eng.php');
				require_once(dirname(__DIR__) . '/sdks/FPDI-1.4.2/fpdi.php');
				
				
				//	1.	Create the PDF object
				$pdf = new FPDI();
				
				//	2.	Output filename
				$pdf_filename = "pdf_report_" . time() . ".pdf";


				//	3.	Set background template on which report will be based
				$bg_template = dirname(__DIR__) . '/images/downloaded/company_report_template_opt.pdf';
				$report_title = strtoupper($company_name . ' - COMPANY REPORT');
				if(!empty($campaign_ids))
				{
					$bg_template = dirname(__DIR__) . '/images/downloaded/campaign_report_template_opt.pdf';
					$report_title = strtoupper($campaign_name . ' - CAMPAIGN REPORT');
				}
					
				$pdf->addPage();
				// $bg_file = dirname(__DIR__) . '/classes/pdf_export/analyticspdf_page1.pdf';
				$pagecount = $pdf->setSourceFile($bg_template);
				$tplidx = $pdf->importPage(1, '/MediaBox');
				$pdf->useTemplate($tplidx, 0, 0, 0, 0, true);
				
				
				// $filename = dirname(__DIR__) . '/images/downloaded/'. $pdf_filename;
				
				//	4.	Company Name and selected Campaign Name (if any)
				
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(255, 255, 255);
				$pdf->SetXY(5, 5);
				$pdf->Write(5, $report_title);
				$campaign_name = "";
				
				
				
				//	5.	Date Range
				$pdf->SetFont('Arial','',12);
				$pdf->SetXY(150, 5);
				if(!empty($start_date) && !empty($end_date))
					$str_start_end_date = $start_date . ' - ' . $end_date;
				else if(!empty($start_date))
					$str_start_end_date = "From " . $start_date;
				else if(!empty($end_date))
					$str_start_end_date = "Till " . $start_date;
					
				$pdf->Write(5, $str_start_end_date);
				
				$tmp_params = array(
					'companyId' => $company_id,
					'campaignId' => $campaign_ids,
					'startDate' => $start_date,
					'endDate' => $end_date
				);
				
				$params = $tmp_params;
				
				
				//	6.	View to Claim Ratio
				$center_left = 52;
				$center_right = 155;
				
				$company_views		= $csapi->getCompanyViews($params);
				$company_claims		= $csapi->getCompanyClaims($params);
				$view_to_claim_ratio = !empty($company_views) ? Round(($company_claims / $company_views) * 100, 0) : 0.00;
				
				$pdf->SetFont('Arial','B', 26);
				$pdf->SetTextColor(0, 0, 0);
				
				$w = $pdf->GetStringWidth($view_to_claim_ratio . '%');
				// error_log("string width: " . $w);
				$pdf->SetXY($center_left - $w / 2, 40);
				$pdf->Write(5, $view_to_claim_ratio . '%');
				
				
				//	7.	Total Unique Emails
				$total_unique_emails = $csapi->getNumUniqueClaimants($params);
				$w = $pdf->GetStringWidth($total_unique_emails);
				$pdf->SetXY($center_right - $w / 2, 40);
				$pdf->Write(5, $total_unique_emails);
				
				$str_campaign_ids = implode('-', explode(',', $campaign_ids));
				$img_path = dirname(__DIR__) . '/images/downloaded/';
				
				$arr_chart_imgs = array();
				
				//	8.	Activity Heatmap - Views
				$params['activityType'] = 'view';
				$img = CSAPI::createPDFChartImage('campaign_activity_day_hour', $params);
				$arr_chart_imgs[] = $img;
				$pdf->Image($img, 5, 80, 100, 75);
				
				
				//	9.	Activity Heatmap - Claims
				$params['activityType'] = 'claim';
				$img = CSAPI::createPDFChartImage('campaign_activity_day_hour', $params);
				$arr_chart_imgs[] = $img;
				$pdf->Image($img, 110, 80, 100, 75);
				
				
				
				//	10.	Traffic from Facebook (Hits)
				$facebook_traffic = 0;
				
				
				//	11.	Traffic from CU.PN (Hits)
				$cupn_traffic = 0;
				
				$params['activityType'] = 'view';
				$response = $csapi->getCumulativeCampaignSources($params);
				
				foreach($response['graph_data'] as $i => $data)
				{
					if($data['name'] == 'Facebook')
						$facebook_traffic = $data['y'];
					else if($data['name'] == 'CUPN')
						$cupn_traffic = $data['y'];
				}
				
				$w = $pdf->GetStringWidth($facebook_traffic);
				$pdf->SetXY($center_left - $w / 2, 170);
				$pdf->Write(5, $facebook_traffic);

				
				$w = $pdf->GetStringWidth($cupn_traffic);
				$pdf->SetXY($center_right - $w / 2, 170);
				$pdf->Write(5, $cupn_traffic);
				
				$params = $tmp_params;
				//	12.	Bar Chart	Age/Gender Breakdown
				$img = CSAPI::createPDFChartImage('entrant_age_gender', $params);
				$arr_chart_imgs[] = $img;
				$pdf->Image($img, 5, 200, 100, 75);
				
				
				
				
				
				//	13.	Pie Chart	Relationship Status Breakdown
				$img = CSAPI::createPDFChartImage('entrant_relationship_status', $params);
				$arr_chart_imgs[] = $img;
				$pdf->Image($img, 100, 200, 110, 75);
				
				
				$pdf->addPage();
				// $bg_file = dirname(__DIR__) . '/classes/pdf_export/analyticspdf_page1.pdf';
				$tplidx = $pdf->importPage(2, '/MediaBox');
				$pdf->useTemplate($tplidx, 0, 0, 0, 0, true);
				
				//	14.	Popular Likes and Interests
				$params['limit'] = 10;
				$params['sort'] = 'entrants';
				$params['sortOrder'] = 'desc';
				// $popular_likes_and_interests = $csapi->getCampaignInterests($params);
				$popular_likes_and_interests = $csapi->getDashboardCampaignInterests($params);
				$pdf->SetFont('Arial','', 9);
				$pdf->SetTextColor(75, 75, 75);
				$row = 39;
				$pdf->SetY($row);
				foreach($popular_likes_and_interests['graph_data'] as $i => $interest)
				{
					$pdf->SetX(10);
					$pdf->Write(2, $interest['interest_name']);
					$pdf->SetX(110);
					$pdf->Write(2, $interest['entrants']);
					$pdf->SetX(175);
					$pdf->Write(2, $interest['enrant_percent_of_total']);
					
					$row += 7.5;
					$pdf->SetY($row);
					
				}
				// error_log("popular_likes_and_interests: " . var_export($popular_likes_and_interests, true));

				
				//	15.	User Locations DMA based
				$params = $tmp_params;
				$params['countryCode'] = 'US';
				$img = CSAPI::createPDFChartImage('entrant_locations', $params);
				$arr_chart_imgs[] = $img;
				$pdf->Image($img, 10, 128, 180, 125);


				// $html = "<h5 style='font-size: small; font-weight: bold;'>Some styled HTML Content 2</h5>";
				// $pdf->writeHTMLCell(0, 0, 0, 0, $html);
				$pdf->Output($pdf_filename, 'D'); // D for Download, F for saving directly, default option renders directly to browser
				
				foreach($arr_chart_imgs as $img)
				{
					if(file_exists($img))
					{
						unlink($img);
					}
				}
				
			}

		}
	}
	
	public static function createPDFChartImage($graph_type, $params, $uploadToS3 = false, $response = null)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		
		$str_campaign_ids = implode('-', explode(',', $campaign_ids));
		
		$dir_name = "/phantomJS";
		
		require_once(dirname(__DIR__) . "/includes/app_config.php");
		require_once(dirname(__DIR__) . '/includes/UUID.php');
		
		global $phantomjs;
		$rnd_number = md5(UUID::v4()  . uniqid());
		if($phantomjs == 'phantomJSLinux')
		{
			// $js_binary = dirname(__DIR__) . $dir_name . "/" . $phantomjs;
            $js_binary = "/usr/local/bin/phantomjs";
		}
		else
		{
			$js_binary = dirname(__DIR__) . $dir_name . "/" . $phantomjs;
		}
			
		$hc_js_script = dirname(__DIR__) . $dir_name . "/js/highcharts-convert.js";
		// $hc_config_obj_part = dirname(__DIR__) . $dir_name . "/tmp/highcharts_config" . $rnd_number . ".js";
		$hc_config_obj_part = dirname(__DIR__) . "/images/downloaded/highcharts_config" . $rnd_number . ".js";
		$output_img_filename = $graph_type . "_" . $company_id . "_" . $str_campaign_ids . "_" . $start_date . "_" . $end_date . "_" . $params['activityType'] . "_" . $rnd_number . ".jpg";
		// $output_image = dirname(__DIR__) . $dir_name . "/img/" . $output_img_filename;
		$output_image = dirname(__DIR__) . "/images/downloaded/" . $output_img_filename;
		$chart_or_map = "Chart";
		// if(!file_exists($output_image))
		{
		
			$graph_names = array(
				'entrant_relationship_status' => 'Relationship Status Weightage',
				'app_permissions' => 'App Permissions Weightage',
			);
		
			$csapi = new CSAPI();
			switch($graph_type)
			{
				case 'campaign_activity_day_hour':
					$highcharts_config = array (
					  'chart' => 
					  array (
						'type' => 'heatmap',
						'marginTop' => 0,
						'marginBottom' => 40,
					  ),
					  'title' => 
					  array (
						'text' => '',
					  ),
					  'subtitle' => 
					  array (
						'text' => '',
					  ),
					  'xAxis' => 
					  array (
						'categories' => 
						array (
						),
					  ),
					  'yAxis' => 
					  array (
						'categories' => 
						array (
						),
						'title' => NULL,
					  ),
					  'colorAxis' => 
					  array (
						'min' => 1,
						'minColor' => '#FFFFFF',
						// 'maxColor' => '#0000AA',
						'max' => 5000,
					  ),
					  'legend' => 
					  array (
						'align' => 'right',
						'layout' => 'vertical',
						'margin' => 0,
						'verticalAlign' => 'top',
						'y' => 25,
						'symbolHeight' => 320,
					  ),
					  'credits' => 
					  array (
						'enabled' => false,
					  ),
					  'exporting' => 
					  array (
						'enabled' => false,
					  ),
					  'series' => 
					  array (
						0 => 
						array (
						  'name' => 'Activity',
						  'borderWidth' => 1,
						  'data' => 
						  array (
						  ),
						  'dataLabels' => 
						  array (
							'enabled' => false,
							'color' => 'black',
							'style' => 
							array (
							  'textShadow' => 'none',
							  'HcTextStroke' => NULL,
							),
						  ),
						),
					  ),
					);
					$response = $csapi->getCampaignActivityDayHour($params);
					// error_log("response in createPDFChartImage(): " . var_export($response, true));
					$highcharts_config['series'][0]['data'] = $response['graph_data']['data'];
					$highcharts_config['xAxis']['categories'] = $response['graph_data']['xAxis']['categories'];
					$highcharts_config['yAxis']['categories'] = $response['graph_data']['yAxis']['categories'];
					$my_max_value = 5;
					$num_elements = count($highcharts_config['series'][0]['data']);
					for ($i=0; $i < $num_elements; $i++) {
						$my_data_value = $highcharts_config['series'][0]['data'][$i][2];
						if ($my_data_value > $my_max_value) {
							$my_max_value= $my_data_value;
						}
					}	
					$highcharts_config['colorAxis']['max']= $my_max_value * 0.70;
					break;
				
				case 'entrant_age_gender':
					$highcharts_config = array (
					  'chart' => 
					  array (
						'type' => 'bar',
					  ),
					  'title' => 
					  array (
						'text' => '',
					  ),
					  'subtitle' => 
					  array (
						'text' => '',
					  ),
					  'xAxis' => 
					  array (
						0 => 
						array (
						  'categories' => 
						  array (
						  ),
						  'reversed' => false,
						  'labels' => 
						  array (
							'step' => 1,
						  ),
						),
						1 => 
						array (
						  'opposite' => true,
						  'reversed' => false,
						  'categories' => 
						  array (
						  ),
						  'linkedTo' => 0,
						  'labels' => 
						  array (
							'step' => 1,
						  ),
						),
					  ),
					  'yAxis' => 
					  array (
						'title' => 
						array (
						  'text' => NULL,
						),
					  ),
					  'plotOptions' => 
					  array (
						'series' => 
						array (
						  'stacking' => 'normal',
						),
					  ),
					  'credits' => 
					  array (
						'enabled' => false,
					  ),
					  'exporting' => 
					  array (
						'enabled' => false,
					  ),
					  'series' => 
					  array (
						0 => 
						array (
						  'name' => 'Male',
						  'data' => 
						  array (
						  ),
						),
						1 => 
						array (
						  'name' => 'Female',
						  'data' => 
						  array (
						  ),
						),
					  ),
					);
					if(empty($response))
						$response = $csapi->getEntrantAgeGender($params);
					$highcharts_config['series'] = $response['graph_data']['series'];
					$highcharts_config['xAxis'][0]['categories'] = $response['graph_data']['categories'];
					$highcharts_config['xAxis'][1]['categories'] = $response['graph_data']['categories'];
					// error_log("response returned for age gender: " . var_export($response, true));
					$total_0 = abs(array_sum($highcharts_config['series'][0]['data']));
					$total_1 = abs(array_sum($highcharts_config['series'][1]['data']));
					$total_sum = $total_0 + $total_1;
					$percent_0 = round(($total_0 / $total_sum) * 100, 2);
					$percent_1 = round(($total_1 / $total_sum) * 100, 2);
					$highcharts_config['series'][0]['name'] .= " - " . $percent_0 . "%";
					$highcharts_config['series'][1]['name'] .= " - " . $percent_1 . "%";
					break;
				
				case 'entrant_relationship_status':
				case 'app_permissions':
					$highcharts_config = array (
					  'chart' => 
					  array (
						'plotBackgroundColor' => NULL,
						'plotBorderWidth' => 0,
						'plotShadow' => false,
					  ),
					  'title' => 
					  array (
						'text' => '',
					  ),
					  'tooltip' => 
					  array (
						'pointFormat' =>  '{series.name}: <b>{point.percentage:.0f}</b>',
					  ),
					  'plotOptions' => 
					  array (
						'pie' => 
						array (
						  'allowPointSelect' => true,
						  'cursor' => 'pointer',
						  'dataLabels' => 
						  array (
							'enabled' => true,
							'format' => '<b>{point.name}</b>: {point.percentage:.0f}', // <b>{point.name}</b>: {point.y:.0f},
							'style' => 
							array (
							  'color' => 'black',
							),
						  ),
						),
					  ),
					  'credits' => 
					  array (
						'enabled' => false,
					  ),
					  'exporting' => 
					  array (
						'enabled' => false,
					  ),
					  'series' => 
					  array (
						0 => 
						array (
						  'type' => 'pie',
						  'name' => $graph_names[$graph_type],
						  'data' => 
						  array (
						  ),
						),
					  ),
					);
					
					if($graph_type == 'entrant_relationship_status')
					{
						if(empty($response))
							$response = $csapi->getEntrantRelationshipStatus($params);
						$highcharts_config['series'][0]['data'] = $response['graph_data']['series']['data'];
					}
					else if($graph_type == 'app_permissions')
					{
						$params['graphType'] = 'permissions';
						if(empty($response))
							$response = $csapi->getCumulativeCampaignActivity($params);
						$highcharts_config['series'][0]['data'] = $response['graph_data'];
					}
					
					break;
				
				case 'weekly_campaign_activity':
					if(empty($response))
						$response = $csapi->getWeeklyCampaignActivity($params);
						
					$highcharts_config = array (
						'chart' => array (
							'type' => 'column'
						  ),
					  	'title' => array (
							'text' => "Weekly Activity Summary"
						),
						'xAxis' => array(
							'categories' => $response['categories'],
							'crosshair' => true
						),
						'yAxis' => array(
							'min' => 0,
							'title' => array(
								'text' => 'Activity'
							),
						),
						'plotOptions' => array (
							'column' => array (
								'pointPadding' => 0.2,
								'borderWidth' => 0,
								'dataLabels' => array (
									'enabled' => true,
								  	'style' => array('fontSize' => '8px', 'fontWeight' => 'normal')
								)
							)
						),
						'series' => $response['series']
					);
					break;
					
				case 'entrant_locations':
					$chart_or_map = "Map";
					require_once(dirname(__DIR__) . '/includes/highmaps-us-dma-data.php');
					global $us_dma_data_json;
					$map_data = json_decode($us_dma_data_json, true);
					
					$highcharts_config = array (
						'chart' => array(
							'marginLeft' => 0,
						),
					  'title' => 
					  array (
						'text' => '',
					  ),
					  'subtitle' => 
					  array (
						'text' => '',
					  ),
					  'legend' => array(
					  		'enabled' => true,
							'layout' => 'horizontal',
							'borderWidth' => 0,
							// 'backgroundColor' => 'rgba(255,255,255,0.85)',
							'floating' => true,
							'verticalAlign' => 'bottom',
							'align' => 'center',
							'marginRight' => 0,
							'marginTop' => 10
						),
					  'mapNavigation' => 
					  array (
						'enabled' => false,
						'buttonOptions' => 
						array (
						  'verticalAlign' => 'bottom',
						),
					  ),
					  'colorAxis' => 
					  array (
						'min' => 0,
						'minColor' => '#FFFFFF',
						// 'maxColor' => '#0000AA',
						'max' => 5,
					  ),
					  'credits' => 
					  array (
						'enabled' => false,
					  ),
					  'exporting' => 
					  array (
						'enabled' => false,
					  ),
					  'series' => 
					  array (
						0 => 
						array (
						  'data' => 
						  array (
						  ),
						  'mapData' => $map_data,
						  'joinBy' => array (0 => 'code', 1 => 'code'),
						  'name' => 'Campaign Entrants',
						  'states' => 
						  array (
							'hover' => 
							array (
							  'color' => '#BADA55',
							),
						  ),
						  'dataLabels' => 
						  array (
							'enabled' => true,
			                'format' => '{point.name}'
						  ),
						),
					  ),
					);
					if(empty($response))
						$response = $csapi->getEntrantLocations($params);
					// error_log("response in createPDFChartImage(): " . var_export($response, true));
					$highcharts_config['series'][0]['data'] = $response['graph_data'];
					
					$my_max_value = 5;
					$num_elements = count($highcharts_config['series'][0]['data']);
					for ($i=0; $i < $num_elements; $i++) {
						$my_data_value = $highcharts_config['series'][0]['data'][$i]['value'];
						if ($my_data_value > $my_max_value) {
							$my_max_value= $my_data_value;
						}
					}	
					$highcharts_config['colorAxis']['max']= $my_max_value * 0.70;
					break;
			}
			$json_hc_config = Common::json_format(json_encode($highcharts_config));
			// $json_hc_config = json_encode($highcharts_config);
			$file = fopen($hc_config_obj_part,"w");
			fwrite($file, $json_hc_config);
			fclose($file);
		
		
			$shell_command = $js_binary . ' ' . $hc_js_script . " -infile " . $hc_config_obj_part . " -type jpg -width 600 -constr " . $chart_or_map . " -outfile " . $output_image;

			// error_log("shell_command in CSAPI::createPDFChartImage(): " . $shell_command);
			$response = shell_exec($shell_command);
			
			if(file_exists($hc_config_obj_part))
			{
				unlink($hc_config_obj_part);
			}
			
			if($uploadToS3)
			{
				require_once(dirname(__DIR__) . "/classes/CoupsmartS3.class.php");
				$CS3 = new CoupsmartS3();
				// connect to S3 cloud
				$CS3->s3_connect();
				$bucket = "uploads.coupsmart.com";
				$upload_result = $CS3->add_image_file($output_image, $bucket);
				error_log("upload_result: " . var_export($upload_result, true));
				if($upload_result)
				{
					// If Upload to s3 was successful, delete file from server disk
					unlink($output_image);
				}
			}
		}
		return $output_image;
	}
	
	
	
	
	
	public static function createXLSReport($campaign_analytics_data, $report_data, $show_in_browser = false)
	{
	
		
		
		///////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////	DEFINING REPORT STYLES	///////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////
		
		$style = array();
		$style['column_header'] = array(
			'font' => array(
				'bold'  => true,
				'color' => array('rgb' => 'FFFFFF'),
				'size'  => 12,
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => '3399FF')
			),
			'alignment' => array(
				// 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
			),
		);
		
		
		///////////////////////////////////////////////////////////////////////////////////
		////////////////////////	GENERATING FILE NAME FOR REPORT	///////////////////////
		///////////////////////////////////////////////////////////////////////////////////
		$company_name = $campaign_analytics_data['company_name'];
		$file_name		= $company_name . ' Report';
		$clean_name = preg_replace('/\s+/', '_', $file_name);
		$clean_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $file_name);

		$file_name = $clean_name . "_" . date('Y-m-d-H-i-s') . '.xls';
		
		
		
		
		//////////////////////////////////////////////////////////////////////////////////
		///////////////////////////	CREATING THE REPORT CONTENT	//////////////////////////
		//////////////////////////////////////////////////////////////////////////////////
		$objPHPExcel = new PHPExcel();
		
		
		//--------------	Set Overall Font Size and Family	------------
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
		

		//-------------	1.	SUMMARY	TAB	---------------
		//---------------------------------------------
		
		$active_sheet = $objPHPExcel->setActiveSheetIndex(0);
		$active_sheet->setTitle('Summary');
		
		
		// Set page margins
		$active_sheet->getPageMargins()->setTop(0.4);
		$active_sheet->getPageMargins()->setRight(0.4);
		$active_sheet->getPageMargins()->setLeft(0.4);
		$active_sheet->getPageMargins()->setBottom(0.4);
		
		// Set orientation to landscape
		$active_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
		
		// Set Font Globally
		$objPHPExcel->getDefaultStyle()->getFont()->setName('Lucida Grande')->setSize(10);
		
		// Tell 'em who the f I is
		$active_sheet->setCellValue('A1', 'CoupSmart LLC');
		$active_sheet->mergeCells('A1:B1');
		
		// Report Start Date
		$active_sheet->setCellValue('A2', "Report Start:");
		$active_sheet->setCellValue('B2', $campaign_analytics_data['start_date']);
		
		// Report End Date
		$active_sheet->setCellValue('A3', "Report End:");
		$active_sheet->setCellValue('B3', $campaign_analytics_data['end_date']);
		
		
		// Add a drawing to the worksheet
		if(!empty($campaign_analytics_data['company_img']))
		{
			// $objDrawing = new PHPExcel_Worksheet_Drawing();
			// $objDrawing->setName('Company Image');
			// $objDrawing->setDescription('PHPExcel logo');
			// $img = file_get_contents($campaign_analytics_data['company_img']);
			// $objDrawing->setImageResource($img);
			// $objDrawing->setCoordinates('A1');
			// $objDrawing->setWorksheet($active_sheet);
		}
		
		// Company Name
		list($row, $col) = array(1, 4);
		$report_title = $campaign_analytics_data['company_name'] . " Campaign Report";
		if(!empty($campaign_analytics_data['campaign_title']))
		{
			$report_title .= " (" . $campaign_analytics_data['campaign_title'] . ")";
		}
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');
		
		// Company Facebook Page
		list($row, $col) = array(2, 4);
		$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign_analytics_data['company_facebook_page']);
		
		// UTC disclaimer
		$active_sheet->setCellValue('D3', 'Dates and Times are in UTC');
		
		$active_sheet->mergeCells('D1:J1');
		$active_sheet->mergeCells('D2:J2');
		$active_sheet->mergeCells('D3:J3');
		
		///////////////////////// HEADERS ////////////////////////////////
		
		// Date
		list($row, $col) = array(6, 1);
		$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Date");
		
		// Fans
		list($row, $col) = array(6, 2);
		$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Fans*");
		
		// Campaign Headers
		$campaign_data = $campaign_analytics_data['data']['campaign_data'];
		$campaign_index = 0;
		
		$num_cols_per_campaign = 5;
		$col = 3;
		$start_col_name = Common::getXLSColIndex($col);
		foreach($campaign_data as $campaign_id => $campaign)
		{
			// Campaign Name
			$row = 5;
			$col_name = Common::getXLSColIndex($col);
			$active_sheet->setCellValue($col_name. $row, $campaign['campaign_name']);
			// Merging 
			$range = "$col_name$row:" . Common::getXLSColIndex($col + 4) . "$row";
			$active_sheet->mergeCells($range);
			// And Center Aligning
			$active_sheet->getStyle($range)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			
			$row = 6;
			// View
			$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Views");
			$col++;
			
			// Rank
			$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Rank");
			$col++;
			
			// Claim
			$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Claims");
			$col++;
			
			// Rank
			$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Rank");
			$col++;
			
			// V2C Rate
			$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "V2C Rate");
			$col++;
			
			// Adding Redemption Columns if present
			$has_redeems = array_sum($campaign['redeems']) > 0;
			if($has_redeems)
			{
				// Redeem
				$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Redeems");
				$col++;
			
				// Rank
				$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "Rank");
				$col++;
			}
			
			$campaign_index++;
		}
		$end_col_name = Common::getXLSColIndex($col);
		
		// Styling the Column headers
		$active_sheet->getStyle($start_col_name . "5:".$end_col_name."5")->getFont()->setBold(true);
		$active_sheet->getStyle("A5:".$end_col_name."7")->applyFromArray(
			array(
				'fill' => array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('rgb' => 'CCCCFF')
				),
				'borders' => array(
					'outline' => array(
						'style' => PHPExcel_Style_Border::BORDER_THICK,
					),
				),
			)
		);
		
		////////////////////////////////////// DATA //////////////////////////////
		$data = $campaign_analytics_data['data'];
		$dates = $data['dates'];
		$row = 8;
		$begin_row = $row;
		$total_views = 0;
		$total_claims = 0;
		
		$activity_gap = 0;
		
		foreach($dates as $i => $date)
		{
			// Flag to indicate that no activity happened that day
			$views_claims_redeems_per_day = 0;
			foreach($campaign_data as $campaign_id => $campaign)
				$views_claims_redeems_per_day += ($campaign['views'][$i] + $campaign['claims'][$i] + $campaign['redeems'][$i]);
				
			if($views_claims_redeems_per_day > 0)
			{
				if($activity_gap > 2)
					$row++;
				$activity_gap = 0;
			}
			else
			{
				$activity_gap++;
			}
		
			// Dates
			$active_sheet->setCellValue(Common::getXLSColIndex(1). $row, date('Y-m-d', $date));
			
			// Fans
			$active_sheet->setCellValue(Common::getXLSColIndex(2). $row, $data['fans'][$i]);
			
			
			// Campaign data
			$campaign_index = 0;
			$col = 3;
			foreach($campaign_data as $campaign_id => $campaign)
			{
				// Views
				$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign['views'][$i]);
				$total_views += $campaign['views'][$i];
				$col++;
				
				// Rank
				$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign['views_rank'][$i]);
				$col++;
				
				// Claims
				$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign['claims'][$i]);
				$total_claims += $campaign['claims'][$i];
				$col++;
				
				// Rank
				$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign['claims_rank'][$i]);
				$col++;
				
				// V2C Rate
				// $v2c_rate_formula = "=" . Common::getXLSColIndex($campaign_index * 5 + 5). $row . "/" . Common::getXLSColIndex($campaign_index * 5 + 3). $row;
				$v2c_rate_formula = "=" . Common::getXLSColIndex($col - 2). $row . "/" . Common::getXLSColIndex($col - 4). $row;
				if($campaign['views'][$i] > 0)
					$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $v2c_rate_formula);
				$col++;
				
				// Adding Redemption Columns if present
				$has_redeems = array_sum($campaign['redeems']) > 0;
				if($has_redeems)
				{
					// Redeem
					$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign['redeems'][$i]);
					$col++;
			
					// Rank
					$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, $campaign['redeems_rank'][$i]);
					$col++;
				}
				
				$campaign_index++;
				
				
			}
			$row++;
		}
		
		$end_row = $row - 1;
		
		
		
		// Total
		$active_sheet->setCellValue(Common::getXLSColIndex(1). $row, 'Total');
		
		// Setting Font to bold for Totals
		$active_sheet->getStyle("A". $row . ":".$end_col_name.$row)->getFont()->setBold(true);
		
		$campaign_index = 0;
		$campaign_views_col_names = array();
		$campaign_claims_col_names = array();
		
		$format_percent = array(
			'numberformat' => array(
				'code' => '0.00%',
			),
		);
		
		$borders_thick = array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
				),
			)
		);
		
		$borders_thin = array(
			'borders' => array(
				'left' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				),
			)
		);
		
		$active_sheet->getStyle("A" . $row . ":" . $end_col_name . $row)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		
		$col = 3;
		$arr_grand_total_views_formula	= array();
		$arr_grand_total_claims_formula	= array();

		foreach($campaign_data as $campaign_id => $campaign)
		{
			// Views
			$col_name_views = Common::getXLSColIndex($col);
			$campaign_views_col_names[] = $col_name_views;
			$total_views_formula = "=sum($col_name_views$begin_row:$col_name_views$end_row)";
			$active_sheet->setCellValue($col_name_views. $row, $total_views_formula);
			$arr_grand_total_views_formula[] = $col_name_views. $row;
			
			// Placing borders
			$active_sheet->getStyle($col_name_views . "5:" . $col_name_views . $row)->applyFromArray($borders_thick);
			// Filling in Background Color
			$active_sheet->getStyle($col_name_views . "8:" . $col_name_views . $row)->applyFromArray(
				array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array('rgb' => 'afe563')
					),
				)
			);
			$col += 2;
			
			// Claims
			$col_name_claims = Common::getXLSColIndex($col);
			$campaign_claims_col_names[] = $col_name_claims;
			$total_claims_formula = "=sum($col_name_claims$begin_row:$col_name_claims$end_row)";
			$active_sheet->setCellValue($col_name_claims. $row, $total_claims_formula);
			$arr_grand_total_claims_formula[] = $col_name_claims. $row;
			// Placing borders
			$active_sheet->getStyle($col_name_claims . "6:" . $col_name_claims . $row)->applyFromArray($borders_thin);
			// Filling in Background Color
			$active_sheet->getStyle($col_name_claims . "8:" . $col_name_claims . $row)->applyFromArray(
				array(
					'fill' => array(
						'type' => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array('rgb' => '3399ff')
					),
				)
			);
			$col+= 2;
			
			// V2C Rate
			$v2c_rate_formula = "=" . $col_name_claims. $row . "/" . $col_name_views. $row;
			$col_name = Common::getXLSColIndex($col);
			$active_sheet->setCellValue($col_name. $row, $v2c_rate_formula);
			
			// Placing borders
			$active_sheet->getStyle($col_name . "5:" . $col_name . $row)->applyFromArray($borders_thin);
			
			// Formatting VC2 Rate to Percentage
			$active_sheet->getStyle($col_name . "7:" . $col_name . $row)->applyFromArray($format_percent);
			$col++;
			
			// Adding Redemption Columns if present
			$has_redeems = array_sum($campaign['redeems']) > 0;
			if($has_redeems)
			{
				// Claims
				$col_name_redeems = Common::getXLSColIndex($col);
				$campaign_claims_col_names[] = $col_name_redeems;
				$total_claims_formula = "=sum($col_name_redeems$begin_row:$col_name_redeems$end_row)";
				$active_sheet->setCellValue($col_name_redeems. $row, $total_claims_formula);
				// Placing borders
				$active_sheet->getStyle($col_name_redeems . "5:" . $col_name_redeems . $row)->applyFromArray($borders_thin);
				// Filling in Background Color
				$active_sheet->getStyle($col_name_redeems . "7:" . $col_name_redeems . $row)->applyFromArray(
					array(
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('rgb' => 'dedeff')
						),
					)
				);
				$col+= 2;
			}
			
			$campaign_index++;
		}
		
		
		///////////////////////////////////// COUPON STATS /////////////////////////////////////////////
		list($row, $col) = array($end_row + 3, 1);
		$active_sheet->setCellValue("A". $row, "COUPON STATS");
		
		// Total Views of Preview Page
		list($row, $col) = array($end_row + 4, 1);
		$active_sheet->setCellValue("A". $row, "Total Views of Preview Page");
		// $active_sheet->setCellValue("E". $row, $total_views);
		if(!empty($arr_grand_total_views_formula))
		{
			$active_sheet->setCellValue("E". $row, "=SUM(" . implode(',', $arr_grand_total_views_formula) . ")");
			$active_sheet->getCell("E". $row)->getCalculatedValue();
		}
		
		// Total Prints (Shares) Total
		list($row, $col) = array($end_row + 5, 1);
		$active_sheet->setCellValue("A". $row, "Total Claims (Shares) Total");
		// $active_sheet->setCellValue("E". $row, $total_claims);
		if(!empty($arr_grand_total_claims_formula))
		{
			$active_sheet->setCellValue("E". $row, "=SUM(" . implode(',', $arr_grand_total_claims_formula) . ")");
			$active_sheet->getCell("E". $row)->getCalculatedValue();
		}
		
		// Total Prints / Total Views
		list($row, $col) = array($end_row + 6, 1);
		$active_sheet->setCellValue("A". $row, "Total Claims/Views Total");
		// $active_sheet->setCellValue("E". $row, $total_views > 0 ? $total_claims / $total_views : 0);
		$active_sheet->setCellValue("E". $row, "=E" . ($row - 1) . "/E" . ($row - 2));
		$active_sheet->getCell("E". $row)->getCalculatedValue();
		$active_sheet->getStyle("E". $row .":E" . $row)->applyFromArray($format_percent);
		
		// Note
		list($row, $col) = array($end_row + 7, 1);
		$active_sheet->setCellValue(Common::getXLSColIndex($col). $row, "*A 15% View-to-Claim (V2C) rate is above average");
		$active_sheet->getStyle(Common::getXLSColIndex($col). $row)->getAlignment()->setWrapText(true);

		// Styling COUPON and FAN STATS
		$style_array = array(
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'color' => array('rgb' => 'CCCCCC')
			),
			'font' => array(
				'bold' => true,
				'underline' => PHPExcel_Style_Font::UNDERLINE_SINGLE
			)
		);
		$sel_range = "A" . ($end_row + 3) . ":E" . ($end_row + 3);
		$active_sheet->getStyle($sel_range)->applyFromArray(
			$style_array
		);
		$active_sheet->mergeCells($sel_range);
		
		$sel_range = "G" . ($end_row + 3) . ":L" . ($end_row + 3);
		$active_sheet->getStyle($sel_range)->applyFromArray(
			$style_array
		);


		///////////////////////////////////// FAN STATS /////////////////////////////////////////////
		list($row, $col) = array($end_row + 3, 10);
		$active_sheet->setCellValue("G". $row, "FAN STATS");
		
		// Total Fan Count Pre-Campaign
		list($row, $col) = array($end_row + 4, 10);
		$fan_count_pre_campaign = 1;
		$active_sheet->setCellValue("G". $row, "Total Fan Count Pre-Campaign");
		$active_sheet->setCellValue("L". $row, $campaign_analytics_data['fan_count_pre_campaign']);
		
		// Total Fan Count at End Report Date
		list($row, $col) = array($end_row + 5, 10);
		$active_sheet->setCellValue("G". $row, "Total Fan Count at End Report Date");
		$active_sheet->setCellValue("L". $row, $campaign_analytics_data['fan_count_end_report']);
		
		// Increase in Fan Count
		list($row, $col) = array($end_row + 6, 10);
		$fan_count_increase = $campaign_analytics_data['fan_count_end_report'] - $campaign_analytics_data['fan_count_pre_campaign'];
		$active_sheet->setCellValue("G". $row, "Increase in Fan Count");
		// $active_sheet->setCellValue("L". $row, $fan_count_increase);
		$active_sheet->setCellValue("L". $row, "=L" . ($row - 1) . "-L" . ($row - 2));
		$active_sheet->getCell("L". $row)->getCalculatedValue();
		
		// Growth Rate Percentage
		list($row, $col) = array($end_row + 7, 10);
		$growth_rate_percent = $campaign_analytics_data['fan_count_pre_campaign'] > 0 ? ($fan_count_increase / $campaign_analytics_data['fan_count_pre_campaign']) * 100 : 0;
		$active_sheet->setCellValue("G". $row, "Growth Rate Percentage");
		$active_sheet->getStyle('G' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		// $active_sheet->setCellValue("L". $row, number_format($growth_rate_percent, 2, '.', ',') . '%');
		$active_sheet->setCellValue("L". $row, "=L" . ($row - 1) . "/L" . ($row - 3));
		$active_sheet->getCell("L". $row)->getCalculatedValue();
		$active_sheet->getStyle("L". $row .":L" . $row)->applyFromArray($format_percent);
		$active_sheet->getStyle("L". $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		$active_sheet->getStyle('L' . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
		
		// Setting Column A's Width
		$active_sheet->getColumnDimension('A')->setWidth(15);
		
		
		
		//-------------	2.	CLAIMANTS SORTABLE	TAB	---------------
		//---------------------------------------------------------
		$objPHPExcel->createSheet(NULL, 1);
		$active_sheet = $objPHPExcel->setActiveSheetIndex(1);
		
		/***** Set Orientation ******/
		$active_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

		$row = 1;
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');		

		$row = 2;
		$active_sheet->setCellValue('A'.$row, 'Customer');
		$active_sheet->setCellValue('B'.$row, 'Email');
		$active_sheet->setCellValue('C'.$row, 'Location');
		$active_sheet->setCellValue('D'.$row, 'Age');
		$active_sheet->setCellValue('E'.$row, 'Date of Birth');
		$active_sheet->setCellValue('F'.$row, 'Gender');
		$active_sheet->setCellValue('G'.$row, 'Relationship');
		$active_sheet->setCellValue('H'.$row, 'Views');
		$active_sheet->setCellValue('I'.$row, 'Claims');
		$active_sheet->setCellValue('J'.$row, 'Redeems');
		$active_sheet->setCellValue('K'.$row, 'Shared');
		// $active_sheet->setCellValue('L'.$row, 'Interests');
		// $active_sheet->setCellValue('M'.$row, 'Page Likes');
		
		$row++;
		
		foreach($report_data['claimants_sortable'] as $user_id => $data)
		{
			// Name
			$active_sheet->setCellValue('A'.$row, $data['name']);
			
			// Email
			$active_sheet->setCellValue('B'.$row, $data['email']);
			
			// Location
			$active_sheet->setCellValue('C'.$row, $data['location']);
			
			// Age
			$active_sheet->setCellValue('D'.$row, $data['age']);
			
			// Date of Birth
			if(!empty($data['date_of_birth']))
			{
				$date_of_birth = $data['date_of_birth'];
				$dateTimeObject = new DateTime($date_of_birth);
				$excelDate = PHPExcel_Shared_Date::PHPToExcel($dateTimeObject);
				$active_sheet->setCellValue('E'.$row, $excelDate);
			}
			
			// Gender
			$active_sheet->setCellValue('F'.$row, $data['gender']);
			
			// Relationship
			$active_sheet->setCellValue('G'.$row, $data['relationship']);
			
			// Views
			$active_sheet->setCellValue('H'.$row, $data['num_views']);
			
			// Claims
			$active_sheet->setCellValue('I'.$row, $data['num_claims']);
			
			// Redeems
			$active_sheet->setCellValue('J'.$row, $data['num_redeems']);
			
			// Shared
			$active_sheet->setCellValue('K'.$row, $data['num_shares']);
			/*
			// Interests
			if(!empty($data['interests']))
			{
				// $str_interests = implode(', ', $data['interests']);
				$str_interests = "";
				foreach($data['interests'] as $index => $interest)
					$str_interests .= ($index + 1) . ") " . $interest . ". ";
				$active_sheet->setCellValue('L'.$row, $str_interests);

			}
			// Page Likes
			if(!empty($data['page_likes']))
			{
				// $str_page_likes = implode(', ', $data['page_likes']);
				$str_page_likes = "";
				// $start_time = array_sum(explode(" ", microtime()));
				foreach($data['page_likes'] as $like_index => $like)
					$str_page_likes .= ($like_index + 1) . ") " . $like . ". ";
				// error_log("time: " . (array_sum(explode(" ", microtime())) - $start_time));
				$active_sheet->setCellValue('M'.$row, $str_page_likes);
			}
			*/
			$row++;
		}
		$active_sheet->getStyle('A2:K2')->applyFromArray($style['column_header']);
		$active_sheet->getStyle('F2:F' . $row)->getNumberFormat()->setFormatCode('mm/dd/yyyy');
		// Setting Column Widths
		foreach(range('A', 'K') as $columnID) {
			$active_sheet->getColumnDimension($columnID)->setAutoSize(true);
		}
		
		$active_sheet->setTitle('Claimants Sortable');
		
		
		//-------------	3.	LOCATIONS	TAB	----------------------
		//---------------------------------------------------------
		$objPHPExcel->createSheet(NULL, 2);
		$active_sheet = $objPHPExcel->setActiveSheetIndex(2);
		$active_sheet->setTitle('Locations');
		
		$row = 1;
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');		
		
		$row = 2;
		$active_sheet->setCellValue('A'.$row, 'Facebook Location');
		$active_sheet->setCellValue('B'.$row, 'Number of Users');
		$row++;
		foreach($report_data['location'] as $location => $num_users)
		{
			// Location
			$active_sheet->setCellValue('A'.$row, $location);
			
			// Number of Users
			$active_sheet->setCellValue('B'.$row, $num_users);
			
			$row++;
		}
		$active_sheet->getStyle('A2:I2')->applyFromArray($style['column_header']);
		// Setting Column Widths
		foreach(range('A', 'B') as $columnID) {
			$active_sheet->getColumnDimension($columnID)->setAutoSize(true);
		}
		
		
		//------------------	4.	AGE GENDER	-------------------
		//---------------------------------------------------------
		$objPHPExcel->createSheet(NULL, 3);
		$active_sheet = $objPHPExcel->setActiveSheetIndex(3);
		$active_sheet->setTitle('Age-Gender');
		
		$row = 1;
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');		
		
		$row = 2;
		$men_women = array('Women', 'Men');
		foreach($men_women as $gender)
		{
			// $active_sheet->setCellValue('H'.$row, $gender);
			$active_sheet->setCellValue('A'.$row, $gender);
			$active_sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($style['column_header']);
			$row++;
		
			// $active_sheet->setCellValue('J'.$row, 'Ages');
			$active_sheet->setCellValue('B'.$row, 'Number');
			$active_sheet->setCellValue('C'.$row, 'Ages');
			$active_sheet->setCellValue('D'.$row, '%');
			$row++;
			
			$num_rows = count(array_values($report_data['age_gender'][$gender]));
			$start_row = $row;
			foreach($report_data['age_gender'][$gender] as $age_group => $num_users)
			{
				// Number of Users
				// $active_sheet->setCellValue('I'.$row, $num_users);
				$active_sheet->setCellValue('B'.$row, $num_users);
				//	Age Group
				// $active_sheet->setCellValue('J'.$row, $age_group);
				$active_sheet->setCellValue('C'.$row, $age_group);
				// Percetage
				// $formula = "=(I" . $row . "/I" . ($start_row + $num_rows + 1) . ")";
				$formula = "=(B" . $row . "/B" . ($start_row + $num_rows + 1) . ")";
				
				// $active_sheet->setCellValue("K" . $row, $formula);
				$active_sheet->setCellValue("D" . $row, $formula);
				// $active_sheet->getCell("K" . $row)->getCalculatedValue();
				$active_sheet->getCell("D" . $row)->getCalculatedValue();
				$row++;
			}
			$end_row = $row - 1;
		
			$row += 1;
			
			// $formula = "=sum(I" . $start_row . ":I" . $end_row . ")";
			// $active_sheet->setCellValue("I" . $row, $formula);
			// $active_sheet->getCell("I" . $row)->getCalculatedValue();
			
			$formula = "=sum(B" . $start_row . ":B" . $end_row . ")";
			$active_sheet->setCellValue("B" . $row, $formula);
			$active_sheet->getCell("B" . $row)->getCalculatedValue();
			
			$row += 2;
		}
		$active_sheet->getStyle("D2:D" . $row)->applyFromArray($format_percent);
		
		
		//------------------	5.	RELATIONSHIP	-------------------
		//---------------------------------------------------------
		$objPHPExcel->createSheet(NULL, 4);
		$active_sheet = $objPHPExcel->setActiveSheetIndex(4);
		$active_sheet->setTitle('Relationships');
		
		$row = 1;
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');		
		
		$row = 2;
		$men_women = array('Women', 'Men');
		foreach($men_women as $gender)
		{
			$active_sheet->setCellValue('A'.$row, $gender);
			$active_sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray($style['column_header']);
			$row++;
		
			$active_sheet->setCellValue('B'.$row, 'Number');
			$active_sheet->setCellValue('C'.$row, 'Status');
			$active_sheet->setCellValue('D'.$row, '%');
			$row++;
			
			$num_rows = count(array_values($report_data['relationship'][$gender]));
			$start_row = $row;
			foreach($report_data['relationship'][$gender] as $relationship => $num_users)
			{
				// Number of Users
				$active_sheet->setCellValue('B'.$row, $num_users);
				// Relationship
				$active_sheet->setCellValue('C'.$row, $relationship);
				// Percent
				$formula = "=(B" . $row . "/B" . ($start_row + $num_rows + 1) . ")";
				$active_sheet->setCellValue("D" . $row, $formula);
				$active_sheet->getCell("D" . $row)->getCalculatedValue();
				$row++;
			}
			$end_row = $row - 1;
		
			$row+=1;
			
			$formula = "=sum(B" . $start_row . ":B" . $end_row . ")";
			$active_sheet->setCellValue("B" . $row, $formula);
			$active_sheet->getCell("B" . $row)->getCalculatedValue();
			
			$row += 2;
		}	
		$active_sheet->getStyle("D2:D" . $row)->applyFromArray($format_percent);
		
		$next_sheet_index = 5;
		if(!empty($report_data['email_codes_data']))
		{
			//------------------	6.	EMAIL CODE CLAIMANTS	-------------------
			//---------------------------------------------------------
			$objPHPExcel->createSheet(NULL, 5);
			$active_sheet = $objPHPExcel->setActiveSheetIndex(5);
			$next_sheet_index = 6;
			$active_sheet->setTitle('Email Code Claimants');
			$row = 1;
			$active_sheet->setCellValue('A'.$row, "Email");
			$active_sheet->setCellValue('B'.$row, "Claims");
			$active_sheet->setCellValue('C'.$row, "Redeems");
			$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
			
			$row = 2;
			foreach($report_data['email_codes_data']['claimants'] as $email => $num_claims)
			{
				$num_redeems = isset($report_data['email_codes_data']['redeemers'][$email]) ? $report_data['email_codes_data']['redeemers'][$email] : 0;
				//	1.	Email
				$active_sheet->setCellValue('A'.$row, $email);
		
				//	2.	Claims
				$active_sheet->setCellValue('B'.$row, $num_claims);
				
				//	3.	Redeems
				$active_sheet->setCellValue('C'.$row, $num_redeems);
				
				$row++;
			}
			foreach(range('A', 'C') as $columnID) {
				$active_sheet->getColumnDimension($columnID)->setAutoSize(true);
			}
		}
		
		
		//------------------	7.	INTERESTS	-------------------
		//---------------------------------------------------------
		$objPHPExcel->createSheet(NULL, $next_sheet_index);
		$active_sheet = $objPHPExcel->setActiveSheetIndex($next_sheet_index);
		$active_sheet->setTitle('Interests');
		
		$row = 1;
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');		
		
		$row = 2;
		$active_sheet->setCellValue('A'.$row, 'Interest');
		$active_sheet->setCellValue('B'.$row, 'Number of Users');
		$active_sheet->setCellValue('C'.$row, 'Percent of Users');
		$row++;
		foreach($report_data['interests']['graph_data'] as $interest)
		{
			// Interest
			$active_sheet->setCellValue('A'.$row, $interest['interest_name']);
			
			// Number of Users
			$active_sheet->setCellValue('B'.$row, $interest['entrants']);
			
			// Percent of Users
			$active_sheet->setCellValue('C'.$row, $interest['enrant_percent_of_total']);
			
			$row++;
		}
		$active_sheet->getStyle('A2:I2')->applyFromArray($style['column_header']);
		// Setting Column Widths
		foreach(range('A', 'C') as $columnID) {
			$active_sheet->getColumnDimension($columnID)->setAutoSize(true);
		}
		
		
		/*
		//------------------	7.	LIKES	-----------------------
		//---------------------------------------------------------
		$objPHPExcel->createSheet(NULL, 6);
		$active_sheet = $objPHPExcel->setActiveSheetIndex(6);
		$active_sheet->setTitle('Likes');
		
		$row = 1;
		$active_sheet->getStyle('A1:Z1')->applyFromArray($style['column_header']);
		$active_sheet->setCellValue("A1", $report_title);
		$active_sheet->mergeCells('A1:L1');
		
		$row = 2;
		$active_sheet->setCellValue('A'.$row, 'Like');
		$active_sheet->setCellValue('B'.$row, 'Number of Users');
		$row++;
		foreach($report_data['likes'] as $like => $num_users)
		{
			// Location
			$active_sheet->setCellValue('A'.$row, $like);
			
			// Number of Users
			$active_sheet->setCellValue('B'.$row, $num_users);
			
			$row++;
		}
		$active_sheet->getStyle('A2:I2')->applyFromArray($style['column_header']);
		
		// Setting Column Widths
		foreach(range('A', 'B') as $columnID) {
			$active_sheet->getColumnDimension($columnID)->setAutoSize(true);
		}
		*/
		
		// Set the first sheet as the default
		$objPHPExcel->setActiveSheetIndex(0);
		
		
		
		//////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////	SAVE REPORT	//////////////////////////////////
		//////////////////////////////////////////////////////////////////////////////////
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		if(!$show_in_browser)
		{
			$objWriter->save($file_path . '/' . $file_name);
		}
		else
		{
			// Redirect output to a client’s web browser (Excel5)
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'. $file_name.'"');
			header('Cache-Control: max-age=0');
		
			$objWriter->save('php://output');
		}
	
	
		// Destroy the Excel Object once done
		unset($objWriter, $objPHPExcel);
		
		// And return the file name
		return $file_path . '/' . $file_name;
		
	}
	
	public function getTopInterests($params)
	{
		$response = array();
		
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$limit = !empty($params['limit']) ? $params['limit'] : 10;
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		
		// Get Top interests
		
		$timer_start = array_sum(explode(" ", microtime()));
		// $sql = "select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) and entrant_like_count >= '$min_group_size' group by interest_id order by sum(entrant_like_count) desc limit $limit";
		
		$sql = "select interest_id, entrant_like_count from
		(
		select interest_id, sum(entrant_like_count) as entrant_like_count from tmp_campaign_interest where campaign_id in ($campaign_ids) group by interest_id
		) as t
		order by entrant_like_count desc limit $limit";
		
		
		// error_log("SQL0 in CSAPI::getTopInterests(): " . $sql);
		$arr_campaign_interest_likes = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$interest_id = $row['interest_id'];
			$entrant_like_count = $row['entrant_like_count'] + 0;
			$arr_campaign_interest_likes[$interest_id] = $entrant_like_count;
		}
		
		// error_log("CSAPI::getTopInterests(): Time taken to get the campaign interests: " . (array_sum(explode(" ", microtime())) - $timer_start));
		$sql = "select fb_id, name, total_followers from tmp_like_interest_1 where fb_id in (" . implode(',', array_keys($arr_campaign_interest_likes)). ")";
		// error_log("SQL1 in CSAPI::getTopInterests(): " . $sql);
		$arr_top_interests = array();
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			// $entrant_count = $row['total_followers'];
			$interest = $row['name'];
			$interest_id = $row['fb_id'];
			$response[$interest_id] = $interest;
		}
		Database::mysqli_free_result($rs);
		return $response;
	}
	
	public function getTopDMARegions($params)
	{
		$response = array();
		
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$limit = !empty($params['limit']) ? $params['limit'] : 10;
		
		if(empty($campaign_ids))
		{
			$arr_campaign_ids = array(-1);
			$rs = Database::mysqli_query("select id as campaign_id from items where manufacturer_id = '$company_id'");
			while($row = Database::mysqli_fetch_assoc($rs))
				$arr_campaign_ids[] = $row['campaign_id'];
			$campaign_ids = implode(',', $arr_campaign_ids);
		}
		$arr_entrant_ids = array();
		$sql = "select entrant_id, dma.dma_region_code, dma.dma_region from (select entrant_id, sum(view_count + claim_count + redeem_count + share_count + refer_count + refer_redeem_count) as activity_count from tmp_entrant_campaign where campaign_id in ($campaign_ids) and entrant_id > 0 group by entrant_id) as t 
		inner join users u on t.entrant_id = u.id
		inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id
			where u.facebook_location_id > 0
		order by activity_count desc limit $limit";
		// error_log("SQL0 in getTopDMARegions(): " . $sql);
		$rs = Database::mysqli_query($sql);
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$dma_region_code = $row['dma_region_code'];
			$dma_region = $row['dma_region'];
			$response[$dma_region_code] = $dma_region;
		}
		Database::mysqli_free_result($rs);
		
		
		return $response;
	}
	
	public function getTopDMARegionsByAdvocacyScore($params)
	{
		$response = array();
		
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$limit = !empty($params['limit']) ? $params['limit'] : 20;
	
		$sql_campaign_ids = "";
		if(!empty($campaign_ids)) 
			$sql_campaign_ids = " and item_id in ($campaign_ids)";
		
		$sql = "select dma_region_code, dma_region, score from
		(
		select dma_code as dma_region_code, dma as dma_region, max(advocacy_score) as score
		from ssp_key_advocates
		where company_id = '$company_id'
		$sql_campaign_ids
		group by dma_code
		) as dma
		order by dma.score desc limit $limit";
		
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $row)
		{
			$dma_region_code = $row['dma_region_code'];
			$dma_region = $row['dma_region'];
			$response[$dma_region_code] = $dma_region;
		}
		return $response;
	}
	
	public function getNumUniqueClaimants($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		// i.	Getting user_ids of users who claimed a coupon
		
		$sql = "select count(distinct(ui.user_id)) as num_users
				from user_items ui
				inner join items i on ui.item_id = i.id
				where i.manufacturer_id = '$company_id'
				and ui.user_id > 0
				and (ui.date_claimed is not null)";
		if(!empty($start_date))
			$sql .= " and date(ui.date_claimed) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(ui.date_claimed) <= '$end_date'";
		if(!empty($arr_test_user_account_ids))
			$sql .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($campaign_ids))
			$sql .= " and ui.item_id in ($campaign_ids)";
			
		// $sql .=		" group by ui.user_id";
		$row = BasicDataObject::getDataRow($sql);
		$num_users = $row['num_users'];
		
		return $num_users;
	}
	
	public static function getDemographicsStatsReportData($company_id, $start_date, $end_date, $campaign_ids = '')
	{
		$arr_genders = array('M' => 'Men', 'F' => 'Women');
		$male_female = array('M' => 'Male', 'F' => 'Female');
		
		$report_data = array(
			'claimants_sortable'	=> array(),
			'location'				=> array(),
			'age_gender'			=> array(),
			'relationships'			=> array(),
			'interests'				=> array(),
			'likes'				=> array(),
		);
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		
		///////////////////////////////////	1.	CLAIMANTS SORTABLE DATA //////////////////////////////
		$start_time = time();
		// i.	Getting user_ids of users who viewed a coupon
		$arr_viewers = array();
		$sql = "select iv.user_id, count(iv.id) as num_views
				from items_views iv
				inner join items i on iv.items_id = i.id
				where i.manufacturer_id = '$company_id'
				and iv.user_id > 0";
		if(!empty($start_date))
			$sql .= " and date(iv.created) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(iv.created) <= '$end_date'";	
		if(!empty($arr_test_user_account_ids))
			$sql .= " and iv.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($campaign_ids))
			$sql .= " and iv.items_id in ($campaign_ids)";
			
		$sql .=	" group by iv.user_id";
		// error_log("SQL for viewers in Stats::getDemographicsStatsReportData(): " . $sql);
		$rs = Database::mysqli_query($sql);
		error_log("\tTime taken to retrieve views: " . (time() - $start_time));
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_viewers[$row['user_id']] = $row['num_views'];
		}
		
		// i.	Getting user_ids of users who claimed a coupon
		$arr_claimers = array();
		$sql = "select ui.user_id, count(ui.id) as num_claims
				from user_items ui
				inner join items i on ui.item_id = i.id
				where i.manufacturer_id = '$company_id'
				and ui.user_id > 0
				and (ui.date_claimed is not null)";
		if(!empty($start_date))
			$sql .= " and date(ui.date_claimed) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(ui.date_claimed) <= '$end_date'";
		if(!empty($arr_test_user_account_ids))
			$sql .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($campaign_ids))
			$sql .= " and ui.item_id in ($campaign_ids)";
			
		$sql .=		" group by ui.user_id";
		// error_log("SQL for claimants in Stats::getDemographicsStatsReportData(): " . $sql);
		$rs = Database::mysqli_query($sql);
		error_log("\tTime taken to retrieve claims: " . (time() - $start_time));
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_claimers[$row['user_id']] = $row['num_claims'];
		}
		
		// i.	Getting user_ids of users who redeemed a coupon
		$arr_redeemers = array();
		$sql = "select ui.user_id, count(ui.id) as num_redeems
				from user_items ui
				inner join items i on ui.item_id = i.id
				where i.manufacturer_id = '$company_id'
				and ui.user_id > 0
				and (ui.date_redeemed is not null)";
		if(!empty($start_date))
			$sql .= " and date(ui.date_redeemed) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(ui.date_redeemed) <= '$end_date'";
		if(!empty($arr_test_user_account_ids))
			$sql .= " and ui.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		if(!empty($campaign_ids))
			$sql .= " and ui.item_id in ($campaign_ids)";
					
		$sql .=		" group by ui.user_id";
		// error_log("SQL for redeems in Stats::getDemographicsStatsReportData(): " . $sql);
		$rs = Database::mysqli_query($sql);
		error_log("\tTime taken to retrieve redeems: " . (time() - $start_time));
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_redeemers[$row['user_id']] = $row['num_redeems'];
		}
		
		
		// ii.	Getting user_ids of users who shared a coupon
		$arr_sharers = array();
		$sql = "select u.id as user_id, count(r.id) as num_shares
			from referrals r
			inner join users u on r.sender_id = u.facebook_id
			where r.company_id = '$company_id'
			and r.app_name != 'sgs'
			and r.share_method != ''";
		if(!empty($start_date))
			$sql .= " and date(r.created) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(r.created) <= '$end_date'";
		if(!empty($arr_test_user_account_ids))
			$sql .= " and u.id not in (" . implode(',', $arr_test_user_account_ids). ")";	
		$sql .= " and r.sender_id > 0";
		if(!empty($campaign_ids))
			$sql .= " and r.item_shared in ($campaign_ids)";
		$sql .= " group by u.id";
		// error_log("SQL for users who share a coupon in Stats::getDemographicsStatsReportData(): " .$sql);
		$rs = Database::mysqli_query($sql);
		error_log("\tTime taken to retrieve shares: " . (time() - $start_time));
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_sharers[$row['user_id']] = $row['num_shares'];
		}
		
		
		// i.	Getting user_ids of users who claimed a email code
		$arr_email_code_claimers = array();
		$sql = "select u.id as user_id, cec.email, count(cec.id) as num_claims
				from customer_email_codes cec
				left join users u on cec.email = u.email
				where cec.company_id = '$company_id'
				and (cec.date_claimed is not null)";
		if(!empty($start_date))
			$sql .= " and date(cec.date_claimed) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(cec.date_claimed) <= '$end_date'";
		// if(!empty($arr_test_user_account_ids))
		//	$sql .= " and (u.id not in (" . implode(',', $arr_test_user_account_ids). ") or u.id is null)";
		
		if(!empty($campaign_ids))
			$sql .= " and cec.item_id in ($campaign_ids)";
			
		$sql .=		" group by cec.email";
		// error_log("SQL for claimants for email coupons in Stats::getDemographicsStatsReportData(): " . $sql);
		$rs = Database::mysqli_query($sql);
		error_log("\tTime taken to retrieve claims for email coupons: " . (time() - $start_time));
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_email_code_claimers[$row['email']] = $row['num_claims'];
		}
		
		
		// i.	Getting user_ids of users who redeemed a email code
		$arr_email_code_redeemers = array();
		$sql = "select u.id as user_id, cec.email, count(cec.id) as num_redeems
				from customer_email_codes cec
				left join users u on cec.email = u.email
				where cec.company_id = '$company_id'
				and (cec.date_redeemed is not null)";
		if(!empty($start_date))
			$sql .= " and date(cec.date_redeemed) >= '$start_date'";
		if(!empty($end_date))
			$sql .= " and date(cec.date_redeemed) <= '$end_date'";
		// if(!empty($arr_test_user_account_ids))
		//	$sql .= " and (u.id not in (" . implode(',', $arr_test_user_account_ids). ") or u.id is null)";
		
		if(!empty($campaign_ids))
			$sql .= " and cec.item_id in ($campaign_ids)";
			
		$sql .=		" group by cec.email";
		// error_log("SQL for redeemers for email coupons in Stats::getDemographicsStatsReportData(): " . $sql);
		$rs = Database::mysqli_query($sql);
		error_log("\tTime taken to retrieve redeems for email coupons: " . (time() - $start_time));
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$arr_email_code_redeemers[$row['email']] = $row['num_redeems'];
		}
		
		if(!empty($arr_email_code_claimers))
		{
			$report_data['email_codes_data']['claimants'] = $arr_email_code_claimers;
			$report_data['email_codes_data']['redeemers'] = $arr_email_code_redeemers;
		}
		
		
		// iii.	Getting user data
		// if(!empty($arr_viewers) || !empty($arr_claimers) || !empty($arr_sharers))
		if(!empty($arr_claimers))
		{
			$sql = "select u.id as user_id, u.email, concat(u.firstname, ' ', u.lastname) as `name`, u.facebook_location_name as `location`, u.date_of_birth, date_format(from_days(to_days(NOW())-to_days(u.date_of_birth)), '%Y')+0 as age, u.gender, u.relationship_status as relationship
			from users u";
			// $sql .= " where u.id in (" . implode(', ', array_unique(array_merge(array_keys($arr_viewers), array_keys($arr_claimers), array_keys($arr_sharers)))). ")";
			$sql .= " where u.id in (" . implode(', ', array_keys($arr_claimers)). ")";
			// error_log("SQL for getting User Info in Stats::getDemographicsStatsReportData(): " . $sql);
			
			$rs = Database::mysqli_query($sql);
			error_log("\tTime taken to retrieve users: " . (time() - $start_time));
			while($row = Database::mysqli_fetch_assoc($rs))
			{
				$user_id = $row['user_id'];
				$location = $row['location'];
				$relationship = $row['relationship'];
				$age = $row['age'];
				$gender = $row['gender'];
				$row['date_of_birth'] = $row['date_of_birth'] == '0000-00-00' ? '' : $row['date_of_birth'];
				
				$row['gender'] = $male_female[$gender];
				
				$row['num_views'] = !empty($arr_viewers[$user_id]) ? $arr_viewers[$user_id] : 0;
				$row['num_claims'] = !empty($arr_claimers[$user_id]) ? $arr_claimers[$user_id] : 0;
				$row['num_redeems'] = !empty($arr_redeemers[$user_id]) ? $arr_redeemers[$user_id] : 0;
				$row['num_shares'] = !empty($arr_sharers[$user_id]) ? $arr_sharers[$user_id] : 0;
				$report_data['claimants_sortable'][$user_id] = $row;
				
				if(!empty($arr_claimers[$user_id]))
				{
					if(!empty($location))
					{
						if(!isset($report_data['location'][$location]))
							$report_data['location'][$location] = 0;
						$report_data['location'][$location]++;
					}
				
					if(!empty($gender))
					{
						$gender = $arr_genders[strtoupper($gender)];
						
						if(!empty($relationship))
						{						
							if(!isset($report_data['relationship'][$gender][$relationship]))
								$report_data['relationship'][$gender][$relationship] = 0;
							$report_data['relationship'][$gender][$relationship]++;
						}
						
						if(!empty($age))
						{
							if($age > 0 && $age <= 12)
								$str_age = '0-12';
							else if($age >= 13 && $age <= 17)
								$str_age = '13-17';
							else if($age >= 18 && $age <= 24)
								$str_age = '18-24';
							else if($age >= 25 && $age <= 34)
								$str_age = '25-34';
							else if($age >= 35 && $age <= 44)
								$str_age = '35-44';
							else if($age >= 45 && $age <= 54)
								$str_age = '45-54';
							else if($age >= 55 && $age <= 64)
								$str_age = '55-64';
							else if($age >= 65)
								$str_age = '65+';
								
							if(!isset($report_data['age_gender'][$gender][$str_age]))
								$report_data['age_gender'][$gender][$str_age] = 0;
							$report_data['age_gender'][$gender][$str_age]++;
						}
					}
				}
			}
		}
		
		//////////////////////////////////////	LIKES AND INTERESTS //////////////
		$csapi = new CSAPI();
		$report_data['interests'] = $csapi->getDashboardCampaignInterests(array('companyId' => $company_id, 'action' => 'campaign_interests', 'limit' => 200));
		
		// error_log("report_data: " . var_export($report_data, true));

		return $report_data;
	}
	
	public function getCampaignActivityIntegrations($params)
	{
		// error_log("params in getCampaignActivity(): " . var_export($params, true));
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		//$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : 3600;
		$interval = !empty($params['timeIntervalSeconds']) ? $params['timeIntervalSeconds'] : (!empty($params['timeintervalseconds']) ? $params['timeintervalseconds'] : 3600 );
		//$in_the_last = !empty($params['inTheLast']) ? $params['inTheLast'] : 0;
		$in_the_last = !empty($params['inTheLast']) ? $params['inTheLast'] : (!empty($params['inthelast']) ? $params['inthelast'] :0 );
		$time_zone = !empty($params['timezone']) ? $params['timezone'] : '-0500';
		//$activity_type = !empty($params['activityType']) ? $params['activityType'] : 'all';
		$activity_type = !empty($params['activityType']) ? $params['activityType'] : (!empty($params['activitytype']) ? $params['activitytype'] :'all');
		$source = !empty($params['source']) ? $params['source'] : 'all';
		
		$integration_type = !empty($params['integrationType']) ? $params['integrationType'] : (!empty($params['integrationtype']) ? $params['integrationtype'] : 'et');
		
		if(empty($company_id))
		{
			$errors[] = array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		$str_date_sub_start = "";
		$str_date_sub_end = "";
		$str_time_zone = 'UTC';
		switch($time_zone)
		{
			case '-0500':
				$str_date_sub_start = "date_sub(";
				$str_date_sub_end = ", interval 5 hour)";
				$str_time_zone = 'EST';
				break;
		}
		
		
		
		//	1.	Calculating Interval specific variables
		switch($interval)
		{
			case 60:	//	Minute
				$mysql_date_format = "%m/%d/%Y\T%H:%i:00$time_zone";
				$php_date_format = "m/d/Y\TH:i:00$time_zone";
				$interval_type = 'minute';
				break;
	
			case 3600:	//	Hour
				$mysql_date_format = "%m/%d/%Y\T%H:00:00$time_zone";
				$php_date_format = "m/d/Y\TH:00:00$time_zone";
				$interval_type = 'hour';
				break;
	
			case 86400:	//	Day
				// $mysql_date_format = '%m/%d/%Y';
				// $php_date_format = 'm/d/Y';
				$mysql_date_format = "%m/%d/%Y\T00:00:00$time_zone";
				$php_date_format = "m/d/Y\T00:00:00$time_zone";
				$interval_type = 'day';
				break;
		}
		if(!empty($in_the_last))
		{
			$sql = "select " . $str_date_sub_start . "now()" . $str_date_sub_end . " as end_date, date_sub(" . $str_date_sub_start . "now()" . $str_date_sub_end . ", interval $in_the_last $interval_type) as start_date";
			// error_log("sql for finding start date and end date: " . $sql);
			$row = BasicDataObject::getDataRow($sql);
			$start_date = $row['start_date'];
			$end_date = $row['end_date'];
		}
		
		$arr_where_clause = array();
		$arr_where_clause_views = array();
		$arr_where_clause_claims = array();
		$arr_where_clause_shares = array();
		$arr_where_clause_redeems = array();
		
		$join_clause = "";
		if(empty($campaign_ids))
		{
 			$arr_where_clause_claims[] = "ui.company_id = '$company_id'";
 			$arr_where_clause_redeems[] = "ui.company_id = '$company_id'";
		}
		else
		{
 			$arr_where_clause_claims[] = "ui.item_id in (" . $campaign_ids . ")";
 			$arr_where_clause_redeems[] = "ui.item_id in (" . $campaign_ids . ")";
		}
		
		if(!empty($activity_type))
		{
			// $arr_where_clause[] = " ea.activity_type = '$activity_type'";
		}
		
		
		if(!empty($start_date) && !empty($end_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. " between '$start_date' and '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. " between '$start_date' and '$end_date'";

		}
		else if(!empty($start_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. " >= '$start_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. " >= '$start_date'";
		}
		else if(!empty($end_date))
		{
			$arr_where_clause_claims[] = " " . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. " <= '$end_date'";
			$arr_where_clause_redeems[] = " " . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. " <= '$end_date'";
		}
		
		
		// SQL for Claims
		$sql_claims = "select date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. ", '$mysql_date_format') as date, ui.date_claimed as sort_date, 'claim' as activity_type, count(id) as activity_count from customer_email_codes ui where ui.date_claimed is not null";
		if(!empty($arr_where_clause_claims))
			$sql_claims .= " and " . implode(" and ", $arr_where_clause_claims);
		$sql_claims .= " group by date_format(" . $str_date_sub_start . "ui.date_claimed" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		// SQL for redeems
		$sql_redeems = "select date_format(" . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. ", '$mysql_date_format') as date, ui.date_redeemed as sort_date, 'redeem' as activity_type, count(id) as activity_count from customer_email_codes ui where ui.date_redeemed is not null";
		if(!empty($arr_where_clause_redeems))
			$sql_redeems .= " and " .implode(" and ", $arr_where_clause_redeems);
		$sql_redeems .= " group by date_format(" . $str_date_sub_start . "ui.date_redeemed" . $str_date_sub_end. ", '$mysql_date_format'), activity_type";
		
		
		switch($activity_type)
		{
			
			case 'claim':
				$sql = $sql_claims;
				break;
				
			case 'redeem':
				$sql = $sql_redeems;
				break;
			
			case 'all':
				$sql = implode(' union all ', array($sql_claims, $sql_redeems));
				break;
			
		}
		$sql = "select t.* from ( 
		" . $sql . "
		) as t 
		order by t.sort_date";
		
		// error_log("SQL in CSAPI::getCampaignActivityInteractions(): " . $sql);
		
		$timer_start = array_sum(explode(" ", microtime()));
		$rs = Database::mysqli_query($sql);
		// error_log("CSAPI::getCampaignActivity(): Time taken to run the SQL: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		$response = array(
			'settings' => $params,
			'data' => array(),
			'graph_data' => array(),
			'totals' => array(),
		);
		
		$tmp_data = array();
		while($row = Database::mysqli_fetch_assoc($rs))
		{
			$activity_type = $row['activity_type'];
			$tmp_date = $row['date'];
			$activity_count = $row['activity_count'] + 0;
			$tmp_data[$activity_type][$tmp_date] = $activity_count;
			$response['totals'][$activity_type] += $activity_count;
		}
		// error_log("CSAPI::getCampaignActivity(): Time taken to iterate through the records: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		// Getting start date
		if(empty($start_date))
		{
			Database::mysqli_data_seek($rs, 0);
			$row = Database::mysqli_fetch_assoc($rs);
			$start_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($start_date);
			$start_date = date_format($obj_dt, $php_date_format);
		}
		
		// Getting End Date
		if(empty($end_date))
		{
			Database::mysqli_data_seek($rs, Database::mysqli_num_rows($rs) - 1);
			$row = Database::mysqli_fetch_assoc($rs);
			$end_date = $row['date'];
		}
		else
		{
			$obj_dt = new DateTime($end_date);
			$end_date = date_format($obj_dt, $php_date_format);
		}
		// error_log("tmp_data: " . var_export($tmp_data, true));
		
		// error_log("CSAPI::getCampaignActivity(): Time taken to set the start and end pointers: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
		$start_date_val = strtotime($start_date . " $str_time_zone");
		$end_date_val = strtotime($end_date . " $str_time_zone");
		
		// error_log("start_date: " . $start_date . ", start_date_val: " . $start_date_val . ", end_date: " . $end_date . ", start_date_val: " . $end_date_val);
		

		
		$time_interval = new DateInterval('PT' . $interval . 'S');
		$pointStart = $start_date_val * 1000;
		$pointInterval = $interval * 1000;
		$activity_titles = array('view' => 'Views', 'claim' => 'Claims', 'redeem' => 'Redeems', 'share' => 'Shares', 'referral' => 'Referrals');
		foreach($tmp_data as $activity_type => $activity_data)
		{
			$total_activity_count = !empty($response['totals'][$activity_type]) ? $response['totals'][$activity_type] : 0;
			$activity_name = $activity_titles[$activity_type] . " (Total: " . $total_activity_count . ")";
			// $response['data'][$activity_type] = array();
			$cumm_entrant_count = 0;
			$tmp_date = $start_date;
			$series_data = array(
				'data' => array(),
				'name' => $activity_name,
				'pointStart' => $pointStart,
				'pointInterval' => $pointInterval,
			);
			for($i = $start_date_val; $i <= $end_date_val; $i += $interval)
			{
				// $tmp_date = date($php_date_format, $i);
				if(isset($activity_data[$tmp_date]))
				{
					$cumm_entrant_count += $activity_data[$tmp_date];
				}
				// $response['data'][$activity_type][$tmp_date] = $cumm_entrant_count;
				$series_data['data'][] = !empty($activity_data[$tmp_date]) ? $activity_data[$tmp_date] + 0 : 0; //$cumm_entrant_count;
				$dt = DateTime::CreateFromFormat($php_date_format, $tmp_date);
				if(!$dt)
					error_log("failed to created date object! using format $php_date_format having date $tmp_date");
				$tmp_date = date_format($dt->add($time_interval), $php_date_format);
			}
			$response['graph_data'][] = $series_data;
		}
		
		// error_log("CSAPI::getCampaignActivity(): Time taken to set the final data: " . (array_sum(explode(" ", microtime())) - $timer_start));
		Database::mysqli_free_result($rs);

		if(!empty($errors))
			return $errors;
			
		return $response;
	}
	
	public function getUsersWithTopInterests($params)
	{
		$response = array();
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		$user_limit = !empty($params['userLimit']) ? $params['userLimit'] : (!empty($params['userlimit']) ? $params['userlimit'] : '500');
		$limit = !empty($params['limit']) ? $params['limit'] : '20';
		$params['limit'] = $limit;
		
		if(empty($company_id))
		{
			return array(
				'message' => "Invalid or Empty value provided for parameter 'companyId'",
				'code' => 'InvalidParamErr'
			);
		}
		
		if(empty($campaign_ids))
			$campaign_ids = Company::getCampaignIdsString($company_id);
		
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		
		$csapi = new CSAPI();
		$top_interests = $csapi->getTopInterests($params);
		$response = array(
			'settings' => $params,
			'data' => array(),
		);
		
		if(!empty($top_interests))
		{
			$sql = "select entrant_id, group_concat(interest_id separator ',') as num_interests 
			from tmp_entrant_interest 
			where campaign_id in (" . $campaign_ids . ") and interest_id in (" . implode(',', array_keys($top_interests)). ")";
	
			if(!empty($arr_test_user_account_ids))
				$sql .= " and entrant_id not in (" . implode(',', $arr_test_user_account_ids) . ")";
				
			$sql .= " group by entrant_id limit $user_limit";
			
			$rs = Database::mysqli_query($sql);
		// error_log("CSAPI::getUsersWithTopInterests(): Time taken to run the SQL: " . (array_sum(explode(" ", microtime())) - $timer_start));
		
			$tmp_data = array();
			while($row = Database::mysqli_fetch_assoc($rs))
			{
				$user_id = $row['entrant_id'];
				$num_interests = $row['num_interests'];
				$likes_data = array();
				$arr_interest_ids = array_unique(explode(',', $num_interests));
				foreach($arr_interest_ids as $interest_id)
				{
					$likes_data[] = array('id' => $interest_id, 'name' => $top_interests[$interest_id]);
				}
				$tmp_data[$user_id] = $likes_data;
			}
			Database::mysqli_free_result($rs);
			
			$sql = "select id as user_id, concat(firstname, ' ', lastname) as `name`, email, gender, date_of_birth, relationship_status as `relationship`, facebook_location_name as `location` 
			from users u where id in (" . implode(',', array_keys($tmp_data)). ")";
			$rs = Database::mysqli_query($sql);
			while($row = Database::mysqli_fetch_assoc($rs))
			{
				$user_id = $row['user_id'];
				$date_of_birth = $row['date_of_birth'];
				$gender = $row['gender'];
				
				if(!empty($gender))
					$row['gender'] = $row['gender'] == 'M' ? 'Male' : ($row['gender'] == 'F' ? 'Female' : '');
					
				if(!empty($date_of_birth) && $date_of_birth != '0000-00-00')
					$row['age'] = Common::calculateAge($date_of_birth);
				
				if(isset($tmp_data[$user_id]))
					$row['likes'] = $tmp_data[$user_id];
					
				if(empty($row['location'])) 
					unset($row['location']);
				
				if(empty($row['relationship'])) 
					unset($row['relationship']);
					
				unset($row['user_id']);
				unset($row['date_of_birth']);
				$response['data'][] = $row;
			}
			Database::mysqli_free_result($rs);
			
		}
		// error_log("response in CSAPI::getUsersWithTopInterests(): " . var_export($response, true));
		return $response;
	}
	
	public function getEmailsSentIntegrations($params)
	{
	
	}
	
	public function getCumulativeClaimedRedeemedIntegrations($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$integration_type = !empty($params['integrationType']) ? $params['integrationType'] : (!empty($params['integrationtype']) ? $params['integrationtype'] : 'et');

		$response = array(
			'graph_data' => array(
				'series' => array(
					'type' => 'pie',
					'name' => 'Claimed to Redeemed Ratio',
					'data' => array()
				)
			)
		);

		if(empty($campaign_ids))
		{
			 $sql = "select group_concat(id separator ',') as item_ids from items where manufacturer_id = '$company_id' and email_code_integration_type = '$integration_type'";
			 
		}
		$str_where_clause = !empty($campaign_ids) ? "item_id in ($campaign_ids)" : "company_id = '$company_id'";
		
		$sql = "select sum(case when date_claimed is null then 0 else 1 end) as num_claims, sum(case when date_redeemed is null then 0 else 1 end) as num_redeems from customer_email_codes where $str_where_clause";
		
		if(!empty($start_date))
			$sql .= " and date_claimed >= '$start_date'";
			
		if(!empty($end_date))
			$sql .= " and date_claimed <= '$end_date'";
			
		// error_log("SQL in CSAPI::getCumulativeClaimedRedeemedIntegrations(): " . $sql);
		
		$row = BasicDataObject::getDataRow($sql);
		$response['graph_data']['series']['data'][] = array("Claims", $row['num_claims'] + 0);
		$response['graph_data']['series']['data'][] = array("Redeems", $row['num_redeems'] + 0);
		$response['settings'] = $params;
		return $response;
	}
	
	public function getCumulativeSentClaimedIntegrations($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$integration_type = !empty($params['integrationType']) ? $params['integrationType'] : (!empty($params['integrationtype']) ? $params['integrationtype'] : 'et');

		$response = array(
			'graph_data' => array(
				'series' => array(
					'type' => 'pie',
					'name' => 'Sent to Claimed Ratio',
					'data' => array()
				)
			)
		);
		
		// error_log("params in CSAPI::getCumulativeSentClaimedIntegrations(): " . var_export($params, true));

		if(empty($campaign_ids))
		{
			 $sql = "select group_concat(id separator ',') as item_ids from items where manufacturer_id = '$company_id' and email_code_integration_type = '$integration_type'";
			 
		}
		$str_where_clause = !empty($campaign_ids) ? "item_id in ($campaign_ids)" : "company_id = '$company_id'";
		
		$sql = "select sum(case when date_claimed is null then 0 else 1 end) as num_claims, sum(case when date_redeemed is null then 0 else 1 end) as num_redeems from customer_email_codes where $str_where_clause";
		
		if(!empty($start_date))
			$sql .= " and date_claimed >= '$start_date'";
			
		if(!empty($end_date))
			$sql .= " and date_claimed <= '$end_date'";
			
		// error_log("SQL in CSAPI::getCumulativeClaimedRedeemedIntegrations(): " . $sql);
		$num_sent = SilverPop::getSentMailings($company_id);
		
		$row = BasicDataObject::getDataRow($sql);
		$response['graph_data']['series']['data'][] = array("Claims", $row['num_claims'] + 0);
		$response['graph_data']['series']['data'][] = array("Sent", $num_sent + 0);
		$response['settings'] = $params;
		return $response;
	}
	
	public function getCumulativeClaimedUnsubscribedIntegrations($params)
	{
		// error_log("params in getCumulativeClaimedUnsubscribedIntegrations() : " . var_export($params, true));
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
		//$start_date = $params['startDate'];
		$start_date = !empty($params['startDate']) ? $params['startDate'] : (!empty($params['startdate']) ? $params['startdate'] :'');
		//$end_date = $params['endDate'];
		$end_date = !empty($params['endDate']) ? $params['endDate'] : (!empty($params['enddate']) ? $params['enddate'] :'');
		$integration_type = !empty($params['integrationType']) ? $params['integrationType'] : (!empty($params['integrationtype']) ? $params['integrationtype'] : 'et');

		$str_where_clause = !empty($campaign_ids) ? "item_id in ($campaign_ids)" : "company_id = '$company_id'";
		
		$sql = "select sum(case when date_claimed is null then 0 else 1 end) as num_claims, sum(case when date_redeemed is null then 0 else 1 end) as num_redeems from customer_email_codes where $str_where_clause";
		
		if(!empty($start_date))
			$sql .= " and date_claimed >= '$start_date'";
			
		if(!empty($end_date))
			$sql .= " and date_claimed <= '$end_date'";
			
		// error_log("SQL in CSAPI::getCumulativeClaimedRedeemedIntegrations(): " . $sql);
		
		$row = BasicDataObject::getDataRow($sql);		
		
		$claims = $row['num_claims'] + 0;
		$unsubscribed = $row['num_redeems'] + 0;
		
		//////////////////////////	CALCULATING THE NUMBER OF UNSUBSCRIBED USERS	///////////////////////////
		
		$graph_data = array(
			array('Claims', $claims),
			array('Unsubscribed', $unsubscribed),
		);
	
		$response = array(
			'settings' => $params,
			'graph_data' => $graph_data
		);
		// error_log("response in getCumulativeClaimedUnsubscribedIntegrations(): " . var_export($response, true));
		
		return $response;
	}
	
	public function login($params)
	{
		$username = !empty($params['userName']) ? $params['userName'] : (!empty($params['username']) ? $params['username'] : '');
		$password = !empty($params['password']) ? $params['password'] : '';
		$hashed	=	!empty($params['hashed']) ? true : false;
		
		$logged_in_user = new User();
		if($logged_in_user->login($username, $password, $hashed, null, null, null, null, null, null, null))
		{
			$group = $logged_in_user->get_group();
			
			$login_type = "";
			$arr_group = is_array($group) ? array_flip($group) : array($group => '0');
			
			if (isset($arr_group['1'])) 
				$login_type = 'admin';
			else if(isset($arr_group['12']) || isset($arr_group['13']))
				$login_type = 'manager';
			else if(isset($arr_group['10']) || isset($arr_group['7']))
				$login_type = 'employee';
			else if (isset($arr_group['14']))
				$login_type = 'consultant';
			
			if(empty($login_type))
			{
				// They're not an employee, show an error message, login denied
				$response['error'] = 'Sorry, your account is not linked to a company as an employee. Please ask your supervisor to invite you from their CoupSmart dashboard.';
			}
			else
			{
				$_SESSION['logged_in'] = true;
				$_SESSION['user'] = $logged_in_user;
				$_SESSION['user_group'] = $group;
				$_SESSION['id'] = $logged_in_user->id;
				$_SESSION['login_type'] = $login_type;
				$_SESSION['customer_id'] = $logged_in_user->id;
				$_SESSION['username'] = $logged_in_user->username;
				// error_log('session in CSAPI::login() ' . var_export($_SESSION, true));
				
				$response['success'] = true;
			}
		}
		else
		{
			$response['error'] = 'Invalid username or password';
		}
		// error_log("response in csapi->login(): " . var_export($response, true));
		return $response;
	}
	
	public function logout($params)
	{
	/*
		setcookie('wp_login_name', '', time()-(3600*2), '/');
		setcookie('wp_login_password', '', time()-(3600*2), '/');
		setcookie('wp_logged_in', '', time()-(3600*2), '/');

		setcookie('logged_out', 'true', 0, '/');
		$rurl = 'http://' . $_SERVER['HTTP_HOST'];

		$pdo = new PDO('mysql:host=' . $db->db_host . ';dbname=' .$db->db, $db->user, $db->pass);
		$options = array("tableName" => "users_cookies",
						'connection' => $pdo, 
						"credentialColumn" => "username", 
						"tokenColumn" => "series", 
						"persistentTokenColumn" => "token",
						"expiresColumn" => "expires");
		// Initialize RememberMe Library with file storage
		// $storage = new Rememberme_Storage_File(__DIR__."/tokens");
		// using the db storage mechanism

		$storage = new Rememberme_Storage_PDO($options);
		$rememberMe = new Rememberme($storage);

		$rememberMe->clearCookie($_SESSION['user']->username);
		$storage->cleanAllTriplets($_SESSION['user']->username);*/
		setcookie("cs_login", null, time() - (3600 * 2), "/", $_SERVER['SERVER_NAME']);
		// error_log('session before destroy: ' . var_export($_SESSION, true));
		// error_log('cookies: ' . var_export($_COOKIE, true));
		$_SESSION = array();
		session_destroy();
		$response['success'] = true;
		return $response;
	}
	
	public function realtimeConsumerStats($params)
	{
		$response = array();
		try
		{
			$field_names		= "";		
			$table_name			= "";
			$inner_join_clause	= "";
			$arr_where_clause 	= array();
			$where_clause		= "";
			$group_by_clause	= "";
			$order_by_clause	= "";
			$limit				= "";
			$limit_clause		= "";
			
			if(empty($params['getCount']))
				$limit			= !empty($params['limit']) ? $params['limit'] : 1000;
		
			$op			=	$params['op'];
			switch($op)
			{
				case 'total_unique_consumers':
					$table_name		= "user_items ui";
					$field_names	=	!empty($params['getCount']) 
										? "count(distinct(ui.user_id)) as num_rows" 
										: "distinct(ui.user_id) as user_id";
					break;	
					
				case 'active_consumers':
					$table_name		= "user_items ui";
					$field_names	=	!empty($params['getCount']) 
										? "count(distinct(ui.user_id)) as num_rows" 
										: "distinct(ui.user_id) as user_id";
					$arr_where_clause = array("ui.date_claimed >= date_sub(now(), interval 1 year)");
					break;
				
				case 'active_fb_token_on':
					$table_name		= "fb_access_tokens fat";
					$field_names	=	!empty($params['getCount']) 
										? "count(distinct(fat.object_id)) as num_rows" 
										: "distinct(fat.object_id) as user_id";
					$arr_where_clause = array("fat.object_type = 'user'", "fat.expire_time > now()");
					break;
					
				case 'likes_data_on':
					$table_name = "user_fb_likes ufl";
					$field_names	=	!empty($params['getCount']) 
										? "count(distinct(ufl.user_id)) as num_rows" 
										: "distinct(ufl.user_id) as user_id";
					$arr_where_clause = array("ufl.date_removed is null");
					break;
					
			}
		
			
		

			if(!empty($arr_where_clause))
				$where_clause = "where " . implode(" and ", $arr_where_clause);
				
			if(!empty($limit))
				$limit_clause = "limit $limit";
			
			$sql = "select $field_names
			from $table_name
			$inner_join_clause
			$where_clause
			$group_by_clause
			$order_by_clause
			$limit_clause";
			// error_log("SQL in CSAPI::realtimeConsumerStats(): " . $sql);
			$rs = Database::mysqli_query($sql);
			if(!$rs)
			{
				throw new Exception(Database::mysqli_error());
			}
			
			if(!empty($params['getCount']))
			{
				$row = Database::mysqli_fetch_assoc($rs);
				$response['num_rows'] = $row['num_rows'];
			}
			else
			{
				$num_rows = mysqli_num_rows($rs);
				while($row = Database::mysqli_fetch_assoc($rs))
				{
					$response['rows'][] = $row;
				}
			}
			
			Database::mysqli_free_result($rs);
			
			return $response;
		}
		catch(Exception $e)
		{
			throw $e;
		}
	}
	
	
	/*	FUNCTIONS FOR FILLING IN OPTIMIZED SSP TABLES */
	public static function refreshDashboardCampaignInterests($company_id)
	{
		$params = array(
			'companyId' => $company_id,
			'action' => 'campaign_interests',
			'limit' => 200,
		);
		$csapi = new CSAPI();
		
		
		// $tmp_table_name = "tmp_ssp_user_ids" . uniqid();
		// Database::mysqli_query("delete from tmp_ssp_user_ids");
		// Database::mysqli_query("create table `$tmp_table_name` (user_id int(11))");
		// $entrants_sql = CSAPI::getSQLForAllEntrants($company_id);
		// $insert_sql = "insert into `$tmp_table_name` (user_id) (" . $entrants_sql . ")";
		// error_log("insert_sql: " . $insert_sql);
		// Database::mysqli_query($insert_sql);
		// $total_entrant_count = Database::mysqli_affected_rows();
		// error_log("total_entrant_count: " . $total_entrant_count);
		$cumulative_entrants_data = $csapi->getCumulativeEntrantCount(array('companyId' => $company_id, 'action' => 'cumulative_entrant_count'));
		
		$total_entrant_count = end($cumulative_entrants_data['graph_data']['series'][0]['data']);
		
		// $data = $csapi->getCampaignInterestsTmp($params);
		$sql = "select t.*
		from
		(
		select ufl.fb_like_id, count(distinct(ufl.user_id)) as num_likes
		from user_fb_likes ufl
		where ufl.company_id = '$company_id'
		group by ufl.fb_like_id
		) as t
		order by num_likes desc limit 200";
		$rows = BasicDataObject::getDataTable($sql);
		$data = array();
		foreach($rows as $i => $row)
		{
			$data[$row['fb_like_id']] = array(
				'num_fans' => $row['num_likes']
			);
		}
		
		$arr_fb_like_ids = implode(",", array_keys($data));
		$interest_names = array();
		
		$sql = "select id, fb_id, name as `like` from fb_likes where id in (" . $arr_fb_like_ids . ")";
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			$interest_names[$row['id']] = array($row['fb_id'], $row['like']);
		}
		
		// sleep(2);
		// Database::mysqli_query("delete from tmp_ssp_user_ids");
		// Database::mysqli_query("drop table if exists `$tmp_table_name`");
		
		
		$insert_sql = '';
		$i = 0;
		foreach($data as $fb_like_id => $row)
		{
			$fb_id = Database::mysqli_real_escape_string($interest_names[$fb_like_id][0]);
			$interest = Database::mysqli_real_escape_string($interest_names[$fb_like_id][1]);
			$num_fans = $row['num_fans'];
			
			if($i > 0)
				$insert_sql .= ", ";
			
			$percent_fans = round(($num_fans / $total_entrant_count) * 100, 2);
			$insert_sql .= "('$company_id', '$fb_id', '$interest', '$num_fans', '$percent_fans')";
			
			$i++;
		}
		
		$sql = "delete from ssp_campaign_interests where company_id = '$company_id'";
		Database::mysqli_query($sql);
		
		$sql = "insert into ssp_campaign_interests (`company_id` , `interest_id`, `interest_name`, `entrants`, `percent_fans`) values " . $insert_sql;
		Database::mysqli_query($sql);
	}
	
	public function getDashboardCampaignInterests($params)
	{
		$company_id = $params['companyId'];
		$limit = !empty($params['limit']) ? $params['limit'] : 100;
		
		$sort = !empty($params['sort']) ? $params['sort'] : 'entrants' ; // 'entrants';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : (!empty($params['sortorder']) ? $params['sortorder'] :'desc');
		
		$sql = "select * from ssp_campaign_interests where company_id = '$company_id' order by $sort $sort_order limit $limit";
		// error_log("SQL in getDashboardCampaignInterests(): " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		
		$response = array();
		$response['graph_data'] = array();
		foreach($rows as $i => $row)
		{
			$response['graph_data'][] = array(
				'interest_id' => $row['interest_id'],
				'interest_name' => $row['interest_name'],
				'entrants' => $row['entrants'], 
				'enrant_percent_of_total' => $row['percent_fans'],
			);
		}

		$response['settings'] = $params;
		return $response;
	}
	
	
	public static function setUnassociatedClaimantIDsInViews($company_id)
	{
		$limit = 100;
		
		// MAKING SURE THAT EACH AND EVERY VIEW IS ASSOCIATED WITH IT'S CLAIMANT (IF ANY)
		$sql = "update items_views iv
		inner join user_items ui  on (iv.id = ui.items_views_id and ui.company_id = '$company_id')
		set iv.user_id = ui.user_id
		where iv.company_id = '$company_id'
		and iv.user_id != ui.user_id";
		Database::mysqli_query($sql);
	}
		
	/*	FUNCTIONS FOR FILLING IN OPTIMIZED SSP TABLES */
	public static function refreshPsychographicBreakdown($company_id, $delay = null, $item_id = null, $deal_id = null, $delivery_method = null)
	{
		// error_log("time 1: " . time());
		
		$views_criteria = empty($item_id) ? " where iv.company_id = '$company_id'" : " where iv.items_id = '$item_id'";
		$claims_criteria = empty($item_id) ? " where ui.company_id = '$company_id'" : " where ui.item_id = '$item_id'";
		$shares_criteria = empty($item_id) ? " where r.company_id = '$company_id'" : " where r.item_shared = '$item_id'";
		$ufl_criteria = empty($item_id) ? " where ufl.company_id = '$company_id'" : " where ufl.item_id = '$item_id'";
		
		//	UNIQUE TEST USERS
		$unique_test_users = User::getTestUserAccountsIds();
		// error_log("unique_test_users: " . var_export($unique_test_users, true));
		
		//	UNIQUE VIEWERS
		// $sql = "select group_concat(distinct(iv.user_id) separator ',') as unique_viewers from items_views iv $views_criteria and iv.user_id > 0";
		$sql = "select concat('{', group_concat(concat('\"', user_id, '\":', num_views) separator ','), '}') as unique_viewers
		from
		(
		select iv.user_id, count(id) as num_views from items_views iv $views_criteria and iv.user_id > 0 group by iv.user_id
		) as t";
		
		// error_log("views sql in CSAPI::refreshPsychographicBreakdown() " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		$unique_viewers = json_decode($row['unique_viewers'], true);
		// error_log("time 2: " . time());
		// error_log("unique_viewers: " . var_export($unique_viewers, true));
		
		//	UNIQUE CLAIMANTS / REDEEMERS / REFERRERS
		// $sql = "select group_concat(distinct(ui.user_id) separator ',') as unique_claimants, group_concat(distinct(if(ui.date_redeemed is not null, ui.user_id, null)) separator ',') as unique_redeemers, group_concat(distinct(if(ui.referral_id > 0, ui.user_id, null)) separator ',') as unique_referrers, group_concat(distinct(if(ui.referral_id > 0 and ui.date_redeemed is not null, ui.user_id, null)) separator ',') as unique_referral_redeemers from user_items ui $claims_criteria and ui.date_claimed is not null";
		
		$sql = "select concat('{', group_concat(concat('\"', user_id, '\":', num_claims) separator ','), '}') as unique_claimants, 
		concat('{', group_concat(concat('\"', user_id, '\":', num_redeems) separator ','), '}') as unique_redeemers, 
		concat('{', group_concat(concat('\"', user_id, '\":', num_referrals) separator ','), '}') as unique_referrers, 
		concat('{', group_concat(concat('\"', user_id, '\":', num_referral_redeems) separator ','), '}') as unique_referral_redeemers
		from 
		(
		select ui.user_id, count(id) as num_claims, sum(if(ui.date_redeemed is not null, 1, 0)) as num_redeems, sum(if(ui.referral_id > 0, 1, 0)) as num_referrals, sum(if(ui.referral_id > 0 and ui.date_redeemed is not null, 1, 0)) as num_referral_redeems
		from user_items ui 
		$claims_criteria 
		and ui.date_claimed is not null 
		and ui.user_id > 0
		group by ui.user_id
		) as t";
		// error_log("claims sql in CSAPI::refreshPsychographicBreakdown() " . $sql);
		$row = BasicDataObject::getDataRow($sql);

		// error_log("time 3: " . time());
		
		$unique_claimants = json_decode($row['unique_claimants'], true);
		$unique_redeemers = json_decode($row['unique_redeemers'], true);
		$unique_referrers = json_decode($row['unique_referrers'], true);
		$unique_referral_redeemers = json_decode($row['unique_referral_redeemers'], true);
		// error_log("unique_claimants: " . var_export($unique_claimants, true));
		// error_log("unique_redeemers: " . var_export($unique_redeemers, true));
		// error_log("unique_referrers: " . var_export($unique_referrers, true));
		
		
		// UNIQUE SHARERS
		// $sql = "select group_concat(distinct(r.user_id) separator ',') as unique_sharers from referrals r $shares_criteria and r.user_id > 0";
		$sql = "select concat('{', group_concat(concat('\"', user_id, '\":', num_shares) separator ','), '}') as unique_sharers
		from
		(
		select r.user_id, count(id) as num_shares from referrals r $shares_criteria and r.user_id > 0 group by r.user_id
		) as t";
		// error_log("shares sql in CSAPI::refreshPsychographicBreakdown() " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		
		// error_log("time 4: " . time());
		$unique_sharers = json_decode($row['unique_sharers'], true);
		// error_log("unique_sharers: " . var_export($unique_sharers, true));
		
		
		
		//	UNIQUE FB USERS
		$sql = "select fb_like_id, user_ids
		from
		(
		select ufl.fb_like_id, count(distinct(ufl.user_id)) as num_users, group_concat(distinct(ufl.user_id) separator ',') as user_ids
		from user_fb_likes ufl
		$ufl_criteria
		group by ufl.fb_like_id
		) as t
		order by num_users desc limit 100";
		
		// error_log("user_fb_likes sql in CSAPI::refreshPsychographicBreakdown() " . $sql);
		$data = BasicDataObject::getDataTable($sql);
		// error_log("time 5: " . time());
		// error_log("data: " . var_export($data, true));
		
		$arr_fb_like_ids = array();
		foreach($data as $i => $row)
		{
			$arr_fb_like_ids[$row['fb_like_id']] = 1;
		}
		
		
		$interest_names = array();
		if(!empty($arr_fb_like_ids))
		{
			$arr_fb_like_ids = implode(",", array_keys($arr_fb_like_ids));
			
	
			$sql = "select id, fb_id, name as `like`, category from fb_likes where id in (" . $arr_fb_like_ids . ")";
			$rows = BasicDataObject::getDataTable($sql);
			// error_log("FB Likes SQL: " . $sql);
			// error_log("time 6: " . time());
			foreach($rows as $i => $row)
			{
				$interest_names[$row['id']] = array($row['fb_id'], $row['like'], $row['category']);
			}
		}
		// error_log("interest_names: " . var_export($interest_names, true));
		// sleep(2);
		// Database::mysqli_query("drop table if exists `$tmp_table_name`");
	
	
		$insert_sql = '';
		foreach($data as $i => $row)
		{
			$fb_like_id = $row['fb_like_id'];
			$fb_id = Database::mysqli_real_escape_string($interest_names[$fb_like_id][0]);
			$interest = Database::mysqli_real_escape_string($interest_names[$fb_like_id][1]);
			$category = Database::mysqli_real_escape_string($interest_names[$fb_like_id][2]);
			
			$unique_fans = explode(',', $row['user_ids']);
			// Remove test users if any
			$unique_fans = array_diff($unique_fans, $unique_test_users);
			$num_fans = count($unique_fans);
			$unique_fans = array_flip($unique_fans);
			
			$fans_and_viewers = array_intersect_key($unique_viewers, $unique_fans);
			// error_log("fans_and_viewers: " . var_export($fans_and_viewers, true));
			$views = array_sum($fans_and_viewers); // count() instead of array_sum will give the number of unique viewers
			
			$fans_and_claimants = array_intersect_key($unique_claimants, $unique_fans);
			// $str_fans_and_claimants = json_encode($fans_and_claimants);
			// error_log("str_fans_and_claimants for interest $interest: " . $str_fans_and_claimants);
			// error_log("fans_and_claimants: " . var_export($fans_and_claimants, true));
			$claims = array_sum($fans_and_claimants); // count() instead of array_sum will give the number of unique claimants
			
			$fans_and_redeemers = array_intersect_key($unique_redeemers, $unique_fans);
			$redeems = array_sum($fans_and_redeemers);
			
			$fans_and_sharers = array_intersect_key($unique_sharers, $unique_fans);
			$shares = array_sum($fans_and_sharers);
			
			$fans_and_referrers = array_intersect_key($unique_referrers, $unique_fans);
			$referrals = array_sum($fans_and_referrers);
			
			$fans_and_referral_redeemers = array_intersect_key($unique_referral_redeemers, $unique_fans);
			$referral_redeems = array_sum($fans_and_referral_redeemers);
			
			
			$v2c_ratio = $views > 0 ? round(($claims / $views) * 100, 2) : 0;
			$referral_ratio = $shares > 0 ? round(($referral_redeems / $shares) * 100, 2) : 0;
		
			if($i > 0)
				$insert_sql .= ", ";
		
			if(empty($item_id))
			{
				$item_id_val = 'NULL';
				$delivery_method_val = 'NULL';
				$deal_id_val = 'NULL';
			}
			else
			{
				$item_id_val = "'" . $item_id . "'";
				$delivery_method_val =  "'" . $delivery_method . "'";
				$deal_id_val =  "'" . $deal_id . "'";
			}
		
			$insert_sql .= "('$company_id', $item_id_val, $deal_id_val, $delivery_method_val, '$fb_id', '$interest', '$category', '$num_fans', '$views', '$claims', '$redeems', '$shares', '$referrals', '$referral_redeems')"; // , '$v2c_ratio', '$referral_ratio')";
		
			$i++;
		}
		// error_log("time 7: " . time());
		$sql = "delete from ssp_psychographic_breakdown where company_id = '$company_id'";
		$sql .= empty($item_id) ? " and item_id is null" : " and item_id = '$item_id'";
		// error_log("delete sql in CSAPI::refreshPsychographicBreakdown() " . $sql);
		Database::mysqli_query($sql);
		if(!empty($insert_sql))
		{
			
		
			$sql = "insert into ssp_psychographic_breakdown (`company_id` , `item_id`, `deal_id`, `delivery_method`, `interest_id`, `interest_name`, `interest_category`, `entrants`, `views`, `claims`, `redeems`, `shares`, `referrals`, `referral_redeems`)";
			// $sql .= ", `v2c_ratio`, `referral_ratio`)";
			$sql .= " values " . $insert_sql;
			// error_log("insert sql in CSAPI::refreshPsychographicBreakdown() " . $sql);
			Database::mysqli_query($sql);
			// error_log("time 8: " . time());
		}
		// error_log("time 6: " . time());
		if(!empty($delay))
			sleep($delay);
		
		
		
		
		
	}
	
	public function getPsychographicBreakdown($params)
	{
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
	
		$limit = !empty($params['limit']) ? $params['limit']: 20;
		$sort = !empty($params['sort']) ? $params['sort'] : 'view2claim_ratio';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		
		$min_interest_fan_base = !empty($params['minInterestFanbase']) ?  $params['minInterestFanbase'] : (!empty($params['mininterestfanbase']) ? $params['mininterestfanbase'] : 0);
		$min_group_size = !empty($params['minGroupSize']) ? $params['minGroupSize']: (!empty($params['mingroupsize']) ? $params['mingroupsize'] : 1);
	
		$response = array();
		$response['graph_data'] = array();
		
		$sql = "select interest_id, interest_name, interest_category, sum(entrants) as entrants, sum(views) as view_count, sum(claims) as claim_count, sum(redeems) as redeem_count, sum(shares) as share_count, sum(referrals) as refer_count, sum(referral_redeems) as refer_redeem_count
			from ssp_psychographic_breakdown";
		if(!empty($campaign_ids))
			$sql .= " where company_id = '$company_id' and item_id in ($campaign_ids)";
		else
			$sql .= " where company_id = '$company_id' and item_id is null";
			
		$sql .= " group by interest_id";
		
		// error_log("SQL in CSAPI::getPsychographicBreakdown(): " . $sql);
		
		$cum_view_count = 0;
		$cum_claim_count = 0;
		$cum_redeem_count = 0;
		$cum_share_count = 0;
		$cum_refer_count = 0;
		$cum_refer_redeem_count = 0;
		
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			$interest_id = $row['interest_id'];
			$interest_name = $row['interest_name'];
			$interest_category = $row['interest_category'];
			$group_size = $row['entrants'] + 0;
			$view_count = $row['view_count'] + 0;
			$claim_count = $row['claim_count'] + 0;
			$redeem_count = $row['redeem_count'] + 0;
			$share_count = $row['share_count'] + 0;
			$refer_count = $row['refer_count'] + 0;
			$refer_redeem_count = $row['refer_redeem_count'] + 0;
			
			// error_log("interest_id: $interest_id, interest_name: $interest_name, group_size: $group_size, view_count: $view_count, claim_count: $claim_count, redeem_count: $redeem_count, share_count: $share_count, refer_count: $refer_count, refer_redeem_count: $refer_redeem_count");

			$view2claim_ratio = $view_count > 0 ? round(($claim_count / $view_count) * 100, 2) : 0;
			$claim2redeem_ratio = $claim_count > 0 ? round(($redeem_count / $claim_count) * 100, 2) : 0;
			$claim2share_ratio = $claim_count > 0 ? round(($share_count / $claim_count) * 100, 2) : 0;
			$share2share_claim_ratio = $share_count > 0 ? round(($refer_count / $share_count) * 100, 2) : 0;
			$share2share_redeem_ratio = $share_count > 0 ? round(($refer_redeem_count / $share_count) * 100, 2) : 0;
		
		
			$response['graph_data'][] = array(
				'interest_name' => $interest_name,
				'interest_fb_id' => $interest_id,
				'interest_category' => $interest_category,
				'group_size' => $group_size,
				'view_count' => $view_count,
				'claim_count' => $claim_count,
				'redeem_count' => $redeem_count,
				'share_count' => $share_count,
				'share_claim_count' => $refer_count,
				'refer_redeem_count' => $refer_redeem_count,
				'view2claim_ratio' => $view2claim_ratio,
				'claim2redeem_ratio' => $claim2redeem_ratio,
				'claim2share_ratio' => $claim2share_ratio,
				'share2share_claim_ratio' => $share2share_claim_ratio,
				'share2share_redeem_ratio' => $share2share_redeem_ratio,
			);
		
			$cum_group_size += $group_size;
			$cum_view_count += $view_count;
			$cum_claim_count += $claim_count;
			$cum_redeem_count += $redeem_count;
			$cum_share_count += $share_count;
			$cum_refer_count += $refer_count;
			$cum_refer_redeem_count += $refer_redeem_count;
		}
		
		$response['cumulative_data'] = array(
			'cum_group_size' => $cum_group_size,
			'cum_view_count' => $cum_view_count,
			'cum_claim_count' => $cum_claim_count,
			'cum_redeem_count' => $cum_redeem_count,
			'cum_share_count' => $cum_share_count,
			'cum_refer_count' => $cum_refer_count,
			'cum_refer_redeem_count' => $cum_refer_redeem_count,
			// 'cum_view2claim_ratio' => $cum_view2claim_ratio,
			// 'cum_claim2redeem_ratio' => $cum_claim2redeem_ratio,
			// 'cum_claim2share_ratio' => $cum_claim2share_ratio,
			// 'cum_share2share_claim_ratio' => $cum_share2share_claim_ratio,
			// 'cum_share2share_redeem_ratio' => $cum_share2share_redeem_ratio,
		);
	
		$response['categories'] = array(
			'interest_name',
			'interest_fb_id',
			'interest_category',
			'group_size',
			'view_count',
			'claim_count',
			'redeem_count',
			'share_count',
			'share_claim_count',
			'refer_redeem_count',
			'view2claim_ratio',
			'claim2redeem_ratio',
			'claim2share_ratio',
			'share2share_claim_ratio',
			'share2share_redeem_ratio',	
		);
	
		// error_log("response['graph_data'] before sorting: " . var_export($response['graph_data'], true));
		if(!empty($sort))
			Common::multiSortArrayByColumn($response['graph_data'], $sort, $sort_order);
		// error_log("response['graph_data'] after sorting: " . var_export($response['graph_data'], true));
		
		$response['graph_data'] = array_slice($response['graph_data'], 0, $limit, true);
		// error_log("response['graph_data'] before slicing: " . var_export($response['graph_data'], true));
		$response['settings'] = $params;
		return $response;
	}
	
	public static function refreshPsychographicProfile($company_id, $delay = null, $item_id = null, $deal_id = null, $delivery_method = null)
	{
	
		//	1.	Get all Company Item Ids
		// $sql = "select id as item_id, deal_id, delivery_method from items where manufacturer_id = '$company_id'";
		// $company_items = BasicDataObject::getDataTable($sql);
		// $company_items[] = array('item_id' => NULL, 'deal_id' => NULL, 'delivery_method' => NULL);
		// $company_items = array_merge(array(array('item_id' => NULL, 'deal_id' => NULL, 'delivery_method' => NULL)), $company_items);
		
		$csapi = new CSAPI();
		
		// foreach($company_items as $i => $item)
		{
			
			// $item_id			= $item['item_id'];
			// $deal_id			= $item['deal_id'];
			// $delivery_method	= $item['delivery_method'];
			
			$sql = "select t.*
			from
			(
			select ufl.fb_like_id, count(distinct(ufl.user_id)) as num_likes, group_concat(distinct(ufl.user_id) order by ufl.user_id separator ',') as entrants
			from user_fb_likes ufl";
			// $sql .= "-- inner join `$tmp_table_name` u on ufl.user_id = u.user_id";
			$sql .= empty($item_id) ? " where ufl.company_id = '$company_id'" : " where ufl.item_id = '$item_id'";
			$sql .= " group by ufl.fb_like_id
			) as t
			order by t.num_likes desc limit 20";
			// error_log("sql for CSAPI::refreshPsychographicProfile(): " . $sql);
			$rows = BasicDataObject::getDataTable($sql);
			
			// 3. Getting Interest Names
			$arr_fb_like_ids = array();
			foreach($rows as $i => $row)
			{
				$arr_fb_like_ids[$row['fb_like_id']] = 1;
			}
			
			
			$interest_names = array();
			if(!empty($arr_fb_like_ids))
			{
				$arr_fb_like_ids = implode(",", array_keys($arr_fb_like_ids));
				
		
				$sql = "select id, fb_id, name as `like` from fb_likes where id in (" . $arr_fb_like_ids . ")";
				$interests = BasicDataObject::getDataTable($sql);
			
				foreach($interests as $i => $row)
				{
					$interest_names[$row['id']] = array($row['fb_id'], $row['like']);
				}
			}
			
			
			$num_rows = count($rows);
			$insert_sql = "";
			for($i = 0; $i < $num_rows; $i++)
			{
				$index_y_axis = $i;
				$interest_id_y_axis = $interest_names[$rows[$i]['fb_like_id']][0];
				$interest_name_y_axis = Database::mysqli_real_escape_string($interest_names[$rows[$i]['fb_like_id']][1]);

				$arr_entrants_y_axis = explode(',', $rows[$i]['entrants']);
				
				
				for($j = 0; $j < $num_rows; $j++)
				{
					$index_x_axis = $j;
					$interest_id_x_axis = $interest_names[$rows[$j]['fb_like_id']][0];
					$interest_name_x_axis = Database::mysqli_real_escape_string($interest_names[$rows[$j]['fb_like_id']][1]);
					$arr_entrants_x_axis = explode(',', $rows[$j]['entrants']);
					
					
				
					if(!empty($insert_sql))
						$insert_sql .= ", ";
					
					if(empty($item_id))
					{
						$item_id_val = 'NULL';
						$delivery_method_val = 'NULL';
						$deal_id_val = 'NULL';
					}
					else
					{
						$item_id_val = "'" . $item_id . "'";
						$delivery_method_val =  "'" . $delivery_method . "'";
						$deal_id_val =  "'" . $deal_id . "'";
					}
					
					$common_entrants = count(array_intersect($arr_entrants_y_axis, $arr_entrants_x_axis));
					$insert_sql .= "($company_id, $item_id_val, $deal_id_val, $delivery_method_val, '$index_x_axis', '$interest_id_x_axis', '$interest_name_x_axis', '$index_y_axis', '$interest_id_y_axis', '$interest_name_y_axis', '$common_entrants')";
				}
				
			}
		
			$sql = "delete from ssp_psychographic_profile where company_id = '$company_id'";
			$sql .= empty($item_id) ? " and item_id is null" : " and item_id = '$item_id'";
			Database::mysqli_query($sql);
			// error_log("delete sql in CSAPI::refreshPsychographicProfile() " . $sql);
			// Database::mysqli_query("delete from ssp_psychographic_profile where company_id = '$company_id'");
			if(!empty($insert_sql))
			{
				
			
				$insert_sql = "insert into ssp_psychographic_profile values $insert_sql";
				// error_log("insert sql in CSAPI::refreshPsychographicProfile() " . $insert_sql);
				Database::mysqli_query($insert_sql);
			}
			
			if(!empty($delay))
				sleep($delay);
		}
	}
	
	public function getPsychographicProfile($params)
	{
		$response = array();
		$response['graph_data'] = array();
		
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
	
		$limit = !empty($params['limit']) ? $params['limit'] * $params['limit']: 100;
		
		$sql = "select * from ssp_psychographic_profile";
		if(!empty($campaign_ids))
			$sql .= " where company_id = '$company_id' and item_id in ($campaign_ids)";
		else
			$sql .= " where company_id = '$company_id' and item_id is null";
			
		$sql .= " limit $limit";
		
		$rows = BasicDataObject::getDataTable($sql);
		$x_titles = array();
		$y_titles = array();
		foreach($rows as $i => $row)
		{
			$x = $row['index_x_axis'] + 0;
			$y = $row['index_y_axis'] + 0;
			$entrants = $row['entrants'] + 0;
			$x_title = $row['interest_name_x_axis'];
			$y_title = $row['interest_name_y_axis'];
			$response['graph_data'][] = array($x, $y, $entrants);
			
			$x_titles[$x_title] = 1;
			$y_titles[$y_title] = 1;
		}
		$response['xAxis'] = array_keys($x_titles);
		$response['yAxis'] = array_keys($y_titles);
		$response['settings'] = $params;
		return $response;
	}
	
	public static function getTopCompanyInterests($company_id, $limit_top_interests)
	{
		$sql = "select ci.*, fl.id as fb_like_id from ssp_campaign_interests ci inner join fb_likes fl on ci.interest_id = fl.fb_id where company_id = '$company_id' order by ci.entrants desc limit $limit_top_interests";
		
		$rows = BasicDataObject::getDataTable($sql);
		return $rows;
	}
	
	
	public static function refreshKeyAdvocates($company_id, $delay = null, $item_id = null, $deal_id = null, $delivery_method = null)
	{
	
		$csapi = new CSAPI();
		
		$views_criteria = empty($item_id) ? " where iv.company_id = '$company_id'" : " where iv.items_id = '$item_id'";
		$claims_criteria = empty($item_id) ? " where ui.company_id = '$company_id'" : " where ui.item_id = '$item_id'";
		$shares_criteria = empty($item_id) ? " where r.company_id = '$company_id'" : " where r.item_shared = '$item_id'";
		$ufl_criteria = empty($item_id) ? " where ufl.company_id = '$company_id'" : " where ufl.item_id = '$item_id'";
	
		//	UNIQUE TEST USERS
		$unique_test_users = json_decode(User::getTestUserAccountsIds(true), true);
		// error_log("time 0: " . time());
		
		//	UNIQUE VIEWERS
		$sql = "select concat('{', group_concat(concat('\"', user_id, '\":', num_views) separator ','), '}') as unique_viewers
		from
		(
		select iv.user_id, count(id) as num_views from items_views iv $views_criteria and iv.user_id > 0 group by iv.user_id
		) as t";
	
		// error_log("views sql in CSAPI::refreshKeyAdvocatesTmp() " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		// error_log("time 1: " . time());
		$unique_viewers = !empty($row['unique_viewers']) ? json_decode($row['unique_viewers'], true) : array();

		$sql = "select concat('{', group_concat(concat('\"', user_id, '\":', num_claims) separator ','), '}') as unique_claimants, 
		concat('{', group_concat(concat('\"', user_id, '\":', num_redeems) separator ','), '}') as unique_redeemers, 
		concat('{', group_concat(concat('\"', user_id, '\":', num_referrals) separator ','), '}') as unique_referrers, 
		concat('{', group_concat(concat('\"', user_id, '\":', num_referral_redeems) separator ','), '}') as unique_referral_redeemers
		from 
		(
		select ui.user_id, count(id) as num_claims, sum(if(ui.date_redeemed is not null, 1, 0)) as num_redeems, sum(if(ui.referral_id > 0, 1, 0)) as num_referrals, sum(if(ui.referral_id > 0 and ui.date_redeemed is not null, 1, 0)) as num_referral_redeems
		from user_items ui 
		$claims_criteria 
		and ui.date_claimed is not null 
		and ui.user_id > 0
		group by ui.user_id
		) as t";
		// error_log("claims sql in CSAPI::refreshKeyAdvocatesTmp() " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		// error_log("time 2: " . time());

		$unique_claimants = !empty($row['unique_claimants']) ? json_decode($row['unique_claimants'], true) : array();
		$unique_redeemers = !empty($row['unique_redeemers']) ? json_decode($row['unique_redeemers'], true) : array();
		$unique_referrers = !empty($row['unique_referrers']) ? json_decode($row['unique_referrers'], true) : array();
		$unique_referral_redeemers = !empty($row['unique_referral_redeemers']) ? json_decode($row['unique_referral_redeemers'], true) : array();


		// UNIQUE SHARERS
		$sql = "select concat('{', group_concat(concat('\"', user_id, '\":', num_shares) separator ','), '}') as unique_sharers
		from
		(
		select r.user_id, count(id) as num_shares from referrals r $shares_criteria and r.user_id > 0 group by r.user_id
		) as t";
		// error_log("shares sql in CSAPI::refreshKeyAdvocatesTmp() " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		// error_log("time 3: " . time());
		$unique_sharers = !empty($row['unique_sharers']) ? json_decode($row['unique_sharers'], true) : array();



		//	UNIQUE FB LIKE IDS
		$sql = "select fb_like_id
		from
		(
		select ufl.fb_like_id, count(distinct(ufl.user_id)) as num_users
		from user_fb_likes ufl
		$ufl_criteria
		group by ufl.fb_like_id
		) as t
		order by num_users desc limit 100";

		// error_log("user_fb_likes sql in CSAPI::refreshKeyAdvocatesTmp() " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		// error_log("time 4: " . time());
		
		$arr_top_fb_like_ids = array();
		foreach($rows as $i => $row)
		{
			$arr_top_fb_like_ids[] = $row['fb_like_id'];
		}

		// TOP LIKE NAMES
		$interest_names = array();
		if(!empty($arr_top_fb_like_ids))
		{
			$sql = "select id, fb_id, name as `like` from fb_likes where id in (" . implode(",", $arr_top_fb_like_ids) . ")";
			$rows = BasicDataObject::getDataTable($sql);
			// error_log("FB Likes SQL: " . $sql);
			// error_log("time 6: " . time());
			foreach($rows as $i => $row)
			{
				$interest_names[$row['id']] = array($row['fb_id'], $row['like']);
			}
		}
		// error_log("time 5: " . time());
		
		//	UNIQUE FANS AND THEIR LIKES
		$sql = "select ufl.user_id, group_concat(ufl.fb_like_id separator ',') as user_fb_likes
		from user_fb_likes ufl
		$ufl_criteria
		group by ufl.user_id";
		// error_log("user fans and likes sql in CSAPI::refreshKeyAdvocatesTmp() " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		// error_log("time 6: " . time());
		$unique_fans = array();
		foreach($rows as $i => $row)
		{
			// $unique_fans = json_decode($row['user_ids'], true);
			$unique_fans[$row['user_id']] = $row['user_fb_likes'];
		}
		
		// GETTING FB LIKE ID FOR COMPANY PAGE (IF ANY)
		$company = new Company($company_id);
		$facebook_page_id = $company->facebook_page_id;
		$sql = "select id from fb_likes where fb_id = '$facebook_page_id' order by id desc limit 1";
		$row = BasicDataObject::getDataRow($sql);
		$company_fb_like_id = $row['id'];
		
		// error_log("unique_viewers: " . var_export($unique_viewers, true));
		// error_log("unique_claimants: " . var_export($unique_viewers, true));
		// error_log("unique_sharers: " . var_export($unique_sharers, true));
		// error_log("unique_fans: " . var_export($unique_fans, true));
		
		
		$all_unique_entrants = array_unique(array_merge(
			array_keys($unique_viewers), 
			array_keys($unique_claimants), 
			array_keys($unique_sharers), 
			array_keys($unique_fans)
			)
		);
		
		// ALL ENTRANTS
		$all_entrants 				= count($all_unique_entrants); // $csapi->getAllEntrants();
		// error_log("CSAPI::refreshKeyAdvocates(): time 7: " . time());
		
		// ALL REFERRAL REDEMPTIONS
		$all_referral_redemptions	= $csapi->getAllReferralRedemptions();
		// error_log("CSAPI::refreshKeyAdvocates(): time 8: " . time());
		
		// ALL REFERRAL CLAIMS
		$all_referral_claims		= $csapi->getAllReferralClaims();
		// error_log("CSAPI::refreshKeyAdvocates(): time 9: " . time());
		
		// ALL REFERRAL CLAIMS
		$avg_referral_claims		= $all_referral_claims / $all_entrants;
		
		// ALL REFERRAL REDEMPTIONS
		$avg_referral_redemptions	= $all_referral_redemptions / $all_entrants;

		// ALL FB FRIENDS
		$all_fb_friends				= $csapi->getAllFBFriends();
		// error_log("CSAPI::refreshKeyAdvocates(): time 10: " . time());
		$avg_friends 				= round($all_fb_friends / $all_entrants, 2);
		$advocacy_scores = array();
		
		foreach($all_unique_entrants as $i => $user_id)
		{
			$view_count		= !empty($unique_viewers[$user_id]) ? $unique_viewers[$user_id] + 0 : 0;
			$claim_count	= !empty($unique_claimants[$user_id]) ? $unique_claimants[$user_id] + 0 : 0;
			$redeem_count	= !empty($unique_redeemers[$user_id]) ? $unique_redeemers[$user_id] + 0 : 0;
			$share_count	= !empty($unique_sharers[$user_id]) ? $unique_sharers[$user_id] + 0 : 0;
			$refer_count	= !empty($unique_referrers[$user_id]) ? $unique_referrers[$user_id] + 0 : 0;
			$refer_redeem_count	= !empty($unique_referral_redeemers[$user_id]) ? $unique_referral_redeemers[$user_id] + 0 : 0;


			// Calculate Advocacy score
			$referral_redemptions = !empty($avg_referral_redemptions) ? min(round(($refer_redeem_count * 100) / $avg_referral_redemptions, 2), 100) : 0;
			$individual_redemptions = !empty($claim_count) ? min(round(($redeem_count * 100) / $claim_count, 2), 100) : 0;
			$referral_claims = !empty($avg_referral_claims) ? min(round(($refer_count * 100) / $avg_referral_claims, 2), 100) : 0;
			$individual_claims = min($claim_count, 100);
			$shares = min($share_count, 100);
			$fan_of_company = 0;
			if(!empty($unique_fans[$user_id]))
			{
				$unique_fan_likes = array_flip(explode(',', $unique_fans[$user_id]));
				$fan_of_company = isset($unique_fan_likes[$company_fb_like_id]) ? 100 : 0;
			}
				
			// $fan_of_company = !empty($row['fan_user_id']) ? 1 : 0;
			// $friend_reach = !empty($avg_friends) ? min($friend_count / $avg_friends, 100) : 0;
			
			$advocacy_score = 
				($referral_redemptions * 0.25 + 
				$individual_redemptions * 0.15 + 
				$referral_claims * 0.15 + 
				$individual_claims * 0.13 + 
				$shares * 0.12 +
				$fan_of_company * 0.125) / 1;
			
			$advocacy_scores[$user_id] = $advocacy_score;
		}
		// error_log("CSAPI::refreshKeyAdvocates(): time 11: " . time());

		arsort($advocacy_scores);
		$advocacy_scores = array_slice($advocacy_scores, 0, 2000, true);
		// $entrant_interests[$entrant_id] = array_slice($entrant_interests[$entrant_id], 0, $interest_limit, true);
		// error_log("advocacy_scores: " . var_export($advocacy_scores, true));
		
		// WE'RE DONE HERE! NOW WE SIMPLY GET THE ORDERED USER DETAILS AND ADD FRIEND COUNT TO THE ADVOCACY SCORE
		if(!empty($advocacy_scores))
		{
			$sql = "select u.id as user_id, u.facebook_id, u.username, u.email, u.firstname, u.lastname, u.gender, u.date_of_birth, u.relationship_status, dma.dma_region_code, dma.dma_region, u.fb_friend_count as friend_count
			from 
			users u
			inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id 
			inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id
			where u.id in (" . implode(',', array_keys($advocacy_scores)) . ")";
			// error_log("user sql in CSAPI::refreshKeyAdvocatesTmp() " . $sql);
			$rows = BasicDataObject::getDataTable($sql);
			// error_log("CSAPI::refreshKeyAdvocates(): time 12: " . time());
			$insert_sql = "";
			foreach($rows as $i => $row)
			{
				$user_id		= $row['user_id'];
				$facebook_id	= $row['facebook_id'];
				$email			= $row['email'];
				$entrant_name	= Database::mysqli_real_escape_string($row['firstname'] . ' ' . $row['lastname']);
				$gender			= strtoupper($row['gender']);
				$gender			= $gender == 'F' ? 'Female' : ($gender == 'M' ? 'Male' : '-');
				$date_of_birth	= $row['date_of_birth'];
				$age 			= Common::calculateAgeFromBirthday($date_of_birth);

				$relationship_status	= Database::mysqli_real_escape_string($row['relationship_status']);
				$dma_code		= Database::mysqli_real_escape_string($row['dma_region_code']);
				$dma_region		= Database::mysqli_real_escape_string($row['dma_region']);
				
				$friend_count 	= $row['friend_count'];
				$friend_reach = !empty($avg_friends) ? min($friend_count * 100 / $avg_friends, 100) : 0;
				
				$advocacy_score = round($advocacy_scores[$user_id] + $friend_reach * 0.075, 2);
				$unique_fan_likes = !empty($unique_fans[$user_id]) ? explode(',', $unique_fans[$user_id]) : array();
				$user_fb_interests = array_intersect($arr_top_fb_like_ids, $unique_fan_likes);
				$user_fb_interests = array_slice($user_fb_interests, 0, 3, true);
				
				
				$user_interests = array();
				foreach($user_fb_interests as $fb_like_id)
				{
					$fb_id = $interest_names[$fb_like_id][0];
					$fb_name = $interest_names[$fb_like_id][1];
					$user_interests[$fb_id] = $fb_name;
				}
				
				$str_user_fb_interests = Database::mysqli_real_escape_string(json_encode($user_interests));

				
				if(!empty($insert_sql))
					$insert_sql .= ", ";
				
				if(empty($item_id))
				{
					$item_id_val = 'NULL';
					$delivery_method_val = 'NULL';
					$deal_id_val = 'NULL';
				}
				else
				{
					$item_id_val = "'" . $item_id . "'";
					$delivery_method_val =  "'" . $delivery_method . "'";
					$deal_id_val =  "'" . $deal_id . "'";
				}
				
				$insert_sql .= "($company_id, $item_id_val, $deal_id_val, $delivery_method_val, '$dma_code', '$dma_region', '$relationship_status', '$entrant_name', '$email', '$facebook_id', '$gender', '$age', '$str_user_fb_interests', '$advocacy_score')";
			}
			
		}
		
		// DELETE ALL DATA BEFORE INSERTING
	
		$sql = "delete from ssp_key_advocates where company_id = '$company_id'";
		$sql .= empty($item_id) ? " and item_id is null" : " and item_id = '$item_id'";
		// error_log("delete sql in CSAPI::refreshKeyAdvocates() " . $sql);
		Database::mysqli_query($sql);
				
		if(!empty($insert_sql))
		{
			$insert_sql = "insert into `ssp_key_advocates` (company_id, item_id, deal_id, delivery_method, dma_code, dma, relationship, username, email, facebook_id, gender, age, top_likes, advocacy_score) values " . $insert_sql;
			Database::mysqli_query($insert_sql);
			// error_log("insert sql in CSAPI::refreshKeyAdvocates() " . $insert_sql);
		}

		// error_log("CSAPI::refreshKeyAdvocates(): time 13: " . time());
		
		if(!empty($delay))
			sleep($delay);
	}
	
	
	public static function refreshKeyAdvocatesTmp($company_id, $delay = null, $item_id = null, $deal_id = null, $delivery_method = null)
	{
	
		$csapi = new CSAPI();
		$arr_test_user_account_ids = User::getTestUserAccountsIds();
		$where_clause = "";
		if(!empty($arr_test_user_account_ids))
			$where_clause = " where t.user_id not in (" . implode(',', $arr_test_user_account_ids). ")";
		
		$limit_top_interests = 20;
		$rows = CSAPI::getTopCompanyInterests($company_id, $limit_top_interests);
		$arr_fb_like_ids = array();
		foreach($rows as $i => $row)
			$arr_fb_like_ids[$row['fb_like_id']] = $row;
			
		// Getting company fb_like_id
		$company = new Company($company_id);
		$sql = "select id as company_fb_like_id from fb_likes where fb_id = '" . $company->facebook_page_id . "' order by id desc limit 1";
		$row = BasicDataObject::getDataRow($sql);
		$company_fb_like_id = $row['company_fb_like_id'];
		
		
		// GET INTEREST IDS
		$tmp_table_name = "tmp_ssp_user_ids" . uniqid();
		Database::mysqli_query("create table `$tmp_table_name` (user_id int(11))");
		$entrants_sql = CSAPI::getSQLForAllEntrants($company_id);
		$insert_sql = "insert into `$tmp_table_name` (user_id) (" . $entrants_sql . ")";
		// error_log("CSAPI::refreshKeyAdvocates(): time -2: " . time());
		Database::mysqli_query($insert_sql);
		
		//	GETTING ALL USER INTERESTS
		$sql = "select ufl.user_id, ufl.fb_like_id
		from user_fb_likes ufl
		inner join $tmp_table_name u on ufl.user_id = u.user_id
		where ufl.fb_like_id in (" . implode(',', array_keys($arr_fb_like_ids)). ")
		and ufl.company_id = '$company_id'";
		
		$rows = BasicDataObject::getDataTable($sql);
		// error_log("CSAPI::refreshKeyAdvocates(): time -1: " . time());
		$user_fb_likes = array();
		
		foreach($rows as $i => $row)
		{
			$user_id = $row['user_id'];
			$fb_like_id = $row['fb_like_id'];
			
			if(!isset($user_fb_likes[$user_id]))
				$user_fb_likes[$user_id] = array();
				
			$priority = $arr_fb_like_ids[$fb_like_id]['entrants'];
			$user_fb_likes[$user_id][$fb_like_id] = $priority;
		}
		// error_log("user_fb_likes in CSAPI::refreshKeyAdvocates():" . var_export($user_fb_likes, true));
		// error_log("CSAPI::refreshKeyAdvocates(): time 0: " . time());
		
		
		

		// error_log("CSAPI::refreshKeyAdvocates(): time 1: " . time());
		$all_referral_redemptions	= $csapi->getAllReferralRedemptions();
		// error_log("CSAPI::refreshKeyAdvocates(): time 2: " . time());
		$all_referral_claims		= $csapi->getAllReferralClaims();
		// error_log("CSAPI::refreshKeyAdvocates(): time 3: " . time());
		$all_entrants 				= $csapi->getAllEntrants();
		// error_log("CSAPI::refreshKeyAdvocates(): time 4: " . time());
		$avg_referral_claims		= $all_referral_claims / $all_entrants;
		$avg_referral_redemptions	= $all_referral_redemptions / $all_entrants;

		$all_fb_friends				= $csapi->getAllFBFriends();
		// error_log("CSAPI::refreshKeyAdvocates(): time 5: " . time());
		$avg_friends 				= round($all_fb_friends / $all_entrants, 2);
	

		//	7.	Get all Company Item Ids
		// $sql = "select id as item_id, deal_id, delivery_method from items where manufacturer_id = '$company_id'";
		// $company_items = BasicDataObject::getDataTable($sql);
		// $company_items[] = array('item_id' => NULL, 'deal_id' => NULL, 'delivery_method' => NULL);
		// $company_items = array_merge(array(array('item_id' => NULL, 'deal_id' => NULL, 'delivery_method' => NULL)), $company_items);

		// foreach($company_items as $i => $item)
		{
			
			// $item_id			= $item['item_id'];
			// $deal_id			= $item['deal_id'];
			// $delivery_method	= $item['delivery_method'];
			
			$items_clause_views		= !empty($item_id)		? " and iv.items_id = '$item_id'" : "";
			$items_clause_claims	= !empty($item_id)		? " and ui.item_id = '$item_id'" : "";
			$items_clause_redeems	= !empty($item_id)		? " and ui.item_id = '$item_id'" : "";
			$items_clause_shares	= !empty($item_id)		? " and r.item_shared = '$item_id'" : "";
			$items_clause_referrals = !empty($item_id)		? " and ui.item_id = '$item_id'" : "";
			$items_clause_redeem_referrals = !empty($item_id) ? " and ui.item_id = '$item_id'" : "";
			
			$sql = 
			"select t.user_id, u.facebook_id, u.username, u.email, u.firstname, u.lastname, u.gender, u.date_of_birth, u.relationship_status, dma.dma_region_code, dma.dma_region, fan_user.user_id as fan_user_id, u.fb_friend_count as friend_count, sum(t.num_views) as `views`, sum(t.num_claims) as `claims`, sum(t.num_redeems) as `redeems`, sum(t.num_shares) as `shares`, sum(t.num_referrals) as `referrals`, sum(t.num_referral_redeems) as `referral_redeems`
			from (
			select iv.user_id, count(id) as num_views, 0 as num_claims, 0 as num_redeems, 0 as num_shares, 0 as num_referrals, 0 as num_referral_redeems from items_views iv where iv.company_id = '$company_id' $items_clause_views group by iv.user_id
			union all
			select ui.user_id, 0 as num_views, count(ui.id) as num_claims, 0 as num_redeems, 0 as num_shares, 0 as num_referrals, 0 as num_referral_redeems from user_items ui where ui.company_id = '$company_id' $items_clause_claims and ui.user_id > 0 and ui.date_claimed is not null group by ui.user_id
			union all 
			select r.user_id, 0 as num_views, 0 as num_claims, 0 as num_redeems, count(r.id) as num_shares, 0 as num_referrals, 0 as num_referral_redeems from referrals r where r.company_id = '$company_id' $items_clause_shares and r.user_id > 0 group by r.user_id
			union all
			select ui.user_id, 0 as num_views, 0 as num_claims, count(ui.id) as num_redeems, 0 as num_shares, 0 as num_referrals, 0 as num_referral_redeems from user_items ui where ui.company_id = '$company_id' $items_clause_redeems and ui.user_id > 0 and ui.date_redeemed is not null group by ui.user_id
			union all
			select ui.user_id, 0 as num_views, 0 as num_claims, 0 as num_redeems, 0 as num_shares, count(ui.id) as num_referrals, 0 as num_referral_redeems from user_items ui where ui.company_id = '$company_id' $items_clause_referrals and ui.user_id > 0 and ui.date_claimed is not null and ui.referral_id > 0 group by ui.user_id
			union all
			select ui.user_id, 0 as num_views, 0 as num_claims, 0 as num_redeems, 0 as num_shares, 0 as num_referrals, count(ui.id) as num_referral_redeems from user_items ui where ui.company_id = '$company_id' $items_clause_redeem_referrals and ui.user_id > 0 and ui.date_redeemed is not null and ui.referral_id > 0 group by ui.user_id
			) as t
			inner join users u on t.user_id = u.id
			 inner join fb_locations_dma_regions fb_dma on u.facebook_location_id = fb_dma.fb_location_id 
			 inner join cities_dma_regions dma on fb_dma.city_dma_region_id = dma.id
			 left join (
			 	select distinct(user_id) as user_id from user_fb_likes where fb_like_id = '$company_fb_like_id'
			 ) as fan_user on u.id = fan_user.user_id
			$where_clause
			and (u.date_of_birth is not null and date(u.date_of_birth) != '0000-00-00')
			and (u.relationship_status is not null and u.relationship_status != '')
			group by t.user_id";
			// error_log("SQL in CSAPI::refreshKeyAdvocates() : " .$sql);
			
			$rows = BasicDataObject::getDataTable($sql);
			// error_log("CSAPI::refreshKeyAdvocates(): time 6: " . time());
			$insert_sql = "";
			foreach($rows as $i => $row)
			{
				$user_id		= $row['user_id'];
				$facebook_id	= $row['facebook_id'];
				$email			= $row['email'];
				$entrant_name	= Database::mysqli_real_escape_string($row['firstname'] . ' ' . $row['lastname']);
				$gender			= strtoupper($row['gender']);
				$gender			= $gender == 'F' ? 'Female' : ($gender == 'M' ? 'Male' : '-');
				$date_of_birth	= $row['date_of_birth'];
				$age = Common::calculateAgeFromBirthday($date_of_birth);

				$relationship_status	= Database::mysqli_real_escape_string($row['relationship_status']);
				$dma_code		= Database::mysqli_real_escape_string($row['dma_region_code']);
				$dma_region		= Database::mysqli_real_escape_string($row['dma_region']);
				$friend_count 	= $row['friend_count'];
				$interest		= '';
				$view_count = $row['views']+ 0;
				$claim_count = $row['claims'] + 0;
				$redeem_count = $row['redeems'] + 0;
				$share_count = $row['shares'] + 0;
				$refer_count = $row['referrals'] + 0;
				$refer_redeem_count = $row['referral_redeems'] + 0;


				// Calculate Advocacy score
				$referral_redemptions = !empty($avg_referral_redemptions) ? min(round(($refer_redeem_count * 100) / $avg_referral_redemptions, 2), 100) : 0;
				$individual_redemptions = !empty($claim_count) ? min(round(($redeem_count * 100) / $claim_count, 2), 100) : 0;
				$referral_claims = !empty($avg_referral_claims) ? min(round(($refer_count * 100) / $avg_referral_claims, 2), 100) : 0;
				$individual_claims = min($claim_count, 100);
				$shares = min($share_count, 100);
				$fan_of_company = !empty($row['fan_user_id']) ? 1 : 0;
				$friend_reach = !empty($avg_friends) ? min($friend_count / $avg_friends, 100) : 0;
				
				$advocacy_score = 
					($referral_redemptions * 0.25 + 
					$individual_redemptions * 0.15 + 
					$referral_claims * 0.15 + 
					$individual_claims * 0.13 + 
					$shares * 0.12 +
					$fan_of_company * 0.125 + 
					$friend_reach * 0.075) / 1;
				
				
				if(isset($user_fb_likes[$user_id]))
				{
					$arr_user_fb_likes = $user_fb_likes[$user_id];
					arsort($arr_user_fb_likes);
					$limit_user_interests = 3;
					$arr_user_fb_likes = array_values(array_keys($arr_user_fb_likes));
					$arr_user_fb_likes = array_slice($arr_user_fb_likes, 0, $limit_user_interests);
					$user_interests = array();
					$user_interests_ids = array();
					foreach($arr_user_fb_likes as $interest_id)
					{
						$user_interests[$arr_fb_like_ids[$interest_id]['interest_id']] = $arr_fb_like_ids[$interest_id]['interest_name'];
						// $user_interests_ids[] = $arr_fb_like_ids[$interest_id]['interest_id'];
					}
					
					// $user_interests_separator = '~~';
					// $str_user_fb_interests = Database::mysqli_real_escape_string(implode($user_interests_separator, $user_interests));
					$str_user_fb_interests = Database::mysqli_real_escape_string(json_encode($user_interests));
					$interest = Database::mysqli_real_escape_string(implode(',', $user_interests_ids));
					
					if(!empty($insert_sql))
						$insert_sql .= ", ";
					
					if(empty($item_id))
					{
						$item_id_val = 'NULL';
						$delivery_method_val = 'NULL';
						$deal_id_val = 'NULL';
					}
					else
					{
						$item_id_val = "'" . $item_id . "'";
						$delivery_method_val =  "'" . $delivery_method . "'";
						$deal_id_val =  "'" . $deal_id . "'";
					}
					
					$insert_sql .= "($company_id, $item_id_val, $deal_id_val, $delivery_method_val, '$dma_code', '$dma_region', '$relationship_status', '$entrant_name', '$email', '$facebook_id', '$gender', '$age', '$str_user_fb_interests', '$advocacy_score')";
				}
			}
			// error_log("CSAPI::refreshKeyAdvocates(): time 7: " . time());
			
			// DELETE ALL DATA BEFORE INSERTING
			// Database::mysqli_query("delete from ssp_key_advocates where company_id = '$company_id'");
			
			$sql = "delete from ssp_key_advocates where company_id = '$company_id'";
			$sql .= empty($item_id) ? " and item_id is null" : " and item_id = '$item_id'";
			// error_log("delete sql in CSAPI::refreshKeyAdvocates() " . $sql);
			Database::mysqli_query($sql);
					
			if(!empty($insert_sql))
			{
				$insert_sql = "insert into `ssp_key_advocates` (company_id, item_id, deal_id, delivery_method, dma_code, dma, relationship, username, email, facebook_id, gender, age, top_likes, advocacy_score) values " . $insert_sql;
				Database::mysqli_query($insert_sql);
				// error_log("insert sql in CSAPI::refreshKeyAdvocates() " . $insert_sql);
			}

			// error_log("CSAPI::refreshKeyAdvocates(): time 8: " . time());
			
			if(!empty($delay))
				sleep($delay);
		}	
		sleep(2);
		Database::mysqli_query("drop table if exists `$tmp_table_name`");
	}
	
	public function getKeyAdvocates($params)
	{
		$response = array();
		$response['graph_data'] = array();
		// error_log("params in getKeyAdvocates: " . var_export($params, true));
		$company_id = !empty($params['companyId']) ? $params['companyId'] : (!empty($params['companyid']) ? $params['companyid'] : '');
		$campaign_ids = !empty($params['campaignId']) ? $params['campaignId'] : (!empty($params['campaignid']) ? $params['campaignid'] : '');
	
		$limit = !empty($params['limit']) ? $params['limit']: 20;
		$sort = !empty($params['sort']) ? $params['sort'] : 'advocacy_score';
		$sort_order = !empty($params['sortOrder']) ? $params['sortOrder'] : 'desc';
		$rel_status_id = !empty($params['relStatusId']) ? $params['relStatusId'] : (!empty($params['relstatusid']) ? $params['relstatusid'] : '');
		$location_id = !empty($params['locationId']) ? $params['locationId'] : (!empty($params['locationid']) ? $params['locationid'] : '');
		$age_range_id = !empty($params['ageRangeId']) ? $params['ageRangeId'] : (!empty($params['agerangeid']) ? $params['agerangeid'] : '');
		$interest_id = !empty($params['interestId']) ? $params['interestId'] : (!empty($params['interestid']) ? $params['interestid'] : '');
		$dma_code = !empty($params['dmaCode']) ? $params['dmaCode'] : (!empty($params['dmacode']) ? $params['dmacode'] : '');
		
		
		$sql = "select * from ssp_key_advocates";
		if(!empty($campaign_ids))
			$sql .= " where company_id = '$company_id' and item_id in ($campaign_ids)";
		else
			$sql .= " where company_id = '$company_id' and item_id is null";
		
		if(!empty($rel_status_id))
		{
			$sql .= " and relationship like '%$rel_status_id%'";
		}
		
		if(!empty($interest_id))
		{
			$sql .= " and top_likes like '%" . $interest_id . "%'";
		}
		
		if(!empty($dma_code))
		{
			$sql .= " and dma_code = '" . $dma_code . "'";
		}
		
		$sql .= " order by $sort $sort_order limit $limit";
		// error_log("SQL in getKeyAdvocates: $sql");
		$rows = BasicDataObject::getDataTable($sql);
		foreach($rows as $i => $row)
		{
			$tmp_data = array();
			$tmp_data['entrant_id'] = $row['user_id'];
			$tmp_data['entrant_fb_id'] = $row['facebook_id'];
			$tmp_data['name'] = $row['username'];
			$tmp_data['email'] = $row['email'];
			// $tmp_data['dob'] = $row['date_of_birth'];
			$tmp_data['age'] = $row['age'];
			$tmp_data['sex'] = $row['gender'];
			$tmp_data['rel_status'] = $row['relationship'];
			$tmp_data['as_score'] = $row['advocacy_score'];
			$tmp_data['top_interests'] = json_decode($row['top_likes'], true); // array($row['interest'] => $row['top_likes']);
			$response['graph_data'][] = $tmp_data;
		}
		
		$response['settings'] = $params;
		return $response;
	}
	
	public function getWeeklyCampaignActivity($params)
	{
		
		$day_range = 7;
		$end_date = Common::getDBCurrentDate(0, 'day', '%Y-%m-%d');
		$start_date = Common::getDBCurrentDate(-($day_range - 1), 'day', '%Y-%m-%d');
		
		
		$php_date_format = 'Y-m-d';
		$mysql_date_format = '%Y-%m-%d';
		$tmp_date = $start_date; // Common::getDBCurrentDate(-($day_range - 1), 'day', $mysql_date_format);
		$int_time_interval = 86400;
		$time_interval = new DateInterval('PT' . $int_time_interval . 'S');
		
		
		$params['timeIntervalSeconds'] = $int_time_interval;
		$params['showDateLabels'] = 1;
		$params['useDate'] = 1;
		$params['startDate'] = $start_date;
		$params['endDate'] = $end_date;
		$response = $this->getCampaignActivity($params);
		// error_log("response in CSAPI::getWeeklyCampaignActivity(): " . var_export($response, true));
		
		$weekly_activity_data = array(
			'series' => array(
				array(
					'name' => "Views",
					'data' =>  array()
				), 
				array(
					'name' => "Claims",
					'data' =>  array()
				), 
			),
			'categories' => array()
		);
		
		for($i = 0; $i < $day_range; $i++)
		{			
			$dt = DateTime::CreateFromFormat($php_date_format, $tmp_date);
			if(!$dt)
				error_log("failed to created date object! using format $php_date_format having date $tmp_date");
			// error_log("dt in CSAPI::getWeeklyCampaignActivity(): " . var_export($dt, true));
			// error_log("tmp_date: " . $tmp_date);
			$day_of_week = date_format($dt, 'D');
			$weekly_activity_data['categories'][] = $day_of_week;
			
			$views = isset($response['graph_data']['view']['data'][$tmp_date]) ? $response['graph_data']['view']['data'][$tmp_date] + 0 : 0;
			$claims = isset($response['graph_data']['claim']['data'][$tmp_date]) ? $response['graph_data']['claim']['data'][$tmp_date] + 0 : 0;
			
			$weekly_activity_data['series'][0]['data'][] = $views;
			$weekly_activity_data['series'][1]['data'][] = $claims;
			
			$tmp_date = date_format($dt->add($time_interval), $php_date_format);
			
			$total_views += $views;
			$total_claims += $claims;
		}
		$weekly_activity_data['series'][0]['name'] = "Views (Total: $total_views)";
		$weekly_activity_data['series'][1]['name'] = "Claims (Total: $total_claims)";
		$weekly_activity_data['totals']['views'] = $total_views;
		$weekly_activity_data['totals']['claims'] = $total_claims;
		
		// error_log("weekly_activity_data: " . var_export($weekly_activity_data, true));
		return $weekly_activity_data;
		
	}
	
	public static function getCampaignActivityTypeIndex($response, $activity_type)
	{
		foreach($response['graph_data'] as $index => $tmp)
		{
			if($tmp['activityType'] == $activity_type)
			{
				return $index;
			}
		}
		return -1;
	}
}
?>