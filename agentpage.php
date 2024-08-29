<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link href="styleslugg.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="top">
        <br><br>  
    </div>
    <a href="logout.php" class="logout-link"><i class="fas fa-power-off"></i> Logout</a>
    <header>
        <h1>Welcome Sacco Agent <?php echo $fnameLname; ?> &nbsp; <img src="images\wave.svg" width="40"><br>AGENT ID: &nbsp;<?php echo $AgentId;?></h1>
    </header>
    <div class="agentpage">
        <div class="agentdashboard">
            <p1 style="text-decoration: underline;">DASHBOARD</p1>
            <br><br>
            <button type="button" name="upload" onclick="openForm()">UPLOAD LUGGAGE &nbsp;&nbsp;<i class="fas fa-suitcase"></i></button>
        </div>
        <div class="form-popup" id="myForm">
            <!-- Form for uploading luggage -->
        </div>
        
