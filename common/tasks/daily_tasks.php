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
 * Daily tasks
 * 
 * Does stuff that needs to be done day by day
 * 
 * @author Fiona Burrows <fiona@fsboard.com>
 * @copyright Fiona Burrows 2007
 * @version 1.0
 * @package FSBoard
 * @subpackage Common
 * 
 * @started 24 Oct 2008
 * @edited 24 Oct 2008
 */



// ----------------------------------------------------------------------------------------------------------------------

// Check script entry
if (!defined("FSBOARD")) die("Script has not been initialised correctly! (FSBOARD not defined)");


// *********************************
// Delete old items in undeletion
// *********************************
$db -> basic_delete("undelete", "time < '".((60 * 60) * 24)."'");

$common_task_log = "Deleted ".$db -> affected_rows()." items in undeletion.";

?>
