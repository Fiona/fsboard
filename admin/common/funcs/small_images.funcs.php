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
 * Small image related administration functions
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


/**
 * Select all image categories for a specific type.
 *
 * @var string $type Type of images we want the cats for. 
 *   (avatars, emoticons, post_icons)
 *
 * @return array An array of arrays containing category data, keys are IDs.
 */
function small_images_get_categories($type)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "small_image_cat",
			"where" => "`type` = '".$db -> escape_string($type)."'"
			)
		);

	if(!$db -> num_rows())
		return array();

	$categories = array();

	while($cat = $db -> fetch_array())
		$categories[$cat['id']] = $cat;

	return $categories;

}


/**
 * Select a single category.
 *
 * @var int $category_id The ID number of the category we require.
 * @var string $type Type of image this category is for.
 *   (avatars, emoticons, post_icons)
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the category.
 */
function small_images_get_category_by_id($category_id, $type)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "small_image_cat",
			"where" => "`id` = ".(int)$category_id." AND `type` = '".$db -> escape_string($type)."'",
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}



/**
 * This will create an image category for us based on data given
 *
 * @var array $category_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|int False on failure or the ID of the new category.
 */
function small_images_add_category($category_data, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "small_image_cat",
			"data" => $category_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_image_cat_error']);
		return False;
	}

	$new_id = $db -> insert_id();

	// Update cache
	$cache -> update_cache("small_image_cats");

	return $new_id;

}


/**
 * This will update a image category for us based on data given
 *
 * @var int $category_id ID number of the category to update.
 * @var array $category_data Array of data to be saved. Keys are the column names.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function small_images_edit_category($category_id, $category_data, $skip_cache = False, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "small_image_cat",
			"data" => $category_data,
			"where" => "id = ".(int)$category_id
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['edit_image_cat_error']);
		return $lang['edit_image_cat_error'];
	}

	// Update cache
	if(!$skip_cache)
		$cache -> update_cache("small_image_cats");

	return True;

}


/**
 * This will delete an image category
 *
 * @var int $category_data This is the full data about the category previously
 *   nabbed from the database. Although the cache should be fine too.
 * @var bool $delete_images Do we want to delete the images in this cat too? If
 *   False then we must also supply the replacemnt category ID.
 * @var int $new_category_id ID of the category we want to move deleted images
 *   into if the previous param was False.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool False on failure.
 */
