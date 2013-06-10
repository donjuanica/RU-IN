<?php
//error_reporting(0);
date_default_timezone_set('America/Denver');
include_once ('config_inc.php');
session_start();
$redirect = false;
$redirect_last = false;
$db = new MySQL();
//  http://127.0.0.1/inorout/index.php?&1=3ac92c8eb0e7207e6a7743ca05498797&2=1

$redirect_location = 'http://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['PHP_SELF']), '/\\').'/index.php';

/* THIS SECTION DETERMINES WHO THE PLAYER IS BASED ON WHETHER THE GET PARAMETER INCLUDES A 1 OR A 4 */
if(isset($_REQUEST['1']) === true && isset($_REQUEST['key1']) === false) $_REQUEST['key1'] = $_REQUEST['1'];
if(isset($_REQUEST['key1']) || isset($_REQUEST['key4']) || ( isset($_COOKIE['PLAYER_ID']) && !isset($_SESSION['PLAYER_ID']) )) { 
	if(isset($_REQUEST['key1'])) if(  strlen($_REQUEST['key1'])  < 1  ) { /* param 1 = hash of player for that day */
		$_SESSION['MESSAGE'][] = '<font color=red>Parameter "key1" is invalid. Please use the link provided in your email.</font>';
		$redirect = true;
	}
//if($_REQUEST['key1'] == '198049a809f91fc4e5bbf396136d329a') echo '<br>'.$_REQUEST['key1'].'<br>';
	if(isset($_REQUEST['key4'])) if(  strlen($_REQUEST['key4'])  < 1  ) { /* param 4 = player_id of the player */
		$_SESSION['MESSAGE'][] = '<font color=red>Parameter "key4" is invalid. Please use the link provided in your email.</font>';
		$redirect = true;
	}
	$sql = "
	SELECT 
	p.*
	FROM `players` AS p
	WHERE 1 
	";
	if(isset($_REQUEST['key1'])) $sql.= " AND p.key = '". $db->verifyVal($_REQUEST['key1']) ."' ";
	else if(isset($_REQUEST['key4'])) $sql.= " AND p.player_id = '". $db->verifyVal($_REQUEST['key4']) ."' ";
        else if(isset($_COOKIE['PLAYER_ID'])) $sql.= " AND p.player_id = '". $db->verifyVal($_COOKIE['PLAYER_ID']) ."' ";
        else {
		$_SESSION['MESSAGE'][] = '<font color=red>Parameters are invalid. Please use the link provided in your email.</font>';
		$redirect = true;
	}
	$sql.= " LIMIT 1 ";
	CheckRedirect($redirect, $redirect_location);
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) {
		$row = $db->FetchArray();
		$_SESSION['FIRST_NAME'] = $row['first_name'];
		$_SESSION['LAST_NAME'] = $row['last_name'];
		$_SESSION['EMAIL'] = $row['email'];
		$_SESSION['PLAYER_ID'] = $row['player_id'];
		$_SESSION['KEY'] = $row['key'];
		setcookie('PLAYER_ID', $_SESSION['PLAYER_ID'], time()+(3600*24*365)); // cookie expires in one year.
	} else {
		$_SESSION['MESSAGE'][] = '<font color=red>Player account not found. Please use the link provided in your email.</font>';
	}
	$redirect_last = true;
}
/* END: THIS SECTION DETERMINES WHO THE PLAYER IS BASED ON WHETHER THE GET PARAMETER INCLUDES A 1 OR A 4 */



/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */


