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
 * This is the results table class. It's designed to provide a completely
 * painless and easy as hell method of displaying tabular data, especially that
 * from a database along with providing stuff like pagination and searching for
 * free. It's mostly used in the admin area as it's pretty much a bunch of CRUD
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
		"items_per_page" => 20,
		"extra_url" => "",
		"default_sort" => "id",
		"default_sort_direction" => "asc",
		"no_results_message" => "no results"
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
	 * Current column we're sorting by
	 *
	 * var string
	 */
	var $sort_column_selected = NULL;


	/**
	 * Current direction we're sorting by (either asc, desc)
	 *
	 * var string
	 */
	var $sort_column_direction = NULL;


	/**
	 * The amount of numbers we will have either side of the selected one
	 *
	 * var int
	 */
	var $padding_amount = 2;


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

		// This url forms the basis of all numerical and column links
		$extra_url = (
			(isset($this -> settings['extra_url']) && $this -> settings['extra_url']) ?
			$this -> settings['extra_url']."&" :
			""
			);

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


			$database_info = array(
					"table" => $this -> settings['db_table'],
					"what" => "COUNT(`".$id."`) as `row_count`",
					"where" => (
						isset($this -> settings['db_where']) ?
						$this -> settings['db_where'] :
						""
						)
				);

			if(isset($this -> settings['db_extra_settings']))
				$database_info = ($database_info + $this -> settings['db_extra_settings']);

			$db -> basic_select($database_info);

			$this -> total_items = $db -> result();
			
			// What page are we on
			$this -> save_total_pages();
			$this -> save_current_page();

			// What are we sorting by at the mo
			$this -> save_sorting_settings();

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
				$database_info = array(
					"table" => $this -> settings['db_table'],
					"what" => implode(", ", array_merge($what, $extra_what)),
					"where" => (
						isset($this -> settings['db_where']) ?
						$this -> settings['db_where'] :
						""
						),
					"order" => $this -> sort_column_selected,
					"direction" => strtoupper($this -> sort_column_direction),
					
					"limit" => (
						(max($this -> current_page-1,0) * $this -> settings['items_per_page']).
						", ".$this -> settings['items_per_page']
						)
					);

				if(isset($this -> settings['db_extra_settings']))
					$database_info = ($database_info + $this -> settings['db_extra_settings']);

				$db -> basic_select($database_info);

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

				// If it's a database column we get the relevant data
				if(isset($col_info['db_column']))
				{
					$columns[$col_id] = $row_data_array[$col_info['db_column']];
				}
				// Or if we're calling a function to get the data
				elseif(isset($col_info['content_callback']))
				{
					$columns[$col_id] = call_user_func(
						$col_info['content_callback'],
						$row_data_array
						);
				}
				// Otherwise there's nothing in it
				else
					$columns[$col_id] = "";

				// If this is a date field
				if(isset($col_info['date_format']))
					$columns[$col_id] = return_formatted_date(
						$col_info['date_format'],
						$columns[$col_id]
						);

				// If we want it sanitised
				if(isset($col_info['sanitise']) && $col_info['sanitise'])
					$columns[$col_id] = sanitise_user_input($columns[$col_id]);

				// Alignment of contents
				if(!isset($col_info['align']))
					$this -> settings['columns'][$col_id]['align'] = "left";

			}

			$rows_html .= $template_global_results_table -> table_row(
				$this -> settings,
				$columns,
				++$row_count_num
				);

		}


		// If we had no items we should tell the user
		if(count($data) == 0)
		{

			$rows_html = $template_global_results_table -> table_row(
				$this -> settings,
				array(
					$template_global_results_table -> no_results_message($this -> settings['no_results_message'])
					),
				1,
				count($this -> settings['columns'])
				);

		}

		// Grab correct pagination urls
		$prev_link = "";
		$next_link = "";
		$first_link = "";
		$last_link = "";

		if(isset($_GET['sort_col']))
		{
			$extra_url .= (
				"sort_col=".$this -> sort_column_selected.
				"&sort_dir=".$this -> sort_column_direction."&"
				);
		}
		
		if($this -> current_page > 1)
		{
			$prev_link = "?".$extra_url."page=".($this -> current_page - 1);
			$first_link = "?".$extra_url."page=1";
		}

		if($this -> total_pages > 1 && $this -> current_page < $this -> total_pages)
		{
			$next_link = "?".$extra_url."page=".($this -> current_page + 1);
			$last_link = "?".$extra_url."page=".($this -> total_pages);
		}

		// We build the individual numbers here
		$number_links = array();

		
		// Normally just show enough links
		if($this -> total_pages < (($this -> padding_amount * 2)+1))
			for($a = 1; $a <= $this -> total_pages; $a++)
				$number_links[] = $template_global_results_table -> pagination_number_link(
					$extra_url, $a, $this -> current_page
					);
		// When we have more than normal we have to split it up and show 
		// only the necessary ones
		else
		{

			// Left hand side
			// Is the current page still connected to the first items we can happily show
			// the first few without any splititng
			if($this -> current_page <= ($this -> padding_amount * 2))
			{
				
				//Create a link for all the first numbers
				for($a = 1; $a < $this -> current_page; $a++)
					$number_links[] = $template_global_results_table -> pagination_number_link(
						$extra_url, $a, $this -> current_page
						);

			}
			// When the current page is not connected to the left have side links we
			// must create the first few items and the ones on the very left of the
			// centre link
			else
			{

				// Show the first numbers 
				for($a = 1; $a <= $this -> padding_amount; $a++)
					$number_links[] = $template_global_results_table -> pagination_number_link(
						$extra_url, $a, $this -> current_page
						);
				
				// Ellipsis splitter
				$number_links[] = $template_global_results_table -> pagination_splitter();

				// Show the numbers directly to the left of the centre link
				for(
					$a = ($this -> current_page - $this -> padding_amount);
					$a < $this -> current_page;
					$a++
					)
					$number_links[] = $template_global_results_table -> pagination_number_link(
						$extra_url, $a, $this -> current_page
						);

			}

			// This is the current page link
			$number_links[] = $template_global_results_table -> pagination_number_link(
				$extra_url, $this -> current_page, $this -> current_page
				);

			// Right hand side
			// If the current page connected to the last items we show them unsplit like
			// the left side does
			if($this -> current_page >=
			   ($this -> total_pages - ($this -> padding_amount * 2)))
			{
				
				// Create a link for all the last numbers
				for($a = ($this -> current_page + 1); $a <= $this -> total_pages; $a++)
					$number_links[] = $template_global_results_table -> pagination_number_link(
						$extra_url, $a, $this -> current_page
						);

			}
			// When not connected to the end we need to show the surrounding links and
			// the splitter
			else
			{

				// Show the numbers direct to the right of the centre link
				$start = ($this -> current_page + 1);
				for($a = $start; $a < ($start + $this -> padding_amount); $a++)
					$number_links[] = $template_global_results_table -> pagination_number_link(
						$extra_url, $a, $this -> current_page
						);
				
				// Ellipsis splitter
				$number_links[] = $template_global_results_table -> pagination_splitter();

				// Show the end numbers
				for(
					$a = (($this -> total_pages - $this -> padding_amount) + 1);
					$a <= $this -> total_pages;
					$a++
					)
					$number_links[] = $template_global_results_table -> pagination_number_link(
						$extra_url, $a, $this -> current_page
						);

			}

		}

		$pagination_html = $template_global_results_table -> pagination(
			$this -> total_pages,
			$this -> current_page,
			$prev_link,
			$next_link,
			$first_link,
			$last_link,
			$number_links,
			$extra_url
			);

		// Finished processing data, give back the finished table
		return $template_global_results_table -> table_wrapper(
			$this -> settings,
			$template_global_results_table -> table_column_header(
				$this -> settings,
				$this -> settings['columns'],
				$extra_url."page=".$this -> current_page,
				$this -> sort_column_selected,
				$this -> sort_column_direction
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

		if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0)
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


	/**
	 * Work out what we're currently sorting by
	 */
	function save_sorting_settings()
	{

		// If we've got a selected column check that we can actually use it
		if(
			isset($_GET['sort_col']) &&
			isset($this -> settings['columns'][$_GET['sort_col']]) &&
			isset($this -> settings['columns'][$_GET['sort_col']]['sortable']) &&
			$this -> settings['columns'][$_GET['sort_col']]['sortable']
			)
		{

			$this -> sort_column_selected = $_GET['sort_col'];

		}
		// Go for the default
		else
			$this -> sort_column_selected = $this -> settings['default_sort'];

		// Get the direction we want
		if(
			isset($_GET['sort_dir']) &&
			in_array($_GET['sort_dir'], array("asc", "desc"))
			)
		{

			$this -> sort_column_direction = $_GET['sort_dir'];

		}
		// Go for the default
		else
			$this -> sort_column_direction = $this -> settings['default_sort_direction'];

	}


}