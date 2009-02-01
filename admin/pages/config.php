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

/*
$secondary_mode = (isset($page_matches['mode'])) ? $page_matches['mode'] : "";


switch($secondary_mode)
{
	case "group":
		page_group();
		break;

	case "edit":
		do_edit();
		break;

	case "revert":
		do_revert();
		break;

	case "importexport":
		page_import_export();
		break;

	case "doimport":
		do_import();
		break;

	case "doexport":
		do_export();
		break;

	// ---------
	// Dev stuff
	// ---------
	case "newgroup":
		do_new_config_group();
		break;

	case "newvalue":
		do_new_config_value();
		break;
				
	default:
		page_main();

}*/


/**
 * Main page - lets us select a category from a list
 */
function page_main()
{

	global $db, $output, $lang;

	// ------------------
	// Set page title
	// ------------------
	$output -> page_title = $lang['admin_config_title'];

	// ------------------
	// Our form
	// ------------------
/*
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
*/
	$db -> basic_select(array(
							"table" => "config_groups",
							"where" => "`order` >= 0",
							"order" => "`order`",
							"dir" => "ASC"
							));

	if(!$db -> num_rows())
	{
		$output -> set_error_message($lang['error_no_config_groups']);
		return False;
	}

	$groups = array();

	while($group = $db -> fetch_array())
		$groups[$group['name']] = $lang['config_dropdown_'.$group['name']];

	$form = new form(array(
						 "meta" => array(
							 "name" => "config_select",
							 "title" => $lang['admin_config_title'],
							 "description" => $lang['admin_config_page_message'],
							 "validation_func" => "form_config_select_validate",
							 "complete_func" => "form_config_select_complete"
							 ),
						 "#config_menu" => array(
							 "name" => $lang['admin_config_menu_input'],
							 "type" => "dropdown",
							 "options" => $groups,
							 "size" => 10,
							 "required" => True
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['admin_config_go']
							 )
						 ));

	$output -> add($form -> render());

/*
	// Create classes
	$table = new table_generate;
	$form = new form_generate;

	$output -> add(
		$form -> start_form("configpageselect", ROOT."admin/index.php?m=config&amp;m2=group").
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		$table -> add_top_table_header($lang['admin_config_title'], 2, "general").
		// ---------------
		// Main bit
		// ---------------
		$table -> add_row(
			array(
				array($lang['admin_config_page_message'],"60%"),
				array(config_menu('', true),"40%")
			)
		, "normalcell").
		// ---------------
		// Submit
		// ---------------
		$table -> add_submit_row($form, "submit", $lang['admin_config_go'], 2).
		$table -> end_table().
		$form -> end_form()
	);
*/
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
   
	global $db, $lang;

	if(!$form -> form_state['#config_menu']['value'])
		return;

	$db -> basic_select(array(
							"table" => "config_groups",
							"where" => "name = '".$db -> escape_string($form -> form_state['#config_menu']['value'])."'",
							"limit" => "1"
							));

	if(!$db -> num_rows())
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
   
	global $db, $lang;

	// Check this group doesn't already exist
	$db -> basic_select(
		array(
			"what" => "`name`",
			"table" => "config_groups",
			"where" => "name = '".$db -> escape_string($form -> form_state['#shortname']['value'])."'",
			"limit" => "1"
			)
		);

	if($db -> num_rows())
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
   
	global $db, $lang, $cache, $output;

	// Put the group in
	$insert = $db -> basic_insert(
		array(
			"table" => "config_groups",
			"data" => array(
				"name" => $form -> form_state['#shortname']['value'],
				"order" => $form -> form_state['#order']['value'],
				)
			)
		);

	if(!$insert)
	{
		$output -> set_error_message($lang['config_group_add_error_insert']);
		return False;
	}

	// Create the phrase
	$insert = $db -> basic_insert(
		array(
			"table" => "language_phrases",
			"data" => array(
				"language_id"	=> LANG_ID,
				"variable_name" => "config_dropdown_".$form -> form_state['#shortname']['value'],
				"group"			=> "admin_config",
				"text"			=> $form -> form_state['#name']['value'],
				"default_text"	=> $form -> form_state['#name']['value']
				)
			)
		);

	if(!$insert)
	{
		$output -> set_error_message($lang['config_group_add_error_phrase']);
		return False;
	}

	// Update lang files, cache and redirect back to config place
	require ROOT."admin/common/funcs/languages.funcs.php";
	build_language_files(LANG_ID, "admin_config");        	

	$cache -> update_cache("config");

	$output -> redirect(l("admin/config/"), $lang['config_group_add_done']);        

}



