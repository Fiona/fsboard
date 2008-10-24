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
*       Small Image Manager     *
*       Started by Fiona        *
*       16th May 2006           *
*********************************
*       Last edit by Fiona      *
*       26th Aug 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// Language stuff
//***********************************************
load_language_group("admin_small_images");


//***********************************************
// Function stuff
//***********************************************
include ROOT."admin/common/funcs/smallimages.funcs.php";

//***********************************************
// What image are we playing with?
//***********************************************
switch(CURRENT_MODE)
{

        case "avatars":
                
                $image_mode = array(
                        "type" => "avatars",
                        "cache" => "avatars",
                        "config_path" => "avatar_upload_path",
                        "export_filename" => "fsboard-avatars.xml",
                        "xml_root" => "avatars_file"
                );

                break;

        case "emoticons":
                
                $image_mode = array(
                        "type" => "emoticons",
                        "cache" => "emoticons",
                        "config_path" => "emoticon_upload_path",
                        "export_filename" => "fsboard-emoticons.xml",
                        "xml_root" => "emoticons_file"
                );

                break;

        case "posticons":

                $image_mode = array(
                        "type" => "post_icons",
                        "cache" => "post_icons",
                        "config_path" => "post_icon_upload_path",
                        "export_filename" => "fsboard-post-icons.xml",
                        "xml_root" => "post_icons_file"
                );

}

$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE], "index.php?m=".CURRENT_MODE);


//***********************************************
// Action/Page selection
//***********************************************
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

	// ******************************
	// ADDING IMAGES
	// ******************************
        case "add":
                page_add_image();
                break;

        case "doaddone":
                do_add_single_image();
                break;

        case "pageaddmany":
                page_add_many_images();
                break;

        case "doaddmany":
                do_add_many_images();
                break;

	// ******************************
	// IMAGE EDITING
	// ******************************
        case "viewimages":
                page_view_images();
                break;

        case "doimageorder":        
                do_update_image_order();
                break;

        case "editimage":
                page_edit_image();
                break;

        case "doeditimage":
                do_edit_image();
                break;

        case "deleteimage":
                do_delete_image();
                break;

	// ******************************
	// MOVING MULTIPLE IMAGES 
	// ******************************
	case "movemultiple":
		page_move_multiple();
		break;

	case "domovemultiple":
		do_move_multiple();
		break;
		
	// ******************************
	// CATEGORY EDITING
	// ******************************
        case "newcat":
                do_add_category();
                break;

        case "editcat":
                page_edit_category();
                break;

        case "doeditcat":
                do_edit_category();
                break;

        case "updatepositions":
                do_update_category_positions();
                break;

        case "deletecat":
                page_delete_category();
                break;

        case "dodeletecat":
                do_delete_category();
                break;

	// ******************************
	// PERMISSION EDITING
	// ******************************
        case "catpermissions":
                page_category_permissions();
                break;

        case "docatpermissions":
                do_category_permissions();
                break;

	// ******************************
	// IMPORTING AND EXPORTING
	// ******************************
        case "importexport":
                page_import_export();
                break;

        case "doimport":
                do_import();
                break;

        case "doexport":
                do_export();
                break;  
                	                
        default;
                page_main();   
                             
}                

// *********************
// Set page title
// *********************
$output -> page_title = $lang['page_title_'.$image_mode['type']];



//***********************************************
// Front page with all the cats
//***********************************************
function page_main()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // ********************
        // Start table
        // ********************
        // Create class
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                // This Javascript function will chuck the page where it needs to when we 
                // Use the action dropdown
                "
                <script type=\"text/javascript\">
                        function do_cat_action(id)
                        {

                                var _value = eval(\"document.imagecats.cat_\"+id+\"_action.options[document.imagecats.cat_\"+id+\"_action.selectedIndex].value\");
                                var go_to = \"\";
                                
                                document.imagecats.reset();

                                if(_value != '')                                
                                {
                               
                                        switch(_value)
                                        {
                                        
                                                case 'edit':
                                                        go_to = \"".ROOT."admin/index.php?m=".CURRENT_MODE."&m2=editcat&id=\"+id;
                                                        break;
                                        
                                                case 'move_multiple':
                                                        go_to = \"".ROOT."admin/index.php?m=".CURRENT_MODE."&m2=movemultiple&id=\"+id;
                                                        break;
                                        
                                                case 'view':
                                                        go_to = \"".ROOT."admin/index.php?m=".CURRENT_MODE."&m2=viewimages&id=\"+id;
                                                        break;
                                                        
                                                case 'delete':
                                                        go_to = \"".ROOT."admin/index.php?m=".CURRENT_MODE."&m2=deletecat&id=\"+id;
                                                        break;
                                                        
                                                case 'permissions':
                                                        go_to = \"".ROOT."admin/index.php?m=".CURRENT_MODE."&m2=catpermissions&id=\"+id;
                                                        break;
                                        
                                        }

                                        window.location = go_to;
                                        
                                }
                                
                        }
                </script>
                ".
                $form -> start_form("imagecats", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=updatepositions", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['page_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['images_main_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "4").
                
                $table -> add_row(
                        array(
                                array($lang['images_main_name'], "auto"),
                                array($lang['images_main_order'], "auto"),
                                array($lang['images_main_count'], "auto"),
                                array($lang['images_main_actions'], "auto")
                        )
                , "strip2")
        );

        // *************************
        // Select categories
        // *************************
        $select_table = $db -> query("select id,name,`order`,image_num from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."' order by `order` asc");

        // No cats?
        if( $db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['images_main_no_cats_'.$image_mode['type']]."</b>", "normalcell",  "padding : 10px", "center")
                );        
                
        else
        {

                // *************************
                // Go through each category if we have some
                // *************************
                while($c_array = $db-> fetch_array())
                {

                        // Do the dropdowns
                        $dropdown_vals = array(
                                "edit",
                                "delete",
                                "view",
                                "move_multiple"
                        );
                        
                        $dropdown_text = array(
                                $lang['images_main_action_edit'],
                                $lang['images_main_action_delete'],
                                $lang['images_main_action_view'],
                                $lang['images_main_action_move_multiple']
                        );
                        
                        // We need permission links
                        if($image_mode['type'] != "emoticons")
                        {
                        
                                $dropdown_vals[] = "permissions";
                                $dropdown_text[] = $lang['images_main_action_permissions'];
                        
                        }

                        // Do the row itself
                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($c_array['name'], "30%"),
                                                array($form -> input_int("order[".$c_array['id']."]", $c_array['order']), "15%"),
                                                array($c_array['image_num'], "10%"),
                                                array(
                                                        // Sorry this is such a messy mess mess
                                                        $form -> input_dropdown(
                                                                "cat_".$c_array['id']."_action", "edit", $dropdown_vals, $dropdown_text,
                                                                "inputtext", "auto", "onchange=\"do_cat_action(".$c_array['id'].");\""
                                                        )
                                                        .$form -> button("actiongo", "Go", "submitbutton", "onclick=\"do_cat_action(".$c_array['id'].");\"")
                                                , "55%")
                                        )
                                , "normalcell")
                        );
                        
                }
                
        }

        // ********************
        // End table and do the new category form
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> submit("savecatorder", $lang['images_main_save_order_submit'])
                , "strip3", "", "center", "100%", "8").
                $table -> end_table().
                $form -> end_form().

                // New cat
                $form -> start_form("newcats", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=newcat", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['image_main_new_cat_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['image_main_new_cat_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "4").
                
                $table -> add_row(
                        array(
                                array($lang['image_main_new_cat_name'], "auto"),
                                array($form -> input_text("name", ""), "auto")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['image_main_new_cat_order'], "auto"),
                                array($form -> input_text("order", ""), "auto")
                        )
                , "normalcell").

                $table -> add_submit_row($form, "submit", $lang['image_main_new_cat_submit']).
                $table -> end_table().
                $form -> end_form()

        );
                
}



