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
 * Installer english language file
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


$lang['charset'] = "ISO-8859-1";

$lang['title'] = "FSBoard Installation";

$lang['steps_menu_title'] = "Install Steps";

$lang['installer_top_message'] = "Follow the instructions and refer to the documentation to install FSBoard.";

$lang['next_step'] = "Next Step...";
$lang['redo_step'] = "Re-do Step...";

// ---------------------------
// STEP 1
// ---------------------------
$lang['step_1_message_a'] = "Welcome to the <b>FSBoard installation</b><br />Follow all of the steps carefully and click the \"Next Step\" button when ready.";
$lang['step_1_message_b'] = "To change the language select it from this dropdown and click \"Go\".";
$lang['step_1_language'] = "Language";
$lang['step_1_go'] = "Go";
$lang['step_1_message_c'] = "Click the \"Next Step\" button to begin.";


// ---------------------------
// STEP 2
// ---------------------------
$lang['step_2_message'] = "First the installer must check the existence and permissions of some files and directories.";
$lang['step_2_does_not_exist'] = "...Not found!";
$lang['step_2_not_writable'] = "...Not writable!";
$lang['step_2_ok'] = "...Ok!";
$lang['step_2_done'] = "All needed files and directories have been checked. No problems were found.";
$lang['step_2_fail'] = "One or more of the checks failed!<br />If a file or directory does not exist make sure you have followed the installation documentation properly.<br />If one of the files/directiories was not writable then CHMOD it to 777 using your FTP software.";


// ---------------------------
// STEP 3
// ---------------------------
$lang['step_3_message'] = "Enter the correct database information carefully.";
$lang['step_3_form_title'] = "Database Information";
$lang['step_3_form_db_type'] = "SQL Database Type";
$lang['step_3_form_db_type_message'] = "The type of SQL database you are running.";
$lang['step_3_form_server'] = "SQL Server Address"; 
$lang['step_3_form_server_message'] = "Usually 'localhost'";
$lang['step_3_form_port'] = "<i>Optional</i> SQL Port Address"; 
$lang['step_3_form_port_message'] = "If you need to specify a port number for the database. Can usually be left blank.";
$lang['step_3_form_db_name'] = "SQL Database Name";
$lang['step_3_form_db_name_message'] = "Database for the message board to use. This database must already exist.";
$lang['step_3_form_username'] = "SQL Username";
$lang['step_3_form_username_message'] = "Username for the SQL database.";
$lang['step_3_form_password'] = "SQL Password";
$lang['step_3_form_password_message'] = "Corrisponding password for this username.";
$lang['step_3_form_table_prefix'] = "<i>Optional</i> SQL Table Prefix";
$lang['step_3_form_table_prefix_message'] = "If you wish to run more than one board on the same database, specify a different prefix for each one so as to not make the installations interfere.";


// ---------------------------
// STEP 4
// ---------------------------
$lang['step_4_message'] = "Checking database information, saving it and creating the tables."; 
$lang['step_4_checking_input'] = "Validating input data...";
$lang['step_4_empty_value'] = "...One or more needed values empty!";
$lang['step_4_ok'] = "...Ok!";
$lang['step_4_connecting_to_sql'] = "Connecting to SQL database...";
$lang['step_4_sql_connect_error'] = "...Error! Check server address, username and password.";
$lang['step_4_check_sql_type'] = "Checking FSBoard SQL type support...";
$lang['step_4_sql_type_not_exist'] = "...Database type class not found!";
$lang['step_4_selecting_db'] = "Setting active database...";
$lang['step_4_sql_error_selecting_db'] = "...Error! Check database name and permissions.";
$lang['step_4_opening_dbconfig'] = "Opening db_config.php for writing...";
$lang['step_4_error_opening_db_config'] = "...Error! Check file permissions.";
$lang['step_4_writing_dbconfig'] = "Writing database settings to db_config.php...";
$lang['step_4_error_writing_db_config'] = "...Error!";
$lang['step_4_getting_db_schema'] = "Getting schema for database type...";
$lang['step_4_error_getting_db_schema'] = "...Could not find required file!";
$lang['step_4_error_dropping_table'] = "...There was an error dropping this table!";
$lang['step_4_error_creating_table'] = "...There was an error creating this table!";
$lang['step_4_created_table_ok'] = "...Table was created OK!";
$lang['step_4_finish_message'] = "The database was set up, ready for default data insertion. Hit 'Next Step...' when ready.";


