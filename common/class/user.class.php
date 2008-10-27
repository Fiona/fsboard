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
*       User Class              *
*       Started by Fiona        *
*       27th Mar 2005           *
*********************************
*       Last edit by Fiona      *
*       18th Aug 2005           *
*********************************
*/




// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");



class user
{

        var $session = "";
        var $session_last_active = 0;
        var $session_user_id = "";
        
        var $found_ip = "";
        var $found_browser = "";
        
        var $user_id = 0;
        
        var $is_guest = true;

        var $in_admin_area = false;

        // Global arrays
        var $perms = array();
        var $forum_perms = array();
        var $mod_perms = array();
        var $info = array();
        var $cookie = array(); // Wish I could protect in < PHP 5.......

        // ---------------------------------------------------
        // get_user_info()
        // Checks the user info, if they're banned or not
        // and loads it all into class variables.
        //
        // Params  - None
        // Returns - None
        // ---------------------------------------------------
        function get_user_info()        
        {

                global $db, $cache, $output;
                
                // Populate some variables...
                $this -> found_ip = user_ip();
                $this -> found_browser = $_SERVER['HTTP_USER_AGENT'];
                
                
                // **************************
                // Are we banned?
                // **************************
        

                // **************************
                // Nab cookie stuff
                // **************************
                $this -> cookie['user_id']      = $this -> get_cookie('user_id');
                $this -> cookie['password']     = $this -> get_cookie('password');
                $this -> cookie['session']      = $this -> get_cookie('session');

                if($this -> cookie['session'] != false)
                        $this -> session = $this -> cookie['session'];
                else
                        $this -> session = false;

                // **************************
                // So load this session info...
                // **************************
                $this -> load_session();

                // **************************
                // Have a session loaded in and okay
                // **************************
                if($this -> session)
                {

                        if(($this -> session_user_id != 0) && (!empty($this -> session_user_id)))
                        {
        
                                // Get user info
                                $this ->  get_user_from_database($this -> session_user_id);
                                
                                // It found a user?
                                if(!$this -> info)
                                {
                                        $this -> kill_user_cookies();
                                        $this -> update_guest_session();
                                }
                                else
                                        $this -> update_user_session();
        
                        }
                        else
                                $this -> update_guest_session();
                                
                }
                                
                // **************************
                // Don't have a session cookie... Okay...
                // **************************
                if(!$this -> session)
                {

                        // So are we a guest or not?
                        if($this -> cookie['user_id'] != "" && $this -> cookie['password'] != "")
                        {

                                // Get user info
                                $this -> get_user_from_database($this -> cookie['user_id']);
                                
                                // It found a user?
                                if(!$this -> info)
                                {
                                        $this -> kill_user_cookies();
                                        $this -> update_guest_session();
                                }
                                else
                                {

                                        // Quick password check...
                                        if($this -> info['password'] != $this -> cookie['password'])
                                        {
                                                $this -> kill_user_cookies();
                                                $this -> update_guest_session();
                                        }
                                        else
                                                $this -> start_user_session();
                                
                                }
                        
                        }
                        else
                                $this -> start_guest_session();

                }


                // **************************
                // Cookies sorted... Load group and moderator permissions...
                // **************************
                // Fall back on guest fail-safe.
                if(!isset($this -> info['user_group']))
                        $this -> info['user_group'] = USERGROUP_GUEST;
                elseif(!isset($cache -> cache['user_groups'][$this -> info['user_group']]))
                        $this -> info['user_group'] = USERGROUP_GUEST;

                // Get global group perms
                foreach($cache -> cache['user_groups'][$this -> info['user_group']] as $key => $val)
                        $this -> perms[$key] = $val;
                        

                // **************************
                // Secondary user groups
                // **************************
                if(isset($this -> info['secondary_user_group']))
					$this -> info['secondary_user_group'] = explode(",", $this -> info['secondary_user_group']);
				else
					$this -> info['secondary_user_group'] = array();
					
					
				// Who wants some nested loops? You do! You do!
				// Basically here we go through all the secondary user groups
				// asigned to this user.....			
				if(count($this -> info['secondary_user_group']) > 0)
				{
		
					foreach($this -> info['secondary_user_group'] as $key => $val)
					{
		
						// Check to see if that user group actually exists :)
						// Then iterate through it's perm values 
						if(!isset($cache -> cache['user_groups'][$val]))
							continue;
							
						foreach($cache -> cache['user_groups'][$val] as $key2 => $val2)
						{
		
							// If it's a permission (they're all bools)
							// and it's better than what we have, override it
							if(substr($key2, 0, 5) == "perm_" && $this -> perms[$key2] == 0)
				            	$this -> perms[$key2] = $val2;
		
						}		          
		
					}
		
				}
				
				
                // **************************
				// Forum perms are logged on a per-forum basis. Run through all the forums.
                // **************************
				if(count($cache -> cache['forums']) > 0)
                {

                        foreach($cache -> cache['forums'] as $f_key => $f_val)
                        {

                                $specific_perm = false;
                        
                                // ******************************
                                // Specifically set perms?
                                // ******************************
                                if(count($cache -> cache['forums_perms']) > 0)
                                {
                                
                                        foreach($cache -> cache['forums_perms'] as $p_key => $p_val)
                                        {
        
                                                if($p_val['forum_id'] == $f_key && $p_val['group_id'] == $this -> info['user_group'])
                                                {
                                                
                                                        foreach($cache -> cache['forums_perms'][$p_key] as $key => $val)
                                                                $this -> forum_perms[$f_key][$key] = $val;
                                                
                                                        $specific_perm = true;
                                                
                                                }                                
        
                                        }

                                }
                                                                
                                // ******************************
                                // Specific perm not found so load default
                                // ******************************
                                if($specific_perm != true)
                                {

                                        // Log the perms
                                        foreach($cache -> cache['user_groups'][$this -> info['user_group']] as $key => $val)
                                                $this -> forum_perms[$f_key][$key] = $val;
                                                        
                                }

                                // ******************************
                                // Get moderator perms?
                                // ******************************
                                if(count($cache -> cache['moderators']) > 0)
                                {
                                
                                        foreach($cache -> cache['moderators'] as $m_key => $m_val)
                                        {
                                        
                                                // For us?
                                                if( ($m_val['forum_id'] == $f_key) && ( $m_val['group_id'] == $this -> info['user_group'] || $m_val['user_id'] == $this -> user_id) )
                                                {

                                                        foreach($cache -> cache['moderators'][$m_key] as $key => $val)
                                                                $this -> mod_perms[$f_key][$key] = $val;

                                                }
                                                                                        
                                        }
                                
                                }

                        } // end foreach($cache -> cache['forums'] as $f_key => $f_val)
                
                } // end if(count($cache -> cache['forums']) > 0)


                // **************************
                // Sort out last visit times...
                // **************************
                if(!$this -> is_guest)
                {
                
                        $active_update = ($cache -> cache['config']['session_last_active_update']) ? $cache -> cache['config']['session_last_active_update'] : 5;

                        // No time logged? Screw...
                        if(!$this -> info['last_visit'])
                        {

                                $db -> save_shutdown_query(
                                        $db -> basic_update(
                                                "users",
                                                array(
                                                        "last_active" => TIME,
                                                        "last_visit" => TIME,
                                                ),
                                                "id = '".$this -> user_id."'",
                                                true
                                        )
                                );
                                
                                $this -> info['last_visit'] = TIME;
                                $this -> info['last_active'] = TIME;
                                        
                        }
                        // Update the last active if applicable
                        elseif((TIME - $this -> info['last_active']) > ($active_update * 60))
                        {

                                $db -> save_shutdown_query(
                                        $db -> basic_update(
                                                "users",
                                                array(
                                                        "last_active" => TIME
                                                ),
                                                "id = '".$this -> user_id."'",
                                                true
                                        )
                                );
                                                        
                        }
                
                }


                // **************************
                // Finally, save the session ID as a cookie. PHEW.
                // **************************
                fs_setcookie("session", $this -> session, false, TIME - ($cache -> cache['config']['session_expire'] * 60));
                
        }



