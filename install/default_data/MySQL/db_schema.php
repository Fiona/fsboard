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
 * Installer File
 * Database schema
 * 
 * MySQL 
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Install
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


// Config table
$sql_schema['table']['config']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."config`;";
$sql_schema['table']['config']['create'] = "CREATE TABLE `".PREFIX."config` (
  `name` varchar(128) NOT NULL default '',
  `value` text NOT NULL,
  `default` text NOT NULL,
  `config_group` varchar(255) NOT NULL default '',
  `config_type` varchar(30) NOT NULL default '',
  `dropdown_values` text NOT NULL,
  `order` int(3) NOT NULL default '0',
  PRIMARY KEY  (`name`)
);";


// Configuration groups
$sql_schema['table']['config_groups']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."config_groups`;";
$sql_schema['table']['config_groups']['create'] = "CREATE TABLE `".PREFIX."config_groups` (
  `name` varchar(255) NOT NULL default '',
  `order` int(4) NOT NULL default '0',
  PRIMARY KEY  (`name`)
);";


// Hooj forums table
$sql_schema['table']['forums']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."forums`;";
$sql_schema['table']['forums']['create'] = "CREATE TABLE `".PREFIX."forums` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(75) NOT NULL default '',
  `description` varchar(75) NOT NULL default '',
  `parent_id` int(10) NOT NULL default '-1',
  `position` int(5) NOT NULL default '0',
  `is_category` tinyint(1) NOT NULL default '0',
  `theme_id` int(10) NOT NULL default '-1',
  `override_user_theme` tinyint(1) NOT NULL default '0',
  `topic_count` int(5) NOT NULL default '0',
  `post_count` int(5) NOT NULL default '0',
  `last_poster_id` int(10) NOT NULL default '-1',
  `last_post_id` int(10) NOT NULL default '-1',
  `last_post_time` int(10) NOT NULL default '-1',
  `password` varchar(60) NOT NULL default '',
  `redirect` tinyint(1) NOT NULL default '0',
  `redirect_url` varchar(128) NOT NULL default '',
  `redirect_hits` int(5) NOT NULL default '0',
  `rules_on` tinyint(1) NOT NULL default '0',
  `rules_title` varchar(128) NOT NULL default '',
  `rules_text` text NOT NULL,
  `use_site_rules` tinyint(1) NOT NULL default '0',
  `hide_forum` tinyint(1) NOT NULL default '0',
  `close_forum` tinyint(1) NOT NULL default '0',
  `bbcode_on` tinyint(1) NOT NULL default '1',
  `html_on` tinyint(1) NOT NULL default '0',
  `polls_on` tinyint(1) NOT NULL default '1',
  `emoticons_on` tinyint(1) NOT NULL default '1',
  `quick_reply_on` tinyint(1) NOT NULL default '1',
  `add_post_count` tinyint(1) NOT NULL default '1',
  `show_forum_jump` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
);";


// forum_permissions table
$sql_schema['table']['forums_perms']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."forums_perms`;";
$sql_schema['table']['forums_perms']['create'] = "CREATE TABLE `".PREFIX."forums_perms` (
  `id` int(10) NOT NULL auto_increment,
  `forum_id` int(10) NOT NULL default '0',
  `group_id` int(10) NOT NULL default '0',
  `perm_see_board` tinyint(1) NOT NULL default '0',
  `perm_use_search` tinyint(1) NOT NULL default '0',
  `perm_view_other_topic` tinyint(1) NOT NULL default '0',
  `perm_post_topic` tinyint(1) NOT NULL default '0',
  `perm_reply_own_topic` tinyint(1) NOT NULL default '0',
  `perm_reply_other_topic` tinyint(1) NOT NULL default '0',
  `perm_edit_own_post` tinyint(1) NOT NULL default '0',
  `perm_edit_own_topic_title` tinyint(1) NOT NULL default '0',
  `perm_delete_own_post` tinyint(1) NOT NULL default '0',
  `perm_delete_own_topic` tinyint(1) NOT NULL default '0',
  `perm_move_own_topic` tinyint(1) NOT NULL default '0',
  `perm_close_own_topic` tinyint(1) NOT NULL default '0',
  `perm_post_closed_topic` tinyint(1) NOT NULL default '0',
  `perm_remove_edited_by` tinyint(1) NOT NULL default '0',
  `perm_use_html` tinyint(1) NOT NULL default '0',
  `perm_use_bbcode` tinyint(1) NOT NULL default '0',
  `perm_use_emoticons` tinyint(1) NOT NULL default '0',
  `perm_no_word_filter` tinyint(1) NOT NULL default '0',
  `perm_new_polls` tinyint(1) NOT NULL default '0',
  `perm_vote_polls` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Moderators table