//***********************************************
// Form for adding an image
//***********************************************
function page_add_image()
{

        global $cache, $output, $lang, $db, $template_admin, $image_mode;

        // *********************
        // Check if DIR is writable
        // *********************
        if(
                !is_dir(ROOT.$cache -> cache['config'][$image_mode['config_path']])
                || !is_writable(ROOT.$cache -> cache['config'][$image_mode['config_path']]) 
        )
        {
                // Error if not
                $output -> add(
                        $template_admin -> critical_error(
                                $output -> replace_number_tags($lang['add_image_directory_not_writable'], array($cache -> cache['config'][$image_mode['config_path']]))
                        )
                );
        
        }

        // *********************
        // For category dropdown
        // *********************
        // Select the table
        $select_table = $db -> query("select id,name from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."' order by `order` asc");

        $cat_dropdown = array();
        
        // Go through all the rows
        while($row = $db -> fetch_array($select_table))
        {

                $cat_dropdown['values'][] = $row['id'];
                $cat_dropdown['text'][] = $row['name'];

        }

        // *********************
        // Adding single image form
        // *********************
	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_add'], "index.php?m=".CURRENT_MODE."&amp;m2=add");

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("addsmallimage", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doaddone", "post", true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info message
                // ---------------
                $table -> add_basic_row($lang['add_one_image_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['add_one_image_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Title/Path/Browse/Category
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['add_one_image_name_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['add_one_image_name_desc_'.$image_mode['type']]."</font>", "50%"),
                                array($form -> input_text("name", ""), "50%")
                        )
                , "normalcell")
        );

        // *********************
        // Post count selection for avatars and post icons
        // *********************
        if($image_mode['type'] == "avatars" || $image_mode['type'] == "post_icons")
                $output -> add(
                        $table -> add_row(
                                array(
                                        array($lang['add_one_image_min_posts_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['add_one_image_min_posts_desc_'.$image_mode['type']]."</font>", "50%"),
                                        array($form -> input_int("min_posts", ""), "50%")
                                )
                        , "normalcell")
                );
        // *********************
        // Emoticon code selection
        // *********************
        elseif($image_mode['type'] == "emoticons")
                $output -> add(
                        $table -> add_row(
                                array(
                                        array($lang['add_one_image_emoticon_code']."<br /><font class=\"small_text\">".$lang['add_one_image_emoticon_code_desc']."</font>", "50%"),
                                        array($form -> input_text("emoticon_code", ""), "50%")
                                )
                        , "normalcell")
                );
                                      
        // *********************
        // Carry on
        // *********************
        $output -> add(
                $table -> add_row(
                        array(
                                array($lang['add_one_image_cat_'.$image_mode['type']], "50%"),
                                array($form -> input_dropdown("cat_id", "", $cat_dropdown['values'], $cat_dropdown['text']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_one_image_filename_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['add_one_image_filename_desc']."</font>", "50%"),
                                array($form -> input_text("filename", "upload/".$image_mode['type']."/image.gif"), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_one_image_upload']."<br /><font class=\"small_text\">".$output -> replace_number_tags($lang['add_one_image_upload_desc'], array($cache -> cache['config'][$image_mode['config_path']]))."</font>", "50%"),
                                array($form -> input_file("upload"), "50%")
                        )
                , "normalcell").

                // --------------
                // Submit and Reset
                // --------------
                $table -> add_submit_row($form, "submit", $lang['add_one_image_submit_'.$image_mode['type']]).
                $table -> end_table().
                $form -> end_form()
        );

        // *********************
        // Multiple images form
        // *********************
        $output -> add(
                $form -> start_form("addmanysmallimage", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=pageaddmany", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info message
                // ---------------
                $table -> add_basic_row($lang['add_many_image_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['add_many_image_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Stuff
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['add_many_image_path_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['add_many_image_path_desc']."</font>", "50%"),
                                array($form -> input_text("path", ""), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_one_image_cat_'.$image_mode['type']], "50%"),
                                array($form -> input_dropdown("cat_id", "", $cat_dropdown['values'], $cat_dropdown['text']), "50%")
                        )
                , "normalcell").
                // --------------
                // Submit and Reset
                // --------------
                $table -> add_submit_row($form, "submit", $lang['add_many_image_submit_'.$image_mode['type']]).
                $table -> end_table().
                $form -> end_form()
        );
        
}

//***********************************************
// Actually adding one image
//***********************************************
function do_add_single_image()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // *********************
        // Get post data
        // *********************
        $submit_info = array(
                "name" => $_POST['name'],
                "cat_id" => $_POST['cat_id'],
                "filename" => $_POST['filename']
        );
        
        if($image_mode['type'] == "emoticons")
                $submit_info['emoticon_code'] = $_POST['emoticon_code'];
        else
                $submit_info['min_posts'] = $_POST['min_posts'];

        $submit_info = array_map("trim", $submit_info);
        

        // *********************
        // Get a file from the upload?
        // *********************
        $file_uploaded = false;
        $file_from_path = false;

        if(file_exists($_FILES['upload']['tmp_name']))
        {
        
                $tmp_filename = $_FILES['upload']['tmp_name'];
                $real_filename = $_FILES['upload']['name'];
                $file_path = ROOT.$cache -> cache['config'][$image_mode['config_path']];
                $full_path = $file_path.$real_filename;
                
                // ---------
                // Check if DIR is writable
                // ---------
                if(
                        !is_dir(ROOT.$cache -> cache['config'][$image_mode['config_path']])
                        || !is_writable(ROOT.$cache -> cache['config'][$image_mode['config_path']]) 
                )
                {
                
                        // Error if not
                        $output -> add(
                                $template_admin -> critical_error(
                                        $output -> replace_number_tags($lang['add_image_directory_not_writable'], array($cache -> cache['config'][$image_mode['config_path']]))
                                )
                        );
                
                        page_add_image();
                        return;
                
                }
                
                // ---------
                // Is file uploaded?
                // ---------
                if(!is_uploaded_file($tmp_filename))
                {
                
                        $output -> add($template_admin -> critical_error($lang['upload_image_error_not_uploaded']));
                        page_add_image();
                        return;
                                        
                }

                // ---------
                // Does it exist?
                // ---------
                if(file_exists($file_path.$real_filename))
                {
                
                        $output -> add(
                                $template_admin -> critical_error(
                                        $output -> replace_number_tags($lang['upload_image_error_file_exists'], array($real_filename))
                                )
                        );
                
                        page_add_image();
                        return;
                
                }
                
                // ---------
                // Is it an image?
                // ---------
                if(!getimagesize($tmp_filename))
                {

                        $output -> add($template_admin -> critical_error($lang['upload_image_error_not_image']));
                        page_add_image();
                        return;
                
                }

                // ---------
                // Move it properly
                // ---------
                if(!move_uploaded_file($tmp_filename, $file_path.$real_filename))
                {
                
                        $output -> add($template_admin -> critical_error($lang['upload_image_error_not_uploaded']));
                        page_add_image();
                        return;
                                        
                }
                                
                $file_uploaded = true;
                $submit_info['filename'] = $cache -> cache['config'][$image_mode['config_path']].$real_filename;

        }
        // *********************
        // Use a file already uploaded
        // *********************
        elseif(file_exists(ROOT.$submit_info['filename']))
        {

                $full_path = ROOT.$submit_info['filename'];
                $file_from_path = true;
                
                if(!getimagesize($full_path))
                {
                        $output -> add($template_admin -> critical_error($lang['upload_image_error_not_image']));
                        page_add_image();
                        return;                
                }
                                        
        }
        // *********************
        // No file was found! :O
        // *********************
        else
        {
                
                $output -> add(
                        $template_admin -> normal_error(
                                $output -> replace_number_tags($lang['upload_image_error_file_not_found'], array($submit_info['filename']))
                        )
                );
        
                page_add_image();
                return;
        
        }        

        // *********************
        // Check we're not missing some needed input
        // *********************
        if($submit_info['name'] == "" || $submit_info['cat_id'] == "" || ($submit_info['emoticon_code'] == "" && $image_mode['type'] == "emoticons") )
        {

                if($file_uploaded)
                        unlink($full_path);

                $output -> add($template_admin -> normal_error($lang['add_one_image_error_input_'.$image_mode['type']]));
                page_add_image();
                return;
        
        }

        // *********************
        // Smilie with code doesn't exist?
        // *********************
        if($image_mode['type'] == "emoticons")
        {

                $db -> query("select id from ".$db -> table_prefix."small_images where `type` = 'emoticons' and emoticon_code = '".$submit_info['emoticon_code']."'");

                if($db -> num_rows() > 0)
                {

                        if($file_uploaded)
                                unlink($full_path);
                                
                        $output -> add($template_admin -> normal_error($lang['add_one_image_error_emoticon_code_exists']));
                        page_add_image();
                        return;
                
                }

        }

        // *********************
        // Try to insert it
        // *********************
        $submit_info['type'] = $image_mode['type'];
        
        if(!$db -> basic_insert("small_images", $submit_info))
        {

                if($file_uploaded)
                        unlink($full_path);
        
                $output -> add($template_admin -> critical_error($lang['add_one_image_error_inserting']));
                page_add_image();
                return;
        
        }     

        // *********************
        // Increase category by +1
        // *********************
        $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num + 1 where id = '".$submit_info['cat_id']."'");

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache($image_mode['cache']);
        $cache -> update_cache("small_image_cats");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action($image_mode['type'], "doaddone", "Added small image (".$image_mode['type']."): ".$submit_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE, $lang['add_one_image_created_sucessfully_'.$image_mode['type']]);
                        
}


//***********************************************
// Adding many images to a cat
//***********************************************
function page_add_many_images()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // **********************
        // Get stuff from the post
        // **********************
        $image_info = array(
                "path"  => $_POST['path'],
                "cat_id" => $_POST['cat_id']
        );

	// HACK HACK HACK
	$_GET['m2'] = "pageaddmany";

        // ***************************
        // Check there's something in the path submitted
        // ***************************
        if(trim($image_info['path']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_many_images_invalid_path']));
                page_add_image();
                return;
        } 

        $image_info['path'] = (_substr($image_info['path'], 0, 3) == ROOT) ?  $image_info['path'] : ROOT.$image_info['path'];
        
        $image_info['path'] = (_substr($image_info['path'], -1, 1) == "/") ? $image_info['path'] : $image_info['path']."/";

        // ***************************
        // Check the path exists and is a dir
        // ***************************
        if(!file_exists($image_info['path']) || !is_dir($image_info['path']))
        {
                $output -> add($template_admin -> normal_error($lang['add_many_images_invalid_path']));
                page_add_image();
                return;
        } 
        

        // ***************************
        // Need all current image paths so we don't insert duplicates
        // ***************************
        $db -> query("select filename from ".$db -> table_prefix."small_images where `type` = '".$image_mode['type']."'");

        $existing_files = array();

        if($db -> num_rows() > 0)
                while($check_array = $db -> fetch_array())
                        $existing_files[$check_array['filename']] = true;
        
        // ***************************
        // Read the directory of images
        // ***************************
        if(!$dirh = opendir($image_info['path']))
        {
                $output -> add($template_admin -> normal_error($lang['add_many_images_invalid_path']));
                page_add_image();
                return;
        } 
        
        $files_array = array();
        
        while(false !== ($file = readdir($dirh)))
        {
        
                // Real file check
                if($file == "." || $file == ".." || is_dir($image_info['path'].$file))
                        continue;

		$file2 = $image_info['path'].$file;
                        
                // image check
                if(!@getimagesize($file2))
                        continue;

                // doesn't already exist check
		$file2 = (_substr($file2, 0, 3) == ROOT) ?  _substr($file2, 3) : $file2;
                
                if(!$existing_files[$file2])                                        
                        $files_array[] = $file;
                
        }

        // ***************************
        // No more to add?
        // ***************************
        if(count($files_array) < 1) 
        {
                $output -> add($template_admin -> normal_error($lang['add_many_images_empty_dir']));
                page_add_image();
                return;
        } 


        // **************************
        // We'll need the category dropdowns first
        // **************************
        $cat_dropdown = array();
        
        $db -> query("select id,name from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."'");
        
        while($cat_array = $db -> fetch_array())
        {
                $cat_dropdown['text'][] = $cat_array['name'];
                $cat_dropdown['vals'][] = $cat_array['id'];
        }
        

        // **************************
        // Show up the images
        // **************************
        $images_per_row = 4;
        $rows_per_page = 4;
        
        $images_per_page = $images_per_row * $rows_per_page;
        
        // work out the limit we want
        $page = ($_GET['page']) ?  $_GET['page'] : $_POST['page'];
        if(!$page)
                $page = 1;

        $start_image = ($page * $images_per_page) - $images_per_page;
        $end_image = $start_image + $images_per_page;

        $total_images = count($files_array);

        if($end_image > $total_images)
                $end_image = $total_images;
        
        // **************************
        // Title of the table/form.
        // **************************
        $form = new form_generate;
        $table = new table_generate;

	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_add_many'], "index.php?m=".CURRENT_MODE."&amp;m2=pageaddmany");

        image_view_title($table, $form, $image_info['path'], $page,  ceil($total_images / $images_per_page), $images_per_row);


        // **************************
        // Now lets pop up all the rows
        // **************************
        $image_cells = array();
        $count = 0;
        $count_all = 0;
        $page_jump = 0;
        
        // Go through each image....
        foreach($files_array as $image_array)        
        {

                if($page != 1 && $page_jump < ($page - 1) * $images_per_page)
                {
                        $page_jump++;
                        continue;
                }

                $count ++;
                $count_all ++;
                
                // Last of the images in this row?
                if($count == $images_per_row)
                {

                        $image_cells[] = image_view_image_cell($form, $image_array, $images_per_row, $image_info['path'], $cat_dropdown);

                        image_view_image_row($table, $image_cells);

                        $image_cells = array();
                        
                        $count = 0;
                        
                }
                // Normally add a cell
                else
                        $image_cells[] = image_view_image_cell($form, $image_array, $images_per_row, $image_info['path'], $cat_dropdown);

                // Hit the end? Fill in some blank cells...
                if($count_all == $images_per_page && $count != 0)
                {
                
                        for($a = $count; $a < $images_per_row; $a++)
                                $image_cells[] = array(" &nbsp; ", ceil(100 / $images_per_row)."%", "center");
                                
                        $count = 0;                                        
                
                }
                
                // We've hit the end, let's die
                if($count_all == $images_per_page)
                        break;

        }

        // Got some left? Oh no.
        if($count > 0)
        {
        
                for($a = $count; $a < $images_per_row; $a++)
                        $image_cells[] = array(" &nbsp; ", ceil(100 / $images_per_row)."%", "center");

                image_view_image_row($table, $image_cells);
        
        }


        // **************************
        // Need some extra info to carry over
        // **************************
        $output -> add(
                $form -> hidden("path", $image_info['path']).
                $form -> hidden("cat_id", $image_info['cat_id'])   
        );
        
        
        // **************************
        // Add submit button
        // **************************
        image_view_submit($table, $form, $page);
        

        // **************************
        // Page selection buttons
        // **************************
        image_view_page_select($images_per_page, $total_images, "", $image_info);
                 
}



//***********************************************
// Actually adding many images to a cat now
//***********************************************
function do_add_many_images()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;


        // *********************
        // Get post data
        // *********************
        $submit_info = array(
                "add"           => $_POST['add'],
                "add_cat"       => $_POST['add_cat'],
                "path"          => $_POST['path'],
                "cat_id"        => $_POST['cat_id']
        );
        
        if($image_mode['type'] == "emoticons")
                $submit_info['add_code'] = $_POST['add_code'];
        else
                $submit_info['add_post_count'] = $_POST['add_post_count'];



        // ********************
        // Get all images to be saved
        // ********************
        if(count($submit_info['add']) < 1)
        {

                $output -> add(
                        $template_admin -> normal_error($lang['add_many_error_none_selected'])
                );
                
                page_add_many_images();
                return;        
        
        }
        
        $image_array = array();
        
        foreach($submit_info['add'] as $key => $val) 
        {
        
                $image_array[$key]['name'] = _substr(reverse_strrchr($key, "."), 0, -1);
                $image_array[$key]['cat_id'] = $submit_info['add_cat'][$key];

                if($image_mode['type'] == "emoticons")
                         $image_array[$key]['emoticon_code'] = $submit_info['add_code'][$key];
                else
                         $image_array[$key]['min_posts'] = $submit_info['add_post_count'][$key];
        
        }
        
	// **********************
	// Go through them all 
	// **********************
	foreach($image_array as $image_filename => $image_info)
	{

		$path = (_substr($submit_info['path'], 0, 3) == ROOT) ?  _substr($submit_info['path'], 3) : $submit_info['path'];

		$image_info['full_path'] = $path.$image_filename;

		// Check it really is an image
                if(!getimagesize(ROOT.$image_info['full_path']))
                {

                        $output -> add(
                        	$template_admin -> normal_error(
                        		$output -> replace_number_tags(
                        			$lang['add_many_not_an_image'], array($image_filename)
                        		)
                        	)
                        );
                        
                        continue;   
                                     
                }
                
                // Check we've put in what input we need
	        if($image_info['name'] == "" || $image_info['cat_id'] == "" || ($image_info['emoticon_code'] == "" && $image_mode['type'] == "emoticons") )
                {

                        $output -> add(
                        	$template_admin -> normal_error(
                        		$output -> replace_number_tags(
                        			$lang['add_many_missing_input_'.$image_mode['type']], array($image_filename)
                        		)
                        	)
                        );
                        
                        continue;   
                                     
                }

	        // Smilie with code doesn't exist?
	        if($image_mode['type'] == "emoticons")
	        {
	
	                $db -> query("select id from ".$db -> table_prefix."small_images where `type` = 'emoticons' and emoticon_code = '".$image_info['emoticon_code']."'");
	
	                if($db -> num_rows() > 0)
	                {
				                                
	                        $output -> add(
	                        	$template_admin -> normal_error(
	                        		$output -> replace_number_tags(
	                        			$lang['add_many_emoticon_code_exists'], array($image_filename)
	                        		)
	                        	)
	                        );
                        		                
				continue;
				
	                }
	
        	}
        	
        	// Try to insert it now!
        	$new_submit_info = array(
        		"type" => $image_mode['type'],
        		"name" => $image_info['name'],
        		"cat_id" => $image_info['cat_id'],
        		"filename" => $image_info['full_path']        	
        	);
        	
        	if($image_mode['type'] == "emoticons")
        		$new_submit_info['emoticon_code'] = $image_info['emoticon_code'];
        	else
        		$new_submit_info['min_posts'] = $image_info['min_posts'];

	        if(!$db -> basic_insert("small_images", $new_submit_info))
	        {

                        $output -> add(
                        	$template_admin -> critical_error(
                        		$output -> replace_number_tags(
                        			$lang['add_many_error_inserting'], array($image_filename)
                        		)
                        	)
                        );
                    		                
			continue;
					                                			
	        }

		// Did it! Say so...
                $output -> add(
                	$template_admin -> message(
                		$lang['add_many_done_title'],
                		$output -> replace_number_tags(
                			$lang['add_many_done_message'], array($image_filename)
                		)
                	)
                );
                    
                 // Want an extra item to the category
	        $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num + 1 where id = '".$image_info['cat_id']."'");

	        // Log it!
	        log_admin_action($image_mode['type'], "doaddone", "Added small image (".$image_mode['type']."): ".$image_info['name']);
	        
	}


        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache($image_mode['cache']);
        $cache -> update_cache("small_image_cats");

        // ***************************
	// Done
        // ***************************
	page_add_many_images();

}



//***********************************************
// Page for viewing images, uses the image_view functions
//***********************************************
function page_view_images()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // **************************
        // Grab the cat
        // **************************
        if($_POST['catselect'])
                $get_id = trim($_POST['catselect']);
        else
                $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name,type", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }        

        // Grab it
        $current_cat_array = $db -> fetch_array();


        // **************************
        // How many images in this category?
        // **************************
        $db -> query("select count(id) from ".$db -> table_prefix."small_images where cat_id='".$get_id."' and type='".$image_mode['type']."'");
        $total_images = $db -> result();
                

        // **************************
        // Want some images please
        // **************************
        $images_per_row = 4;
        $rows_per_page = 4;
        
        $images_per_page = $images_per_row * $rows_per_page;
        
        // work out the limit we want
        $page = ($_GET['page']) ?  $_GET['page'] : $_POST['page'];
        
        if($page > 1)
        {
                $current_page = $page;
                $limit_sql = " LIMIT ".(($page * $images_per_page) - $images_per_page).", ".$images_per_page;
        }
        else
        {
                $current_page = "1";
                $limit_sql = " LIMIT ".$images_per_page;
        }
        
        // Grab all the images in this category
        $select_images = $db -> query("select * from ".$db -> table_prefix."small_images where cat_id='".$get_id."' and type='".$image_mode['type']."' order by `order` asc ".$limit_sql);
        $image_count = $db -> num_rows($select_images);
        
        // Die if it doesn't exist
        if($db -> num_rows($select_images) == 0)
                $colspan = 1;
        else
                $colspan = $images_per_row;

        // **************************
        // Chuck the menu up there
        // **************************
        image_view_menu($get_id);

        // **************************
        // Title of the table/form.
        // **************************
	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_view'], "index.php?m=".CURRENT_MODE."&amp;m2=viewimages&amp;id=".$get_id);

        $form = new form_generate;
        $table = new table_generate;

        image_view_title($table, $form, $current_cat_array['name'], $current_page,  ceil($total_images / $images_per_page), $colspan);


        // **************************
        // No images around. Let's say so.
        // **************************
        if($colspan == 1)
        {

               $output -> add(
                        $table -> add_basic_row("<b>".$lang['image_view_no_images_'.$image_mode['type']]."</b>", "normalcell",  "padding : 10px", "center")
                );
        
        }
        // **************************
        // We have images, need to do the rows
        // **************************
        else
        {
        
                $image_cells = array();
                $row_count = 0;
                $page_count = 0;
                
                // Go through each one....
                while($image_array = $db -> fetch_array($select_images))        
                {
                
                        $row_count ++;
                        $page_count ++;
                        
                        // Last of the images in this row?
                        if($row_count == $images_per_row)
                        {

                                $image_cells[] = image_view_image_cell($form, $image_array, $images_per_row);

                                image_view_image_row($table, $image_cells);

                                $image_cells = array();
                                
                                $row_count = 0;
                                
                        }
                        // Normally add a cell
                        else
                                $image_cells[] = image_view_image_cell($form, $image_array, $images_per_row);

                        // Hit the end? Fill in some blank cells...
                        if($page_count == $images_per_page && $row_count != 0)
                        {
                        
                                for($a = $row_count; $a < $images_per_row; $a++)
                                        $image_cells[] = array(" &nbsp; ", ceil(100 / $images_per_row)."%", "center");
                                        
                                $row_count = 0;                                        
                        
                        }
                        
                }
                
                // Got some left? Oh no.
                if($row_count > 0)
                {
                
                        for($a = $row_count; $a < $images_per_row; $a++)
                                $image_cells[] = array(" &nbsp; ", ceil(100 / $images_per_row)."%", "center");

                        image_view_image_row($table, $image_cells);
                
                }
                
        }
        
        // **************************
        // Order submit button
        // **************************
        image_view_submit($table, $form, $current_page);

        // **************************
        // Page selection buttons
        // **************************
        image_view_page_select($images_per_page, $total_images, $get_id);
        
}



//***********************************************
// updating one or more image orders from the image view
//***********************************************
function do_update_image_order()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }

        // Grab it
        $current_cat_array = $db -> fetch_array();


        // **************************
        // Updating all of them?
        // **************************
        if(isset($_POST['save_order']['all']))
        {

                foreach($_POST['orders'] as $key => $val)
                {

                        $order_info = array("order" => $val);
                        
                        if(!$db -> basic_update("small_images", $order_info, "id='".$key."' and type='".$image_mode['type']."'"))        
                        {
                                $output -> add($template_admin -> critical_error($lang['error_updating_image_order']));
                                page_view_images();
                                return;
                        }
                                
                }

        }
        // **************************
        // Just updating one order
        // **************************
        else
        {

                $key = key($_POST['save_order']);
                
                $order_info = array("order" => $_POST['orders'][$key]);
        
                if(!$db -> basic_update("small_images", $order_info, "id='".$key."' and type='".$image_mode['type']."'"))        
                {
                        $output -> add($template_admin -> critical_error($lang['error_updating_image_order']));
                        page_view_images();
                        return;
                }

        }


        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache($image_mode['cache']);
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action($image_mode['type'], "doimageorder", "Updated small image order (".$image_mode['type'].")");

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=viewimages&amp;id=".$get_id."&amp;page=".$_POST['page'], $lang['image_orders_updated']);

}



//***********************************************
// form for editing an images details and stuff
//***********************************************
function page_edit_image()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // **************************
        // Grab the image
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_images", $get_id, "*", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_image_id']));
                page_main();
                return;
        }

        // Grab it
        $image_array = $db -> fetch_array();


        // *********************
        // For category dropdown
        // *********************
        // Select the table
        $select_table = $db -> query("select id,name from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."' order by `order` asc");

        $cat_dropdown = array();
        
        // Go through all the rows
        while($row = $db -> fetch_array($select_table))
        {

                $cat_dropdown['values'][] = $row['id'];
                $cat_dropdown['text'][] = $row['name'];

        }
        

        // *********************
        // The form
        // *********************
	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_view'], "index.php?m=".CURRENT_MODE."&amp;m2=viewimages&amp;id=".$image_array['cat_id']);
	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_edit_image'], "index.php?m=".CURRENT_MODE."&amp;m2=editimage&amp;id=".$get_id);

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("editimage", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doeditimage&amp;id=".$get_id, "post", true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info message
                // ---------------
                $table -> add_basic_row(
                        $output -> replace_number_tags($lang['edit_image_title_'.$image_mode['type']], array($image_array['name']))
                , "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row($lang['edit_image_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Title
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['edit_image_name_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['edit_image_name_desc_'.$image_mode['type']]."</font>", "50%"),
                                array($form -> input_text("name", $image_array['name']), "50%")
                        )
                , "normalcell")
        );
        
        // ******************
        // Emoticons want a code field, others want a post count field
        // ******************
        switch($image_mode['type'])
        {
                case "emoticons":

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($lang['edit_image_code_emoticons']."<br /><font class=\"small_text\">".$lang['edit_image_code_desc_emoticons']."</font>", "50%"),
                                                array($form -> input_text("emoticon_code", $image_array['emoticon_code']), "50%")
                                        )
                                , "normalcell")
                        );                

                        break;
                
                default:

                        $output -> add(
                                $table -> add_row(
                                        array(
                                                array($lang['edit_image_posts_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['edit_image_posts_desc_'.$image_mode['type']]."</font>", "50%"),
                                                array($form -> input_int("min_posts", $image_array['min_posts']), "50%")
                                        )
                                , "normalcell")
                        );                
        }


        // ******************
        // Rest of form
        // ******************
        $output -> add(
                $table -> add_row(
                        array(
                                array($lang['edit_image_order']."<br /><font class=\"small_text\">".$lang['edit_image_order_desc']."</font>", "50%"),
                                array($form -> input_int("order", $image_array['order']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_image_cat_'.$image_mode['type']], "50%"),
                                array($form -> input_dropdown("cat_id", $image_array['cat_id'], $cat_dropdown['values'], $cat_dropdown['text']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['edit_image_filename_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['edit_image_filename_desc']."</font>", "50%"),
                                array($form -> input_text("filename", $image_array['filename']), "50%")
                        )
                , "normalcell").
                // --------------
                // Submit and Reset
                // --------------
                $table -> add_submit_row($form, "submit", $lang['edit_image_submit']).
                $table -> end_table().
                $form -> end_form()
        );
        
}





//***********************************************
// Actually editing the image woohoo
//***********************************************
function do_edit_image()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **************************
        // Grab the image
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_images", $get_id, "id,name,cat_id", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_image_id']));
                page_main();
                return;
        }

        // Grab it
        $image_array = $db -> fetch_array();


        // *********************
        // Get post data
        // *********************
        $submit_info = array(
                "name" => $_POST['name'],
                "cat_id" => $_POST['cat_id'],
                "order" => $_POST['order'],
                "filename" => $_POST['filename']
        );
        
        if($image_mode['type'] == "emoticons")
                $submit_info['emoticon_code'] = $_POST['emoticon_code'];
        else
                $submit_info['min_posts'] = $_POST['min_posts'];

        $submit_info = array_map("trim", $submit_info);


        // *********************
        // Check we're not missing some needed input
        // *********************
        if($submit_info['name'] == "" || $submit_info['cat_id'] == "" || ($submit_info['emoticon_code'] == "" && $image_mode['type'] == "emoticons") )
        {

                $output -> add($template_admin -> normal_error($lang['edit_image_error_input_'.$image_mode['type']]));
                page_edit_image();
                return;
        
        }
        

        // *********************
        // Smilie with code doesn't exist?
        // *********************
        if($image_mode['type'] == "emoticons")
        {

                $db -> query("select id from ".$db -> table_prefix."small_images where `type` = 'emoticons' and emoticon_code = '".$submit_info['emoticon_code']."' and id != '".$image_array['id']."'");

                if($db -> num_rows() > 0)
                {
                                
                        $output -> add($template_admin -> normal_error($lang['edit_image_error_emoticon_code_exists']));
                        page_edit_image();
                        return;
                
                }

        }
        

        // *********************
        // Did we chose a new category? Check it exstis.
        // *********************
        if($submit_info['cat_id'] != $image_array['cat_id'])
        {
        
                if(!$db -> query_check_id_rows("small_image_cat", $submit_info['cat_id'], "id", "`type` = '".$image_mode['type']."'"))
                {
                        $output -> add($template_admin -> critical_error($lang['edit_image_new_cat_not_found']));
                        page_edit_image();
                        return;
                }
                
                $new_category = true;
        
        }
        else
		$new_category = false;


        // ***************************
        // Update the image
        // ***************************
        if(!$db -> basic_update("small_images", $submit_info, "`id` = '".$image_array['id']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_image_update_error']));
                page_edit_image();
                return;
        }
        
        
        // ***************************
        // Update category counts
        // ***************************
        if($new_category)
        {

                $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num + 1 where id = '".$submit_info['cat_id']."'");
                $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num - 1 where id = '".$image_array['cat_id']."'");
       
        }
        
        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache($image_mode['cache']);
        
        if($new_category)
                $cache -> update_cache("small_image_cats");
        
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action($image_mode['type'], "doeditimage", "Edited small image (".$image_mode['type']."): ".$submit_info['name']);


        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=viewimages&amp;id=".$submit_info['cat_id'], $lang['edit_image_edited_sucessfully_'.$image_mode['type']]);
        
}



//***********************************************
// Image deletion
//***********************************************
function do_delete_image()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **************************
        // Grab the image
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_images", $get_id, "id,name,cat_id,filename", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_image_id']));
                page_main();
                return;
        }

        // Grab it
        $image_array = $db -> fetch_array();


        // ***************************
        // Delete image...        
        // ***************************
        if(!$db -> basic_delete("small_images", "id='".$image_array['id']."' and type='".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['delete_image_error_deleting_image']));
                page_main();
                return;
        }


        // ***************************
        // Update users with this avatar
        // ***************************
        if($image_mode['type'] == "avatars")
        {

                $avatar_user_update = array(
                        "avatar_type" => "no",
                        "avatar_address" => "",
                        "avatar_gallery_cat" => ""
                );
                
                $db -> basic_update("users", $avatar_user_update, "`avatar_address` = '".$image_array['filename']."' and `avatar_type` = 'gallery'");

        }
                

        // ***************************
        // Update category count
        // ***************************
        $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num - 1 where id = '".$image_array['cat_id']."'");

       
        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache($image_mode['cache']);
        $cache -> update_cache("small_image_cats");
        
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action($image_mode['type'], "deleteimage", "Deleted small image (".$image_mode['type']."): ".$image_array['name']);


        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=viewimages&amp;id=".$image_array['cat_id'], $lang['delete_image_deleted_sucessfully_'.$image_mode['type']]);

}



//***********************************************
// Page for moving multiple images
//***********************************************
function page_move_multiple()
{

        global $output, $lang, $db, $template_admin, $image_mode;


        // **************************
        // Check category
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }

	$current_cat_array = $db -> fetch_array();

	// HACK HACK HACK again
	$_GET['m2'] = "movemultiple";
	
	
        // **************************
        // Get list of alternate categories
        // **************************
        $db -> query("select id,name from small_image_cat where `id` != ' ".$get_id."' and `type` = '".$image_mode['type']."'");
        
        if(!$db -> num_rows())
        {
                $output -> add($template_admin -> normal_error($lang['move_multiple_no_other_cats']));
                page_main();
                return;
        }

	$category_dropdown = array(
					"text" => array(""),
					"vals" => array("")
				);
	
	while($cat_array = $db -> fetch_array())
	{
		
		$category_dropdown['text'][] = $cat_array['name'];
		$category_dropdown['vals'][] = $cat_array['id'];
		
	}
	

        // **************************
        // How many images in this category?
        // **************************
        $db -> query("select count(id) from ".$db -> table_prefix."small_images where cat_id='".$get_id."' and type='".$image_mode['type']."'");
        $total_images = $db -> result();


        // **************************
        // Want some images please
        // **************************
        $images_per_row = 4;
        $rows_per_page = 4;
        
        $images_per_page = $images_per_row * $rows_per_page;
        
        // work out the limit we want
        $page = ($_GET['page']) ?  $_GET['page'] : $_POST['page'];
        
        if($page > 1)
        {
                $current_page = $page;
                $limit_sql = " LIMIT ".(($page * $images_per_page) - $images_per_page).", ".$images_per_page;
        }
        else
        {
                $current_page = "1";
                $limit_sql = " LIMIT ".$images_per_page;
        }
        
        // Grab all the images in this category
        $select_images = $db -> query("select * from ".$db -> table_prefix."small_images where cat_id='".$get_id."' and type='".$image_mode['type']."' order by `order` asc ".$limit_sql);
        $image_count = $db -> num_rows($select_images);
        
        // Die if it doesn't exist
        if($db -> num_rows($select_images) == 0)
                $colspan = 1;
        else
                $colspan = $images_per_row;

        // **************************
        // Title of the table/form.
        // **************************
	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_move_multiple'], "index.php?m=".CURRENT_MODE."&amp;m2=movemultiple&amp;id=".$get_id);

        $form = new form_generate;
        $table = new table_generate;

        image_view_title($table, $form, $current_cat_array['name'], $current_page,  ceil($total_images / $images_per_page), $colspan);


        // **************************
        // No images around. Let's say so.
        // **************************
        if($colspan == 1)
        {

               $output -> add(
                        $table -> add_basic_row("<b>".$lang['image_view_no_images_'.$image_mode['type']]."</b>", "normalcell",  "padding : 10px", "center")
                );
        
        }
        // **************************
        // We have images, need to do the rows
        // **************************
        else
        {
       
                $image_cells = array();
                $count = 0;
                
                // Go through each one....
                while($image_array = $db -> fetch_array($select_images))        
                {
                
                        $count ++;
                        
                        // Last of the images in this row?
                        if($count == $images_per_row)
                        {

                                $image_cells[] = image_view_image_cell($form, $image_array, $images_per_row, "", $category_dropdown);

                                image_view_image_row($table, $image_cells);

                                $image_cells = array();
                                
                                $count = 0;
                                
                        }
                        // Normally add a cell
                        else
                                $image_cells[] = image_view_image_cell($form, $image_array, $images_per_row, "", $category_dropdown);

                        // Hit the end? Fill in some blank cells...
                        if($total_images == $images_per_page && $count != 0)
                        {
                        
                                for($a = $count; $a < $images_per_row; $a++)
                                        $image_cells[] = array(" &nbsp; ", ceil(100 / $images_per_row)."%", "center");
                                        
                                $count = 0;                                        
                        
                        }
                        
                }
                
                // Got some left? Oh no.
                if($count > 0)
                {
                
                        for($a = $count; $a < $images_per_row; $a++)
                                $image_cells[] = array(" &nbsp; ", ceil(100 / $images_per_row)."%", "center");

                        image_view_image_row($table, $image_cells);
                
                }
                
        }

        // **************************
        // Move images submit button
        // **************************
        image_view_submit($table, $form, $current_page);

        // **************************
        // Page selection buttons
        // **************************
        image_view_page_select($images_per_page, $total_images, $get_id);
                         
}



//***********************************************
// Actually moving multiple the images
//***********************************************
function do_move_multiple()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;


        // **************************
        // Check category
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }

	$current_cat_array = $db -> fetch_array();


        // **************************
        // Get list of alternate categories
        // **************************
        $db -> query("select id from small_image_cat where `id` != ' ".$get_id."' and `type` = '".$image_mode['type']."'");
        
        if(!$db -> num_rows())
        {
                $output -> add($template_admin -> normal_error($lang['move_multiple_no_other_cats']));
                page_main();
                return;
        }

	$other_cats = array();
	
	while($cat_array = $db -> fetch_array())
		$other_cats[] = $cat_array['id'];


        // **************************
        // Grab all the images in this category
        // **************************
        $select_images = $db -> query("select id,name from ".$db -> table_prefix."small_images where cat_id='".$get_id."' and type='".$image_mode['type']."' order by `order` asc ");
        
	while($image_array = $db -> fetch_array())
		$image_list[$image_array['id']] = $image_array['name']; 
 
        // **************************
	// Go through the ones we want moving
        // **************************
        $reduce_count = 0;
        
        $move_post = array_map("trim", $_POST['move']);

	foreach($move_post as $image_id => $cat_id)
	{
		
		if(!$cat_id)
			continue;
			
		// Does the cat exist?
		if(!in_array($cat_id, $other_cats))
		{

			$message = $lang['move_multiple_cat_doesnt_exist'];

                        $output -> add(
                        	$template_admin -> critical_error(
                        		$output -> replace_number_tags(
                        			$message, array($image_list[$image_id])
                        		)
                        	)
                        );
                        
                        continue;  
			
		}
		
		// Actually try it now
                $move_update_info = array("cat_id" => $cat_id);
        
                if(!$db -> basic_update("small_images", $move_update_info, "id='".$image_id."' and type='".$image_mode['type']."'"))        
                {

			$message = $lang['move_multiple_error_database'];

                        $output -> add(
                        	$template_admin -> critical_error(
                        		$output -> replace_number_tags(
                        			$message, array($image_list[$image_id])
                        		)
                        	)
                        );
                        
                        continue;  

                }
                
                // Save that we've moved one
                $reduce_count++;

		$message = $lang['move_multiple_done_message'];

		// Did it! Say so...
                $output -> add(
                	$template_admin -> message(
                		$lang['move_multiple_done_title'],
                		$output -> replace_number_tags(
                			$message, array($image_list[$image_id])
                		)
                	)
                ); 
                
                // Update new category count
                $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num + 1 where id = '".$cat_id."'");

	        // Log it!
	        log_admin_action($image_mode['type'], "domovemultiple", "Moved small image (".$image_mode['type']."): ".$image_list[$image_id]);
                                     		
	}

        // **************************
	// Done them, if some have been moved update the old category count
        // **************************
	if($reduce_count > 0)
	{

                $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num - ".$reduce_count." where id = '".$get_id."'");
	        
	        $cache -> update_cache($image_mode['cache']);
	        $cache -> update_cache("small_image_cats");

	}

        // ***************************
	// Done
        // ***************************
	page_move_multiple();
			
}


//***********************************************
// Adding an image category
//***********************************************
function do_add_category()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **********************
        // Get stuff from the post
        // **********************
        $cat_info = array(
                "name"  => $_POST['name'],
                "order" => $_POST['order'],
                "type"  => $image_mode['type']
        );

        // ***************************
        // Check there's something in the name
        // ***************************
        if(trim($cat_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_image_cat_no_name']));
                page_main();
                return;
        } 

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("small_image_cat", $cat_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_image_cat_error']));
                page_main();
                return;
        }               

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("small_image_cats");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action(CURRENT_MODE, "addcat", "Added category (".$image_mode['type']."): ".$cat_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE, $lang['add_image_cat_created_'.$image_mode['type']]);

}


//***********************************************
// Form for editing an image category
//***********************************************
function page_edit_category($cat_info = "")
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name,`order`", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }
  
        if(!$cat_info)
                $cat_info = $db -> fetch_array();

        // **************************
        // Do the form!
        // **************************
	$output -> add_breadcrumb($lang['breadcrumb_edit_category'], "index.php?m=".CURRENT_MODE."&amp;m2=editcat&amp;id=".$get_id);

        $form = new form_generate;
        $table = new table_generate;
        
        $output -> add(
                $form -> start_form("editcat", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doeditcat&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['image_edit_cat_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['image_edit_cat_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "4").
                
                $table -> add_row(
                        array(
                                array($lang['image_edit_cat_name'], "auto"),
                                array($form -> input_text("name", $cat_info['name']), "auto")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['image_edit_cat_order'], "auto"),
                                array($form -> input_text("order", $cat_info['order']), "auto")
                        )
                , "normalcell").

                $table -> add_submit_row($form, "submit", $lang['image_main_edit_submit']).
                $table -> end_table().
                $form -> end_form()
        );
                
}


