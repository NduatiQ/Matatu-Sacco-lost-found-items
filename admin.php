<!Doctype html>
<html>
<div class="top">
  <br><br>  
</div><br>
<a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
<header>
	
	<h1> SACCO MANAGER PAGE </h1>
	<link href="styleslugg.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</header>
<div class="adminpage">
<body>
<div class= "dashboard">
<p1 style ="text-decoration: underline;">DASHBOARD </p1>
<br><br>
        <button onclick="togglePopup()" class="remove-agents-btn ">Remove Agents &nbsp;<i class="fas fa-user-minus"></i></button><br><br>

        <button onclick="togglePopup2()" class="remove-luggage-btn">Remove Uploaded luggage &nbsp;<i class="fas fa-trash-alt"></i></button>
   
</div>

<form id = "adminform" method="post">

<div class="adminbtn">
<button type="submit" name="viewagt" id="admbtn">VIEW AGENTS</button>
<button type="button" name = "agent" onclick="openForm2()" id = "admbtn">ADD AGENT </button>

</div>


</form>

</head>
<div class="form-popup" id="myForm2">
<form method="post" id="custform">
	<h2 style="text-align: center;"> ADD NEW SACCO AGENT </h2>
	<label name="fname" id="nm">FIRST NAME: </label>
<input type="text" name="fname" id="lb" required value="<?php echo isset($_POST['fname']) ? $_POST['fname'] : ''; ?>">
<br><br>
<label name="lname" id="nm">LAST NAME: </label>
<input type="text" name="lname" id="lb" required value="<?php echo isset($_POST['lname']) ? $_POST['lname'] : ''; ?>">
<br><br>
<label name="Sbranch" id="nm">SACCO BRANCH: </label>
<select id="lb" name="Sbranch">
    <option value="muranga" <?php echo (isset($_POST['Sbranch']) && $_POST['Sbranch'] == 'muranga') ? 'selected' : ''; ?>>Muranga</option>
    <option value="thika" <?php echo (isset($_POST['Sbranch']) && $_POST['Sbranch'] == 'thika') ? 'selected' : ''; ?>>Thika</option>
    <option value="nairobi" <?php echo (isset($_POST['Sbranch']) && $_POST['Sbranch'] == 'nairobi') ? 'selected' : ''; ?>>Nairobi</option>
    <option value="nyeri" <?php echo (isset($_POST['Sbranch']) && $_POST['Sbranch'] == 'nyeri') ? 'selected' : ''; ?>>Nyeri</option>
</select>
<br><br>
<label name="tel" id="nm">TELEPHONE NO: </label>
<input type="number" name="tel" id="lb" value="<?php echo isset($_POST['tel']) ? $_POST['tel'] : ''; ?>">
<br><br>
<label name="password" id="nm">AGENT PASSWORD: </label>
<input type="password" name="password" required id="lb">
<br><br>

<input type= "submit" id="luggagesubmit1" name="luggagesubmit1" value ="ADD">
<button type="button" id = "luggagesubmit" class="btn cancel" onclick="closeForm2()">CLOSE</button>


</form>
</div>

<div class="admreport">
    <h3 style="text-align: center; color: darkblue; text-decoration: underline;">REPORTS</h3>
    <a href="reportadmin.php" class="report-link">
        <img src="https://img.freepik.com/premium-photo/chart-graph-paper-financial-account-statistic-business-data-concept_39768-8812.jpg?size=626&ext=jpg" alt="report" class="report">
    </a>
</div>

<?php

//Establishing connection to the database
$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  //die ("connection failed:" . mySqli_connect_error());

}else{
	//echo "connection Successfull";
}

//Creating the agents table 
$sql6 = "CREATE TABLE agentTbl (
  AgentId number(6) PRIMARY KEY,
  FirstName VARCHAR(30) NOT NULL,
  LastName VARCHAR(30) NOT NULL,
  SaccoBranch CHAR(20) NOT NULL,
  TelephoneNo INT(10) NOT NULL
)";

$sql8="ALTER TABLE agentTbl
ADD agentPassword CHAR(20)";
if (mysqli_select_db($conn,"signup") && mysqli_query($conn, $sql8)){

      //printAlert( "Information Added to table");
       //header("location: admin.php");
      }
    else {
        //printAlert("Erro in adding info");

        //echo "ERROR: Could not create Account " .mysqli_error($conn);
  }


if (mysqli_select_db($conn,"signup") && mySqli_query($conn, $sql6)){
    //echo "Table details created successfully";
  }else {
    //echo "Error creating table: " . mySqli_error($conn);
}

