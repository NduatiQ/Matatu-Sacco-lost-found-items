<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // Path to PHPMailer autoloader

session_start();
$fName = $_SESSION['fName'];
$lName = $_SESSION['lName'];
$tel = $_SESSION['tel'];
$emailAd = $_SESSION['email'];
$nationalId = $_SESSION['nationalId'];
$_SESSION['submitted_images'] = array();
if (!isset($_SESSION['visible_buttons'])) {
    $_SESSION['visible_buttons'] = []; // This will hold IDs of images whose buttons are visible.
} 

//Function to generate a unique random number
function generateUniqueRandNumber() {
    do {
    $randomNumber = rand(1000, 9999);
    } while (in_array($randomNumber, $_SESSION['generated_numbers'])); // Keep generating until it's unique

    // Add the generated number to the session array
    $_SESSION['generated_numbers'][] = $randomNumber;

    return $randomNumber;
} 

function printAlert($message){
    echo "<script>alert('$message')</script>";
}
?>
<!DOCTYPE html>
<div class="top">
  <br><br>  
</div>
<br>
<a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
<head>
    <link rel="icon" type="image/x-icon" href="https://i.ibb.co/wMxTVtq/Screenshot-143.png">
	<h1>THIS IS WHERE ITEMS UPLOADED WILL APPEAR </h1>
	<link href="styleslugg.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<div class="search-container">
    <form action="" method="GET">
        <input type="text" name="search_keyword" placeholder="Additional Description">
        <button type="submit">Search</button>
    </form>
</div>
<div class="alb-container">
<?php
$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  die ("connection failed:" . mySqli_connect_error());

}else {
//echo "connect Successfull";
}

// Selecting database
$db_selected = mysqli_select_db($conn, "signup");

// Check if database selection succeeded
if (!$db_selected) {
    die("Database selection failed: " . mysqli_error($conn));
}

$sql1 = "CREATE TABLE claimTbl (
  claimId INT(6) AUTO_INCREMENT PRIMARY KEY,
  custId INT(6) NOT NULL,
  luggageId INT(6) NOT NULL,
  collectionNo INT(6) NOT NULL,
  EmailAd VARCHAR(30) NOT NULL,
  dateCol DATE
)";


  if (mysqli_select_db($conn,"signup") && mySqli_query($conn, $sql1)){
    //echo "Table details created successfully";
  }else {
    //echo "Error crating table: " . mySqli_error($conn);
  }




// Your PHP code to fetch images based on search query goes here
  //in this code:

