<?php

// require_once(dirname(__DIR__) . '/classes/BasicDataObject.class.php');
require_once(dirname(__DIR__) . '/includes/app_config.php');

/*
*SGS Discount Class
*
*/
class SGS_Discount extends BasicDataObject
{
	var $id;
	var $smart_deal_id;
	var $company_id;
	
	var $code;
	var $name;
	var $type;
	
	var $begin_date;
	var $end_date;
	
	var $amount;
	var $allowed_uses;
	var $conditions; /*conditions stores either the item to be discounted, or
	bogo - whatever it needs to be depending on the type*/
	
	public static $DISCOUNT_TYPES = 
		array(     									//	these are conditions - all array keys
			0 => 'Percent Off Products',			//	item_id, quantity, sgs_item_option_data_id 
													//!the sgs_item_option_data_id is optional!
			1 => 'Percent Off Cart',				//	N/A
			2 => 'Amount Off Product', 				//	item_id, quantity, sgs_item_option_data_id 
													//!the sgs_item_option_data_id is optional!
			3 => 'Amount Off Cart',					//	N/A
			4 => 'Buy X Get Y', 					//	buy=>{item_id, quantity, sgs_item_option_data_id}, get=>{item_id, quantity, sgs_item_option_data_id} !the sgs_item_option_data_id is optional!
			5 => 'If Cart Total Get Percent Off', 	//	cart_total
			6 => 'If Cart Total Get Amount Off', 	//	cart_total
			7 => 'Free Shipping on Amount' 			//	if sub_total exceeds amount, then shipping gets free.
	);
	
	public static $COUPON_CODE_LENGTH = 30;
	
	public static $CHECK_WITH_USER_ID = "uj4LFhAJv5nPNCdedR0K";
	public static $CHECK_WITHOUT_FBID = "MyeaRd1na5FLhFyUMDQH";
	
	function __construct($id = null)
	{
		if(!empty($id)){
			$id = Database::mysqli_real_escape_string($id);
			$this->Select("id='".$id."'");
		}
		return $this;
	}
	
	//return associated discount, if not there, return false
	static public function constructByCode($code)
	{
		$return = false;
		if(!empty($code)){
			$id = SGS_Discount::getIdByCode($code);
			
			if(!empty($id))
				$return = new SGS_Discount($id);
		}
		
		return $return;
	}
	
	static public function getIdByCode($code)
	{
		$sql = '
			select id
			from sgs_discounts
			where code = "' . Database::mysqli_real_escape_string($code) . '";
		';
		
		$discount = BasicDataObject::getDataRow($sql);
		return !empty($discount['id']) ? $discount['id'] : '';
	}
	
	public function getBeginTimestamp()
	{
		if(empty($this->begin_date)){
			$timestamp = 0;
		} else {
			$timestamp = strtotime($this->begin_date);
		}
		
		return $timestamp;
	}
	
	public function getEndTimestamp()
	{
		if(empty($this->end_date)){
			$timestamp = PHP_INT_MAX;
		} else {
			$timestamp = strtotime($this->end_date);
		}
		
		return $timestamp;
	}
	
	//look at $DISCOUNT_TYPES for $conditions format
	public static function createDiscount($smart_deal_id, $company_id, $code, $name, $type, $begin_date, $end_date, $amount, $allowed_uses, $conditions){
		$errors = array();
	
		$discount = new SGS_Discount();
		
		$discount->company_id = $company_id;
		
		if($code == 'random' || $code == null){
			$discount->code = SGS_Discount::getRandomCode();
		} else {
			$discount->code = $code;
		}
		
		$discount->name = $name;
		
		// if(array_key_exists($type, SGS_Discount::$DISCOUNT_TYPES)){
		if(isset(SGS_Discount::$DISCOUNT_TYPES[$type])){
			$discount->type = $type;
		} else {
			$errors['type'] = 'must include correct type';
		}
		
		if($begin_date == 'now' || $begin_date == null){
			$begin_date = null;
		} else {
			$discount->begin_date = $begin_date;
		}
		
		if($end_date == 'never' || $end_date == null){
			$end_date = null;
		} else {
			$discount->end_date = $end_date;
		}
		
		$discount->amount = $amount;
		$discount->allowed_use = $allowed_uses;
		$discount->conditions = serialize($conditions);
		
		if(empty($errors)){
			// $discount->save();
			$discount->Insert();
		}
		
		return empty($errors) ? $discount : $errors;
	}
	
