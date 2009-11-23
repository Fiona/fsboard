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
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * This class acts as a single interface for creating forms in FSBoard.
 * Through the use of hooks, plugins can extend forms too, easily adding
 * additional functionality.
 *
 * TODO: ERRORS ARE IN ENGLISH. STOP BEING LAZY.
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
				$uploads = array();
				
				// Get the values of each form field
				foreach($this -> form_state as $id => $info)
				{
					
					if($id[0] != "#")
						continue;
						 
					$id = substr($id, 1);

					if(isset($request[$id]))
						$this -> form_state["#".$id]['value'] = (is_array($request[$id]) ? $request[$id] : trim($request[$id]));
					else
						$this -> form_state["#".$id]['value'] = "";

					// Preliminary tests
					if(isset($info['required']))
						if($this -> form_state["#".$id]['value'] == "")
							$this -> set_error($id, "fill in the field");					
					
					if(isset($info['identical_to']))
						if($this -> form_state["#".$id]['value'] != $this -> form_state[$info['identical_to']]['value'])
							$this -> set_error($id, "value must be same as ".$this -> form_state[$info['identical_to']]['name']);					
							
					// Keep track of uploaded files so we can process them later
					if($this -> form_state['#'.$id]['type'] == "upload" && isset($this -> form_state['#'.$id]['upload']))
						$uploads[$id] = $this -> form_state['#'.$id]['upload'];

				}	

				// Process any uploads that we have
				if(count($uploads))
				{

					include_once ROOT."common/class/upload.class.php";

					foreach($uploads as $upload_id => $upload_info)
					{

						// Quick check to see if it's required and error if it doesn't exist
						if(!$_FILES[$upload_id]['tmp_name'] || !$_FILES[$upload_id]['name'])
						{

							if(isset($this -> form_state['#".$upload_id']['required']) )
								$this -> set_error($upload_id, "fill in the field");

						}
						else
						{
						
							// Create an upload class instance for each one and set settings
							$is_image = (isset($upload_info['is_image']) ?  $upload_info['is_image'] : False);

							$class = new upload($is_image);
							$class -> destination_path = $upload_info['destination_path'];
							$class -> overwrite_existing = (isset($upload_info['overwrite_existing']) ? $upload_info['overwrite_existing'] : False);

							// If there's an initial problem with teh file then we need to tell the user
							if(($error = $class -> check_upload_from_form($upload_id)) !== True)
								$this -> set_error($upload_id, $error);

							// By saving the uploaded file as the field value it enables us to easily
							// check if the upload has worked in validation/completion callbacks.
							$this -> form_state['#'.$upload_id]['value'] = $class -> real_name;
							$this -> form_state['#'.$upload_id]['upload']['class'] = $class;

						}

					}

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
		// Initial data is used for edit forms and is what we give to the form at first.
		// If we've given some to the form then we need to set it to the values.
		elseif(isset($this -> form_state['meta']['initial_data']))
		{

			foreach($this -> form_state as $id => $info)
			{
				
				if($id[0] != "#")
					continue;

				$id = substr($id, 1);

				if(isset($this -> form_state['meta']['initial_data'][$id]))
					$this -> form_state["#".$id]['value'] = $this -> form_state['meta']['initial_data'][$id];

			}

		}


		// Last minute check to force the builder not to create the form
		if(isset($this -> form_state['meta']['halt_form_render']) && $this -> form_state['meta']['halt_form_render'])
			$reshow_form = False;
		
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
					elseif(isset($info['type']) && $info['type'] == "mini_message")
						$inner_form_html .= $template_global_forms -> form_field_mini_message($id, $num++, $info, $this -> form_state);
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
						if(isset($info['blank_option']) && $info['blank_option'])
							$info['options'] = array("" => " ") + $info['options'];

						$info['size'] = (!isset($info['size'])) ? 0 : $info['size'];
						$field_html = $template_global_forms -> form_field_dropdown($id, $info, $this -> form_state);
						break;
						
					case "checkbox":
						$field_html = $template_global_forms -> form_field_checkbox($id, $info, $this -> form_state);
						break;

					case "checkboxes":
						if(!is_array($info['value']))
							$info['value'] = array();
						$field_html = $template_global_forms -> form_field_checkboxes($id, $info, $this -> form_state);
						break;

					case "date":

						// The date field expects the current value to be passed as an array containing entries for the
						// different parts of the date. As such we can pass it either the expected array or as a timestamp.
						// We work out the actual array based on the timestamp if necessary.
						if(is_numeric($info['value']))
						{
							$worked_out_date = array(
                                "year" 		=> ($info['value'] > 0) ? return_formatted_date("Y", $info['value']) : "",
                                "month" 	=> ($info['value'] > 0) ? return_formatted_date("n", $info['value']) : "",
                                "day"		=> ($info['value'] > 0) ? return_formatted_date("j", $info['value']) : "",
                                "hour" 		=> ($info['value'] > 0) ? return_formatted_date("H", $info['value']) : "",
                                "minute" 	=> ($info['value'] > 0) ? return_formatted_date("i", $info['value']) : ""
								);
							$info['value'] = $worked_out_date;
						}
						elseif(!is_array($info['value']))
						{
							if(isset($info['time']))
								$info['value'] = array("day" => "", "month" => "0", "year" => "", "hour" => "", "minute" => "");
							else
								$info['value'] = array("day" => "", "month" => "0", "year" => "");
						}

						$field_html = $template_global_forms -> form_field_date($id, $info, $this -> form_state);
						break;

					case "file":
					case "upload":
						$info['size'] = (!isset($info['size'])) ? 30 : $info['size'];					
						$field_html = $template_global_forms -> form_field_file($id, $info, $this -> form_state);
						break;

					case "results_table":

						if(isset($info['results_table_checkboxes']))
						{
							$info['results_table_settings']['columns'] = array(
								"checkboxes" => array(
									"name" => $template_global_forms -> form_field_checkbox("select_all_checkbox", $info, $this -> form_state),
									"content_callback" => array($this, "table_checkboxes_callback"),
									'content_callback_parameters' => array($id, $info),
									)
								) +
								$info['results_table_settings']['columns'];
						}

						$info['results_table_class'] = new results_table($info['results_table_settings']);
						$field_html = $info['results_table_class'] -> render();
						break;

					case "hidden":
						$field_html = $template_global_forms -> form_field_hidden($id, $info, $this -> form_state);
						break;
						
					case "text":
					default:
						$info['size'] = (!isset($info['size'])) ? 30 : $info['size'];					
						$field_html = $template_global_forms -> form_field_text($id, $info, $this -> form_state);
				}
				
				if(in_array($info['type'], array("hidden", "results_table")))
					$inner_form_html .= $field_html;
				else
				{
					$inner_form_html .= $template_global_forms -> form_field_wrapper($field_html, $num, $id, $info, $this -> form_state);
					$num++;
				}
				
			}
			
			// Wrap the form up
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
	

	/*
	 * Helper function to work out a unix timestamp from a date field
	 * that has already been submitted. (Will get the value from form_state.)
	 *
	 * @param string $field_name Name/ID of the date field. Including preceding #.
	 * @return int Unix timestamp conversion of the current value.
	 */
	function get_date_timestamp($field_name)
	{

		// Looked a bit of a mess without this
		$vals = array_map("intval", $this -> form_state[$field_name]['value']);

		// If one of the pieces equates to true then we can calculate a time
		// otherwise we need to return 0 (internally 0 is "never")
		foreach($vals as $val)
		{

			if($val)
			{

				// If it's a time based widget we need to calculate the hour/min too
				if(isset($this -> form_state[$field_name]['time']))
					return mktime($vals['hour'], $vals['minute'], 0, $vals['month'], $vals['day'], $vals['year']);
				else
					return mktime(0, 0, 0, $vals['month'], $vals['day'], $vals['year']);

			}

			return 0;

		}

	}


	/*
	 * This is used for result table fields to add checkboxes to the table.
	 */
	function table_checkboxes_callback($raw_data, $id, $info)
	{

		global $template_global_forms;

		return $template_global_forms -> form_field_checkbox($id, $info, $this -> form_state);

	}

}

?>