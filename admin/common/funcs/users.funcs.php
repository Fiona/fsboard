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
 * Admin user functions
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 *
 * @started 01 Jun 2007
 * @edited 01 Jun 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



/**
 * Takes field values and chucks it all in a handy query
 * string for use when searching for users.
 *
 * @param array $search_info Stuff from the form to search.
 * @param array $extra Any other entries that need putting into the string.
 * @return string Finished string to be inserted into a query
 */
function create_user_search_string($search_info, $extra = "")
{

        global $db;

        $query = array();
        
        if($extra)
                $query = $extra;

        // Username
        if($search_info['username'])
        {
                switch($search_info['username_search'])
                {
                        case "1":
                                $query[] = "u.`username` = '".$search_info['username']."'";
                                break;
                        case "2":
                                $query[] = "u.`username` LIKE '%".$search_info['username']."'";
                                break;
                        case "3":
                                $query[] = "u.`username` LIKE '".$search_info['username']."%'";
                                break;
                        default:
                                $query[] = "u.`username` LIKE '%".$search_info['username']."%'";
                }
        }

        // E-mail
        if($search_info['email'])
                $query[] = "u.`email` LIKE '%".$search_info['email']."%'";

        // User group
        $db -> basic_select("user_groups", "id,name");

        while($g_array = $db -> fetch_array())
                 $user_group_array[$g_array['id']] = $g_array['name'];

        if($search_info['usergroup'])
                if($user_group_array[$search_info['usergroup']])
                        $query[] = "u.`user_group` = '".$search_info['usergroup']."' ";

        // Secondary user group
        if(is_array($search_info['usergroup_secondary']) && count($search_info['usergroup_secondary']) > 0)
        {
                foreach($search_info['usergroup_secondary'] as $key => $val)
                        if($user_group_array[$key])
                                       $query[] = "find_in_set('".$key."', `secondary_user_group`) ";
        }

        // Title
        if($search_info['title'])
                $query[] = "u.`title` LIKE '%".$search_info['title']."%'";

        // Signature
        if($search_info['signature'])
                $query[] = "u.`signature` LIKE '%".$search_info['signature']."%'";

        // Homepage
        if($search_info['homepage'])
                $query[] = "u.`homepage` LIKE '%".$search_info['homepage']."%'";

        // Posts
        if($search_info['posts_g'])
                $query[] = "u.`posts` > ".$search_info['posts_g'];

        if($search_info['posts_l'])
                $query[] = "u.`posts` < ".$search_info['posts_g'];

        // Registered date
        list($day, $month, $year) = explode("-", $search_info['register_b']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`registered` < ".mktime(0, 0, 0, $month, $day, $year);

        list($day, $month, $year) = explode("-", $search_info['register_a']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`registered` > ".mktime(0, 0, 0, $month, $day, $year);

        // Last active date
        list($day, $month, $year) = explode("-", $search_info['last_active_b']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_active` < ".mktime(0, 0, 0, $month, $day, $year);

        list($day, $month, $year) = explode("-", $search_info['last_active_a']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_active` > ".mktime(0, 0, 0, $month, $day, $year);

        // Last post date
        list($day, $month, $year) = explode("-", $search_info['last_post_b']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_post_time` < ".mktime(0, 0, 0, $month, $day, $year);

        list($day, $month, $year) = explode("-", $search_info['last_post_a']);
        if($day && $month && $year)
                if(checkdate($month, $day, $year))
                        $query[] = "u.`last_post_time` > ".mktime(0, 0, 0, $month, $day, $year);


        // *****************************
        // Custom profile fields! :D
        // *****************************
        $db -> basic_select("profile_fields", "id");

        while($p_array = $db -> fetch_array())
                if($_POST['field_'.$p_array['id']])
                        $query[] = "p.`field_".$p_array['id']."` LIKE '%".$_POST['field_'.$p_array['id']]."%'";

        // *****************************
        // Finish and send back
        // *****************************
        $query_string = "";
        
        if(count($query) > 0)
                $query_string = " WHERE ".implode(" AND ", $query);

        return $query_string;
        
}
