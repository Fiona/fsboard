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


//***********************************************
// Main group listings
//***********************************************
/*
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['admin_usergroups_title'];

        // Create classes
        $table = new table_generate;

        // ----------------
        // GROUP LIST
        // ----------------
        $output -> add(
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['admin_usergroups_title'], "strip1",  "", "left", "100%", "5").
                $table -> add_basic_row($lang['admin_usergroups_message'], "normalcell",  "padding : 5px", "left", "100%", "5").
                $table -> add_row(      
                        array(
                                array($lang['usergroups_main_name'], "25%"),
                                array($lang['usergroups_main_admin_area'], "20%"),
                                array($lang['usergroups_main_global_mod'], "20%"),
                                array($lang['usergroups_main_members'], "10%"),
                                array($lang['usergroups_main_actions'], "25%")
                        ),
                "strip2")
        );

        // *************************
        // Grab all groups
        // *************************
        $user_groups = $db -> query("select g.id, g.name, g.perm_admin_area, g.perm_global_mod, g.removable, 
        count(u.id) as count from ".$db -> table_prefix."user_groups as g
        left join ".$db -> table_prefix."users as u on (u.user_group = g.id)
        group by g.id order by g.id asc");

        // Get amount
        $groups_amount = $db -> num_rows($user_groups);


        // *************************
        // No user groups?
        // *************************
        if($groups_amount < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['usergroups_main_no_groups']."</b>", "normalcell",  "padding : 10px", "center", "100%", "5")
                );        
                
        else
        {

                // *************************
                // Go through each group if we have some
                // *************************
                while($g_array = $db-> fetch_array($user_groups))
                {
                
                        // Admin?
                        if($g_array['perm_admin_area'])
                                $admin_html = "<b>".$lang['yes']."</b>";
                        else
                                $admin_html = $lang['no'];
                                
                        // Mod?
                        if($g_array['perm_global_mod'])
                                $mod_html = "<b>".$lang['yes']."</b>";
                        else
                                $mod_html = $lang['no'];

                        // Sort picture links out
                        $actions = "
                        <a href=\"index.php?m=usergroups&amp;m2=edit&amp;id=".$g_array['id']."\" title=\"".$lang['usergroups_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>";
                        
                        if($g_array['removable'])
                                $actions .= " <a href=\"index.php?m=usergroups&amp;m2=delete&amp;id=".$g_array['id']."\" title=\"".$lang['usergroups_main_delete']."\">
                                <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>
                                ";
                        
                        // *************************
                        // Print row
                        // *************************
                        $output -> add(
                                $table -> add_row(      
                                        array(
                                                array($g_array['name'], "25%"),
                                                array($admin_html, "20%"),
                                                array($mod_html, "20%"),
                                                array($g_array['count'], "10%"),
                                                array($actions, "25%")
                                        ),
                                "normalcell")
                        );
                        
                        // Save stuff for the new groups form
                        $new_dropdown_text[] .= $g_array['name'];
                        $new_dropdown_values[] .= $g_array['id'];
                        
                }                
                
        }
        
        // *************************
        // New form
        // *************************
        $form = new form_generate;
        
        $output -> add(
                $table -> end_table().
                
                $form -> start_form("addforum", ROOT."admin/index.php?m=usergroups&amp;m2=add", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['usergroups_add_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(      
                        array(
                                array($lang['usergroups_add_inherit_from'], "25%"),
                                array($form->input_dropdown("inherit", "", $new_dropdown_values, $new_dropdown_text), "25%")
                        ),
                "normalcell").
                $table -> add_basic_row($form -> submit("submit", $lang['usergroups_add_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()                
        );

}
*/

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

