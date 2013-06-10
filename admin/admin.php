<?php

include_once('../config_inc.php');
session_start();
include_once('admin.class.php');
include_once('../template.class.php');
include_once('email_functions.php');
$admin = new Admin();
$admin->Check_Access();

$GLOBALS['PRINTER'] = '';

//	http://localhost/inorout/admin/generate_daily_email.php?run_key=run_for_your_life&whats_your_favorite_color=987654321&debug=0&player_id=1
//	http://adobe.epicswell.com/admin/generate_daily_email.php?run_key=run_for_your_life&whats_your_favorite_color=987654321&debug=0

$DEBUG = true;
//$$_SESSION['ADMIN'] = false;


if(isset($_POST['to']) and isset($_POST['subject']) and isset($_POST['message']) and isset($_POST['send_email']) ) {
	include_once('email_functions.php');
	$from = "Travis Whitney <inorout@epicswell.com>";
	$sendPEARmail_tmp = sendPEARmail($from, stripslashes_array($_POST['to']), stripslashes_array($_POST['subject']), stripslashes_array($_POST['message']), '');
}



if($sendPEARmail_tmp['value']) $GLOBALS['PRINTER'].= "<br>".$sendPEARmail_tmp['value']."<br><hr><br>";

$GLOBALS['PRINTER'].= update_player_keys() ." Records Updated.<br><hr><br>";

$how_many_queued_mail_tmp = queued_mail_count();

$GLOBALS['PRINTER'].= "<a href='{$url}/admin/generate_daily_email.php?run_key=run_for_your_life&whats_your_favorite_color=987654321&debug=0' target='_blank'>Automate - Generate Daily Email Queue Records</a><br>
<br>
<form method=POST action='email.php'>Number of queued mail messages:&nbsp;<strong>".$how_many_queued_mail_tmp['value']."</strong>&nbsp;&nbsp;<input class='actionButton_classes' type='submit' name='process_email_submit' value='Process Queued Mail'><input type='hidden' name='request_id_process_email' value='987654321'></form><br>
<br>
<br>
<a href='{$url}/index.php?key4=1' target='_blank'>View Index as Admin</a><br>
<br>
<hr>
<br>";






