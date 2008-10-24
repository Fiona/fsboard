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
*       Admin logs              *
*       Started by Fiona        *
*       12th Jan 2006           *
*********************************
*       Last edit by Fiona      *
*       17th Jan 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Include meh language file
//***********************************************
load_language_group("admin_adminlogs");


$output -> add_breadcrumb($lang['breadcrumb_admin_logs'], "index.php?m=adminlogs");

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
        $output -> page_title = $lang['adminlogs_title'];

        // Create classes
        $table = new table_generate;
        
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title
                // ---------------
                $table -> add_basic_row($lang['adminlogs_title'], "strip1",  "", "left", "100%", "5").
                $table -> add_basic_row($lang['adminlogs_message'], "normalcell",  "", "left", "100%", "5").
                $table -> add_row(array(
                        array($lang['adminlogs_username_ip'], "15%"),
                        array($lang['adminlogs_page'], "10%"),
                        array($lang['adminlogs_mode'], "20%"),
                        array($lang['adminlogs_date'], "20%"),
                        array($lang['adminlogs_note'], "35%")
                ), "strip2")
        );
 
 
        //**************************
        // Sort out all the search criteria
        //**************************
        if($_POST['days'] == '')
                $_POST['days'] = 14;        
        $date = TIME - ($_POST['days'] * 24 * 60 * 60);

        if(trim($_POST['note_search']) != "")
                $search .= " and a.`note` like '%".$_POST['note_search']."%'";

        if(trim($_POST['ip_search']) != "")
                $search .= " and a.`ip` like '%".$_POST['ip_search']."%'";

        if(trim($_POST['user_search']) != "")
                $search .= " and a.`member` like '%".$_POST['user_search']."%'";

        if(trim($_POST['page_search']) != "")
                $search .= " and a.`page_name` like '%".$_POST['page_search']."%'";

        // Grab the logs
        $wanted_logs = $db->query("
        select a.*, u.username from ".$db->table_prefix."admin_logs as a 
        left join ".$db->table_prefix."users as u on(u.id = a.member)
        where a.`date` > '".$date."' ".$search."
        order by a.date desc");


        //**************************
        // None? Tell 'em.
        //**************************
        if($db -> num_rows($wanted_logs) < 1)
                $output -> add($table -> add_basic_row($lang['adminlogs_none_found'], "normalcell",  "padding : 10px;", "center", "100%", "5"));
        else
        {
        
                while($log_array = $db->fetch_array($wanted_logs))
                {

                        $output -> add(
                                $table -> add_row(array(
                                        array($log_array['username']." (".$log_array['ip'].")", "15%"),
                                        array($log_array['page_name'], "10%"),
                                        array($log_array['mode'], "10%"),
                                        array(date("M j Y, g:i a", $log_array['date']), "20%"),
                                        array($log_array['note'], "45%")
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
                $form -> start_form("changeview", ROOT."admin/index.php?m=adminlogs" , "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['adminlogs_change_view'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['adminlogs_change_view_message'], "normalcell",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['adminlogs_search_days'], "40%"),
                        array($form->input_int("days",$_POST['days']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['adminlogs_search_note'], "40%"),
                        array($form->input_text("note_search",$_POST['note_search']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['adminlogs_search_ip'], "40%"),
                        array($form->input_text("ip_search",$_POST['ip_search']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['adminlogs_search_user'], "40%"),
                        array($form->input_text("user_search",$_POST['user_search']), "60%")
                ), "normalcell").
                $table -> add_row(array(
                        array($lang['adminlogs_search_page'], "40%"),
                        array($form->input_text("page_search",$_POST['page_search']), "60%")
                ), "normalcell").
                $table -> add_basic_row($form->submit("submit",$lang['adminlogs_search_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().  
                $form -> end_form().

                // ---------------
                // Delete some
                // ---------------
                $form -> start_form("deletelogs", ROOT."admin/index.php?m=adminlogs&amp;m2=delete", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['adminlogs_delete_tite'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['adminlogs_delete_days'], "40%"),
                        array($form->input_int("days","60"), "60%")
                ), "normalcell").
                $table -> add_basic_row($form->submit("submit",$lang['adminlogs_delete_submit']), "strip3",  "", "center", "100%", "2").
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
        $remove_logs = "delete from ".$db -> table_prefix."admin_logs where `date` < '".$date."'";
        
        if (!$db -> query($remove_logs))        
        {
                $output -> add($template_admin -> critical_error($lang['error_removing_adminlog']));
                page_main();
                return;
        }
        else
                $output -> redirect(ROOT."admin/index.php?m=adminlogs", $lang['deleted_adminlog']);

                
}


?>
