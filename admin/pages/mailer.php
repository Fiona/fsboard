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
 * Mass-mailer
 * 
 * This will let admins start mailing users.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Talk to me message board
//***********************************************
load_language_group("admin_mailer");


//***********************************************
// Functions fur der page ja
//***********************************************
include ROOT."admin/common/funcs/mailer.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_mailer'], "index.php?m=mailer");

$_GET['m2'] = ($_GET['m2']) ? $_GET['m2'] : "add";
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_mail();
                break;
              
        case "dopreview":
                page_preview_mail();
                break;

        case "doadd":
                do_add_mail();
                break;

        case "dofinish":
                do_finish_mail();
                break;

        default:
                page_add_mail();

}



/**
 * Page to let us set tho correct criteria
 * 
 * @param array $search_info Array of already input values
 */
function page_add_mail($search_info = "")
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Keep the groups in arrays
        // **************************
        $group_dropdown_values[] .= -1;
        $group_dropdown_text[] .= $lang['mail_search_usergroup_all'];

        $db -> basic_select("user_groups", "id,name", "", "id", "", "asc");

        while($g_array = $db -> fetch_array())
        {
                $group_dropdown_values[] .= $g_array['id'];
                $group_dropdown_text[] .= $g_array['name'];
                
                $group_secondary_checkbox[$g_array['id']] = $g_array['name'];
        }

        $search_info['bulk_num'] = ($search_info['bulk_num']) ? $search_info['bulk_num'] : "50"; 
        $search_info['mail_from'] = ($search_info['mail_from']) ? $search_info['mail_from'] : $cache -> cache['config']['mail_from_address'];
        $search_info['mail_contents'] = ($search_info['mail_contents']) ? $search_info['mail_contents'] : $lang['mail_search_default_contents']; 
         
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['mail_search_title'];

        $time_description = "<br /><font class=\"small_text\">".$lang['mail_search_time_example']."</font>";
        
        // Create classes
        $table = new table_generate;
        $var_table = new table_generate;
        
        $form = new form_generate;


        // **************************
        // Generate the form
        // **************************
        $output -> add(
                $form -> start_form("searchmail", ROOT."admin/index.php?m=mailer&amp;m2=dopreview", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_top_table_header($lang['mail_search_title'], 2, "mailer").
                $table -> simple_input_row_text($form, $lang['mail_add_from'], "mail_from", $search_info['mail_from'], "mail_from").     
                $table -> simple_input_row_text($form, $lang['mail_add_subject'], "mail_subject", $search_info['mail_subject'], "mail_subject").     
                $table -> simple_input_row_textbox($form, $lang['mail_add_contents'], "mail_contents", $search_info['mail_contents'], 10, "mail_contents").     
                
                $table -> add_basic_row(
                        $var_table -> start_table("", "border-collapse : collapse;", "left", "100%").
                        $table -> add_row(
                                array(
                                        array("<span style=\"font-size : 9px;\"><b>{board_name}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_board_name']."</span>",
                                        array("<span style=\"font-size : 9px;\"><b>{user_num}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_num']."</span>"
                                )
                        ).       
                        $table -> add_row(
                                array(
                                        array("<span style=\"font-size : 9px;\"><b>{board_url}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_board_url']."</span>",
                                        array("<span style=\"font-size : 9px;\"><b>{post_num}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_post_num']."</span>"
                                )
                        ).       
                        $table -> add_row(
                                array(
                                        array("<span style=\"font-size : 9px;\"><b>{user_id}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_id']."</span>",
                                        array("<span style=\"font-size : 9px;\"><b>{user_name}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_name']."</span>"
                                )
                        ).       
                        $table -> add_row(
                                array(
                                        array("<span style=\"font-size : 9px;\"><b>{user_joined}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_joined']."</span>",
                                        array("<span style=\"font-size : 9px;\"><b>{user_posts}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_posts']."</span>"
                                )
                        ).       
                        $table -> add_row(
                                array(
                                        array("<span style=\"font-size : 9px;\"><b>{user_email}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_email']."</span>",
                                        array("<span style=\"font-size : 9px;\"><b>{user_usergroup}</b></span>", "100px"),
                                        "<span style=\"font-size : 9px;\">".$lang['mail_vars_user_usergroup']."</span>"
                                )
                        ).       
                        $var_table -> end_table()
                , "normalcell",  "", "left", "100%", "2").                
                $table -> simple_input_row_yesno($form, $lang['mail_search_ignore_admin'], "ignore_admin", $search_info['ignore_admin'], "ignore_admin").     
                $table -> simple_input_row_int($form, $lang['mail_search_bulk_num'], "bulk_num", $search_info['bulk_num'], "bulk_num")     
        );

        if(defined("DEVELOPER"))
                $output -> add(
                        $table -> simple_input_row_yesno($form, $lang['mail_search_test'], "test", $search_info['test'], "test")
                );
                
        $output -> add(
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['mail_search_subtitle_search_criteria'], "strip2",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['mail_search_message'], "normalcell",  "padding : 5px", "left", "100%", "2").

                $table -> add_row(
                        array(
                                array($lang['mail_search_username'], "50%"),
                                array(
                                        $output -> return_help_button("username", false).
                                        $form -> input_dropdown("username_search", $search_info['username_search'],
                                                array(0, 1, 2, 3),
                                                array($lang['mail_search_username_contains'], $lang['mail_search_username_exactly'], $lang['mail_search_username_starts'], $lang['mail_search_username_end'])
                                                , "inputtext", "auto")." ".
                                        $form -> input_text("username", $search_info['username'], "inputtext", "60%")
                                , "50%")
                        )
                , "normalcell").       
                $table -> simple_input_row_text($form, $lang['mail_search_email'], "email", $search_info['email'], "email").     
                $table -> simple_input_row_dropdown($form, $lang['mail_search_usergroup'], "usergroup", $search_info['usergroup'], $group_dropdown_values, $group_dropdown_text, "usergroup").     
                $table -> simple_input_row_checkbox_list($form, $lang['mail_search_usergroup_secondary'], "usergroup_secondary", $search_info['usergroup_secondary'], $group_secondary_checkbox, "usergroup_secondary").     
                $table -> simple_input_row_text($form, $lang['mail_search_user_title'], "title", $search_info['title'], "title").     
                $table -> simple_input_row_text($form, $lang['mail_search_signature'], "signature", $search_info['signature'], "signature").     
                $table -> simple_input_row_text($form, $lang['mail_search_homepage'], "homepage", $search_info['homepage'], "homepage").     
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['mail_search_subtitle_posts'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_int($form, $lang['mail_search_posts_g'], "posts_g", $search_info['posts_g'], "posts_g").     
                $table -> simple_input_row_int($form, $lang['mail_search_posts_l'], "posts_l", $search_info['posts_l'], "posts_l").     
                $table -> end_table().

                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['mail_search_subtitle_times'], "strip2",  "", "left", "100%", "2").
                $table -> simple_input_row_text($form, $lang['mail_search_register_b'].$time_description, "register_b", $search_info['register_b'], "register_b").     
                $table -> simple_input_row_text($form, $lang['mail_search_register_a'].$time_description, "register_a", $search_info['register_a'], "register_a").     
                $table -> simple_input_row_text($form, $lang['mail_search_last_active_b'].$time_description, "last_active_b", $search_info['last_active_b'], "last_active_b").     
                $table -> simple_input_row_text($form, $lang['mail_search_last_active_a'].$time_description, "last_active_a", $search_info['last_active_a'], "last_active_a").     
                $table -> simple_input_row_text($form, $lang['mail_search_last_post_b'].$time_description, "last_post_b", $search_info['last_post_b'], "last_post_b").     
                $table -> simple_input_row_text($form, $lang['mail_search_last_post_a'].$time_description, "last_post_a", $search_info['last_post_a'], "last_post_a").     
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
                        $table -> add_basic_row($lang['mail_search_subtitle_custom_fields'], "strip2",  "", "left", "100%", "2")
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
                $table -> add_submit_row($form, "submitsearch", $lang['mail_search_submit']).
                $table -> end_table().
                $form -> end_form()
        );           
             
}



/**
 * Uses the criteria to find the e-mails wanted
 */
function page_preview_mail()
{

        global $output, $lang, $db, $template_admin, $cache, $user;

        $search_info = array(
                "mail_from" => $_POST['mail_from'],
                "mail_subject" => $_POST['mail_subject'],
                "mail_contents" => $_POST['mail_contents'],
                "ignore_admin" => $_POST['ignore_admin'],
                "bulk_num" => $_POST['bulk_num'],
                "test" => $_POST['test'],
                
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

        $search_info['bulk_num'] = ("") ? 50 : $search_info['bulk_num'];
        

        // *****************************
        // Check we're submitting a search
        // *****************************
        if(!$_POST['submitsearch'])
        {
                $output -> add($template_admin -> normal_error($lang['invalid_search']));
                page_add_mail($search_info);
                return;
        }     


        // *****************************
        // Check the e-mail info has been put in
        // *****************************
        if(!$search_info['mail_from'] || !$search_info['mail_subject'] || !$search_info['mail_contents'])
        {
                $output -> add($template_admin -> normal_error($lang['no_mail_info_inputted']));
                page_add_mail($search_info);
                return;
        }     


        // *****************************
        // Let's build the query with a function
        // *****************************
        include ROOT."admin/common/funcs/users.funcs.php";
        
        $ex_query = array();

        // Ignore "no e-mails from admin"?
        if($search_info['ignore_admin'] == 0)
                $ex_query[] = "u.`email_from_admin` = '1'";

        $query_string = create_user_search_string($search_info, $ex_query);


        // *****************************
        // Do the query
        // *****************************
        $search_query = $db -> query("SELECT u.username, u.email, u.id, u.posts, u.user_group, u.registered FROM ".$db -> table_prefix."users u LEFT JOIN ".$db -> table_prefix."profile_fields_data p ON (p.member_id=u.id)".$query_string);

        if($db -> num_rows($search_query) < 1)
        {
                $output -> add($template_admin -> normal_error($lang['search_no_results']));
                page_add_mail($search_info);
                return;
        }


        // *****************************
        // Display e-mail preview
        // *****************************
        $preview_email_text = replace_email_variables($search_info['mail_contents'], $user -> info);


        // **************************
        // Generate the form
        // **************************
        $output -> add_breadcrumb($lang['breadcrumb_preview_mail'], "index.php?m=mailer&amp;m2=dopreview");        
        
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("previewmail", ROOT."admin/index.php?m=mailer&amp;m2=dofinish", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_top_table_header($lang['mail_preview_title'], 0, "mailer").
                $table -> add_basic_row(nl2br($preview_email_text), "normalcell", "text-align : left;").
                $table -> add_basic_row($lang['mail_preview_subtitle_recipients'], "strip2",  "", "left", "100%", "2")
        );

        // If more than 100 e-mails we just say how many
        if($db -> num_rows($search_query) > 100)
        {
                
                $output -> add(
                        $table -> add_basic_row(
                                $output -> replace_number_tags($lang['mail_preview_no_recipients'], array( $db -> num_rows($search_query) )) 
                        , "normalcell", "padding : 5px;")
                );
                                
        }
        // Less than 100 and we list them all 
        else
        {
                
                $a = 0;
                
                while($addresses = $db -> fetch_array($search_query))                
                        $output -> add(
                                $table -> add_basic_row(
                                        $output -> replace_number_tags($lang['mail_preview_recipient_entry'], array($addresses['email'], $addresses['username'], ++$a) ) 
                                , "normalcell", "text-align : left;")
                        );
                
        }


        // **************************
        // The last bit has hidden form elements for all the search
        // criteria and two submit buttons (finish and back)
        // **************************
        $output -> add(
                $table -> add_basic_row(
                        $form -> submit("send", $lang['mail_preview_send'])." ".$form -> submit("back", $lang['mail_preview_go_back']),
                        "strip3"
                ).
                $table -> end_table().
                
                $form -> hidden("mail_from", $search_info['mail_from']).
                $form -> hidden("mail_subject", $search_info['mail_subject']).
                $form -> hidden("mail_contents", $search_info['mail_contents']).
                $form -> hidden("ignore_admin", $search_info['ignore_admin']).
                $form -> hidden("bulk_num", $search_info['bulk_num']).
                $form -> hidden("test", $search_info['test']).

                $form -> hidden("username", $search_info['username']).
                $form -> hidden("username_search", $search_info['username_search']).
                $form -> hidden("email", $search_info['email']).
                $form -> hidden("usergroup", $search_info['usergroup']).

                $form -> hidden("usergroup_secondary", $search_info['usergroup_secondary']).
                $form -> hidden("title", $search_info['title']).
                $form -> hidden("signature", $search_info['signature']).
                $form -> hidden("homepage", $search_info['homepage']).

                $form -> hidden("posts_g", $search_info['posts_g']).
                $form -> hidden("posts_l", $search_info['posts_l']).
                $form -> hidden("register_b", $search_info['register_b']).
                $form -> hidden("register_a", $search_info['register_a']).

                $form -> hidden("last_active_b", $search_info['last_active_b']).
                $form -> hidden("last_active_a", $search_info['last_active_a']).
                $form -> hidden("last_post_b", $search_info['last_post_b']).
                $form -> hidden("last_post_a", $search_info['last_post_a']).

                $form -> end_form()
        );
                               
}


/**
 * After previewing the e-mails this one will send them,
 * or go back to the input if wanted.
 */
function do_finish_mail()
{

        global $output, $lang, $db, $template_admin, $cache, $user;

        $search_info = array(
                "mail_from" => $_POST['mail_from'],
                "mail_subject" => $_POST['mail_subject'],
                "mail_contents" => $_POST['mail_contents'],
                "ignore_admin" => $_POST['ignore_admin'],
                "bulk_num" => $_POST['bulk_num'],
                "test" => $_POST['test'],

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

        $search_info['bulk_num'] = ("") ? 50 : $search_info['bulk_num'];
            echo $_POST['usergroup_secondary'];

        // **************************
        // Going back to add?
        // **************************
        if($_POST['back'])
        {
                page_add_mail($search_info);
                return;
        }


        // *****************************
        // Check we're submitting a search
        // *****************************
        if(!$_POST['send'])
        {
                $output -> add($template_admin -> normal_error($lang['invalid_submit']));
                page_add_mail($search_info);
                return;
        }


        // *****************************
        // Let's build the query with a function
        // *****************************
        include ROOT."admin/common/funcs/users.funcs.php";

        $ex_query = array();

        // Ignore "no e-mails from admin"?
        if($search_info['ignore_admin'] == 0)
                $ex_query[] = "u.`email_from_admin` = '1'";

        $query_string = create_user_search_string($search_info, $ex_query);


        // *****************************
        // Do the query
        // *****************************
        $search_query = $db -> query("SELECT u.username, u.email, u.id, u.posts, u.user_group, u.registered FROM ".$db -> table_prefix."users u LEFT JOIN ".$db -> table_prefix."profile_fields_data p ON (p.member_id=u.id)".$query_string);

        $users_selected = $db -> num_rows($search_query);

        if($users_selected < 1)
        {
                $output -> add($template_admin -> normal_error($lang['search_no_results']));
                page_add_mail($search_info);
                return;
        }


        // *****************************
        // Mail set insert
        // *****************************
        $set_insert = array(
                "bulk_num" => $search_info['bulk_num'],
                "emails_left" => $users_selected,
                "emails_sent" => "0",
                "from_email" => $search_info['mail_from'],
                "test" => $search_info['test']
        );

        $db -> basic_insert("mass_mailer", $set_insert);
        
        $set_id = $db -> insert_id();
        

        // *****************************
        // Go through all the entries if we have any.
        // *****************************
        while($user_array = $db -> fetch_array($search_query))
        {

                $email_text = replace_email_variables($search_info['mail_contents'], $user_array);

                $email_insert = array(
                        "set_id" => $set_id,
                        "to_email" => $user_array['email'],
                        "subject" => $search_info['mail_subject'],
                        "contents" => $email_text
                );

                $db -> basic_insert("mass_mailer_emails", $email_insert);

        }


        // *****************************
        // Save config
        // *****************************
        cache_waiting_mail_update(1);

        
        // *****************************
        // Redirect the user
        // *****************************
        $output -> redirect(ROOT."admin/index.php?m=mailer", $lang['emails_added_sucessfully']);

}
?>