//***********************************************
// Form for adding or editing a user group
//***********************************************
/*
function page_add_edit_group($adding = false, $group_info = "")
{

        global $output, $lang, $db, $template_admin;

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ***************************
        // Need different headers
        // ***************************
        if($adding)
        {

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['usergroups_add_form_title'];

		$output -> add_breadcrumb($lang['breadcrumb_usergroups_add'], "index.php?m=usergroups&amp;m2=add");

                $output -> add(
                        $form -> start_form("addforum", ROOT."admin/index.php?m=usergroups&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['usergroups_add_form_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['usergroups_add_form_message'], "normalcell",  "padding : 5px;", "left", "100%", "2")
                );

                $submit_lang = $lang['add_group_submit'];

                if(!$group_info)
                {

                        // ----------------
                        // Grab the inherited group
                        // ----------------
                        $post_id = trim($_POST['inherit']);
                
                        // No ID
                        if($post_id == '')
                        {
                                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                                page_main();
                                return;
                        }
                                
                        // Grab wanted group
                        $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$post_id."'");
        
                        // Die if it doesn't exist
                        if($db -> num_rows($group) == 0)
                        {
                                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                                page_main();
                                return;
                        }
        
                        $group_info = $db -> fetch_array($group);

                        $group_info['name'] = "";
                                        
                }

        }
        else
        {

                // ----------------
                // Grab the group we're editing
                // ----------------
                $get_id = trim($_GET['id']);
        
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                        page_main();
                        return;
                }
                        
                // Grab wanted group
                $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$get_id."'");

                // Die if it doesn't exist
                if($db -> num_rows($group) == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                        page_main();
                        return;
                }

                $group_info = $db -> fetch_array($group);

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['usergroups_edit_form_title'];

		$output -> add_breadcrumb($lang['breadcrumb_usergroups_edit'], "index.php?m=usergroups&amp;m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("addforum", ROOT."admin/index.php?m=usergroups&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                        // ---------------
                        // Title and info
                        // ---------------
                        $table -> add_basic_row($lang['usergroups_edit_form_title']. " <b>".$group_info['name']."</b> (ID: <i>".$get_id."</i>)", "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_group_submit'];
        
        }
        
        // ***************************
        // THE FORM
        // Holy crap
        // ***************************

        $output -> add(
                // --------------------
                // Basic Info
                // --------------------
                $table -> add_row(
                        array(
                                array($lang['add_group_name'], "50%"), 
                                array($form -> input_text("name", $group_info['name']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_prefix']."<br /><font class=\"small_text\">".$lang['add_group_prefix_desc']."</font>", 
                                $form -> input_text("prefix", $group_info['prefix'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_suffix']."<br /><font class=\"small_text\">".$lang['add_group_suffix_desc']."</font>", 
                                $form -> input_text("suffix", $group_info['suffix'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_flood_control_time']."<br /><font class=\"small_text\">".$lang['add_group_flood_control_time_desc']."</font>", 
                                $form -> input_int("flood_control_time", $group_info['flood_control_time'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_edit_time']."<br /><font class=\"small_text\">".$lang['add_group_edit_time_desc']."</font>", 
                                $form -> input_int("edit_time", $group_info['edit_time'])
                        ),
                "normalcell").
                $table -> end_table().

                // --------------------
                // Global Perms
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_global_perms_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_admin_area'], "50%"), 
                                array($form -> input_yesno("perm_admin_area", $group_info['perm_admin_area']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_see_maintenance_mode'], 
                                $form -> input_yesno("perm_see_maintenance_mode", $group_info['perm_see_maintenance_mode'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_global_mod']."<br /><font class=\"small_text\">".$lang['add_group_global_mod_desc']."</font>", 
                                $form -> input_yesno("perm_global_mod", $group_info['perm_global_mod'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_banned']."<br /><font class=\"small_text\">".$lang['add_group_banned_desc']."</font>", 
                                $form -> input_yesno("banned", $group_info['banned'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_edit_own_profile'], 
                                $form -> input_yesno("perm_edit_own_profile", $group_info['perm_edit_own_profile'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_see_member_list'], 
                                $form -> input_yesno("perm_see_member_list", $group_info['perm_see_member_list'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_see_profile'], 
                                $form -> input_yesno("perm_see_profile", $group_info['perm_see_profile'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Visibility Options
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_visibility_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_hide_from_member_list'], "50%"), 
                                array($form -> input_yesno("hide_from_member_list", $group_info['hide_from_member_list']), "50%")
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // PM Perms
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_pm_perms'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_use_pm'], "50%"), 
                                array($form -> input_yesno("perm_use_pm", $group_info['perm_use_pm']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_pm_total']."<br /><font class=\"small_text\">".$lang['add_group_pm_total_desc']."</font>", 
                                $form -> input_int("pm_total", $group_info['pm_total'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Avatar Settings
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_avatar_settings'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow", $group_info['perm_avatar_allow']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow_gallery'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow_gallery", $group_info['perm_avatar_allow_gallery']), "50%")
                        ),
                "normalcell").                        
                     $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow_upload'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow_upload", $group_info['perm_avatar_allow_upload']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_avatar_allow_external'], "50%"), 
                                array($form -> input_yesno("perm_avatar_allow_external", $group_info['perm_avatar_allow_external']), "50%")
                        ),
                "normalcell").                
                $table -> add_row(
                        array(
                                $lang['add_group_avatar_width']."<br /><font class=\"small_text\">".$lang['add_group_avatar_width_desc']."</font>", 
                                $form -> input_int("perm_avatar_width", $group_info['perm_avatar_width'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_avatar_height']."<br /><font class=\"small_text\">".$lang['add_group_avatar_height_desc']."</font>", 
                                $form -> input_int("perm_avatar_height", $group_info['perm_avatar_height'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_avatar_filesize']."<br /><font class=\"small_text\">".$lang['add_group_avatar_filesize_desc']."</font>", 
                                $form -> input_int("perm_avatar_filesize", $group_info['perm_avatar_filesize'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // User title Settings
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_user_title_settings'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_display_user_title'], "50%"), 
                                array($form -> input_text("display_user_title", $group_info['display_user_title']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_override_user_title'], "50%"), 
                                array($form -> input_yesno("override_user_title", $group_info['override_user_title']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_custom_user_title'], "50%"), 
                                array($form -> input_yesno("perm_custom_user_title", $group_info['perm_custom_user_title']), "50%")
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Forum/Topic Viewing
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_forum_viewing'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_see_board'], "50%"), 
                                array($form -> input_yesno("perm_see_board", $group_info['perm_see_board']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_search'],
                                $form -> input_yesno("perm_use_search", $group_info['perm_use_search'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_view_other_topic'],
                                $form -> input_yesno("perm_view_other_topic", $group_info['perm_view_other_topic'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Topic/Reply Posting
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_topic_posting'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_post_topic'], "50%"), 
                                array($form -> input_yesno("perm_post_topic", $group_info['perm_post_topic']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_reply_own_topic'], 
                                $form -> input_yesno("perm_reply_own_topic", $group_info['perm_reply_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_reply_other_topic'], 
                                $form -> input_yesno("perm_reply_other_topic", $group_info['perm_reply_other_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_edit_own_post'], 
                                $form -> input_yesno("perm_edit_own_post", $group_info['perm_edit_own_post'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_edit_own_topic_title'], 
                                $form -> input_yesno("perm_edit_own_topic_title", $group_info['perm_edit_own_topic_title'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_delete_own_post'], 
                                $form -> input_yesno("perm_delete_own_post", $group_info['perm_delete_own_post'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_delete_own_topic'], 
                                $form -> input_yesno("perm_delete_own_topic", $group_info['perm_delete_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_move_own_topic'], 
                                $form -> input_yesno("perm_move_own_topic", $group_info['perm_move_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_close_own_topic'], 
                                $form -> input_yesno("perm_close_own_topic", $group_info['perm_close_own_topic'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_post_closed_topic'], 
                                $form -> input_yesno("perm_post_closed_topic", $group_info['perm_post_closed_topic'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Posting Options
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_posting_options'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_remove_edited_by'], "50%"), 
                                array($form -> input_yesno("perm_remove_edited_by", $group_info['perm_remove_edited_by']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_html']."<br /><font class=\"small_text\">".$lang['add_group_perm_use_html_desc']."</font>", 
                                $form -> input_yesno("perm_use_html", $group_info['perm_use_html'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_bbcode']."<br /><font class=\"small_text\">".$lang['add_group_perm_use_bbcode_desc']."</font>", 
                                $form -> input_yesno("perm_use_bbcode", $group_info['perm_use_bbcode'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_use_emoticons'], 
                                $form -> input_yesno("perm_use_emoticons", $group_info['perm_use_emoticons'])
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_no_word_filter'], 
                                $form -> input_yesno("perm_no_word_filter", $group_info['perm_no_word_filter'])
                        ),
                "normalcell").
                $table -> end_table().


                // --------------------
                // Polls
                // --------------------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_add_polls_perms'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($lang['add_group_perm_new_polls'], "50%"), 
                                array($form -> input_yesno("perm_new_polls", $group_info['perm_new_polls']), "50%")
                        ),
                "normalcell").
                $table -> add_row(
                        array(
                                $lang['add_group_perm_vote_polls'], 
                                $form -> input_yesno("perm_vote_polls", $group_info['perm_vote_polls'])
                        ),
                "normalcell").
                $table -> end_table().

                // -----------
                // Submit Buttons
                // -----------
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($form -> submit("submit", $submit_lang), "strip3", "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

}

*/


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



