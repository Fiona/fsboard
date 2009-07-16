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
 * Main index file
 * 
 * Everything runs through this index file, it does
 * lots of checking on the current selected mode and 
 * shows up the main template files. As well as including
 * {@link init.php}.
 * 
 * The admin and moderator areas have their own index.php.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


error_reporting(E_ALL|E_STRICT);

//***********************************************
// Initial tasks
//***********************************************

/**
 * Every source file checks for the definition FSBOARD
 * being set, this ensures that none of the source files
 * can be ran directly. 
 */
define('FSBOARD', 1);

/**
 *  Lots of stuff needs the path script root, 
 * this exists because the script root is different in the admin area..
 */ 
define( 'ROOT', "./" );

// List of pages and thier cache
$mode_file_list = array(

	// ----------------
	// Index page - usually forum view
	// ----------------
	'index' => array(
		"page" => "view_main",
		"cache" => array()
	),
	
	
	// ----------------
	// Login and lost password
	// ----------------
	'login(/(?<mode>logout|lost_password|lost_password_step_2))?' => array("page" => "login", "cache" => array()),
	'login/(?<mode>lost_password_step_2)/(?<user_id>[0-9]+)/(?<activate_code>[a-zA-Z0-9]+)' => array(
		"page" => "login",
		"cache" => array()
	),
	
	
	// ----------------
	// Registration and activation
	// ----------------
	'register(/(?<mode>activate))?' => array(
		"page" => "register",
		"cache" => array("profile_fields")
	),
	'register/(?<mode>activate)/(?<user_id>[0-9]+)/(?<activate_code>[a-zA-Z0-9]+)' => array(
		"page" => "register",
		"cache" => array("profile_fields")
	),

	
	// ----------------
	// Profile and control panel sections
	// ----------------
	'profile' => array(
		"page" => "view_profile",
		"cache" => array("profile_fields", "avatars", "small_image_cats_perms", "user_titles", "custom_bbcode", "emoticons")
	),
	'control' => array(
		"page" => "control_panel",
		"cache" => array("profile_fields", "avatars", "small_image_cats", "small_image_cats_perms", "user_titles", "custom_bbcode", "emoticons")
	)
);


        
// TODO: Remove old 'm' page getting
if(isset($_GET['m']))
	$page_val = $_GET['m'];
else
{
	$page_val = (isset($_GET['q'])) ? $_GET['q'] : "index/";
	unset($_GET['q']);	
}


// If we don't have a / on the end, we do need it
/*
if(substr($page_val, strlen($page_val) - 1, 1) != "/")
{
       $q = explode("?", $_SERVER['REQUEST_URI']);
       $q = $q[0]."/".(isset($q[1]) ?  "?".$q[1] : "");
       header("location:".$q);
       die();
}
*/


$match = NULL;
$extra_cache = array();
$page_matches = array();

// Iterate through the different page types and get ours
foreach($mode_file_list as $regex => $page_to)
{
	
	$regex = str_replace("/", "\/", $regex);
	
	if(preg_match("/^".$regex."\/?$/i", $page_val, $page_matches))
	{
		$match = $page_to['page'];
		$extra_cache = $page_to['cache'];
		break;
	}
	
}



/**
 * Quick way to access the name of the current page.
 */    
define("CURRENT_MODE", $match);


//***********************************************
// Now we know the page, do the inital tasks
//***********************************************
require ROOT.'common/init.php';


//***********************************************
// Check maintenance mode stuff
//***********************************************
if($cache -> cache['config']['maintenance'] && !$user -> perms['perm_see_maintenance_mode'])
{

        // Print maintenance page
        $output -> finish($template_global -> maintenance($output -> stylesheet));        
        
        // Kill script
        die();

}


//***********************************************
// PHP header include
//***********************************************
ob_start();

eval($template_global -> php_header_include());

$php_header = ob_get_contents();

ob_end_clean();

if($php_header)
        echo $php_header;


//***********************************************
// Show the header
//***********************************************
$quick_links = (isset($quick_links)) ? $quick_links : "";
$last_visit = (isset($last_visit)) ? $last_visit : "";

$output -> page_blocks['header'] = $template_global -> main_page_header(IMGDIR, $quick_links, $last_visit, $template_global -> quick_login());


//***********************************************
// Work out what page we're requesting and stuff
//***********************************************
// Error if the page doesn't exist or include it
if(CURRENT_MODE == NULL)
	$output -> set_error_message($lang['error_page_no_exist']);
else
	include ROOT."pages/".CURRENT_MODE.".php";



//***********************************************
// Finish off the page with the footers
//***********************************************
if($output -> page_title == "")
        $output -> page_title = $cache -> cache['config']['board_name'];
else
        $output -> page_title .= " - ".$cache -> cache['config']['board_name'];

$output -> page_blocks['footer'] = $template_global -> main_page_footer();



//***********************************************
// If we're in maintenance mode, and an admin show up the little message thing
//***********************************************
if($user -> perms['perm_see_maintenance_mode'] && $cache -> cache['config']['maintenance'])
        $output -> page_blocks['header'] = "<p style=\"border-style : dashed; border-color: #FF4040; border-width: 1px;\"align=\"center\"><b>".$lang['admin_message_maintenance_mode']."</b></p>\n".$output -> page_blocks['header'];
        
        
//***********************************************
// Show up the final page
//***********************************************
$output -> build_and_output();


//***********************************************
// PHP footer include
//***********************************************
// Start it.
ob_start();

// Grab it.
eval($template_global -> php_footer_include());
$php_footer = ob_get_contents();
ob_end_clean();

// Chuck it.
if($php_footer)
        echo $php_footer;

// From here on the shutdown_tasks function will run.
// Which does the common tasks and the shutdown queries.

?>
