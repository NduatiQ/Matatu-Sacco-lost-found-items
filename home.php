
<?php
session_start();
$isRegistered = $_SESSION['logId'];
$sesname = $_SESSION['sesname'];
//Establishing connection to the database
$servername = "localhost";
$username = "root" ;
$password = "";
$database = "signup";

$conn = new mysqli($servername , $username , $password, $database);
$mobileno = '/^07[0-9]{8}/';
$regexname = '/^[A-Z][a-z]{3,10}/';
$pemail = '/^[A-Za-z0-9._%+-]+@gmail\.com$/';
$natlID = '/^[9,1,2,3,4][0-9]{5,8}/';
function printAlert($message){
    echo "<script>alert('$message')</script>";
  }

if (!$conn){
  //die ("connection failed:" . mySqli_connect_error());

}else{
    //echo "connection Successfull";
}

//Creating the agents table 


if(isset($_POST['luggagesubmit']) && isset($_FILES['id_card'])){
  $customerNatl = $_POST['natlId'];
  $fname = $_POST['fname'];
  $lname = $_POST['lname'];
  $tel = $_POST['tel'];
  $email = $_POST['email'];
  $img_name = $_FILES['id_card']['name'];
  $img_size = $_FILES['id_card']['size'];
  $tmp_name = $_FILES['id_card']['tmp_name'];
  $error = $_FILES['id_card']['error'];

  

if (!preg_match($natlID, $customerNatl)){
    printAlert("Not a Valid Id Number");
}

else if (!preg_match($regexname, $fname)){
    printAlert("Enter a valid First name");
}

else if (!preg_match($regexname, $lname)){
    printAlert("Enter a valid last name");
}

else if(!preg_match($mobileno, $tel)){
    printAlert("Enter a valid Mobile Number");
}

else if(!preg_match($pemail, $email)){
    printAlert("Enter a valid Personal Email Address");
}

else{
    if ($error === 0) {
        if ($img_size > 12500000) {
            printAlert("Sorry, your Image file is too large.");
        }else {
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);

            $allowed_exs = array("jpg", "jpeg", "png"); 

            if (in_array($img_ex_lc, $allowed_exs)) {
                $new_img_name = uniqid("IMG-", true).'.'.$img_ex_lc;
                $img_upload_path = 'iduploads/'.$new_img_name;
                move_uploaded_file($tmp_name, $img_upload_path);
                $_SESSION['customernatlid'] = $new_img_name;

                // Insert into Database
                $sql2 = "INSERT INTO customerDetails (CustomerId, FirstName, LastName, TelephoneNo, EmailAddress, isRegistered, IdPhoto) VALUES ('$customerNatl', '$fname', '$lname' ,'$tel', '$email', '$isRegistered', '$new_img_name')";
                if(mysqli_select_db($conn,"signup") && mysqli_query($conn, $sql2)){
                printAlert( "ID image upload succeessfull");
                //echo $itemname. $itemdescription. $branch;
                //$_SESSION['customernatlid'] = $new_image_name;
                
                header("location: claim.php");
                }else {
                echo "You can't upload files of this type".mysqli_error($conn);
            }
        }else {
                printAlert("You can't upload files of this type");
            }
        }
    }

  

/*function printAlert($message){
    echo "<script>alert('$message')</script>";
  }*/
$_SESSION['fName'] = $fname;
$_SESSION['lName'] = $lname;
$_SESSION['tel'] = $tel;
$_SESSION['email'] = $email;
$_SESSION['nationalId'] = $customerNatl;

/*}else if (isset($_POST['contact'])){
        header("location: contact.php");
    }
   */
}
}else{
    //echo "error".mysqli_error($conn);
}
?>

<!doctype html>
<html>
<div class="homepage">

<div class="top">
  <br><br>  
</div>
<br>
<a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="https://i.ibb.co/wMxTVtq/Screenshot-143.png">
<style>
<?php include "styles.css" ?>