//***********************************************
// Create a new configuration group
//***********************************************
/*
function do_new_config_group()
{

	global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
		"name" 		=> $_POST['name'],
		"shortname"	=> $_POST['shortname'],
		"order"		=> $_POST['order']
	);

	$input = array_map("trim", $input);
	
	// Empty?
	if($input['name'] == "" || $input['shortname'] == "")
	{
		$output -> add($template_admin -> normal_error($lang['config_group_add_error_input']));
		page_main();
		return;
	}

	// Try inputting
	$group_data = array(
		"name" 	=> $input['shortname'],
		"order" => $input['order']
	);
	
	if(!$db -> basic_insert("config_groups", $group_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_group_add_error_insert']));
		page_main();
		return;
	}
        
	
	// Create the phrase
	$phrase_data = array(
		"language_id"	=> LANG_ID,
		"variable_name" => "config_dropdown_".$input['shortname'],
		"group"			=> "admin_config",
		"text"			=> $input['name'],
		"default_text"	=> $input['name']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_group_add_error_phrase']));
		page_language_groups();
		return;
	}
	
	require ROOT."admin/common/funcs/languages.funcs.php";
	
	build_language_files(LANG_ID, "admin_config");        	

	$output -> redirect(ROOT."admin/index.php?m=config", $lang['config_group_add_done']);

}
*/

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
	$db -> basic_select(array(
							"table" => "config_groups",
							"where" => "`order` >= 0",
							"order" => "`order`",
							"dir" => "ASC"
							));

	if(!$db -> num_rows())
	{
		$output -> set_error_message($lang['error_no_config_groups']);
		return False;
	}

	$groups = array();

	while($group = $db -> fetch_array())
		$groups[$group['name']] = $lang['config_dropdown_'.$group['name']];

	$form = new form(array(
						 "meta" => array(
							 "name" => "small_config_select",
							 "title" => $lang['admin_config_title'],
							 "validation_func" => "form_config_select_validate",
							 "complete_func" => "form_config_select_complete"
							 ),
						 "#config_menu" => array(
							 "name" => $lang['admin_config_menu_input'],
							 "type" => "dropdown",
							 "options" => $groups,
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
	$form_state = array(
		"meta" => array(
			"name" => "configuration_update",
			"title" => $lang['config_dropdown_'.$config_group_name],
			"complete_func" => "form_config_update_complete"
			)
		);

	$db -> basic_select(array(
							"table" => "config",
							"where" => "config_group = '".$db -> escape_string($config_group_name)."'",
							"order" => "`order` ASC"
							));
	 
	if(!$db -> num_rows())
		$output -> set_error_message($lang['error_no_config_settings']);
	else
	{

		// go through all the fields in turn
		$config_names = array();

		while($config_field = $db -> fetch_array())
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
				"options" => $dropdown_options
				);

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
//									 "value" => $lang['admin_config_go']
								 )
							 ));

		$output -> add($form -> render());
/*
		$output -> add(
			$form -> start_form("newvalue", ROOT."admin/index.php?m=config&amp;m2=newvalue&amp;group=".$_POST['group']).
            $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['config_value_add'], 2).
			$table -> simple_input_row_text($form, $lang['config_value_add_shortname'], "shortname", "").
			$table -> simple_input_row_text($form, $lang['config_value_add_name'], "name", "").
			$table -> simple_input_row_text($form, $lang['config_value_add_description'], "description", "").
			
			$table -> simple_input_row_dropdown($form, $lang['config_value_add_type'], "config_type", "",
				array("text", "int", "dropdown", "yesno", "textbox"),
				array("text", "int", "dropdown", "yesno", "textarea")				
			).
			
			$table -> simple_input_row_int($form, $lang['config_value_add_order'], "order", "").
			$table -> simple_input_row_textbox($form, $lang['config_value_add_dropdown_values'], "dropdown_values", "").			
			$table -> add_submit_row($form).
			$table -> end_table().
			$form -> end_form()
		);	
*/				
	}	