$sql_schema['table']['moderators']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."moderators`;";
$sql_schema['table']['moderators']['create'] = "CREATE TABLE `".PREFIX."moderators` (
  `id` int(10) NOT NULL auto_increment,
  `forum_id` int(10) NOT NULL default '0',
  `user_id` int(10) NOT NULL default '-1',
  `username` varchar(128) NOT NULL default '',
  `group_id` int(10) NOT NULL default '-1',
  `group_name` varchar(128) NOT NULL default '',
  `perm_edit_post` tinyint(1) NOT NULL default '0',
  `perm_edit_topic` tinyint(1) NOT NULL default '0',
  `perm_delete_post` tinyint(1) NOT NULL default '0',
  `perm_delete_topic` tinyint(1) NOT NULL default '0',
  `perm_view_ip` tinyint(1) NOT NULL default '0',
  `perm_close_topic` tinyint(1) NOT NULL default '0',
  `perm_move_topic` tinyint(1) NOT NULL default '0',
  `perm_sticky_topic` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Lol sessions
$sql_schema['table']['sessions']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."sessions`;";
$sql_schema['table']['sessions']['create'] = "CREATE TABLE `".PREFIX."sessions` (
  `id` varchar(32) NOT NULL default '',
  `user_id` int(10) NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  `user_group` int(10) NOT NULL default '0',
  `invisible` tinyint(1) NOT NULL default '0',
  `last_active` int(12) NOT NULL default '0',
  `ip_address` varchar(16) NOT NULL default '',
  `browser` varchar(255) NOT NULL default '',
  `location` text NOT NULL,
  PRIMARY KEY  (`id`)
);";


// Main user table
$sql_schema['table']['users']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."users`;";
$sql_schema['table']['users']['create'] = "CREATE TABLE `".PREFIX."users` (
  `id` int(10) NOT NULL auto_increment,
  `username` varchar(255) NOT NULL default '',
  `user_group` int(10) NOT NULL default '1',
  `ip_address` varchar(16) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `banned` tinyint(1) NOT NULL default '0',
  `email` varchar(128) NOT NULL default '',
  `registered` int(12) NOT NULL default '-1',
  `last_visit` int(12) NOT NULL default '-1',
  `last_active` int(12) NOT NULL default '-1',
  `last_post_time` int(12) NOT NULL default '-1',
  `posts` int(10) NOT NULL default '0',
  `reputation` int(10) NOT NULL default '0',
  `theme` int(10) NOT NULL default '-1',
  `language` int(10) NOT NULL default '-1',
  `hide_email` tinyint(1) NOT NULL default '0',
  `time_offset` decimal(4,2) NOT NULL default '0.00',
  `dst_on` tinyint(1) NOT NULL default '0',  
  `signature` text,
  `title` varchar(64) NOT NULL default '',
  `avatar_type` enum('no','gallery','upload','external') NOT NULL default 'no',
  `avatar_address` text,
  `avatar_gallery_cat` int(10) default NULL,
  `homepage` varchar(128) NOT NULL default '',
  `real_name` varchar(255) default NULL,
  `yahoo_messenger` varchar(32) default NULL,
  `aol_messenger` varchar(20) default NULL,
  `msn_messenger` varchar(120) default NULL,
  `icq_messenger` varchar(20) default NULL,
  `gtalk_messenger` varchar(120) default NULL,
  `birthday_day` int(2) default NULL,
  `birthday_month` int(2) default NULL,
  `birthday_year` int(4) default NULL,
  `view_sigs` tinyint(1) NOT NULL default '1',
  `view_images` tinyint(1) NOT NULL default '1',
  `view_avatars` tinyint(1) NOT NULL default '1',
  `email_new_pm` tinyint(1) NOT NULL default '0',
  `email_from_admin` tinyint(1) NOT NULL default '1',
  `view_topic_num` tinyint(1) NOT NULL default '0',
  `view_post_num` tinyint(1) NOT NULL default '0',
  `validate_id` varchar(13) NOT NULL default '',
  `need_validate` tinyint(1) NOT NULL default '0',
  `reset_password` tinyint(1) NOT NULL default '0',
  `notepad` text,  
  PRIMARY KEY  (`id`),
  KEY `user_group` (`user_group`)
);";


