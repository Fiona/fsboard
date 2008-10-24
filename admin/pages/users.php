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
*       Managing Users          *
*       Started by Fiona        *
*       15th Feb 2006           *
*********************************
*       Last edit by Fiona      *
*       02nd Sep 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Is this a dagger I see before me? NO!
//***********************************************
load_language_group("admin_users");


//***********************************************
// Functions please Jeeves..
//***********************************************
include ROOT."admin/common/funcs/users.funcs.php";



$_GET['m2'] = ($_GET['m2']) ? $_GET['m2'] : "search";
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        // -------------------
        // Adding
        // -------------------
        case "add":
                page_add_user();
                break;

        case "doadd":
                do_add_user();
                break;

        // -------------------
        // Search and edit
        // -------------------
        case "search":
                page_search_users();
                break;

        case "dosearch":
                do_search_users();
                break;

        case "edit":
                page_edit_user();
                break;

        case "doedit":
                do_edit_user();
                break;

        // -------------------
        // Change name
        // -------------------
        case "changename":
                page_change_name();
                break;

        case "dochangename":
                do_change_name();
                break;

        // -------------------
        // Change password
        // -------------------
        case "changepass":
                page_change_password();
                break;

        case "dochangepass":
                do_change_password();
                break;

        // -------------------
        // Delete user
        // -------------------
        case "delete":
                page_delete_user();
                break;

        case "dodelete":
                do_delete_user();
                break;

        // -------------------
        // Search by IP
        // -------------------
        case "ipsearch":
                page_search_ip();
                break;

        case "doipsearch":
                do_search_ip();
                break;
                
}




