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
*       XML class               *
*       Started by Fiona        *
*       08th Apr 2005           *
*********************************
*       Last edit by Fiona      *
*       26th Feb 2007           *
*********************************


This pretty COOL class lets me make and parse XML files quickly.
*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


class xml
{

        // Full text for exporting
        var $export_xml = "";
        var $export_xml_middle = "";

        // Top of it
        var $export_xml_header = "";
        
        // Root tag text
        var $export_xml_root_name = "";
        var $export_xml_root_attrs = "";

        // Current group thing
        var $export_xml_group_name = "";
        var $export_xml_group_attrs = "";
        var $export_xml_group_entries = "";
        
        // Bool for checking if we're doing any XML generation
        var $generating = false;

        // Parser stuff
        var $import_fsboard_version = 0;
        var $import_parser_object = "";
        var $import_xml_values = array();
        var $import_root_name = "";
        var $import_group_name = "";
        var $import_current_group_ref = "";
        var $import_current_entry_ref = "";
        
        // ----------------------------------------------------
        // EXPORTING/GENERATING XML
        // ----------------------------------------------------
        
        
        
        //***********************
        // Export header
        //***********************
        function export_xml_start($version  = "1.0", $encoding = "ISO-8859-1")
        {
        
                $this -> export_xml_header = "<?xml version=\"".$version."\" encoding=\"".$encoding."\"?".">\r\n\r\n";

                $this -> generating = true;
                        
        }        


        //***********************
        // Generate root tag
        //***********************
        function export_xml_root($name, $attrs = "")
        {

                if(!$this -> generating)
                        return false;
        
                global $cache;
                
                $this -> export_xml_root_name = $name;

                $attr_array['fsboard_version'] = $cache -> cache['config']['current_version'];
                $attr_array['time'] = date("d-m-y g:i a", TIME);
                
                if(is_array($attrs))
                {
                
                        foreach($attrs as $key => $val)
                                $attr_array[$key] = $val;
                
                }

                $this -> export_xml_root_attrs = $this -> export_xml_generate_attributes($attr_array);
        
        }


        //***********************
        // Init a new group
        //***********************
        function export_xml_start_group($name, $attrs = "")
        {

                if(!$name || !$this -> generating)
                        return false;

                $this -> export_xml_group_name = $name;
                $this -> export_xml_group_attrs = $this -> export_xml_generate_attributes($attrs);
                $this -> export_xml_group_entries = array();
                
        }
        
        
        //***********************
        // Add an entry to the group
        //***********************
        function export_xml_add_group_entry($name, $attrs = "", $content = "", $escape_cdata = true)
        {

                if(!$name || !$this -> generating)
                        return false;

                $this -> export_xml_group_entries[] = array(
                                'NAME' => $name,
                                'ATTRS' => $this -> export_xml_generate_attributes($attrs),
                                'CONTENT' => ($escape_cdata) ? "<![CDATA[".$this -> xml_escape_cdata($content)."]]>" : $content
                );

        }
        
        
        //***********************
        // Generate a big group entry in the xml array
        //***********************
        function export_xml_generate_group()
        {
       
                if(!$this -> export_xml_group_name || !$this -> generating)
                        return false;

                $this -> export_xml_middle .= "\t<".$this -> export_xml_group_name.$this -> export_xml_group_attrs.">\r\n\r\n";

                if(is_array($this -> export_xml_group_entries))
                {
                
                        foreach($this -> export_xml_group_entries as $entry)
                                $this -> export_xml_middle .= "\t\t<".$entry['NAME'].$entry['ATTRS'].">".$entry['CONTENT']."</".$entry['NAME'].">\r\n";
                
                }

                $this -> export_xml_middle .= "\t</".$this -> export_xml_group_name.">\r\n";
                
        }

        
        //***********************
        // Add a single, non cdata entry
        //***********************
        function export_xml_add_single_entry($name, $attrs = "")
        {
       
                if(!$name || !$this -> generating)
                        return false;

                $this -> export_xml_middle .= "\t<".$name.$this -> export_xml_generate_attributes($attrs)." />\r\n";
                
        }   
             

        //***********************
        // Play with attributes
        //***********************
        function export_xml_generate_attributes($attr_array)
        {
        
                if(!is_array($attr_array) || !$this -> generating)
                        return false;

                $return_string = "";
                
                foreach($attr_array as $key => $val)
                        $return_string .= " ".$key."=\"".$val."\"";
                        
                return $return_string;
        
        }
        
        
        //***********************
        // Finish and complete making it
        //***********************
        function export_xml_generate()
        {
        
                if(!$this -> generating)
                        return false;
                        
                $this -> export_xml = $this -> export_xml_header;
                $this -> export_xml .= "<".$this -> export_xml_root_name.$this -> export_xml_root_attrs.">\r\n\r\n";
                $this -> export_xml .= $this -> export_xml_middle;
                $this -> export_xml .= "</".$this -> export_xml_root_name.">\r\n";

        }




