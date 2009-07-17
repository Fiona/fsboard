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
 * Admin area - Promotions
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


// Load language phrases for this page
load_language_group("admin_promotions");


// General page functions
include ROOT."admin/common/funcs/promotions.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_promotions'], l("admin/promotions/"));

// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{

	case "add":
		page_add_promotions();
		break;

	case "edit":
		page_edit_promotions($page_matches['promotion_id']);
		break;

	case "delete":
		page_delete_promotions($page_matches['promotion_id']);
		break;
               
	default:
		page_view_promotions();

}


function page_view_promotions()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['promotions_main_title'];

	// Define the table
	require_once ROOT."admin/common/funcs/user_groups.funcs.php";
	$user_group_data = user_groups_get_groups();

	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("usergroups").$lang['promotions_main_title'],
			"description" => $lang['promotions_main_message'],
			"no_results_message" => $lang['no_promotions'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_promotions_button'],
				"url" => l("admin/promotions/add/")
				),

			"db_table" => "promotions",
			"default_sort" => "group_id",
			"db_extra_what" => array(
				"`group_id`", "`group_to_id`", "`promotion_type`",
				"`use_reputation`", "`reputation`"
				),

			"columns" => array(
				"group_id" => array(
					"name" => $lang['promotions_group_from'],
					"content_callback" => 'table_view_promotions_group_id_callback',
					"content_callback_parameters" => array($user_group_data),
					"sortable" => True
					),
				"group_to_id" => array(
					"name" => $lang['promotions_group_to'],
					"content_callback" => 'table_view_promotions_group_to_id_callback',
					"content_callback_parameters" => array($user_group_data),
					"sortable" => True
					),
				"promotion_type" => array(
					"name" => $lang['promotions_type'],
					"content_callback" => 'table_view_promotions_type_callback',
					"sortable" => True
					),
				"posts" => array(
					"name" => $lang['promotions_posts'],
					"db_column" => "posts"
					),
				"reputation" => array(
					"name" => $lang['promotions_reputation'],
					"db_column" => "reputation"
					),
				"days_registered" => array(
					"name" => $lang['promotions_days_registered'],
					"db_column" => "days_registered"
					),

				"actions" => array(
					"content_callback" => 'table_view_promotions_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}




/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the promotions view group id.
 *
 * @param object $form
 */
function table_view_promotions_group_id_callback($row_data, $user_group_data)
{
	return $user_group_data[$row_data['group_id']]['name'];
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the promotions view group to id.
 *
 * @param object $form
 */
function table_view_promotions_group_to_id_callback($row_data, $user_group_data)
{
	return $user_group_data[$row_data['group_to_id']]['name'];
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the promotions view type.
 *
 * @param object $form
 */
function table_view_promotions_type_callback($row_data)
{
	global $lang;
	return $lang['promotions_main_promotion_type_'.$row_data['promotion_type']];
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the promotions view actions.
 *
 * @param object $form
 */
function table_view_promotions_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['promotions_main_edit'],
			l("admin/promotions/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['promotions_main_delete'],
			l("admin/promotions/delete/".$row_data['id']."/")
			)
		);

}



/**
 * Page for creating a new promotion.
 */
function page_add_promotions()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_promotions_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_promotions_add'],
		l("admin/promotions/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_promotions("add")
		);

	form_add_edit_promotions_add_extra_fields($form);

	$output -> add($form -> render());

}



/**
 * Page to edit an existing promotion
 *
 * @param int $group_id Integer of the promotion we want to edit.
 */
function page_edit_promotions($promotion_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the promotion
	$promotion_info = promotions_get_promotion_by_id($promotion_id);

	if($promotion_info === False)
	{
		$output -> set_error_message($lang['edit_promotions_invalid_id']);
		page_view_promotions();
		return;
	}

	$output -> page_title = $lang['edit_promotions_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_promotions_edit'],
		l("admin/promotions/edit/".$promotion_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_promotions("edit", $promotion_info)
		);

	form_add_edit_promotions_add_extra_fields($form);

	$output -> add($form -> render());

}



/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing promotions
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_promotions($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Prepare the data for dropdowns
	$group_options = array();
	$promotion_type_options = array();
	$comparison_options = array();

	include ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();

	foreach($groups as $g_id => $g_info)
		$group_options[$g_id] = $g_info['name'];

	for($a = 0; $a <= 1; $a++)
	{
		$promotion_type_options[$a] = $lang['add_promotions_promotion_type_'.$a];
		$comparison_options[$a] = $lang['add_promotions_reputation_comparison_'.$a];
	}

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "user_groups_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("usergroups")
					),
				"initial_data" => $initial_data,
				"validation_func" => "form_add_edit_promotions_validate"
				),
			"#group_id" => array(
				"name" => $lang['add_promotions_group_id'],
				"type" => "dropdown",
				"required" => True,
				"options" => $group_options
				),
			"#promotion_type" => array(
				"name" => $lang['add_promotions_promotion_type'],
				"type" => "dropdown",
				"required" => True,
				"options" => $promotion_type_options
				),
			"#group_to_id" => array(
				"name" => $lang['add_promotions_group_to_id'],
				"type" => "dropdown",
				"required" => True,
				"options" => $group_options
				),
			"#use_posts" => array(
				"name" => $lang['add_promotions_posts'],
				"description" => $lang['add_promotions_posts_message'],
				"type" => "checkbox"
				),
			"#use_reputation" => array(
				"name" => $lang['add_promotions_reputation'],
				"description" => $lang['add_promotions_reputation_message'],
				"type" => "checkbox"
				),
			"#reputation_comparison" => array(
				"name" => $lang['add_promotions_reputation_comparison'],
				"type" => "dropdown",
				"required" => True,
				"options" => $comparison_options
				),
			"#use_days_registered" => array(
				"name" => $lang['add_promotions_days_registered'],
				"description" => $lang['add_promotions_days_registered_message'],
				"type" => "checkbox"
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_promotions_title'];
		$form_data['meta']['description'] = $lang['add_promotions_message'];
		$form_data['meta']['complete_func'] = "form_add_promotions_complete";
		$form_data['#submit']['value'] = $lang['add_promotions_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_promotions_title'];
		$form_data['meta']['complete_func'] = "form_edit_promotions_complete";
		$form_data['#submit']['value'] = $lang['edit_promotions_submit'];
	}

	return $form_data;

}


