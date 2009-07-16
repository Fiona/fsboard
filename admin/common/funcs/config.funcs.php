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


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * Return an array of all configuration groups.
 *
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|array False on failure or an array of configuration groups from
 *      the database.
 */
function config_get_config_groups($supress_errors = False)
{

	global $db, $lang;

	$db -> basic_select(array(
							"table" => "config_groups",
							"where" => "`order` >= 0",
							"order" => "`order`",
							"dir" => "ASC"
							));

	if(!$db -> num_rows())
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['error_no_config_groups']);
		return False;
	}

	$groups = array();

	while($group = $db -> fetch_array())
		$groups[] = $group;

	return $groups;

}


/**
 * Return a single configuration group. This shoud be used for checking if a group
 * exists too.
 *
 * @var string $group_short_name Short name (ID) of the group you want to get.
 *
 * @return bool|array False if group does not exist or the information about the group.
 */
function config_get_single_config_group($group_short_name)
{

	global $db;

	$db -> basic_select(array(
							"table" => "config_groups",
							"where" => "name = '".$db -> escape_string($group_short_name)."'",
							"limit" => "1"
							));

	if(!$db -> num_rows())
		return False;

	return $db -> result();

}


/**
 * Create a new configuration group
 *
 * @var string $group_short_name Short name (ID) of this group that it 
 *      will be refered to internally. Must be a string consisting of
 *      only alphanumeric characters. (No spaces)
 * @var string $group_name String that will be inserted to the current lang
 *      and used as the display name.
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool True or false on failure.
 */
function config_add_config_group($group_short_name, $group_name, $group_order = 0, $supress_errors = False)
{

	global $db, $output, $lang, $cache;

	$insert = $db -> basic_insert(
		array(
			"table" => "config_groups",
			"data" => array(
				"name" => $group_short_name,
				"order" => $group_order
				)
			)
		);

	if(!$insert)
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['config_group_add_error_insert']);
		return False;
	}

	// Create the phrase
	$insert = $db -> basic_insert(
		array(
			"table" => "language_phrases",
			"data" => array(
				"language_id"	=> LANG_ID,
				"variable_name" => "config_dropdown_".$group_short_name,
				"group"			=> "admin_config",
				"text"			=> $group_name,
				"default_text"	=> $group_name
				)
			)
		);

	if(!$insert)
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['config_group_add_error_phrase']);
		return False;
	}

	// Rebuild the language file
	require ROOT."admin/common/funcs/languages.funcs.php";
	build_language_files(LANG_ID, "admin_config");        	

	// Update the configuration cache
	$cache -> update_cache("config");

	return True;

}


/**
 * Return a single configuration field. This shoud be used for checking if a field
 * exists too.
 *
 * @var string $config_group Short name (ID) of the configuration group the field belongs
 *      to.
 * @var string $field_short_name Short name (ID) of the field you want to get.
 *
 * @return bool|array False if field does not exist or the information about the group.
 */
function config_get_single_config_field($config_group, $field_short_name)
{

	global $db;

	$db -> basic_select(array(
							"table" => "config",
							"where" => "config_group = '".$db -> escape_string($config_group).
								"' AND name = '".$db -> escape_string($field_short_name)."'",
							"limit" => "1"
							));

	if(!$db -> num_rows())
		return False;

	return $db -> result();

}


/**
 * Return an array of all configuration fields from a single group.
 *
 * @var string $config_group Short name (ID) of the configuration group we want
 *      to fetch fields for.
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool|array False on failure or an array of configuration fields from
 *      the database.
 */
function config_get_config_fields($config_group, $supress_errors = False)
{

	global $db, $lang, $output;

	$db -> basic_select(array(
							"table" => "config",
							"where" => "config_group = '".$db -> escape_string($config_group)."'",
							"order" => "`order` ASC"
							));
	 
	if(!$db -> num_rows())
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['error_no_config_settings']);
		return False;
	}

	$groups = array();

	while($group = $db -> fetch_array())
		$groups[] = $group;

	return $groups;

}


/**
 * Create a new configuration field
 *
 * @var string $group_short_name Short name (ID) of this group that this value
 *      will belong to.
 * @var string $field_short_name Short name (ID) of this field that it 
 *      will be refered to internally. Must be a string consisting of
 *      only alphanumeric characters. (No spaces)
 * @var string $field_name String that will be inserted to the current lang
 *      and used as the display name.
 * @var string $field_description String that will be inserted to the current lang
 *      and used as the field's short description.
 * @var string $field_type String that describes the type of field, can be one of the
 *      following: text, int, yesno, dropdown, textarea
 * @var int $field_order Number for rearranging fields in numerical order.
 * @var string $field_dropdown_values This is text describing the dropdown values as
 *      a pipe dilimetered string. It is only the values, not the text that they will
 *      appear as.
 * @var bool $suppress_errors Normally this function will output error messages
 *      using set_error_message. If this is not wanted for whatever reason setting
 *      this to True will stop them appearing.
 *
 * @return bool True or false on failure.
 */