function small_images_delete_category(
	$category_data,
	$delete_images,
	$new_category_id = NULL,
	$suppress_errors = False
	)
{

	global $db, $lang, $output, $cache;

	// If we're outright deleting images
	if($delete_images)
	{

		// Remove images...
		save_undelete_data(
			"small_images",
			"Deleted images from category ".$category_data['name'],
			"`cat_id` = ".(int)$category_data['id']
			);
		$remove = $db -> basic_delete(
			array(
				"table" => "small_images",
				"where" => "`cat_id` = ".(int)$category_data['id']
				)
			);

		if(!$remove)
		{
			if(!$suppress_errors)
				$output -> set_error_message($lang['delete_cat_error_deleting_images']);
			return $lang['delete_cat_error_deleting_images'];
		}
		
		// Update users with this avatar
		if($category_data['type'] == "avatars")
			$db -> basic_update(
				array(
					"table" => "users",
					"data" => array(
						"avatar_type" => "no",
						"avatar_address" => "",
						"avatar_gallery_cat" => ""
						),
					"where" => "`avatar_gallery_cat` = ".(int)$category_data['id']." AND `avatar_type` = 'gallery'"
					)
				);
		
	}
	else
	{

		if($category_data['image_num'])
		{

			// We're moving images into another category then :)
			$update = $db -> basic_update(
				array(
					"table" => "small_images",
					"data" => array("cat_id" => $new_category_id),
					"where" => "`cat_id` = ".(int)$category_data['id']." AND `type`='".$category_data['type']."'",
					)
				);
			
			if(!$update)
			{
				if(!$suppress_errors)
					$output -> set_error_message($lang['delete_cat_error_moving_images']);
				return $lang['delete_cat_error_moving_images'];
			}
			
			// Update count numbers of the new category
			$db -> basic_update(
				array(
					"table" => "small_image_cat",
					"data" => array(
						"image_num" => "`image_num` + ".(int)$category_data['image_num'],
						),
					"where" => "`id` = ".(int)$new_category_id
					)
				);

		}

		// Update users with this avatar
		if($category_data['type'] == "avatars")
			$db -> basic_update(
				array(
					"table" => "users",
					"data" => array(
						"avatar_gallery_cat" => $new_category_id
						),
					"where" => "`avatar_gallery_cat` = ".(int)$category_data['id']." AND `avatar_type` = 'gallery'"
					)
				);

	}

	// Delete image category
	save_undelete_data(
		"small_image_cat",
		"Deleted small image category ".$category_data['id'],
		"`id` = ".(int)$category_data['id']
		);
	$delete = $db -> basic_delete(
		array(
			"table" => "small_image_cat",
			"where" => "`id` = ".(int)$category_data['id']." AND `type` = '".$category_data['type']."'"
			)
		);

	if(!$delete)
	{
			if(!$suppress_errors)
				$output -> set_error_message($lang['delete_cat_error_deleting_category']);
			return $lang['delete_cat_error_deleting_category'];
	}

	// Delete category permissions
	save_undelete_data(
		"small_image_cat_perms",
		"Deleted permissions for small image category ".$category_data['id'],
		"`cat_id` = ".(int)$category_data['id']
		);
	$delete = $db -> basic_delete(
		array(
			"table" => "small_image_cat_perms",
			"where" => "`cat_id` = ".(int)$category_data['id']
			)
		);

	if(!$delete)
	{
			if(!$suppress_errors)
				$output -> set_error_message($lang['delete_cat_error_deleting_perms']);
			return $lang['delete_cat_error_deleting_perms'];
	}

	// Update the various caches
	$cache -> update_cache("small_image_cats");
	$cache -> update_cache("small_image_cats_perms");
	$cache -> update_cache($category_data['type']);

	return True;

}


/**
 * Selects all user groups that are denied access to an image category.
 *
 * @var int $category_id The ID number of the category we're checking.
 *
 * @return array An array of user group IDs that represent groups that may
 *   not access a category.
 */
function small_images_get_category_permissions($category_id)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "small_image_cat_perms",
			"where" => "`cat_id` = ".(int)$category_id
			)
		);

	if(!$db -> num_rows())
		return array();

	$user_groups = array();
	while($g = $db -> fetch_array())
		$user_groups[] = $g['user_group_id'];

	return $user_groups;

}


/**
 * Update permissions for a given category. By defaulte all user groups have
 * access to all categories unless specified.
 *
 * @var int $category_id ID number of the category to update.
 * @var array $denied_groups An array of IDs of groups that are forbidden to use this category.
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string True on success or an error string.
 */
function small_images_edit_category_permissions($category_id, $denied_groups, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// First we kill existing permissions
	$q = $db -> basic_delete(
		array(
			"table" => "small_image_cat_perms",
			"where" => "cat_id = ".(int)$category_id
			)
		);

	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['cat_perms_error_deleting_perms']);
		return $lang['cat_perms_error_deleting_perms'];
	}

	// If any groups are to be denied access we save them
	if(count($denied_groups))
	{

		$inserts = array();

		foreach($denied_groups as $group_id)
			$inserts[] = array('cat_id' => $category_id, 'user_group_id' => $group_id);

		$q = $db -> basic_insert(
			array(
				"table" => "small_image_cat_perms",
				"data" => $inserts,
				'multiple_inserts' => True
				)
			);

		if(!$q)
		{
			if(!$suppress_errors)
				$output -> set_error_message($lang['cat_perms_insert_error']);
			return $lang['cat_perms_insert_error'];
		}

	}

	$cache -> update_cache("small_image_cats_perms");

	return True;

}


/**
 * Select all small images of a particular type.
 *
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 * @var string $what_query What's asked for as "what" for the query ran.
 *
 * @return bool|array Either false on failure or an array of arrays containing info
 *   about the images.
 */
function small_images_get_images_by_type($type, $what_query = "*")
{

	global $db;

	$db -> basic_select(
		array(
			"what" => $what_query,
			"table" => "small_images",
			"where" => "`type` = '".$db -> escape_string($type)."'"
			)
		);

	if(!$db -> num_rows())
		return False;

	$images = array();

	while($img = $db -> fetch_array())
		$images[] = $img;

	return $images;

}