//***********************************************
// Add a user form
//***********************************************
function page_add_user($user_info = "")
{

        global $output, $lang, $db, $template_admin;

        // Keep the groups in arrays
        $db -> basic_select("user_groups", "id,name", "", "id", "", "asc");

        while($g_array = $db -> fetch_array())
        {
                $dropdown_values[] .= $g_array['id'];
                $dropdown_text[] .= $g_array['name'];
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['add_user_title'];

        $output -> add_breadcrumb($lang['breadcrumb_users_add'], "index.php?m=users&amp;m2=add");
        
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // Fooooorm
        $output -> add(
                $form -> start_form("adduser", ROOT."admin/index.php?m=users&amp;m2=doadd", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_top_table_header($lang['add_user_title'], 2, "users_and_groups").
                $table -> simple_input_row_text($form, $lang['add_user_form_username'], "username", $user_info['username'], "username").     
                $table -> simple_input_row_text($form, $lang['add_user_form_password'], "password", $user_info['password'], "password").     
                $table -> simple_input_row_text($form, $lang['add_user_form_email'], "email", $user_info['email'], "email").     
                $table -> simple_input_row_dropdown($form, $lang['add_user_form_usergroup'], "user_group", $user_info['user_group'], $dropdown_values, $dropdown_text, "usergroup").     
                $table -> add_submit_row($form, "submit", $lang['add_user_submit']).
                $table -> end_table().
                $form -> end_form()
        );

}



//***********************************************
// Add a user now kthnxbai
//***********************************************
function do_add_user()
{

        global $output, $lang, $db, $template_admin;

        $user_info = array(
                "username" => trim(stripslashes($_POST['username'])),
                "password" => $_POST['password'],
                "email" => $_POST['email'],
                "user_group" => $_POST['user_group'],
        );

        // Sort out invalid e-mail characters
        $user_info['email'] = str_replace( " ", "", $user_info['email']);
        $user_info['email'] = preg_replace( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $user_info['email']);

        // Check username length
        if(_strlen($user_info['username']) < 2 || _strlen($user_info['username']) > 25)
        {
               $output -> add($template_admin -> normal_error($lang['error_username_too_long']));        
               page_add_user($user_info);
               return;
        }

        // Check password length
        if(_strlen($user_info['password']) < 4 || _strlen($user_info['password']) > 14)
        {
               $output -> add($template_admin -> normal_error($lang['error_password_too_long']));        
               page_add_user($user_info);
               return;
        }

        // Check e-mail is valid
        if (!preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $user_info['email']))
        {
               $output -> add($template_admin -> normal_error($lang['error_invalid_email']));        
               page_add_user($user_info);
               return;
        }

        // Check for reserved characters in username
        $invalid_chars = array("'", "\"", "<!--", "\\");
        foreach ($invalid_chars as $char)
        {

                if (strstr($user_info['username'], $char))
                {
                       $output -> add($template_admin -> normal_error($lang['error_username_reserved_chars']));        
                       page_add_user($user_info);
                       return;
                }

        }

        // Check username is valid
        $check_username = $db -> query("select username from ".$db -> table_prefix."users where lower(username)='"._strtolower($user_info['username'])."'");
        
        if ($db -> num_rows($check_username) > 0)
        {
               $output -> add($template_admin -> normal_error($lang['error_username_exists']));        
               page_add_user($user_info);
               return;
        }

        // ********************************
        // Registraion was okay, so let's attempt to add this account
        // ********************************
        $query_string = "INSERT INTO ".$db -> table_prefix."users
        (username, user_group, ip_address, password, email, registered, 
        last_active, validate_id, need_validate)
        VALUES('".$user_info['username']."', '".$user_info['user_group']."', '".user_ip()."', '".md5($user_info['password'])."', '".$user_info['email']."', '".TIME."',
        '".TIME."', '0', '0')";
        
        $db -> basic_insert("users", $user_info);
        

        // Execute the query and check if it died.
        if(!$db -> query($query_string))
        {
               $output -> add($template_admin -> critical_error($lang['error_user_add']));        
               page_add_user($user_info);
               return;
        }

        // Get the ID number of the account just inserted
        $user_id = $db -> insert_id();

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=users&amp;m2=edit&amp;id=".$user_id, $lang['user_added_sucessfully']);

}



//***********************************************
// Search for a user form
//***********************************************
function page_search_users($search_info = "")
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Keep the groups in arrays
        // **************************
        $group_dropdown_values[] .= -1;
        $group_dropdown_text[] .= $lang['search_user_usergroup_all'];

        $db -> basic_select("user_groups", "id,name", "", "id", "", "asc");

        while($g_array = $db -> fetch_array())
        {
                $group_dropdown_values[] .= $g_array['id'];
                $group_dropdown_text[] .= $g_array['name'];
                
                $group_secondary_checkbox[$g_array['id']] = $g_array['name'];
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['search_user_title'];

        $output -> add_breadcrumb($lang['breadcrumb_users_search'], "index.php?m=users&amp;m2=search");

        $time_description = "<br /><font class=\"small_text\">".$lang['search_user_time_example']."</font>";
        
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // **************************
        // Fooooorm
        // **************************
        $output -> add(
                $form -> start_form("searchuser", ROOT."admin/index.php?m=users&amp;m2=dosearch", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_top_table_header($lang['search_user_title'], 2, "users_and_groups").
                $table -> add_basic_row($lang['search_user_message'], "normalcell",  "padding : 5px", "left", "100%", "2").

                $table -> add_row(
                        array(
                                array($lang['search_user_username'], "50%"),
                                array(
                                        $output -> return_help_button("username", false).
                                        $form -> input_dropdown("username_search", $search_info['username_search'],
                                                array(0, 1, 2, 3),
                                                array($lang['username_search_contains'], $lang['username_search_exactly'], $lang['username_search_starts'], $lang['username_search_end'])
                                                , "inputtext", "auto")." ".
                                        $form -> input_text("username", $search_info['username'], "inputtext", "60%")
                                , "50%")
                        )
                , "normalcell").       
                $table -> simple_input_row_text($form, $lang['search_user_email'], "email", $search_info['email'], "email").     
                $table -> simple_input_row_dropdown($form, $lang['search_user_usergroup'], "usergroup", $search_info['usergroup'], $group_dropdown_values, $group_dropdown_text, "usergroup").     
                $table -> simple_input_row_checkbox_list($form, $lang['search_user_usergroup_secondary'], "usergroup_secondary", $search_info['usergroup_secondary'], $group_secondary_checkbox, "usergroup_secondary").     
                $table -> simple_input_row_text($form, $lang['search_user_user_title'], "title", $search_info['title'], "title").     
                $table -> simple_input_row_text($form, $lang['search_user_signature'], "signature", $search_info['signature'], "signature").     
                $table -> simple_input_row_text($form, $lang['search_user_homepage'], "homepage", $search_info['homepage'], "homepage").     
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['search_subtitle_posts'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_int($form, $lang['search_user_posts_g'], "posts_g", $search_info['posts_g'], "posts_g").     
                $table -> simple_input_row_int($form, $lang['search_user_posts_l'], "posts_l", $search_info['posts_l'], "posts_l").     
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['search_subtitle_times'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_text($form, $lang['search_user_register_b'].$time_description, "register_b", $search_info['register_b'], "register_b").     
                $table -> simple_input_row_text($form, $lang['search_user_register_a'].$time_description, "register_a", $search_info['register_a'], "register_a").     
                $table -> simple_input_row_text($form, $lang['search_user_last_active_b'].$time_description, "last_active_b", $search_info['last_active_b'], "last_active_b").     
                $table -> simple_input_row_text($form, $lang['search_user_last_active_a'].$time_description, "last_active_a", $search_info['last_active_a'], "last_active_a").     
                $table -> simple_input_row_text($form, $lang['search_user_last_post_b'].$time_description, "last_post_b", $search_info['last_post_b'], "last_post_b").     
                $table -> simple_input_row_text($form, $lang['search_user_last_post_a'].$time_description, "last_post_a", $search_info['last_post_a'], "last_post_a").     
                $table -> end_table()
        );


        // **************************
        // Custom profile fields!
        // **************************
        $db -> basic_select("profile_fields", "id,name,description,field_type,dropdown_values,dropdown_text", "", "name", "", "asc");

        if($db -> num_rows()  > 0)
        {

                $output -> add(
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_basic_row($lang['search_subtitle_custom_fields'], "strip2",  "", "left", "100%", "2")
                );
                
                // We have some fields, go through them...
                while($f_array = $db -> fetch_array())
                {

                        // What input?
                        switch($f_array['field_type'])
                        {
                        
                                case "text":
                                        $form_bit = $form -> input_text("field_".$f_array['id'], $search_info['field'.$f_array['id']]);
                                        break;
                                        
                                case "textbox":
                                        $form_bit = $form -> input_textbox("field_".$f_array['id'], $search_info['field'.$f_array['id']]);
                                        break;

                                case "yesno":
                                        $form_bit = $form -> input_yesno("field_".$f_array['id'], $search_info['field'.$f_array['id']]);
                                        break;

                                case "dropdown":
                                        
                                        $dropdown_values = explode('|', $f_array['dropdown_values']);
                                        $dropdown_text = explode('|', $f_array['dropdown_text']);

                                        $dropdown_valuesa[] = "";
                                        $dropdown_texta[] = " ";

                                        foreach($dropdown_values as $key2 => $val)
                                        {
                                                $dropdown_valuesa[] = $val;
                                                $dropdown_texta[] = $dropdown_text[$key2];
                                        }

                                        $form_bit = $form -> input_dropdown("field_".$f_array['id'], $search_info['field_'.$f_array['id']], $dropdown_valuesa, $dropdown_texta);

                                        break;
                                        
                        }

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($f_array['name'] . "<br /><font class=\"small_text\">".$f_array['description'] ."</font>", "50%"),
                                                array($form_bit, "50%")
                                        )
                                , "normalcell")
                        );
                        
                }

                $output -> add(
                        $table -> end_table()
                );
                                
        }
        
        
        // **************************
        // End form
        // **************************
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_submit_row($form, "submitsearch", $lang['search_users_submit']).
                $table -> end_table().
                $form -> end_form()
        );
        
}