/*
	// Create classes
	$table = new table_generate;
	$form = new form_generate;


	// ********************************
	// Group dropdown
	// ********************************
	$output -> add(
	$form -> start_form("configpageselect", ROOT."admin/index.php?m=config&amp;m2=group").
	$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	$table -> add_top_table_header($lang['admin_config_title'], 2, "general").
	// ---------------
	// Dropdown
	// ---------------
	$table -> add_basic_row(
	config_menu($_POST['group']).$form->submit("submit", $lang['admin_config_go'])
	, "normalcell",  "padding : 5px;", "left", "100%").
	// ---------------
	// Input
	// ---------------
	$table -> end_table().
	$form -> end_form()
	);

	// *********************
	// Set page title
	// *********************


	// ********************************
	// Settings
	// ********************************
	$output -> add(
	$form -> start_form("changeconfig", ROOT."admin/index.php?m=config&amp;m2=edit&amp;group=".$_POST['group']).
	$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	// ---------------
	// Title
	// ---------------
	$table -> add_basic_row($lang['config_dropdown_'.$_POST['group']], "strip1",  "", "left", "100%", 2)
	);
*/

/*
	// ********************************
	// Now we're going to go through all the settings we want to edit.
	// And generate the HTML for it.
	// ********************************
	// First grab them all
	$all_config = $db -> query("select * from ".$db -> table_prefix."config where config_group = '".$_POST['group']."' order by `order` asc");
	 
	// go through all of the config vals
	while($config_array = $db -> fetch_array($all_config))
	{

		$config_array['value'] = _htmlspecialchars($config_array['value']);

		// What type it is?
		switch($config_array['config_type'])
		{

			case "text":

				$input = $table -> simple_input_row_text(
				$form,
					"<font class=\"small_text\">".$lang['admin_config_desc_'.$config_array['name']]."</font>",
					"submit_config[".$config_array['name']."]",
				$config_array['value'],
				$config_array['name']);
				break;

			case "textbox":
				$input = $table -> simple_input_row_textbox(
				$form,
					"<font class=\"small_text\">".$lang['admin_config_desc_'.$config_array['name']]."</font>",
					"submit_config[".$config_array['name']."]",
				$config_array['value'],
				3,
				$config_array['name']);
				break;

			case "int":
				$input = $table -> simple_input_row_int(
				$form,
					"<font class=\"small_text\">".$lang['admin_config_desc_'.$config_array['name']]."</font>",
					"submit_config[".$config_array['name']."]",
				$config_array['value'],
				$config_array['name']);
				break;

			case "yesno":
				$input = $table -> simple_input_row_yesno(
				$form,
					"<font class=\"small_text\">".$lang['admin_config_desc_'.$config_array['name']]."</font>",
					"submit_config[".$config_array['name']."]",
				$config_array['value'],
				$config_array['name']);
				break;

			case "dropdown":

				// We need to workout the text in the dropdown
				$dropdown_text = array();

				// Get the values from the db
				$dropdown_values_array = explode("|", $config_array['dropdown_values']);

				// Reset counter
				$a = 0;

				// Go through 'em all
				foreach($dropdown_values_array as $dropdown_value)
				{

					// Save text
					$dropdown_text[$a] = $lang['admin_config_'.$config_array['name'].'_value_'.$dropdown_value];

					// Counter increase
					$a ++;

				}

				$input = $table -> simple_input_row_dropdown(
				$form,
					"<font class=\"small_text\">".$lang['admin_config_desc_'.$config_array['name']]."</font>",
					"submit_config[".$config_array['name']."]",
				$config_array['value'],
				$dropdown_values_array, $dropdown_text,
				$config_array['name']);

				break;

		}

		//Output the HTML for each setting
		$output -> add(
		$table -> add_basic_row(
                                "<div style=\"float: right; clear: left;\">[ <a href=\"index.php?m=config&amp;m2=revert&amp;group=".$_POST['group']."&amp;name=".$config_array['name']."\" onClick=\"return confirm('".$lang['reset_confirm'].$lang['admin_config_name_'.$config_array['name']]."')\" title=\"".$lang['reset_value_info']."\">".$lang['reset_value']."</a> ]</div>                                       
                                ".$lang['admin_config_name_'.$config_array['name']]
		, "strip2",  "", "left", "100%", "2").

		$input
		);

	}

	// ********************************
	// Chuck out the submit button
	// ********************************
	$output -> add(
	$table -> add_basic_row(
	$form -> submit("submit", $lang['admin_config_submit']).
	$form -> reset("submit", $lang['admin_config_reset'])
	, "strip3",  "padding : 5px", "center", "100%", "2").
	$table -> end_table().
	$form -> end_form()
	);

	// As delveloper we can add a value
	if(defined("DEVELOPER"))
	{
	
		$output -> add(
			$form -> start_form("newvalue", ROOT."admin/index.php?m=config&amp;m2=newvalue&amp;group=".$_POST['group']).
            $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['config_value_add'], 2).
			$table -> simple_input_row_text($form, $lang['config_value_add_shortname'], "shortname", "").
			$table -> simple_input_row_text($form, $lang['config_value_add_name'], "name", "").
			$table -> simple_input_row_text($form, $lang['config_value_add_description'], "description", "").
			
			$table -> simple_input_row_dropdown($form, $lang['config_value_add_type'], "config_type", "",
				array("text", "int", "dropdown", "yesno", "textbox"),
				array("text", "int", "dropdown", "yesno", "textarea")				
			).
			
			$table -> simple_input_row_int($form, $lang['config_value_add_order'], "order", "").
			$table -> simple_input_row_textbox($form, $lang['config_value_add_dropdown_values'], "dropdown_values", "").			
			$table -> add_submit_row($form).
			$table -> end_table().
			$form -> end_form()
		);	
				
	}	
*/
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


	foreach($form -> form_state['meta']['config_names'] as $config_name)
		$db -> basic_update(
			array(
				"table" => "config",
				"data" => array("value" => _html_entity_decode($form -> form_state['#submit_config_'.$config_name]['value'])),
				"where" => "`name` = '".$config_name."' AND `config_group` = '".$page_matches['group_name']."'"
				)
			);

	$cache -> update_cache("config");

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



