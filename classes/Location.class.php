<?php

/**
* UserFavoriteStores Class
*/
class Location extends BasicDataObject
{
	var $id;
	var $companies_id;
	var $name;
	var $address1;
	var $address2;
	var $city;
	var $state;
	var $zip;
	var $country;
	var $address_lat;
	var $address_lon;
	var $created;
	var $modified;
	var $can_spam_address;

	function __construct($id = null)
	{
		if(!empty($id))
		{
			$id = Database::mysqli_real_escape_string($id);
			$sql = "SELECT * FROM locations WHERE `id` = '$id'";
			$result = Database::mysqli_query($sql);
			if($result)
			{
				$row = Database::mysqli_fetch_assoc($result);
				$this->id = $row['id'];
				$this->companies_id = $row['companies_id'];
				$this->name = $row['name'];
				$this->address1 = $row['address1'];
				$this->address2 = $row['address2'];
				$this->city = $row['city'];
				$this->state = $row['state'];
				$this->zip = $row['zip'];
				$this->country = $row['country'];
				$this->address_lat = $row['address_lat'];
				$this->address_lon = $row['address_lon'];
				$this->created = $row['created'];
				$this->modified = $row['modified'];

			}
		}
	}

	public static function AddLocation($bus_name, $bus_address1, $bus_address2, $bus_city, $bus_state, $bus_zip, $bus_country){


	}
	
	public static function get_locations_by_company_id($company_id)
	{
		$result = array();
		
		$sql = "select * from locations where companies_id = '".Database::mysqli_real_escape_string($company_id)."'";
		// error_log("SQL in Location::get_locations_by_company_id(): " . $sql);
		$rs = Database::mysqli_query($sql);
		
		if($rs && Database::mysqli_num_rows($rs) > 0)
			while($row = Database::mysqli_fetch_assoc($rs))
				$result[] = $row; 
		
		Database::mysqli_free_result($rs);
		return $result;
	}


	public static function get_dealbuilder_company_locations($company_id)
	{
		$sql = "select id, name from locations where companies_id = '".Database::mysqli_real_escape_string($company_id)."' order by name";
		return BasicDataObject::getDataTable($sql);
	}
	
	public static function get_default_location_info($company_id)
	{
		$location = new Location();
		$location->Select("companies_id='".Database::mysqli_real_escape_string($company_id)."'");
		return $location;
	}
	
	public static function validateZipCode($zip)
	{
		// $sql = "select id, zip from locations where zip = '$zip'";
		$sql = "select id, zip from zcta5 where zip = '$zip'";
		$row = BasicDataObject::getDataRow($sql);
		// error_log("SQL in validateZipCode: " . $sql);
		return !empty($row['id']);
	}
	
	public static function getRunningDealsByZipcode($zip, $company_id, $delivery_method)
	{
	
		$sql = "select i.*
				from campaigns_locations cl
				inner join items i on cl.campaigns_id = i.campaign_id
				inner join locations l on cl.locations_id = l.id
				where i.manufacturer_id = '$company_id' 
				and i.status = 'running'
				and ((l.zip = '$zip' and cl.is_backup_deal = 0) or (l.zip != '$zip' and cl.is_backup_deal = 1))";
		if(!empty($delivery_method))
			$sql .= " and i.delivery_method in ($delivery_method)";

		// error_log("SQL in getRunningDealsByZipcode: " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		return $rows;
	}
	
	public static function getRunningDealsByCity($city, $company_id, $delivery_method)
	{
		$city = strtolower($city);
		
		// To be customized later
		$sql = "select i.*
				from campaigns_locations cl
				inner join items i on cl.campaigns_id = i.campaign_id
				inner join locations l on cl.locations_id = l.id
				where i.manufacturer_id = '$company_id' 
				and i.status = 'running'
				and ((lcase(l.city) = '$city' and cl.is_backup_deal = 0) or (lcase(l.city) != '$city' and cl.is_backup_deal = 1))";
		if(!empty($delivery_method))
			$sql .= " and i.delivery_method in ($delivery_method)";
		
		
		
		$sql = "select i.*
				from campaigns_locations cl
				inner join items i on cl.campaigns_id = i.campaign_id
				inner join locations l on cl.locations_id = l.id
				where i.manufacturer_id = '$company_id' 
				and i.status = 'running'
				and ((lcase(l.state) = '$city' and cl.is_backup_deal = 0) or (lcase(l.state) != '$city' and cl.is_backup_deal = 1))";
		if(!empty($delivery_method))
			$sql .= " and i.delivery_method in ($delivery_method)";
			
			
		// error_log("SQL in getRunningDealsByCity: " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		return $rows;
	}
	
	public static function getRunningDealsByDMA($dma_code, $company_id)
	{
		$city = strtolower($city);
		$sql = "select i.*
				from campaigns_dma_locations cd
				inner join items i on cd.campaigns_id = i.campaign_id
				where i.manufacturer_id = '$company_id' 
				and i.status = 'running'
				and (cd.dma_code = '$dma_code' and cl.is_backup_deal = 0) or (cd.dma_code != '$dma_code' and cl.is_backup_deal = 1))";
		// error_log("SQL in getRunningDealsByCity: " . $sql);
		$rows = BasicDataObject::getDataTable($sql);
		return $rows;
	}
	
	public static function getCityByFacebookLocationId($fb_location_id)
	{
		// To be customized later
		$sql = "select lcase(city) as city
		from fb_locations_dma_regions fbl
		inner join cities_dma_regions c on fbl.city_dma_region_id = c.id
		where fbl.fb_location_id = '$fb_location_id'";
		
		$sql = "select lcase(state_code) as city
		from fb_locations_dma_regions fbl
		inner join cities_dma_regions c on fbl.city_dma_region_id = c.id
		where fbl.fb_location_id = '$fb_location_id'";
		
		// error_log("SQL in getCityByFacebookLocationId: " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row['city']) ? $row['city'] : '';
	}
	
	public static function getAreaByFacebookLocationId($fb_location_id, $area_type = 'state')
	{
		if($area_type == 'state')
			$area_type = 'state_code';
			
		// To be customized later		
		$sql = "select lcase($area_type) as `area_name`
		from fb_locations_dma_regions fbl
		inner join cities_dma_regions c on fbl.city_dma_region_id = c.id
		where fbl.fb_location_id = '$fb_location_id'";
		
		// error_log("SQL in getCityByFacebookLocationId: " . $sql);
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row['area_name']) ? $row['area_name'] : '';
	}
	
	public static function getAllCities($company_id)
	{
	
		// To be customized later
		$sql = "select distinct(l.city) as city from locations l ";
		$sql .= " where l.companies_id = '$company_id'";
		
		$sql = "select distinct(l.state) as city from locations l ";
		$sql .= " where l.companies_id = '$company_id'";
		$rows = BasicDataObject::getDataTable($sql);
		
		return $rows;
	}
	
	public static function getFormattedAddress($location)
	{
		$address = $location['name'];
		if(!empty($location['address1']))
			$address .= ", " . $location['address1'];
		
		if(!empty($location['address2']))
			$address .= ", " . $location['address2'];
		
		if(!empty($location['city']))
			$address .= ", " . $location['city'];
			
		return $address;
	}

}

?>