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
*          FUNCTIONS            *
*       Admin Common Tasks      *
*       Started by Fiona        *
*       12th Mar 2006           *
*********************************
*       Last edit by Fiona      *
*       06th Feb 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");




//***********************************************
// get_next_run_date()
// Chucks back the timestamp for the next time the given task runs
//
// Params  - $task_info - array of stuff with run time params
// Returns - Timestamp of next run
//***********************************************
function get_next_run_date($task_info)
{

        // Get current time stuff
        $date = array();
        $run_date = array();
        
        $run_date['minute']     = $date['minute']   = gmdate("i", TIME);
        $run_date['hour']       = $date['hour']     = gmdate("H", TIME);
        $run_date['day']        = $date['week_day'] = gmdate("w", TIME);
        $run_date['month']      = $date['month']    = gmdate("m", TIME);
        $run_date['year']       = $date['year']     = gmdate("Y", TIME);
        $date['month_day']      = gmdate("d", TIME);

        // Check what we're doing regarding these
        $have_day = ($task_info['week_day'] == -1 && $task_info['month_day'] == -1) ? 0 : 1;
        $have_min = ($task_info['minute'] == -1) ? 0 : 1;

        // ************************
        // Work out day
        // ************************
        if($task_info['week_day'] == -1)
        {
                if($task_info['month_day'] != -1)
                {
                        $run_date['day'] = $task_info['month_day'];
                        $day_plus = "m";
                }
                else
                {
                        $run_date['day'] = $date['month_day'];
                        $day_plus = "-1";
                }
        }
        else
        {
                // Next week day
                $run_date['day'] = $date['month_day'] + ($task_info['week_day'] - $date['week_day']);
                $day_plus = "w";
        }

        // ************************
        // If worked out day is before todays date. Need to compensate
        // ************************
        if($run_date['day'] < $date['month_day'])
        {
                switch($day_plus)
                {
                        case 'm':
                                add_month($run_date);
                                break;
                        case 'w':
                                add_day($run_date, 7);
                                break;
                        default:
                                add_day($run_date);
                }
        }

        // ************************
        // Work out hour
        // ************************
        if($task_info['hour'] == -1)
                $run_date['hour'] = $date['hour'];
        else
        {

                // Every hour?                
                if(!$have_day && !$have_min)
                        add_hour($run_date, $task_info['hour']);
                else
                        $run_date['hour'] = $task_info['hour'];

        }                          

        // ************************
        // Work out minute
        // ************************
        if($task_info['minute'] == -1)
                add_minute($run_date);
        else
        {

                if($task_info['hour'] == -1 && !$have_day)
                        add_minute($run_date, $task_info['minute']);
                else
                        $run_date['minute'] = $task_info['minute'];

        }

        // ************************
        // Work out hour
        // ************************
        if($run_date['hour'] <= $date['hour'] && $run_date['day'] == $date['month_day'])
        {
        
                if($task_info['hour'] == -1)
                {
                
                        // Doing it every hour                        
                        if($run_date['hour'] == $date['hour'] && $run_date['minute'] <= $date['minute'])
                                 add_hour($run_date);
                                 
                }
                else
                {

                        // Set amount of hours
                        if(!$have_day && !$have_min)
                                add_hour($run_date, $task_info['hour']);
                        elseif(!$have_day)
				add_day($run_date);
                        else
                        {

                                switch($day_plus)
                                {
                                        case 'm':
                                                add_month($run_date);
                                                break;
                                        case 'w':
                                                add_day($run_date, 7);
                                                break;
                                        default:
                                                add_day($run_date);
                                }

                         }
                         
                 }
                 
        }


        // ************************
        // FINISHED give it back plz
        // ************************
        return gmmktime($run_date['hour'], $run_date['minute'], 0, $run_date['month'], $run_date['day'], $run_date['year']);
                
}


        
//***********************************************
// add_month()
// Throws a month on when working out next date
//
// Params  - $run_date - array of stuff with run time params
// Returns - Nothing - affects directly
//***********************************************
function add_month(&$run_date)
{

        if(gmdate("m", TIME) == 12)
        {
                $run_date['month'] = 1;
                $run_date['year']++;
        }
        else
                $run_date['month']++;
        
}

//***********************************************
// add_month()
// Adds a day or two on when werking out date
//
// Params  -    $run_date - array of stuff with run time params
//              $days - How many days we wanna add
// Returns - Nothing - affects directly
//***********************************************
function add_day(&$run_date, $days = 1)
{

        $month_day = gmdate("d", TIME);
        $days_in_month = gmdate("t", TIME);
        
        if($month_day >= ($days_in_month - $days))
        {
                $run_date['day'] = ($month_day + $days) - $days_in_month;
                add_month($run_date);
        }
        else
                $run_date['day'] += $days;

}


//***********************************************
// add_hour()
// Adds however many hours we want on when werking out date
//
// Params  -    $run_date - array of stuff with run time params
//              $hours - How many hours we wanna add
// Returns - Nothing - affects directly
//***********************************************
function add_hour(&$run_date, $hours = 1)
{

        $hour_date = gmdate("H", TIME);

        if($hour_date >= (24 - $hours))
        {
                $run_date['hour'] = ($hour_date + $hours) - 24;
                add_day($run_date);
        }
        else
                $run_date['hour'] += $hours;

}


//***********************************************
// add_minute()
// Adds however many mins we want on when werking out date
//
// Params  -    $run_date - array of stuff with run time params
//              $minutes - How many mins we wanna add
// Returns - Nothing - affects directly
//***********************************************
function add_minute(&$run_date, $minutes = 1)
{

        $minute_date = gmdate("i", TIME);

        if($minute_date >= (60 - $minutes))
        {
                $run_date['minute'] = ($minute_date + $minutes) - 60;
                add_hour($run_date);
        }
        else
                $run_date['minute'] += $minutes;
        
}


//***********************************************
// config_save_next_run()
// Works out the time of the next task run and saves to config table
//
// Params  - None
// Returns - Nothing
//***********************************************
function config_save_next_run($include_root = ROOT)
{

        global $db, $cache;
        
        // Select next run task
        $db -> query("SELECT next_runtime FROM ".$db->table_prefix."tasks WHERE enabled='1' ORDER BY next_runtime ASC LIMIT 1");
        $next_run = $db -> result();

        // Check we have one
        if(!$next_run)
                $save_runtime = "-1";
        else
                $save_runtime = $next_run;

        // Save it
	$db -> basic_update("config", array('value' => $save_runtime), "name='next_task_runtime'");
        
        // Update cache
        $cache -> cache['config']['next_task_runtime'] = $save_runtime;
        $cache -> update_single_cache("config", $include_root); 
                
}


//***********************************************
// log_task_run()
// Logs a task that was run and saves it to the DB on shutdown.
//
// Params  - None
// Returns - true on success.
//***********************************************
function log_task_run($task_id, $task_name, $action)
{

        global $db;
                
        // Log it.
        if($db -> save_shutdown_query(
                $db -> basic_insert("task_logs",
                	array(
                                "task_id" 	=> $task_id,
                                "task_name" 	=> $task_name,
                                "action"	=> $action,
                        	"date"		=> TIME,
                                "ip" 		=> user_ip()
                        ),
                true)
        ))
                return true;
        else
                return false;
 
}

?>
