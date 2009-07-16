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
 * Control panel specific functions
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


/*
 * Shortcut to tell the control panel to finish it's output
 */
function finish_cp_output()
{

	global $output, $template_control_panel;
	
	$output -> add(
		$template_control_panel -> control_panel_wrap(
			$template_control_panel -> control_panel_menu(),		
			$output -> buffer_2
		)		
	);

}

?>