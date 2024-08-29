<?php session_start();
$customerId = $_SESSION['nationalId'];
$new_img_name = $_SESSION['customernatlid'];
?>
<!DOCTYPE html>
<html lang="en">
<div class="top">
  <br><br>  
</div>
<br>
<a href="home.php" class="logout-link"><i class="fas fa-arrow-left"></i> BACK</a><head>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="styles.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="https://i.ibb.co/wMxTVtq/Screenshot-143.png">
    <title class ="claim-form">Claim Form</title>
</head>
<body class ="claim-form">
    <h2 class = "claim-form">Claim Form</h2>
    <form action='' enctype="multipart/form-data" method="post" class ="claim-form">
        <label for="item_name" class ="claim-form">ITEM NAME:</label><br>
        <input type="text" id="item_name" name="item_name" required><br><br>

        <label for="item_description" class ="claim-form">ITEM DESCRIPTION:</label><br>
        <textarea id="item_description" name="item_description" rows="4" cols="50" required class ="claim-form"> </textarea><br><br>

       <label name="Sbranch" id="" class ="claim-form">SACCO BRANCH: </label>
<select id="" name="Sbranch">
    <option value="muranga">Muranga</option>
    <option value="thika">Thika</option>
    <option value="nairobi">Nairobi</option>
    <option value="nyeri">Nyeri</option>
</select>
<br><br>

        <input type="submit" name="claim" value="Claim" class ="claim-form">
    </form>





</body>
<?php
function printAlert($message){
    echo "<script>alert('$message')</script>";
  }


$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
//die ("connection failed:" . mySqli_connect_error());

}
else{
    //echo "connect Successfull";
}


if (isset($_POST['claim']) ){
$itemname = $_POST['item_name'];
$itemdescription = $_POST['item_description'];
$branch = $_POST['Sbranch'];
//$natnId = $_POST['id_card'];
$regexname = '/[A-Za-z]{3,10}/';
$regexdescrip = '/^[A-Za-z0-9\s]+$/';
$dateTime = date("Y-m-d H:i:s");


if (!preg_match($regexname, $itemname)){
    printAlert("Enter a valid Item Name");
}

else if(!preg_match($regexdescrip, $itemdescription)){
    printAlert("Provide a detailed and valid description");
}

else{
    /*if ($error === 0) {
        if ($img_size > 12500000) {
            printAlert("Sorry, your Image file is too large.");
        }else {
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);

            $allowed_exs = array("jpg", "jpeg", "png"); 

            if (in_array($img_ex_lc, $allowed_exs)) {
                $new_img_name = uniqid("IMG-", true).'.'.$img_ex_lc;
                $img_upload_path = 'iduploads/'.$new_img_name;
                move_uploaded_file($tmp_name, $img_upload_path);*/
            /*$sqlcustid = "SELECT * FROM customerDetails WHERE CustomerId = $customerId";
// Prepare and execute the SQL query
$result = mysqli_query($conn, $sqlcustid);

// Check if the query was successful
if (mysqli_select_db($conn,"signup") && $result) {
    // Fetch the result row as an associative array
    $idData = mysqli_fetch_assoc($result);
    
    // Now you can access the columns of the luggage data using keys
    $new_img_name = $idData['IdPhoto'];
} else {
    // Query failed
    echo "Error: " . mysqli_error($conn);
}*/

    // Use the retrieved data as needed, such as sending an email to the customer



                // Insert into Database
                $sql3= "INSERT INTO  claimFormTbl (CustomerId, ItemName, ItemDescription, SaccoBranch, IdPhoto, dateCol) VALUES 
    ('$customerId','$itemname', '$itemdescription' ,'$branch', '$new_img_name', '$dateTime')";
                if(mysqli_select_db($conn,"signup") && mysqli_query($conn, $sql3)){
                printAlert( "ID image upload succeessfull");
                //echo $itemname. $itemdescription. $branch;
                $_SESSION['itemname'] = $itemname;
                $_SESSION['itemdescription'] = $itemdescription;
                $_SESSION['custbranch '] = $branch;
                header("location: items.php");
                /*}else {
                echo "You can't upload files of this type".mysqli_error($conn);
            }*/
        }else {
                printAlert("You can't upload files of this type");
            }
    




}


}

/*else{
    if (mysqli_query($conn, $sql3)){
        //echo "Success";

       printAlert( "Item Upload Successfull");
      }else {
        //printAlert("Error in Creating Account");
        echo "fail".mysqli_error($conn);

        //echo "ERROR: Could not create Account ".mysqli_error($conn);
  }
}*/

$sql6 = "CREATE TABLE claimFormTbl (
  claimId INT(6) AUTO_INCREMENT PRIMARY KEY,
  CustomerId INT(10) NOT NULL,
  ItemName VARCHAR(30) NOT NULL,
  ItemDescription CHAR(20) NOT NULL,
  SaccoBranch CHAR(20) NOT NULL,
  IdPhoto text NOT NULL
)";

if (mysqli_select_db($conn,"signup") && mySqli_query($conn, $sql6)){
    //echo "Table details created successfully";
  }else {
//echo "Error crating table: " . mySqli_error($conn);
  }

?>

<footer class="footer">
   <?php include 'footer.php'; ?>
</footer>
</html>