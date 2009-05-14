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
 * Admin area - User management
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


// Is this a dagger I see before me? NO!
load_language_group("admin_users");


// Functions please Jeeves..
include ROOT."admin/common/funcs/users.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_users'], l("admin/users/"));


$mode = isset($page_matches['mode']) ? $page_matches['mode'] : "";

switch($mode)
{
	case "add":
		page_add_user();
		break;

	case "search":
		page_search_users();
		break;

	case "edit":
		page_edit_user($page_matches['user_id']);
		break;

	case "username":
		page_edit_user_username($page_matches['user_id']);
		break;

	case "password":
		page_edit_user_password($page_matches['user_id']);
		break;

	case "delete":
		page_delete_user($page_matches['user_id']);
		break;

	case "ipsearch":
		page_ipsearch_users();
		break;

	default:
		page_search_users();

}


/**
 * Page to create a new user
 */
function page_add_user()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['add_user_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_add'],
		l("admin/users/add/")
		);

	// Get a list of user groups for the form
	include ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();

	$dropdown_options = array();

	foreach($groups as $group_id => $group_info)
		$dropdown_options[$group_id] = $group_info['name'];

	// Add user form
	$form = new form(
		array(
			"meta" => array(
				"name" => "user_add",
				"title" => $lang['add_user_title'],
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("users")
					),
				"validation_func" => "form_users_add_validate",
				"complete_func" => "form_users_add_complete"
				),
			"#username" => array(
				"name" => $lang['add_user_form_username'],
				"type" => "text",
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("username", False)
				),
			"#password" => array(
				"name" => $lang['add_user_form_password'],
				"type" => "text",
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("password", False)
				),
			"#email" => array(
				"name" => $lang['add_user_form_email'],
				"type" => "text",
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("email", False)
				),
			"#user_group" => array(
				"name" => $lang['add_user_form_usergroup'],
				"type" => "dropdown",
				"options" => $dropdown_options,
				"required" => True,
				"extra_field_contents_left" => $output -> help_button("usergroup", False)
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $lang['add_user_submit']
				)
			)
		);

	$output -> add($form -> render());

}



/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for creating a new user
 *
 * @param object $form
 */
