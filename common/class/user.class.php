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
 * User class
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


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
	var $admin_menu = array();
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
                 
		if(!isset($this -> info['secondary_user_group']))
			$this -> info['secondary_user_group'] = array();

		// **************************
		// Secondary user groups
		// **************************
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

		// We may want to add to what we need to get if we're in admin so we set this here.
		$what = "u.*, g.group_id as secondary_id";

		// Our first join is the secondary groups table.
		$joins = array(
			array(
				"join" => "users_secondary_groups as g",
				"join_type" => "LEFT",
				"join_on" => "g.user_id = u.id"
				)
			);

		// If we're in the admin area then we may have some settings we want to get. (Like state of the menu.)
		if(defined("ADMIN"))
		{

			$what .= ", s.admin_menu";

			$joins[] = array(
				"join" => "users_admin_settings s",
				"join_type" => "LEFT",
				"join_on" => "s.user_id = u.id"
				);

		}

		// Finally do the select with all the joins and our combined "what"
		$db -> basic_select(
			array(
				"table" => "users u",
				"what" => $what, 
				"where" => "u.id = ".(int)$user_id, "", 
				"join" => $joins
				)
			);

		// If we found a user with these details.
		if($db -> num_rows())
		{

			$info = NULL;

			// Our secondary groups are left joined. If we have more than one secondaary 
			// group for this user then we would have multiple rows with the same data in apart
			// from the secondary groups. So we go through our results.
			while($user_info = $db -> fetch_array())
			{

				// If this is the initial iteration then we need to get our user data from the first row.
				if($info === NULL)
				{
					$info = $user_info;
					$info['secondary_user_group'] = array();
				}
				
				// For all rows we keep track of the secondary group IDs as they come.
				if($user_info['secondary_id'] && is_numeric($user_info['secondary_id']))
					$info['secondary_user_group'][] = $user_info['secondary_id'];

			}

			// Now we have a complete picture of the user, groups and all.
			$this -> info = $info;
			$this -> user_id = $this -> info['id'];

			if(defined("ADMIN") && isset($this -> info['admin_menu']))
				$this -> admin_menu = explode(",", $this -> info['admin_menu']);

			// Last minute check to see if something went wrong. No reason for it to on top of my head, but sanity...
			if($this -> user_id)
				return True;
                               
		}
                
		// There was no user, so we kill the cookies and set them as a guest.
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
