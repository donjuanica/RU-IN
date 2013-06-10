<?php

/**
* Demo of TableEditor class. Uses the following table:
*
* CREATE TABLE `TableEditorDemo` (
*   `te_id` int(10) unsigned NOT NULL auto_increment,
*   `te_name` varchar(32) NOT NULL default '',
*   `te_password` varchar(32) NOT NULL default '',
*   `te_email` varchar(32) NOT NULL default '',
*   `te_datetime` datetime NOT NULL default '0000-00-00 00:00:00',
*   `te_age` tinyint(3) unsigned NOT NULL default '0',
*   `te_live` enum('LIVE','NOT LIVE') default NULL,
*   `te_desc` mediumtext NOT NULL,
*   PRIMARY KEY  (`te_id`)
* ) TYPE=MyISAM;
*/

include_once ('config.inc');

$db_connection = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS)
    or die('Could not connect: ' . mysql_error());
mysql_select_db('pool') or die('Could not select database');
require_once('TableEditor.php');

$editor = new TableEditor($db_connection, 'customer');
    
// $editor->setConfig('allowView', false);
// $editor->setConfig('allowAdd', false);
// $editor->setConfig('allowEdit', false);
// $editor->setConfig('allowCopy', false);
// $editor->setConfig('allowDelete', false);

$editor->setConfig('perPage', 15);

$editor->setDisplayNames(array('customer_id'       => 'ID',
                               'parent_first'     => 'Parent First',
                               'parent_last' => 'Parent Last',
                               'child_first'    => 'Child First',
                               'child_last' => 'Child Last',
                               'child_birthday'      => 'Child Birthday',
                               'email'     => 'Email',
                               'phone_home'     => 'Home Phone',
                               'phone_cell'     => 'Cell Phone',
                               'emergencey_contact_name'     => '911 Name',
                               'emergencey_contact_phone'     => '911 Phone',
                               'username'     => 'Username'
							   ));

// $editor->noDisplay('te_password');
//$editor->noEdit('te_live');

//$editor->setInputType('te_password', 'password');
//$editor->setInputType('te_email', 'email');

$editor->setSearchableFields('customer_id', 'parent_first', 'parent_last', 'child_first', 'child_last', 'email', 'phone_home', 'phone_cell', 'emergencey_contact_name', 'emergencey_contact_phone', 'username');
$editor->setRequiredFields('parent_first', 'parent_last');

$editor->setDefaultOrderby('customer_id');
$editor->setDefaultValues(array('customer_id'   => NULL));

//$editor->addAdditionCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row added", implode("\n", $body));'));
//$editor->addEditCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row edited", implode("\n", $body));'));
//$editor->addCopyCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row copied", implode("\n", $body));'));
//$editor->addDeleteCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row deleted", implode("\n", $body));'));

function validateAge(&$obj, $data)
{
    $data = (int)$data;

    if ($data < 18 OR $data > 80) {
        $obj->addError('Invalid age! Please enter an age between 18 and 80');
    }

    return $data;
}

//$editor->addValidationCallback('te_age', 'validateAge');

//$editor->addDisplayFilter('te_desc', create_function('$v', 'return substr($v, 0, 100) . "...";'));

$editor->display();

?> 