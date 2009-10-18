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
 * @version 1.0
 * @package FSBoard
 * @subpackage Main
 */


// -----------------------------------------------------------------------------


// Check script entry
if(!defined("FSBOARD"))
	die("Script has not been initialised correctly! (FSBOARD not defined)");


// *********************************
// Delete old items in undeletion
// *********************************
$db -> basic_delete("undelete", "`time` < '".(time() - ((60 * 60) * 24))."'");

$common_task_log = "Deleted ".$db -> affected_rows()." items in undeletion.";

?>
