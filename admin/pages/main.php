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
 * Admin area main page
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
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

if(isset($page_matches['mode']) && $page_matches['mode'] == "check_friendly_urls")
	page_check_friendly_urls();
else
	page_main();


/**
 * Main page of the admin area
 * It provides an introduction to the admin area including the help system,
 * an easy access to search for users, a place to store admin only notes and
 * provides various bits of information about the system environment.
 */
function page_main()
{
	global $db, $output, $lang, $cache, $template_global;

	// Get config and stat values
	$board_info = array(
		'fsboard_version' => $cache -> cache['config']['current_version'],
		'php_version' => phpversion(),
		'php_server_os' => php_uname ("s"),
		'php_server_software' => $_SERVER['SERVER_SOFTWARE'],
		'php_database' => $db -> database_info_string(),
		'post_max_size' => ini_get('post_max_size'),
		'upload_max_filesize' => ini_get('upload_max_filesize'),
		'admin_notes' => $cache -> cache['config']['admin_notes']
	);

	$db -> basic_select(array(
		"table" => "users",
		"what" => "COUNT(*)",
		"where" => "`registered` >= ".(TIME-86400)
	));
	$board_info['new_users_today'] = $db -> result() ? $db -> result() : 0;

	$db -> basic_select("users", "COUNT(*)", "`last_active` >= ".(TIME-86400));
	$board_info['users_online_today'] = $db -> result() ? $db -> result() : 0;

	//***********************************************
	// Top part of page
	//***********************************************
	$output -> add(
		$template_global -> generic_info_wrapper(
			$lang['admin_main_page'],
			$template_global -> generic_info_content(
				$output -> replace_number_tags($lang['admin_main_page_message'],
					array(
						l("admin/config/"),
						l("admin/forums/"),
						$cache -> cache['config']['board_url']
					)
				)
			)
		)
	);


	$form = new form(array(
        "meta" => array(
			"name" => "user_search",
        	"title" => $lang['admin_quick_user_moderation'],
			"complete_func" => "form_user_search"	
        ),
        
        "#username" => array(
        	"type" => "text",
        	"name" => $lang['admin_quick_user_moderation'],
        	"required" => True
        ),

		"#submit" => array(
			"type" => "submit",
			"value" => $lang['admin_search']
		)
	));

	$output -> add($form -> render());


	$form = new form(array(
        "meta" => array(
			"name" => "admin_notes",
        	"title" => $lang['admin_notes'],
			"complete_func" => "form_admin_notes_complete"	
        ),
        
        "#notes" => array(
        	"type" => "textarea",
        	"name" => $lang['admin_notes'],
        	"required" => True,
			"value" => $board_info['admin_notes']
        ),

		"#submit" => array(
			"type" => "submit",
			"value" => $lang['admin_save_note']
		)
	));

	$output -> add($form -> render());


	$output -> add(
		$template_global -> generic_info_wrapper(
			$output -> return_help_button("", false).$lang['admin_board_information'],
			$template_global -> generic_info_content(
				"<dl class=\"admin_info_list\">".
					"<dt>".$lang['admin_fsboard_version'].'</dt> <dd><b>'.$board_info['fsboard_version'].'</b></dd>'.
					"<dt>".$lang['admin_php_version'].'</dt> <dd><b>'.$board_info['php_version'].'</b> [ <a href="'.ROOT.'admin/index.php?m=phpinfo">'.$lang['get_php_info'].'</a> ]</dd>'.
				"</dl>".

				"<dl class=\"admin_info_list\">".
					"<dt>".$lang['admin_server_os'].'</dt> <dd><b>'.$board_info['php_server_os'].'</b></dd>'.
					"<dt>".$lang['admin_server_software'].'</dt> <dd><b>'.$board_info['php_server_software'].'</b></dd>'.
					"<dt>".$lang['admin_database_version'].'</dt> <dd><b>'.$board_info['php_database'].'</b></dd>'.
				"</dl>".

				"<dl class=\"admin_info_list\">".
					"<dt>".$lang['admin_php_post_size'].'</dt> <dd><b>'.$board_info['post_max_size'].'</b></dd>'.
					"<dt>".$lang['admin_php_upload_filesize'].'</dt> <dd><b>'.$board_info['upload_max_filesize'].'</b></dd>'.
				"</dl>".

				"<dl class=\"admin_info_list\">".
					"<dt>".$lang['admin_new_users'].'</dt> <dd><b>'.$board_info['new_users_today'].'</b></dd>'.
					"<dt>".$lang['admin_new_threads'].'</dt> <dd><b>#</b></dd>'.
					"<dt>".$lang['admin_new_posts'].'</dt> <dd><b>#</b></dd>'.
					"<dt>".$lang['admin_users_online'].'</dt> <dd><b>'.$board_info['users_online_today'].'</b></dd>'.
				"</dl>".
				'<div class="small_text" style="clear : left; text-align:center;">'.$output -> replace_number_tags($lang['admin_board_statistics_message'], array(l("admin/stats/"))).'</div>',
				False
			)
		)
	);


	$output -> add(
		$template_global -> generic_info_wrapper(
			$lang['fsboard_credits_title'],
			$template_global -> generic_info_content(
				"<dl class=\"admin_info_list\">".
					"<dt>".$lang['credits_lead_programmer']."</dt> <dd><b>Fiona Burrows</b></dd>".
					"<dt>".$lang['credits_additional_coding']."</dt> <dd><b>Mark Frimston</b></dd>".
					"<dt>".$lang['credits_geshi']."</dt> <dd><b><a href=\"http://qbnz.com/highlighter\" target=\"_blank\">Nigel McNie</a></b></dd>".
					"<dt>".$lang['credits_codemirror']."</dt> <dd><b><a href=\"http://marijn.haverbeke.nl/codemirror/\" target=\"_blank\">Marijn Haverbeke</a></b></dd>".
					"<dt>".$lang['credits_utf8_functions']."</dt> <dd><b>Niels Leenheer &amp; Andy Matsubara</b></dd>".
					"<dt>".$lang['credits_special_thanks']."</dt> <dd><b><a href=\"http://code.google.com\">Google Code</a> &amp; <a href=\"http://www.github.com\">GitHub</a></b></dd>".
				"</dl>",
				False
			)
		)
	);

}



/**
 * Completion function for user search form
 */
function form_user_search($form)
{

	die();

}


/**
 * Completion function for admin notes form
 */
function form_admin_notes_complete($form)
{

	global $db, $output, $lang, $cache;

	$cache -> cache['config']['admin_notes'] = $form -> form_state['#notes']['value'];

	// Update DB
	$db -> basic_update(array(
		"table" => "config",
		"where" => "name='admin_notes'",
		"data" => array("value" => $cache -> cache['config']['admin_notes']),
	));

	// Update cache
	$cache ->  update_single_cache("config");

	// Redirect the user
	$form -> form_state['meta']['redirect'] = array(
		"url" => l("admin/"),
		"message" => $lang['admin_saved_note']
	);

}




/**
 * Friendly URL check
 * This page purely exists to make sure friendly urls work. Simply 
 * gives a message going "yup".
 */
function page_check_friendly_urls()
{

	global $db, $output, $lang, $cache, $template_global;

	$output -> add(
		$template_global -> generic_info_wrapper(
			$lang['admin_friendly_urls_title'],
			$template_global -> generic_info_content($lang['admin_friendly_urls_message'])
			)
	);

}

?>