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
 * Admin user group related functions
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * Select all user groups.
 *
 * @return array An array of groups, with ids against info
 */
function user_groups_get_groups()
{

	global $db;

	$users = array();

	$db -> basic_select(
		array(
			"table" => "user_groups",
			"order" => "id", 
			"direction" => "asc"
			)
		);

	if(!$db -> num_rows())
		return $users;

	while($g_array = $db -> fetch_array())
		$users[$g_array['id']] = $g_array;

	return $users;

}

?>