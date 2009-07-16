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
 * Installer output class
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


// Get the normal output class so we can share some functions
require ROOT."common/class/output.class.php";


class install_output extends output
{

	var $same_step = false;
	var $next_step_submit_text = "";
	var $completed_install = false;

	// ---------------------------------------------
	// Wrapper wraps around the whole page
	// ---------------------------------------------
	function install_wrapper()
	{
		
		global $lang, $fsboard_install_version, $current_step, $db, $install_step_fail, $main_form;

		// *********************************************
		// Show debug stuff if wanted
		// *********************************************
		$debug_level_2 = (defined("DEBUG_MODE")) ? $this -> return_debug_level(2) : "";
		
		// *********************************************
		// Steps listing/menu/diagram/whatever
		// *********************************************
		$return_menu = $this -> install_menu();
		
		// *********************************************
		// Next Step button
		// *********************************************
		$start_submit = $main_form -> start_form("nextstep", ROOT."install/index.php", "post");
		$return_submit = $main_form -> hidden("language", CURRENT_LANGUAGE);
		
		if($this -> same_step)
		{
			$return_submit .= $main_form -> hidden("step", $current_step);
			$return_submit .= $main_form -> submit("submit", $this -> next_step_submit_text);
		}
		elseif($install_step_fail)
		{
			$return_submit .= $main_form -> hidden("step", $current_step);
			$return_submit .= $main_form -> submit("submit", $lang['redo_step']);
		}
		else
		{
			$return_submit .= $main_form -> hidden("step", $current_step + 1);
			$return_submit .= $main_form -> submit("submit", $lang['next_step']);
		}
		
		$end_submit = $main_form -> end_form();
		$return_submit = '<tr><td class="submit_cell">'.$return_submit.'</td></tr>';
		
		// whoops
		if($this -> completed_install)
		{
			$start_submit = "";
			$end_submit = "";
			$return_submit = "";			
		}
		

		// *********************************************
		// Page itself
		// *********************************************		
		$return = <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
        <head>
        
		<meta HTTP-EQUIV="content-type" CONTENT="text/html; charset={$lang['charset']}">
                <title>{$lang['title']} - {$this -> page_title}</title>
                <link rel="stylesheet" type="text/css" href="style/install_style.css" />
                
        </head>
        <body>

		<div class="logo_div" align="left">
	       		<img src="style/logo.png" alt="{$lang['title']}">
        	</div>

		{$start_submit}
		        			
		<table class="main_table" align="center">
			<tr>
			
				<td class="title_strip">
					{$lang['title']} {$lang['step_'.$current_step.'_title']}
				</td>
				
			</tr>
			<tr>
			
				<td class="normal_cell">
					{$lang['installer_top_message']}
				</td>
			
			</tr>
			<tr>
			
				<td class="header_strip">
					{$lang['step_'.$current_step.'_name']}
				</td>
				
			</tr>
			<tr>
			
				<td class="normal_cell">
					{$return_menu}
					{$this -> page_output}
				</td>
			
			</tr>
					{$return_submit}
		</table>

		{$end_submit}
		
		<p class="footer">
	                <a href="http://www.fsboard.com/">FSBoard</a> Version {$fsboard_install_version} &copy; 2006 <a href="mailto:fiona@fsboard.com">Fiona Burrows</a><br />
	        </p>


	        <div style="margin:5px">
	                $debug_level_2
	        </div>
                	
        </body>
</html>               		
		
END;

		return $return;
		
	}



	// ---------------------------------------------
	// The side menu thing
	// ---------------------------------------------
	function install_menu()
	{
		
		global $lang, $fsboard_install_version, $current_step, $db;

		$return = <<<END
			<div class="installstepswrapper">
				<table class="installstepstable">
					<tr>
						<td class="installstepsheader">
							<p class="installstepsheadertext">
	                        				{$lang['steps_menu_title']}
							</p>
						</td>
					</tr>
	
					<tr>
						<td class="installstepscell">
							<p class="installstepstext">
END;

		for($a = 1; $a <= 12; $a++)
			$return .= $this -> install_menu_step_text($a);

		$return .= <<<END
						</td>
					</tr>
	
				</table>
			</div>		
END;

		return $return;

	}
	


	// ---------------------------------------------
	// Menu entry
	// ---------------------------------------------
	function install_menu_step_text($wanted_step)
	{
	
		global $lang, $current_step;	
	
		$text = $lang['step_'.$wanted_step.'_menu'];
		
		$return = "";
		
		$return .= ($wanted_step == $current_step) ? "<img src=\"style/current_step.gif\" /><b>" : "";
	
		$return .= <<<END
								{$text}<br />
END;
	
		$return .= ($wanted_step == $current_step) ? "</b>" : "";	
		
		return $return;
			
	}
	
	

	// ---------------------------------------------
	// Action text
	// ---------------------------------------------
	 function install_step_action($success, $action_message, $outcome_message)
	 {

		if($success)
			$text_class = "installactionoutcomeok"; 
		else
			$text_class = "installactionoutcomefail"; 

		$return = <<<END
		
			<p class="installactionwrapper">
				<span class="installactionfloat {$text_class}">
					{$outcome_message}
				</span> 
				{$action_message}
			</p>
END;
		
		return $return;	 	
	 	
	 }
	 

