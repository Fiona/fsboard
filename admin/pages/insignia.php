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
 * Admin area - Post insignia
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
load_language_group("admin_insignia");


// General page functions
include ROOT."admin/common/funcs/insignia.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_insignia'], l("admin/insignia/"));


// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{

	case "add":
		page_add_insignia();
		break;

	case "edit":
		page_edit_insignia($page_matches['insignia_id']);
		break;

	case "delete":
		page_delete_insignia($page_matches['insignia_id']);
		break;
               
	default:
		page_view_insignia();

}


/**
 * Main table view
 */
function page_view_insignia()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['insignia_main_title'];

	// Define the table
	require_once ROOT."admin/common/funcs/user_groups.funcs.php";
	$user_group_data = user_groups_get_groups();

	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("titles_insignia_reputation").$lang['insignia_main_title'],
			"description" => $lang['insignia_main_message'],
			"no_results_message" => $lang['no_insignia'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_insignia_button'],
				"url" => l("admin/insignia/add/")
				),

			"db_table" => "user_insignia",
			"default_sort" => "user_group",
			"db_extra_what" => array(
				"`user_group`", "`text`", "`repeat_no`", "`image`", "`newline`"
				),

			"columns" => array(
				"user_group" => array(
					"name" => $lang['insignia_main_user_group'],
					"content_callback" => 'table_view_insignia_user_group_callback',
					"content_callback_parameters" => array($user_group_data),
					"sortable" => True
					),
				"display" => array(
					"name" => $lang['insignia_main_insignia'],
					"content_callback" => 'table_view_insignia_display_callback'
					),
				"min_posts" => array(
					"name" => $lang['insignia_main_posts'],
					"db_column" => "min_posts",
					"sortable" => True
					),
				"newline" => array(
					"name" => $lang['insignia_main_newline'],
					"content_callback" => 'table_view_insignia_newline_callback',
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_insignia_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the insignia view actions.
 *
 * @param object $form
 */
function table_view_insignia_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['insignia_main_edit'],
			l("admin/insignia/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['insignia_main_delete'],
			l("admin/insignia/delete/".$row_data['id']."/")
			)
		);

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the insignia user group.
 *
 * @param array $row_data
 * @param array $user_group_data
 */
function table_view_insignia_user_group_callback($row_data, $user_group_data)
{

	global $lang;

	if($row_data['user_group'] == "-1")
		return $lang['insignia_main_all_groups'];

	return $user_group_data[$row_data['user_group']]['name'];

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the insignia display
 *
 * @param array $row_data
 */
function table_view_insignia_display_callback($row_data)
{

	global $cache;

	if($row_data['repeat_no'] == 0)
		return "&nbsp;";

	if($row_data['text'])
		$insignia = $row_data['text'];
	else
		$insignia = "<img src=\"".$cache -> cache['config']['board_url'].$row_data['image']."\" />";

	return str_repeat($insignia,  $row_data['repeat_no']);

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the insignia newline column
 *
 * @param array $row_data
 */
function table_view_insignia_newline_callback($row_data)
{

	global $lang;

	return ($row_data['newline'] ? $lang['yes'] : $lang['no']);

}


/**
 * Page for creating a new insignia
 */
function page_add_insignia()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_insignia_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_insignia_add'],
		l("admin/insignia/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_insignia("add")
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing insignia
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_insignia($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Prepare dropdown data
	include ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();

	$group_options = array("-1" => $lang['add_insignia_groups_dropdown_all']);

	foreach($groups as $g_id => $g_info)
		$group_options[$g_id] = $g_info['name'];

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "insignia_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("titles_insignia_reputation")
					),
				"validation_func" => "form_add_edit_insignia_validate",
				"initial_data" => $initial_data
				),

			"#min_posts" => array(
				"name" => $lang['add_insignia_min_posts'],
				"type" => "int"
				),
			"#user_group" => array(
				"name" => $lang['add_insignia_user_group'],
				"type" => "dropdown",
				"options" => $group_options
				),
			"#newline" => array(
				"name" => $lang['add_insignia_newline'],
				"type" => "yesno"
				),
			"#repeat_no" => array(
				"name" => $lang['add_insignia_repeat_no'],
				"type" => "int"
				),
			"#image" => array(
				"name" => $lang['add_insignia_image'],
				"type" => "text"
				),
			"#text" => array(
				"name" => $lang['add_insignia_text'],
				"type" => "text"
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_insignia_title'];
		$form_data['meta']['description'] = $lang['add_insignia_message'];
		$form_data['meta']['complete_func'] = "form_add_insignia_complete";
		$form_data['#submit']['value'] = $lang['add_insignia_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_insignia_title'];
		$form_data['meta']['complete_func'] = "form_edit_insignia_complete";
		$form_data['#submit']['value'] = $lang['edit_insignia_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for adding and editing insignia
 *
 * @param object $form
 */
function form_add_edit_insignia_validate($form)
{

	global $lang;

	// Check we have at least an image or text
	if(!trim($form -> form_state['#image']['value']) && !trim($form -> form_state['#text']['value']))
		$form -> set_error(NULL, $lang['add_insignia_fill_in_something']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding insignia
 *
 * @param object $form
 */
function form_add_insignia_complete($form)
{

	global $lang, $output;

	// Try and add the insignia
	$new_insignia_id = insignia_add_insignia(
		array(
			"min_posts" => $form -> form_state['#min_posts']['value'],
			"user_group" => $form -> form_state['#user_group']['value'],
			"newline" => $form -> form_state['#newline']['value'],
			"repeat_no" => $form -> form_state['#repeat_no']['value'],
			"image" => $form -> form_state['#image']['value'],
			"text" => $form -> form_state['#text']['value']
			)
		);

	if($new_insignia_id === False)
		return False;

	// Log
	log_admin_action("insignia", "add", "Added insignia ".$new_insignia_id);

	// Redirect...
	$output -> redirect(
		l("admin/insignia/"),
		$lang['add_insignia_created_sucessfully']
		);

}


/**
 * Page for editing an existing insignia
 */
function page_edit_insignia($insignia_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the insignia
	$insignia_info = insignia_get_insignia_by_id($insignia_id);

	if($insignia_info === False)
	{
		$output -> set_error_message($lang['edit_insignia_invalid_id']);
		page_view_insignia();
		return;
	}

	$output -> page_title = $lang['edit_insignia_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_insignia_edit'],
		l("admin/insignia/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_insignia("edit", $insignia_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing insignia
 *
 * @param object $form
 */
function form_edit_insignia_complete($form)
{

	global $lang, $output;

	// Try and add the insignia
	$update = insignia_edit_insignia(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"min_posts" => $form -> form_state['#min_posts']['value'],
			"user_group" => $form -> form_state['#user_group']['value'],
			"newline" => $form -> form_state['#newline']['value'],
			"repeat_no" => $form -> form_state['#repeat_no']['value'],
			"image" => $form -> form_state['#image']['value'],
			"text" => $form -> form_state['#text']['value']
			)
		);

	if($update !== True)
		return False;

	// Log
	log_admin_action("insignia", "edit", "Edited insignia ".$form -> form_state['meta']['initial_data']['id']);

	// Redirect...
	$output -> redirect(
		l("admin/insignia/"),
		$lang['edit_insignia_edited_sucessfully']
		);

}


/**
 * Confirmation page to remove an insignia.
 *
 * @var $promotion_id ID of the promotion we're deleting.
 */
function page_delete_insignia($insignia_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the insignia
	$insignia_info = insignia_get_insignia_by_id($insignia_id);

	if($insignia_info === False)
	{
		$output -> set_error_message($lang['edit_insignia_invalid_id']);
		page_view_insignia();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_insignia_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_insignia_delete'],
		l("admin/insignia/delete/".$insignia_id."/")
		);

	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("titles_insignia_reputation"),
				"description" => $lang['delete_insignia_message'],
				"callback" => "insignia_delete_insignia_complete",
				"arguments" => array($insignia_id),
				"confirm_redirect" => l("admin/insignia/"),
				"cancel_redirect" => l("admin/insignia/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting an insignia
 *
 * @param int $insignia_id The ID of the insignia being deleted.
 */
function insignia_delete_insignia_complete($insignia_id)
{

	global $output, $lang;

	// Delete and check the response
	$return = insignia_delete_insignia($insignia_id);

	if($return === True)
	{

        // Log it
        log_admin_action("insignia", "delete", "Deleted insignia.");
		return True;

	}
	else
		return False;

}

?>
