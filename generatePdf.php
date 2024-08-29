<?php 
require_once './dompdf-2.0.4/dompdf/autoload.inc.php';

// DB Connection 
$servername = "localhost";
$username = "root";
$password = "";
$database = "signup";
$conn = new mysqli($servername, $username, $password, $database);

if (!$conn){
    // die ("connection failed:" . mySqli_connect_error());
} else {
    // echo "connection Successful";
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
$title = "Admin Report - " . $start_date . " to " . $end_date;
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

$html .= '<h1>' . $title . '</h1>'; // Title

$html .= '<div class="report-card">
        <h2 class="report-title">Items Found vs Not Found</h2>
        <div class="info">
            <p>Total Customer Reports: ' . $totalReports . '</p>
            <p>Items Found: ' . $items_found_count . '</p>
            <p>Items Not Found: ' . $items_not_found_count . '</p>
        </div>
    </div>';

$html .= '<div class="report-card">
        <h2 class="report-title">Total Items in the System</h2>
        <div class="info">
            <p>Total Items: ' . $total_items . '</p>
            <p>Claimed Items: ' . $claimed_items . '</p>
            <p>Not Claimed Items: ' . $not_claimed_items . '</p>
        </div>
    </div>';

$html .= '<div class="report-card">
        <h2 class="report-title">Items Collected vs Not Collected</h2>
        <div class="info">
            <p>Total Claims: ' . $total_claims . '</p>
            <p>Total Items Collected: ' . $total_collected_items . '</p>
            <p>Total Items Not Collected: ' . $total_not_collected_items . '</p>
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
$dompdf->stream("admin_report.pdf");
?>