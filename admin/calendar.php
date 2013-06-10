<?php
/* include('calendar.php'); */

if(isset($_REQUEST['clear_date_range'])) {
	unset($_SESSION['start']);
	unset($_SESSION['end']);
}

if(isset($_REQUEST['start'])) $GLOBALS["start"] = $_REQUEST['start'] ; 
else if(isset($_SESSION['start'])) $GLOBALS["start"] = $_SESSION['start'] ;
else $GLOBALS["start"] = false;

if(isset($_REQUEST['end'])) $GLOBALS["end"] = $_REQUEST['end'] ; 
else if(isset($_SESSION['end'])) $GLOBALS["end"] = $_SESSION['end'] ;
else $GLOBALS["end"] = false;

if(strlen($GLOBALS["start"]) < 7 ) unset($GLOBALS["start"]);
if(strlen($GLOBALS["end"]) < 7 ) unset($GLOBALS["end"]);

$GLOBALS["beginmonth"] = date("Y-m-01");
$GLOBALS["thismonthend"] = date('Y-m-d',strtotime('-1 second',strtotime((date('m')+1).'/01/'.date('Y').' 00:00:00')));
$GLOBALS["todayprint"] = date("Y-m-d G:i A");
$GLOBALS["todayshort"] = date("Y-m-d");

if (empty($GLOBALS["start"])) $GLOBALS["start"] = "$beginmonth" ;
if (empty($GLOBALS["end"])) $GLOBALS["end"] = "$thismonthend" ;

if ((!empty($GLOBALS["start"])) && (!empty($GLOBALS["end"])) && ($GLOBALS["start"] > $GLOBALS["end"] )) {
	$temp_date_thing = $GLOBALS["start"];
	$GLOBALS["start"] = $GLOBALS["end"];
	$GLOBALS["end"] = $temp_date_thing;
	unset($temp_date_thing);
}

$GLOBALS["start_slash"] = $GLOBALS["start"];
$GLOBALS["end_slash"] = $GLOBALS["end"];

if (!empty($GLOBALS["start"])) $GLOBALS["start"] = str_replace("/", "-", $GLOBALS["start"]);
if (!empty($GLOBALS["end"])) $GLOBALS["end"] = str_replace("/", "-", $GLOBALS["end"]);

if(isset($_REQUEST['set_date_range'])) {
	$_SESSION['start'] = $GLOBALS["start"];
	$_SESSION['end'] = $GLOBALS["end"];
}

?>