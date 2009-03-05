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
 * Admin area - Configuration section
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


// Include meh language file
load_language_group("admin_config");


// Include config functions
include ROOT."admin/common/funcs/config.funcs.php";


// Config page crumb
$output -> add_breadcrumb($lang['breadcrumb_config'], l("admin/config/"));


$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{
	case "show_group":
		page_group($page_matches['group_name']);
		break;

	case "backup":
		page_backup();
		break;

	default:
		page_main();

}


/**
 * Main page - lets us select a category from a list
 */
function page_main()
{

	global $db, $output, $lang, $template_admin;

	// ------------------
	// Set page title
	// ------------------
	$output -> page_title = $lang['admin_config_title'];

	// ------------------
	// Our form
	// ------------------
	$groups = config_get_config_groups();

	if($groups === False)
		return False;

	$menu_groups = array();

	foreach($groups as $group)
		$menu_groups[$group['name']] = $lang['config_dropdown_'.$group['name']];

	$form = new form(array(
						 "meta" => array(
							 "name" => "config_select",
							 "title" => $lang['admin_config_title'],
							 "extra_title_contents_left" => $output -> help_button("", True).$template_admin -> form_header_icon("general"),
							 "description" => $lang['admin_config_page_message'],
							 "validation_func" => "form_config_select_validate",
							 "complete_func" => "form_config_select_complete"
							 ),
						 "#config_menu" => array(
							 "name" => $lang['admin_config_menu_input'],
							 "type" => "dropdown",
							 "options" => $menu_groups,
							 "size" => 10,
							 "required" => True
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['admin_config_go']
							 )
						 ));

	$output -> add($form -> render());

	// As delveloper we can add a group
	if(defined("DEVELOPER"))
	{

		$dev_form = new form(array(
								 "meta" => array(
									 "name" => "config_group_add",
									 "title" => $lang['config_group_add'],
									 "action" => l("admin/config/newgroup"),
									 "validation_func" => "form_config_group_add_validate",
									 "complete_func" => "form_config_group_add_complete"	
									 ),
								 "#name" => array(
									 "name" => $lang['config_group_add_name'],
									 "type" => "text",
									 "required" => True
									 ),
								 "#shortname" => array(
									 "name" => $lang['config_group_add_shortname'],
									 "type" => "text",
									 "required" => True
									 ),
								 "#order" => array(
									 "name" => $lang['config_group_add_order'],
									 "type" => "int"
									 ),
								 "#submit" => array(
									 "type" => "submit"
									 )
								 ));

		$output -> add($dev_form -> render());
				
	}
	
}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for selecting a config group
 * Will check if the item we've selected exists
 *
 * @param object $form
 */
