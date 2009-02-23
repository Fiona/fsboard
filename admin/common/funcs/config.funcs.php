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
 * Admin area - Configuration section related functions
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
 * Import config settings from XML
 *
 * @var string $xml_contents Large string containing the contents of  
 * 		XML file we want to import
 * @var bool $ignore_version If true, this function will not check the
 * 		current FSBoard version number against the number the XML file
 * 		was created in. Primarily used when installing the message board.
 *
 * @return bool|string If not set to ignore the version info, will return a
 * 		string containing "VERSION" - otherwise True/False on sucessfully
 * 		importing settings.
 */
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
				$db -> basic_delete("config", "name='".$config['ATTRS']['name']."'");

			// Inseeeeeert
			$config_insert = array(
                                'name'            => $config['ATTRS']['name'],
                                'value'           => trim($config['CONTENT']),
                                'default'         => trim($config['CONTENT']),
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