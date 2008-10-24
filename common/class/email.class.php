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
*       Mailer class            *
*       Started by Fiona        *
*       17th Feb 2005           *
*********************************
*       Last edit by Fiona      *
*       17th Feb 2005           *
*********************************


E-mailing class. Even sends with SMTP too. Oooh...
Create one. Send mail. KABOOM.
*/




// ----------------------------------------------------------------------------------------------------------------------


// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


class email
{

        var $method = "mail";
        var $html_email = 1;
        
        var $from_address = "";
        var $to_address = "";
        var $message_body = "";
        var $subject = "";
        var $headers = "";
        
        var $smtp_socket = "";
        var $smtp_code = 0;
        var $smtp_response = "";
        var $smtp_host = "";
        var $smtp_port = "";
        var $smtp_user = "";
        var $smtp_password = "";
        
        var $complete_from_addres = "";
        
        function email()
        {
        
                global $cache;
                
                // SMTP?
                if($cache -> cache['config']['mail_send_method'] == "smtp")
                {

                        $this -> method = "smtp";
                        $this -> smtp_host = $cache -> cache['config']['mail_smtp_host'];
                        $this -> smtp_port = intval($cache -> cache['config']['mail_smtp_port']);
                        $this -> smtp_user = $cache -> cache['config']['mail_smtp_user'];
                        $this -> smtp_password = $cache -> cache['config']['mail_smtp_password'];
                        
                }

                // HTML e-mails?
                if(!$cache -> cache['config']['mail_html'])
                        $this -> html_email = 0;
                        
                // Set this...
                $this -> from_address = $cache -> cache['config']['mail_from_address'];

                return true;
                
        }


        // -------------------------------------------------------------------------


        function send_mail($to, $subject, $message, $test_only = false)
        {
        
                global $cache;

                // **********************
                // No From?
                // **********************
                if(!trim($this -> from_address))
                {
                        $this -> save_error("No from address.");
                        return false;
                }
        
                // Do some crap
                $this -> complete_from_address = $cache -> cache['config']['board_name'].' <'.$this -> from_address.'>';

                // Remove colons from $from. This would fcuk things up.
                $this -> complete_from_address = str_replace(':', ' ', $this -> complete_from_address);

                // **********************
                // No recipitent
                // **********************
                if(!$to)
                {
                        $this -> save_error("No recipitent specified.");
                        return false;
                }

                $this -> to_address = $to;
        
                // **********************
                // No message
                // **********************
                if(!trim($message))
                {
                        $this -> save_error("Message was empty.");
                        return false;
                }

                $this -> message_body = $message;
                
                // **********************
                // No Subject
                // **********************
                if(!trim($subject))
                {
                        $this -> save_error("Subject was empty.");
                        return false;
                }

                $this -> subject = $subject;
                
                // **********************
                // Headers plz
                // **********************
                $this -> make_headers();

                // **********************
                // Send it!!1111
                // **********************
                if($test_only)
                        $this -> save_log();
                else
                {
                
                        if($this -> method == "mail")
                        {
                
                                // Doing PHP mail
                                if(@mail($this -> to_address, $this -> subject, $this -> message_body, $this -> headers))
                                        $this -> save_log();
                                else
                                        // Phail
                                        $this -> save_error("Unexpected error sending with PHP mail() function.");
                
                        }
                        elseif($this -> method == "smtp")
                                // Doing SMTP
                                $this -> smtp_send_mail();
                                
                }
                
                return true;

        }


        // -------------------------------------------------------------------------


        function make_headers()
        {
                
                // ON MY HEAD THEY'RE ON MY HEAD(er)
                if($this -> html_email)
                        $this -> headers .= "MIME-Version: 1.0\r\n". 
                                "Content-type: text/html\r\n";

                $this -> headers .= "From: ".$this -> complete_from_address."\r\n";        

                if($this -> method == "smtp")
                        $this -> headers .= "To: ".$this -> to_address."\r\n".
                                "Subject: ".$this -> subject."\r\n";

                return true;

        }


        // -------------------------------------------------------------------------


        function save_error($error_msg)
        {
        
                global $db;
                
                $db -> basic_insert("email_logs",
                        array(
                                "date" => TIME,
                                "from" => $this -> from_address,
                                "to" => $this -> to_address,
                                "subject" => $this -> subject,
                                "text" => $this -> message_body,
                                "note" => $error_msg,
                                "error" => 1
                        )
                );

                return true;
                
        }                


        // -------------------------------------------------------------------------


