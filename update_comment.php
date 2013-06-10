<?php
//error_reporting(0);
date_default_timezone_set('America/Denver');
include_once ('config_inc.php');
session_start();
$redirect = false;
$redirect_last = false;
$db = new MySQL();
if($_SESSION['PLAYER_ID'] < 1) { 
	$_SESSION['MESSAGE'][] = '<font color=red>You must be logged in to update your comments.</font"';
	$redirect = true;
}
if($_REQUEST['comment_id'] < 1) { 
	$_SESSION['MESSAGE'][] = '<font color=red>Invalid comment_id.</font"';
	$redirect = true;
}


if ($redirect) {
	header('Location: index.php');
	exit();
}

$sql = "
SELECT 
	c.comment
	, c.player_id
	, c.comment_id
	, p.first_name
	, p.last_name
	#, DATE_FORMAT(SUBDATE(c.date_time, INTERVAL 1 HOUR),'%h:%i:%s %p') as 'date_time'
	, DATE_FORMAT(c.date_time,'%h:%i:%s %p') as 'date_time'
FROM 
	`comments` c
JOIN `players` p ON p.player_id = c.player_id
WHERE 1
AND c.`visible` = 1
AND c.`date` = DATE('".date("Y-m-d")."')
AND c.`player_id` = '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
AND c.`comment_id` = '". $db->verifyVal($_REQUEST['comment_id']) ."'
LIMIT 1
";
$db->Execute($sql);
$GLOBALS['COMMENTS'] = array();
if($db->TotalRows() > 0) {
	while ($row = $db->FetchArray())	{
		$GLOBALS['COMMENTS'][] = array(
			'COMMENT_ID'=> $row['comment_id'],
			'PLAYER_ID'=> $row['player_id'],
			'NAME'=> $row['first_name']." ".$row['last_name'],
			'DATE_TIME'=>$row['date_time'],
			'COMMENT'=>stripslashes($row['comment'])
			);
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



require_once("update_comment_content.html");




$_SESSION['MESSAGE'] = null;







/*
ALTER TABLE `players` ADD `phone` VARCHAR( 25 ) NOT NULL ,
ADD `has_texting` TINYINT( 1 ) NOT NULL ,
ADD `im` VARCHAR( 25 ) NOT NULL ,
ADD `im_service` VARCHAR( 25 ) NOT NULL ;
*/
?>