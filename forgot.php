<Doctype html>

<?php
session_start();
?>

<?php 
//mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  //die ("connection failed:" . mySqli_connect_error());

}
//echo "connect Successfull";
?>

<html>
<head>
    <?php //<link href="styles.css" rel="stylesheet">?>
   <style>
  <?php include "styles.css" ?>
</style>
   
  </head>
  <div class="outer">

  	<div class="logo-container">
        <img src="images\Screenshot (143).png" alt="Logo" class="logo">
    </div>

    <div class="top">
  <br><br>  
</div>
<div class="logform">
<form id= "myform" method= "post">


	<fieldset>
<label name="newuserpass" id="loglb">NEW PASSWORD: </label>
<input type="password" name="newpass" id="logfld"><br><br>
<label name="confirmpass" id="loglb">CONFIRM PASSWORD: </label>
<input type="password" name="confirmpass" id="logfld"><br><br>
<div class="buttons">
<div class="changebtn">
<input type ="submit" value ="CHANGE" id="change" name="change">
</div>
</div>
<br><br><br>

<div class = "forgot">
<a href="log.php">Back To Login </a>
</div>
</fieldset>

</form>
</div>

<?php

if (isset($_POST["change"])){
mysqli_select_db($conn,"signup");


$mynewpass = $_POST['newpass'];
$confirmpass = $_POST['confirmpass'];
$name = $_SESSION['sesname'];
$regexpass ='/^[A-Z][0-9a-z]{7,20}/';

function printAlert($message){
    echo "<script>alert('$message')</script>";
  }


if ($name == "default admin"){
	printAlert("Admin PAssword can't be changed");

}

else if (!preg_match($regexpass, $mynewpass)){
    printAlert("Password should start with a capital letter,have a minimum length of 8 but not exceeding 20");
  }
else if ($mynewpass != $confirmpass){
	printAlert("Passwords do not Match");
}

else{


$sql4 = "UPDATE information SET UserPassword = '$mynewpass' WHERE UserName = '$name' ";

$changepass = mysqli_query ($conn, $sql4);

//if ($mynewpass == $confirmpass){
	//echo "Passwords match";


$row = $changepass || mysqli_fetch_assoc($changepass);
if ($changepass||mysqli_num_rows($changepass > 0)){
	while ($row || !$changepass){
		
		printAlert("Password Change Successfull") ;
		break;

	
}
	}else{
	
	printAlert("ERROR!");
	 
}
}


}

mysqli_close($conn)
?>