//***********************************************
// Add the groups
//***********************************************
/*
function do_add_group()
{

        global $output, $lang, $db, $template_admin, $cache;

        // Get stuff from the post
        $group_info = array(
                "name"                          => $_POST['name'],
                "prefix"                        => $_POST['prefix'],
                "suffix"                        => $_POST['suffix'],
                "flood_control_time"            => $_POST['flood_control_time'],
                "edit_time"                     => $_POST['edit_time'],
                "perm_admin_area"               => $_POST['perm_admin_area'],
                "perm_see_maintenance_mode"     => $_POST['perm_see_maintenance_mode'],
                "perm_global_mod"               => $_POST['perm_global_mod'],
                "banned"                        => $_POST['banned'],
                "perm_edit_own_profile"         => $_POST['perm_edit_own_profile'],
                "perm_see_member_list"          => $_POST['perm_see_member_list'],
                "perm_see_profile"              => $_POST['perm_see_profile'],
                "hide_from_member_list"         => $_POST['hide_from_member_list'],
                "perm_use_pm"                   => $_POST['perm_use_pm'],
                "pm_total"                      => $_POST['pm_total'],
                "perm_see_board"                => $_POST['perm_see_board'],
                "perm_use_search"               => $_POST['perm_use_search'],

                "perm_view_other_topic"         => $_POST['perm_view_other_topic'],
                "perm_post_topic"               => $_POST['perm_post_topic'],
                "perm_reply_own_topic"          => $_POST['perm_reply_own_topic'],
                "perm_reply_other_topic"        => $_POST['perm_reply_other_topic'],
                "perm_edit_own_post"            => $_POST['perm_edit_own_post'],
                "perm_edit_own_topic_title"     => $_POST['perm_edit_own_topic_title'],

                "perm_delete_own_post"          => $_POST['perm_delete_own_post'],
                "perm_delete_own_topic"         => $_POST['perm_delete_own_topic'],
                "perm_move_own_topic"           => $_POST['perm_move_own_topic'],
                "perm_close_own_topic"          => $_POST['perm_close_own_topic'],
                "perm_post_closed_topic"        => $_POST['perm_post_closed_topic'],
                "perm_remove_edited_by"         => $_POST['perm_remove_edited_by'],

                "perm_use_html"                 => $_POST['perm_use_html'],
                "perm_use_bbcode"               => $_POST['perm_use_bbcode'],
                "perm_use_emoticons"            => $_POST['perm_use_emoticons'],
                "perm_no_word_filter"           => $_POST['perm_no_word_filter'],
                "perm_new_polls"                => $_POST['perm_new_polls'],
                "perm_vote_polls"               => $_POST['perm_vote_polls'],
                "perm_avatar_allow"             => $_POST['perm_avatar_allow'],

                "perm_avatar_allow_gallery"     => $_POST['perm_avatar_allow_gallery'],
                "perm_avatar_allow_upload"      => $_POST['perm_avatar_allow_upload'],
                "perm_avatar_allow_external"    => $_POST['perm_avatar_allow_exeternal'],
                "perm_avatar_width"             => $_POST['perm_avatar_width'],
                "perm_avatar_height"            => $_POST['perm_avatar_height'],
                "perm_avatar_filesize"          => $_POST['perm_avatar_filesize'],

                "display_user_title"			=> $_POST['display_user_title'],
                "override_user_title"			=> $_POST['override_user_title'],
                "perm_custom_user_title"		=> $_POST['perm_custom_user_title']
        );

        // ----------------------
        // Check there's something in the name
        // ----------------------
        if(trim($group_info['name']) == "")
        {
                $output -> add($template_admin -> critical_error($lang['add_group_no_name']));
                page_add_edit_group(true, $group_info);
                return;
        }               

        // ----------------------
        // Add it!
        // ----------------------
        if(!$db -> basic_insert("user_groups", $group_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_group_error']));
                page_add_edit_group(true, $group_info);
                return;
        }               
       
        // ----------------------
        // Update cache
        // ----------------------
        $cache -> update_cache("user_groups");        
        
        // ----------------------
        // Log it!
        // ----------------------
        log_admin_action("usergroups", "doadd", "Added user group: ".$group_info['name']);
        
        // ----------------------
        // Done
        // ----------------------
        $output -> redirect(ROOT."admin/index.php?m=usergroups", $lang['usergroup_created_sucessfully']);
        
}
*/


