<?php

  // This is the Mail.php file from PEAR.
/*
  if( file_exists('PEAR/Mail.php')) include_once('PEAR/Mail.php'); 
	else if(file_exists('Mail.php')) include_once('Mail.php');
	else die("Mail.php file not found");
  if( file_exists('PEAR/Mail/mime.php') ) include_once('PEAR/Mail/mime.php'); 
	else if(file_exists('Mail/mime.php')) include_once('Mail/mime.php');
	else die("mime.php file not found");
*/	
  require_once("Mail.php");
  require_once('Mail/mime.php'); 
  
  //define("FROM_EMAIL", "travis@epicswell.com");
  define("FROM_EMAIL", "travis.epicswell@gmail.com");
  
// All email sent to travis@epicswell.com  will now be forwarded to twhitney@doba.com

function sendPEARmail($from = null, $to = null, $subject = null, $html_message = null, $txt_message = null) {
	
	if(is_null($from) == true) return "The From: parameter is missing. Your email has not been sent.";
	if(is_null($to) == true) return "The To: parameter is missing. Your email has not been sent.";
	if(is_null($subject) == true) return "The Subject: parameter is missing. Your email has not been sent.";
	if(is_null($html_message) == true) return "The html_message: parameter is missing. Your email has not been sent.";
	if(is_null($txt_message) == true) return "The txt_message: parameter is missing. Your email has not been sent.";
	
//die("from=".htmlentities($from)."<br>to=".htmlentities($to)."<br>subject=$subject<br>message=$message");

	$headers = array (
		'From' => $from,
		'To' => $to,
		'Bcc' => '' /*$from*/,
		'Subject' => $subject);
		
	//$smtp = Mail::factory('smtp', array (
	//	'host' => "localhost",
	//	'port' => "25",
	//	/*'port' => "465",*/
	//	'auth' => true,
	//	/*'username' => "",*/
	//	'username' => "",
	//	'password' => ""
	//	/*,'persist' => true*/
	//	));
		
	$smtp = Mail::factory('smtp', array (
		'host' => "smtp.gmail.com",
		'port' => "587",
		/*'port' => "465",*/
		'auth' => true,
		'username' => "",
		'password' => ""
		/*,'persist' => true*/
		));
		
	$message = new Mail_mime("\n");
	$message->setTXTBody($txt_message); 
	$message->setHTMLBody("<!DOCTYPE html PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'><html><head><meta http-equiv='content-type' content='text/html; charset=iso-8859-1'></head><body>".$html_message."</body></html>"); 
	$body = $message->get();
	$headers = $message->headers($headers);
	
	$mail = $smtp->send($to, $headers, $body);
	
	if (PEAR::isError($mail)) {
		return (array("status" => false, "value" =>  "Message NOT SENT. ".htmlentities($to)." | ". $mail->getMessage()." <br>"));
	} else {
		return (array("status" => true, "value" => "Message successfully sent! ".htmlentities($to)." <br>"));
	}
}





function list_all_queued_mail_records(){
	$db = new MySQL();
	$sql = "SELECT * FROM email_queue WHERE status = 0";
	$Result = $db->Execute($sql);		
	if($db->TotalRows() < 1 ) return "<br><br>Error: No records returned.<br><br>Number of rows returned = ".$db->TotalRows();
	
	$queued_email_list = "<table cellspacing=0 cellpadding=5 border=0 class='lesson_table'>\n
<thead>
	<th>email_queue_id</th>
	<th>batch_id</th>
	<th>status</th>
	<th>date_time</th>
	<th>from</th>
	<th>player_id</th>
	<th>to</th>
	<th>subject</th>
</thead>
<tbody>
";	
	while ($row = mysqli_fetch_assoc($Result)) {
		$queued_email_list.= "\n<tr>\n
	<td>{$row['email_queue_id']}</td>\n
	<td>{$row['batch_id']}</td>\n
	<td>{$row['status']}</td>\n
	<td>{$row['date_time']}</td>\n
	<td>".htmlentities($row['from'])."</td>\n
	<td>{$row['player_id']}</td>\n
	<td>".htmlentities($row['to'])."</td>\n
	<td>{$row['subject']}</td>
</tr>\n";		
	}
	
	$queued_email_list.= "</tbody>\n</table>";		
	return $queued_email_list;
}











