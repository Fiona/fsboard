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
//include ROOT."admin/common/funcs/profilefields.funcs.php";


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

			"db_extra_what" => array("`name`", "`description`", "`id`"),

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


//***********************************************
// Main view, you know the dillyo.
//***********************************************
/*
function page_main()
{

        global $output, $lang, $db, $template_admin;

        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['fields_main_title'];

        // Create class
        $table = new table_generate;
        $form = new form_generate;

        // ********************
        // Start table
        // ********************
        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['fields_main_title'], "strip1",  "", "left", "100%", "4").
                $table -> add_basic_row($lang['fields_main_message'], "normalcell",  "padding : 5px", "left", "100%", "4").

                $table -> add_row(array($lang['fields_main_name'],$lang['fields_main_database_name'],$lang['fields_main_order'],$lang['fields_main_actions']), "strip2")
        );

        // ********************
        // Grab all fields
        // ********************
        $fields = $db -> query("select id, name, description, `order` from ".$db -> table_prefix."profile_fields order by `order` asc");

        // Get amount
        $fields_amount = $db -> num_rows($fields);

        // No fields?
        if($fields_amount < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_fields']."</b>", "normalcell",  "padding : 10px", "center")
                );        
                
        else
        {

                // *************************
                // Print row for each field
                // *************************
                while($f_array = $db-> fetch_array($fields))
                {

                        $actions = "
                        <a href=\"index.php?m=profilefields&amp;m2=edit&amp;id=".$f_array['id']."\" title=\"".$lang['fields_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"index.php?m=profilefields&amp;m2=delete&amp;id=".$f_array['id']."\" title=\"".$lang['fields_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";
        
                        $output -> add(
                                $table -> add_row(      
                                        array(
                                                array($f_array['name']."<br /><font class=\"small_text\">".$f_array['description']."</font>", "25%"),
                                                array("field_".$f_array['id'], "20%"),
                                                array($f_array['order'], "20%"),
                                                array($actions, "25%")
                                        ),
                                "normalcell")
                        );

                }                
                
        }
        
        // ********************
        // End table
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> button("addfield", $lang['add_field_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=profilefields&m2=add';\"")
                , "strip3", "", "center").
                $table -> end_table().
                $form -> end_form()
        );
        
}
*/



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

	$output -> page_title = $lang['edit_field_title'];
	$output -> add_breadcrumb(
		$lang['breadcrumb_profilefields_edit'],
		l("admin/profile_fields/edit/".$field_id."/")
		);

	$form = new form(
		form_add_edit_profile_fields("edit")
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
function form_add_edit_profile_fields($type)
{

	global $lang, $output, $template_admin;

	if($type == "add")
	{
		$title = $lang['add_field_title'];
		$description = $lang['add_field_message'];
		$submit = $lang['add_field_submit'];
	}
	elseif($type == "edit")
	{
		$title = $lang['edit_field_title'];
		$description = NULL;
		$submit = $lang['edit_field_submit'];
	}

	$form_data = array(
			"meta" => array(
				"name" => "profile_field_".$type,
				"title" => $title,
				"description" => $description,
				"extra_title_contents_left" => (
					$output -> help_button("", True).
					$template_admin -> form_header_icon("users")
					),
//				"validation_func" => "form_users_add_validate",
//				"complete_func" => "form_users_add_complete"
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
				"type" => "yesno"
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
				"type" => "textarea"
				),
			"#submit" => array(
				"type" => "submit",
				"value" => $submit
				)
		);

	return $form_data;

}

//***********************************************
// Form for adding or editing a profile field
//***********************************************
/*
function page_add_edit_field($adding = false, $field_info = "")
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
                $output -> page_title = $lang['add_field_title'];

		$output -> add_breadcrumb($lang['breadcrumb_profilefields_add'], "index.php?m=profilefields&amp;m2=add");

                // ADDING
                $output -> add(
                        $form -> start_form("addfield", ROOT."admin/index.php?m=profilefields&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                        $table -> add_basic_row($lang['add_field_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_field_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_field_submit'];

                // init some values
                if(!$field_info)
                {
                
                        $field_info['user_can_edit'] = "1";
                        $field_info['topic_html'] = "<name>: <value><br />";

                }
                
        }
        else
        {


                // EDITING

                // Grab the field
                $get_id = trim($_GET['id']);
        
                // No ID
                if($get_id == '')
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_field_id']));
                        page_main();
                        return;
                }
                        
                // Grab wanted field
                $field = $db -> query("select * from ".$db -> table_prefix."profile_fields where id='".$get_id."'");

                // Die if it doesn't exist
                if($db -> num_rows($field) == 0)
                {
                        $output -> add($template_admin -> critical_error($lang['invalid_field_id']));
                        page_main();
                        return;
                }

                $field_info = $db -> fetch_array($field);

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_field_title'];

		$output -> add_breadcrumb($lang['breadcrumb_profilefields_edit'], "index.php?m=profilefields&amp;m2=edit&amp;id=".$get_id);

                // start form
                $output -> add(
                        $form -> start_form("editfield", ROOT."admin/index.php?m=profilefields&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                        $table -> add_basic_row($lang['edit_field_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_field_submit'];

                $field_info['dropdown_values'] = str_replace( '|', "\n", $field_info['dropdown_values']);
                $field_info['dropdown_text'] = str_replace( '|', "\n", $field_info['dropdown_text']);
        
        }


        // ***************************
        // Print some of the form
        // ***************************
        $output -> add(
                $table -> add_row(
                        array(
                                array($lang['add_field_name'], "50%"),
                                array($form -> input_text("name", $field_info['name']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_description'], "50%"),
                                array($form -> input_text("description", $field_info['description']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_field_type'], "50%"),
                                array(
                                        $form -> input_dropdown("field_type", $field_info['field_type'],
                                                array("text", "textbox", "yesno", "dropdown"),
                                                array($lang['field_type_text'], $lang['field_type_textbox'], $lang['field_type_yesno'], $lang['field_type_dropdown'])
                                        )
                                , "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_size']."<br /><font class=\"small_text\">".$lang['add_field_size_desc']."</font>", "50%"),
                                array($form -> input_int("size", $field_info['size']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_max_length']."<br /><font class=\"small_text\">".$lang['add_field_max_length_desc']."</font>", "50%"),
                                array($form -> input_int("max_length", $field_info['max_length']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_order']."<br /><font class=\"small_text\">".$lang['add_field_order_desc']."</font>", "50%"),
                                array($form -> input_int("order", $field_info['order']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_dropdown_values']."<br /><font class=\"small_text\">".$lang['add_field_dropdown_values_desc']."</font>", "50%"),
                                array($form -> input_textbox("dropdown_values", $field_info['dropdown_values']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_dropdown_text']."<br /><font class=\"small_text\">".$lang['add_field_dropdown_text_desc']."</font>", "50%"),
                                array($form -> input_textbox("dropdown_text", $field_info['dropdown_text']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_show_on_reg'], "50%"),
                                array($form -> input_yesno("show_on_reg", $field_info['show_on_reg']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_user_can_edit']."<br /><font class=\"small_text\">".$lang['add_field_user_can_edit_desc']."</font>", "50%"),
                                array($form -> input_yesno("user_can_edit", $field_info['user_can_edit']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_is_private']."<br /><font class=\"small_text\">".$lang['add_field_is_private_desc']."</font>", "50%"),
                                array($form -> input_yesno("is_private", $field_info['is_private']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_admin_only_field']."<br /><font class=\"small_text\">".$lang['add_field_admin_only_field_desc']."</font>", "50%"),
                                array($form -> input_yesno("admin_only_field", $field_info['admin_only_field']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_must_be_filled']."<br /><font class=\"small_text\">".$lang['add_field_must_be_filled_desc']."</font>", "50%"),
                                array($form -> input_yesno("must_be_filled", $field_info['must_be_filled']), "50%")
                        )
                , "normalcell").
                $table -> add_row(
                        array(
                                array($lang['add_field_topic_html']."<br /><font class=\"small_text\">".$lang['add_field_topic_html_desc']."</font>", "50%"),
                                array($form -> input_textbox("topic_html", $field_info['topic_html']), "50%")
                        )
                , "normalcell").
                $table -> add_basic_row($form -> submit("submit", $submit_lang), "strip3", "", "center", "100%", "2").
                $table -> end_table().
                $form -> end_form()
        );
                
}
*/


//***********************************************
// Add the field we're telling it to add or we tell it to go away
//***********************************************
function do_add_field()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Get stuff from the post
        // **********************
        if($_POST['dropdown_values'])
                $ddv = str_replace("\n", "|", str_replace("\n\n","\n",trim($_POST['dropdown_values'])));
        if($_POST['dropdown_text'])
                $ddt = str_replace("\n", "|", str_replace("\n\n","\n",trim($_POST['dropdown_text']))); // Isn't that a wrestling move?

        $field_info = array(
                "name"                  => $_POST['name'],
                "description"           => $_POST['description'],
                "field_type"            => $_POST['field_type'],
                "size"                  => $_POST['size'],
                "max_length"            => $_POST['max_length'],
                "order"                 => $_POST['order'],
                "dropdown_values"       => $ddv,
                "dropdown_text"         => $ddt,
                "show_on_reg"           => $_POST['show_on_reg'],
                "user_can_edit"         => $_POST['user_can_edit'],
                "is_private"            => $_POST['is_private'],
                "admin_only_field"      => $_POST['admin_only_field'],
                "must_be_filled"        => $_POST['must_be_filled'],
                "topic_html"            => $_POST['topic_html']
        );

        // **********************
        // Check there's something in the name
        // **********************
        if(trim($field_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_field_no_name']));
                page_add_edit_field(true, $field_info);
                return;
        }               

        // **********************
        // Add it!
        // **********************
        if(!$db -> basic_insert("profile_fields", $field_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_field_error']));
                page_add_edit_field(true, $field_info);
                return;
        }               

        // **********************
        // Sort out database
        // **********************
        $new_id = $db -> insert_id();
        $db -> query("ALTER TABLE ".$db -> table_prefix."profile_fields_data ADD field_".$new_id." text default NULL");

        // **********************
        // Update cache
        // **********************
        $cache -> update_cache("profile_fields");

        // **********************
        // Log it!
        // **********************
        log_admin_action("profilefields", "doadd", "Added field: ".$field_info['name']);
        
        // **********************
        // Done
        // **********************
        $output -> redirect(ROOT."admin/index.php?m=profilefields", $lang['field_created_sucessfully']);
                
}



//***********************************************
// Submit an edit
//***********************************************
function do_edit_field()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Grab the field
        // **********************
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_field_id']));
                page_main();
                return;
        }
                
        // Grab wanted field
        $field = $db -> query("select * from ".$db -> table_prefix."profile_fields where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($field) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_field_id']));
                page_main();
                return;
        }


        // **********************
        // Get stuff from the post
        // **********************
        if($_POST['dropdown_values'])
                $ddv = str_replace("\n", "|", str_replace("\n\n","\n",trim($_POST['dropdown_values'])));
        if($_POST['dropdown_text'])
                $ddt = str_replace("\n", "|", str_replace("\n\n","\n",trim($_POST['dropdown_text'])));

        $field_info = array(
                "name"                  => $_POST['name'],
                "description"           => $_POST['description'],
                "field_type"            => $_POST['field_type'],
                "size"                  => $_POST['size'],
                "max_length"            => $_POST['max_length'],
                "order"                 => $_POST['order'],
                "dropdown_values"       => $ddv,
                "dropdown_text"         => $ddt,
                "show_on_reg"           => $_POST['show_on_reg'],
                "user_can_edit"         => $_POST['user_can_edit'],
                "is_private"            => $_POST['is_private'],
                "admin_only_field"      => $_POST['admin_only_field'],
                "must_be_filled"        => $_POST['must_be_filled'],
                "topic_html"            => $_POST['topic_html']
        );

        // **********************
        // Check there's something in the name
        // **********************
        if(trim($field_info['name']) == "")
        {
                $output -> add($template_admin -> normal_error($lang['add_field_no_name']));
                page_add_edit_field(false, $field_info);
                return;
        }               


        // **********************
        // Do the query
        // **********************
        if(!$db -> basic_update("profile_fields", $field_info, "id='".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['error_editing_field']));
                page_main();
                return;
        }

        // **********************
        // Update cache
        // **********************
        $cache -> update_cache("profile_fields");

        // **********************
        // Log it!
        // **********************
        log_admin_action("profilefields", "doedit", "Edited field: ".$field_info['name']);
        
        // **********************
        // Done
        // **********************
        $output -> redirect(ROOT."admin/index.php?m=profilefields", $lang['field_edited_sucessfully']);

}