        // ---------------------------------------------------
        // get_cookie()
        // Reads and returns the specified cookie name
        //
        // Params  - $cookie_name
        // Returns - Contents of cookie or false
        // ---------------------------------------------------
        function get_cookie($cookie_name)        
        {

                global $db;
                
                if(isset($_COOKIE["fsboard_".$db -> table_prefix.$cookie_name]))
                        return $_COOKIE["fsboard_".$db -> table_prefix.$cookie_name];
                else
                        return false;
                
        }



        // ---------------------------------------------------
        // load_session()
        // Checks the current session to one in the database
        //
        // Params  - Nothing
        // Returns - true/false
        // ---------------------------------------------------
        function load_session()        
        {

                global $db, $config, $cache;
                
                if($this -> session == false)
                {
                
                        // Blank session...
                        $this -> session = 0;
                        $this -> session_user_id = 0;
                        $this -> session_last_active = 0;                        
                        return false;
                
                }                     

                // **************************
                // Grab the session
                // **************************
				$db -> basic_select("sessions", "id, user_id, last_active, ip_address, browser", "id = '".$this -> session."'", "", "1");


                // **************************
                // No session?
                // **************************
                if($db -> num_rows() < 1)
                {
                        // Blank session...
                        $this -> session = 0;
                        $this -> session_user_id = 0;
                        $this -> session_last_active = 0;                        
                        return false;
                
                }
                // **************************
                // Have a session
                // **************************
                else
                {
                
                        $session_array = $db -> fetch_array();
                        $bad = false;

                        // IP address okay?
                        if(isset($cache -> cache['config']['login_ip_check']) && $this -> found_ip != $session_array['ip_address'])
                                $bad = true;

                        // Browser okay?
                        if(isset($cache -> cache['config']['login_check_browser']) && $this -> found_browser != $session_array['browser'])
                                $bad = true;
                        
                        // Something smegged up...
                        if($bad)
                        {
                        
                                $this -> session = 0;
                                $this -> session_user_id = 0;
                                $this -> session_last_active = 0;                        
                                return false;
                                                
                        }
                        // All okay and checked, we're fine to go ahread with this session...
                        else
                        {

                                // Log it all
                                $this -> session_user_id = $session_array['user_id'];
                                $this -> session_last_active = $session_array['last_active'];
                        
                                return true;
                                        
                        }
                                        
                }
        
        }
                                        



