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
 * Common task editing page
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
// Don't come near me with your broom... face...
//***********************************************
load_language_group("admin_tasks");


//***********************************************
// Give me functions!
//***********************************************
include_once ROOT."admin/common/funcs/tasks.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_tasks'], "index.php?m=tasks");

$secondary_mode = (isset($_GET['m2'])) ? $_GET['m2'] : "";

switch($secondary_mode)
{

        case "add":
                page_add_edit_task(true);
                break;
                
        case "doadd":
                do_add_task();
                break;
                                
        case "edit":
                page_add_edit_task();
                break;
                
        case "doedit":
                do_edit_task();
                break;
                                
        case "run":
                do_run_task();
                break;
                                
        case "delete":
                do_delete_task();
                break;
                
        default:
                page_main();

}


//***********************************************
// Main view of it all
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // Create class
        $table = new table_generate;
        $form = new form_generate;

        // Sort message
        $lang['tasks_main_message'] = $output -> replace_number_tags
	        (
	                $lang['tasks_main_message'],
	                array( gmdate("F dS Y H:i A", time()) )
	        );

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['tasks_main_title'];
        
        // ********************
        // Start table
        // ********************
        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['tasks_main_title'], "strip1",  "", "left", "100%", "8").
                $table -> add_basic_row($lang['tasks_main_message'], "normalcell",  "padding : 5px", "left", "100%", "8").
                
                $table -> add_row(
                        array(
                                array($lang['tasks_main_min'], "auto"),
                                array($lang['tasks_main_hour'], "auto"),
                                array($lang['tasks_main_mday'], "auto"),
                                array($lang['tasks_main_wday'], "auto"),
                                array($lang['tasks_main_description'], "25%"),
                                array($lang['tasks_main_next_run'], "25%"),
                                array($lang['tasks_main_actions'], "15%")
                        )
                , "strip2")
        );

        // ********************
        // Grab all tasks
        // ********************
        $db -> query("select * from ".$db -> table_prefix."tasks order by `task_name` asc");

        // Get amount
        $tasks_amount = $db -> num_rows();

        // No tasks?
        if($tasks_amount < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_tasks']."</b>", "normalcell",  "padding : 10px", "center", "100%", "8")
                );        
                
        else
        {

                // *************************
                // Go through each task if we have some
                // *************************
                while($t_array = $db-> fetch_array())
                {

                        $min = ($t_array['minute'] == -1) ? "&nbsp;" : $t_array['minute'];
                        $hour = ($t_array['hour'] == -1) ? "&nbsp;" : $t_array['hour'];
                        $month_day = ($t_array['month_day'] == -1) ? "&nbsp;" : $t_array['month_day'];
                        $week_day = ($t_array['week_day'] == -1) ? "&nbsp;" : $t_array['week_day']; 


                        // Linky linky to actions
                        $actions = "
                        <a href=\"".ROOT."admin/index.php?m=tasks&amp;m2=edit&amp;id=".$t_array['id']."\" title=\"".$lang['tasks_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=tasks&amp;m2=delete&amp;id=".$t_array['id']."\" onclick=\"return confirm('".$lang['delete_task_confirm']."')\" title=\"".$lang['tasks_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=tasks&amp;m2=run&amp;id=".$t_array['id']."\" title=\"".$lang['tasks_main_run_now']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-run.png\"></a>";

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($min, "auto"),
                                                array($hour, "auto"),
                                                array($month_day, "auto"),
                                                array($week_day, "auto"),
                                                array(
                                                        $t_array['task_name']."<br /><font class=\"small_text\">".$t_array['task_description']
                                                , "25%"),
                                                array(return_formatted_date("jS F Y, h:i A", $t_array['next_runtime']), "25%"),
                                                array($actions, "15%", "center")
                                        )
                                , "normalcell")
                        );
               
               }
                
        }

        // ********************
        // End table
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> button("addtask", $lang['add_task_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=tasks&m2=add';\"")
                , "strip3", "", "center", "100%", "8").
                $table -> end_table().
                $form -> end_form()
        );
        
}


