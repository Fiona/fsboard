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
 * Admin area - User management
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


// Is this a dagger I see before me? NO!
load_language_group("admin_users");


// Functions please Jeeves..
include ROOT."admin/common/funcs/users.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_users'], l("admin/users/"));


$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{
	case "add":
		page_add_user();
		break;

	case "search":
		page_search_users();
		break;

	case "edit":
		page_edit_user($page_matches['user_id']);
		break;

	case "username":
		page_edit_user_username($page_matches['user_id']);
		break;

	case "password":
		page_edit_user_password($page_matches['user_id']);
		break;

	case "delete":
		page_delete_user($page_matches['user_id']);
		break;

	default:
		page_search_users();

}

/*
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
*/


/**
 * Page to create a new user
 */
function page_add_user()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_user_title'];
	$output -> add_breadcrumb($lang['breadcrumb_users_add'], l("admin/users/add/"));

	// Get a list of user groups for the form
	include ROOT."admin/common/funcs/usergroups.funcs.php";
	$groups = usergroups_get_groups();

	$dropdown_options = array();

	foreach($groups as $group_id => $group_info)
		$dropdown_options[$group_id] = $group_info['name'];

	// Add user form
	$form = new form(
		array(
			"meta" => array(
				"name" => "user_add",
				"title" => $lang['add_user_title'],
				"extra_title_contents_left" => $output -> help_button("", True).$template_admin -> form_header_icon("users"),
				"validation_func" => "form_users_add_validate",
				"complete_func" => "form_users_add_complete"
				),
			"#username" => array(
				"name" => $lang['add_user_form_username'],
				"type" => "text",
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("username", False)
				),
			"#password" => array(
				"name" => $lang['add_user_form_password'],
				"type" => "text",
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("password", False)
				),
			"#email" => array(
				"name" => $lang['add_user_form_email'],
				"type" => "text",
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("email", False)
				),
			"#user_group" => array(
				"name" => $lang['add_user_form_usergroup'],
				"type" => "dropdown",
				"options" => $dropdown_options,
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("usergroup", False)
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $lang['add_user_submit']
				)
			)
		);

	$output -> add($form -> render());

}



/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for creating a new user
 *
 * @param object $form
 */