</style>
</head>
<body>
<meta name="viewport" content="width=device-width, initial-scale=1">
<h1 style="text-align: center; color: darkblue;">HOME</h1>

<form method="post" id = "homeform" action="claim.php" enctype="multipart/form-data" >
    <?php
    $sqlregistered = "SELECT * FROM customerDetails where isRegistered = '$isRegistered'";
    $rest = mysqli_query($conn, $sqlregistered);

    if($rest) {
  $row = mysqli_fetch_assoc($rest);


  if( $row === null || $row['isRegistered'] === null ){
         ?>
 <h1 style="text-align: center;"><?php echo "Welcome ".$sesname ?> &nbsp; <img src="images\wave.svg" width="40"></h1> 
<button type="button" onclick="openForm()" name="register" id="register">REGISTER</button>

<?php 
}elseif ($row['isRegistered'] == $isRegistered){?>
<h1 style="text-align: center; color: black;"><?php echo "Welcome Back ".$sesname ?> &nbsp; <img src="images\wave.svg" width="40"></h1>
<button type="submit"  name = "view" id = "register">VIEW ITEMS </button> 

<?php 
$_SESSION['nationalId'] = $row['CustomerId'];
$_SESSION['fName'] = $row['FirstName'];
$_SESSION['lName'] = $row['LastName'];
$_SESSION['tel'] = $row['TelephoneNo'];
$_SESSION['email'] = $row['EmailAddress'];
$_SESSION['customernatlid'] = $row['IdPhoto'];
}
else{
    echo mysqli_error($conn);
}
} else {
    //error in executing query
    echo "Error in Query Execution ".mysqli_error($conn);
} ?> 

<button type="button" formaction="<?php header("location: contact.php");?>" name = "contact" id = "contact">CONTACT US </button>
</form>
<br><br><br>
<div class="logimg">
<img src="images\Screenshot (143).png" width="900">
</div>
<?php
if(isset($_POST['contact'])){
    header("location: contact.php");
}
$sql1 = "CREATE TABLE customerDetails (
  CustomerId INT(10)  PRIMARY KEY NOT NULL,
  FirstName VARCHAR(30) NOT NULL,
  LastName VARCHAR(30) NOT NULL,
  TelephoneNo INT(10) NOT NULL,
  EmailAddress VARCHAR(30) NOT NULL
)";

if (mysqli_select_db($conn,"signup") && mySqli_query($conn, $sql1)){
    //echo "Table details created successfully";
  }else {
    //echo "Error creating table: " . mySqli_error($conn);
  }
?>


<div class="homepara">
    <div class = "p1">
<p1>The Awaah broker provides a better solution to facilitate trade of different commodities between comrades based on their geographic locations.The system opts you to choose whether you want to access as a seller or a client.</p1></div><br>

<div class = "p2">
<p2>Once you click on the seller, the system allows you to upload the image of the commodity you want to sell, the price and the description which can later be viewed in a dashboard by clients around the same location as the seller</p2></div><br>
<div class= "p3">
<p3>Once you click on the client, a dashboard is presented to you where you can be able to view different commodities based on your geographical location where you can proceed to boy where you will follow the payment procedure and be linked with the seller for delivery. </p3></div><br>
</div><br>


