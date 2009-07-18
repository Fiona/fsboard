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
 * Admin area - User titles
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
load_language_group("admin_titles");


// General page functions
include ROOT."admin/common/funcs/titles.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_titles'], l("admin/titles/"));

// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{

	case "add":
		page_add_titles();
		break;

	case "edit":
		page_edit_titles($page_matches['title_id']);
		break;

	case "delete":
		page_delete_titles($page_matches['title_id']);
		break;
               
	default:
		page_view_titles();

}


/**
 * Main table view
 */
function page_view_titles()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['titles_main_title'];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("titles_insignia_reputation").$lang['titles_main_title'],
			"description" => $lang['titles_main_message'],
			"no_results_message" => $lang['no_titles'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_title_button'],
				"url" => l("admin/titles/add/")
				),

			"db_table" => "user_titles",
			"default_sort" => "min_posts",

			"columns" => array(
				"title" => array(
					"name" => $lang['titles_main_name'],
					"db_column" => "title",
					"sortable" => True
					),
				"min_posts" => array(
					"name" => $lang['titles_main_posts'],
					"db_column" => "min_posts",
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_titles_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the titles view actions.
 *
 * @param object $form
 */
function table_view_titles_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['titles_main_edit'],
			l("admin/titles/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['titles_main_delete'],
			l("admin/titles/delete/".$row_data['id']."/")
			)
		);

}


/**
 * Page for creating a new title.
 */
function page_add_titles()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_titles_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_titles_add'],
		l("admin/titles/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_titles("add")
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing user titles
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_titles($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "user_titles_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("titles_insignia_reputation")
					),
				"initial_data" => $initial_data
				),

			"#title" => array(
				"name" => $lang['add_titles_name'],
				"type" => "text",
				"required" => True
				),
			"#min_posts" => array(
				"name" => $lang['add_titles_min_posts'],
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
		$form_data['meta']['title'] = $lang['add_titles_title'];
		$form_data['meta']['description'] = $lang['add_titles_message'];
		$form_data['meta']['complete_func'] = "form_add_titles_complete";
		$form_data['#submit']['value'] = $lang['add_titles_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_titles_title'];
		$form_data['meta']['complete_func'] = "form_edit_titles_complete";
		$form_data['#submit']['value'] = $lang['edit_titles_submit'];
	}

	return $form_data;

}



/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding titles
 *
 * @param object $form
 */
function form_add_titles_complete($form)
{

	global $lang, $output;

	// Try and add the title
	$new_title_id = titles_add_title(
		array(
			"title" => $form -> form_state['#title']['value'],
			"min_posts" => $form -> form_state['#min_posts']['value'],
			)
		);

	if($new_title_id === False)
		return False;

	// Log
	log_admin_action(
		"titles",
		"add",
		"Added user title: ".$form -> form_state['#title']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/titles/"),
		$lang['add_titles_created_sucessfully']
		);

}


/**
 * Page for editing an existing title.
 */
function page_edit_titles($title_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the title
	$title_info = titles_get_title_by_id($title_id);

	if($title_info === False)
	{
		$output -> set_error_message($lang['edit_titles_invalid_id']);
		page_view_titles();
		return;
	}

	// Show the page
	$output -> page_title = $lang['edit_titles_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_titles_edit'],
		l("admin/titles/edit/".$title_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_titles("edit", $title_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing titles
 *
 * @param object $form
 */
function form_edit_titles_complete($form)
{

	global $lang, $output;

	// Try and edit the title
	$update = titles_edit_title(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"title" => $form -> form_state['#title']['value'],
			"min_posts" => $form -> form_state['#min_posts']['value'],
			)
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"titles",
		"edit",
		"Edit user title: ".$form -> form_state['#title']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/titles/"),
		$lang['add_titles_created_sucessfully']
		);

}


/**
 * Confirmation page to remove a user title.
 *
 * @var $title_id ID of the title we're deleting.
 */
function page_delete_titles($title_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the title
	$title_info = titles_get_title_by_id($title_id);

	if($title_info === False)
	{
		$output -> set_error_message($lang['edit_titles_invalid_id']);
		page_view_titles();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_titles_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_titles_delete'],
		l("admin/titles/delete/".$title_id."/")
		);


	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("titles_insignia_reputation"),
				"description" => $output -> replace_number_tags(
					$lang['delete_titles_message'],
					sanitise_user_input($title_info['title'])
					),
				"callback" => "titles_delete_title_complete",
				"arguments" => array($title_id, $title_info['title']),
				"confirm_redirect" => l("admin/titles/"),
				"cancel_redirect" => l("admin/titles/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a user title
 *
 * @param int $title_id The ID of the title being deleted.
 * @param string $title Name of the title. (For logging.)
 */
function titles_delete_title_complete($title_id, $title)
{

	global $output, $lang;

	// Delete and check the response
	$return = titles_delete_title($title_id);

	if($return === True)
	{

        // Log it
        log_admin_action("titles", "delete", "Deleted title: ".$title);
		return True;

	}
	else
		return False;

}

?>
