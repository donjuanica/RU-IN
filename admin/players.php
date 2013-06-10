<?php
include_once('../config_inc.php');
session_start();
include_once('admin.class.php');
$admin = new Admin();
$admin->Check_Access();


$active_nav = "players";
$admin_site_name = SITE_NAME . " Admin | Players";
$table_to_edit = 'players';


global $db_connection;
setup_db();

require_once('TableEditor.php');

$editor = new TableEditor($db_connection, $table_to_edit, $admin_site_name);
    
// $editor->setConfig('allowView', false);
// $editor->setConfig('allowAdd', false);
// $editor->setConfig('allowEdit', false);
// $editor->setConfig('allowCopy', false);
// $editor->setConfig('allowDelete', false);

$editor->setConfig('perPage', 100);

/*
$editor->setDisplayNames(array('instructor_id'       => 'ID',
                               'first_name'     => 'First',
                               'last_name' => 'Last',
                               'email'     => 'Email',
                               'phone_home'     => 'Home Phone',
                               'phone_cell'     => 'Cell Phone',
                               'username'     => 'Username'
							   ));
*/
$editor->noDisplay('key');
$editor->noEdit('key');

//$editor->setInputType('te_password', 'password');
//$editor->setInputType('te_email', 'email');

$editor->setSearchableFields('first_name', 'last_name', 'email');
//$editor->setRequiredFields('parent_first', 'parent_last');

$editor->setDefaultOrderby('player_id');
//$editor->setDefaultValues(array('created'   => NULL));

//$editor->addAdditionCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row added", implode("\n", $body));'));
//$editor->addEditCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row edited", implode("\n", $body));'));
//$editor->addCopyCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row copied", implode("\n", $body));'));
//$editor->addDeleteCallback(create_function('$data', 'foreach($data as $k => $v) {$body[] = "$k => $v";} mail("joe@example.com", "Row deleted", implode("\n", $body));'));

//$editor->addValidationCallback('te_age', 'validateAge');

//$editor->addDisplayFilter('te_desc', create_function('$v', 'return substr($v, 0, 100) . "...";'));

$editor->display();

?> 