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
 * Promotions
 * 
 * Admin page for editing automatic user group
 * promotions.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 * 
 * @started 30 Jan 2007
 * @edited 06 Feb 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// I have no mouth but I must scream
//***********************************************
load_language_group("admin_promotions");


$output -> add_breadcrumb($lang['breadcrumb_promotions'], "index.php?m=promotions");

$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{

        case "add":
                page_add_edit_promotions(true);
                break;

        case "doadd":
                do_add_promotions();
                break;
               
        case "edit":
                page_add_edit_promotions();
                break;

        case "doedit":
                do_edit_promotions();
                break;
               
        case "delete":
                do_delete_promotions();
                break;
               
        default:
                page_main();

}


/**
 * The main view of the promotions manager.
 */
function page_main()
{

        global $lang, $output, $db;
        
        // *********************
        // Set page title
        // *********************
        $output -> page_title = $lang['promotions_main_title'];

        // ********************
        // Start table
        // ********************
        // Create class
        $table = new table_generate;
        $form = new form_generate;

        $output -> add(
                $form -> start_form("dummyform", "", "post").
                $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").

                $table -> add_basic_row($lang['promotions_main_title'], "strip1",  "", "left", "100%", "7").
                $table -> add_basic_row($lang['promotions_main_message'], "normalcell",  "padding : 5px", "left", "100%", "7").
                
                $table -> add_row(
                        array(
                                array($lang['promotions_group_from'], "auto"),
                                array($lang['promotions_group_to'], "auto"),
                                array($lang['promotions_type'], "auto"),
                                array($lang['promotions_posts'], "auto"),
                                array($lang['promotions_reputation'], "auto"),
                                array($lang['promotions_days_registered'], "auto"),
                                array($lang['promotions_actions'], "auto")
                        )
                , "strip2")
        );

        // ********************
        // Grab all promotions
        // ********************
        $promotion_select = $db -> basic_select("promotions", "*", "", "group_id", "", "asc");

        // No titles?
        if($db -> num_rows() < 1)
               $output -> add(
                        $table -> add_basic_row("<b>".$lang['no_promotions']."</b>", "normalcell",  "padding : 10px")
                );        
                
        else
        {

		// User group names
		$group_select = $db -> basic_select("user_groups", "id, name");
		
		while($group = $db -> fetch_array($group_select))
			$group_array[$group['id']] = $group['name'];

		// Promotion type dropdown
		for($a = 0; $a <= 1; $a++)
			$promotion_type_text[$a] = $lang['promotions_main_promotion_type_'.$a];

		// Ticks
		$use_pics[0] = "";
		$use_pics[1] = "<img style=\"vertical-align:bottom;\" src=\"".IMGDIR."/tick.png\"> ";

		// Loopy
                while($p_array = $db-> fetch_array($promotion_select))
                {

                        $actions = "
                        <a href=\"".ROOT."admin/index.php?m=promotions&amp;m2=edit&amp;id=".$p_array['id']."\" title=\"".$lang['promotions_main_edit']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>
                        <a href=\"".ROOT."admin/index.php?m=promotions&amp;m2=delete&amp;id=".$p_array['id']."\" onclick=\"return confirm('".$lang['delete_promotions_confirm']."')\" title=\"".$lang['promotions_main_delete']."\">
                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a>";

			$output -> add(
				$table -> add_row(
				        array(
				                array($group_array[$p_array['group_id']], "auto"),
				                array($group_array[$p_array['group_to_id']], "auto"),
				                array($promotion_type_text[$p_array['promotion_type']], "auto"),
				                array($use_pics[$p_array['use_posts']] . $p_array['posts'], "auto"),
				                array($use_pics[$p_array['use_reputation']] . $p_array['reputation'], "auto"),
				                array($use_pics[$p_array['use_days_registered']] . $p_array['days_registered'], "auto"),
				                array($actions, "auto")
				        )
				, "normalcell")
			);

                }
                
        }

        // ********************
        // End table
        // ********************
        $output -> add(
                $table -> add_basic_row(
                        $form -> button("addpromotion", $lang['add_promotions_button'], "submitbutton", "onclick=\"return window.location = '".ROOT."admin/index.php?m=promotions&m2=add';\"")
                , "strip3").
                $table -> end_table().
                $form -> end_form()
        );  
                
}


