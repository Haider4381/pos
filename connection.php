<?php
//session_start();
 $con=mysqli_connect("localhost" , "root" , "" , "abdullahtraders" );
 mysqli_set_charset($con,"utf8");
 $GLOBALS['con']=$con; 

 function exe_Query($query)
 {  
 	$con=$GLOBALS['con'];
 	if(mysqli_query($con,$query)) 
 	{
 		return 1;
 	}
 	else
 	{
 		return 0;
 	}
 }

$time_zone="Asia/Karachi";
//if(isset($_SESSION['branch_TimeZone']) && !empty($_SESSION['branch_TimeZone'])) {$time_zone=$_SESSION['branch_TimeZone'];}
date_default_timezone_set($time_zone);


$current_datetime_sql=date('Y-m-d H:i:s');

function test_input($data)
 {
 	return $data;
 }
 
function validate_input($data)
{
 	$con=$GLOBALS['con'];
	htmlentities(mysqli_real_escape_string($con,$data));
	return $data;
}
 
function validate_date_sql($data)
{
 	$con=$GLOBALS['con'];
	$data=date("Y-m-d", strtotime($data));
	return $data;
}

function validate_date_display($data)
{
 	$con=$GLOBALS['con'];
	$data=date("d-m-Y", strtotime($data));
	return $data;
}
$branch_name_session='ePOS Dadday';
$currency_symbol='';
if(isset($_SESSION['currency_symbol'])) {$currency_symbol=$_SESSION['currency_symbol'];} 
if(isset($_SESSION['u_id'])) {$u_id=$_SESSION['u_id'];} 
if(isset($_SESSION['branch_id'])) {$branch_id=$_SESSION['branch_id'];} 
if(isset($_SESSION['branch_Name'])) {$branch_name_session=$_SESSION['branch_Name'];} 

$base_url_branchlogo='../epos-admin/';
?>
