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
*       Admin Cache             *
*       Started by Fiona        *
*       30th Dec 2005           *
*********************************
*       Last edit by Fiona      *
*       26th Feb 2007           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Include meh language file
//***********************************************
load_language_group("admin_cache");


$output -> add_breadcrumb($lang['breadcrumb_cache'], "index.php?m=cache");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
        case "view":
        
                page_view_cache();
                break;

        case "update":
        
                do_update_cache();
                break;

        case "updateall":
        
                do_update_cache(true);
                break;

	// ---------
	// Dev stuff
	// ---------
        case "newcache":
        
		do_new_cache();
		break;

        default:
        
                page_main();
                
}



//***********************************************
// Front page
//***********************************************
function  page_main()
{

        global $output, $lang, $db;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_cache_title'];

        // Create classes
        $table = new table_generate;
        
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title
                // ---------------
                $table -> add_basic_row($lang['admin_cache_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['admin_cache_message'], "normalcell",  "", "left", "100%", "2").
                $table -> add_row(array(
                        array($lang['cache_name'], "70%"),
                        array($lang['cache_actions'], "30%")
                ), "strip2")
        );

        $cache = $db -> query("select name from ".$db -> table_prefix."cache order by name");

        while($cache_array = $db->fetch_array($cache))
        {

                $output -> add(
                        $table -> add_row(array(
                                array(
                                        $lang['cache_'.$cache_array['name'].'_name']."<br /><font class=\"small_text\">".$lang['cache_'.$cache_array['name'].'_desc']."</font>"
                                , "70%"),
                                array(
                                        "[ <a href=\"".ROOT."admin/index.php?m=cache&amp;m2=view&amp;name=".$cache_array['name']."\">".$lang['cache_action_view']."</a> - ".
                                        "<a href=\"".ROOT."admin/index.php?m=cache&amp;m2=update&amp;name=".$cache_array['name']."\">".$lang['cache_action_update']."</a> ]"
                                , "30%")
                        ), "normalcell")
                );
                        
        }

        $output -> add(
                $table -> add_basic_row("[ <a href=\"".ROOT."admin/index.php?m=cache&amp;m2=updateall\" onClick=\"return confirm('".$lang['admin_cache_update_all_confirm']."')\">".$lang['admin_cache_update_all']."</a> ]", "strip3",  "", "center", "100%", "2").
                $table -> end_table()
        );


	// As delveloper we can add a cache entry
	if(defined("DEVELOPER"))
	{

		$form = new form_generate;
		
                $output -> add(
                        $form -> start_form("newcache", ROOT."admin/index.php?m=cache&amp;m2=newcache").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['cache_add'], 2).
			$table -> simple_input_row_text($form, $lang['cache_add_var_name'], "var_name", "").
			$table -> simple_input_row_int($form, $lang['cache_add_array_levels'], "array_levels", 2).
			$table -> simple_input_row_text($form, $lang['cache_add_name'], "name", "").
			$table -> simple_input_row_text($form, $lang['cache_add_description'], "description", "").
			$table -> add_submit_row($form).
			$table -> end_table().
			$form -> end_form()
		);	
				
	}
        
}


//***********************************************
// Cache view
//***********************************************
function  page_view_cache()
{

        global $output, $lang, $template_admin, $db;

        // ----------------
        // Get the current cache
        // ----------------
        $get_name = trim($_GET['name']);
        
        // No name
        if($get_name == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_cache_name']));
                page_main();
                return;
        }
                
        // Grab wanted cache
        $cache = $db -> query("select content,array_levels from ".$db -> table_prefix."cache where name='".$get_name."'");
        
        // Die if it doesn't exist
        if($db -> num_rows($cache) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_cache_query']));
                page_main();
                return;
        }
        else
                $cache_array = $db -> fetch_array($cache);

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['cache_'.$get_name.'_name'];

	$output -> add_breadcrumb($lang['cache_'.$get_name.'_name'], "index.php?m=cache&amp;m2=view&amp;name=".$get_name);
	
        // ----------------
        // Steal the array
        // ----------------
        $the_cache = unserialize($cache_array['content']);

        // ----------------
        // Nothing in it!
        // ----------------
        if(!is_array($the_cache) || count($the_cache) < 1)
        {
        
                // Create class
                $table = new table_generate;
                
                $output -> add(
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        $table -> add_basic_row($lang['cache_'.$get_name.'_name'], "strip1",  "", "left").
                        $table -> add_basic_row($lang['cache_empty'], "normalcell").
                        $table -> end_table()
                );

                return;
                
        }
        else
        {
        
                // ----------------
                // Show if it's one-dimensional array
                // ----------------
                if($cache_array['array_levels'] == 1)
                {

                        // Create class
                        $table = new table_generate;
                        
                        $output -> add(
                                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%", "2").
                                $table -> add_basic_row($lang['cache_'.$get_name.'_name'], "strip1",  "", "left", "100%", "2").
                                $table -> add_row(
                                        array(
                                                array($lang['cache_key'], "50%"),
                                                array(_htmlentities($lang['cache_value']), "50%")
                                        )
                                ,"strip2", "")
                        );

                        foreach($the_cache as $key => $val)
                        {
                        
                                $output -> add(
                                        $table -> add_row(array($key,$val),"normalcell", "")
                                );

                        
                        }
                        
                        $output -> add(
                                $table -> end_table()
                        );
        
                        return;
                
                
                }
                
                // ----------------
                // Show if it's two-dimensional array
                // ----------------
                if($cache_array['array_levels'] == 2)
                {
                
                        // Create class
                        $table = new table_generate;
                        
                        $output -> add(
                                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%", "2")
                        );                

                        foreach($the_cache as $level_1_key => $level_1_val)
                        {
                        

                                $output -> add(
                                        $table -> add_basic_row($lang['cache_'.$get_name.'_name']." - <b>".$level_1_key."</b>", "strip1",  "", "left", "100%", "2").
                                        $table -> add_row(
                                                array(
                                                        array($lang['cache_key'], "50%")
                                                        ,array($lang['cache_value'], "50%")
                                                )
                                        ,"strip2", "")
                                );                
        
                                foreach($level_1_val as $level_2_key => $level_2_val)
                                {

                                        $output -> add(
                                                $table -> add_row(array($level_2_key, _htmlspecialchars($level_2_val)),"normalcell", "")
                                        );

                                }                        
                                
                        }

                        $output -> add(
                                $table -> end_table()
                        );
        
                        return;
                                                
                }
        
        }
        
        
}


