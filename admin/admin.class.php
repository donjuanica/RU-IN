<?php
/**
* Contains the Admin class
* @package classes
* @author Travis Whitney
*
*/


function setup_db() {

	include_once('../config_inc.php');
	$db_host = DATABASE_HOST;
	$db_user = DATABASE_USER;
	$db_pass = DATABASE_PASS;
	
	global $db_connection;
	$db_connection = mysql_connect($db_host, $db_user, $db_pass)
		or die('Could not connect: ' . mysql_error());
	mysql_select_db('epicadve_inorout_adobe') or die('Could not select database');
}

class Admin {
	/**
	* @var string sql
	*/
	//var $sql;


	/**
	* Check_Access function
	* Retrieves a list of active instructors from the database and checks to see if the current logged-in user is in the list
	*/
	function Check_Access() {			
		if($_SESSION['ADMIN']) return true;
		if(isset($_REQUEST['u']) and isset($_REQUEST['p']) and isset($_REQUEST['p'])) {
			if($_REQUEST['u'] == 'administrator' and $_REQUEST['p'] == '546asdfsafd1623424654651asdgfasgh3re' and $_REQUEST['Login'] == 'Login') {
				$_SESSION['ADMIN'] = true;
				header('Location: admin.php');
				exit();
			}
		}
		
		include_once('../template.class.php');
		$template = new Template;
		$template->load("admin_template.html");
		$template->replace("title", SITE_NAME." Admin");
		$active_nav = "admin";
		$template->replace("head_head", "" );
		$template->replace("content", "<? include('admin_login_content.html'); ?>" );
		$template->publish();		
		die();
		
	}
}	
	
?>