//***********************************************
// Lollerskates
//***********************************************
function do_search_users($search_info = "")
{

        global $output, $lang, $db, $template_admin;

        $search_info = array(
                "username" => $_POST['username'],
                "username_search" => $_POST['username_search'],
                "email" => $_POST['email'],
                "usergroup" => $_POST['usergroup'],
                "usergroup_secondary" => $_POST['usergroup_secondary'],
                "title" => $_POST['title'],
                "signature" => $_POST['signature'],
                "homepage" => $_POST['homepage'],                
                "posts_g" => intval($_POST['posts_g']),
                "posts_l" => intval($_POST['posts_l']),
                "register_b" => $_POST['register_b'],
                "register_a" => $_POST['register_a'],
                "last_active_b" => $_POST['last_active_b'],
                "last_active_a" => $_POST['last_active_a'],
                "last_post_b" => $_POST['last_post_b'],
                "last_post_a" => $_POST['last_post_a']
        );
        
        array_map("trim", $search_info);

        // *****************************
        // Check we're submitting a search
        // *****************************
        if(!$_POST['submitsearch'])
        {
                $output -> add($template_admin -> normal_error($lang['invalid_search']));
                page_search_users($search_info);
                return;
        }               

        // *****************************
        // Let's build the query
        // *****************************
        $query_string =  create_user_search_string($search_info);

        // *****************************
        // Do the query
        // *****************************
        $search_query = $db -> query("SELECT u.*, u.id as user_id FROM ".$db -> table_prefix."users u LEFT JOIN ".$db -> table_prefix."profile_fields_data p ON (p.member_id=u.id)".$query_string);

        if($db -> num_rows($search_query) < 1)
        {
                $output -> add($template_admin -> normal_error($lang['search_no_results']));
                page_search_users($search_info);
                return;
        }

        // *****************************
        // Only found one? Let's show it.
        // *****************************
        if($db -> num_rows($search_query) == 1)
        {
                $u_info = $db -> fetch_array($search_query);
                
                $_GET['id'] = $u_info['user_id'];
                
                page_edit_user();
                return;
        
        } 

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['search_results_title'];

        $output -> add_breadcrumb($lang['breadcrumb_users_search_results'], "index.php?m=users&amp;m2=dosearch");

        // **************************
        // Print the results!
        // **************************
        $table = new table_generate;

        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['search_results_title'], "strip1",  "", "left", "100%", "6").
                $table -> add_row(
                        array(
                                array($lang['search_results_username'], "20%"),
                                array($lang['search_results_email'], "25%"),
                                array($lang['search_results_posts'], "5%"),
                                array($lang['search_results_last_active'], "20%"),
                                array($lang['search_results_registered'], "20%"),
                                array($lang['search_results_actions'], "5%")
                        )
                , "strip2")

        );

        //Go through users
        while($user_array = $db -> fetch_array($search_query))
                $output -> add(
                        $table -> add_row(
                                array(
                                        array("<a href=\"".ROOT."index.php?m=u&amp;id=".$user_array['user_id']."\" title=\"".$lang['search_users_view']."\">".$user_array['username']."</a>
                                                <br /><font class=\"small_text\">".$user_array['ip_address']." (<a href=\"index.php?m=users&amp;m2=doipsearch&amp;user_id=".$user_array['user_id']."\">IP info</a>)</font>"
                                        , "20%"),
                                        array("<a href=\"mailto:".$user_array['email']."\">".$user_array['email']."</a>", "25%"),
                                        array($user_array['posts'], "5%"),
                                        array(return_formatted_date("dS M Y H:i", $user_array['last_active']), "20%"),
                                        array(return_formatted_date("dS M Y", $user_array['registered']), "20%"),
                                        array("
                                                <a href=\"index.php?m=users&amp;m2=edit&amp;id=".$user_array['user_id']."\" title=\"".$lang['search_users_edit']."\">
                                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                                                <a href=\"index.php?m=users&amp;m2=delete&amp;id=".$user_array['user_id']."\" title=\"".$lang['search_users_delete']."\">
                                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>
                                        ", "5%", "center")
                                )
                        , "normalcell")
                );

        $output -> add($table -> end_table());
                
}



