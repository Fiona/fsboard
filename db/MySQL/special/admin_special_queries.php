<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2007 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License.
See gpl.txt for a full copy of this license.
--------------------------------------------------------------------------
*/

/**
 * Special MySQL Queries (Admin version)
 * 
 * This is a file for a bunch of queries which
 * are beyond the scope of the basic database 
 * functions.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Database
 * 
 * @started 26 Feb 2007
 * @edited 26 Feb 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * Class encapsulates some functions that just
 * return some extended SQL as a string.
 */
class db_admin_special_queries extends db_special_queries
{
	
	/**
	 * The query to get the help docs we want
	 */
	 function query_get_help($page, $action, $field)
	 {
	 	
	 	global $db;
	 	
	 	$page = $db -> escape_string($page);
	 	$action = $db -> escape_string($action);
	 	$field = $db -> escape_string($field);
	 	
	 	$field_sql = ($field) ? "AND field = '".$field."'" : "";
	 	
	 	return "SELECT * FROM ".$db -> table_prefix."admin_area_help
	 		WHERE `page`='".$page."'
	 		AND (`action` = '' OR FIND_IN_SET('".$action."', `action`))
	 		".$field_sql."
	 		ORDER BY `order` ASC";
	 		
	 }
	 	
}

?>