function form_users_add_validate($form)
{
   
	global $db, $lang, $page_matches;

	$form -> form_state['#email']['value'] = users_sanitise_email_address($form -> form_state['#email']['value']);

	$error = users_add_verify_username($form -> form_state['#username']['value'], True);
	if($error !== True)
		$form -> set_error("username", $error);

	$error = users_verify_password($form -> form_state['#password']['value'], True);
	if($error !== True)
		$form -> set_error("password", $error);

	$error = users_verify_email($form -> form_state['#email']['value'], True);
	if($error !== True)
		$form -> set_error("email", $error);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for creating a new user
 *
 * @param object $form
 */
function form_users_add_complete($form)
{

	global $output, $lang;

	$new_user_id = users_add_user(
		$form -> form_state['#username']['value'],
		$form -> form_state['#password']['value'],
		$form -> form_state['#email']['value'],
		$form -> form_state['#user_group']['value']
		);

	if($new_user_id === False)
		return False;

	log_admin_action("users", "add", "Added new user: ".$form -> form_state['#username']['value']);

	$output -> redirect(l("admin/users/edit/".$new_user_id."/"), $lang['user_added_sucessfully']);

}



//***********************************************
// Add a user now kthnxbai
//***********************************************
/*
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
        
        //$db -> basic_insert("users", $user_info);
        

        // Execute the query and check if it died.
        if(!$db -> query($query_string))
        {
               $output -> add($template_admin -> critical_error($lang['error_user_add']));        
               page_add_user($user_info);
               return;
        }

        // Get the ID number of the account just inserted
        $user_id = $db -> insert_id();

        // Add user to admin settings table
        $db -> basic_insert("users_admin_settings", array("user_id" => $user_id));

        // Redirect the user
        $output -> redirect(ROOT."admin/index.php?m=users&amp;m2=edit&amp;id=".$user_id, $lang['user_added_sucessfully']);

}
*/



/**
 * Page for searching for users
 */
function page_search_users()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['search_user_title'];
	$output -> add_breadcrumb($lang['breadcrumb_users_search'], l("admin/users/search/"));

	// Get a list of user groups for the form
	include ROOT."admin/common/funcs/usergroups.funcs.php";
	$groups = usergroups_get_groups();

	$dropdown_options = array();

	foreach($groups as $group_id => $group_info)
		$dropdown_options[(string)$group_id] = $group_info['name'];


	// Begin defining search form
	$form = new form(
		array(
			"meta" => array(
				"name" => "user_search",
				"title" => $lang['search_user_title'],
				"description" => $lang['search_user_message'],
				"extra_title_contents_left" => $output -> help_button("", True).$template_admin -> form_header_icon("users"),
				"validation_func" => "form_users_search_validate",
				"complete_func" => "form_users_search_complete"
				)
			)
		);

	// Yeah this is terrible.
	// The user name search wanted an extra dropdown sitting in there, what 
	// i'm gonna do is just put put this html in and hopefully it will work
	// alright - I'm just gonna go for the $_POST data later for this one item.
	global $template_global_forms;

	$user_search_critera = $template_global_forms -> form_field_dropdown(
		"#username_search",
		array(
			"options" => array(
				0 => $lang['username_search_contains'],
				1 => $lang['username_search_exactly'],
				2 => $lang['username_search_starts'],
				3 => $lang['username_search_end']
				),
			"size" => 0,
			"value" => (isset($_POST['username_search']) ? $_POST['username_search'] : 0)
			),
		$form -> form_state
		);

	// Finish the form definition
	$form -> form_state = $form -> form_state + array(
		"#username" => array(
			"name" => $lang['search_user_username'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("email", False).$user_search_critera,
			),
		"#email" => array(
			"name" => $lang['search_user_email'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("email", False)
			),
		"#usergroup" => array(
			"name" => $lang['search_user_usergroup'],
			"type" => "dropdown",
			"blank_option" => True,
			"options" => array("-1" => $lang['search_user_usergroup_all']) + $dropdown_options,
			"extra_field_contents_left" => $output -> help_button("usergroup", False)
			),
		"#usergroup_secondary" => array(
			"name" => $lang['search_user_usergroup_secondary'],
			"type" => "checkboxes",
			"options" => $dropdown_options,
			"extra_field_contents_left" => $output -> help_button("usergroup_secondary", False)
			),
		"#title" => array(
			"name" => $lang['search_user_user_title'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("title", False)
			),
		"#signature" => array(
			"name" => $lang['search_user_signature'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("signature", False)
			),
		"#homepage" => array(
			"name" => $lang['search_user_homepage'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("homepage", False)
			),

		"search_subtitle_posts" => array(
			"title" => $lang['search_subtitle_posts'],
			"type" => "message"
			),
		"#posts_g" => array(
			"name" => $lang['search_user_posts_g'],
			"type" => "int",
			"extra_field_contents_left" => $output -> help_button("posts_g", False)
			),
		"#posts_l" => array(
			"name" => $lang['search_user_posts_l'],
			"type" => "int",
			"extra_field_contents_left" => $output -> help_button("posts_l", False)
			),

		"search_subtitle_times" => array(
			"title" => $lang['search_subtitle_times'],
			"type" => "message"
			),
		"#register_b" => array(
			"name" => $lang['search_user_register_b'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("register_b", False)
			),
		"#register_a" => array(
			"name" => $lang['search_user_register_a'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("register_a", False)
			),
		"#last_active_b" => array(
			"name" => $lang['search_user_last_active_b'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_active_b", False)
			),
		"#last_active_a" => array(
			"name" => $lang['search_user_last_active_a'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_active_a", False)
			),
		"#last_post_a" => array(
			"name" => $lang['search_user_last_post_a'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_post_a", False)
			),
		"#last_post_b" => array(
			"name" => $lang['search_user_last_post_b'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_post_b", False)
			)
		);

	// Custom profile fields
	users_add_custom_profile_form_fields($form, False);

	$form -> form_state["#submit"] = array(
			"type" => "submit",
			"value" => $lang['search_users_submit']
		);

	$output -> add($form -> render());

}


//***********************************************
// Search for a user form
//***********************************************
/*
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
*/


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




/**
 * Page to edit an existing user
 */
function page_edit_user($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

        
	// Sort out the birthday vaules
	$user_info['birthday'] = array(
		"day" => $user_info['birthday_day'],
		"month" => $user_info['birthday_month'],
		"year" => $user_info['birthday_year']
		);

	// Expand secondary usergroups
	$user_info['user_groups_secondary_expanded'] = array();
	foreach(explode(",", $user_info['secondary_user_group']) as $val)
		$user_info['user_groups_secondary_expanded'][$val] = 1;         

	// Build the timezone dropdown
	$time_offset_dropdown = array(
		-12 => $lang['timezone_gmt_minus_12'],
		-11 => $lang['timezone_gmt_minus_11'],
		-10 => $lang['timezone_gmt_minus_10'],
		-9 => $lang['timezone_gmt_minus_9'],
		-8 => $lang['timezone_gmt_minus_8'],
		-7 => $lang['timezone_gmt_minus_7'],
		-6 => $lang['timezone_gmt_minus_6'],
		-5 => $lang['timezone_gmt_minus_5'],
		-4 => $lang['timezone_gmt_minus_4'],
		-3 => $lang['timezone_gmt_minus_3'],
		-2 => $lang['timezone_gmt_minus_2'],
		-1 => $lang['timezone_gmt_minus_1'],
		0 => $lang['timezone_gmt'],
		1 => $lang['timezone_gmt_plus_1'],
		2 => $lang['timezone_gmt_plus_2'],
		3 => $lang['timezone_gmt_plus_3'],
		4 => $lang['timezone_gmt_plus_4'],
		5 => $lang['timezone_gmt_plus_5'],
		6 => $lang['timezone_gmt_plus_6'],
		7 => $lang['timezone_gmt_plus_7'],
		8 => $lang['timezone_gmt_plus_8'],
		9 => $lang['timezone_gmt_plus_9'],
 		10 => $lang['timezone_gmt_plus_10'],
		11 => $lang['timezone_gmt_plus_11'],
		12 => $lang['timezone_gmt_plus_12']
        );

	// Get data for user groups fields
	include ROOT."admin/common/funcs/usergroups.funcs.php";
	$groups = usergroups_get_groups();

	$user_groups_options = array();

	foreach($groups as $group_id => $group_info)
		$user_groups_options[$group_id] = $group_info['name'];


	// Get data for language selection
	include ROOT."admin/common/funcs/languages.funcs.php";
	$langs = languages_get_languages();

	$languages_options = array(-1 => $lang['edit_user_board_default']);

	foreach($langs as $lang_id => $lang_info)
		$languages_options[$lang_id] = $lang_info['name'];


	// Get data for theme selection
	include ROOT."admin/common/funcs/themes.funcs.php";
	$themes = themes_get_themes(False);

	$themes_options = array(-1 => $lang['edit_user_board_default']);

	foreach($themes as $theme_id => $theme_info)
		$themes_options[$theme_id] = $theme_info['name'];


	// Set up the page
	$output -> page_title = $output -> replace_number_tags($lang['edit_user_title'], array($user_info['username']));
	$output -> add_breadcrumb($lang['breadcrumb_users_edit'], l("admin/users/edit/".$user_id."/"));

	$form = new form(
		array(
			"meta" => array(
				"name" => "edit_user",
				"title" => $output -> page_title,
				"validation_func" => "form_users_edit_user_validate",
				"complete_func" => "form_users_edit_user_complete",
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") => $lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") => $lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/edit/".$user_id."/")
					),
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"data_user_groups" => $groups,
				"data_languages" => $langs,
				"data_themes" => $themes,
				"data_username" => $user_info['username']
				),
			// ----------------
			// Profile info
			// ----------------
			"profile_info_title" => array(
				"title" => $lang['edit_user_profile_info_title'],
				"type" => "message"
				),
			"#email" => array(
				"name" => $lang['edit_user_email'],
				"type" => "text",
				"value" => $user_info['email'],
				"required" => True,
				),
			"#user_group" => array(
				"name" => $lang['edit_user_usergroup'],
				"type" => "dropdown",
				"value" => $user_info['user_group'],
				"options" => $user_groups_options,
				"required" => True,
				),
			"#user_group_secondary" => array(
				"name" => $lang['edit_user_usergroup_secondary'],
				"type" => "checkboxes",
				"value" => $user_info['user_groups_secondary_expanded'],
				"options" => $user_groups_options
				),
			"#title" => array(
				"name" => $lang['edit_user_usertitle'],
				"type" => "text",
				"value" => $user_info['title']
				),
			"#real_name" => array(
				"name" => $lang['edit_user_real_name'],
				"type" => "text",
				"value" => $user_info['real_name']
				),
			"#homepage" => array(
				"name" => $lang['edit_user_homepage'],
				"type" => "text",
				"value" => $user_info['homepage']
				),
			"#yahoo_messenger" => array(
				"name" => $lang['edit_user_yahoo_messenger'],
				"type" => "text",
				"value" => $user_info['yahoo_messenger']
				),
			"#aol_messenger" => array(
				"name" => $lang['edit_user_aol_messenger'],
				"type" => "text",
				"value" => $user_info['aol_messenger']
				),
			"#msn_messenger" => array(
				"name" => $lang['edit_user_msn_messenger'],
				"type" => "text",
				"value" => $user_info['msn_messenger']
				),
			"#icq_messenger" => array(
				"name" => $lang['edit_user_icq_messenger'],
				"type" => "text",
				"value" => $user_info['icq_messenger']
				),
			"#gtalk_messenger" => array(
				"name" => $lang['edit_user_gtalk_messenger'],
				"type" => "text",
				"value" => $user_info['gtalk_messenger']
				),
			"#birthday" => array(
				"name" => $lang['edit_user_birthday'],
				"type" => "date",
				"value" => $user_info['birthday']
				),
			"#signature" => array(
				"name" => $lang['edit_user_signature'],
				"type" => "textarea",
				"value" => _htmlentities($user_info['signature'])
				),
			"#posts" => array(
				"name" => $lang['edit_user_posts'],
				"type" => "int",
				"value" => $user_info['posts']
				),

			// ----------------
			// Display settings
			// ----------------
			"display_title" => array(
				"title" => $lang['edit_user_display_title'],
				"type" => "message"
				),
			"#language" => array(
				"name" => $lang['edit_user_language'],
				"type" => "dropdown",
				"value" => $user_info['language'],
				"options" => $languages_options
				),
			"#theme" => array(
				"name" => $lang['edit_user_theme'],
				"type" => "dropdown",
				"value" => $user_info['theme'],
				"options" => $themes_options
				),

			// ----------------
			// Board settings
			// ----------------
			"board_settings_title" => array(
				"title" => $lang['edit_user_board_settings_title'],
				"type" => "message"
				),
			"#hide_email" => array(
				"name" => $lang['edit_user_hide_email'],
				"type" => "yesno",
				"value" => $user_info['hide_email']
				),
			"#view_sigs" => array(
				"name" => $lang['edit_user_view_sigs'],
				"type" => "yesno",
				"value" => $user_info['view_sigs']
				),
			"#view_avatars" => array(
				"name" => $lang['edit_user_view_avatars'],
				"type" => "yesno",
				"value" => $user_info['view_avatars']
				),
			"#view_images" => array(
				"name" => $lang['edit_user_view_images'],
				"type" => "yesno",
				"value" => $user_info['view_images']
				),
			"#email_new_pm" => array(
				"name" => $lang['edit_user_email_new_pm'],
				"type" => "yesno",
				"value" => $user_info['email_new_pm']
				),
			"#email_from_admin" => array(
				"name" => $lang['edit_user_email_from_admin'],
				"type" => "yesno",
				"value" => $user_info['email_from_admin']
				),

			// ----------------
			// Time settings
			// ----------------
			"time_title" => array(
				"title" => $lang['edit_user_time_title'],
				"type" => "message"
				),
			"#time_offset" => array(
				"name" => $lang['edit_user_time_offset'],
				"type" => "dropdown",
				"value" => $user_info['time_offset'],
				"options" => $time_offset_dropdown
				),
			"#dst_on" => array(
				"name" => $lang['edit_user_dst_on'],
				"type" => "yesno",
				"value" => $user_info['dst_on']
				),
			"#registered" => array(
				"name" => $lang['edit_user_registered'],
				"type" => "date",
				"value" => $user_info['registered']
				),
			"#last_active" => array(
				"name" => $lang['edit_user_last_active'],
				"type" => "date",
				"value" => $user_info['last_active'],
				"time" => True
				),
			"#last_post_time" => array(
				"name" => $lang['edit_user_last_post_date'],
				"type" => "date",
				"value" => $user_info['last_post_time'],
				"time" => True
				),

			)

		);

	
	// Custom profile fields
	users_add_custom_profile_form_fields($form, False);

	// Submit button
	$form -> form_state['#submit'] = array(
		"type" => "submit",
		"value" => $lang['edit_user_submit']
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing an existing user
 *
 * @param object $form
 */
function form_users_edit_user_validate($form)
{

	global $db, $page_matches, $user, $lang;
	
	// Cannot edit your own primary user group
	if($user -> user_id == $page_matches['user_id'] && $user -> info['user_group'] != $form -> form_state['#user_group']['value'])
		$form -> set_error("user_group", $lang['cant_edit_own_group']);        

	// Check email is alright
	$form -> form_state['#email']['value'] = users_sanitise_email_address($form -> form_state['#email']['value']);

	$error = users_verify_email($form -> form_state['#email']['value'], True);
	if($error !== True)
		$form -> set_error("email", $error);

	// Check theme
	if($form -> form_state['#theme']['value'] != -1 && !array_key_exists($form -> form_state['#theme']['value'], $form -> form_state['meta']['data_themes']))
		$form -> set_error("theme", $lang['edit_user_invalid_theme']);

	// Check language
	if($form -> form_state['#language']['value'] != -1 && !array_key_exists($form -> form_state['#language']['value'], $form -> form_state['meta']['data_languages']))
		$form -> set_error("language", $lang['edit_user_invalid_language']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing an existing user
 *
 * @param object $form
 */
function form_users_edit_user_complete($form)
{

	global $db, $page_matches, $user, $lang, $output;
	
	// First grab all the normal user info
	$user_info = array(
		"email" 				=> $form -> form_state['#email']['value'],
		"user_group" 			=> $form -> form_state['#user_group']['value'],
		"secondary_user_group"	=> $form -> form_state['#user_group_secondary']['value'],
		"title" 				=> $form -> form_state['#title']['value'],
		"real_name" 			=> $form -> form_state['#real_name']['value'],
		"homepage" 				=> $form -> form_state['#homepage']['value'],
		"yahoo_messenger"	 	=> $form -> form_state['#yahoo_messenger']['value'],
		"msn_messenger" 		=> $form -> form_state['#msn_messenger']['value'],
		"icq_messenger" 		=> $form -> form_state['#icq_messenger']['value'],
		"gtalk_messenger" 		=> $form -> form_state['#gtalk_messenger']['value'],
		"birthday_day" 			=> $form -> form_state['#birthday']['value']['day'],
		"birthday_month"	 	=> $form -> form_state['#birthday']['value']['month'],
		"birthday_year" 		=> $form -> form_state['#birthday']['value']['year'],
		"signature" 			=> $form -> form_state['#signature']['value'],
		"posts" 				=> $form -> form_state['#posts']['value'],
		"language" 				=> $form -> form_state['#language']['value'],
		"theme" 				=> $form -> form_state['#theme']['value'],
		"hide_email"			=> $form -> form_state['#hide_email']['value'],
		"view_sigs"				=> $form -> form_state['#view_sigs']['value'],
		"view_avatars" 			=> $form -> form_state['#view_avatars']['value'],
		"view_images"	 		=> $form -> form_state['#view_images']['value'],
		"email_new_pm"			=> $form -> form_state['#email_new_pm']['value'],
		"email_from_admin"  	=> $form -> form_state['#email_from_admin']['value'],
		"time_offset"	 		=> $form -> form_state['#time_offset']['value'],
		"dst_on" 				=> $form -> form_state['#dst_on']['value'],
		"registered" 			=> $form -> get_date_timestamp('#registered'),
		"last_active"	 		=> $form -> get_date_timestamp('#last_active'),
		"last_post_time"	 	=> $form -> get_date_timestamp('#last_post_time')
		);

	// Get custom field data
	if(is_array($form -> form_state['meta']['data_custom_fields']) && count($form -> form_state['meta']['data_custom_fields']) > 0)
		foreach($form -> form_state['meta']['data_custom_fields'] as $key => $junk)
			$user_info['field_'.$key] = $form -> form_state['#field_'.$key]['value'];

	// Update the user info
	$update_result = users_update_user($page_matches['user_id'], $user_info, $form -> form_state['meta']['data_custom_fields']);

	if($update_result === False)
		return False;

	// Log the action
	log_admin_action("users", "edit", "Edited user: ".$form -> form_state['meta']['data_username']);

	// Finished
	$output -> redirect(l("admin/users/edit/".$page_matches['user_id']."/"), $lang['user_updated']);

	// Secondary user groups
/*
	$secondary_groups = $form -> form_state['#user_group_secondary']['value'];
	if(is_array($secondary_groups) && count($secondary_groups))
		$user_info['secondary_user_group'] = implode(",", $secondary_groups);
	else
		$user_info['secondary_user_group'] = "";
*/

/*
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
*/
}


/*
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
*/



//***********************************************
// Guess I'm editing
//***********************************************
/*
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

*/


/**
 * Page to edit an existing users username
 */
function page_edit_user_username($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

	// Set up the page
	$output -> page_title = $output -> replace_number_tags($lang['edit_username_title'], array($user_info['username']));
	$output -> add_breadcrumb($lang['breadcrumb_users_edit'], l("admin/users/edit/".$user_id."/"));
	$output -> add_breadcrumb($lang['breadcrumb_users_edit_name'], l("admin/users/username/".$user_id."/"));

	$form = new form(
		array(
			"meta" => array(
				"name" => "edit_username",
				"title" => $output -> page_title,
				"validation_func" => "form_users_edit_username_validate",
				"complete_func" => "form_users_edit_username_complete",
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") => $lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") => $lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/username/".$user_id."/")
					),
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"data_current_username" => $user_info['username'],
				"data_user_email" => $user_info['email']
				),

			"#username" => array(
				"name" => $lang['edit_username_enter_new'],
				"description" => $output -> replace_number_tags($lang['edit_username_current'], $user_info['username']),
				"type" => "text",
				"value" => $user_info['username'],
				"required" => True,
				),
			"#send_email" => array(
				"name" => $lang['edit_username_send_email'],
				"type" => "yesno",
				"value" => 0
				),
			"#email_contents" => array(
				"name" => $lang['edit_username_email_contents'],
				"description" => $lang['email_changed_username_description'],
				"type" => "textarea",
				"value" => $lang['email_changed_username'],
				"size" => 12
				),

			'#submit' => array(
				"type" => "submit",
				"value" => $lang['edit_username_submit']
				)
			)
		);

	$output -> add($form -> render());

}

//***********************************************
// Edit a users username
//***********************************************
/*
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
*/


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing a user's username
 *
 * @param object $form
 */
function form_users_edit_username_validate($form)
{

	$error = users_edit_username_verify_username(
		$form -> form_state['meta']['data_current_username'],
		$form -> form_state['#username']['value']
		);

	if($error !== True)
		$form -> set_error("username", $error);

}

/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a user's username
 *
 * @param object $form
 */
function form_users_edit_username_complete($form)
{

	global $page_matches, $cache, $lang, $output;

	$update_result = users_update_username(
		$page_matches['user_id'],
		$form -> form_state['meta']['data_current_username'],
		$form -> form_state['#username']['value']
		);

	if($update_result !== True)
		return False;


	// If we're sending an e-mail alerting the affected user
	if($form -> form_state['#send_email']['value'])
	{
                
		// We need to replace certain tokens in the email message.
		$message = str_replace(
			array(
				'<old_name>',
				'<new_name>',
				'<board_name>',
				'<board_url>'
				),
			array(
				$form -> form_state['meta']['data_current_username'],
				$form -> form_state['#username']['value'],
				$cache -> cache['config']['board_name'],
				$cache -> cache['config']['board_url']
				),
			 $form -> form_state['#email_contents']['value']
			);

		// Send the e-mail
		$mail = new email;
		$mail -> send_mail($form -> form_state['meta']['data_user_email'], $lang['email_changed_username_subject'], $message);
        
	}

	// Log it!
	log_admin_action(
		"users",
		"username", 
		"Changed member '".$form -> form_state['meta']['data_current_username']."' name to '".$form -> form_state['#username']['value']."'"
		);

	$output -> redirect(l("admin/users/username/".$page_matches['user_id']."/"), $lang['username_changed_sucessfully']);

}


//***********************************************
// Submit an edit for a users username
//***********************************************
/*
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
*/



/**
 * Page to edit an existing users password
 */
function page_edit_user_password($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

	// Set up the page
	$output -> page_title = $output -> replace_number_tags($lang['edit_password_title'], array($user_info['username']));
	$output -> add_breadcrumb($lang['breadcrumb_users_edit'], l("admin/users/edit/".$user_id."/"));
	$output -> add_breadcrumb($lang['breadcrumb_users_edit_password'], l("admin/users/password/".$user_id."/"));

	$form = new form(
		array(
			"meta" => array(
				"name" => "edit_password",
				"title" => $output -> page_title,
				"validation_func" => "form_users_edit_password_validate",
				"complete_func" => "form_users_edit_password_complete",
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") => $lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") => $lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/password/".$user_id."/")
					),
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"data_username" => $user_info['username'],
				"data_user_email" => $user_info['email']
				),

			"#password" => array(
				"name" => $lang['edit_password_enter_new'],
				"type" => "password",
				"required" => True
				),
			"#password2" => array(
				"name" => $lang['edit_password_enter_new_again'],
				"type" => "password",
				"required" => True
				),
			"#send_email" => array(
				"name" => $lang['edit_password_send_email'],
				"type" => "yesno",
				"value" => 0
				),
			"#email_contents" => array(
				"name" => $lang['edit_password_email_contents'],
				"description" => $lang['edit_password_email_contents_description'],
				"type" => "textarea",
				"value" => $lang['email_changed_password'],
				"size" => 12
				),

			'#submit' => array(
				"type" => "submit",
				"value" => $lang['edit_password_submit']
				)
			)
		);

	$output -> add($form -> render());

}


//***********************************************
// Edit a users password
//***********************************************
/*
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
*/

/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing a user's password
 *
 * @param object $form
 */
function form_users_edit_password_validate($form)
{

	global $lang;

	if($form -> form_state['#password']['value'] != $form -> form_state['#password2']['value'])
		$form -> set_error("password", $lang['change_password_error_no_match']);

	$error = users_verify_password($form -> form_state['#password']['value']);

	if($error !== True)
		$form -> set_error("password", $error);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a user's password
 *
 * @param object $form
 */
function form_users_edit_password_complete($form)
{

	global $page_matches, $cache, $lang, $output;

	$update_result = users_update_password(
		$page_matches['user_id'],
		$form -> form_state['#password']['value']
		);

	if($update_result !== True)
		return False;


	// If we're sending an e-mail alerting the affected user
	if($form -> form_state['#send_email']['value'])
	{
                
		// We need to replace certain tokens in the email message.
		$message = str_replace(
			array(
				'<username>',
				'<new_password>',
				'<board_name>',
				'<board_url>'
				),
			array(
				$form -> form_state['meta']['data_username'],
				$form -> form_state['#password']['value'],
				$cache -> cache['config']['board_name'],
				$cache -> cache['config']['board_url']
				),
			 $form -> form_state['#email_contents']['value']
			);

		// Send the e-mail
		$mail = new email;
		$mail -> send_mail($form -> form_state['meta']['data_user_email'], $lang['email_changed_password_subject'], $message);
        
	}

	// Log it!
	log_admin_action(
		"users",
		"password", 
		"Changed password for '".$form -> form_state['meta']['data_username']."'"
		);

	$output -> redirect(l("admin/users/password/".$page_matches['user_id']."/"), $lang['password_changed_sucessfully']);

}



//***********************************************
// Submit an edit for a users password
//***********************************************
/*
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
*/



/**
 * Page to delete an existing user
 */
function page_delete_user($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

	// Set up the page
	$output -> page_title = $output -> replace_number_tags($lang['delete_user_title'], $user_info['username']);
	$output -> add_breadcrumb($lang['breadcrumb_users_edit'], l("admin/users/edit/".$user_id."/"));
	$output -> add_breadcrumb($lang['breadcrumb_users_delete'], l("admin/users/delete/".$user_id."/"));

	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title ,
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") => $lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") => $lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/delete/".$user_id."/")
					),
				"description" => $output -> replace_number_tags($lang['delete_user_message'], $user_info['username']),
				"callback" => "users_delete_user_complete",
				"arguments" => array($user_id, $user_info['username']),
				"confirm_redirect" => l("admin/users/search/"),
				"cancel_redirect" => l("admin/users/edit/".$user_id."/")
				)
			)
		);

}

//***********************************************
// Baleetion!
//***********************************************
/*
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
*/

/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a user
 *
 * @param int $user_id The ID of the user being deleted.
 */
function users_delete_user_complete($user_id, $username)
{

	global $user, $output, $lang;

	// Check to see if we're deleting ourselves. (Can't do this.)
	if($user_id == $user -> user_id)
	{
		$output -> set_error_message($lang['cannot_delete_self']);
		return False;
	}

	// Delete the user and check the responce
	$return = users_delete_user($user_id);

	if($return === True)
	{

        // Log it
        log_admin_action("users", "delete", "Deleted user ".$username);

		return True;

	}
	else
		return False;

}


//***********************************************
// Finish baleetion!
//***********************************************
/*
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
*/


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
