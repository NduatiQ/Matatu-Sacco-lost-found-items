<?php session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Path to PHPMailer autoloader 
$fnameLname = $_SESSION['sesname'];
$agentPass = $_SESSION['agentpass'];
$pattern = '/^([A-Z][a-z]+)([A-Z][a-z]+)$/';
$servername = "localhost";
$username = "root" ;
$password = "";
$database = "signup";


$conn = new mysqli($servername , $username , $password, $database);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Select database
if (!$conn->select_db("signup")) {
    die("Database selection failed: " . $conn->error);
}

if (preg_match($pattern,$fnameLname, $matches)) {
    // Extracted first name
    $firstName = $matches[1];
    
    // Extracted last name
    $lastName = $matches[2];

    // Output the extracted names
    //echo $firstName." ". $lastName. $agentPass;
$sqlAgent = "SELECT * FROM agentTbl WHERE FirstName = '$firstName' AND LastName = '$lastName' AND agentPassword = '$agentPass'";
$resultAgent = mysqli_query($conn, $sqlAgent);
if ($resultAgent === false) {
        // Handle the error
        echo "Error executing query: " . mysqli_error($conn);
    }
if (mysqli_num_rows($resultAgent) > 0) {
    // Fetch the SACCO branch from the result
    $rowAgent = mysqli_fetch_assoc($resultAgent);
    $saccoBranch = $rowAgent['SaccoBranch'];
    $AgentId = $rowAgent['AgentId'];
    $_SESSION['agentId'] = $AgentId;
    $_SESSION['branch'] = $saccoBranch;

}

}



?>
<!Doctype html>
<html>
<div class="top">
  <br><br>  
</div><br>

<a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
<header>
    <h1><?php echo  "Welcome Sacco Agent  $fnameLname "  ?> &nbsp; <img src="images\wave.svg" width="40"><br>AGENT ID: &nbsp;<?php echo $AgentId;?>&nbsp;&nbsp;&nbsp;AGENT BRANCH:<?php echo strtoupper($saccoBranch )?> </h1>
    
    <link href="styleslugg.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</header>
<div class="agentpage">
<body>
<div class= "agentdashboard">
<p1 style ="text-decoration: underline;">DASHBOARD </p1>
<br><br>
        <button type="button" name="upload" onclick="openForm()" id="" >UPLOAD FOUND ITEMS &nbsp;&nbsp;<i class="fas fa-suitcase"></i></button>
     <button type="submit" name="reportagt" onclick="window.location.href = 'reportAgent.php';" id="">VIEW REPORTS  &nbsp;&nbsp;<i class="fas fa-file-alt"></i></button>
</div>

<div class="form-popup" id="myForm">
<form method="post" enctype="multipart/form-data" id="custform">
<h2> UPLOAD LOST/FOUND ITEM </h2>
    <label name="itemtype" id="nm">ITEM NAME: </label>
<input type="text" name="itemtype" id="lb" required value="<?php echo isset($_POST['itemtype']) ? $_POST['itemtype'] : ''; ?>">
<br><br>
<label name="itmdescription" id="nm">ITEM DESCRIPTION: </label>
<textarea id="itmdescription" name="itmdescription" rows="10" cols="100"><?php echo isset($_POST['itmdescription']) ? $_POST['itmdescription'] : 'Provide a detailed description of the found item'; ?></textarea>
<br><br>
<label name="idimg" id="nm">ITEM IMAGE: </label>
<input type="file" name="idimg" id="lb" accept="image/*" required>
<br><br>

<input type= "submit" id="luggagesubmit2" name="luggagesubmit2" value ="SUBMIT">
<button type="button" id = "luggagesubmit" class="btn cancel" onclick="closeForm()">CLOSE</button>


</form>
</div>

<div class="container">

    <script>
function openForm() {
  document.getElementById("myForm").style.display = "block";
}

function closeForm() {
  document.getElementById("myForm").style.display = "none";
}
</script>
</div>


</body>
</div>


<br>

<footer class="footer">
   <?php include 'footer.php'; ?>

</footer>

</html>


<?php


function printAlert($message){
    echo "<script>alert('$message')</script>";
  }

//luggage upload


