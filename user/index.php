<?php
// CS306 Project - User Homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CS306 Database Project - User Interface</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .section h2 {
            color: #0066cc;
            margin-top: 0;
        }
        .feature-list {
            list-style-type: none;
            padding: 0;
        }
        .feature-list li {
            margin: 10px 0;
            padding: 10px;
            background-color: #f8f9fa;
            border-left: 4px solid #0066cc;
        }
        .feature-list a {
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
        }
        .feature-list a:hover {
            text-decoration: underline;
        }
        .responsible {
            font-size: 0.9em;
            color: #666;
            font-style: italic;
        }
        .support-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            padding: 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .support-link:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>CS306 Database Project</h1>
        <h2 style="text-align: center; color: #666;">User Interface</h2>
        
        <div class="section">
            <h2>Database Triggers</h2>
            <p>Click on any trigger below to test its functionality:</p>
            <ul class="feature-list">
                <li>
                    <a href="trigger_publisher_count.php">Trigger 1: Publisher Game Counter</a><br>
                    <span class="responsible">Responsible: Arda Barış Tonbil</span><br>
                    <small>Description: Automatically updates the number of games when a publisher-game relationship is added</small>
                </li>
                <li>
                    <a href="trigger_requirements_check.php">Trigger 2: Game Requirements Validator</a><br>
                    <span class="responsible">Responsible: Kemal Arda Adışen</span><br>
                    <small>Description: Validates that a game exists before adding system requirements</small>
                </li>
                <li>
                    <a href="trigger_audit_log.php">Trigger 3: Game Update Logger</a><br>
                    <span class="responsible">Responsible: Tuna Mintaş</span><br>
                    <small>Description: Logs all changes made to game information for audit purposes</small>
                </li>
            </ul>
        </div>

        <div class="section">
            <h2>Stored Procedures</h2>
            <p>Click on any stored procedure below to execute it with custom parameters:</p>
            <ul class="feature-list">
                <li>
                    <a href="procedure_game_info.php">Procedure 1: Get Game Information</a><br>
                    <span class="responsible">Responsible: Arda Barış Tonbil</span><br>
                    <small>Description: Retrieves complete game details including publisher and age rating</small>
                </li>
                <li>
                    <a href="procedure_system_requirements.php">Procedure 2: Get System Requirements</a><br>
                    <span class="responsible">Responsible: Kemal Arda Adışen</span><br>
                    <small>Description: Shows detailed system requirements for any game</small>
                </li>
                <li>
                    <a href="procedure_add_game.php">Procedure 3: Add New Game</a><br>
                    <span class="responsible">Responsible: Tuna Mintaş</span><br>
                    <small>Description: Safely adds a new game with validation and publisher linking</small>
                </li>
            </ul>
        </div>

        <a href="support_tickets.php" class="support-link">
            🎫 Support Tickets System
        </a>
        
    </div>
</body>
</html>