//***********************************************
// Edit like a crazy bear
//***********************************************
/*
function do_edit_group()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ----------------
        // Grab the group we're editing
        // ----------------
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select * from ".$db -> table_prefix."user_groups where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

 
        // ----------------------
        // Get stuff from the post
        // ----------------------
        $group_info = array(
                "name"                          => $_POST['name'],
                "prefix"                        => $_POST['prefix'],
                "suffix"                        => $_POST['suffix'],
                "flood_control_time"            => $_POST['flood_control_time'],
                "edit_time"                     => $_POST['edit_time'],
                "perm_admin_area"               => $_POST['perm_admin_area'],
                "perm_see_maintenance_mode"     => $_POST['perm_see_maintenance_mode'],
                "perm_global_mod"               => $_POST['perm_global_mod'],
                "banned"                        => $_POST['banned'],
                "perm_edit_own_profile"         => $_POST['perm_edit_own_profile'],
                "perm_see_member_list"          => $_POST['perm_see_member_list'],
                "perm_see_profile"              => $_POST['perm_see_profile'],
                "hide_from_member_list"         => $_POST['hide_from_member_list'],
                "perm_use_pm"                   => $_POST['perm_use_pm'],
                "pm_total"                      => $_POST['pm_total'],
                "perm_see_board"                => $_POST['perm_see_board'],
                "perm_use_search"               => $_POST['perm_use_search'],
                "perm_view_other_topic"         => $_POST['perm_view_other_topic'],
                "perm_post_topic"               => $_POST['perm_post_topic'],
                "perm_reply_own_topic"          => $_POST['perm_reply_own_topic'],
                "perm_reply_other_topic"        => $_POST['perm_reply_other_topic'],
                "perm_edit_own_post"            => $_POST['perm_edit_own_post'],
                "perm_edit_own_topic_title"     => $_POST['perm_edit_own_topic_title'],
                "perm_delete_own_post"          => $_POST['perm_delete_own_post'],
                "perm_delete_own_topic"         => $_POST['perm_delete_own_topic'],
                "perm_move_own_topic"           => $_POST['perm_move_own_topic'],
                "perm_close_own_topic"          => $_POST['perm_close_own_topic'],
                "perm_post_closed_topic"        => $_POST['perm_post_closed_topic'],
                "perm_remove_edited_by"         => $_POST['perm_remove_edited_by'],
                "perm_use_html"                 => $_POST['perm_use_html'],
                "perm_use_bbcode"               => $_POST['perm_use_bbcode'],
                "perm_use_emoticons"            => $_POST['perm_use_emoticons'],
                "perm_no_word_filter"           => $_POST['perm_no_word_filter'],
                "perm_new_polls"                => $_POST['perm_new_polls'],
                "perm_vote_polls"               => $_POST['perm_vote_polls'],
                "perm_avatar_allow"             => $_POST['perm_avatar_allow'],
                "perm_avatar_allow_gallery"     => $_POST['perm_avatar_allow_gallery'],        
                "perm_avatar_allow_upload"      => $_POST['perm_avatar_allow_upload'],
                "perm_avatar_allow_external"    => $_POST['perm_avatar_allow_external'],
                "perm_avatar_width"             => $_POST['perm_avatar_width'],
                "perm_avatar_height"            => $_POST['perm_avatar_height'],
                "perm_avatar_filesize"          => $_POST['perm_avatar_filesize'],
                "display_user_title"			=> $_POST['display_user_title'],
                "override_user_title"			=> $_POST['override_user_title'],
                "perm_custom_user_title"		=> $_POST['perm_custom_user_title']
        );
 


        // ----------------------
        // Check there's something in the name
        // ----------------------
        if(trim($group_info['name']) == "")
        {
                $output -> add($template_admin -> critical_error($lang['add_group_no_name']));
                page_add_edit_group(false, $group_info);
                return;
        }               

        // ----------------------
        // Do the query
        // ----------------------
        if(!$db -> basic_update("user_groups", $group_info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_updating_usergroup']));
                page_main();
                return;
        }

        // ----------------------
        // Update cache
        // ----------------------
        $cache -> update_cache("user_groups");        
        
        // ----------------------
        // Log it!
        // ----------------------
        log_admin_action("usergroups", "doedit", "Edited user group: ".$group_info['name']);
        
        // ----------------------
        // Done
        // ----------------------
        $output -> redirect(ROOT."admin/index.php?m=usergroups&amp;m2=edit&amp;id=".$get_id, $lang['usergroup_updated_sucessfully']);

}
*/

