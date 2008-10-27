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
 * Special MySQL Queries
 * 
 * This is a file for a bunch of queries which
 * are beyond the scope of the basic database 
 * functions.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Database
 * 
 * @started 08 Feb 2007
 * @edited 29 Apr 2007
 */



// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



/**
 * Class encapsulates some functions that just
 * return some extended SQL as a string.
 */
class db_special_queries
{
        
        /**
         * The automatic promotions common task main query.
         */
        function query_task_promotions()
        {
                
                global $db;
                
                return "SELECT user.id as user_id, user.username as username, user.user_group as user_user_group,
                        user.secondary_user_group as user_secondary_user_group, user.registered as user_register_date,
                        user.posts as user_posts, user.reputation as user_reputation,
                        promotions.group_to_id as promotion_group_to_id, promotions.promotion_type as promotion_type,
                        promotions.use_posts as promotion_use_posts, promotions.use_reputation as promotion_use_reputation,
                        promotions.use_days_registered as promotion_use_days_registered,   
                        promotions.posts as promotion_posts, promotions.reputation as promotion_reputation,
                        promotions.days_registered as promotion_days_registered,
                        promotions.reputation_comparison as promotion_reputation_comparison                                        
                        FROM ".$db -> table_prefix."users as user
                        LEFT JOIN ".$db -> table_prefix."promotions as promotions ON(user.user_group = promotions.group_id)
                        LEFT JOIN ".$db -> table_prefix."user_groups as user_groups ON(user_groups.id = promotions.group_to_id)
                        WHERE user.last_active > ".(TIME - (60*60*24*7));
                
        }


        /**
         * The automatic promotions common task, updates the secondary user groups.
         */
        function query_task_promotions_update_secondary($group_to_id, $user_ids)
        {        
                
                global $db;
                
                return "UPDATE ".$db -> table_prefix."users
                        SET secondary_user_group = IF(secondary_user_group = '', '".$group_to_id."', CONCAT(secondary_user_group, ',".$group_to_id."'))
                        WHERE id IN(".implode(",",$user_ids).")";
                
        }


        /**
         * Getting the user view profile page info.
         */
        function query_view_profile($id)
        {
                global $db;
                
                return "SELECT u.id, u.username, u.user_group, u.secondary_user_group, u.real_name, u.posts,
				u.avatar_type, u.avatar_address, u.avatar_address, u.title, u.signature,
				u.email, u.hide_email, u.homepage, u.yahoo_messenger, u.aol_messenger, u.msn_messenger,
				u.icq_messenger, u.gtalk_messenger, u.registered, u.last_active, u.last_post_time,
				u.birthday_day, u.birthday_month, u.birthday_year, p.*
				FROM ".$db -> table_prefix."users as u
				LEFT JOIN ".$db -> table_prefix."profile_fields_data as p ON(p.member_id = u.id) 
                WHERE u.id='".$db -> escape_string($id)."'
                LIMIT 1";
                
        }
}

?>