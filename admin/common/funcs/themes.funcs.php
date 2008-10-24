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
*       Admin Themes            *
*       Started by Fiona        *
*       09th Apr 2006           *
*********************************
*       Last edit by Fiona      *
*       09th Apr 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



//***********************************************
// Imports themes from XML
//***********************************************
function import_themes_xml($xml_contents, $ignore_version = false)
{
        
        global $db;

        // Start parser
        $xml = new xml;

        $xml -> import_root_name = "theme_file";
        $xml -> import_group_name = "theme";
        
        // Run parser and check version
        $parse_return = $xml -> import_xml($xml_contents, $ignore_version);

        if($parse_return == "VERSION" && !$ignore_version)
                return "VERSION";
        
        // Nothing?
        if(count($xml -> import_xml_values['theme']) < 1)
                return true;

        // **********************
        // Go through each theme               
        // **********************
        foreach($xml -> import_xml_values['theme'] as $theme)
        {

                // Inseeeeeert
                $theme_insert = array(
                        'name'            => $theme['ATTRS']['name'],
                        'css'             => $theme['theme_css'][0]['CONTENT'],
                        'author'          => $theme['ATTRS']['author'],
                        'image_dir'       => $theme['ATTRS']['image_dir']
                );
                                
                if($db -> basic_insert("themes", $theme_insert))
                {
                
                        // Log it!
                        if(!defined("INSTALL"))
                                log_admin_action("themes", "doimport", "Imported theme: ".trim($theme['name']));
                
                }
                else
                	return false;
                        
        }
        
        return true;
        
}


?>