//***********************************************
// Edit one user
//***********************************************
function page_edit_user($user_info = "")
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select u.*, p.*, u.username as orig_username from ".$db -> table_prefix."users u 
        left join ".$db -> table_prefix."profile_fields_data p on(p.member_id = u.id)
        where u.id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        if(!$user_info)
        {
                $user_info = $db -> fetch_array();
                $user_info['username'] = $user_info['orig_username'];
        }
        else
        {
                $db_info = $db -> fetch_array();
                $user_info['orig_username'] = $db_info['username'];
        }
        
        // **************************
        // Sort out the birthday vaules
        // **************************
        $user_info['birthday'] = array($user_info['birthday_day'], $user_info['birthday_month'], $user_info['birthday_year']);

        // **************************
        // Secondary usergroups
        // **************************
        $user_info['usergroup_secondary'] = array();
        foreach(explode(",", $user_info['secondary_user_group']) as $val)
                $user_info['usergroup_secondary'][$val] = 1;         

        // **************************
        // Do timezone stuff
        // **************************
        $time_offset_dropdown_text = array(
                $lang['timezone_gmt_minus_12'], $lang['timezone_gmt_minus_11'],
                $lang['timezone_gmt_minus_10'], $lang['timezone_gmt_minus_9'],
                $lang['timezone_gmt_minus_8'], $lang['timezone_gmt_minus_7'],
                $lang['timezone_gmt_minus_6'], $lang['timezone_gmt_minus_5'],
                $lang['timezone_gmt_minus_4'], $lang['timezone_gmt_minus_3'],
                $lang['timezone_gmt_minus_2'], $lang['timezone_gmt_minus_1'],
                $lang['timezone_gmt'], $lang['timezone_gmt_plus_1'],
                $lang['timezone_gmt_plus_2'], $lang['timezone_gmt_plus_3'],
                $lang['timezone_gmt_plus_4'], $lang['timezone_gmt_plus_5'],
                $lang['timezone_gmt_plus_6'], $lang['timezone_gmt_plus_7'],
                $lang['timezone_gmt_plus_8'], $lang['timezone_gmt_plus_9'],
                $lang['timezone_gmt_plus_10'], $lang['timezone_gmt_plus_11'],
                $lang['timezone_gmt_plus_12']
        );
        
        $time_offset_dropdown_values = array(
                -12, -11, -10, -9, -8, -7, -6, -5, -4, -3, -2, -1, 0,
                1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12
        );


        // **************************
        // Do user group dropdown
        // **************************
        $usergroups_dropdown_text = array(); 
        $usergroups_dropdown_values = array(); 
        $group_secondary_checkbox = array();
        
        $db -> basic_select("user_groups", "id,name", "", "id", "", "asc");

        while($g_array = $db -> fetch_array())
        {
                $usergroups_dropdown_values[] .= $g_array['id'];
                $usergroups_dropdown_text[] .= $g_array['name'];
                
                $group_secondary_checkbox[$g_array['id']] = $g_array['name'];
        }

        // **************************
        // Create language dropdown
        // **************************
        $languages_dropdown_text = array($lang['edit_user_board_default']); 
        $languages_dropdown_values = array(-1); 

        $db -> basic_select("languages", "id,name", "", "name", "", "asc");

        while($l_array = $db -> fetch_array())
        {
                $languages_dropdown_text[] .= $l_array['name']; 
                $languages_dropdown_values[] .= $l_array['id'];                 
        }

        // **************************
        // Create theme dropdown
        // **************************
        $themes_dropdown_text = array($lang['edit_user_board_default']); 
        $themes_dropdown_values = array(-1); 

        $db -> basic_select("themes", "id,name", "", "name", "", "asc");

        while($t_array = $db -> fetch_array())
        {
                $themes_dropdown_text[] .= $t_array['name']; 
                $themes_dropdown_values[] .= $t_array['id'];                 
        }


        // **************************
        // Some of the page
        // **************************
        $page_title = $output -> replace_number_tags($lang['edit_user_title'], array($user_info['orig_username']));

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $page_title;

        $output -> add_breadcrumb($lang['breadcrumb_users_edit'], "index.php?m=users&amp;m2=edit&amp;id=".$get_id);

        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("edituser", ROOT."admin/index.php?m=users&amp;m2=doedit&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($page_title, "strip1",  "", "left").
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row(
                        "[ <b>".$lang['edit_user_edit_profile']."</b> -  
			<a href=\"".ROOT."admin/index.php?m=users&amp;m2=changename&amp;id=".$get_id."\">".$lang['edit_user_change_username']."</a> - 
                        <a href=\"".ROOT."admin/index.php?m=users&amp;m2=changepass&amp;id=".$get_id."\">".$lang['edit_user_change_password']."</a> - 
                        <a href=\"".ROOT."admin/index.php?m=users&amp;m2=delete&amp;id=".$get_id."\">".$lang['edit_user_delete_user']."</a> ]", "normalcell
                ").
                $table -> end_table().

                // -------------------
                // Profile Info
                // -------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['edit_user_profile_info_title'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_text($form, $lang['edit_user_email'], "email", $user_info['email']).     
                $table -> simple_input_row_dropdown($form, $lang['edit_user_usergroup'], "user_group", $user_info['user_group'], $usergroups_dropdown_values, $usergroups_dropdown_text).     
                $table -> simple_input_row_checkbox_list($form, $lang['edit_user_usergroup_secondary'], "usergroup_secondary", $user_info['usergroup_secondary'], $group_secondary_checkbox).     
                $table -> simple_input_row_text($form, $lang['edit_user_usertitle'], "title", $user_info['title']).     
                $table -> simple_input_row_text($form, $lang['edit_user_real_name'], "real_name", $user_info['real_name']).     
                $table -> simple_input_row_text($form, $lang['edit_user_homepage'], "homepage", $user_info['homepage']).     
                $table -> simple_input_row_text($form, $lang['edit_user_yahoo_messenger'], "yahoo_messenger", $user_info['yahoo_messenger']).     
                $table -> simple_input_row_text($form, $lang['edit_user_aol_messenger'], "aol_messenger", $user_info['aol_messenger']).     
                $table -> simple_input_row_text($form, $lang['edit_user_msn_messenger'], "msn_messenger", $user_info['msn_messenger']).     
                $table -> simple_input_row_text($form, $lang['edit_user_icq_messenger'], "icq_messenger", $user_info['icq_messenger']).     
                $table -> simple_input_row_text($form, $lang['edit_user_gtalk_messenger'], "gtalk_messenger", $user_info['gtalk_messenger']).     
                $table -> simple_input_row_date($form, $lang['edit_user_birthday'], "birthday", $user_info['birthday'], true, true).     
                $table -> simple_input_row_textbox($form, $lang['edit_user_signature'], "signature", _htmlentities($user_info['signature']), 10).     
                $table -> simple_input_row_int($form, $lang['edit_user_posts'], "posts", $user_info['posts']).     
                $table -> end_table().

                // -------------------
                // Display Settings
                // -------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['edit_user_display_title'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_dropdown($form, $lang['edit_user_language'], "language", $user_info['language'], $languages_dropdown_values, $languages_dropdown_text).     
                $table -> simple_input_row_dropdown($form, $lang['edit_user_theme'], "theme", $user_info['theme'], $themes_dropdown_values, $themes_dropdown_text).     
                $table -> end_table().

                // -------------------
                // Board Settings
                // -------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['edit_user_board_settings_title'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_yesno($form, $lang['edit_user_hide_email'], "hide_email", $user_info['hide_email']).     
                $table -> simple_input_row_yesno($form, $lang['edit_user_view_sigs'], "view_sigs", $user_info['view_sigs']).     
                $table -> simple_input_row_yesno($form, $lang['edit_user_view_avatars'], "view_avatars", $user_info['view_avatars']).     
                $table -> simple_input_row_yesno($form, $lang['edit_user_view_images'], "view_images", $user_info['view_images']).     
                $table -> simple_input_row_yesno($form, $lang['edit_user_email_new_pm'], "email_new_pm", $user_info['email_new_pm']).     
                $table -> simple_input_row_yesno($form, $lang['edit_user_email_from_admin'], "email_from_admin", $user_info['email_from_admin']).     
                $table -> end_table().

                // -------------------
                // Time Settings
                // -------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['edit_user_time_title'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_dropdown($form, $lang['edit_user_time_offset'], "time_offset", $user_info['time_offset'], $time_offset_dropdown_values, $time_offset_dropdown_text).     
                $table -> simple_input_row_yesno($form, $lang['edit_user_dst_on'], "dst_on", $user_info['dst_on']).     
                $table -> simple_input_row_date($form, $lang['edit_user_registered'], "registered", $user_info['registered'], true).     
                $table -> simple_input_row_date($form, $lang['edit_user_last_active'], "last_active", $user_info['last_active']).     
                $table -> simple_input_row_date($form, $lang['edit_user_last_post_date'], "last_post_time", $user_info['last_post_time']).     
                $table -> end_table()
                
        );


        // **************************
        // Custom profile fields!
        // **************************
        $db -> basic_select("profile_fields", "id,name,description,field_type,dropdown_values,dropdown_text", "", "name", "", "asc");

        if($db -> num_rows()  > 0)
        {

                $output -> add(
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_basic_row($lang['edit_user_custom_fields_title'], "strip2",  "", "left", "100%", "2")
                );

                // We have some fields, go through them...
                while($f_array = $db -> fetch_array())
                {

                        // What input?
                        switch($f_array['field_type'])
                        {
                        
                                case "text":
                                        $form_bit = $form -> input_text("field_".$f_array['id'], $user_info['field'.$f_array['id']]);
                                        break;
                                        
                                case "textbox":
                                        $form_bit = $form -> input_textbox("field_".$f_array['id'], $user_info['field'.$f_array['id']]);
                                        break;

                                case "yesno":
                                        $form_bit = $form -> input_yesno("field_".$f_array['id'], $user_info['field'.$f_array['id']]);
                                        break;

                                case "dropdown":
                                        
                                        $dropdown_values = explode('|', $f_array['dropdown_values']);
                                        $dropdown_text = explode('|', $f_array['dropdown_text']);

                                        $dropdown_valuesa[] = "";
                                        $dropdown_texta[] = " ";

                                        foreach($dropdown_values as $key2 => $val)
                                        {
                                                $dropdown_valuesa[] = $val;
                                                $dropdown_texta[] = $dropdown_text[$key2];
                                        }

                                        $form_bit = $form -> input_dropdown("field_".$f_array['id'], $user_info['field_'.$f_array['id']], $dropdown_valuesa, $dropdown_texta);

                                        break;
                                        
                        }

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($f_array['name'] . "<br /><font class=\"small_text\">".$f_array['description'] ."</font>", "50%"),
                                                array($form_bit, "50%")
                                        )
                                , "normalcell")
                        );
                        
                }

                $output -> add(
                        $table -> end_table()
                );
                                
        }

        // **************************
        // End the page
        // **************************
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_submit_row($form, "submitedit", $lang['edit_user_submit']).
                $table -> end_table().
                $form -> end_form()
        );
                
}



