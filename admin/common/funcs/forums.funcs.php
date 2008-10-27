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
*       Admin Forums            *
*       Started by Fiona        *
*       08th Feb 2006           *
*********************************
*       Last edit by Fiona      *
*       08th Feb 2006           *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");




//***********************************************
// return_help_nodes_dropdown()
// Returns an array for use on the admin forum page and dropdown
//
// Params  - $forums = Pre-selected forum query
// Returns - Array
//***********************************************
function return_admin_forums_tree(&$forums)
{

        global $db;
                                              
        while($field_row = $db -> fetch_array($forums))
        {
                $forums_tree[$field_row['id']]['parent_id'] = $field_row['parent_id'];
                $forums_tree[$field_row['id']]['name'] = $field_row['name'];
                $forums_tree[$field_row['id']]['position'] = $field_row['position'];
                $forums_tree[$field_row['id']]['id'] = $field_row['id'];
                $forums_tree[$field_row['id']]['is_category'] = $field_row['is_category'];

                $forums_tree[$field_row['parent_id']]['children'][] = $field_row['id'];
        }

        return $forums_tree;

}


//***********************************************
// print_admin_forums_layout()
// Lots of thanks to Mark for the bitflags crap
//***********************************************
function print_admin_forums_layout($id_want, &$forums_tree, $table, $form, $v_flags, $depth, $groups_array, $forums_perms_array)
{

        global $output, $lang;
                
        $a = 1;

        // ***********************
        // No kids? Nothing to print.
        // ***********************
        if($forums_tree[$id_want]['children'] == null)
                return 0;

        // ***********************
        // Need a branch for each child
        // ***********************
        foreach($forums_tree[$id_want]['children'] as $val)
        {


                // ***********************
                // Reset the output html
                // ***********************
                $option_string = "";

                // Are we the last child?
                if($a == count($forums_tree[$id_want]['children']))
                        $last_child = true;
                else
                        $last_child = false;

                // Go through each branch iteration up to now
                for($i=0; $i<$depth; $i++)
                {

                        // Print the right images. Bitwise operators are O_o
                        if($v_flags  & (1<<$i))
                                $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_vline.gif\">";
                        else
                                $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_spacer.gif\">";
                
                }

                // Save for next time
                $new_flagset = $v_flags;

                // Last one? Post the last branch image
                if($last_child)
                        $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_lastbranch.gif\">";
                else
                {
                        // Not last child, post normal image
                        $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_branch.gif\">";
                        $new_flagset |= (1<<$depth);
                }

                // ***********************
                // Finally save it
                // ***********************
                if($forums_tree[$val]['is_category'])
                        $name = "<b>".$forums_tree[$val]['name']."</b>";
                else
                        $name = $forums_tree[$val]['name'];

                // ***********************
                // Build group dropdown!
                // ***********************
                $perms_dropdown_values = array("delete", "noall");
                $perms_dropdown_text = array($lang['forums_perms_menu_delete'], $lang['forums_perms_menu_noall']);

                // Go through each group
                foreach($groups_array as $group_id => $group_name)
                {
                
                        $permed = false;

                        // Check if there is a permission  associated with it
                        // and mark it with an asterisk if so
                        foreach($forums_perms_array as $perm)
                                if($perm['group_id'] == $group_id && $perm['forum_id'] == $val)
                                {
                                        $perms_dropdown_text[] .= "* ".$group_name;
                                        $permed = true;
                                        break;
                                }
                                else
                                        continue;
                                
                        $perms_dropdown_values[] .= $group_id;

                        // Didn't find a permission? Just do it normally                        
                        if(!$permed)                        
                                $perms_dropdown_text[] .= $group_name;
                                
                }

                // ***********************
                // MESSY ALERT                        
                // ***********************
                $output -> add(
                        $table -> add_row(
                                array(
                                        // Name
                                        array(
                                                $option_string.$name,
                                                "30%"
                                        )
                                        // Actions
                                        ,array(
                                                $form -> input_dropdown("forum_".$val."_action", "edit", 
                                                        array(
                                                                "edit",
                                                                "add_child",
                                                                "view",
                                                                "delete"
                                                        ),
                                                        array(
                                                                $lang['forum_action_edit'],
                                                                $lang['forum_action_add_child'],
                                                                $lang['forum_action_view'],
                                                                $lang['forum_action_delete']
                                                        ),
                                                        "inputtext", "auto", "onchange=\"do_forum_action(".$val.");\""
                                                )
                                                .$form -> button("actiongo", "Go", "submitbutton", "onclick=\"do_forum_action(".$val.");\"")
                                        , "30%")
                                        // Perms
                                        ,array(
                                                $form -> input_dropdown("forum_".$val."_perms", "edit", 
                                                        $perms_dropdown_values,
                                                        $perms_dropdown_text,
                                                        "inputtext", "auto", "onchange=\"do_forum_perms(".$val.");\""
                                                )
                                                .$form -> button("permsgo", "Go", "submitbutton", "onclick=\"do_forum_perms(".$val.");\"")
                                        , "30%")                                        
                                        // Position
                                        ,array(
                                                $form -> input_int("position[".$val."]", $forums_tree[$val]['position'])
                                        ,"10%")
                                )
                                ,"normalcell"
                        )
                );

                // ***********************
                // Recursive attack
                // ***********************
                print_admin_forums_layout($val, $forums_tree, $table, $form, $new_flagset, $depth+1, $groups_array, $forums_perms_array);

                $a++;

        }
        
}




