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




// ----------------------------------------------------------------------------------------------------------------------



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
	// Index page - usually forum view
	// ----------------
	'index' => array(
		"page" => "main"
	),
	
/*	
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
*/


                                'help'			=> 'help.php',
                                'phpinfo'       => 'main.php',
                                'config'        => 'config.php',
                                'ieconfig'      => 'ieconfig.php',
                                'templates'     => 'templates.php',
                                'ietemplates'   => 'ietemplates.php',
                                'themes'        => 'themes.php',
                                'iethemes'      => 'iethemes.php',
                                'cache'         => 'cache.php',
                                'emaillogs'     => 'email_logs.php',
                                'adminlogs'     => 'admin_logs.php',
                                'sqltools'      => 'sqltools.php',
                                'langs'         => 'languages.php',
                                'ielangs'       => 'ielanguages.php',
                                'forums'        => 'forums.php',
                                'usergroups'    => 'usergroups.php',
                                'moderators'    => 'moderators.php',
                                'users'         => 'users.php',
                                'profilefields' => 'profilefields.php',
                                'tasks'         => 'tasks.php',
                                'tasklogs'      => 'tasks_logs.php',
                                'bbcode'        => 'bbcode.php',
                                'attachments'   => 'attachments.php',
                                'emoticons'     => 'smallimages.php',
                                'avatars'       => 'smallimages.php',
                                'posticons'     => 'smallimages.php',
                                'titles'		=> 'titles.php',
                                'insignia'		=> 'insignia.php',
                                'reputations'   => 'reputations.php',
                                'wordfilter'    => 'wordfilter.php',
                                'promotions'	=> 'promotions.php',
                                'mailer'		=> 'mailer.php',
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
	}
	
}

define("CURRENT_MODE", $match);


//***********************************************
// If we're here we clearly have access so desplay the header,
// the page contents (or error if appropriate), set title and show footer
//***********************************************
if(CURRENT_MODE == NULL)
	$output -> set_error_message($lang['error_page_no_exist']);
else
	include ROOT."admin/pages/".CURRENT_MODE.".php";


if($output -> page_title == "")
	$output -> page_title = $cache -> cache['config']['board_name']." ".$lang['admin_area_title'];
else
	$output -> page_title .= " - ".$cache -> cache['config']['board_name']." ".$lang['admin_area_title'];

$output -> add_breadcrumb($lang['breadcrumb_admin_area'], l("admin/"));

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