// ---------------------------
// STEP 5
// ---------------------------
$lang['step_5_message'] = "Inserting all the initial data into the database.";
$lang['step_5_fail'] = "..Failed!";
$lang['step_5_ok'] = "...Ok!";
$lang['step_5_getting_db_defaults'] = "Getting default data entries...";
$lang['step_5_entries_inserted'] = "...All default entries inserted!";
$lang['step_5_error_inserting_entry'] = "...Error inserting an entry!";
$lang['step_5_finish_message'] = "All of the default entries in the database were inserted. Hit 'Next Step...' when ready to set and load the message board configuration.";


// ---------------------------
// STEP 6
// ---------------------------
$lang['step_6_message'] = "This step will get some basic configuration settings and then insert all the entries.";
$lang['step_6_form_title'] = "Message board information";
$lang['step_6_form_board_name'] = "Message board name";
$lang['step_6_form_board_name_message'] = "The name of your message board. Shows up in the title, page headers and various other places.";
$lang['step_6_form_default_board_name'] = "My FSBoard";
$lang['step_6_form_board_url'] = "Message board URL address";
$lang['step_6_form_board_url_message'] = "URL pointing to the directory that your message board is located, <b>without trailing slash. ' / '</b>. Notice that the currently inputted URL is a calculated guess.";
$lang['step_6_form_mail_from_address'] = "E-mail sent from address.";
$lang['step_6_form_mail_from_address_message'] = "E-mails sent by the message board will appear to have been sent by this address. Can be a dummy address if you wish.";
$lang['step_6_form_default_mail_from_address'] = "you@youremail.com";
$lang['step_6_form_title_2'] = "Website information";
$lang['step_6_form_home_name'] = "Website name";
$lang['step_6_form_home_name_message'] = "The name of the website that this message board is for.";
$lang['step_6_form_default_home_name'] = "My Website";
$lang['step_6_form_home_address'] = "Website URL address";
$lang['step_6_form_home_address_message'] = "The URL for the aformentioned website.";
$lang['step_6_form_default_home_address'] = "http://www.mywebsite.com";
$lang['step_6_next_step'] = "Submit settings...";
$lang['step_6_loading_config_xml'] = "Loading default configuration XML file...";
$lang['step_6_inserting_config_data'] = "Saving these settings...";
$lang['step_6_saving_config_changes'] = "Updating your changes...";
$lang['step_6_fail'] = "..Failed!";
$lang['step_6_ok'] = "...Ok!";
$lang['step_6_finish_message'] = "Configuration settings saved. Hit 'Next Step...' when ready to generate the languages. (Note; this step may take a long time.)";


// ---------------------------
// STEP 7
// ---------------------------
$lang['step_7_message'] = "Creating language files.";
$lang['step_7_checking_dir_perms'] = "Checking 'language' directory is writable...";
$lang['step_7_writable_perms_fail'] = "..Failed! Check directory permissions.";
$lang['step_7_ok'] = "...Ok!";
$lang['step_7_fail'] = "...Failed!";
$lang['step_7_loading_languages_xml'] = "Loading languages and phrases from XML file...";
$lang['step_7_loading_adminhelp_xml'] = "Loading admin area help info...";
$lang['step_7_inserting_languages_data'] = "Saving languages and phrases and creating files...";
$lang['step_7_inserting_adminhelp_data'] = "Saving admin area help info...";
$lang['step_7_saving_default_lang'] = "Saving default language...";
$lang['step_7_finish_message'] = "Languages generated. Hit 'Next Step...' when ready to generate the board templates. (Note; this step may take a long time.)";


// ---------------------------
// STEP 8
// ---------------------------
$lang['step_8_message'] = "Creating templates.";
$lang['step_8_ok'] = "...Ok!";
$lang['step_8_fail'] = "...Failed!";
$lang['step_8_checking_dir_perms'] = "Checking 'templates' directory is writable...";
$lang['step_8_writable_perms_fail'] = "..Failed! Check directory permissions.";
$lang['step_8_loading_templates_xml'] = "Loading templates from XML file...";
$lang['step_8_inserting_templates_data'] = "Saving templates and creating files...";
$lang['step_8_finish_message'] = "Templates generated. Hit 'Next Step...' when ready to save the board themes.";


