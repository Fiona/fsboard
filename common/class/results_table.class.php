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
 * Results table class.
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Database
 */



// -----------------------------------------------------------------------------



// Check script entry
if (!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


/**
 * This is the results table class. It's designed to provide
 * a completely painless and easy as hell method of displaying
 * tabular data, especially that from a database along with providing
 * stuff like pagination and searching for free.
 * It's mostly used in the admin area as it's pretty much a bunch of CRUD
 * pages. 
 */
class results_table
{

	/**
	 * This is a bunch of settings that defines our table.
	 *
	 * var array
	 */
	var $settings = array(
		"items_per_page" => 50
		);


	/**
	 * Constructor, saves the settings.
	 * 
	 * @param array $settings 
	 */
	function results_table($settings)
	{
/*
		if(!isset($GLOBALS['template_global_results_table']))
			$GLOBALS['template_global_results_table'] = load_template_class(
				"template_global_results_table"
				); 
*/		
		$this -> settings = array_merge($this -> settings, $settings);

	}


	/**
	 * Works out what we want and returns the correct
	 * HTML for the current table definition
	 */
	function render()
	{

		global $db;

		/*
		 * First we need to work out what data we're dealing with
		 */
		if(isset($this -> settings['db_table']))
		{

			// First we should get how many we have in total
			$id = (
				isset($this -> settings['db_id_column']) ?
				$this -> settings['db_id_column'] :
				"id"
				);

			$db -> basic_select(
				array(
					"table" => $this -> settings['db_table'],
					"what" => "COUNT(`".$id."`) as `row_count`",
					"where" => (
						isset($this -> settings['db_where']) ?
						$this -> settings['db_where'] :
						""
						)
					)
				);

			$total_data = $db -> result();
			
			// Select the data


		}
		elseif(isset($this -> settings['data']))
		{
			$total_data = count($data);
			$data = $thiis -> settings['data'];
		}
		else
		{
			$total_data = 0;
			$data = array();
		}

	}

}