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
 * Admin language related functions
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
 * Select all languages.
 *
 * @return array An array of langs, with ids against info
 */
function languages_get_languages()
{

	global $db;

	$langs = array();

	$db -> basic_select(
		array(
			"table" => "languages",
			"order" => "name", 
			"direction" => "asc"
			)
		);

	if(!$db -> num_rows())
		return $langs;

	while($l_array = $db -> fetch_array())
		$langs[$l_array['id']] = $l_array;

	return $langs;

}


//***********************************************
// Builds the language files by taking them from the DB
// Will build one file if $group_name is defined
// Will not write any files if $write_files is false
//***********************************************
function build_language_files($lang_id, $group_name = false, $write_files = true)
{

	global $db;

	// If only one group
	if($group_name)
	{

		// Get werds
		$phrases_query = $db -> query("select * from ".$db -> table_prefix."language_phrases where language_id='".$lang_id."' and `group`='".$group_name."' order by variable_name");

		$output_php = "";
                
		// Go through 'em all
		while($phrase_array = $db -> fetch_array($phrases_query))
		{
			$text = str_replace('"', '\"', $phrase_array['text']);
			$output_php .= return_variable_entry($phrase_array['variable_name'], $text);
		}
                
		$final_php = "<"."?php \n//FSBOARD GENERATED LANGUAGE FILE \n//DO NOT EDIT DIRECTLY \n\n".$output_php." \n?".">";

		if($write_files)
		{

			// Write the file
			$fh = fopen(ROOT."languages/lang_id".$lang_id."/".$group_name.".php", "w");
			fwrite($fh, $final_php);
			fclose($fh);

			@chmod(ROOT."languages/lang_id".$lang_id."/".$group_name.".php", 0777);
						
		}
		else
			return $final_php;

	}
	else
	{

		// Get werds
		$phrases_query = $db -> query("select * from ".$db -> table_prefix."language_phrases where language_id='".$lang_id."' order by `group`,variable_name");

		$a = 0;               
		$output_php = "";
		$num_rows = $db -> num_rows($phrases_query);
		$text = "";

		// Go through 'em all
		while($phrase_array = $db -> fetch_array($phrases_query))
		{

			$a ++;

			if($a == 1 || $a == $num_rows)
				$current_group_name = $phrase_array['group'];

			// Check if we want to write the file
			if(($current_group_name != $phrase_array['group'] || $a == $num_rows))
			{
                                
				if(($a == $num_rows) && ($current_group_name == $phrase_array['group']))
				{
					$text = str_replace('"', '\"', $phrase_array['text']);
					$output_php .= return_variable_entry($phrase_array['variable_name'], $text);
				}

				$final_php = "<"."?php \n//FSBOARD GENERATED LANGUAGE FILE \n//DO NOT EDIT DIRECTLY \n\n"
					."if (!defined(\"FSBOARD\")) die(\"Script has not been initialised correctly! (FSBOARD not defined)\"); \n\n"				
					.$output_php." \n?".">";

				if($write_files)
				{

					// Write the file
					$fh = fopen(ROOT."languages/lang_id".$phrase_array['language_id']."/".$current_group_name.".php", "w");
					fwrite($fh, $final_php);
					fclose($fh);

				}

				$text = str_replace('"', '\"', $phrase_array['text']);
				$output_php = return_variable_entry($phrase_array['variable_name'], $text);

			}
			else
			{
				$text = str_replace('"', '\"', $phrase_array['text']);
				$output_php .= return_variable_entry($phrase_array['variable_name'], $text);
			}                        
                        
			$current_group_name = $phrase_array['group'];
                                
		}
                
                
	}

}


//***********************************************
// Throws back what is put into a language file
//***********************************************
function return_variable_entry($variable_name, $variable_value)
{
	return "\$lang['".$variable_name."'] = \"".$variable_value."\";\n";
}