function list_all_customer_emails(){
	$db = new MySQL();
	$sql = "
SELECT 
	player_id
	, CONCAT(first_name,' ',last_name,' <',email,'>') AS 'long_email'
	, email as 'short_email'
FROM
	players
WHERE 1
	AND CHAR_LENGTH(email)  > 1
	AND email LIKE '%@%'
GROUP BY
	email
";
	$Result = $db->Execute($sql);		
	if($db->TotalRows() < 1 ) return "<br><br>Error: No records returned.<br><br>Number of rows returned = ".$db->TotalRows();
	
	$full_player_email_list = '';
	$zz=0;
	
	while ($row = mysqli_fetch_assoc($Result)) {
		if($z >= 1) $full_player_email_list.= ", ";
		$full_player_email_list.= $row['long_email'];		
		$z++;
	}
	return $full_player_email_list;
}










	
function queued_mail_count() {
	$db = new MySQL();
	$sql = "SELECT COUNT(*) AS 'count' FROM email_queue WHERE status = 0";
	$Result = $db->Execute($sql);		
	if($db->TotalRows() < 1 ) return (array("status" => false, "value" => "<br><br>Error: ".$db->Error_Message() ));
	$row = mysqli_fetch_assoc($Result);
	return (array("status" => true, "value" => $row["count"]));		
}












function next_email_batch_id() {
	$db = new MySQL();
	$sql = "SELECT IF(MAX(batch_id) IS NULL, 0, MAX(batch_id) + 1) AS 'new_batch_id' FROM email_queue WHERE 1";
	$Result = $db->Execute($sql);		
	if($db->TotalRows() < 1 ) return (array("status" => false, "value" => "<br><br>Error: Can't determine new batch_id.<br><br>Number of rows returned = ".$db->TotalRows() ));
	$row = mysqli_fetch_assoc($Result);
	return (array("status" => true, "value" => $row["new_batch_id"]));		
}









function remove_records_from_queue_by_batch_id($batch_id = null) {
	if(is_null($batch_id) == true) return (array("status" => false, "value" => "The batch_id parameter is missing."));
	
	$db = new MySQL();
	$sql = "UPDATE `email_queue` SET `status` = '-1' WHERE `batch_id` = '".$batch_id."'";
	//die(var_dump($sql));
	$db->Execute($sql);		
	if($db->Affected_Rows() < 1 ) return (array("status" => false, "value" => "<br><br>Error: Can't update records. Error: ".$db->Error_Message() ));
	else return (array("status" => true, "value" => "<br>". $db->Affected_Rows() ." records have been removed from the queue." ));		
}




function remove_records_from_queue_by_email_queue_id($email_queue_id = null) {
	if(is_null($email_queue_id) == true) return (array("status" => false, "value" => "The email_queue_id parameter is missing."));
	
	$db = new MySQL();
	$sql = "UPDATE `email_queue` SET `status` = '-1' WHERE `email_queue_id` = '".$email_queue_id."'";
	//die(var_dump($sql));
	$db->Execute($sql);		
	if($db->Affected_Rows() < 1 ) return (array("status" => false, "value" => "<br><br>Error: Can't update records. Error: ".$db->Error_Message() ));
	else return (array("status" => true, "value" => "<br>". $db->Affected_Rows() ." records have been removed from the queue." ));		
}









