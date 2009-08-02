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
 * Admin page for attatchments and filetypes
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


// This file was refactored to Xotox
// Ah wonderful noise..
load_language_group("admin_attachments");


// General page functions
include ROOT."admin/common/funcs/attachments.funcs.php";


// Work out where we need to be
$page_type = $page_matches['page'];
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

if($page_type == "filetypes")
{

	$output -> add_breadcrumb($lang['breadcrumb_filetypes'], l("admin/attachments/filetypes"));

	switch($mode)
	{

		case "add":
			page_add_filetypes();
			break;

		case "edit":
			page_edit_filetypes($page_matches['filetype_id']);
			break;

		case "delete":
			page_delete_filetypes($page_matches['filetype_id']);
			break;

		default:
			page_view_filetypes();

	}

}



/**
 * Main table view
 */
function page_view_filetypes()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['filetypes_main_title'];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("attachments").$lang['filetypes_main_title'],
			"description" => $lang['filetypes_main_message'],
			"no_results_message" => $lang['filetypes_main_no_filetypes'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['filetypes_main_add_button'],
				"url" => l("admin/attachments/filetypes/add/")
				),

			"db_table" => "filetypes",
			"db_extra_what" => array("name", "extension", "icon_file", "use_avatar", "use_attachment", "enabled"),
			"default_sort" => "extension",

			"columns" => array(
				"extension" => array(
					"name" => $lang['filetypes_main_extension'],
					"content_callback" => 'table_view_filetypes_extension_callback',
					"sortable" => True
					),
				"icon" => array(
					"name" => $lang['filetypes_main_icon'],
					"content_callback" => 'table_view_filetypes_icon_callback',
					),
				"use_avatar" => array(
					"name" => $lang['filetypes_main_use_avatar'],
					"content_callback" => 'table_view_filetypes_use_avatar_callback',
					"sortable" => True
					),
				"use_attachment" => array(
					"name" => $lang['filetypes_main_use_attachment'],
					"content_callback" => 'table_view_filetypes_use_attachment_callback',
					"sortable" => True
					),
				"enabled" => array(
					"name" => $lang['filetypes_main_enabled'],
					"content_callback" => 'table_view_filetypes_enabled_callback',
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_filetypes_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the filetypes extension and name.
 *
 * @param object $row_data
 */
function table_view_filetypes_extension_callback($row_data)
{
	return $row_data['name']." (<strong>".$row_data['extension']."</strong>)";
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the filetypes icon
 *
 * @param object $row_data
 */
function table_view_filetypes_icon_callback($row_data)
{
	global $cache;
	return (
		$row_data['icon_file'] ?
		"<img src=\"".$cache -> cache['config']['board_url']."/".$row_data['icon_file']."\" alt=\"".$row_data['name']."\">" :
		"&nbsp;"
		);
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the filetypes "Avatar?" column.
 *
 * @param object $row_data
 */
function table_view_filetypes_use_avatar_callback($row_data)
{
	global $lang;
	return ($row_data['use_avatar'] ? $lang['yes'] : $lang['no']);
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the filetypes "Attachment?" column.
 *
 * @param object $row_data
 */
function table_view_filetypes_use_attachment_callback($row_data)
{
	global $lang;
	return ($row_data['use_attachment'] ? $lang['yes'] : $lang['no']);
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the filetypes "Enabled?" column.
 *
 * @param object $row_data
 */
function table_view_filetypes_enabled_callback($row_data)
{
	global $lang;
	return ($row_data['enabled'] ? $lang['yes'] : $lang['no']);
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the filetypes view actions.
 *
 * @param object $row_data
 */
function table_view_filetypes_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['filetypes_main_edit'],
			l("admin/attachments/filetypes/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['filetypes_main_delete'],
			l("admin/attachments/filetypes/delete/".$row_data['id']."/")
			)
		);

}


/**
 * Page for creating a new filetypes
 */
function page_add_filetypes()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_filetype_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_filetype_add'],
		l("admin/attachments/filetypes/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_filetypes("add")
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing filetypes
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 */
function form_add_edit_filetypes($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "filetypes_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("attachments")
					),
				"initial_data" => $initial_data
				),

			"#name" => array(
				"name" => $lang['add_filetype_name'],
				"type" => "text",
				"required" => True
				),
			"#extension" => array(
				"name" => $lang['add_filetype_extension'],
				"description" => $lang['add_filetype_extension_desc'],
				"type" => "text",
				"required" => True
				),
			"#mime_type" => array(
				"name" => $lang['add_filetype_mime_type'],
				"description" => $lang['add_filetype_mime_type_desc'],
				"type" => "text",
				"value" => "Content-type: unknown/unknown",
				"required" => True
				),
			"#icon_file" => array(
				"name" => $lang['add_filetype_icon_file'],
				"description" => $lang['add_filetype_icon_file_desc'],
				"type" => "text",
				"value" => "images/filetypes/unknown.gif",
				"required" => True
				),
			"#enabled" => array(
				"name" => $lang['add_filetype_enabled'],
				"description" => $lang['add_filetype_enabled_desc'],
				"type" => "yesno",
				"value" => "1"
				),
			"#use_avatar" => array(
				"name" => $lang['add_filetype_use_avatar'],
				"description" => $lang['add_filetype_use_avatar_desc'],
				"type" => "yesno",
				"value" => "1"
				),
			"#use_attachment" => array(
				"name" => $lang['add_filetype_use_attachment'],
				"description" => $lang['add_filetype_use_attachment_desc'],
				"type" => "yesno",
				"value" => "1"
				),
			"#max_file_size" => array(
				"name" => $lang['add_filetype_max_file_size'],
				"description" => $lang['add_filetype_max_file_size_desc'],
				"type" => "int"
				),
			"#max_width" => array(
				"name" => $lang['add_filetype_max_width'],
				"description" => $lang['add_filetype_max_width_desc'],
				"type" => "int"
				),
			"#max_height" => array(
				"name" => $lang['add_filetype_max_height'],
				"description" => $lang['add_filetype_max_height_desc'],
				"type" => "int"
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_filetype_title'];
		$form_data['meta']['validation_func'] = "form_add_filetypes_validate";
		$form_data['meta']['complete_func'] = "form_add_filetypes_complete";
		$form_data['#submit']['value'] = $lang['add_filetype_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['edit_filetype_title'];
		$form_data['meta']['validation_func'] = "form_edit_filetypes_validate";
		$form_data['meta']['complete_func'] = "form_edit_filetypes_complete";
		$form_data['#submit']['value'] = $lang['edit_filetype_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for adding filetypes
 *
 * @param object $form
 */
function form_add_filetypes_validate($form)
{

	global $lang, $db;

	$db -> basic_select(
		array(
			"what" => "id",
			"table" => "filetypes",
			"where" => "extension = '".$db -> escape_string($form -> form_state['#extension']['value'])."'",
			"limit" => 1
			)
		);

	if($db -> num_rows())
		$form -> set_error("extension", $lang['add_filetype_ext_exists']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding filetypes
 *
 * @param object $form
 */
function form_add_filetypes_complete($form)
{

	global $lang, $db, $output;

	// Add the filetype
	$new_id = attachments_filetypes_add_filetype(
        array(
			"name"           => $form -> form_state['#name']['value'],
			"extension"      => $form -> form_state['#extension']['value'],
			"mime_type"      => $form -> form_state['#mime_type']['value'],
			"use_avatar"     => $form -> form_state['#use_avatar']['value'],
			"use_attachment" => $form -> form_state['#use_attachment']['value'],
			"enabled"        => $form -> form_state['#enabled']['value'],
			"icon_file"      => $form -> form_state['#icon_file']['value'],
			"max_file_size"	 => $form -> form_state['#max_file_size']['value'],
			"max_width"      => $form -> form_state['#max_width']['value'],
			"max_height"     => $form -> form_state['#max_height']['value']
			)
		);

	if($new_id === False)
		return False;

	// Log
	log_admin_action(
		"attachments",
		"add_filetype",
		"Added filetype: ".$form -> form_state['#extension']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/attachments/filetypes/"),
		$lang['filetype_created_sucessfully']
		);

}


/**
 * Page for editing an existing filetype.
 */
function page_edit_filetypes($filetype_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the filetype
	$filetype_info = attachments_filetypes_get_filetype_by_id($filetype_id);

	if($filetype_info === False)
	{
		$output -> set_error_message($lang['invalid_filetype_id']);
		page_view_filetypes();
		return;
	}

	// Show the page
	$output -> page_title = $lang['edit_filetype_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_filetype_edit'],
		l("admin/attachments/filetypes/edit/".$filetype_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_filetypes("edit", $filetype_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing filetypes
 *
 * @param object $form
 */
function form_edit_filetypes_validate($form)
{

	global $lang, $db;

	// If we have changed the extension then we need to make sure it's not
	if($form -> form_state['meta']['initial_data']['extension'] == $form -> form_state['#extension']['value'])
		return;

	form_add_filetypes_validate($form);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing filetypes
 *
 * @param object $form
 */
function form_edit_filetypes_complete($form)
{

	global $lang, $db, $output;

	// Edit the filetype
	$update = attachments_filetypes_edit_filetype(
		$form -> form_state['meta']['initial_data']['id'],
        array(
			"name"           => $form -> form_state['#name']['value'],
			"extension"      => $form -> form_state['#extension']['value'],
			"mime_type"      => $form -> form_state['#mime_type']['value'],
			"use_avatar"     => $form -> form_state['#use_avatar']['value'],
			"use_attachment" => $form -> form_state['#use_attachment']['value'],
			"enabled"        => $form -> form_state['#enabled']['value'],
			"icon_file"      => $form -> form_state['#icon_file']['value'],
			"max_file_size"	 => $form -> form_state['#max_file_size']['value'],
			"max_width"      => $form -> form_state['#max_width']['value'],
			"max_height"     => $form -> form_state['#max_height']['value']
			)
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"attachments",
		"edit_filetype",
		"Edited filetype: ".$form -> form_state['#extension']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/attachments/filetypes/"),
		$lang['filetype_edited_sucessfully']
		);

}


/**
 * Confirmation page to remove a filetype.
 *
 * @var $reputation_id ID of the rep we're deleting.
 */
function page_delete_filetypes($filetype_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the filetype
	$filetype_info = attachments_filetypes_get_filetype_by_id($filetype_id);

	if($filetype_info === False)
	{
		$output -> set_error_message($lang['invalid_filetype_id']);
		page_view_filetypes();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_filetypes_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_filetypes_delete'],
		l("admin/attachments/filetypes/delete/".$filetype_id."/")
		);


	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("attachments"),
				"description" => $output -> replace_number_tags(
					$lang['delete_filetypes_message'],
					sanitise_user_input($filetype_info['name'])
					),
				"callback" => "attachments_delete_filetype_complete",
				"arguments" => array($filetype_id, $filetype_info['name']),
				"confirm_redirect" => l("admin/attachments/filetypes/"),
				"cancel_redirect" => l("admin/attachments/filetypes/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a filetype
 *
 * @param int $reputation_id The ID of the rep being deleted.
 * @param string $name Name of the rep. (For logging.)
 */
function attachments_delete_filetype_complete($filetype_id, $name)
{

	global $output, $lang;

	// Delete and check the response
	$return = attachments_filetypes_delete_filetype($filetype_id);

	if($return === True)
	{

        // Log it
        log_admin_action("attachments", "delete_filetype", "Deleted filetype: ".$name);
		return True;

	}
	else
		return False;

}

?>