        function save_log()
        {
        
                global $db;
                
                $db -> basic_insert("email_logs",
                        array(
                                "date" => TIME,
                                "from" => $this -> from_address,
                                "to" => $this -> to_address,
                                "subject" => $this -> subject,
                                "text" => $this -> message_body,
                                "note" => "Mail was sent sucessfully!",
                                "error" => 0
                        )
                );

                return true;
                
        }                


        // -------------------------------------------------------------------------
        // SMTP STUFF
        // Which was *not* fun to write and test...
        // -------------------------------------------------------------------------

        
        function smtp_send_command($command = "")
        {

                if(!$command)
                        return false;
                        
                // Send it
                fputs($this -> smtp_socket, $command."\r\n");
                
                // Get what they said
                $this -> smtp_get_response();
                
                // Worked or not?
                if($this -> smtp_code == "")
                        return false;
                else
                        return true;
                       
        }


        // -------------------------------------------------------------------------


        function smtp_get_response()
        {

                $this -> smtp_response = "";
                $this -> smtp_code = "";

                // Grab code and message from SMTP
                while($response_line = fgets($this -> smtp_socket, 1024))
                {
                
                        $this -> smtp_response .= $response_line;
                        
                        // No more codes!
                        if(substr($response_line, 3, 1) == " ")
                                break;
                        
                }

                $this -> smtp_code = substr($this -> smtp_response, 0, 3);

        }
        
        
        // -------------------------------------------------------------------------


        function smtp_send_mail()
        {
        
                // **********************
                // Connect to the SMTP server! OH EXCITING!
                // **********************
                $this -> smtp_socket = @fsockopen($this -> smtp_host, $this -> smtp_port, $errno, $errstr, 30);
        
                // Cock!
                if(!$this -> smtp_socket)
                {
                        $this -> save_error("Could not connect to SMTP server.");               
                        return false;
                }
                
                $this -> smtp_get_response();

                // **********************
                // Check service ready
                // **********************
                if($this -> smtp_code != 220)
                {
                        $this -> save_error("SMTP: Service not ready.");               
                        return false;
                }

                // **********************
                // Greetings, I am FSBoard.
                // **********************
                $this -> smtp_send_command("HELO ".$this -> smtp_host);

                if($this -> smtp_code != 250)
                {
                        $this -> save_error("SMTP: Failed on HELO command.");               
                        return false;
                }

                // **********************
                // Check login info
                // **********************
                if($this -> smtp_user && $this -> smtp_password)
                {

                        $this -> smtp_send_command("AUTH LOGIN");
                
                        if($this -> smtp_code != 334 && $this -> smtp_code != 200)
                        {
                                $this -> save_error("SMTP: Server does not support authorisation.");               
                                return false;
                        }
                
                        // Check user
                        $this -> smtp_send_command(base64_encode($this -> smtp_user));
                
                        if($this -> smtp_code != 334)
                        {
                                $this -> save_error("SMTP: Username not accepted.");               
                                return false;
                        }

                        // Check password
                        $this -> smtp_send_command(base64_encode($this -> smtp_password));
                
                        if($this -> smtp_code != 235)
                        {
                                $this -> save_error("SMTP: Password was rejected.");               
                                return false;
                        }
                }
                
                // **********************
                // Logged in. Tell it where we're from.
                // **********************
                $this -> smtp_send_command("MAIL FROM:".$this -> from_address);

                if(!$this -> smtp_code == 250)
                {
                        $this -> save_error("SMTP: Failed on FROM command.");               
                        return false;
                }

                // **********************
                // Where are we off?
                // **********************
                $this -> smtp_send_command("RCPT TO:".$this -> to_address);

                if(!$this -> smtp_code == 250)
                {
                        $this -> save_error("SMTP: Failed on TO command.");               
                        return false;
                }

                // **********************
                // Send it now!
                // **********************
                $this -> smtp_send_command("DATA");

                if(!$this -> smtp_code == 354)
                {
                        $this -> save_error("SMTP: Failed on DATA command.");               
                        return false;
                }

                fputs($this -> smtp_socket, $this -> headers."\n".$this -> message_body."\r\n");

                // **********************
                // Disconnect
                // **********************
                $this -> smtp_send_command(".");

                if(!$this -> smtp_code == 250)
                {
                        $this -> save_error("SMTP: Failed on disconnect.");               
                        return false;
                }

                $this -> smtp_send_command("QUIT");

                if(!$this -> smtp_code == 221)
                {
                        $this -> save_error("SMTP: Failed on disconnect.");               
                        return false;
                }

                // **********************
                // Close connection....
                // **********************
                @fclose($this -> smtp_socket);

                $this -> save_log();
                
                return true;
                
        }


}

?>
