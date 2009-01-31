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
 * Global admin area AJAX requests
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// If we are wanting the php info
//***********************************************
if(CURRENT_MODE == "phpinfo") die(phpinfo());


switch($page_matches['mode'])
{
	case "admin_menu":
		update_menu_items();

}

/**
 * Updates our administration menu and saves the values into the database.
 * Requires two GET params to be passed to it.
 * $_GET['t'] is the type of update (open/close)
 * $_GET['id'] is the id number of the menu
 */
function update_menu_items()
{

	if(!isset($_GET['t']) || !in_array($_GET['t'], array("open", "close")))
		die();

	if(!isset($_GET['id']) || !is_numeric($_GET['id']))
		die();

	global $user, $db;

	if($_GET['t'] == "open")
	{
		if(array_search($_GET['id'], $user -> admin_menu) === False)
			$user -> admin_menu[] = $_GET['id'];
	}
	elseif($_GET['t'] == "close")
		if(($k = array_search($_GET['id'], $user -> admin_menu)) !== False)
			unset($user -> admin_menu[$k]);

	$db -> basic_update(
		array(
			"table" => "users_admin_settings",
			"data" => array(
				"admin_menu" => implode(",", $user -> admin_menu)
				),
			"where" => "`user_id` = ".$user -> user_id
			)
		);

	die();

}


?>