<?php 
// DB Connection 
$servername = "localhost";
$username = "root";
$password = "";
$database = "signup";
$conn = new mysqli($servername, $username, $password, $database);

if (!$conn){
    //die ("connection failed:" . mySqli_connect_error());
} else {
    //echo "connection Successful";
}

// Function to sanitize user input
function sanitize_input($conn, $data) {
    return mysqli_real_escape_string($conn, trim($data));
}

// Function to format date input
function format_date($date) {
    return date("Y-m-d", strtotime($date));
}

// Get start and end dates from form submission
$start_date = isset($_GET['start_date']) ? format_date($_GET['start_date']) : '1970-01-01';
$end_date = isset($_GET['end_date']) ? format_date($_GET['end_date']) : date('Y-m-d');

// Retrieve count of items found
$sql_found = "SELECT COUNT(*) AS found_count FROM claimTbl WHERE (claimFormId IS NOT NULL AND dateCol BETWEEN '$start_date' AND '$end_date') OR (claimFormId IS NULL AND dateCol IS NULL)";
$result_found = mysqli_query($conn, $sql_found);
$row_found = mysqli_fetch_assoc($result_found);
$items_found_count = $row_found['found_count'];

// Retrieve count of items not found
$sql_not_found = "SELECT COUNT(*) AS not_found_count FROM claimFormTbl WHERE (claimId NOT IN (SELECT DISTINCT claimFormId FROM claimTbl) AND dateCol BETWEEN '$start_date' AND '$end_date') OR (dateCol IS NULL)";
$result_not_found = mysqli_query($conn, $sql_not_found);
$row_not_found = mysqli_fetch_assoc($result_not_found);
$items_not_found_count = $row_not_found['not_found_count'];

// Calculate percentages
$totalReports = $items_found_count + $items_not_found_count;
$percentage_found = ($items_found_count / $totalReports) * 100;
$percentage_not_found = ($items_not_found_count / $totalReports) * 100;

// Retrieve count of items claimed and not claimed from luggagetbl
$sql_total_items = "SELECT is_claimed, COUNT(*) AS total_count FROM luggagetbl WHERE (dateCol BETWEEN '$start_date' AND '$end_date') OR (dateCol IS NULL) GROUP BY is_claimed";
$result_total_items = mysqli_query($conn, $sql_total_items);

// Prepare data for pie chart and display
$total_items_data = array();
$total_items = 0;
$claimed_items = 0;
$not_claimed_items = 0;

while ($row = mysqli_fetch_assoc($result_total_items)) {
    $is_claimed = $row['is_claimed'] ? 'Claimed' : 'Not Claimed';
    $total_items_data[$is_claimed] = $row['total_count'];
    $total_items += $row['total_count'];
    if ($row['is_claimed']) {
        $claimed_items += $row['total_count'];
    } else {
        $not_claimed_items += $row['total_count'];
    }
}

// Retrieve total number of claims
$sql_total_claims = "SELECT COUNT(*) AS total_claims FROM claimTbl WHERE (dateCol BETWEEN '$start_date' AND '$end_date') OR (dateCol IS NULL)";
$result_total_claims = mysqli_query($conn, $sql_total_claims);
$row_total_claims = mysqli_fetch_assoc($result_total_claims);
$total_claims = $row_total_claims['total_claims'];

// Retrieve count of items collected and not collected from claimTbl
$sql_collected_items = "SELECT isCollected, COUNT(*) AS total_count FROM claimTbl WHERE (dateCol BETWEEN '$start_date' AND '$end_date') OR (dateCol IS NULL) GROUP BY isCollected";
$result_collected_items = mysqli_query($conn, $sql_collected_items);

// Prepare data for pie chart
$collected_items_data = array();
$total_collected_items = 0;
$total_not_collected_items = 0;

