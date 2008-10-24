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
 *       Admin Main Page         *
 *       Started by Fiona        *
 *       08th Aug 2005           *
 *********************************
 *       Last edit by Fiona      *
 *       06th Feb 2006			 *
 *********************************

 */




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


//***********************************************
// If we are wanting the php info
//***********************************************
if(CURRENT_MODE == "phpinfo") die(phpinfo());


//***********************************************
// Get the language file first!
//***********************************************
load_language_group("admin_main");


$_GET['m2'] = (isset($_GET['m2'])) ? $_GET['m2'] : "main";
$secondary_mode = $_GET['m2'];

switch($secondary_mode)
{
	case "notes":
		do_save_notes();
		break;

	case "themechange":
		do_change_theme();
		break;

	case "main":
		page_main();

}


//***********************************************
// If saving the admin notes
//***********************************************
function  do_save_notes()
{

	global $db, $output, $lang, $cache;

	$cache -> cache['config']['admin_notes'] = $_POST['notes'];

	// Update DB
	$db -> basic_update("config", array("value" => $cache -> cache['config']['admin_notes']), "name='admin_notes'");

	// Update cache
	$cache ->  update_single_cache("config");

	// Redirect the user
	$output -> redirect(ROOT."admin/index.php?m=index", $lang['admin_saved_note']);

}



//***********************************************
// If changing the admin theme
//***********************************************
function  do_change_theme()
{

	global $db, $output, $lang, $template_admin, $cache;

	if(!is_dir(ROOT."admin/themes/".$_POST['theme']))
	{
		$output -> add($template_admin -> critical_error($lang['error_admin_theme_exist']));
		page_main();
		return;
	}

	$cache -> cache['config']['admin_area_theme'] = $_POST['theme'];

	// Update config
	$db -> basic_update("config", array("value" => $cache -> cache['config']['admin_area_theme']), "name='admin_area_theme'");

	// Update cache
	$cache -> update_cache("config");    
	
	// Redirect the user
	$output -> redirect(ROOT."admin/index.php", $lang['admin_did_change_theme']);

}



