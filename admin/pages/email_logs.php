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
*       Admin E-mail logs       *
*       Started by Fiona        *
*       03rd Jan 2006           *
*********************************
*       Last edit by Fiona      *
*       30th May 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Include meh language file
//***********************************************
load_language_group("admin_emaillogs");


$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "view":        
                page_single_email();
                break;

        case "delete":        
                do_delete_single_email();
                break;

        case "deletesome":        
                do_delete_some_emails();
                break;

        default:
                page_main();
                break;
}


//***********************************************
// Front page
//***********************************************
function  page_main()
{

        global $output, $lang, $db;


        //**************************
        // If we want errors then get different phrases
        //**************************
        if($_GET['error'] == "1")
        {
                $title = $lang['admin_emaillogs_error_title'];
                $message = $lang['admin_emaillogs_error_message'];
                $first_cell = $lang['emaillogs_error_message'];
                $error_url_add = "&amp;error=1";

		$output -> add_breadcrumb($lang['breadcrumb_email_error_logs'], "index.php?m=emaillogs");

        }
        else
        {
                $title = $lang['admin_emaillogs_title'];
                $message = $lang['admin_emaillogs_message'];
                $first_cell = $lang['emaillogs_address_from'];
                $error_url_add = "";

		$output -> add_breadcrumb($lang['breadcrumb_email_logs'], "index.php?m=emaillogs&amp;error=1");

        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $title;

        // Create classes
        $table = new table_generate;
        
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title
                // ---------------
                $table -> add_basic_row($title, "strip1",  "", "left", "100%", "5").
                $table -> add_basic_row($message, "normalcell",  "", "left", "100%", "5").
                $table -> add_row(array(
                        array($first_cell, "25%"),
                        array($lang['emaillogs_reciptent'], "25%"),
                        array($lang['emaillogs_subject'], "20%"),
                        array($lang['emaillogs_date_sent'], "20%"),
                        array($lang['emaillogs_actions'], "10%")
                ), "strip2")
        );
        

        //**************************
        // Sort out all the search criteria
        //**************************
        if($_POST['days'] == '')
                $_POST['days'] = 30;        
        $date = TIME - ($_POST['days'] * 24 * 60 * 60);

        if(trim($_POST['subject_search']) != "")
                $search .= " and `subject` like '%".$_POST['subject_search']."%'";

        if(trim($_POST['to_search']) != "")
                $search .= " and `to` like '%".$_POST['to_search']."%'";

        if(trim($_POST['from_search']) != "")
                $search .= " and `from` like '%".$_POST['from_search']."%'";

        // Grab the emails
        if($_GET['error'] == "1")
                $wanted_mails = $db->query("select * from ".$db->table_prefix."email_logs where `date` > '".$date."' and `error` = '1' ".$search." order by date desc");
        else
                $wanted_mails = $db->query("select * from ".$db->table_prefix."email_logs where `date` > '".$date."' and `error` = '0' ".$search." order by date desc");


        //**************************
        // None? Tell 'em.
        //**************************
        if($db -> num_rows($wanted_mails) < 1)
                $output -> add($table -> add_basic_row($lang['emaillogs_none_found'], "normalcell",  "padding : 10px;", "center", "100%"));
        else
        {
        
                while($email_array = $db->fetch_array($wanted_mails))
                {

                        // Again, different stuff if errors
                        if($_GET['error'] == "1")
                                $first_cell = "<b>".$email_array['note']."</b>";
                        else
                                $first_cell = $email_array['from'];
                                
                        // Chuck out the row!
                        $output -> add(
                                $table -> add_row(array(
                                        array($first_cell, "25%"),
                                        array($email_array['to'], "25%"),
                                        array($email_array['subject'], "20%"),
                                        array(date("F j, Y, g:i a", $email_array['date']), "20%"),
                                        array(
                                                "<a href=\"index.php?m=emaillogs&amp;m2=view&amp;id=".$email_array['id']."\" title=\"".$lang['view_emaillog']."\">
                                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-preview.png\"></a>".

                                                "<a href=\"index.php?m=emaillogs&amp;m2=delete&amp;id=".$email_array['id']."\" title=\"".$lang['delete_emaillog']."\">
                                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>"                                      
                                        , "10%")
                                ), "normalcell")
                        );
                        
                }
        
        }


        //**************************
        // set up form stuff        
        //**************************
        $form = new form_generate;
        
        // Chuck the rest on...
        $output -> add(
                $table -> end_table().
                // ---------------
                // Change View
                // ---------------
                $form -> start_form("changeview", ROOT."admin/index.php?m=emaillogs".$error_url_add , "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['emaillogs_change_view'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['emaillogs_change_view_message'], "normalcell",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['emaillogs_search_days'], "40%"),
                        array($form->input_int("days",$_POST['days']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['emaillogs_search_subject'], "40%"),
                        array($form->input_text("subject_search",$_POST['subject_search']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['emaillogs_search_from'], "40%"),
                        array($form->input_text("from_search",$_POST['from_search']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['emaillogs_search_to'], "40%"),
                        array($form->input_text("to_search",$_POST['to_search']), "60%")
                ), "normalcell").
                $table -> add_basic_row($form->submit("submit",$lang['emaillogs_search_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().  
                $form -> end_form().     

                // ---------------
                // Delete some
                // ---------------
                $form -> start_form("deleteemails", ROOT."admin/index.php?m=emaillogs&amp;m2=deletesome".$error_url_add, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['emaillogs_delete_some_tite'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['emaillogs_delete_days'], "40%"),
                        array($form->input_int("days","30"), "60%")
                ), "normalcell").
                $table -> add_basic_row($form->submit("submit",$lang['emaillogs_delete_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().  
                $form -> end_form()            

        );

}


//***********************************************
// Viewing one log
//***********************************************
function  page_single_email()
{

        global $output, $lang, $db, $template_admin;

        //**************************
        // Nab the log
        //**************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['emaillog_no_id']));
                page_main();
                return;
        }
                
        // Grab wanted log
        $log = $db -> query("select * from ".$db -> table_prefix."email_logs where id='".$get_id."'");
        $log_array = $db -> fetch_array($log);
        
        // Die if it doesn't exist
        if($db -> num_rows($log) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['emaillog_single_not_found']));
                page_main();
                return;
        }

        if($log_array['error'])
        {
                $error_url_add = "&amp;m2=error";
		$output -> add_breadcrumb($lang['breadcrumb_email_error_logs'], "index.php?m=emaillogs&amp;error=1");
        }                
        else
        {
                $error_url_add = "";
		$output -> add_breadcrumb($lang['breadcrumb_email_logs'], "index.php?m=emaillogs");
        }                

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['single_log_title'];

	$output -> add_breadcrumb($lang['breadcrumb_single_email'], "index.php?m=emaillogs&amp;id=".$get_id.$error_url_add);

        //**************************
        // Show the log
        //**************************
        // Create classes
        $table = new table_generate;
        
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Innit.
                // ---------------
                $table -> add_basic_row($lang['single_log_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['single_log_from'], "40%"),
                        array("&lt;<b>".$log_array['from']."</b>&gt;", "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['single_log_to'], "40%"),
                        array("&lt;<b>".$log_array['to']."&gt;</b>", "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['single_log_date'], "40%"),
                        array(date("F j, Y, g:i a", $log_array['date']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['single_log_subject'], "40%"),
                        array($log_array['subject'], "60%")
                ), "normalcell").
                $table -> add_basic_row(str_replace("\n", "<br />", $log_array['text']), "normalcell",  "padding : 10px;", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['single_log_message'], "40%"),
                        array($log_array['note'], "60%")
                ), "normalcell").
                $table -> end_table()
        );

}


//***********************************************
// Killing one e-mail
//***********************************************
function  do_delete_single_email()
{

        global $output, $lang, $db, $template_admin;

        //**************************
        // Nab the log
        //**************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['emaillog_no_id']));
                page_main();
                return;
        }
                
        // Grab wanted log
        $log = $db -> query("select id,error from ".$db -> table_prefix."email_logs where id='".$get_id."'");
        $log_array = $db -> fetch_array($log);
        
        // Die if it doesn't exist
        if($db -> num_rows($log) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['emaillog_single_not_found']));
                page_main();
                return;
        }

        if($log_array['error'])
                $error_url_add = "&amp;m2=error";
        else
                $error_url_add = "";


        // Remove the email
        if(!$db -> basic_delete("email_logs", "id = '".$log_array['id']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_emaillog']));
                page_main();
                return;
        }
        else
                $output -> redirect(ROOT."admin/index.php?m=emaillogs".$error_url_add, $lang['deleted_emaillog']);

}


//***********************************************
// Killing more than one e-mail
//***********************************************
function  do_delete_some_emails()
{

        global $output, $lang, $db, $template_admin;

        if($_GET['error'] == "1")
        {
                $errors = " and `error`='1'";
                $error_url_add = "&amp;error=1";
        }
        else
        {
                $errors = " and `error`='0'";
                $error_url_add = "";
        }

        $date = TIME - ($_POST['days'] * 24 * 60 * 60);
                
        // Remove the emails
        if(!$db -> basic_delete("email_logs", "`date` < '".$date."'".$errors))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_emaillog']));
                page_main();
                return;
        }
        else
                $output -> redirect(ROOT."admin/index.php?m=emaillogs".$error_url_add, $lang['deleted_emaillog']);

                
}

?>