// Users to secondary groups
$sql_schema['table']['user_secondary_groups']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."users_secondary_groups`;";
$sql_schema['table']['user_secondary_groups']['create'] = "CREATE TABLE IF NOT EXISTS `".PREFIX."users_secondary_groups` (
  `user_id` int(12) NOT NULL,
  `group_id` int(12) NOT NULL,
  UNIQUE KEY `unique` (`user_id`,`group_id`),
  KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
);";


// User admin settings
$sql_schema['table']['users_admin_settings']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."users_admin_settings`;";
$sql_schema['table']['users_admin_settings']['create'] = "CREATE TABLE `".PREFIX."users_admin_settings` (
  `user_id` int(12) NOT NULL,
  `admin_menu` text NOT NULL,
  `access_config` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`user_id`)
);";


// Crazy big user groups table
$sql_schema['table']['user_groups']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."user_groups`;";
$sql_schema['table']['user_groups']['create'] = "CREATE TABLE `".PREFIX."user_groups` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `removable` tinyint(1) NOT NULL default '1',
  `suffix` varchar(128) NOT NULL default '',
  `prefix` varchar(128) NOT NULL default '',
  `hide_from_member_list` tinyint(1) NOT NULL default '0',
  `flood_control_time` int(10) NOT NULL default '0',
  `edit_time` int(10) NOT NULL default '0',
  `pm_total` int(3) NOT NULL default '0',
  `perm_admin_area` tinyint(1) NOT NULL default '0',
  `perm_global_mod` tinyint(1) NOT NULL default '0',
  `banned` tinyint(1) NOT NULL default '0',
  `perm_see_board` tinyint(1) NOT NULL default '0',
  `perm_see_maintenance_mode` tinyint(1) NOT NULL default '0',
  `perm_see_member_list` tinyint(1) NOT NULL default '0',
  `perm_see_profile` tinyint(1) NOT NULL default '0',
  `perm_use_search` tinyint(1) NOT NULL default '0',
  `perm_edit_own_post` tinyint(1) NOT NULL default '0',
  `perm_use_pm` tinyint(1) NOT NULL default '0',
  `perm_post_topic` tinyint(1) NOT NULL default '0',
  `perm_reply_own_topic` tinyint(1) NOT NULL default '0',
  `perm_remove_edited_by` tinyint(1) NOT NULL default '0',
  `perm_delete_own_post` tinyint(1) NOT NULL default '0',
  `perm_close_own_topic` tinyint(1) NOT NULL default '0',
  `perm_post_closed_topic` tinyint(1) NOT NULL default '0',
  `perm_new_polls` tinyint(1) NOT NULL default '0',
  `perm_vote_polls` tinyint(1) NOT NULL default '0',
  `perm_use_html` tinyint(1) NOT NULL default '0',
  `perm_use_bbcode` tinyint(1) NOT NULL default '0',
  `perm_no_word_filter` tinyint(1) NOT NULL default '0',
  `perm_use_emoticons` tinyint(1) NOT NULL default '0',
  `perm_reply_other_topic` tinyint(1) NOT NULL default '0',
  `perm_view_other_topic` tinyint(1) NOT NULL default '0',
  `perm_delete_own_topic` tinyint(1) NOT NULL default '0',
  `perm_move_own_topic` tinyint(1) NOT NULL default '0',
  `perm_edit_own_profile` tinyint(1) NOT NULL default '0',
  `perm_edit_own_topic_title` tinyint(1) NOT NULL default '0',
  `perm_avatar_allow` tinyint(1) NOT NULL default '0',
  `perm_avatar_allow_gallery` tinyint(1) NOT NULL default '0',
  `perm_avatar_allow_upload` tinyint(1) NOT NULL default '0',  
  `perm_avatar_allow_external` tinyint(1) NOT NULL default '0',
  `perm_avatar_width` int(4) NOT NULL default '0',
  `perm_avatar_height` int(4) NOT NULL default '0',
  `perm_avatar_filesize` int(8) NOT NULL default '0',
  `display_user_title` varchar(100) default NULL,
  `override_user_title` tinyint(1) NOT NULL default '0',
  `perm_custom_user_title` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Automatic user group promotions