//***********************************************
// Baleetion!
//***********************************************
function page_delete_group()
{

        global $output, $lang, $db, $template_admin;


        // ----------------
        // Grab the group we're editing
        // ----------------
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select id,name,removable from ".$db -> table_prefix."user_groups where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        // array me
        $group_array = $db -> fetch_array($group);
        
        // Can we delete it?
        if(!$group_array['removable'])
        {
                $output -> add($template_admin -> critical_error($lang['group_non_removable']));
                page_main();
                return;
        }

        // *************************
        // Build group dropdown
        // *************************
        $user_groups = $db -> query("select id, name from ".$db -> table_prefix."user_groups where id != '".$get_id."' order by id asc");

        while($g_array = $db -> fetch_array($user_groups))
        {
        
                $groups_dropdown_text[] .= $g_array['name']; 
                $groups_dropdown_values[] .= $g_array['id']; 
        
        }

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['usergroups_delete_title'];

	$output -> add_breadcrumb($lang['breadcrumb_usergroups_delete'], "index.php?m=usergroups&amp;m2=delete&amp;id=".$get_id);

        // *************************
        // Delete form
        // *************************
        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // ***************************
        // Need different headers
        // ***************************
        $output -> add(
                $form -> start_form("deletegroup", ROOT."admin/index.php?m=usergroups&amp;m2=dodelete&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['usergroups_delete_title'], "strip1",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array($output -> replace_number_tags($lang['usergroups_delete_message'], array($group_array['name'])), "50%"),
                                array($form -> input_dropdown("replace", "", $groups_dropdown_values, $groups_dropdown_text), "50%")
                        ),
                "normalcell").
                $table -> add_basic_row($form->submit("submit", $lang['usergroups_delete_submit']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );

}


//***********************************************
// Actually deleting this time
//***********************************************
function do_delete_group()
{

        global $output, $lang, $db, $template_admin, $cache;

        // ********************
        // Grab the group we're editing
        // ********************
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $group = $db -> query("select id,removable,name from ".$db -> table_prefix."user_groups where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        // array me
        $group_array = $db -> fetch_array($group);
        
        // Can we delete it?
        if(!$group_array['removable'])
        {
                $output -> add($template_admin -> critical_error($lang['group_non_removable']));
                page_main();
                return;
        }


        // ********************
        // Check the group to move to
        // ********************
        $replace_id = trim($_POST['replace']);

        // No ID
        if($replace_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }
                
        // Grab wanted group
        $replace_group = $db -> query("select id from ".$db -> table_prefix."user_groups where id='".$replace_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($replace_group) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_group_id']));
                page_main();
                return;
        }

        // ********************
        // Delete it
        // ********************
        if(!$db -> basic_delete("user_groups", "id = '".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['usergroup_delete_fail']));
                page_main();
                return;
        }
        
        // ********************
        // Move users
        // ********************
        if(!$db -> basic_update("users", array("user_group" => $replace_id), "user_group='".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['usergroup_delete_move_fail']));
                page_main();
                return;
        }

	// Remove everyone with this as secondary group
	// Got this little beaut' from a comment in the mysql docs         
        if(!$db -> query("UPDATE ".$db -> table_prefix."users SET secondary_user_group = TRIM(BOTH ',' FROM REPLACE(  CONCAT(',', `secondary_user_group`, ',') , CONCAT(',', '".$get_id."', ',') , ','  ))"))
        {
                $output -> add($template_admin -> critical_error($lang['usergroup_delete_move_fail']));
                page_main();
                return;
        }

	// Kill promotions
        $db -> basic_delete("promotions", "group_id = '".$get_id."' OR group_to_id = '".$get_id."'");

        // ********************
        // Update cache
        // ********************
        $cache -> update_cache("user_groups");        

        // Delete/Update perms
	$db -> basic_delete("forums_perms", "group_id = '".$get_id."'");
        $cache -> update_cache("forums_perms");        

        // Delete/Update moderators
	$db -> basic_delete("moderators", "group_id = '".$get_id."'");
        $cache -> update_cache("moderators");        
        
        // ********************
        // Log it!
        // ********************
        log_admin_action("usergroups", "dodelete", "Deleted user group: ".$group_array['name']);
        
        // ----------------------
        // Done
        // ----------------------
        $output -> redirect(ROOT."admin/index.php?m=usergroups", $lang['usergroup_deleted_sucessfully']);

}

?>