/**
 * Select small image by the category they a member of.
 *
 * @var int $category_id The ID number of the category whose images we require.
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 *
 * @return bool|array Either false on failure or an array of arrays containing info
 *   about the images.
 */
function small_images_get_image_by_category($category_id, $type)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "small_images",
			"where" => "`cat_id` = ".(int)$category_id." AND `type` = '".$db -> escape_string($type)."'"
			)
		);

	if(!$db -> num_rows())
		return False;

	$images = array();

	while($img = $db -> fetch_array())
		$images[] = $img;

	return $images;

}


/**
 * Counts the number of images that a category has.
 *
 * @var int $category_id The ID number of the image category we're querying
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 *
 * @return bool|int Either false on failure the number of counted images.
 */
function small_images_count_category_images($category_id, $type)
{

	global $db;

	$db -> basic_select(
		array(
			"what" => "COUNT(`id`) as image_count",
			"table" => "small_images",
			"where" => "`cat_id` = ".(int)$category_id." AND `type` = '".$db -> escape_string($type)."'",
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> result();

}


/**
 * Select a single small image.
 *
 * @var int $image_id The ID number of the image we require.
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 *
 * @return bool|array Either false on failure or an array of containing info
 *   about the image.
 */
function small_images_get_image_by_id($image_id, $type)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "small_images",
			"where" => "`id` = ".(int)$image_id." AND `type` = '".$db -> escape_string($type)."'",
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_assoc();

}


/**
 * Select full info for an emoticon by it's code. This is only used to check existance
 * of conflicting emoticons at the moment.
 *
 * @var string $code Code to check
 * @var int $ignore_id ID of any emoticon that we wish to ignore when checking
 *
 * @return bool|array Either false on nothing existing or an array of containing info
 *   about the emoticon.
 */
function small_images_get_emoticon_by_code($code, $ignore_id = NULL)
{

	global $db;

	$db -> basic_select(
		array(
			"table" => "small_images",
			"where" => (
				"`type` = 'emoticons' AND `emoticon_code` = '".$db -> escape_string($code)."'".
				($ignore_id !== NULL ? " AND `id` != ".(int)$ignore_id : "")
				),
			"limit" => 1
			)
		);

	if(!$db -> num_rows())
		return False;

	return $db -> fetch_array();

}


/**
 * This will create a small image for us based on data given
 *
 * @var array $image_data Array of data to be saved. Keys are the column names.
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string True on success otherwise a string cantaining an error.
 */
function small_images_add_image($image_data, $type, $skip_cache = False, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Try inserting
	$q = $db -> basic_insert(
		array(
			"table" => "small_images",
			"data" => $image_data
			)
		);

	// Error if something went wrong
	if(!$q)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['add_one_image_error_inserting']);
		return $lang['add_one_image_error_inserting'];
	}

	// Update category counts
	$cat_image_count = (int)small_images_count_category_images($image_data['cat_id'], $type);
	$result = small_images_edit_category($image_data['cat_id'], array('image_num' => $cat_image_count), $skip_cache, True);

	if($result === False)
	{
		if(!$suppress_errors)
			$output -> set_error_message($result);
		return $result;
	}

	// Update cache
	if(!$skip_cache)
		$cache -> update_cache($type);

	return True;

}


/**
 * This will update a single small image for us based on data given
 *
 * @var int $image_id ID number of the image to update.
 * @var array $image_data Array of data to be saved. Keys are the column names.
 * @var int $initial_cat_id If we're moving categories then this should be passed as
 *   the category id that the image used to belong to, to ensure that the image count
 *   gets updated correctly.
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string True on success otherwise a string cantaining an error.
 */