//***********************************************
// Create a new configuration value
//***********************************************
/*
function do_new_config_value()
{

	global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
		"name" 				=> $_POST['name'],
		"description" 		=> $_POST['description'],
		"shortname"			=> $_POST['shortname'],
		"config_type"		=> $_POST['config_type'],
		"order"				=> $_POST['order'],
		"dropdown_values"	=> $_POST['dropdown_values'],
		"config_group"		=> $_GET['group']	
	);

	$input = array_map("trim", $input);
	
	// Check group
	$db -> basic_select("config_groups", "name", "name='".$input['config_group']."'");
	
	if($db -> num_rows() < 1)
	{
		$output -> add($template_admin -> critical_error($lang['config_value_add_error_no_group']));
		page_main();
		return;
	}
        	
	// Empty?
	if($input['name'] == "" || $input['shortname'] == "")
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_input']));
		page_main();
		return;
	}

	// Try inputting
	$value_data = array(
		"name" 				=> $input['shortname'],
		"config_group" 		=> $input['config_group'],
		"config_type" 		=> $input['config_type'],
		"dropdown_values" 	=> $input['dropdown_values'],
		"order" 			=> $input['order'],
		"value"				=> "",
		"default"			=> ""
	);
	
	if(!$db -> basic_insert("config", $value_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_insert']));
		page_main();
		return;
	}
        
	
	// Create the name phrase
	$phrase_data = array(
		"language_id"	=> LANG_ID,
		"variable_name" => "admin_config_name_".$input['shortname'],
		"group"			=> "admin_config",
		"text"			=> $input['name'],
		"default_text"	=> $input['name']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_phrase']));
		page_language_groups();
		return;
	}

	// Create the description phrase
	$phrase_data = array(
		"language_id"	=> LANG_ID,
		"variable_name" => "admin_config_desc_".$input['shortname'],
		"group"			=> "admin_config",
		"text"			=> $input['description'],
		"default_text"	=> $input['description']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_phrase']));
		page_language_groups();
		return;
	}
	
	require ROOT."admin/common/funcs/languages.funcs.php";
	
	build_language_files(LANG_ID, "admin_config");        	

	// Done, redirect
	$output -> redirect(ROOT."admin/index.php?m=config&m2=group&group=".$input['config_group'], $lang['config_value_add_done']);

}
*/


//***********************************************
// We're submitting an edit
//***********************************************
/*
function do_edit()
{

	global $db, $output, $lang, $cache;

	$submit_config = array_map('trim', $_POST['submit_config']);

	foreach($submit_config as $config_name => $config_value)
	{
		 
		$info = array(
                        "value" => _html_entity_decode($config_value)
		);

		$db -> basic_update("config", $info, "name='".$config_name."'");

	}

	// Update cache
	$cache -> update_cache("config");


	$output -> redirect(ROOT."admin/index.php?m=config&amp;m2=group&amp;group=".$_GET['group'], $lang['admin_config_updated']);

}
*/

