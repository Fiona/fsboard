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
 * Admin specific output routines
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");



class admin_output extends output
{

	/**
	 * ...
	 */
	var $theme_folder = '';

	/**
	 * ...
	 */
	var $breadcrumb = array();

	/**
	 * ...
	 */
	var $show_breadcrumb = true;  
        

	/**
	 * ...
	 * @param $title
	 * @param $url
	 */
	function add_breadcrumb($title, $url)
	{
                
		$this -> breadcrumb[] = array("title" => $title, "url" => $url);   
             
	}       


	/**
	 * ...
	 * @param $field
	 * @param $text
	 */
	function return_help_button($field = "", $text = false)
	{

		return $this -> help_button($field, $text);

	}

     
	/**
	 * ...
	 * @param $field
	 * @param $text
	 * @param $different_page
	 * @param $different_action
	 */   
	function help_button($field = "", $text = false, $different_page = NULL, $different_action = NULL)
	{
        
		global $cache, $page_matches, $template_admin;

		$page = is_null($different_page) ? CURRENT_MODE : $different_page;

		if(is_null($different_action))
			$action = isset($page_matches['mode']) ? $page_matches['mode'] : "";
		else
			$action = $different_action;

		if(!$action)
		{
			if(isset($cache -> cache['admin_area_help'][$page]['__yes__']))
				return $template_admin -> help_button($text, $page);
			else
				return "";
		}
		elseif(!$field)
		{
			if(isset($cache -> cache['admin_area_help'][$page][$action]['__yes__']))
				return $template_admin -> help_button($text, $page, $action);
			else
				return "";
		}
		else
		{
			if(isset($cache -> cache['admin_area_help'][$page][$action][$field]['__yes__']))
				return $template_admin -> help_button($text, $page, $action, $field);
			else
				return "";
		}
			
	}        
        
 
	/**
	 * Display a confirmation message for a specific action, when the user
	 * confirms to the action a callback will be called and then optionally
	 * a redirect will occur.
	 *
	 * @param array $settings Array of settings describting the confirmation.
	 *		Keys are as follows:
	 * 		(string) title: Title that appears on the confirmation. (Required)
	 * 		(string) description: Description of what is being confirmed. (Required)
	 * 		(callback) callback: PHP callback - the function that will be called
	 * 			if the user confirms. Should return True/False on success. (Required)
	 * 		(string) confirm_redirect: URL that the user will be redirected to after the
	 * 			callback is called and finishes. (Required)
	 * 		(string) cancel_redirect: URL that the user will be redirected if they cancel. (Required)
	 * 		(array) arguments: Array of arguments that will be passed to the callback.
	 * 		(string) extra_title_contents_left: HTML that will be placed to the left
	 * 			of the title. (Usually used for page icons.)
	 * 		(string) extra_title_contents_right: HTML that will be placed to the right
	 * 			of the title. (Usually used for help icons.)
	 * 		(string) admin_sub_menu: Admin area can sometimes have a little sub-menu.
	 */   
	function confirmation_page($info)
	{

		global $template_global, $lang, $output;

		// Make sure we have text in these.
		$info['admin_sub_menu'] = (isset($info['admin_sub_menu']) ? $info['admin_sub_menu'] : "");
		$info['extra_title_contents_left'] = (isset($info['extra_title_contents_left']) ? $info['extra_title_contents_left'] : "");
		$info['extra_title_contents_right'] = (isset($info['extra_title_contents_right']) ? $info['extra_title_contents_right'] : "");

		// if we've confirmed already
		if(isset($_POST['confirm']) && $_POST['confirm'])
		{

			// Call the callback
			if(isset($info['arguments']))
				$responce = call_user_func_array($info['callback'], $info['arguments']);
			else
				$responce = call_user_func($info['callback']);

			// Redirect if all was okay
			if($responce)
				$output -> redirect($info['confirm_redirect'], $lang['confirmation_confirming_action']);
			else
				return $template_global -> confirm_message($info);

		}
		// Or if we've cancelled - redirect away
		elseif(isset($_POST['cancel']) && $_POST['cancel'])
			$output -> redirect($info['cancel_redirect'], $lang['confirmation_cancelling_action']);
		// Otherwise just show the message
		else
			return $template_global -> confirm_message($info);

	}

}

?>