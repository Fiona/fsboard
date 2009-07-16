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
 * Admin SQL tools
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
// Include meh language file
//***********************************************
load_language_group("admin_sqltools");


$output -> add_breadcrumb($lang['breadcrumb_sqltools'], "index.php?m=sqltools");


$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
        case "query":
                page_run_query();
                break;

        case "doquery":
                do_show_query($_POST['querytext']);
                break;
                                
        case "serverstatus":
                do_show_query("SHOW STATUS");
                break;
                                
        case "systemvars":
                do_show_query("SHOW VARIABLES");
                break;

        case "table":
                do_show_query("SELECT * FROM `".$_GET['table']."`");
                break;                

        case "dospecial":
                do_special_command();
                break;

        case "backup":
                page_backup();
                break;
        
        case "dobackup":
                do_backup();
                break;
        
        default:
                page_main();
                break;
}


//***********************************************
// View all the tables in a list nigga
//***********************************************
function  page_main()
{

        global $output, $lang, $db;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['sqltools_tables_title'];

        // Create classes
        $table = new table_generate;
        $form = new form_generate;
        
        $output -> add(
                "
                <script language=\"JavaScript\" type=\"text/javascript\">
                <!--
                function checkUncheckAll(theElement) {
                        var theForm = theElement.form, z = 0;
                        for(z=0; z<theForm.length;z++){
                                if(theForm[z].type == 'checkbox' && theForm[z].name != 'checkall'){
                                        theForm[z].checked = theElement.checked;
                                }
                        }
                }
                //-->
                </script>".
                $form -> start_form("runspecial", ROOT."admin/index.php?m=sqltools&amp;m2=dospecial" , "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title
                // ---------------
                $table -> add_basic_row($lang['sqltools_tables_title'], "strip1",  "", "left", "100%", "3").
                $table -> add_basic_row($lang['sqltools_tables_message'], "normalcell",  "", "left", "100%", "3").
                $table -> add_row(array(
                        array($lang['sqltools_tables_name'], "55%"),
                        array($lang['sqltools_tables_rownum'], "40%"),
                        array($form -> input_checkbox("checkall", "1", "inputclass", false, "checkUncheckAll(this);"), "5%")
                ), "strip2")
        );

        // Grab the tables plz
        $db -> query("SHOW TABLE STATUS FROM `".$db->database_name."`");

        while($table_row = $db -> fetch_array())
        {

                // Check it's got the tbl prefix if applicable
                if($db -> table_prefix != "")
                {
                        if(!preg_match("/^".$db->table_prefix."/", $table_row['Name']))
                                continue;
                }

                $output -> add(
                        $table -> add_row(array(
                                array("<a href=\"".ROOT."admin/index.php?m=sqltools&amp;m2=table&amp;table=".$table_row['Name']."\">".$table_row['Name']."</a>", "55%"),
                                array($table_row['Rows'], "40%"),
                                array($form -> input_checkbox("table[".$table_row['Name']."]", "1"), "5%", "center")
                        ), "normalcell")
                );
                
        }        

        // Setup dropdown values
        $task_values = array(
                "repair",
                "analyse",
                "check",
                "optimise"
        );
        $task_text = array(
                $lang['sqltools_special_repair'],
                $lang['sqltools_special_analyse'],
                $lang['sqltools_special_check'],
                $lang['sqltools_special_optimise']
        );
        
        $output -> add(
                $table -> add_basic_row(
                        $lang['sqltools_special_message'].
                        $form -> input_dropdown("task", "", $task_values, $task_text, "inputtext", "auto").
                        $form -> submit("submit", $lang['sqltools_special_submit'])
                , "strip3",  "", "center", "100%", "3").

                $table -> end_table().
                $form -> end_form().
                // ---------------
                // Query box
                // ---------------
                $form -> start_form("runquery", ROOT."admin/index.php?m=sqltools&amp;m2=doquery" , "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['sqltools_runquery_title'], "strip1",  "", "left", "100%").
                $table -> add_basic_row($form -> input_textbox("querytext", "SELECT * FROM `table` WHERE `foo`='bar'", 7), "normalcell",  "", "center", "100%").
                $table -> add_basic_row($form -> submit("submit", $lang['sqltools_runquery_submit']), "strip3",  "", "center", "100%").
                $table -> end_table().
                $form -> end_form()
        );        
        
}



//***********************************************
// Form to run a query
//***********************************************
function page_run_query($query = "")
{

        global $output, $lang, $db;

        if($query == "")
                $query = "SELECT * FROM `table` WHERE `foo`='bar'";

        // *********************
        // Set page title
        // *********************
	if($_GET['m2'] == "query")
		$output -> add_breadcrumb($lang['breadcrumb_sqltools_run_query'], "index.php?m=sqltools&amp;m2=query");
	
        $output -> page_title = $lang['sqltools_runquery_title'];
                
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                // ---------------
                // Query box
                // ---------------
                $form -> start_form("runquery", ROOT."admin/index.php?m=sqltools&amp;m2=doquery" , "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['sqltools_runquery_title'], "strip1",  "", "left", "100%").
                $table -> add_basic_row($form -> input_textbox("querytext", stripslashes($query), 7), "normalcell",  "", "center", "100%").
                $table -> add_basic_row($form -> submit("submit", $lang['sqltools_runquery_submit']), "strip3",  "", "center", "100%").
                $table -> end_table()
        );        

}



//***********************************************
// Run a query and display the results
//***********************************************
function do_show_query($query, $show_run_query = true)
{

        global $output, $lang, $db, $template_admin, $breadcrumb_done;

        switch($_GET['m2'])
        {
                case "serverstatus":
                        $title = $lang['sqltools_server_status'];
			$output -> add_breadcrumb($lang['breadcrumb_sqltools_server_status'], "index.php?m=sqltools&amp;m2=serverstatus");
                        break;
                        
                case "systemvars":
                        $title = $lang['sqltools_system_vars'];
			$output -> add_breadcrumb($lang['breadcrumb_sqltools_system_vars'], "index.php?m=sqltools&amp;m2=systemvars");
                        break;
                        
                default:
                        $title = $lang['sqltools_do_query_title'];
                        
                        if($breadcrumb_done == false)
				$output -> add_breadcrumb($lang['breadcrumb_sqltools_run_query'], "index.php?m=sqltools&amp;m2=query");
				
			$breadcrumb_done = true;
        }


        // *********************
        // Set page title
        // *********************
        $output -> page_title = $title;

        // check if we're allowed to do this
        if(preg_match("/^DROP|CREATE|FLUSH|TRUNCATE/i", trim($query)))
        {
                $output -> add($template_admin -> critical_error($lang['sqltools_safety_check']));
                return;        
        }

        // Do the query 
        $db -> query(stripslashes($query));
        
        // Check for errors
        if($db->log_error != "")
        {        
               $output -> add($template_admin -> normal_error(stripslashes($db->log_error), $lang['sqltools_error_title']));
               return;       
        }

        // These commands have no output
        if(preg_match("/^INSERT|UPDATE|DELETE|ALTER/i", trim($query)))
        {
               $output -> add($template_admin -> message($lang['sqltools_query_done_title'], $lang['sqltools_query_done_msg']."<br /><b>".$query."</b>"));
                return;        
        }

        // Get column information
        $column_fields = $db -> fetch_fields();

        $total_cols = count($column_fields);
        
        for($a = 0; $a < $total_cols; $a++)
                $row[] = $column_fields[$a] -> name;
                
        // Print the table
        $table = new table_generate;

        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($title, "strip1", "", "left", "", $total_cols).
                $table -> add_row($row, "strip2")
        );

        // Rows
        while($row_array = $db -> fetch_array())
        {
        
                $row = array();
                
                for($a = 0; $a < $total_cols; $a++)
                {
                        $text = $row_array[$column_fields[$a] -> name];
                        $text = wordwrap($row_array[$column_fields[$a] -> name], "75", "\n");
                        $text = _htmlspecialchars($text);
                        $text = nl2br($text);
                        
                        $row[] = $text;
                }      
                
                // print row          
                $output -> add(
                        $table -> add_row($row, "normalcell")
                );
        
        }                

        $output -> add($table -> end_table());

        if($show_run_query)        
                page_run_query($query);
        
}



//***********************************************
// Run a special command on one or more tables
//***********************************************
function do_special_command()
{

        global $output, $lang, $db, $template_admin;

        // Sort out the tables we want
        $post_tables = $_POST['table'];

        // No tables?
        if(count($post_tables) < 1)
        {
                $output -> add($template_admin -> critical_error($lang['sqltools_no_tables']));
                page_main();
                return;        
        }
        
        $tables = array();
        
        foreach($post_tables as $key => $val)
        {
       
                // Check it's got the tbl prefix if applicable
                if($db -> table_prefix != "")
                {
                        if(!preg_match("/^".$db->table_prefix."/", $key))
                                continue;
                }
        
                $tables[] = $key;
                
        }
       
        // what command
        switch($_POST['task'])
        {
                case "repair":
                        $command = "REPAIR TABLE ";
                        break;

                case "analyse":
                        $command = "ANALYZE TABLE ";
                        break;
                        
                case "check":
                        $command = "CHECK TABLE ";
                        break;

                case "optimise":
                        $command = "OPTIMIZE TABLE ";
                        break;
                        
                default:
                        $output -> add($template_admin -> critical_error($lang['sqltools_task_error']));
                        return;        
        }

        // Go through each table and run the command
        foreach($tables as $one_table)
        {
        
                do_show_query($command."`".$one_table."`", false);
        
        }

        page_run_query($command);        
        
}



//***********************************************
// Form for backing up tables
//***********************************************
function page_backup()
{

        global $output, $lang, $db, $template_admin;

        // Setup the dropdown
        $tables_dropdown[] = "-1";
        $tables_dropdown_text[] = $lang['sqltools_all_dropdown'];
        
        // Grab the tables plz
        $db -> query("SHOW TABLE STATUS FROM `".$db->database_name."`");

        while($table_row = $db -> fetch_array())
        {

                // Check it's got the tbl prefix if applicable
                if($db -> table_prefix != "")
                {
                        if(!preg_match("/^".$db->table_prefix."/", $table_row['Name']))
                                continue;
                }

                $tables_dropdown[] = $table_row['Name'];
                $tables_dropdown_text[] = $table_row['Name'];
         
        }

        // *********************
        // Set page title
        // *********************
	$output -> add_breadcrumb($lang['breadcrumb_sqltools_backup'], "index.php?m=sqltools&amp;m2=backup");

        $output -> page_title = $lang['sqltools_backup_title'];
        
        // Create classes
        $table = new table_generate;
        $form = new form_generate;
        
        $output -> add(
                $form -> start_form("export", ROOT."admin/index.php?m=sqltools&amp;m2=dobackup" , "post", false, true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title
                // ---------------
                $table -> add_basic_row($lang['sqltools_backup_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['sqltools_backup_message'], "normalcell",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['sqltools_export_filename'], "50%"),
                        array($form -> input_text("filename", "backup_".date("d-m-y",TIME).".sql"), "50%")
                ), "normalcell").
                $table -> add_row(
                        array(
                                array($lang['sqltools_export_what'],"50%"),
                                array($form -> input_dropdown("table", "", $tables_dropdown, $tables_dropdown_text),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array(
                                	$output -> replace_number_tags($lang['sqltools_export_schema'], array($db -> db_type))
                                ,"50%"),
                                array($form -> input_yesno("schema", ""),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['sqltools_export_drop_table'],"50%"),
                                array($form -> input_yesno("drop_table", ""),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form->submit("submit", $lang['sqltools_export_submit']).$form->reset("reset", $lang['sqltools_export_reset']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );


}


//***********************************************
// Let's backup some SQL
//***********************************************
function do_backup()
{

        global $output, $lang, $db, $template_admin;

        // ********************
        // Sort out create table stuff
        // ********************
        if($_POST['schema'])
        {
    
    		// Check mysql version for different schema
            if($db -> db_type == "MySQL" && version_compare($db -> version, "4.1.3", ">="))
                $schema_extra_path = "MySQL4.1/";
            else
                $schema_extra_path = "";

	        if(!file_exists(ROOT."install/default_data/".$db -> db_type."/".$schema_extra_path."db_schema.php"))
	        {
                $output -> add(
                	$template_admin -> critical_error(
                        	$output -> replace_number_tags($lang['install_file_not_found'], array($db -> db_type))
                	)
                );
                return;
	        }
	
	        // requires this definition
	        define(PREFIX, $db -> table_prefix);
	        
	        // include it
	        $sql_schema = array();
	        
	        include(ROOT."install/default_data/".$db -> db_type."/".$schema_extra_path."db_schema.php");
	        
	        // Run through
			foreach($sql_schema['table'] as $tbl_name => $val)
			{

	            if($_POST['drop_table'])
	                $sql .= $sql_schema['table'][$tbl_name]['drop']."\r\n";
	
	            $sql .= $sql_schema['table'][$tbl_name]['create']."\r\n";
	            
			}
                
        }

        // ********************
        // If we're nabbing all tables
        // ********************
        if($_POST['table'] == "-1")
        {
        
                // Get all tables
                $db -> query("SHOW TABLE STATUS FROM `".$db->database_name."`");
        
                while($table_row = $db -> fetch_array())
                        $table_list[]  = $table_row['Name'];
                
        }
        else
                $table_list[]  = $_POST['table'];

        // ********************
        // Go through each table we want
        // ********************
        foreach($table_list as $table_name)
        {
        
                // Check it's got the tbl prefix if applicable
                if($db -> table_prefix != "")
                {
                        if(!preg_match("/^".$db->table_prefix."/", $table_name))
                                return;
                }

                // Get one table
                $db -> query("SELECT * FROM `".$table_name."`");
                
                while($row = $db -> fetch_assoc())
                {

                        $values = ""; $names = "";
                        
                        foreach($row as $key => $value)
                        {
                                $names .= " `".$key."`,";
                                $values .= " '".$db -> escape_string($value)."',";
                        }
                        
                        $names = _substr($names, 0, -1);
                        $values = _substr($values, 0, -1);
                
                        $sql .= "INSERT INTO `".$table_name."`(".$names.") VALUES(".$values.");\r\n";
                        
                }
        
        }

        // ********************
        // Output the file
        // ********************
        if($_POST['filename'] == '')                
                $filename = "backup_".date("d-m-y", TIME).".sql";
        else
                $filename = $_POST['filename'];
        
        output_file($sql, $filename, "text/x-sql");
        
}

?>
