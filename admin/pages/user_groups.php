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
 * Admin area - User groups
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


// Load language phrases into memory
load_language_group("admin_usergroups");


// General page functions
include ROOT."admin/common/funcs/user_groups.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_usergroups'], l("admin/user_groups/"));

// Work out where we need to be
$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{
	case "add":
		page_add_user_groups();
		break;

	case "edit":
		page_edit_user_groups($page_matches['group_id']);
		break;

	case "delete":
		page_delete_user_groups($page_matches['group_id']);
		break;

	default:
		page_view_user_groups();
}


/**
 * Viewing all user groups
 */
function page_view_user_groups()
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['admin_usergroups_title'];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon("usergroups").$lang['admin_usergroups_title'],
			"description" => $lang['admin_usergroups_message'],
			"no_results_message" => $lang['usergroups_main_no_groups'],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['usergroups_add_submit'],
				"url" => l("admin/user_groups/add/")
				),

			"db_table" => "user_groups g",
			'db_id_column' => "g.id",

			"db_extra_what" => array(
				"count(u.id) as count",
				"g.perm_admin_area",
				"g.perm_global_mod",
				"g.removable"
				),

			'db_extra_settings' => array(
				"join" => "users u",
				"join_type" => "left",
				"join_on" => "u.user_group = g.id",
				"group" => "g.id"
				),

			"default_sort" => "name",

			"columns" => array(
				"name" => array(
					"name" => $lang['usergroups_main_name'],
					"db_column" => "name",
					"sortable" => True
					),
				"admin_area" => array(
					"name" => $lang['usergroups_main_admin_area'],
					"content_callback" => 'table_view_groups_admin_area_callback'
					),
				"global_mod" => array(
					"name" => $lang['usergroups_main_global_mod'],
					"content_callback" => 'table_view_groups_global_mod_callback'
					),
				"count" => array(
					"name" => $lang['usergroups_main_members'],
					"content_callback" => 'table_view_groups_count_callback',
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_view_groups_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * The results table needs to specifically be told what to put in here
 * because we got the data from a count() sql function and not directly
 * from a column.
 *
 * @param object $form
 */
function table_view_groups_count_callback($row_data)
{
	return $row_data['count'];
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * We need to show the words yes or no depending on the value of these.
 *
 * @param object $form
 */
function table_view_groups_admin_area_callback($row_data)
{

	global $lang;

	if($row_data['perm_admin_area'])
		return "<strong>".$lang['yes']."<strong>";

	return $lang['no'];

}


/**
 * See table_view_groups_admin_area_callback
 */
function table_view_groups_global_mod_callback($row_data)
{

	global $lang;

	if($row_data['perm_global_mod'])
		return "<strong>".$lang['yes']."</strong>";

	return $lang['no'];

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the user group view actions.
 *
 * @param object $form
 */
function table_view_groups_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	$return = $template_global_results_table -> action_button(
		"edit",
		$lang['usergroups_main_edit'],
		l("admin/user_groups/edit/".$row_data['id']."/")
		);

	// The default user groups in FSBoard cannot be deleted so we'll
	// hide the button for those groups.
	if($row_data['removable'])
		$return .= $template_global_results_table -> action_button(
			"delete",
			$lang['usergroups_main_delete'],
			l("admin/user_groups/delete/".$row_data['id']."/")
			);

	return $return;

}


/**
 * Page to create a new user group.
 */
function page_add_user_groups()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['usergroups_add_form_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_usergroups_add'],
		l("admin/user_groups/add/")
		);

	// You can inherit settings for a new user group. Here we
	// check if we have currently set an ID for inheritence.
	// If we haven't set one then we need to define the form
	// for selecting one.
	if(!isset($_POST['inherit']))
	{

		$dropdown_options = array();
		$groups = user_groups_get_groups();

		foreach($groups as $g_id => $g_info)
			$dropdown_options[$g_id] = $g_info['name'];

		$form = new form(
			array(
				"meta" => array(
					"name" => "user_groups_add_inherit",
					"title" => $lang['usergroups_add_title'],
					"extra_title_contents_left" => (
						$output -> help_button("", True).
						$template_admin -> form_header_icon("usergroups")
						)
					),
				"#inherit" => array(
					"name" => $lang['usergroups_add_inherit_from'],
					"type" => "dropdown",
					"options" => $dropdown_options
					),
				"#submit" => array(
					"value" => $lang['usergroups_add_submit'],
					"type" => "submit"
					)
				)
			);

		$output -> add($form -> render());

		// Oh no multiple return points
		return;

	}


	// At this point we have got data from the base user group form and we can use
	// it to base our new group on.
	$base_group_info = user_groups_get_group_by_id((int)$_POST['inherit']);

	// If there was a problem getting the data just jump out.
	if($base_group_info === False)
	{
		$output -> set_error_message($lang['invalid_group_id']);
		page_view_user_groups();
		return;
	}

	// We have the data, first we need to this is passed on to the real form
	// otherwise we'll keep getting the inheritance form
	$base_group_info['inherit'] = (int)$_POST['inherit'];

	// The name of course doesn't want to be set to same.
	unset($base_group_info['name']);

	// We can safely put the form on now
	$form = new form(
		form_add_edit_user_groups("add", $base_group_info)
		);

	$output -> add($form -> render());

}



/**
 * Page to edit an existing user group
 *
 * @param int $group_id Integer of the group we want to edit.
 */
function page_edit_user_groups($group_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the user group
	$group_info = user_groups_get_group_by_id($group_id);

	if($group_info === False)
	{
		$output -> set_error_message($lang['invalid_group_id']);
		page_view_user_groups();
		return;
	}

	// Otherwise show the page
	$output -> page_title = $lang['usergroups_edit_form_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_usergroups_edit'],
		l("admin/user_groups/edit/".$group_id."/")
		);

	$form = new form(
		form_add_edit_user_groups("edit", $group_info)
		);

	$output -> add($form -> render());

}



