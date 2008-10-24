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
*	INSTALL			*
*       Database Dafualt Data	*
*       Started by Fiona        *
*       17rd Jan 07	        *
*********************************
*       Last edit by Fiona      *
*       06th May 07	        *
*********************************

*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


// Default user groups

$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` (`id`, `name`, `removable`, `suffix`, `prefix`, `hide_from_member_list`, `flood_control_time`, `edit_time`, `pm_total`, `perm_admin_area`, `perm_global_mod`, `banned`, `perm_see_board`, `perm_see_maintenance_mode`, `perm_see_member_list`, `perm_see_profile`, `perm_use_search`, `perm_edit_own_post`, `perm_use_pm`, `perm_post_topic`, `perm_reply_own_topic`, `perm_remove_edited_by`, `perm_delete_own_post`, `perm_close_own_topic`, `perm_post_closed_topic`, `perm_new_polls`, `perm_vote_polls`, `perm_use_html`, `perm_use_bbcode`, `perm_no_word_filter`, `perm_use_emoticons`, `perm_reply_other_topic`, `perm_view_other_topic`, `perm_delete_own_topic`, `perm_move_own_topic`, `perm_edit_own_profile`, `perm_edit_own_topic_title`, `perm_avatar_allow`, `perm_avatar_allow_gallery`, `perm_avatar_allow_upload`, `perm_avatar_allow_external`, `perm_avatar_width`, `perm_avatar_height`, `perm_avatar_filesize`, `display_user_title`, `override_user_title`, `perm_custom_user_title`)".
	"VALUES (1, 'Administrators', 0, '', '', 0, 0, 0, 0, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 'Administrator', 0, 1);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` (`id`, `name`, `removable`, `suffix`, `prefix`, `hide_from_member_list`, `flood_control_time`, `edit_time`, `pm_total`, `perm_admin_area`, `perm_global_mod`, `banned`, `perm_see_board`, `perm_see_maintenance_mode`, `perm_see_member_list`, `perm_see_profile`, `perm_use_search`, `perm_edit_own_post`, `perm_use_pm`, `perm_post_topic`, `perm_reply_own_topic`, `perm_remove_edited_by`, `perm_delete_own_post`, `perm_close_own_topic`, `perm_post_closed_topic`, `perm_new_polls`, `perm_vote_polls`, `perm_use_html`, `perm_use_bbcode`, `perm_no_word_filter`, `perm_use_emoticons`, `perm_reply_other_topic`, `perm_view_other_topic`, `perm_delete_own_topic`, `perm_move_own_topic`, `perm_edit_own_profile`, `perm_edit_own_topic_title`, `perm_avatar_allow`, `perm_avatar_allow_gallery`, `perm_avatar_allow_upload`, `perm_avatar_allow_external`, `perm_avatar_width`, `perm_avatar_height`, `perm_avatar_filesize`, `display_user_title`, `override_user_title`, `perm_custom_user_title`)".
	"VALUES (2, 'Global Moderators', 0, '', '', 0, 0, 0, 100, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 100, 100, 51200, 'Overlord Moderator', 0, 1);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` (`id`, `name`, `removable`, `suffix`, `prefix`, `hide_from_member_list`, `flood_control_time`, `edit_time`, `pm_total`, `perm_admin_area`, `perm_global_mod`, `banned`, `perm_see_board`, `perm_see_maintenance_mode`, `perm_see_member_list`, `perm_see_profile`, `perm_use_search`, `perm_edit_own_post`, `perm_use_pm`, `perm_post_topic`, `perm_reply_own_topic`, `perm_remove_edited_by`, `perm_delete_own_post`, `perm_close_own_topic`, `perm_post_closed_topic`, `perm_new_polls`, `perm_vote_polls`, `perm_use_html`, `perm_use_bbcode`, `perm_no_word_filter`, `perm_use_emoticons`, `perm_reply_other_topic`, `perm_view_other_topic`, `perm_delete_own_topic`, `perm_move_own_topic`, `perm_edit_own_profile`, `perm_edit_own_topic_title`, `perm_avatar_allow`, `perm_avatar_allow_gallery`, `perm_avatar_allow_upload`, `perm_avatar_allow_external`, `perm_avatar_width`, `perm_avatar_height`, `perm_avatar_filesize`, `display_user_title`, `override_user_title`, `perm_custom_user_title`)".
	"VALUES (3, 'Members', 0, '', '', 0, 15, 0, 50, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 1, 100, 100, 51200, 'Member', 1, 1);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` (`id`, `name`, `removable`, `suffix`, `prefix`, `hide_from_member_list`, `flood_control_time`, `edit_time`, `pm_total`, `perm_admin_area`, `perm_global_mod`, `banned`, `perm_see_board`, `perm_see_maintenance_mode`, `perm_see_member_list`, `perm_see_profile`, `perm_use_search`, `perm_edit_own_post`, `perm_use_pm`, `perm_post_topic`, `perm_reply_own_topic`, `perm_remove_edited_by`, `perm_delete_own_post`, `perm_close_own_topic`, `perm_post_closed_topic`, `perm_new_polls`, `perm_vote_polls`, `perm_use_html`, `perm_use_bbcode`, `perm_no_word_filter`, `perm_use_emoticons`, `perm_reply_other_topic`, `perm_view_other_topic`, `perm_delete_own_topic`, `perm_move_own_topic`, `perm_edit_own_profile`, `perm_edit_own_topic_title`, `perm_avatar_allow`, `perm_avatar_allow_gallery`, `perm_avatar_allow_upload`, `perm_avatar_allow_external`, `perm_avatar_width`, `perm_avatar_height`, `perm_avatar_filesize`, `display_user_title`, `override_user_title`, `perm_custom_user_title`)".
	"VALUES (4, 'Guests', 0, '', '', 0, 15, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 'Guest', 0, 0);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` (`id`, `name`, `removable`, `suffix`, `prefix`, `hide_from_member_list`, `flood_control_time`, `edit_time`, `pm_total`, `perm_admin_area`, `perm_global_mod`, `banned`, `perm_see_board`, `perm_see_maintenance_mode`, `perm_see_member_list`, `perm_see_profile`, `perm_use_search`, `perm_edit_own_post`, `perm_use_pm`, `perm_post_topic`, `perm_reply_own_topic`, `perm_remove_edited_by`, `perm_delete_own_post`, `perm_close_own_topic`, `perm_post_closed_topic`, `perm_new_polls`, `perm_vote_polls`, `perm_use_html`, `perm_use_bbcode`, `perm_no_word_filter`, `perm_use_emoticons`, `perm_reply_other_topic`, `perm_view_other_topic`, `perm_delete_own_topic`, `perm_move_own_topic`, `perm_edit_own_profile`, `perm_edit_own_topic_title`, `perm_avatar_allow`, `perm_avatar_allow_gallery`, `perm_avatar_allow_upload`, `perm_avatar_allow_external`, `perm_avatar_width`, `perm_avatar_height`, `perm_avatar_filesize`, `display_user_title`, `override_user_title`, `perm_custom_user_title`)".
	"VALUES (5, 'Validating', 0, '', '', 0, 15, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, NULL, 0, 0);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` (`id`, `name`, `removable`, `suffix`, `prefix`, `hide_from_member_list`, `flood_control_time`, `edit_time`, `pm_total`, `perm_admin_area`, `perm_global_mod`, `banned`, `perm_see_board`, `perm_see_maintenance_mode`, `perm_see_member_list`, `perm_see_profile`, `perm_use_search`, `perm_edit_own_post`, `perm_use_pm`, `perm_post_topic`, `perm_reply_own_topic`, `perm_remove_edited_by`, `perm_delete_own_post`, `perm_close_own_topic`, `perm_post_closed_topic`, `perm_new_polls`, `perm_vote_polls`, `perm_use_html`, `perm_use_bbcode`, `perm_no_word_filter`, `perm_use_emoticons`, `perm_reply_other_topic`, `perm_view_other_topic`, `perm_delete_own_topic`, `perm_move_own_topic`, `perm_edit_own_profile`, `perm_edit_own_topic_title`, `perm_avatar_allow`, `perm_avatar_allow_gallery`, `perm_avatar_allow_upload`, `perm_avatar_allow_external`, `perm_avatar_width`, `perm_avatar_height`, `perm_avatar_filesize`, `display_user_title`, `override_user_title`, `perm_custom_user_title`)".
	"VALUES (6, 'Banned', 0, '', '', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 'BANNED', 0, 0);";

