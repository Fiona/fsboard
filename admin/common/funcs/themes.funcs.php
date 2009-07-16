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
 * Admin theme related functions
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * Select all themes.
 *
 * @param bool $get_css If True the function will also return CSS
 *    along with the info. Not getting the CSS if unnessesary will
 *    significantly speed up the query.
 *
 * @return array An array of themes, with ids against info.
 */
function themes_get_themes($get_css = True)
{

	global $db;

	$themes = array();

	$db -> basic_select(
		array(
			"table" => "themes",
			"what" => ($get_css ? "*" : "id, name, image_dir, author"),
			"order" => "name", 
			"direction" => "asc"
			)
		);

	if(!$db -> num_rows())
		return $themes;

	while($t_array = $db -> fetch_array())
		$themes[$t_array['id']] = $t_array;

	return $themes;

}


//***********************************************
// Imports themes from XML
//***********************************************
function import_themes_xml($xml_contents, $ignore_version = false)
{
        
	global $db;

	// Start parser
	$xml = new xml;

	$xml -> import_root_name = "theme_file";
	$xml -> import_group_name = "theme";
        
	// Run parser and check version
	$parse_return = $xml -> import_xml($xml_contents, $ignore_version);

	if($parse_return == "VERSION" && !$ignore_version)
		return "VERSION";
        
	// Nothing?
	if(count($xml -> import_xml_values['theme']) < 1)
		return true;

	// **********************
	// Go through each theme               
	// **********************
	foreach($xml -> import_xml_values['theme'] as $theme)
	{

		// Inseeeeeert
		$theme_insert = array(
			'name'            => $theme['ATTRS']['name'],
			'css'             => $theme['theme_css'][0]['CONTENT'],
			'author'          => $theme['ATTRS']['author'],
			'image_dir'       => $theme['ATTRS']['image_dir']
			);
                                
		if($db -> basic_insert("themes", $theme_insert))
		{
                
			// Log it!
			if(!defined("INSTALL"))
				log_admin_action("themes", "doimport", "Imported theme: ".trim($theme['name']));
                
		}
		else
			return false;
                        
	}
        
	return true;
        
}


?>
