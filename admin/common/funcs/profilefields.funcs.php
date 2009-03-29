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
 * Admin custom profile field related functions
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
 * Select all profile fields.
 *
 * @return array An array of fields, with ids against info
 */
function profilefields_get_fields()
{

	global $db;

	$fields = array();

	$db -> basic_select(
		array(
			"table" => "profile_fields",
			"order" => "`order`", 
			"direction" => "asc"
			)
		);

	if(!$db -> num_rows())
		return $fields;

	while($f_array = $db -> fetch_array())
		$fields[$f_array['id']] = $f_array;

	return $fields;

}

?>