function cacheMAIL($batch_id = null, $player_id = null, $to = null, $subject = null, $message = null) {
	
	if(is_null($batch_id) == true) return (array("status" => false, "value" => "The batch_id parameter is missing. Your email has not been queued."));
	if(is_null($player_id) == true) return (array("status" => false, "value" => "The player_id parameter is missing. Your email has not been queued."));
	if(is_null($to) == true) return (array("status" => false, "value" => "The To: parameter is missing. Your email has not been queued."));
	if(is_null($subject) == true) return (array("status" => false, "value" => "The Subject: parameter is missing. Your email has not been queued."));
	if(is_null($message) == true) return (array("status" => false, "value" => "The Message: parameter is missing. Your email has not been queued."));
	
	$from = "Travis Whitney <".FROM_EMAIL.">";
	$from_short = FROM_EMAIL;
		
	$db = new MySQL();
	$sql = "
INSERT INTO `email_queue` SET 
`batch_id` = '".$batch_id."'
, `status` = '0'
, `date_time` = NOW()
, `from` = '".$from."'
, `player_id` = '".$player_id."'
, `to` = '".$to."'
, `subject` = '".addslashes($subject)."'
, `content` = '".addslashes($message)."'
";
	//die(htmlentities($sql));
	$db->Execute($sql);		

	if($db->Affected_Rows() > 0) 
		return(array("status" => true, "value" => "<br>Message queued successfully! ".htmlentities($to)."\t batch_id = ".$GLOBALS["batch_id"])); 	
	else 
		return(array("status" => false, "value" => "<br>Message NOT queued! ".htmlentities($to)));
}


















function send_queued_mail($batch_id = null, $email_queue_id = null) {

	$from = "Travis Whitney <".FROM_EMAIL.">";
	$from_short = FROM_EMAIL;
	
	$db = new MySQL();
	$sql = "
SELECT 
	`email_queue_id`
	, `batch_id`
	, `status`
	, `date_time`
	, `from`
	, `player_id`
	, `to`
	, `subject`
	, `content` 
FROM 
	`email_queue` 
WHERE 1 ";
	if($batch_id > 0) $sql.= " AND `batch_id` = '".$batch_id."' ";
	if($email_queue_id > 0) $sql.= " AND `email_queue_id` = '".$email_queue_id."' ";
	if(!$batch_id and !$email_queue_id) $sql.= " AND `status` = 0 ";
	
	//die(htmlentities($sql));
	
	$Result = $db->Execute($sql);		
	if($db->TotalRows() < 1 ) return (array("status" => false, "value" => "<br><br>Error: No records returned.<br><br>Number of rows returned = ".$db->TotalRows() ));
	while ($row = mysqli_fetch_assoc($Result)) {
		$sendPEARmail_tmp = sendPEARmail($from, $row['to'], stripslashes($row['subject']), stripslashes($row['content']), '');
		print "<br>".$sendPEARmail_tmp['value'];
		if($sendPEARmail_tmp['status'] == true)
			update_queued_mail_status($row['email_queue_id'] , 1, $sendPEARmail_tmp['value']);
		else 
			update_queued_mail_status($row['email_queue_id'] , '-1', $sendPEARmail_tmp['value']);
		
		usleep(5000); // in microseconds = 2000000 = 2 seconds
	}		
}













function update_queued_mail_status($email_queue_id = null, $status = null, $debug = null) {
	if(is_null($email_queue_id) == true) return "The 'email_queue_id' parameter is missing. Your queued message cannot be updated.";
	if(is_null($status) == true) return "The 'status' parameter is missing. Your queued message cannot be updated.";
	$db = new MySQL();
	$sql = "UPDATE email_queue SET status = '".$status."'";
	if($status > 0) $sql.= " , date_time_sent = NOW() ";
	if(is_null($debug) == false) $sql.= " , `debug` = '". addslashes($debug) . "' ";
	$sql.= " WHERE email_queue_id = ".$email_queue_id;
	
	$db->Execute($sql);
	
	if($db->Affected_Rows() > 0 ) 
		return (array("status" => true, "value" => $row["new_batch_id"]));
	else 
		return (array("status" => false, "value" => "<br><br>Error: ".$db->Error_Message() ));
}










?>