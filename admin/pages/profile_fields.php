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
 * Admin area - Custom profile fields
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


// Words
load_language_group("admin_profilefields");


// General page functions
include ROOT."admin/common/funcs/profile_fields.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_profilefields'], l("admin/profile_fields/"));


// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{
	case "add":
		page_add_profile_fields();
		break;

	case "edit":
		page_edit_profile_fields($page_matches['field_id']);
		break;

	case "delete":
		page_delete_profile_fields($page_matches['field_id']);
		break;

	default:
		page_view_profile_fields();
}


/**
 * Main view of all profile fields
 */
function page_view_profile_fields()
{

	global $lang, $output, $template_admin;

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("users").$lang['fields_main_title'],
			"description" => $lang['fields_main_message'],
			"no_results_message" => $lang['no_fields'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['add_field_button'],
				"url" => l("admin/profile_fields/add/")
				),

			"db_table" => "profile_fields",
			"default_sort" => "order",

			"db_extra_what" => array("`name`", "`description`"),

			"columns" => array(
				"name" => array(
					"name" => $lang['fields_main_name'],
					"content_callback" => 'table_view_fields_name_callback',
					"sortable" => True
					),
				"id" => array(
					"name" => $lang['fields_main_database_name'],
					"content_callback" => 'table_view_fields_id_callback',
					"sortable" => True
					),
				"order" => array(
					"name" => $lang['fields_main_order'],
					"db_column" => "order",
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_fields_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the profile view name.
 *
 * @param object $form
 */
function table_view_fields_name_callback($row_data)
{
	return (
		$row_data['name'].
		'<br /><p class="results_table_small_text">'.
		$row_data['description'].
		'</p>'
		);
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the profile view id.
 *
 * @param object $form
 */
function table_view_fields_id_callback($row_data)
{
	return "field_".$row_data['id'];
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the profile view actions.
 *
 * @param object $form
 */
function table_view_fields_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['fields_main_edit'],
			l("admin/profile_fields/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['fields_main_delete'],
			l("admin/profile_fields/delete/".$row_data['id']."/")
			)
		);

}


/**
 * Page to create a new profile field
 */
function page_add_profile_fields()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_field_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_profilefields_add'],
		l("admin/profile_fields/add/")
		);

	$form = new form(
		form_add_edit_profile_fields("add")
		);

	$output -> add($form -> render());

}


/**
 * Page to edit an existing profile field
 */
function page_edit_profile_fields($field_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the field
	$field_info = profile_fields_get_field_by_id($field_id);

	if($field_info === False)
	{
		$output -> set_error_message($lang['invalid_field_id']);
		page_view_profile_fields();
		return;
	}

	// Otherwise show the page
	$output -> page_title = $lang['edit_field_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_profilefields_edit'],
		l("admin/profile_fields/edit/".$field_id."/")
		);

	$form = new form(
		form_add_edit_profile_fields("edit", $field_info)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing profile fields
 *
 * @param string $type The type of request. "add" or "edit".
 */
function form_add_edit_profile_fields($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// Define the form
	$form_data = array(
			"meta" => array(
				"name" => "profile_field_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("users")
					),
				"validation_func" => "form_add_edit_profile_fields_validate"
				),
			"#name" => array(
				"name" => $lang['add_field_name'],
				"type" => "text",
				"required" => True
				),
			"#description" => array(
				"name" => $lang['add_field_description'],
				"type" => "text"
				),
			"#field_type" => array(
				"name" => $lang['add_field_field_type'],
				"type" => "dropdown",
				"options" => array(
					"text" => $lang['field_type_text'],
					"textbox" => $lang['field_type_textbox'],
					"yesno" => $lang['field_type_yesno'],
					"dropdown" => $lang['field_type_dropdown'],
					)
				),
			"#size" => array(
				"name" => $lang['add_field_size'],
				"description" => $lang['add_field_size_desc'],
				"type" => "int"
				),
			"#max_length" => array(
				"name" => $lang['add_field_max_length'],
				"type" => "int"
				),
			"#order" => array(
				"name" => $lang['add_field_order'],
				"description" => $lang['add_field_order_desc'],
				"type" => "int"
				),
			"#dropdown_values" => array(
				"name" => $lang['add_field_dropdown_values'],
				"description" => $lang['add_field_dropdown_values_desc'],
				"type" => "textarea"
				),
			"#dropdown_text" => array(
				"name" => $lang['add_field_dropdown_text'],
				"description" => $lang['add_field_dropdown_text_desc'],
				"type" => "textarea"
				),
			"#show_on_reg" => array(
				"name" => $lang['add_field_show_on_reg'],
				"type" => "yesno"
				),
			"#user_can_edit" => array(
				"name" => $lang['add_field_user_can_edit'],
				"description" => $lang['add_field_user_can_edit_desc'],
				"type" => "yesno",
				"value" => "1"
				),
			"#is_private" => array(
				"name" => $lang['add_field_is_private'],
				"description" => $lang['add_field_is_private_desc'],
				"type" => "yesno"
				),
			"#admin_only_field" => array(
				"name" => $lang['add_field_admin_only_field'],
				"description" => $lang['add_field_admin_only_field_desc'],
				"type" => "yesno"
				),
			"#must_be_filled" => array(
				"name" => $lang['add_field_must_be_filled'],
				"description" => $lang['add_field_must_be_filled_desc'],
				"type" => "yesno"
				),
			"#topic_html" => array(
				"name" => $lang['add_field_topic_html'],
				"description" => $lang['add_field_topic_html_desc'],
				"type" => "textarea",
				"value" => "<name>: <value><br />"
				),
			"#submit" => array(
				"type" => "submit"
				)
		);


	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['add_field_title'];
		$form_data['meta']['description'] = $lang['add_field_message'];
		$form_data['meta']['complete_func'] = "form_add_profile_fields_complete";
		$form_data['#submit']['value'] = $lang['add_field_submit'];
	}
	elseif($type == "edit")
	{
		// Have to process the dropdown related stuff first
		$initial_data['dropdown_values'] = str_replace(
			'|', "\n", $initial_data['dropdown_values']
			);
		$initial_data['dropdown_text'] = str_replace(
			'|', "\n", $initial_data['dropdown_text']
			);

		$form_data['meta']['initial_data'] = $initial_data;
		$form_data['meta']['title'] = $lang['edit_field_title'];
		$form_data['meta']['complete_func'] = "form_edit_profile_fields_complete";
		$form_data['#submit']['value'] = $lang['edit_field_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for creating a new profile field
 * actually I'm cheating and using this to do some processing
 * on the dropdown fields. Sssh.
 *
 * @param object $form
 */
function form_add_edit_profile_fields_validate($form)
{

	$form -> form_state['meta']['dd_values'] = str_replace(
		"\n",
		"|",
		str_replace(
			"\n\n",
			"\n",
			trim($form -> form_state['#dropdown_values']['value'])
			)
		);

	$form -> form_state['meta']['dd_text'] = str_replace(
		"\n",
		"|",
		str_replace(
			"\n\n",
			"\n",
			trim($form -> form_state['#dropdown_text']['value'])
			)
		);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for creating a new profile field
 *
 * @param object $form
 */
function form_add_profile_fields_complete($form)
{


	global $output, $lang;

	// Try and add the field
	$new_profile_field_id = profile_fields_add_field(
		array(
			"name" => $form -> form_state['#name']['value'],
			"description" => $form -> form_state['#description']['value'],
			"field_type" => $form -> form_state['#field_type']['value'],
			"size" => $form -> form_state['#size']['value'],
			"max_length" => $form -> form_state['#max_length']['value'],
			"order" => $form -> form_state['#order']['value'],
			"dropdown_values" => $form -> form_state['meta']['dd_values'],
			"dropdown_text" => $form -> form_state['meta']['dd_text'],
			"show_on_reg" => $form -> form_state['#show_on_reg']['value'],
			"user_can_edit" => $form -> form_state['#user_can_edit']['value'],
			"is_private" => $form -> form_state['#is_private']['value'],
			"admin_only_field" => $form -> form_state['#admin_only_field']['value'],
			"must_be_filled" => $form -> form_state['#must_be_filled']['value'],
			"topic_html" => $form -> form_state['#topic_html']['value'],
			)
		);

	if($new_profile_field_id === False)
		return False;

	// Log
	log_admin_action(
		"profile_fields",
		"add",
		"Added new custom profile field: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/profile_fields/"),
		$lang['field_created_sucessfully']
		);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a new profile field
 *
 * @param object $form
 */
function form_edit_profile_fields_complete($form)
{


	global $output, $lang;

	// Try and add the field
	$update = profile_fields_edit_field(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"name" => $form -> form_state['#name']['value'],
			"description" => $form -> form_state['#description']['value'],
			"field_type" => $form -> form_state['#field_type']['value'],
			"size" => $form -> form_state['#size']['value'],
			"max_length" => $form -> form_state['#max_length']['value'],
			"order" => $form -> form_state['#order']['value'],
			"dropdown_values" => $form -> form_state['meta']['dd_values'],
			"dropdown_text" => $form -> form_state['meta']['dd_text'],
			"show_on_reg" => $form -> form_state['#show_on_reg']['value'],
			"user_can_edit" => $form -> form_state['#user_can_edit']['value'],
			"is_private" => $form -> form_state['#is_private']['value'],
			"admin_only_field" => $form -> form_state['#admin_only_field']['value'],
			"must_be_filled" => $form -> form_state['#must_be_filled']['value'],
			"topic_html" => $form -> form_state['#topic_html']['value'],
			)
		);

	if($update !== True)
		return False;

	// Log
	log_admin_action(
		"profile_fields",
		"edit",
		"Edited custom profile field: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/profile_fields/"),
		$lang['field_edited_sucessfully']
		);

}


/**
 * Confirmation page to remove a custom profile field.
 *
 * @var $field_id ID of the field we're deleting.
 */
function page_delete_profile_fields($field_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the field
	$field_info = profile_fields_get_field_by_id($field_id);

	if($field_info === False)
	{
		$output -> set_error_message($lang['invalid_field_id']);
		page_view_profile_fields();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_field_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_profilefields_delete'],
		l("admin/profile_fields/delete/".$field_id."/")
		);


	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"description" => $output -> replace_number_tags(
					$lang['delete_profile_field_message'],
					sanitise_user_input($field_info['name'])
					),
				"callback" => "profile_fields_delete_field_complete",
				"arguments" => array($field_id, $field_info['name']),
				"confirm_redirect" => l("admin/profile_fields/"),
				"cancel_redirect" => l("admin/profile_fields/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a profile field
 *
 * @param int $field_id The ID of the field being deleted.
 * @param string $name Name of the fierst. (For logging.)
 */
function profile_fields_delete_field_complete($field_id, $name)
{

	global $output, $lang;

	// Delete and check the response
	$return = profile_fields_delete_field($field_id);

	if($return === True)
	{

        // Log it
        log_admin_action("profile_fields", "delete", "Deleted field: ".$name);
		return True;

	}
	else
		return False;

}

?>
