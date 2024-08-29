<?php
//starting the session
session_start();
#$_SESSION["name"] = $name;
?>
<!doctype html>

<html>

  
  <head>
    <link href="styles.css" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="https://i.ibb.co/wMxTVtq/Screenshot-143.png">
  

   
  </head>


  
  <div class="outer">

<div class="logo-container">
        <img src="images\Screenshot (143).png" alt="Logo" class="logo">
    </div>

    <div class="top">
  <br><br>  
</div>
 
 <div class="logform">
  <form id ="myform" method="post">


    <fieldset>
    <label name="usernameQ" id="loglb"> USER NAME: </label>
    <input type="text" id="logfld" required name="username"><br><br>

    <label name="password" id="loglb"> PASSWORD: </label>
    <input type="password" id = "logfld" name="password" required id="password">
</br></br>
<div class="buttons">

    <div class="mybtns">
    <input type= "submit" id="signup" name="SignUp" value ="SignUp">
     <input type= "submit" id="LogIn" name="LogIn" value ="LogIn">
</div>
</div>
  <br><br><br>

  <div class="forgot">
<b><a href ="forgot.php" style="color: red; text-decoration: none;"> Forgot Password ?</a></b>
</div>
    
    </fieldset>
  </form>
</div>
<br><br>


    <br><br><br>


<?php 
$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  die ("connection failed:" . mySqli_connect_error());

}
//echo "connect Successfull";


//creating a database

$sql = "CREATE DATABASE signup";
if (mySqli_query($conn, $sql)){
  //echo "Database create successful";
}else {
  //echo "Error in database creation". mySqli_error($conn);
}

//creating table 

$sql1 = "CREATE TABLE information (
  Id INT(6) AUTO_INCREMENT PRIMARY KEY,
  UserName VARCHAR(30) NOT NULL,
  UserPassword CHAR(20) NOT NULL 
)";


  if (mysqli_select_db($conn,"signup") && mySqli_query($conn, $sql1)){
    //echo "Table details created successfully";
  }else {
    //echo "Error crating table: " . mySqli_error($conn);
  }

  $sql8="ALTER TABLE information
ADD accountStatus int(3)";
if (mysqli_select_db($conn,"signup") && mysqli_query($conn, $sql8)){

      //printAlert( "Information Added to table");
       //header("location: admin.php");
      }
    else {
        //printAlert("Erro in adding info");

        //echo "ERROR: Could not create Account " .mysqli_error($conn);
  }

 

$regexname = '/^[A-Z][A-Za-z]{6,15}/';

$regexpass ='/^[A-Z][0-9a-z]{7,20}/';

function printAlert($message){
    echo "<script>alert('$message')</script>";
  }

if (isset($_POST["SignUp"])){

//$sql9="SELECT"

  $Userpass = $_POST['password'];
  $name =  $_POST['username'];


  $sql5 = "SELECT * FROM information WHERE UserName = '$name'";
  $checkname = mysqli_query($conn, $sql5);

  if (mysqli_num_rows($checkname) > 0){
    printAlert("Username Already Taken");
  }
  else if (!preg_match($regexname, $name)){
    printAlert("Name Must begin with a Capital Letter and have atleast 7 letters but not exceeding 15 ");
  }
  else if (!preg_match($regexpass, $Userpass)){
    echo printAlert("Password should start with a capital letter,have a minimum length of 8 but not exceeding 20");
  }


  else{

 //Store a hash of the password in a variabe
  #$hashUserpass= password_hash($Userpass, PASSWORD_DEFAULT);
$hashedPassword = password_hash($Userpass, PASSWORD_DEFAULT);
    $sql2 = "INSERT INTO information (UserName, UserPassword, accountStatus) VALUES 
    ('$name', '$hashedPassword' ,'1')";

      if (mysqli_query($conn, $sql2)){

       printAlert( "Account Created Successfully");
      }else {
        printAlert("Error in Creating Account");

        //echo "ERROR: Could not create Account ".mysqli_error($conn);
  }


  }
}


if (isset($_POST["LogIn"])) {
    // Get username and password from form
    $name = $_POST['username'];
    $Userpass = $_POST['password'];

    // Check for admin login
    if ($name === "default admin" && $Userpass === "13470") {
        header("location: admin.php");
        exit;
    }

    // Prepare SQL statement to fetch user details
    $sql = "SELECT * FROM information WHERE UserName = ?";

    // Initialize statement
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        // Bind parameters and execute query
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);

        // Get result
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Verify password
          $passVer = password_verify($Userpass, $row['UserPassword']);
            if (password_verify($Userpass, $row['UserPassword'])) {
                // Check account status
                if ($row['accountStatus'] == '1') {
                    // Customer login
                    $_SESSION['logId'] = $row['Id'];
                    $_SESSION['sesname'] = $row['UserName'];
                    $_SESSION['agentpass'] = $row['UserPassword'];
                    header("Location: home.php");
                    exit;
                } elseif ($row['accountStatus'] == '2') {
                    // Agent login
                    $_SESSION['agentId'] = $row['Id'];
                    $_SESSION['sesname'] = $row['UserName'];
                    $_SESSION['agentpass'] = $row['UserPassword'];
                    header("Location: agent.php");
                    exit;
                }
            } else {
                // Print debugging information
                printAlert("Incorrect Details, Try again");
            }
        } else {
            // User not found
            printAlert("User not found");
        }
    } else {
        // SQL statement preparation failed
        printAlert("SQL statement preparation failed");
    }

    // Close statement
    mysqli_stmt_close($stmt);
}
?>
</div>






    
