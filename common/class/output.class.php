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
 * Output class
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * The output class is where all HTML to be output to the user
 * should be buffered. It also outputs debug information and 
 * handles redirections.
 */
class output
{

	/**
	 * The main page output buffer, everything to be placed in the
	 * content of the page is shoved in here and output with a wrapper later
	 */
	var $page_output = '';
	
	
	/**
	 * blocks are low level pieces of the page that are passed into the wrapper later
	 */
	var $page_blocks = array(
		"header" => "",
		"error_box" => "",
		"content" => "",
		"footer" => "",
	);

	
	/**
	 * Some pages may need to utilise a secondary buffer for
	 * inner parts of pages that will have a wrapper, this works as 
	 * such.
	 */
	var $buffer_2 = '';
        
	/**
	 * The ID of the currently used template set
	 */
	var $template_set_id = '';
        
	/**
	 * Contents of the current theme stylesheet
	 */
	var $stylesheet = '';
        
	/**
	 * Text that will be place in <title> tags
	 */
	var $page_title = '';        
	
	/**
	 * An array of strings of error messages, if there's anything in
	 * this they will be displayed at the top of the page in a list 
	 * inside an error box.
	 * Note that this is only for error displaying, it will not
	 * halt the script or anything similar.
	 *
	 * @var string
	 */
	 var $error_messages = array();
        
        
	/**
	 * Set the template set to be used
	 * 
	 * @param int $set_id The ID of the template set to change to
	 */
	function template_set($set_id)
	{
        
		global $config, $db, $cache;

		// Requesting the default?
		if($set_id == $cache -> cache['config']['default_template_set'])
			$this -> template_set_id = $cache -> cache['config']['default_template_set'];
		// Not default.. does the set even exist?                
		elseif(!is_dir(ROOT."templates/template_id".$set_id))
			$this -> template_set_id = $cache -> cache['config']['default_template_set'];
		// Not default and the set should exist.
		else
			$this -> template_set_id = $set_id;
                        
	}


	/**
	 * Add text to the main output buffer.
	 * 
	 * @param string $output_to_add Text to append to the buffer
	 * @param reference $buffer If wanting to add to an alternate buffer pass it here
	 */
	function add($output_to_add, &$buffer = false)
	{

        if($buffer === false)
        	$buffer = &$this -> page_output;
        	
		$buffer .= $output_to_add;
        
	}

        
	/**
	 * Builds the final page and outputs the contents of the buffer in whole to the user
	 * 
	 * @param string $final_output The text to throw out - usually main output buffer.
	 */
	function build_and_output()
	{

		global $cache, $template_global;

		// Put blocks together
		$this -> page_blocks['content'] = $this -> page_output;
		$this -> page_blocks['error_box'] = $this -> get_error_information();
       
		// Level 1 Debug = Query amount and Execution time
		if(
			$cache -> cache['config']['debug'] >= "1" &&
			((defined("ADMIN") && defined("ADMINDEBUG")) || !defined("ADMIN"))
		)
			$debug_level_1 = $this -> return_debug_level(1);
		else
			$debug_level_1 = "";

		// Level 2 Debug = Query printing
		if(
			$cache -> cache['config']['debug'] >= "2" &&
			((defined("ADMIN") && defined("ADMINDEBUG")) || !defined("ADMIN"))
		)
			$debug_level_2 = $this -> return_debug_level(2);
		else 
			$debug_level_2 = "";

		// Send final info to the wrapper
		$final_output =  $template_global -> global_wrapper(
        	$this -> page_title,
        	$this -> stylesheet,
        	CHARSET, 
        	$this -> page_blocks,
        	$debug_level_1,
        	$debug_level_2
		);

		// Built it up and output
		$buffer = 8192;
		
		$chars = strlen($final_output)-1;
		
		for($start = 0; $start <= $chars; $start += $buffer)
			echo substr($final_output, $start, $buffer);
		        
	}

        
	/**
	 * Redirects the current user to another page automatically
	 * 
	 * @param string $redirect_to URL to go to
	 * @param string $msg Message to display to the user on transit
	 */
	function redirect($redirect_to, $msg = '', $instant = False)
	{
        
		global $cache, $template_global;
                
		if(defined("ADMIN"))
			global $template_admin;

		$time = ($instant) ? 0 : $cache -> cache['config']['redirect_time'];
                
		$header = "";

		switch($cache -> cache['config']['redirect_type'])
		{
			// Meta Refresh                
			case '0':
				$header = "<meta http-equiv='refresh' content='".$time."; url=$redirect_to'>";
				break;
                                        
			// PHP header refresh
			case '1':
				if($time > 0)
					header("Refresh: ".$cache -> cache['config']['redirect_time']."; URL=".$redirect_to);        
				else	
					header("location: ".$redirect_to); 
                        
				break;
	
			// Jscript refresh
			case '2':
	
				$timeout = $time*1000;
	                
				$header = <<<END
	<script language="javascript" type="text/javascript">
		function redirect()
		{
			window.location.replace("{$redirect_to}");
		}
	                
		setTimeout("redirect();", {$timeout});
	</script>
END;
                
		}

		// Print redirect page
		$this -> page_output = ($template_global -> redirect($msg, $redirect_to, $header, $this -> stylesheet));
		$this -> build_and_output();

		if(defined("ADMIN"))
			$this -> show_breadcrumb = false;

		die();
        
	}