//***********************************************
// Delete a field
//***********************************************
function do_delete_field()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **********************
        // Grab the field
        // **********************
        $get_id = trim($_GET['id']);

        // No ID
        if($get_id == '')
        {
                $output -> add($template_admin -> critical_error($lang['invalid_field_id']));
                page_main();
                return;
        }
                
        // Grab wanted field
        $field = $db -> query("select id,name from ".$db -> table_prefix."profile_fields where id='".$get_id."'");

        // Die if it doesn't exist
        if($db -> num_rows($field) == 0)
        {
                $output -> add($template_admin -> critical_error($lang['invalid_field_id']));
                page_main();
                return;
        }

        $field_info = $db -> fetch_array($field);

        // ********************
        // Delete it
        // ********************
        if(!$db -> basic_delete("profile_fields", "id='".$get_id."'"))
        {
                $output -> add($template_admin -> critical_error($lang['field_delete_fail']));
                page_main();
                return;
        }
        

        // ********************
        // Sort out database
        // ********************
        if(!$db -> query("ALTER TABLE ".$db -> table_prefix."profile_fields_data DROP field_".$get_id))
        {
                $output -> add($template_admin -> critical_error($lang['field_delete_database_fail']));
                page_main();
                return;
        }


        // **********************
        // Update cache
        // **********************
        $cache -> update_cache("profile_fields");

        // **********************
        // Log it!
        // **********************
        log_admin_action("profilefields", "delete", "Deleted field: ".$field_info['name']);
        
        // **********************
        // Done
        // **********************
        $output -> redirect(ROOT."admin/index.php?m=profilefields", $lang['field_deleted_sucessfully']);

}

?>
