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
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 * 
 * @started 01 Aug 2005
 * @edited 01 Jun 2007
 */




// ----------------------------------------------------------------------------------------------------------------------

error_reporting(E_ALL);

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
                'index' 	=> array("view_main.php", 	array()),
                'login' 	=> array("login.php", 		array()),
                'reg'   	=> array("register.php", 	array("profile_fields")),
                'profile' 	=> array("view_profile.php", 	array("profile_fields", "avatars", "small_image_cats_perms", "user_titles", "custom_bbcode", "emoticons")),
                'control' 	=> array("control_panel.php", 	array("profile_fields", "avatars", "small_image_cats", "small_image_cats_perms", "user_titles", "custom_bbcode", "emoticons"))
        );


// If we want the main page
$m_mode = isset($_GET['m']) ? $_GET['m'] : "";

if ($m_mode == '' || $mode_file_list[$m_mode][0] == '')
        $current_mode_definition = "index";
else 
        // Get the wanted mode from the url
        $current_mode_definition = $m_mode;

/**
 * Quick way to access the name of the current page.
 */        
define('CURRENT_MODE', $current_mode_definition);

//***********************************************
// Now we know the page, do the inital tasks
//***********************************************
require ROOT.'common/init.php';


//***********************************************
// Start the global template stuff
//***********************************************
$template_global = load_template_class("template_global");


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
// If the page doesn't exist
if(CURRENT_MODE != '' && $mode_file_list[CURRENT_MODE][0] == '')
        $output -> add($template_global -> critical_error($lang['error_page_no_exist']));
else
{

        //include the right file
        include ROOT."pages/".$mode_file_list[CURRENT_MODE][0];

}


//***********************************************
// Finish off the page with the footers
//***********************************************
if($output -> page_title == "")
        $output -> page_title = $cache -> cache['config']['board_name'];
else
        $output -> page_title .= " - ".$cache -> cache['config']['board_name'];

$output -> page_blocks['footer'] = $template_global -> main_page_footer();


//***********************************************
// Get all the debug stuff
//***********************************************
// Level 1 Debug = Query amount and Execution time
if ($cache -> cache['config']['debug'] >= "1")
        $debug_level_1 = $output -> return_debug_level(1);

// Level 2 Debug = Query printing
if ($cache -> cache['config']['debug'] >= "2")
        $debug_level_2 = $output -> return_debug_level(2);


//***********************************************
// If we're in maintenance mode, and an admin show up the little message thing
//***********************************************
if($user -> perms['perm_see_maintenance_mode'] && $cache -> cache['config']['maintenance'])
        $output -> page_blocks['header'] = "<p style=\"border-style : dashed; border-color: #FF4040; border-width: 1px;\"align=\"center\"><b>".$lang['admin_message_maintenance_mode']."</b></p>\n".$output -> page_blocks['header'];
        
        
//***********************************************
// Show up the final page
//***********************************************
$output -> page_blocks['content'] = $output -> page_output;
$output -> page_blocks['error_box'] = $output -> get_error_information();


$output -> finish(
        $template_global -> global_wrapper(
        	$output -> page_title,
        	$output -> stylesheet,
        	CHARSET, 
        	$output -> page_blocks,
        	$debug_level_1,
        	$debug_level_2
        )
);


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