function form_users_add_validate($form)
{
   
	global $db, $lang, $page_matches;

	$form -> form_state['#email']['value'] = users_sanitise_email_address(
		$form -> form_state['#email']['value']
		);

	$error = users_add_verify_username(
		$form -> form_state['#username']['value'],
		True
		);
	if($error !== True)
		$form -> set_error("username", $error);

	$error = users_verify_password(
		$form -> form_state['#password']['value'],
		True
		);
	if($error !== True)
		$form -> set_error("password", $error);

	$error = users_verify_email(
		$form -> form_state['#email']['value'],
		True
		);
	if($error !== True)
		$form -> set_error("email", $error);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for creating a new user
 *
 * @param object $form
 */
function form_users_add_complete($form)
{

	global $output, $lang;

	$new_user_id = users_add_user(
		$form -> form_state['#username']['value'],
		$form -> form_state['#password']['value'],
		$form -> form_state['#email']['value'],
		$form -> form_state['#user_group']['value']
		);

	if($new_user_id === False)
		return False;

	log_admin_action(
		"users",
		"add",
		"Added new user: ".$form -> form_state['#username']['value']
		);

	$output -> redirect(
		l("admin/users/edit/".$new_user_id."/"),
		$lang['user_added_sucessfully']
		);

}


/**
 * Page for searching for users
 */
function page_search_users()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['search_user_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_search'],
		l("admin/users/search/")
		);

	// Get a list of user groups for the form
	include ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();

	$dropdown_options = array();

	foreach($groups as $group_id => $group_info)
		$dropdown_options[(string)$group_id] = $group_info['name'];

	// Get all the custom profile fields
	include ROOT."admin/common/funcs/profile_fields.funcs.php";
	$custom_profile_fields = profile_fields_get_fields();

	// Begin defining search form
	$form = new form(
		array(
			"meta" => array(
				"method" => "GET",
				"name" => "user_search",
				"title" => $lang['search_user_title'],
				"description" => $lang['search_user_message'],
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("users")
					),
				"validation_func" => "form_users_search_validate",
				"complete_func" => "form_users_search_complete",
				"data_user_groups" => $groups,
				"data_custom_profile_fields" => $custom_profile_fields
				)
			)
		);

	// Yeah this is terrible.
	// The user name search wanted an extra dropdown sitting in there, what 
	// i'm gonna do is just put put this html in  with the extra_field_contents_left setting and hopefully it will work
	// alright - I'm just gonna go for the $_POST data later for this one item.
	global $template_global_forms;

	$user_search_critera = $template_global_forms -> form_field_dropdown(
		"username_search",
		array(
			"options" => array(
				0 => $lang['username_search_contains'],
				1 => $lang['username_search_exactly'],
				2 => $lang['username_search_starts'],
				3 => $lang['username_search_end']
				),
			"size" => 0,
			"value" => (isset($_POST['username_search']) ? $_POST['username_search'] : 0)
			),
		$form -> form_state
		);

	// Finish the form definition
	$form -> form_state = $form -> form_state + array(
		"#username" => array(
			"name" => $lang['search_user_username'],
			"type" => "text",
			"extra_field_contents_left" => (
				$output -> help_button("email", False).
				$user_search_critera
				),
			),
		"#email" => array(
			"name" => $lang['search_user_email'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("email", False)
			),
		"#usergroup" => array(
			"name" => $lang['search_user_usergroup'],
			"type" => "dropdown",
			"blank_option" => True,
			"options" => $dropdown_options,
			"extra_field_contents_left" => $output -> help_button("usergroup", False)
			),
		"#usergroup_secondary" => array(
			"name" => $lang['search_user_usergroup_secondary'],
			"type" => "checkboxes",
			"options" => $dropdown_options,
			"extra_field_contents_left" => $output -> help_button(
				"usergroup_secondary",
				False
				)
			),
		"#title" => array(
			"name" => $lang['search_user_user_title'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("title", False)
			),
		"#signature" => array(
			"name" => $lang['search_user_signature'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("signature", False)
			),
		"#homepage" => array(
			"name" => $lang['search_user_homepage'],
			"type" => "text",
			"extra_field_contents_left" => $output -> help_button("homepage", False)
			),

		"search_subtitle_posts" => array(
			"title" => $lang['search_subtitle_posts'],
			"type" => "message"
			),
		"#posts_g" => array(
			"name" => $lang['search_user_posts_g'],
			"type" => "int",
			"extra_field_contents_left" => $output -> help_button("posts_g", False)
			),
		"#posts_l" => array(
			"name" => $lang['search_user_posts_l'],
			"type" => "int",
			"extra_field_contents_left" => $output -> help_button("posts_l", False)
			),

		"search_subtitle_times" => array(
			"title" => $lang['search_subtitle_times'],
			"type" => "message"
			),
		"#register_b" => array(
			"name" => $lang['search_user_register_b'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("register_b", False)
			),
		"#register_a" => array(
			"name" => $lang['search_user_register_a'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("register_a", False)
			),
		"#last_active_b" => array(
			"name" => $lang['search_user_last_active_b'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_active_b", False)
			),
		"#last_active_a" => array(
			"name" => $lang['search_user_last_active_a'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_active_a", False)
			),
		"#last_post_a" => array(
			"name" => $lang['search_user_last_post_a'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_post_a", False)
			),
		"#last_post_b" => array(
			"name" => $lang['search_user_last_post_b'],
			"type" => "date",
			"extra_field_contents_left" => $output -> help_button("last_post_b", False)
			)
		);

	// Custom profile fields
	users_add_custom_profile_form_fields($form, False, False, True, $custom_profile_fields);

	$form -> form_state["#submit"] = array(
			"type" => "submit",
			"value" => $lang['search_users_submit']
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for searching for users
 *
 * @param object $form
 */
function form_users_search_validate($form)
{
   
	global $lang;

	// Gather together all the data for building the query
	$search_data = array(
		"username" => $form -> form_state['#username']['value'],
		"username_search" => (int)$_GET['username_search'],
		"email" => $form -> form_state['#email']['value'],
		"usergroup" => $form -> form_state['#usergroup']['value'],
		"usergroup_secondary" => $form -> form_state['#usergroup_secondary']['value'],
		"title" => $form -> form_state['#title']['value'],
		"signature" => $form -> form_state['#signature']['value'],
		"homepage" => $form -> form_state['#homepage']['value'],
		"posts_g" => $form -> form_state['#posts_g']['value'],
		"posts_l" => $form -> form_state['#posts_l']['value'],
		"register_b" => $form -> get_date_timestamp('#register_b'),
		"register_a" => $form -> get_date_timestamp('#register_a'),
		"last_active_b" => $form -> get_date_timestamp('#last_active_b'),
		"last_active_a" => $form -> get_date_timestamp('#last_active_a'),
		"last_post_b" => $form -> get_date_timestamp('#last_post_b'),
		"last_post_a" => $form -> get_date_timestamp('#last_post_a')
		);

	// Get the custom fields
	foreach($form -> form_state['meta']['data_custom_profile_fields'] as $field_id => $field_info)
		$search_data["field_".$field_id] = $form -> form_state['#field_'.$field_id]['value'];

	// Build the query
	$query_array = users_build_user_search_query_array(
		$search_data,
		$form -> form_state['meta']['data_user_groups'],
		$form -> form_state['meta']['data_custom_profile_fields']
		);

	// Something went wrong
	if(!is_array($query_array))
		$form -> set_error(NULL, $lang['invalid_search']);

	// Pass it to the form so the completion function can search on it
	$form -> form_state['meta']['search_query'] = $query_array;

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for searching for users
 *
 * @param object $form
 */
function form_users_search_complete($form)
{

	global $lang, $output, $cache, $template_admin;

	// If we've got to the page via some other means
	// like the "back to results" link we check here
	// by checking for this optional item and simply
	// show the form again
	if(!isset($_GET['submit']))
		return;

	// Define the table
	$results_table = new results_table(
		array(
			"items_per_page" => 50,

			"title" => $template_admin -> form_header_icon("users").$lang['search_user_title'],
			"no_results_message" => $lang['search_no_results'],

			"db_table" => "users u",
			"db_where" => $form -> form_state['meta']['search_query']['where'],
			"db_extra_what" => array("`username`", "`ip_address`"),

			"db_extra_settings" => array(
				"join" => array(
					array(
						"join" => "profile_fields_data p",
						"join_type" => "LEFT",
						"join_on" => "p.member_id = u.id"
						),
					array(
						"join" => "users_secondary_groups s",
						"join_type" => "LEFT",
						"join_on" => "s.user_id = u.id"
						)
					),
				"distinct" => True
				),

			"extra_url" => users_build_user_search_url($form -> form_state['meta']['data_custom_profile_fields']),

			"back_url" => "?".users_build_user_search_url($form -> form_state['meta']['data_custom_profile_fields'], True),
			"back_text" => $lang['users_search_back_button'],

			"default_sort" => "username",

			"columns" => array(
				"username" => array(
					"name" => $lang['search_results_username'],
					"content_callback" => 'table_users_search_username_callback',
					"sortable" => True
					),
				"email" => array(
					"name" => $lang['search_results_email'],
					"db_column" => "email",
					"sanitise" => True,
					"sortable" => True
					),
				"posts" => array(
					"name" => $lang['search_results_posts'],
					"db_column" => "posts",
					"sortable" => True
					),
				"last_active" => array(
					"name" => $lang['search_results_last_active'],
					"db_column" => "last_active",
					"date_format" => $cache -> cache['config']['format_date'],
					"sortable" => True
					),
				"registered" => array(
					"name" => $lang['search_results_registered'],
					"db_column" => "registered",
					"date_format" => $cache -> cache['config']['format_date'],
					"sortable" => True
					),
				"actions" => array(
					"content_callback" => 'table_users_search_actions_callback'
					)
				)
			)
		);

	$output -> add($results_table -> render());

	// Stop search form displaying
	$form -> form_state['meta']['halt_form_render'] = True;

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the user name to inject a link in.
 *
 * @param object $form
 */
function table_users_search_username_callback($row_data)
{

	global $lang;

	return (
		'<a href="'.l("admin/users/edit/".$row_data['id']."/").'" '.
		'title="'.$lang['search_users_view'].'">'.sanitise_user_input($row_data['username']).'</a>'.
		'<br /><p class="results_table_small_text">'.$row_data['ip_address'].
		' (<a href="'.l("admin/users/ipsearch/user/".$row_data['id']."/").'">'.$lang['search_users_ip_info'].'</a>)</p>'
		);

}



/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the user search actions.
 *
 * @param object $form
 */
function table_users_search_actions_callback($row_data)
{

	global $lang, $template_global_results_table;

	return (
		$template_global_results_table -> action_button(
			"edit",
			$lang['search_users_edit'],
			l("admin/users/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['search_users_delete'],
			l("admin/users/delete/".$row_data['id']."/")
			)
		);

}


/**
 * Page to edit an existing user
 */
function page_edit_user($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

        
	// Sort out the birthday vaules
	$user_info['birthday'] = array(
		"day" => $user_info['birthday_day'],
		"month" => $user_info['birthday_month'],
		"year" => $user_info['birthday_year']
		);

	// Expand secondary usergroups
	$user_info['user_groups_secondary_expanded'] = array();
	foreach($user_info['secondary_user_groups'] as $val)
		$user_info['user_groups_secondary_expanded'][$val] = 1;         

	// Build the timezone dropdown
	$time_offset_dropdown = array(
		-12 => $lang['timezone_gmt_minus_12'],
		-11 => $lang['timezone_gmt_minus_11'],
		-10 => $lang['timezone_gmt_minus_10'],
		-9 => $lang['timezone_gmt_minus_9'],
		-8 => $lang['timezone_gmt_minus_8'],
		-7 => $lang['timezone_gmt_minus_7'],
		-6 => $lang['timezone_gmt_minus_6'],
		-5 => $lang['timezone_gmt_minus_5'],
		-4 => $lang['timezone_gmt_minus_4'],
		-3 => $lang['timezone_gmt_minus_3'],
		-2 => $lang['timezone_gmt_minus_2'],
		-1 => $lang['timezone_gmt_minus_1'],
		0 => $lang['timezone_gmt'],
		1 => $lang['timezone_gmt_plus_1'],
		2 => $lang['timezone_gmt_plus_2'],
		3 => $lang['timezone_gmt_plus_3'],
		4 => $lang['timezone_gmt_plus_4'],
		5 => $lang['timezone_gmt_plus_5'],
		6 => $lang['timezone_gmt_plus_6'],
		7 => $lang['timezone_gmt_plus_7'],
		8 => $lang['timezone_gmt_plus_8'],
		9 => $lang['timezone_gmt_plus_9'],
 		10 => $lang['timezone_gmt_plus_10'],
		11 => $lang['timezone_gmt_plus_11'],
		12 => $lang['timezone_gmt_plus_12']
        );

	// Get data for user groups fields
	include ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();

	$user_groups_options = array();

	foreach($groups as $group_id => $group_info)
		$user_groups_options[$group_id] = $group_info['name'];


	// Get data for language selection
	include ROOT."admin/common/funcs/languages.funcs.php";
	$langs = languages_get_languages();

	$languages_options = array(-1 => $lang['edit_user_board_default']);

	foreach($langs as $lang_id => $lang_info)
		$languages_options[$lang_id] = $lang_info['name'];


	// Get data for theme selection
	include ROOT."admin/common/funcs/themes.funcs.php";
	$themes = themes_get_themes(False);

	$themes_options = array(-1 => $lang['edit_user_board_default']);

	foreach($themes as $theme_id => $theme_info)
		$themes_options[$theme_id] = $theme_info['name'];


	// Set up the page
	$output -> page_title = $output -> replace_number_tags(
		$lang['edit_user_title'],
		array(sanitise_user_input($user_info['username']))
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_edit'],
		l("admin/users/edit/".$user_id."/")
		);

	$form = new form(
		array(
			"meta" => array(
				"name" => "edit_user",
				"title" => $output -> page_title,
				"validation_func" => "form_users_edit_user_validate",
				"complete_func" => "form_users_edit_user_complete",
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") =>
							$lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") =>
							$lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/edit/".$user_id."/")
					),
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"data_user_groups" => $groups,
				"data_languages" => $langs,
				"data_themes" => $themes,
				"data_username" => $user_info['username']
				),
			// ----------------
			// Profile info
			// ----------------
			"profile_info_title" => array(
				"title" => $lang['edit_user_profile_info_title'],
				"type" => "message"
				),
			"#email" => array(
				"name" => $lang['edit_user_email'],
				"type" => "text",
				"value" => $user_info['email'],
				"required" => True,
				),
			"#user_group" => array(
				"name" => $lang['edit_user_usergroup'],
				"type" => "dropdown",
				"value" => $user_info['user_group'],
				"options" => $user_groups_options,
				"required" => True,
				),
			"#user_group_secondary" => array(
				"name" => $lang['edit_user_usergroup_secondary'],
				"type" => "checkboxes",
				"value" => $user_info['user_groups_secondary_expanded'],
				"options" => $user_groups_options
				),
			"#title" => array(
				"name" => $lang['edit_user_usertitle'],
				"type" => "text",
				"value" => $user_info['title']
				),
			"#real_name" => array(
				"name" => $lang['edit_user_real_name'],
				"type" => "text",
				"value" => $user_info['real_name']
				),
			"#homepage" => array(
				"name" => $lang['edit_user_homepage'],
				"type" => "text",
				"value" => $user_info['homepage']
				),
			"#yahoo_messenger" => array(
				"name" => $lang['edit_user_yahoo_messenger'],
				"type" => "text",
				"value" => $user_info['yahoo_messenger']
				),
			"#aol_messenger" => array(
				"name" => $lang['edit_user_aol_messenger'],
				"type" => "text",
				"value" => $user_info['aol_messenger']
				),
			"#msn_messenger" => array(
				"name" => $lang['edit_user_msn_messenger'],
				"type" => "text",
				"value" => $user_info['msn_messenger']
				),
			"#icq_messenger" => array(
				"name" => $lang['edit_user_icq_messenger'],
				"type" => "text",
				"value" => $user_info['icq_messenger']
				),
			"#gtalk_messenger" => array(
				"name" => $lang['edit_user_gtalk_messenger'],
				"type" => "text",
				"value" => $user_info['gtalk_messenger']
				),
			"#birthday" => array(
				"name" => $lang['edit_user_birthday'],
				"type" => "date",
				"value" => $user_info['birthday']
				),
			"#signature" => array(
				"name" => $lang['edit_user_signature'],
				"type" => "textarea",
				"value" => _htmlentities($user_info['signature'])
				),
			"#posts" => array(
				"name" => $lang['edit_user_posts'],
				"type" => "int",
				"value" => $user_info['posts']
				),

			// ----------------
			// Display settings
			// ----------------
			"display_title" => array(
				"title" => $lang['edit_user_display_title'],
				"type" => "message"
				),
			"#language" => array(
				"name" => $lang['edit_user_language'],
				"type" => "dropdown",
				"value" => $user_info['language'],
				"options" => $languages_options
				),
			"#theme" => array(
				"name" => $lang['edit_user_theme'],
				"type" => "dropdown",
				"value" => $user_info['theme'],
				"options" => $themes_options
				),

			// ----------------
			// Board settings
			// ----------------
			"board_settings_title" => array(
				"title" => $lang['edit_user_board_settings_title'],
				"type" => "message"
				),
			"#hide_email" => array(
				"name" => $lang['edit_user_hide_email'],
				"type" => "yesno",
				"value" => $user_info['hide_email']
				),
			"#view_sigs" => array(
				"name" => $lang['edit_user_view_sigs'],
				"type" => "yesno",
				"value" => $user_info['view_sigs']
				),
			"#view_avatars" => array(
				"name" => $lang['edit_user_view_avatars'],
				"type" => "yesno",
				"value" => $user_info['view_avatars']
				),
			"#view_images" => array(
				"name" => $lang['edit_user_view_images'],
				"type" => "yesno",
				"value" => $user_info['view_images']
				),
			"#email_new_pm" => array(
				"name" => $lang['edit_user_email_new_pm'],
				"type" => "yesno",
				"value" => $user_info['email_new_pm']
				),
			"#email_from_admin" => array(
				"name" => $lang['edit_user_email_from_admin'],
				"type" => "yesno",
				"value" => $user_info['email_from_admin']
				),

			// ----------------
			// Time settings
			// ----------------
			"time_title" => array(
				"title" => $lang['edit_user_time_title'],
				"type" => "message"
				),
			"#time_offset" => array(
				"name" => $lang['edit_user_time_offset'],
				"type" => "dropdown",
				"value" => $user_info['time_offset'],
				"options" => $time_offset_dropdown
				),
			"#dst_on" => array(
				"name" => $lang['edit_user_dst_on'],
				"type" => "yesno",
				"value" => $user_info['dst_on']
				),
			"#registered" => array(
				"name" => $lang['edit_user_registered'],
				"type" => "date",
				"value" => $user_info['registered']
				),
			"#last_active" => array(
				"name" => $lang['edit_user_last_active'],
				"type" => "date",
				"value" => $user_info['last_active'],
				"time" => True
				),
			"#last_post_time" => array(
				"name" => $lang['edit_user_last_post_date'],
				"type" => "date",
				"value" => $user_info['last_post_time'],
				"time" => True
				),

			)

		);

	
	// Custom profile fields
	users_add_custom_profile_form_fields($form, False, True, False, NULL, $user_info);

	// Submit button
	$form -> form_state['#submit'] = array(
		"type" => "submit",
		"value" => $lang['edit_user_submit']
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing an existing user
 *
 * @param object $form
 */
function form_users_edit_user_validate($form)
{

	global $db, $page_matches, $user, $lang;
	
	// Cannot edit your own primary user group
	if(
		$user -> user_id == $page_matches['user_id'] &&
		$user -> info['user_group'] != $form -> form_state['#user_group']['value']
		)
		$form -> set_error("user_group", $lang['cant_edit_own_group']);        

	// Check email is alright
	$form -> form_state['#email']['value'] = users_sanitise_email_address(
		$form -> form_state['#email']['value']
		);

	$error = users_verify_email($form -> form_state['#email']['value'], True);
	if($error !== True)
		$form -> set_error("email", $error);

	// Check theme
	if(
		$form -> form_state['#theme']['value'] != -1 &&
		!array_key_exists(
			$form -> form_state['#theme']['value'],
			$form -> form_state['meta']['data_themes']
			)
		)
		$form -> set_error("theme", $lang['edit_user_invalid_theme']);

	// Check language
	if(
		$form -> form_state['#language']['value'] != -1 &&
		!array_key_exists(
			$form -> form_state['#language']['value'],
			$form -> form_state['meta']['data_languages']
			)
		)
		$form -> set_error("language", $lang['edit_user_invalid_language']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing an existing user
 *
 * @param object $form
 */
function form_users_edit_user_complete($form)
{

	global $db, $page_matches, $user, $lang, $output;
	
	// First grab all the normal user info
	$user_info = array(
		"email" 				=> $form -> form_state['#email']['value'],
		"user_group" 			=> $form -> form_state['#user_group']['value'],
		"secondary_user_group"  => $form -> form_state['#user_group_secondary']['value'],
		"title" 				=> $form -> form_state['#title']['value'],
		"real_name" 			=> $form -> form_state['#real_name']['value'],
		"homepage" 				=> $form -> form_state['#homepage']['value'],
		"yahoo_messenger"	 	=> $form -> form_state['#yahoo_messenger']['value'],
		"msn_messenger" 		=> $form -> form_state['#msn_messenger']['value'],
		"icq_messenger" 		=> $form -> form_state['#icq_messenger']['value'],
		"gtalk_messenger" 		=> $form -> form_state['#gtalk_messenger']['value'],
		"birthday_day" 			=> $form -> form_state['#birthday']['value']['day'],
		"birthday_month"	 	=> $form -> form_state['#birthday']['value']['month'],
		"birthday_year" 		=> $form -> form_state['#birthday']['value']['year'],
		"signature" 			=> $form -> form_state['#signature']['value'],
		"posts" 				=> $form -> form_state['#posts']['value'],
		"language" 				=> $form -> form_state['#language']['value'],
		"theme" 				=> $form -> form_state['#theme']['value'],
		"hide_email"			=> $form -> form_state['#hide_email']['value'],
		"view_sigs"				=> $form -> form_state['#view_sigs']['value'],
		"view_avatars" 			=> $form -> form_state['#view_avatars']['value'],
		"view_images"	 		=> $form -> form_state['#view_images']['value'],
		"email_new_pm"			=> $form -> form_state['#email_new_pm']['value'],
		"email_from_admin"  	=> $form -> form_state['#email_from_admin']['value'],
		"time_offset"	 		=> $form -> form_state['#time_offset']['value'],
		"dst_on" 				=> $form -> form_state['#dst_on']['value'],
		"registered" 			=> $form -> get_date_timestamp('#registered'),
		"last_active"	 		=> $form -> get_date_timestamp('#last_active'),
		"last_post_time"	 	=> $form -> get_date_timestamp('#last_post_time')
		);

	// Get custom field data
	if(
		is_array($form -> form_state['meta']['data_custom_fields']) &&
		count($form -> form_state['meta']['data_custom_fields']) > 0
		)
		foreach($form -> form_state['meta']['data_custom_fields'] as $key => $junk)
			$user_info['field_'.$key] = $form -> form_state['#field_'.$key]['value'];

	// Update the user info
	$update_result = users_update_user(
		$page_matches['user_id'],
		$user_info,
		$form -> form_state['meta']['data_custom_fields']
		);

	if($update_result === False)
		return False;

	// Log the action
	log_admin_action(
		"users",
		"edit",
		"Edited user: ".sanitise_user_input($form -> form_state['meta']['data_username'])
		);

	// Finished
	$output -> redirect(
		l("admin/users/edit/".$page_matches['user_id']."/"),
		$lang['user_updated']
		);

}


/**
 * Page to edit an existing users username
 */
function page_edit_user_username($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

	// Set up the page
	$output -> page_title = $output -> replace_number_tags(
		$lang['edit_username_title'],
		array(sanitise_user_input($user_info['username']))
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_edit'],
		l("admin/users/edit/".$user_id."/")
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_edit_name'],
		l("admin/users/username/".$user_id."/")
		);

	$form = new form(
		array(
			"meta" => array(
				"name" => "edit_username",
				"title" => $output -> page_title,
				"validation_func" => "form_users_edit_username_validate",
				"complete_func" => "form_users_edit_username_complete",
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") =>
							$lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") =>
							$lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/username/".$user_id."/")
					),
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"data_current_username" => $user_info['username'],
				"data_user_email" => $user_info['email']
				),

			"#username" => array(
				"name" => $lang['edit_username_enter_new'],
				"description" => $output -> replace_number_tags(
					$lang['edit_username_current'],
					sanitise_user_input($user_info['username'])
					),
				"type" => "text",
				"value" => $user_info['username'],
				"required" => True,
				),
			"#send_email" => array(
				"name" => $lang['edit_username_send_email'],
				"type" => "yesno",
				"value" => 0
				),
			"#email_contents" => array(
				"name" => $lang['edit_username_email_contents'],
				"description" => $lang['email_changed_username_description'],
				"type" => "textarea",
				"value" => $lang['email_changed_username'],
				"size" => 12
				),

			'#submit' => array(
				"type" => "submit",
				"value" => $lang['edit_username_submit']
				)
			)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing a user's username
 *
 * @param object $form
 */
function form_users_edit_username_validate($form)
{

	$error = users_edit_username_verify_username(
		$form -> form_state['meta']['data_current_username'],
		$form -> form_state['#username']['value']
		);

	if($error !== True)
		$form -> set_error("username", $error);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a user's username
 *
 * @param object $form
 */
function form_users_edit_username_complete($form)
{

	global $page_matches, $cache, $lang, $output;

	$update_result = users_update_username(
		$page_matches['user_id'],
		$form -> form_state['meta']['data_current_username'],
		$form -> form_state['#username']['value']
		);

	if($update_result !== True)
		return False;


	// If we're sending an e-mail alerting the affected user
	if($form -> form_state['#send_email']['value'])
	{
                
		// We need to replace certain tokens in the email message.
		$message = str_replace(
			array(
				'<old_name>',
				'<new_name>',
				'<board_name>',
				'<board_url>'
				),
			array(
				$form -> form_state['meta']['data_current_username'],
				$form -> form_state['#username']['value'],
				$cache -> cache['config']['board_name'],
				$cache -> cache['config']['board_url']
				),
			 $form -> form_state['#email_contents']['value']
			);

		// Send the e-mail
		$mail = new email;
		$mail -> send_mail(
			$form -> form_state['meta']['data_user_email'],
			$lang['email_changed_username_subject'],
			$message
			);
        
	}

	// Log it!
	log_admin_action(
		"users",
		"username", 
		("Changed member '".
		 	sanitise_user_input($form -> form_state['meta']['data_current_username']).
			 "' name to '".$form -> form_state['#username']['value']."'")
		);

	$output -> redirect(
		l("admin/users/username/".$page_matches['user_id']."/"),
		$lang['username_changed_sucessfully']
		);

}


/**
 * Page to edit an existing users password
 */
function page_edit_user_password($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

	// Set up the page
	$output -> page_title = $output -> replace_number_tags(
		$lang['edit_password_title'],
		array(sanitise_user_input($user_info['username']))
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_edit'],
		l("admin/users/edit/".$user_id."/")
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_edit_password'],
		l("admin/users/password/".$user_id."/")
		);

	$form = new form(
		array(
			"meta" => array(
				"name" => "edit_password",
				"title" => $output -> page_title,
				"validation_func" => "form_users_edit_password_validate",
				"complete_func" => "form_users_edit_password_complete",
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") =>
							$lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") => 
							$lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/password/".$user_id."/")
					),
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"data_username" => $user_info['username'],
				"data_user_email" => $user_info['email']
				),

			"#password" => array(
				"name" => $lang['edit_password_enter_new'],
				"type" => "password",
				"required" => True
				),
			"#password2" => array(
				"name" => $lang['edit_password_enter_new_again'],
				"type" => "password",
				"required" => True
				),
			"#send_email" => array(
				"name" => $lang['edit_password_send_email'],
				"type" => "yesno",
				"value" => 0
				),
			"#email_contents" => array(
				"name" => $lang['edit_password_email_contents'],
				"description" => $lang['edit_password_email_contents_description'],
				"type" => "textarea",
				"value" => $lang['email_changed_password'],
				"size" => 12
				),

			'#submit' => array(
				"type" => "submit",
				"value" => $lang['edit_password_submit']
				)
			)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing a user's password
 *
 * @param object $form
 */
function form_users_edit_password_validate($form)
{

	global $lang;

	if($form -> form_state['#password']['value'] != $form -> form_state['#password2']['value'])
		$form -> set_error("password", $lang['change_password_error_no_match']);

	$error = users_verify_password($form -> form_state['#password']['value']);

	if($error !== True)
		$form -> set_error("password", $error);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a user's password
 *
 * @param object $form
 */
function form_users_edit_password_complete($form)
{

	global $page_matches, $cache, $lang, $output;

	$update_result = users_update_password(
		$page_matches['user_id'],
		$form -> form_state['#password']['value']
		);

	if($update_result !== True)
		return False;


	// If we're sending an e-mail alerting the affected user
	if($form -> form_state['#send_email']['value'])
	{
                
		// We need to replace certain tokens in the email message.
		$message = str_replace(
			array(
				'<username>',
				'<new_password>',
				'<board_name>',
				'<board_url>'
				),
			array(
				$form -> form_state['meta']['data_username'],
				$form -> form_state['#password']['value'],
				$cache -> cache['config']['board_name'],
				$cache -> cache['config']['board_url']
				),
			 $form -> form_state['#email_contents']['value']
			);

		// Send the e-mail
		$mail = new email;
		$mail -> send_mail(
			$form -> form_state['meta']['data_user_email'],
			$lang['email_changed_password_subject'],
			$message
			);
        
	}

	// Log it!
	log_admin_action(
		"users",
		"password", 
		"Changed password for '".sanitise_user_input($form -> form_state['meta']['data_username'])."'"
		);

	$output -> redirect(
		l("admin/users/password/".$page_matches['user_id']."/"),
		$lang['password_changed_sucessfully']
		);

}


/**
 * Page to delete an existing user
 */
function page_delete_user($user_id)
{

	global $output, $lang, $template_admin;

	// Get the user info
	$user_info = users_get_user_by_id($user_id);

	if($user_info === False)
	{
		$output -> set_error_message($lang['invalid_user_id']);
		return;
	}

	// Set up the page
	$output -> page_title = $output -> replace_number_tags(
		$lang['delete_user_title'],
		sanitise_user_input($user_info['username'])
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_edit'],
		l("admin/users/edit/".$user_id."/")
		);
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_delete'],
		l("admin/users/delete/".$user_id."/")
		);

	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title ,
				"extra_title_contents_left" => $template_admin -> form_header_icon("users"),
				"admin_sub_menu" => $template_admin -> admin_sub_menu(
					array(
						l("admin/users/edit/".$user_id."/") => $lang['edit_user_edit_profile'],
						l("admin/users/username/".$user_id."/") =>
							$lang['edit_user_change_username'],
						l("admin/users/password/".$user_id."/") =>
							$lang['edit_user_change_password'],
						l("admin/users/delete/".$user_id."/") => $lang['edit_user_delete_user']
						),
					l("admin/users/delete/".$user_id."/")
					),
				"description" => $output -> replace_number_tags(
					$lang['delete_user_message'],
					sanitise_user_input($user_info['username'])
					),
				"callback" => "users_delete_user_complete",
				"arguments" => array($user_id, $user_info['username']),
				"confirm_redirect" => l("admin/users/search/"),
				"cancel_redirect" => l("admin/users/edit/".$user_id."/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a user
 *
 * @param int $user_id The ID of the user being deleted.
 */
function users_delete_user_complete($user_id, $username)
{

	global $user, $output, $lang;

	// Check to see if we're deleting ourselves. (Can't do this.)
	if($user_id == $user -> user_id)
	{
		$output -> set_error_message($lang['cannot_delete_self']);
		return False;
	}

	// Delete the user and check the responce
	$return = users_delete_user($user_id);

	if($return === True)
	{

        // Log it
        log_admin_action("users", "delete", "Deleted user ".sanitise_user_input($username));
		return True;

	}
	else
		return False;

}


/**
 * Search users by IP address
 */
function page_ipsearch_users()
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['search_ip_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_users_ipsearch'],
		l("admin/users/ipsearch/")
		);

	// Search form
	$form = new form(
		array(
			"meta" => array(
				"method" => "GET",
				"name" => "users_ipsearch",
				"title" => $lang['search_ip_title'],
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("users")
					),
				"validation_func" => "form_users_ipsearch_validate",
				"complete_func" => "form_users_ipsearch_complete"
				),
			"#by_ip" => array(
				"name" => $lang['search_ip_by_ip'],
				"type" => "text",
				"extra_field_contents_left" => $output -> help_button("by_ip", False)
				),
			"#by_username" => array(
				"name" => $lang['search_ip_by_name'],
				"type" => "text",
				"extra_field_contents_left" => $output -> help_button("by_name", False)
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $lang['submit_search_ip']
				)
			)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for searching user IP addresses
 *
 * @param object $form
 */
function form_users_ipsearch_validate($form)
{

	global $lang;

	// Check if both inputs are empty
	if(!$form -> form_state['#by_ip']['value'] && !$form -> form_state['#by_username']['value'])
	{
		$form -> set_error(NULL, $lang['search_ip_no_input']);
		return False;
	}

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for searching user IP addresses
 *
 * @param object $form
 */
function form_users_ipsearch_complete($form)
{

	global $output, $lang, $db, $template_admin;

	// If we've got to the page via some other means  like the "change search" link
	// we check here by checking for this optional item and simply show the form
	// again
	if(!isset($_GET['submit']))
		return;

	// If we're searching by IP address
	if($form -> form_state['#by_ip']['value'])
	{

		// Define the table
		$results_table = new results_table(
			array(
			"items_per_page" => 15,

			"title" => (
				$template_admin -> form_header_icon("users").
				$lang['search_ip_results_ip_reg_title']
				),
			"no_results_message" => $lang['search_ip_results_ip_reg_none'],

			"db_table" => "users",
			"db_where" => (
				"ip_address LIKE '%".
				$db -> escape_string($form -> form_state['#by_ip']['value']).
				"%'"
				),
			"db_extra_what" => array("`username`", "`ip_address`"),

			"extra_url" => (
				"by_ip=".urlencode($form -> form_state['#by_ip']['value']).
				"&by_username=".urlencode($form -> form_state['#by_username']['value']).
				"&form_users_ipsearch=".urlencode($_GET['form_users_ipsearch']).
				"&submit=".urlencode($_GET['submit'])
				),

			"back_url" => (
				"?by_ip=".urlencode($form -> form_state['#by_ip']['value']).
				"&by_username=".urlencode($form -> form_state['#by_username']['value']).
				"&form_users_ipsearch=".urlencode($_GET['form_users_ipsearch'])
				),
			"back_text" => $lang['users_search_back_button'],

			"default_sort" => "username",

			"columns" => array(
				"username" => array(
					"name" => $form -> form_state['#by_ip']['value'],
					"content_callback" => 'table_users_ipsearch_username_callback',
					"sortable" => True
					),
				"hostname" => array(
					"name" => $lang['search_ip_hostname'],
					"content_callback" => 'table_users_ipsearch_hostname_callback',
					)
				)
			)
		);

		$output -> add($results_table -> render());

		// Stop search form displaying
		$form -> form_state['meta']['halt_form_render'] = True;

	}
	// If we're searching by name
	else
	{
		$output -> set_error_message("Not yet implemented");
	}

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the username column on ip search
 *
 * @param object $form
 */
function table_users_ipsearch_username_callback($row_data)
{

	global $lang;

	return (
		'<a href="'.l("admin/users/edit/".$row_data['id']."/").'" '.
		'title="'.$lang['search_users_view'].'">'.sanitise_user_input($row_data['username']).'</a>'.
		'<br /><p class=\"results_table_small_text\">'.$row_data['ip_address'].
		' (<a href="'.l("admin/users/ipsearch/user/".$row_data['id']."/").'">'.$lang['search_ip_results_other_ip'].'</a>)</p>'
		);

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the hostname column on ip search
 *
 * @param object $form
 */
function table_users_ipsearch_hostname_callback($row_data)
{

	global $lang;

	$hostname = @gethostbyaddr($row_data['ip_address']);
	$hostname = (!$hostname ? $lang['search_ip_no_hostname'] : $hostname);

	return $hostname;

}

?>
