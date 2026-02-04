<?php
session_start();
include('connection.php');
?>
<!DOCTYPE html>
<html lang="en" class="loading">
  <!-- BEGIN : Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Apex admin is super flexible, powerful, clean &amp; modern responsive bootstrap 4 admin template with unlimited possibilities.">
    <meta name="keywords" content="admin template, Apex admin template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="PIXINVENT">
    <title>Forgot Password - ePOS Dadday</title>
    <link rel="apple-touch-icon" sizes="60x60" href="login_assets/img/ico/apple-icon-60.png">
    <link rel="apple-touch-icon" sizes="76x76" href="login_assets/img/ico/apple-icon-76.png">
    <link rel="apple-touch-icon" sizes="120x120" href="login_assets/img/ico/apple-icon-120.png">
    <link rel="apple-touch-icon" sizes="152x152" href="login_assets/img/ico/apple-icon-152.png">
    <link rel="shortcut icon" type="image/x-icon" href="login_assets/img/ico/favicon.ico">
    <link rel="shortcut icon" type="image/png" href="login_assets/img/ico/favicon-32.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-touch-fullscreen" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link href="https://fonts.googleapis.com/css?family=Rubik:300,400,500,700,900|Montserrat:300,400,500,600,700,800,900" rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <!-- font icons-->
    <link rel="stylesheet" type="text/css" href="login_assets/fonts/feather/style.min.css">
    <link rel="stylesheet" type="text/css" href="login_assets/fonts/simple-line-icons/style.css">
    <link rel="stylesheet" type="text/css" href="login_assets/fonts/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="login_assets/vendors/css/perfect-scrollbar.min.css">
    <link rel="stylesheet" type="text/css" href="login_assets/vendors/css/prism.min.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN APEX CSS-->
    <link rel="stylesheet" type="text/css" href="login_assets/css/app.css">
    <!-- END APEX CSS-->
    <!-- BEGIN Page Level CSS-->
    <!-- END Page Level CSS-->
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  </head>
  <!-- END : Head-->

  <!-- BEGIN : Body-->
  <body data-col="1-column" class=" 1-column  blank-page">
    <!-- ////////////////////////////////////////////////////////////////////////////-->

<?php
$autoCaptcha='D03NP';
$autoCaptcha=uniqid();
$autoCaptcha=strtoupper(substr($autoCaptcha, 8, 13));

