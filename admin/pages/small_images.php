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
 * Small image management
 * This acts as the hub for editing emoticons, avatars and post icons.
 * They're all very special and very similar so it did not make any sense to
 * not make a general admin interface for all three.
 *
 * TODO: Reordering images and categories.
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


// This file was refactored to Prometheus Burning... Like lots of other refactors.
load_language_group("admin_small_images");


// General page functions
include ROOT."admin/common/funcs/small_images.funcs.php";


// First we determine what image type we're dealing with as various things change
// depending on this.
switch($page_matches['page'])
{

	case "avatars":
		$image_settings = array(
			"type" => "avatars",
			"config_path" => "avatar_upload_path",
			"export_filename" => "fsboard-avatars.xml",
			"xml_root" => "avatars_file"
			);

		break;

	case "emoticons":
		$image_settings = array(
			"type" => "emoticons",
			"config_path" => "emoticon_upload_path",
			"export_filename" => "fsboard-emoticons.xml",
			"xml_root" => "emoticons_file"
			);

		break;

	case "post_icons":
		$image_settings = array(
			"type" => "post_icons",
			"config_path" => "post_icon_upload_path",
			"export_filename" => "fsboard-post-icons.xml",
			"xml_root" => "post_icons_file"
			);

}


// Main root breadcrumb
$output -> add_breadcrumb(
	$lang['breadcrumb_'.$page_matches['page']],
	l("admin/".$page_matches['page'])
	);



// Hey now, we need to make sure that the directory that's supposed to hold
// the images is writable. We might as well do this here.
if(
	!is_dir(ROOT.$cache -> cache['config'][$image_settings['config_path']])
	|| !is_writable(ROOT.$cache -> cache['config'][$image_settings['config_path']]) 
	)
{

	$output -> set_error_message(
		$output -> replace_number_tags($lang['add_image_directory_not_writable'], array($cache -> cache['config'][$image_settings['config_path']]))
		);
	
}


// Action selection (error check te onsure we pass the previous test
if(!count($output -> error_messages))
{

	$page_matches['mode'] = (isset($page_matches['mode']) ? $page_matches['mode'] : "");

	switch($page_matches['mode'])
	{

		case "add":
			if(isset($page_matches['category_id']))
				page_add_small_images($image_settings, $page_matches['category_id']);
			else
				page_add_small_images_category($image_settings);
			break;

		case "add_multiple":
			page_add_multiple_small_images($image_settings, $page_matches['category_id']);
			break;

		case "move_multiple":
			page_move_multiple_small_images($image_settings, $page_matches['category_id']);
			break;

		case "permissions":
			page_edit_permissions_small_images_category($image_settings, $page_matches['category_id']);
			break;

		case "edit":
			if(isset($page_matches['image_id']))
				page_edit_small_images($image_settings, $page_matches['category_id'], $page_matches['image_id']);
			else
				page_edit_small_images_category($image_settings, $page_matches['category_id']);
			break;

		case "delete":
			if(isset($page_matches['image_id']))
				page_delete_small_image($image_settings, $page_matches['image_id']);
			else
				page_delete_small_images_category($image_settings, $page_matches['category_id']);
			break;

		case "view":
			page_view_small_images($image_settings, $page_matches['category_id']);
			break;

		case "backup":
			page_backup_small_images($image_settings);
			break;

		default:
			page_view_small_images_categories($image_settings);

	}

}                


/**
 * Shows all image categories
 */