/*

//***********************************************
// What part of the admin index do we want?
//***********************************************
switch(CURRENT_MODE)
{

        //***********************************************
        // Trying to login...
        //***********************************************
        case "login":

                if(process_admin_login())
                {
        
                        // Write cookie
                        $_SESSION["fsboard_".$db -> table_prefix.'admin_area_session'] = true;

                        // Sort out redirect
                        $extra_url = (isset($_GET['go_m'])) ? "?go_m=".$_GET['go_m'] : "";
                        
                        // Redirect user                                        
                        $output -> redirect(ROOT."admin/index.php".$extra_url, $lang['logged_in']);
        
                }
                
                break;

        //***********************************************
        // Trying to logout...
        //***********************************************
        case "logout":

                // Invalidate Cookie
                unset($_SESSION["fsboard_".$db -> table_prefix.'admin_area_session']);

                // Redirect the user
                $output -> redirect(ROOT."index.php", $lang['admin_logout_done']);
        
                break;
        
        //***********************************************
        // Header
        //***********************************************
        case "head":

                die("
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
    <title>FSBoard Admin Header</title>
    <meta HTTP-EQUIV=\"content-type\" CONTENT=\"text/html; charset=".CHARSET."\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"".ROOT."admin/themes/".$output -> theme_folder."/style.css\" />
</head>

<body>    
        <table width=100% style=\"border-collapse : collapse; margin : 0px; height:100%;\">
        <tr>
                <td width=50% style=\"border : 0px;\" class=\"normalcell\">
                        <p><b>FSBoard ".$cache -> cache['config']['current_version']." ".$lang['admin_area']."</b> (".$cache -> cache['config']['board_name'].")</p>
                </td>
                <td width=50% align=\"right\" style=\"border : 0px;\" class=\"normalcell\">
                        <p><b><a href=\"".ROOT."index.php\" target=\"_top\">Back to forum</a> -
                        <a href=\"".ROOT."admin/index.php?m=logout\"  onclick=\"return confirm('".$lang['admin_logout_confirm']."');\" target=\"_top\">".$lang['admin_logout']."</a></b></p>
                </td>

        </tr>
        </table>
</body>

</html>");
                
                break;

        //***********************************************
        // Admin Menu
        //***********************************************
        case "menu":
                
                die("
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">
<html>
<head>
    <title>FSBoard Menu</title>
    <meta HTTP-EQUIV=\"content-type\" CONTENT=\"text/html; charset=".CHARSET."\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"".ROOT."admin/themes/".$output -> theme_folder."/style.css\" />
</head>

<body>     
".$template_admin -> admin_menu()."
</body>

</html>");
                
                break;               
        //***********************************************
        // Frameset
        //***********************************************
        case "":

                // Sort out redirect
                $extra_url = (isset($_GET['go_m'])) ? "?m=".$_GET['go_m'] : "?m=index";

                // Do it
                die("
<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Frameset//EN\" \"http://www.w3.org/TR/REC-html40/frameset.dtd\">
<html>
	 <head><title>".$cache -> cache['config']['board_name']." ".$lang['admin_area_title']."</title></head>

        <frameset cols=\"240,*\" framespacing=\"0\" border=\"0\" frameborder=\"0\">
                <frame name=\"menu\" noresize scrolling=\"auto\" src=\"".ROOT."admin/index.php?m=menu\">
        	<frameset rows=\"18,*\"  framespacing=\"0\" border=\"0\" frameborder=\"0\">
                        <frame name='head' noresize scrolling='no' marginwidth='10' marginheight='0' border='no' src='".ROOT."admin/index.php?m=head'>
                        <frame name='page' noresize scrolling='auto' src='".ROOT."admin/index.php".$extra_url."'>
        	</frameset>
        </frameset>
</html>");
       
                break;        

        //***********************************************
        // Normal page
        //***********************************************
        default:

                // Array of files we're allowed to use        
                $mode_file_list =
                        array(
                                'index'         => 'main.php',
                                'help'			=> 'help.php',
                                'phpinfo'       => 'main.php',
                                'config'        => 'config.php',
                                'ieconfig'      => 'ieconfig.php',
                                'templates'     => 'templates.php',
                                'ietemplates'   => 'ietemplates.php',
                                'themes'        => 'themes.php',
                                'iethemes'      => 'iethemes.php',
                                'cache'         => 'cache.php',
                                'emaillogs'     => 'email_logs.php',
                                'adminlogs'     => 'admin_logs.php',
                                'sqltools'      => 'sqltools.php',
                                'langs'         => 'languages.php',
                                'ielangs'       => 'ielanguages.php',
                                'forums'        => 'forums.php',
                                'usergroups'    => 'usergroups.php',
                                'moderators'    => 'moderators.php',
                                'users'         => 'users.php',
                                'profilefields' => 'profilefields.php',
                                'tasks'         => 'tasks.php',
                                'tasklogs'      => 'tasks_logs.php',
                                'bbcode'        => 'bbcode.php',
                                'attachments'   => 'attachments.php',
                                'emoticons'     => 'smallimages.php',
                                'avatars'       => 'smallimages.php',
                                'posticons'     => 'smallimages.php',
                                'titles'		=> 'titles.php',
                                'insignia'		=> 'insignia.php',
                                'reputations'   => 'reputations.php',
                                'wordfilter'    => 'wordfilter.php',
                                'promotions'	=> 'promotions.php',
                                'mailer'		=> 'mailer.php',
                                'plugins'		=> 'plugins.php',
                                'undelete'		=> 'undelete.php'
                        );

                // If the page doesn't exist
                if ($mode_file_list[CURRENT_MODE] == '')
                {
                
                        $output -> page_output = $template_admin -> critical_error($lang['error_page_no_exist']);
                        $output -> finish();
                        die();
                
                }        

		// Root breadcrumb
		$output -> add_breadcrumb($lang['breadcrumb_admin_area'], "index.php?m=index");
                
                // It exists, so include the file
                include ROOT."admin/pages/".$mode_file_list[CURRENT_MODE];
                
                $output -> finish();
                die();

}
*/



/**
 * form_admin_login_validate()
 * Does quick validation of user information sent from login form
 *
 * @param object $form    
 */
function form_admin_login_validate($form)
{

	global $db, $lang, $user;
	
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

	global $mode_file_list, $db, $user;
      
	if($mode_file_list[$page_mode] == '')
		return false;
                
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