// ------------------------------------------------------------------------



//***********************************************
// print_admin_moderators_layout()
//***********************************************
function print_admin_moderators_layout($id_want, &$forums_tree, $table, $form, $v_flags, $depth, $groups_array)
{

        global $output, $lang, $db;
                
        $a = 1;

        if($forums_tree[$id_want]['children'] == null)
                return 0;

        foreach($forums_tree[$id_want]['children'] as $val)
        {

                $option_string = "";

                if($a == count($forums_tree[$id_want]['children']))
                        $last_child = true;
                else
                        $last_child = false;

                // ************************
                // Work out wanted arrows
                // ************************
                for($i=0; $i<$depth; $i++)
                {

                        if($v_flags  & (1<<$i))
                                $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_vline.gif\">";
                        else
                                $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_spacer.gif\">";
                
                }

                $new_flagset = $v_flags;

                if($last_child)
                        $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_lastbranch.gif\">";
                else
                {
                        $option_string .= "<img style=\"vertical-align : middle;\" src=\"".IMGDIR."/admin_tree_branch.gif\">";
                        $new_flagset |= (1<<$depth);
                }

                // ************************
                // Finally print
                // ************************
                if($forums_tree[$val]['is_category'])
                        $name = "<b>".$forums_tree[$val]['name']."</b>";
                else
                        $name = $forums_tree[$val]['name'];

                // ************************
                // Build group dropdown!
                // ************************
                $perms_dropdown_values = array("delete", "noall");
                $perms_dropdown_text = array($lang['forums_perms_menu_delete'], $lang['forums_perms_menu_noall']);

                foreach($groups_array as $gid => $gname)
                {
                        $perms_dropdown_values[] .= $gid;
                        $perms_dropdown_text[] .= $gname;
                }

                // ************************
                // Generate moderator links
                // ************************
                // Select the mods table
                $select_mods_table = $db -> query("select id,group_id,group_name,user_id,username from ".$db -> table_prefix."moderators where forum_id='".$val."'");

                $mods = "";
                
                if($db -> num_rows($select_mods_table) < 1)                        
                        $mods = $lang['mod_main_none'];
                else
                {                        

                        $a = 0;
                        
                        // Go through all the rows
                        while($mod_row = $db -> fetch_array($select_mods_table))
                        {

                                $a ++;

                                $mods .= "<a href=\"index.php?m=moderators&amp;m2=edit&amp;id=".$mod_row['id']."\" title=\"".$lang['edit_moderator']."\">
                                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-edit.png\"></a>";
                                $mods .= " <a href=\"index.php?m=moderators&amp;m2=dodelete&amp;id=".$mod_row['id']."\" title=\"".$lang['delete_moderator']."\">
                                        <img border=\"0\" style=\"vertical-align:bottom;\" src=\"".IMGDIR."/button-delete.png\"></a> ";
                                
                                if($mod_row['group_id'] > "-1")
                                {
                                        $title = $lang['mod_main_group_name'];
                                        $mods .= $output -> replace_number_tags($title, array($mod_row['group_name']));
                                }
                                else
                                {
                                        $title = $lang['mod_main_user_name'];
                                        $mods .= $output -> replace_number_tags($title, array($mod_row['username']));
                                }
                                                                
                                if($a != $db -> num_rows($select_mods_table))
                                        $mods .= "<br />";
                                        
                        }
                        
                }
                
                // ************************
                // Produce the roow
                // ************************
                $output -> add(
                        $table -> add_row(
                                array(
                                        // Check box
                                        array(
                                                $form -> input_checkbox("forums[".$forums_tree[$val]['id']."]", "0"),
                                                "4%"
                                        ),
                                        // Name
                                        array(
                                                $option_string.$name,
                                                "48%"
                                        ),
                                        // Position
                                        array(
                                                $mods,
                                                "48%"
                                        )
                                )
                                ,"normalcell"
                        )
                );

                // ************************
                // Recursive attack
                // ************************
                print_admin_moderators_layout($val, $forums_tree, $table, $form, $new_flagset, $depth+1, $groups_array);

                $a++;

        }
        
}