// ---------------------------
// STEP 9
// ---------------------------
$lang['step_9_message'] = "Saving themes.";
$lang['step_9_ok'] = "...Ok!";
$lang['step_9_fail'] = "...Failed!";
$lang['step_9_loading_themes_xml'] = "Loading themes from XML file...";
$lang['step_9_inserting_themes_data'] = "Saving themes...";
$lang['step_9_finish_message'] = "Themes saved. Hit 'Next Step...' when ready to generate emoticons, avatars and post icons. (Note; this step may take a long time.)";


// ---------------------------
// STEP 10
// ---------------------------
$lang['step_10_message'] = "Saving and creating emoticons, avatars and post icons.";
$lang['step_10_ok'] = "...Ok!";
$lang['step_10_fail'] = "...Failed!";
$lang['step_10_checking_avatar_perms'] = "Checking 'upload/avatar' directory is writable...";
$lang['step_10_checking_emoticons_perms'] = "Checking 'upload/emoticon' directory is writable...";
$lang['step_10_checking_post_icon_perms'] = "Checking 'upload/post_icon' directory is writable...";
$lang['step_10_writable_perms_fail'] = "..Failed! Check directory permissions.";
$lang['step_10_loading_avatars_xml'] = "Loading avatars from XML file...";
$lang['step_10_inserting_avatars_data'] = "Saving avatar info and generating images...";
$lang['step_10_loading_emoticons_xml'] = "Loading emoticons from XML file...";
$lang['step_10_inserting_emoticons_data'] = "Saving emoticons info and generating images...";
$lang['step_10_loading_post_icons_xml'] = "Loading post icons from XML file...";
$lang['step_10_inserting_post_icons_data'] = "Saving post icons info and generating images...";
$lang['step_10_finish_message'] = "Avatars, emoticons and post icons generated. Hit 'Next Step...' when ready to create the administrator account.";


// ---------------------------
// SETP 11
// ---------------------------
$lang['step_11_message'] = "Create the default administrator account.";
$lang['step_11_ok'] = "...Ok!";
$lang['step_11_fail'] = "...Failed!";
$lang['step_11_form_title'] = "Account information";
$lang['step_11_form_admin_username'] = "Administrator username";
$lang['step_11_form_admin_username_message'] = "Desired username for the root administrator account on your message board. Must be between 2 and 25 characters.";
$lang['step_11_form_admin_password'] = "Account password";
$lang['step_11_form_admin_password_message'] = "Desired password for this account, must be between 4 and 14 characters.";
$lang['step_11_form_admin_password2'] = "Re-enter password";
$lang['step_11_form_admin_password2_message'] = "Input the password again for verification.";
$lang['step_11_form_admin_email'] = "Account e-mail address";
$lang['step_11_verify_account_info'] = "Verifying account information...";
$lang['step_11_fail_username_invalid'] = "...Failed! Username is invalid.";
$lang['step_11_fail_password_invalid'] = "...Failed! Password is invalid.";
$lang['step_11_fail_email_invalid'] = "...Failed! E-mail is invalid.";
$lang['step_11_fail_password_match'] = "...Failed! Entered passwords do not match.";
$lang['step_11_insert_account'] = "Inserting administrator account into database...";
$lang['step_11_next_step'] = "Create account...";
$lang['step_11_finish_message'] = "Almost done! The last step will do a few minor tasks, build the board cache and try to lock the installer. Hit 'Next Step...' when ready.";


// ---------------------------
// STEP 12
// ---------------------------
$lang['step_12_message'] = "Lastly, do a few minor tasks and build the cache.";
$lang['step_12_ok'] = "...Ok!";
$lang['step_12_fail'] = "...Failed!";
$lang['step_12_save_task_runtime'] = "Saving next common task run...";
$lang['step_12_build_cache'] = "Build message board cache...";
$lang['step_12_lock_board'] = "Trying to lock installer...";
$lang['step_12_finish_message'] = "Wahey! All the steps have been completed and that should mean that everything went swimmingly well. With any luck your new shiny message board should be installed.<br /><br /><b>".
				"Note</b>: Although I tried to lock the installer (It should say above if that worked or not.) It is still recommended that you delete the install directory, just in case...<br /><br />".
				"Now you should click the link below to visit your message board and login. If anything goes wrong and you can't work out why, post on <a href=\"http://www.fsboard.com\" target=\"_blank\">www.fsboard.com</a> or contact <a href=\"mailto:fiona@fsboard.com\">Fiona</a>." .
				"<br /><br /><div style=\"text-align : center; margin : 20px;\"><b><a href=\"".ROOT."index.php\">Click here to go your message board's index!</a></b></div>";		


