<?php 
session_start();
require_once('connection.php'); 
// Turn off all error reporting
//error_reporting(0);


$branch_id=$_SESSION['branch_id'];

$u_id = $_SESSION['u_id'];



if($_REQUEST['action'] == "erase")
{



			  mysqli_query($con, "TRUNCATE TABLE `adm_client`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_expenses`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_item`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_itemcategory`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_itemsubcategory`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_itemunit`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_payee`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_purchase`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_purchasereturn`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_purchasereturn_detail`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_purchase_detail`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_purchase_payment`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_quotation`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_quotation_detail`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_sale_payment`");
			  mysqli_query($con, "TRUNCATE TABLE `adm_supplier`");
			  mysqli_query($con, "TRUNCATE TABLE `cust_sale`");
			  mysqli_query($con, "TRUNCATE TABLE `cust_salereturn`");
			  mysqli_query($con, "TRUNCATE TABLE `cust_salereturn_detail`");
			  mysqli_query($con, "TRUNCATE TABLE `cust_sale_customerdisplay`");
			  mysqli_query($con, "TRUNCATE TABLE `cust_sale_detail`");
			  mysqli_query($con, "TRUNCATE TABLE `xx_cust_salereturn`");
			  mysqli_query($con, "TRUNCATE TABLE `xx_cust_salereturn_detail`");


			  header('location:earse_all_data.php?msg=done');




}







?>