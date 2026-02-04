<?php 
	if(isset($_GET["aaa"]))
	{
		echo "<pre>";
//print_r($_POST);
echo "</pre>";


    $name= '';
	$emailee = $_GET["aaa"];
	//die();
	$chasisNumber = '';
	$CarManufacturingName = '';
	$firstRegistration ='';
	$carInternationalExp = '';
	$registrationCity = '';
	$Nationality = '';
	$phone = '';
	$Dob ='';
	$MufacturingYear ='';
	$Model ='';
	$registrationCity = '';
	$drivingExperience = '';
	
	
	

	            $to= "peacefulmateen@gmail.com";
				//$to='asad.general@gmail.com';
				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
									
				// Additional headers
				//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
				$headers .= 'From: testing  <testing@gmail.com>' . "\r\n";
				//$headers .= 'Cc: asad.general@gmail.com' . "\r\n";
				//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";				

			 
				

				$email_content="
				<h2 style='color:#03C; color:#c44735; font-size:28px; text-shadow:1px 1px 1px #000; font-family:Georgia, 'Times New Roman', Times, serif'>Website Name</h2>
				<b style='font-family:Arial, Helvetica, sans-serif; color:#999; font-weight:normal;'>WebSite Tag</b>
				<div style='width:100%; height:20px; font-family:Arial, Helvetica, sans-serif; font-size:16px; padding:6px 0px; background-color:#c44735; font-weight:normal; color:#FFF; margin:4px 0px; text-shadow:2px 2px 2px #000'>
				&nbsp; Car Insurance User Request 
				</div>
				<span style='font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:25px; font-weight:normal'>
				<b>Member Name : ".$name."</b><br /><br />

				Your Registration detail as below:-<br />
				<table align='left' style='border:solid 1px #CCC;border-collapse: collapse;font-family:arial;font-size:14px;'>
					<thead>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>User Email</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$to."</th>
					</tr>
					
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Car Chasis Number</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$chasisNumber."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Car Manufacturing Company</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$CarManufacturingName."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'> Car First Registration</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$firstRegistration."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'> Driving International Experience</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$carInternationalExp."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Registration City</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$registrationCity."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Nationality</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$Nationality."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Phone</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$phone."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'> Date Of Birth</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$Dob."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'> Manufacturing Year</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$MufacturingYear."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Model</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$Model."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Registration City</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$registrationCity."</th>
					</tr>
					<tr>
						<th style='border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;'>Driving Experience In UAE</th>
						<th style='border:solid 1px #CCC;padding:6px; text-align:left;'>".$drivingExperience."</th>
					</tr>
				
					
					
					</thead>
					<tbody>
				</table>";
				
			//echo $email_content;
			//exit;
				
				
				$sendEmail=mail($to, 'Welcome - testing Website', $email_content, $headers);
		


                if($sendEmail)
				{
					echo "Thanks! Our Team Contact you very soon.";
				}
				else
				{
				    echo 'not sent';
				}
			
	}
	
	if(isset($_POST["internet"]))
	{
		echo "<pre>";
        print_r($_POST);
        echo "</pre>";
        
        $PackageName = $_POST['package_name'];
        $CustomerName = $_POST['CusmtomerName'];
        $CustomerPhone = $_POST['CustomerPhone'];
        $CustomerCity = $_POST['CustomerCity'];
        $CustomerEmailAddress = $_POST['CustomerEmailAddress'];
        $CustomerCountry = $_POST['CustomerCountry'];
        $CustomerAddress = $_POST['CustomerAddress'];
        
        
        $to= "peacefulmateen@gmail.com";
				//$to='asad.general@gmail.com';
				// To send HTML mail, the Content-type header must be set
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
									
				// Additional headers
				//$headers .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
				$headers .= 'From: testing  <testing@gmail.com>' . "\r\n";
				//$headers .= 'Cc: asad.general@gmail.com' . "\r\n";
				//$headers .= 'Bcc: birthdaycheck@example.com' . "\r\n";				

				$email_content='
				<h2 style="color:#03C; color:#c44735; font-size:28px; text-shadow:1px 1px 1px #000; font-family:Georgia, "Times New Roman", Times, serif">Website Name</h2>
				<b style="font-family:Arial, Helvetica, sans-serif; color:#999; font-weight:normal;">WebSite Tag</b>
				<div style="width:100%; height:20px; font-family:Arial, Helvetica, sans-serif; font-size:16px; padding:6px 0px; background-color:#c44735; font-weight:normal; color:#FFF; margin:4px 0px; text-shadow:2px 2px 2px #000">
				&nbsp; User Request For Internet Connection  
				</div>
				<span style="font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:25px; font-weight:normal">
				<b>User Name : '.$CustomerName.'</b><br /><br />

				Your Registration detail as below:-<br />';

				$email_content.='
				<table align="left" style="border:solid 1px #CCC;border-collapse: collapse;font-family:arial;font-size:14px;">
					<thead>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">User Selected Package</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$PackageName.'</th>
					</tr>
					
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">User Name</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$CustomerName.'</th>
					</tr>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">User Phone</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$CustomerPhone.'</th>
					</tr>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;"> User City</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$CustomerCity.'</th>
					</tr>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;"> User Email</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$CustomerEmailAddress.'</th>
					</tr>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">User Country</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$CustomerCountry.'</th>
					</tr>
					<tr>
						<th style="border:solid 1px #CCC;background-color:#f2f2f2;padding:6px;">Address</th>
						<th style="border:solid 1px #CCC;padding:6px; text-align:left;">'.$CustomerAddress.'</th>
					</tr>
					
					
					
					</thead>
					<tbody>
				</table>';
				
			//echo $email_content;
			//exit;
				
				
				$sendEmail=mail($to, 'Welcome - testing Website', $email_content, $headers);
		


                if($sendEmail)
				{
					echo "Thanks! Our Team Contact you very soon.";
				}
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
    }


?>