// ---------------------------
// DB connect 
// ---------------------------
$lang['db_connect_ok'] = "...Ok!";
$lang['db_connect_fail'] ="...Failed.";
$lang['db_connect_get_db_info'] = "Getting database info...";
$lang['db_connect_get_db_info_fail_message'] = "There was an error including the db_config.php file or the required info was not found. Ensure that step 4 was completed properly.";
$lang['db_connect_connect_db'] = "Connecting to database...";
$lang['db_connect_connect_db_fail_message'] = "There was an error connecting to the database. Ensure that the relevant information is in db_config.php and that step 4 was completely sucessfully.";


// ---------------------------
// Step names and titles
// ---------------------------
$lang['step_1_title'] = "(Step 1)";
$lang['step_1_menu'] = "1) Initial message";
$lang['step_1_name'] = "Initial Installer Message";

$lang['step_2_title'] = "(Step 2)";
$lang['step_2_menu'] = "2) File check";
$lang['step_2_name'] = "File and Directory Permissions Check";

$lang['step_3_title'] = "(Step 3)";
$lang['step_3_menu'] = "3) Database info";
$lang['step_3_name'] = "Supply SQL Database Settings";

$lang['step_4_title'] = "(Step 4)";
$lang['step_4_menu'] = "4) Creating database";
$lang['step_4_name'] = "Create Tables in the Database";

$lang['step_5_title'] = "(Step 5)";
$lang['step_5_menu'] = "5) Populate database";
$lang['step_5_name'] = "Insert Default Database Data";

$lang['step_6_title'] = "(Step 6)";
$lang['step_6_menu'] = "6) Load configuration";
$lang['step_6_name'] = "Insert Default Config Settings";

$lang['step_7_title'] = "(Step 7)";
$lang['step_7_menu'] = "7) Create languages";
$lang['step_7_name'] = "Insert Default Languages and Phrases";

$lang['step_8_title'] = "(Step 8)";
$lang['step_8_menu'] = "8) Create templates";
$lang['step_8_name'] = "Insert Default Templates";

$lang['step_9_title'] = "(Step 9)";
$lang['step_9_menu'] = "9) Create themes";
$lang['step_9_name'] = "Insert Default themes";

$lang['step_10_title'] = "(Step 10)";
$lang['step_10_menu'] = "10) Create small images";
$lang['step_10_name'] = "Generate Emoticons, Avatars and Post Icons";

$lang['step_11_title'] = "(Step 11)";
$lang['step_11_menu'] = "11) Admin account";
$lang['step_11_name'] = "Create Admin Account";

$lang['step_12_title'] = "(Step 12)";
$lang['step_12_menu'] = "12) Initial cache build";
$lang['step_12_name'] = "Generate the Board Cache";


// ---------------------------
// Default user title text
// ---------------------------
$lang['default_user_title_0'] = "Total Newbie";
$lang['default_user_title_100'] = "Regular Poster";
$lang['default_user_title_500'] = "Veteran Poster";
$lang['default_user_title_2500'] = "Serious Spammer";

// ---------------------------
// Default reputation title text
// ---------------------------
$lang['default_reputation_title_-500'] = "A relatively unknown user.";
$lang['default_reputation_title_-250'] = "A relatively unknown user.";
$lang['default_reputation_title_-100'] = "A relatively unknown user.";
$lang['default_reputation_title_-10'] = "A relatively unknown user.";
$lang['default_reputation_title_0'] = "A relatively unknown user.";
$lang['default_reputation_title_10'] = "A known user.";
$lang['default_reputation_title_50'] = "A reasonably liked user.";
$lang['default_reputation_title_100'] = "A well known user.";
$lang['default_reputation_title_250'] = "A very popular user.";
$lang['default_reputation_title_500'] = "An extremely popular user.";
$lang['default_reputation_title_1000'] = "Everyone\'s friend..";


?>