//***********************************************
// Actually editing an image category
//***********************************************
function do_edit_category()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name,`order`", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }

        // **********************
        // Get stuff from the post
        // **********************
        $cat_info = array(
                "name"  => $_POST['name'],
                "order" => $_POST['order'],
                "type"  => $image_mode['type']
        );
       
        // ***************************
        // Check there's something in the name
        // ***************************
        if(trim($cat_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_image_cat_no_name']));
                page_edit_category($cat_info);
                return;
        } 

        // ***************************
        // Do the edit...
        // ***************************
        if(!$db -> basic_update("small_image_cat", $cat_info, "id='".$get_id."' and type='".$image_mode['type']."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['edit_image_cat_error']));
                page_edit_category($cat_info);
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("small_image_cats");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action(CURRENT_MODE, "doeditcat", "Edited category (".$image_mode['type']."): ".$cat_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE, $lang['edit_image_cat_edited']);
        
}



//***********************************************
// Update positions from the main page
//***********************************************
function do_update_category_positions()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // ***************************
        // Get vaules
        // ***************************
        $order_array = $_POST['order'];
        $order_array = array_map('trim', $order_array);
        
        // ***************************
        // If we have any
        // ***************************
        if(count($order_array) > 0)
        {

                // Grab wanted categories
                $query_cats = $db -> query("select id,`order` from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."'");
                
                // Die if it doesn't exist
                if($db -> num_rows($query_cats) <= 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                        page_main();
                        return;
                }

                // ***************************
                // Go through the ones we have
                // ***************************
                while($cat_array = $db -> fetch_array($query_cats))
                {
                
                        // Is there a change?
                        if($order_array[$cat_array['id']] == $cat_array['order'])
                                continue; // skip it
                                
                        // Do the query
                        $update_info = array("order" => (int)$order_array[$cat_array['id']]);
                
                        if(!$db -> basic_update("small_image_cat", $update_info, "id='".$cat_array['id']."' and type='".$image_mode['type']."'"))        
                        {
                                $output -> add($template_admin -> critical_error($lang['error_updating_cat_positions']));
                                page_main();
                                return;
                        }
                
                }
                
        }

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE, $lang['cat_positions_updated']);
        
}



//***********************************************
// Ask for permission to delete and movement category
//***********************************************
function page_delete_category()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }        
                
        // Grab it
        $current_cat_array = $db -> fetch_array();

        // **************************
        // Get all categories for the dropdown
        // **************************
        $db -> query("select id,name from ".$db -> table_prefix."small_image_cat where id != '".$current_cat_array['id']."' and type='".$image_mode['type']."' order by `order`");

        // Can't delete the last one
        if($db -> num_rows() <= 0)
        {
                $output -> add($template_admin -> normal_error($lang['error_delete_image_cat_last_one']));
                page_main();
                return;
        }

        // Go through all and get dropdown values
        while($cats_array = $db -> fetch_array())
        {
                $dropdown_values[] = $cats_array['id'];
                $dropdown_text[] = $cats_array['name'];
        }

        // **************************
        // Do the permission form!
        // **************************
	$output -> add_breadcrumb($lang['breadcrumb_delete_category'], "index.php?m=".CURRENT_MODE."&amp;m2=deletecat&amp;id=".$get_id);

        $form = new form_generate;
        $table = new table_generate;
        
        $output -> add(
                $form -> start_form("deletecat", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=dodeletecat&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row(
                        $output -> replace_number_tags($lang['image_delete_cat_title_'.$image_mode['type']], array($current_cat_array['name']))
                , "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['image_delete_cat_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "4").
                
                $table -> add_row(
                        array(
                                array($lang['image_delete_cat_delete_images_'.$image_mode['type']], "50%"),
                                array($form -> input_yesno("deleteimages", 0), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['image_delete_cat_new_cat'], "50%"),
                                array($form -> input_dropdown("newcat", "", $dropdown_values, $dropdown_text), "50%")
                        )
                , "normalcell").

                $table -> add_submit_row($form, "submit", $lang['image_delete_cat_submit']).
                $table -> end_table().
                $form -> end_form()
        );        

}



//***********************************************
// Ask for permission to delete and movement category
//***********************************************
function do_delete_category()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name,image_num", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }        
                        
        $cat_info = $db -> fetch_array();

        // **************************
        // Get rid of all the images or move them
        // **************************
        if($_POST['deleteimages'])
        {

                // --------------
                // Delete images...        
                // --------------
                if(!$db -> basic_delete("small_images", "cat_id='".$get_id."' and type='".$image_mode['type']."'"))
                {
                        $output -> add($template_admin -> critical_error($lang['delete_cat_error_deleting_images']));
                        page_main();
                        return;
                }

                // --------------
                // Update users with this avatar
                // --------------
                if($image_mode['type'] == "avatars")
                {

                        $avatar_user_update = array(
                                "avatar_type" => "no",
                                "avatar_address" => "",
                                "avatar_gallery_cat" => ""
                        );
                        
                        $db -> basic_update("users", $avatar_user_update, "`avatar_gallery_cat` = '".$get_id."' and `avatar_type` = 'gallery'");

                }
                                
        }
        else
        {

                // --------------
                // Move images...
                // --------------        
                $get_move_id = trim($_POST['newcat']);
        
                if(!$db -> query_check_id_rows("small_image_cat", $get_move_id, "id,name", "`type` = '".$image_mode['type']."'"))
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                        page_main();
                        return;
                }
                                        
                $new_cat = $db -> fetch_array();

                // Do the queryyy!
                if(!$db -> basic_update("small_images", array("cat_id" => $get_move_id), "cat_id='".$get_id."' and type='".$image_mode['type']."'"))
                {
                        $output -> add($template_admin -> critical_error($lang['delete_cat_error_moving_images']));
                        page_main();
                        return;
                }

                // --------------
                // Update count numbers
                // --------------
                $db -> query("update ".$db -> table_prefix."small_image_cat set image_num = image_num + ".$cat_info['image_num']." where id='".$get_move_id."'");

                // --------------
                // Update users with this avatar
                // --------------
                if($image_mode['type'] == "avatars")
                {
                
                        $avatar_user_update = array(
                                "avatar_gallery_cat" => $get_move_id
                        );
                        
                        $db -> basic_update("users", $avatar_user_update, "`avatar_gallery_cat` = '".$get_id."' and `avatar_type` = 'gallery'");

                }
                                
        }

        // **************************
        // Delete image category       
        // **************************
        if(!$db -> basic_delete("small_image_cat", "cat_id='".$get_id."' and type='".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['delete_cat_error_deleting_category']));
                page_main();
                return;
        }

        // **************************
        // Delete category permissions      
        // **************************
        if(!$db -> basic_delete("small_image_cat_perms", "cat_id='".$get_id."'"))
        {
                $output -> add($template_admin -> normal_error($lang['delete_cat_error_deleting_perms']));
                page_main();
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("small_image_cats");
        $cache -> update_cache("small_image_cats_perms");

        $cache -> update_cache($image_mode['cache']);

        if(!$_POST['deleteimages'])
                $cache -> update_cache($new_cat['name']);
                
        // ***************************
        // Log it!
        // ***************************
        log_admin_action(CURRENT_MODE, "dodeletecat", "Deleted category (".$image_mode['type']."): ".$cat_info['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE, $lang['delete_cat_successful']);
        
}





//***********************************************
// Category permissions!
//***********************************************
function page_category_permissions()
{

        global $output, $lang, $db, $template_admin, $image_mode;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }        

        // Grab it
        $current_cat_array = $db -> fetch_array();
        
        // No permissions for emoticons
        if($current_cat_array['type'] == "emoticons")
        {
                $output -> add($template_admin -> normal_error($lang['cat_perms_emoticons_no']));
                page_main();
                return;
        }


        // **************************
        // Get list of user groups
        // **************************
        $db -> query("select id,name from ".$db -> table_prefix."user_groups order by id asc");

        // Are there any?
        if($db -> num_rows() > 0)
        {

                while($group = $db -> fetch_array())
                {
                        $perms_array[$group['id']] = true;
                        $groups_array[$group['id']]['name'] = $group['name'];
                }

        }
        else
        {
        
                $output -> add($template_admin -> critical_error($lang['cat_perms_no_user_groups']));
                page_main();
                return;
                
        }

        // **************************
        // Get the current permission data
        // **************************
        $db -> query("select user_group_id from ".$db -> table_prefix."small_image_cat_perms where cat_id='".$get_id."'");

        // Are there any?
        if($db -> num_rows() > 0)
        {

                while($perm = $db -> fetch_array())
                        $perms_array[$perm['user_group_id']] = false;

        }

        // **************************
        // Do the page itself
        // **************************
	$output -> add_breadcrumb($lang['breadcrumb_category_perms'], "index.php?m=".CURRENT_MODE."&amp;m2=catpermissions&amp;id=".$get_id);

        $form = new form_generate;
        $table = new table_generate;
        
        $output -> add(
                $form -> start_form("editcatperms", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=docatpermissions&amp;id=".$get_id, "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row(
                        $output -> replace_number_tags($lang['cat_perms_title_'.$image_mode['type']], array($current_cat_array['name']))
                , "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['cat_perms_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "4").
                $table -> add_row(
                        array(
                                array($lang['cat_perms_name'], "50%"),
                                array($lang['cat_perms_user_group_can_use'], "50%")
                        )
                , "strip2")
        );

        // Go through each one
        foreach($groups_array as $key => $group)
                // add it to the form
                $output -> add(
                        $table -> add_row(
                                array(
                                        array($group['name'], "50%"),
                                        array($form -> input_yesno("perms[".$key."]", $perms_array[$key]), "auto")
                                )
                        , "normalcell")
                );
        
        $output -> add(
                $table -> add_submit_row($form, "submit", $lang['cat_perms_submit']).
                $table -> end_table().
                $form -> end_form()
        );
        
}


//***********************************************
// Saving category permissions!
//***********************************************
function do_category_permissions()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // **************************
        // Grab the cat
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("small_image_cat", $get_id, "id,name", "`type` = '".$image_mode['type']."'"))
        {
                $output -> add($template_admin -> critical_error($lang['invalid_image_cat_id']));
                page_main();
                return;
        }        

        // Grab it
        $current_cat_array = $db -> fetch_array();
        
        // No permissions for emoticons
        if($current_cat_array['type'] == "emoticons")
        {
                $output -> add($template_admin -> normal_error($lang['cat_perms_emoticons_no']));
                page_main();
                return;
        }

        // **************************
        // Start by killing existing perms
        // **************************
        if(!$db -> basic_delete("small_image_cat_perms", "cat_id='".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['cat_perms_error_deleting_perms']));
                page_main();
                return;
        }

        // **************************
        // Then save any that were submitted
        // **************************
        // Get from post
        $post_perms = $_POST['perms'];
        array_map('trim', $post_perms);

        // Go through all
        foreach($post_perms as $key => $val)
        {
        
                // So not allowed? Save!
                if(!$val)
                {

                        $insert_stuff = array("cat_id" => $get_id, "user_group_id" => $key);
                        
                        if(!$db -> basic_insert("small_image_cat_perms", $insert_stuff))
                        {
                                $output -> add($template_admin -> critical_error($lang['cat_perms_insert_error']));
                                page_main();
                                return;
                        }

                }
                
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("small_image_cats_perms");
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action(CURRENT_MODE, "docatpermissions", "Edited category permissions (".$image_mode['type']."): ".$current_cat_array['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=".CURRENT_MODE, $lang['cat_perms_successful']);
        
}



//***********************************************
// Page for importing and exporting as XML
//***********************************************
function page_import_export()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;


        // *************************
        // Select categories
        // *************************
        $select_table = $db -> query("select id,name from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."' order by `order` asc");

	$output -> add_breadcrumb($lang['breadcrumb_'.CURRENT_MODE.'_importexport'], "index.php?m=".CURRENT_MODE."&amp;m2=importexport");

        // Create classes
        $table = new table_generate;
        $form = new form_generate;

        // No cats?
        if($db -> num_rows() > 0)
	{

	        $cats_dropdown[] .= "-1";
	        $cats_dropdown_text[] .= $lang['ie_all_cats_dropdown'];
	        
	        // Go through all cats
	        while($cat_array = $db -> fetch_array())
	        {
	                // Add to dropdown arrays
	                $cats_dropdown[] .= $cat_array['id'];
	                $cats_dropdown_text[] .= $cat_array['name'];
	        }
        		
	        // ----------------
	        // EXPORT FORM
	        // ----------------
	        $output -> add(
	                $form -> start_form("exportimages", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doexport", "post", false, true).
	                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	                // ---------------
	                // Title and info
	                // ---------------
	                $table -> add_basic_row($lang['ie_export_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "2").
	                $table -> add_basic_row($lang['ie_export_message_'.$image_mode['type']], "normalcell",  "padding : 5px", "left", "100%", "2").
	                // ---------------
	                // Export form
	                // ---------------
	                $table -> add_row(
	                        array(
	                                array($lang['ie_export_filename_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['ie_export_filename_message_'.$image_mode['type']]."</font>","50%"),
	                                array($form -> input_text("filename", $image_mode['export_filename']),"50%")
	                        )
	                , "normalcell").
	                $table -> add_row(
	                        array(
	                                array($lang['ie_export_which_cat_'.$image_mode['type']]."<br /><font class=\"small_text\">".$lang['ie_export_which_cat_message_'.$image_mode['type']]."</font>","50%"),
	                                array($form -> input_dropdown("cat", "", $cats_dropdown, $cats_dropdown_text),"50%")
	                        )
	                , "normalcell").
	                // ---------------
	                // Submit
	                // ---------------
	                $table -> add_basic_row($form->submit("submit", $lang['ie_export_submit_'.$image_mode['type']]) . $form -> reset("reset", $lang['form_reset']), "strip3",  "", "center", "100%", "2").
	                $table -> end_table().
	                $form -> end_form()
	        );
		
	}

        // ----------------
        // IMPORT FORM
        // ----------------
        $output -> add(
                $form -> start_form("importimages", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doimport", "post", true).
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                // ---------------
                // Title and info
                // ---------------
                $table -> add_basic_row($lang['ie_import_title_'.$image_mode['type']], "strip1",  "", "left", "100%", "2").
                $table -> add_basic_row(
                	$output -> replace_number_tags($lang['ie_import_message_'.$image_mode['type']], array($cache -> cache['config'][$image_mode['config_path']]))
                , "normalcell",  "padding : 5px", "left", "100%", "2").
                // ---------------
                // Import form
                // ---------------
                $table -> add_row(
                        array(
                                array($lang['ie_import_upload']."<br /><font class=\"small_text\">".$lang['ie_import_upload_message_'.$image_mode['type']]."</font>","50%"),
                                array($form -> input_file("file"),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['ie_import_filename']."<br /><font class=\"small_text\">".$lang['ie_import_filename_message_'.$image_mode['type']]."</font>","50%"),
                                array($form -> input_text("filename", "includes/".$image_mode['export_filename']),"50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['ie_import_overwrite_files']."<br /><font class=\"small_text\">".$lang['ie_import_overwrite_files_message_'.$image_mode['type']]."</font>","50%"),
                                array($form -> input_yesno("overwrite_files", 1),"50%")
                        )
                , "normalcell").
                // ---------------
                // Submit
                // ---------------
                $table -> add_basic_row($form -> submit("submit", $lang['ie_import_submit_'.$image_mode['type']]) . $form -> reset("reset", $lang['form_reset']), "strip3",  "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );      

}


//***********************************************
// Generating the XML file
//***********************************************
function do_export()
{

        global $output, $lang, $db, $template_admin, $image_mode;


        // *************************
        // Select categories
        // *************************
        if($_POST['cat'] > "-1")
            $single_id = ' and id = "'.$_POST['cat'].'" ';
		else
			$single_id = "";		               
                
        $select_table = $db -> query("select * from ".$db -> table_prefix."small_image_cat where `type` = '".$image_mode['type']."'".$single_id."order by `order` asc");

        // No cats?
        if($db -> num_rows() < 1)
	{
                $output -> add($template_admin -> critical_error($lang['export_could_not_find_cats']));
                page_import_export();
                return;
	}

        // *************************
        // Start XML'ing
        // *************************
        $xml = new xml;
        $xml -> export_xml_start();
        $xml -> export_xml_root($image_mode['xml_root']);
        	        
        // Go through all cats
        while($cat_array = $db -> fetch_array($select_table))
        {

                // *************************
                // Start off the group
                // *************************
                $xml -> export_xml_start_group(
                        "image_cat",
                        array(
                                "name" => $cat_array['name'],
                                "order" => $cat_array['order']
                        )
                );

                // *************************
                // Select the images in this group
                // *************************
                $select_images = $db -> query("select * from ".$db -> table_prefix."small_images where cat_id = '".$cat_array['id']."' and type = '".$image_mode['type']."' order by `order` asc");

                if($db -> num_rows($select_images) < 1)
	            	continue;

                while($image_array = $db -> fetch_array($select_images))
                {

			// Check the file exists first
			if(!file_exists(ROOT.$image_array['filename']))
				continue;
				
			// Get the data for it
			$h = fopen(ROOT.$image_array['filename'], "rb");
			
			$image_array['data'] = chunk_split(
							base64_encode(
								fread($h, filesize(ROOT.$image_array['filename']))
							)
						);
			
			fclose($h);
			
			
			// Do image XML entry
			$xml_entry = array(
				"name" => $image_array['name'],
				"order" => $image_array['order'],
				"filename" => _substr(strrchr($image_array['filename'], "/"), 1)
			);
			
			if($image_array['type'] == "emoticons")
				$xml_entry['emoticon_code'] = $image_array['emoticon_code'];
			else				
				$xml_entry['min_posts'] = $image_array['min_posts'];

                        // Add the image entry
                        $xml -> export_xml_add_group_entry("image", $xml_entry, $image_array['data']);
                                
                }                        

                // *************************
                // Finish group
                // *************************
                $xml -> export_xml_generate_group();
                                                           	
        }

        // *************************
        // Finish XML'ing
        // *************************
        $xml -> export_xml_generate();

        // *************************
        // Work out output file name                
        // *************************
	$filename = (!$_POST['filename']) ? $image_mode['export_filename'] : $_POST['filename'];
        
        // *************************
        // Chuck the file out
        // *************************
        output_file($xml -> export_xml, $filename, "text/xml");
        	        
}	        


//***********************************************
// Getting XML given to the script and importing it
//***********************************************
function do_import()
{

        global $output, $lang, $db, $template_admin, $image_mode, $cache;

        // Get file from upload
        if(file_exists($_FILES['file']['tmp_name']))
                $xml_contents = file_get_contents($_FILES['file']['tmp_name']);
        // Get file from server
        elseif(file_exists(ROOT.$_POST['filename']))
                $xml_contents = file_get_contents(ROOT.$_POST['filename']);
        // No file
        else
        {
                $output -> add($template_admin -> normal_error($lang['xml_file_not_found']));
                page_import_export();
                return;
        }

        // *************************
        // Import...
        // *************************
        $get_error = import_images_xml($xml_contents, $image_mode['type'], $cache -> cache['config'][$image_mode['config_path']], $_POST['overwrite_files']);

        // If we have version mismatch
        if((string)$get_error == "VERSION")
        {
                $output -> add($template_admin -> critical_error($lang['xml_version_mismatch_'.$image_mode['type']]));
                page_import_export();
                return;
        }

        // ***************************
        // Update cache
        // ***************************
        $cache -> update_cache("small_image_cats");
        $cache -> update_cache($image_mode['cache']);
                        
        $output -> add($template_admin -> message($lang['import_done_title'], $lang['import_done_message_'.$image_mode['type']]));

        page_import_export();

}


// ------------------------------------------------------------------



// ****************************
// Image view functions
// ****************************


// ---
// Dropdown menu to switch to other cats
// ----
function image_view_menu($cat_id)
{

        global $output, $lang, $db, $image_mode;

        // ************
        // Get all current cats
        // ************
        $db -> query("select id,name from ".$db -> table_prefix."small_image_cat where `type` ='".$image_mode['type']."'");
        
        while($cat_array = $db -> fetch_array())
        {
        
                $dropdown_text[] = $cat_array['name'];
                $dropdown_vals[] = $cat_array['id'];
        
        }

        // ************
        // Form itself
        // ************
        $form = new form_generate;
        $table = new table_generate;

        $output -> add(
                $form -> start_form("catselect", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=viewimages").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($lang['image_view_menu_title_'.$image_mode['type']], "strip1",  "", "left", "100%").
                $table -> add_basic_row(
                        $form -> input_dropdown("catselect", $cat_id, $dropdown_vals, $dropdown_text, "inputtext", "85%")
                        .$form -> submit("submit", $lang['image_view_menu_submit'])
                        , "normalcell",  "padding:10px;", "left", "100%"
                ).
                $table -> end_table().
                $form -> end_form()
        );
                
}


// ---
// Title row for the table
// ----
function image_view_title(&$table, &$form, $cat_name = "", $current_page = "", $page_num = "", $colspan = "1")
{

        global $output, $lang, $image_mode;

	$page_num = ($page_num == 0) ? $page_num = 1 : $page_num;

        // What message?
        switch($_GET['m2'])
        {
                case "viewimages":
                
                        if($_POST['catselect'])
                                $id = trim($_POST['catselect']);
                        else
                                $id = trim($_GET['id']);
                
                        $title_message = $output -> replace_number_tags(
                                $lang['image_view_title_'.$image_mode['type']],
                                array($cat_name, $current_page, $page_num)
                        );
                        $output -> add( $form -> start_form("imagesorder", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doimageorder&amp;id=".$id) );
                        break;

                case "pageaddmany":
               
                        $title_message = $output -> replace_number_tags(
                                $lang['image_add_many_title_'.$image_mode['type']],
                                array($cat_name, $current_page, $page_num)
                        );
                        $output -> add( $form -> start_form("addmanyimages", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=doaddmany") );
                        break;

                case "movemultiple":
               
                        $title_message = $output -> replace_number_tags(
                                $lang['image_move_multiple_title_'.$image_mode['type']],
                                array($cat_name, $current_page, $page_num)
                        );
                        
                        $id = trim($_GET['id']);
                        
                        $output -> add( $form -> start_form("movemultipleimages", ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=domovemultiple&amp;id=".$id) );
                        break;
        }

        // Chuck it up there
        $output -> add
        (
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
                $table -> add_basic_row($title_message, "strip1",  "", "left", "100%", $colspan)
        );
        
}


// ---
// Submit row for the end of the table
// ----
function image_view_submit(&$table, &$form, $current_page = 1)
{

        global $output, $lang, $image_mode;

        // What message?
        switch($_GET['m2'])
        {
                case "viewimages":
                        $submit_text = $lang['image_view_save_order'];
                        $submit_val = "save_order[all]";
                        break;
                        
                case "pageaddmany":
                        $submit_text = $lang['add_many_add_submit'];
                        $submit_val = "add_many";
                        break;

                case "movemultiple":
                        $submit_text = $lang['multiple_move_submit'];
                        $submit_val = "move_multiple";
                        break;
        }

        // finish table off
        $output -> add
        (
                $table -> add_submit_row($form, $submit_val, $submit_text).
                $form -> hidden("page", $current_page).
                $table -> end_table().
                $form -> end_form()
        );

}


// ---
// Cell that has each individual image in
// ---
function image_view_image_cell($form, $image_array, $images_per_row, $path = "", $cat_dropdown = "")
{

        global $output, $lang, $image_mode;

        $return = "";
        
        // What mode are we in?
        switch($_GET['m2'])
        {
                // Viewing the images        
                case "viewimages":

                        $table2 = new table_generate;

                        // emoticon want to see the code
                        if($image_mode['type'] == "emoticons")
                                $cells[] = $image_array['emoticon_code'];
                                
                        $cells[] = array("<img src=\"".ROOT.$image_array['filename']."\" alt=\"".$image_array['filename']."\" />", "50%", "center");
                        $cells[] = array(
                                        "<a href=\"".ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=editimage&amp;id=".$image_array['id']."\" title=\"".$lang['image_view_edit_'.$image_mode['type']]."\">
                                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a><br />
                                        <a href=\"".ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=deleteimage&amp;id=".$image_array['id']."\" onclick=\"return confirm('".$lang['image_view_delete_confirm_'.$image_mode['type']]."')\" title=\"".$lang['image_view_delete_'.$image_mode['type']]."\">
                                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>"
                                , "50%", "right");
                        
                        $return = 
                                $table2 -> start_table().
                                $table2 -> add_row($cells).
                                $table2 -> add_basic_row(
                                        $lang['image_view_order_text']." ".
                                        $form -> input_int("orders[".$image_array['id']."]", $image_array['order'], "inputtext", "3").
                                        " ".
                                        $form -> submit("save_order[".$image_array['id']."]", $lang['image_view_order_submit'])
                                ).
                                $table2 -> add_basic_row("<b>".$image_array['name']."</b>").
                                $table2 -> end_table()
                        ;

                        break;

                // Adding some bloody images
                case "pageaddmany":

                        $table2 = new table_generate;
                        $table2 -> colspan = 2;
                        $cell = "";

                        // emoticon want code input
                        if($image_mode['type'] == "emoticons")
                        {

                                $cell = $lang['add_many_add_code']." ".
                                        $form -> input_text("add_code[".$image_array."]", 
                                                ":"._substr(reverse_strrchr($image_array, "."), 0, -1).":"
                                        , "inputtext", "auto");

                        }
                        // Everything else wants post count input
                        else
                        {

                                $cell = $lang['add_many_add_post_count']." ".
                                        $form -> input_int("add_post_count[".$image_array."]", "0");
                        
                        }
                        
                        $return = 
                                $table2 -> start_table().
                                $table2 -> add_basic_row("<b>".$image_array."</b>").

                                $table2 -> add_row(
                                        array(
                                                array("<img src=\"".$path.$image_array."\" alt=\"".$image_array."\" />" , "50%", "center"),
                                                array($lang['add_many_add_text']." ".$form -> input_checkbox("add[".$image_array."]", "1") , "50%", "center")
                                        )
                                ).

                                $table2 -> add_basic_row(
                                        $lang['add_many_cat_text'] ." ".$form -> input_dropdown("add_cat[".$image_array."]", $_POST['cat_id'], $cat_dropdown['vals'], $cat_dropdown['text'], "inputtext", "auto")
                                ).

                                $table2 -> add_basic_row($cell).
                                
                                $table2 -> end_table()
                        ;

                        break;
                        
                // Moving more than one of the cunts      
                case "movemultiple":

                        $table2 = new table_generate;

                        // emoticon want to see the code
                        if($image_mode['type'] == "emoticons")
                                $cells[] = $image_array['emoticon_code'];

                        $cells[] = array("<img src=\"".ROOT.$image_array['filename']."\" alt=\"".$image_array['filename']."\" />", "50%", "center");

                        $return = 
                                $table2 -> start_table().
                                $table2 -> add_row($cells).
                                $table2 -> add_basic_row(
                                        $lang['move_multiple_dropdown_text']." ".
                                        $form -> input_dropdown("move[".$image_array['id']."]", "", $cat_dropdown['vals'], $cat_dropdown['text'], "inputtext", "auto")
                                ).
                                $table2 -> add_basic_row("<b>".$image_array['name']."</b>").
                                $table2 -> end_table()
                        ;
                        			
			break;                        
                        
        }
        
        // Bye
        return array(
                        $return,
                        ceil(100 / $images_per_row)."%",
                        "center"
                );
        
}


// ---
// Row that hold images on image viewing
// ---
function image_view_image_row(&$table, $image_cells)
{

        global $output;
        
        // Do the row
        $output -> add
        (
                $table -> add_row($image_cells, "normalcell")
        );

}


// ---
// Row with all the page selection stuff
// ---
function image_view_page_select($images_per_page, $total_images, $id = "", $image_info = "")
{

        global $output, $lang;

        if($total_images > $images_per_page)
        {

                $form = new form_generate;
                $table = new table_generate;
                
                // Work out where we are going
                switch($_GET['m2'])
                {
                
                        case "viewimages":
                                $form_target = ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=viewimages&amp;id=".$id;
                                break;
                
                        case "pageaddmany":
                                $form_target = ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=pageaddmany";
                                break;
                
                        case "movemultiple":
                                $form_target = ROOT."admin/index.php?m=".CURRENT_MODE."&amp;m2=movemultiple&amp;id=".$id;
                                break;
                
                };

                $output -> add(
                        $form -> start_form("pageselect", $form_target).
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;")
                );                

                // Carry over some form elements
                $carry_over = "";
                
                if($_GET['m2'] == "pageaddmany")
                        $carry_over =
                                $form -> hidden("path", $image_info['path']).
                                $form -> hidden("cat_id", $image_info['cat_id']);
        
                $pages_wanted = ceil($total_images / $images_per_page);
        
                for($a = 1; $a <= $pages_wanted; $a++)
                        $submit_buttons .= $form -> submit("page", $a) . " ";

                $output -> add(
                        $table -> add_basic_row($submit_buttons, "strip3").
                        $table -> end_table().
                        $carry_over.
                        $form -> end_form()
                );

        }
        
}

?>
