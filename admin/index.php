<?php
/*
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2007 Fiona Burrows (fiona@fsboard.net)

SBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License.
See gpl.txt for a full copy of this license.
--------------------------------------------------------------------------
*/

/**
 * Admin index
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


//***********************************************
// Initial tasks
//***********************************************

// Denife FSBOARD this means only index.php can do 
// stuff, which removes some potential security issues.
define('FSBOARD', 1);

// Define script root 
define( 'ROOT', "../" );

// Make sure the script knows we're in Admin territory
define('ADMIN', 1);

// Start off the script
require ROOT.'common/init.php';


//***********************************************
// Get the admin templates
//***********************************************
require ROOT."admin/common/templates/admin.tpl.php";
$template_admin = new template_admin;


//***********************************************
// Get the global language stuff
//***********************************************
load_language_group("admin_global");


//***********************************************
// Are they allowed in here?
//***********************************************
if(!$user -> perms['perm_admin_area'])
{
	$output -> set_error_message($lang['permission_denied']);
	$output -> build_and_output();
	die();
}


//***********************************************
// Get the required page from the URL
//***********************************************
// TODO: Remove old 'm' page getting
if(isset($_GET['m']))
	$page_val = $_GET['m'];
else
{
	$page_val = (isset($_GET['q'])) ? $_GET['q'] : "index/";
	unset($_GET['q']);	
}


//***********************************************
// Check if we have reauthenticated, otherwise show the login form 
//***********************************************
if(empty($_SESSION["fsboard_".$db -> table_prefix.'admin_area_session'])) 
{

	$extra_url = ($page_val) ? "&amp;go=".$page_val : "";
	
	$form = new form(array(
		"meta" => array(
			"name" => "admin_login",
			"title" => $lang['admin_login_title'],
			"description" => $lang['admin_login_msg'],
			"validation_func" => "form_admin_login_validate",
			"complete_func" => "form_admin_login_complete"	
		),
        
		"#username" => array(
			"type" => "text",
			"name" => $lang['admin_username'],
			"required" => True
		),
		"#password" => array(
			"type" => "password",
			"name" => $lang['admin_password'],
			"required" => True
		),
		"#submit" => array(
			"type" => "submit",
			"value" => $lang['admin_login']
		)        
	));	
	
	$output -> add($form -> render());
	$output -> build_and_output();
	die();
        
}


// ******************************
// List of pages of the admin area
// ******************************
$mode_file_list = array(

	// ----------------
	// Log out of admin
	// ----------------
	'logout' => array(
		"page" => "logout"
	),

	// ----------------
	// Index page - Main view, includes board info and admin notes
	// ----------------
	'index' => array(
		"page" => "main"
	),
	'index/(?<mode>check_friendly_urls)' => array(
		"page" => "main"
	),

	// ----------------
	// Global admin ajax requests
	// ----------------
	'ajax/(?<mode>admin_menu)' => array(
		"page" => "ajax"
	),

	// ----------------
	// Configuration
	// ----------------
	'config(/(?<mode>backup))?' => array(
		"page" => "config"
	),
	'config(/(?<mode>show_group)/(?<group_name>[a-zA-Z-_]+))?' => array(
		"page" => "config"
	),

	// ----------------
	// Users
	// ----------------
	'users(/(?<mode>search|add|ipsearch))?' => array(
		"page" => "users"
	),
	'users/(?<mode>edit|username|password|delete)/(?<user_id>[0-9]+)' => array(
		"page" => "users"
	),

	// ----------------
	// Custom profile fields
	// ----------------
	'profile_fields(/(?<mode>add))?' => array(
		"page" => "profile_fields"
	),
	'profile_fields/(?<mode>edit|delete)/(?<field_id>[0-9]+)' => array(
		"page" => "profile_fields"
	),

	// ----------------
	// User groups
	// ----------------
	'user_groups(/(?<mode>add))?' => array(
		"page" => "user_groups"
	),
	'user_groups/(?<mode>edit|delete)/(?<group_id>[0-9]+)' => array(
		"page" => "user_groups"
	),

	// ----------------
	// Promotions
	// ----------------
	'promotions(/(?<mode>add))?' => array(
		"page" => "promotions"
	),
	'promotions/(?<mode>edit|delete)/(?<promotion_id>[0-9]+)' => array(
		"page" => "promotions"
	),

	// ----------------
	// User titles
	// ----------------
	'titles(/(?<mode>add))?' => array(
		"page" => "titles"
	),
	'titles/(?<mode>edit|delete)/(?<title_id>[0-9]+)' => array(
		"page" => "titles"
	),

	// ----------------
	// Post insignia
	// ----------------
	'insignia(/(?<mode>add))?' => array(
		"page" => "insignia"
	),
	'insignia/(?<mode>edit|delete)/(?<insignia_id>[0-9]+)' => array(
		"page" => "insignia"
	),

	// ----------------
	// Reputations
	// ----------------
	'reputations(/(?<mode>add))?' => array(
		"page" => "reputations"
	),
	'reputations/(?<mode>edit|delete)/(?<reputation_id>[0-9]+)' => array(
		"page" => "reputations"
	),

	// ----------------
	// Mass mailer
	// ----------------
	'mailer(/(?<mode>send))?' => array("page" => "mailer"),

	// ----------------
	// Attachments
	// ----------------
	'attachments/(?<page>filetypes)(/(?<mode>add))?' => array(
		"page" => "attachments"
	),
	'attachments/(?<page>filetypes)/(?<mode>edit|delete)/(?<filetype_id>[0-9]+)' => array(
		"page" => "attachments"
	),

	// ----------------
	// Small images
	// ----------------
	'(?<page>avatars|emoticons|post_icons)(/(?<mode>add|backup))?' => array(
		"page" => "small_images"
	),
	'(?<page>avatars|emoticons|post_icons)/(?<mode>edit|delete|view|move_multiple|permissions)/(?<category_id>[0-9]+)' => array(
		"page" => "small_images"
	),
	'(?<page>avatars|emoticons|post_icons)/(?<category_id>[0-9]+)/(?<mode>add|add_multiple)' => array(
		"page" => "small_images"
	),
	'(?<page>avatars|emoticons|post_icons)/(?<category_id>[0-9]+)/(?<image_id>[0-9]+)/(?<mode>edit|delete)' => array(
		"page" => "small_images"
	),

	// ----------------
	// Custom BBCode
	// ----------------
	'bbcode(/(?<mode>add))?' => array(
		"page" => "bbcode"
	),
	'bbcode/(?<mode>edit|delete)/(?<bbcode_id>[0-9]+)' => array(
		"page" => "bbcode"
	),

	// --------------------
	// Waiting to be refactored pages
	// --------------------
	'help'			=> 'help.php',
	'phpinfo'       => 'main.php',
	'templates'     => 'templates.php',
	'themes'        => 'themes.php',
	'cache'         => 'cache.php',
	'emaillogs'     => 'email_logs.php',
	'adminlogs'     => 'admin_logs.php',
	'sqltools'      => 'sqltools.php',
	'langs'         => 'languages.php',
	'forums'        => 'forums.php',
	'moderators'    => 'moderators.php',
	'tasks'         => 'tasks.php',
	'tasklogs'      => 'tasks_logs.php',
	'wordfilter'    => 'wordfilter.php',
	'plugins'		=> 'plugins.php',
	'undelete'		=> 'undelete.php'

);


$match = NULL;
$page_matches = array();


// ******************************
// Iterate through the different page types and get our current page
// ******************************
foreach($mode_file_list as $regex => $page_to)
{
	
	$regex = str_replace("/", "\/", $regex);
	
	if(preg_match("/^".$regex."\/?$/i", $page_val, $page_matches))
	{
		if(is_array($page_to))
			$match = $page_to['page'];
		else
			$match = substr($page_to, 0, -4);

		break;
	}
	
}

define("CURRENT_MODE", $match);


//***********************************************
// Quick check if we've logging out
//***********************************************
if(CURRENT_MODE == "logout")
{
	unset($_SESSION["fsboard_".$db -> table_prefix.'admin_area_session']);
	$output -> redirect(l("/"), $lang['admin_logout_done']);
}


//***********************************************
// If we're here we clearly have access so desplay the header,
// the page contents (or error if appropriate), set title and show footer
//***********************************************
$output -> add_breadcrumb($lang['breadcrumb_admin_area'], l("admin/"));

if(CURRENT_MODE == NULL)
	$output -> set_error_message($lang['error_page_no_exist']);
else
	require ROOT."admin/pages/".CURRENT_MODE.".php";


if($output -> page_title == "")
	$output -> page_title = $cache -> cache['config']['board_name']." ".$lang['admin_area_title'];
else
	$output -> page_title .= " - ".$cache -> cache['config']['board_name']." ".$lang['admin_area_title'];

$crumbin = $template_admin -> admin_breadcrumb();

$output -> page_blocks['header'] = $template_admin -> admin_header(
	$template_admin -> admin_menu(),
	$crumbin
);
$output -> page_blocks['footer'] = $template_admin -> admin_footer($crumbin);


//***********************************************
// Show up the final page
//***********************************************
$output -> build_and_output();



/**
 * form_admin_login_validate()
 * Does quick validation of user information sent from login form
 *
 * @param object $form    
 */
