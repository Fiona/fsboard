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
 * Global functions file
 * 
 * All the functions that pertain to the entire
 * project and not any specific area are placed here.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 * 
 * @started 02 Aug 2005
 * @edited 12 Jun 2007
 */

// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");




// ----------------------------------------------------------------------------------------------------------------------




/**
 * Wrapper for the PHP setcookie() function.
 * 
 * @param string $cookie_name Name of the cookie to write, is prefixed
 * with fsboard_(db table prefix).
 * @param string $value Data to write to the cookie.
 * @param bool $no_expire If set to true well expire after a year.
 * @param int $time_expire When $no_expire is not set, this can be set 
 * to a specific expire time.
 * @return bool If setcookie() worked okay.
 */
function fs_setcookie($cookie_name, $value = "", $no_expire = true, $time_expire = 0)
{

        global $db;
        
        // If we don't want to expire, set to expire after a year
        if($no_expire)
                $expire = TIME+(60*60*24*365);
        else
                $expire = TIME+$time_expire;

        // Put together the cookie name
        $cookie_name = "fsboard_".$db -> table_prefix.$cookie_name;

        // set the cookie and return the result
        if($time_expire == 0 && $no_expire == false)
        {
        
                if(@setcookie($cookie_name, $value, 0, "/"))
                        return true;
                else
                        return false;                 

        }
        else        
        {

                if(@setcookie($cookie_name, $value, $expire, "/"))
                        return true;
                else
                        return false;    
                                
        }
        
}



/**
 * Includes the template name asked for, or generates it with code
 * if DATABASETEMPLATES is defined.
 * 
 * @param string $name Class name of the template we want 
 * @return class Pointer to a new instance of the template. 
 */
function load_template_class($name)
{

        global $db, $output, $cache;
        
        // If we want to include from files (Normal)
        if(!defined("DATABASETEMPLATES"))
                require ROOT."templates/template_id".$cache -> cache['config']['default_template_set']."/".$name.".php";

        // If we want to get from database (Debug Purposes)
        else
        {

		require_once(ROOT."admin/common/funcs/templates.funcs.php");
		
                // Grab the ones we want
                $get_templates = $db -> query("select function_name, text, parameters from ".$db -> table_prefix."templates where class_name='".$name."' and set_id = '".$output -> template_set_id."'");

                // Start the PHP, class name definition
                $class_code = "class ".$name." {\n";

                // Go through each one
                while($template_array = $db -> fetch_array($get_templates))        
                	$class_code .= return_function_call($template_array);
                
                // Closing class bracket
                $class_code .= "}";
                
                // Run the code
                eval($class_code);
                
        }
        
        // Return a pointer to the wanted class
        return new $name();

}


/**
 * Includes the language group asked for, or generates it with code
 * if DATABASELANGUAGES is defined.
 * 
 * @param string $name Class name of the template we want 
 * @return class Pointer to a new instance of the template. 
 */
function load_language_group($name)
{

        global $db, $output, $cache, $lang;
        
        // If we want to include from files (Normal)
        if(!defined("DATABASELANGUAGES"))
                require ROOT."languages/".LANG."/".$name.".php"; 

        // If we want to get from database (Debug Purposes)
        else
        {

		require_once(ROOT."admin/common/funcs/languages.funcs.php");
		
                // Get werds
                $phrases_query = $db -> query("select * from ".$db -> table_prefix."language_phrases where language_id='".LANG_ID."' and `group`='".$name."' order by variable_name");

                $output_php = "";
                
                // Go through 'em all
                while($phrase_array = $db -> fetch_array($phrases_query))
                {
                        $text = str_replace('"', '\"', $phrase_array['text']);
                        $output_php .= return_variable_entry($phrase_array['variable_name'], $text);
                }
                
                $final_php = $output_php;

                // Run the code
                eval($final_php);
                
        }

}


/**
 * Sets various constants for time and date.
 */
function set_time_variables()
{

        global $cache;

        /**
         * Just the server time, stops us having to call the time()
         * function again and again.
         */
        define('TIME', time());
        
        // Offsetted version (I don't think I use this...)
        $offset = $cache -> cache['config']['time_offset'];
        if($cache -> cache['config']['dst_on'])
                $offset ++;

        /**
         * Current timestamp on the server, but with the 
         * offset and dst settings appended.
         */                        
        define('TIMEOFFSET', time()+($offset * 3600));
        
}



