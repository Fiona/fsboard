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
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Admin
 * 
 * @started 20 May 2007
 * @edited 20 May 2007
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



/**
 * Quick function for creating the language variable name or an entry.
 * 
 * @param string $email_text Content of the e-mail to replace the variables on
 * @param array $user_info Array of information to replace the variables with
 * @return string The completed string
 */
function replace_email_variables($email_text, $user_info)
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
                date("F jS, Y", $user_info['registered']),
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
function send_waiting_mails()
{

        global $db, $cache;

        // *****************************
        // Start by getting set
        // *****************************
        $set_select = $db -> basic_select("mass_mailer", "*", "emails_left > '0'", "id", "0, 1", "asc");
        

        // *****************************
        // Nothing there? Let's rectify whatever mistake happened.
        // *****************************
        if($db -> num_rows() < 1)
        {
                cache_waiting_mail_update(0);
                return;
        }
        
        $mailer_set = $db -> fetch_array();
        

        // *****************************
        // Grab next set of emails
        // *****************************
        $emails_select = $db -> basic_select("mass_mailer_emails", "*", "set_id = '".$mailer_set['id']."'", "id", "0, ".$mailer_set['bulk_num'], "asc");

        $email_amount = $db -> num_rows($emails_select);
        

        // *****************************
        // No e-mails? Something messed up... :(
        // *****************************
        if($email_amount < 1)
        {

                $db -> basic_update("mass_mailer", array('emails_left' => 0), "id = '".$mailer_set['id']."'");
                cache_waiting_mail_update(0, true);

        }
        

        // *****************************
        // Go through each e-mail sending
        // *****************************
        $email_class = new email;
        $email_class -> from_address = $mailer_set['from_email'];
        
        while($email_array = $db -> fetch_array($emails_select))
                $email_class ->  send_mail($email_array['to_email'], $email_array['subject'], $email_array['contents'], $mailer_set['test']);


        // *****************************
        // Delete e-mails sent
        // *****************************
        $db -> basic_delete("mass_mailer_emails", "set_id = '".$mailer_set['id']."'", "id", $email_amount, "asc");
        

        // *****************************
        // Update the set entry
        // *****************************
        $new_emails_left = $mailer_set['emails_left'] - $email_amount;
        
        if($new_emails_left < 0)
                $new_emails_left = 0;
                
        $db -> basic_update("mass_mailer",
                array(
                        'emails_left' => $new_emails_left,
                        'emails_sent' => $mailer_set['emails_sent'] + $email_amount
                ),
                "id = '".$mailer_set['id']."'");


        // *****************************
        // Need to update the config?
        // *****************************
        cache_waiting_mail_update(0, true);
        
}


/**
 * Used with send_waiting_mails() to update the config cache quickly.
 *
 * @param int $value What to update the config to.
 * @param bool $check_left Check if there are any emails left to send?
 */
function cache_waiting_mail_update($value, $check_left = false)
{

        global $db, $cache;
        
        if($check_left)
        {

                $set_select = $db -> basic_select("mass_mailer", "*", "emails_left > '0'", "id", "0, 1", "asc");

                if($db -> num_rows() < 1)
                        $value = 0;
                else
                        $value = 1;

        }

        if($value != $cache -> cache['config']['mass_mailer_waiting'])
        {
        
                $db -> basic_update("config", array('value' => $value), "name='mass_mailer_waiting'");

                // Update cache
                $cache -> cache['config']['mass_mailer_waiting'] = $value;
                $info = array("content" => serialize($cache -> cache['config']));

                $db -> basic_update("cache", $info, "name='config'");
                
        }
        
}


?>
