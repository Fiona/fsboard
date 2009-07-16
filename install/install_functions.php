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
 * Install functions file
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Install
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


function step_2_check_file($filename)
{

        global $lang, $output, $install_step_fail;
        
        if(!file_exists(ROOT.$filename))
        {
                $output -> page_output .= $output -> install_step_action(false, $filename, $lang['step_2_does_not_exist']);
                $install_step_fail = true;
                return;
        }
        elseif(!is_writable(ROOT.$filename))
        {
                $output -> page_output .= $output -> install_step_action(false, $filename, $lang['step_2_not_writable']);
                $install_step_fail = true;
                return;
        }
        else
        {                        
                $output -> page_output .= $output -> install_step_action(true, $filename, $lang['step_2_ok']);
                return;
        }
        
}


function step_3_database_dropdown($db_type)
{
        
        global $main_form;
        
        $dropdown = array();
        
        $dh  = opendir(ROOT."db");        

        while(($filename = readdir($dh)) !== false)
        {

                if($filename=='.' || $filename=='..' || !is_dir(ROOT."db/".$filename))
                        continue;

				if(!file_exists(ROOT."db/".$filename."/database.class.php"))
					continue;
			
                $dropdown[] = $filename;

        }
                
        sort($dropdown);                

        return $main_form -> input_dropdown("sql_db_type", $db_type, $dropdown, $dropdown);
        
}


function step_4_error($message, $outcome, $mysql_error = false)
{

        global $output, $install_step_fail, $install_values;
                
        $output -> page_output .= $output -> install_step_action(false, $message, $outcome);
        
        if($mysql_error)
        	$output -> page_output .= $output -> mysql_error_box();
        
        $output -> page_output .= "<hr />";
        $output -> page_output .= $output -> install_step_3_form($install_values);                                        
        $install_step_fail = true;
                
}


function connect_to_database(&$db)
{

        global $output, $lang, $DB_CONFIG, $install_step_fail;

        // ---------------------
        // Check db_config is there
        // ---------------------
        if(!file_exists(ROOT."db_config.php"))
        {
                $output -> page_output .= $output -> install_step_action(false, $lang['db_connect_get_db_info'], $lang['db_connect_fail']);
                $output -> page_output .= "<hr />";
                $output -> page_output .= "<p>".$lang['db_connect_get_db_info_fail_message']."</p>";
                $install_step_fail = true;
                return false;
        }


        // ---------------------
        // Make sure DB config has right stuff in        
        // ---------------------
        $DB_CONFIG = array();
        @include ROOT.'db_config.php';
        
        if(!defined('DBCONFIG_PRESENT'))
        {
                $output -> page_output .= $output -> install_step_action(false, $lang['db_connect_get_db_info'], $lang['db_connect_fail']);
                $output -> page_output .= "<hr />";
                $output -> page_output .= "<p>".$lang['db_connect_get_db_info_fail_message']."</p>";
                $install_step_fail = true;
                return false;
        }


        // ---------------------
        // Include the db class file        
        // ---------------------
        $db_file = ROOT . "db/" . $DB_CONFIG['db type'] . "/database.class.php";
        if(!@include $db_file)
        {
                $output -> page_output .= $output -> install_step_action(false, $lang['db_connect_get_db_info'], $lang['db_connect_fail']);
                $output -> page_output .= "<hr />";
                $output -> page_output .= "<p>".$lang['db_connect_get_db_info_fail_message']."</p>";
                $install_step_fail = true;
                return false;
        }


        // ---------------------
        // First bit okay
        // ---------------------
        $output -> page_output .= $output -> install_step_action(true, $lang['db_connect_get_db_info'], $lang['db_connect_ok']);
        

        // ---------------------
        // Connect to database with our newfound info
        // ---------------------
        $db = new database;
        
        $db -> connect($DB_CONFIG['host'], $DB_CONFIG['database'], $DB_CONFIG['username'], $DB_CONFIG['password'], $DB_CONFIG['table prefix'], $DB_CONFIG['port']);

        if(!$db -> connection_link)
        {
                $output -> page_output .= $output -> install_step_action(false, $lang['db_connect_connect_db'], $lang['db_connect_fail']);
                $output -> page_output .= "<hr />";
                $output -> page_output .= "<p>".$lang['db_connect_connect_db_fail_message']."</p>";
                $install_step_fail = true;
                return false;
        }        


        // ---------------------
        // All done and connected!
        // ---------------------
        $output -> page_output .= $output -> install_step_action(true, $lang['db_connect_connect_db'], $lang['db_connect_ok']);

        return true;
                
}

?>