function form_admin_login_validate($form)
{

	global $db, $lang, $user, $output;
	
	if(!$form -> form_state['#username']['value'] || !$form -> form_state['#password']['value'])
		return;

	// grab user from DB
	$db -> basic_select(array(
		"table" => "users",
		"what" => "password, id, need_validate",
		"where" => "LOWER(username) = '".$db -> escape_string(_strtolower($form -> form_state['#username']['value']))."'",
		"limit" => 1
	));

	if(!$db -> num_rows())
	{
		$form -> set_error("username", $lang['error_no_user']);
		return;
	}

	$user_array = $db -> fetch_array();
        
	// Check password
	if($user_array['password'] != md5($form -> form_state['#password']['value']))
	{
		$lang['error_wrong_password'] = $output -> replace_number_tags($lang['error_wrong_password'], l("login/lost_password/"));
		$form -> set_error("password", $lang['error_wrong_password']);
		return;
	}


	// Same user as logged in as?
	if($user -> info['id'] != $user_array['id'])
	{
		$form -> set_error("password", $lang['login_error_user_not_same']);
		return;
	}
        
	// Check validation
	if($user_array['need_validate'])
	{
		$form -> set_error("password", $lang['error_need_validation']);
		return;
	}
        
}



/**
 * form_admin_login_complete()
 * After form goes through validation this sets the user as being allowed to access admin
 *
 * @param object $form    
 */
function form_admin_login_complete($form)
{

	global $db, $lang, $user;

	// magic cookie
	$_SESSION["fsboard_".$db -> table_prefix.'admin_area_session'] = true;

	// Redirect the user
	$form -> form_state['meta']['redirect'] = array(
		"url" => l("admin/"),
		"message" => $lang['logged_in']
	);


}




/**
 * log_admin_action()
 * Quickly throws an admin action into the database log
 *
 * @param string $page_mode
 * @param string $secondary_mode
 * @param string $note
 * @return True on success
 */
function log_admin_action($page_mode, $secondary_mode = "", $note = "Admin action.")
{

	global $db, $user;
                
	if($db -> basic_insert("admin_logs",
       	array(
			"date" => TIME,
			"page_name" => $page_mode,
			"mode" => $secondary_mode,
			"member" => $user -> user_id,
			"ip" => user_ip(),
			"note" => $note
		)
	))
		return true;
	else
		return false;
 
}

?>