function form_config_select_validate($form)
{
   
	global $lang;

	if(!$form -> form_state['#config_menu']['value'])
		return;

	$group = config_get_single_config_group($form -> form_state['#config_menu']['value']);

	if($group === False)
		$form -> set_error("config_menu", $lang['config_error_no_group']);        

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for selecting a new config group
 *
 * @param object $form
 */
function form_config_select_complete($form)
{
 
	global $output;

	// Instant redirect to the right page
	$output -> redirect(l("admin/config/show_group/".$form -> form_state['#config_menu']['value']."/"), "", True);

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for creating a new config group
 *
 * @param object $form
 */
function form_config_group_add_validate($form)
{
   
	global $lang;

	// Check this group doesn't already exist
	$group = config_get_single_config_group($form -> form_state['#config_menu']['value']);

	if($group !== False)
		$form -> set_error("shortname", $lang['config_group_add_error_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for creating a new config group
 *
 * @param object $form
 */
function form_config_group_add_complete($form)
{
   
	global $lang, $output;

	// Put the group in
	$group_add = config_add_config_group(
		$form -> form_state['#shortname']['value'],
		$form -> form_state['#name']['value'],
		$form -> form_state['#order']['value']
		);

	if($group_add === False)
		return False;

	// Redirect back to the main config page
	$output -> redirect(l("admin/config/"), $lang['config_group_add_done']);        

}


/**
 * Display one of the configuration groups
 *
 * @param string $config_group_name Short name of the config group to display
 */
function page_group($config_group_name)
{

	global $db, $output, $lang, $template_admin, $cache;


	// ********************
	// Group dropdown menu 
	// ********************
	$groups = config_get_config_groups();

	if($groups === False)
		return False;

	$menu_groups = array();

	foreach($groups as $group)
		$menu_groups[$group['name']] = $lang['config_dropdown_'.$group['name']];

	$form = new form(array(
						 "meta" => array(
							 "name" => "small_config_select",
							 "title" => $lang['admin_config_title'],
							 "extra_title_contents_left" => $template_admin -> form_header_icon("general"),
							 "validation_func" => "form_config_select_validate",
							 "complete_func" => "form_config_select_complete"
							 ),
						 "#config_menu" => array(
							 "name" => $lang['admin_config_menu_input'],
							 "type" => "dropdown",
							 "options" => $menu_groups,
							 "required" => True,
							 "value" => $config_group_name
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['admin_config_go']
							 )
						 ));

	$output -> add($form -> render());


	// ********************************
	// Page title and breadcrumb
	// ********************************
	$output -> page_title = $lang['config_dropdown_'.$config_group_name];
	$output -> add_breadcrumb($lang['config_dropdown_'.$config_group_name], l("admin/config/show_group/".$config_group_name."/"));



	// ********************************
	// The configuration settings form
	// ********************************
	$fields = config_get_config_fields($config_group_name);

	$form_state = array(
		"meta" => array(
			"name" => "configuration_update",
			"title" => $lang['config_dropdown_'.$config_group_name],
			"complete_func" => "form_config_update_complete"
			)
		);

	if($fields !== False)
	{

		// go through all the fields in turn
		$config_names = array();

		foreach($fields as $config_field)
		{

			$config_names[] = $config_field['name'];

			// Dropdowns have options that need to be pulled out
			$dropdown_options = Null;

			if($config_field['config_type'] == "dropdown")
			{

				$dropdown_options = array();

				$dropdown_values = explode("|", $config_field['dropdown_values']);

				foreach($dropdown_values as $value)
					$dropdown_options[$value] = $lang['admin_config_'.$config_field['name']."_value_".$value];

			}

			// Form system will do the rest of the work for us
			$form_state["#submit_config_".$config_field['name']] = array(
				"name" => $lang['admin_config_name_'.$config_field['name']],
				"description" => $lang['admin_config_desc_'.$config_field['name']],
				"type" => $config_field['config_type'],
				"value" => _htmlspecialchars($config_field['value']),
				"options" => $dropdown_options,
				"extra_field_contents_left" => $output -> help_button($config_field['name'], False, NULL, "group")
				);

			if($config_field['config_type'] == "text")
				$form_state['#submit_config_'.$config_field['name']]['size'] = 50;

		}

		$form_state['meta']['config_names'] = $config_names;

		$form_state["#submit"] = array(
			"type" => "submit",
			"value" => $lang['admin_config_submit']
			);

		$form = new form($form_state);
		$output -> add($form -> render());

	}

	// ********************************
	// Developer mode - we can add a value to this group
	// ********************************
	if(defined("DEVELOPER"))
	{
	
		$form = new form(array(
							 "meta" => array(
								 "name" => "config_value_add",
								 "title" => $lang['config_value_add'],
								 "validation_func" => "form_config_value_add_validate",
								 "complete_func" => "form_config_value_add_complete"
								 ),
							 "#shortname" => array(
								 "name" => $lang['config_value_add_shortname'],
								 "type" => "text",
								 "required" => True,
								 ),
							 "#name" => array(
								 "name" => $lang['config_value_add_name'],
								 "type" => "text",
								 "required" => True,
								 ),
							 "#description" => array(
								 "name" => $lang['config_value_add_description'],
								 "type" => "text",
								 "required" => True,
								 ),
							 "#type" => array(
								 "name" => $lang['config_value_add_type'],
								 "type" => "dropdown",
								 "options" => array(
									 "text" => "text",
									 "int" => "int",
									 "dropdown" => "dropdown",
									 "yesno" => "yesno",
									 "textarea" => "textarea",
									 ),
								 "required" => True
								 ),
							 
							 "#order" => array(
								 "name" => $lang['config_value_add_order'],
								 "type" => "int"
								 ),
							 "#dropdown_values" => array(
								 "name" => $lang['config_value_add_dropdown_values'],
								 "type" => "textarea"
								 ),
							 
							 "#submit" => array(
								 "type" => "submit"
								 )
							 ));

		$output -> add($form -> render());
		
	}	

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for updating configuration values
 *
 * @param object $form
 */
function form_config_update_complete($form)
{
 
	global $db, $output, $lang, $cache, $page_matches;

	if(!isset($form -> form_state['meta']['config_names']) || !count($form -> form_state['meta']['config_names']))
	{
		$output -> set_error_message($lang['error_no_config_settings']);
		return;
	}


	$config_values = array();

	foreach($form -> form_state['meta']['config_names'] as $config_name)
		$config_values[$config_name] = $form -> form_state['#submit_config_'.$config_name]['value'];

	config_update_config_values($config_group_name, $config_values);

	$output -> redirect(l("admin/config/show_group/".$page_matches['group_name']."/"), $lang['admin_config_updated']);

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for creating a new config value
 *
 * @param object $form
 */
function form_config_value_add_validate($form)
{
   
	global $db, $lang, $page_matches;

	// Check this value doesn't already exist
	$db -> basic_select(
		array(
			"what" => "`name`",
			"table" => "config",
			"where" => "name = '".$db -> escape_string($form -> form_state['#shortname']['value'])."' AND `config_group` = '".$page_matches['group_name']."'",
			"limit" => "1"
			)
		);

	if($db -> num_rows())
		$form -> set_error("shortname", $lang['config_value_add_error_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for creating a new config value
 *
 * @param object $form
 */
function form_config_value_add_complete($form)
{
   
	global $db, $lang, $cache, $output, $page_matches;


	// Put the value in
	$insert = $db -> basic_insert(
		array(
			"table" => "config",
			"data" => array(
				"name" => $form -> form_state['#shortname']['value'],
				"config_group" => $page_matches['group_name'],
				"config_type" => $form -> form_state['#type']['value'],
				"order" => $form -> form_state['#order']['value'],
				"dropdown_values" => $form -> form_state['#dropdown_values']['value']
				)
			)
		);

	if(!$insert)
	{
		$output -> set_error_message($lang['config_value_add_error_insert']);
		return False;
	}


	// Create the phrases
	$insert = $db -> basic_insert(
		array(
			"table" => "language_phrases",
			"data" => array(
				"language_id"	=> LANG_ID,
				"variable_name" => "admin_config_name_".$form -> form_state['#shortname']['value'],
				"group"			=> "admin_config",
				"text"			=> $form -> form_state['#name']['value'],
				"default_text"	=> $form -> form_state['#name']['value']
				)
			)
		);

	if(!$insert)
	{
		$output -> set_error_message($lang['config_value_add_error_phrase']);
		return False;
	}

	$insert = $db -> basic_insert(
		array(
			"table" => "language_phrases",
			"data" => array(
				"language_id"	=> LANG_ID,
				"variable_name" => "admin_config_desc_".$form -> form_state['#shortname']['value'],
				"group"			=> "admin_config",
				"text"			=> $form -> form_state['#description']['value'],
				"default_text"	=> $form -> form_state['#name']['value']
				)
			)
		);

	if(!$insert)
	{
		$output -> set_error_message($lang['config_value_add_error_phrase']);
		return False;
	}

	// Update lang files, cache and redirect back to config place
	require ROOT."admin/common/funcs/languages.funcs.php";
	build_language_files(LANG_ID, "admin_config");        	

	$cache -> update_cache("config");

	$output -> redirect(l("admin/config/show_group/".$page_matches['group_name']."/"), $lang['config_value_add_done']);        

}


/**
 * Backup or import settings
 */
function page_backup()
{

	global $output, $lang, $template_admin;


	// ********************************
	// Page title and breadcrumb
	// ********************************
	$output -> page_title = $lang['admin_menu_config_import'];
	$output -> add_breadcrumb($lang['breadcrumb_importexport'], l("admin/config/backup/"));

	// ********************************
	// Export form
	// ********************************
	$form = new form(array(
						 "meta" => array(
							 "name" => "export_config",
							 "title" => $lang['export_config_title'],
							 "extra_title_contents_left" => $output -> help_button("", True).$template_admin -> form_header_icon("general"),
							 "validation_func" => "form_config_export_validate",
							 "complete_func" => "form_config_export_complete"
							 ),
						 "#filename" => array(
							 "name" => $lang['export_filename'],
							 "type" => "text",
							 "required" => True,
							 "value" => "fsboard-settings.xml",
							 "extra_field_contents_left" => $output -> help_button("export_filename")
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['export_config']
							 )
						 ));

	$output -> add($form -> render());

	// ********************************
	// Import form
	// ********************************
	$form = new form(array(
						 "meta" => array(
							 "name" => "import_config",
							 "title" => $lang['import_config_title'],
							 "enctype" => "multipart/form-data",
							 "validation_func" => "form_config_import_validate",
							 "complete_func" => "form_config_import_complete"
							 ),
						 "#import_file_upload" => array(
							 "name" => $lang['import_upload'],
							 "type" => "file",
							 "extra_field_contents_left" => $output -> help_button("file")
							 ),
						 "#import_file_path" => array(
							 "name" => $lang['import_filename'],
							 "type" => "text",
							 "value" => "includes/fsboard-settings.xml",
							 "extra_field_contents_left" => $output -> help_button("import_filename")
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['import_config']
							 )
						 ));

	$output -> add($form -> render());

	 
}


/**
 * FORM FUNCTION
 * --------------
 * Exporting config values
 *
 * @param object $form
 */
function form_config_export_validate($form)
{

	global $db, $lang;

	// Select config groups
	$form -> form_state['meta']['config_group_q'] = $db -> basic_select(
		array(
			"table" => "config_groups",
			"order" => "`order`"
			)
		);

	if(!$db -> num_rows())
		$form -> set_error("shortname", $lang['getting_config_groups_error']);

}


/**
 * FORM FUNCTION
 * --------------
 * Exporting config values
 *
 * @param object $form
 */
function form_config_export_complete($form)
{

	global $db, $lang;

	$xml = new xml;
	$xml -> export_xml_start();
	$xml -> export_xml_root("configuration_file");

	while($config_group = $db -> fetch_array($form -> form_state['meta']['config_group_q']))
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

	output_file($xml -> export_xml, $form -> form_state['#filename']['value'], "text/xml");

}


/**
 * FORM FUNCTION
 * --------------
 * Importing config values
 *
 * @param object $form
 */
function form_config_import_validate($form)
{

	global $output, $lang;

	// Check if we've upload, or supplied a filename and pass the path onto the complete form.
	// Otherwise Error out.
	if(file_exists($_FILES['import_file_upload']['tmp_name']))
		$form -> form_state['meta']['real_filename'] = $_FILES['import_file_upload']['tmp_name'];
	elseif(file_exists(ROOT.$form -> form_state['#import_file_path']['value']))
		$form -> form_state['meta']['real_filename'] = ROOT.$form -> form_state['#import_file_path']['value'];
	else
		$form -> set_error("import_file_path", 
						   $output -> replace_number_tags($lang['xml_file_not_found'], $form -> form_state['#import_file_path']['value'])
			);

}



/**
 * FORM FUNCTION
 * --------------
 * Importing config values
 *
 * @param object $form
 */
function form_config_import_complete($form)
{

	global $output, $lang, $db, $template_global, $cache;

	$get_error = import_config_xml(
		file_get_contents($form -> form_state['meta']['real_filename'])
		);

	// If we have version mismatch
	if((string)$get_error == "VERSION")
	{
		$form -> set_error("import_file_path", $lang['xml_version_mismatch']);
		return false;
	}

	// Update cache
	$cache -> update_cache("config");

	$form -> form_state['meta']['redirect'] = array(
		"url" => l("admin/config/backup/"),
		"message" => $lang['import_done_message']
	);

}


?>