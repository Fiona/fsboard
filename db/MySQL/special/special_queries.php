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
 * @version 1.0
 * @package FSBoard
 * @subpackage Database
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * Class encapsulates some functions that just
 * return some extended SQL as a string.
 */
class db_special_queries
{

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