function printAlert($message){
    echo "<script>alert('$message')</script>";
  }


 //Getting all the fields from the form
if (isset($_POST["luggagesubmit1"])){

$fname = $_POST['fname'];
$lname = $_POST['lname'];
$branch = $_POST['Sbranch'];
$tel = $_POST['tel'];
$agentPass=$_POST['password'];

$mobileno = '/^07[0-9]{8}/';
$regexname = '/^[A-Z][a-z]{3,10}/';
$regexpass ='/^[A-Z][0-9a-z]{7,20}/';

$agentPass1 = password_hash($agentPass, PASSWORD_DEFAULT);

$sql7 = "INSERT INTO agentTbl(FirstName, LastName, SaccoBranch, TelephoneNo, agentPassword) VALUES
('$fname', '$lname', '$branch','$tel','$agentPass1')";

$sql9= "INSERT INTO  information (UserName, UserPassword, accountStatus) VALUES 
    ('$fname$lname', '$agentPass1' ,'2')";

if (!preg_match($regexname, $fname)){
	printAlert("Not a valid First name");
}

else if(!preg_match($regexname, $lname)){
	printAlert("Not a valid last name");
}

else if (!preg_match($mobileno, $tel)){
	printAlert("Enter a valid mobile number");
}

else if(!preg_match($regexpass, $agentPass)){
	printAlert("Password should start with a capital letter,have a minimum length of 8 but not exceeding 20");
}

else{ 
	if (mysqli_query($conn, $sql7) && mysqli_query($conn,$sql9)){

       printAlert( "Information Added to tables");
       //header("location: admin.php");
      }
    

      else {
        //printAlert("Erro in adding info");

        echo "ERROR: Could not create Account " .mysqli_error($conn);
  }
}




}else if(isset($_POST['viewagt'])){
    header("location: adminreport.php");
}


?>


<script>
function openForm() {
  document.getElementById("myForm").style.display = "block";
}

function closeForm() {
  document.getElementById("myForm").style.display = "none";
}
</script>

<script>
function openForm2() {
  document.getElementById("myForm2").style.display = "block";
}

function closeForm2() {
  document.getElementById("myForm2").style.display = "none";
}
</script>

<?php

$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  die ("connection failed:" . mySqli_connect_error());

}
//echo "connection Successfull";

if(isset($_POST["logout"])){
	header("location: log.php");
}

/*if (isset($_POST["upload"])){
	header ("location: foundlugg.php");
}

else if (isset($_POST["agent"])){
	header("location: addagent.php");

}*/

$servername = "localhost";
$username = "root" ;
$password = "";

$conn = new mysqli($servername , $username , $password);


if (!$conn){
  //die ("connection failed:" . mySqli_connect_error());

}
else{
	//printAlert("connect Successfull");
}

//Creating the Items table 
$sql2 = "CREATE TABLE luggageTbl (
  luggageId int(6) AUTO_INCREMENT PRIMARY KEY,
  ItemName VARCHAR(30) NOT NULL,
  ItemDescription VARCHAR(30) NOT NULL,
  TownLocation CHAR(20) NOT NULL,
  ItemImage text NOT NULL
)";

if (mysqli_select_db($conn,"signup") && mySqli_query($conn, $sql2)){
    //echo "Table details created successfully";
  }else {
    //echo "Error crating table: " . mySqli_error($conn);
  }

// Fetch all agents from the database
$sqlAgent = "SELECT * FROM agentTbl";
$rest = mysqli_query($conn, $sqlAgent);

