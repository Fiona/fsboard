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
 * Common form interface class
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Database
 */



// ----------------------------------------------------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * This class acts as a single interface for creating forms in FSBoard.
 * Through the use of hooks, plugins can extend forms too, easily adding
 * additional functionality.
 */
class form
{
	
	/**
	 * This single array is the heart of the system,
	 * it holds all the data about a form.
	 * 
	 * @var array
	 */
	var $form_state = array();
	
	
	/**
	 * A string of HTML that gets build as the form is being rendered
	 *
	 * @var string
	 */
	var $final_html = "";
	
	
	/**
	 * Saves all the data for the form
	 *
	 * @param array $form_state
	 */
	function form($form_state)
	{
		
		global $user;
		
		if(!isset($GLOBALS['template_global_forms']))
			$GLOBALS['template_global_forms'] = load_template_class("template_global_forms"); 
			
		$this -> form_state = $form_state;

		if(!isset($this -> form_state['meta']))
			return trigger_error("Form is missing meta data.", E_USER_WARNING); 
			
		if(!isset($this -> form_state['meta']['name']))
			return trigger_error("Form is missing a name.", E_USER_WARNING); 
			
		if(!isset($this -> form_state['meta']['path']))
			$this -> form_state['meta']['path'] = $_SERVER['REQUEST_URI'];
			
		if(!isset($this -> form_state['meta']['method']))
			$this -> form_state['meta']['method'] = "post";
			
		$this -> form_state['meta']['key'] = md5($user -> session);

	}
	
	/**
	 * Renders out the form as HTML and returns it.
	 * This will also call the various callback functions.
	 *
	 * @return string HTML to display on the page, if any.
	 */
	function render()
	{
		
		global $template_global_forms, $user;

		
		# ---------------------------
		# Checking form input
		# ---------------------------
		$this -> form_state['error'] = "";
		
		$request = ($this -> form_state['meta']['method'] == "post") ? $_POST : $_GET;
		
		if(isset($request['form_'.$this -> form_state['meta']['name']]))
		{
			
			// we have submitted the form
			if($request['form_'.$this -> form_state['meta']['name']] ==  md5($user -> session))
			{
				
				// Get the values of each form field
				foreach($this -> form_state as $id => $info)
				{
					
					$id = substr($id, 1);
					
					if(isset($request[$id]))
						$this -> form_state["#".$id]['value'] = trim($request[$id]);
					
					// Preliminary tests
					if(isset($info['required']))
					{
						if($this -> form_state["#".$id]['value'] == "")
						{
							$this -> form_state["#".$id]['error'] = "fill in the field";
							$this -> form_state['error'] .= $template_global_forms -> form_main_error("field is required: ".$info['name']);
						}
					}	
					
				}				
				
			}
			
		}
		
		# ---------------------------
		# Creating the form HTML
		# ---------------------------
		
		// If we have something to put before the form
		$this -> final_html .= (isset($this -> form_state['meta']['before'])) ? $this -> form_state['meta']['before'] : "";
		
		// Go through each field
		$num = 0;
		$inner_form_html = "";
		
		foreach($this -> form_state as $id => $info)
		{
			
			// all fields start with a hash
			if($id[0] != "#")
				continue;
				
			$id = substr($id, 1);

			// Different fields for each field type
			$info['type'] = (!isset($info['type'])) ? "text" : $info['type'];
			$info['value'] = (!isset($info['value'])) ? "" : $info['value'];
			$info['name'] = (!isset($info['name'])) ? "&nbsp;" : $info['name'];
			
			switch($info['type'])
			{
				case "submit":
					$field_html = $template_global_forms -> form_field_submit($id, $info, $this -> form_state);
					break;
					
				case "text":
				default:
					$info['size'] = (!isset($info['size'])) ? 60 : $info['size'];					
					$field_html = $template_global_forms -> form_field_text($id, $info, $this -> form_state);
			}
				
			$inner_form_html .= $template_global_forms -> form_field_wrapper($field_html, $num, $id, $info, $this -> form_state);

			$num++;
			
		}
		
		$this -> final_html .= $template_global_forms -> form_wrapper($this -> form_state, $inner_form_html);
		
		return $this -> final_html;
		
	}
	
}

?>