        // ---------------------------------------------------
        // kill_user_cookies()
        // Destroys any cookies stored that are used for user login.
        //
        // Params  - Nothing
        // Returns - Nothing
        // ---------------------------------------------------
        function kill_user_cookies()
        {
        
                global $db;
                
                // Death to all cookies...
                fs_setcookie("user_id", "", false, TIME - 36000);
                fs_setcookie("password", "", false, TIME - 36000);
                fs_setcookie("session", "", false, TIME - 36000);
                
                // Unset this
                $this -> user_id = 0;
        
        }



        // ---------------------------------------------------
        // get_user_from_database()
        // Selects the user info from the DB, and populates the right 
        // info if one is found...
        //
        // Params  - Nothing
        // Returns - Nothing
        // ---------------------------------------------------
        function get_user_from_database($user_id)
        {
        
                global $db;
                
                if(!$user_id)
                        return false;
                        
                // Select the member...
				$db -> basic_select("users", "*", "id = ".(int)$user_id, "", "1");

                // Have a a bite?
                if($db -> num_rows())
                {
                
                        // Get the full info
                        $this -> info = $db -> fetch_array();
                        $this -> user_id = $this -> info['id'];
                        
                        // Check we're a real user?
                        if($this -> user_id)
                                return true;
                               
                }
                
                // Damn.. Guess there's no user.
                $this -> kill_user_cookies();
                                                
        }
        

        
        // ---------------------------------------------------
        // start_user_session()
        // Inserts a new session log into the DB
        //
        // Params  - Nothing
        // Returns - Nothing
        // ---------------------------------------------------
        function start_user_session()
        {

                global $db, $cache;
                
                // No user... What?
                if(!$this -> user_id)
                {
                        $this -> start_guest_session();
                        return;
                }
                
                // **************************
                // Delete any old sessions
                // **************************
		$db -> basic_delete("sessions", "user_id='".$this -> user_id."'");

                // **************************
                // Save new session
                // **************************
                if($this -> session == false)
                	$this -> session = md5(uniqid(rand(), true));

                //fs_setcookie("session", $this -> session, false, TIME - ($cache -> cache['config']['session_expire'] * 60));

                // Get location
                $location = $this -> get_location_string();
                
                $db -> save_shutdown_query(
                        $db -> basic_insert(
                                "sessions",
                                array(
                                        "id" => $this -> session,
                                        "user_id" => $this -> user_id,
                                        "username" => $this -> info['username'],
                                        "user_group" => $this -> info['user_group'],
                                        "last_active" => TIME,
                                        "ip_address" => $this -> found_ip,
                                        "browser" => $this -> found_browser,
                                        "location" => $location
                                ),
                                true
                        )
                );


                // **************************
                // Session is an hour old, update last visit times
                // **************************
                if(TIME - $this -> info['last_active'] > 3600)
                {
                
                        $db -> save_shutdown_query(
                                $db -> basic_update(
                                        "users",
                                        array(
                                                "last_active" => TIME,
                                                "last_visit" => TIME
                                        ),
                                        "id='".$this -> user_id."'",
                                        true
                                )
                        );
                
                        $this -> info['last_visit'] = $this -> info['last_active'];
                        $this -> info['last_active'] = TIME;
                
                }

                $this -> info['logged_in'] = 1;
                $this -> is_guest = false;
        
        }
        


