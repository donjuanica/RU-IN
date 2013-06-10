<?php
date_default_timezone_set('America/Denver');
// http://adobe.epicswell.com/getPlayerInfo.php?player_id=16&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298

/*
http://adobe.epicswell.com/api.php?op=getPlayers&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298

http://127.0.0.1/inorout/api.php?op=getPlayers&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=getPlayerInfo&key=3ac92c8eb0e7207e6a7743ca05498797&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=getComments&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=submitChoice&key=3ac92c8eb0e7207e6a7743ca05498797&choice=-1&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=submitComment&key=3ac92c8eb0e7207e6a7743ca05498797&comment=This is a nice comment&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=updateComment&key=3ac92c8eb0e7207e6a7743ca05498797&comment_id=37&comment=This is an updated comment&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=deleteComment&key=3ac92c8eb0e7207e6a7743ca05498797&comment_id=3014&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://127.0.0.1/inorout/api.php?op=updatePlayerStatus&key=3ac92c8eb0e7207e6a7743ca05498797&status=1&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298




http://adobe.epicswell.com/api.php?op=getPlayers&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=getPlayerInfo&key=2cf65b5231aa46ccc0dc2ed35318f0cf&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=getComments&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=submitChoice&key=2cf65b5231aa46ccc0dc2ed35318f0cf&choice=-1&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=submitComment&key=2cf65b5231aa46ccc0dc2ed35318f0cf&comment=This is a nice comment&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=updateComment&key=2cf65b5231aa46ccc0dc2ed35318f0cf&comment_id=37&comment=This is an updated comment&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=deleteComment&key=2cf65b5231aa46ccc0dc2ed35318f0cf&comment_id=3014&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
http://adobe.epicswell.com/api.php?op=updatePlayerStatus&key=2cf65b5231aa46ccc0dc2ed35318f0cf&status=-1&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298
*/

isset($_REQUEST['token']) ? $token = trim($_REQUEST['token']) : $token = false;
if($token != 'ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298' || !$token) return_error('You have no idea how to use this thing, do you?');

isset($_REQUEST['op']) ? $op = trim($_REQUEST['op']) : $op = false;
switch ($op) {
	case false:
		return_error('`op` parameter is missing.');
		break;
	case 'getPlayers':
		run_getPlayers();
		break;
	case 'getPlayerInfo':
		isset($_REQUEST['key']) ? $key = trim($_REQUEST['key']) : $key = false;
		if($key == false) return_error('`key` parameter is missing.');
		run_getPlayerInfo($key);
		break;
	case 'getComments':
		run_getComments();
		break;
	case 'submitChoice':
		isset($_REQUEST['key']) ? $key = trim($_REQUEST['key']) : $key = false;
		if($key == false) return_error('`key` parameter is missing.');
		isset($_REQUEST['choice']) ? $choice = trim($_REQUEST['choice']) : $choice = false;
		if($choice == false) return_error('`choice` parameter is missing.');
		run_submitChoice($key, $choice);
		break;
	case 'submitComment':
		isset($_REQUEST['key']) ? $key = trim($_REQUEST['key']) : $key = false;
		if($key == false) return_error('`key` parameter is missing.');
		isset($_REQUEST['comment']) ? $comment = trim($_REQUEST['comment']) : $comment = false;
		if($comment == false) return_error('`comment` parameter is missing.');
		if(strlen($comment)  < 1) return_error('`comment` parameter is empty.');
		run_submitComment($key, $comment);
		break;
	case 'updateComment':
		isset($_REQUEST['key']) ? $key = trim($_REQUEST['key']) : $key = false;
		if($key == false) return_error('`key` parameter is missing.');
		isset($_REQUEST['comment_id']) ? $comment_id = trim($_REQUEST['comment_id']) : $comment_id = false;
		if($comment_id == false) return_error('`comment_id` parameter is missing.');
		if($comment_id < 1) return_error('`comment_id` parameter is invalid.');
		isset($_REQUEST['comment']) ? $comment = trim($_REQUEST['comment']) : $comment = false;
		if($comment == false) return_error('`comment` parameter is missing.');
		if(strlen($comment)  < 1) return_error('`comment` parameter is empty.');
		run_updateComment($key, $comment_id, $comment);
		break;
	case 'deleteComment':
		isset($_REQUEST['key']) ? $key = trim($_REQUEST['key']) : $key = false;
		if($key == false) return_error('`key` parameter is missing.');
		isset($_REQUEST['comment_id']) ? $comment_id = trim($_REQUEST['comment_id']) : $comment_id = false;
		if($comment_id == false) return_error('`comment_id` parameter is missing.');
		run_deleteComment($key, $comment_id);
		break;
	case 'updatePlayerStatus':
		isset($_REQUEST['key']) ? $key = trim($_REQUEST['key']) : $key = false;
		if($key == false) return_error('`key` parameter is missing.');
		isset($_REQUEST['status']) ? $status = trim($_REQUEST['status']) : $status = false;
		if($status == false) return_error('`status` parameter is missing.');
		run_updatePlayerStatus($key, $status);
		break;
		
		
}