$sql_schema['table']['promotions']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."promotions`;";
$sql_schema['table']['promotions']['create'] = "CREATE TABLE `".PREFIX."promotions` (
  `id` int(12) NOT NULL auto_increment,
  `group_id` int(12) NOT NULL default '0',
  `group_to_id` int(12) NOT NULL default '0',
  `reputation` int(4) NOT NULL default '0',
  `use_reputation` tinyint(1) NOT NULL default '0',
  `reputation_comparison` tinyint(1) NOT NULL default '0',
  `days_registered` int(5) NOT NULL default '0',
  `use_days_registered` tinyint(1) NOT NULL default '0',
  `posts` int(5) NOT NULL default '0',
  `use_posts` tinyint(1) NOT NULL default '0',
  `promotion_type` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Profile fields table
$sql_schema['table']['profile_fields']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."profile_fields`;";
$sql_schema['table']['profile_fields']['create'] = "CREATE TABLE `".PREFIX."profile_fields` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `field_type` varchar(30) NOT NULL default '',
  `size` int(4) NOT NULL default '0',
  `max_length` int(4) NOT NULL default '0',
  `order` int(10) NOT NULL default '0',
  `dropdown_values` text NOT NULL,
  `dropdown_text` text NOT NULL,
  `show_on_reg` tinyint(1) NOT NULL default '0',
  `user_can_edit` tinyint(1) NOT NULL default '1',
  `is_private` tinyint(1) NOT NULL default '0',
  `admin_only_field` tinyint(1) NOT NULL default '0',
  `must_be_filled` tinyint(1) NOT NULL default '0',
  `topic_html` text NOT NULL,
  PRIMARY KEY  (`id`)
);";


// Data for profile fields
$sql_schema['table']['profile_fields_data']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."profile_fields_data`;";
$sql_schema['table']['profile_fields_data']['create'] = "CREATE TABLE `".PREFIX."profile_fields_data` (
  `member_id` int(11) NOT NULL default '0'
);";


// Reputation titles
$sql_schema['table']['user_reputations']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."user_reputations`;";
$sql_schema['table']['user_reputations']['create'] = "CREATE TABLE `".PREFIX."user_reputations` (
  `id` int(12) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `min_rep` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Reputations given log