// Check if there are agents
if (mysqli_num_rows($rest) > 0) {
    ?>
    <!-- Button to reveal delete buttons -->
    

    <!-- Popup to display delete buttons -->
    <div class="popup" id="popup">
        <h2>MANAGE AGENTS</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Sacco Branch </th>
                <th>Delete</th>
            </tr>
            <?php
            // Loop through each agent and display them in a table with delete buttons
            while ($row = mysqli_fetch_assoc($rest)) {
                ?>
                <tr>
                    <td><?php echo $row['AgentId']; ?></td>
                    <td><?php echo $row['FirstName']; ?></td>
                    <td><?php echo $row['LastName']; ?></td>
                    <td><?php echo $row['SaccoBranch']; ?></td>
                    <td>
                        <!-- Form for deleting agent -->
                        <form method="post">
                            <input type="hidden" name="agentId" value="<?php echo $row['AgentId']; ?>">
                            <input type ="hidden" name="confirmDelete" value="yes">
                            <button class="deleteButton" type="submit" name="deleteAgent">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>
    </div>

    <script>
    // JavaScript function to toggle the visibility of the popup
    function togglePopup() {
        var popup = document.getElementById("popup");
        if (popup.style.display === "none") {
            popup.style.display = "block";
        } else {
            popup.style.display = "none";
        }
    }

// JavaScript function to show confirmation dialog before deleting agent
    function confirmDelete() {
        return confirm("Are you sure you want to delete this agent?");
    }
    </script>

    <script>
    // JavaScript function to toggle the visibility of the popup
    function togglePopup2() {
        var popup = document.getElementById("popup2");
        if (popup.style.display === "none") {
            popup.style.display = "block";
        } else {
            popup.style.display = "none";
        }
    }

// JavaScript function to show confirmation dialog before deleting agent
    function confirmDelete2() {
        return confirm("Are you sure you want to delete this Luggage?");
    }
    </script>
    <?php
} else {
    echo "No agents found.";
}

// Function to delete an agent
function deleteAgent($conn, $agent_Id) {
    $SqlDelAgent = "DELETE FROM agentTbl WHERE AgentId = ?";
    $stmt = mysqli_prepare($conn, $SqlDelAgent);
    mysqli_stmt_bind_param($stmt, "i", $agent_Id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Check if the delete button is clicked
if (isset($_POST['deleteAgent'])) {
    // Check if the confirmation is provided
    if (isset($_POST['confirmDelete']) && $_POST['confirmDelete'] === 'yes') {
        // Get the agent ID to be deleted
        $agentId = $_POST['agentId'];
        
        // Call the deleteAgent function to delete the agent
        deleteAgent($conn, $agentId);

        // Redirect to the same page to refresh the agent list
        printAlert("Agent Id $agentId removed successfully");
        //header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    } else {
        // If confirmation is not provided, display a message or perform another action
        echo "<script>alert('Confirmation required to delete agent.');</script>";
    }
}


 
 // Fetch all luggage from the database
$sqlLuggage = "SELECT * FROM luggageTbl";
$result2 = mysqli_query($conn, $sqlLuggage);
if (!$result2) {
    die("Error fetching data: " . mysqli_error($conn));
}

// Check if there are Items
if (mysqli_num_rows($result2) > 0) {
    ?>
    <!-- Popup to display delete buttons -->
     <div class="popup2" id="popup2">
        <h2>REMOVE ITEMS</h2>
        <div class="form-container2">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Image</th>
                        <th>Item Name</th>
                        <th>Item Description</th>
                        <th>Town Location</th>
                        <th>Delete</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row2 = mysqli_fetch_assoc($result2)) { ?>
                        <tr>
                            <td><?php echo $row2['luggageId']; ?></td>
                            <td><img src="uploads/<?php echo $row2['ItemImage']; ?>" alt="<?php echo $row2['ItemName']; ?>" style="width: 100px; height: auto;"></td>
                            <td><?php echo $row2['ItemName']; ?></td>
                            <td><?php echo $row2['ItemDescription']; ?></td>
                            <td><?php echo $row2['TownLocation']; ?></td>
                            <td>
                                <!-- Form for deleting agent -->
                                <form method="post">
                                    <input type="hidden" name="luggageId" value="<?php echo $row2['luggageId']; ?>">
                                    <input type="hidden" name="confirmDelete2" value="yes">
                                    <button class="deleteButton" type="submit" name="deleteLuggage">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } else {
    echo "No luggage found.";
} ?>
</div>


    
    <?php


// Function to delete an item
function deleteLuggage($conn, $luggageId) {
    $sqlDel = "DELETE FROM luggageTbl WHERE luggageId = ?";
    $stmt = mysqli_prepare($conn, $sqlDel);
    mysqli_stmt_bind_param($stmt, "i", $luggageId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Check if the delete button is clicked
if (isset($_POST['deleteLuggage'])) {
    // Check if the confirmation is provided
    if (isset($_POST['confirmDelete2']) && $_POST['confirmDelete2'] === 'yes') {
        // Get the item ID to be deleted
        $luggageId = $_POST['luggageId'];
        
        // Call the deleteLuggage function to delete the item
        deleteLuggage($conn, $luggageId);

        // Redirect to the same page to refresh the item list
        printAlert("Luggage $luggageId Removed from System");

        //header("Location: {$_SERVER['PHP_SELF']}");
        exit;
    } else {
        // If confirmation is not provided, display a message or perform another action
        echo "<script>alert('Confirmation required to delete item.');</script>";
    }
}

?>
<br><br>







</body>
</div>
<footer class="footer">
   <?php include 'footer.php'; ?>
</footer>
</html>






