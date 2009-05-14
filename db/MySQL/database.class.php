<?php
  /* 

	 --------------------------------------------------------------------------
	 FSBoard - Free, open-source message board system.
	 Copyright (C) 2006 Fiona Burrows (fiona@fsboard.net)

	 FSBoard is free software; you can redistribute it and/or modify
	 it under the terms of the GNU General Public License as published by
	 the Free Software Foundation; either version 2 of the License, or
	 (at your option) any later version.

	 FSBoard is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details.

	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
	 --------------------------------------------------------------------------

	 *********************************
	 *       FSBoard                 *
	 *       by Fiona 2006           *
	 *********************************
	 *       MySQL Class             *
	 *       Started by Fiona        *
	 *       01st Aug 05             *
	 *********************************
	 *       Last edit by Fiona      *
	 *       29th Apr 07             *
	 *********************************

	 A class that keeps track of all the variables
	 relating to the database connecton, also holds 
	 wrappers for all the MySQL functions.

	 Also has a few functions for lazy inserting, 
	 updating and the like. 
  */



  // ----------------------------------------------------------------------------------------------------------------------


  // Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


// Check mySQL is supported.
if (!function_exists('mysql_connect'))
	die('<p>Support for mySQL cannot be found. To use mySQL as a database for this forum, mySQL must be installed. Please consult the PHP documentation, re-installation or contact the server administrator.</p>');


class database
{

	var $db_type			= 'MySQL';
	var $version			= "";

	var $connection_link    = '';
	var $table_prefix       = '';
	var $database_name      = '';
	var $saved_queries      = array();
	var $num_query          = 0;
	var $query_result       = '';

	var $shutdown_queries   = array();
        
	var $special_queries	= '';

	var $log_error          = '';
	var $log_errorno        = '';
        
