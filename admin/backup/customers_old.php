<?php
include_once('config.inc');

include_once('admin.class.php');
$admin = new Admin();
$admin->Check_Access();

include_once('template.class.php');

$template = new Template;
$template->load("admin_template.html");
$template->replace("title", ADMIN_SITE_NAME." | Customers");
$active_nav = "customers";
$template->replace("mootools_script", "" );
$template->replace("footer_message", FOOTER_MESSAGE );


isset($_POST['customer_id']) 		? $customer_id =  		$_POST['customer_id'] 		: $customer_id =  false;
isset($_POST['email']) 				? $email =  			$_POST['email'] 			: $email =  false;
isset($_POST['username']) 			? $username =  			$_POST['username'] 			: $username =  false;
isset($_POST['first_name']) 		? $first_name =  		$_POST['first_name'] 		: $first_name =  false;
isset($_POST['last_name']) 			? $last_name =  		$_POST['last_name'] 		: $last_name =  false;
isset($_POST['child_first_name']) 	? $child_first_name =  	$_POST['child_first_name'] 	: $child_first_name =  false;
isset($_POST['child_last_name']) 	? $child_last_name =  	$_POST['child_last_name'] 	: $child_last_name =  false;
isset($_POST['phone_home']) 		? $phone_home =  		$_POST['phone_home'] 		: $phone_home =  false;
isset($_POST['phone_cell']) 		? $phone_cell =  		$_POST['phone_cell'] 		: $phone_cell =  false;
isset($_POST['submit']) 			? $submit =  			$_POST['submit'] 			: $submit =  false;
isset($_POST['request_id']) 		? $request_id =  		$_POST['request_id'] 		: $request_id =  false;


if( empty($request_id) && empty($submit) ) {
    $template->replace("content", "<? include('admin_customers_search_content.html'); ?>" );
	$template->publish();
}

if( isset($request_id) && isset($submit) ) {
    $template->replace("content", "<? include('admin_customers_search_result_content.html'); ?>" );
	$template->publish();
}

include_once('customer.class.php');
$customer = new customer( intval($customer_id) );


    $template->replace("content", "<? include('admin_customers_content.html'); ?>" );









$template->publish();

?>