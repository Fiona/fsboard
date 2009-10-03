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
 * Inital startup actions
 * 
 * This file will run at the start of every FSBoard page.
 * It connects to the database, loads the configuration, 
 * loads the language file and loads the current user info. 
 * Among other things....
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


/**
 * If defined, this will display debug messages in the admin and moderator areas
 */
define("ADMINDEBUG", true);

/**
 * If defined, you will have access to the developer areas in the admin area
 */
define("DEVELOPER", true);

/**
 * If you are having troubles with templates, uncomment this line to load
 * templates from the database, as opposed to including from files.
 * 
 * Warning: This is slow and should only be used for debug or troubleshooting.
 * -- Do not use in a working environment. --
 */ 
//define("DATABASETEMPLATES", true);


/**
 * If you are having troubles with languages, uncomment this line to load
 * languags phrases from the database, as opposed to including from files.
 * 
 * Warning: This is slow and should only be used for debug or troubleshooting.
 * -- Do not use in a working environment. --
 */ 
//define("DATABASELANGUAGES", true);


/**
 * If you are having troubles with the filesystem saved cache, uncomment 
 * this line to load cache from the database, as opposed to including from files.
 * 
 * Warning: This is slow and should only be used for debug or troubleshooting.
 * -- Do not use in a working environment. --
 */ 
//define("DATABASECACHE", true);


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Bunch of important definitions
//***********************************************
define("USERGROUP_ADMIN",	 	1);
define("USERGROUP_GLOBALMOD",	2);
define("USERGROUP_MEMBERS", 	3);
define("USERGROUP_GUEST", 		4);
define("USERGROUP_VALIDATING", 	5);
define("USERGROUP_BANNED", 		6);

define("PROMOTION_TYPE_PRI", 0);
define("PROMOTION_TYPE_SEC", 1);
define("PROMOTION_REPUTATION_GT", 0);
define("PROMOTION_REPUTATION_LT", 1);


//***********************************************
// Include the database configuration file
//***********************************************

// Initialise the array
$DB_CONFIG = array();

// Check for the config file, if there's nothing tell the user to install first.
if(!file_exists(ROOT."db_config.php"))
{
	header("location: ".ROOT."install/");
	die(
		"<p>Cannot find <b>db_config.php</b>! Please install your message board:".
		"<a href=\"".ROOT."install/\">Click here.</a></p>"
		);
}

// The file exists, now include it        
require ROOT.'db_config.php';

// Check it's alright
if(!defined('DBCONFIG_PRESENT'))
{
	header("location: ".ROOT."install/");
	die(
		"<p>Cannot find relevant info in <b>db_config.php</b>!".
		"Please install your message board:".
		"<a href=\"".ROOT."install/\">Click here.</a></p>"
		);
}

// With info from the config, include the DB class wanted
$db_file = ROOT . "db/" . $DB_CONFIG['db type'] . "/database.class.php";
require($db_file);


/**
 * Global instance of the database class
 * @global class $db 
 * @name $db
 */
$db = new database;

// Get a connection
$db -> connect(
	$DB_CONFIG['host'],
	$DB_CONFIG['database'],
	$DB_CONFIG['username'],
	$DB_CONFIG['password'],
	$DB_CONFIG['table prefix'],
	$DB_CONFIG['port']
	);


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
if(version_compare(PHP_VERSION, '5.3', '<'))
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
// Load up inportant stuff
//***********************************************

// Load the global functions file
require ROOT."common/funcs/global.funcs.php";

// Cache class
require ROOT."common/class/cache.class.php";

/**
 * Global instance of the cache class
 * @global class $cache 
 * @name $cache
 */
$cache = new cache;
$cache -> load_cache(); 

// Emailer class
require ROOT."common/class/email.class.php";
// Table/Form generation classes
require ROOT."common/class/form_table.class.php";
// XML generate/parser class
require ROOT."common/class/xml.class.php";
// BBCode parser class
require ROOT."common/class/parser.class.php";
// Hook list for plugins
require ROOT."common/plugin_hooks.php";
// Form system
require ROOT."common/class/form.class.php";
// Generic result tables
require ROOT."common/class/results_table.class.php";



// Stop index.php?GLOBALS[xxx] sillyness
if(isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
	exit("i don't think so~");

// Custom error output
set_error_handler('error_handler');

// Start the debug timer
start_debug_timer();


/**
 * Global instance of the parser
 * @global class $parser
 * @name $parser
 */
$parser = new parser; 

// Setup all the time consts
set_time_variables();


//***********************************************
// Shut down function register...
//***********************************************
register_shutdown_function("shutdown_tasks");


//***********************************************
// Get the current information for the user
//***********************************************
// Gotta be done...
session_start();

// Include class
require ROOT."common/class/user.class.php";

/**
 * Global instance of the user handing class
 * @global class $user 
 * @name $user
 */
$user = new user;

// Tell the forum we are in admin if we are
if(defined('ADMIN'))
        $user -> in_admin_area = true;

// Authorise and grab user info
$user -> get_user_info();


//***********************************************
// Load Language
//***********************************************
if(!isset($user -> info['language']) || $user -> info['language'] == "")
        $id = $cache -> cache['config']['default_lang'];
elseif(!file_exists(ROOT."languages/lang_id".$user -> info['language']))
        $id = $cache -> cache['config']['default_lang'];
else
        $id = $user -> info['language'];

/**
 * Definition of the current language directory
 */
define("LANG", "lang_id".$id);

/**
 * ID number of the language
 */
define("LANG_ID", $id);

/**
 * Charset, used in templates
 */
define("CHARSET", $cache -> cache['languages'][LANG_ID]['charset']);

// Global languages file
load_language_group("global");


//***********************************************
// Load global output class and theme specific stuff
//***********************************************
require ROOT."common/class/output.class.php";

if(defined("ADMIN"))
{
	require ROOT."admin/common/class/admin_output.class.php";
	$output = new admin_output;
}
else
	$output = new output;
        
// Load the set in
$output -> template_set($cache -> cache['config']['default_template_set']);
        

//***********************************************
// Load the global template - It is important that this
// is done after the output class is created as it needs
// to get the current template set ID from the  output.
//***********************************************
$template_global = load_template_class("template_global");


//***********************************************
// Load Stylesheet, one wonders why she's not doing tihs in the output class yet
// TODO ******* REWRITE THIS BIT FIONA ********
//***********************************************
// TEMPORARY
$grab_theme_row = $db -> query("SELECT te.default_theme, th.css, th.image_dir FROM ".$db -> table_prefix."template_sets te, ".$db -> table_prefix."themes th WHERE te.id = '".$output -> template_set_id."' AND th.id = te.default_theme LIMIT 1");
$theme_array = $db -> fetch_array($grab_theme_row);
        
/**
 * Definition of the current theme image directory (main)
 */
if(defined("ADMIN"))
	define(
		'IMGDIR',
		$cache -> cache['config']['board_url']."/admin/themes/FSBoard_Green/"
		);
else
	define(
		'IMGDIR',
		$cache -> cache['config']['board_url']."/".$theme_array['image_dir']
		);
    
// This string is put into the outputted file
$stylesheet = $theme_array['css'];
        
// Replace $imgdir with the right stuff in the stylesheet
$output -> stylesheet = str_replace(
	'$imgdir',
	$cache -> cache['config']['board_url']."/".$theme_array['image_dir'],
	$stylesheet
	);


// This array is used in some templates
$GLOBAL_OTHER = array();
$GLOBAL_OTHER['imgdir'] = IMGDIR;
$GLOBAL_OTHER['charset'] = CHARSET;


?>