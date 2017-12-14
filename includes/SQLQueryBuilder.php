<?php
	class SQLTableReference
	{
		var $description;		//	Table name or a SQLSelect object
		var $alias;	//	Alias 
		
		public function __construct($description = null, $alias = null)
		{
			
			$this->description	= $description;
			$this->alias		= $alias;
		}
		
		public function SQL()
		{
			$sql = "";
			if(is_object($this->description) && get_class($this->description) == 'SQLSelect')
			{
				$sql .= " (" . $this->description->SQL() . ") ";
			}
			else if(is_string($this->description))
			{
				$sql .= $this->description;
			}
			
			if(!empty($this->alias))
				$sql .= " as " . $this->alias;
			
			return $sql;
		}
	}
	
	class SQLColumns
	{
		var $columns;
		
		public function __construct($columns)
		{
			$this->columns	= $columns;
		}
		
		public function SQL()
		{
			$sql = "";
			if(is_string($this->columns))
			{
				$sql .= $this->columns;
			}
			else if(is_array($this->columns))
			{
				$sql .= implode(', ', $this->columns);
			}
			return $sql;
		}
	}
	
	class SQLJoin
	{
		var $type;	// left join, right join, outer join, inner join
		var $table_ref;	//	Table reference
		var $where_condition;	//	An instance of SQLWhere class
		
		
		/*
			This function can either accept 1 param to be a SQLJoinObject or a string
			OR
			2 params, a table_ref or a string and a where_condition or a string
		*/
		public function __construct($type = '', $table_ref, $where_condition = null)
		{
			
			$this->type	= $type;
			$this->table_ref		= $table_ref;
			$this->where_condition	= $where_condition;
		}
		
		public function SQL()
		{
			$sql = " " . $this->type . " join ";
			
			if(is_string($this->table_ref))
			{
				$sql .= $this->table_ref;
			}
			else if(is_object($this->table_ref) && get_class($this->table_ref) == 'SQLTableReference')
			{
				$sql .= $this->table_ref->SQL();
			}
			else if(is_object($this->table_ref) && get_class($this->table_ref) == 'SQLSelect')
			{
				$sql .= " (" . $this->table_ref->SQL() . ") ";
			}
			
			if(!empty($this->where_condition))
			{
				$sql .= " on ";
				if(is_string($this->where_condition))
				{
					$sql .= $this->where_condition;
				}
				else if(is_object($this->where_condition) && get_class($this->where_condition) == 'SQLWhere')
				{
					$sql .= $this->where_condition->SQL();
				}
			}
			
			return $sql;
		}
	
	}
	
	class SQLExpression
	{
		var $operand1;
		var $operand2;	//	Can be a string, array of values or even a select SQL
		var $op;		//	Operator (=, >, <, in, like)
		
		public function __construct($operand1, $op = null, $operand2 = null)
		{
			$this->operand1	= $operand1;
			$this->operand2	= $operand2;
			$this->op		= $op;
		}
		
		public function SQL()
		{
			$sql = "";
			if(is_string($this->operand1))
			{
				$sql .= $this->operand1;
			}
			else if(is_object($this->operand1))
			{
				$sql .= " (" . $this->operand1->SQL() . ") ";
			}
			else if(is_array($this->operand1))
			{
				$sql .= " ('" . implode("', '", $this->operand1) . "') ";
			}
			
			$sql .= " " . $this->op . " ";
			
			if(is_string($this->operand2))
			{
				$sql .= $this->operand2;
			}
			else if(is_object($this->operand2))
			{
				$sql .= " (" . $this->operand2->SQL() . ") ";
			}
			else if(is_array($this->operand2))
			{
				$sql .= " ('" . implode("', '", $this->operand2) . "') ";
			}
			return $sql;
		}
	}
	
	class SQLWhere
	{
		var $expr1;	//	String, SQLExpression or a SQLWhere instance
		var $expr2;	//	Optional - String, SQLExpression or a SQLWhere instance
		var $op;	//	Optional (Only if expr2 is present). Can have values 'and', 'or'
		
		public function __construct($expr1, $op = null, $expr2 = null)
		{
			$this->expr1	= $expr1;
			$this->expr2	= $expr2;
			$this->op		= $op;
		}
		
		public function SQL()
		{
			$sql = "";
			//	If only one operand was provided
			if(empty($this->expr2) || empty($this->op))
			{
				if(is_string($this->expr1))
				{
					$sql .= $this->expr1;
				}
				else if(is_object($this->expr1))
				{
					$sql .= $this->expr1->SQL();
				}
			}
			else if(!empty($this->expr2) && !empty($this->op))
			{
				$sql .= " (";
				if(is_string($this->expr1))
				{
					$sql .= $this->expr1;
				}
				else if(is_object($this->expr1))
				{
					$sql .= " ( " . $this->expr1->SQL() . ") ";
				}
				
				$sql .= " " . $this->op . " ";
				
				if(is_string($this->expr2))
				{
					$sql .= $this->expr2;
				}
				else if(is_object($this->expr2))
				{
					$sql .= " (" . $this->expr2->SQL() . ") ";
				}
				$sql .= ") ";
			}
			
			
			return $sql;
		}
	}
	
	class SQLGroupBy
	{
		var $columns;
		var $having;
		
		public function __construct($columns, $having = null)
		{
			$this->columns	= $columns;
			$this->having	= $having;
		}
		
		public function SQL()
		{
			$sql = "";
			
			if(is_string($this->columns))
			{
				$sql .= $this->columns;
			}
			else if(is_object($this->columns) && get_class($this->columns) == 'SQLColumns')
			{
				$sql .= $this->columns->SQL();
			}
			else if(is_array($this->columns))
			{
				$sql .= implode(', ', $this->columns);
			}
			
			if(!empty($this->having))
			{
				$sql .= " having ";
				if(is_string($this->having))
				{
					$sql .= $this->having;
				}
				else if(is_object($this->having) && get_class($this->having) == 'SQLWhere')
				{
					$sql .= $this->having->SQL();
				}
				else if(is_object($this->having) && get_class($this->having) == 'SQLExpression')
				{
					$sql .= $this->having->SQL();
				}
			}
			return $sql;
		}
	}

	class SQLOrderBy
	{
		var $sort_column;
		var $sort_order;
		
		public function __construct($sort_column, $sort_order = null)
		{
			$this->sort_column = $sort_column;
			$this->sort_order = $sort_order;
		}
		
		public function SQL()
		{
			$sql = $this->sort_column;
			if(!empty($this->sort_order))
				$sql .= ' ' . $this->sort_order;
		}
	}
	
	
	class SQLUnion
	{
		var $type; // Union, Union All
		var $union_sql;	//	A string or an SQLSelect query
		
		public function __construct($union_sql, $type = null)
		{
			$this->type = $type;
			$this->union_sql = $union_sql;
		}
		
		public function SQL()
		{
			$sql = "";
			
			if(is_string($this->union_sql))
				$sql .= $this->union_sql;
			else
				$sql .= $this->union_sql->SQL();

			return $sql;
		}
	}
	
	/* 
		select_expr:
		Columns to select, e.g. 
		1.	Can be a string: * or products.* or p.name, i.item_number, r.height or 1 + 3 + 4
		2.	Or an array:	array('p.name', 'i.item_number', 'r.height') or array('*') or array('products.*')
		3.	If left empty it defaults to `table_alias`.*
		
		table_references:
		A single table reference or an array of multiple table references or joins (see the SQLTableReference and SQLJoin classes)
		e.g	
		array(
			$table_ref1,
			$table_ref2,
		)
		OR
		array(
			$table_ref1,
			$join1,
			$join2,
		)
		OR
		array(
			"products p",
			"inner join "
		)
		
	*/
	class SQLSelect
	{
		
		var $select_expr; 
		var $table_references; 
		var $where_condition;
		var $group_by;
		var $union_sql;	// Another SQL that this can be union'ed with
		var $order_by;
		var $limit;
		
		
		public function __construct($table_references, $select_expr = '*', $where_condition = null, $group_by = null, $union_sql = null, $order_by = null, $limit = null)
		{
			$this->select_expr		= $select_expr;

			if(is_array($table_references))
				$this->table_references	= $table_references;
			else
				$this->table_references[]	= $table_references;
				
			$this->where_condition	= $where_condition;
			$this->group_by			= $group_by;	
			$this->union_sql		= $union_sql;	
			$this->order_by			= $order_by;	
			$this->limit			= $limit;	
		}
		
		/*
		Usage:
		$query->columns("p.name, count(p.id) as num_products");
		$query->columns(array("p.name", "count(p.id) as num_products"));
		*/
		public function &columns($select_expr)
		{
			$this->select_expr = $select_expr;
			return $this;
		}
		
		/*
		Usage:
		$query->where("p.id in (1, 2, 3)");
		$query->whereAnd("c.id = 1");
		$query->whereAnd(new SQLWhere("m.id = 1"));
		$query->whereOr(new SQLWhere(new SQLWhere("p.id=2"), "and", new SQLWhere("c.id=3")));
		$query->whereOr(new SQLExpression("m.code = '001'"));
		$query->whereOr(new SQLExpression("c.id", "in", new SQLSelect("categories", "id")));	
		*/
		public function &where($where_condition)
		{
			$this->where_condition	= $where_condition;
			return $this;
		}
		
		public function &whereAnd($where_condition)
		{
			$this->where_condition = empty($this->where_condition) ? $where_condition : new SQLWhere($this->where_condition, 'and', $where_condition);
			return $this;
		}
		
		public function &whereOr($where_condition)
		{
			$this->where_condition = empty($this->where_condition) ? $where_condition : new SQLWhere($this->where_condition, 'or', $where_condition);
			return $this;
		}
		
		
		/*
		Usage:
		$query = new SQLSelect(
			new SQLTableReference('products', 'p'),	//	table reference (for joins, this must be an array)
			'p.*, c1.*, m1.*'	// columns
		);
	
		$query->innerJoin(new SQLTableReference('categories', 'c'), new SQLWhere('p.category_id = c.id'));
		
		$query->innerJoin(
			new SQLJoin('inner', new SQLTableReference('manufacturers', 'm'), new SQLWhere('p.manufacturer_id = m.id'))
		);
		
		$query->innerJoin("categories c1 on p.category_id = c1.id");
		
		$query->innerJoin("manufacturers m1", "p.manufacturer_id = m1.id");
		*/
		
		public function &join($param1, $param2 = null, $type = '')
		{
			
			if(!empty($param2))
			{
				$this->table_references[] = new SQLJoin($type, $param1, $param2);
			}
			else
			{
				if(is_string($param1))
					$this->table_references[] = " " . $type . " join " . $param1;
				else
					$this->table_references[] = $param1;
			}
			return $this;
		}
		
		public function &innerJoin($param1, $param2 = null)
		{
			$this->join($param1, $param2, 'inner');
			return $this;
		}
		
		public function &outerJoin($join)
		{
			$this->join($param1, $param2, 'outer');
			return $this;
		}
		
		public function &leftJoin($join)
		{
			$this->join($param1, $param2, 'left');
			return $this;
		}
		
		public function &rightJoin($join)
		{
			$this->join($param1, $param2, 'right');
			return $this;
		}
		
		/*
		Usage:
		$query->union("select name, id, id as num_products from products");
		$query->union(new SQLSelect("products", "name, id, id as num_products"));
		$query->unionAll("select name, id, id as num_products from products");
		$query->unionAll(new SQLSelect("products", "name, id, id as num_products"));
		*/
		public function &union($unionSQL)
		{
			$this->union_sql = $unionSQL;
			return $this;
		}
		
		public function &unionAll($unionSQL)
		{
			$this->union_sql = new SQLUnion($unionSQL, 'all');
			return $this;
		}
		
		/*
		Usage:
		$query->groupBy("p.name");
		$query->groupBy(array("p.name", "c.id"));
		$query->groupBy(new SQLGroupBy(array("p.name", "c.id"), "p.name like '%Sa%'"));
		$query->groupBy(new SQLGroupBy(array("p.name", "c.id"), new SQLExpression("c.id", "in", new SQLSelect("categories", "id"))));
		*/		
		public function &groupBy($columns, $having = null)
		{
			if(empty($having))
				$this->group_by = $columns;
			else
				$this->group_by = new SQLGroupBy($columns, $having);
			return $this;
		}
		
		public function &orderBy($order_by)
		{
			$this->order_by = $order_by;
			return $this;
		}
		
		public function &limit($limit)
		{
			$this->limit = $limit;
			return $this;
		}
		
		public function SQL()
		{
			$sql = "select ";
			if(is_string($this->select_expr))
			{
				$sql .= $this->select_expr;
			}
			else if(is_object($this->select_expr) && get_class($this->select_expr) == 'SQLColumns')
			{
				$sql .= $this->select_expr->SQL();
			}
			else if(is_array($this->select_expr))
			{
				$sql .= implode(', ', $this->select_expr);
			}
			
			$sql .= " from ";
			
			if(is_string($this->table_references))
			{
				$sql .= $this->table_references;
			}
			else if(is_object($this->table_references) && get_class($this->table_references) == 'SQLTableReference')
			{
				$sql .= $this->table_references->SQL();
			}
			else if(is_array($this->table_references))
			{
				foreach($this->table_references as $i => $table_ref)
				{
					if(is_string($table_ref))
					{
						$sql .= $table_ref;
					}
					else if(is_object($table_ref) && get_class($table_ref) == 'SQLTableReference')
					{
						$sql .= $table_ref->SQL();
					}
					else if(is_object($table_ref) && get_class($table_ref) == 'SQLJoin')
					{
						$sql .= $table_ref->SQL();
					}
				}
			}

			if(!empty($this->where_condition))
			{
				$sql .= " where ";
				if(is_string($this->where_condition))
				{
					$sql .= $this->where_condition;
				}
				else if(is_object($this->where_condition) && get_class($this->where_condition) == 'SQLWhere')
				{
					$sql .= $this->where_condition->SQL();
				}
			}
			
			if(!empty($this->group_by))
			{
				$sql .= " group by ";
				if(is_string($this->group_by))
				{
					$sql .= $this->group_by;
				}
				else if(is_array($this->group_by))
				{
					$sql .= implode(', ', $this->group_by);
				}
				else if(is_object($this->group_by) && get_class($this->group_by) == 'SQLGroupBy')
				{
					$sql .= $this->group_by->SQL();
				}
			}
			
			if(!empty($this->union_sql))
			{
				$sql .= " union ";
				if(!empty($this->union_sql->type))
					$sql .= $this->union_sql->type . " ";
					
				if(is_string($this->union_sql))
				{
					$sql .= $this->union_sql;
				}
				else if(is_object($this->union_sql) && get_class($this->union_sql) == 'SQLSelect')
				{
					$sql .= $this->union_sql->SQL();
				}
				else if(is_object($this->union_sql) && get_class($this->union_sql) == 'SQLUnion')
				{
					$sql .= $this->union_sql->SQL();
				}
			}
			
			if(!empty($this->order_by))
			{
				$sql .= " order by ";
				if(is_string($this->order_by))
				{
					$sql .= $this->order_by;
				}
				else if(is_object($this->order_by) && get_class($this->order_by) == 'SQLOrderBy')
				{
					$sql .= $this->order_by->SQL();
				}
			}
			
			if(!empty($this->limit))
			{
				$sql .= " limit ";
				if(is_string($this->limit))
				{
					$sql .= $this->limit;
				}
			}
			
			

			return $sql;
		}
	}
?>