$msg="";
$status =1;
$ip="";
if(isset($_POST['recover']))
{
		$admin_Email =htmlentities(mysqli_real_escape_string($con,$_POST['input_Email']));
		
  	$sqlCheckLogin = "SELECT u.u_id, u_FullName,u.u_Email, u.u_Password2, u.branch_id, branch_admin, currency_symbol, branch_Name, branch_Code, branch_ActiveToDate
FROM `u_user` as u
INNER JOIN adm_branch ON adm_branch.branch_id=u.branch_id
LEFT OUTER JOIN adm_currency ON adm_currency.currency_id=adm_branch.currency_id
WHERE u.u_Email = '$admin_Email';";
		
		$queryCheckLogin = mysqli_query($con, $sqlCheckLogin);
		$branch_user=mysqli_num_rows($queryCheckLogin);
		
		if($branch_user===1)
		{
			$rowCheckLogin = mysqli_fetch_assoc($queryCheckLogin);
			$branch_ActiveToDate=$rowCheckLogin['branch_ActiveToDate'];
			$current_date=date('Y-m-d');
			if($branch_ActiveToDate>=$current_date)
			{				
				
				$to=$rowCheckLogin['u_Email'];
				$password=$rowCheckLogin['u_Password2'];
				
				
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
									
				// Additional headers
				//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
				$headers .= 'From: ePOS Daddy <info@eposdaddy.com>' . "\r\n";
				//$headers .= 'Cc: asad.general@gmail.com' . "\r\n";
				//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";				

				$email_content='
				<h2 style="color:#03C; color:#c44735; font-size:28px; text-shadow:1px 1px 1px #000; font-family:Georgia, "Times New Roman", Times, serif">ePOS Daddy</h2>
				<b style="font-family:Arial, Helvetica, sans-serif; color:#999; font-weight:normal;">(That Was Easy)</b>
				<div style="width:100%; height:20px; font-family:Arial, Helvetica, sans-serif; font-size:16px; padding:6px 0px; background-color:#c44735; font-weight:normal; color:#FFF; margin:4px 0px; text-shadow:2px 2px 2px #000">
				&nbsp; Forgot Password!
				</div>
				<span style="font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:25px; font-weight:normal">
				<b>Dear Member,</b><br /> On behalf of your request for password, below is your current passowrd,<br />
				<strong>'.$password.'</strong><br>
				If you not request this please ignore this email
				';

				$email_content.='
				<br style="clear:both;" >
				<br style="clear:both;" >
				<br style="clear:both;" >
					<a href="https://www.eposdaddy.com/epos/login"><button style="padding:8px 20px;float:left;background-color:#c44735; cursor:pointer;font-weight:normal; color:#FFF;">Click here to Login</button></a>
				<br style="clear:both;" >
				</span><br />
					';
				$sendEmail=mail($to, 'Forgot Password - ePOS Daddy', $email_content, $headers);
				
				
				
				
				$_SESSION['msg']="<div class='alert alert-success'>Email sent, Please check your inbox or junk folder.</div>";
				?>
				<script type="text/javascript">window.location.href='forgot_password';</script>
				<?php
				die();
			}
			else
			{
				$_SESSION['msg']='<div class="alert alert-danger">Login Rights Expired!</div>';	
				?>
				<script type="text/javascript">window.location.href='forgot_password';</script>
				<?php
				die();
			}
		}
		else
		{
			$_SESSION['msg']='<div class="alert alert-danger">Email Not Exists!</div>';
			?>
				<script type="text/javascript">window.location.href='forgot_password';</script>
				<?php
				die();

		}  
}
?>



    <div class="wrapper">
      <div class="main-panel">
        <!-- BEGIN : Main Content-->
        <div class="main-content">
          <div class="content-wrapper"><!--Login Page Starts-->
<section id="login">
  <div class="container-fluid">
    <div class="row full-height-vh m-0">
      <div class="col-12 d-flex align-items-center justify-content-center">
        <div class="card">
          <div class="card-content">
            <div class="card-body login-img">
              <div class="row m-0">
                <div class="col-lg-6 d-lg-block d-none py-2 text-center align-middle">
                  <img src="login_assets/img/gallery/forgot.png" alt="" class="img-fluid mt-5" width="300" height="230">
                </div>
                <div class="col-lg-6 col-md-12 bg-white px-4 pt-3">
                  <h4 class="mb-2 card-title">Recover Password</h4>
                  <p class="card-text mb-3">
                    Please enter your email address and we'll send you email.
                  </p>
                  <?php if(!empty($_SESSION['msg'])){ echo $_SESSION['msg']; unset($_SESSION['msg']);} ?>
                  <form action="" id="login-form" method="post" class="smart-form client-form">
                  <input type="email" class="form-control mb-3" placeholder="Enter your email" name="input_Email"/>
                  <div class="fg-actions d-flex justify-content-between">
                    <div class="login-btn">
                      <button class="btn btn-outline-primary">
                        <a href="login" class="text-decoration-none">Back To Login</a>
                      </button>
                    </div>
                    <div class="recover-pass">
                      <button  type="submit" name="recover" class="btn btn-primary">
                        Recover
                      </button>
                    </div>
                  </div>
                  
                  
                  
                  </form>
                  <hr class="m-0">
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!--Login Page Ends-->

          </div>
        </div>
        <!-- END : End Main Content-->
      </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <!-- BEGIN VENDOR JS-->
    <script src="login_assets/vendors/js/core/jquery-3.2.1.min.js" type="text/javascript"></script>
    <script src="login_assets/vendors/js/core/bootstrap.min.js" type="text/javascript"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN APEX JS-->
    <script src="login_assets/js/customizer.js" type="text/javascript"></script>
    <!-- END APEX JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    <!-- END PAGE LEVEL JS-->
  </body>
  <!-- END : Body-->
</html>