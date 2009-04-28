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
		"items_per_page" => 20
		);


	/**
	 * Amount of items that the table has in total.
	 *
	 * var int
	 */
	var $total_items = 0;


	/**
	 * Amount of pages that the table has in total.
	 *
	 * var int
	 */
	var $total_pages = 0;


	/**
	 * Which page we are currently on
	 *
	 * var int
	 */
	var $current_page = 0;


	/**
	 * Constructor, saves the settings.
	 * 
	 * @param array $settings 
	 */
	function results_table($settings)
	{

		if(!isset($GLOBALS['template_global_results_table']))
			$GLOBALS['template_global_results_table'] = load_template_class(
				"template_global_results_table"
				); 
	
		$this -> settings = array_merge($this -> settings, $settings);

	}


	/**
	 * Works out what we want and returns the correct
	 * HTML for the current table definition
	 */
	function render()
	{

		global $db, $template_global_results_table;

		/*
		 * First we need to work out what data we're dealing with
		 */
		// If we're getting it from the DB
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

			$this -> total_items = $db -> result();
			
			// What page are we on
			$this -> save_total_pages();
			$this -> save_current_page();

			$data = array();

			if($this -> total_items > 0)
			{

				// Get what kind of data we need to display
				$what = array("`".$id."`");

				foreach($this -> settings['columns'] as $col)
					if(isset($col['db_column']))
						$what[] = "`".$col['db_column']."`";
		   
				$extra_what = (
					isset($this -> settings['db_extra_what']) ?
					$this -> settings['db_extra_what'] :
					array()
					);

				// Select the final data
				$db -> basic_select(
					array(
						"table" => $this -> settings['db_table'],
						"what" => implode(", ", array_merge($what, $extra_what)),
						"where" => (
							isset($this -> settings['db_where']) ?
							$this -> settings['db_where'] :
							""
							),
						"limit" => (
							(max($this -> current_page-1,0) * $this -> settings['items_per_page']).
							", ".$this -> settings['items_per_page']
							)
						)
					);

				if($db -> num_rows())
					while($row = $db -> fetch_array())
						$data[] = $row;

			}

		}
		// If we have defined the data ourselves
		elseif(isset($this -> settings['data']))
		{
			$this -> total_items = count($data);
			$this -> save_total_pages();
			$this -> save_current_page();
			$data = $this -> settings['data'];
		}
		// Otherwise we've got an empty table
		else
		{
			$this -> total_items = 0;
			$this -> save_total_pages();
			$this -> save_current_page();
			$data = array();
		}


		// Now we have our data we're going to throw it into rows
		$rows_html = "";
		$row_count_num = 1;

		foreach($data as $row_data_array)
		{

			$columns = array();

			// We need to build the right data for the template function
			foreach($this -> settings['columns'] as $col_id => $col_info)
			{

				if(isset($col_info['db_column']))
				{
					$columns[$col_id] = $row_data_array[$col_info['db_column']];
				}
				elseif(isset($col_info['content_callback']))
				{
					$columns[$col_id] = call_user_func(
						$col_info['content_callback'],
						$row_data_array
						);
				}
				else
					$columns[$col_id] = "";

				// If this is a date field
				if(isset($col_info['date_format']))
					$columns[$col_id] = return_formatted_date(
						$col_info['date_format'],
						$columns[$col_id]
						);

			}

			$rows_html .= $template_global_results_table -> table_row(
				$this -> settings,
				$columns,
				++$row_count_num
				);

		}


		// Grab correct pagination links
		$prev_link = NULL;
		$next_link = NULL;
		$first_link = NULL;
		$last_link = NULL;

		if($this -> current_page > 1)
		{
			$prev_link = "?page=".($this -> current_page-1);
			$first_link = "?page=1";
		}

		if($this -> total_pages > 1 && $this -> current_page < $this -> total_pages)
		{
			$next_link = "?page=".($this -> current_page+1);
			$last_link = "?page=".($this -> total_pages);
		}

		$pagination_html = $template_global_results_table -> pagination(
			$this -> total_pages,
			$this -> current_page,
			$prev_link,
			$next_link,
			$first_link,
			$last_link
			);

		// Finished processing data, give back the finished table
		return $template_global_results_table -> table_wrapper(
			$this -> settings,
			$template_global_results_table -> table_column_header(
				$this -> settings,
				$this -> settings['columns']
				),
			$rows_html,
			$pagination_html
			);

	}


	/**
	 * Will work out what page we should be on right now.
	 */
	function save_current_page()
	{

		if(isset($_GET['page']) && is_int($_GET['page']) && $_GET['page'] > 0)
			$cur_page = intval($_GET['page']);
		else
			$cur_page = 1;

		if($cur_page > $this -> total_pages)
			$cur_page = $this -> total_pages;

		$this -> current_page = $cur_page;

	}


	/**
	 * Will work out haw many pages we have in total
	 */
	function save_total_pages()
	{

		$this -> total_pages = (int)(
			$this -> total_items / $this -> settings['items_per_page']
			);

	}

}