while ($row = mysqli_fetch_assoc($result_collected_items)) {
    $is_collected = $row['isCollected'] ? 'Collected' : 'Not Collected';
    $collected_items_data[$is_collected] = $row['total_count'];
    if ($row['isCollected']) {
        $total_collected_items += $row['total_count'];
    } else {
        $total_not_collected_items += $row['total_count'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Reports</title>
<link href="styleslugg.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>

    
    /* Date UI styling */
    .date-form label {
        color: black; /* Set label color to white */
    }

    .date-form input[type="date"] {
        width: 200px;
        margin: 5px;
        padding: 8px;
        border: 2px solid darkblue; /* Set border color */
        border-radius: 5px;
        background-color: skyblue; /* Set background color */
        color: black; /* Set text color */
    }

    /* Button styling */
    .date-form button {
        padding: 10px 20px;
        margin: 10px;
        border: none;
        border-radius: 5px;
        background-color: darkblue; /* Set background color */
        color: black; /* Set text color */
        cursor: pointer;
    }

    /* Apply button */
    .date-form button[type="submit"] {
        background-color: skyblue; /* Set background color */
        cursor: pointer;
    }

    .date-form button[type="submit"]:hover {
        background-color: darkblue; /* Set background color */
    }

    /* Generate PDF button */
    #generate_pdf_button {
        background-color: white; /* Set background color */
        cursor: pointer;
    }

    #generate_pdf_button:hover {
        background-color: skyblue; /* Set background color */
    }

    .report-card {
        background-color: #f9f9f9;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin: 20px;
    }

    .report-title {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
    }

    .chart-container {
        width: 100%;
        max-width: 600px;
        margin: auto;
    }

    .info {
        text-align: center;
    }

    .date-form {
        text-align: center;
        margin-bottom: 20px;
    }

    .date-form input[type="date"] {
        width: 200px;
        margin: 5px;
    }
</style>
</head>

<body>
    <div class="top">
        <br><br>  
    </div><br>
    <a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    <form class="date-form" method="GET" action="">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date">
    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date">
    <button type="submit">Apply</button>
</form>
<button id="generate_pdf_button">Generate PDF Report</button>

<script>
    function generatePDF() {
        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;
        var url = 'generatePdf.php?start_date=' + startDate + '&end_date=' + endDate;
        window.open(url, '_blank');
    }

    // Attach event listener to the "Generate PDF Report" button
    document.getElementById('generate_pdf_button').addEventListener('click', function() {
        generatePDF();
    });
</script>

    <div class="report-card">
        <h2 class="report-title">Items Found vs Not Found</h2>
        <div class="chart-container">
            <canvas id="itemsFoundChart" width="400" height="200"></canvas>
        </div>
        <div class="info">
            <p>Total Customer Reports: <?php echo $totalReports; ?></p>
            <p>Items Found: <?php echo $items_found_count; ?></p>
            <p>Items Not Found: <?php echo $items_not_found_count; ?></p>
        </div>
    </div>

    <div class="report-card">
        <h2 class="report-title">Total Items in the System</h2>
        <div class="chart-container">
            <canvas id="totalItemsChart" width="400" height="200"></canvas>
        </div>
        <div class="info">
            <p>Total Items: <?php echo $total_items; ?></p>
            <p>Claimed Items: <?php echo $claimed_items; ?></p>
            <p>Not Claimed Items: <?php echo $not_claimed_items; ?></p>
        </div>
    </div>

    <div class="report-card">
        <h2 class="report-title">Items Collected vs Not Collected</h2>
        <div class="chart-container">
            <canvas id="itemsCollectedChart" width="400" height="200"></canvas>
        </div>
        <div class="info">
            <p>Total Claims: <?php echo $total_claims; ?></p>
            <p>Total Items Collected: <?php echo $total_collected_items; ?></p>
            <p>Total Items Not Collected: <?php echo $total_not_collected_items; ?></p>
        </div>
    </div>

    <!-- Include Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 

    <script>
        // Data for the items found histogram
        var itemsFoundData = {
            labels: ["Items Found", "Items Not Found"],
            datasets: [{
                label: 'Items Found vs Not Found',
                data: [<?php echo $items_found_count; ?>, <?php echo $items_not_found_count; ?>],
                backgroundColor: ['#66b3ff', '#ff9999'],
            }]
        };

        // Create the items found histogram
        var itemsFoundChart = new Chart(document.getElementById('itemsFoundChart'), {
            type: 'bar',
            data: itemsFoundData,
            options: {
                responsive: false,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                },
                title: {
                    display: true,
                    text: 'Items Found vs Not Found'
                }
            }
        });

        // Data for the total items pie chart
        var totalItemsData = {
            labels: ["Claimed Items", "Not Claimed Items"],
            datasets: [{
                label: 'Total Items in the System',
                data: [<?php echo $claimed_items; ?>, <?php echo $not_claimed_items; ?>],
                backgroundColor: ['#66b3ff', '#ff9999'],
            }]
        };

        // Create the total items pie chart
        var totalItemsChart = new Chart(document.getElementById('totalItemsChart'), {
            type: 'pie',
            data: totalItemsData,
            options: {
                responsive: false,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: 'Total Items in the System (Claimed vs Not Claimed)'
                }
            }
        });

        // Data for the items collected pie chart
        var itemsCollectedData = {
            labels: ["Items Collected", "Items Not Collected"],
            datasets: [{
                label: 'Items Collected vs Not Collected',
                data: [<?php echo $total_collected_items; ?>, <?php echo $total_not_collected_items; ?>],
                backgroundColor: ['#66b3ff', '#ff9999'],
            }]
        };

        // Create the items collected pie chart
        var itemsCollectedChart = new Chart(document.getElementById('itemsCollectedChart'), {
            type: 'pie',
            data: itemsCollectedData,
            options: {
                responsive: false,
                maintainAspectRatio: false,
                title: {
                    display: true,
                    text: 'Items Collected vs Not Collected'
                }
            }
        });
    </script> 

</body>
<br>
<footer class="footer">
   <?php include 'footer.php'; ?>
</footer>

</html>


<?php
// Close the connection
mysqli_close($conn);
?>