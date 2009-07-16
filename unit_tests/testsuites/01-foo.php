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
 * FSBoard unit test - Developing the unit test suite.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Tests
 */


// -----------------------------------------------------------------------------


class foo_testsuite extends testsuite
{

	function test_nothing1()
	{

		return True;

	}

	function test_nothing2()
	{

		$this -> error_message = "Something went wrong.";
		return False;

	}

}

?>
