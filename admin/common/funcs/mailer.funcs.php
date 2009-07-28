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
 * Mass-mailer functions
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


/**
 * Quick function for creating the language variable name or an entry.
 * 
 * @param string $email_text Content of the e-mail to replace the variables on
 * @param array $user_info Array of information to replace the variables with
 * @return string The completed string
 */
function mailer_replace_email_variables($email_text, $user_info)
{
        
	global $cache;
        
	$variable_list = array(
		"{board_name}",
		"{user_num}",
		"{board_url}",
		"{post_num}",
		"{user_id}",
		"{user_name}",
		"{user_joined}",
		"{user_posts}",
		"{user_email}",
		"{user_usergroup}"
        );
	
	$replacement_list = array(
		$cache -> cache['config']['board_name'],
		$cache -> cache['stats']['total_members'],
		$cache -> cache['config']['board_url'],
		$cache -> cache['stats']['total_posts'],
		$user_info['id'],
		$user_info['username'],
		return_formatted_date("F jS, Y", $user_info['registered']),
		$user_info['posts'],
		$user_info['email'],
		$cache -> cache['user_groups'][ $user_info['user_group'] ]['name']
        );
	
	$email_text = str_replace(
		$variable_list,
		$replacement_list,
		$email_text
        );
      
	return $email_text;
        
}


/**
 * Runs at shut down, checks for waiting mails and sends next batch.
 */
function mailer_send_waiting_mails($include_root = "")
{

	global $db, $cache;

	// Start by getting a waiting mailer set
	$set_select = $db -> basic_select(
		array(
			"table" => "mass_mailer",
			"where" => "emails_left > 0",
			"order" =>  "id",
			"direction" => "asc",
			"limit" => 1
			)
		);
        

	// Nothing there? Let's rectify whatever mistake happened.
	if(!$db -> num_rows())
	{
		mailer_cache_waiting_mail_update(0, False, $include_root);
		return;
	}

	$mailer_set = $db -> fetch_array();


	// Grab next set of emails from this set
	$emails_select = $db -> basic_select(
		array(
			"table" => "mass_mailer_emails",
			"where" =>  "set_id = ".(int)$mailer_set['id'],
			"order" => "id",
			"direction" => "asc",
			"limit" =>  "0, ".$mailer_set['bulk_num']
			)
		);

	$email_amount = $db -> num_rows();

	// No e-mails? Something messed up... :(
	if($email_amount < 1)
	{
		$db -> basic_update(
			array(
				"table" => "mass_mailer",
				"data" => array(
					"emails_left" => 0
					),
				"where" => "id = ".(int)$mailer_set['id']
				)
			);

		mailer_cache_waiting_mail_update(0, true, $include_root);
	}
        

	// Go through each e-mail and send it off
	$email_class = new email;
	$email_class -> from_address = $mailer_set['from_email'];
        
	while($email_array = $db -> fetch_array($emails_select))
		$email_class ->  send_mail($email_array['to_email'], $email_array['subject'], $email_array['contents'], $mailer_set['test']);


	// Delete e-mails sent
	$db -> basic_delete(
		array(
			"table" => "mass_mailer_emails",
			"where" => "set_id = ".(int)$mailer_set['id'],
			"order" => "id",
			"direction" => "asc",
			"limit" => $email_amount
			)
		);
        

	// Update the set entry
	$new_emails_left = $mailer_set['emails_left'] - $email_amount;
        
	if($new_emails_left < 0)
		$new_emails_left = 0;
                
	$db -> basic_update(
		array(
			"table" => "mass_mailer",
			"data" => array(
				"emails_left" => $new_emails_left,
				"emails_sent" => $mailer_set['emails_sent'] + $email_amount
				),
			"where" => "id = ".(int)$mailer_set['id']
			)
		);


	// Update the config value
	mailer_cache_waiting_mail_update(0, true, $include_root);
        
}


/**
 * Used with mailer_send_waiting_mails() to update the config cache quickly.
 *
 * @param int $value What to update the config to.
 * @param bool $check_left Check if there are any emails left to send?
 */
function mailer_cache_waiting_mail_update($config_value = 0, $check_left = false, $include_root = "")
{

	global $db, $cache;
        
	if($check_left)
	{
		$db -> basic_select(
			array(
				"table" => "mass_mailer",
				"where" => "emails_left > 0",
				"order" => "id",
				"direction" => "asc",
				"limit"=>  "1"
				)
			);

		if($db -> num_rows())
			$config_value = 1;
	}

	// We check how many emails FSBoard currently thinks is waiting and update
	// the configuration with it if necessary.
	if($config_value != $cache -> cache['config']['mass_mailer_waiting'])
	{
        
		$db -> basic_update(
			array(
				"table" => "config",
				"data" => array(
					"value" => $config_value
					),
				"where" => "name='mass_mailer_waiting'"
				)
			);

		// Update cache
		$cache -> update_single_cache("config", $include_root);
                
	}
        
}

?>