//***********************************************
// We're reverting a setting to it's default
//***********************************************
function do_revert()
{

	global $db, $output, $lang, $template_admin, $cache;

	// Grab the setting we want
	$single_config = $db -> query("select `default`, config_group from ".$db -> table_prefix."config where name = '".$_GET['name']."'");
	$config_array = $db -> fetch_array($single_config);

	// Check it exists
	if($db -> num_rows($single_config) > 0)
	{

		$info = array(
                        "value" => $config_array['default']
		);

		// Reset the value
		$db -> basic_update("config", $info, "name='".$_GET['name']."'");

		// Update cache
		$cache -> update_cache("config");

		// Redirect
		$output -> redirect(ROOT."admin/index.php?m=config&amp;m2=group&amp;group=".$_GET['group'], $lang['config_reset_success']);

	}
	else // Doesn't exist
	{

		// Give error
		$output -> add($template_admin -> critical_error($lang['reset_error']));
		page_group();

	}


}


/**
 * Backup or import settings
 */
function page_backup()
{

	global $output, $lang;


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
//							 "validation_func" => "form_config_select_validate",
//							 "complete_func" => "form_config_select_complete"
							 ),
						 "#filename" => array(
							 "name" => $lang['export_filename'],
							 "type" => "text",
							 "required" => True,
							 "value" => "fsboard-settings.xml"
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
							 "enctype" => "multipart/form-data"
//							 "validation_func" => "form_config_select_validate",
//							 "complete_func" => "form_config_select_complete"
							 ),
						 "#import_file_upload" => array(
							 "name" => $lang['import_upload'],
							 "type" => "file"
							 ),
						 "#import_file_path" => array(
							 "name" => $lang['import_filename'],
							 "type" => "text",
							 "value" => "includes/fsboard-settings.xml"
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['import_config']
							 )
						 ));

	$output -> add($form -> render());

/*
	// *********************
	// Set page title
	// *********************
	$output -> page_title = $lang['admin_menu_config_import'];

	$output -> add_breadcrumb($lang['breadcrumb_importexport'], "index.php?m=config&amp;m2=importexport");

	// Create classes
	$table = new table_generate;
	$form = new form_generate;

	// ----------------
	// EXPORT FORM
	// ----------------
	$output -> add(
	$form -> start_form("exportconfig", ROOT."admin/index.php?m=config&amp;m2=doexport", "post", false, true).
	$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	$table -> add_top_table_header($lang['export_config_title'], 2, "general").
	$table -> simple_input_row_text($form, $lang['export_filename'], "filename", "fsboard-settings.xml", "export_filename").
	$table -> add_submit_row($form, "submit", $lang['export_config']).
	$table -> end_table().
	$form -> end_form()
	);


	// ----------------
	// IMPORT FORM
	// ----------------
	$output -> add(
	$form -> start_form("importconfig", ROOT."admin/index.php?m=config&amp;m2=doimport", "post", true).
	$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	$table -> add_top_table_header($lang['import_config_title'], 2).
	$table -> simple_input_row_file($form, $lang['import_upload'], "filename", "file").
	$table -> simple_input_row_text($form, $lang['import_filename'], "filename", "includes/fsboard-settings.xml", "import_filename").
	$table -> add_submit_row($form, "submit", $lang['import_config']).
	$table -> end_table().
	$form -> end_form()
	);
*/
	 
}



//***********************************************
// Export some settings
//***********************************************
function do_export()
{

	global $output, $lang, $db, $template_admin;


	// *************************
	// Start XML'ing
	// *************************
	$xml = new xml;
	$xml -> export_xml_start();
	$xml -> export_xml_root("configuration_file");

	// *************************
	// Select the configuration groups
	// *************************
	$select_config_groups = $db -> query("select * from ".$db -> table_prefix."config_groups order by `order` asc");

	if($db -> num_rows($select_config_groups) < 1)
	{
		$output -> add($template_admin -> critical_error($lang['getting_config_groups_error']));
		page_import_export();
		return;
	}

	// *************************
	// Spin through groups
	// *************************
	while($config_group_array = $db -> fetch_array($select_config_groups))
	{

		// *************************
		// Start off the group
		// *************************
		$xml -> export_xml_start_group(
			"config_group",
			array(
				"name" => $config_group_array['name'],
				"order" => $config_group_array['order']
			)
		);

		// *************************
		// Select the configuration in this group
		// *************************
		$select_config = $db -> query("select * from ".$db -> table_prefix."config where config_group='".$config_group_array['name']."'");

		if($db -> num_rows($select_config) > 0)
		{

			while($config_array = $db -> fetch_array($select_config))
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

				// Add the default value entry
				$xml -> export_xml_add_group_entry(
					"config_default",
					array("name" => $config_array['name']),
					trim($config_array['default'])
				);

			}

		}
		 
		// *************************
		// Finish group
		// *************************
		$xml -> export_xml_generate_group();

	}


	// *************************
	// Finish XML'ing
	// *************************
	$xml -> export_xml_generate();


	// *************************
	// Work out output file name
	// *************************
	if($_POST['filename'] == '')
		$filename = "fsboard-settings.xml";
	else
		$filename = $_POST['filename'];


	// *************************
	// Chuck the file out
	// *************************
	output_file($xml -> export_xml, $filename, "text/xml");

}


