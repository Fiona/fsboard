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
 *          FUNCTIONS            *
 *       Admin Configuration     *
 *       Started by Fiona        *
 *       31st Oct 2005           *
 *********************************
 *       Last edit by Fiona      *
 *       09th Apr 2006           *
 *********************************

 */




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



//***********************************************
// This function prints the drop-down menu for the configuration groups
//***********************************************
function config_menu($current = '', $big = false)
{

	global $db, $lang, $output;

	// Grab all configuration groups in order
	$select_groups = $db -> query("select * from ".$db -> table_prefix."config_groups where `order` >= 0 order by `order` asc");

	// No groups?! DANGER DANGER WILL ROBINSON
	if($db -> num_rows($select_groups) < 1)
	{

		// Bye
		$output -> page_output = $template_admin -> critical_error($lang['error_no_config_groups']);
		$output -> finish();
		die();

	}

	// check if we want the dropdown biiiiiig
	$dropdown_size = ($big) ? "size=\"10\"" : "";
	$width = ($big) ? "style=\"width:100%\"" : "style=\"width:90%\"";

	// Start up the form
	$final_html = "<select class=\"inputtext\" ".$width." ".$dropdown_size." name=\"group\">";

	// Go through all the damn groups now
	while($group_array = $db -> fetch_array($select_groups))
	{

		// Check to see if we're on the page
		if($current == $group_array['name'])
			$selected = " selected=\"selected\"";
		else
			$selected = "";

		// Option html
		$final_html .= "<option value=\"".$group_array['name']."\"".$selected.">".$lang['config_dropdown_'.$group_array['name']]."</option>";

	}

	// Finish form
	$final_html .= "</select>";

	if($big)
		$final_html .= "<br />";

	// Add it to the page output. End.
	return $final_html;

}



//***********************************************
// Imports config settings from XML
//***********************************************
function import_config_xml($xml_contents, $ignore_version = false)
{

	global $db;

	// Start parser
	$xml = new xml;

	$xml -> import_root_name = "configuration_file";
	$xml -> import_group_name = "config_group";

	// Run parser and check version
	$parse_return = $xml -> import_xml($xml_contents, $ignore_version);

	if($parse_return == "VERSION" && !$ignore_version)
		return "VERSION";

	// Nothing?
	if(count($xml -> import_xml_values['config_group']) < 1)
		return true;

	// **********************
	// Get existing config and groups
	// **********************
	$current_groups = array();
	$current_config = array();

	$db -> query("select name from ".$db -> table_prefix."config_groups order by `order`");

	while($a = $db -> fetch_array())
		$current_groups[$a['name']] = true;

	$db -> query("select name from ".$db -> table_prefix."config order by `config_group`");

	while($a = $db -> fetch_array())
		$current_config[$a['name']] = true;

	// **********************
	// Go through each group
	// **********************
	foreach($xml -> import_xml_values['config_group'] as $group)
	{

		// If exists - Baleete it
		if(isset($current_groups[$group['ATTRS']['name']]))
			$db -> basic_delete("config_groups", "name='".$group['ATTRS']['name']."'");

		// Stick it in! So to speak.
		$group_insert = array(
                        'name'  => $group['ATTRS']['name'],
                        'order' => $group['ATTRS']['order']
		);

		if(!$db -> basic_insert("config_groups", $group_insert))
			return false;

		// No config in this group?
		if(!isset($group['config']))
			continue;

		if(count($group['config']) < 1)
			continue;
			
		// Obviously we have config in this group
		foreach($group['config'] as $id => $config)
		{

			// If exists delete
			if(isset($current_config[$config['ATTRS']['name']]))
				$db -> basic_delete("config", "name='".$group['ATTRS']['name']."'");

			// Inseeeeeert
			$config_insert = array(
                                'name'            => $config['ATTRS']['name'],
                                'value'           => trim($config['CONTENT']),
                                'default'         => $group['config_default'][$id]['CONTENT'],
                                'config_group'    => $group['ATTRS']['name'],
                                'config_type'     => $config['ATTRS']['config_type'],
                                'dropdown_values' => $config['ATTRS']['dropdown_values'],
                                'order'           => $config['ATTRS']['order']
			);

			if(!$db -> basic_insert("config", $config_insert))
				return false;

		}

	}

	return true;

}

?>