$sql_schema['table']['saved_reputations']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."saved_reputations`;";
$sql_schema['table']['saved_reputations']['create'] = "CREATE TABLE `".PREFIX."saved_reputations` (
  `id` int(12) NOT NULL auto_increment,
  `rep_given` int(2) NOT NULL default '1',
  `post_id` int(12) NOT NULL default '0',
  `user_recieve_id` int(12) NOT NULL default '0',
  `user_give_id` int(12) NOT NULL default '0',
  `reason` varchar(255) NOT NULL default '',
  `date` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// User titles
$sql_schema['table']['user_titles']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."user_titles`;";
$sql_schema['table']['user_titles']['create'] = "CREATE TABLE `".PREFIX."user_titles` (
  `id` int(12) NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `min_posts` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Icons
$sql_schema['table']['user_insignias']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."user_insignia`;";
$sql_schema['table']['user_insignias']['create'] = "CREATE TABLE `".PREFIX."user_insignia` (
  `id` int(12) NOT NULL auto_increment,
  `user_group` int(12) NOT NULL default '-1',
  `min_posts` int(10) NOT NULL default '0',
  `newline` tinyint(1) NOT NULL default '1',
  `image` varchar(255) default NULL,
  `text` text,
  `repeat_no` int(2) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Template sets table
$sql_schema['table']['template_sets']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."template_sets`;";
$sql_schema['table']['template_sets']['create'] = "CREATE TABLE `".PREFIX."template_sets` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) default NULL,
  `default_theme` varchar(128) NOT NULL default '0',
  `can_change_theme` tinyint(1) NOT NULL default '1',
  `author` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
);";


// Templates Table
$sql_schema['table']['templates']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."templates`;";
$sql_schema['table']['templates']['create'] = "CREATE TABLE `".PREFIX."templates` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `class_name` varchar(255) NOT NULL default '',
  `set_id` int(10) NOT NULL default '0',
  `function_name` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `parameters` text NOT NULL,
  PRIMARY KEY  (`id`)
);";


// Themes (CSS) table
$sql_schema['table']['themes']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."themes`;";
$sql_schema['table']['themes']['create'] = "CREATE TABLE `".PREFIX."themes` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `css` text NOT NULL,
  `image_dir` varchar(128) NOT NULL default '',
  `author` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
);";


// Main language table
$sql_schema['table']['languages']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."languages`;";
$sql_schema['table']['languages']['create'] = "CREATE TABLE `".PREFIX."languages` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `short_name` varchar(255) NOT NULL default '',
  `charset` varchar(128) NOT NULL default '',
  `allow_user_select` tinyint(1) NOT NULL default '1',
  `direction` tinyint(1) NOT NULL default '0',
  `author` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
);";


// Keeping track of language groups
$sql_schema['table']['language_groups']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."language_groups`;";
$sql_schema['table']['language_groups']['create'] = "CREATE TABLE `".PREFIX."language_groups` (
  `id` int(10) NOT NULL auto_increment,
  `short_name` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
);";


// Words themselves
$sql_schema['table']['language_phrases']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."language_phrases`;";
$sql_schema['table']['language_phrases']['create'] = "CREATE TABLE `".PREFIX."language_phrases` (
  `id` int(10) NOT NULL auto_increment,
  `language_id` int(10) NOT NULL default '0',
  `variable_name` varchar(255) NOT NULL default '',
  `group` varchar(128) NOT NULL default '',
  `text` text NOT NULL,
  `default_text` text NOT NULL,
  PRIMARY KEY  (`id`)
);";


// Filetypes table
$sql_schema['table']['filetypes']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."filetypes`;";
$sql_schema['table']['filetypes']['create'] = "CREATE TABLE `".PREFIX."filetypes` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  `extension` varchar(4) NOT NULL default '',
  `mime_type` varchar(255) NOT NULL default 'Content-type: unknown/unknown',
  `use_avatar` tinyint(1) NOT NULL default '1',
  `use_attachment` tinyint(1) NOT NULL default '1',
  `enabled` tinyint(1) NOT NULL default '1',
  `max_file_size` int(10) NOT NULL default '1000000',
  `max_width` int(5) NOT NULL default '0',
  `max_height` int(5) NOT NULL default '0',
  `icon_file` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);";


