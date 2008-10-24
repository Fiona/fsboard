<?php
/* 
--------------------------------------------------------------------------
FSBoard - Free, open-source message board system.
Copyright (C) 2006 Fiona Burrows (fiona@fsboard.net)

FSBoard is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

FSBoard is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
--------------------------------------------------------------------------

*********************************
*       FSBoard                 *
*       by Fiona 2006           *
*********************************
*          FUNCTIONS            *
*       Admin Small Images      *
*       Started by Fiona        *
*       26th Aug 2006           *
*********************************
*       Last edit by Fiona      *
*       26th Aug 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Takes a bit of XML, generates the image categories defined
// and creates the images themselves, file and db wise
//***********************************************
function import_images_xml($xml_contents, $image_type, $save_images_path, $overwrite_images, $ignore_version = false)
{


	global $db;
	
	// Image type check
	switch($image_type)
	{
	
		case "avatars":
			$xml_root = "avatars_file";
			break;
	
		case "emoticons":
			$xml_root = "emoticons_file";
			break;
	
		case "post_icons":
			$xml_root = "post_icons_file";
	
	}

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
	return true;


	// **********************
	// Go through each category              
	// **********************
	foreach($xml -> import_xml_values['image_cat'] as $cat)
	{

		$image_cat_count = 0;
		
		// Stick it in! So to speak.               
		$cat_insert = array(
			'name'	=> $cat['ATTRS']['name'],
			'type'	=> $image_type,
			'order'	=> $cat['ATTRS']['order']
		);

		if(!$db -> basic_insert("small_image_cat", $cat_insert))
			return false;

		// Get the ID
		$cat_id = $db -> insert_id();

		// Create directory
		if(!is_dir(ROOT.$save_images_path))
			@mkdir(ROOT.$save_images_path, 0777);

		// Log it!
		if(!defined("INSTALL"))
			log_admin_action(CURRENT_MODE, "doimport", "Imported image set (".$image_type."): ".trim($cat_insert['name']));

		// No images in this group?
		if(count($cat['image']) < 1)
			continue;

		// **********************
		// Obviously we have images in this group
		// **********************
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
			$db -> basic_delete("small_images", "type='".$image_type."' and filename='".$save_images_path.$image['ATTRS']['filename']."'");

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

					if(!$db -> basic_insert("small_images", $image_insert))
						return false;

					$image_cat_count++;
		
				}
	
			}

		}

		// Update category count
		if($image_cat_count > 0)
			$db -> basic_update("small_image_cat", array("image_num" => $image_cat_count), "id = '".$cat_id."'");
                                
	}
        
	return true;
        
}


?>
