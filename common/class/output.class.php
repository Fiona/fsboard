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
*       Output Class            *
*       Started by Fiona        *
*       02nd Aug 2005           *
*********************************
*       Last edit by Fiona      *
*       19th Jan 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



class output
{

		/**
		 * The main page output buffer, most everything
		 * is shoved in here and output with a wrapper later
		 */
        var $page_output = '';
        
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
         * Set the template set to be used
         * 
         * @param int $set_id The ID of the template set to change to
         */
        function template_set($set_id)
        {
        
                global $config, $db, $cache;

                // secretly the default (ssh)
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
         * Outputs the contents of the buffer in whole to the user
         * 
         * @param string $final_output The text to throw out - usually main output buffer.
         */
        function finish($final_output)
        {
       
			$buffer = 8192;
			
			$chars = strlen($final_output)-1;
			
			for($start = 0; $start <= $chars; $start += $buffer)
				echo _substr($final_output, $start, $buffer);
			        
        }

        
        /**
         * Redirects the current user to another page automatically
         * 
         * @param string $redirect_to URL to go to
         * @param string $msg Message to display to the user on transit
         */
        function redirect($redirect_to, $msg = '')
        {
        
                global $cache, $template_global;
                
                if(defined("ADMIN"))
                	global $template_admin;
                
                switch($cache -> cache['config']['redirect_type'])
                {
	                // Meta Refresh                
	                case '0':
	                
	                        $header = "<meta http-equiv='refresh' content='".$cache -> cache['config']['redirect_time']."; url=$redirect_to'>";
	
	                        break;
	                                        
	                // PHP header refresh
	                case '1':
	
	                        if ($cache -> cache['config']['redirect_time'] > 0)
	                                header("Refresh: ".$cache -> cache['config']['redirect_time']."; URL=".$redirect_to);        
	                        else	
	                                header("location: ".$redirect_to); 
	                        
	                        break;
	
	                // Jscript refresh
	                case '2':
	
	                        $timeout = $cache -> cache['config']['redirect_time']*1000;
	                
	                        $header = "<script language=\"javascript\" type=\"text/javascript\">
	                        function redirect() {
	                            window.location.replace(\"$redirect_to\");
	                        }
	                
	                        setTimeout(\"redirect();\", $timeout);
	                        </script>";
                
                }

                // Print redirect page
                if(defined("ADMIN"))
                {
                	
	                $this -> show_breadcrumb = false;
	                
	                $this -> page_output = ($template_admin -> redirect($msg, $redirect_to, $header, $this -> stylesheet));                
	                $this -> finish();
                	
                }
                else
                	$this -> finish($template_global -> redirect($msg, $redirect_to, $header, $this -> stylesheet));                
                
                // Kill script
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
			
			        $return .= "<p class=\"debug_level_2_entry\">".$query_string
			        ."<br /> <span class=\"debug_level_2_extra\">".$db -> saved_queries['file'][$key]
			        ." - Line ".$db -> saved_queries['line'][$key]
			        ." - Time ".$db -> saved_queries['time'][$key]
			        ."</span></p>";

                                if($db -> saved_queries['errorno'][$key] > -1)
                                	$return .= "<p class=\"debug_level_2_error\"><b>Error ".$db -> saved_queries['errorno'][$key]."</b><br />".$db -> saved_queries['error'][$key]."</p>";
			
			}
			
			$return .= "</div>";
			
		}
	
		return $return;
		
	}
	
}

?>