// Avatars/emoticons/post icons table
$sql_schema['table']['small_images']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."small_images`;";
$sql_schema['table']['small_images']['create'] = "CREATE TABLE `".PREFIX."small_images` (
  `id` int(10) NOT NULL auto_increment,
  `type` varchar(10) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `cat_id` int(10) NOT NULL default '0',
  `order` int(10) NOT NULL default '1',
  `min_posts` int(8) NOT NULL default '0',
  `filename` varchar(255) NOT NULL default '',
  `emoticon_code` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
);";


// Avatars/emoticons/post icons categories
$sql_schema['table']['small_image_cat']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."small_image_cat`;";
$sql_schema['table']['small_image_cat']['create'] = "CREATE TABLE `".PREFIX."small_image_cat` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `order` int(5) NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `image_num` int(5) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Avatars/post icons permissions
$sql_schema['table']['small_image_cat_perms']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."small_image_cat_perms`;";
$sql_schema['table']['small_image_cat_perms']['create'] = "CREATE TABLE `".PREFIX."small_image_cat_perms` (
  `id` int(10) NOT NULL auto_increment,
  `cat_id` int(10) NOT NULL default '0',
  `user_group_id` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Cache table
$sql_schema['table']['cache']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."cache`;";
$sql_schema['table']['cache']['create'] = "CREATE TABLE `".PREFIX."cache` (
  `name` varchar(128) NOT NULL default '',
  `content` mediumtext,
  `array_levels` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`name`)
);";


// Common tasks table
$sql_schema['table']['tasks']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."tasks`;";
$sql_schema['table']['tasks']['create'] = "CREATE TABLE `".PREFIX."tasks` (
  `id` int(10) NOT NULL auto_increment,
  `task_filepath` varchar(255) NOT NULL default '',
  `next_runtime` int(12) NOT NULL default '0',
  `enabled` tinyint(1) NOT NULL default '1',
  `month_day` int(2) NOT NULL default '-1',
  `task_name` varchar(255) NOT NULL default '',
  `task_description` varchar(255) NOT NULL default '',
  `week_day` int(1) NOT NULL default '-1',
  `hour` int(2) NOT NULL default '-1',
  `minute` int(2) NOT NULL default '-1',
  `keep_log` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
);";


// Common tasks logs table
$sql_schema['table']['task_logs']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."task_logs`;";
$sql_schema['table']['task_logs']['create'] = "CREATE TABLE `".PREFIX."task_logs` (
  `id` int(10) NOT NULL auto_increment,
  `date` int(12) NOT NULL default '0',
  `task_id` int(10) NOT NULL default '-1',
  `task_name` varchar(128) NOT NULL default '',
  `ip` varchar(30) NOT NULL default '',
  `action` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);";


// Admin logs
$sql_schema['table']['admin_logs']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."admin_logs`;";
$sql_schema['table']['admin_logs']['create'] = "CREATE TABLE `".PREFIX."admin_logs` (
  `id` int(10) NOT NULL auto_increment,
  `date` int(12) NOT NULL default '0',
  `page_name` varchar(128) NOT NULL default '',
  `mode` varchar(128) NOT NULL default '',
  `member` int(10) NOT NULL default '0',
  `ip` varchar(30) NOT NULL default '',
  `note` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
);";


// Email logs
$sql_schema['table']['email_logs']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."email_logs`;";
$sql_schema['table']['email_logs']['create'] = "CREATE TABLE `".PREFIX."email_logs` (
  `id` int(10) NOT NULL auto_increment,
  `date` int(12) NOT NULL default '0',
  `from` varchar(255) NOT NULL default '',
  `to` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `text` text NOT NULL,
  `note` varchar(128) NOT NULL default '',
  `error` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Word filter
