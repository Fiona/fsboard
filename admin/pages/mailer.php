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
 * Mass-mailer (SPAM ENGINE)
 * 
 * This will let admins start mailing users.
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


// This page was refactored to Prometheus Burning
load_language_group("admin_mailer");


// Common page functions
include ROOT."admin/common/funcs/mailer.funcs.php";


// Main page crumb
$output -> add_breadcrumb($lang['breadcrumb_mailer'], l("admin/mailer/"));


// Call the main form
page_mailer((isset($page_matches['mode']) ? $page_matches['mode'] : ""));


/**
 * The main form lets you select the criteria for sending mail
 */
function page_mailer($mode)
{

	global $lang, $output, $template_admin, $cache;
		
	$output -> page_title = $lang['mail_search_title'];

	$form = new form(
		array(
			"meta" => array(
				"method" => "GET",
				"name" => "user_search",
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("mailer")
					),
				"validation_func" => "form_mailer_validate",
				"complete_func" => "form_mailer_complete",
				"title" => $lang['mail_search_title'],
				"data_mode" => $mode
				),

			"#mail_from" => array(
				"name" => $lang['mail_add_from'],
				"type" => "text",
				"required" => True,
				"value" => $cache -> cache['config']['mail_from_address']
				),
			"#mail_subject" => array(
				"name" => $lang['mail_add_subject'],
				"type" => "text",
				"required" => True
				),
			"#mail_contents" => array(
				"name" => $lang['mail_add_contents'],
				"type" => "textarea",
				"required" => True,
				"value" => $lang['mail_search_default_contents']
				),
			"contents_instructions" => array(
				"value" => (
					"<table>".
					"<tr>".
					"<td><strong>{board_name}</strong></td><td>".$lang['mail_vars_board_name']."</td>".
					"<td><strong>{user_num}</strong></td><td>".$lang['mail_vars_user_num']."</td>".
					"</tr>".

					"<tr>".
					"<td><strong>{board_url}</strong></td><td>".$lang['mail_vars_board_url']."</td>".
					"<td><strong>{post_num}</strong></td><td>".$lang['mail_vars_post_num']."</td>".
					"</tr>".

					"<tr>".
					"<td><strong>{user_id}</strong></td><td>".$lang['mail_vars_user_id']."</td>".
					"<td><strong>{user_name}</strong></td><td>".$lang['mail_vars_user_name']."</td>".
					"</tr>".

					"<tr>".
					"<td><strong>{user_joined}</strong></td><td>".$lang['mail_vars_user_joined']."</td>".
					"<td><strong>{user_posts}</strong></td><td>".$lang['mail_vars_user_posts']."</td>".
					"</tr>".

					"<tr>".
					"<td><strong>{user_email}</strong></td><td>".$lang['mail_vars_user_email']."</td>".
					"<td><strong>{user_usergroup}</strong></td><td>".$lang['mail_vars_user_usergroup']."</td>".
					"</tr>".
					"</table>"
					),
				"type" => "mini_message"
				),
			"#ignore_admin" => array(
				"name" => $lang['mail_search_ignore_admin'],
				"type" => "yesno"
				),
			"#bulk_num" => array(
				"name" => $lang['mail_search_bulk_num'],
				"type" => "int",
				"value" => 50
				)
			)
		);

	if(defined("DEVELOPER"))
		$form -> form_state["#search_test"] = array(
			"name" => $lang['mail_search_test'],
			"type" => "yesno"
			);

	$form -> form_state["search_users_title"] = array(
		"title" => $lang['mail_search_subtitle_search_criteria'],
		"description" => $lang['mail_search_message'],
		"type" => "message"
		);

	// Get the user groups
	include_once ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();
	$form -> form_state['meta']['data_user_groups'] = $groups;

	// Add the general search fields for users
	load_language_group("admin_users");
	include ROOT."admin/common/funcs/users.funcs.php";
	users_add_user_search_form_fields($form, $groups);

	// Custom profile fields
	include ROOT."admin/common/funcs/profile_fields.funcs.php";
	$form -> form_state['meta']['data_custom_profile_fields'] = profile_fields_get_fields();
	users_add_custom_profile_form_fields(
		$form,
		False,
		False,
		True,
		$form -> form_state['meta']['data_custom_profile_fields']
		);

	$form -> form_state["#submit"] = array(
		"value" => $lang['mail_search_submit'],
		"type" => "submit"
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for mailing users
 *
 * @param object $form
 */
function form_mailer_validate($form)
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

	// Get the e-mail info that's not related to the search fields
	$form -> form_state['meta']['data_email_info'] = array(
		"mail_from" => $_GET['mail_from'],
		"mail_subject" => $_GET['mail_subject'],
		"mail_contents" => $_GET['mail_contents'],
		"ignore_admin" => (isset($_GET['ignore_admin']) ? $_GET['ignore_admin'] : 0),
		"bulk_num" => $_GET['bulk_num'],
		"search_test" => (isset($_GET['search_test']) ? $_GET['search_test'] : 0)
		);

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
function form_mailer_complete($form)
{

	global $lang, $output, $cache, $template_admin, $user;

	// If we've got to the page via some other means
	// like the "back to results" link we check here
	// by checking for this optional item and simply
	// show the form again
	if(!isset($_GET['submit']))
		return;

	$output -> add_breadcrumb($lang['breadcrumb_preview_mail'], l("admin/mailer/"));


	// If we should be sending then we do it, and skip the results table
	if($form -> form_state['meta']['data_mode'] == "send")
	{
		page_send_mail($form);
		return;
	}

	$preview_email_contents = mailer_replace_email_variables(
		$form -> form_state['meta']['data_email_info']['mail_contents'],
		$user -> info
		);

	$extra_url = users_build_user_search_url(
		$form -> form_state['meta']['data_custom_profile_fields'],
		False,
		$form -> form_state['meta']['data_email_info']
		);

	// Define the table
	$results_table = new results_table(
		array(
			"items_per_page" => 50,

			"title" => $template_admin -> form_header_icon("mailer").$lang['mail_preview_title'],
			"description" => nl2br($preview_email_contents),
			"no_results_message" => $lang['mail_preview_no_recipients'],

			"db_table" => "users u",
			"db_where" => $form -> form_state['meta']['search_query']['where'],

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

			"extra_url" => $extra_url,

			"back_url" => "?".users_build_user_search_url(
				$form -> form_state['meta']['data_custom_profile_fields'],
				True,
				$form -> form_state['meta']['data_email_info']
				),
			"back_text" => $lang['mail_preview_go_back'],

			"title_button" => array(
				"type" => "mail",
				"text" => $lang['mail_preview_send'],
				"url" => l("admin/mailer/send/?".$extra_url)
				),

			"default_sort" => "username",

			"columns" => array(
				"username" => array(
					"name" => $lang['mail_preview_subtitle_recipients'],
					"db_column" => "username",
					"sortable" => True
					),
				"email" => array(
					"name" => $lang['mail_preview_subtitle_recipients'],
					"db_column" => "email",
					"sortable" => True
					)
				)
			)
		);

	$output -> add($results_table -> render());

	// Stop search form displaying
	$form -> form_state['meta']['halt_form_render'] = True;

}


/**
 * After previewing the e-mail list we go here which will queue for sending
 */
function page_send_mail($form)
{

	global $db, $lang, $output;

	// Stop search form displaying when we're sending
	$form -> form_state['meta']['halt_form_render'] = True;

	// Get the user list
	$user_select = $db -> basic_select(
		array(
			"what" => "u.username, u.email, u.id, u.posts, u.user_group, u.registered",
			"table" => "users u",
			"where" => $form -> form_state['meta']['search_query']['where'],

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
			)
		);

	// If we somehow didn't get anything then fail and put the form back up
	$total_number_of_users_selected = $db -> num_rows();

	if(!$total_number_of_users_selected)
	{
		$form -> set_error(NULL, $lang['search_no_results']);
		$form -> form_state['meta']['halt_form_render'] = False;
		return;
	}

	// First insert the mail set. This is so we can keep track of the mailing list progress.
	$db -> basic_insert(
		array(
			"table" => "mass_mailer",
			"data" => array(
				"bulk_num" => $form -> form_state['#bulk_num']['value'],
				"emails_left" => $total_number_of_users_selected,
				"emails_sent" => "0",
				"from_email" => $form -> form_state['#mail_from']['value'],
				"test" => (isset($form -> form_state['#search_test']['value']) ? $form -> form_state['#search_test']['value'] : 0)
				)
			)
		);
        
	$mass_mailing_set_id = $db -> insert_id();
        

	// Go through all the users we have selected and insert them into the database for sending
	while($user = $db -> fetch_array($user_select))
	{

		$email_text = mailer_replace_email_variables($form -> form_state['#mail_contents']['value'], $user);

		$db -> basic_insert(
			array(
				"table" => "mass_mailer_emails",
				"data" => array(
					"set_id" => $mass_mailing_set_id,
					"to_email" => $user['email'],
					"subject" => $form -> form_state['#mail_subject']['value'],
					"contents" => $email_text
					)
				)
			);

	}

	// Update the current configuration value for how many mails are waiting
	mailer_cache_waiting_mail_update(1);


	// Done - redirect
	$output -> redirect(
		l("admin/mailer/"),
		$lang['emails_added_sucessfully']
		);

}

?>
