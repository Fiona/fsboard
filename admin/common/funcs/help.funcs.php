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
 * Admin help functions
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 * 
 * @started 26 Feb 2007
 * @edited 26 Feb 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



/**
 * Quick function for creating the language variable name or an entry.
 * 
 * @param string $page Page that the help file is for.
 * @param string $action Action that the help file is for.
 * @param string $field Field that the help file is for.
 * @return string The completed variable name.
 */
function get_help_lang_var($page, $action, $field)
{

	$lang_var = $page;

	if($action)
		$lang_var = $lang_var."_".$action;

	if($field)
		$lang_var = $lang_var."_".$field;

	return $lang_var;
	
}


/**
 * Add the developer menu at the top of the help page
 */
function add_dev_menu()
{

	global $lang, $output;

	$table = new table_generate; 
	
	$row = "<a href=\"".ROOT."admin/index.php?m=help&amp;m2=devadd\">".$lang['help_dev_menu_add']."</a> - ".
		"<a href=\"".ROOT."admin/index.php?m=help&amp;m2=devview\">".$lang['help_dev_menu_edit']."</a> - ".
		"<a href=\"".ROOT."admin/index.php?m=help&amp;m2=devexport\">".$lang['help_dev_menu_export']."</a>";
	
	$output -> add(
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
		$table -> add_top_table_header($lang['help_dev_title']).
		$table -> add_basic_row($row, "normalcell").
		$table -> end_table()
	);

}

/**
 * Import help area info from XML (installer really) 
 */
function import_help_xml($xml_contents, $ignore_version = false)
{
        
        global $db;

        // Start parser
        $xml = new xml;

        $xml -> import_root_name = "adimn_help_file";
        $xml -> import_group_name = "help_entry";
        
        // Run parser and check version
        $parse_return = $xml -> import_xml($xml_contents, $ignore_version);

        if($parse_return == "VERSION" && !$ignore_version)
                return "VERSION";
        
        // Nothing?
        if(count($xml -> import_xml_values) < 1)
                return true;

        // **********************
        // Go through each entry              
        // **********************
        foreach($xml -> import_xml_values['help_entry'] as $help)
        {

                // Inseeeeeert
                $help_insert = array(
                        'page'		=> $help['ATTRS']['page'],
                        'action'	=> $help['ATTRS']['action'],
                        'field'		=> $help['ATTRS']['field'],
                        'order'		=> $help['ATTRS']['order']
                );
                                
                if(!$db -> basic_insert("admin_area_help", $help_insert))
	            	return false;
                        
        }
        
        return true;
        
}

?>