/**
 * Page contaninig the firm for adding or editing promotions
 * 
 * @param bool $adding If set to true will display the adding form instead of editing
 * @param array $reputations_info Array of already input values
 */
function page_add_edit_promotions($adding = false, $promotions_info = "")
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
                $output -> page_title = $lang['add_promotions_title'];

		$output -> add_breadcrumb($lang['breadcrumb_promotions_add'], "index.php?m=promotions&m2=add");

                $output -> add(
                        $form -> start_form("addpromotions", ROOT."admin/index.php?m=promotions&amp;m2=doadd", "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['add_promotions_title'], "strip1",  "", "left", "100%", "2").
                        $table -> add_basic_row($lang['add_promotions_message'], "normalcell",  "padding : 5px", "left", "100%", "2")
                );

                $submit_lang = $lang['add_promotions_submit'];

        }
        else
        {

	        // **************************
	        // Grab the promotion
	        // **************************
	        $get_id = trim($_GET['id']);
	
	        if(!$db -> query_check_id_rows("promotions", $get_id, "*"))
	        {
	                $output -> add($template_admin -> critical_error($lang['edit_promotions_invalid_id']));
	                page_main();
	                return;
	        }
	  
	        if(!$promotions_info)
	                $promotions_info = $db -> fetch_array();

                // *********************
                // Set page title
                // *********************
                $output -> page_title = $lang['edit_promotions_title'];

		$output -> add_breadcrumb($lang['breadcrumb_promotions_edit'], "index.php?m=promotions&m2=edit&amp;id=".$get_id);

                $output -> add(
                        $form -> start_form("editpromotions", ROOT."admin/index.php?m=promotions&amp;m2=doedit&amp;id=".$get_id, "post").
                        $table -> start_table("", "margin-top : 10px; border-collapse : collapse;").
                        $table -> add_basic_row($lang['edit_promotions_title'], "strip1",  "", "left", "100%", "2")
                );

                $submit_lang = $lang['edit_promotions_submit'];

        	
        }

	// Fetch user groups for dropdowns
	$groups_array = array();
	
	$db -> basic_select("user_groups", "id, name", "", "id");
	
	while($g = $db -> fetch_array())
	{
		$group_dropdown_values[] = $g['id'];
		$group_dropdown_text[] = $g['name'];
	}

	// Promotion type dropdown
	for($a = 0; $a <= 1; $a++)
	{
		$promotion_type_dropdown_values[] = $a;
		$promotion_type_dropdown_text[] = $lang['add_promotions_promotion_type_'.$a];
	}

	// Reputation comparison type dropdown
	for($a = 0; $a <= 1; $a++)
	{
		$comparison_dropdown_values[] = $a;
		$comparison_dropdown_text[] = $lang['add_promotions_reputation_comparison_'.$a];
	}

        // ***************************
        // Print the form
        // ***************************
        $output -> add(
		$table -> simple_input_row_dropdown($form, $lang['add_promotions_group_id'], "group_id", $promotions_info['group_id'], $group_dropdown_values, $group_dropdown_text).
		$table -> simple_input_row_dropdown($form, $lang['add_promotions_promotion_type'], "promotion_type", $promotions_info['promotion_type'], $promotion_type_dropdown_values, $promotion_type_dropdown_text).
		$table -> simple_input_row_dropdown($form, $lang['add_promotions_group_to_id'], "group_to_id", $promotions_info['group_to_id'], $group_dropdown_values, $group_dropdown_text).

		$table -> add_row(
			array(
				$lang['add_promotions_posts']."<br /><font class=\"small_text\">".$lang['add_promotions_posts_message']."</font>",
				$form -> input_checkbox("use_posts", "1", "inputtext", $promotions_info['use_posts'])." ".
				$form -> input_int("posts", $promotions_info['posts'])
			),
			"normalcell"
		).
		$table -> add_row(
			array(
				$lang['add_promotions_reputation']."<br /><font class=\"small_text\">".$lang['add_promotions_reputation_message']."</font>",
				$form -> input_checkbox("use_reputation", "1", "inputtext", $promotions_info['use_reputation'])." ".
				$form -> input_int("reputation", $promotions_info['reputation'])
			),
			"normalcell"
		).
		$table -> simple_input_row_dropdown($form, $lang['add_promotions_reputation_comparison'], "reputation_comparison", $promotions_info['reputation_comparison'], $comparison_dropdown_values, $comparison_dropdown_text).
		$table -> add_row(
			array(
				$lang['add_promotions_days_registered']."<br /><font class=\"small_text\">".$lang['add_promotions_days_registered_message']."</font>",
				$form -> input_checkbox("use_days_registered", "1", "inputtext", $promotions_info['use_days_registered'])." ".
				$form -> input_int("days_registered", $promotions_info['days_registered'])
			),
			"normalcell"
		).

                $table -> add_submit_row($form, "submit", $submit_lang).
                $table -> end_table().
                $form -> end_form()
        );   
                
}        


