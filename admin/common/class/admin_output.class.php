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
        
        
        function add_breadcrumb($title, $url)
        {
                
                $this -> breadcrumb[] = array("title" => $title, "url" => $url);
                
        }       


        // --------------------------------------------------------------------------------
        
        
        function return_help_button($field = "", $text = false)
        {
        
                global $cache, $page_matches;

                $page = CURRENT_MODE;
                $action = isset($page_matches['mode']) ? $page_matches['mode'] : "";

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