function return_success ($response) {
	$json = array('code'=>200, 'message'=>'success', 'response'=>$response);
	echo json_encode($json);
	die();
}

function return_error ($response) {
	$json = array('code'=>400, 'message'=>'error', 'response'=>$response);
	echo json_encode($json);
	die();
}

function run_getPlayers() {
	//error_reporting(0);
	require_once ('config_inc.php');
	$db = new MySQL();	
	$sql = "
	SELECT 
	p.*
	, ifnull(r.choice, 2) AS 'choice'
	, (
	    SELECT COUNT(*)
	    FROM `records`
	    WHERE `records`.`player_id` = p.player_id
	    AND `records`.choice = 1
	    AND `records`.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
	    AND DAYOFWEEK(`records`.date) IN (2,3,4,5,6)
	) AS `times_played_in_past_month`
	FROM `players` AS p 
	LEFT JOIN `records` AS r 
	    ON p.player_id = r.player_id
	    AND r.date = DATE(NOW())
	WHERE p.active = 1
	ORDER BY `times_played_in_past_month` DESC, p.first_name ASC
	";
	$db->Execute($sql);
	
	$playersIn = array();
	$playersOut = array();
	$playersUndecided = array();
	
	while ($row = $db->FetchArray())	{
		$data = array('name' => $row['first_name'].' '.$row['last_name'], 'times_played_in_past_month' => $row['times_played_in_past_month']);
		switch ($row['choice']) {
			case 1: $playersIn[] = $data; break;
			case 2: $playersUndecided[] = $data; break;
			default: $playersOut[] = $data;
		}
	}
	$sql = "
	SELECT `content`
	FROM 
	`message` 
	WHERE `active` = 1
	";
	$db->Execute($sql);
	$Messages = array();
	while ($row = $db->FetchArray())	{
		$Messages[] = $row['content'];
	}
	return_success(array('playersIn' => $playersIn , 'playersOut' => $playersOut , 'playersUndecided' => $playersUndecided , 'Messages' => $Messages));
}

function run_getPlayerInfo($key) {	
	require_once ('config_inc.php');
	$db = new MySQL();
	$player_id = getPlayerIDfromKey($key);
	$sql = "
	SELECT p.*
	, CASE  
		WHEN r.choice IS NULL THEN 'Questionable'  
		WHEN r.choice = 1 THEN 'In'  
		ELSE 'Out'  END AS 'choice'
	, (    
		SELECT COUNT(*)    
		FROM `records`    
		WHERE `records`.`player_id` = '".$db->verifyVal($player_id)."'
		AND `records`.choice = 1    
		AND `records`.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)    
		AND DAYOFWEEK(`records`.date) IN (2,3,4,5,6)
		) AS `times_played_in_past_month`
	, IF(p.active = 1, 'active','in-active') `status`
	FROM `players` AS `p` 
	LEFT JOIN `records` AS `r` ON p.player_id = r.player_id AND r.date = DATE(NOW())
	WHERE 1
	#AND p.active = 1
	AND p.player_id = '".$db->verifyVal($player_id)."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() < 1) return_error('`key` '. $key .' could not be found.');
	$playerInfo=array();
	while ($row = $db->FetchArray())	{	
		$playerInfo[] = array(    
			'player_id' => $row['player_id']    
			, 'full_name' => $row['first_name'].' '.$row['last_name']    
			, 'first_name' => $row['first_name']    
			, 'last_name' => $row['last_name']    
			, 'times_played_in_past_month' => $row['times_played_in_past_month']    
			, 'choice' => $row['choice']    
			, 'email' => $row['email']    
			, 'key' => $row['key']       
			, 'status' => $row['status']    
		);
	}
	return_success ($playerInfo);
}

