<?php
session_start();

?>
<!DOCTYPE html>
<head>
<link href="styleslugg.css" rel="stylesheet">




<div class="all">
<h1> WELCOME </h1>
<h2> LOST LUGGAGE FORM </h2>
</head>
<form method ="post" id = "lostform">
	<label name = "fname" id="nm" >FIRST NAME: </label>
	<input type ="text" name="fname" id ="lb" required>
<br><br>
	<label name ="lname" id="nm"> LAST NAME: </label>
	<input type="text" name = "lname" id="lb" required>
<br><br>
	<label name ="idno" id="nm"> ID NUMBER: </label>
	<input type="number" name = "idno" id = "lb" required>
<br><br>
	<label name="idimg" id="nm">ID ATTACHMENT: </label>
	<input type="file" name="idimg" id="idimg" accept = "image*/" required>
<br><br>
<label name="email" id="nm">EMAIL ADDRESS: </label>
<input type = "text" name="email" id="lb" required>
<br><br>
<label name="itmtype" id="nm">ITEM TYPE: </label>
<input type="text" name="itmtype" id ="lb" required>
<br><br>
<label name= "itmdescription" id="nm">ITEM DESCRIPTION: </label>
<textarea id = "itmdescription" rows="10" cols = "100">Provide a detailed description of the lost item </textarea>
<br><br>

<input type= "submit" id="luggagesubmit" name="luggagesubmit" value ="SUBMIT">


</form>
</div>

<?php

$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  die ("connection failed:" . mySqli_connect_error());

}
//echo "connection Successfull";




