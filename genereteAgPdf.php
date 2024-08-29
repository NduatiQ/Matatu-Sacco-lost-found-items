<?php 
require_once './dompdf-2.0.4/dompdf/autoload.inc.php';
session_start();
$agent_id = $_SESSION['agentId']; // Corrected variable name
$branch = $_SESSION['branch']; // Corrected variable name

// DB Connection 
$servername = "localhost";
$username = "root";
$password = "";
$database = "signup";
$mysqli = new mysqli($servername, $username, $password, $database);

if ($mysqli->connect_error){
    die ("Connection failed: " . $mysqli->connect_error);
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
$title = "Agent Report For Agent Id:  " .$agent_id. "<br> From:". $start_date . " to " . $end_date;

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
$reports_without_match = $total_reports - $items_found;

// Now, generate the PDF using Dompdf
use Dompdf\Dompdf;

// Create a new Dompdf instance
$dompdf = new Dompdf();

// HTML content for the PDF report
$html = '<html>
<head>
    <title>' . $title . '</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: white; /* Dark blue background */
            color: #ffffff; /* White text */
            padding: 20px;
        }
        h1 {
            color: #3498db; /* Sky blue heading */
        }
        .report-card {
            background-color: #34495e; /* Dark blue report card background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px 0;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #ffffff; /* White report title */
            text-align: center;
            margin-bottom: 20px;
        }
        .info {
            text-align: center;
            color: #ffffff; /* White text */
        }
    </style>
</head>
<body>';
$agentId = $_SESSION['agentId'];

$html .= '<h1>' . $title .'</h1><br>';

 // Title

$html .= '<div class="report-card">
        <h2 class="report-title">Total lost item reports in the stage against the Items found of the reports</h2>
        <div class="info">
            <p>Total Customer Reports: ' . $total_reports . '</p>
            <p>Items Found: ' . $items_found . '</p>
            <p>Items Not Found: ' . $reports_without_match . '</p>
        </div>
    </div>';

$html .= '<div class="report-card">
        <h2 class="report-title">Total Items in the System</h2>
        <div class="info">
            <p>Total Assignments: ' . $total_claims . '</p>
            <p>Collected Items: ' . $collected_claims . '</p>
            <p>Items Not Collected: ' . $not_collected_claims . '</p>
        </div>
    </div>';

$html .= '</body></html>';

// Load HTML content into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render PDF (output to browser)
$dompdf->render();

// Output the generated PDF to the browser
$dompdf->stream("Agent_Report.pdf");
?>