//***********************************************
// Returns a dropdown for language groups
//***********************************************
function language_group_menu($current = '', $big = false)
{

	global $db, $lang, $output;

	// Grab all configuration groups in order        
	$select_groups = $db -> query("select * from ".$db -> table_prefix."language_groups order by short_name asc");

	// No groups
	if($db -> num_rows($select_groups) < 1)
	{
        
		// Bye
		$output -> page_output = $template_admin -> critical_error($lang['error_no_language_groups']);
		$output -> finish();
		die();
                        
	}

	// check if we want the dropdown biiiiiig
	if($big)
	{
		$dropdown_size = "size=\"20\"";
		$width = "style=\"width:100%\"";
	}         
	else
		$width = "style=\"width:90%\"";

	// Start up the form
	$final_html = "<select class=\"inputtext\" ".$width." ".$dropdown_size." name=\"group\">
        <optgroup label=\"".$lang['groups_menu_admin_area']."\">";
				
	// Go through all the damn groups now
	$normal_header = false;
        
	while($group_array = $db -> fetch_array($select_groups))
	{
                
		// Check to see if we're on the page
		if($current == $group_array['short_name'])
			$selected = " selected=\"selected\"";
		else
			$selected = "";

		// Change option group if need be
		if(substr($group_array['short_name'],0,6) != "admin_" && $normal_header == false)
		{
			$final_html .= "</optgroup><optgroup label=\"".$lang['groups_menu_open_areas']."\">";
			$normal_header = true;
		}
                                                
		// Option html
		$final_html .= "<option value=\"".$group_array['short_name']."\"".$selected.">".$lang['group_menu_'.$group_array['short_name']]."</option>";
        
	}
   	
   	// Finish form
	$final_html .= "
        </optgroup>
        </select>";

	if($big)
		$final_html .= "<br />";

	// Add it to the page output. End.
	return $final_html;

}


//***********************************************
// Imports languages from XML
//***********************************************
function import_languages_xml($xml_contents, $ignore_version = false)
{
        
	global $db;

	// Start parser
	$xml = new xml;

	$xml -> import_root_name = "languages_file";
	$xml -> import_group_name = "language";
        
	// Run parser and check version
	$parse_return = $xml -> import_xml($xml_contents, $ignore_version);

	if($parse_return == "VERSION" && !$ignore_version)
		return "VERSION";
        
	// Nothing?
	if(count($xml -> import_xml_values['language']) < 1)
		return true;

	// **********************
	// Go through each lang           
	// **********************
	foreach($xml -> import_xml_values['language'] as $set)
	{

		// Create it
		$info = array(
			"name"                  => $set['ATTRS']['name'],
			"short_name"            => $set['ATTRS']['short_name'],
			"charset"               => $set['ATTRS']['charset'],
			"author"                => $set['ATTRS']['author'],
			"direction"             => $set['ATTRS']['direction'],
			"allow_user_select"     => $set['ATTRS']['allow_user_select']
			);
                
		$db -> basic_insert("languages", $info);
                
		// Get the ID for later use
		$set_id = $db -> insert_id();
                
		// Create directory
		if(!file_exists(ROOT."languages/lang_id".$set_id))
			if(!@mkdir(ROOT."languages/lang_id".$set_id, 0777))
				return false;
				
		@chmod(ROOT."languages/lang_id".$set_id, 0777);

		// Log it!
		if(!defined("INSTALL"))
			log_admin_action("ielanguages", "import", "Imported language: ".trim($info['name']));
                        
		// No phrases in this group?
		if(count($set['phrases']) < 1)
			continue;

		$replace_sql = "";
                
		// **********************
		// We have phrases here
		// **********************
		$num = count($set['phrases']);
		$a = 0;
                
		foreach($set['phrases'] as $id => $phrase)
		{

			$a++;
                		
			$phrase['CONTENT'] = trim($phrase['CONTENT']);
                        
			// Build big replace query, too many individual inserts broke it.
			$info = array(
				"'".$phrase['ATTRS']['variable_name']."'",
				"'".$set_id."'",
				"'".$phrase['ATTRS']['group']."'",
				"'".$db -> escape_string($phrase['CONTENT'])."'",
				"'".$db -> escape_string($phrase['CONTENT'])."'",
				);
                        
			$replace_sql .= "(". implode(", ", $info) .")";
                        
			if(strlen($replace_sql) > 100000)
			{
                        
				if(!$db -> query("INSERT INTO ".$db -> table_prefix."language_phrases(`variable_name`, `language_id`, `group`, `text`, `default_text`) VALUES ".$replace_sql))
					return false;
                                
				$replace_sql = "";
                        
			}
			elseif($a != $num)
				$replace_sql .= ", ";
                                                
		}

		// anything we forgot?        
		if($replace_sql)
			if(!$db -> query("INSERT INTO ".$db -> table_prefix."language_phrases(`variable_name`, `language_id`, `group`, `text`, `default_text`) VALUES ".$replace_sql))                
				return false;
				
		// Build the new files
		build_language_files($set_id);        
                
	}           
            
	return true;
	
}

?>
