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
	
	
	/*
	 * If this is set to true, the form was found to have been submitted..
	 */
	var $form_submitted = False;
	
	
	/*
	 * If this is set to true, the form will not submit.
	 */
	var $show_error = False;
	
	
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
			
		$this -> form_state['meta']['key'] = md5($user -> session.$this -> form_state['meta']['name']);

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
		$reshow_form = False;
	
		$request = ($this -> form_state['meta']['method'] == "post") ? $_POST : $_GET;
		
		if(isset($request['form_'.$this -> form_state['meta']['name']]))
		{
			
			// we have submitted the form
			if($request['form_'.$this -> form_state['meta']['name']] ==  md5($user -> session.$this -> form_state['meta']['name']))
			{
				
				$this -> form_submitted = True;
				
				// Get the values of each form field
				foreach($this -> form_state as $id => $info)
				{
					
					if($id[0] != "#")
						continue;
						 
					$id = substr($id, 1);

					if(isset($request[$id]))
						$this -> form_state["#".$id]['value'] = trim($request[$id]);
					else
						$this -> form_state["#".$id]['value'] = "";

					// Preliminary tests
					if(isset($info['required']))
						if($this -> form_state["#".$id]['value'] == "")
							$this -> set_error($id, "fill in the field");					
					
					if(isset($info['identical_to']))
						if($this -> form_state["#".$id]['value'] != $this -> form_state[$info['identical_to']]['value'])
							$this -> set_error($id, "value must be same as ".$this -> form_state[$info['identical_to']]['name']);					
							
					
				}	

				// call custom validation
				if(isset($this -> form_state['meta']['validation_func']))
					call_user_func($this -> form_state['meta']['validation_func'], $this);
					
				// Call completion function if we have no errors
				if(!isset($this -> form_state['meta']['show_error']))
				{
					$ret = call_user_func($this -> form_state['meta']['complete_func'], $this);
					
					// Check if we should redirect
					if(isset($this -> form_state['meta']['redirect']) && is_array($this -> form_state['meta']['redirect']))
					{
						global $output;
						$output -> redirect($this -> form_state['meta']['redirect']['url'], $this -> form_state['meta']['redirect']['message']);						
						return $ret;
					}

					$reshow_form = True;
				
				}
					
			}
			
		}
		
		# ---------------------------
		# Creating the form HTML
		# ---------------------------
		if((!$this -> form_submitted) || ($this -> form_submitted && isset($this -> form_state['meta']['show_error'])) || $reshow_form)
		{
		
			// If we have something to put before the form
			$this -> final_html .= (isset($this -> form_state['meta']['before'])) ? $this -> form_state['meta']['before'] : "";
			
			// Go through each field
			$num = 0;
			$inner_form_html = "";
			
			foreach($this -> form_state as $id => $info)
			{
				
				// all fields start with a hash
				if($id[0] != "#")
				{
					if(isset($info['type']) && $info['type'] == "message")
						$inner_form_html .= $template_global_forms -> form_field_sub_message($id, $info, $this -> form_state);
					continue;
				}
								
				$id = _substr($id, 1);
	
				// Different fields for each field type
				$info['type'] = (!isset($info['type'])) ? "text" : $info['type'];
				$info['value'] = (!isset($info['value'])) ? "" : $info['value'];
				$info['name'] = (!isset($info['name'])) ? "&nbsp;" : $info['name'];
				
				switch($info['type'])
				{
					case "submit":
						$field_html = $template_global_forms -> form_field_submit($id, $info, $this -> form_state);
						break;

					case "password":
						$info['size'] = (!isset($info['size'])) ? 30 : $info['size'];					
						$field_html = $template_global_forms -> form_field_password($id, $info, $this -> form_state);
						break;

					case "int":
						$info['size'] = (!isset($info['size'])) ? 7 : $info['size'];					
						$field_html = $template_global_forms -> form_field_text($id, $info, $this -> form_state);
						break;

					case "yesno":
						$field_html = $template_global_forms -> form_field_yesno($id, $info, $this -> form_state);
						break;

					case "textarea":
						$info['size'] = (!isset($info['size'])) ? 4 : $info['size'];					
						$field_html = $template_global_forms -> form_field_textarea($id, $info, $this -> form_state);
						break;

					case "dropdown":
						$info['size'] = (!isset($info['size'])) ? 0 : $info['size'];					
						$field_html = $template_global_forms -> form_field_dropdown($id, $info, $this -> form_state);
						break;
						
					case "checkbox":
						$field_html = $template_global_forms -> form_field_checkbox($id, $info, $this -> form_state);
						break;

					case "file":
						$info['size'] = (!isset($info['size'])) ? 30 : $info['size'];					
						$field_html = $template_global_forms -> form_field_file($id, $info, $this -> form_state);
						break;
						
					case "text":
					default:
						$info['size'] = (!isset($info['size'])) ? 30 : $info['size'];					
						$field_html = $template_global_forms -> form_field_text($id, $info, $this -> form_state);
				}
					
				$inner_form_html .= $template_global_forms -> form_field_wrapper($field_html, $num, $id, $info, $this -> form_state);
	
				$num++;
				
			}
			
			$this -> final_html .= $template_global_forms -> form_wrapper($this -> form_state, $inner_form_html);

			// If we have something to put after the form
			$this -> final_html .= (isset($this -> form_state['meta']['after'])) ? $this -> form_state['meta']['after'] : "";
			
		}
		
		return $this -> final_html;
		
	}
	
	
	
	/*
	 * Sets an error on a field on the form
	 */
	function set_error($field_id, $error_message)
	{
		
		global $output;
		
		if($field_id == NULL)
			$output -> set_error_message($error_message);
		else
		{
			$output -> set_error_message($this -> form_state["#".$field_id]['name'].": ".$error_message);
			$this -> form_state["#".$field_id]['error'] = $error_message;
		}

		$this -> form_state['meta']['show_error'] = True;
		
	}
	
}

?>