/**
 * I need to force a couple of extra fields into this form, unfortunately to do
 * this I also need access to an already created form_state. So this just gets
 * called after the form is created.
 *
 * @var object &$form
 */
function form_add_edit_promotions_add_extra_fields(&$form)
{

	global $template_global_forms;

	$init_items = array("posts", "reputation", "days_registered");

	foreach($init_items as $item)
	{

		if(isset($_POST[$item]))
			$value = $_POST[$item];
		elseif(isset($form -> form_state['meta']['initial_data'][$item]))
			$value = $form -> form_state['meta']['initial_data'][$item];
		else
			$value = "";

		$form -> form_state['#use_'.$item]['extra_field_contents_right'] =
			$template_global_forms -> form_field_text(
				$item,
				array(
					"value" => $value,
					"size" => 7
					),
				$form -> form_state
				);

	}

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for adding and editing promotions
 *
 * @param object $form
 */
function form_add_edit_promotions_validate($form)
{

	global $lang;

	// Selected user groups can't be the same
	if($form -> form_state['#group_id']['value'] == $form -> form_state['#group_to_id']['value'])
		$form -> set_error("group_id", $lang['add_promotions_same_group']);

	// If no rules have been selected
	if(!$form -> form_state['#use_posts']['value'] && !$form -> form_state['#use_reputation']['value'] && !$form -> form_state['#use_days_registered']['value'])
		$form -> set_error(NULL, $lang['add_promotions_no_rules']);

	// Check group moving from exists
	$group_from = user_groups_get_group_by_id($form -> form_state['#group_id']['value']);

	if($group_from == False)
		$form -> set_error("group_id", $lang['add_promotions_group_id_no_exist']);

	// Check group moving to exists
	$group_to = user_groups_get_group_by_id($form -> form_state['#group_to_id']['value']);

	if($group_from == False)
		$form -> set_error("group_to_id", $lang['add_promotions_group_to_id_no_exist']);

	// Keep these around - they are needed for the log
	$form -> form_state['meta']['data_group_from_name'] = $group_from['name'];
	$form -> form_state['meta']['data_group_to_name'] = $group_to['name'];

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding promotions
 *
 * @param object $form
 */
function form_add_promotions_complete($form)
{

	global $lang, $output;

	// Try and add the promotion
	$new_promotion_id = promotions_add_promotion(
		array(
			"group_id" => $form -> form_state['#group_id']['value'],
			"promotion_type" => $form -> form_state['#promotion_type']['value'],
			"group_to_id" => $form -> form_state['#group_to_id']['value'],
			"use_posts" => $form -> form_state['#use_posts']['value'],
			"use_reputation" => $form -> form_state['#use_reputation']['value'],
			"use_days_registered" => $form -> form_state['#use_days_registered']['value'],
			"reputation_comparison" => $form -> form_state['#reputation_comparison']['value'],
			"posts" => intval($_POST['posts']),
			"reputation" => intval($_POST['reputation']),
			"days_registered" => intval($_POST['days_registered'])
			)
		);

	if($new_promotion_id === False)
		return False;

	// Log
	log_admin_action(
		"promotions",
		"add",
		"Added promotion: ".$form -> form_state['meta']['data_group_from_name']." to ".$form -> form_state['meta']['data_group_to_name']
		);

	// Redirect...
	$output -> redirect(
		l("admin/promotions/"),
		$lang['add_promotions_created_successfully']
		);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing promotions
 *
 * @param object $form
 */
function form_edit_promotions_complete($form)
{

	global $lang, $output;

	// Try and edit the promotion
	$update = promotions_edit_promotion(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"group_id" => $form -> form_state['#group_id']['value'],
			"promotion_type" => $form -> form_state['#promotion_type']['value'],
			"group_to_id" => $form -> form_state['#group_to_id']['value'],
			"use_posts" => $form -> form_state['#use_posts']['value'],
			"use_reputation" => $form -> form_state['#use_reputation']['value'],
			"use_days_registered" => $form -> form_state['#use_days_registered']['value'],
			"reputation_comparison" => $form -> form_state['#reputation_comparison']['value'],
			"posts" => intval($_POST['posts']),
			"reputation" => intval($_POST['reputation']),
			"days_registered" => intval($_POST['days_registered'])
			)
		);

	if($update !== True)
		return False;

	// Log
	log_admin_action(
		"promotions",
		"edit",
		"Edited promotion: ".$form -> form_state['meta']['data_group_from_name']." to ".$form -> form_state['meta']['data_group_to_name']
		);

	// Redirect...
	$output -> redirect(
		l("admin/promotions/"),
		$lang['edit_promotions_edited_successfully']
		);

}


/**
 * Confirmation page to remove a promotion.
 *
 * @var $promotion_id ID of the promotion we're deleting.
 */
function page_delete_promotions($promotion_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the promotion
	$promotion_info = promotions_get_promotion_by_id($promotion_id);

	if($promotion_info === False)
	{
		$output -> set_error_message($lang['edit_promotions_invalid_id']);
		page_view_promotions();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_promotion_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_promotions_delete'],
		l("admin/promotions/delete/".$promotion_id."/")
		);

	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("usergroups"),
				"description" => $lang['delete_promotion_message'],
				"callback" => "promotions_delete_promotion_complete",
				"arguments" => array($promotion_id),
				"confirm_redirect" => l("admin/promotions/"),
				"cancel_redirect" => l("admin/promotions/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a promotions
 *
 * @param int $promotion_id The ID of the promotion being deleted.
 */
function promotions_delete_promotion_complete($promotion_id)
{

	global $output, $lang;

	// Delete and check the response
	$return = promotions_delete_promotion($promotion_id);

	if($return === True)
	{

        // Log it
        log_admin_action("promotions", "delete", "Deleted promotion.");
		return True;

	}
	else
		return False;

}

?>