	// -------------------------------------
	// Connect to the database
	// -------------------------------------
	function connect($db_host, $db_name, $db_user, $db_password, $db_prefix, $db_port = false, $install_mode = FALSE)
	{
    
		// Set these
		$this -> table_prefix = $db_prefix;
		$this -> database_name = $db_name;

		// Get the classes for special queries
		include ROOT."db/".$this -> db_type."/special/special_queries.php";

		if(defined("ADMIN"))
		{
			include ROOT."db/".$this -> db_type."/special/admin_special_queries.php";
			$this -> special_queries = new db_admin_special_queries;
		}
		else
			$this -> special_queries = new db_special_queries;

		// Try to connect        
		$db_host = $db_host.($db_port !== false ? ":".$db_port : "");
		$this -> connection_link = @mysql_connect($db_host, $db_user, $db_password);

		if($this -> connection_link)
		{

			// Collect version number
			$this -> version = mysql_get_server_info($this -> connection_link);

			if(version_compare($this -> version, '4.1.3', '>='))
			{
				// Force UTF-8
				@mysql_query("SET NAMES 'utf8'");
            		
				// Force no SQL mode (hack to disable strict mode)
				@mysql_query("SET @@sql_mode = ''");
			}
            	
		}
			
			                                
		if ($install_mode == FALSE)
		{
                    
			if (!$this -> connection_link)
				exit("<h2>Error connecting to database!</h2>
                    <p>There was an error when attempting to connect to the specified database host.<br />
                    Please check that your database host, username and password are correct.</p>");
			else
				return $this -> select_db($db_name);

		}
		else        
		{        

			if (!$this -> connection_link)
				return FALSE;
			else
				return $this -> connection_link;
                    
		}
                
	}


	// -------------------------------------
	// Chucks the type of DB and version out
	// -------------------------------------
	function database_info_string()
	{
		return "MySQL ".$this -> version;	
	}


	// -------------------------------------
	// Select a database
	// -------------------------------------
	function select_db($db_name, $install_mode = FALSE)
	{
        
		if (!@mysql_select_db($db_name, $this -> connection_link))
		{

			if ($install_mode == FALSE)
				exit("<h2>Error connecting to daatabse!</h2>".
					 "<p>There was an error when attempting to connect to the specified database.<br />".
					 "Please check your database name, and that you have sufficient priviliges.</p>");
                
			return false;

		}
		else
			return true;
                
	}

	// -------------------------------------
	// Close database connection
	// -------------------------------------
	function close()
	{
        
		if($result = @mysql_close($this -> connection_link))
			return $result;
		else
			return false;
        
	}

	// -------------------------------------
	// Return error message
	// -------------------------------------
	function error()
	{
        
		return @mysql_error($this -> connection_link);        
        
	}

	// -------------------------------------
	// Run a query
	// -------------------------------------
	function query($query, $backtrace = false)
	{
        
		global $cache;

		$error = false;
                
		// Debug
		if(
			(
				!isset($cache -> cache['config']) ||
				$cache -> cache['config']['debug'] >= 2
				) &&
			!defined("DISABLE_DATABASE_DEBUG")
			)
			$start_dbg_time = explode(' ', microtime());

		// run query and log error
		if(!$this -> query_result = @mysql_query($query, $this -> connection_link))
		{
			$this -> log_error = mysql_error();
			$this -> log_errorno = mysql_errno();
			$error = true;
		}
                
		$this -> num_query ++;

		// Debug
		if(
			(
				!isset($cache -> cache['config']) ||
				$cache -> cache['config']['debug'] >= 2
				) &&
			!defined("DISABLE_DATABASE_DEBUG")
			)
		{
                        
			$end_dbg_time = explode(' ', microtime());
                        
			$end_dbg_time = $end_dbg_time[0] + $end_dbg_time[1];
			$start_dbg_time = $start_dbg_time[0] + $start_dbg_time[1];
                        
			if(!$backtrace)
				$backtrace = debug_backtrace();
        
			$this -> saved_queries['time'][] = round(($end_dbg_time - $start_dbg_time), 6);
			$this -> saved_queries['queries'][] = $this -> generate_query_colours($query);        
			$this -> saved_queries['file'][] = $backtrace[0]['file'];
			$this -> saved_queries['line'][] = $backtrace[0]['line'];

			// explain queries
			if(!$error && preg_match("/^SELECT/i", $query))
			{

				$explain = mysql_query("EXPLAIN ".$query);
				$explains = array();

				while($fetched = mysql_fetch_assoc($explain))
					$explains[] = $fetched;

				$this -> saved_queries['explain'][] = $explains;

			}
			else
				$this -> saved_queries['explain'][] = NULL;

		}
                
		if($error)
		{
			$this -> saved_queries['error'][] = $this -> log_error;
			$this -> saved_queries['errorno'][] = $this -> log_errorno;
		}
		else
		{
			$this -> saved_queries['error'][] = "none";
			$this -> saved_queries['errorno'][] = -1;
		}

		if($this -> query_result)
			return $this -> query_result;
		else
			return false;
        
	}

	// -------------------------------------
	// Saves a query to be ran when everything else is done and outputted
	// -------------------------------------
	function save_shutdown_query($query)
	{
        
		if(!$query)        
			return false;
		else
			$this -> shutdown_queries[] = $query;
                
		return true;
                
	}

	// -------------------------------------
	// Get number of rows returned in a query
	// -------------------------------------
	function num_rows($query = "")
	{

		if($query == "")
			$query = $this -> query_result;
        
		if($result = @mysql_num_rows($query))
			return($result);
		else
			return false;        
                
	}

	// -------------------------------------
	// Get an array from the query
	// -------------------------------------
	function fetch_array($query = "")
	{

		if($query == "")
			$query = $this -> query_result;
        
		if($result = @mysql_fetch_array($query))
			return($result);
		else
			return false;        
        
	}

	// -------------------------------------
	// Get an associative array from the query
	// -------------------------------------
	function fetch_assoc($query = "")
	{

		if($query == "")
			$query = $this -> query_result;
        
		if($result = @mysql_fetch_assoc($query))
			return ($result);
		else
			false;        
        
	}

	// -------------------------------------
	// Retrieve the contents of one cell from a MySQL result set
	// -------------------------------------
	function result($query = "", $row = 0)
	{

		if($query == "")
			$query = $this -> query_result;
        
		if($result = @mysql_result($query, $row))
			return ($result);
		else
			false;        
        
	}

	// -------------------------------------
	// Get the last inserted ID
	// -------------------------------------
	function insert_id()
	{
        
		if($result = @mysql_insert_id($this -> connection_link))
			return ($result);
		else
			false;    
        
	}

	// -------------------------------------
	// Get the affected row amount of the last query
	// -------------------------------------
	function affected_rows()
	{
        
		$result = @mysql_affected_rows();

		return($result);
        
	}

	// -------------------------------------
	// Escapes a string ready for doing crap with
	// -------------------------------------
	function escape_string($string)
	{

		return mysql_real_escape_string($string);
                
	}


	// -------------------------------------
	// Changes the internal counter for a data result
	// -------------------------------------
	function data_seek($resource, $count)
	{

		return mysql_data_seek($resource, $count);
                
	}

        
	// -------------------------------------
	// Get column info
	// -------------------------------------
	function fetch_fields($query = "")
	{

		if($query == "")
			$query = $this -> query_result;

		$return = array();
                
		while($field =  mysql_fetch_field($query))
			$return[] = $field;
                        
		return $return;
                
	}
        
        
	// -------------------------------------
	// Return two formatted strings for use in INSERT queries
	// -------------------------------------
	function create_insert_strings($info)
	{
        
		$names = "";
		$vals = "";
                
		foreach($info as $key => $data)
		{
			$data = $this -> escape_string($data);

			$names.= "`$key`,";
			$vals.= "'$data',";
		}
                
		$names = _substr($names, 0, -1);
		$vals = _substr($vals, 0, -1);
                
		return array("names" => $names, "values" => $vals);
        
	}

	// -------------------------------------
	// Return a formatted string for use in UPDATE queries
	// -------------------------------------
	function create_update_string($info)
	{

		$string = "";

		foreach($info as $key => $data)
		{

			if(strpos($data, "`") !== false)
				$data = $data;
			else
				$data = "'".$this -> escape_string($data)."'";

			$string .= "`".$key."`=".$data.",";
		}
                
		$string = _substr($string, 0, -1);
                
		return $string;
        
	}


	// -------------------------------------
	// Do a quick select
	// -------------------------------------
	function basic_select($info, $what = "*", $where = "", $order_by = "", $limit = "", $direction = "", $just_return = false)
	{

		$join = "";
		$group_by = "";
		$distinct = "";

		if(is_string($info))
		{
			$where = ($where) ? " WHERE ".$where."" : "";
			$order_by = ($order_by) ? " ORDER BY ".$order_by : "";
			$limit = ($limit) ? " LIMIT ".$limit : "";
			$direction = ($direction) ? " ".$direction : "";
			$table = $info;
		}
		else
		{
			if(!isset($info['table']))
				return false;

			$table = $info['table'];
			$where = (isset($info['where']) && $info['where']) ? " WHERE ".$info['where'] : "";
			$what = (isset($info['what'])) ? $info['what'] : "*";
			$order_by = (isset($info['order'])) ? " ORDER BY ".$info['order'] : "";
			$limit = (isset($info['limit'])) ? " LIMIT ".$info['limit'] : "";
			$direction = (isset($info['direction'])) ? " ".$info['direction'] : "";
			$just_return = (isset($info['just_return'])) ? True : False;
			$group_by = (isset($info['group'])) ? " GROUP BY ".$info['group'] : "";
			$distinct = (isset($info['distinct']) && $info['distinct']) ? " DISTINCT " : "";

			// Deal with joins. We can either pass in single parameters for one join or an array
			// of different joins.
			if(isset($info['join']))
			{

				// If it's not an array package it up into one
				if(!is_array($info['join']))
				{

					$join_info = array("join" => $info['join']);

					if(isset($info['join_type']))
						$join_info['join_type'] = $info['join_type'];

					if(isset($info['join_on']))
						$join_info['join_on'] = $info['join_on'];

					$join_info = array($join_info);

				}
				else
					$join_info = $info['join'];

				// Iterate through our joins and build the SQL
				foreach($join_info as $one_join)
				{
					$type = (isset($one_join['join_type'])) ? $one_join['join_type']." " : "";
					$on = (isset($one_join['join_on'])) ? " ON(".$one_join['join_on'].") " : "";
					$join .= " ".$type."JOIN ".$this -> table_prefix.$one_join['join'].$on." ";
				}

			}

		}
        		
		$full_query = "SELECT ".
			$distinct.
			$what.
			" FROM ".
			$this -> table_prefix.
			$table.
			$join.
			$where.
			$group_by.
			$order_by.
			$direction.
			$limit;

		if($just_return)
			return $full_query;
                        
		// Do query
		if($query = $this -> query($full_query, debug_backtrace()))
			return $query;
		else
			return false;
                                        
	}
        
        
	// -------------------------------------
	// Do a quick insert
	// -------------------------------------
	function basic_insert($info, $entries_array = null, $just_return = false)
	{

		if(is_string($info))
		{
			$table = $info;
			$insert_data = $this -> create_insert_strings($entries_array);
			$final_names = $insert_data['names'];
			$final_data = "(".$insert_data['values'].")";
		}
		else
		{
					
			if(!isset($info['table']) || !isset($info['data']))
				return false;

			$table = $info['table'];
			$just_return = (isset($info['just_return'])) ? True : False;
        			
			if(isset($info['multiple_inserts']))
			{

				$final_data = array();
						
				foreach($info['data'] as $data)
				{
					$curr = $this -> create_insert_strings($data);
					$final_data[] = "(".$curr['values'].")";
				}
						
				$final_data = implode(", ", $final_data);

				$final_names = $curr['names'];
						
			}
			else
			{
						
				$insert_data = $this -> create_insert_strings($info['data']);
				$final_names = $insert_data['names'];
				$final_data = "(".$insert_data['values'].")";
	                					
			}
					
		}
				
		$full_query = "INSERT INTO ".$this -> table_prefix.$table."(".$final_names.") VALUES".$final_data;
				
		if($just_return)
			return $full_query;
                        
		// Do query
		if($query = $this -> query($full_query, debug_backtrace()))
			return $query;
		else
			return false;
        
	}

	// -------------------------------------
	// Do a quick update
	// -------------------------------------
	function basic_update($info, $entries_array = array(), $where = "", $just_return = false)
	{

		if(is_string($info))
		{
			$table = $info;
			$update = $this -> create_update_string($entries_array);
			$where = ($where) ? " WHERE ".$where : "";
			$limit = "";
		}
		else
		{
			if(!isset($info['table']) || !isset($info['data']))
				return false;

			$table = $info['table'];
			$update = $this -> create_update_string($info['data']);					        		
			$just_return = (isset($info['just_return'])) ? True : False;
			$where = (isset($info['where'])) ? " WHERE ".$info['where'] : "";
			$limit = (isset($info['limit'])) ? " LIMIT ".$info['limit'] : "";
		}

		$full_query = "UPDATE ".$this -> table_prefix.$table." SET ".$update.$where.$limit;
                                
		if($just_return)
			return $full_query;
                        
		// Do query
		if($query = $this -> query($full_query, debug_backtrace()))
			return $query;
		else
			return false;
        
	}


	// -------------------------------------
	// Do a quick update with a where array
	// -------------------------------------
	function basic_update_in($table, $entries_array, $in_what, $where, $just_return = false)
	{

		if(!$in_what || !is_array($where) || count($where) < 1)
			return false;

		$where_string = " ".$in_what." IN(".implode(",",$where).")";                        

		$update = $this -> create_update_string($entries_array);
                
		$full_query = "UPDATE ".$this -> table_prefix.$table." SET ".$update.$where_string;
                                
		if($just_return)
			return $full_query;
                        
		// Do query
		if($query = $this -> query($full_query, debug_backtrace()))
			return $query;
		else
			return false;
                        
	}
        

	// -------------------------------------
	// Do a quick delete
	// -------------------------------------
	function basic_delete($info, $where = "", $order_by = "", $limit = "", $direction = "", $just_return = false)
	{
        
		if(is_string($info))
		{        	
			$table = $info;
			$where = ($where) ? " WHERE ".$where : "";
			$order_by = ($order_by) ? " order by ".$order_by : "";
			$limit = ($limit) ? " limit ".$limit : "";
			$direction = ($direction) ? " ".$direction : "";
		}
		else
		{
			if(!isset($info['table']))
				return false;
        				
			$table = $info['table'];
			$where = (isset($info['where'])) ? " WHERE ".$info['where'] : "";
			$order_by = (isset($info['order'])) ? " ORDER BY ".$info['order'] : "";
			$limit = (isset($info['limit'])) ? " LIMIT ".$info['limit'] : "";
			$direction = (isset($info['direction'])) ? " ".$info['direction'] : "";
			$just_return = (isset($info['just_return'])) ? True : False;                	        			
		}
        		
		$full_query = "DELETE FROM ".$this -> table_prefix.$table.$where.$order_by.$direction.$limit;
                
		if($just_return)
			return $full_query;
                                        
		// Do query
		if($query = $this -> query($full_query, debug_backtrace()))
			return $query;
		else
			return false;
        
	}


	/**
	 * Will add us a column
	 * Is very nice
	 */
	function add_column($table_name, $col_name, $col_type, $default = "NULL")
	{
		
		return $this -> query("ALTER TABLE `".$this -> table_prefix.$table_name."` ADD `".$col_name."` ".$col_type." default ".$default.";");
		
	}
	

	/**
	 * Will change a column's type
	 */
	function alter_column_type($table_name, $col_name, $col_type)
	{
		
		return $this -> query("ALTER TABLE `".$this -> table_prefix.$table_name."` MODIFY `".$col_name."` ".$col_type);
		
	}
				
				
	/**
	 * Will delete a column
	 */
	function remove_column($table_name, $col_name)
	{
		
		return $this -> query("ALTER TABLE `".$this -> table_prefix.$table_name."` DROP `".$col_name."`");
		
	}


	/**
	 * Will rename a column
	 */
	function rename_column($table_name, $old_col, $new_col, $col_type)
	{

		return $this -> query("ALTER TABLE `".$this -> table_prefix.$table_name."` CHANGE COLUMN `".$old_col."` `".$new_col."` ".$col_type.";");
		
	}


	// -------------------------------------
	// Queries for something and returns the number of rows
	// -------------------------------------
	function query_check_id_rows($table, $id = "", $items = "", $where = "")
	{

		if(!$id)
			return false;
                        
		if(!$items)
			$items = "*";

		$where2 = " WHERE `id` = '".$id."' ";
                
		if($where)
			$where2 = $where2." AND ".$where;

		$full_query = "SELECT ".$items." FROM ".$this -> table_prefix.$table.$where2;
                                        
		// Do query
		if($this -> query($full_query, debug_backtrace()))
			return $this -> num_rows();
		else
			return false;
        
	}


	/**
	 * Will prepare the HTML for syntax highlighting SQL queries that appear in
	 * the footer when the debug level is set to 2.
	 *
	 * @param string $query_text The raw SQL for the query we want to colour.
	 * @param bool $bbcode_parse I have got no fucking idea what this does at all.
	 */
	function generate_query_colours($query_text, $bbcode_parse = False)
	{

		return $query_text;

		// WHAT DOES THIS DO? WHAT IS IT FOR?
		if(!$bbcode_parse)
			$query_text = _htmlspecialchars($query_text);

		// Before I was colouring queries with a bunch of incomplete and confusing
		// Regex. Since I'm using Geshi for code highlighting I might as well get it
		// to highlight my queries for me.
		require_once(ROOT."common/class/geshi.class.php");

		$geshi = new GeSHi($query_text, "mysql");
		$geshi->set_header_type(GESHI_HEADER_NONE);
		$parsed_query = $geshi->parse_code();        

		return $parsed_query;

	} 
        
}

?>
