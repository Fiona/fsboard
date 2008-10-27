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
 * COMMON TASK
 * Hourly cleanup
 * 
 * This runs about every hour and cleans up unneeded database entries.
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Common
 * 
 * @started 28 Mar 2006
 * @edited 06 Feb 2007
 */



// ----------------------------------------------------------------------------------------------------------------------

// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


// *********************************
// Delete old sessions
// *********************************
$timeout = ($cache -> cache['config']['session_expire']) ? $cache -> cache['config']['session_expire'] : 10;
$timeout = TIME - ($timeout * 60);

$db -> basic_delete("sessions", "last_active < '".$timeout."'");

$common_task_log = "Deleted ".$db -> affected_rows()." sessions.";

?>