//***********************************************
// Doing nothing special.... So just give us the page.
//***********************************************
function  page_main()
{

	global $db, $output, $lang, $cache;

	// Get config and stat values
	$current_fsboard_version = $cache -> cache['config']['current_version'];
	$php_version = phpversion();
	$php_server_os = php_uname ("s");
	$php_server_software = $_SERVER['SERVER_SOFTWARE'];
	$php_database = $db -> database_info_string();
	$post_max_size = ini_get('post_max_size');
	$upload_max_filesize = ini_get('upload_max_filesize');
	$admin_notes = $cache -> cache['config']['admin_notes'];

	$db -> basic_select("users", "COUNT(*)", "`registered` >= ".(TIME-86400));
	$new_users_today = $db -> result() ? $db -> result() : 0;

	$db -> basic_select("users", "COUNT(*)", "`last_active` >= ".(TIME-86400));
	$users_online_today = $db -> result() ? $db -> result() : 0;

	// Create classes
	$table = new table_generate;
	$user_search_form = new form_generate;
	$admin_notes_form = new form_generate;


	//***********************************************
	// Top part of page
	//***********************************************
	$lang['admin_main_page_message'] = $output -> replace_number_tags($lang['admin_main_page_message'], array(ROOT));

	$output -> add(
	
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
	
		// ---------------
		// Title
		// ---------------
		$table -> add_top_table_header($lang['admin_main_page'], 2).
		$table -> add_basic_row($lang['admin_main_page_message'], "normalcell",  "padding : 10px;", "left", "100%", "2").
		// ---------------
		// Quick select user
		// ---------------
		$table -> add_secondary_table_header($lang['admin_quick_user_moderation'], 2).
		$table -> add_basic_row(
			$user_search_form -> start_form("searchuser", ROOT."admin/index.php?m=users&amp;m2=dosearch", "post").
			$user_search_form -> input_text("username", "", "inputtext", "25%").
			$user_search_form -> hidden("username_search", 0).
			$user_search_form -> submit("submitsearch", $lang['admin_search']).
			$user_search_form -> end_form()
		, "normalcell",  "padding : 10px", "left", "100%", "2").
		// ---------------
		// Admin notes
		// ---------------
		$table -> add_secondary_table_header($lang['admin_notes'], 2).
		$table -> add_basic_row(
			$user_search_form -> start_form("adminnotes", ROOT."admin/index.php?m=index&amp;m2=notes").
			$user_search_form -> input_textbox("notes", $admin_notes, 8, "inputnotes", "90%").
			$user_search_form -> submit("submit", $lang['admin_save_note'], "submitnotes").
			$user_search_form -> end_form()
		, "normalcell",  "padding : 10px", "center", "100%", "2").
		// ---------------
		// Stats
		// ---------------
		$table -> add_row(
			array(
				array($lang['admin_board_information'],"50%"),
				array($output -> return_help_button("", false).$lang['admin_board_statistics'],"50%")
			)
		, "strip2").
		$table -> add_row(
			array(
				array(
					$lang['admin_fsboard_version'].': <b>'.$current_fsboard_version.'</b><br />'.
					$lang['admin_php_version'].': <b>'.$php_version.'</b> [ <a href="'.ROOT.'admin/index.php?m=phpinfo">'.$lang['get_php_info'].'</a> ]<br /><br />'.
					$lang['admin_server_os'].': <b>'.$php_server_os.'</b><br />'.
					$lang['admin_server_software'].': <b>'.$php_server_software.'</b><br />'.
					$lang['admin_database_version'].': <b>'.$php_database.'</b><br /><br />'.
					$lang['admin_php_post_size'].': <b>'.$post_max_size.'</b><br/>'.
					$lang['admin_php_upload_filesize'].': <b>'.$upload_max_filesize.'</b>'
					,"50%"
				),
				array(
					$lang['admin_new_users'].': <b>'.$new_users_today.'</b><br /><br />'.
					$lang['admin_new_threads'].': <b>#</b><br /><br />'.
					$lang['admin_new_posts'].': <b>#</b><br /><br />'.
					$lang['admin_users_online'].': <b>'.$users_online_today.'</b>'.
					'<br /><br /><div class="small_text" align="center">'.$output -> replace_number_tags($lang['admin_board_statistics_message'], array(ROOT)).'</div>'
					,"50%"
				)
			)
			, "normalcell", "vertical-align : top; padding : 10px;"
		).

		$table -> end_table()
					 
	);

	//***********************************************
	// Change Theme
	//***********************************************
	$dir = opendir(ROOT."admin/themes/");
	while($var = readdir($dir))
	{
		if($var != "." && $var != ".." && file_exists(ROOT."admin/themes/".$var."/style.css"))
		{
			$dropdown[] .= str_replace("_", " ", $var);
			$dropdown_values[] .= $var;
		}
	}

	$output -> add(
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		$table -> add_top_table_header($lang['admin_change_theme'], 2).
		$table -> add_basic_row(
			$user_search_form -> start_form("themechange", ROOT."admin/index.php?m=index&amp;m2=themechange", "post", false, false, true).
			$user_search_form -> input_dropdown("theme", $cache -> cache['config']['admin_area_theme'], $dropdown_values, $dropdown, "inputtext", "50%")." ".
			$user_search_form -> submit("submit", $lang['admin_theme_submit']).
			$user_search_form -> end_form()
			, "normalcell",  "padding : 10px", "left", "100%"
		).
		$table -> end_table()
	);


	//***********************************************
	// Credits
	//***********************************************
	$output -> add(
	
		$table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		$table -> add_top_table_header($lang['fsboard_credits_title'], 2).
		// ---------------
		// Me
		// ---------------
		$table -> add_row(
			array(
				array($lang['credits_lead_programmer'],"50%"),
				array("Fiona Burrows","50%")
			)
		, "normalcell").
		// ---------------
		// Mark
		// ---------------
		$table -> add_row(
			array(
				array($lang['credits_additional_coding'],"50%"),
				array("Mark Frimston","50%")
			)
		, "normalcell").
		// ---------------
		// GeSHi
		// ---------------
		$table -> add_row(
			array(
				array($lang['credits_geshi'],"50%"),
				array("<a href=\"http://qbnz.com/highlighter\" target=\"_blank\">Nigel McNie</a>","50%")
			)
	    , "normalcell").
	    // ---------------
	    // UTF-8 guys
	    // ---------------
	    $table -> add_row(
		    array(
			    array($lang['credits_utf8_functions'],"50%"),
			    array("Niels Leenheer &amp; Andy Matsubara","50%")
		    )
	    , "normalcell").
	    // ---------------
	    // Special
	    // ---------------
	    $table -> add_row(
		    array(
		    array($lang['credits_special_thanks'],"50%"),
		    array("<a href=\"http://www.sourceforge.net\" target=\"_blank\">Sourceforge.net</a> &amp; ".
				"<a href=\"http://www.hostingzoom.com\" target=\"_blank\">Hosting Zoom</a>","50%")
		    )
	    , "normalcell").
	
	    $table -> end_table()

	);

}


?>