/*
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` VALUES (1, 'Administrators', 0, '', '', 0, 0, 0, 0, 1, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 'Administrator', 0, 1);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` VALUES (2, 'Global Moderators', 0, '', '', 0, 0, 0, 100, 0, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 100, 100, 51200, 'Overlord Moderator', 0, 1);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` VALUES (3, 'Members', 0, '', '', 0, 15, 0, 50, 0, 0, 0, 1, 0, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 0, 1, 1, 0, 1, 0, 1, 1, 1, 0, 0, 1, 1, 1, 1, 1, 100, 100, 51200, 'Member', 1, 1);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` VALUES (4, 'Guests', 0, '', '', 0, 15, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 'Guest', 0, 0);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` VALUES (5, 'Validating', 0, '', '', 0, 15, 0, 0, 0, 0, 0, 1, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, NULL, 0, 0);";
$sql_schema['entries']['user_groups'][] = "INSERT INTO `".PREFIX."user_groups` VALUES (6, 'Banned', 0, '', '', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 'BANNED', 0, 0);";
*/

// Language groups
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('register');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('login');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('global');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('view_profile');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('control_panel');";

$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_themes');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_templates');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_sqltools');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_main');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_languages');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_global');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_emaillogs');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_config');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_cache');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_adminlogs');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_forums');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_usergroups');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_users');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_profilefields');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_tasks');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_attachments');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_bbcode');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_small_images');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_wordfilter');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_titles');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_insignia');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_reputations');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_promotions');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_area_help');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_mailer');";
$sql_schema['entries']['language_groups'][] = "INSERT INTO `".PREFIX."language_groups`(`short_name`) VALUES ('admin_plugins');";