$sql_schema['table']['wordfilter']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."wordfilter`;";
$sql_schema['table']['wordfilter']['create'] = "CREATE TABLE `".PREFIX."wordfilter` (
  `id` int(12) NOT NULL auto_increment,
  `word` varchar(255) NOT NULL default '',
  `replacement` varchar(255) NOT NULL default '',
  `perfect_match` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Custom BBCode
$sql_schema['table']['bbcode']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."bbcode`;";
$sql_schema['table']['bbcode']['create'] = "CREATE TABLE `".PREFIX."bbcode` (
  `id` int(10) NOT NULL auto_increment,
  `tag` varchar(128) NOT NULL default '',
  `replacement` text NOT NULL,
  `name` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `example` text NOT NULL,
  `use_param` tinyint(1) NOT NULL default '0',
  `button_image` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";


// Admin area help popup entries
$sql_schema['table']['admin_area_help']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."admin_area_help`;";
$sql_schema['table']['admin_area_help']['create'] = "CREATE TABLE `".PREFIX."admin_area_help` (
  `id` int(12) NOT NULL auto_increment,
  `page` varchar(128) default NULL,
  `action` varchar(255) default NULL,
  `field` varchar(255) default NULL,
  `order` int(10) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";



// Mass-mailer sets
$sql_schema['table']['mass_mailer']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."mass_mailer;";
$sql_schema['table']['mass_mailer']['create'] = "CREATE TABLE `".PREFIX."mass_mailer` (
  `id` int(10) NOT NULL auto_increment,
  `bulk_num` int(5) NOT NULL,
  `emails_left` int(5) NOT NULL,
  `emails_sent` int(5) NOT NULL,
  `from_email` varchar(255) NOT NULL,
  `test` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";



// Mass-mailer emails
$sql_schema['table']['mass_mailer_emails']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."mass_mailer_emails;";
$sql_schema['table']['mass_mailer_emails']['create'] = "CREATE TABLE `".PREFIX."mass_mailer_emails` (
  `id` int(10) NOT NULL auto_increment,
  `set_id` int(10) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `contents` text NOT NULL,
  PRIMARY KEY  (`id`)
);";



// Miscellanious message board stats
$sql_schema['table']['stats']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."stats;";
$sql_schema['table']['stats']['create'] = "CREATE TABLE `".PREFIX."stats` (
  `stat_name` varchar(32) NOT NULL default '',
  `stat_value` text NOT NULL,
  PRIMARY KEY  (`stat_name`)
);";


// Plugin system
$sql_schema['table']['plugins']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."plugins;";
$sql_schema['table']['plugins']['create'] = "CREATE TABLE `".PREFIX."plugins` (
  `id` int(12) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `author` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `enabled` tinyint(1) NOT NULL default '0',
  `installed` tinyint(1) NOT NULL default '0'
  PRIMARY KEY  (`id`)
);";


// Plugin system hooks
$sql_schema['table']['plugins_files']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."plugins_files;";
$sql_schema['table']['plugins_files']['create'] = "CREATE TABLE `".PREFIX."plugins_files` (
  `id` int(12) NOT NULL auto_increment,
  `summary` varchar(255) NOT NULL default '',
  `plugin_id` int(12) NOT NULL default '0',
  `hook_file` varchar(128) NOT NULL default '',
  `hook_name` varchar(128) NOT NULL default '',
  `code` text NOT NULL,
  PRIMARY KEY  (`id`)
);";


// Undelete table
$sql_schema['table']['undelete']['drop'] = "DROP TABLE IF EXISTS `".PREFIX."undelete;";
$sql_schema['table']['undelete']['create'] = "CREATE TABLE `".PREFIX."undelete` (
  `id` int(12) NOT NULL auto_increment,
  `table` varchar(128) NOT NULL default '',
  `data` longblob default NULL,
  `action` varchar(255) default NULL,
  `time` int(12) NOT NULL default '0',
  PRIMARY KEY  (`id`)
);";

?>