// ------------------------------------------------------------------------



// ---------------------------------------------------
// return_help_nodes_dropdown()
// Returns two arrays for use in the dropdown form class
//
// Params  - stuff
// Returns - Two arrays with keys 'values' and 'text'
// ---------------------------------------------------
function return_admin_forums_dropdown(&$forums_tree, $root_name = "Root")
{

        // Init arrays
        $return['values'] = array();
        $return['text'] = array();

        $return['text'][] = $root_name;
        $return['values'][] = "-1";
        
        real_return_admin_forums_dropdown(-1, $forums_tree, $return, 0, 0);       
                        
        return $return;
        
}

// ---------------------------------------------------
// real_return_admin_forums_dropdown()
// ---------------------------------------------------
function real_return_admin_forums_dropdown($id_want, &$forums_tree, &$return, $v_flags, $depth)
{

        $a = 1;

        if($forums_tree[$id_want]['children'] == null)
                return 0;
                
        foreach($forums_tree[$id_want]['children'] as $val)
        {

                $option_string = "";

                if($a == count($forums_tree[$id_want]['children']))
                        $last_child = true;
                else
                        $last_child = false;
              
                for($i=0; $i<$depth; $i++)
                {

                        if($v_flags  & (1<<$i))
                                $option_string .= "|";
                        else
                                $option_string .= "&nbsp;";

                        $option_string .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                
                }

                $new_flagset = $v_flags;

                if($last_child)
                        $option_string .= "'-";
                else
                {
                        $option_string .= "|-";
                        $new_flagset |= (1<<$depth);
                }
        
                $return['text'][] = $option_string." ".$forums_tree[$val]['name'];
                $return['values'][] = $val;

                real_return_admin_forums_dropdown($val, $forums_tree, $return, $new_flagset, $depth+1);

                $a++;

        }
                
}



// -------------------------------------------------------------------------------



function recursive_delete_forum_children($node, $forums_tree)
{
        global $db;

        // Kill forum
        save_undelete_data("forums", "Deleting forum id ".$node, "id = ".(int)$node);
        $db -> query("DELETE FROM ".$db -> table_prefix."forums WHERE id=".(int)$node);

        // Remove topics
        // --

        // Remove posts
        // --

        // Remove permissions
        save_undelete_data("forums_perms", "Deleting perms from forum id ".$node, "forum_id = ".(int)$node);
        $db -> query("DELETE FROM ".$db -> table_prefix."forums_perms WHERE forum_id=".(int)$node);
        
        // Remove moderators
        save_undelete_data("moderators", "Deleting mods from forum id ".$node, "forum_id = ".(int)$node);
        $db -> query("DELETE FROM ".$db -> table_prefix."moderators WHERE forum_id=".(int)$node);
        
        // Finished?
        if($forums_tree[$node]['children'] == null)
                return 0;
        
        // Do the kids (So te speak)
        foreach($forums_tree[$node]['children'] as $val)
                recursive_delete_forum_children($val, $forums_tree);
                
}



// -------------------------------------------------------------------------------



function check_perm_same($perms_array, $perms_array_b)
{

        global $db;

        foreach($perms_array as $key => $val)
        {
        
                if($perms_array_b[$key] != $val)
                        return false;
        
        }

        return true;
        
}



// -------------------------------------------------------------------------------



function perms_copy_to_children($group_id, $forum_id, $perms_info)
{

        global $db;

        // Grab all forums
        $forums = $db -> query("select id, parent_id from ".$db -> table_prefix."forums");

        // Sort it out
        $forums_tree = return_admin_forums_tree($forums);

        recursive_perms_copy_to_children($group_id, $forum_id, $perms_info, $forums_tree);        
        
}

function recursive_perms_copy_to_children($group_id, $forum_id, $perms_info, &$forums_tree)
{

        global $db;
        
        if($forums_tree[$forum_id]['children'] == null)
                return true;

        foreach($forums_tree[$forum_id]['children'] as $val)
        {

                $perms_info["group_id"] = $group_id;
                $perms_info["forum_id"] = $val;

                // Perm row exist?
                if($db -> num_rows($db->query("select id from ".$db -> table_prefix."forums_perms WHERE forum_id='".$val."' and group_id='".$group_id."'")) < 1)
                        $db -> basic_insert("forums_perms", $perms_info);

                recursive_perms_copy_to_children($group_id, $val, $perms_info, $forums_tree);

        }

}

?>