// Cache entries
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('config', 1);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('themes', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('languages', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('forums', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('user_groups', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('forums_perms', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('moderators', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('profile_fields', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('filetypes', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('custom_bbcode', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('avatars', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('emoticons', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('post_icons', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('small_image_cats', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('small_image_cats_perms', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('wordfilter', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('user_titles', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('user_insignia', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('user_reputations', 2);";
$sql_schema['entries']['cache'][] = "INSERT INTO `".PREFIX."cache`(name,array_levels) VALUES ('plugins', 2);";


// Common tasks
$sql_schema['entries']['tasks'][] = "INSERT INTO `".PREFIX."tasks` VALUES (1, 'common/tasks/cleanup_hour.php', 0, 1, -1, 'Cleanup (Every hour)', 'Deletes old sessions', -1, -1, 59, 1);";
$sql_schema['entries']['tasks'][] = "INSERT INTO `".PREFIX."tasks` VALUES (2, 'common/tasks/promotions.php', 0, 1, -1, 'Promotions', 'Will automatically promote users who match the criteria.', -1, -1, 30, 1);";
$sql_schema['entries']['tasks'][] = "INSERT INTO `".PREFIX."tasks` VALUES (3, 'common/tasks/daily_tasks.php', 0, 1, -1, 'Daily tasks', 'Things that need to be done or cleaned up once a day', -1, 0, 45, 1);";


// User tiles
$sql_schema['entries']['user_titles'][] = "INSERT INTO `".PREFIX."user_titles`(title,min_posts) VALUES ('".$lang['default_user_title_0']."', 0);";
$sql_schema['entries']['user_titles'][] = "INSERT INTO `".PREFIX."user_titles`(title,min_posts) VALUES ('".$lang['default_user_title_100']."', 100);";
$sql_schema['entries']['user_titles'][] = "INSERT INTO `".PREFIX."user_titles`(title,min_posts) VALUES ('".$lang['default_user_title_500']."', 500);";
$sql_schema['entries']['user_titles'][] = "INSERT INTO `".PREFIX."user_titles`(title,min_posts) VALUES ('".$lang['default_user_title_2500']."', 2500);";


// reputation titles
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_-500']."', -500);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_-250']."', -250);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_-100']."', -100);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_-10']."', -10);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_0']."', 0);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_10']."', 10);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_50']."', 50);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_100']."', 100);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_250']."', 250);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_500']."', 500);";
$sql_schema['entries']['user_reputations'][] = "INSERT INTO `".PREFIX."user_reputations`(name,min_rep) VALUES ('".$lang['default_reputation_title_1000']."', 1000);";


// Board statistic types
$sql_schema['entries']['stats'][] = "INSERT INTO `".PREFIX."stats` VALUES ('newest_member_id', '');";
$sql_schema['entries']['stats'][] = "INSERT INTO `".PREFIX."stats` VALUES ('newest_member_username', '');";
$sql_schema['entries']['stats'][] = "INSERT INTO `".PREFIX."stats` VALUES ('total_members', '');";


?>