/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing user groups
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially. When adding this is set to the
 *   group we're inheriting from and when  editing it is set to the current data
 *   of the group we're editing.
 */
function form_add_edit_user_groups($type, $initial_data = NULL)
{

	global $lang, $output, $template_admin;

	// This is an extremely long form, and certainly quite daunting to new users.
	// Perhaps something should be done about this...
	$form_data = array(
			"meta" => array(
				"name" => "user_groups_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("usergroups")
					),
				"initial_data" => $initial_data
				),

			"#name" => array(
				"name" => $lang['add_group_name'],
				"type" => "text",
				"required" => True
				),
			"#prefix" => array(
				"name" => $lang['add_group_prefix'],
				"description" => $lang['add_group_prefix_desc'],
				"type" => "text"
				),
			"#suffix" => array(
				"name" => $lang['add_group_suffix'],
				"description" => $lang['add_group_suffix_desc'],
				"type" => "text"
				),
			"#flood_control_time" => array(
				"name" => $lang['add_group_flood_control_time'],
				"description" => $lang['add_group_flood_control_time_desc'],
				"type" => "int"
				),
			"#edit_time" => array(
				"name" => $lang['add_group_edit_time'],
				"description" => $lang['add_group_edit_time_desc'],
				"type" => "int"
				),

			"title_global_perms" => array(
				"title" => $lang['usergroups_add_global_perms_title'],
				"type" => "message"
				),
			"#perm_admin_area" => array(
				"name" => $lang['add_group_admin_area'],
				"type" => "yesno"
				),
			"#perm_see_maintenance_mode" => array(
				"name" => $lang['add_group_perm_see_maintenance_mode'],
				"type" => "yesno"
				),
			"#perm_global_mod" => array(
				"name" => $lang['add_group_global_mod'],
				"description" => $lang['add_group_global_mod_desc'],
				"type" => "yesno"
				),
			"#banned" => array(
				"name" => $lang['add_group_banned'],
				"description" => $lang['add_group_banned_desc'],
				"type" => "yesno"
				),
			"#perm_edit_own_profile" => array(
				"name" => $lang['add_group_perm_edit_own_profile'],
				"type" => "yesno"
				),
			"#perm_see_member_list" => array(
				"name" => $lang['add_group_perm_see_member_list'],
				"type" => "yesno"
				),
			"#perm_see_profile" => array(
				"name" => $lang['add_group_perm_see_profile'],
				"type" => "yesno"
				),

			"title_visibility" => array(
				"title" => $lang['usergroups_add_visibility_title'],
				"type" => "message"
				),
			"#hide_from_member_list" => array(
				"name" => $lang['add_group_hide_from_member_list'],
				"type" => "yesno"
				),

			"title_pm_perms" => array(
				"title" => $lang['usergroups_add_pm_perms'],
				"type" => "message"
				),
			"#perm_use_pm" => array(
				"name" => $lang['add_group_perm_use_pm'],
				"type" => "yesno"
				),
			"#pm_total" => array(
				"name" => $lang['add_group_pm_total'],
				"description" => $lang['add_group_pm_total_desc'],
				"type" => "int"
				),

			"title_avatar_settings" => array(
				"title" => $lang['usergroups_add_avatar_settings'],
				"type" => "message"
				),
			"#perm_avatar_allow" => array(
				"name" => $lang['add_group_avatar_allow'],
				"type" => "yesno"
				),
			"#perm_avatar_allow_gallery" => array(
				"name" => $lang['add_group_avatar_allow_gallery'],
				"type" => "yesno"
				),
			"#perm_avatar_allow_upload" => array(
				"name" => $lang['add_group_avatar_allow_upload'],
				"type" => "yesno"
				),
			"#perm_avatar_allow_external" => array(
				"name" => $lang['add_group_avatar_allow_external'],
				"type" => "yesno"
				),
			"#perm_avatar_width" => array(
				"name" => $lang['add_group_avatar_width'],
				"description" => $lang['add_group_avatar_width_desc'],
				"type" => "int"
				),
			"#perm_avatar_height" => array(
				"name" => $lang['add_group_avatar_height'],
				"description" => $lang['add_group_avatar_height_desc'],
				"type" => "int"
				),
			"#perm_avatar_filesize" => array(
				"name" => $lang['add_group_avatar_filesize'],
				"description" => $lang['add_group_avatar_filesize_desc'],
				"type" => "int"
				),

			"title_user_title_settings" => array(
				"title" => $lang['usergroups_add_user_title_settings'],
				"type" => "message"
				),
			"#display_user_title" => array(
				"name" => $lang['add_group_display_user_title'],
				"type" => "text"
				),
			"#override_user_title" => array(
				"name" => $lang['add_group_override_user_title'],
				"type" => "yesno"
				),
			"#perm_custom_user_title" => array(
				"name" => $lang['add_group_perm_custom_user_title'],
				"type" => "yesno"
				),

			"title_forum_viewing" => array(
				"title" => $lang['usergroups_add_forum_viewing'],
				"type" => "message"
				),
			"#perm_see_board" => array(
				"name" => $lang['add_group_perm_see_board'],
				"type" => "yesno"
				),
			"#perm_use_search" => array(
				"name" => $lang['add_group_perm_use_search'],
				"type" => "yesno"
				),
			"#perm_view_other_topic" => array(
				"name" => $lang['add_group_perm_view_other_topic'],
				"type" => "yesno"
				),

			"title_topic_posting" => array(
				"title" => $lang['usergroups_add_topic_posting'],
				"type" => "message"
				),
			"#perm_post_topic" => array(
				"name" => $lang['add_group_perm_post_topic'],
				"type" => "yesno"
				),
			"#perm_reply_own_topic" => array(
				"name" => $lang['add_group_perm_reply_own_topic'],
				"type" => "yesno"
				),
			"#perm_reply_other_topic" => array(
				"name" => $lang['add_group_perm_reply_other_topic'],
				"type" => "yesno"
				),
			"#perm_edit_own_post" => array(
				"name" => $lang['add_group_perm_edit_own_post'],
				"type" => "yesno"
				),
			"#perm_edit_own_topic_title" => array(
				"name" => $lang['add_group_perm_edit_own_topic_title'],
				"type" => "yesno"
				),
			"#perm_delete_own_post" => array(
				"name" => $lang['add_group_perm_delete_own_post'],
				"type" => "yesno"
				),
			"#perm_delete_own_topic" => array(
				"name" => $lang['add_group_perm_delete_own_topic'],
				"type" => "yesno"
				),
			"#perm_move_own_topic" => array(
				"name" => $lang['add_group_perm_move_own_topic'],
				"type" => "yesno"
				),
			"#perm_close_own_topic" => array(
				"name" => $lang['add_group_perm_close_own_topic'],
				"type" => "yesno"
				),
			"#perm_post_closed_topic" => array(
				"name" => $lang['add_group_perm_post_closed_topic'],
				"type" => "yesno"
				),

			"title_posting_options" => array(
				"title" => $lang['usergroups_add_posting_options'],
				"type" => "message"
				),
			"#perm_remove_edited_by" => array(
				"name" => $lang['add_group_perm_remove_edited_by'],
				"type" => "yesno"
				),
			"#perm_use_html" => array(
				"name" => $lang['add_group_perm_use_html'],
				"description" => $lang['add_group_perm_use_html_desc'],
				"type" => "yesno"
				),
			"#perm_use_bbcode" => array(
				"name" => $lang['add_group_perm_use_bbcode'],
				"description" => $lang['add_group_perm_use_bbcode_desc'],
				"type" => "yesno"
				),
			"#perm_use_emoticons" => array(
				"name" => $lang['add_group_perm_use_emoticons'],
				"type" => "yesno"
				),
			"#perm_no_word_filter" => array(
				"name" => $lang['add_group_perm_no_word_filter'],
				"type" => "yesno"
				),

			"title_polls" => array(
				"title" => $lang['usergroups_add_polls_perms'],
				"type" => "message"
				),
			"#perm_new_polls" => array(
				"name" => $lang['add_group_perm_new_polls'],
				"type" => "yesno"
				),
			"#perm_vote_polls" => array(
				"name" => $lang['add_group_perm_vote_polls'],
				"type" => "yesno"
				),

			"#inherit" => array(
				"type" => "hidden"
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['usergroups_add_form_title'];
		$form_data['meta']['description'] = $lang['usergroups_add_form_message'];
		$form_data['meta']['complete_func'] = "form_add_user_group_complete";
		$form_data['#submit']['value'] = $lang['add_group_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['usergroups_edit_form_title'];
		$form_data['meta']['complete_func'] = "form_edit_user_group_complete";
		$form_data['#submit']['value'] = $lang['edit_group_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for creating a new user group
 *
 * @param object $form
 */
function form_add_user_group_complete($form)
{


	global $output, $lang;

	// Try and add the group
	$new_group_id = user_groups_add_group(
		array(
			"name" => $form -> form_state['#name']['value'],
			"prefix" => $form -> form_state['#prefix']['value'],
			"suffix" => $form -> form_state['#suffix']['value'],
			"flood_control_time" => $form -> form_state['#flood_control_time']['value'],
			"edit_time" => $form -> form_state['#edit_time']['value'],

			"perm_admin_area" => $form -> form_state['#perm_admin_area']['value'],
			"perm_see_maintenance_mode" => $form -> form_state['#perm_see_maintenance_mode']['value'],
			"perm_global_mod" => $form -> form_state['#perm_global_mod']['value'],
			"banned" => $form -> form_state['#banned']['value'],
			"perm_edit_own_profile" => $form -> form_state['#perm_edit_own_profile']['value'],
			"perm_see_member_list" => $form -> form_state['#perm_see_member_list']['value'],
			"perm_see_profile" => $form -> form_state['#perm_see_profile']['value'],

			"hide_from_member_list" => $form -> form_state['#hide_from_member_list']['value'],

			"perm_use_pm" => $form -> form_state['#perm_use_pm']['value'],
			"pm_total" => $form -> form_state['#pm_total']['value'],

			"perm_avatar_allow" => $form -> form_state['#perm_avatar_allow']['value'],
			"perm_avatar_allow_gallery" => $form -> form_state['#perm_avatar_allow_gallery']['value'],
			"perm_avatar_allow_upload" => $form -> form_state['#perm_avatar_allow_upload']['value'],
			"perm_avatar_allow_external" => $form -> form_state['#perm_avatar_allow_external']['value'],
			"perm_avatar_width" => $form -> form_state['#perm_avatar_width']['value'],
			"perm_avatar_height" => $form -> form_state['#perm_avatar_height']['value'],
			"perm_avatar_filesize" => $form -> form_state['#perm_avatar_filesize']['value'],

			"display_user_title" => $form -> form_state['#display_user_title']['value'],
			"override_user_title" => $form -> form_state['#override_user_title']['value'],
			"perm_custom_user_title" => $form -> form_state['#perm_custom_user_title']['value'],

			"perm_see_board" => $form -> form_state['#perm_see_board']['value'],
			"perm_use_search" => $form -> form_state['#perm_use_search']['value'],
			"perm_view_other_topic" => $form -> form_state['#perm_view_other_topic']['value'],

			"perm_post_topic" => $form -> form_state['#perm_post_topic']['value'],
			"perm_reply_own_topic" => $form -> form_state['#perm_reply_own_topic']['value'],
			"perm_reply_other_topic" => $form -> form_state['#perm_reply_other_topic']['value'],
			"perm_edit_own_post" => $form -> form_state['#perm_edit_own_post']['value'],
			"perm_edit_own_topic_title" => $form -> form_state['#perm_edit_own_topic_title']['value'],
			"perm_delete_own_post" => $form -> form_state['#perm_delete_own_post']['value'],
			"perm_delete_own_topic" => $form -> form_state['#perm_delete_own_topic']['value'],
			"perm_move_own_topic" => $form -> form_state['#perm_move_own_topic']['value'],
			"perm_close_own_topic" => $form -> form_state['#perm_close_own_topic']['value'],
			"perm_post_closed_topic" => $form -> form_state['#perm_post_closed_topic']['value'],

			"perm_remove_edited_by" => $form -> form_state['#perm_remove_edited_by']['value'],
			"perm_use_html" => $form -> form_state['#perm_use_html']['value'],
			"perm_use_bbcode" => $form -> form_state['#perm_use_bbcode']['value'],
			"perm_use_emoticons" => $form -> form_state['#perm_use_emoticons']['value'],
			"perm_no_word_filter" => $form -> form_state['#perm_no_word_filter']['value'],

			"perm_new_polls" => $form -> form_state['#perm_new_polls']['value'],
			"perm_vote_polls" => $form -> form_state['#perm_vote_polls']['value'],
			)
		);

	if($new_group_id === False)
		return False;

	// Log
	log_admin_action(
		"user_groups",
		"add",
		"Added new user group: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/user_groups/"),
		$lang['usergroup_created_sucessfully']
		);

}



/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a user group
 *
 * @param object $form
 */
function form_edit_user_group_complete($form)
{


	global $output, $lang;

	// Try to edit the group
	$update = user_groups_edit_group(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"name" => $form -> form_state['#name']['value'],
			"prefix" => $form -> form_state['#prefix']['value'],
			"suffix" => $form -> form_state['#suffix']['value'],
			"flood_control_time" => $form -> form_state['#flood_control_time']['value'],
			"edit_time" => $form -> form_state['#edit_time']['value'],

			"perm_admin_area" => $form -> form_state['#perm_admin_area']['value'],
			"perm_see_maintenance_mode" => $form -> form_state['#perm_see_maintenance_mode']['value'],
			"perm_global_mod" => $form -> form_state['#perm_global_mod']['value'],
			"banned" => $form -> form_state['#banned']['value'],
			"perm_edit_own_profile" => $form -> form_state['#perm_edit_own_profile']['value'],
			"perm_see_member_list" => $form -> form_state['#perm_see_member_list']['value'],
			"perm_see_profile" => $form -> form_state['#perm_see_profile']['value'],

			"hide_from_member_list" => $form -> form_state['#hide_from_member_list']['value'],

			"perm_use_pm" => $form -> form_state['#perm_use_pm']['value'],
			"pm_total" => $form -> form_state['#pm_total']['value'],

			"perm_avatar_allow" => $form -> form_state['#perm_avatar_allow']['value'],
			"perm_avatar_allow_gallery" => $form -> form_state['#perm_avatar_allow_gallery']['value'],
			"perm_avatar_allow_upload" => $form -> form_state['#perm_avatar_allow_upload']['value'],
			"perm_avatar_allow_external" => $form -> form_state['#perm_avatar_allow_external']['value'],
			"perm_avatar_width" => $form -> form_state['#perm_avatar_width']['value'],
			"perm_avatar_height" => $form -> form_state['#perm_avatar_height']['value'],
			"perm_avatar_filesize" => $form -> form_state['#perm_avatar_filesize']['value'],

			"display_user_title" => $form -> form_state['#display_user_title']['value'],
			"override_user_title" => $form -> form_state['#override_user_title']['value'],
			"perm_custom_user_title" => $form -> form_state['#perm_custom_user_title']['value'],

			"perm_see_board" => $form -> form_state['#perm_see_board']['value'],
			"perm_use_search" => $form -> form_state['#perm_use_search']['value'],
			"perm_view_other_topic" => $form -> form_state['#perm_view_other_topic']['value'],

			"perm_post_topic" => $form -> form_state['#perm_post_topic']['value'],
			"perm_reply_own_topic" => $form -> form_state['#perm_reply_own_topic']['value'],
			"perm_reply_other_topic" => $form -> form_state['#perm_reply_other_topic']['value'],
			"perm_edit_own_post" => $form -> form_state['#perm_edit_own_post']['value'],
			"perm_edit_own_topic_title" => $form -> form_state['#perm_edit_own_topic_title']['value'],
			"perm_delete_own_post" => $form -> form_state['#perm_delete_own_post']['value'],
			"perm_delete_own_topic" => $form -> form_state['#perm_delete_own_topic']['value'],
			"perm_move_own_topic" => $form -> form_state['#perm_move_own_topic']['value'],
			"perm_close_own_topic" => $form -> form_state['#perm_close_own_topic']['value'],
			"perm_post_closed_topic" => $form -> form_state['#perm_post_closed_topic']['value'],

			"perm_remove_edited_by" => $form -> form_state['#perm_remove_edited_by']['value'],
			"perm_use_html" => $form -> form_state['#perm_use_html']['value'],
			"perm_use_bbcode" => $form -> form_state['#perm_use_bbcode']['value'],
			"perm_use_emoticons" => $form -> form_state['#perm_use_emoticons']['value'],
			"perm_no_word_filter" => $form -> form_state['#perm_no_word_filter']['value'],

			"perm_new_polls" => $form -> form_state['#perm_new_polls']['value'],
			"perm_vote_polls" => $form -> form_state['#perm_vote_polls']['value'],
			)
		);

	if($update !== True)
		return False;

	// Log
	log_admin_action(
		"user_groups",
		"edit",
		"Edited user group: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/user_groups/"),
		$lang['usergroup_updated_sucessfully']
		);

}


/**
 * Page to delete an existing user group
 *
 * @param int $group_id Integer of the group we want to remove.
 */
function page_delete_user_groups($group_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the user group
	$group_info = user_groups_get_group_by_id($group_id);

	if($group_info === False)
	{
		$output -> set_error_message($lang['invalid_group_id']);
		page_view_user_groups();
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['usergroups_delete_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_usergroups_delete'],
		l("admin/user_groups/delete/".$group_id."/")
		);

	// You can't remove the default user groups
	if(!$group_info['removable'])
	{
		$output -> set_error_message($lang['group_non_removable']);
		page_view_user_groups();
		return;
	}

	// Get groups for dropdown
	$groups = user_groups_get_groups();
	unset($groups[$group_info['id']]);

	$dropdown_options = array();

	foreach($groups as $id => $info)
		$dropdown_options[$id] = $info['name'];

	// Display the form
	$form = new form(
		array(
			"meta" => array(
				"name" => "user_group_delete",
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"complete_func" => "form_delete_user_groups_complete",
				"title" => $lang['usergroups_delete_title'],
				"description" => $output -> replace_number_tags(
					$lang['usergroups_delete_main_message'], $group_info['name']
					),
				"data_group_id"  => $group_id,
				"data_group_name"  => $group_info['name']
				),
			"#replace" => array(
				"name" => $output -> replace_number_tags(
					$lang['usergroups_delete_message'], $group_info['name']
					),
				"type" => "dropdown",
				"required" => True,
				"options" => $dropdown_options
				),
			"#submit" => array(
				"value" => $lang['usergroups_delete_submit'],
				"type" => "submit"
				)
			)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for deleting a user group
 *
 * @param object $form
 */
function form_delete_user_groups_complete($form)
{

	global $lang, $output;

	// Delete and check the response
	$return = user_groups_delete_group(
		$form -> form_state['meta']['data_group_id'],
		$form -> form_state['#replace']['value']
		);

	if($return === True)
	{

        // Log it
        log_admin_action(
			"user_groups",
			"delete",
			"Deleted user group: ".$form -> form_state['meta']['data_group_name']
			);

		$output -> redirect(
			l("admin/user_groups/"),
			$lang['usergroup_deleted_sucessfully']
			);

		return True;

	}

	page_view_user_groups();

}

?>
