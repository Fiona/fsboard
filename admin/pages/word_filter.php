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
 * Word filter page
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


// Section was refactored to Massiv in Mensch
load_language_group("admin_wordfilter");

$output -> add_breadcrumb($lang['breadcrumb_wordfilter'], l("admin/word_filter/"));


// General page functions
include ROOT."admin/common/funcs/word_filter.funcs.php";


// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{

	case "add":
		page_add_word_filter();
		break;
                
	case "edit":
		page_edit_word_filter($page_matches['word_filter_id']);
		break;
                
	case "delete":
		page_delete_word_filter($page_matches['word_filter_id']);
		break;
                
	default:
		page_view_word_filter();

}


/**
 * Main table view
 */
function page_view_word_filter()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['wordfilter_main_title'];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("wordfilter").$lang['wordfilter_main_title'],
			"description" => $lang['wordfilter_main_message'],
			"no_results_message" => $lang['no_filters'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_wordfilter_button'],
				"url" => l("admin/word_filter/add/")
				),

			"db_table" => "wordfilter",
			"default_sort" => "word",

			"columns" => array(
				"word" => array(
					"name" => $lang['wordfilter_main_word'],
					"db_column" => 'word',
					"sortable" => True
					),
				"replacement" => array(
					"name" => $lang['wordfilter_main_replacement'],
					"db_column" => 'replacement',
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_word_filter_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the word filter view actions.
 *
 * @param object $row_data
 */
function table_view_word_filter_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['wordfilter_main_edit'],
			l("admin/word_filter/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['wordfilter_main_delete'],
			l("admin/word_filter/delete/".$row_data['id']."/")
			)
		);

}


/**
 * Page for creating a new word filter
 */
function page_add_word_filter()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_wordfilter_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_wordfilter_add'],
		l("admin/word_filter/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_word_filter("add")
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing word filters
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_word_filter($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "word_filters_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("wordfilter")
					),
				"initial_data" => $initial_data
				),

			"#word" => array(
				"name" => $lang['add_wordfilter_word'],
				"type" => "text",
				"required" => True
				),
			"#replacement" => array(
				"name" => $lang['add_wordfilter_replacement'],
				"type" => "text",
				"required" => True
				),
			"#perfect_match" => array(
				"name" => $lang['add_wordfilter_perfect'],
				"description" => $lang['add_wordfilter_perfect_desc'],
				"type" => "yesno"
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_wordfilter_title'];
		$form_data['meta']['description'] = $lang['add_wordfilter_message'];
		$form_data['meta']['validation_func'] = "form_add_word_filter_validate";
		$form_data['meta']['complete_func'] = "form_add_word_filter_complete";
		$form_data['#submit']['value'] = $lang['add_wordfilter_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_wordfilter_title'];
		$form_data['meta']['validation_func'] = "form_edit_word_filter_validate";
		$form_data['meta']['complete_func'] = "form_edit_word_filter_complete";
		$form_data['#submit']['value'] = $lang['edit_wordfilter_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for adding word filters
 *
 * @param object $form
 */
function form_add_word_filter_validate($form)
{

	global $lang;

	if(word_filter_get_word_filter_by_word($form -> form_state['#word']['value']) !== False)
		$form -> set_error("word", $lang['wordfilter_word_already_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding word filters
 *
 * @param object $form
 */
function form_add_word_filter_complete($form)
{

	global $lang, $output;

	// Add the filter
	$new_id = word_filter_add_word_filter(
        array(
			"word" 			=> $form -> form_state['#word']['value'],
			"replacement" 	=> $form -> form_state['#replacement']['value'],
			"perfect_match" => $form -> form_state['#perfect_match']['value'],
			)
		);

	if($new_id === False)
		return False;

	// Log
	log_admin_action(
		"word_filter",
		"add",
		"Added new word filter ".$form -> form_state['#word']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/word_filter/"),
		$lang['wordfilter_created_sucessfully']
		);

}


/**
 * Page for editing an existing word filter
 */
function page_edit_word_filter($filter_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the word filter
	$filter_info = word_filter_get_word_filter_by_id($filter_id);

	if($filter_info === False)
	{
		$output -> set_error_message($lang['invalid_wordfilter_id']);
		page_view_word_filter();
		return;
	}

	// Show the page
	$output -> page_title = $lang['edit_wordfilter_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_wordfilter_edit'],
		l("admin/word_filter/edit/".$filter_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_word_filter("edit", $filter_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing word filters
 *
 * @param object $form
 */
function form_edit_word_filter_validate($form)
{

	global $lang;

	if(word_filter_get_word_filter_by_word($form -> form_state['#word']['value'], $form -> form_state['meta']['initial_data']['id']) !== False)
		$form -> set_error("word", $lang['wordfilter_word_already_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing word filters
 *
 * @param object $form
 */
function form_edit_word_filter_complete($form)
{

	global $lang, $output;

	// Edit the word filter
	$update = word_filter_edit_word_filter(
		$form -> form_state['meta']['initial_data']['id'],
        array(
			"word" 			=> $form -> form_state['#word']['value'],
			"replacement" 	=> $form -> form_state['#replacement']['value'],
			"perfect_match" => $form -> form_state['#perfect_match']['value']
			)
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"word_filter",
		"edit",
		"Edited word filter: ".$form -> form_state['#word']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/word_filter/"),
		$lang['wordfilter_edited_sucessfully']
		);

}


/**
 * Confirmation page to remove a word filter.
 *
 * @var $filter_id ID of the word filter we're deleting.
 */
function page_delete_word_filter($filter_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the word filter
	$filter_info = word_filter_get_word_filter_by_id($filter_id);

	if($filter_info === False)
	{
		$output -> set_error_message($lang['invalid_wordfilter_id']);
		page_view_word_filter();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_wordfilter_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_wordfilter_delete'],
		l("admin/word_filter/delete/".$filter_id."/")
		);


	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("wordfilter"),
				"description" => $output -> replace_number_tags(
					$lang['delete_wordfilter_confirm'],
					sanitise_user_input($filter_info['word'])
					),
				"callback" => "delete_wordfilter_complete",
				"arguments" => array($filter_id, $filter_info['word']),
				"confirm_redirect" => l("admin/word_filter/"),
				"cancel_redirect" => l("admin/word_filter/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a word filter
 *
 * @param int $filter_id The ID of the word filter being deleted.
 * @param string $word Word for the filter. (For logging.)
 */
function delete_wordfilter_complete($filter_id, $word)
{

	global $output, $lang;

	// Delete and check the response
	$return = word_filter_delete_word_filter($filter_id);

	if($return === True)
	{

        // Log it
        log_admin_action("word_filter", "delete", "Deleted word filter: ".$word);
		return True;

	}
	else
		return False;

}

?>
