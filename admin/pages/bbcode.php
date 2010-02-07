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
 * Custom BBCode admin page 
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



// This file was refactored to Preemptive Strike 1.0
load_language_group("admin_bbcode");

$output -> add_breadcrumb($lang['breadcrumb_bbcode'], l("admin/bbcode/"));


// General page functions
include ROOT."admin/common/funcs/bbcode.funcs.php";


// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{

	case "add":
		page_add_bbcode();
		break;
                
	case "edit":
		page_edit_bbcode($page_matches['bbcode_id']);
		break;
                
	case "delete":
		page_delete_bbcode($page_matches['bbcode_id']);
		break;
                
	default:
		page_view_bbcode();

}


/**
 * Main table view
 */
function page_view_bbcode()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['bbcode_main_title'];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("bbcode").$lang['bbcode_main_title'],
			"description" => $lang['bbcode_main_message'],
			"no_results_message" => $lang['no_bbcode'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_bbcode_button'],
				"url" => l("admin/bbcode/add/")
				),

			"db_table" => "bbcode",
			"db_extra_what" => array("tag"),
			"default_sort" => "tag",

			"columns" => array(
				"name" => array(
					"name" => $lang['bbcode_main_name'],
					"db_column" => 'name',
					"sortable" => True
					),
				"tag" => array(
					"name" => $lang['bbcode_main_tag'],
					"content_callback" => 'table_view_bbcode_tag_callback',
					"sortable" => True
					),
				"example" => array(
					"name" => $lang['bbcode_main_example'],
					"db_column" => 'example'
					),
				"actions" => array(
					"content_callback" => 'table_view_bbcode_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

	// Test bbcode form
	$form = new form(
		array(
			"meta" => array(
				"name" => "test_bbcode",
				"title" => $lang['bbcode_test_title'],
				"description" => $lang['bbcode_test_description'],
				"complete_func" => "form_test_bbcode_complete"
				),
			"#text" => array(
				"type" => "textarea",
				"name" => $lang['bbcode_test_form_text'],
				"required" => True
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $lang['bbcode_test_form_submit']
				)
			)
		);

	$output -> add($form -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the custom bbcode view actions.
 *
 * @param object $row_data
 */
function table_view_bbcode_tag_callback($row_data)
{
	return "[<b>".$row_data['tag']."</b>]";
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the custom bbcode view actions.
 *
 * @param object $row_data
 */
function table_view_bbcode_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['bbcode_main_edit'],
			l("admin/bbcode/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['bbcode_main_delete'],
			l("admin/bbcode/delete/".$row_data['id']."/")
			)
		);

}



/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for testing bbcode
 *
 * @param object $form
 */
function form_test_bbcode_complete($form)
{

	global $lang, $output, $parser, $template_global;

	$parser -> options(True, True, True, True, True, True);

	$output -> add(
		$template_global -> message(
			$lang['bbcode_test_result'],
			$parser -> do_parser($form -> form_state['#text']['value'])
			)
		);

}


/**
 * Page for creating a new custom bbcode
 */
function page_add_bbcode()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_bbcode_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_bbcode_add'],
		l("admin/bbcode/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_bbcode("add")
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing bbcode
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_bbcode($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "bbcode_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("bbcode")
					),
				"initial_data" => $initial_data
				),

			"#name" => array(
				"name" => $lang['add_bbcode_name'],
				"type" => "text",
				"required" => True
				),
			"#description" => array(
				"name" => $lang['add_bbcode_description'],
				"description" => $lang['add_bbcode_description_desc'],
				"type" => "textarea"
				),
			"#example" => array(
				"name" => $lang['add_bbcode_example'],
				"description" => $lang['add_bbcode_example_desc'],
				"type" => "textarea"
				),
			"#button_image" => array(
				"name" => $lang['add_bbcode_button_image'],
				"description" => $lang['add_bbcode_button_image_desc'],
				"type" => "text"
				),
			"#tag" => array(
				"name" => $lang['add_bbcode_tag'],
				"description" => $lang['add_bbcode_tag_desc'],
				"type" => "text",
				"required" => True,
				'extra_field_contents_left' => "[ ",
				'extra_field_contents_right' => " ]"
				),
			"#use_param" => array(
				"name" => $lang['add_bbcode_use_param'],
				"description" => $lang['add_bbcode_use_param_desc'],
				"type" => "yesno"
				),
			"#replacement" => array(
				"name" => $lang['add_bbcode_replacement'],
				"description" => $lang['add_bbcode_replacement_desc'],
				"type" => "textarea",
				"required" => True
				),

			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_bbcode_title'];
		$form_data['meta']['description'] = $lang['add_bbcode_message'];
		$form_data['meta']['validation_func'] = "form_add_bbcode_validate";
		$form_data['meta']['complete_func'] = "form_add_bbcode_complete";
		$form_data['#submit']['value'] = $lang['add_bbcode_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_bbcode_title'];
		$form_data['meta']['validation_func'] = "form_edit_bbcode_validate";
		$form_data['meta']['complete_func'] = "form_edit_bbcode_complete";
		$form_data['#submit']['value'] = $lang['edit_bbcode_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for adding bbcode
 *
 * @param object $form
 */
function form_add_bbcode_validate($form)
{

	global $lang;

	if(custom_bbcode_get_bbcode_by_tag($form -> form_state['#tag']['value']) !== False)
		$form -> set_error("tag", $lang['bbcode_tag_already_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding bbcode
 *
 * @param object $form
 */
function form_add_bbcode_complete($form)
{

	global $lang, $output;

	// Add the filetype
	$new_id = custom_bbcode_add_bbcode(
        array(
			"name" 			=> $form -> form_state['#name']['value'],
			"description" 	=> $form -> form_state['#description']['value'],
			"example" 		=> $form -> form_state['#example']['value'],
			"button_image" 	=> $form -> form_state['#button_image']['value'],
			"tag" 			=> $form -> form_state['#tag']['value'],
			"use_param" 	=> $form -> form_state['#use_param']['value'],
			"replacement" 	=> $form -> form_state['#replacement']['value']
			)
		);

	if($new_id === False)
		return False;

	// Log
	log_admin_action(
		"bbcode",
		"add",
		"Added new custom BBCode ".$form -> form_state['#tag']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/bbcode/"),
		$lang['bbcode_created_sucessfully']
		);

}


/**
 * Page for editing an existing bbcode
 */
function page_edit_bbcode($bbcode_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the BBCode
	$bbcode_info = custom_bbcode_get_bbcode_by_id($bbcode_id);

	if($bbcode_info === False)
	{
		$output -> set_error_message($lang['invalid_bbcode_id']);
		page_view_bbcode();
		return;
	}

	// Show the page
	$output -> page_title = $lang['edit_bbcode_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_bbcode_edit'],
		l("admin/bbcode/edit/".$bbcode_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_bbcode("edit", $bbcode_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing bbcode
 *
 * @param object $form
 */
function form_edit_bbcode_validate($form)
{

	global $lang;

	if(custom_bbcode_get_bbcode_by_tag($form -> form_state['#tag']['value'], $form -> form_state['meta']['initial_data']['id']) !== False)
		$form -> set_error("tag", $lang['bbcode_tag_already_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing bbcode
 *
 * @param object $form
 */
function form_edit_bbcode_complete($form)
{

	global $lang, $output;

	// Edit the filetype
	$update = custom_bbcode_edit_bbcode(
		$form -> form_state['meta']['initial_data']['id'],
        array(
			"name" 			=> $form -> form_state['#name']['value'],
			"description" 	=> $form -> form_state['#description']['value'],
			"example" 		=> $form -> form_state['#example']['value'],
			"button_image" 	=> $form -> form_state['#button_image']['value'],
			"tag" 			=> $form -> form_state['#tag']['value'],
			"use_param" 	=> $form -> form_state['#use_param']['value'],
			"replacement" 	=> $form -> form_state['#replacement']['value']
			)
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"bbcode",
		"edit",
		"Edited custom BBCode: ".$form -> form_state['#tag']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/bbcode/"),
		$lang['bbcode_edited_sucessfully']
		);

}


/**
 * Confirmation page to remove a custom bbcode.
 *
 * @var $bbcode_id ID of the bbcode we're deleting.
 */
function page_delete_bbcode($bbcode_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the filetype
	$bbcode_info = custom_bbcode_get_bbcode_by_id($bbcode_id);

	if($bbcode_info === False)
	{
		$output -> set_error_message($lang['invalid_bbcode_id']);
		page_view_bbcode();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_bbcode_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_bbcode_delete'],
		l("admin/bbcode/delete/".$bbcode_id."/")
		);


	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("bbcode"),
				"description" => $output -> replace_number_tags(
					$lang['delete_bbcode_message'],
					sanitise_user_input($bbcode_info['name'])
					),
				"callback" => "delete_bbcode_complete",
				"arguments" => array($bbcode_id, $bbcode_info['name']),
				"confirm_redirect" => l("admin/bbcode/"),
				"cancel_redirect" => l("admin/bbcode/")
				)
			)
		);

}




/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a custom bbcode
 *
 * @param int $bbcode_id The ID of the bbcode being deleted.
 * @param string $name Name of the bbcode. (For logging.)
 */
function delete_bbcode_complete($bbcode_id, $name)
{

	global $output, $lang;

	// Delete and check the response
	$return = custom_bbcode_delete_bbcode($bbcode_id);

	if($return === True)
	{

        // Log it
        log_admin_action("bbcode", "delete", "Deleted bbcode: ".$name);
		return True;

	}
	else
		return False;

}


?>
