<?php 
session_start();
//if(!isset($_SESSION['admin_id']) && !isset($_SESSION['u_id']) && !isset($_SESSION['branch_id'])){
if(!isset($_SESSION['u_id'])){
	?>
		<script type="text/javascript">
			window.location = "login";
		</script>
	<?php
	die();
}
if(!isset($_SESSION['admin_id'])){
	?>
		<script type="text/javascript">
			window.location = "login";
		</script>
	<?php
	die();
}
if(!isset($_SESSION['branch_id'])){
	?>
		<script type="text/javascript">
			window.location = "login";
		</script>
	<?php
	die();
}
$GLOBALS['branch_id']=$_SESSION['branch_id'];
$branch_id=$GLOBALS['branch_id'];

 ?>