/**
 * Taking input and adding the promotion
 */
function do_add_promotions()
{

        global $output, $lang, $db, $template_admin;

        // **********************
        // Get stuff from the post
        // **********************
        $promotions_info = array(
                "group_id"		=> $_POST['group_id'],
                "promotion_type"	=> $_POST['promotion_type'],
                "group_to_id"		=> $_POST['group_to_id'],
                "use_posts"		=> $_POST['use_posts'],
                "posts"			=> intval($_POST['posts']),
                "use_reputation"	=> $_POST['use_reputation'],
                "reputation"		=> intval($_POST['reputation']),
                "reputation_comparison"	=> $_POST['reputation_comparison'],
                "use_days_registered"	=> $_POST['use_days_registered'],
                "days_registered"	=> intval($_POST['days_registered'])
        );

        // **********************
	// User groups the same lmbo
        // **********************
        if($promotions_info['group_id'] == $promotions_info['group_to_id'])
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_same_group']));
		page_add_edit_promotions(true, $promotions_info);        	
		return;        	
        }

        // **********************
	// Nothing selected
        // **********************
        if(!$promotions_info['use_posts'] && !$promotions_info['use_reputation'] && !$promotions_info['use_days_registered'])
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_no_rules']));
		page_add_edit_promotions(true, $promotions_info);        	
		return;        	
        }

        // **********************
	// Check group moving from exists
        // **********************
	if($db -> query_check_id_rows("user_groups", $promotions_info['group_id'], "name") < 1)
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_group_id_no_exist']));
		page_add_edit_promotions(true, $promotions_info);        	
		return;        	
        }
        
        $group_from = $db -> fetch_array();

        // **********************
	// Check group moving to exists
        // **********************
	if($db -> query_check_id_rows("user_groups", $promotions_info['group_to_id'], "name") < 1)
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_group_to_id_no_exist']));
		page_add_edit_promotions(true, $promotions_info);        	
		return;        	
        }
        
        $group_to = $db -> fetch_array();

        // ***************************
        // Add it!
        // ***************************
        if(!$db -> basic_insert("promotions", $promotions_info))
        {
                $output -> add($template_admin -> critical_error($lang['add_promotions_insert_error']));
                page_add_edit_promotions(true, $promotions_info);
                return;
        }    
        
        // ***************************
        // Log it!
        // ***************************
        log_admin_action("promotions", "doadd", "Added promotion: ".$group_from['name']." to ".$group_to['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=promotions", $lang['add_promotions_created_successfully']);
        
}



/**
 * Taking input and editing
 */
function do_edit_promotions()
{

        global $output, $lang, $db, $template_admin;

        // **************************
        // Grab the promotion
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("promotions", $get_id, "*"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_promotions_invalid_id']));
		page_main();
                return;
        }
	        
        // **********************
        // Get stuff from the post
        // **********************
        $promotions_info = array(
                "group_id"		=> $_POST['group_id'],
                "promotion_type"	=> $_POST['promotion_type'],
                "group_to_id"		=> $_POST['group_to_id'],
                "use_posts"		=> $_POST['use_posts'],
                "posts"			=> intval($_POST['posts']),
                "use_reputation"	=> $_POST['use_reputation'],
                "reputation"		=> intval($_POST['reputation']),
                "reputation_comparison"	=> $_POST['reputation_comparison'],
                "use_days_registered"	=> $_POST['use_days_registered'],
                "days_registered"	=> intval($_POST['days_registered'])
        );

        // **********************
	// User groups the same lmbo
        // **********************
        if($promotions_info['group_id'] == $promotions_info['group_to_id'])
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_same_group']));
		page_add_edit_promotions(false, $promotions_info);        	
		return;        	
        }

        // **********************
	// Nothing selected
        // **********************
        if(!$promotions_info['use_posts'] && !$promotions_info['use_reputation'] && !$promotions_info['use_days_registered'])
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_no_rules']));
		page_add_edit_promotions(false, $promotions_info);        	
		return;        	
        }

        // **********************
	// Check group moving from exists
        // **********************
	if($db -> query_check_id_rows("user_groups", $promotions_info['group_id'], "name") < 1)
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_group_id_no_exist']));
		page_add_edit_promotions(false, $promotions_info);        	
		return;        	
        }
        
        $group_from = $db -> fetch_array();

        // **********************
	// Check group moving to exists
        // **********************
	if($db -> query_check_id_rows("user_groups", $promotions_info['group_to_id'], "name") < 1)
        {
                $output -> add($template_admin -> normal_error($lang['add_promotions_group_to_id_no_exist']));
		page_add_edit_promotions(false, $promotions_info);        	
		return;        	
        }
        
        $group_to = $db -> fetch_array();

        // *********************
        // Do the query
        // *********************
        if(!$db -> basic_update("promotions", $promotions_info, "id = '".$get_id."'"))        
        {
                $output -> add($template_admin -> critical_error($lang['edit_promotions_error_editing']));
                page_main();
                return;
        }

        // ***************************
        // Log it!
        // ***************************
        log_admin_action("promotions", "doedit", "Edited promotion: ".$group_from['name']." to ".$group_to['name']);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=promotions", $lang['edit_promotions_edited_successfully']);

}