//***********************************************
// Trying to import the data from XML
//***********************************************
function do_import()
{

	global $output, $lang, $db, $template_admin, $cache;

	// Get file from upload
	if(file_exists($_FILES['file']['tmp_name']))
		$xml_contents = file_get_contents($_FILES['file']['tmp_name']);
	// Get file from server
	elseif(file_exists(ROOT.$_POST['filename']))
		$xml_contents = file_get_contents(ROOT.$_POST['filename']);
	// No file - ERROR DANGER DANGER WILL ROBINSON
	else
	{
		$output -> add($template_admin -> normal_error($lang['xml_file_not_found'].$_POST['filename']));
		page_import_export();
		return;
	}

	// *************************
	// Dew it.
	// *************************
	$get_error = import_config_xml($xml_contents);

	// If we have version mismatch
	if((string)$get_error == "VERSION")
	{
		$output -> add($template_admin -> critical_error($lang['xml_version_mismatch']));
		return false;
	}

	// Update cache
	$cache -> update_cache("config");

	$output -> add($template_admin -> message($lang['import_done_title'], $lang['import_done_message']));

}


//***********************************************
// Create a new configuration value
//***********************************************
/*
function do_new_config_value()
{

	global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
		"name" 				=> $_POST['name'],
		"description" 		=> $_POST['description'],
		"shortname"			=> $_POST['shortname'],
		"config_type"		=> $_POST['config_type'],
		"order"				=> $_POST['order'],
		"dropdown_values"	=> $_POST['dropdown_values'],
		"config_group"		=> $_GET['group']	
	);

	$input = array_map("trim", $input);
	
	// Check group
	$db -> basic_select("config_groups", "name", "name='".$input['config_group']."'");
	
	if($db -> num_rows() < 1)
	{
		$output -> add($template_admin -> critical_error($lang['config_value_add_error_no_group']));
		page_main();
		return;
	}
        	
	// Empty?
	if($input['name'] == "" || $input['shortname'] == "")
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_input']));
		page_main();
		return;
	}

	// Try inputting
	$value_data = array(
		"name" 				=> $input['shortname'],
		"config_group" 		=> $input['config_group'],
		"config_type" 		=> $input['config_type'],
		"dropdown_values" 	=> $input['dropdown_values'],
		"order" 			=> $input['order'],
		"value"				=> "",
		"default"			=> ""
	);
	
	if(!$db -> basic_insert("config", $value_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_insert']));
		page_main();
		return;
	}
        
	
	// Create the name phrase
	$phrase_data = array(
		"language_id"	=> LANG_ID,
		"variable_name" => "admin_config_name_".$input['shortname'],
		"group"			=> "admin_config",
		"text"			=> $input['name'],
		"default_text"	=> $input['name']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_phrase']));
		page_language_groups();
		return;
	}

	// Create the description phrase
	$phrase_data = array(
		"language_id"	=> LANG_ID,
		"variable_name" => "admin_config_desc_".$input['shortname'],
		"group"			=> "admin_config",
		"text"			=> $input['description'],
		"default_text"	=> $input['description']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['config_value_add_error_phrase']));
		page_language_groups();
		return;
	}
	
	require ROOT."admin/common/funcs/languages.funcs.php";
	
	build_language_files(LANG_ID, "admin_config");        	

	// Done, redirect
	$output -> redirect(ROOT."admin/index.php?m=config&m2=group&group=".$input['config_group'], $lang['config_value_add_done']);

}
*/

?>