//***********************************************
// Update a cache
//***********************************************
function  do_update_cache($all = false)
{

        global $output, $lang, $template_admin, $db, $cache;

        // ----------------
        // Doing the lot
        // ----------------
        if($all == true)
        {
        
                if($cache -> update_cache("ALL"))
                        $output -> redirect(ROOT."admin/index.php?m=cache", $lang['cache_updated_sucessfully']);
                else
                {
                        $output -> add($template_admin -> critical_error($lang['error_update_cache']));
                        page_main();
                        return;
                }
        
        }
        

        // ----------------
        // Get the current cache
        // ----------------
        $get_name = trim($_GET['name']);
        
        // No name
        if($get_name == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_cache_name']));
                page_main();
                return;
        }
                
        // Grab wanted cache
        $db -> query("select name from ".$db -> table_prefix."cache where name='".$get_name."'");
        
        // Die if it doesn't exist
        if($db -> num_rows() == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_cache_query']));
                page_main();
                return;
        }

        // ----------------
        // Run it nigga
        // ----------------
        if($cache -> update_cache($get_name))
                $output -> redirect(ROOT."admin/index.php?m=cache", $lang['cache_updated_sucessfully']);
        else
        {
                $output -> add($template_admin -> critical_error($lang['error_update_cache']));
                page_main();
                return;
        }
        
}


//***********************************************
// Create a cache
//***********************************************
function  do_new_cache()
{

        global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
			"var_name"	=> $_POST['var_name'],
			"array_levels"	=> $_POST['array_levels'],
			"name"		=> $_POST['name'],
			"description"	=> $_POST['description']
		);

	$input = array_map("trim", $input);

	// ******************
	// Empty?
	// ******************
	if($input['var_name'] == "" || $input['array_levels'] == "" || $input['name'] == "" || $input['description'] == "")
        {
                $output -> add($template_admin -> normal_error($lang['cache_add_error_input']));
                page_main();
                return;
        }
        
	// ******************
        // Array levels
	// ******************
        if($input['array_levels'] != "1" && $input['array_levels'] != "2")
        {
                $output -> add($template_admin -> normal_error($lang['cache_add_error_array_levels']));
                page_main();
                return;
        }

	// ******************
	// Create the cache
	// ******************
	$cache_data = array(
		"name"		=> $input['var_name'],
		"array_levels" 	=> $input['array_levels']
	);

	if(!$db -> basic_insert("cache", $cache_data))
        {
                $output -> add($template_admin -> normal_error($lang['cache_add_error']));
                page_main();
                return;
        }
        
	// ******************
	// Create the name phrase
	// ******************
	$phrase_data = array(
		"language_id" 	=> LANG_ID,
		"variable_name" => "cache_".$input['var_name']."_name",
		"group"		=> "admin_cache",
		"text"		=> $input['name'],
		"default_text"	=> $input['name']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
        {
                $output -> add($template_admin -> normal_error($lang['cache_add_phrase_error']));
                page_main();
                return;
        }

	// ******************
	// Create the description phrase
	// ******************
	$phrase_data = array(
		"language_id" 	=> LANG_ID,
		"variable_name" => "cache_".$input['var_name']."_desc",
		"group"		=> "admin_cache",
		"text"		=> $input['description'],
		"default_text"	=> $input['description']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
        {
                $output -> add($template_admin -> normal_error($lang['cache_add_phrase_error']));
                page_main();
                return;
        }

	include ROOT."admin/common/funcs/languages.funcs.php";
        build_language_files(LANG_ID, "admin_cache");            
        
	// ******************
	// Done
	// ******************
        $output -> redirect(ROOT."admin/index.php?m=cache", $lang['cache_add_done']);
 
}

?>