/**
 * Deleting a promotion
 */
function do_delete_promotions()
{

        global $output, $lang, $db, $template_admin, $cache;

        // **************************
        // Grab the promotion
        // **************************
        $get_id = trim($_GET['id']);

        if(!$db -> query_check_id_rows("promotions", $get_id, "*"))
        {
                $output -> add($template_admin -> critical_error($lang['edit_promotions_invalid_id']));
		page_main();
                return;
        }
        
        $promotions_info = $db -> fetch_array();

	// Fetch user groups
	$groups_array = array();
	
	$db -> basic_select("user_groups", "id, name", "", "id");
	
	while($g = $db -> fetch_array())
	{
		if($promotions_info['group_id'] == $g['id'])
			$group_from = $g['name'];
						
		if($promotions_info['group_to_id'] == $g['id'])
			$group_to = $g['name'];			
	}


        // ********************
        // Delete it
        // ********************
        $db -> basic_delete("promotions", "id = '".$get_id."'");

        // ***************************
        // Log it!
        // ***************************
        log_admin_action("promotions", "delete", "Deleted promotion: ".$group_from." to ".$group_to);

        // ***************************
        // Done
        // ***************************
        $output -> redirect(ROOT."admin/index.php?m=promotions", $lang['delete_promotions_deleted_successfully']);

}  

?>