        // ---------------------------------------------------
        // update_user_session()
        // Updates the current session log...
        //
        // Params  - Nothing
        // Returns - Nothing
        // ---------------------------------------------------        
        function update_user_session()
        {
       
                global $db;

                // No session really...
                if(!$this -> session)
                {       
                        $this -> start_user_session();
                        return;
                }        

                // No user, kill it
                if(!$this -> user_id)
                {       
                        $this -> kill_user_cookies();
                        $this -> start_guest_session();
                        return;
                }        
                
                // Get location
                $location = $this -> get_location_string();

                // **************************
                // Update the session...
                // **************************
                $db -> save_shutdown_query(
                        $db -> basic_update(
                                "sessions",
                                array(
                                        "user_id" => $this -> user_id,
                                        "username" => $this -> info['username'],
                                        "last_active" => TIME,
                                        "ip_address" => $this -> found_ip,
                                        "browser" => $this -> found_browser,
                                        "location" => $location
                                ),
                                "id = '".$this -> session."'",
                                true
                        )
                );
        
                $this -> info['logged_in'] = 1;
                $this -> is_guest = false;
                        
        }
        


        // ---------------------------------------------------
        // start_guest_session()
        // Inserts a new session log into the DB
        //
        // Params  - Nothing
        // Returns - Nothing
        // ---------------------------------------------------
        function start_guest_session()
        {

                global $db, $cache;

                // **************************
                // Delete any old sessions if applicable
                // **************************
                $db -> basic_delete("sessions", "id='".$db -> escape_string($this -> session)."'");

                $this -> kill_user_cookies();

                // **************************
                // Save new session
                // **************************
                $this -> session = md5(uniqid(rand(), true));

                // Get location
                $location = $this -> get_location_string();

                $db -> save_shutdown_query(
                        $db -> basic_insert(
                                "sessions",
                                array(
                                        "id" => $this -> session,
                                        "user_id" => 0,
                                        "username" => "",
                                        "user_group" => 4,
                                        "last_active" => TIME,
                                        "ip_address" => $this -> found_ip,
                                        "browser" => $this -> found_browser,
                                        "location" => $location
                                ),
                                true
                        )
                );       

                $this -> info['logged_in'] = 0;

        }
        


        // ---------------------------------------------------
        // update_guest_session()
        // Updates the current session log...
        //
        // Params  - Nothing
        // Returns - Nothing
        // ---------------------------------------------------        
        function update_guest_session()
        {

                global $db;

                // No session really...
                if(!$this -> session)
                {       
                        $this -> start_guest_session();
                        return;
                }        

                // Get location
                $location = $this -> get_location_string();

                // **************************
                // Update the session...
                // **************************
                $db -> save_shutdown_query(
                        $db -> basic_update(
                                "sessions",
                                array(
                                        "user_id" => '0',
                                        "username" => '0',
                                        "last_active" => TIME,
                                        "ip_address" => $this -> found_ip,
                                        "browser" => $this -> found_browser,
                                        "location" => $location
                                ),
                                "id = '".$this -> session."'",
                                true
                        )
                );
                
                $this -> info['logged_in'] = 0;
                        
        }



        // ---------------------------------------------------
        // get_location_string()
        // Works out the location of the user in the forum
        //
        // Params  - Nothing
        // Returns - String containing location
        // ---------------------------------------------------   
        function get_location_string()
        {
        
                $location = "";
        
                if($this -> in_admin_area)
                        $location = "admin";
                else
                        $location = "0";

                return $location;                        
                                        
        }

}

?>
