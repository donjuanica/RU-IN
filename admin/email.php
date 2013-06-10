<?php

include_once('../config_inc.php');
session_start();
include_once('admin.class.php');
include_once('../template.class.php');
$admin = new Admin();
$admin->Check_Access();

$GLOBALS['PRINTER'] = '';


include_once('email_functions.php');

unset($_SESSION['class_id']);

include('calendar.php');

// CUSTOM EMAIL SECTION //
isset($_POST['email_title']) ? $GLOBALS["email_title"] = trim($_POST['email_title']) : $GLOBALS["email_title"] = false;
isset($_POST['email_to']) ? $GLOBALS["email_to"] = trim($_POST['email_to']) : $GLOBALS["email_to"] = false;
isset($_POST['class_reminder_submit']) ? $GLOBALS["class_reminder_submit"] = trim($_POST['class_reminder_submit']) : $GLOBALS["class_reminder_submit"] = false;
isset($_POST['custom_email_submit']) ? $GLOBALS["custom_email_submit"] = trim($_POST['custom_email_submit']) : $GLOBALS["custom_email_submit"] = false;
isset($_POST['request_id']) ? $GLOBALS["request_id"] = trim($_POST['request_id']) : $GLOBALS["request_id"] = false;
isset($_POST['email_content']) ? $GLOBALS["email_content"] = trim($_POST['email_content']) : $GLOBALS["email_content"] = false;
isset($_POST['email_footer']) ? $GLOBALS["email_footer"] = trim($_POST['email_footer']) : $GLOBALS["email_footer"] = "\nSincerely,\n\Travis Whitney";
// end CUSTOM EMAIL SECTION //

isset($_REQUEST['request_id']) ? $GLOBALS["request_id"] = $_REQUEST['request_id'] : $GLOBALS["request_id"] = false;
isset($_REQUEST['SEND_FINAL_EMAIL']) ? $GLOBALS["SEND_FINAL_EMAIL"] = $_REQUEST['SEND_FINAL_EMAIL'] : $GLOBALS["SEND_FINAL_EMAIL"] = false;
isset($_REQUEST['TEST_EMAIL']) ? $GLOBALS["TEST_EMAIL"] = $_REQUEST['TEST_EMAIL'] : $GLOBALS["TEST_EMAIL"] = false;
isset($_REQUEST['batch_id']) ? $GLOBALS["batch_id"] = $_REQUEST['batch_id'] : $GLOBALS["batch_id"] = false;

if($GLOBALS["request_id"] == false) {
	$GLOBALS["TEST_EMAIL"] = false; 	
	$GLOBALS["SEND_FINAL_EMAIL"] = false; 
	$GLOBALS["LIST_ONLY"] = false; 
} else {
	if($GLOBALS["TEST_EMAIL"] == true) {
		$GLOBALS["SEND_FINAL_EMAIL"] = true; 	
		$GLOBALS["LIST_ONLY"] = false; 
	} else if($GLOBALS["SEND_FINAL_EMAIL"] == true) {
		$GLOBALS["TEST_EMAIL"] = false; 	
		$GLOBALS["LIST_ONLY"] = false; 
	} else $GLOBALS["LIST_ONLY"] = true; 
}



$template = new Template;
$template->load("admin_template.html");
$active_nav = "email";
$template->replace("title", SITE_NAME." Admin | Email");
$template->replace("content", "<? include('email_content.html'); ?>" );
$template->replace("head_head", "");
/*
$template->replace("head_head", "
<!-- BEGIN ZAPATEC DHTML CALENDAR -->
<!-- UTF-8 is the recommended encoding for your pages -->
<!-- Loading Theme file(s) -->
    <link href='zpcal/themes/doba.css' rel='stylesheet' type='text/css' />
<!-- Loading Calendar JavaScript files -->
    <script type='text/javascript' src='zpcal/utils/zapatec.js'></script>
    <script type='text/javascript' src='zpcal/src/calendar.js'></script>
<!-- Loading language definition file -->
    <script type='text/javascript' src='zpcal/lang/calendar-en.js'></script>
<!-- END ZAPATEC DHTML CALENDAR -->
" );
*/
//print "<xmp>";
//print_r($course_row);
//print "</xmp>";
$template->publish();




// PROCESS QUEUED EMAIL //
isset($_POST['process_email_submit']) ? $GLOBALS["process_email_submit"] = trim($_POST['process_email_submit']) : $GLOBALS["process_email_submit"] = false;
isset($_POST['request_id_process_email']) ? $GLOBALS["request_id_process_email"] = trim($_POST['request_id_process_email']) : $GLOBALS["request_id_process_email"] = false;

if($GLOBALS["process_email_submit"] == 'Process Queued Mail' and $GLOBALS["request_id_process_email"] == '987654321') {
	send_queued_mail(null,null);
}
// END PROCESS QUEUED EMAIL //












// PROCESS QUEUED EMAIL BY BATCH_ID //
isset($_POST['request_id_process_records_from_queue_by_batch_ID']) ? $GLOBALS["request_id_process_records_from_queue_by_batch_ID"] = trim($_POST['request_id_process_records_from_queue_by_batch_ID']) : $GLOBALS["request_id_process_records_from_queue_by_batch_ID"] = false;
isset($_POST['process_email_by_batch_ID_submit']) ? $GLOBALS["process_email_by_batch_ID_submit"] = trim($_POST['process_email_by_batch_ID_submit']) : $GLOBALS["process_email_by_batch_ID_submit"] = false;
isset($_POST['batch_id_to_send']) ? $GLOBALS["batch_id_to_send"] = trim($_POST['batch_id_to_send']) : $GLOBALS["batch_id_to_send"] = false;

