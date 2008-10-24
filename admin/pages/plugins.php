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
 * Plugin Manager
 * 
 * This will let admins start mailing users.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 * 
 * @started 04 Oct 2007
 * @edited 04 Oct 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Talk to me message board
//***********************************************
load_language_group("admin_plugins");


//***********************************************
// These do things
//***********************************************
include ROOT."admin/common/funcs/plugins.funcs.php";


$output -> add_breadcrumb($lang['breadcrumb_plugins'], "index.php?m=plugins");


$_GET['m2'] = ($_GET['m2']) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

	case "add":
		page_add_edit_plugins(true);
		break;

	case "doadd":
		do_add_plugins();
		break;

	case "edit":
		page_add_edit_plugins();
		break;

	case "doedit":
		do_edit_plugins();
		break;

	case "delete":
		do_delete_plugins();
		break;
                
	case "viewfiles":
		page_view_files();
		break;                
                
	case "addfiles":
		page_add_edit_files(true);
		break;                
                
	case "editfiles":
		page_add_edit_files();
		break;                
                
	case "doaddfiles":
		do_add_files();
		break;                
                
	case "doeditfiles":
		do_edit_files();
		break;                
                
	case "deletefiles":
		do_delete_files();
		break;               
        
	case "importexport":
		page_import_export();
		break;

	case "doimport":
		do_import();
		break;

	case "doexport":
		do_export();
		break;
                
	case "hookinfo":
		ajax_hook_info();
		break;

    // DEVELOPER            
	case "newhook":
		dev_new_hook();
		break;
		
	default:
		page_main();

}


/**
 * List of all plugins installed
 */
