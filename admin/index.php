<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2006 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

FSBoard is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
--------------------------------------------------------------------------

*********************************
*       FSBoard                 *
*       by Fiona 2006           *
*********************************
*       Admin Index             *
*       Started by Fiona        *
*       7th Aug 2005            *
*********************************
*       Last edit by Fiona      *
*       2rd Oct 2007            *
*********************************

Index for the admin area. Shows the menu, the header, the framset etc.
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
        $output -> page_output = $template_admin -> critical_error($lang['permission_denied']);
        $output -> finish();
        die();
}


//***********************************************
// Get the required page from the URL
//***********************************************
$m_mode = isset($_GET['m']) ? $_GET['m'] : "";
define('CURRENT_MODE', $m_mode);


//***********************************************
// Check if we're logged into the admin area or not
//***********************************************
if(empty($_SESSION["fsboard_".$db -> table_prefix.'admin_area_session']) && CURRENT_MODE != "login") 
{

        $extra_url = ($m_mode) ? "&amp;go_m=".$m_mode : "";

		$output -> add($template_admin -> login($extra_url));
        $output -> finish();
        die();
        
}


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




// ---------------------------------------------------
// process_admin_login()
// Checks login info inputted with a form and does... stuff.
//
// Params  - None
// Returns - true on success.
// ---------------------------------------------------
function process_admin_login()
{

        global $db, $output, $lang, $template_admin, $user;

        // grab user from DB
        $select_user = $db -> basic_select("users", "password, id, need_validate", "lower(username) = '".$db -> escape_string(_strtolower($_POST['username']))."'"); 

        // See if it exists
        if($db -> num_rows($select_user) < 1)
        {
                $output -> add($template_admin -> normal_error($lang['error_no_user']));
                $output -> add($template_admin -> login());
                $output -> finish();
                die();
        }

        // Grab the full info
        $user_array = $db -> fetch_array($select_user);
        
        // Check password
        if($user_array['password'] != md5($_POST['password']))
        {
                $output -> add($template_admin -> normal_error($output -> replace_number_tags($lang['error_wrong_password'], array(ROOT))));
                $output -> add($template_admin -> login());
                $output -> finish();
                die();
        }

        // Same user as logged in as?
        if($user -> info['id'] != $user_array['id'])
        {
                $output -> add($template_admin -> normal_error($lang['login_error_user_not_same']));
                $output -> add($template_admin -> login());
                $output -> finish();
                die();
        }
        
        // Check validation
        if($user_array['need_validate'] != "0")
        {
                $output -> add($template_admin -> normal_error($lang['error_need_validation']));
                $output -> add($template_admin -> login());
                $output -> finish();
                die();
        }

        // Worked - done.
        return true;
        
}



// ---------------------------------------------------
// log_admin_action()
// Logs an action done by an admin and saves it to the DB.
//
// Params  - None
// Returns - true on success.
// ---------------------------------------------------
function log_admin_action($page_mode, $secondary_mode = "", $note = "Admin action.")
{

        global $mode_file_list, $db, $user;
        
        if($mode_file_list[$page_mode] == '')
                return false;
                
        // Log it.
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
