<?php
//error_reporting(0);

include_once ('config_inc.php');

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
//die("asdf=".$db->Affected_Rows());

$playersIn = array();
$playersOut = array();
$question = array();

while ($row = $db->FetchArray())	{
	$data = array('name' => $row['first_name'].' '.$row['last_name'], 'score' => $row['times_played_in_past_month']);
	if($row['choice'] == 1) $playersIn[] = $data;
	else if($row['choice'] == 2) $question[] = $data;
	else $playersOut[] = $data;
}
$sql = "
SELECT `content`
FROM 
`message` 
WHERE `active` = 1
";
$db->Execute($sql);
while ($row = $db->FetchArray())	{
	$message[] = $row['content'];
}



$sql = "
SELECT 
	c.comment
	, p.first_name
	, p.last_name
	, DATE_FORMAT(SUBDATE(c.date_time, INTERVAL 1 HOUR),'%h:%i:%s %p') as 'date_time'
FROM 
	`comments` c
JOIN `players` p ON p.player_id = c.player_id
WHERE 
	`visible` = 1
	AND `date` = DATE(NOW())
ORDER BY `comment_id` ASC
";
$db->Execute($sql);
$comments = array();
if($db->TotalRows() > 0) {
	while ($row = $db->FetchArray())	{
		$comments[] = array(
			'name'=> $row['first_name'].' '.$row['last_name'],
			'timestamp'=>$row['date_time'],
			'comment'=>stripslashes($row['comment'])
			);
	}
}
$json = array('playersIn' => $playersIn , 'playersOut' => $playersOut , 'question' => $question , 'messages' => $message , 'comments' => $comments);
//var_dump($json);
echo json_encode($json);
?>