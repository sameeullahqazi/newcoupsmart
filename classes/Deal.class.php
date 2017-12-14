<?php

require_once(dirname(__DIR__) . '/classes/Common.class.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');
/**
* Deal Class
*/
class Deal extends BasicDataObject
{
	// Database fields as class attributes
	var $company_id;
	var $deal_name;
	var $cm_list_id;
	var $cm_list_name;

	
	function __construct($id = null, $read_only_mode = true){
		if(!empty($id))
		{
			$id = "id='" . $id . "'";
			$this->Select($id, $read_only_mode);
		}
	}
	
	public static function getCompanyDeals($company_id = null)
	{
		$sql = "select * from deals";
		if(!empty($company_id))
			$sql .= " where company_id = '$company_id'";
		
		$rows = BasicDataObject::getDataTable($sql);
		return $rows;
	}

}

?>