/**
 * Formats and date and also adds the server offset stuff.
 * 
 * @param string $format Format of the date, uses same as PHP date(). 
 * @param int $timestamp Time stamp to base it on. Uses server time by default.
 * @return string The correctly formatted date.
 */
function return_formatted_date($format, $timestamp = TIME)
{

        global $cache;

        // Sort out timezone offsets
        $offset = $cache -> cache['config']['time_offset'];        
        if($cache -> cache['config']['dst_on'])
                $offset ++;
                
        $timestamp = intval($timestamp);

        // Send it back
        return gmdate($format, $timestamp + ($offset * 3600));

}



/**
 * Starts a timer that when used with end_debug_timer() will return
 * the time it took to execute the script in miliseconds.
 */
function start_debug_timer()
{

        global $start_time;

        $micro_time = explode(' ', microtime());
        $start_time = $micro_time[1] + $micro_time[0];

}

/**
 * Ends the timer started by start_debug_timer(). 
 * 
 * @return float Execution time in miliseconds rounded to nearest 5 dp.
 */
function end_debug_timer()
{

        global $start_time;

        $micro_time = explode(' ', microtime());
        $end_time = $micro_time[1] + $micro_time[0];

        return round(($end_time - $start_time), 5);

}


/**
 * Returns a link relative to the current URL if clean urls is on, otherwise
 * gives back a url with a query string appended.
 * 
 * @param string $path Path to something on the board we want, relative
 * 		to board root and without following slash.
 * @param bool $force_friendly_url Setting this to true will force l() to return
 *      a path for a friendly URL, ignoring any settings.
 * @return string Correct path.
 */
function l($path, $force_friendly_url = False)
{
	
	global $cache;

	$relative_path = parse_url($cache -> cache['config']['board_url'], PHP_URL_PATH);
	$relative_path = ($relative_path[0] != "/") ? "/".$relative_path : $relative_path;

	// Check for being in the admin area
	if(substr($path, 0, 6) == "admin/")
	{
		$relative_path .= "/admin";
		$path = substr($path, 6);
	}

	if($force_friendly_url || $cache -> cache['config']['clean_urls'])
		return $relative_path."/".$path;
	else
		return $relative_path."/?q=".$path;
	
}



/**
 * Returns a link to an image in the current theme.
 * 
 * @param $path Image filename we want.
 * @param string Correct path.
 */
function img($path)
{
	
	global $cache;
	return IMGDIR."/".$path;
		
}


/**
 * Takes something (anything) that has been input by the user and
 * sanitises it. It escapes HTML and runs the word filter on it.
 *
 * @param $input The input that will be escaped.
 * @return string
 */
function sanitise_user_input($input)
{

	$input = htmlentities($input);

	// TODO: Word filter

	return $input;

}


/**
 * Return the current IP being used to browse the script.
 * 
 * @author Found on php.net. Edited by Fiona.
 * @return mixed The IP address, or false if it could not be detected.
 */
function user_ip()
{

        global $_SERVER;

        if(array_key_exists('REMOTE_ADDR', $_SERVER))
                $ip = $_SERVER['REMOTE_ADDR'];
        else
                $ip = false;

        if(array_key_exists('HTTP_CLIENT_IP', $_SERVER))
                $ip = $_SERVER['HTTP_CLIENT_IP'];

        if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip))
                $ip = false;

        return $ip;
        
}



/**
 * Opposite of PHP strrchr(). Get's everything in in the haystack that comes
 * AFTER the needle.
 * 
 * @param string $haystack Text to search.
 * @param string $needle Text to search for.
 * @return string Text in haystack that comes after needle.
 */
function reverse_strrchr($haystack, $needle)
{

        $pos = _strrpos($haystack, $needle);
        
        if($pos === false)
                return $haystack;
        
        return _substr($haystack, 0, $pos + 1);
   
}

/**
 * Utility function for sorting a 2 dimensional array by one of the members.
 * Affects the array directly.
 *
 * @param array Array we wish to sort
 * @param string $member_name Key of the member we wish to sort by
 */
