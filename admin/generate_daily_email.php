<?php

//	http://localhost/inorout/generate_daily_email.php?run_key=run_for_your_life&whats_your_favorite_color=666&debug=0&player_id=1
//	http://adobe.epicswell.com/generate_daily_email.php?run_key=run_for_your_life&whats_your_favorite_color=666&debug=0

session_start();
require_once('../config_inc.php');
include_once('email_functions.php');

if(isset($argc)) { 
  if($argc > 0) {   
    for ($i=1;$i < $argc;$i++)   {     
      parse_str($argv[$i],$tmp);     
      $_REQUEST = array_merge($_REQUEST, $tmp);   
    } 
  } 
}

$DEBUG = true;

if(isset($_REQUEST['debug']) == true) {
	if($_REQUEST['debug'] == 0) $DEBUG = false;
}

if(isset($_REQUEST['run_key']) == false) {
	die();
}

if($_REQUEST['run_key'] != 'run_for_your_life') {
	die();
}

if(isset($_REQUEST['whats_your_favorite_color']) == false) {
	die();
}

if($_REQUEST['whats_your_favorite_color'] != '987654321') {
	die();
}


$log = update_player_keys($_REQUEST['player_id']) . " keys were updated.<br><hr><br>";

$db = new MySQL();
$sql = "SELECT `content` as 'special_message' FROM `message` WHERE `active` = 1";
$db->Execute($sql);
$special_message='';
if($db->TotalRows() > 0) {
	while ($row = $db->FetchArray()) {
		$special_message.= "<font color=red><strong>Notice:</strong></font>&nbsp;".$row['special_message']."<br><br>";
	}
}
$sql = "
SELECT 
	p.*
	, DATE(NOW()) AS 'today'
FROM `players` AS p
WHERE 1
";
if(isset($_REQUEST['player_id']) == true) {
	if($_REQUEST['player_id'] > 0) $sql.= " AND p.player_id = '". $db->verifyVal($_REQUEST['player_id']) ."' " ;
} else $sql.= " AND p.active = 1 AND p.send_email = 1";
//die($sql);
$db->Execute($sql);
$total_players_found = $db->Affected_Rows();
if($total_players_found > 0) {
	$count = 0;
	
	$GLOBALS["batch_id"] = next_email_batch_id();
	if($GLOBALS["batch_id"]['status'] == true) $GLOBALS["batch_id"] = $GLOBALS["batch_id"]['value'];
	
	while ($row = $db->FetchArray()) {
		$count++;
		if($GLOBALS['IS_DEV']) $url = "http://localhost/inorout";
			else $url = "http://adobe.epicswell.com";
		$email_content = $special_message . $row['first_name'] . ',<br><br>'."\n".'
Are you playing ball today? ' . $row['today'] . ' @ 11:30 A.M.<br><br>'."\n".'

<a href="'.$url.'/index.php?&key1='.$row['key'].'&key2=1" target="_blank">Yes - I\'m in!</a>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="'.$url.'/index.php?&key1='.$row['key'].'&key2=0" target="_blank">No - I\'m out!</a>

<br />
<br />
<br />
<a href="'.$url.'/index.php?&key1='.$row['key'].'" target="_blank">View today\'s list.</a><br><br>'."\n".'
<a href="'.$url.'/index.php?&key1='.$row['key'].'" target="_blank">Use this link to create a bookmark within your browser that will identify you when opened.</a><br><br>'."\n".'
<a href="'.$url.'/index.php?&key4='.$row['player_id'].'&key5=0" target="_blank">If you wish to disable your account and no longer receive these emails, click here.</a><br>
<a href="'.$url.'/index.php?&key4='.$row['player_id'].'&key5=1" target="_blank">If you wish to enable your account and receive these emails after previously declining, click here.</a>
<br />
<br />
<a href="'.$url.'/index.php?&key4='.$row['player_id'].'&key6=0" target="_blank">If you no longer wish to receive these emails but want your account to remain active, click here.</a><br>
<a href="'.$url.'/index.php?&key4='.$row['player_id'].'&key6=1" target="_blank">If your account is active and you  wish to receive these emails after previously declining them, click here.</a>
';
		$from = "Travis Whitney <inorout@epicswell.com>";
		
		if($DEBUG == true) $to = $from; else $to = $row['first_name'] . " " . $row['last_name'] . " <" . $row['email'] . ">";

		$subject =  "Ball? " . $row['today'] . " @ 11:30 | " . $row['first_name'];
		
		$cacheMAIL_tmp = cacheMAIL($GLOBALS["batch_id"], $row['player_id'], $to, $subject, $email_content);
		
		//$sendPEARmail_tmp = sendPEARmail($from, $to, $subject, $email_content, '');
		
		$log.= "<br>{$count} of {$total_players_found}<br>". htmlentities($to) ."<br>".$subject."<br>".$email_content."<br>";
		//print $cacheMAIL_tmp['value'];
		$log.= $cacheMAIL_tmp['value'];
		$log.= "<br><hr>";
		//usleep(5000); // in microseconds = 2000000 = 2 seconds
	}
	
	//sendPEARmail($from, "Travis <travis.epicswell@gmail.com>", "Today's Ball Email Log " . $row['today'], $log, '');
	
	//$cacheMAIL_tmp = cacheMAIL($GLOBALS["batch_id"], "1", "Travis <travis.epicswell@gmail.com>", "Today's Ball Email Log " . $row['today'], $log);
	
}
print "<br><br><hr><hr><br><br>".$log;
send_queued_mail(null,null);
?>