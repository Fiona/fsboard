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
*       Common Task Logs        *
*       Started by Fiona        *
*       21st Mar 2006           *
*********************************
*       Last edit by Fiona      *
*       06th Feb 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Use same language file from other page
//***********************************************
load_language_group("admin_tasks");


$output -> add_breadcrumb($lang['breadcrumb_task_logs'], "index.php?m=tasklogs");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
        case "delete":        
                do_delete_logs();
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

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['taskslogs_title'];

        // Create classes
        $table = new table_generate;
        
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title
                // ---------------
                $table -> add_basic_row($lang['taskslogs_title'], "strip1",  "", "left", "100%", "5").
                $table -> add_basic_row($lang['taskslogs_message'], "normalcell",  "", "left", "100%", "5").
                $table -> add_row(array(
                        array($lang['taskslogs_task_name'], "25%"),
                        array($lang['tasklogs_action_taken'], "35%"),
                        array($lang['taskslogs_date'], "25%"),
                        array($lang['taskslogs_ip'], "15%")
                ), "strip2")
        );

        //**************************
        // Sort out all the search criteria
        //**************************
        if($_POST['days'] == '')
                $_POST['days'] = 14;        
        $date = TIME - ($_POST['days'] * 24 * 60 * 60);

        if(trim($_POST['name_search']) != "")
                $search .= " and `task_name` like '%".$_POST['name_search']."%'";

        if(trim($_POST['ip_search']) != "")
                $search .= " and `ip` like '%".$_POST['ip_search']."%'";

        // Grab the logs
        $wanted_logs = $db->query("select * from ".$db->table_prefix."task_logs where `date` > '".$date."' ".$search." order by date desc");

        //**************************
        // None? Tell 'em.
        //**************************
        if($db -> num_rows($wanted_logs) < 1)
                $output -> add($table -> add_basic_row($lang['taskslogs_none_found'], "normalcell",  "padding : 10px;", "center", "100%", "4"));
        else
        {

                while($log_array = $db->fetch_array($wanted_logs))
                {

                        $output -> add(
                                $table -> add_row(array(
                                        array($log_array['task_name'], "25%"),
                                        array($log_array['action'], "35%"),
                                        array(date("M j Y, g:i a", $log_array['date']), "25%"),
                                        array($log_array['ip'], "15%")
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
                $form -> start_form("changeview", ROOT."admin/index.php?m=tasklogs" , "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['taskslogs_change_view'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['taskslogs_change_view_message'], "normalcell",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['taskslogs_search_days'], "40%"),
                        array($form->input_int("days",$_POST['days']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['taskslogs_search_name'], "40%"),
                        array($form->input_text("name_search",$_POST['name_search']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['taskslogs_search_ip'], "40%"),
                        array($form->input_text("ip_search",$_POST['ip_search']), "60%")
                ), "normalcell").
                $table -> add_basic_row($form->submit("submit",$lang['taskslogs_search_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().  
                $form -> end_form().
                
                // ---------------
                // Delete some
                // ---------------
                $form -> start_form("deletelogs", ROOT."admin/index.php?m=tasklogs&amp;m2=delete", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['taskslogs_delete_tite'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['taskslogs_delete_days'], "40%"),
                        array($form->input_int("days","60"), "60%")
                ), "normalcell").
                $table -> add_basic_row($form->submit("submit",$lang['taskslogs_delete_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().  
                $form -> end_form()  
        );
        
}


//***********************************************
// Trimming some logs
//***********************************************
function do_delete_logs()
{

        global $output, $lang, $db, $template_admin;

        $date = TIME - ($_POST['days'] * 24 * 60 * 60);
                
        // Remove the logs
        if(!$db -> basic_delete("task_logs", "`date` < '".$date."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['taskslogs_error_removing']));
                page_main();
                return;
        }
        else
                $output -> redirect(ROOT."admin/index.php?m=tasklogs", $lang['taskslogs_deleted']);

                
}


?>
