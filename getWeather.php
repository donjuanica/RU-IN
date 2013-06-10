<?php

session_start();


//getWeather(); // http://adobe.epicswell.com/getWeather.php?debug=1

if(!$_SESSION['WEATHER_CACHE'] || !$_SESSION['WEATHER_MICROTIME']) getWeather();


list($usec, $sec) = explode(" ", microtime());
$microtime = ((float)$usec + (float)$sec);
$time_diff = abs($microtime - $_SESSION['WEATHER_MICROTIME']);
if($time_diff > 3600) getWeather(); else die($_SESSION['WEATHER_CACHE']);


function getWeather(){
//`rm -rf /tmp/wsdl-*`;
ini_set("soap.wsdl_cache_enabled", 0); // disabling WSDL cache
ini_set("soap.wsdl_cache_ttl", 1); // disabling WSDL cache
ini_set("soap.wsdl_cache_limit", 0); // disabling WSDL cache
error_reporting (E_ALL ^ E_NOTICE);
set_time_limit(3000);
//ini_set('memory_limit', '512M');

list($usec, $sec) = explode(" ", microtime());
$microtime = ((float)$usec + (float)$sec);
$_SESSION['WEATHER_MICROTIME'] = $microtime;

isset($_REQUEST['html']) ? $html = trim($_REQUEST['html']) : $html = false;
isset($_REQUEST['debug']) ? $debug = trim($_REQUEST['debug']) : $debug = false;

if($debug) print '<textarea style=" width: 100%; height: 80%; border: 1px solid gray; border:outset 1px #ccc; font-weight:normal; font-size: 11px; /*margin: -4px -8px -8px -4px;*/ ">';


// WSDL Information
// http://www.weather.gov/forecasts/xml/SOAP_server/ndfdXMLserver.php
// http://www.weather.gov/forecasts/xml/rest.php#XML_contents

$URL = "http://graphical.weather.gov/xml/DWMLgen/wsdl/ndfdXML.wsdl";
$SOAPclient = new SoapClient($URL, array("trace" => 1));

// Orem, UT 84097 = latitude 40.33, longitude -111.69
// http://forecast.weather.gov/MapClick.php?textField1=40.33&textField2=-111.69
$latitude = 40.42;
$longitude = -111.88;

$product = 'time-series';
date_default_timezone_set('America/Denver');
$startTime = date("Y-m-d",strtotime('+0 day')).'T'.date("H:00");
$endTime = date("Y-m-d",strtotime('+1 day')).'T15:00';
$unit = 'e'; // e = english, m = metric
$parameters = array(
    'maxt' => TRUE,
    'mint' => TRUE,
    'temp' => TRUE,
    'dew' => FALSE,
    'pop12' => FALSE,
    'qpf' => FALSE,
    'sky' => FALSE,
    'snow' => FALSE,
    'wspd' => TRUE,
    'wdir' => FALSE,
    'wx' => TRUE,
    'waveh' => FALSE,
    'icons' => TRUE,
    'rh' => FALSE,
    'appt' => FALSE,
    'incw34' => FALSE,
    'incw50' => FALSE,
    'incw64' => FALSE,
    'cumw34' => FALSE,
    'cumw50' => FALSE,
    'cumw64' => FALSE,
    'conhazo' => FALSE,
    'ptornado' => FALSE,
    'phail' => FALSE,
    'ptstmwinds' => FALSE,
    'pxtornado' => FALSE,
    'pxhail' => FALSE,
    'pxtstmwinds' => FALSE,
    'ptotsvrtstm' => FALSE,
    'pxtotsvrtstm' => FALSE,
    'tmpabv14d' => FALSE,
    'tmpblw14d' => FALSE,
    'tmpabv30d' => FALSE,
    'tmpblw30d' => FALSE,
    'tmpabv90d' => FALSE,
    'tmpblw90d' => FALSE,
    'prcpabv14d' => FALSE,
    'prcpblw14d' => FALSE,
    'prcpabv30d' => FALSE,
    'prcpblw30d' => FALSE,
    'prcpabv90d' => FALSE,
    'prcpblw90d' => FALSE,
    'precipa_r' => FALSE,
    'sky_r' => FALSE,
    'td_r' => FALSE,
    'temp_r' => FALSE,
    'wdir_r' => FALSE,
    'wspd_r' => FALSE,
    'wwa' => FALSE,
    'wgust' => FALSE,
    'critfireo' => FALSE,
    'dryfireo' => FALSE,
    'tstmprb' => FALSE,
    'tstmcat' => FALSE,
    'iceaccum' => FALSE,
    'maxrh' => FALSE,
    'minrh' => FALSE,
    );
try { 
    $Response = $SOAPclient->NDFDgen($latitude,$longitude,$product,$startTime,$endTime,$unit,$parameters);
} catch ( SOAPFault $e ) {
    $Response = "Error ".$e->faultcode.": ".$e->faultstring;
    if($debug) die($Response); else die('<!-- '.$Response.' -->');
}
if($debug) print "URL = $URL\n\n";
if($debug) print "Method = NDFDgen\n\n";
//if($debug) print "Request:\n=====================================================\n\n";
//if($debug) print_r($Request);
if($debug) print "\n\nResponse:\n=================================================\n\n";
if($debug) print_r($Response);

if($debug) echo "\n=================================================\n\n";
try {
    $sxml = new SimpleXMLElement($Response);
} catch (Exception $e) {
    if($debug) die("XML Could not be converted to a simple object."); else die('');
}
if($debug) print_r($sxml->data);





if($debug) echo "\n=================================================\n\n";
$hourdates = array();
$days = array();
foreach($sxml->data->{'time-layout'}[2]->{'start-valid-time'} as $val){
    if($debug) echo 'val='.date("Y-m-d H",strtotime($val))."\n";
    $days[] = date("Y-m-d",strtotime($val));
    $hourdates[] = date("Y-m-d H:00:00",strtotime($val));
}
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($hourdates);



if($debug) echo "\n=================================================\n\n";
$hourtemps = array();
foreach($sxml->data->parameters->temperature[2]->value as $val){
    if($debug) echo 'val='."{$val}\n";
    $hourtemps[] = (string) $val;
}
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($hourtemps);


if($debug) echo "\n=================================================\n\n";
$hightemps = array();
foreach($sxml->data->parameters->temperature[0]->value as $val){
    if($debug) echo 'val='."{$val}\n";
    $hightemps[] = (string) $val;
}
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($hightemps);



if($debug) echo "\n=================================================\n\n";
$windspeed = array();
foreach($sxml->data->parameters->{'wind-speed'}->value as $val){
    if($debug) echo 'val='."{$val}\n";
    $windspeed[] = (string) $val;
}
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($windspeed);



if($debug) echo "\n=================================================\n\n";
$icon = array();
foreach($sxml->data->parameters->{'conditions-icon'}->{'icon-link'} as $val){
    if($debug) echo 'val='."{$val}\n";
    $icon[] = (string) $val;
}
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($icon);



if($debug) echo "\n=================================================\n\n";
$condition = array();
foreach($sxml->data->parameters->weather->{'weather-conditions'} as $val){
    if(count($val->value) > 0 ) {
        foreach($val->value as $tmp) {
            $val = $tmp['coverage'].' '.
                $tmp['intensity'].' '.
                $tmp['weather-type'];
        }
    } else $val = 'clear';
    if($debug) echo 'val='."{$val}\n";
    $condition[] = (string) $val;
}
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($condition);



if($debug) echo "\n=================================================\n\n";

$moreweatherinfolink = (string) $sxml->data->{'moreWeatherInformation'};
if($debug) echo 'val='."{$moreweatherinfolink}\n";
if($debug) echo "\n=================================================\n\n";
if($debug) print_r($moreweatherinfolink);



if($debug) echo '</textarea>';

$hourtempcount = count($hourdates);
$i = 0;
$forecast=array();
$current_datetime = date("Y-m-d H:i:s");
while($i < $hourtempcount) {
    $forecast[] = array(
        'datetime' => $hourdates[$i],
        'date' => date("Y-m-d",strtotime($hourdates[$i])),
        'day_name_short' => date("D",strtotime($hourdates[$i])),
        'month_day_short' => date("M j",strtotime($hourdates[$i])),
        'time' => date("g a",strtotime($hourdates[$i])),
        'temperature' => $hourtemps[$i].'&deg; F',
        'wind-speed' => $windspeed[$i].' mph',
        'weather' => $condition[$i],
        'icon' => $icon[$i]
    );
    $i++;
}
$html_content = '
<table cellpadding=0 cellspacing=0 border=0>'."\n".'
<tr>'."\n".'
';
$last_day = '';
foreach($forecast as $tmp) {
    $html_content.= '<td width="75px" valign="TOP">'."\n";
    $html_content.= '<div class="weatherBottomLine"><div>';
    if($last_day !== $tmp['date']) {
        $html_content.= $tmp['day_name_short'].'<br />'.$tmp['month_day_short'];
    } else $html_content.= '&nbsp;<br />&nbsp;';
    $last_day = $tmp['date'];
    $html_content.= '</div></div>'."\n";
    $html_content.= '<div class="weatherBottomLine"><div>';
    $html_content.= $tmp['time'];
    $html_content.= '<br />'.$tmp['temperature'];
    $html_content.= '</div></div>'."\n";
    $html_content.= '<div class="weatherBottomLine"><div>';
    $html_content.= '<img src="'.$tmp['icon'].'" border="0">';
    $html_content.= '</div></div>'."\n";
    $html_content.= '<div class="weatherSmallText BottomLine"><div>';
    $html_content.= $tmp['weather'];
    $html_content.= '<br />'.'wind '.$tmp['wind-speed'];
    $html_content.= '</div></div>'."\n";
    $html_content.= '</td>'."\n";
}
$html_content.= '
</tr>'."\n".'
<tr>
<td colspan="'.$hourtempcount.'" width="100%">
	<table cellpadding=0 cellspacing=0 border=0 width="100%">'."\n".'
	<tr><td width="50%" align="left">
	<div class="SmallText left"><div><a href="'.$moreweatherinfolink.'" target="_blank">View Today\'s Weather Forecast</a></div></div>
	</td>
	<td width="50%" align="right">
	<div class="SmallText right"><div>Last Updated at '.$current_datetime.'</div></div>
	</td>
	</tr>
	</table>
</td>
</tr>
</table>'."\n".'
';

$_SESSION['WEATHER_CACHE'] = json_encode(array(
  'forecast_array' => $forecast,
  'forecast_html' => $html_content,
  'microtime' => $microtime,
  'moreweatherinfolink' => $moreweatherinfolink,
  'current_datetime' => $current_datetime
  ));

if($html != true) {
    die($_SESSION['WEATHER_CACHE']);
} else die($html_content);

}
?>