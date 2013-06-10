<?php
// http://adobe.epicswell.com/getPlayerInfo.php?player_id=16&token=ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298

isset($_REQUEST['token']) ? $token = trim($_REQUEST['token']) : $token = false;
if($token != 'ASDF9ASHAS084382HAS8D843H2NANASA89GH34NB2828GHEBAOWS9D8GYH3B298' || !$token) export_json_feed(array('Error' => 'Decrypt this error message to find where you went wrong.'));

isset($_REQUEST['player_id']) ? $player_id = trim($_REQUEST['player_id']) : $player_id = false;
if($player_id < 1) export_json_feed(array('Error' => 'player_id '. $player_id .' is not valid.'));

include_once ('config_inc.php');
$db = new MySQL();
$sql = "
SELECT p.*
, CASE  
	WHEN r.choice IS NULL THEN 'Questionable'  
	WHEN r.choice = 1 THEN 'In'  
	ELSE 'Out'  END AS 'choice'
, (    
	SELECT COUNT(*)    
	FROM `records`    
	WHERE `records`.`player_id` = p.player_id
	AND `records`.`player_id` = '". $db->verifyVal($player_id) ."'
	AND `records`.choice = 1    
	AND `records`.date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)    
	AND DAYOFWEEK(`records`.date) IN (2,3,4,5,6)
	) AS `times_played_in_past_month`
FROM `players` AS `p` 
LEFT JOIN `records` AS `r` ON p.player_id = r.player_id AND r.date = DATE(NOW())
WHERE 1
AND p.active = 1
AND p.player_id = '". $db->verifyVal($player_id) ."'
LIMIT 1
";
$db->Execute($sql);
//die($sql."<br>asdf=".$db->Affected_Rows());
if($db->Affected_Rows() < 1) export_json_feed(array('Error' => 'player_id '. $player_id .' is not valid.'));
$player=array();
while ($row = $db->FetchArray())	{	
	$player[] = array(    
		'player_id' => $row['player_id']    
		, 'first_name' => $row['first_name']    
		, 'last_name' => $row['last_name']    
		, 'score' => $row['times_played_in_past_month']    
		, 'choice' => $row['choice']    
		, 'email' => $row['email']    
		, 'key' => $row['key']       
		, 'active' => $row['active']    
	);
}

//var_dump($player);
export_json_feed ($player);
function export_json_feed ($json) {
	echo json_encode($json);
	die();
}
die();
?>