if(isset($_REQUEST['key2'])) { /* 0 or 1 - choice for whether they are playing ball today or not - in = 1, out = 0 */
	if(strlen($_REQUEST['key2']) < 0 ||  strlen($_REQUEST['key2']) > 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Parameter "key2" is invalid. Please use the link provided in your email.</font>';
		$redirect = true;
	}
	if(isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Error. Please use the link provided in your email.</font>';
		$redirect = true;
	}
	$check_hour = true;
	$check_hour = false;	
	if($_REQUEST['key2'] == 1 && date('H') > 12 && $check_hour) {
		$_SESSION['MESSAGE'][] = '<font color=red>There appears to be an error in your decision making process.<br />The error seems to be that you think its OK to run up your numbers even though you didn\'t say you were going to play before noon.<br />You should be ashamed of yourself. Now go to your room and think about you\'ve done.</font>';
		$redirect = true;
	}

	CheckRedirect($redirect, $redirect_location);
	RemovePlayerChoice($_SESSION['PLAYER_ID']);
	SetPlayerChoice($_SESSION['PLAYER_ID'], $_REQUEST['key2']);
	$redirect = true;
}

function CheckRedirect($redirect, $redirect_location) {
	if ($redirect) {
		header('Location: '.$redirect_location);
		exit();
	}
}

function RemovePlayerChoice($player_id = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	global $db;
	$sql = "
	DELETE IGNORE FROM `records` 
	WHERE 
	`records`.`player_id` = '" . $db->verifyVal($player_id) ."'
	AND `records`.`date` = DATE('".date("Y-m-d")."')
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your previous choice has been removed.</font>';
}


function SetPlayerChoice($player_id = false, $choice = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	global $db;
	$sql = "
	INSERT IGNORE INTO `records` 
	SET 
		`player_id` = '" . $db->verifyVal($player_id) ."'
		, `date` = DATE('".date("Y-m-d")."')
		, `choice` = '" . $db->verifyVal($choice) ."'
		, `last_update` = '".date("Y-m-d H:i:s")."'
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		if($db->Affected_Rows() > 0) {
			$_SESSION['MESSAGE'][] = '<font color=green>Your choice has been saved.</font>';
			if($choice > 0) SetVenueAvailableForPlayer($player_id);
			else SetVenueNOTAvailableForPlayer($player_id);
		} else $_SESSION['MESSAGE'][] = '<font color=red>Your choice could not be saved.</font>';
	} else {
		$_SESSION['MESSAGE'][]= '<font color=red>Your choice could not be saved.</font>';
	}		
}

function SetVenueAvailableForPlayer ($player_id = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	global $db;
	$sql = "
	INSERT IGNORE INTO `venue_available` 
	SELECT
		null `venue_available_id`
		, `venue`.`venue_id` `venue_id`
		, '" . $db->verifyVal($player_id) ."' `player_id`
		, '".date("Y-m-d")."' `date`
		, '".date("Y-m-d H:i:s")."' `last_update`
	FROM `venue_owner`
	JOIN `venue` ON `venue`.`venue_id` = `venue_owner`.`venue_id`
	WHERE 1
	AND `venue_owner`.`player_id` = '" . $db->verifyVal($player_id) ."'
	AND `venue`.`venue_status` = 1
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		$_SESSION['MESSAGE'][] = '<font color=green>Your venue has been set as available.</font>';
	}
	return true;
}

function SetVenueNOTAvailableForPlayer ($player_id = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	global $db;
	$sql = "
	DELETE IGNORE FROM `venue_available` 
	WHERE 1
	AND `player_id` = '" . $db->verifyVal($player_id) ."'
	AND `date` = DATE('".date("Y-m-d")."')
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your venue has been set as un-available.</font>';
	else $_SESSION['MESSAGE'][] = '<font color=red>Your venue could not be set as un-available.</font>';
	$redirect = true;
}


function SetVenueAvailableByVenueID ($venue_id = false) {
	if(!$venue_id) {
		$_SESSION['MESSAGE'][] = '<font color=red>Your venue could not be set as available.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	global $db;
	$sql = "
	INSERT IGNORE INTO `venue_available` 
	SELECT
		null `venue_available_id`
		, `venue`.`venue_id` `venue_id`
		, '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."' `player_id`
		, '".date("Y-m-d")."' `date`
		, '".date("Y-m-d H:i:s")."' `last_update`
	FROM `venue_owner`
	JOIN `venue` ON `venue`.`venue_id` = `venue_owner`.`venue_id`
	WHERE 1
	AND `venue_owner`.`player_id` = '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
	AND `venue`.`venue_id` = '" . $db->verifyVal($venue_id) ."'
	#AND `venue`.`venue_status` = 1
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) {
		$_SESSION['MESSAGE'][] = '<font color=green>Your venue has been set as available.</font>';
		return true;
	} else return false;
}