function run_getComments() {
	require_once ('config_inc.php');
	$db = new MySQL();
	$sql = "
	SELECT 
		c.comment
		, p.first_name
		, p.last_name
		, DATE_FORMAT(SUBDATE(c.date_time, INTERVAL 1 HOUR),'%h:%i:%s %p') as 'date_time'
		, c.player_id
		, p.key
		, c.comment_id
	FROM `comments` `c`
	JOIN `players` `p` ON p.player_id = c.player_id
	WHERE 1
	AND `visible` = 1
	AND `date` = DATE(NOW())
	ORDER BY `comment_id` ASC
	";
	$db->Execute($sql);
	$playerComments = array();
	if($db->TotalRows() > 0) {
		$count=0;
		while ($row = $db->FetchArray())	{
			$playerComments[] = array(
				'name'=> $row['first_name'].' '.$row['last_name'] 
				, 'first_name' => $row['first_name']    
				, 'last_name' => $row['last_name']    
				, 'timestamp' => $row['date_time']
				, 'comment' => stripslashes($row['comment'])
				, 'player_id' => $row['player_id'] 
				, 'key' => $row['key']
				, 'order' => $count
				, 'comment_id' => $row['comment_id']
				);
			$count++;
		}
	}
	return_success(array('playerComments' => $playerComments));
}

function run_submitChoice($key, $choice) {
	// valid choices are 1 for 'In' or -1 for 'Out'
	if(in_array($choice, array(1,-1)) == false) return_error('`choice` parameter is invalid.');
	require_once ('config_inc.php');
	$db = new MySQL();
	$player_id = getPlayerIDfromKey($key);
	
	if($choice == 1 && date('H') >= 12 ) return_failure('There appears to be an error in your thinking. The error seems to be that you think its OK to run up your numbers even though you didn\'t say you were going to play before noon. If your behaviour peresists, a demerit will be added to your account for all to see. Now, go forth and sin no more!'); 
	
	$sql = "
	DELETE IGNORE FROM `records`
	WHERE 1
	AND `records`.`player_id` = '" . $player_id ."'
	AND `records`.`date` = DATE('".date("Y-m-d")."')
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	$sql = "
	INSERT IGNORE INTO `records` 
	SET 
		`player_id` = '" . $player_id ."'
		, `date` = DATE('".date("Y-m-d")."')
		, `choice` = '" . $db->verifyVal($choice) ."'
		, `last_update` = '".date("Y-m-d H:i:s")."'
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		if($db->Affected_Rows() > 0) return_success('Your choice has been saved.');
			else return_failure('Your choice could not be saved.');
	}
	return_failure('Your choice could not be saved.');
}

function run_submitComment($key, $comment) {
	require_once ('config_inc.php');
	$db = new MySQL();
	$player_id = getPlayerIDfromKey($key);
	
	$sql = "
	INSERT IGNORE INTO `comments` 
	SET
		`player_id` = '" . $player_id ."'
		, `visible` = 1
		, `comment` = '". addslashes($comment) ."'
		, `date` = '".date("Y-m-d H:i:s")."'
		, `date_time` = '".date("Y-m-d H:i:s")."'
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) return_success('Your comment has been saved.');
	else return_error('Your comment could not be saved.');
}


function run_updateComment($key, $comment_id, $comment) {
	require_once ('config_inc.php');
	$db = new MySQL();
	$player_id = getPlayerIDfromKey($key);
	
	$sql = "
	UPDATE `comments` 
	SET `comment` = '". addslashes($comment) ."'
	WHERE 1
	AND `player_id` = '" . $db->verifyVal($player_id) ."'
	AND `comment_id` = '". $db->verifyVal($comment_id) ."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) return_success('Your comment has been updated.');
	else return_error('Your comment could not be updated.');
}


function run_deleteComment($key, $comment_id) {
	require_once ('config_inc.php');
	$db = new MySQL();
	$player_id = getPlayerIDfromKey($key);
	
	$sql = "
	DELETE FROM `comments` 
	WHERE 1
	AND `player_id` = '" . $db->verifyVal($player_id) ."'
	AND `comment_id` = '". $db->verifyVal($comment_id) ."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) return_success('Your comment has been deleted.');
	else return_error('Your comment could not be deleted.');
}

function run_updatePlayerStatus($key, $status) {
	// valid choices are 1 for 'active' and -1 for 'inactive'
	if(in_array($status, array(1,-1)) == false) return_error('`status` parameter is invalid.');
	if($status < 1) $status = 0;
	require_once ('config_inc.php');
	$db = new MySQL();
	$player_id = getPlayerIDfromKey($key);
	
	$sql = "
	UPDATE IGNORE `players` 
	SET `active` = '" . $db->verifyVal($status) ."'
	WHERE `player_id` = '" . $db->verifyVal($player_id) ."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) return_success('Your status has been updated.');
	else return_error('Your status could not be updated.');
}

function getPlayerIDfromKey ($key) {
	require_once ('config_inc.php');
	$db = new MySQL();
	$sql = "
	SELECT 
	p.player_id
	FROM `players` AS `p`
	WHERE 1 
	AND p.key = '". $db->verifyVal($key) ."'
	LIMIT 1
	";
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) {
		$row = $db->FetchArray();
		return $row['player_id'];
	}
	return_error('Player account not found. Please use a valid key.');

}

die();
?>