        // ----------------------------------------------------
        // IMPORTING XML
        // ----------------------------------------------------
        
        
        
        
        //***********************
        // Main import function
        //***********************
        function import_xml($xml_contents, $ignore_version = false)
        {

                global $cache;

                if(!$xml_contents || !$this -> import_root_name || !$this -> import_group_name)
                        return false;

                $this -> import_xml_values = array();
        
                //create xml parser object
                $this -> import_parser_object = xml_parser_create(); 
                
                // set parser options
                xml_set_object($this -> import_parser_object, $this);
                xml_parser_set_option($this -> import_parser_object, XML_OPTION_CASE_FOLDING, 0); 
                xml_set_element_handler($this -> import_parser_object, "import_xml_parse_start", "import_xml_parse_end");
                xml_set_character_data_handler($this -> import_parser_object, "import_xml_parse_cdata");
                
                // parse it
                xml_parse($this -> import_parser_object, $xml_contents);
                
                // Close the parser
                xml_parser_free($this -> import_parser_object); 

                // **************************
                // Check the version is okay!
                // **************************
                if($ignore_version == false)
                	if($cache -> cache['config']['current_version'] < $this -> import_fsboard_version)
                        return("VERSION");

		}        


        //***********************
        // Parser start
        //***********************
        function import_xml_parse_start($parser, $name, $attrs)
        {
        
                switch($name)
                {
                
                        // -- Root --
                        case $this -> import_root_name:                        

                                $this -> import_fsboard_version = $attrs['fsboard_version'];
                                break;
                                                        

                        // -- Main Group --
                        case $this -> import_group_name:   
                                             
                                $this -> import_xml_values[$name][] = array();
                                $id = count($this -> import_xml_values[$name]) -1;
                                
                                $this -> import_current_group_ref =& $this -> import_xml_values[$name][$id];

                                if(is_array($attrs) && count($attrs))
                                {

                                	if(!isset($this -> import_current_group_ref['ATTRS']))
										$this -> import_current_group_ref['ATTRS'] = array();
										                                	
                                	foreach($attrs as $key => $val)
                                	{

                                		if(!isset($this -> import_current_group_ref['ATTRS'][$key]))
                                    		$this -> import_current_group_ref['ATTRS'][$key] = "";
                                    		
                                		$this -> import_current_group_ref['ATTRS'][$key] .= $val;
                                		
                                	}
                                        
                                }
                                
                                break;

                        // -- Other Entry --
                        default:                                

                                $this -> import_current_group_ref[$name][] = array();
                                $id = count($this -> import_current_group_ref[$name]) -1;

                                $this -> import_current_entry_ref =& $this -> import_current_group_ref[$name][$id];

                                if(is_array($attrs) && count($attrs))
                                {

                                	if(!isset($this -> import_current_entry_ref['ATTRS']))
											$this -> import_current_entry_ref['ATTRS'] = array();
											
                                    foreach($attrs as $key => $val)
                                    {

                                		if(!isset($this -> import_current_entry_ref['ATTRS'][$key]))
                                    		$this -> import_current_entry_ref['ATTRS'][$key] = "";
                                    		                                    	
                                    	$this -> import_current_entry_ref['ATTRS'][$key] .= $val;
                                    	
									}
                                        
                                }
                                                                
                }
                
        }


        //***********************
        // Dummy - not needed
        //***********************
        function import_xml_parse_end($parser, $name) { }
 

        //***********************
        // Grab data
        //***********************
        function import_xml_parse_cdata($parser, $data)
        {

        		if(!isset($this -> import_current_entry_ref['CONTENT']))
        			$this -> import_current_entry_ref['CONTENT'] = "";
        			
                $this -> import_current_entry_ref['CONTENT'] .= $this -> xml_unescape_cdata($data);
        
        }

 
        // ---------------------------------------------------
        // xml_escape_cdata()
        // Stops CDATA being nested in XML files
        //
        // Params - $xml = Text to be espaced
        // Returns - The escaped text
        // ---------------------------------------------------
        function xml_escape_cdata($xml)
        {
        
                $return = str_replace('<![CDATA[', '«![CDATA[', $xml);
                $return = str_replace(']]>', ']]»', $return);
                
                return $return;
                
        }
        
        // ---------------------------------------------------
        // xml_unescape_cdata()
        // Stops CDATA being nested in XML files
        //
        // Params - $xml = Text to be unespaced
        // Returns - The escaped text
        // ---------------------------------------------------
        function xml_unescape_cdata($xml)
        {
        
                $return = str_replace('«![CDATA[', '<![CDATA[', $xml);
                $return = str_replace(']]»', ']]>', $return);
                
                return $return;
                
        }        
        
}

?>
