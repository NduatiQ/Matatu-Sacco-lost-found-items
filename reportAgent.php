<!DOCTYPE html>
<html lang="en">
<div class="top">
  <br><br>  
</div><br>
<a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AGENT REPORTS Reports</title>
    <link href="styleslugg.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .card {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head><br>
<body>
    
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
        var url = 'genereteAgPdf.php?start_date=' + startDate + '&end_date=' + endDate;
        window.open(url, '_blank');
    }

    // Attach event listener to the "Generate PDF Report" button
    document.getElementById('generate_pdf_button').addEventListener('click', function() {
        generatePDF();
    });
</script>
    

    <?php
    // Start session to access agent's ID (assuming it's stored in a session variable)
    session_start();

    // Check if agent ID is set in session
    if(isset($_SESSION['agentId'])) {
        $agent_id = $_SESSION['agentId'];
        $branch = $_SESSION['branch'];
        $servername = "localhost";
        $username = "root" ;
        $password = "";
        $database = "signup";

        // Establish database connection
        $mysqli = mysqli_connect($servername, $username, $password, $database);

        // Check connection
        if (mysqli_connect_errno()) {
            echo "Failed to connect to MySQL: " . mysqli_connect_error();
            exit();
        }

        // Get start and end dates from the form
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

    // Prepare and execute query to fetch assignment information for the logged-in agent
    $query = "SELECT COUNT(*) AS total_claims, SUM(isCollected) AS collected_claims 
              FROM assignmentTbl 
              WHERE AgentId = ?";
    
    // Add date filtering conditions to the query
    if ($start_date && $end_date) {
        $query .= " AND dateCol BETWEEN ? AND ?";
    }
    
    $stmt = mysqli_prepare($mysqli, $query);
    
    // Bind parameters based on whether start and end dates are provided
    if ($start_date && $end_date) {
        mysqli_stmt_bind_param($stmt, "iss", $agent_id, $start_date, $end_date);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $agent_id);
    }
    
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $total_claims, $collected_claims);
    mysqli_stmt_fetch($stmt);

    // Check if query returned any results
    if($total_claims !== null) {
        $not_collected_claims = $total_claims - $collected_claims;

        // Prepare data for chart
        $chart_data = [
            'collected_claims' => $collected_claims,
            'not_collected_claims' => $not_collected_claims
        ];
    } else {
        echo "No assignment data found for this agent.";
    }
    mysqli_stmt_close($stmt);


        // Query to fetch total reports made in the stage
        $total_reports_query = "SELECT COUNT(*) AS total_reports 
                            FROM claimFormTbl 
                            WHERE SaccoBranch = '$branch'";
    // Add date filtering conditions to the query
    if ($start_date && $end_date) {
        $total_reports_query .= " AND dateCol BETWEEN '$start_date' AND '$end_date'";
    }

    // Execute the query
    $result_total_reports = mysqli_query($mysqli, $total_reports_query);

    // Fetch the total reports count
    $total_reports = 0;
    if ($result_total_reports) {
        $row = mysqli_fetch_assoc($result_total_reports);
        $total_reports = $row['total_reports'];
    } else {
        echo "Error: " . mysqli_error($mysqli);
    }

   

        // Query to fetch total items found in the claim form table
        $items_found_query = "SELECT COUNT(*) AS items_found 
                          FROM claimTbl 
                          WHERE claimFormId IN (SELECT claimFormId FROM claimFormTbl WHERE SaccoBranch = '$branch')";
    // Add date filtering conditions to the query
    if ($start_date && $end_date) {
        $items_found_query .= " AND dateCol BETWEEN '$start_date' AND '$end_date'";
    }

    // Execute the query
    $result_items_found = mysqli_query($mysqli, $items_found_query);

    // Fetch the items found count
    $items_found = 0;
    if ($result_items_found) {
        $row = mysqli_fetch_assoc($result_items_found);
        $items_found = $row['items_found'];
    } else {
        echo "Error: " . mysqli_error($mysqli);
    }

    // Calculate reports without match
    // Assuming $total_reports is already defined
    $reports_without_match = $total_reports - $items_found;

    // Prepare data for histogram
    $histogram_data = [
        'total_reports' => $total_reports,
        'items_found' => $items_found,
        'reports_without_match' => $reports_without_match
    ];

     // Close connection
    mysqli_close($mysqli);
  }
    ?>

    <div class="card">
        <h2>Agent Assignment Report</h2>
        <canvas id="assignmentChart" width="400" height="400"></canvas>
        <p>Total Assignments: <?php echo $total_claims; ?></p>
        <p>Collected: <?php echo $collected_claims; ?></p>
        <p>Not Collected: <?php echo $not_collected_claims; ?></p>
    </div>

    <div class="card">
        <h2>Items Found vs Not Found in <?php echo $branch ?> branch.</h2>
        <canvas id="itemsFoundChart" width="400" height="400"></canvas>
        <p>Total Reports: <?php echo $total_reports; ?></p>
        <p>Items Found: <?php echo $items_found; ?></p>
        <p>Items Not Found: <?php echo $reports_without_match; ?></p>
    </div>

    <script>
        // Pass PHP data to JavaScript
        var chartData = <?php echo json_encode($chart_data); ?>;

        // Chart.js code to render the pie chart
        var ctx = document.getElementById('assignmentChart').getContext('2d');
        var assignmentChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Collected', 'Not Collected'],
                datasets: [{
                    label: 'Assignment Status',
                    data: [chartData.collected_claims, chartData.not_collected_claims],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                title: {
                    display: true,
                    text: 'Agent Assignment Report'
                }
            }
        });

        // Data for the items found histogram
        var itemsFoundData = {
            labels: ["Items Found", "Items Not Found"],
            datasets: [{
                label: 'Items Found vs Not Found',
                data: [<?php echo $items_found; ?>, <?php echo $reports_without_match; ?>],
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
    </script>
</body>
<footer class="footer">
   <?php include 'footer.php'; ?>

</footer>
</html>