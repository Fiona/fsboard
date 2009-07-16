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
 * Main FSBoard javascript file
 *
 * @author Fiona Burrows <fiona@fsboard.com>
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


$(document).ready(function()
{

	$(".logged_in_as_msg").click(function()
	{
	
		$(".logo_header img").slideToggle();
		return false;
	
	});


	// Debug information
	$("div.debug_level_2_wrapper a[rel=explain]").click(
		function()
		{
			$(this).next("table.explain_table").slideToggle();
			return false;
		}
	);
	$("div.debug_level_2_wrapper table.explain_table").hide();

});