/*After retrieving items based on the keyword search, it calculates the similarity between the session description and each item's description.
It sets a threshold percentage (e.g., 60%) above which it considers the descriptions to be sufficiently similar.
If the similarity percentage exceeds the threshold, it displays the item details.*/

    if(isset($_SESSION['itemdescription']) || isset($_POST['search_keyword'])) {
    // Check if search keyword was submitted via POST
    if(isset($_POST['search_keyword'])) {
        $_SESSION['itemdescription'] .= ' ' . $_POST['search_keyword']; // Append additional keywords to the existing search
    }
    
    $keyword = $_SESSION['itemdescription'];
    

    // Split the keyword into individual words
    $keywords = explode(" ", $keyword);

    // Construct the WHERE clause dynamically
    $whereClauses = [];
    foreach ($keywords as $word) {
        $whereClauses[] = "ItemDescription LIKE '%$word%'";
    }
    $whereClause = implode(" OR ", $whereClauses);

    // Construct SQL to count occurrences of each keyword in the description
    $keywordCounts = [];
    foreach ($keywords as $word) {
        $keywordCounts[] = "SUM(LENGTH(ItemDescription) - LENGTH(REPLACE(ItemDescription, '$word', '')))";
    }
    $countExpression = implode(" + ", $keywordCounts);
    $Branch = $_SESSION['custbranch '];

    // Query to fetch items from the database
    $sqlUp = "SELECT *, ($countExpression) AS relevance_score 
              FROM luggageTbl 
              WHERE is_claimed=0 
              AND ($whereClause) 
              AND TownLocation = '$Branch'"; // Adding condition for matching SACCO branch
              
    $sqlUp .= " GROUP BY luggageId HAVING relevance_score > 0
                ORDER BY relevance_score DESC, luggageId DESC";

    $res = mysqli_query($conn, $sqlUp);

    if (mysqli_num_rows($res) > 0) {
        while ($images = mysqli_fetch_assoc($res)) {
            $sessionDescription = $_SESSION['itemdescription'];
            $itemDescription = $images['ItemDescription'];
            $Saccobranch = $images['TownLocation'];

            // Calculate similarity between session description and item description
            similar_text($sessionDescription, $itemDescription, $similarityPercentage);

            // Set a threshold for similarity
            $threshold = 60; // You can adjust this threshold as per your requirement

            // If similarity percentage exceeds the threshold, display the item
            if ($similarityPercentage >= $threshold) {
                $imageId = $images['luggageId'];
?>
                <div class="alb-container">
    <div class="item-container">
        <img src="uploads/<?= $images['ItemImage'] ?>" class="luggage-image">
    
    <div class="item-details">
        
        <form action='' method="post" class="form-container">
            <input type='hidden' name='image_id' value='<?php echo $imageId ?>'>
            <h3><?php echo $images['ItemName'] ?></h3>
            <p><?php echo $images['ItemDescription'] ?></p>
            <button type='submit' name="luggagesubmit3" id="luggagesubmit3" class="claim-btn" value='<?php echo $imageId ?>'>COLLECT</button>
        </form>
    </div>
</div>
</div>
<?php
            }
        }
    } else {
        echo "No records found";
    }
}






// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['image_id'])) {
    $imageId = $_POST['image_id'];
    if (isset($_SESSION['submittedImages'][$imageId])){
        printAlert("Item Already Claimed!");
        exit;
        
    
    } else {

// Check if the item is already claimed
    $checkClaimedQuery = "SELECT is_claimed FROM luggageTbl WHERE luggageId = ?";
    $stmt = mysqli_prepare($conn, $checkClaimedQuery);
    mysqli_stmt_bind_param($stmt, "i", $submittedImageId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $isClaimed);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($isClaimed == 0){
        // Item has not been claimed yet
        // Update is_claimed status of the item to claimed for it not to be available to another user.

    $query1 = "UPDATE luggageTbl SET is_claimed = 1 WHERE luggageId = ?";
    $stmt = mysqli_prepare($conn, $query1);
    mysqli_stmt_bind_param($stmt, "i", $imageId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    
    $randomNumber = generateUniqueRandNumber();
    $_SESSION['random_numbers'][$imageId] = $randomNumber;
    $dateTime = date("Y-m-d H:i:s");
    $_SESSION['date_time'] = $dateTime;
    
    //echo "Random number generated for $imageId is : " . $randomNumber;
    
    $emailAd = $_SESSION['email'];
    $nationalId = $_SESSION['nationalId'];
     unset($_SESSION['visible_buttons'][$imageId]);

     $sqlclaims = "SELECT MAX(claimId) AS claimFormId, IdPhoto FROM claimFormTbl WHERE CustomerId = $nationalId";
// Prepare and execute the SQL query
$result3 = mysqli_query($conn, $sqlclaims);

// Check if the query was successful
if ($result3) {
    // Fetch the result row as an associative array
    $claimData = mysqli_fetch_assoc($result3);
    
    // Now you can access the columns of the luggage data using keys
    $idimage = $claimData['IdPhoto'];
    $claimFormId = $claimData['claimFormId'];


    // Use the retrieved data as needed, such as sending an email to the customer
} else {
    // Query failed
    echo "Error: " . mysqli_error($conn);
}


    $sql2 = "INSERT INTO claimTbl (custId, luggageId, collectionNo,EmailAd, dateCol,claimFormId) VALUES 
    ('$nationalId','$imageId', '$randomNumber' ,'$emailAd', '$dateTime', '$claimFormId')";
    
      
      if (mysqli_query($conn, $sql2)){
     
      echo "Random number generated for image ID $imageId: $randomNumber";
      printAlert("Item Claim Successful");
      $_SESSION['submittedImages'][$imageId] = true;

//incase of probs check from here


// Reset the isAssigned Column for all agents
$resetAgentQuery = "UPDATE agentTbl SET isAssigned = 0";
mysqli_query($conn, $resetAgentQuery);


// Reset the agent_id column in the images table to NULL
$resetClaimsQuery = "UPDATE claimTbl SET agentId = NULL";
mysqli_query($conn, $resetClaimsQuery);




// Retrieve all agents 
$sqlagent = "SELECT * FROM AgentTbl WHERE SaccoBranch = '$Saccobranch'";;
$agentResult = mysqli_query($conn, $sqlagent);

// Check if the query executed successfully
if (!$agentResult) {
    die("Agents query failed: " . mysqli_error($conn));
}




// Retrieve unassigned claims
$sqlclaim = "SELECT * FROM claimTbl WHERE agentId IS NULL";
$claimResult = mysqli_query($conn, $sqlclaim);

// Check if the query executed successfully
if (!$claimResult) {
    die("Images query failed: " . mysqli_error($conn));
}

// Count the total number of agents and images
$numAgents = mysqli_num_rows($agentResult);
$numClaims = mysqli_num_rows($claimResult);

// Calculate the maximum number of images each agent can receive
$maxClaimsPerAgent = min(5, ceil($numClaims / $numAgents));

// Assign images to agents in a round-robin manner



while ($claimRow = mysqli_fetch_assoc($claimResult)) {
$claimId = $claimRow['claimId'];
 $luggageID = $claimRow['luggageId'];  
 $collectionNO = $claimRow['collectionNo']; 

// Fetch the next agent
    $agentRow = mysqli_fetch_assoc($agentResult);
    if (!$agentRow) {

        // Reset agents result set pointer
        mysqli_data_seek($agentResult, 0);
        $agentRow = mysqli_fetch_assoc($agentResult);
    }
    $agentId = $agentRow['AgentId'];
    $agentfame = $agentRow['FirstName'];
    $agentlame = $agentRow['LastName'];
    


// Check if the agent is already assigned three images
    /*$assignedClaimQuery = "SELECT COUNT(*) FROM claimTbl WHERE agentId = ?";
    $stmt = mysqli_prepare($conn, $assignedClaimQuery);
    mysqli_stmt_bind_param($stmt, "i", $agentId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $assignedClaimCount);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ($assignedClaimCount < 5) {
        */

        // Update the images table to assign the agent to the image
        $sqlUpdateClaim = "UPDATE claimTbl SET agentId = ? WHERE ClaimId = ?";
        $stmt = mysqli_prepare($conn, $sqlUpdateClaim);
        mysqli_stmt_bind_param($stmt, "ii", $agentId, $claimId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        
        // Insert the assignment record into the assignments table
    $insertAssignQuery = "INSERT INTO assignmentTbl (claimId, agentId, dateCol) VALUES (?, ?, ?)";
    $stmtAssignment = mysqli_prepare($conn, $insertAssignQuery);
    mysqli_stmt_bind_param($stmtAssignment, "iis", $claimId, $agentId, $dateTime);
    mysqli_stmt_execute($stmtAssignment);
    mysqli_stmt_close($stmtAssignment);


        // Update the agents table to mark the agent as assigned
        $updateAgentQuery = "UPDATE agentTbl SET isAssigned = 1 WHERE AgentId = ?";
        $stmtAgent = mysqli_prepare($conn, $updateAgentQuery);
        mysqli_stmt_bind_param($stmtAgent, "i", $agentId);
        mysqli_stmt_execute($stmtAgent);
        mysqli_stmt_close($stmtAgent);

        // Check if each agent has reached the maximum number of images
    $claimsPerAgentSql = "SELECT COUNT(*) FROM claimTbl WHERE agentId = ?";
    $stmtclaimsPerAgent = mysqli_prepare($conn, $claimsPerAgentSql);
    mysqli_stmt_bind_param($stmtclaimsPerAgent, "i", $agentId);
    mysqli_stmt_execute($stmtclaimsPerAgent);
    mysqli_stmt_bind_result($stmtclaimsPerAgent, $assignedClaimsCount);
    mysqli_stmt_fetch($stmtclaimsPerAgent);
    mysqli_stmt_close($stmtclaimsPerAgent);

    if ($assignedClaimsCount >= $maxClaimsPerAgent) {
        // If the agent has reached the maximum number of images, move to the next agent
        continue;
    }

    // Break the loop if all images have been assigned
    if (mysqli_num_rows($claimResult) == 0) {
        break;
    }

}
}



//To here nothing else to check

//retrieve the luggage from the luggage tbl
$sqlluggage = "SELECT * FROM luggageTbl WHERE luggageId = $luggageID";
// Prepare and execute the SQL query
$result = mysqli_query($conn, $sqlluggage);

// Check if the query was successful
if ($result) {
    // Fetch the result row as an associative array
    $luggageData = mysqli_fetch_assoc($result);
    
    // Now you can access the columns of the luggage data using keys
    $itemName = $luggageData['ItemName'];
    $itemDescription = $luggageData['ItemDescription'];
    $itemImage = $luggageData['ItemImage'];
    $townlocation = $luggageData['TownLocation'];


    // Use the retrieved data as needed, such as sending an email to the customer
} else {
    // Query failed
    echo "Error: " . mysqli_error($conn);
}

      //exit();
  


//Create an instance; passing `true` enables exceptions

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
            }
            .container {
                background-color: white;
                padding: 20px;
            }
            h1 {
                color: darkblue;
            }
            p {
                color: black;
            }
            .footer {
                background-color: skyblue;
                color: white;
                padding: 10px 20px;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Collection Of Your Lost/Found item.</h1>
            <p>Hello '.$fName.',</p>
            <p>Collect your '.$itemName.'.</p>
            <p>With Description: '.$itemDescription.'.</p>
            <p>And collection Number: '.$collectionNO .'.</p>
            <p>From agent</p>
            <p>Agent Id: '.$agentId.'.</p>
            <p>Agent Name: '.$agentfame.' '.$agentlame.'.</p>
            <p>From our Sacco Branch in '.$townlocation.' town.</p>
        </div>
    </body>
    </html>
';

// Add email attachment
$mail->addAttachment('uploads/' . $itemImage, $itemImage);

// Send email
$mail->send();

// Append the footer content after adding attachments
$mail->Body .= '<div class="footer">'.file_get_contents('footer.php').'</div>';

echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}



  
}
}
  


}/*else{
   echo  "<script>window.location.href = window.location.href;</script>";
   exit;
    //printAlert("Image Already Submitted");
    //echo $fName. $lName . $tel. $emailAd. $nationalId;
}*/



// Initialize or retrieve the session array
if (!isset($_SESSION['generated_numbers'])) {
    $_SESSION['generated_numbers'] = array();
}

if (empty($_SESSION['visible_buttons'])) {
    // Query to fetch all image IDs
    $sql = "SELECT luggageId FROM luggageTbl";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0 ){
        while ($imagesid = mysqli_fetch_assoc($result)) {
        $_SESSION['visible_buttons'][$imagesid['luggageId']] = true; // Set all image IDs as available initially
        }
    }
}





 ?>

</form>
</div>
<footer class="footer">
   <?php include 'footer.php'; ?>
</footer>
