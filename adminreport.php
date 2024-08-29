<!Doctype html>
<html>
<div class="top">
  <br><br>  
</div><br>
<a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
<header>
    <link href="footer.css" rel="stylesheet">
    </header>
    <body>
<?php 
//Establishing connection to the database
$servername = "localhost";
$username = "root" ;
$password = "";
$database = "signup";

$conn = new mysqli($servername , $username , $password,$database);


if (!$conn){
  //die ("connection failed:" . mySqli_connect_error());

}else{
    //echo "connection Successfull";
}

// Fetch all agents from the database
// Fetch all agents from the database
$sqlAgents = "SELECT * FROM agentTbl";
$resultAgents = mysqli_query($conn, $sqlAgents);

// Check if agents exist
if (mysqli_num_rows($resultAgents) > 0) {
    // Array to store assigned items for each agent
    $assignedItems = array();

    // Fetch assigned items for all agents
    $sqlAssignedItems = "SELECT * FROM claimTbl";
    $resultAssignedItems = mysqli_query($conn, $sqlAssignedItems);
    
    // Iterate through assigned items and organize them by agentId
    while ($rowAssignedItem = mysqli_fetch_assoc($resultAssignedItems)) {
        $agentId = $rowAssignedItem['agentId'];
        // If agentId key doesn't exist in the $assignedItems array, initialize it
        if (!isset($assignedItems[$agentId])) {
            $assignedItems[$agentId] = array();
        }
        // Push assigned item details into the array under the respective agentId
        $assignedItems[$agentId][] = $rowAssignedItem;
    }


    // Loop through each agent to display their details and assigned items
    while ($rowAgents = mysqli_fetch_assoc($resultAgents)) {
        $agentId = $rowAgents['AgentId'];
        $agentFname = $rowAgents['FirstName'];
        $agentLname = $rowAgents['LastName'];
        $agentBranch = $rowAgents['SaccoBranch'];
        $agentPhone = $rowAgents['TelephoneNo'];

        // Get assigned items for the current agent from the array
        $agentAssignedItems = isset($assignedItems[$agentId]) ? $assignedItems[$agentId] : array();

        // Count collected and not collected items
        $collected_items = 0;
        $not_collected_items = 0;




        // Calculate total assigned items for the agent
    // Calculate total assigned items for the agent
$totalItems = count($agentAssignedItems);

// If agent has assigned items, calculate collected and not collected percentages
// If agent has assigned items, calculate collected and not collected percentages
if ($totalItems > 0) {
    // Iterate through assigned items to count collected and not collected items
    foreach ($agentAssignedItems as $rowAssignedItem) {
        $isCollected = $rowAssignedItem['isCollected'];
        if ($isCollected == 1) {
            $collected_items++;
        } else {
            $not_collected_items++;
        }
    }

    // Calculate collected and not collected percentages
    $collectedPercentage = ($collected_items / $totalItems) * 100; // Percentage
    $notCollectedPercentage = ($not_collected_items / $totalItems) * 100; // Percentage
}
 else {
    // If agent has no assigned items, set percentages to 0
    $collectedPercentage = 0;
    $notCollectedPercentage = 0;
}

        // Display agent card
        ?>
        <div class='agent-container'>
    <div class="agent-card">
        <h2><?php echo $agentFname." ". $agentLname; ?></h2>
        <h3>Agent ID: <?php echo $agentId; ?></h3>
        <p>Phone: <?php echo "0".$agentPhone; ?></p>
        <p>Sacco Branch: <?php echo $agentBranch; ?></p>
        <p>Total Assigned Items: <?php echo count($agentAssignedItems); ?></p>
        <p>Collected Items: <?php echo $collected_items; ?></p>
        <p>Not-Collected Items: <?php echo $not_collected_items; ?></p>
        <div class="chart-container">
            <div class="pie-chart">
                <svg viewBox="0 0 100 100" class="donut">
    <circle cx="50" cy="50" r="40" fill="transparent" stroke="#ffffff" stroke-width="20"></circle>
    <circle cx="50" cy="50" r="40" fill="transparent" stroke="darkblue" stroke-width="20" stroke-dasharray="<?php echo $collectedPercentage ?>, 100"></circle>
    <circle cx="50" cy="50" r="40" fill="transparent" stroke="red" stroke-width="20" stroke-dasharray="<?php echo $notCollectedPercentage ?>, 100"></circle>
</svg>
                <div class="legend">
                    <span class="collected"></span> Collected
                    <span class="not-collected"></span> Not Collected
                </div>
            </div>
        </div>
        <br><br>
        <button class="view-items-btn" onclick="toggleItems(<?php echo $agentId; ?>)">View Items</button>
    

    <!-- Agent Items - Initially hidden -->
   
        <!-- This is where you will display the items assigned to the agent -->
    </div>
</div>
 <div id="agentItems_<?php echo $agentId; ?>" class="agent-items" style="display: none;">
                <!-- Display items for the agent here -->
                <?php
                foreach ($agentAssignedItems as $rowAssignedItem) {
                    $itemId = $rowAssignedItem['luggageId'];
                    $isCollected = $rowAssignedItem['isCollected'];

                    // Fetch customer details for the assigned item
                    $sql_customer_details = "SELECT * FROM customerDetails WHERE CustomerId = '{$rowAssignedItem['custId']}'";
                    $result_customer_details = mysqli_query($conn, $sql_customer_details);
                    $row_customer_details = mysqli_fetch_assoc($result_customer_details);
                    $customerFname = $row_customer_details['FirstName'];
                    $customerLname = $row_customer_details['LastName'];
                    $custTel = $row_customer_details['TelephoneNo'];
                    $custAdd = $row_customer_details['EmailAddress'];

                    // Display item details and associated customer details
                    ?>
                    <div class="item">
                        <p>Item ID: <?php echo $itemId; ?></p>
                        <p>Collected: <?php echo ($isCollected == 1 ? 'Yes' : 'No'); ?></p>
                        <p>Customer Name: <?php echo $customerFname . " " . $customerLname; ?></p>
                        <p>Customer Phone: <?php echo $custTel; ?></p>
                        <p>Customer Address: <?php echo $custAdd; ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <?php
    }
} else {
    echo "No agents found.";
}
?>

</div>

<!-- Script for viewing items -->
<script>
    function toggleItems(agentId) {
        var agentItemsDiv = document.getElementById('agentItems_' + agentId);
        if (agentItemsDiv.style.display === 'none') {
            agentItemsDiv.style.display = 'block';
        } else {
            agentItemsDiv.style.display = 'none';
        }
    }
</script>
</body>
</html>