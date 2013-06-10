<?php
//error_reporting(0);
date_default_timezone_set('America/Denver');
include_once ('config_inc.php');
session_start();
$redirect = false;
$redirect_last = false;
$redirect_location = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/manage_venue_players.php';
$db = new MySQL();

if($_SESSION['PLAYER_ID'] < 1) { 
	$_SESSION['MESSAGE'][] = '<font color=red>You must be logged in to manage venue players.</font"';
	$redirect = true;
}

CheckRedirect($redirect, $redirect_location);

function CheckRedirect($redirect, $redirect_location) {
	if ($redirect) {
		header('Location: '.$redirect_location);
		exit();
	}
}

if($_REQUEST['venue_id'] > 0 && strlen($_REQUEST['keyword']) > 1) {
	$sql = "
	SELECT 
		p.player_id
		, p.first_name
		, p.last_name
	FROM `players` `p`
	WHERE p.first_name LIKE '%" . $db->verifyVal($_REQUEST['keyword']) ."%'
	OR p.last_name LIKE '%" . $db->verifyVal($_REQUEST['keyword']) ."%'
	OR p.email LIKE '%" . $db->verifyVal($_REQUEST['keyword']) ."%'
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->TotalRows() > 0) {
		$GLOBALS['PLAYERS'] = array();
		while ($row = $db->FetchArray())	{
			$GLOBALS['PLAYERS'][] = array(
				'PLAYER_ID' => $row['player_id'],
				'NAME' => $row['first_name'].' '.$row['last_name'],
			);
		}
	}
}

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

require_once("manage_venue_players_content.html");
$_SESSION['MESSAGE'] = null;