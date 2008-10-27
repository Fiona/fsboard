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
*       Admin Output Class      *
*       Started by Fiona        *
*       08th Aug 2005           *
*********************************
*       Last edit by Fiona      *
*       25th Feb 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



class admin_output extends output
{

        var $theme_folder = '';
        
        var $breadcrumb = array();
        var $show_breadcrumb = true;  
        

        // --------------------------------------------------------------------------------

       
        // This finishes the output. It calls echo on everything and *DOESN'T* stop the script.
        function finish($use_debug = true)
        {

                global $template_admin, $cache, $db, $lang;

                // If we have debug messages in the admin area
				$debug_level_1 = "";
               	$debug_level_2 = "";
                                        
                if(defined('ADMINDEBUG') && $use_debug == true)
                {

                        // Level 1 Debug = Script time and query amount
                        if(!isset($cache -> cache['config']) || $cache -> cache['config']['debug'] >= 1)
                        {
                        
                                $script_execution_time = end_debug_timer();
                                $debug_level_1 = "[ ".$lang['script_time'].": <b>".$script_execution_time."</b> seconds ] [ ".$lang['sql_queries'].": <b>".$db -> num_query."</b> ]";
                                
                        }
                        
                        // Level 2 Debug = Query printing
                        if(!isset($cache -> cache['config']) || $cache -> cache['config']['debug'] >= 2)
                        {
                                
                                $debug_level_2 = "<div class=\"debug_level_2_wrapper\">";
                                
                                foreach($db -> saved_queries['queries'] as $key => $query_string)
                                {
                                
                                        $debug_level_2 .= "<p class=\"debug_level_2_entry\">".$query_string
                                        ."<br /> <span class=\"debug_level_2_extra\">".$db -> saved_queries['file'][$key]
                                        ." - Line ".$db -> saved_queries['line'][$key]
                                        ." - Time ".$db -> saved_queries['time'][$key]
                                        ."</span></p>";

                                        if($db -> saved_queries['errorno'][$key] > -1)
                                                $debug_level_2 .= "<p class=\"debug_level_2_error\"><b>Error ".$db -> saved_queries['errorno'][$key]."</b><br />".$db -> saved_queries['error'][$key]."</p>";
                        
                                }
                                
                                $debug_level_2 .= "</div>";
                        
                        }
                
                }
                
                // Sort the title out
                if($this -> page_title)
                        $title = $this -> page_title." - ".$cache -> cache['config']['board_name']." - ".$lang['admin_area_title'];
                else
                        $title = $cache -> cache['config']['board_name']." - ".$lang['admin_area_title'];

                // Breadcrummin''
                $breadcrumb = "";
                $a = 0;
                
                if(count($this -> breadcrumb) > 0 and $this -> show_breadcrumb)
                {
                        
                        foreach($this -> breadcrumb as $crumb)
                        {
                        
                                $a ++;
                                
                                if($a != count($this -> breadcrumb))
                                        $breadcrumb .= "<span class=\"breadcrumb_entry\"><a href=\"".ROOT."admin/".$crumb['url']."\" class=\"breadcrumb_link\">".$crumb['title']."</a></span> <span class=\"breadcrumb_seperator\">&gt;</span> ";
                                else
                                        $breadcrumb .= "<span class=\"breadcrumb_entry\"><b>".$crumb['title']."</b></span>";
                        
                        }
                        
                        $breadcrumb = "<div class=\"breadcrumb_wrapper\">".$breadcrumb."</div>";
                        
                }       
                         
                // Get the lot        
                $final_output = $template_admin -> page_wrapper($this -> theme_folder, $this -> page_output, $title, $breadcrumb, $debug_level_1, $debug_level_2);      
                
                echo $final_output;
        
        }


        // --------------------------------------------------------------------------------
        
        
        function add_breadcrumb($title, $url)
        {
                
                $this -> breadcrumb[] = array("title" => $title, "url" => $url);
                
        }       


        // --------------------------------------------------------------------------------
        
        
        function return_help_button($field = "", $text = false)
        {
        
                global $cache;

                $page = CURRENT_MODE;
                $action = $_GET['m2'];

                if(!$action)
                {
                        if(isset($cache -> cache['admin_area_help'][$page]['__yes__']))
                                return $this -> return_help_button_html($text, $page);
                        else
                                return "";
                }
                elseif(!$field)
                {
                        if(isset($cache -> cache['admin_area_help'][$page][$action]['__yes__']))
                                return $this -> return_help_button_html($text, $page, $action);
                        else
                                return "";
                }
                else
                {
                        if(isset($cache -> cache['admin_area_help'][$page][$action][$field]['__yes__']))
                                return $this -> return_help_button_html($text, $page, $action, $field);
                        else
                                return "";
                }
                
        }        



        // --------------------------------------------------------------------------------
        
        
        function return_help_button_html($text, $page, $action = "", $field = "")
        {
        
                global $lang;
                
                if($text)
                        $text = $lang['help_button_text']." ";
        
                return "<span class=\"adminhelpbutton\">
                                <a class=\"adminhelplink\" href=\"javascript:open_admin_area_help('".$page."', '".$action."', '".$field."');\">
                                        ".$text."
                                        <img src=\"".IMGDIR."/help.png\" border=0  style=\"vertical-align : middle;\" title=\"".$lang['help_button_text']."\" />
                                </a>
                        </span>";
        
        }
                 
}

?>