//***********************************************
// Guess I'm editing
//***********************************************
function do_edit_user()
{

        global $output, $lang, $db, $template_admin, $user;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select username, id from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db_user = $db -> fetch_array();

        // **************************
        // Grab all the info
        // **************************
        foreach($_POST['registered'] as $key => $val)
        	if(!$val)
        		$_POST['registered'][$key] = 0;
        
        foreach($_POST['last_active'] as $key => $val)
        	if(!$val)
        		$_POST['last_active'][$key] = 0;
        
        foreach($_POST['last_post_time'] as $key => $val)
        	if(!$val)
        		$_POST['last_post_time'][$key] = 0;
        
        $user_info = array(
                "email"                 => $_POST['email'],
                "user_group"            => $_POST['user_group'],
                "title"                 => $_POST['title'],
                "real_name"             => $_POST['real_name'],
                "homepage"              => $_POST['homepage'],
                "yahoo_messenger"       => $_POST['yahoo_messenger'],
                "aol_messenger"         => $_POST['aol_messenger'],
                "msn_messenger"         => $_POST['msn_messenger'],
                "icq_messenger"         => $_POST['icq_messenger'],
                "gtalk_messenger"       => $_POST['gtalk_messenger'],                
                "birthday_day"          => intval($_POST['birthday_day']),
                "birthday_month"        => intval($_POST['birthday_month']),
                "birthday_year"         => intval($_POST['birthday_year']),
                "signature"             => $_POST['signature'],
                "posts"                 => $_POST['posts'],
                "language"              => $_POST['language'],
                "theme"                 => $_POST['theme'],
                "hide_email"            => $_POST['hide_email'],
                "view_sigs"             => $_POST['view_sigs'],
                "view_avatars"          => $_POST['view_avatars'],
                "view_images"           => $_POST['view_images'],
                "email_new_pm"          => $_POST['email_new_pm'],
                "email_from_admin"      => $_POST['email_from_admin'],
                "time_offset"           => $_POST['time_offset'],
                "dst_on"                => $_POST['dst_on'],
                "registered"            => mktime(0, 0, 0, $_POST['registered']['month'], $_POST['registered']['day'], $_POST['registered']['year']),
                "last_active"           => mktime($_POST['last_active']['hour'], $_POST['last_active']['minute'], 0, $_POST['last_active']['month'], $_POST['last_active']['day'], $_POST['last_active']['year']),
                "last_post_time"        => mktime($_POST['last_post_time']['hour'], $_POST['last_post_time']['minute'], 0, $_POST['last_post_time']['month'], $_POST['last_post_time']['day'], $_POST['last_post_time']['year'])
        );

        $user_info['email'] = str_replace( " ", "", $user_info['email']);
        $user_info['email'] = preg_replace( "#[\;\#\n\r\*\'\"<>&\%\!\(\)\{\}\[\]\?\\/\s]#", "", $user_info['email']);

        // Secondary user groups
        $second_groups = $_POST['usergroup_secondary'];
        $second_groups2 = array();

        if(count($second_groups) > 0)
        {
                foreach($second_groups as $key => $val)
                        $second_groups2[] = $key;
                        
                $user_info['secondary_user_group'] = implode(",", $second_groups2); 
        }
        else
                $user_info['secondary_user_group'] = "";

        // Custom profile fields
		$profile_fields_info = array();
		
        $db -> basic_select("profile_fields", "id");

		if($db -> num_rows() > 0)
	        while($p_array = $db -> fetch_array())
	                $profile_fields_info['field_'.$p_array['id']] = $_POST['field_'.$p_array['id']];
                        
        
        // **************************
        // Editing own user group? HAH.
        // **************************
        if(
                $user -> user_id == $db_user['id']
                &&
                (
                        $user -> info['user_group'] != $user_info['user_group'])
                        ||
                        implode(",", $user -> info['secondary_user_group']) != trim($user_info['secondary_user_group'])
                )
        {                
                $output -> add($template_admin -> normal_error($lang['cant_edit_own_group']));
                page_edit_user($user_info);
                return;
        }

        // **************************
        // Check e-mail is valid
        // **************************
        if (!preg_match( "/^.+\@(\[?)[a-zA-Z0-9\-\.]+\.([a-zA-Z]{2,4}|[0-9]{1,4})(\]?)$/", $user_info['email']))
        {
               $output -> add($template_admin -> normal_error($lang['error_invalid_email']));        
               page_edit_user($user_info);
               return;
        }

        // **************************
        // Theme check
        // **************************
        $db -> basic_select("themes", "id");

        while($t_array = $db -> fetch_array())
                $themes_array[$t_array['id']] = true;

        if($user_info['theme'] != -1 && !array_key_exists($user_info['theme'], $themes_array))
        {
               $output -> add($template_admin -> normal_error($lang['edit_user_invalid_theme']));        
               page_edit_user($user_info);
               return;
        }
        
        // **************************
        // Language check
        // **************************
        $db -> basic_select("languages", "id");

        while($l_array = $db -> fetch_array())
                $lang_array[$l_array['id']] = true;

        if($user_info['language'] != -1 && !array_key_exists($user_info['language'], $lang_array))
        {
               $output -> add($template_admin -> normal_error($lang['edit_user_invalid_language']));        
               page_edit_user($user_info);
               return;
        }

        // **************************
        // Birthday check 
        // **************************
        if($user_info['birthday_year'] && $user_info['birthday_month'] && $user_info['birthday_day'])
        {
        
                if($user_info['birthday_year'] < 1901 OR $user_info['birthday_year'] > date('Y'))
                        $user_info['birthday_year'] = "";
        
                if($user_info['birthday_month'] < 10)
                        $user_info['birthday_month'] = "0".$user_info['birthday_month'];
        
                if($user_info['birthday_day'] < 10)
                        $user_info['birthday_day'] = "0".$user_info['birthday_day'];

        }
        else
        {
                $user_info['birthday_year'] = "";
                $user_info['birthday_month'] = "";
                $user_info['birthday_day'] = "";
        }
        

        // **************************
        // Update the profile now!
        // **************************
        if(!$db -> basic_update("users", $user_info, "id='".$db_user['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_user']));
                page_edit_user($user_info);
                return;
        }

        // **************************
        // and the custom fields
        // **************************
        if(count($profile_fields_info) > 0)
        {

	        // Check the entry exists
	        $db -> basic_select("profile_fields_data", "member_id", "member_id='".$db_user['id']."'");
	        
	        // Insert or update
	        if($db -> num_rows() == 0)
	        {
	                $profile_fields_info['member_id'] = $db_user['id'];
	                $update_query = $db -> basic_insert("profile_fields_data", $profile_fields_info);
	        }
	        else
	                $update_query = $db -> basic_update("profile_fields_data", $profile_fields_info, "member_id='".$db_user['id']."'");
	        
	        // Check it
	        if(!$update_query)        
	        {
	                $output -> add($template_admin -> critical_error($lang['error_updating_user_profile_fields']));
	                page_edit_user($user_info);
	                return;
	        }
        }
        
        // *********************
        // Log action
        // *********************
        log_admin_action("users", "doedit", "Edited user: ".$db_user['username']);

        // *********************
        // Redirect the user
        // *********************
        $output -> redirect(ROOT."admin/index.php?m=users&amp;m2=edit&amp;id=".$db_user['id'], $lang['user_updated']);
             
}


