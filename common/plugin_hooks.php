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
 * Plugin hooks
 * 
 * This file is mostly just a big array of hooks
 * for the plugin system to use. 
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 * 
 * @started 13 Oct 2007
 * @edited 13 Oct 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


/*
 My reference:
	$lang['plugins_hooks_files_'.FILE];
	$lang['plugins_hooks_'.FILE.'_'.HOOK];
 */


$PLUGIN_HOOKS = array(

	// ****************************
	// Special hooks
	// ****************************
	"special" => array(
		"plugin_hooks" => array(
			"params" => '&$PLUGIN_HOOKS'
		),
		"install" => array(
			"params" => '$plugin_info'
		),
		"uninstall" => array(
			"params" => '$plugin_info'
		),
		"enable" => array(
			"params" => '$plugin_info'
		),
		"disable" => array(
			"params" => '$plugin_info'
		)
	),
	
	// ****************************
	// Registration page
	// ****************************
	"register" => array(
		"before_reg_form" => array(
			"params" => '&$entered_data',
			"return" => ''
		),
		"after_reg_form" => array(
			"params" => '&$entered_data',
			"return" => ''
		)
	)
	
);


// special case, new hooks
generate_plugin_function("special", "plugin_hooks", $PLUGIN_HOOKS['special']['plugin_hooks']);

hook_special_plugin_hooks($PLUGIN_HOOKS);



/*
 * The plugin system requires us to create functions
 * for all the hooks, thanks to eval this is pretty easy
 */
foreach($PLUGIN_HOOKS as $hook_file => $hook_names)
{

	if(!count($hook_names))
		continue;
		
	foreach($hook_names as $hook_name => $hook_info)
	{

		if($hook_file == "special" && $hook_name == "plugin_hooks")
			continue;
			
		generate_plugin_function($hook_file, $hook_name, $hook_info);
		
	}
	
}


/**
 * generate_plugin_function()
 * Used to eval the function information for each plugin hook
 *
 * @param string $hook_file File, or category of hook
 * @param string $hook_name Name of the hook in question
 * @param array $hook_info Array of info about hook, like params and return
 */
function generate_plugin_function($hook_file, $hook_name, $hook_info)
{
	
	global $cache;
	
	$hook_info['params_no_amp'] = str_replace("&", "", $hook_info['params']);
	 
	$evalled_stuff = '
function hook_'.$hook_file.'_'.$hook_name.'('.$hook_info['params'].')
{

	global $cache; 
	
	if(!isset($cache -> cache[\'plugins\']["'.$hook_file.':'.$hook_name.'"]))
		return '.(isset($hook_info['return']) ? $hook_info['return'] : "").';
	
	if(is_array($cache -> cache[\'plugins\']["'.$hook_file.':'.$hook_name.'"]) && count($cache -> cache[\'plugins\']["'.$hook_file.':'.$hook_name.'"]) > 0)
    {
    	
		foreach($cache -> cache[\'plugins\']["'.$hook_file.':'.$hook_name.'"] as $plugin_id)
		{
			
			if(file_exists(ROOT."plugins/plugin_id".$plugin_id."/'.$hook_file.'_'.$hook_name.'.php"))
			{
				
				include ROOT."plugins/plugin_id".$plugin_id."/'.$hook_file.'_'.$hook_name.'.php";

				'.((isset($hook_info['return']) && !empty($hook_info['return'])) ? $hook_info['return'].' = ' : "").'eval(\'p\'.$plugin_id.\'_'.$hook_file.'_'.$hook_name.'('.$hook_info['params_no_amp'].');\');
					
			}
		
		}
		
    }
    
	return '.(isset($hook_info['return']) ? $hook_info['return'] : "").';
	
}';

	eval($evalled_stuff);
		
}


?>