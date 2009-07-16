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
 * Core install file
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Install
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Inital tasks
//***********************************************
// Needed definitions
define('ROOT', "../");
define('FSBOARD', 1);
define('INSTALL', 1);
define('TIME', time());
define('DEFAULT_LANGUAGE', "English UK");

// Uncomment this if really needed
define('DEBUG_MODE', 1);
// If uncommented this wont actually create any files or edit the DB 
//define('DUMMY_MODE', 1); 

// Check if the installer is locked
if(file_exists(ROOT.'install/install.no'))
	die("The installer is locked, this indicaties that FSBoard has already been installed. Visit your message board <a href=\"".ROOT."index.php\">here</a>.");

// Some files
require ROOT.'common/funcs/global.funcs.php';
require ROOT.'install/install_output.php';
require ROOT.'install/install_functions.php';
require ROOT.'common/class/xml.class.php';
require ROOT.'common/class/form_table.class.php';

// Custom error output
set_error_handler('error_handler');


$output = new install_output;

$fsboard_install_version = "0.1";
$install_step_fail = false;

start_debug_timer();

$main_form = new form_generate;
$main_form -> ignore_started = true;


//***********************************************
// Mess with magic quotes
//***********************************************

/**
 * Applies stripslashes() on an array af values, 
 * also traverses multi-dimensional arrays.
 * 
 * This function is fed $_GET, $_POST, $_COOKIES and $_FILES.  
 * 
 * @param array &$variable Array of variables to strip slashes on.
 */
function magic_quotes_gpc_stripslashes(&$variable)
{

	if(is_array($variable))
	{
        
		foreach($variable as $key => $val)
		{
                
			if(is_array($val))
				$variable[$key] = magic_quotes_gpc_stripslashes($val);
			else 
				$variable[$key] = stripslashes($val);
                        
		}
        
	}
        
	return $variable;

}


// Less than version 6 differences
if(version_compare(PHP_VERSION, '6.0.0-dev', '<'))
{

	// Sort out gpc magic quotes
	if(get_magic_quotes_gpc())
	{
		
		// Fix the arrays
		magic_quotes_gpc_stripslashes($_GET);
		magic_quotes_gpc_stripslashes($_POST);
		magic_quotes_gpc_stripslashes($_COOKIE);
		magic_quotes_gpc_stripslashes($_FILES);
	
	}

	// Go away
	set_magic_quotes_runtime(0);
	
}	



//***********************************************
// Language work out
//***********************************************
$lang = array();

$post_lang = (isset($_POST['language'])) ? trim($_POST['language']) : "";

if(!$post_lang)
	$selected_language = DEFAULT_LANGUAGE;
else
	if(file_exists(ROOT."install/languages/".$post_lang.".lang.php"))
		$selected_language = $post_lang;
	else        
		$selected_language = DEFAULT_LANGUAGE;

require ROOT."install/languages/".$selected_language.".lang.php";

define('CURRENT_LANGUAGE', $selected_language);


//***********************************************
// What step?
//***********************************************
$current_step = (isset($_POST['step'])) ? $_POST['step'] : "1";

// Title tag
$output -> page_title = $lang['step_'.$current_step.'_name']; 


