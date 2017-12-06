<?php
	class BasicDataObject
	{
		var $id;
		var $obj_read_only = false;

		public static $plurals = array(
			'allergy' => 'allergies',
			'company' => 'companies',
			'sgs_item_options_data' => 'sgs_item_options_data',
			'sgs_item_options' => 'sgs_item_options',
			'sgs_item_option_values' => 'sgs_item_option_values',
			'item_allergy' => 'item_allergies',
			'item_category' => 'item_categories',
			'item_sent' => 'items_sent',
			'product_category' => 'product_categories',
			'campaignsharingimage' => 'campaign_sharing_images',
			'campaignstatustransitions' => 'campaign_status_transitions',
			'cacheuseragentstring'	=> 'cache_useragent_strings',
			'cacheimage'	=> 'cache_images',
			'orderitem' 	=> 'users_orders_items',
			'orderfulfillment' => 'order_fulfillment',
			'countmeinsignup' => 'countmein_signups',
			'voucherlayout' =>	'voucher_layouts',
			'voucherlayoutpart' => 'voucher_layout_parts',
			'usercompanyinteraction' => 'user_company_interactions',
			'useractivitylog' => 'user_activity_log',
			'emailedcoupon' => 'emailed_coupons',
			'dealbuilderformdata' => 'dealbuilder_form_data',
			'clickreferral' => 'click_referrals',
			'useractionprocesslog' => 'user_action_process_log',
			'bookingemail' => 'booking_emails',
			'smartemail' => 'smart_emails',
			'smartbookingorder' => 'smart_booking_orders',
			'adminmarketerlog' => 'marketer_admin_log',
			'userorder' => 'users_orders',
			'userpayment' => 'users_payments',
			'companiesservice' => 'companies_services',
			'authnetuserprofile' => 'authnet_user_profiles',
			'authnetcompaniespaymentprofile' => 'authnet_companies_payment_profiles',
			'usernodeal' => 'users_nodeals',
			'usernotification' => 'users_notifications',
			'userpermissions' => 'users_permissions',
			'paymentrequest' => 'payment_requests',
			'emailtemplates' => 'email_templates',
			'appconfig' => 'app_config',
		);

		var $salt = '9201340522657012';

		private static function plural($singular) {
			if (isset(BasicDataObject::$plurals[strtolower($singular)])) {
				return BasicDataObject::$plurals[strtolower($singular)];
			} else {
				return $singular.'s';
			}
		}

		// Fills the caller object with the data of the first row matching the criteria passed
		public function Select($criteria, $read_only_mode = true)
		{
			//error_log("criteria: " . var_export($criteria, true));
			// Set read only mode
			$this->obj_read_only = $read_only_mode;
			try
			{
				$class_name = get_class($this);
				$object_attributes = array_diff_key(get_object_vars($this), get_class_vars(__CLASS__));
				$table_name = $this->plural(strtolower($class_name));
				$sql = "select * from $table_name where $criteria";
				// error_log("SQL in BasicDataObject::Select(): " . $sql);
				
				$rs = Database::mysqli_query($sql);

				if(!$rs)
				{
					error_log("Mysql error " . Database::mysqli_error() . " executing select statement: " . $sql . "   ---   " . Database::mysqli_error());
					Throw new Exception("Mysql error " . Database::mysqli_errno() . " executing select statement: " . $sql . "   ---   " . Database::mysqli_error());
				}

				$count = Database::mysqli_num_rows($rs);
				//error_log("Count: " . var_export($count, true));
				if($count > 0)
				{
					$row = Database::mysqli_fetch_assoc($rs);
					foreach($object_attributes as $name => $value) {
						// error_log('name = ' . $name . ',  value = ' . $value);
						if (isset($row[$name])) 
						{
							$this->{$name} = $row[$name];
						} 
						else 
						{
							$this->{$name} = null;
						}
					}
					$this->id = $row['id'];
				}
				// error_log("this in Select(): ".var_export($this, true));
				Database::mysqli_free_result($rs);
				return $count;
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}

		/*
		// Inserts the data that the caller object is filled with in the database

		public function Insert()
		{
			try
			{
				if($this->obj_read_only)
				{
					die("Invalid operation! Trying to insert a read only object!");
				}

				$class_name = get_class($this);
				$class_vars = get_class_vars($class_name);
				
				// many classes have a plurals array for table names, inherited from this class.
				// So remove that before finding the array difference with the object vars.
				if (isset($class_vars['plurals'])) {
					unset($class_vars['plurals']);
				}
				// $object_attributes = array_diff(get_object_vars($this), $class_vars);
				// By Samee - Fri, Nov 1st, 2013
				// class_vars can contain the same values as those returned by get_object_vars() so it's safer to compare against indexes.
				$object_attributes = array_diff_assoc(get_object_vars($this), $class_vars);
				// error_log("object_attributes in Insert(): " . var_export($object_attributes, true));
				$table_name = $this->plural(strtolower($class_name));

				$field_names = "";
				$field_values = "";

				foreach($object_attributes as $name => $value)
				{
					if(!is_null($value) && $name != 'id')//
					{
						if(strtolower($class_name) == 'user' && $name == 'salt')
							continue;
							
						if($field_names != '')
						{
							$field_names .= ', ';
							$field_values .= ', ';
						}
						$field_names .= "`" . $name . "`";
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = Database::mysqli_real_escape_string($value);
							$value = "'".$value."'";
						}
						$field_values .= $value;
					}
				}
				$sql = "insert into $table_name ($field_names) values ($field_values)";
				// error_log("BasicDataObject Insert SQL: ".$sql);
				if(!Database::mysqli_query($sql))
				{
					error_log("Error executing Insert statement: " . Database::mysqli_error() . "    query: " . $sql);
					Throw new Exception("Error executing Insert statement: ". (__LINE__) . " " . Database::mysqli_error() . "\nSQL: ". $sql);
				}
				$num_affected_rows = Database::mysqli_affected_rows();
				if($num_affected_rows > 0)
					$this->id = Database::mysqli_insert_id();

				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				Throw $e;
			}

		}
		*/

		// Updates the data that the caller object is filled with in the database

		/*
		A few usage examples

		// 1. Update Item example
		$item->id = 4;

		$item->name = ''; // Storing empty strings
		$item->upc = '';
		$item->small_type = '';
		$item->inventory_count = 0; // Storing zero values
		$item->description = 'null'; // Storing null values
		$item->start_date = 'now()'; // Using built in SQL functions such as now()

		$item->Update();

		// 2. Select and Update example
		$item->Select("id='4'");
		$item->description = 'Some description';
		$item->Update();

		*/

	/*
		public function Update()
		{
			try
			{
				if($this->obj_read_only)
				{
					error_log("Invalid operation! Trying to update a read only object!");
					die("Invalid operation! Trying to update a read only object!");
				}
				
				$class_name = get_class($this);
				$class_vars = get_class_vars($class_name);
				// error_log("class_vars in Update: " . var_export($class_vars, true));
				// error_log("object vars in Update(): " . var_export(get_object_vars($this), true));

				$object_attributes = array_diff_assoc(get_object_vars($this), $class_vars);

				$table_name = $this->plural(strtolower($class_name));

				$fields = "";
				// error_log("object_attributes in Update(): " . var_export($object_attributes, true));
				foreach($object_attributes as $name => $value)
				{
					if(!is_null($value) && $name != 'id')
					{
						if(strtolower($class_name) == 'user' && $name == 'salt')
							continue;
							
						if($fields != '')
						{
							$fields .= ', ';
						}
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = Database::mysqli_real_escape_string($value);
							$value = "'".$value."'";
						}
						$fields .= "`" . $name."` = ".$value;
					}
				}
				
				$num_affected_rows = 0;
				if(!empty($fields))
				{
					$sql = "update $table_name set $fields where id = '".$this->id."'";
					// error_log("Update SQL in BasicDataObject: ".$sql);
					$rs = Database::mysqli_query($sql);
					if(Database::mysqli_error()) {
						error_log("Error executing Update statement: " . Database::mysqli_error() . "    query: " . $sql);
						Throw new Exception("Error executing Update statement: ".Database::mysqli_error());
					}
					$num_affected_rows = Database::mysqli_affected_rows();
					Database::mysqli_free_result($rs);
				}
				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				// error_log('caught exception in BasicDataObject: ' . $e);
				Throw $e;
			}
		}
		*/
		
		public function Insert()
		{
			try
			{
				$field_names = "";
				$field_values = "";
				$num_affected_rows = 0;
				
				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));
				
				if($this->obj_read_only)
				{
					die("Invalid operation! Trying to insert a read only object!");
				}

				$obj = clone($this);
				unset($obj->{'id'});
				unset($obj->{'obj_read_only'});
				unset($obj->{'email_notify'});
				unset($obj->{'sms_notify'});
				unset($obj->{'push_notify'});
				unset($obj->{'salt'});

				foreach($obj as $name => $value)
				{
					if(!is_null($value))//
					{
						if($field_names != '')
						{
							$field_names .= ', ';
							$field_values .= ', ';
						}
						$field_names .= "`$name`";
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = Database::mysqli_real_escape_string($value);
							$value = "'".$value."'";
						}
						$field_values .= $value;
					}
				}
				if(!empty($field_names))
				{
					$sql = "insert into $table_name ($field_names) values ($field_values)";
					error_log("BasicDataModel Insert SQL in Insert2: ".$sql);
				
					if(!Database::mysqli_query($sql))
					{
						Throw new Exception("Error executing Insert statement: ". (__LINE__) . " " . Database::mysqli_error() . "\nSQL: ".$sql);
					}
					$num_affected_rows = Database::mysqli_affected_rows();
					if($num_affected_rows > 0)
						$this->id = Database::mysqli_insert_id();
						
					
				}

				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}
		
		public function Update()
		{
			try
			{
				if($this->obj_read_only)
				{
					error_log("Invalid operation! Trying to update a read only object!");
					die("Invalid operation! Trying to update a read only object!");
				}
				
				$obj = clone($this);
				unset($obj->{'id'});
				unset($obj->{'obj_read_only'});
				unset($obj->{'email_notify'});
				unset($obj->{'sms_notify'});
				unset($obj->{'push_notify'});
				unset($obj->{'salt'});
				
				
				$fields = "";
				$num_affected_rows = 0;
				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));

				foreach($obj as $name => $value)
				{
					if(!is_null($value))//
					{
						if($fields != '')
						{
							$fields .= ', ';
						}
						if(!in_array(strtolower($value), array('now()', 'null')) )
						{
							$value = Database::mysqli_real_escape_string($value);
							$value = "'".$value."'";
						}
						$fields .= "`".$name."` = ".$value;
					}
				}
				
				if(!empty($fields))
				{

					$sql = "update $table_name set $fields where id = '".$this->id."'";
					// error_log("Update SQL in Update2(): ".$sql);
					$rs = Database::mysqli_query($sql);
					if(Database::mysqli_error()) {
						error_log("Error executing Update statement: " . Database::mysqli_error() . "\n    query: " . $sql);
						Throw new Exception("Error executing Update statement: ".Database::mysqli_error() . "\nSQL: " . $sql);
					}
					$num_affected_rows = Database::mysqli_affected_rows();
				}
				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				// error_log('caught exception in BasicDataModel: ' . $e);
				Throw $e;
			}
		}
		// Deletes the row matching the id of the caller object
		public function Delete()
		{
			try
			{
				if($this->obj_read_only)
				{
					die("Invalid operation! Trying to delete a read only object!");
				}

				$class_name = get_class($this);
				$table_name = $this->plural(strtolower($class_name));
				$sql = "delete from $table_name where id = '".$this->id."'";

				if(!Database::mysqli_query($sql))
				{
					error_log("Error executing Delete statement: " . Database::mysqli_error() . "    query: " . $sql);
					Throw new Exception("Error executing Delete statement: ".Database::mysqli_error());
				}

				$num_affected_rows = Database::mysqli_affected_rows();
				return $num_affected_rows;
			}
			catch(Exception $e)
			{
				Throw $e;
			}
		}
		
		/* Returns the resultset resulting from the specified SQL as an array */
		public static function getDataTable($sql)
		{
			//error_log("sql: ".$sql);
			$result = array();
			
			try
			{
				$rs = Database::mysqli_query($sql);

				if(!$rs)
				{
					throw new Exception("SQL error in funtion BasicDataObject::getDataTable(): ".Database::mysqli_error() . "   Original query: " . $sql);
				}
				else if(Database::mysqli_num_rows($rs) > 0)
				{
					while($row = Database::mysqli_fetch_assoc($rs))
					{
						$result[] = $row;
					}
				}
				Database::mysqli_free_result($rs);
				return $result;
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
		}
		
		/* Returns the first row of the resultset resulting from the specified SQL as an array */
		public static function getDataRow($sql)
		{
			// error_log("sql: ".$sql);
			$result = array();
			
			try
			{
				$rs = Database::mysqli_query($sql);
			
				if(!$rs)
				{
					throw new Exception("SQL error in funtion BasicDataObject::getDataRow(): ".Database::mysqli_error(). "\nSQL: " . $sql);
				}
				else if(Database::mysqli_num_rows($rs) > 0)
				{
					if($row = Database::mysqli_fetch_assoc($rs))
					{
						$result = $row;
					}
				}
				Database::mysqli_free_result($rs);

				return $result;
			}
			catch(Exception $e)
			{
				error_log($e->getMessage());
				throw $e;
			}
		}

		/* Inserts table data */
		public static function InsertTableData($table_name, $table_data)
		{
			$arr_field_names = array();
			$arr_field_values = array();
		
			foreach($table_data as $name => $value)
			{
				if(is_array($value))
				{
					$value = $value[0];
				}
				else
				{
					$value = Database::mysqli_real_escape_string($value);
					if(!in_array(strtolower($value), array('now()', 'null')) )
						$value = "'".$value."'";
				}
						
				$arr_field_names[] = "`".$name."`";
				$arr_field_values[] = $value;
			}
		
			$sql = "insert into `$table_name` (".implode(',', $arr_field_names).") values (".implode(',', $arr_field_values).")";
			// error_log("Insert SQL in InsertTableData(): ".$sql);
			try
			{
				if(!Database::mysqli_query($sql))
				{
					error_log("SQL error in BasicDataObject::InsertTableData(): ".Database::mysqli_error().", \nSQL: ".$sql);
					throw new Exception(Database::mysqli_error() . "\nSQL: " . $sql);
					return false;
				}
			}
			catch(Exception $e)
			{
				throw $e;
			}
			return Database::mysqli_insert_id();
		}
		
		/* Replace table data */
		public static function ReplaceTableData($table_name, $table_data)
		{
			$arr_field_names = array();
			$arr_field_values = array();
		
			foreach($table_data as $name => $value)
			{
				$value = Database::mysqli_real_escape_string($value);
				if(!in_array(strtolower($value), array('now()', 'null')) )
					$value = "'".$value."'";
						
				$arr_field_names[] = "`".$name."`";
				$arr_field_values[] = $value;
			}
		
			$sql = "replace into `$table_name` (".implode(',', $arr_field_names).") values (".implode(',', $arr_field_values).")";
			// error_log("Replace SQL in ReplaceTableData(): ".$sql);
			if(!Database::mysqli_query($sql))
			{
				error_log("SQL error in BasicDataObject::ReplaceTableData(): ".Database::mysqli_error().", \nSQL: ".$sql);
				return false;
			}
			return Database::mysqli_insert_id();
		}
		
		/* Inserts table data */
		public static function InsertMultipleRows($table_name, $table_data)
		{
			$arr_data = array();
		
			foreach($table_data as $table_row)
			{
				$arr_field_names = array();
				$arr_field_values = array();
				
				foreach($table_row as $name => $value)
				{
					$arr_field_names[] = "`".$name."`";
					$arr_field_values[] = "'".Database::mysqli_real_escape_string($value)."'";
				}
				$arr_data[] = "(" . implode(',', $arr_field_values) . ")";
			}
		
			$sql = "insert into `$table_name` (".implode(',', $arr_field_names).") values ".implode(', ', $arr_data);
			// error_log("Insert SQL in InsertMultipleRows(): ".$sql);
			if(!Database::mysqli_query($sql))
			{
				error_log("SQL error in BasicDataObject::InsertTableData(): ".Database::mysqli_error().", \nSQL: ".$sql);
				return false;
			}
			return Database::mysqli_insert_id();
		}
		
		/* Inserts table data */
		public static function UpdateTableData($table_name, $table_data, $row_id, $col_name = 'id')
		{
			$arr_field_names = array();
			$arr_field_values = array();
		
			foreach($table_data as $name => $value)
			{
				if(is_array($value))
				{
					$value = $value[0];
				}
				else
				{
					$value = Database::mysqli_real_escape_string($value);
					if(!in_array(strtolower($value), array('now()', 'null')) )
						$value = "'".$value."'";
				}
			
				// $value = Database::mysqli_real_escape_string($value);
				// if(!in_array(strtolower($value), array('now()', 'null')) )
				// 	$value = "'".$value."'";
				$table_data[$name] = $value;
			}
			$str_table_data = urldecode(http_build_query($table_data, '', ', '));
		
			$sql = "update `$table_name` set $str_table_data where $col_name = '".Database::mysqli_real_escape_string($row_id)."'";
			// error_log("Update SQL in UpdateTableData(): ".$sql);
			try
			{
				if(!Database::mysqli_query($sql))
				{
					error_log("SQL error in BasicDataObject::UpdateTableData(): ".Database::mysqli_error().", \nSQL: ".$sql);
					throw new Exception(Database::mysqli_error() . "\nSQL: " . $sql);
				}
			}
			catch(Exception $e)
			{
				throw $e;
			}

			return Database::mysqli_affected_rows();
		}


	}
?>