function small_images_edit_image($image_id, $image_data, $type, $initial_cat_id = NULL, $suppress_errors = False)
{

	global $db, $output, $lang, $cache;

	// Update the table
	$update_result = $db -> basic_update(
		array(
			"table" => "small_images",
			"data" => $image_data,
			"where" => "id = ".(int)$image_id." AND `type` = '".$db -> escape_string($type)."'"
			)
		);

	if(!$update_result)
	{
		if(!$suppress_errors)
			$output -> set_error_message($lang['edit_image_update_error']);
		return $lang['edit_image_update_error'];
	}

	// Update category image counts if necessary
	if(isset($image_data['cat_id']) && $initial_cat_id !== NULL && $initial_cat_id != $image_data['cat_id'])
	{

		// We get the current count and update both categories
		$old_cat_count = (int)small_images_count_category_images($initial_cat_id, $type);
		$result = small_images_edit_category($initial_cat_id, array('image_num' => $old_cat_count), False, True);

		if($result === False)
		{
			if(!$suppress_errors)
				$output -> set_error_message($result);
			return $result;
		}

		$new_cat_count = (int)small_images_count_category_images($image_data['cat_id'], $type);
		$result = small_images_edit_category($image_data['cat_id'], array('image_num' => $new_cat_count), False, True);

		if($result === False)
		{
			if(!$suppress_errors)
				$output -> set_error_message($result);
			return $result;
		}

	}

	// Update cache
	$cache -> update_cache($type);

	return True;

}


/**
 * This will delete a single image
 *
 * @var array $image_info Full data for the image we're deleting from the database.
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 * @var bool $suppress_errors Normally this function will output error messages
 *   using set_error_message. If this is not wanted for whatever reason
 *   setting this to True will stop them appearing.
 *
 * @return bool|string True on success otherwise a string cantaining an error.
 */
function small_images_delete_small_image($image_info, $type, $suppress_errors = False)
{

	global $db, $lang, $output, $cache;

	// Delete image category
	save_undelete_data(
		"small_images",
		"Deleted small image ".$image_info['name'],
		$image_info
		);
	$delete = $db -> basic_delete(
		array(
			"table" => "small_images",
			"where" => "`id` = ".(int)$image_info['id']." AND `type` = '".$type."'"
			)
		);

	if(!$delete)
	{
			if(!$suppress_errors)
				$output -> set_error_message($lang['delete_cat_error_deleting_category']);
			return $lang['delete_cat_error_deleting_category'];
	}

	// Update category counts
	$cat_image_count = (int)small_images_count_category_images($image_info['cat_id'], $type);
	$result = small_images_edit_category($image_info['cat_id'], array('image_num' => $cat_image_count), False, True);

	if($result === False)
	{
		if(!$suppress_errors)
			$output -> set_error_message($result);
		return $result;
	}

	// Cachin'
	$cache -> update_cache($type);

	return True;

}


/**
 * Will return the XML data for small images. You need to pass in an array of 
 * group arrays 
 *
 * @var array $config_groups This is an array of groups. Usually supplied by a function
 *   like small_images_get_categories();
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 * @var string $xml_root The name of the root node for this type.
 *
 * @return string The contents of the XML file for importing.
 */
function small_images_get_exported_small_images($image_groups, $type, $xml_root)
{
	
	// Start XMLing
	$xml = new xml;
	$xml -> export_xml_start();
	$xml -> export_xml_root($xml_root);

	// Wander through all of the categories
	foreach($image_groups as $image_group_id => $image_group_info)
	{

		// XML group for each category
		$xml -> export_xml_start_group(
			"image_cat",
			array(
				"name" => $image_group_info['name'],
				"order" => $image_group_info['order']
				)
			);

		$images = small_images_get_image_by_category($image_group_id, $type);

		if(!count($images))
		{
			$xml -> export_xml_generate_group();
			continue;
		}

		// Each image has a node on the group
		foreach($images as $image_data)
		{

			if(!file_exists(ROOT.$image_data['filename']))
				continue;
				
			// We embed the data for the image in base64
			$h = fopen(ROOT.$image_data['filename'], "rb");
			
			$image_data['data'] = chunk_split(
				base64_encode(
					fread($h, filesize(ROOT.$image_data['filename']))
					)
				);
			
			fclose($h);

			// Finish creating the node
			$xml_entry = array(
				"name" => $image_data['name'],
				"order" => $image_data['order'],
				"filename" => _substr(strrchr($image_data['filename'], "/"), 1)
			);
			
			if($type == "emoticons")
				$xml_entry['emoticon_code'] = $image_data['emoticon_code'];
			else				
				$xml_entry['min_posts'] = $image_data['min_posts'];

			$xml -> export_xml_add_group_entry("image", $xml_entry, $image_data['data']);

		}

		$xml -> export_xml_generate_group();

	}

	// Finish XML'ing and chuck the file out
	$xml -> export_xml_generate();

	return $xml -> export_xml;

}