function config_add_config_field($group_short_name, $field_short_name, $field_name,
								 $field_description = "", $field_type = "text", $field_order = 0,
								 $field_dropdown_values = "", $supress_errors = False)
{

	global $db, $lang, $cache, $output, $page_matches;


	// Put the value in
	$insert = $db -> basic_insert(
		array(
			"table" => "config",
			"data" => array(
				"name" => $field_short_name,
				"config_group" => $group_short_name,
				"config_type" => $field_type,
				"order" => $field_order,
				"dropdown_values" => $field_dropdown_values
				)
			)
		);

	if(!$insert)
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['config_value_add_error_insert']);
		return False;
	}


	// Create the phrases
	$insert = $db -> basic_insert(
		array(
			"table" => "language_phrases",
			"data" => array(
				"language_id"	=> LANG_ID,
				"variable_name" => "admin_config_name_".$field_short_name,
				"group"			=> "admin_config",
				"text"			=> $field_name,
				"default_text"	=> $field_name
				)
			)
		);

	if(!$insert)
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['config_value_add_error_phrase']);
		return False;
	}

	$insert = $db -> basic_insert(
		array(
			"table" => "language_phrases",
			"data" => array(
				"language_id"	=> LANG_ID,
				"variable_name" => "admin_config_desc_".$field_short_name,
				"group"			=> "admin_config",
				"text"			=> $field_description,
				"default_text"	=> $field_description
				)
			)
		);

	if(!$insert)
	{
		if(!$supress_errors)
			$output -> set_error_message($lang['config_value_add_error_phrase']);
		return False;
	}

	// Update lang files, cache and return as done
	require ROOT."admin/common/funcs/languages.funcs.php";
	build_language_files(LANG_ID, "admin_config");        	

	$cache -> update_cache("config");

	return True;

}


/**
 * Attempts to update config values of a particular group.
 *
 * @var string $config_group_name Name of the config group that these values
 *      belong too.
 * @var array $config_values Array of config short names to config values.
 *
 * @return bool False if the supplied $config_values was incorrect otherwise True.
 */
function config_update_config_values($config_group_name, $config_values)
{

	global $db, $cache;

	if(!is_array($config_values) || count($config_values) < 0)
		return False;

	foreach($config_values as $config_name => $config_value)
		$db -> basic_update(
			array(
				"table" => "config",
				"data" => array("value" => _html_entity_decode($config_value)),
				"where" => "`name` = '".$config_name."' AND `config_group` = '".$config_group_name."'"
				)
			);

	$cache -> update_cache("config");

	return True;

}



/**
 * Will return the XML data for an array of configuration groups ready to import later.
 *
 * @var string $config_group_name Name of the config group that these values
 *      belong too.
 * @var array $config_groups This is an array of groups. Usually supplied by a function
 *      like config_get_config_groups();
 *
 * @return string The contents of the XML file for importing.
 */
function config_get_exported_config_groups($config_groups)
{

	global $db;

	$xml = new xml;
	$xml -> export_xml_start();
	$xml -> export_xml_root("configuration_file");

	foreach($config_groups as $config_group)
	{

		// Start off the group
		$xml -> export_xml_start_group(
			"config_group",
			array(
				"name" => $config_group['name'],
				"order" => $config_group['order']
				)
			);

		// Select the configuration in this group
		$db -> basic_select(
			array(
				"table" => "config",
				"where" => "`config_group` = '".$config_group['name']."'"
				)
			);

		if($db -> num_rows())
		{

			while($config_array = $db -> fetch_array())
			{

				// Add the entry
				$xml -> export_xml_add_group_entry(
					"config",
					array(
						"name" => $config_array['name'],
						"config_type" => $config_array['config_type'],
						"dropdown_values" => $config_array['dropdown_values'],
						"order" => $config_array['order']                                                
						),
					trim($config_array['value'])
					);
				
			}

		}
		 
		// Finish group
		$xml -> export_xml_generate_group();

	}

	// Finish XML'ing and chuck the file out
	$xml -> export_xml_generate();

	return $xml -> export_xml;

}



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
function config_import_config_xml($xml_contents, $ignore_version = false)
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

/*
 * this stub is here for backwards compatiblitiy and should be removed when it can be
 */
function import_config_xml($xml_contents, $ignore_version = false){ config_import_config_xml($xml_contents, $ignore_version); }


?>