function SetVenueNOTAvailableByAvailabilityID ($venue_available_id = false) {
	if(!$venue_available_id) {
		$_SESSION['MESSAGE'][] = '<font color=red>Your venue could not be set as un-available.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	global $db;
	$sql = "
	DELETE IGNORE FROM `venue_available` 
	WHERE 1
	AND `venue_available_id` = '" . $db->verifyVal($venue_available_id) ."'
	AND `date` = DATE('".date("Y-m-d")."')
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) {
		$_SESSION['MESSAGE'][] = '<font color=green>Your venue has been set as un-available.</font>';
		return true;
	} else {
		$_SESSION['MESSAGE'][] = '<font color=red>Your venue could not be set as un-available.</font>';
		return false;
	}
}

/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */


if(isset($_REQUEST['go_back'])) { 
	CheckRedirect(true, $redirect_location);
	exit();
}

// Sets a venue as being unavailable for today.
if(isset($_REQUEST['set_venue_NOT_available']) == true && isset($_REQUEST['venue_available_id']) == true) { 
	if( strlen($_REQUEST['venue_available_id']) < 1 || $_REQUEST['venue_available_id'] < 0 || $_REQUEST['set_venue_availability'] < 0 || $_REQUEST['set_venue_availability'] > 1) {
		$_SESSION['MESSAGE'][] = '<font color=red>Bad request.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	SetVenueNOTAvailableByAvailabilityID($_REQUEST['venue_available_id']);
	$redirect = true;
}

// Sets a venue as available for today.
if(isset($_REQUEST['set_venue_available']) == true && isset($_REQUEST['venue_id']) == true) { 
	if( strlen($_REQUEST['venue_id']) < 1 || $_REQUEST['venue_id'] < 0 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Bad request.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	SetVenueAvailableByVenueID($_REQUEST['venue_id']);
	$redirect = true;
}

// Changes account status - 1 = active, 0 = incactive
if(isset($_REQUEST['key5'])) {
	if(  strlen($_REQUEST['key5'])  < 0 or  strlen($_REQUEST['key5'])  > 1  ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Parameter "key5" is invalid. Please use the link provided in your email.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Error. Please use the link provided in your email.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	ChangeAccountStatus($_SESSION['PLAYER_ID'], $_REQUEST['key5']);
	$redirect = true;
}

function ChangeAccountStatus($player_id = false, $status = false) {
	global $db;
	$sql = "
	UPDATE IGNORE `players` 
	SET
		`active` = '" . $db->verifyVal($status) ."'
	WHERE
		`player_id` = '" . $db->verifyVal($player_id) ."'
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		if($_REQUEST['key5'] == 1) $_SESSION['MESSAGE'][] = '<font color=green>Your account has been activated.</font>';
		else $_SESSION['MESSAGE'][] = '<font color=green>Your account has been disabled.</font>';
	} else $_SESSION['MESSAGE'][] = '<font color=red>Your account could not be updated.</font>';
}

function Change_Send_Email_Option ($player_id = false, $key6 = false) {
	global $db;
	$sql = "
	UPDATE IGNORE `players` 
	SET
		`send_email` = '" . $db->verifyVal($key6) ."'
	WHERE
		`player_id` = '" . $db->verifyVal($player_id) ."'
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		if($_REQUEST['key5'] == 1) $_SESSION['MESSAGE'][] = '<font color=green>You will now receive the daily emails.</font>';
		else $_SESSION['MESSAGE'][] = '<font color=green>You will no longer receive the daily emails.</font>';
		return true;
	} else {
		$_SESSION['MESSAGE'][] = '<font color=red>Your request could not be completed.</font>';
		return false;
	}
}

// Changes option to send daily email or not - 1 = active, 0 = incactive
if(isset($_REQUEST['key6'])) {
	if( $_REQUEST['key6'] < 0 or $_REQUEST['key6'] > 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Parameter "key6" is invalid. Please use the link provided in your email.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Error. Please use the link provided in your email.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	Change_Send_Email_Option($_SESSION['PLAYER_ID'], $_REQUEST['key6']);
	$redirect = true;
}
/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */

//die(print_r($_POST));


if ($redirect == true or $redirect_last == true) {
		header('Location: '.$redirect_location);
	exit();
}

// Adds a new comment.
if(isset($_POST['submit_comment']) and $_POST['submit_comment'] == 'Add Comment' and isset($_POST['comment_content'])) { 
	if(  strlen($_REQUEST['comment_content'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No comment content found. Please type something into the comment box before submitting.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	AddComment($_SESSION['PLAYER_ID'], $_POST['comment_content']);
	$redirect = true;
}

function AddComment ($player_id = false, $comment = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	if(!$comment) return false;
	global $db;
	$sql = "
	INSERT IGNORE INTO `comments` 
	SET
		`player_id` = '" . $db->verifyVal($player_id) ."'
		, `visible` = 1
		, `comment` = '". $db->verifyVal(addslashes($comment)) ."'
		, `date` = '".date("Y-m-d H:i:s")."'
		, `date_time` = '".date("Y-m-d H:i:s")."'
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		$_SESSION['MESSAGE'][] = '<font color=green>Your comment has been saved.</font>';
		return true;
	} else {
		$_SESSION['MESSAGE'][] = '<font color=red>Your comment could not be saved.</font>';
		return false;
	}
}

// Updates an existing comment.
if(isset($_POST['update_comment']) && isset($_POST['comment_content']) && isset($_POST['comment_id'])) { 
	if($_POST['update_comment'] != 'Update Comment') {
		$redirect = true;
	}
	if(strlen($_REQUEST['comment_content'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No comment content found. Please type something into the comment box before submitting.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	UpdateComment($_SESSION['PLAYER_ID'], $_POST['comment_id'], $_POST['comment_content']);
	$redirect = true;
}

function UpdateComment($player_id = false, $comment_id = false, $comment = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	if(!$comment || !$comment_id) return false;
	global $db;
	$sql = "
	UPDATE `comments` 
	SET `comment` = '". $db->verifyVal(addslashes($comment)) ."'
	WHERE 1
	AND `player_id` = '" . $db->verifyVal($player_id) ."'
	AND `comment_id` = '". $db->verifyVal($comment_id) ."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		$_SESSION['MESSAGE'][] = '<font color=green>Your comment has been updated.</font>';
		return true;
	} else {
		$_SESSION['MESSAGE'][] = '<font color=red>Your comment could not be updated.</font>';
		return false;
	}
}

// Deletes an existing comment.
if($_REQUEST['delete_comment'] == 'Delete Comment' and $_REQUEST['comment_id'] > 0) { 
	
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected.</font>';
		$redirect = true;
	}	
	CheckRedirect($redirect, $redirect_location);
	DeleteComment($_SESSION['PLAYER_ID'], $_REQUEST['comment_id']);
	$redirect = true;
}

function DeleteComment($player_id = false, $comment_id = false) {
	if(!$player_id) $player_id = $_SESSION['PLAYER_ID'];
	if(!$comment_id) return false;
	global $db;
	$sql = "
	DELETE FROM `comments` 
	WHERE 1
	AND `player_id` = '" . $db->verifyVal($player_id) ."'
	AND `comment_id` = '". $db->verifyVal($comment_id) ."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	if($db->Execute($sql)) {
		$_SESSION['MESSAGE'][] = '<font color=green>Your comment has been deleted.</font>';
		return true;
	} else {
		$_SESSION['MESSAGE'][] = '<font color=red>Your comment could not be deleted.</font>';
		return false;
	}
}


if(isset($_POST['add_venue']) and $_POST['add_venue'] == 'Add Venue') { 
	if( strlen($_REQUEST['venue_name'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No venue name found. Please type something into the name box before submitting.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected. Please log back in.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	$sql = "
	INSERT IGNORE INTO `venue` 
	SET
		`venue_name` = '". $db->verifyVal(addslashes($_REQUEST['venue_name'])) ."'
		, `venue_address` = '". $db->verifyVal(addslashes($_REQUEST['venue_address'])) ."'
		, `venue_url` = '". $db->verifyVal(addslashes($_REQUEST['venue_url'])) ."'
		, `venue_instructions` = '". $db->verifyVal(addslashes($_REQUEST['venue_instructions'])) ."'
		, `venue_indoor` = '". $db->verifyVal($_REQUEST['venue_indoor']) ."'
		, `venue_status` = '". $db->verifyVal($_REQUEST['venue_status']) ."'
		, `venue_owner_player_id` = '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
		, `venue_create_date` = NOW()
		, `venue_last_update` = NOW()
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	$new_venue_id = $db->Last_Insert_ID();
	if($db->Affected_Rows() > 0 AND $new_venue_id > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your new venue has been saved.</font>';
	else $_SESSION['MESSAGE'][] = '<font color=red>Your new venue could not be saved.</font>';
	//die('New Venue_id: '.$new_venue_id);
	$sql = "
	INSERT IGNORE INTO `venue_owner` 
	SET
		`venue_id` = ". $db->verifyVal($new_venue_id) ."
		, `player_id` = " . $db->verifyVal($_SESSION['PLAYER_ID']) ."
		, `venue_owner_create_date` = NOW()
		, `venue_owner__last_update` = NOW()
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	$new_venue_id = $db->Last_Insert_ID();
	if($db->Affected_Rows() > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your new venue owner link has been saved.</font>';
	else $_SESSION['MESSAGE'][] = '<font color=red>Your new venue owner link could not be saved.</font>';
	//echo 'New Venue_id: '.$new_venue_id;
	$redirect = true;
}


if(isset($_POST['update_venue']) and $_POST['update_venue'] == 'Update Venue') { 
	if( strlen($_REQUEST['venue_name'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No venue name found. Please type something into the name box before submitting.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( strlen($_REQUEST['venue_address'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No venue address found. Please type something into the name box before submitting.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( strlen($_REQUEST['venue_url'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No venue URL found. Please type something into the name box before submitting.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( $_REQUEST['venue_indoor'] <> 1 ) $_REQUEST['venue_indoor'] = 0; else $_REQUEST['venue_indoor'] = 1;
	if( $_REQUEST['venue_status'] <> 1 ) $_REQUEST['venue_status'] = 0; else $_REQUEST['venue_status'] = 1;
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected. Please log back in.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	$sql = "
	UPDATE `venue` 
	SET
		`venue_name` = '". $db->verifyVal(addslashes($_REQUEST['venue_name'])) ."'
		, `venue_address` = '". $db->verifyVal(addslashes($_REQUEST['venue_address'])) ."'
		, `venue_url` = '". $db->verifyVal(addslashes($_REQUEST['venue_url'])) ."'
		, `venue_instructions` = '". $db->verifyVal(addslashes($_REQUEST['venue_instructions'])) ."'
		, `venue_indoor` = '". $db->verifyVal($_REQUEST['venue_indoor']) ."'
		, `venue_status` = '". $db->verifyVal($_REQUEST['venue_status']) ."'
	WHERE `venue_id` = '". $db->verifyVal($_REQUEST['venue_id']) ."'
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	$new_venue_id = $db->Last_Insert_ID();
	if($db->Affected_Rows() > 0 AND $new_venue_id > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your venue has been updated.</font>';
	else $_SESSION['MESSAGE'][] = '<font color=red>Your venue could not be updated.</font>';
	$redirect = true;
}




if($_REQUEST['add_player_to_venue'] == 'add_player_to_venue') { 
	$redirect_location = 'manage_venue.php?venue_id='.$_REQUEST['venue_id'];
	if( $_REQUEST['venue_id']  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No venue_id provided.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( strlen($_REQUEST['venue_player_id'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No player_id found.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected. Please log back in.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	$sql = "
	INSERT IGNORE INTO `venue_owner` 
	SET
		`venue_id` = '". $db->verifyVal($_REQUEST['venue_id']) ."'
		, `player_id` = '". $db->verifyVal($_REQUEST['venue_player_id']) ."'
		, `venue_owner_create_date` = NOW()
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your player has been added to the venue.</font>';
	else $_SESSION['MESSAGE'][] = '<font color=red>Your player could not be added to the venue.</font>';
	$redirect = true;
}




if($_REQUEST['remove_player_from_venue'] == 'remove_player_from_venue') { 
	$redirect_location = 'manage_venue.php?venue_id='.$_REQUEST['venue_id'];
	if( $_REQUEST['venue_id']  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No venue_id provided.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( strlen($_REQUEST['venue_player_id'])  < 1 ) {
		$_SESSION['MESSAGE'][] = '<font color=red>No player_id found.</font>';
		$_SESSION['ERROR'] = true;
		$redirect = true;
	}
	if( isset($_SESSION['PLAYER_ID']) == false ) {
		$_SESSION['MESSAGE'][] = '<font color=red>Invalid session detected. Please log back in.</font>';
		$redirect = true;
	}
	CheckRedirect($redirect, $redirect_location);
	$sql = "
	DELETE FROM `venue_owner` 
	WHERE
		`venue_owner_id` = '". $db->verifyVal($_REQUEST['venue_owner_id']) ."'
	LIMIT 1
	";
	//die(htmlentities($sql));
	$db->Execute($sql);
	if($db->Affected_Rows() > 0) $_SESSION['MESSAGE'][] = '<font color=green>Your player has been removed from the venue.</font>';
	else $_SESSION['MESSAGE'][] = '<font color=red>Your player could not be removed from the venue.</font>';
	$redirect = true;
}


if ($redirect == true or $redirect_last == true) {
		header('Location: '.$redirect_location);
	exit();
}


/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------------------------------------ */



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
    AND `records`.date >= DATE_SUB(DATE('".date("Y-m-d")."'), INTERVAL 1 MONTH)
    AND DAYOFWEEK(`records`.date) IN (2,3,4,5,6)
) AS `times_played_in_past_month`
FROM `players` AS p 
LEFT JOIN `records` AS r 
    ON p.player_id = r.player_id
    AND r.date = DATE('".date("Y-m-d")."')
WHERE p.active = 1
ORDER BY `times_played_in_past_month` DESC, p.first_name ASC
";
$db->Execute($sql);
//die("asdf=".$db->Affected_Rows());

$GLOBALS['in'] = array();
$GLOBALS['out'] = array();
$GLOBALS['question'] = array();
if($_SESSION['PLAYER_ID'] == 1) $GLOBALS['email_list'] = array();

while ($row = $db->FetchArray())	{
	$data = $row['first_name']." ".$row['last_name'].' ('.$row['times_played_in_past_month'].') ';
	if( $row['player_id'] == $_SESSION['PLAYER_ID'] ) {
		$data = '<font color=green><strong>'.$data.'</strong></font>'; 
		$_SESSION['INOROUT'] = $row['choice'];
	}
	if($row['choice'] == 1) {
		$GLOBALS['in'][] = $data;
		if($_SESSION['PLAYER_ID'] == 1 ) $GLOBALS['email_list'][] = htmlentities($row['first_name'] . " " . $row['last_name'] . " <" . $row['email'] . ">");
	} else if($row['choice'] == 2) $GLOBALS['question'][] = $data;
	else $GLOBALS['out'][] = $data;
}

$sql = "
SELECT `content`
FROM 
`message` 
WHERE `active` = 1
";
$db->Execute($sql);
while ($row = $db->FetchArray())	{
	$GLOBALS['SPECIAL_MESSAGE'][] = $row['content'];
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
WHERE 
	`visible` = 1
	AND `date` = DATE('".date("Y-m-d")."')
ORDER BY `comment_id` ASC
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
			'COMMENT'=>htmlentities(stripslashes($row['comment']))
			);
	}
}


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
FROM `venue` `v`
JOIN `venue_owner` `vo` ON v.venue_id = vo.venue_id
WHERE 1
AND vo.player_id = '" . $db->verifyVal($_SESSION['PLAYER_ID']) ."'
";
$db->Execute($sql);
$GLOBALS['VENUES'] = array();
if($db->TotalRows() > 0) {
	while ($row = $db->FetchArray())	{
		$GLOBALS['VENUES'][] = array(
			'VENUE_ID'=> $row['venue_id'],
			'VENUE_NAME'=>htmlentities(stripslashes($row['venue_name'])),
			'VENUE_ADDRESS'=>htmlentities(stripslashes($row['venue_address'])),
			'VENUE_URL'=>htmlentities(stripslashes($row['venue_url'])),
			'VENUE_INSTRUCTIONS'=>htmlentities(stripslashes($row['venue_instructions'])),
			'VENUE_INDOOR'=> $row['venue_indoor'],
			'VENUE_STATUS'=> $row['venue_status'],
			'VENUE_OWNER_PLAYER_ID'=> $row['venue_owner_player_id'],
			);
	}
}

GetTodaysAvailableVenues();
function GetTodaysAvailableVenues () {
	global $db;
	$sql = "
	SELECT 
		v.venue_id
		, v.venue_name
		, v.venue_address
		, v.venue_url
		, v.venue_instructions
		, v.venue_indoor
		, v.venue_status
		, va.player_id
		, va.venue_available_id
	FROM `venue_available` `va`
	JOIN `venue` `v` ON `v`.`venue_id` = `va`.`venue_id`
	WHERE 1
	#AND `v`.`venue_status` = 1
	AND `va`.`date` = DATE('".date("Y-m-d")."')
	GROUP BY v.venue_id
	ORDER BY `v`.`venue_priority` ASC
	";
	$db->Execute($sql);
	//die(htmlentities($sql));
	$GLOBALS['TODAYS_AVAILABLE_VENUES'] = array();
	if($db->TotalRows() > 0) {
		while ($row = $db->FetchArray())	{
			$GLOBALS['TODAYS_AVAILABLE_VENUES'][] = array(
				'VENUE_ID'=> $row['venue_id'],
				'VENUE_NAME'=>htmlentities(stripslashes($row['venue_name'])),
				'VENUE_ADDRESS'=>htmlentities(stripslashes($row['venue_address'])),
				'VENUE_URL'=>htmlentities(stripslashes($row['venue_url'])),
				'VENUE_INSTRUCTIONS'=>htmlentities(stripslashes($row['venue_instructions'])),
				'VENUE_INDOOR'=> $row['venue_indoor'],
				'VENUE_STATUS'=> $row['venue_status'],
				'PLAYER_ID'=> $row['player_id'],
				'VENUE_AVAILABLE_ID'=> $row['venue_available_id'],
				);
		}
	}
}










if(count($GLOBALS['in']) > 0 && count($GLOBALS['in']) < 11) $link_icon = count($GLOBALS['in']).".png";
else $link_icon = "Basketball_32x32.png";

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

//getWeather();

if(!$_SESSION['WEATHER'] || !$_SESSION['WEATHER_MICROTIME']) {
  getWeather();
} else {
  list($usec, $sec) = explode(" ", microtime());
  $microtime = ((float)$usec + (float)$sec);
  $time_diff = abs($microtime - $_SESSION['WEATHER_MICROTIME']);
  if($time_diff > 3600) {
    getWeather();
  }
}

function getWeather(){
  $weather = file_get_contents('http://adobe.epicswell.com/getWeather.php');
  if(!$weather) {
    $_SESSION['WEATHER'] = false;
  } else {
    $weather = json_decode($weather);
    $_SESSION['WEATHER'] = $weather->forecast_html;
    list($usec, $sec) = explode(" ", microtime());
    $microtime = ((float)$usec + (float)$sec);
    $_SESSION['WEATHER_MICROTIME'] = $microtime;
  }
}

require_once("index_content.html");

$_SESSION['MESSAGE'] = null;