//***********************************************
// Edit a users username
//***********************************************
function page_change_name($input_info = "")
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select id, username from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        $user_info = $db -> fetch_array();

        // **************************
        // Do the form
        // **************************
        $page_title = $output -> replace_number_tags($lang['edit_username_title'], array($user_info['username']));
        $contents = $input_info['contents']? $input_info['contents'] : $lang['email_changed_username'];

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $page_title;

        $output -> add_breadcrumb($lang['breadcrumb_users_edit'], "index.php?m=users&amp;m2=edit&amp;id=".$get_id);
        $output -> add_breadcrumb($lang['breadcrumb_users_edit_name'], "index.php?m=users&amp;m2=changename&amp;id=".$get_id);

        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("editusername", ROOT."admin/index.php?m=users&amp;m2=dochangename&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($page_title, "strip1",  "", "left").
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row(
                        "[ <a href=\"".ROOT."admin/index.php?m=users&amp;m2=edit&amp;id=".$get_id."\">".$lang['edit_user_edit_profile']."</a> -  
			<b>".$lang['edit_user_change_username']."</b> - 
                        <a href=\"".ROOT."admin/index.php?m=users&amp;m2=changepass&amp;id=".$get_id."\">".$lang['edit_user_change_password']."</a> - 
                        <a href=\"".ROOT."admin/index.php?m=users&amp;m2=delete&amp;id=".$get_id."\">".$lang['edit_user_delete_user']."</a> ]", "normalcell
                ").
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_row(
                        array( 
                                array($lang['edit_username_current'], "50%"),
                                array($user_info['username'], "50%")
                        ),
                "normalcell").
                $table -> simple_input_row_text($form, $lang['edit_username_enter_new'], "username", $input_info['username']).     
                $table -> simple_input_row_yesno($form, $lang['edit_username_send_email'], "send_email", $input_info[send_email]).     
                $table -> simple_input_row_textbox($form, $lang['edit_username_email_contents']."<br /><font class=\"small_text\">".$lang['email_changed_username_description']."</font>", "contents", $contents, 5).     
                $table -> add_submit_row($form, "submit", $lang['edit_username_submit']).
                $table -> end_table().
                
                $form -> end_form()
        );        
}



//***********************************************
// Submit an edit for a users username
//***********************************************
function do_change_name()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select id, username, email from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        $user_info = $db -> fetch_array();

        // **************************
        // Check the inputted stuff
        // **************************
        $input_info = array(
                "username" => trim(stripslashes($_POST['username'])),
                "send_email" => $_POST['send_email'],
                "contents" => $_POST['contents']
        );

        // Check username is even inputted
        if($input_info['username'] == "")
        {
               $output -> add($template_admin -> normal_error($lang['change_username_error_not_inputted']));        
               page_change_name($input_info);
               return;
        }
                
        // Check for reserved characters in username
        $invalid_chars = array("'", "\"", "<!--", "\\");
        foreach($invalid_chars as $char)
        {

                if(strstr($input_info['username'], $char))
                {
                       $output -> add($template_admin -> normal_error($lang['error_username_reserved_chars']));        
                       page_change_name($input_info);
                       return;
                }

        }

        // Check username length
        if(_strlen($input_info['username']) < 2 || _strlen($input_info['username']) > 25)
        {
               $output -> add($template_admin -> normal_error($lang['error_username_too_long']));        
               page_change_name($input_info);
               return;
        }
        
        // Check username is the same
        if($input_info['username'] == $user_info['username'])
        {
               $output -> add($template_admin -> normal_error($lang['error_username_is_same']));        
               page_change_name($input_info);
               return;
        }
        
        // Check username is valid
        $check_username = $db -> query("select username from ".$db -> table_prefix."users where lower(username)='"._strtolower($input_info['username'])."'");
        
        if($db -> num_rows($check_username) > 0)
        {
        	
		// Check for same name but different case. fiona > Fiona is valid.
		if($input_info['username'] != $user_info['username'] && _strtolower($input_info['username']) != _strtolower($user_info['username']))
		{
			$output -> add($template_admin -> normal_error($lang['error_username_exists']));        
			page_change_name($input_info);
			return;
		}
		
        }

        // **************************
        // Sending an e-mail?
        // **************************
        if($input_info['send_email'])        
        {
                
                // We need to replace certain things so the email sends the right info. Kay?
                $input_info['contents'] = str_replace('<old_name>', $user_info['username'], $input_info['contents']);
                $input_info['contents'] = str_replace('<new_name>', $input_info['username'], $input_info['contents']);
                $input_info['contents'] = str_replace('<board_name>', $cache -> cache['config']['board_name'], $input_info['contents']);
                $input_info['contents'] = str_replace('<board_url>', $cache -> cache['config']['board_url'], $input_info['contents']);

                // Send the e-mail
                $mail = new email;
                $mail -> send_mail($user_info['email'], $lang['email_changed_username_subject'], $input_info['contents']);
        
        }
        
        // **************************
        // Now update where we need to
        // **************************
        $db -> basic_update("users", array("username" => $input_info['username']), "id='".$user_info['id']."'");
        $db -> basic_update("moderators", array("username" => $input_info['username']), "user_id='".$user_info['id']."'");

        // ******************
        // Update moderator cache
        // ******************
        $cache -> update_cache("moderators");

        // ******************
        // Log it!
        // ******************
        log_admin_action("users", "dochangename", "Changed member '".$user_info['username']."' name to '".$input_info['username']."'");
        
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=users&amp;m2=changename&amp;id=".$user_info['id'], $lang['username_changed_sucessfully']);
        
}