	public static function getRandomCode()
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < SGS_Discount::$COUPON_CODE_LENGTH; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}
	
	public function checkUsage($fb_id, $user_id)
	{
		if($this->getUses($fb_id, $user_id) >= $this->allowed_uses){
			$allowed['allowed'] = false;
			$allowed['error'] = 'Coupon Use Exceeded';
		} else {
			$allowed['allowed'] = true;
		}
		
		return $allowed;
	}
	
	public function checkIfCanApplyWithUserId($total_items_price, $items, $user_id, $comp_id)
	{
		return $this->checkIfCanApply($total_items_price, $items, SGS_Discount::$CHECK_WITH_USER_ID, $comp_id, $user_id);
	}
	
	public function checkIfCanApplyWithoutFbID($total_items_price, $items, $comp_id)
	{
		return $this->checkIfCanApply($total_items_price, $items, SGS_Discount::$CHECK_WITHOUT_FBID, $comp_id);
	}
	
	//check if a this discount can be applied to a cart. returns if it's allowed,
	//how much the discount is if it's allowed, and the error if not.
	public function checkIfCanApply($total_items_price, $items, $fb_id, $comp_id, $user_id = null)
	{
		$allowed = array();
		
		//check if coupon exists
		if($this->id == null){
			$allowed['allowed'] = false;
			$allowed['error'] = 'Coupon Use Not Allowed - Coupon Does Not Exist';
			return $allowed;
		}

		if($this->company_id != $comp_id){
			$allowed['allowed'] = false;
			$allowed['error'] = 'Coupon Use Not Allowed - Wrong Store';
			return $allowed;
		}
		
		/****************
		Check Usage Below
		****************/
		
		$allowed['checked'] = '1';
		
		//If this is empty, it's trying to run this with an empty fb_id. Something is up.
		if(empty($fb_id)){
			$allowed['allowed'] = false;
			$allowed['error'] = "Facebook Error. Please reload page and try again.";
		} 
		
		//check if usage is too much using user id
		else if($fb_id == SGS_Discount::$CHECK_WITH_USER_ID){
			$allowed = $this->checkUsage(null, $user_id);
			if(!$allowed['allowed']){
				return $allowed;
			}
		}
		
		//check if usage is too much using fb id
		else if($fb_id != SGS_Discount::$CHECK_WITHOUT_FBID){
			$allowed = $this->checkUsage($fb_id, $user_id);
			if(!$allowed['allowed']){
				return $allowed;
			}
		}
		
		//cant check, continue
		else {
			$allowed['checked'] = '0';
		}
		
		/**************
		End Check Usage
		**************/
		
		//check if within date range of use
		$now = strtotime(date("Y-m-d H:i:s"));
		$begin = $this->getBeginTimestamp();
		$end = $this->getEndTimestamp();
		
		if( ($now < $begin) || ($now > $end) ){
			$allowed['allowed'] = false;
			$allowed['error'] = 'Discount Use Not Allowed - Expired';
			return $allowed;
		}
		
		$arr_conditions = unserialize($this->conditions);
		// error_log('arr_conditions: ' . var_export($arr_conditions, true));
		
		$items_quants_by_id = array();
		$items_options_quants_by_id = array();
		foreach($items as $item)
		{
			// if( !array_key_exists($item['id'], $items_quants_by_id) )
			if( !isset($items_quants_by_id[$item['id']]) )
				$items_quants_by_id[$item['id']] = 0;
			$items_quants_by_id[$item['id']]++;
			
			if(!empty($item['sgs_item_option_data_id']))
			{
				if( !isset($items_options_quants_by_id[$item['id']][$item['sgs_item_option_data_id']] ))
					$items_options_quants_by_id[$item['id']][$item['sgs_item_option_data_id']] = 0;
					
				$items_options_quants_by_id[$item['id']][$item['sgs_item_option_data_id']]++;
			}
		}
 
		switch($this->type){
		
			case '0':
				if($items_quants_by_id[$arr_conditions['item_id']] >= $arr_conditions['quantity']){
					$cur_item = new SGS_Item($arr_conditions['item_id']);
					$allowed['allowed'] = true;
					$allowed['discount'] = (floatval($cur_item->price) * floatval($this->amount) * 0.01 * floatval($arr_conditions['quantity']));
				} else {
					$allowed['allowed'] = false;
					$allowed['error'] = 'Coupon Requirements Not Met';
				}
				break;
			
			case '1':
				$allowed['allowed'] = true;
				$allowed['discount'] = (floatval($total_items_price) * floatval($this->amount) * 0.01);
				break;
			
			case '2':
				if($items_quants_by_id[$arr_conditions['item_id']] >= $arr_conditions['quantity']){
					
					if(!empty($arr_conditions['sgs_item_option_data_id']))
					{
						if($items_options_quants_by_id[$arr_conditions['item_id']][$arr_conditions['sgs_item_option_data_id']] >= $arr_conditions['quantity'])
						{
							$allowed['allowed'] = true;
							$allowed['discount'] = floatval($this->amount);
						}
						else
						{
							$allowed['allowed'] = false;
							$allowed['error'] = 'Coupon Requirements Not Met';
						}
					}
					else
					{
				
						$allowed['allowed'] = true;
						$allowed['discount'] = floatval($this->amount);
					}
				} else {
					$allowed['allowed'] = false;
					$allowed['error'] = 'Coupon Requirements Not Met';
				}
				break;
				
			case '3':
				$allowed['allowed'] = true;
				$allowed['discount'] = floatval($this->amount);
				break;
			
			case '4':
				$buy = $arr_conditions['buy'];
				$get = $arr_conditions['get'];
				
				if($items_quants_by_id[$buy['item_id']] >= $buy['quantity']){
					$cur_item = new SGS_Item($get['item_id']);
					
					if($items_quants_by_id[$get['item_id']] >= $get['quantity']){
						$allowed['allowed'] = true;
						$allowed['discount'] = (floatval($cur_item->price) * floatval($get['quantity']));
					} else {
						$allowed['allowed'] = false;
						$allowed['error'] = 'Add All Items Required For Coupon';
					}
				} else {
					$allowed['allowed'] = false;
					$allowed['error'] = 'Coupon Requirements Not Met';
				}
				break;
			
			case '5':
				if($total_items_price >= $arr_conditions['cart_total']){
					$allowed['allowed'] = true;
					$allowed['discount'] = (floatval($total_items_price) * floatval($this->amount) * 0.01);
				} else {
					$allowed['allowed'] = false;
					$allowed['error'] = 'Coupon Requirements Not Met';
				}
				break;
			
			case '6':
				if($total_items_price >= $arr_conditions['cart_total']){
					$allowed['allowed'] = true;
					$allowed['discount'] = floatval($this->amount);
				} else {
					$allowed['allowed'] = false;
					$allowed['error'] = 'Coupon Requirements Not Met';
				}
				break;
		}
		
		//ensure that discount isn't more than cart total
		if($allowed['allowed']){
			if($allowed['discount'] > $total_items_price){
				$allowed['discount'] = $total_items_price;
			}
			
			//need name of discount applied
			$allowed['name'] = $this->name;
			$allowed['id'] = $this->id;
		}
	
		return $allowed;
	}
	
	public function getUses($fb_id, $user_id = null)
	{
		$user_fb = User::findByFacebookId($fb_id);
		
		
		$sql = 'select count(distinct id) as uses
				from sgs_user_discounts
				where sgs_discount_id= "' . $this->id . '"
				AND ( fb_id = "'. Database::mysqli_real_escape_string($fb_id) .'"
					OR user_id = "'. Database::mysqli_real_escape_string($user_id) .'");';
		
		$uses = BasicDataObject::getDataRow($sql);
		// error_log(var_export($uses, true));
		// error_log($uses['count(fb_id)']);
		return $uses['uses'];
	}
	
	public static function catchUsage($fb_id, $discount_id, $sgs_order_id, $user_id = null)
	{
		if(empty($user_id) && !empty($fb_id)){
			$user_id = User::getUserIdByFacebookId($fb_id);
		}
		
		if(empty($user_id) && empty($fb_id)){
			error_log('SGS_Discount catchUsage() Error: Trying to input data without User ID and without Facebook ID at line ' . __LINE__);
		}
		
		$sql = "
			insert into sgs_user_discounts (`fb_id`, `sgs_discount_id`, `sgs_order_id`, `date`, `user_id`)
			values ('" . Database::mysqli_real_escape_string($fb_id) . "', " . Database::mysqli_real_escape_string($discount_id) . ", " . Database::mysqli_real_escape_string($sgs_order_id) . ", NOW(), " . Database::mysqli_real_escape_string($user_id) . ");
		";
		
		if(!Database::mysqli_query($sql)){
			error_log("Insert SQL Error in SGS_Discount::catchUsage: " . Database::mysqli_error() . " \nSQL:\n" . $sql);
		}
	}
	
	public static function getReservedCode($session_id, $company_id)
	{
		$sql = "
			select discount_code as code
			from sgs_session_discounts
			where session_id = '" . Database::mysqli_real_escape_string($session_id) . "'
			and company_id = '" . Database::mysqli_real_escape_string($company_id) . "'
			and status = '1'
			ORDER BY id DESC LIMIT 0, 1;
		";
		
		$row = BasicDataObject::getDataRow($sql);
		
		if(!empty($row)){
			return $row['code'];
		}
		
		else {
			return null;
		}
	}
	
	public static function addReservedDiscount($session_id, $discount)
	{
		$sql = "
			INSERT INTO sgs_session_discounts
			(session_id, company_id, discount_code)
			values ('" . Database::mysqli_real_escape_string($session_id) . "', '" . Database::mysqli_real_escape_string($discount->company_id) . "', '" . Database::mysqli_real_escape_string($discount->code) . "');
		";
		
		SGS_Item::removeReservedDiscounts($discount->company_id);
		
		if(!Database::mysqli_query($sql)){
			error_log('Error adding Reserved Discount in SGS_Discount line ' . __LINE__ . ' SQL: ' . $sql);
		}
	}
	
	public static function removeReservedDiscounts($company_id)
	{
		$sql_discount = 'update `sgs_session_discounts` 
						set `status`="0" 
						where `status`="1" 
						and `session_id` = "' . session_id() . '"
						and `company_id` = "' . Database::mysqli_real_escape_string($company_id) . '";';
		//error_log($sql_discount);
		if(!Database::mysqli_query($sql_discount))
			error_log("Delete error in SGS_Item::expireItemsReservedSession(): ".Database::mysqli_error()."\nSQL: " . $sql_discount);
	}
	
	public static function hasFreeShipping($sub_total, $company_id)
	{
		$sql = "select * from sgs_discounts where `type` = '7' and company_id = '$company_id' and `amount` < '$sub_total'";
		$row = BasicDataObject::getDataRow($sql);
		return !empty($row['id']) ? true : false;
	}
	
}

?>