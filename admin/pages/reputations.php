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
 * Admin page for reputations
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


// This file was refactored to BOOZE UP AND RIOT by Caustic
load_language_group("admin_reputations");


// General page functions
include ROOT."admin/common/funcs/reputations.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_reputations'], l("admin/reputations/"));

// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{

	case "add":
		page_add_reputations();
		break;

	case "edit":
		page_edit_reputations($page_matches['reputation_id']);
		break;

	case "delete":
		page_delete_reputations($page_matches['reputation_id']);
		break;
               
	default:
		page_view_reputations();

}



/**
 * Main table view
 */
function page_view_reputations()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['reputations_main_title'];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("titles_insignia_reputation").$lang['reputations_main_title'],
			"description" => $lang['reputations_main_message'],
			"no_results_message" => $lang['no_reputation'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_reputations_button'],
				"url" => l("admin/reputations/add/")
				),

			"db_table" => "user_reputations",
			"default_sort" => "min_rep",

			"columns" => array(
				"name" => array(
					"name" => $lang['reputations_main_name'],
					"db_column" => "name",
					"sortable" => True
					),
				"min_rep" => array(
					"name" => $lang['reputations_main_min_rep'],
					"db_column" => "min_rep",
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_reputations_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the reputations view actions.
 *
 * @param object $form
 */
function table_view_reputations_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['reputations_main_edit'],
			l("admin/reputations/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['reputations_main_delete'],
			l("admin/reputations/delete/".$row_data['id']."/")
			)
		);

}


/**
 * Page for creating a new reputation.
 */
function page_add_reputations()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_reputations_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_reputations_add'],
		l("admin/reputations/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_reputations("add")
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing reputations
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_reputations($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "reputations_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("titles_insignia_reputation")
					),
				"initial_data" => $initial_data
				),

			"#name" => array(
				"name" => $lang['add_reputations_name'],
				"type" => "text",
				"required" => True
				),
			"#min_rep" => array(
				"name" => $lang['add_reputations_min_rep'],
				"type" => "int",
				"required" => True
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_reputations_title'];
		$form_data['meta']['description'] = $lang['add_reputations_message'];
		$form_data['meta']['complete_func'] = "form_add_reputations_complete";
		$form_data['#submit']['value'] = $lang['add_reputations_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_reputations_title'];
		$form_data['meta']['complete_func'] = "form_edit_reputations_complete";
		$form_data['#submit']['value'] = $lang['edit_reputations_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding reputations
 *
 * @param object $form
 */
function form_add_reputations_complete($form)
{

	global $lang, $output;

	// Try and add the reputation
	$new_rep_id = reputations_add_reputation(
		array(
			"name" => $form -> form_state['#name']['value'],
			"min_rep" => $form -> form_state['#min_rep']['value'],
			)
		);

	if($new_rep_id === False)
		return False;

	// Log
	log_admin_action(
		"reputations",
		"add",
		"Added reputation: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/reputations/"),
		$lang['add_reputations_created_successfully']
		);

}


/**
 * Page for editing an existing reputation.
 */
function page_edit_reputations($reputation_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the reputation
	$reputation_info = reputations_get_reputation_by_id($reputation_id);

	if($reputation_info === False)
	{
		$output -> set_error_message($lang['edit_reputations_invalid_id']);
		page_view_reputations();
		return;
	}

	// Show the page
	$output -> page_title = $lang['edit_reputations_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_reputations_edit'],
		l("admin/reputations/edit/".$reputation_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_reputations("edit", $reputation_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing reputations
 *
 * @param object $form
 */
function form_edit_reputations_complete($form)
{

	global $lang, $output;

	// Try and edit the reputation
	$update = reputations_edit_reputation(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"name" => $form -> form_state['#name']['value'],
			"min_rep" => $form -> form_state['#min_rep']['value'],
			)
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"reputations",
		"edit",
		"Edit reputation: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/reputations/"),
		$lang['edit_reputations_edited_successfully']
		);

}


/**
 * Confirmation page to remove a reputation.
 *
 * @var $reputation_id ID of the rep we're deleting.
 */
function page_delete_reputations($reputation_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the rep
	$reputation_info = reputations_get_reputation_by_id($reputation_id);

	if($reputation_info === False)
	{
		$output -> set_error_message($lang['edit_reputations_invalid_id']);
		page_view_reputations();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_reputations_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_reputations_delete'],
		l("admin/reputations/delete/".$reputation_id."/")
		);


	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("titles_insignia_reputation"),
				"description" => $output -> replace_number_tags(
					$lang['delete_reputations_message'],
					sanitise_user_input($reputation_info['name'])
					),
				"callback" => "reputations_delete_reputation_complete",
				"arguments" => array($reputation_id, $reputation_info['name']),
				"confirm_redirect" => l("admin/reputations/"),
				"cancel_redirect" => l("admin/reputations/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a reputation
 *
 * @param int $reputation_id The ID of the rep being deleted.
 * @param string $name Name of the rep. (For logging.)
 */
function reputations_delete_reputation_complete($reputation_id, $name)
{

	global $output, $lang;

	// Delete and check the response
	$return = reputations_delete_reputation($reputation_id);

	if($return === True)
	{

        // Log it
        log_admin_action("reputations", "delete", "Deleted reputation: ".$name);
		return True;

	}
	else
		return False;

}

?>