//***********************************************
// Edit a users password
//***********************************************
function page_change_password($input_info = "")
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select id, username from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        $user_info = $db -> fetch_array();

        // **************************
        // Do the form
        // **************************
        $page_title = $output -> replace_number_tags($lang['edit_password_title'], array($user_info['username']));
        $contents = $input_info['contents']? $input_info['contents'] : $lang['email_changed_password'];

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $page_title;

        $output -> add_breadcrumb($lang['breadcrumb_users_edit'], "index.php?m=users&amp;m2=edit&amp;id=".$get_id);
        $output -> add_breadcrumb($lang['breadcrumb_users_edit_password'], "index.php?m=users&amp;m2=changepass&amp;id=".$get_id);

        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("editpassword", ROOT."admin/index.php?m=users&amp;m2=dochangepass&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($page_title, "strip1",  "", "left").
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row(
                        "[ <a href=\"".ROOT."admin/index.php?m=users&amp;m2=edit&amp;id=".$get_id."\">".$lang['edit_user_edit_profile']."</a> -  
			<a href=\"".ROOT."admin/index.php?m=users&amp;m2=changename&amp;id=".$get_id."\">".$lang['edit_user_change_username']."</a> - 
                        <b>".$lang['edit_user_change_password']."</b> - 
                        <a href=\"".ROOT."admin/index.php?m=users&amp;m2=delete&amp;id=".$get_id."\">".$lang['edit_user_delete_user']."</a> ]", "normalcell
                ").
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> simple_input_row_text($form, $lang['edit_password_enter_new'], "password", $input_info['password']).     
                $table -> simple_input_row_text($form, $lang['edit_password_enter_new_again'], "password2", $input_info['password2']).     
                $table -> simple_input_row_yesno($form, $lang['edit_password_send_email'], "send_email", $input_info['send_email']).     
                $table -> simple_input_row_textbox($form, $lang['edit_password_email_contents']."<br /><font class=\"small_text\">".$lang['edit_password_email_contents_description']."</font>", "contents", $contents, 5).     
                $table -> add_submit_row($form, "submit", $lang['edit_password_submit']).
                $table -> end_table().
                
                $form -> end_form()
        );        
        
}



//***********************************************
// Submit an edit for a users password
//***********************************************
function do_change_password()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select id, username, email from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        $user_info = $db -> fetch_array();

        // **************************
        // Check the inputted stuff
        // **************************
        $input_info = array(
                "password" => $_POST['password'],
                "password2" => $_POST['password2'],
                "send_email" => $_POST['send_email'],
                "contents" => $_POST['contents']
        );

        // Check password is inputted
        if($input_info['password'] == "" || $input_info['password2'] == "")
        {
               $output -> add($template_admin -> normal_error($lang['change_password_error_not_inputted']));        
               page_change_password($input_info);
               return;
        }

        
        // Check password length
        if(_strlen($input_info['password']) < 4 || _strlen($input_info['password']) > 14)
        {
               $output -> add($template_admin -> normal_error($lang['change_password_error_too_long']));        
               page_change_password($input_info);
               return;
        }

        // Check passwords match
        if($input_info['password'] != $input_info['password2'])
        {
               $output -> add($template_admin -> normal_error($lang['change_password_error_no_match']));        
               page_change_password($input_info);
               return;
        }                

        // **************************
        // Sending an e-mail?
        // **************************
        if($input_info['send_email'])        
        {
                
                // We need to replace certain things so the email sends the right info. Kay?
                $input_info['contents'] = str_replace('<username>', $user_info['username'], $input_info['contents']);
                $input_info['contents'] = str_replace('<new_password>', $input_info['password'], $input_info['contents']);
                $input_info['contents'] = str_replace('<board_name>', $cache -> cache['config']['board_name'], $input_info['contents']);
                $input_info['contents'] = str_replace('<board_url>', $cache -> cache['config']['board_url'], $input_info['contents']);

                // Send the e-mail
                $mail = new email;
                $mail -> send_mail($user_info['email'], $lang['email_changed_password_subject'], $input_info['contents']);
        
        }
        
        // **************************
        // Now update where we need to
        // **************************
        $db -> basic_update("users", array("password" => md5($input_info['password'])), "id='".$user_info['id']."'");

        // ******************
        // Log it!
        // ******************
        log_admin_action("users", "dochangepass", "Changed password for ".$user_info['username']);
        
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=users&amp;m2=changepass&amp;id=".$user_info['id'], $lang['password_changed_sucessfully']);
        
}



//***********************************************
// Baleetion!
//***********************************************
function page_delete_user()
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select id, username from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        $user_info = $db -> fetch_array();

        // Print message
        $lang['delete_user_message'] = $output -> replace_number_tags($lang['delete_user_message'], array($user_info['username'], ROOT, $user_info['id']));
        $lang['delete_user_title'] = $output -> replace_number_tags($lang['delete_user_title'], array($user_info['username']));

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['delete_user_title'];

        $output -> add_breadcrumb($lang['breadcrumb_users_edit'], "index.php?m=users&amp;m2=edit&amp;id=".$get_id);
        $output -> add_breadcrumb($lang['breadcrumb_users_delete'], "index.php?m=users&amp;m2=delete&amp;id=".$get_id);

        $table = new table_generate;

        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['delete_user_title'], "strip1",  "", "left").
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row(
                        "[ <a href=\"".ROOT."admin/index.php?m=users&amp;m2=edit&amp;id=".$get_id."\">".$lang['edit_user_edit_profile']."</a> -  
			<a href=\"".ROOT."admin/index.php?m=users&amp;m2=changename&amp;id=".$get_id."\">".$lang['edit_user_change_username']."</a> - 
                        <a href=\"".ROOT."admin/index.php?m=users&amp;m2=changepass&amp;id=".$get_id."\">".$lang['edit_user_change_password']."</a> - 
                        <b>".$lang['edit_user_delete_user']."</b> ]", "normalcell
                ").

                $table -> end_table().

                $template_admin -> message($lang['delete_user_title'], $lang['delete_user_message'])
        );

}