<br><br>
<div class = "slideshowContainer">
    <div class ="mySlides fade">
        <img src = "https://imgs.search.brave.com/VNvJH_B7dQKIxA3rLreybOuGP3BMZ-dq8RzqLGTtV90/rs:fit:860:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMTIy/NTAyNTQ1OC9waG90/by9zdWl0Y2FzZS1p/c29sYXRlZC1vbi13/aGl0ZS1iYWNrZ3Jv/dW5kLmpwZz9zPTYx/Mng2MTImdz0wJms9/MjAmYz10T1JUek1I/Z1BTNnM5R3o2VldJ/UHlPOWlJM25Ibl9S/WXkxdDdSS1M5SFRj/PQ" style="width:100%">
    </div>

    <div class ="mySlides fade">
        <img src = "https://imgs.search.brave.com/UwY_gvCz7denJegsCB_9QbBavz_4ne1CxyGux_bW0b4/rs:fit:860:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudW5zcGxhc2gu/Y29tL3Bob3RvLTE2/MjI1NjA0ODA2NTQt/ZDk2MjE0ZmRjODg3/P3E9ODAmdz0xMDAw/JmF1dG89Zm9ybWF0/JmZpdD1jcm9wJml4/bGliPXJiLTQuMC4z/Jml4aWQ9TTN3eE1q/QTNmREI4TUh4elpX/RnlZMmg4TVRWOGZH/SmhZMnR3WVdOcmZH/VnVmREI4ZkRCOGZI/d3c" style="width:100%">
    </div>

    <div class ="mySlides fade">
        <img src = "https://imgs.search.brave.com/mwQ0i1eWVl6LPyFqwGWiQOzFJqgtXZQOPrt2pQZpxYA/rs:fit:860:0:0/g:ce/aHR0cHM6Ly9wcmV2/aWV3cy4xMjNyZi5j/b20vaW1hZ2VzL2lh/NjQvaWE2NDEwMDkv/aWE2NDEwMDkwMDA0/Ni83OTE5MDIyLXJv/cGUta25vdC10aWVk/LWZ1bGwtYnVybGFw/LWdpZnQtZ2FyYmFn/ZS1zYWNrLWJhZy5q/cGc" style="width:100%">
    </div>


<br>

<div style= "text-align: center;">
    <span class="dot"></span>
    <span class="dot"></span>
    <span class="dot"></span>
</div>

<script>
    let slideIndex = 0;
    showSlides();

    function showSlides() {
        let i;
        let slides = document.getElementsByClassName("mySlides");
        let dots = document.getElementsByClassName("dot");
        for (i =0; i<slides.length; i++){
            slides[i].style.display = "none";

        }
        slideIndex++;
        if (slideIndex > slides.length) {slideIndex = 1}
        for (i=0; i<dots.length; i++){
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
        setTimeout(showSlides, 2000); //changes image after 2s
                }
    
</script>



</head>


<script>
function openForm() {
  document.getElementById("myForm").style.display = "block";
}

function closeForm() {
  document.getElementById("myForm").style.display = "none";
}
</script>
</body>



<div class="form-popup" id="myForm" >
<form method="post" id="custform" enctype="multipart/form-data">
    <h2 style="color: darkblue; text-align: center;"> REGISTER AS A CUSTOMER</h2>
    <label name="natlId" id="nm">NATIONAL ID NUMBER: </label>
<input type="number" name="natlId" id="lb" value="<?php echo isset($_POST['natlId']) ? $_POST['natlId'] : ''; ?>">
<br><br>
<label name="fname" id="nm">FIRST NAME: </label>
<input type="text" name="fname" id="lb" required value="<?php echo isset($_POST['fname']) ? $_POST['fname'] : ''; ?>">
<br><br>
<label name="lname" id="nm">LAST NAME: </label>
<input type="text" name="lname" id="lb" required value="<?php echo isset($_POST['lname']) ? $_POST['lname'] : ''; ?>">
<br><br>
<label name="tel" id="nm">TELEPHONE NO: </label>
<input type="number" name="tel" id="lb" value="<?php echo isset($_POST['tel']) ? $_POST['tel'] : ''; ?>">
<br><br>
<label name="email" id="nm">EMAIL ADDRESS: </label>
<input type="text" name="email" id="lb" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
<br><br>
<label for="id_card" class="claim-form" id="nm">ID CARD UPLOAD:</label><br>
<input type="file" id="lb" name="id_card" accept="image/*" required><br><br>



<button type= "submit" id="luggagesubmit" name="luggagesubmit">REGISTER</button>
<button type="button" id = "luggagesubmit" class="btn cancel" onclick="closeForm()">CLOSE</button>
</form>
</div>
</div>
</div>
</body>
</div>
<footer class="footer">
   <?php include 'footer.php'; ?>
</footer>
</html>
