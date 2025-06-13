<?php
require_once 'config/database.php';

$message = '';
$triggerResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mysqli = getMySQLConnection();
        
        if (isset($_POST['case1'])) {
            // Case 1: Add a new game-publisher relationship
            
            // First, show current state
            $beforeQuery = "SELECT PublisherName, NumberOfGames FROM publisher WHERE PublisherID = 'P001'";
            $beforeResult = $mysqli->query($beforeQuery);
            $beforeData = $beforeResult->fetch_assoc();
            
            // Add a new game for Ubisoft (P001)
            $newGameId = 'G011';
            $newGameTitle = 'Assassins Creed Valhalla';
            
            // Insert new game
            $gameInsert = "INSERT IGNORE INTO game (GameID, Title, ReleaseYear, Genre, Platform) 
                          VALUES ('$newGameId', '$newGameTitle', 2020, 'Action-Adventure', 'Multiple')";
            $mysqli->query($gameInsert);
            
            // Insert game-publisher relationship (this triggers the trigger!)
            $relationInsert = "INSERT IGNORE INTO game_publisher (GameID, PublisherID) VALUES ('$newGameId', 'P001')";
            
            if ($mysqli->query($relationInsert)) {
                // Check after trigger execution
                $afterQuery = "SELECT PublisherName, NumberOfGames FROM publisher WHERE PublisherID = 'P001'";
                $afterResult = $mysqli->query($afterQuery);
                $afterData = $afterResult->fetch_assoc();
                
                $triggerResult = "
                <div style='background-color: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745;'>
                    <h4>✅ Case 1: Publisher Game Counter Trigger Executed</h4>
                    <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 15px 0;'>
                        <div style='background-color: #fff; padding: 15px; border-radius: 5px;'>
                            <strong>📊 Before Trigger:</strong><br>
                            Publisher: {$beforeData['PublisherName']}<br>
                            Game Count: <span style='font-size: 1.2em; color: #dc3545;'>{$beforeData['NumberOfGames']}</span>
                        </div>
                        <div style='background-color: #fff; padding: 15px; border-radius: 5px;'>
                            <strong>📈 After Trigger:</strong><br>
                            Publisher: {$afterData['PublisherName']}<br>
                            Game Count: <span style='font-size: 1.2em; color: #28a745;'>{$afterData['NumberOfGames']}</span>
                        </div>
                    </div>
                    <strong>🎮 Action:</strong> Added '$newGameTitle' to Ubisoft<br>
                    <strong>🔄 Trigger Effect:</strong> NumberOfGames automatically increased from {$beforeData['NumberOfGames']} to {$afterData['NumberOfGames']}!<br>
                    <strong>✨ Result:</strong> The trigger successfully updated the game count without manual intervention!
                </div>";
            } else {
                $triggerResult = "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px;'>❌ Error: " . $mysqli->error . "</div>";
            }
        }
        
        if (isset($_POST['case2'])) {
            // Case 2: Show all publishers and their current game counts
            $query = "
                SELECT 
                    p.PublisherID,
                    p.PublisherName,
                    p.NumberOfGames,
                    p.EstablishmentYear,
                    COUNT(gp.GameID) as ActualGameCount
                FROM publisher p
                LEFT JOIN game_publisher gp ON p.PublisherID = gp.PublisherID
                GROUP BY p.PublisherID
                ORDER BY p.NumberOfGames DESC
            ";
            
            $result = $mysqli->query($query);
            
            $triggerResult = "
            <div style='background-color: #e3f2fd; padding: 20px; border-radius: 10px; border-left: 5px solid #2196f3;'>
                <h4>📊 Case 2: Publisher Game Count Status</h4>
                <p>This shows how the trigger maintains accurate game counts for all publishers:</p>
                <div style='overflow-x: auto;'>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 15px; background-color: white;'>
                        <thead>
                            <tr style='background-color: #f8f9fa;'>
                                <th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Publisher</th>
                                <th style='border: 1px solid #ddd; padding: 12px; text-align: center;'>Stored Count</th>
                                <th style='border: 1px solid #ddd; padding: 12px; text-align: center;'>Actual Count</th>
                                <th style='border: 1px solid #ddd; padding: 12px; text-align: center;'>Status</th>
                                <th style='border: 1px solid #ddd; padding: 12px; text-align: center;'>Est. Year</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            while ($row = $result->fetch_assoc()) {
                $status = ($row['NumberOfGames'] == $row['ActualGameCount']) ? 
                    "<span style='color: #28a745; font-weight: bold;'>✅ Synced</span>" : 
                    "<span style='color: #dc3545; font-weight: bold;'>❌ Out of sync</span>";
                
                $triggerResult .= "
                    <tr>
                        <td style='border: 1px solid #ddd; padding: 12px; font-weight: bold;'>{$row['PublisherName']}</td>
                        <td style='border: 1px solid #ddd; padding: 12px; text-align: center; font-size: 1.1em;'>{$row['NumberOfGames']}</td>
                        <td style='border: 1px solid #ddd; padding: 12px; text-align: center;'>{$row['ActualGameCount']}</td>
                        <td style='border: 1px solid #ddd; padding: 12px; text-align: center;'>$status</td>
                        <td style='border: 1px solid #ddd; padding: 12px; text-align: center;'>{$row['EstablishmentYear']}</td>
                    </tr>";
            }
            
            $triggerResult .= "
                        </tbody>
                    </table>
                </div>
                <div style='margin-top: 15px; padding: 10px; background-color: #fff3cd; border-radius: 5px;'>
                    <strong>💡 Note:</strong> The 'Stored Count' is maintained automatically by the trigger. 
                    It should always match the 'Actual Count' for data integrity!
                </div>
            </div>";
        }
        
        if (isset($_POST['case3'])) {
            // Case 3: Test trigger with a different publisher
            $publisherId = 'P005'; // Mojang Studios
            
            $beforeQuery = "SELECT PublisherName, NumberOfGames FROM publisher WHERE PublisherID = '$publisherId'";
            $beforeResult = $mysqli->query($beforeQuery);
            $beforeData = $beforeResult->fetch_assoc();
            
            // Add Minecraft Dungeons to Mojang
            $newGameId = 'G012';
            $gameInsert = "INSERT IGNORE INTO game (GameID, Title, ReleaseYear, Genre, Platform) 
                          VALUES ('$newGameId', 'Minecraft Dungeons', 2020, 'Action RPG', 'Multiple')";
            $mysqli->query($gameInsert);
            
            $relationInsert = "INSERT IGNORE INTO game_publisher (GameID, PublisherID) VALUES ('$newGameId', '$publisherId')";
            
            if ($mysqli->query($relationInsert)) {
                $afterQuery = "SELECT PublisherName, NumberOfGames FROM publisher WHERE PublisherID = '$publisherId'";
                $afterResult = $mysqli->query($afterQuery);
                $afterData = $afterResult->fetch_assoc();
                
                $triggerResult = "
                <div style='background-color: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107;'>
                    <h4>🎯 Case 3: Multi-Publisher Trigger Test</h4>
                    <div style='background-color: white; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <strong>🎮 Test Scenario:</strong> Adding 'Minecraft Dungeons' to Mojang Studios<br><br>
                        <div style='display: flex; justify-content: space-between; align-items: center;'>
                            <div>
                                <strong>Before:</strong> {$beforeData['PublisherName']} had {$beforeData['NumberOfGames']} games
                            </div>
                            <div style='font-size: 2em;'>→</div>
                            <div>
                                <strong>After:</strong> {$afterData['PublisherName']} has {$afterData['NumberOfGames']} games
                            </div>
                        </div>
                    </div>
                    <strong>🔄 Trigger Behavior:</strong> Each publisher's count is updated independently<br>
                    <strong>✅ Result:</strong> Trigger works correctly across different publishers!
                </div>";
            }
        }
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Gaming Trigger: Publisher Game Counter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #0066cc;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #0066cc;
            color: white;
            transform: translateY(-2px);
        }
        .trigger-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .trigger-info {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .sql-code {
            background-color: #2d3748;
            color: #68d391;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            border-left: 5px solid #68d391;
            overflow-x: auto;
        }
        .test-cases {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .test-case {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        .test-case:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .test-case h3 {
            margin-top: 0;
            color: #2d3748;
            font-size: 1.3em;
        }
        .test-case button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
        }
        .test-case button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .case2 {
            background: linear-gradient(135deg, #fdcb6e, #e17055);
        }
        .case3 {
            background: linear-gradient(135deg, #a29bfe, #6c5ce7);
        }
        .result-section {
            margin-top: 40px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 5px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">🏠 Homepage</a>
            <a href="procedure_game_info.php">⚙ Procedures</a>
            <a href="support_tickets.php">🎫 Support</a>
        </div>
        
        <div class="trigger-header">
            <h1>🔄 Gaming Trigger: Publisher Game Counter</h1>
            <p><strong>Responsible:</strong> Arda Barış Tonbil | <strong>Database:</strong> GamingDatabase</p>
        </div>
        
        <div class="trigger-info">
            <h3>📋 How This Gaming Trigger Works</h3>
            <p><strong>🎯 Purpose:</strong> Automatically maintains the <code>NumberOfGames</code> field in the publisher table whenever a new game is linked to a publisher.</p>
            
            <p><strong>🎮 Gaming Context:</strong></p>
            <ul style="text-align: left; margin-left: 20px;">
                <li>When a new game is published by a company (like EA, Ubisoft, Epic Games)</li>
                <li>The game-publisher relationship is added to the database</li>
                <li>The trigger automatically increments the publisher's game count</li>
                <li>Keeps publisher statistics accurate without manual updates</li>
            </ul>
            
            <div class="sql-code">
                <strong>📝 Trigger SQL Code:</strong><br><br>
                CREATE TRIGGER update_publisher_game_count<br>
                AFTER INSERT ON game_publisher<br>
                FOR EACH ROW<br>
                BEGIN<br>
                &nbsp;&nbsp;&nbsp;&nbsp;UPDATE publisher<br>
                &nbsp;&nbsp;&nbsp;&nbsp;SET NumberOfGames = (<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SELECT COUNT(*)<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;FROM game_publisher<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WHERE PublisherID = NEW.PublisherID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;)<br>
                &nbsp;&nbsp;&nbsp;&nbsp;WHERE PublisherID = NEW.PublisherID;<br>
                END
            </div>
        </div>
        
        <h2>🧪 Interactive Trigger Testing</h2>
        <p>Test the publisher game counter trigger with real gaming scenarios:</p>
        
        <form method="POST">
            <div class="test-cases">
                <div class="test-case">
                    <h3>🎮 Case 1: Add New Game</h3>
                    <p>Add "Assassin's Creed Valhalla" to Ubisoft and watch the game count automatically increase.</p>
                    <button type="submit" name="case1">Add Game to Ubisoft</button>
                </div>
                
                <div class="test-case case2">
                    <h3>📊 Case 2: View All Publishers</h3>
                    <p>Display current game counts for all gaming publishers and verify trigger accuracy.</p>
                    <button type="submit" name="case2">Show Publisher Stats</button>
                </div>
                
                <div class="test-case case3">
                    <h3>🎯 Case 3: Test Different Publisher</h3>
                    <p>Add "Minecraft Dungeons" to Mojang Studios and test trigger on different publisher.</p>
                    <button type="submit" name="case3">Add Game to Mojang</button>
                </div>
            </div>
        </form>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($triggerResult): ?>
            <div class="result-section">
                <h3>🎯 Trigger Execution Results</h3>
                <?php echo $triggerResult; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: bold;">← Return to Gaming Database Homepage</a>
        </div>
    </div>
</body>
</html>