//***********************************************
// Finish baleetion!
//***********************************************
function do_delete_user()
{

        global $output, $lang, $db, $template_admin, $user, $cache;

        // **************************
        // Select the user we want
        // **************************
        $get_id = $_GET['id'];
        if(!$get_id)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }
        
        $db -> query("select id, username from ".$db -> table_prefix."users where id='".$get_id."' limit 1");

        if($db -> num_rows() < 1)
        {
                $output -> add($template_admin -> normal_error($lang['invalid_user_id']));
                return;
        }

        $user_info = $db -> fetch_array();

        
        // **************************
        // Deleting ourself?
        // **************************
        if($user_info['id'] == $user -> user_id)
        {
                $output -> add($template_admin -> normal_error($lang['cannot_delete_self']));
                return;
        }

        // **************************
        // Remove avatar
        // **************************

        //
        
        // **************************
        // Convert postings to guest
        // **************************
        
        //
        
        // **************************
        // Remove PM's
        // **************************

        // 

        // **************************
        // Remove user
        // **************************
        $db -> basic_delete("users", "id='".$user_info['id']."'");
        $db -> basic_delete("profile_fields_data", "member_id='".$user_info['id']."'");

        // **************************
        // Remove moderators
        // **************************
        $db -> basic_delete("moderators", "user_id='".$user_info['id']."'");
        
        $cache -> update_cache("moderators");
        
        // **************************
        // Fix stat cache
        // **************************
        // Total members...
        $q = $db -> query("select count(*) from ".$db -> table_prefix."users where user_group <> 5");
        $q_r = $db -> result();
        $cache -> cache['stats']['total_members'] = $q_r;

        // Newest member members...
        $q = $db -> query("select id,username from ".$db -> table_prefix."users where user_group <> 5 order by registered desc limit 1");
        $q_r = $db -> fetch_array();
        $cache -> cache['stats']['newest_member_id'] = $q_r['id'];
        $cache -> cache['stats']['newest_member_username'] = $q_r['username'];

        // Update cache
        $db -> basic_update("cache", array("content" => serialize($cache -> cache['stats'])), "name='stats'");

        // ******************
        // Log it!
        // ******************
        log_admin_action("users", "dodelete", "Deleted user ".$user_info['username']);
        
        // ******************
        // Done
        // ******************
        $output -> redirect(ROOT."admin/index.php?m=users&amp;m2=search", $lang['user_deleted_sucessfully']);

}



//***********************************************
// Search for a user by IP, the form
//***********************************************
function page_search_ip($search_info = "")
{

        global $output, $lang;

        $output -> page_title = $lang['search_ip_title'];

        $output -> add_breadcrumb($lang['breadcrumb_users_ipsearch'], "index.php?m=users&amp;m2=ipsearch");

        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("searchip", ROOT."admin/index.php?m=users&amp;m2=doipsearch", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['search_ip_title'], "strip1",  "", "left", "100%", 2).
                $table -> simple_input_row_text($form, $lang['search_ip_by_ip'], "by_ip", $search_info['by_ip']).     
                $table -> simple_input_row_text($form, $lang['search_ip_by_name'], "by_name", $search_info['by_name']).     
                $table -> add_submit_row($form, "submit", $lang['submit_search_ip']).
                $table -> end_table().
                $form -> end_form()
        );


}



//***********************************************
// Search for users IP addresses.
//***********************************************
function do_search_ip()
{

        global $output, $lang, $db, $template_admin;

        $search_info = array(
                        "by_ip" => $_POST['by_ip'],
                        "by_name" => $_POST['by_name']
                );
                
        array_map('trim', $search_info);
        
        if(trim($_GET['user_id']))
        {
                $search_info['by_name'] = trim($_GET['user_id']);
                $search_by_id = true;
        }
        
        // -----------------
        // Doing username
        // -----------------
        if($search_info['by_name'])
        {

                // ---

                // if search_by_id
                //      select id from user where id='$search_info['by_name']'
                // else
                //      select id from user where username='$search_info['by_name']'

                // if num_rows < 1
                //      error
                // else
                //      $id = result;
                //      select ip_address from post where user_id = $id and ip_address <> ''

                // ---
        
        }
        // -----------------
        // Doing IP search
        // -----------------
        elseif($search_info['by_ip'])
        {

                // Table header
                $hostname = @gethostbyaddr($search_info['by_ip']);
                $hostname = ($hostname == $search_info['by_ip'] || $hostname == "") ? $lang['search_ip_no_hostname'] : $hostname;
                
                $table = new table_generate;
                
                $output -> add(
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_basic_row($lang['search_ip_results_ip_title'], "strip1",  "", "left", "100%", 2).
                        $table -> add_row(
                                array(
                                        array("<b>".$search_info['by_ip']."</b>", "50%"),
                                        array($lang['search_ip_hostname']." - ".$hostname, "50%")
                                )
                        , "normalcell")
                );                

                // ********************        
                // Registered IP addresses
                // ********************  
                $output -> add(
                        $table -> add_basic_row($lang['search_ip_results_ip_reg_title'], "strip2",  "", "left", "100%", 2)
                );
                                      
                // Grab users with this IP address
                $db -> query("SELECT username, id, ip_address FROM ".$db -> table_prefix."users WHERE ip_address LIKE '%".$search_info['by_ip']."%' ORDER BY username");

                // Chuck out empty or some
                if($db -> num_rows() < 1)
                       $output -> add(
                                $table -> add_basic_row("<b>".$lang['search_ip_results_ip_reg_none']."</b>", "normalcell",  "padding : 10px", "center", "100%", 2)
                        );        
                else
                {

                        while($u_array = $db-> fetch_array())
                                $output -> add(
                                        $table -> add_row(
                                                array(
                                                        array
                                                                ("<b><a href=\"".ROOT."admin/index.php?m=users&m2=edit&id=".$u_array['id']."\">".$u_array['username']."</a></b> (".$u_array['ip_address'].")"
                                                        , "50%"),
                                                        array("<a href=\"".ROOT."admin/index.php?m=users&m2=doipsearch&user_id=".$u_array['id']."\">".$lang['search_ip_results_other_ip']."</a>", "50%")
                                                )
                                        , "normalcell")
                                );                
                
                }                                


                // ********************  
                // Found post IP addresses
                // ********************  
                $output -> add(
                        $table -> add_basic_row($lang['search_ip_results_ip_posts_title'], "strip2",  "", "left", "100%", 2)
                );

                // select username, user_id, ip_address from post where ip_address like '%$ip%' and ip_address <> ''

                // ---

                // ******************
                // End table.
                // ******************
                $output -> add($table -> end_table());

                page_search_ip($search_info);
                        
        }
        // -----------------
        // Empty input :(
        // -----------------
        else
        {
                $output -> add($template_admin -> critical_error($lang['search_ip_no_input']));
                page_search_ip();
                return;
        }       
         
}
?>