function page_view_small_images_categories($image_settings)
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['page_title_'.$image_settings['type']];

	// Define the table
	$results_table = new results_table(
		array(
			"title" => $template_admin -> form_header_icon($image_settings['type']).$output -> page_title,
			"description" => $lang['images_main_message_'.$image_settings['type']],
			"no_results_message" => $lang['images_main_no_cats_'.$image_settings['type']],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['image_main_new_cat_title_'.$image_settings['type']],
				"url" => l("admin/".$image_settings['type']."/add/")
				),

			"db_table" => "small_image_cat",
			"db_where" => "`type` = '".$image_settings['type']."'",
			"default_sort" => "order",

			"columns" => array(
				"name" => array(
					"name" => $lang['images_main_name'],
					"db_column" => "name"
					),
				"image_num" => array(
					"name" => $lang['images_main_count'],
					"db_column" => "image_num"
					),
				"actions" => array(
					"name" => $lang['images_main_actions'],
					"content_callback" => 'table_view_small_images_cats_actions_callback',
					'content_callback_parameters' => array($image_settings)
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the image category view actions.
 *
 * @param object $row_data
 */
function table_view_small_images_cats_actions_callback($row_data, $image_settings)
{

	global $lang, $template_global_results_table;

	$return = (
		$template_global_results_table -> action_button(
			"preview",
			$lang['images_main_action_view'],
			l("admin/".$image_settings['type']."/view/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"edit",
			$lang['images_main_action_edit'],
			l("admin/".$image_settings['type']."/edit/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['images_main_action_delete'],
			l("admin/".$image_settings['type']."/delete/".$row_data['id']."/")
			).
		$template_global_results_table -> action_button(
			"run",
			$lang['images_main_action_move_multiple'],
			l("admin/".$image_settings['type']."/move_multiple/".$row_data['id']."/")
			)
		);

	if($image_settings['type'] != "emoticons")
		$return .= $template_global_results_table -> action_button(
			"users",
			$lang['images_main_action_permissions'],
			l("admin/".$image_settings['type']."/permissions/".$row_data['id']."/")
			);

	return $return;

}


/**
 * Page for creating a new category for images.
 */
function page_add_small_images_category($image_settings)
{

	global $output, $lang, $db, $template_admin;

	$output -> page_title = $lang['image_main_new_cat_title_'.$image_settings['type']];
	$output -> add_breadcrumb($output -> page_title, l("admin/".$image_settings['type']."/add/"));

	// Put the form up
	$form = new form(
		form_add_edit_small_images_category("add", NULL, $image_settings)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing image categories
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 * @param array $image_settings Settings for the current image page type
 */
function form_add_edit_small_images_category($type, $initial_data = NULL, $image_settings = array())
{

	global $lang, $output, $template_admin;

	// Form definition
	$form_data = array(
			"meta" => array(
				"name" => "small_images_category_".$image_settings['type']."_".$type,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon($image_settings['type'])
					),
				"initial_data" => $initial_data,
				"data_image_settings" => $image_settings
				),

			"#name" => array(
				"name" => $lang['image_main_new_cat_name'],
				"type" => "text",
				"required" => True
				),
			"#submit" => array(
				"type" => "submit"
				)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['meta']['title'] = $lang['image_main_new_cat_title_'.$image_settings['type']];
		$form_data['meta']['description'] = $lang['image_main_new_cat_message_'.$image_settings['type']];
		$form_data['meta']['complete_func'] = "form_add_small_images_category_complete";
		$form_data['#submit']['value'] = $lang['image_main_new_cat_submit'];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['title'] = $lang['image_edit_cat_title_'.$image_settings['type']];
		$form_data['meta']['description'] = $lang['image_edit_cat_message_'.$image_settings['type']];
		$form_data['meta']['complete_func'] = "form_edit_small_images_category_complete";
		$form_data['#submit']['value'] = $lang['image_main_edit_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding image categories
 *
 * @param object $form
 */
function form_add_small_images_category_complete($form)
{

	global $lang, $output;

	// Try and add the category
	$new_cat_id = small_images_add_category(
		array(
			"name" => $form -> form_state['#name']['value'],
			"type"  => $form -> form_state['meta']['data_image_settings']['type']
			)
		);

	if($new_cat_id === False)
		return False;

	// Log
	log_admin_action(
		"small_images",
		"add",
		"Added small image category: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/"),
		$lang['add_image_cat_created_'.$form -> form_state['meta']['data_image_settings']['type']]
		);

}


/**
 * Page for editing an existing category.
 */
function page_edit_small_images_category($image_settings, $category_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the category
	$category_info = small_images_get_category_by_id($category_id, $image_settings['type']);

	if($category_info === False)
	{
		$output -> set_error_message($lang['invalid_image_cat_id']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// Show the page
	$output -> page_title = $lang['image_edit_cat_title_'.$image_settings['type']];
	$output -> add_breadcrumb(
		$lang['breadcrumb_edit_category'],
		l("admin/".$image_settings['type']."/edit/".$category_id."/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_small_images_category("edit", $category_info, $image_settings)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing image categories
 *
 * @param object $form
 */
function form_edit_small_images_category_complete($form)
{

	global $lang, $output;

	// Try and edit the category
	$update = small_images_edit_category(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"name" => $form -> form_state['#name']['value']
			)
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"small_images",
		"edit",
		"Edited image category: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/"),
		$lang['edit_image_cat_edited']
		);

}


/**
 * Confirmation page to remove an image category
 *
 * @var array $image_settings Settings for the current type of image were on.
 * @var int $category_id ID of the cat we're deleting.
 */
function page_delete_small_images_category($image_settings, $category_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the cat
	$category_info = small_images_get_category_by_id($category_id, $image_settings['type']);

	if($category_info === False)
	{
		$output -> set_error_message($lang['invalid_image_cat_id']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// Add titles
	$output -> page_title = $output -> replace_number_tags($lang['image_delete_cat_title_'.$image_settings['type']], $category_info['name']);
	$output -> add_breadcrumb($output -> page_title, l("admin/".$image_settings['type']."/delete/".$category_id."/"));

	// We want the other categories for the other dropdowns
	$categories = small_images_get_categories($image_settings['type']);
	unset($categories[$category_id]);

	if(!count($categories))
	{
		$output -> set_error_message($lang['error_delete_image_cat_last_one']);
		page_view_small_images_categories($image_settings);
		return;
	}

	$form_field_categories = array();

	foreach($categories as $cat_id => $cat)
		$form_field_categories[$cat_id] = $cat['name'];

	// Spit out the form
	$form = new form(
		array(
			"meta" => array(
				"title" => $output -> page_title,
				"description" => $lang['image_delete_cat_message_'.$image_settings['type']],
				"name" => "small_images_category_".$image_settings['type']."_delete",
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon($image_settings['type'])
					),
				"validation_func" => "form_delete_small_images_category_validate",
				"complete_func" => "form_delete_small_images_category_complete",
				"data_image_settings" => $image_settings,
				"data_category_info" => $category_info,
				),

			"#delete_images" => array(
				"name" => $lang['image_delete_cat_delete_images_'.$image_settings['type']],
				"type" => "yesno",
				"value" => "0"
				),
			"#new_cat" => array(
				"name" => $lang['image_delete_cat_new_cat'],
				"type" => "dropdown",
				"options" => $form_field_categories
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $lang['image_delete_cat_submit']
				)
			)
		);

	$output -> add($form -> render());

}



/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing image categories
 *
 * @param object $form
 */
function form_delete_small_images_category_validate($form)
{

	global $lang, $output;

	// If we're not deleting images then we need to make sure that the category we'll be moving images to exists.
	if(!$form -> form_state['#delete_images']['value'])
		if(small_images_get_category_by_id(
			   $form -> form_state['#new_cat']['value'],
			   $form -> form_state['meta']['data_image_settings']['type']
			   ) === False)
			$form -> set_error("new_cat", $lang['invalid_image_cat_id']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing image categories
 *
 * @param object $form
 */
function form_delete_small_images_category_complete($form)
{

	global $lang, $output;

	// Try and delete the category
	$remove = small_images_delete_category(
		$form -> form_state['meta']['data_category_info'],
		($form -> form_state['#delete_images']['value'] ? True : False),
		$form -> form_state['#new_cat']['value']
		);

	if($remove === False)
		return False;

	// Log
	log_admin_action(
		"small_images",
		"delete",
		"Deleted image category: ".$form -> form_state['meta']['data_category_info']['name']
		);

	// Redirect...
	$output -> redirect(
		l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/"),
		$lang['delete_cat_successful']
		);

}


/*
 * Page for viewing all images in a category.
 */
function page_view_small_images($image_settings, $category_id)
{

	global $lang, $output, $template_admin;

	$output -> page_title = $lang['breadcrumb_'.$image_settings['type']."_view"];
	$output -> add_breadcrumb($output -> page_title, l("admin/".$image_settings['type']."/view/".$category_id."/add/"));

	// Select the cat
	$category_info = small_images_get_category_by_id($category_id, $image_settings['type']);

	if($category_info === False)
	{
		$output -> set_error_message($lang['invalid_image_cat_id']);
		page_view_small_images_categories($image_settings);
		return;
	}


	// Category dropdown
	$menu_categories = array();
	$categories = small_images_get_categories($image_settings['type']);

	foreach($categories as $cat)
		$menu_categories[$cat['id']] = $cat['name'];

	$form = new form(array(
						 "meta" => array(
							 "name" => "small_image_cat_select",
							 "title" => $lang['image_view_menu_title_'.$image_settings['type']],
							 "extra_title_contents_left" => $template_admin -> form_header_icon($image_settings['type']),
							 "validation_func" => "form_images_cat_select_validate",
							 "complete_func" => "form_images_cat_select_complete",
							 "data_image_settings" => $image_settings
							 ),
						 "#category_menu" => array(
							 "name" => $lang['image_view_menu_input'],
							 "type" => "dropdown",
							 "options" => $menu_categories,
							 "required" => True,
							 "value" => $category_info['id']
							 ),
						 "#submit" => array(
							 "type" => "submit",
							 "value" => $lang['image_view_menu_submit']
							 )
						 ));

	$output -> add($form -> render());


	// Define the table
	$results_table = new results_table(
		array(
			"title" => $output -> page_title,
			"no_results_message" => $lang['image_view_no_images_'.$image_settings['type']],
			"title_button" => array(
				"type" => "add",
				"text" => $lang['breadcrumb_'.$image_settings['type'].'_add'],
				"url" => l("admin/".$image_settings['type']."/".$category_info['id']."/add/")
				),

			"db_table" => "small_images",
			"db_extra_what" => array("`filename`", "`cat_id`"),
			"db_where" => "`type` = '".$image_settings['type']."' AND cat_id=".(int)$category_info['id'],
			"default_sort" => "name",

			"columns" => array(
				"file" => array(
					"name" => $lang['image_view_image_file'],
					"content_callback" => "table_view_small_images_file",
					"align" => "center"
					),
				"emoticon_code" => (
					$image_settings['type'] == "emoticons" ?
					array(
						"name" => $lang['image_view_emoticon_code'],
						"db_column" => "emoticon_code"
						) :
					NULL
					),
				"name" => array(
					"name" => $lang['image_view_image_name'],
					"db_column" => "name"
					),
				"actions" => array(
					"name" => $lang['images_main_actions'],
					"content_callback" => 'table_view_small_images_actions_callback',
					'content_callback_parameters' => array($image_settings)
					)
				)
			)
		);

	$output -> add($results_table -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the image view file view.
 *
 * @param object $row_data
 */
function table_view_small_images_file($row_data)
{

	global $cache;

	$file = $cache -> cache['config']['board_url']."/".$row_data['filename'];
	return "<img src=\"".$file."\" alt=\"".$file."\" />";

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the image view actions.
 *
 * @param object $row_data
 */
function table_view_small_images_actions_callback($row_data, $image_settings)
{

	global $lang, $template_global_results_table;

	$return = (
		$template_global_results_table -> action_button(
			"edit",
			$lang['image_view_edit_'.$image_settings['type']],
			l("admin/".$image_settings['type']."/".$row_data['cat_id']."/".$row_data['id']."/edit/")
			).
		$template_global_results_table -> action_button(
			"delete",
			$lang['image_view_delete_'.$image_settings['type']],
			l("admin/".$image_settings['type']."/".$row_data['cat_id']."/".$row_data['id']."/delete/")
			)
		);

	return $return;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for selecting a category
 * Will check if the item we've selected exists
 *
 * @param object $form
 */
function form_images_cat_select_validate($form)
{
   
	global $lang;

	if(!$form -> form_state['#category_menu']['value'])
		return;

	$cat = small_images_get_category_by_id(
		$form -> form_state['#category_menu']['value'],
		$form -> form_state['meta']['data_image_settings']['type']
		);

	if($cat === False)
		$form -> set_error("category_menu", $lang['invalid_image_cat_id']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for selecting a category
 *
 * @param object $form
 */
function form_images_cat_select_complete($form)
{
 
	global $output;

	// Instant redirect to the right page
	$output -> redirect(l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/view/".$form -> form_state['#category_menu']['value']."/"), "", True);

}


/**
 * Form for adding a brand new image
 */
function page_add_small_images($image_settings, $category_id)
{

	global $lang, $output;

	// Select the cat
	$category_info = small_images_get_category_by_id($category_id, $image_settings['type']);

	if($category_info === False)
	{
		$output -> set_error_message($lang['invalid_image_cat_id']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// Breadcrummin'
	$output -> add_breadcrumb($lang['breadcrumb_'.$image_settings['type']."_view"], l("admin/".$image_settings['type']."/view/".$category_id."/"));

	$output -> page_title = $lang['breadcrumb_'.$image_settings['type'].'_add'];

	$output -> add_breadcrumb(
		$lang['breadcrumb_'.$image_settings['type'].'_add'],
		l("admin/".$image_settings['type']."/".$category_id."/add/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_small_images("add", $category_id, NULL, $image_settings)
		);

	$output -> add($form -> render());

	// Adding images has a second form for multiple image adding
	$multiple_form = new form(
		array(
			"meta" => array(
				"title" => $lang['add_many_image_title_'.$image_settings['type']],
				"description" => $lang['add_many_image_message_'.$image_settings['type']],
				"name" => "small_images_".$image_settings['type']."_add_multiple",
				"extra_title_contents_left" => (
					$output -> help_button("", True)
					),
				'validation_func' => "form_add_multiple_small_images_validate",
				'complete_func' => "form_add_multiple_small_images_complete",
				"data_image_settings" => $image_settings
				),

			"#path" => array(
				"name" => $lang['add_many_image_path_'.$image_settings['type']],
				"description" => $lang['add_many_image_path_desc'],
				"type" => "text",
				"required" => True
				),
			"#cat_id" => array(
				"name" => $lang['add_one_image_cat_'.$image_settings['type']],
				"type" => "dropdown",
				"options" => $form -> form_state['#cat_id']['options'],
				"value" => $category_id,
				"required" => True
				),
			"#submit" => array(
				"value" => $lang['add_many_image_submit_'.$image_settings['type']],
				"type" => "submit"
				)
			)
		);

	$output -> add($multiple_form -> render());

}


/**
 * Edit a single image
 */
function page_edit_small_images($image_settings, $category_id, $image_id)
{

	global $lang, $output;

	// Select the cat
	$category_info = small_images_get_category_by_id($category_id, $image_settings['type']);

	if($category_info === False)
	{
		$output -> set_error_message($lang['invalid_image_cat_id']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// Select the image
	$image_info = small_images_get_image_by_id($image_id, $image_settings['type']);

	if($image_info === False)
	{
		$output -> set_error_message($lang['invalid_image_image_id']);
		page_view_small_images($image_settings, $category_id);
		return;
	}

	// Display the page
	$output -> page_title = $output -> replace_number_tags(
		$lang['edit_image_title_'.$image_settings['type']],
		$image_info['name']
		);

	$output -> add_breadcrumb($lang['breadcrumb_'.$image_settings['type']."_view"], l("admin/".$image_settings['type']."/view/".$category_id."/"));

	$output -> add_breadcrumb(
		$lang['breadcrumb_'.$image_settings['type'].'_edit_image'],
		l("admin/".$image_settings['type']."/edit/".$category_id."/".$image_id."/edit/")
		);

	// Put the form up
	$form = new form(
		form_add_edit_small_images("edit", $category_id, $image_info, $image_settings)
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * This is the form definition for adding/editing small images
 *
 * @param string $type The type of request. "add" or "edit".
 * @param array $page_matches 
 * @param array $initial_data Array of data directly from the database that will
 *   be used to populate the fields initially.
 * @param array $image_settings Settings for the current image page type
 */
function form_add_edit_small_images($type, $page_matches, $initial_data = NULL, $image_settings = array())
{

	global $lang, $output, $template_admin, $cache;

	// Get categories for the dropdown
	$dropdown_cats_values = array();

	$cats = small_images_get_categories($image_settings['type']);

	foreach($cats as $cat_id => $cat_info)
		$dropdown_cats_values[$cat_id] = $cat_info['name'];

	// Form definition
	$form_data = array(
		"meta" => array(
			"title" => $output -> page_title,
			"name" => "small_images_".$image_settings['type']."_".$type,
			"enctype" => "multipart/form-data",
			"extra_title_contents_left" => (
				$output -> help_button("", True).
				$template_admin -> form_header_icon($image_settings['type'])
				),
			"initial_data" => $initial_data,
			'validation_func' => "form_add_edit_small_images_validate",
			"data_image_settings" => $image_settings
			),

		"#name" => array(
			"name" => $lang['edit_image_name_'.$image_settings['type']],
			"description" => $lang['edit_image_name_desc_'.$image_settings['type']],
			"type" => "text",
			"required" => True
			)
		);

	// Emoticons require a code to represent  it by, everything else has a minimum
	// post setting.
	if($image_settings['type'] == "emoticons")
		$form_data += array(
			"#emoticon_code" => array(
				"name" => $lang['edit_image_code_emoticons'],
				"description" => $lang['edit_image_code_desc_emoticons'],
				"type" => "text",
				"required" => True
				)
			);
	else
		$form_data += array(
			"#min_posts" => array(
				"name" => $lang['edit_image_posts_'.$image_settings['type']],
				"description" => $lang['edit_image_posts_desc_'.$image_settings['type']],
				"type" => "int"
				)
			);

	$form_data += array(
		"#cat_id" => array(
			"name" => $lang['edit_image_cat_'.$image_settings['type']],
			"type" => "dropdown",
			"options" => $dropdown_cats_values,
			"value" => $page_matches,
			"required" => True
			),
		"#filename" => array(
			"name" => $lang['edit_image_filename_'.$image_settings['type']],
			"description" => $lang['edit_image_filename_desc'],
			"type" => "text",
			)
		);

	if($type == "edit")
		$form_data += array(
			"msg" => array(
				"title" => $lang['edit_image_replace_image'],
				"type" => "message"
				)
			);

	$form_data += array(
		"#upload" => array(
			"name" => $lang[($type == "add" ? 'add_one_image_upload' : 'edit_image_upload')],
			"description" => $output -> replace_number_tags($lang['add_one_image_upload_desc'], $cache -> cache['config'][$image_settings['config_path']]),
			"type" => "upload",
			"upload" => array(
				"destination_path" => $cache -> cache['config'][$image_settings['config_path']],
				"is_image" => True,
				"overwrite_existing" => True
				)
			),
		"#submit" => array(
			"type" => "submit"
			)
		);

	// Make alterations to the form based on the mode we're in before sending back
	if($type == "add")
	{
		$form_data['#filename']['name'] = $lang['add_one_image_filename_'.$image_settings['type']];
		$form_data['meta']['description'] = $lang['add_one_image_message_'.$image_settings['type']];
		$form_data['meta']['complete_func'] = "form_add_small_images_complete";
		$form_data['#submit']['value'] = $lang['add_one_image_submit_'.$image_settings['type']];
	}
	elseif($type == "edit")
	{
		$form_data['meta']['description'] = $lang['edit_image_message_'.$image_settings['type']];
		$form_data['meta']['complete_func'] = "form_edit_small_images_complete";
		$form_data['#submit']['value'] = $lang['edit_image_submit'];
	}

	return $form_data;

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for editing and adding an image
 *
 * @param object $form
 */
function form_add_edit_small_images_validate($form)
{

	global $lang, $output;

	// We want to make sure we don't fudge with images if we haven't filled
	// in a required field or something.
	if(isset($form -> form_state['meta']['show_error']))
		return;

	// If we're not uploading then we must be selecting by filename
	if(!$form -> form_state['#upload']['value'])
	{

		// First check the path actually exists
		if(!file_exists(ROOT.$form -> form_state['#filename']['value']))
			$form -> set_error(
				"filename", 
				$output -> replace_number_tags($lang['upload_image_error_file_not_found'], array($form -> form_state['#filename']['value']))
				);
		// Then check it's actually an image
		elseif(!getimagesize(ROOT.$form -> form_state['#filename']['value']))
			$form -> set_error("filename", $lang['upload_image_error_not_image']);

	}

	// Ensure that emoticons don't have the same codes
	if($form -> form_state['meta']['data_image_settings']['type'] == "emoticons")
	{

		$check_code = small_images_get_emoticon_by_code(
			$form -> form_state['#emoticon_code']['value'],
			$form -> form_state['meta']['initial_data']['id']
			);

		if($check_code !== False)
			$form -> set_error("emoticon_code", $lang['add_one_image_error_emoticon_code_exists']);

	}

	// Ensure the category exists if we're adding
	if($form -> form_state['meta']['name'] == "small_images_".$form -> form_state['meta']['data_image_settings']."_add")
	{

		if(small_images_get_category_by_id($form -> form_state['#cat_id']['value'], $form -> form_state['meta']['data_image_settings']['type']) === False)
		{
			$form -> set_error("cat_id", $lang['edit_image_new_cat_not_found']);
			return False;
		}

	}
	// If we're editing and moving image to another cat then make sure it exists
	elseif($form -> form_state['meta']['name'] == "small_images_".$form -> form_state['meta']['data_image_settings']."_edit")
	{

		if(small_images_get_category_by_id($form -> form_state['#cat_id']['value'], $form -> form_state['meta']['data_image_settings']['type']) === False)
		{
			$form -> set_error("cat_id", $lang['edit_image_new_cat_not_found']);
			return False;
		}

	}

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding an image
 *
 * @param object $form
 */
function form_add_small_images_complete($form)
{

	global $lang, $output;

	// Write the image to the filesystem if we're uploading
	if($form -> form_state['#upload']['value'])
	{

		// Saves the upload and returns true or returns an error message on failure
		$upload_return = $form -> form_state['#upload']['upload']['class'] -> complete_upload_from_form();

		if($upload_return !== True)
		{
			$output -> set_error_message($upload_return);
			return False;
		}

		// Save the value for putting into the db
		$form -> form_state['#filename']['value'] = $form -> form_state['#upload']['upload']['class'] -> final_file_path;

	}

	// Add the name image
	$add_result = small_images_add_image(
		array(
			"name" => $form -> form_state['#name']['value'],
			"cat_id" => $form -> form_state['#cat_id']['value'],
			"filename" => $form -> form_state['#filename']['value'],
			"emoticon_code" => (isset($form -> form_state['#emoticon_code']['value']) ? $form -> form_state['#emoticon_code']['value'] : NULL),
			"min_posts" => (isset($form -> form_state['#min_posts']['value']) ? $form -> form_state['#min_posts']['value'] : 0),
			"type" => $form -> form_state['meta']['data_image_settings']['type']
			),
		$form -> form_state['meta']['data_image_settings']['type']
		);

	if($add_result === False)
		return False;

	// Log
	log_admin_action(
		"small_images",
		"add_image",
		"Added small image: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/view/".$form -> form_state['#cat_id']['value']."/"),
		$lang['add_one_image_created_sucessfully_'.$form -> form_state['meta']['data_image_settings']['type']]
		);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing an image
 *
 * @param object $form
 */
function form_edit_small_images_complete($form)
{

	global $lang, $output;

	// Write the image to the filesystem if we're uploading
	if($form -> form_state['#upload']['value'])
	{

		// Saves the upload and returns true or returns an error message on failure
		$upload_return = $form -> form_state['#upload']['upload']['class'] -> complete_upload_from_form();

		if($upload_return !== True)
		{
			$output -> set_error_message($upload_return);
			return False;
		}

		// Save the value for putting into the db
		$form -> form_state['#filename']['value'] = $form -> form_state['#upload']['upload']['class'] -> final_file_path;

	}

	// Edit the image info 
	$update = small_images_edit_image(
		$form -> form_state['meta']['initial_data']['id'],
		array(
			"name" => $form -> form_state['#name']['value'],
			"cat_id" => $form -> form_state['#cat_id']['value'],
			"filename" => $form -> form_state['#filename']['value'],
			"emoticon_code" => (isset($form -> form_state['#emoticon_code']['value']) ? $form -> form_state['#emoticon_code']['value'] : NULL),
			"min_posts" => (isset($form -> form_state['#min_posts']['value']) ? $form -> form_state['#min_posts']['value'] : 0)
			),
		$form -> form_state['meta']['data_image_settings']['type'],
		$form -> form_state['meta']['initial_data']['cat_id']
		);

	if($update === False)
		return False;

	// Log
	log_admin_action(
		"small_images",
		"edit_image",
		"Edited image: ".$form -> form_state['#name']['value']
		);

	// Redirect...
	$output -> redirect(
		l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/view/".$form -> form_state['#cat_id']['value']."/"),
		$lang['edit_image_edited_sucessfully_'.$form -> form_state['meta']['data_image_settings']['type']]
		);

}



/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for adding multiple images
 *
 * @param object $form
 */
function form_add_multiple_small_images_validate($form)
{

	global $lang, $output;

	if(isset($form -> form_state['meta']['show_error']))
		return;

	if(($ret = small_images_add_multiple_images_check_routine($form -> form_state['meta'], $form -> form_state['#cat_id']['value'], $form -> form_state['#path']['value'])) !== True)
		$form -> set_error(NULL, $ret);

}


/**
 * Used by both steps of the add multiple procedures
 */
function small_images_add_multiple_images_check_routine(&$form_state_meta, $cat_id, $path)
{

	global $lang;

	// Check category exists
	if(small_images_get_category_by_id($cat_id, $form_state_meta['data_image_settings']['type']) === False)
		return $lang['edit_image_new_cat_not_found'];

	// Make sure we have / on the end so we can check it as a real dir
	$form_state_meta['data_real_path'] = (_substr($path, -1, 1) == "/" ? $path : $path."/");

	// Check the path exists and is a dir
	if(!file_exists(ROOT.$form_state_meta['data_real_path']) || !is_dir(ROOT.$form_state_meta['data_real_path']))
		return $lang['add_many_images_invalid_path'];

	// Get all the current images by their filename - to ensure that we don't have any duplicates
	$all_images_raw = small_images_get_images_by_type($form_state_meta['data_image_settings']['type'], "`filename`");

	// Make it a bit more searchable
	$all_images = array();

	foreach($all_images_raw as $image)
		$all_images[] = $image['filename'];

	// Read the directory we've got for getting a list of viable images
	if(!$dirh = opendir(ROOT.$form_state_meta['data_real_path']))
		return $lang['add_many_images_invalid_path'];

	// Get a list of only images from this directory
	$form_state_meta['data_image_filename_list'] = array();

	while(False !== ($file_checking = readdir($dirh)))
	{

		// If you're wondering about the filesize check it's because that it seems that
		// imagesize() bawlks over files less than 12 bytes in length regardless of error
		// supression (i'm serious, all it says is "Read error!". Stellar.)
		// i found a bug report that was closed in 2007 as fixed, it's now 2009 I'M SERIOUS
		// Good job on a class A regression there guys, if you even fixed it in the first place.
		if(
			$file_checking == "." || $file_checking == ".." ||
			is_dir(ROOT.$form_state_meta['data_real_path'].$file_checking) ||
			!is_readable(ROOT.$form_state_meta['data_real_path'].$file_checking) ||
			filesize(ROOT.$form_state_meta['data_real_path'].$file_checking) < 12
			)
			continue;

		if(!@getimagesize(ROOT.$form_state_meta['data_real_path'].$file_checking))
			continue;

		if(!in_array($form_state_meta['data_real_path'].$file_checking, $all_images))
			$form_state_meta['data_image_filename_list'][] = array(
				"filename" => $file_checking
				);

	}
	
	// If we didn't manage to get any images
	if(!count($form_state_meta['data_image_filename_list']))
		return $lang['add_many_images_empty_dir'];

	return True;

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding multiple images
 *
 * @param object $form
 */
function form_add_multiple_small_images_complete($form)
{

	global $lang, $output;

	// Instant redirect to the correct page for these shenanegans
	$output -> redirect(
		l(
			"admin/".$form -> form_state['meta']['data_image_settings']['type']."/".
			$form -> form_state['#cat_id']['value']."/add_multiple/?path=".
			urlencode($form -> form_state['meta']['data_real_path'])
			),
		"",
		True
		);

}


/**
 * Page for adding multiple images, this does the cool table/form thing with checkboxes
 */
function page_add_multiple_small_images($image_settings, $category_id)
{

	global $lang, $output, $template_admin;

	// Check get param for path
	$path = (isset($_GET['path']) ? urldecode(trim($_GET['path'])) : "");

	if(!$path)
	{
		$output -> set_error_message($lang['add_many_images_invalid_path']);
		return page_add_small_images($image_settings, $category_id);
	}

	$form_state_meta = array(
		'data_image_settings' => $image_settings
		);

	if(($ret = small_images_add_multiple_images_check_routine($form_state_meta, $category_id, $path)) !== True)
	{
		$output -> set_error_message($ret);
		return page_add_small_images($image_settings, $category_id);
	}

	// oh crumbs
	$output -> page_title = $lang['breadcrumb_'.$image_settings['type'].'_add_many'];

	$output -> add_breadcrumb(
		$lang['breadcrumb_'.$image_settings['type'].'_add'],
		l("admin/".$image_settings['type']."/".$category_id."/add/")
		);

	$output -> add_breadcrumb(
		$lang['breadcrumb_'.$image_settings['type'].'_add_many'],
		l("admin/".$image_settings['type']."/".$category_id."/add_multiple?path=".$_GET['path'])
		);

	// THIS IS GREAT
	// We make a result table for the filename list and then we add it to a form as a field
	// MAGICALLY the result table gets checkboxes that we can check as form values as normal.
	// is nifty, no?
	$form = new form(
		array(
			"meta" => array(
				"title" => $output -> page_title,
				"name" => "small_images_".$image_settings['type']."_add_multiple_step2",
				"description" => $lang[$image_settings['type'].'_add_many_description'],
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon($image_settings['type'])
					),
				'complete_func' => "form_add_multiple_small_images_step2_complete",
				"data_image_settings" => $image_settings
				) + $form_state_meta,
			)
		);

	// Define the table
	$results_table = array(
		"no_results_message" => $lang['add_many_images_empty_dir'],

		"data" => $form_state_meta['data_image_filename_list'],
		"items_per_page" => count($form_state_meta['data_image_filename_list']),
		"default_sort" => "name",

		"columns" => array(
			"image" => array(
				"name" => $lang['image_add_multiple_image_image'],
				"content_callback" => "table_add_multiple_images_image",
				'content_callback_parameters' => array($path),
				"align" => "center"
				),
			"filename" => array(
				"name" => $lang['image_add_multiple_image_filename'],
				"db_column" => "filename"
				),
			"name" => array(
				"name" => $lang['image_add_multiple_image_name'],
				"content_callback" => "table_add_multiple_images_name",
				'content_callback_parameters' => array($form),
				),
			"emoticon_code" => (
				$image_settings['type'] == "emoticons" ? 
				array(
					"name" => $lang['image_add_multiple_image_emoticon_code'],
					"content_callback" => "table_add_multiple_images_emoticon_code",
					'content_callback_parameters' => array($form),
					)
				: NULL
				),
			)
		);

	$form -> form_state += array(
		"#images" => array(
			"type" => "results_table",
			"results_table_settings" => $results_table,
			"results_table_checkboxes" => True,
			"results_table_value_key" => "filename"
			),
		"#submit" => array(
			"value" => $lang['add_many_add_submit'],
			"type" => "submit"
			)
		);


	$output -> add($form -> render());
	
}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the adding multilpe images table. (Image)
 *
 * @param object $row_data
 */
function table_add_multiple_images_image($row_data, $path)
{

	global $cache;

	$file = $cache -> cache['config']['board_url']."/".$path.$row_data['filename'];
	return "<img src=\"".$file."\" alt=\"".$file."\" />";

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the adding multilpe images table. (Names)
 *
 * @param object $row_data
 */
function table_add_multiple_images_name($row_data, $form)
{

	global $template_global_forms;

	return $template_global_forms -> form_field_text(
		"image_name[".$row_data['filename']."]",
		array(
			"value" => strrev(_substr(strchr(strrev($row_data['filename']), "."), 1 )),
			"size" => 30
			),
		$form -> form_state
		);

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the adding multilpe images table. (emote code)
 *
 * @param object $row_data
 */
function table_add_multiple_images_emoticon_code($row_data, $form)
{

	global $template_global_forms;

	return $template_global_forms -> form_field_text(
		"emoticon_code[".$row_data['filename']."]",
		array(
			"value" => ":".strrev(_substr(strchr(strrev($row_data['filename']), "."), 1 )).":",
			"size" => 15
			),
		$form -> form_state
		);

}




/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for adding multiple images
 *
 * @param object $form
 */
function form_add_multiple_small_images_step2_complete($form)
{

	global $lang, $output, $page_matches;

	if(!count($form -> form_state['#images']['value']))
	{
		$form -> set_error(NULL, $lang['add_many_error_none_selected']);
		return;
	}

	// Get together all of our new images
	$new_images = array();
	foreach($form -> form_state['#images']['value'] as $filename)
	{

		// Ensure we have a name
		if(!isset($_POST['image_name'][$filename]) || !trim($_POST['image_name'][$filename]))
			continue;

		// Check the path actually exists and it is an image
		if(!file_exists(ROOT.$_GET['path'].$filename) || !getimagesize(ROOT.$_GET['path'].$filename))
			continue;

		// Get together all the info that will go into the DB
		$i = array(
			"filename" => $_GET['path'].$filename,
			"name" => $_POST['image_name'][$filename],
			"cat_id" => $page_matches['category_id'],
			"type" => $form -> form_state['meta']['data_image_settings']['type'],
			);

		// Emoticon only checks
		if($form -> form_state['meta']['data_image_settings']['type'] == "emoticons")
		{

			if(!isset($_POST['emoticon_code'][$filename]) || !trim($_POST['emoticon_code'][$filename]))
				continue;

			// Ensure that emoticons don't have the same codes
			$check_code = small_images_get_emoticon_by_code($_POST['emoticon_code'][$filename]);
			
			if($check_code !== False)
				continue;

			$i['emoticon_code'] = $_POST['emoticon_code'][$filename];

		}

		// everything went better than expected
		$new_images[] = $i;

	}

	// Make sure we have enough valid images :)
	if(!count($new_images))
	{
		$form -> set_error(NULL, $lang['add_many_error_none_selected']);
		return;
	}

	// Go through them all and add them! Hurrah.
	$total_added = 0;

	foreach($new_images as $image)
	{
		// The ternary is for skipping cache rebuilds until the last image
		// is added - cache rebuilding is a hefty procedure.
		$add_result = small_images_add_image($image, $image['type'], ($total_added == count($new_images)-1 ? False : True));

		if($add_result !== False)
			$total_added++;
		
	}

	log_admin_action("small_images", "add_multiple", "Added ".$total_added." small images");

	// We've done here
	$form -> form_state['meta']['redirect'] = array(
		'url' => l(
			"admin/".$form -> form_state['meta']['data_image_settings']['type']."/view/".
			$page_matches['category_id']."/"
			),
		'message' => $output -> replace_number_tags($lang['add_many_done_message'], $total_added)
		);

}


/**
 * Confirmation page to remove a single small image.
 *
 * @var array $image_settings
 * @var int $image_id ID of the image we're deleting.
 */
function page_delete_small_image($image_settings, $image_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the image
	$image_info = small_images_get_image_by_id($image_id, $image_settings['type']);

	if($image_info === False)
	{
		$output -> set_error_message($lang['invalid_image_image_id']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// Show the confirmation page
	$output -> page_title = $lang['delete_small_images_title_'.$image_settings['type']];

	$output -> add_breadcrumb($lang['breadcrumb_'.$image_settings['type']."_view"], l("admin/".$image_settings['type']."/view/".$image_info['cat_id']."/"));

	$output -> add_breadcrumb(
		$output -> page_title,
		l("admin/".$image_settings['type']."/".$image_info['cat_id']."/".$image_info['id']."/delete/")
		);

	$output -> add(
		$output -> confirmation_page(
			array(
				"title" => $output -> page_title,
				"extra_title_contents_left" => $template_admin -> form_header_icon($image_settings['type']),
				"description" => $output -> replace_number_tags(
					$lang['delete_small_images_message_'.$image_settings['type']],
					sanitise_user_input($image_info['name'])
					),
				"callback" => "small_images_delete_small_image_complete",
				"arguments" => array($image_info, $image_settings),
				"confirm_redirect" => l("admin/".$image_settings['type']."/view/".$image_info['cat_id']."/"),
				"cancel_redirect" => l("admin/".$image_settings['type']."/view/".$image_info['cat_id']."/")
				)
			)
		);

}


/**
 * CONFIRMATION CALLBACK
 * ---------------------
 * Completion funciton for deleting a small image
 *
 * @param array $image_info Full info about the image from the database.
 * @param array $image_settings Settings for the current mode
 */
function small_images_delete_small_image_complete($image_info, $image_settings)
{

	global $output, $lang;

	// Delete and check the response
	$return = small_images_delete_small_image($image_info, $image_settings['type']);

	if($return === True)
	{

        // Log it
        log_admin_action("small_images", "delete_image", "Deleted small image: ".$image_info['name']);
		return True;

	}
	else
		return False;

}


/**
 * Page for moving multiple images
 */
function page_move_multiple_small_images($image_settings, $category_id)
{

	global $lang, $output, $template_admin;

	// oh crumbs
	$output -> page_title = $lang['breadcrumb_'.$image_settings['type'].'_move_multiple'];

	$output -> add_breadcrumb(
		$lang['breadcrumb_'.$image_settings['type'].'_move_multiple'],
		l("admin/".$image_settings['type']."/move_multiple/".$category_id."/")
		);


	// Get alternate categories for the dropdown
	$raw_category_data = small_images_get_categories($image_settings['type']);
	unset($raw_category_data[$category_id]);

	$category_data = array();

	foreach($raw_category_data as $id => $cat_info)
		$category_data[$id] = $cat_info['name'];


	// Define form and table and liiiink
	$form = new form(
		array(
			"meta" => array(
				"title" => $output -> page_title,
				"name" => "small_images_".$image_settings['type']."_move_multiple",
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon($image_settings['type'])
					),
				'validation_func' => "form_move_multiple_small_images_validation",
				'complete_func' => "form_move_multiple_small_images_complete",
				"data_image_settings" => $image_settings
				),
			)
		);

	// Define the table
	$image_data = small_images_get_image_by_category($category_id, $image_settings['type']);

	$results_table = array(
		"no_results_message" => $lang['image_view_no_images_'.$image_settings['type']],

		"data" => $image_data,
		"items_per_page" => count($image_data),
		"default_sort" => "name",

		"columns" => array(
			"image" => array(
				"name" => $lang['image_move_multiple_images_image'],
				"content_callback" => "table_move_multiple_images_image",
				"align" => "center"
				),
			"name" => array(
				"name" => $lang['image_move_multiple_image_name'],
				"db_column" => "name"
				)
			)
		);

	$form -> form_state += array(
		"#images" => array(
			"type" => "results_table",
			"results_table_settings" => $results_table,
			"results_table_checkboxes" => True,
			"results_table_value_key" => "id"
			),
		"#new_category" => array(
			"name" => $lang['move_multiple_dropdown_text'],
			"type" => "dropdown",
			"options" => $category_data,
			"required" => True
			),
		"#submit" => array(
			"value" => $lang['images_main_action_move_multiple'],
			"type" => "submit"
			)
		);


	$output -> add($form -> render());

}


/**
 * RESULTS TABLE FUNCTION
 * ----------------------
 * Content callback for the moving multilpe images table. (Image)
 *
 * @param object $row_data
 */
function table_move_multiple_images_image($row_data)
{

	global $cache;

	return "<img src=\"".$cache -> cache['config']['board_url']."/".$row_data['filename']."\" alt=\"".$row_data['name']."\" />";

}


/**
 * FORM FUNCTION
 * --------------
 * Validation funciton for moving multiple images
 *
 * @param object $form
 */
function form_move_multiple_small_images_validation($form)
{

	global $lang, $output, $page_matches;

	// Make sure we select some
	if(!count($form -> form_state['#images']['value']))
		$form -> set_error(NULL, $lang['move_multiple_error_none_selected']);

	// Make sure the category exists
	if(small_images_get_category_by_id($form -> form_state['#new_category']['value'], $form -> form_state['meta']['data_image_settings']['type']) === False)
		$form -> set_error('new_category', $lang['invalid_image_cat_id']);

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for moving multiple images
 *
 * @param object $form
 */
function form_move_multiple_small_images_complete($form)
{

	global $lang, $output, $page_matches;

	// Move all of our new images
	$count = 0;

	foreach($form -> form_state['#images']['value'] as $image_id)
	{

		$move = small_images_edit_image(
			$image_id, 
			array("cat_id" => $form -> form_state['#new_category']['value']),
			$form -> form_state['meta']['data_image_settings']['type'],
			$page_matches['category_id']);

		if($move === True)
			$count++;

	}

	log_admin_action("small_images", "move_multiple", "Moved ".$count." small images.");

	// Redirect
	$form -> form_state['meta']['redirect'] = array(
		'url' => l(
			"admin/".$form -> form_state['meta']['data_image_settings']['type']."/view/".
			$form -> form_state['#new_category']['value']."/"
			),
		'message' => $output -> replace_number_tags($lang['move_multiple_done_message'], $count)
		);
	
}


/**
 * Page for editing an existing categories user group permissions.
 */
function page_edit_permissions_small_images_category($image_settings, $category_id)
{

	global $output, $lang, $db, $template_admin;

	// Select the category
	$category_info = small_images_get_category_by_id($category_id, $image_settings['type']);

	if($category_info === False)
	{
		$output -> set_error_message($lang['invalid_image_cat_id']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// No permissions for emoticons
	if($category_info['type'] == "emoticons")
	{
		$output -> set_error_message($lang['cat_perms_emoticons_no']);
		page_view_small_images_categories($image_settings);
		return;
	}

	// Get current permission values
	$current_denied_user_groups = small_images_get_category_permissions($category_id, $image_settings['type']);

	// Get all user groups
	include ROOT."admin/common/funcs/user_groups.funcs.php";
	$groups = user_groups_get_groups();

	// Show the page
	$output -> page_title = $output -> replace_number_tags($lang['cat_perms_title_'.$image_settings['type']], $category_info['name']);
	$output -> add_breadcrumb(
		$lang['breadcrumb_category_perms'],
		l("admin/".$image_settings['type']."/permissions/".$category_id."/")
		);

	// Put the form up
	$form = new form(
		array(
			"meta" => array(
				"name" => "small_images_category_".$image_settings['type']."_edit_permissions",
				"title" => $output -> page_title,
				"description" => $lang['cat_perms_message_'.$image_settings['type']],
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon($image_settings['type'])
					),
				'complete_func' => "form_edit_permissions_small_images_category_complete",
				"data_category_info" => $category_info,
				"data_image_settings" => $image_settings,
				"data_user_groups" => $groups
				),
			)
		);

	// Put fields in for each user group
	foreach($groups as $group_id => $group_info)
		$form -> form_state['#group_'.$group_id] = array(
			'type' => 'yesno',
			'name' => $group_info['name'],
			'value' => (in_array($group_id, $current_denied_user_groups) ? "0" : "1")
			);

	// Plop submit button on the end
	$form -> form_state['#submit'] = array(
		'type' => "submit",
		'value' => $lang['cat_perms_submit']
		);

	$output -> add($form -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Completion funciton for editing a categories permissions
 *
 * @param object $form
 */
function form_edit_permissions_small_images_category_complete($form)
{

	global $lang, $output, $page_matches;

	// Gather up all the groups that are now denied
	$denied_groups = array();

	foreach($form -> form_state['meta']['data_user_groups'] as $group_id => $junk)
		if(isset($form -> form_state['#group_'.$group_id]) && !$form -> form_state['#group_'.$group_id]['value'])
			$denied_groups[] = $group_id;

	// Save the new permissions
	$update = small_images_edit_category_permissions($form -> form_state['meta']['data_category_info']['id'], $denied_groups);

	if($update !== True)
		return;

	log_admin_action("small_images", "permissions", "Edited category permissions (".$form -> form_state['meta']['data_image_settings']['type']."): ".$form -> form_state['meta']['data_category_info']['name']);

	// Redirect
	$form -> form_state['meta']['redirect'] = array(
		'url' => l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/"),
		'message' => $lang['cat_perms_successful']
		);

}


/**
 * Backup or import small images
 */
function page_backup_small_images($image_settings)
{

	global $output, $lang, $template_admin, $cache;

	$output -> page_title = $lang['breadcrumb_'.$image_settings['type'].'_importexport'];
	$output -> add_breadcrumb($output -> page_title, l("admin/".$image_settings['type']."/backup/"));
	
	// Export form
	$categories = array(-1 => $lang['ie_all_cats_dropdown']);
	$raw_category_data = small_images_get_categories($image_settings['type']);

	foreach($raw_category_data as $cat_id => $cat_info)
		$categories[$cat_id] = $cat_info['name'];

	$form = new form(
		array(
			"meta" => array(
				"name" => "export_".$image_settings['type'],
				"title" => $lang['ie_export_title_'.$image_settings['type']],
				"description" => $lang['ie_export_message_'.$image_settings['type']],
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon($image_settings['type'])
					),
				"validation_func" => "form_small_images_export_validate",
				"complete_func" => "form_small_images_export_complete",
				"data_image_settings" => $image_settings
				),

			"#filename" => array(
				"name" => $lang['ie_export_filename_'.$image_settings['type']],
				"description" => $lang['ie_export_filename_message_'.$image_settings['type']],
				"type" => "text",
				"required" => True,
				"value" => $image_settings['export_filename']
				),
			"#category_id" => array(
				"name" => $lang['ie_export_which_cat_'.$image_settings['type']],
				"description" => $lang['ie_export_which_cat_message_'.$image_settings['type']],
				"type" => "dropdown",
				"required" => True,
				"options" => $categories
				),

			"#submit" => array(
				"type" => "submit",
				"value" => $lang['ie_export_submit_'.$image_settings['type']]
				)
			)
		);

	$output -> add($form -> render());


	// import form
	$form2 = new form(
		array(
			"meta" => array(
				"name" => "import_".$image_settings['type'],
				"title" => $lang['ie_import_title_'.$image_settings['type']],
				"description" => $output -> replace_number_tags($lang['ie_import_message_'.$image_settings['type']], $cache -> cache['config'][$image_settings['config_path']]),
				"extra_title_contents_left" => $output -> help_button("", True),
				"validation_func" => "form_small_images_import_validate",
				"complete_func" => "form_small_images_import_complete",
				"data_image_settings" => $image_settings,
				"data_image_path" => $cache -> cache['config'][$image_settings['config_path']],
				"enctype" => "multipart/form-data"
				),

			"#file" => array(
				"name" => $lang['ie_import_upload'],
				"description" => $lang['ie_import_upload_message_'.$image_settings['type']],
				"type" => "upload",
				"upload" => array(
					"destination_path" => $cache -> cache['config'][$image_settings['config_path']],
					"is_image" => False,
					"overwrite_existing" => True
					)
				),
			"#filename" => array(
				"name" => $lang['ie_import_filename'],
				"description" => $lang['ie_import_filename_message_'.$image_settings['type']],
				"type" => "text",
				"value" => "upload/".$image_settings['export_filename']
				),
			"#overwrite_files" => array(
				"name" => $lang['ie_import_overwrite_files'],
				"description" => $lang['ie_import_overwrite_files_message_'.$image_settings['type']],
				"type" => "yesno",
				"value" => "1"
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $lang['ie_import_submit_'.$image_settings['type']]
				)
			)
		);

	$output -> add($form2 -> render());

}


/**
 * FORM FUNCTION
 * --------------
 * Exporting small images
 *
 * @param object $form
 */
function form_small_images_export_validate($form)
{

	global $db, $lang;

	$categories = array();

	if($form -> form_state['#category_id']['value'] > -1)
	{

		$cat = small_images_get_category_by_id($form -> form_state['#category_id']['value'], $form -> form_state['meta']['data_image_settings']['type']);

		if($cat === False)
			$form -> set_error("category_id", $lang['export_could_not_find_cats']);

		$categories[$cat['id']] = $cat;

	}
	else
	{

		$cats = small_images_get_categories($form -> form_state['meta']['data_image_settings']['type']);

		if(!count($cats))
			$form -> set_error("category_id", $lang['export_could_not_find_cats']);
		else
			$categories = $cats;

	}

	$form -> form_state['meta']['data_category_info'] = $categories;

}


/**
 * FORM FUNCTION
 * --------------
 * Exporting small images
 *
 * @param object $form
 */
function form_small_images_export_complete($form)
{

	global $db, $lang;

	$config_xml_text = small_images_get_exported_small_images(
		$form -> form_state['meta']['data_category_info'], 
		$form -> form_state['meta']['data_image_settings']['type'],
		$form -> form_state['meta']['data_image_settings']['xml_root']
		);
	output_file($config_xml_text, $form -> form_state['#filename']['value'], "text/xml");

}


/**
 * FORM FUNCTION
 * --------------
 * Importing small images
 *
 * @param object $form
 */
function form_small_images_import_validate($form)
{

	global $output, $lang;

	// Check if we've upload, or supplied a filename and pass the path onto the complete form.
	// Otherwise Error out.
	if(file_exists($_FILES['file']['tmp_name']))
		$form -> form_state['meta']['real_filename'] = $_FILES['file']['tmp_name'];
	elseif(file_exists(ROOT.$form -> form_state['#filename']['value']))
		$form -> form_state['meta']['real_filename'] = ROOT.$form -> form_state['#filename']['value'];
	else
		$form -> set_error("filename", $lang['xml_file_not_found']);

}


/**
 * FORM FUNCTION
 * --------------
 * Importing small images
 *
 * @param object $form
 */
function form_small_images_import_complete($form)
{

	global $output, $lang, $db, $template_global, $cache;

	$get_error = small_images_import_images_xml(
		file_get_contents($form -> form_state['meta']['real_filename']),
		$form -> form_state['meta']['data_image_settings']['type'],
		$form -> form_state['meta']['data_image_settings']['xml_root'],
		$form -> form_state['meta']['data_image_path'],
		$form -> form_state['#overwrite_files']['value']
		);

	// If we have version mismatch
	if((string)$get_error == "VERSION")
	{
		$form -> set_error("filename", $lang['xml_version_mismatch_'.$form -> form_state['meta']['data_image_settings']['type']]);
		return false;
	}

	// Log and redirect
	log_admin_action($form -> form_state['meta']['data_image_settings']['type'], "import", "Imported image set (".$form -> form_state['meta']['data_image_settings']['type'].") from ".$form -> form_state['meta']['real_filename']);

	$form -> form_state['meta']['redirect'] = array(
		"url" => l("admin/".$form -> form_state['meta']['data_image_settings']['type']."/backup/"),
		"message" => $lang['import_done_message_'.$form -> form_state['meta']['data_image_settings']['type']]
	);

}

?>
