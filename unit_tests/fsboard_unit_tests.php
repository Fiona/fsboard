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
 * FSBoard unit tests script.
 * This is designed to be ran from the command line. It will instruct
 * on how to run tests and then do them with the right commands.
 *
 * -t suite    Runs the specified test.
 * -a          Runs all tests sequentially.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Tests
 */


// Test suites extend from this.
include "testsuite.class.php";

// Our newlines
define("NL", "\n");


class fsboard_unit_tests
{

	/**
	 * @var int
	 * Rolling number of tests we've ran so far.
	 */
	var $total_tests_ran = 0;


	/**
	 * @var float
	 *Start timer
	 */
	var $start_time = 0.0;


	/**
	 * Starts the test application
	 */
	function begin()
	{

		global $argv, $argc;

		// Start timing the script
        $micro_time = explode(' ', microtime());
		$this -> start_time =  $micro_time[1] + $micro_time[0];

		// Spit out header
		echo "FSBoard Test Suite".NL;
		echo "==================".NL;

		// No args = tell how to use it
		if($argc < 2)
			$this -> print_usage();

		// If we want help then oblige
		if(in_array("-h", $argv) || in_array("--help", $argv))
			$this -> print_usage();
		// If we're running a single test then do it
		elseif(in_array("-t", $argv) && $argc > 2)
			$this -> run_test();
		// If we want to run all tests then do it
		elseif(in_array("-a", $argv))
			$this -> run_all_tests();
		// Invalid input so tell the user how to use it.
		else
		{
			echo "Invalid input.".NL;
			$this -> print_usage();
		}

	}


	/**
	 * Tells how to use the test script and dies
	 */
	function print_usage()
	{
		echo "Usage:".NL;
		echo "  -t suite   Specify which test suite to run.".NL;
		echo "  -a         Run all test suites. (Creates a test database to run on.)".NL;
		die();
	}


	/**
	 * Run a single test suite
	 */
	function run_test()
	{

		global $argv;

		// Get the test we want
		$arg_num = array_search("-t", $argv);
		if(!isset($argv[$arg_num+1]))
		{
			echo "Invalid input - Specify test suite.".NL;
			print_usage();
		}

		$suite_name = $argv[$arg_num+1];

		// Actually run it
		$this -> run_suite($suite_name);

		// Spit out the footer
		$this -> end_running_tests();

	}


	/**
	 * Run every test suite one after the other
	 */
	function run_all_tests()
	{

		$files = glob("testsuites/*.php");

		foreach($files as $filename)
		{

			$matches = array();

			$num = preg_match(
				"/testsuites\/[0-9]+\-(?<suitename>[a-zA-Z-_]+).php/",
				$filename,
				$matches
				);

			if(!$num)
				continue;

			$this -> run_suite($matches['suitename']);

		}

		$this -> end_running_tests();

	}


	/**
	 * Actually runs a suite and spits out the result.
	 *
	 * @var string $suite_name Name of the suite
	 */
	function run_suite($suite_name)
	{
		
		// Print the name header
		echo NL."== Test suite: ".$suite_name." ==".NL;

		// Check it exists
		$files = glob("testsuites/*-".$suite_name.".php");

		if(count($files) < 1)
		{
			echo "Invalid input - Test suite does not exist.".NL;
			print_usage();
		}

		require $files[0];

		$classname = $suite_name."_testsuite";
		$suite = new $classname;

		$result = $suite -> run();

		$this -> total_tests_ran += $suite -> num_tests;

	}


	/**
	 * Spit out the final result at the end of the test
	 */
	function end_running_tests()
	{

		$micro_time = explode(' ', microtime());
		$end_time = $micro_time[1] + $micro_time[0];
		$spit_out_time = round(($end_time - $this -> start_time), 5);

		echo NL."==================".NL;
		echo "Ran ".$this -> total_tests_ran." tests in ".$spit_out_time.NL;

	}

}


// Run the program
$app = new fsboard_unit_tests;
$app -> begin();

?>