//***********************************************
// Form for adding or editing a task
//***********************************************
function page_add_edit_task($adding = false, $task_info = "")
{

        global $output, $lang, $db, $template_admin;

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ***************************
        // Need different headers
        // ***************************
        if($adding)
        {

                // *********************
                // Set page title
                // *********************
		$output -> add_breadcrumb($lang['breadcrumb_tasks_add'], "index.php?m=tasks&amp;m2=add");

                $output -> page_title = $lang['add_task_title'];

                // ADDING
                $output -> add(
                        $form -> start_form("addtask", ROOT."admin/index.php?m=tasks&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['add_task_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_task_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_task_submit'];

                if(!$task_info)
                        $task_info['task_filepath'] = "common/tasks/script.php";

        }
        else
        {

                // **************************
                // Grab the task
                // **************************
                $get_id = trim($_GET['id']);
                
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                        page_main();
                        return;
                }
                        
                // Grab wanted task
                $db -> query("select * from ".$db -> table_prefix."tasks where id='".$get_id."'");
        
                // Die if it doesn't exist
                if($db -> num_rows() == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                        page_main();
                        return;
                }
        
                // Get stuff
                if(!$task_info)
                        $task_info = $db -> fetch_array();

                // *********************
                // Set page title
                // *********************
		$output -> add_breadcrumb($lang['breadcrumb_tasks_edit'], "index.php?m=tasks&amp;m2=edit&amp;id=".$get_id);

                $output -> page_title = $lang['edit_task_title'];

                // ADDING
                $output -> add(
                        $form -> start_form("edittask", ROOT."admin/index.php?m=tasks&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['edit_task_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['edit_task_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_task_submit'];
        
        }


        // ***************************
        // Do minutes dropdown
        // ***************************
        $minutes_vaules[] = -1;
        $minutes_text[] = $lang['every_minute'];
        
        for($a = 0; $a < 60; $a++)
        {
                $minutes_vaules[] = $a;
                $minutes_text[] = $a;
        }

        // ***************************
        // Do hours dropdown
        // ***************************
        $hours_vaules[] = -1;
        $hours_text[] = $lang['every_hour'];
        
        for($a = 0; $a < 24; $a++)
        {
                $hours_vaules[] = $a;
                $hours_text[] = $a;
        }
        
        // ***************************
        // Do week dropdown
        // ***************************
        $week_day_vaules = array(
                -1, 0, 1, 2, 3, 4, 5, 6
        );
        
        $week_day_text = array(
                $lang['every_week_day'],
                $lang['monday'],
                $lang['tuesday'],
                $lang['wednesday'],
                $lang['thursday'],
                $lang['friday'],
                $lang['saturday'],
                $lang['sunday']
        );
                
        // ***************************
        // Do month dropdown
        // ***************************
        $month_day_vaules[] = -1;
        $month_day_text[] = $lang['every_month_day'];
        
        for($a = 1; $a < 32; $a++)
        {
                $month_day_vaules[] = $a;
                $month_day_text[] = $a;
        }
                
        // ***************************
        // Print the form
        // ***************************
        $output -> add(
                $table -> add_row(
                        array(
                                array($lang['add_task_name'], "50%"),
                                array($form -> input_text("task_name", $task_info['task_name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_description'], "50%"),
                                array($form -> input_text("task_description", $task_info['task_description']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_filepath']."<br /><font class=\"small_text\">".$lang['add_task_filepath_desc']."</font>", "50%"),
                                array($form -> input_text("task_filepath", $task_info['task_filepath']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_minute'], "50%"),
                                array($form -> input_dropdown("minute", $task_info['minute'], $minutes_vaules, $minutes_text), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_hour'], "50%"),
                                array($form -> input_dropdown("hour", $task_info['hour'], $hours_vaules, $hours_text), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_week_day'], "50%"),
                                array($form -> input_dropdown("week_day", $task_info['week_day'], $week_day_vaules, $week_day_text), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_month_day'], "50%"),
                                array($form -> input_dropdown("month_day", $task_info['month_day'], $month_day_vaules, $month_day_text), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_enable'], "50%"),
                                array($form -> input_yesno("enabled", $task_info['enabled']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_task_keep_log'], "50%"),
                                array($form -> input_yesno("keep_log", $task_info['keep_log']), "50%")
                        )
                , "normalcell").
                // -----------
                // Submit Button
                // -----------
                $table -> add_basic_row($form -> submit("submit", $submit_lang), "strip3", "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );               
        
}


//***********************************************
// Add the task
//***********************************************
function do_add_task()
{

        global $output, $lang, $db, $template_admin;

        // ***************************
        // Get stuff from the post
        // ***************************
        $task_info = array(
                "task_name"             => $_POST['task_name'],
                "task_description"      => $_POST['task_description'],
                "task_filepath"         => $_POST['task_filepath'],
                "minute"                => $_POST['minute'],
                "hour"                  => $_POST['hour'],
                "week_day"              => $_POST['week_day'],
                "month_day"             => $_POST['month_day'],
                "enabled"               => $_POST['enabled'],
                "keep_log"              => $_POST['keep_log']
        );

        // ***************************
        // Check there's something in the file path
        // ***************************
        if(trim($task_info['task_filepath']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_task_no_path']));
                page_add_edit_task(true, $task_info);
                return;
        }               

        // ***************************
        // Get next run date
        // ***************************
        $task_info['next_runtime'] = get_next_run_date($task_info);

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("tasks", $task_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_task_error']));
                page_add_edit_task(true, $task_info);
                return;
        }               

        // ***************************
        // Save next task run in DB
        // ***************************
        config_save_next_run();

        // ***************************
        // Log it!
        // ***************************
        log_admin_action("tasks", "doadd", "Added common task: ".$task_info['task_name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=tasks", $lang['task_created_sucessfully']);
                                
}


//***********************************************
// Running a task??
//***********************************************
function do_run_task()
{

        global $cache, $output, $lang, $db, $template_admin, $common_task_log;

        // **************************
        // Grab the task
        // **************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                page_main();
                return;
        }
                
        // Grab wanted task
        $db -> query("select * from ".$db -> table_prefix."tasks where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows() == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                page_main();
                return;
        }

        // Get stuff
        $task_info = $db -> fetch_array();

        // **************************
        // Update next runtime
        // **************************
        $edit_info['next_runtime'] = get_next_run_date($task_info);

	$db -> basic_update("tasks", $edit_info, "id='".$get_id."'");

        // **************************
        // Run it
        // **************************
        if(file_exists(ROOT.$task_info['task_filepath']))
                include ROOT.$task_info['task_filepath'];
        else
        {
                $output -> add(
                        $template_admin -> critical_error(
                                $output -> replace_number_tags($lang['task_file_not_found'], array(ROOT.$task_info['task_filepath']))
                        )
                );
                page_main();
                return;
        }

        // **************************
        // Save log
        // **************************
        if($task_info['keep_log'])
                log_task_run($task_info['id'], $task_info['task_name'], $common_task_log);


        // ***************************
        // Save next task run in DB
        // ***************************
        config_save_next_run();

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=tasks", $lang['task_ran_sucessfully']);

}


//***********************************************
// Edit a task
//***********************************************
function do_edit_task()
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Grab the task
        // **************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                page_main();
                return;
        }
                
        // Grab wanted task
        $db -> query("select id from ".$db -> table_prefix."tasks where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows() == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                page_main();
                return;
        }

        // Get stuff
        $task_info = $db -> fetch_array();
        
        // ***************************
        // Get stuff from the post
        // ***************************
        $task_info = array(
                "task_name"             => $_POST['task_name'],
                "task_description"      => $_POST['task_description'],
                "task_filepath"         => $_POST['task_filepath'],
                "minute"                => $_POST['minute'],
                "hour"                  => $_POST['hour'],
                "week_day"              => $_POST['week_day'],
                "month_day"             => $_POST['month_day'],
                "enabled"               => $_POST['enabled'],
                "keep_log"              => $_POST['keep_log']
        );

        // ***************************
        // Check there's something in the file path
        // ***************************
        if(trim($task_info['task_filepath']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_task_no_path']));
                page_add_edit_task(false, $task_info);
                return;
        }               

        // ***************************
        // Get next run date
        // ***************************
        $task_info['next_runtime'] = get_next_run_date($task_info);

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_update("tasks", $task_info, "id='".$_GET['id']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_task_error']));
                page_add_edit_task(false, $task_info);
                return;
        }               

        // ***************************
        // Save next task run in DB
        // ***************************
        config_save_next_run();

        // ***************************
        // Log it!
        // ***************************
        log_admin_action("tasks", "doedit", "Edited common task: ".$task_info['task_name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=tasks", $lang['task_edited_sucessfully']);
        
}


//***********************************************
// Baleeete a task
//***********************************************
function do_delete_task()
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Grab the task
        // **************************
        $get_id = trim($_GET['id']);
        
        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                page_main();
                return;
        }
                
        // Grab wanted task
        $db -> query("select id, task_name from ".$db -> table_prefix."tasks where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows() == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_task_id']));
                page_main();
                return;
        }

        // Get stuff
        $task_info = $db -> fetch_array();

        // ********************
        // Delete it
        // ********************
        $db -> query("DELETE FROM ".$db -> table_prefix."tasks WHERE id='".$get_id."'");

        // ***************************
        // Save next task run in DB
        // ***************************
        config_save_next_run();

        // ***************************
        // Log it!
        // ***************************
        log_admin_action("tasks", "delete", "Deleted common task: ".$task_info['task_name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=tasks", $lang['task_deleted_sucessfully']);
                
}

?>