	/**
	 * Used for language strings with replacement variables. Takes input the and
	 * the replacements as an array.
	 *
	 * This function returns, it does not affect directly.
	 * 
	 * @param string $input_string Text with variable keys to replace. 
	 * 				i.e. "This is a title for <1>"
	 * 				will replace the first variable in the array with <1>
	 * @param array $replacements An array with the variables to replace
	 * @return string The string with all the replacements applied.
	 */
	function replace_number_tags($input_string, $replacements)
	{

		$not_array = false;
				
		if(!is_array($replacements))
			$not_array = true;

		if(!$not_array && count($replacements) < 1)
			return false;

		if($not_array)
			$input_string = str_replace("<1>", $replacements, $input_string);
		else                        
			foreach($replacements as $key => $value)
				$input_string = str_replace("<".($key+1).">", $value, $input_string);

		return $input_string;                
                
	}


	/**
	* Kick back the HTML for a set level.
	* 
	* @param int $level The level of debugging that you require.
	* 				Level 1: How long the script took to run and the amount of queries executed.
	* 				Level 2: Information of the queries themselves, where they were and how long they took. 
	* @return string The HTML representing the debug info.
	*/                
	function return_debug_level($level)
	{

		global $lang, $db;
				
		if($level == 1)
		{

			$script_execution_time = end_debug_timer();
			$return = "[ ".$lang['script_time'].": <b>".$script_execution_time."</b> seconds ] [ ".$lang['sql_queries'].": <b>".$db -> num_query."</b> ]";
 			
		}		
		elseif($level == 2)
		{

			if(!isset($db -> saved_queries['queries']))
				return false;
			
			if(count($db -> saved_queries['queries']) < 1)
				return false;
				
			$return = "<div class=\"debug_level_2_wrapper\">";
			
			foreach($db -> saved_queries['queries'] as $key => $query_string)
			{

				$explain_link = "";

				if($db -> saved_queries['explain'][$key] != NULL)
				{

					$items = "";

					foreach($db -> saved_queries['explain'][$key] as $explain_array)
					{

						$headers = "";
						$single_items = "";

						foreach($explain_array as $head_name => $val)
						{


							$headers .= "<th>".$head_name."</th>";
							$single_items .= "<td>".($val ? "<strong>".$val."</strong>" : "NULL")."</td>";

						}

						$items .= "<tr>".$single_items."</tr>";

					}

					$explain = "<table class=\"explain_table\"><tr>".$headers."</tr>".$items."</table>";

					$explain_link = " - <a href=\"#\" rel=\"explain\">Explain query</a>";

				}
				else
					$explain = "";

				$return .= "<p class=\"debug_level_2_entry\">".$query_string.
			        "<br /> <span class=\"debug_level_2_extra\">".$db -> saved_queries['file'][$key].
			        " - Line ".$db -> saved_queries['line'][$key].
			        " - Time ".$db -> saved_queries['time'][$key].
					$explain_link.
			        $explain.
					"</span></p>";

				if($db -> saved_queries['errorno'][$key] > -1)
				{
					$return .= "<p class=\"debug_level_2_error\"><b>Error ".$db -> saved_queries['errorno'][$key]."</b><br />".$db -> saved_queries['error'][$key]."</p>";
				die($return);
				}
			}
			
			$return .= "</div>";
			
		}
	
		return $return;
		
	}
	
	
	/**
	 * Adds another error to the global error message list.
	 *
	 * @param string $error_msg Text of the error.
	 */
	function set_error_message($error_msg)
	{
		
		$this -> error_messages[] = $error_msg;
		
	}
	
	
	/**
	 * Returns the error box for any page-wide errors
	 * if there are any.
	 * 
	 * @return string HTML of the error message.
	 */
	function get_error_information()
	{
				
		if(!count($this -> error_messages))
			return "";

		global $template_global;
			
		return $template_global -> page_error_box($this -> error_messages); 
		
	}
	
}

?>