<?php
include_once('../config_inc.php');
session_start();
include_once('admin.class.php');
$admin = new Admin();
$admin->Check_Access();

include_once('../template.class.php');

include_once ('../database.php');
$db = new MySQL();

if(isset($_POST['SET_DISPLAY_DATE']) && $_POST['SET_DISPLAY_DATE'] == 'UPDATE') {
//print "<br>sign-in stuff<br>";		
	$sql = " UPDATE IGNORE `lesson_display` SET `display_date` = DATE('".$_POST['display_date']."') WHERE display_date_id=1";
	//die("-".$sql."-");
	$db->Execute($sql);
	header('Location: index.php');
	exit();

}

if(isset($_POST['SET']) && $_POST['SET'] == 'SET') {
//print "<br>sign-in stuff<br>";		
	$_SESSION['SERVER'] = $_POST['SERVER'];
	header('Location: index.php');
	exit();

}




$sql = " SELECT COUNT(*) 'count'  FROM `session` WHERE `start_time` <= '7:00:00' ";
$Result = $db->Execute($sql);

$row = mysqli_fetch_assoc($Result) 	;
$GLOBALS['fix_start_time_count'] = $row['count'];

	
if($GLOBALS['fix_start_time_count'] > 0 ) {
	$sql = " UPDATE `session` SET `start_time` = ADDTIME('12:00:00',`start_time`) WHERE `start_time` <= '7:00:00' ";
	$db->Execute($sql);
	$_SESSION['start_time_fix_message'] = $db->Affected_Rows() ." records were updated.";

}

$sql = "SELECT `display_date` FROM `lesson_display` WHERE 1 LIMIT 1";
$db->Execute($sql);
//die("-".$db->Affected_Rows()."-");
if($db->TotalRows() == 1) {
	$row = $db->FetchArray();
	$GLOBALS['display_date'] = $row['display_date'] ;
} else $GLOBALS['display_date'] = "error" ;


$template = new Template;
$template->load("admin_template.html");
$template->replace("title", ADMIN_SITE_NAME." | Home");
$active_nav = "home";
$template->replace("content", "<?php include('admin_index_content.html'); ?>" );
$template->replace("mootools_script", "" );
$template->replace("head_head", "" );

$template->replace("footer_message", FOOTER_MESSAGE );

$template->publish();

?>