function page_main()
{
	
	global $lang, $output, $db;
	        
	// *********************
	// Set page title
	// *********************
	$output -> page_title = $lang['plugins_main_title'];
	
	// Create class
	$table = new table_generate;
	$form = new form_generate;
	
	// ********************
	// Start table
	// ********************
    $output -> add(
		$form -> start_form("plugin_main_form", "", "post").
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                
		$table -> add_top_table_header($lang['plugins_main_title'], 4, "plugins").
                
		$table -> add_row(
			array(
				array($lang['plugins_main_status'], "auto"),
				array($lang['plugins_main_name'], "auto"),
				array($lang['plugins_main_author'], "auto"),
				array($lang['plugins_main_actions'], "auto")
			),
		"strip2")
	);
        

	// ********************
	// Grab all plugins
	// ********************
	$db -> basic_select("plugins", "*", "", "`installed` desc, `enabled` desc, `name` asc");

	// No plugins?
	if( $db -> num_rows() < 1)
		$output -> add(
			$table -> add_basic_row("<b>".$lang['no_plugins']."</b>", "normalcell",  "padding : 10px", "center")
		);        
	else
	{

		// *************************
		// Go through each one if we have some
		// *************************
		while($p_array = $db-> fetch_array())
		{

			// Work out the status and the appropriate stuff
			$status_text = $enabled = $disabled = $installed = $uninstalled = false;
			
			if($p_array['enabled'])
			{
				
				$status_text = "...enabled...";
				$enabled = $installed = true;
				
			}
			else
			{
				
				$disabled = true;
				$status_text = "...disabled...";
				
				if($p_array['installed'])
					$installed = true;
				else
				{
					$uninstalled = true;
					$status_text = "...not installed...";
				}	
			}
			
			// Linky linky to actions
			$actions = $form -> input_dropdown("plugin_".$p_array['id'], "edit", 
				array(
					"viewfiles",
					"edit",
					($disabled  && $uninstalled) ? "installed" : "",
					($disabled  && $installed) ? "uninstalled" : "",
					($disabled && $installed) ? "enable" : "",
					($enabled && $installed) ? "disable" : ""
				),
				array(								
					$lang['plugins_main_view'],
					$lang['plugins_main_edit'],
					($disabled  && $uninstalled) ? $lang['plugins_main_install'] : "",
					($disabled  && $installed) ? $lang['plugins_main_uninstall'] : "",
					($disabled && $installed) ? $lang['plugins_main_enable'] : "",
					($enabled && $installed) ? $lang['plugins_main_disable'] : ""	
				),
				"inputtext", "auto"
			)
			.$form -> button("go_button", $lang['go'], "submitbutton"); 
/*"
                        <a href=\"".ROOT."admin/index.php?m=plugins&amp;m2=viewfiles&amp;id=".$p_array['id']."\" title=\"".$lang['plugins_main_view']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-preview.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=plugins&amp;m2=edit&amp;id=".$p_array['id']."\" title=\"".$lang['plugins_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=plugins&amp;m2=delete&amp;id=".$p_array['id']."\" onclick=\"return confirm('".$lang['delete_plugins_confirm']."')\" title=\"".$lang['plugins_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";
*/
			
			$output -> add(
				$table -> add_row(
					array(
						$status_text,
						$p_array['name'],
						$p_array['author'],
						$actions
					)
				, "normalcell")
			);
                        
		}

	}

	
	// ********************
	// End table
	// ********************
	$output -> add(
		$table -> add_basic_row(
			$form -> button("addplugins", $lang['add_plugins_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=plugins&m2=add';\"")
		, "strip3", "", "center", "100%").
		$table -> end_table().
		$form -> end_form()
	);

	
	/*
	 * Developers can add plugin hooks
	 */
	if(defined("DEVELOPER"))
	{

		$form = new form_generate;
                
		$output -> add(
			$form -> start_form("newhook", ROOT."admin/index.php?m=plugins&amp;m2=newhook").
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_addhook'], 2).
			
			$table -> simple_input_row_text($form, $lang['plugins_addhook_pageid'], "pageid", "").
			$table -> simple_input_row_text($form, $lang['plugins_addhook_pagename'], "pagename", "").
			
			$table -> simple_input_row_text($form, $lang['plugins_addhook_hookid'], "hookid", "").
			$table -> simple_input_row_text($form, $lang['plugins_addhook_hookname'], "hookname", "").
			
			$table -> simple_input_row_textbox($form, $lang['plugins_addhook_description'], "description", "", 4).
			$table -> simple_input_row_textbox(
				$form,
				$lang['plugins_addhook_parameters'],
				"params",
				"<ul>\n  <li><i>&amp;amp;"."\\"."\$param_name</i>: Param description</li>\n</ul>",
				4
			).
				
			$table -> add_submit_row($form).
			$table -> end_table().
			$form -> end_form()
		);        

	}
	
}


/**
 * Page to let us add a plugin
 * 
 * @param array $search_info Array of already input values
 */
function page_add_edit_plugins($adding = false, $plugin_info = "")
{

	global $output, $lang, $db, $template_admin;

	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/") || !file_exists(ROOT."plugins/"))
	{
			
		$output -> add(
			$template_admin -> critical_error($lang['plugins_dir_not_writable'])
		);
			
		page_main();
		return;
			
	}		

		
	// Create classes
	$table = new table_generate;        
	$form = new form_generate;
        
	// ****************
	// adding
	// ****************
	if($adding)
	{

		// Set page title and crumb
		$output -> add_breadcrumb($lang['plugins_add_title'], "index.php?m=plugins&amp;m2=add");
		$output -> page_title = $lang['plugins_add_title'];

		$output -> add(
			$form -> start_form("plugin_form", ROOT."admin/index.php?m=plugins&amp;m2=doadd", "post").
		                
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_add_title'], 2, "plugins")
		);
				
		$submit_text = $lang['plugins_add_submit'];

	}
	// ****************
	// editing
	// ****************
	else
	{
			
		if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
		{
			$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
			page_main();
			return;
		}		
	        	
		if(!$plugin_info)
			$plugin_info = $db -> fetch_array();
        				
		$output -> add_breadcrumb($lang['plugins_edit_title'], "index.php?m=plugins&amp;m2=edit");
		$output -> page_title = $lang['plugins_edit_title'];

		$output -> add(
			$form -> start_form("plugin_form", ROOT."admin/index.php?m=plugins&amp;m2=doedit&amp;id=".(int)$_GET['id'], "post").
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_edit_title'], 2, "plugins")
		);

		$submit_text = $lang['plugins_edit_submit'];
					
	}


	// **************************
	// Generate the form
	// **************************
	$output -> add
	(
		$table -> simple_input_row_text($form, $lang['plugins_add_name'], "name", $plugin_info['name'], "name").     
		$table -> simple_input_row_text($form, $lang['plugins_add_author'], "author", $plugin_info['author'], "author").     
		$table -> simple_input_row_textbox($form, $lang['plugins_add_description'], "description", $plugin_info['description'], 4, "description").     

		$table -> add_submit_row($form, "submit", $submit_text).
		$table -> end_table().
		$form -> end_form()
	);         

}


/**
 * The action to add a plugin
 */
function do_add_plugins()
{

	global $output, $lang, $db, $template_admin;

	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/") || !file_exists(ROOT."plugins/"))
	{
			
		$output -> add(
			$template_admin -> critical_error($lang['plugins_dir_not_writable'])
		);
			
		page_main();
		return;
			
	}	
		
	// Get post stuff
	$plugin_info = array(
		"name" 			=> $_POST['name'],
		"author" 		=> $_POST['author'],
		"description"	=> $_POST['description']
	);	
		
	// Missing input
	if(trim($plugin_info['name']) == "")
	{
						
		$output -> add(
			$template_admin -> normal_error($lang['plugins_missing_name'])
		);
			
		page_add_edit_plugins(true, $plugin_info);
		return;
						
	}
		
	// Try to input
	if(!$db -> basic_insert("plugins", $plugin_info))
	{

		$output -> add(
			$template_admin -> critical_error($lang['plugins_error_add'])
		);
			
		page_add_edit_plugins(true, $plugin_info);
		return;
			
	}

	// directory add
	$new_id = $db -> insert_id();
		
	if(!mkdir(ROOT."plugins/plugin_id".$new_id))
	{

		$output -> add(
			$template_admin -> critical_error($lang['plugins_error_create_dir'])
		);
			
		page_main();
		return;
			
	}
		
	@chmod(ROOT."plugins/plugin_id".$new_id, 0777);


	// Redirect				
	$output -> redirect(ROOT."admin/index.php?m=plugins", $lang['plugins_add_success']);
		
}		


/**
 * The action to edit a plugin
 */
function do_edit_plugins()
{

	global $output, $lang, $db, $template_admin;

	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/") || !file_exists(ROOT."plugins/"))
	{
			
		$output -> add(
			$template_admin -> critical_error($lang['plugins_dir_not_writable'])
		);
			
		page_main();
		return;
			
	}	

	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
    {
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
	}	
	        			
	// Get post stuff
	$plugin_info = array(
		"name" 			=> $_POST['name'],
		"author" 		=> $_POST['author'],
		"description"	=> $_POST['description']
	);	
		
	// Missing input
	if(trim($plugin_info['name']) == "")
	{
						
		$output -> add(
			$template_admin -> normal_error($lang['plugins_missing_name'])
		);
			
		page_add_edit_plugins(false, $plugin_info);
		return;
						
	}
		
	// Try to input
	if(!$db -> basic_update("plugins", $plugin_info, "id = ".(int)$_GET['id'].""))
	{

		$output -> add(
			$template_admin -> critical_error($lang['plugins_error_edit'])
		);
			
		page_add_edit_plugins(false, $plugin_info);
		return;
			
	}

	// Redirect				
	$output -> redirect(ROOT."admin/index.php?m=plugins", $lang['plugins_edit_success']);
		
}		


/**
 * The action to delete a plugin
 */
function do_delete_plugins()
{

	global $output, $lang, $db, $template_admin;

	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
   	{
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
	}	

	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/plugin_id".(int)$_GET['id']."/"))
	{
			
		$output -> add(
			$template_admin -> critical_error(
				$output -> replace_number_tags($lang['plugins_delete_not_writable'], ROOT."plugins/plugin_id".(int)$_GET['id'])
			)
		);
			
		page_main();
		return;
			
	}
		
	// delete main database netry
	if(!$db -> basic_delete("plugins", "id = ".(int)$_GET['id']))
	{
		$output -> add($template_admin -> critical_error($lang['plugins_delete_error']));
		page_main();
		return;
	}

	// Nab all the files
	$db -> basic_select("plugins_files", "*", "plugin_id = ".(int)$_GET['id']);
		
	if($db -> num_rows() > 0)
	{
		
		while($array = $db -> fetch_array())
			unlink(ROOT."plugins/plugin_id".(int)$_GET['id']."/".$array['hook_file']."_".$array['hook_name'].".php");

		@$db -> basic_delete("plugins_files", "plugin_id = ".(int)$_GET['id']);
			
	}	
				 
	rmdir(ROOT."plugins/plugin_id".(int)$_GET['id']."/");
						 
	// Redirect				
	$output -> redirect(ROOT."admin/index.php?m=plugins", $lang['plugins_delete_success']);
						    	        
}        


/**
 * Looking at our pretty little plugin files
 */
function page_view_files()
{

	global $output, $lang, $db, $template_admin;

	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
    {
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
    }	
    	
	// *********************
	// Set page title and bread crumb
	// *********************
	$output -> page_title = $lang['plugins_view_files_title'];
	$output -> add_breadcrumb($lang['plugins_view_files_title'], "index.php?m=plugins&amp;m2=viewfiles&amp;id=".(int)$_GET['id']);

	// Create class
	$table = new table_generate;
	$form = new form_generate;

	// ********************
	// Start table
	// ********************
	$output -> add(
		$form -> start_form("dummyform", "", "post").
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                
		$table -> add_top_table_header($lang['plugins_view_files_title'], 3, "plugins").
                
		$table -> add_row(
			array(
				array($lang['plugins_view_files_summary'], "auto"),
				array($lang['plugins_view_files_hook'], "auto"),
				array($lang['plugins_view_files_actions'], "auto")
			)
			, "strip2")
	);
        

	// ********************
	// Grab all plugins
	// ********************
	$db -> basic_select("plugins_files", "*", "plugin_id = '".(int)$_GET['id']."'", "`hook_file`, `hook_name`");

	// No plugins?
	if($db -> num_rows() < 1)
		$output -> add(
			$table -> add_basic_row("<b>".$lang['no_plugin_files']."</b>", "normalcell",  "padding : 10px", "center")
		);        
	else
	{

		// *************************
		// Go through each one if we have some
		// *************************
		while($p_array = $db-> fetch_array())
		{

			// Linky linky to actions
			$actions = "
				<a href=\"".ROOT."admin/index.php?m=plugins&amp;m2=editfiles&amp;id=".(int)$_GET['id']."&amp;fid=".$p_array['id']."\" title=\"".$lang['plugins_files_edit']."\">
				<img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
				<a href=\"".ROOT."admin/index.php?m=plugins&amp;m2=deletefiles&amp;id=".(int)$_GET['id']."&amp;fid=".$p_array['id']."\" onclick=\"return confirm('".$lang['delete_plugins_files_confirm']."')\" title=\"".$lang['plugins_files_delete']."\">
				<img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

			$output -> add(
				$table -> add_row(
					array(
						$p_array['summary'],
						$lang['plugins_hooks_files_'.$p_array['hook_file']].":".$lang['plugins_hooks_'.$p_array['hook_file'].'_'.$p_array['hook_name']],
						$actions
					)
					, "normalcell")
			);
                       
		}

	}

	// ********************
	// End table
	// ********************
	$output -> add(
		$table -> add_basic_row(
			$form -> button("addplugins", $lang['add_plugin_files_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=plugins&m2=addfiles&id=".(int)$_GET['id']."';\"")
				, "strip3", "", "center", "100%").
			$table -> end_table().
			$form -> end_form()
	);
    	
}
    	

/**
 * Page to let us add a plugin file
 * 
 * @param array $search_info Array of already input values
 */
function page_add_edit_files($adding = false, $plugin_info = "")
{

	global $output, $lang, $db, $template_admin, $PLUGIN_HOOKS;


	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
	{
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
    }	

	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/plugin_id".(int)$_GET['id']."/"))
	{
			
		$output -> add(
			$template_admin -> critical_error(
				$output -> replace_number_tags($lang['plugins_files_not_writable'], ROOT."plugins/plugin_id".(int)$_GET['id'])
			)
		);
			
		page_main();
		return;
			
	}	


	// Add crumb
	$output -> add_breadcrumb($lang['plugins_view_files_title'], "index.php?m=plugins&amp;m2=viewfiles&amp;id=".(int)$_GET['id']);
		
	// Create classes
	$table = new table_generate;        
	$form = new form_generate;
        
	// ****************
	// adding
	// ****************
	if($adding)
	{

		// Set page title and crumb
		$output -> add_breadcrumb($lang['plugins_add_files_title'], "index.php?m=plugins&amp;m2=addfiles&amp;id=".$_GET['id']);
		$output -> page_title = $lang['plugins_add_files_title'];

		$output -> add(
			$form -> start_form("plugin_files_form", ROOT."admin/index.php?m=plugins&amp;m2=doaddfiles&amp;id=".$_GET['id'], "post").
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_add_files_title'], 2, "plugins")
		);
				
		$submit_text = $lang['plugins_add_files_submit'];

	}
	// ****************
	// editing
	// ****************
	else
	{
			
		if($db -> query_check_id_rows("plugins_files", (int)$_GET['fid']) < 1)
		{
			$output -> add($template_admin -> critical_error($lang['plugins_edit_files_id_error']));
			page_main();
			return;
		}		
	        	
		if(!$plugin_info)
			$plugin_info = $db -> fetch_array();
        				
		$output -> add_breadcrumb($lang['plugins_edit_files_title'], "index.php?m=plugins&amp;m2=editfiles&amp;id=".$_GET['id']."&amp;fid=".$_GET['fid']);
		$output -> page_title = $lang['plugins_edit_files_title'];

		$output -> add(
			$form -> start_form("plugin_files_form", ROOT."admin/index.php?m=plugins&amp;m2=doeditfiles&amp;id=".(int)$_GET['id']."&amp;fid=".$_GET['fid'], "post").
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_edit_files_title'], 2, "plugins")
		);

		$submit_text = $lang['plugins_edit_files_submit'];
					
	}


	// **************************
	// Build the hook dropdown
	// **************************
	$hook_dropdown_vals = array();
	$hook_dropdown_text = array();
		
	foreach($PLUGIN_HOOKS as $filename => $hooks)
	{
			
		if(is_array($hooks) && count($hooks) > 0)
		{
				
			foreach($hooks as $hook_name => $junk)
			{
		
				$hook_dropdown_vals[] = $filename.":".$hook_name;
				$hook_dropdown_text[] =	$lang['plugins_hooks_files_'.$filename]." : ".$lang['plugins_hooks_'.$filename.'_'.$hook_name]; 
				
			}
				
		}
			
	}
		

	// **************************
	// Generate the form
	// **************************
	$output -> add
	(
		$table -> simple_input_row_text($form, $lang['plugins_add_files_summary'], "summary", $plugin_info['summary'], "summary").     
		$table -> simple_input_row_dropdown($form, $lang['plugins_add_files_hook'], "hook", $plugin_info['hook_file'].":".$plugin_info['hook_name'], $hook_dropdown_vals, $hook_dropdown_text, "hook").
                
		$table -> add_basic_row($lang['plugins_add_files_code'], "strip2", "", "left").
		$table -> add_basic_row(
			$output -> return_help_button("code", false).
			$form -> input_textbox("code", _htmlspecialchars($plugin_info['code']), 20, "inputtext", "95%"),
			"normalcell", "", "center"
		).

		$table -> add_basic_row(
			"<div class=\"plugin_hook_info\">".$lang['plugins_add_hook_info_default']."</div>",
			"normalcell", "", "center"
		).
                
		$table -> add_submit_row($form, "submit", $submit_text).
		$table -> end_table().
		$form -> end_form()
	);         

}


/**
 * Actually do the adding we want
 */
function do_add_files()
{

	global $output, $lang, $db, $cache, $template_admin, $PLUGIN_HOOKS;


	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
	{
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
	}	

	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/plugin_id".(int)$_GET['id']."/"))
	{
			
		$output -> add(
			$template_admin -> critical_error(
				$output -> replace_number_tags($lang['plugins_files_not_writable'], ROOT."plugins/plugin_id".(int)$_GET['id'])
			)
		);
			
		page_main();
		return;
			
	}	

		
	// Get post stuff
	$hook = explode(":", $_POST['hook']);
		
	$plugin_info = array(
		"summary" 	=> $_POST['summary'],
		"hook_file" => $hook[0],
		"hook_name"	=> $hook[1],
		"code"		=> $_POST['code']
	);	

	array_map("trim", $plugin_info);

		
	// Missing input
	if($plugin_info['summary'] == "" || $plugin_info['hook_file'] == "" || $plugin_info['hook_name'] == "")
	{
						
		$output -> add(
			$template_admin -> normal_error($lang['plugins_file_missing_input'])
		);
			
		page_add_edit_files(true, $plugin_info);
		return;
						
	}

		
	// Make sure the hook exists lol
	if(!isset($PLUGIN_HOOKS[ $plugin_info['hook_file'] ][ $plugin_info['hook_name'] ]))
	{
						
		$output -> add(
			$template_admin -> normal_error($lang['plugins_file_hook_not_exist'])
		);
			
		page_add_edit_files(true, $plugin_info);
		return;
						
	}


	// Check if something with this hook exists	
	if(
		$db -> num_rows(
			$db -> basic_select("plugins_files", "id","plugin_id = '".(int)$_GET['id']."' AND hook_file = '".$plugin_info['hook_file']."' AND hook_name = '".$plugin_info['hook_name']."'")
		) > 0
	)
	{
		$output -> add($template_admin -> critical_error($lang['plugins_file_hook_exists']));
		page_add_edit_files(true, $plugin_info);
		return;
	}			

		
	// Try to input
	$plugin_info['plugin_id'] = (int)$_GET['id'];
		
	if(!$db -> basic_insert("plugins_files", $plugin_info))
	{

		$output -> add(
			$template_admin -> critical_error($lang['plugins_file_error_insert'])
		);
			
		page_add_edit_files(true, $plugin_info);
		return;
			
	}

	build_plugin_files($plugin_info['plugin_id'], $plugin_info['hook_file'].":".$plugin_info['hook_name']);

	$cache -> update_cache("plugins");
        
	// Redirect				
	$output -> redirect(ROOT."admin/index.php?m=plugins&m2=viewfiles&id=".$_GET['id'], $lang['plugins_file_insert_success']);

}


/**
 * Edit a file now
 */
function do_edit_files()
{

	global $output, $lang, $db, $cache, $template_admin, $PLUGIN_HOOKS;


	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
    {
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
	}


	// Check the file exists	
	if($db -> query_check_id_rows("plugins_files", (int)$_GET['fid']) < 1)
    {
		$output -> add($template_admin -> critical_error($lang['plugins_edit_files_id_error']));
		page_main();
		return;
	}	
    	
	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/plugin_id".(int)$_GET['id']."/"))
	{
			
		$output -> add(
			$template_admin -> critical_error(
				$output -> replace_number_tags($lang['plugins_files_not_writable'], ROOT."plugins/plugin_id".(int)$_GET['id'])
			)
		);
			
		page_main();
		return;
			
	}	

		
	// Get post stuff
	$hook = explode(":", $_POST['hook']);
		
	$plugin_info = array(
		"summary" 	=> $_POST['summary'],
		"hook_file" => $hook[0],
		"hook_name"	=> $hook[1],
		"code"		=> $_POST['code']
	);	

	array_map("trim", $plugin_info);

		
	// Missing input
	if($plugin_info['summary'] == "" || $plugin_info['hook_file'] == "" || $plugin_info['hook_name'] == "")
	{
						
		$output -> add(
			$template_admin -> normal_error($lang['plugins_file_missing_input'])
		);
			
		page_add_edit_files(false, $plugin_info);
		return;
						
	}

		
	// Make sure the hook exists lol
	if(!isset($PLUGIN_HOOKS[ $plugin_info['hook_file'] ][ $plugin_info['hook_name'] ]))
	{
						
		$output -> add(
			$template_admin -> normal_error($lang['plugins_file_hook_not_exist'])
		);
			
		page_add_edit_files(false, $plugin_info);
		return;
						
	}


	// Check it something with this hook exists	
	if(
		$db -> num_rows(
			$db -> basic_select("plugins_files", "id","plugin_id = '".(int)$_GET['id']."' AND hook_file = '".$plugin_info['hook_file']."' AND hook_name = '".$plugin_info['hook_name']."' AND id <> ".(int)$_GET['fid'])
		) > 0
	)
   	{
		$output -> add($template_admin -> critical_error($lang['plugins_file_hook_exists']));
		page_add_edit_files(false, $plugin_info);
		return;
    }			

		
	// Try to input
	if(!$db -> basic_update("plugins_files", $plugin_info, "plugin_id = '".(int)$_GET['id']."' AND id = ".(int)$_GET['fid']))
	{

		$output -> add(
			$template_admin -> critical_error($lang['plugins_file_error_update'])
		);
			
		page_add_edit_files(false, $plugin_info);
		return;
			
	}

	build_plugin_files((int)$_GET['id'], $plugin_info['hook_file'].":".$plugin_info['hook_name']);

	$cache -> update_cache("plugins");
        
	// Redirect				
	$output -> redirect(ROOT."admin/index.php?m=plugins&m2=viewfiles&id=".$_GET['id'], $lang['plugins_file_update_success']);

}
    	

/**
 * The action to delete a plugin file
 */
function do_delete_files()
{

	global $output, $lang, $db, $cache, $template_admin;


	// Check it exists	
	if($db -> query_check_id_rows("plugins", (int)$_GET['id']) < 1)
    {
		$output -> add($template_admin -> critical_error($lang['plugins_edit_id_error']));
		page_main();
		return;
	}	


	// Check the file exists	
	if($db -> query_check_id_rows("plugins_files", (int)$_GET['fid'], "*") < 1)
	{
		$output -> add($template_admin -> critical_error($lang['plugins_edit_files_id_error']));
		page_main();
		return;
	}
    	
    $file_array = $db -> fetch_array();
    	
    	
	// Check we can write to file yo
	if(!is_writable(ROOT."plugins/plugin_id".(int)$_GET['id']."/"))
	{
			
		$output -> add(
			$template_admin -> critical_error(
				$output -> replace_number_tags($lang['plugins_delete_not_writable'], ROOT."plugins/plugin_id".(int)$_GET['id'])
			)
		);
			
		page_view_files();
		return;
			
	}
		
	// delete main database entry
	if(!$db -> basic_delete("plugins_files", "id = ".(int)$_GET['fid']))
	{
		$output -> add($template_admin -> critical_error($lang['plugins_file_delete_error']));
		page_view_files();
		return;
	}

	// Delete the file
	@unlink(ROOT."plugins/plugin_id".(int)$_GET['id']."/".$file_array['hook_file']."_".$file_array['hook_name'].".php");

	$cache -> update_cache("plugins");
		
	// Redirect				
	$output -> redirect(ROOT."admin/index.php?m=plugins&m2=viewfiles&id=".$_GET['id'], $lang['plugins_file_delete_success']);
						    	        
}        
    	
    	
/*
 * Show the import/export page
 */
function page_import_export()
{

	global $output, $lang, $db;
        
	// *********************
	// Set page title
	// *********************
	$output -> page_title = $lang['plugins_importexport_title'];

	$output -> add_breadcrumb($lang['plugins_importexport_title'], "index.php?m=plugins&amp;m2=importexport");

	// Create classes
	$table = new table_generate;
	$form = new form_generate;


	// Grab all plugins
	$db -> basic_select("plugins", "id,name", "", "name");
		
	$plugin_count = $db -> num_rows();
        
	// Go through all
	if($plugin_count)
	{

		while($plugin_array = $db -> fetch_array())
		{
			// Add to dropdown arrays
			$plugin_dropdown[] .= $plugin_array['id'];
			$plugin_dropdown_text[] .= $plugin_array['name'];
		}
        
	}
    
	// ----------------
	// EXPORT FORM
	// ----------------
	if($plugin_count)
	{        
        
		$output -> add(
			$form -> start_form("exportplugins", ROOT."admin/index.php?m=plugins&amp;m2=doexport", "post").
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_export_title'], 2, "plugins").
			$table -> simple_input_row_text($form, $lang['plugins_export_filename'], "filename", "fsboard-plugin.xml", "filename").     
			$table -> simple_input_row_dropdown($form, $lang['plugins_export_which_plugin'], "plugin", "", $plugin_dropdown, $plugin_dropdown_text, "plugin").     
	
			$table -> add_submit_row($form, "submit", $lang['plugins_export_submit']).
			$table -> end_table().
			$form -> end_form()
		);

	}
		
	// ----------------
	// IMPORT FORM
	// ----------------
	// Check we can write to dir yo
	if(!is_writable(ROOT."plugins/") || !file_exists(ROOT."plugins/"))
	{
			
		$output -> add(
			$template_admin -> critical_error($lang['plugins_dir_not_writable'])
		);
			
	}	
	else
	{        

		$output -> add(
			$form -> start_form("importplugins", ROOT."admin/index.php?m=plugins&amp;m2=doimport", "post", true).
			$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
			$table -> add_top_table_header($lang['plugins_import_title'], 2).
			$table -> simple_input_row_file($form, $lang['plugins_import_upload'], "file", "file").
			$table -> simple_input_row_text($form, $lang['plugins_import_filename'], "filename", "includes/fsboard-plugin.xml", "import_filename").
			$table -> add_submit_row($form, "submit", $lang['plugins_import_submit']).
			$table -> end_table().
			$form -> end_form()
		);                

	}
		         
}


/*
 * Export some settings
 */
function do_export()
{

	global $output, $lang, $db, $template_admin;

	$output -> add_breadcrumb($lang['plugins_importexport_title'], "index.php?m=plugins&amp;m2=importexport");

	$plugin_id = (int)$_POST['plugin'];

	// *************************
	// Start XML'ing
	// *************************
	$xml = new xml;
	$xml -> export_xml_start();
	$xml -> export_xml_root("fsboard_plugin");

	// *************************
	// Get the plugin info
	// *************************
	$db -> basic_select("plugins", "*", "id=".$plugin_id);

	if($db -> num_rows() < 1)
	{
		$output -> add($template_admin -> critical_error($lang['export_error_get_plugin']));
		page_import_export();
		return;
	}
        
	$plugin_array = $db -> fetch_array();

	// *************************
	// Start off the plugin group
	// *************************
	$xml -> export_xml_start_group(
		"plugin",
		array(
			"name" 			=> $plugin_array['name'],
			"author" 		=> $plugin_array['author'],
			"description" 	=> $plugin_array['description']
		)
	);
                        
	// *************************
	// Select the files in this plugin
	// *************************
	$db -> basic_select("plugins_files", "*", "plugin_id=".$plugin_id);

	if($db -> num_rows() < 1)
	{
		$output -> add($template_admin -> critical_error($lang['export_error_no_files']));
		page_import_export();
		return;
	}                	        


	// *************************
	// add entry for each file
	// *************************
	while($file_array = $db -> fetch_array())
	{

		$xml -> export_xml_add_group_entry(
			"plugin_file",
			array(
				"hook_file" 	=> $file_array['hook_file'],
				"hook_name" 	=> $file_array['hook_name'],
				"summary" 		=> $file_array['summary']
			),
			$file_array['code']
		);
                                			
	}

	// *************************
	// Finish all
	// *************************
	$xml -> export_xml_generate_group();
	$xml -> export_xml_generate();


	// *************************
	// Work out output file name                
	// *************************
	if($_POST['filename'] == '')                
		$filename = "fsboard-plugin.xml";
	else
		$filename = $_POST['filename'];

        
	// *************************
	// Chuck the file out
	// *************************
	output_file($xml -> export_xml, $filename, "text/xml");

}
    	

/*
 * Trying to import
 */
function do_import()
{

	global $output, $lang, $db, $template_admin, $cache;

	$output -> add_breadcrumb($lang['plugins_importexport_title'], "index.php?m=plugins&amp;m2=importexport");

	// Check we can write to dir yo
	if(!is_writable(ROOT."plugins/") || !file_exists(ROOT."plugins/"))
	{
			
		$output -> add(
			$template_admin -> critical_error($lang['plugins_dir_not_writable'])
		);
		page_import_export();
		return;
						
	}	
		
		
	// Get file from upload
	if(file_exists($_FILES['file']['tmp_name']))
		$xml_contents = file_get_contents($_FILES['file']['tmp_name']);
	// Get file from server
	elseif(file_exists(ROOT.$_POST['filename']))
		$xml_contents = file_get_contents(ROOT.$_POST['filename']);
	// No file - red alert
	else
	{
		$output -> add($template_admin -> normal_error(
			$output -> replace_number_tags($lang['import_file_not_found'], $_POST['filename'])
		));
		page_import_export();
		return;
	}


	// *************************
	// Import me please
	// *************************
	$get_error = import_plugin_xml($xml_contents);

	// something went wrong       
	if($get_error === false)
	{
		$output -> add($template_admin -> critical_error($lang['import_generic_error']));
 		return false;
	}

	// If we have version mismatch
	if((string)$get_error == "VERSION")
	{
		$output -> add($template_admin -> critical_error($lang['import_version_mismatch']));
		return false;
	}

	// Update cache
	$cache -> update_cache("plugins");
                
	$output -> add($template_admin -> message($lang['import_done_title'], $lang['import_done_message']));

}    	


/*
 * Get the description of a hook and send it back to the user
 */
function ajax_hook_info()
{
	
	global $lang, $PLUGIN_HOOKS;
		
	// Get hook_name
	$hook = explode(":", trim($_GET['hook']));	

	// Missing input
	if(count($hook) <> 2)
		die();
	
	// Make sure the hook exists
	if(!isset($PLUGIN_HOOKS[ $hook[0] ][ $hook[1] ]))
		die($lang['plugins_file_hook_not_exist']);			
	
	$hook_info = $PLUGIN_HOOKS[ $hook[0] ][ $hook[1] ];
	$hook_name = "hook_".$hook[0]."_".$hook[1];
	
	$returned_info = $hook_name.'('.$hook_info['params'].')';
	
	if(trim($hook_info['params']))
		$returned_info .= "<br /><br /><b>".$lang['hook_info_params']."</b>".$lang[$hook_name."_parameters"];

	$returned_info .= "<br />".$lang[$hook_name."_description"];
		
	if(trim($hook_info['return']))
		$returned_info .= "<br /><br /><b>".$lang['hook_info_return']."</b><br /><br />".$hook_info['return'];

	die($returned_info);	
	
}


/*
 * Developer- Can add new hooks,
 * basically just adding language vals
 */
function dev_new_hook()
{
	
	global $output, $lang, $db, $template_admin;

	if(!defined("DEVELOPER"))
	{
		page_main();
		return;
	}
	
	$input = array(
		"pageid"		=> $_POST['pageid'],
		"pagename"		=> $_POST['pagename'],
		"hookid"		=> $_POST['hookid'],
		"hookname"		=> $_POST['hookname'],
		"description"	=> $_POST['description'],
		"params"		=> $_POST['params']
	);

	$input = array_map("trim", $input);
	
	// ******************
	// Empty?
	// ******************
	if($input['pageid'] == "" || $input['hookid'] == "")
	{
		$output -> add($template_admin -> normal_error($lang['plugins_addhook_empty_error']));
		page_mains();
		return;
	}                

	
	// ******************
	// Add hook name phrase
	// ******************
	$phrase_data = array(
		"language_id"	=> LANG_ID,
		"variable_name" => "plugins_hooks_".$input['pageid']."_".$input['hookid'],
		"group"			=> "admin_plugins",
		"text"			=> $input['hookname'],
		"default_text"	=> $input['hookname']
	);

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['plugin_addhook_lang_error']));
		page_main();
		return;
	}

	
	// ******************
	// Add hook description phrase
	// ******************
	$phrase_data['variable_name'] = "hook_".$input['pageid']."_".$input['hookid']."_description";
	$phrase_data['text'] = $input['description'];
	$phrase_data['default_text'] = $input['description'];

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['plugin_addhook_lang_error']));
		page_main();
		return;
	}

	// ******************
	// Add hook parameters phrase
	// ******************
	$phrase_data['variable_name'] = "hook_".$input['pageid']."_".$input['hookid']."_parameters";
	$phrase_data['text'] = $input['params'];
	$phrase_data['default_text'] = $input['params'];

	if(!$db -> basic_insert("language_phrases", $phrase_data))
	{
		$output -> add($template_admin -> normal_error($lang['plugin_addhook_lang_error']));
		page_main();
		return;
	}

	// ******************
	// Add hook page name phrase
	// ******************
	if($input['pagename'])
	{
		
		$phrase_data['variable_name'] = "plugins_hooks_files_".$input['pageid'];
		$phrase_data['text'] = $input['pagename'];
		$phrase_data['default_text'] = $input['pagename'];
	
		if(!$db -> basic_insert("language_phrases", $phrase_data))
		{
			$output -> add($template_admin -> normal_error($lang['plugin_addhook_lang_error']));
			page_main();
			return;
		}

	}
	
	require ROOT."admin/common/funcs/languages.funcs.php";
	
	build_language_files(LANG_ID, "admin_plugins");        	

	// Done, redirect
	$output -> redirect(ROOT."admin/index.php?m=plugins", $lang['plugin_addhook_done']);
	
}

?>