	// ---------------------------------------------
	// MySQL error box 
	// ---------------------------------------------
	 function mysql_error_box()
	 {

		global $db;
		
                if($db -> log_errorno > -1)
	            	return "<p class=\"sql_error\"><b>Error ".$db -> log_errorno."</b><br />".$db -> log_error."</p>";
				 
	 }
	 

	// ---------------------------------------------
	// Form for inputting the database info
	// ---------------------------------------------
	 function install_step_3_form($submit_info)
	 {
	 	
		global $lang, $main_form;
			 	
		$table = new table_generate();
		
                $return = $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").

                $table -> add_basic_row($lang['step_3_form_title'], "title_strip",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_db_type'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_db_type_message']."</form>"
                                , "50%"),
                                array(step_3_database_dropdown($submit_info['sql_db_type']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_server'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_server_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("sql_server", $submit_info['sql_server']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_port'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_port_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("sql_port", $submit_info['sql_port']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_db_name'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_db_name_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("sql_db_name", $submit_info['sql_db_name']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_username'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_username_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("sql_username", $submit_info['sql_username']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_password'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_password_message']."</form>"
                                , "50%"),
                                array($main_form -> input_password("sql_password", $submit_info['sql_password']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_3_form_table_prefix'].
                                	"<br /><font class=\"small_text\">".$lang['step_3_form_table_prefix_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("sql_table_prefix", $submit_info['sql_table_prefix']), "50%")
                        )
                , "normal_cell").
		
		$table -> end_table();

		return $return;
			 	
	 }




	// ---------------------------------------------
	// Form for inputting some default config
	// ---------------------------------------------
	 function install_step_6_form()
	 {
	 	
		global $lang, $main_form;

		// ------------
	        // Guess the script root
		// ------------
	        $guess_board_url = str_replace("install/index.php", "", $_SERVER['PHP_SELF']);
	        $guess_board_url = _substr($guess_board_url, 0, _strlen($guess_board_url) -1);
	        $guess_board_url = 'http://'.$_SERVER['SERVER_NAME'].$guess_board_url;

		// ------------
		// form
		// ------------
		$table = new table_generate();
		
                $return = $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		$main_form -> hidden("submit_config", "true").

                $table -> add_basic_row($lang['step_6_form_title'], "title_strip",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_6_form_board_name'].
                                	"<br /><font class=\"small_text\">".$lang['step_6_form_board_name_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("board_name", $lang['step_6_form_default_board_name']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_6_form_board_url'].
                                	"<br /><font class=\"small_text\">".$lang['step_6_form_board_url_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("board_url", $guess_board_url), "50%")
                        )
                , "normal_cell"). 		
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_6_form_mail_from_address'].
                                	"<br /><font class=\"small_text\">".$lang['step_6_form_mail_from_address_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("mail_from_address", $lang['step_6_form_default_mail_from_address']), "50%")
                        )
                , "normal_cell"). 		

		$table -> add_basic_row($lang['step_6_form_title_2'], "title_strip",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_6_form_home_name'].
                                	"<br /><font class=\"small_text\">".$lang['step_6_form_home_name_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("home_name", $lang['step_6_form_default_home_name']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_6_form_home_address'].
                                	"<br /><font class=\"small_text\">".$lang['step_6_form_home_address_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("home_address", $lang['step_6_form_default_home_address']), "50%")
                        )
                , "normal_cell").

		$table -> end_table();

		$this -> same_step = true;
		$this -> next_step_submit_text = $lang['step_6_next_step'];

		return $return;
			 	
	 }



	// ---------------------------------------------
	// Form for the default admin account
	// ---------------------------------------------
	 function install_step_11_form($input)
	 {
	 	
		global $lang, $main_form;

		// ------------
		// form
		// ------------
		$table = new table_generate();
		
                $return = $table -> start_table("", "margin-top : 10px; border-collapse : collapse;", "center", "95%").
		$main_form -> hidden("submit_admin", "true").

                $table -> add_basic_row($lang['step_11_form_title'], "title_strip",  "", "left", "100%", "2").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_11_form_admin_username'].
                                	"<br /><font class=\"small_text\">".$lang['step_11_form_admin_username_message']."</form>"
                                , "50%"),
                                array($main_form -> input_text("admin_username", $input['username']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_11_form_admin_password'].
                                	"<br /><font class=\"small_text\">".$lang['step_11_form_admin_password_message']."</form>"
                                , "50%"),
                                array($main_form -> input_password("admin_password", $input['password']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array(
                                	$lang['step_11_form_admin_password2'].
                                	"<br /><font class=\"small_text\">".$lang['step_11_form_admin_password2_message']."</form>"
                                , "50%"),
                                array($main_form -> input_password("admin_password2", $input['password2']), "50%")
                        )
                , "normal_cell").
                $table -> add_row(
                        array(
                                array($lang['step_11_form_admin_email'], "50%"),
                                array($main_form -> input_text("admin_email", $input['email']), "50%")
                        )
                , "normal_cell").

		$table -> end_table();

		$this -> same_step = true;
		$this -> next_step_submit_text = $lang['step_11_next_step'];

		return $return;	
		
	 }
	 			
}


?>
