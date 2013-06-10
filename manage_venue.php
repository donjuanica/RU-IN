<?php
//error_reporting(0);
date_default_timezone_set('America/Denver');
include_once ('config_inc.php');
session_start();
$redirect = false;
$redirect_last = false;
$redirect_location = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/manage_venue.php';
$db = new MySQL();
if($_SESSION['PLAYER_ID'] < 1) { 
	$_SESSION['MESSAGE'][] = '<font color=red>You must be logged in to manage venues.</font"';
	$redirect = true;
}

CheckRedirect($redirect, $redirect_location);

function CheckRedirect($redirect, $redirect_location) {
	if ($redirect) {
		header('Location: '.$redirect_location);
		exit();
	}
}

if($_REQUEST['venue_id'] > 0) {
	$sql = "
	SELECT 
		v.venue_id
		, v.venue_name
		, v.venue_address
		, v.venue_url
		, v.venue_instructions
		, v.venue_indoor
		, v.venue_status
		, v.venue_owner_player_id
		, v.venue_create_date
		, v.venue_last_update
		, p.first_name
		, p.last_name
		#, DATE_FORMAT(SUBDATE(c.date_time, INTERVAL 1 HOUR),'%h:%i:%s %p') as 'date_time'
		#, DATE_FORMAT(c.date_time,'%h:%i:%s %p') as 'date_time'
	FROM `venue` `v`
	JOIN `venue_owner` `vo` ON v.venue_id = vo.venue_id
	JOIN `players` `p` ON p.player_id = vo.player_id
	WHERE 1
	AND v.venue_id = '" . $db->verifyVal($_REQUEST['venue_id']) ."'
	AND vo.player_id = '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
	LIMIT 1
	";
	$db->Execute($sql);
	if($db->TotalRows() > 0) {
		while ($row = $db->FetchArray())	{
			$VENUE_ID=htmlentities(stripslashes($row['venue_id']));
			$VENUE_NAME=htmlentities(stripslashes($row['venue_name']));
			$VENUE_ADDRESS=htmlentities(stripslashes($row['venue_address']));
			$VENUE_URL=htmlentities(stripslashes($row['venue_url']));
			$VENUE_INSTRUCTIONS=htmlentities(stripslashes($row['venue_instructions']));
			$VENUE_INDOOR=htmlentities(stripslashes($row['venue_indoor']));
			$VENUE_STATUS=$row['venue_status'];
			$VENUE_OWNER_PLAYER_ID=$row['venue_owner_player_id'];
		}
	}
	

	$sql = "
	SELECT p.player_id
		, p.first_name
		, p.last_name
		, vo.venue_owner_id
	FROM `venue` `v`
	JOIN `venue_owner` `vo` ON v.venue_id = vo.venue_id
	JOIN `players` `p` ON p.player_id = vo.player_id
	WHERE 1
	AND v.venue_id = '" . $db->verifyVal($_REQUEST['venue_id']) ."'
	AND vo.player_id != '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
	AND v.venue_owner_player_id = '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
	";
	$db->Execute($sql);
	if($db->TotalRows() > 0) {
		$GLOBALS['VENUE_PLAYERS'] = array();
		while ($row = $db->FetchArray())	{
			$GLOBALS['VENUE_PLAYERS'][] = array(
				'PLAYER_ID' => $row['player_id'],
				'NAME' => $row['first_name'].' '.$row['last_name'],
				'VENUE_OWNER_ID' => $row['venue_owner_id'],
			);
		}
	}
} else $_REQUEST['venue_id'] = false;


$GLOBALS['table_setup'] = "\n
<div class='p'>\n
<table class='list_table' border='0' cellspacing='0'>
<tbody>
";

$GLOBALS['table_takedown'] = "\n
</tbody>\n
</table>\n
<br />\n
</div>\n
";

require_once("manage_venue_content.html");
$_SESSION['MESSAGE'] = null;