/**
 * Given the XML for a previously exported set of small images this will
 * create categores and image including files.
 *
 * @var string $xml_contents XML for the file we wish to get images from.
 * @var string $type Type of image this is.
 *   (avatars, emoticons, post_icons)
 * @var string $xml_root The root XML node. (avatars_file, emoticons_file or post_icons_file)
 * @var string $save_images_path The path we'll be saving images to.
 * @var bool $overwrite_images If we want to ovewrite any images that already exist when we import.
 * @var bool $ignore_version Normally this will check the FSBoard version that the XML 
 *   file was created with and error. We can skip this check if neccessary.
 *
 * @return bool|string True on succes and False on failure. If the method returns "VERSION" then there is
 *   a version mismatch.
 */
function small_images_import_images_xml($xml_contents, $image_type, $xml_root, $save_images_path, $overwrite_images, $ignore_version = false)
{

	global $db, $cache;

    // Start parser
	$xml = new xml;
	$xml -> import_root_name = $xml_root;
	$xml -> import_group_name = "image_cat";
	
	// Run parser and check version
	$parse_return = $xml -> import_xml($xml_contents, $ignore_version);

	if($parse_return == "VERSION" && !$ignore_version)
		return "VERSION";
	
	// Nothing?
	if(count($xml -> import_xml_values['image_cat']) < 1)
		return True;


	// Go through each category              
	foreach($xml -> import_xml_values['image_cat'] as $cat)
	{

		// Stick it in! So to speak.               
		$cat_insert = array(
			'name'	=> $cat['ATTRS']['name'],
			'type'	=> $image_type,
			'order'	=> $cat['ATTRS']['order']
			);

		$cat_id = small_images_add_category($cat_insert);

		if($cat_id === False)
			return False;

		// Create directory
		if(!is_dir(ROOT.$save_images_path))
			@mkdir(ROOT.$save_images_path, 0777);

		// No images in this group?
		if(count($cat['image']) < 1)
			continue;

		// Obviously we have images in this group
		foreach($cat['image'] as $id => $image)
		{

			// Are we overwriting?
			if(!$overwrite_images)
				// If it exists then forget it		
				if(file_exists(ROOT.$save_images_path.$image['ATTRS']['filename']))
					continue;

			// Delete the original
			if(file_exists(ROOT.$save_images_path.$image['ATTRS']['filename']))
				unlink(ROOT.$save_images_path.$image['ATTRS']['filename']);

			// Remove from DB
			$db -> basic_delete(
				array(
					"table" => "small_images",
					"where" => "`type` = '".$image_type."' and `filename` ='".$db -> escape_string($save_images_path.$image['ATTRS']['filename'])."'"
					)
				);

			// Get data first
			$image_data = preg_replace("/\r\n/", "", $image['CONTENT']);
			$image_data = base64_decode($image_data);

			// Write the image
			if($fh = fopen(ROOT.$save_images_path.$image['ATTRS']['filename'], "wb"))
			{
	
				if(fwrite($fh, $image_data))
				{

					fclose($fh);
					@chmod(ROOT.$save_images_path.$image['ATTRS']['filename'], 0777);

					// Inseeeeeert
					$image_insert = array(
						'name'		=> $image['ATTRS']['name'],
						'cat_id'	=> $cat_id,
						'type'		=> $image_type,
						'filename'	=> $save_images_path.$image['ATTRS']['filename'],
						'order'		=> $image['ATTRS']['order'] 
						);

					if($image_type == "emoticons")
						$image_insert['emoticon_code'] = $image['ATTRS']['emoticon_code'];
					else
						$image_insert['min_posts'] = $image['ATTRS']['min_posts'];

					$image = small_images_add_image($image_insert, $image_type, True, True);

					if($image !== True)
						return False;
		
				}
	
			}

		}
                                
	}

	$cache -> update_cache("small_image_cats");
	$cache -> update_cache($image_type);
        
	return true;
        
}

?>