if(isset($_POST['luggagesubmit2']) && isset($_FILES['idimg'])){
$Itemname = $_POST['itemtype'];
$Itemdescription = $_POST['itmdescription'];
//$Townlocation = $_POST['branch'];
//$Itemimage = $_POST['idimg'];
$regexname = '/[A-Za-z]{3,10}/';
$regexdescrip = '/^(?:\w+\s){1,9}\w+$/';
$img_name = $_FILES['idimg']['name'];
$img_size = $_FILES['idimg']['size'];
$tmp_name = $_FILES['idimg']['tmp_name'];
$error = $_FILES['idimg']['error'];



if (!preg_match($regexname, $Itemname)){
    printAlert("Enter a valid Item Name");
}

else if(!preg_match($regexdescrip, $Itemdescription)){
    printAlert("Provide a detailed and valid description");
}

else{
    if ($error === 0) {
        if ($img_size > 12500000) {
            printAlert("Sorry, your file is too large.");
        }else {
            $img_ex = pathinfo($img_name, PATHINFO_EXTENSION);
            $img_ex_lc = strtolower($img_ex);

            $allowed_exs = array("jpg", "jpeg", "png"); 

            if (in_array($img_ex_lc, $allowed_exs)) {
                $new_img_name = uniqid("IMG-", true).'.'.$img_ex_lc;
                $img_upload_path = 'uploads/'.$new_img_name;
                move_uploaded_file($tmp_name, $img_upload_path);

                // Insert into Database
                //$Townlocation = $_SESSION['saccoBranch'];
                $sql3= "INSERT INTO  luggageTbl (ItemName, ItemDescription, TownLocation, ItemImage) VALUES 
    ('$Itemname', '$Itemdescription' ,'$saccoBranch', '$new_img_name')";
                mysqli_query($conn, $sql3);
                printAlert( "Item Upload Successfull");
                }else {
                printAlert("You can't upload files of this type");
            }
        }
    }else {
        printAlert("unknown error occurred!");
        
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
}

//luggage upload end

/// Separate the full name into first name and last name using regular expression
preg_match('/([A-Z][^A-Z]*)/', $fnameLname, $matches);

// Extract the first and last names
$firstName = $matches[0];
$lastName = substr($fnameLname, strlen($firstName));


//echo $firstName." ". $lastName;


// Check if agent is logged in
/*if (!isset($_SESSION['agentId'])) {
    // Redirect to login page if not logged in
    header("Location: log.php");
    exit;
}*/


// Prepare SQL statement with JOIN to fetch required information
$sqlAssign = "SELECT 
            a.claimId,
            c.CustomerId,
            c.FirstName,
            c.LastName,
            c.TelephoneNo,
            cl.collectionNo,
            cl.claimFormId,
            cl.EmailAd,
            l.ItemImage,
            l.ItemName,
            l.ItemDescription,
            l.TownLocation,
            cf.IdPhoto
        FROM 
            assignmentTbl a
        INNER JOIN 
            claimTbl cl ON a.claimId = cl.claimId
        INNER JOIN 
            customerDetails c ON cl.custId = c.CustomerId
        INNER JOIN 
            luggageTbl l ON cl.luggageId = l.luggageId
        INNER JOIN
            claimFormTbl cf ON cl.claimFormId = cf.claimId
        WHERE 
            a.AgentId = ? AND 
            a.isCollected = 0  AND
            cl.isCollected = 0";

// Prepare and bind parameter
$stmt = $conn->prepare($sqlAssign);
$stmt->bind_param("i", $AgentId);

// Execute query
$stmt->execute();

// Get result
$result2 = $stmt->get_result();
 echo "<div class='container'>";
// Check if any rows are returned
if ($result2->num_rows > 0) {
    // Output data of each row
    while ($row2 = $result2->fetch_assoc()) {
        $emailAd = $row2['EmailAd'];
        $fName = $row2['FirstName'];
        $itemName = $row2['ItemName'];
        $itemDescription = $row2['ItemDescription'];
        $collectionNO = $row2['collectionNo'];
        $townLocation = $row2['TownLocation'];
        echo "<div class='assignment'>";
        echo "<div class='assignment-info'>";
        echo "<div class='center-content'>";
        echo "<h2 style='text-align: left;'>TO BE COLLECTED</h2>";
        echo "Customer ID: " . $row2["CustomerId"] . "<br>";
        echo "Customer First Name: " . $row2["FirstName"] . "<br>";
        echo "Customer Last Name: " . $row2["LastName"] . "<br>";
        echo "Customer Tel: 0" . $row2["TelephoneNo"] . "<br>";
        echo "Item Name: " . $row2["ItemName"] . "<br>";
        echo "Item Description: " . $row2["ItemDescription"] . "<br>";
        echo "Collection Number: " . $row2["collectionNo"] . "<br>";
        echo "Email Address: " . $row2["EmailAd"] . "<br>";
        echo "</div>";
        // Display luggage image (assuming it's a URL)
        echo "<div class='assignment-infoimg'>";
        echo "<div class='lugg-photo-container'>";
        echo "<label>Item image</label><br>";
        echo "<img src='" ."uploads/". $row2["ItemImage"] . "' alt='Luggage Image'><br>";
        echo "</div>";
        echo "<div class='id-photo-container'>";
         echo "<label>Customer ID Card</label><br>";
        echo "<img src='iduploads/" . $row2["IdPhoto"] . "'class ='idIMG' alt='ID Photo'><br>";
        echo "</div>";
        echo "</div>";
        // Add a collected button for each assignment
        echo "<form action='' method='post'>";
        echo "<input type='hidden' name='claimId' value='" . $row2["claimId"] . "'>";
        echo "<input type='submit' class='collected-btn' value='Collected'>";
        echo "</form>";
        echo "<hr>";
        echo "</div>";

   


   // Check if claim_id is provided via POST request
if (isset($_POST['claimId'])) {
    // Get claim_id from POST data
    $claimId = $_POST['claimId'];

    // Prepare SQL statement to update isCollected column in claimTbl
    $sqlUpdateClaim = "UPDATE claimTbl SET isCollected = 1 WHERE claimId = ?";

    // Prepare and bind parameter
    $stmt_update_claim = $conn->prepare($sqlUpdateClaim);
    $stmt_update_claim->bind_param("i", $claimId);

    // Execute query to update isCollected column in claimTbl
    $stmt_update_claim->execute();

    // Close statement
    $stmt_update_claim->close();

    // Prepare SQL statement to update isCollected column in assignmentTbl
    $sqlUpdateAssignment = "UPDATE assignmentTbl SET isCollected = 1 WHERE claimId = ?";

    // Prepare and bind parameter
    $stmt_update_assignment = $conn->prepare($sqlUpdateAssignment);
    $stmt_update_assignment->bind_param("i", $claimId);

    // Execute query to update isCollected column in assignmentTbl
    $stmt_update_assignment->execute();

    // Close statement
    $stmt_update_assignment->close();

    // Close connection
    $conn->close();

    // Display an alert message indicating that the claim has been collected
    $mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = 0;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'awaahsolutions@gmail.com';                     //SMTP username
    $mail->Password   = 'qqta frnn dpmq vxcb';                               //SMTP password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('awaahsolutions@gmail.com', 'Awaah Lost/Found System');
    $mail->addAddress($emailAd, $fName);     //Add a recipient
    //$mail->addAddress('ellen@example.com');               //Name is optional
    //$mail->addReplyTo('info@example.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

    //Content
    $mail->isHTML(true); // Set email format to HTML
$mail->Subject = 'Collection Of Your Lost/Found item.';

// Define email body with custom styling
$mail->Body = '
    <html>
    <head>
        <style>
            /* Define custom styles */
            body {
                font-family: Arial, sans-serif;
                color: black;
                background-color: white;
                margin: 0;
                padding: 20px;
            }
            .container {
                background-color: skyblue;
                padding: 20px;
                border-radius: 10px;
            }
            h1 {
                color: darkblue;
                margin-top: 0;
            }
            p {
                color: black;
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Collection Of Your Lost/Found item.</h1>
            <p>Thank you '.$fName.'&nbsp; for collecting your '.$itemName.'.</p>
            <p>With Description: '.$itemDescription.'.</p>
            <p>And collection Number: '.$collectionNO .'.</p>
            <p>From our Sacco Branch in '.$townLocation.' town.</p>
        </div>
    </body>
    </html>
';

// Send email
$mail->send();
echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}



  
}


    // Exit the script
    exit;
  

//$AgentID=$_SESSION['agentId'];


// Close connection
$conn->close();


    }
} else {
    printAlert("No Customer Collection Pending");
    echo mysqli_error($conn);

}

// Close statement
$stmt->close();
 



?>


