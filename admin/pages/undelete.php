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
 * Undelete tool
 * 
 * Everytime something is baleeted, FSBoard keeps a log.
 * This is a tool to get them back if you deleted something by accudent.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 * 
 * @started 23 Oct 2008
 * @edited 23 Oct 2008
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Message board says things to me behind my back
//***********************************************
load_language_group("admin_undelete");



//***********************************************
// Can haz
//***********************************************
$output -> add_breadcrumb($lang['breadcrumb_undelete'], "index.php?m=undelete");


$_GET['m2'] = (isset($_GET['m2'])) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

	case "doundelete":
		do_undelete();
		break;

	default:
		page_main();

}		



/**
 * List of items that can be undeleted
 */
function page_main()
{
	
	global $lang, $output, $db;
	        
	// *********************
	// Set page title
	// *********************
	$output -> page_title = $lang['undelete_main_title'];
	
	// Create class
	$table = new table_generate;
	$form = new form_generate;
	
	// ********************
	// Start table
	// ********************
    $output -> add(
		$form -> start_form("undelete_main_form", ROOT."admin/index.php?m=undelete&amp;m2=doundelete", "post").
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                
		$table -> add_top_table_header($lang['undelete_main_title'], 4, "undelete").
		$table -> add_basic_row($lang['undelete_main_message'], "normalcell", "", "left").
		
		$table -> add_row(
			array(
				array("&nbsp;", "5%"),
				array($lang['undelete_main_table'], "30%"),
				array($lang['undelete_main_action'], "50%"),
				array($lang['undelete_main_time'], "15%")				
			),
		"strip2")
	);
	
	$db -> basic_select(array(
		"table" => "undelete",
		"what" => "`id`,`table`,`action`,`time`",
		"order" => "`time`"
	));
	
	if(!$db -> num_rows())
		$output -> add(
			$table -> add_basic_row("<b>".$lang['no_deleted_items']."</b>", "normalcell",  "padding : 10px", "center")
		);        
	else
	{
		
		while($undelete = $db -> fetch_array())
			$output -> add(
				$table -> add_row(
					array(
						$form -> input_checkbox("undelete[".$undelete['id']."]", "1"),
						$undelete['table'],
						$undelete['action'],
						date("M j, G:s", $undelete['time'])
					)
				, "normalcell")
			);
					
	}
	
	// ********************
	// End table
	// ********************
	$output -> add(
		$table -> add_submit_row($form, "undelete_submit", $lang['undelete_button']).
		$table -> end_table().
		$form -> end_form()		
	);
	
}


/**
 * Restore items that have been checked 
 */
function do_undelete()
{
	
	global $lang, $output, $cache, $db, $template_admin;
	
	if(!isset($_POST['undelete']) || !count($_POST['undelete']) ||
		!isset($_POST['undelete_submit']))
	{
		$output -> add($template_admin -> normal_error($lang['undelete_error_select_items']));
		page_main();
		return;		
	}
	
	$undelete_items = $_POST['undelete'];
	$getting_you_guys = array();
	
	foreach($undelete_items as $id => $junk)
		$getting_you_guys[] = (int)$id;
		
	$getting_you_guys = implode(",", $getting_you_guys);
	
	$res = $db -> basic_select(array(
		"table" => "undelete",
		"where" => "id IN(".$getting_you_guys.")",
		"limit" => count($undelete_items)
	));
	
	if(!$db -> num_rows())
	{
		$output -> add($template_admin -> critical_error($lang['undelete_cant_select_items']));
		page_main();
		return;		
	}
	
	while($item = $db -> fetch_array($res))
		$db -> basic_insert(array(
			"table" => $item['table'],
			"data" => unserialize($item['data'])
		));

	$db -> basic_delete(array(
		"table" => "undelete",
		"where" => "id IN(".$getting_you_guys.")",
		"limit" => count($undelete_items)		
	));
	
	$cache -> update_cache("ALL");

	$output -> add($template_admin -> message($lang['undelete_completed_title'], $lang['undelete_completed_message']));
	
}

?>