function sort_array_by_member(&$array, $member_name)
{

	$code = 'if($a[\''.$member_name.'\'] < $b[\''.$member_name.'\'])
		return -1;
	elseif($a[\''.$member_name.'\'] == $b[\''.$member_name.'\'])
		return 0;
	return 1;';
	
	uasort($array, create_function('$a, $b', $code));
	
}


/**
 * Utility that takes variable parameters and returns a bool if they're
 * all equal to each other
 * 
 * @param mixed $params Variable parameters to check
 * @return bool True/False on success
 */
function check_equals()
{
	
	for($i = 0; $i < func_num_args(); $i++)
		if(func_get_arg($i) != func_get_arg(0))
			return false;		
	
	return true;
		
}

/**
 * Outputs the data given as a file for the user to download and ends the script.
 * 
 * @param string $file_contents Potentially large string contaning all
 *  the data that the file will include.
 * @param string $filename Target name of the file that will be outputted.
 * @param bool $content_type MIME type of the file.
 */
function output_file($file_contents, $filename, $content_type)
{

        header('Content-Type: '.$content_type);
        header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Content-Length: '.strlen($file_contents));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        echo $file_contents;
        
        die();
        
}



/**
 * Checks the cache to see if a plugin needs to load and does it if so.
 * 
 * @param string $hook_file The page that needs checking.
 * @param string $hook_name The name of the hook
 * @return bool On failure.
 */
function run_plugins($hook_file, $hook_name)
{

    global $cache, $db, $user, $output, $lang, $PLUGIN_HOOKS;

	// Does the plugin exist?
	if(!isset($PLUGIN_HOOKS[$hook_file][$hook_name]) || !isset($cache -> cache['plugins'][$hook_file.":".$hook_name]))
		return false;

    // We have some?
    if(is_array($cache -> cache['plugins'][$hook_file.":".$hook_name]) && count($cache -> cache['plugins'][$hook_file.":".$hook_name]) > 0)
    {
    	
		foreach($cache -> cache['plugins'][$hook_file.":".$hook_name] as $plugin_id)
		{
			
			if(file_exists(ROOT."plugins/plugin_id".$plugin_id."/".$hook_file."_".$hook_name.".php"))
			{
				
				include ROOT."plugins/plugin_id".$plugin_id."/".$hook_file."_".$hook_name.".php";

				$var_param = array();
				$params_string = array();
				
				for($i = 2; $i < func_num_args(); $i++)
				{
					
					$var_param[$i] = func_get_arg($i);
					
					$params_string[] = '$var_param['.$i.']';
					
				}
				
				eval("p".$plugin_id."_".$hook_file."_".$hook_name."(".implode(", ", $params_string).");");
				
			}
		}

    }

	return true;
	
}


/**
 * This function sholud be called directly before deleting something.
 * It will save the data in the undelete table.
 *
 * @param String $table_name The table name that we're saving data from - sans prefix
 * @param String $action Short string describing the action that was taken.
 * @param Mixed $data If this is a string, it's used as a where query for getting the current data,
 * 		If it is an associative array, the data is serialised and saved normally.
 * 		Otherwise it is assumed to be a query resource, in which case it is iterated though, and the data saved.
 */
function save_undelete_data($table_name, $action, $data, $extra_query = array())
{

	global $db;
	
	$always_insert = array("table" => $table_name, "action" => $action, "time" => TIME);
	$final_data = array();
	
	if(is_array($data) && count($data) > 0)
		$final_data[] = array_merge($always_insert, array("data" => serialize($data)));
	elseif(is_string($data))
	{
	
		$query_opts = array(
			"table" => $table_name,
			"where" => $data
		);
		
		$query_opts = array_merge($query_opts, $extra_query);
		
		$db -> basic_select($query_opts);
		
		if(!$db -> num_rows())
			return;
		
		while($guy = $db -> fetch_assoc())
			$final_data[] = array_merge($always_insert, array("data" => serialize($guy)));
			
	}
	else
	{
		
		if(!$db -> num_rows($data))
			return;
			
		$db -> data_seek($data, 0);
		
		while($guy = $db -> fetch_assoc($data))
			$final_data[] = array_merge($always_insert, array("data" => serialize($guy)));
		
	}
	
	if(count($final_data) < 1)
		return;
		
	$db -> basic_insert(
		array(
			"table" => "undelete",
			"data" => $final_data,
			"multiple_inserts" => True
		)
	);
	
}

/**
 * Our error handler callback
 */
function error_handler($errno, $errstr, $errfile, $errline)
{

	$debug = "";
	$a = 0;

	$backtrace = debug_backtrace();

	foreach($backtrace as $index => $info)
	{

		$a++;

		if($a == 1)
			continue;

		$info['file'] = isset($info['file']) ? $info['file']."
			&nbsp;&nbsp;&nbsp;&nbsp; " : "";
		$info['line'] = isset($info['line']) ? "(line {$info['line']})" : "";
		$info['function'] = isset($info['function']) ?
			"&nbsp;&nbsp;&nbsp;&nbsp; ".$info['function'] : "";

		$debug .= <<<END
		<div style="border: 1px solid #111; background: #555; color : #fff;
			padding : 3px; margin : 3px; font-size : 10px;">
			{$info['file']} {$info['line']} {$info['function']}
		</div>
END;

	}

	switch($errno)
	{
		case E_USER_ERROR:
			$error_type = "(Fatal error)";
			break;

		case E_USER_WARNING:
			$error_type = "(Warning)";
			break;

		case E_USER_NOTICE:
			$error_type = "(Notice)";
			break;

		default:
			$error_type = "";
			break;
	}

	echo <<<END
		<div style="border: 1px solid #111; background: #333; color : #fff;
			padding : 5px; margin : 5px;">
			Error: <b>{$error_type}</b> ({$errno}) {$errstr}<br />
			Line <b>{$errline}</b> in <b>{$errfile}</b>
			{$debug}
		</div>
END;

	return true;

}


/**
 * SHUTDOWN FUNCTION
 * 
 * Does all of the stuff we want to do when the main script has 
 * finished doing everything else.
 */
function shutdown_tasks()
{

        global $db, $cache;
        
        // This incredible hack is due to Apache being fucking stupid
        // when it comes to shutdown tasks and making the cwd random.
        // Yes it will probably fall apart on different server software.
        // That's what testing is for.
        $include_root = str_replace(
	        	array("admin/index.php", "index.php"),
	        	array("", ""),
	        	$_SERVER['DOCUMENT_ROOT'].$_SERVER['PHP_SELF']
	        );
        
        // Run common tasks
        task_check_run($include_root);

        // Got mass mails waiting?
        if($cache -> cache['config']['mass_mailer_waiting'])
        {

                include_once $include_root."admin/common/funcs/mailer.funcs.php"; // Need this.
                send_waiting_mails();

        }

        // Run shutdown queries
        if(count($db -> shutdown_queries) > 0)
                foreach($db -> shutdown_queries as $query)
                        $db -> query($query);

}



/**
 * SHUTDOWN FUNCTION
 * 
 * Used by shutdown_tasks(). It checks if we have a common task to 
 * run and does it accordingly.
 * 
 * @return bool True if a task was found and ran correctly, otherwise false.
 */
function task_check_run($include_root = "./")
{

        global $cache, $db, $common_task_log;
       
        // Got one to run?
        if($cache -> cache['config']['next_task_runtime'] <= TIME && $cache -> cache['config']['next_task_runtime'] != -1)
        {

                // **************************
                // Select the right task
                // **************************
                $db -> query("SELECT * FROM ".$db->table_prefix."tasks WHERE enabled='1' ORDER BY next_runtime ASC LIMIT 1");
                $task_array = $db -> fetch_array();
        
                // **************************
                // Check we have one
                // **************************
                if(!$task_array)
                        return false;        

                // **************************
                // Update next runtime
                // **************************
                include_once $include_root."admin/common/funcs/tasks.funcs.php"; // Need this.

                $edit_info['next_runtime'] = get_next_run_date($task_array);
        
                $db -> basic_update("tasks", $edit_info, "id='".$task_array['id']."'");
        
                // **************************
                // Run it
                // **************************
                $common_task_log = "";
                
                if(file_exists($include_root.$task_array['task_filepath']))
                        include $include_root.$task_array['task_filepath'];
                else
                        return false;

                // **************************
                // Save log
                // **************************
                if($task_array['keep_log'])
                        log_task_run($task_array['id'], $task_array['task_name'], $common_task_log);
        
                // ***************************
                // Save next task run in DB
                // ***************************
                config_save_next_run($include_root);
        
        }
        
        return true;

}


/**
 * UTF-8 Friendly replacement for strlen()
 * Original code by Niels Leenheer & Andy Matsubara, released under GPL.
 * 
 * @param string $string The string to count characters from.
 * @return int The amount of characters found.
 */
function _strlen($string)
{
	
	return preg_match_all("/[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]/", $string, $dummy);

}
 
 
/**
 * UTF-8 Friendly replacement for substr()
 * Original code by Niels Leenheer & Andy Matsubara, released under GPL.
 * 
 * @param string $string The string to cut characters out of.
 * @param int $start Character number to start cutting from.
 * @param int $length Amount of characters to cut. 
 * @return int The amount of characters found.
 */
function _substr($string, $start, $length = NULL)
{

	preg_match_all('/[\x01-\x7F]|[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]/', $string, $array);

	if(is_int($length))
		return implode('', array_slice($array[0], $start, $length));
	else
		return implode('', array_slice($array[0], $start));

}


/**      
 * UTF-8 Friendly replacement for strpos()
 * Original code by Niels Leenheer & Andy Matsubara, released under GPL.
 * 
 * @param string $string The string to search in.
 * @param string $needle Character string to search for. 
 * @param int $offset Which character to start searching from.
 * @return mixed False if not there. Numeric position of first occurence if is.
 */
function _strpos($haystack, $needle, $offset = 0)
{
	
	$comp = 0;
	
	while(!isset($length) || $length < $offset) 
	{

		$position = strpos($haystack, $needle, $offset + $comp);
		
		if($position === false)
			return false;
				
		$length = _strlen(substr($haystack, 0, $position));
		
		if($length < $offset)
			$comp = $position - $length;
		
	}
	
	return $length;

}

        
/**
 * UTF-8 Friendly replacement for strrpos()
 * Original code by Niels Leenheer & Andy Matsubara, released under GPL.
 * 
 * @param string $string The string to search in.
 * @param string $needle Character string to search for. 
 * @return mixed False if not there. Numeric position of last occurence if is.
 */
function _strrpos($haystack, $needle)
{

	$pos = strrpos($haystack, $needle);
	
	if($pos === false) 
		return false;
	else
		return _strlen(substr($haystack, 0, $pos));

}
        

/**
 * _strtolower() and _strtoupper() need some big arrays to
 * support case change in other languages than english
 */
require(ROOT."common/utf8_tables.php");       
        

/**
 * UTF-8 Friendly replacement for strtolower()
 * Original code by Niels Leenheer & Andy Matsubara, released under GPL.
 * 
 * @param string $string The string to change to lowercase.
 * @return string Lower case version of the string.
 */
function _strtolower($str)
{

	global $UTF8_TABLES;
	return strtr($str, $UTF8_TABLES['strtolower']);

}


/**
 * UTF-8 Friendly replacement for strtoupper()
 * Original code by Niels Leenheer & Andy Matsubara, released under GPL.
 * 
 * @param string $string The string to change to uppercase.
 * @return string Upper case version of the string.
 */
function _strtoupper($str)
{

	global $UTF8_TABLES;
	return strtr($str, $UTF8_TABLES['strtoupper']);

}
        

/**
 * UTF-8 Friendly replacement for htmlentities()
 * 
 * @param string $string The string to replaced.
 * @return string HTML entity replaced string.
 */
function _htmlentities($string)
{
	
	return htmlentities($string, ENT_COMPAT, "UTF-8");

}
        

/**
 * UTF-8 Friendly replacement for html_entity_decode()
 * 
 * @param string $string The string to replaced.
 * @return string HTML entity replaced string.
 */
function _html_entity_decode($string)
{
	
	return html_entity_decode($string, ENT_COMPAT, "UTF-8");

}
      
      
/**
 * UTF-8 Friendly replacement for htmlspecialchars()
 * 
 * @param string $string The string to replaced.
 * @return string HTML entity replaced string.
 */
function _htmlspecialchars($string)
{
	
	return htmlspecialchars($string, ENT_COMPAT, "UTF-8");

}
              

if(!function_exists("htmlspecialchars_decode"))
{

	/**
	 * PHP4 compatible htmlspecialchars_decode
	 * 
	 * @param string $string The string to replaced.
	 * @param int $quote_style Quote style.
	 * @return string HTML entity replaced string.
	 */
	function htmlspecialchars_decode($string, $quote_style = ENT_COMPAT)
	{
		
		return strtr($string, array_flip(get_html_translation_table(HTML_SPECIALCHARS, $quote_style)));
	
	}

}


/**
 * Just a handly little debug function yo.
 * Prints any amount of information from parameters given to it.
 */
function var_show()
{

	echo "<pre>";

	for($i = 1; $i <= func_num_args(); $i++)
	{
		
		echo $i.": <b>";
		print_r(func_get_arg($i-1));
		echo "</b><br />";
		
	}
	
	echo "</pre>";
	
} 
 
?>