switch($current_step)
{
//***********************************************
// STEP 1
//***********************************************
case 1:

	// Opening messages
	$output -> page_output .= $lang['step_1_message_a'];
	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_1_message_b'];
	$output -> page_output .= "<div style=\"margin:10px;\" align=\"center\">";
                
	// Language change form
	$dh  = opendir(ROOT."install/languages");
                
	while(false !== ($filename = readdir($dh)))
	{

		if($filename == '.' || $filename == '..')
			continue;

		if(!is_dir(ROOT."install/languages".$filename) && _substr($filename, -9) == ".lang.php")
		{
			$lang_name = _substr($filename, 0, -9);

			$dropdown_values[] = $lang_name;        
			$dropdown_text[] = $lang_name;        
		}
                        
	}

	$form = new form_generate;
	$output -> page_output .= $form -> start_form("changelang", ROOT."install/index.php", "post");
	$output -> page_output .= $lang['step_1_language']." ".$form -> input_dropdown("language", $selected_language, $dropdown_values, $dropdown_text, "inputtext", "50%");
	$output -> page_output .= " ".$form -> submit("submit", $lang['step_1_go']).$form -> end_form()."</div>";

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_1_message_c'];
                
	break;

//***********************************************
// STEP 2
//***********************************************
case 2:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_2_message']."</p>";
	$output -> page_output .= "<hr />";

	step_2_check_file("db_config.php");
	step_2_check_file("languages/");
	step_2_check_file("templates/");
	step_2_check_file("cache/");
	step_2_check_file("upload/avatar/");
	step_2_check_file("upload/emoticon/");
	step_2_check_file("upload/post_icon/");
	step_2_check_file("upload/user_avatar/");
	step_2_check_file("install/");

	$output -> page_output .= "<hr />";

	if($install_step_fail)
		$output -> page_output .= "<p>".$lang['step_2_fail']."</p>";
	else
		$output -> page_output .= "<p>".$lang['step_2_done']."</p>";

	break;

//***********************************************
// STEP 3
//***********************************************
case 3:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_3_message']."</p>";
	$output -> page_output .= "<hr />";
                
	$output -> page_output .= $output -> install_step_3_form(
		array(
			"sql_db_type" => "",
			"sql_server" => "",
			"sql_port" => "",
			"sql_db_name" => "",
			"sql_username" => "",
			"sql_password" => "",
			"sql_table_prefix" => "fsb_"
			)
		);
                                
	break;

//***********************************************
// STEP 4
//***********************************************
case 4:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_4_message']."</p>";
	$output -> page_output .= "<hr />";
                
	// -------------------------------
	// Check inputted stuff
	// -------------------------------
	$install_values = array(
		"sql_db_type" => $_POST['sql_db_type'],
		"sql_server" => trim($_POST['sql_server']),
		"sql_port" => trim($_POST['sql_port']),
		"sql_db_name" => trim($_POST['sql_db_name']),
		"sql_username" => trim($_POST['sql_username']),
		"sql_password" => $_POST['sql_password'],
		"sql_table_prefix" => trim($_POST['sql_table_prefix'])
		);                

	foreach($install_values as $key => $val)
	{
                        
		if($val == "" && ($key != "sql_table_prefix" && $key != "sql_port"))
		{

			step_4_error($lang['step_4_checking_input'], $lang['step_4_empty_value']);
			break 2;
                        
		}
                        
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_4_checking_input'], $lang['step_4_ok']);
                
	// -------------------------------
	// Check SQL connection
	// -------------------------------
	if(file_exists(ROOT."db/".$install_values['sql_db_type']."/database.class.php"))
	{

		$output -> page_output .= $output -> install_step_action(true, $lang['step_4_check_sql_type'], $lang['step_4_ok']);
                
		require ROOT."db/".$install_values['sql_db_type']."/database.class.php";                        
		$db = new database;

		// Get a connection
		if(!$db -> connect($install_values['sql_server'], "",  $install_values['sql_username'], $install_values['sql_password'], $install_values['sql_table_prefix'], $install_values['sql_port'], TRUE))
		{
                        
			step_4_error($lang['step_4_connecting_to_sql'], $lang['step_4_sql_connect_error']);
			break;
                                                        
		}

		$output -> page_output .= $output -> install_step_action(true, $lang['step_4_connecting_to_sql'], $lang['step_4_ok']);

		// Select active database
		if(!$db -> select_db($install_values['sql_db_name'], TRUE))
		{
                        
			step_4_error($lang['step_4_selecting_db'], $lang['step_4_sql_error_selecting_db']);
			break;
                                                        
		}

		$output -> page_output .= $output -> install_step_action(true, $lang['step_4_selecting_db'], $lang['step_4_ok']);

	}
	else                   
	{
                        
		step_4_error($lang['step_4_check_sql_type'], $lang['step_4_sql_type_not_exist']);
		break;
                        
	}

	// -------------------------------
	// Create config file
	// -------------------------------
	// Preparing file contents
	$db_config =
		"<"."?php
\$DB_CONFIG['host'] = '".$install_values['sql_server']."';
\$DB_CONFIG['port'] = '".$install_values['sql_port']."';
\$DB_CONFIG['database'] = '".$install_values['sql_db_name']."';
\$DB_CONFIG['username'] = '".$install_values['sql_username']."';
\$DB_CONFIG['password'] = '".$install_values['sql_password']."';
\$DB_CONFIG['db type'] = '".$install_values['sql_db_type']."';
\$DB_CONFIG['table prefix'] = '".$install_values['sql_table_prefix']."';
define('DBCONFIG_PRESENT', 1);
?".">";
                
	if(!defined("DUMMY_MODE"))
	{
                        
		if(!$fh = @fopen(ROOT."db_config.php", "w"))
		{
                        
			step_4_error($lang['step_4_opening_dbconfig'], $lang['step_4_error_opening_db_config']);
			break;
                                                        
		}                        

		$output -> page_output .= $output -> install_step_action(true, $lang['step_4_opening_dbconfig'], $lang['step_4_ok']);
                        
		if(!@fwrite($fh, $db_config))
		{
                        
			step_4_error($lang['step_4_writing_dbconfig'], $lang['step_4_error_writing_db_config']);
			break;
                                                        
		}                        

		$output -> page_output .= $output -> install_step_action(true, $lang['step_4_writing_dbconfig'], $lang['step_4_ok']);
                                
		fclose($fh);
                        
	}
                
	// -------------------------------
	// Create databse tables
	// -------------------------------
	define("PREFIX", $install_values['sql_table_prefix']);
                
	if($install_values['sql_db_type'] == 'MySQL')
	{

		if(version_compare($db -> version, "4.1.3", ">="))
			$schema_extra_path = "MySQL4.1/";
		else
			$schema_extra_path = "";

	}
                
	//Get schema
	if(!@include ROOT."install/default_data/".$install_values['sql_db_type']."/".$schema_extra_path."db_schema.php")
	{

		step_4_error($lang['step_4_getting_db_schema'], $lang['step_4_error_getting_db_schema']);
		break;
                
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_4_getting_db_schema'], $lang['step_4_ok']);

	if(!defined("DUMMY_MODE"))
	{
                        
		// Go through them all
		foreach($sql_schema['table'] as $tbl_name => $val)
		{
                        
			if(!$db -> query($sql_schema['table'][$tbl_name]['drop']))
			{

				step_4_error($tbl_name, $lang['step_4_error_dropping_table']);
				break 2;                                        
                                        
			}        
                        
			if(!$db -> query($sql_schema['table'][$tbl_name]['create']))
			{

				step_4_error($tbl_name, $lang['step_4_error_creating_table'], true);
				break 2;                                        
                                        
			}        

			$output -> page_output .= $output -> install_step_action(true, $tbl_name, $lang['step_4_created_table_ok']);
                                
		}
                        
	}

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_4_finish_message'];
        
	break;


//***********************************************
// STEP 5
//***********************************************
case 5:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_5_message']."</p>";
	$output -> page_output .= "<hr />";
                
	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// -------------------------------
	// Insert DB entries
	// -------------------------------
	define("PREFIX", $db -> table_prefix);
                
	//Get stuff
	if(!@include ROOT."install/default_data/".$db -> db_type."/db_default_insert.php")
	{

		$output -> page_output .= $output -> install_step_action(false, $lang['step_5_getting_db_defaults'], $lang['step_5_fail']);
		$install_step_fail = true;
		break;
                
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_5_getting_db_defaults'], $lang['step_5_ok']);

	if(!defined("DUMMY_MODE"))
	{

		$current_table = "";
                        
		// Go through all the tables
		foreach($sql_schema['entries'] as $tbl_name => $entries_array)
		{
        
			if($current_table != "")
				$output -> page_output .= $output -> install_step_action(true, $tbl_name, $lang['step_5_entries_inserted']);
                                
			// Go through all the queries
			foreach($entries_array as $query)
			{
                                        
				if(!$db -> query($query))
				{
        
					$output -> page_output .= $output -> install_step_action(false, $tbl_name, $lang['step_5_error_inserting_entry']);
					$install_step_fail = true;
					break 3;                                        
                                                
				}        
                                        
			}
                                
			$current_table = $tbl_name;
                                
		}                

		if($current_table != "" && $current_table != $tbl_name)
			$output -> page_output .= $output -> install_step_action(true, $tbl_name, $lang['step_5_entries_inserted']);

	}

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_5_finish_message'];
                
	break;


//***********************************************
// STEP 6
//***********************************************
case 6:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_6_message']."</p>";
	$output -> page_output .= "<hr />";

	// ---------------
	// Want the form?
	// ---------------
	if(!isset($_POST['submit_config']))
	{
		$output -> page_output .= $output -> install_step_6_form();
		break;
	}

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// ---------------
	// Assume we have the form info, get the data or defaults =/
	// ---------------
	$guess_board_url = str_replace("install/index.php", "", $_SERVER['PHP_SELF']);
	$guess_board_url = _substr($guess_board_url, 0, _strlen($guess_board_url) -1);
	$guess_board_url = 'http://'.$_SERVER['SERVER_NAME'].$guess_board_url;

	$config_data['board_name']           = (!$_POST['board_name'])            ? $lang['step_6_form_default_board_name']        : $_POST['board_name'];
	$config_data['board_url']           = (!$_POST['board_url'])            ? $guess_board_url                                     : $_POST['board_url'];
	$config_data['mail_from_address'] = (!$_POST['mail_from_address']) ? $lang['step_6_form_default_mail_from_address'] : $_POST['mail_from_address'];
	$config_data['home_name']          = (!$_POST['home_name'])            ? $lang['step_6_form_default_home_name']             : $_POST['home_name'];
	$config_data['home_address']           = (!$_POST['home_address'])            ? $lang['step_6_form_default_home_address']             : $_POST['home_address'];

	// ---------------
	// Import the config XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-settings.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_6_loading_config_xml'], $lang['step_6_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_6_loading_config_xml'], $lang['step_6_ok']);
        
	require_once(ROOT."admin/common/funcs/config.funcs.php"); 
                
	if(!defined("DUMMY_MODE"))
	{

		$return = config_import_config_xml($xml, true);

		if(!$return)
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_6_inserting_config_data'], $lang['step_6_fail']);
			$install_step_fail = true;
			break;
		}

	}
                                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_6_inserting_config_data'], $lang['step_6_ok']);
                
	// ---------------
	// Saving our changes
	// ---------------
	if(!defined("DUMMY_MODE"))
	{

		foreach($config_data as $name => $val)
		{
        
			$new_data = array("value" => $val, "default" => $val);
        
			if(!$db -> basic_update("config", $new_data, "name = '".$name."'"))
			{        
				$output -> page_output .= $output -> install_step_action(false, $lang['step_6_saving_config_changes'], $lang['step_6_fail']);
				$install_step_fail = true;
				break 2;
			}
                                
		}

	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_6_saving_config_changes'], $lang['step_6_ok']);

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_6_finish_message'];

	break;                                


//***********************************************
// STEP 7
//***********************************************
case 7:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_7_message']."</p>";
	$output -> page_output .= "<hr />";

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// -------------------------------
	// check dir perms
	// -------------------------------
	if(!is_writable(ROOT."languages"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_7_checking_dir_perms'], $lang['step_7_writable_perms_fail']);
		$install_step_fail = true;
		break;
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_7_checking_dir_perms'], $lang['step_7_ok']);

	// ---------------
	// Import the languages XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-languages.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_7_loading_languages_xml'], $lang['step_7_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_7_loading_languages_xml'], $lang['step_7_ok']);
        
	require_once(ROOT."admin/common/funcs/languages.funcs.php"); 
                
	if(!defined("DUMMY_MODE"))
	{
                        
		if(!import_languages_xml($xml, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_7_inserting_languages_data'], $lang['step_7_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}        
                        
	$output -> page_output .= $output -> install_step_action(true, $lang['step_7_inserting_languages_data'], $lang['step_7_ok']);

	// ---------------
	// Default language
	// ---------------
	if(!defined("DUMMY_MODE"))
	{
                
		if(!$db -> basic_update("config", array('value' => '1'), "name = 'default_lang'"))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_7_saving_default_lang'], $lang['step_7_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_7_saving_default_lang'], $lang['step_7_ok']);

	// ---------------
	// Import the admin help
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-adminhelp.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_7_loading_adminhelp_xml'], $lang['step_7_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_7_loading_adminhelp_xml'], $lang['step_7_ok']);
        
	require_once(ROOT."admin/common/funcs/help.funcs.php"); 
                
	if(!defined("DUMMY_MODE"))
	{
                        
		if(!import_help_xml($xml, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_7_inserting_adminhelp_data'], $lang['step_7_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}        
                        
	$output -> page_output .= $output -> install_step_action(true, $lang['step_7_inserting_adminhelp_data'], $lang['step_7_ok']);

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_7_finish_message'];
                
	break;
                

//***********************************************
// STEP 8
//***********************************************
case 8:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_8_message']."</p>";
	$output -> page_output .= "<hr />";

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// -------------------------------
	// check dir perms
	// -------------------------------
	if(!is_writable(ROOT."templates"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_8_checking_dir_perms'], $lang['step_8_writable_perms_fail']);
		$install_step_fail = true;
		break;
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_8_checking_dir_perms'], $lang['step_8_ok']);

	// ---------------
	// Import the templates XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-templates.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_8_loading_templates_xml'], $lang['step_8_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_8_loading_templates_xml'], $lang['step_8_ok']);
        
	require_once(ROOT."admin/common/funcs/templates.funcs.php"); 
                
	if(!defined("DUMMY_MODE"))
	{

		if(!import_templates_xml($xml, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_8_inserting_templates_data'], $lang['step_8_fail']);
			$install_step_fail = true;
			break;
		}

	}
                                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_8_inserting_templates_data'], $lang['step_8_ok']);

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_8_finish_message'];
 
	break;

//***********************************************
// STEP 9
//***********************************************
case 9:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_9_message']."</p>";
	$output -> page_output .= "<hr />";

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// ---------------
	// Import the themes XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-themes.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_9_loading_themes_xml'], $lang['step_9_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_9_loading_themes_xml'], $lang['step_9_ok']);
        
	require_once(ROOT."admin/common/funcs/themes.funcs.php"); 

	if(!defined("DUMMY_MODE"))
	{
                        
		if(!import_themes_xml($xml, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_9_inserting_themes_data'], $lang['step_9_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}        
                        
	$output -> page_output .= $output -> install_step_action(true, $lang['step_9_inserting_themes_data'], $lang['step_9_ok']);

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_9_finish_message'];

	break;                


//***********************************************
// STEP 10
//***********************************************
case 10:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_10_message']."</p>";
	$output -> page_output .= "<hr />";

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// -------------------------------
	// check dir perms
	// -------------------------------
	if(!is_writable(ROOT."upload/avatar"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_10_checking_avatar_perms'], $lang['step_10_writable_perms_fail']);
		$install_step_fail = true;
		break;
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_checking_avatar_perms'], $lang['step_10_ok']);
                
                
	if(!is_writable(ROOT."upload/emoticon"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_10_checking_emoticons_perms'], $lang['step_10_writable_perms_fail']);
		$install_step_fail = true;
		break;
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_checking_emoticons_perms'], $lang['step_10_ok']);
                
                
	if(!is_writable(ROOT."upload/post_icon"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_10_checking_post_icon_perms'], $lang['step_10_writable_perms_fail']);
		$install_step_fail = true;
		break;
	}
                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_checking_post_icon_perms'], $lang['step_10_ok']);


	require_once(ROOT."admin/common/funcs/smallimages.funcs.php"); 


	// ---------------
	// Import the avatars XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-avatars.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_10_loading_avatars_xml'], $lang['step_10_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_loading_avatars_xml'], $lang['step_10_ok']);

	if(!defined("DUMMY_MODE"))
	{
                
		if(!import_images_xml($xml, "avatars", "upload/avatar/", true, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_10_inserting_avatars_data'], $lang['step_10_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}        
                        
	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_inserting_avatars_data'], $lang['step_10_ok']);


	// ---------------
	// Import the emoticons XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-emoticons.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_10_loading_emoticons_xml'], $lang['step_10_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_loading_emoticons_xml'], $lang['step_10_ok']);

	if(!defined("DUMMY_MODE"))
	{
                
		if(!import_images_xml($xml, "emoticons", "upload/emoticon/", true, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_10_inserting_emoticons_data'], $lang['step_10_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}
                                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_inserting_emoticons_data'], $lang['step_10_ok']);


	// ---------------
	// Import the post icons XML
	// ---------------
	if(!$xml = @file_get_contents(ROOT."install/default_data/fsboard-post-icons.xml"))
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_10_loading_post_icons_xml'], $lang['step_10_fail']);
		$install_step_fail = true;
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_loading_post_icons_xml'], $lang['step_10_ok']);

	if(!defined("DUMMY_MODE"))
	{
                
		if(!import_images_xml($xml, "post_icons", "upload/post_icon/", true, true))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_10_inserting_post_icons_data'], $lang['step_10_fail']);
			$install_step_fail = true;
			break;
		}

	}
                                
	$output -> page_output .= $output -> install_step_action(true, $lang['step_10_inserting_post_icons_data'], $lang['step_10_ok']);

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_10_finish_message'];

	break;
                

//***********************************************
// STEP 11
//***********************************************
case 11:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_11_message']."</p>";
	$output -> page_output .= "<hr />";

	// ---------------
	// Want the form?
	// ---------------
	if(!isset($_POST['submit_admin']))
	{

		$output -> page_output .= $output -> install_step_11_form(
			array(
				"username"	=> "",
				"password"	=> "",
				"password2"	=> "",
				"email"		=> ""
				)
			);
                        
		break;

	}

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// -------------------------------
	// Check account info
	// -------------------------------
	$account_info = array(
		"username"         => $_POST['admin_username'],
		"password"         => $_POST['admin_password'],
		"password2"         => $_POST['admin_password2'],
		"email"         => $_POST['admin_email']
		);
        
	array_map('trim', $account_info);
                
	// username
	if(_strlen($account_info['username']) < 2 || _strlen($account_info['username']) > 25)                
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_11_verify_account_info'], $lang['step_11_fail_username_invalid']);
		$output -> page_output .= $output -> install_step_11_form($account_info);
		break;
	}                                

	// Check for reserved characters in username
	$invalid_chars = array("'", "\"", "<!--", "\\");
	foreach($invalid_chars as $char)
	{
                
		if(strstr($account_info['username'], $char))
		{        
			$output -> page_output .= $output -> install_step_action(false, $lang['step_11_verify_account_info'], $lang['step_11_fail_username_invalid']);
			$output -> page_output .= $output -> install_step_11_form($account_info);
			break 2;
		}                                
                
	}
                                
	// password
	if(_strlen($account_info['password']) < 4 || _strlen($account_info['password']) > 14)                
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_11_verify_account_info'], $lang['step_11_fail_password_invalid']);
		$output -> page_output .= $output -> install_step_11_form($account_info);
		break;
	}                                
                                
	// password watch
	if($account_info['password'] != $account_info['password2'])                
	{        
		$output -> page_output .= $output -> install_step_action(false, $lang['step_11_verify_account_info'], $lang['step_11_fail_password_match']);
		$output -> page_output .= $output -> install_step_11_form($account_info);
		break;
	}                                

	// e-mail
	$account_info['email'] = str_replace(" ", "", $account_info['email']);
	$account_info['email'] = preg_replace("#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $account_info['email']);

	if(!preg_match("/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $account_info['email']))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_11_verify_account_info'], $lang['step_11_fail_email_invalid']);
		$output -> page_output .= $output -> install_step_11_form($account_info);
		break;
	}

	$output -> page_output .= $output -> install_step_action(true, $lang['step_11_verify_account_info'], $lang['step_11_ok']);


	// -------------------------------
	// Insert the account
	// -------------------------------
	$insert_stuff = array(
		"username"        => $account_info['username'],
		"user_group"        => '1',
		"password"        => md5($account_info['password']),
		"email"                => $account_info['email'],
		"registered"        => TIME
		);

	if(!defined("DUMMY_MODE"))
	{
                        
		if(!$db -> basic_insert("users", $insert_stuff))
		{
			$output -> page_output .= $output -> install_step_action(false, $lang['step_11_insert_account'], $lang['step_11_fail']);
			$install_step_fail = true;
			break;
		}

		if(!$db -> basic_insert("users_admin_settings", array('user_id' => $db -> insert_id())))
		{
			$output -> page_output .= $output -> install_step_action(false, $lang['step_11_insert_account'], $lang['step_11_fail']);
			$install_step_fail = true;
			break;
		}
                        
	}
                        
	$output -> page_output .= $output -> install_step_action(true, $lang['step_11_insert_account'], $lang['step_11_ok']);

	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_11_finish_message'];
                                
	break;


//***********************************************
// STEP 12
//***********************************************
case 12:

	// Opening message
	$output -> page_output .= "<p>".$lang['step_12_message']."</p>";
	$output -> page_output .= "<hr />";

	// -------------------------------
	// Connect to DB first
	// -------------------------------
	if(!connect_to_database($db))
		break;

	// ------------------------------------
	// Task next runtime
	// ------------------------------------
	$db -> basic_select("tasks", "next_runtime", "enabled='1'", "next_runtime", "", "asc");
	$next_run = $db -> result();
                
	// Check we have one
	if(!$next_run)
		$save_runtime = "-1";
	else
		$save_runtime = $next_run;

	if(!defined("DUMMY_MODE"))
	{
                        
		// Save it
		if(!$db -> basic_update("config", array('value' => $save_runtime), "name='next_task_runtime'"))
		{
			$output -> page_output .= $output -> install_step_action(false, $lang['step_12_save_task_runtime'], $lang['step_12_fail']);
			$install_step_fail = true;
			break;
		}
                
	}                

	$output -> page_output .= $output -> install_step_action(true, $lang['step_12_save_task_runtime'], $lang['step_12_ok']);

	// ------------------------------------
	// Cache build
	// ------------------------------------          
	if(!is_writable(ROOT."cache/"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_12_build_cache'], $lang['step_12_fail_writable']);
		$install_step_fail = true;
		break;
	}
                
	require ROOT.'common/class/cache.class.php';
	$cache = new cache;
                      
	if(!$cache -> update_cache("ALL"))
	{
		$output -> page_output .= $output -> install_step_action(false, $lang['step_12_build_cache'], $lang['step_12_fail']);
		$install_step_fail = true;
		break;
	}
            
	$output -> page_output .= $output -> install_step_action(true, $lang['step_12_build_cache'], $lang['step_12_ok']);

	// ------------------------------------
	// Lock board
	// ------------------------------------
	if(!$fh = @fopen(ROOT."install/install.no", "w"))
		$output -> page_output .= $output -> install_step_action(false, $lang['step_12_lock_board'], $lang['step_12_fail']);
                
	if(!@fwrite($fh, "Installation was locked on ".date("l dS \of F Y h:i:s A")))
		$output -> page_output .= $output -> install_step_action(false, $lang['step_12_lock_board'], $lang['step_12_fail']);
                
	@fclose($fh);

	@chmod(ROOT."install/install.no", 0777);
				
	$output -> page_output .= $output -> install_step_action(true, $lang['step_12_lock_board'], $lang['step_12_ok']);

	// ------------------------------------
	// Final message
	// ------------------------------------
	$output -> page_output .= "<hr />";
	$output -> page_output .= $lang['step_12_finish_message'];
                
	$output -> completed_install = true;
                                                
}


//***********************************************
// Chuck it out
//***********************************************
echo $output -> install_wrapper(); 

?>
