<?
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
 * FSBoard unit testsutie class.
 *
 * All test suites should extend from this.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Tests
 */


/**
 * Child class of all testsuites.
 * Suites can override setup() and teardown() all other methods will be
 * assumed to be tests. The tests should return True or False depending
 * on their outcome.
 */
class testsuite
{

	/**
	 * @var string
	 * This should be used to explain any error that was found. It will be spat
	 * out if any of the tests return False.
	 */
	var $error_message = "";


	/**
	 * @var int
	 * Number of test methods we have.
	 */
	var $num_tests = 0;


	/**
	 * Start up the suite.
	 */
	function setup(){ }


	/**
	 * Actually run the tests
	 *
	 * @return bool On success or failure.
	 */
	function run()
	{

		// Get all the methods that contain our tests
		$tests = get_class_methods($this);
		$tests = array_diff($tests, array("setup", "teardown", "run"));
		$this -> num_tests = count($tests);

		// Setup the suite
		$this -> setup();

		// Run all of our methods
		foreach($tests as $test_method)
		{

			echo "Running ".$test_method." ... ";

			$test_result = $this -> {$test_method}();

			if($test_result === False)
			{

				echo "\x1b[31mFailure!\x1b[0m".NL."  Error:".$this -> error_message.NL;
				$this -> teardown();
				return False;

			}

			echo "\x1b[33mOK\x1b[0m".NL;

		}

		// Take everything down again
		$this -> teardown();

		return True;

	}


	/**
	 * Undo all the damage.
	 */
	function teardown(){ }

}