if($GLOBALS["process_email_by_batch_ID_submit"] == 'Send Queued Email Records with this Batch_ID' and $GLOBALS["request_id_process_records_from_queue_by_batch_ID"] == '987654321' and $GLOBALS["batch_id_to_send"] > 0) {
	send_queued_mail($GLOBALS["batch_id_to_send"], null);
}
// END PROCESS QUEUED EMAIL //


//	send_queued_mail($batch_id = null, $email_queue_id = null)



// PROCESS QUEUED EMAIL BY BATCH_ID //
isset($_POST['request_id_process_records_from_queue_by_email_queue_ID']) ? $GLOBALS["request_id_process_records_from_queue_by_email_queue_ID"] = trim($_POST['request_id_process_records_from_queue_by_email_queue_ID']) : $GLOBALS["request_id_process_records_from_queue_by_email_queue_ID"] = false;
isset($_POST['process_email_by_email_queue_ID_submit']) ? $GLOBALS["process_email_by_email_queue_ID_submit"] = trim($_POST['process_email_by_email_queue_ID_submit']) : $GLOBALS["process_email_by_email_queue_ID_submit"] = false;
isset($_POST['email_queue_id_to_send']) ? $GLOBALS["email_queue_id_to_send"] = trim($_POST['email_queue_id_to_send']) : $GLOBALS["email_queue_id_to_send"] = false;

if($GLOBALS["process_email_by_email_queue_ID_submit"] == 'Send Queued Email Records with this email_queue_ID' and $GLOBALS["request_id_process_records_from_queue_by_email_queue_ID"] == '987654321' and $GLOBALS["email_queue_id_to_send"] > 0) {
	send_queued_mail(null, $GLOBALS["email_queue_id_to_send"]);
}
// END PROCESS QUEUED EMAIL //



// REMOVE FROM EMAIL QUEUE BY BATCH_ID //
isset($_POST['remove_records_from_queue_by_batch_ID_submit']) ? $GLOBALS["remove_records_from_queue_by_batch_ID_submit"] = trim($_POST['remove_records_from_queue_by_batch_ID_submit']) : $GLOBALS["remove_records_from_queue_by_batch_ID_submit"] = false;
isset($_POST['batch_id_to_remove']) ? $GLOBALS["batch_id_to_remove"] = trim($_POST['batch_id_to_remove']) : $GLOBALS["batch_id_to_remove"] = false;
isset($_POST['request_id_remove_records_from_queue_by_batch_ID']) ? $GLOBALS["request_id_remove_records_from_queue_by_batch_ID"] = trim($_POST['request_id_remove_records_from_queue_by_batch_ID']) : $GLOBALS["request_id_remove_records_from_queue_by_batch_ID"] = false;

if($GLOBALS["remove_records_from_queue_by_batch_ID_submit"] == 'Remove Records From Queue with this Batch_ID' and $GLOBALS["request_id_remove_records_from_queue_by_batch_ID"] == '987654321' and $GLOBALS["batch_id_to_remove"] > 0 ) {
	$tmp =  remove_records_from_queue_by_batch_id($GLOBALS["batch_id_to_remove"]);
	print $tmp['value'];
}
// REMOVE FROM EMAIL QUEUE BY BATCH_ID //









// REMOVE FROM EMAIL QUEUE BY EMAIL_QUEUE_ID //
isset($_POST['remove_records_from_queue_by_email_log_ID_submit']) ? $GLOBALS["remove_records_from_queue_by_email_log_ID_submit"] = trim($_POST['remove_records_from_queue_by_email_log_ID_submit']) : $GLOBALS["remove_records_from_queue_by_email_log_ID_submit"] = false;
isset($_POST['email_log_id_to_remove']) ? $GLOBALS["email_log_id_to_remove"] = trim($_POST['email_log_id_to_remove']) : $GLOBALS["email_log_id_to_remove"] = false;
isset($_POST['request_id_remove_records_from_queue_by_email_log_ID']) ? $GLOBALS["request_id_remove_records_from_queue_by_email_log_ID"] = trim($_POST['request_id_remove_records_from_queue_by_email_log_ID']) : $GLOBALS["request_id_remove_records_from_queue_by_email_log_ID"] = false;

if($GLOBALS["remove_records_from_queue_by_email_log_ID_submit"] == 'Remove Records From Queue with this email_log_ID' and $GLOBALS["request_id_remove_records_from_queue_by_email_log_ID"] == '987654321' and $GLOBALS["email_log_id_to_remove"] > 0 ) {
	$tmp =  remove_records_from_queue_by_email_queue_id($GLOBALS["email_log_id_to_remove"]);
	print $tmp['value'];
}
// REMOVE FROM EMAIL QUEUE BY EMAIL_QUEUE_ID //





// LIST ALL QUEUED EMAIL RECORDS IN A TABLE //
isset($_POST['list_all_queued_records_submit']) ? $GLOBALS["list_all_queued_records_submit"] = trim($_POST['list_all_queued_records_submit']) : $GLOBALS["list_all_queued_records_submit"] = false;
isset($_POST['request_id_list_all_queued_records']) ? $GLOBALS["request_id_list_all_queued_records"] = trim($_POST['request_id_list_all_queued_records']) : $GLOBALS["request_id_list_all_queued_records"] = false;

if($GLOBALS["list_all_queued_records_submit"] == 'List Queued Mail Records' and $GLOBALS["request_id_list_all_queued_records"] == '987654321') {
	print list_all_queued_mail_records();
}		
// LIST ALL QUEUED EMAIL RECORDS IN A TABLE //



print "<br><br>";
?>