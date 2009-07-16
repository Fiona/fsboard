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
 * Plugin functions
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


/**
 * Builds the plugin code files by taking them from the DB
 * 
 * @param id $plugin_id The ID of the plugin to build
 * @param string $filename If we want a single file, put the name in here
 * @param bool $write_files False will stop the script actually writing anything
 * @return string The completed file or nothing depending on $write_files
 */
function build_plugin_files($plugin_id, $filename = "", $write_files = true)
{

	global $db;

    // If only one file
    if($filename)
    {

        // Get the plugin
		$db -> basic_select("plugins", "*", "id='".$plugin_id."'");
        $plugin_array = $db -> fetch_array();

        // Get the plugin file
		$hook = explode(":", $filename);
		$db -> basic_select("plugins_files", "*", "plugin_id='".$plugin_id."' and hook_file='".$hook[0]."' and hook_name='".$hook[1]."'");
        $array = $db -> fetch_array();

        // The file contents please
        $output_php = return_file_contents($plugin_array, $array);
        
        // Check if this is writing files or returning
        if($write_files)
        {

	        // Write the file
	        $fh = fopen(ROOT."plugins/plugin_id".$plugin_id."/".$hook[0]."_".$hook[1].".php", "w");
	        fwrite($fh, $output_php);
	        fclose($fh);
	
			@chmod(ROOT."plugins/plugin_id".$plugin_id."/".$hook[0]."_".$hook[1].".php", 0777);
        
        }
        else
	        return $output_php;
                        
    }
    else // All the files
    {

        // Get the plugin
		$db -> basic_select("plugins", "*", "id='".$plugin_id."'");
        $plugin_array = $db -> fetch_array();

        // Get the plugin files
		$db -> basic_select("plugins_files", "*", "plugin_id='".$plugin_id."'");
        
        if($db -> num_rows() < 1)
        	return;

		while($array = $db -> fetch_array())
		{

	        // The file contents please
	        $output_php = return_file_contents($plugin_array, $array);
	        
	        // Check if this is writing files or returning
	        if($write_files)
	        {
	
		        // Write the file
		        $fh = fopen(ROOT."plugins/plugin_id".$plugin_id."/".$array['hook_file']."_".$array['hook_name'].".php", "w");
		        fwrite($fh, $output_php);
		        fclose($fh);
		
				@chmod(ROOT."plugins/plugin_id".$plugin_id."/".$array['hook_file']."_".$array['hook_name'].".php", 0777);
	        
	        }

		}
			                	
	}

}



/**
 * Chucks back the contents for a single file based on information
 * 
 * @param array $plugin_array Information from the database about the plugin
 * @param array $array Information about the file
 * @return string The formatted file
 */
function return_file_contents($plugin_array, $array)
{
	
	global $PLUGIN_HOOKS;
	
	$function_name = $plugin_array['id']."_".$array['hook_file']."_".$array['hook_name'];
	$function_params = $PLUGIN_HOOKS[$array['hook_file']][$array['hook_name']]['params'];
	
	return "<"."?php
//FSBOARD GENERATED PLUGIN FILE
//DO NOT EDIT DIRECTLY

/*
  * Plugin: ".$plugin_array['name']."
  * Author: ".$plugin_array['author']."
  *
  * ".$array['summary']."         				
  */
  
if (!defined(\"FSBOARD\")) die(\"Script has not been initialised correctly! (FSBOARD not defined)\");

function p".$function_name."(".$function_params.")
{

".$array['code']."

}

?".">";	

}



//***********************************************
// Imports config settings from XML
//***********************************************
/**
 * Imports a plugin from an XML version of it
 * 
 * @param string $xml_contents the XML stored as a string read from a file
 * @param bool $ignore_version If tre the version of FSBoard will not be checked
 * @return bool On success
 */
function import_plugin_xml($xml_contents, $ignore_version = false)
{
        
        global $db;

        // Start parser
        $xml = new xml;

        $xml -> import_root_name = "fsboard_plugin";
        $xml -> import_group_name = "plugin";
        
        // Run parser and check version
        if($xml -> import_xml($xml_contents) == "VERSION" && !$ignore_version)
                return "VERSION";

        // Nothing?
        if(count($xml -> import_xml_values['plugin']) < 1)
                return true;
                
                
        // **********************
        // Get the plugin             
        // **********************
        var_show($xml -> import_xml_values);
        $plugin_files_array = $xml -> import_xml_values['plugin'][0];
        

        // Stick it in! So to speak.               
        $plugin_insert = array(
                'name'  		=> $plugin_files_array['ATTRS']['name'],
                'author' 		=> $plugin_files_array['ATTRS']['author'],
                'description' 	=> $plugin_files_array['ATTRS']['description']
        );
        
		if(!$db -> basic_insert("plugins", $plugin_insert))
			return false;               	

		$new_plugin_id = $db -> insert_id();

        // Obviously we have files
        if(count($plugin_files_array['plugin_file']))
	        foreach($plugin_files_array['plugin_file'] as $id => $file_array)
	        {
	
	                // Inseeeeeert
	                $plugin_file_insert = array(
	                        'summary'	=> $file_array['ATTRS']['summary'],
	                        'plugin_id'	=> $new_plugin_id,
	                        'hook_file'	=> $file_array['ATTRS']['hook_file'],
	                        'hook_name'	=> $file_array['ATTRS']['hook_name'],
	                        'code' 		=> trim($file_array['CONTENT'])
	                );
	                
	                if(!$db -> basic_insert("plugins_files", $plugin_file_insert))
	                	return false;
	                        
	        }
        
        // Directory
        if(!mkdir(ROOT."plugins/plugin_id".$new_plugin_id))
        	return false;

		@chmod(ROOT."plugins/plugin_id".$new_plugin_id, 0777);        	
        
        // Write the files
 		build_plugin_files($new_plugin_id);
 		        
        return true;

}

?>
