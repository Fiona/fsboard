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


$output -> add_breadcrumb($lang['breadcrumb_config'], "index.php?m=config");


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

}


/**
 * Main page - lets us select a category from a list
 */
function page_main()
{

	global $output, $lang;

	// ------------------
	// Set page title
	// ------------------
	$output -> page_title = $lang['admin_config_title'];

	// ------------------
	// Our form
	// ------------------
	$form = new form(array(
        "meta" => array(
			"name" => "config_select",
        	"title" => $lang['admin_config_title'],
//			"validation_func" => "form_register_validate",
//			"complete_func" => "form_register_complete"	
        ),
		"config_menu" => array(
			"name" => "config_menu",
			"title" => $lang['admin_config_page_message'],
			"type" => "dropdown"
		)
	));


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

	// As delveloper we can add a group
	if(defined("DEVELOPER"))
	{
	
		$output -> add(
			$form -> start_form("newgroup", ROOT."admin/index.php?m=config&amp;m2=newgroup").
            $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['config_group_add'], 2).
			$table -> simple_input_row_text($form, $lang['config_group_add_name'], "name", "").
			$table -> simple_input_row_text($form, $lang['config_group_add_shortname'], "shortname", "").
			$table -> simple_input_row_int($form, $lang['config_group_add_order'], "order", "").
			$table -> add_submit_row($form).
			$table -> end_table().
			$form -> end_form()
		);	
				
	}
	*/		
}




//***********************************************
// Just one of the groups thanks
//***********************************************
function page_group()
{

	global $db, $output, $lang, $template_admin;

	$_POST['group'] = (isset($_GET['group'])) ? $_GET['group'] : $_POST['group'];

	$output -> add_breadcrumb($lang['config_dropdown_'.$_POST['group']], "index.php?m=config&amp;m2=group&amp;group=".$_POST['group']);

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
	$output -> page_title = $lang['config_dropdown_'.$_POST['group']];

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
		/*                        $table -> add_row(
		 array(
		 array("<font class=\"small_text\">".$lang['admin_config_desc_'.$config_array['name']]."</font>", "50%"),
		 array($input, "50%")
		 )
		 ,"normalcell")
		 */
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
	
}


//***********************************************
// We're submitting an edit
//***********************************************
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


//***********************************************
// Importing/Exporting settings
//***********************************************
function page_import_export()
{

	global $output, $lang;

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
// Create a new configuration group
//***********************************************
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


//***********************************************
// Create a new configuration value
//***********************************************
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

?>