if(isset($_REQUEST['SORT_BY'])) {
	if($_REQUEST['SORT_BY'] == 'player_id') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` ASC';
	else if($_REQUEST['SORT_BY'] == 'first_name') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` ASC';
	else if($_REQUEST['SORT_BY'] == 'last_name') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` ASC';
	else if($_REQUEST['SORT_BY'] == 'active') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
	else if($_REQUEST['SORT_BY'] == 'send_email') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
	else if($_REQUEST['SORT_BY'] == '1_Months') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
	else if($_REQUEST['SORT_BY'] == '3_Months') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
	else if($_REQUEST['SORT_BY'] == '6_Months') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
	else if($_REQUEST['SORT_BY'] == '1_Year') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
	else if($_REQUEST['SORT_BY'] == 'all_time') $SORT_BY = '`'.$_REQUEST['SORT_BY'].'` DESC';
} else $SORT_BY = ' `player_id` ASC ';

$db = new MySQL();
$sql = "
SELECT
	p.*
	, SUM(if(r.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH), 1, 0)) `1_Months`
	, SUM(if(r.date >= DATE_SUB(NOW(), INTERVAL 3 MONTH), 1, 0)) `3_Months`
	, SUM(if(r.date >= DATE_SUB(NOW(), INTERVAL 6 MONTH), 1, 0)) `6_Months`
	, SUM(if(r.date >= DATE_SUB(NOW(), INTERVAL 1 YEAR), 1, 0)) `1_Year`
	, SUM(if(r.date >= DATE_SUB(NOW(), INTERVAL 50 YEAR), 1, 0)) `all_time`
	, DATE(NOW()) AS 'today'
FROM `players` `p`
LEFT OUTER JOIN `records` `r` ON r.player_id = p.player_id
WHERE 1
#AND r.date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)
GROUP BY p.player_id
ORDER BY ".$SORT_BY."
";
//die($sql);
$db->Execute($sql);
if($db->TotalRows() > 0) {
	$player_list = "<table cellspacing=1 border=0 cellpadding=4>
<tr bgcolor='#999999'>
	<td><a href='?SORT_BY=player_id'>player_id</a></td>
	<td><a href='?SORT_BY=first_name'>first_name</a></td>
	<td><a href='?SORT_BY=last_name'>last_name</a></td>
	<td>email</td>
	<td><a href='?SORT_BY=active'>active</a></td>
	<td>Activate</td>
	<td><a href='?SORT_BY=send_email'>send_email</a></td>
	<td>Email Status</td>
	<td>Login</td>
	<td>In Link</td>
	<td>Out Link</td>
	<td>send_email_link</td>
	<td><a href='?SORT_BY=1_Months'>1_Months</a></td>
	<td><a href='?SORT_BY=1_Months'>3_Months</a></td>
	<td><a href='?SORT_BY=1_Months'>6_Months</a></td>
	<td><a href='?SORT_BY=1_Year'>1_Year</a></td>
	<td><a href='?SORT_BY=all_time'>All Time</a></td>
</tr>
";
	$table_end = "
</tbody>
</table>
";
	if($GLOBALS['IS_DEV']) $url = "http://127.0.0.1/inorout";
		else $url = "http://adobe.epicswell.com";
	
	$i=0;
	$f=0;
	$active_email_list='';
	while ($row = $db->FetchArray()) {
		if($row['active'] > 0 ) {
			if ($odd = $i%2) $bg = 'd7e8b9'; else $bg = 'C3E38B';
			if($i>0) $active_email_list.= ", ";
			$active_email_list.= $row['first_name']." ".$row['last_name']." <".$row['email'].">";			
			$i++;
		}
		if ($odd = $f%2) $bg = 'd7e8b9'; else $bg = 'C3E38B';
		$player_list.= "
<tr bgcolor='#$bg'>
	<td>{$row['player_id']}</td>
	<td>{$row['first_name']}</td>
	<td>{$row['last_name']}</td>
	<td>{$row['email']}</td>
	<td>{$row['active']}</td>
	";
	if($row['active'] == 1) $player_list.= "<td><a href='{$url}/index.php?key4=" . $row['player_id'] . "&key5=0' target='_blank'>De-activate</a></td>";
	else $player_list.= "<td><a href='{$url}/index.php?key4=" . $row['player_id'] . "&key5=1' target='_blank'>Activate</a></td>";
	$player_list.= "
	<td>{$row['send_email']}</td>
	";
	if($row['send_email'] == 1) $player_list.= "<td><a href='{$url}/index.php?key4=" . $row['player_id'] . "&key6=0' target='_blank'>Disable Email</a></td>";
	else $player_list.= "<td><a href='{$url}/index.php?key4=" . $row['player_id'] . "&key6=1' target='_blank'>Enable Email</a></td>";
	$player_list.= "
	<td><a href='{$url}/index.php?key1=" . $row['key'] . "' target='_blank'>Login</a></td>
	";
	$player_list.= "
	<td><a href='{$url}/index.php?key1=" . $row['key'] . "&key2=1' target='_blank'>Yes - I'm in!</a></td>
	<td><a href='{$url}/index.php?key1=" . $row['key'] . "&key2=0' target='_blank'>No - I'm out!</a></td>
	<td><a href='{$url}/admin/generate_daily_email.php?run_key=run_for_your_life&whats_your_favorite_color=987654321&debug=0&player_id={$row['player_id']}' target='_blank'>Generate_Email</a></td>
	<td>{$row['1_Months']}</td>
	<td>{$row['3_Months']}</td>
	<td>{$row['6_Months']}</td>
	<td>{$row['1_Year']}</td>
	<td>{$row['all_time']}</td>
</tr>
";
		$f++;
	}
	
}

$GLOBALS['PRINTER'].= $player_list.$table_end."<br><h4>Active Email List:</h4><br>".htmlentities($active_email_list);


$GLOBALS['PRINTER'].= "
<br><br><hr><br><br>
<form action='' method=POST>
<table cellpadding=5 cellspacing=0>
 <tr valign=top>
  <td>
	<strong>To:</strong>
   
  </td>
  <td>
	<textarea name='to' rows=3 cols=77>".htmlentities($active_email_list)."</textarea>
  </td>
 </tr>
  
 <tr valign=middle>
  <td>
	<strong>Subject:</strong>
  </td>
  <td>
	<INPUT name='subject' size=102>
  </td>
 </tr>
 
 <tr valign=top>
  <td>
	<strong>Message:</strong>
  </td>
  <td>
	<textarea name='message' rows=10 cols=77></textarea>
  </td>
 </tr>
 
 <tr valign=middle>
  <td>
	&nbsp;
  </td>
  <td>
	<input type='submit' name='send_email' value='Send Email'>
  </td>
 </tr> 
</table>
</form>
";



$template = new Template;
$template->load("admin_template.html");
$template->replace("title", SITE_NAME." Admin");
$active_nav = "admin";
$template->replace("head_head", "" );
$template->replace("content", "<? include('admin_content.html'); ?>" );
$template->publish();
die();



?>