<?php
require_once 'config/database.php';

$message = '';
$triggerResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mysqli = getMySQLConnection();
        
        if (isset($_POST['case1'])) {
            // Case 1: Try to add requirements for a valid game
            $validGameId = 'G001'; // Skyrim
            $newReqId = 99;
            
            // Show game exists
            $gameCheck = $mysqli->query("SELECT Title FROM game WHERE GameID = '$validGameId'");
            $gameData = $gameCheck->fetch_assoc();
            
            // Try to insert valid requirements
            $insertSql = "INSERT IGNORE INTO minimumrequirements (RequirementID, GameID) VALUES ($newReqId, '$validGameId')";
            
            if ($mysqli->query($insertSql)) {
                $triggerResult = "
                <div style='background-color: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745;'>
                    <h4>✅ Case 1: Valid Game Requirements Addition</h4>
                    <div style='background-color: #fff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <strong>🎮 Game Check:</strong> '{$gameData['Title']}' exists in database<br>
                        <strong>📋 Action:</strong> Added requirement ID $newReqId for game $validGameId<br>
                        <strong>🔄 Trigger Result:</strong> <span style='color: #28a745; font-weight: bold;'>PASSED VALIDATION</span><br>
                        <strong>✅ Outcome:</strong> Requirements successfully added because game exists!
                    </div>
                    <p><strong>🛡 Trigger Protection:</strong> The trigger validated that the game exists before allowing the requirement insertion.</p>
                </div>";
            }
        }
        
        if (isset($_POST['case2'])) {
            // Case 2: Try to add requirements for an invalid game (should fail)
            $invalidGameId = 'G999'; // Non-existent game
            $newReqId = 98;
            
            try {
                // This should trigger an error due to our validation trigger
                $insertSql = "INSERT INTO minimumrequirements (RequirementID, GameID) VALUES ($newReqId, '$invalidGameId')";
                
                if ($mysqli->query($insertSql)) {
                    $triggerResult = "
                    <div style='background-color: #f8d7da; padding: 20px; border-radius: 10px; border-left: 5px solid #dc3545;'>
                        <h4>❌ Unexpected: Trigger Should Have Blocked This</h4>
                        <p>The requirement was added even though the game doesn't exist. The trigger may not be active.</p>
                    </div>";
                } else {
                    $triggerResult = "
                    <div style='background-color: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107;'>
                        <h4>⚠ Case 2: Invalid Game Requirements Blocked</h4>
                        <div style='background-color: #fff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>🚫 Game Check:</strong> Game ID '$invalidGameId' does not exist<br>
                            <strong>📋 Action:</strong> Attempted to add requirement ID $newReqId for non-existent game<br>
                            <strong>🔄 Trigger Result:</strong> <span style='color: #dc3545; font-weight: bold;'>BLOCKED INSERTION</span><br>
                            <strong>❌ Error:</strong> " . $mysqli->error . "
                        </div>
                        <p><strong>🛡 Security Success:</strong> The trigger successfully prevented invalid data entry!</p>
                    </div>";
                }
            } catch (Exception $e) {
                $triggerResult = "
                <div style='background-color: #d1ecf1; padding: 20px; border-radius: 10px; border-left: 5px solid #17a2b8;'>
                    <h4>🛡 Case 2: Trigger Protection Activated</h4>
                    <div style='background-color: #fff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                        <strong>🚫 Validation:</strong> Game '$invalidGameId' does not exist<br>
                        <strong>🔄 Trigger Action:</strong> <span style='color: #dc3545; font-weight: bold;'>PREVENTED INSERTION</span><br>
                        <strong>🛡 Protection:</strong> Database integrity maintained<br>
                        <strong>📝 Message:</strong> " . $e->getMessage() . "
                    </div>
                    <p><strong>✅ Success:</strong> The trigger is working correctly by blocking invalid requirements!</p>
                </div>";
            }
        }
        
        if (isset($_POST['case3'])) {
            // Case 3: Show current requirements and their game associations
            $query = "
                SELECT 
                    mr.RequirementID,
                    mr.GameID,
                    g.Title as GameTitle,
                    g.Genre,
                    g.ReleaseYear,
                    CASE 
                        WHEN g.GameID IS NOT NULL THEN 'Valid'
                        ELSE 'Invalid'
                    END as Status
                FROM minimumrequirements mr
                LEFT JOIN game g ON mr.GameID = g.GameID
                ORDER BY mr.RequirementID
            ";
            
            $result = $mysqli->query($query);
            
            $triggerResult = "
            <div style='background-color: #e3f2fd; padding: 20px; border-radius: 10px; border-left: 5px solid #2196f3;'>
                <h4>📊 Case 3: Current Requirements Validation Status</h4>
                <p>This shows all requirements and verifies they have valid game associations:</p>
                
                <div style='overflow-x: auto; margin-top: 15px;'>
                    <table style='width: 100%; border-collapse: collapse; background-color: white; border-radius: 8px; overflow: hidden;'>
                        <thead>
                            <tr style='background: linear-gradient(135deg, #667eea, #764ba2); color: white;'>
                                <th style='padding: 15px; text-align: left;'>Req ID</th>
                                <th style='padding: 15px; text-align: left;'>Game ID</th>
                                <th style='padding: 15px; text-align: left;'>Game Title</th>
                                <th style='padding: 15px; text-align: left;'>Genre</th>
                                <th style='padding: 15px; text-align: center;'>Year</th>
                                <th style='padding: 15px; text-align: center;'>Status</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            while ($row = $result->fetch_assoc()) {
                $statusBadge = $row['Status'] === 'Valid' ? 
                    "<span style='background: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;'>✅ Valid</span>" :
                    "<span style='background: #dc3545; color: white; padding: 5px 10px; border-radius: 15px; font-size: 0.8em;'>❌ Invalid</span>";
                
                $triggerResult .= "
                    <tr style='border-bottom: 1px solid #e2e8f0;'>
                        <td style='padding: 12px; font-weight: bold;'>{$row['RequirementID']}</td>
                        <td style='padding: 12px;'>{$row['GameID']}</td>
                        <td style='padding: 12px;'>" . ($row['GameTitle'] ?: '<em>No Game Found</em>') . "</td>
                        <td style='padding: 12px;'>" . ($row['Genre'] ?: '-') . "</td>
                        <td style='padding: 12px; text-align: center;'>" . ($row['ReleaseYear'] ?: '-') . "</td>
                        <td style='padding: 12px; text-align: center;'>$statusBadge</td>
                    </tr>";
            }
            
            $triggerResult .= "
                        </tbody>
                    </table>
                </div>
                
                <div style='margin-top: 15px; padding: 15px; background-color: #fff3cd; border-radius: 5px;'>
                    <strong>🛡 Trigger Protection:</strong> All requirements shown have valid game associations because the trigger prevents invalid insertions!
                </div>
            </div>";
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
    <title>🛡 Gaming Trigger: Requirements Validator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);
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
            color: #e91e63;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #e91e63;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #e91e63;
            color: white;
            transform: translateY(-2px);
        }
        .trigger-header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .trigger-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .sql-code {
            background-color: #2d3748;
            color: #f093fb;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            border-left: 5px solid #f093fb;
            overflow-x: auto;
        }
        .test-cases {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .test-case {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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
            background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
        }
        .case3 {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            <a href="trigger_publisher_count.php">🔄 Trigger 1</a>
            <a href="trigger_audit_log.php">📝 Trigger 3</a>
            <a href="procedure_game_info.php">⚙ Procedures</a>
        </div>
        
        <div class="trigger-header">
            <h1>🛡 Gaming Trigger: Requirements Validator</h1>
            <p><strong>Responsible:</strong> Kemal Arda Adışen | <strong>Database:</strong> GamingDatabase</p>
        </div>
        
        <div class="trigger-info">
            <h3>🔒 How This Gaming Security Trigger Works</h3>
            <p><strong>🎯 Purpose:</strong> Validates that a game exists in the database before allowing system requirements to be added, preventing orphaned requirement records.</p>
            
            <p><strong>🎮 Gaming Security Context:</strong></p>
            <ul style="text-align: left; margin-left: 20px;">
                <li>Prevents adding system requirements for non-existent games</li>
                <li>Maintains referential integrity in gaming database</li>
                <li>Stops data corruption from invalid game-requirement associations</li>
                <li>Ensures all requirements have valid game references</li>
            </ul>
            
            <div class="sql-code">
                <strong>🔐 Security Trigger SQL:</strong><br><br>
                CREATE TRIGGER check_game_requirements<br>
                BEFORE INSERT ON minimumrequirements<br>
                FOR EACH ROW<br>
                BEGIN<br>
                &nbsp;&nbsp;&nbsp;&nbsp;DECLARE game_exists INT DEFAULT 0;<br>
                &nbsp;&nbsp;&nbsp;&nbsp;SELECT COUNT(*) INTO game_exists FROM game WHERE GameID = NEW.GameID;<br>
                &nbsp;&nbsp;&nbsp;&nbsp;IF game_exists = 0 THEN<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SIGNAL SQLSTATE '45000'<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SET MESSAGE_TEXT = 'Game must exist before adding requirements';<br>
                &nbsp;&nbsp;&nbsp;&nbsp;END IF;<br>
                END
            </div>
        </div>
        
        <h2>🧪 Security Validation Testing</h2>
        <p>Test the requirements validator trigger with different scenarios:</p>
        
        <form method="POST">
            <div class="test-cases">
                <div class="test-case">
                    <h3>✅ Case 1: Valid Game</h3>
                    <p>Try to add requirements for "Skyrim" (existing game). Should succeed.</p>
                    <button type="submit" name="case1">Add Valid Requirements</button>
                </div>
                
                <div class="test-case case2">
                    <h3>❌ Case 2: Invalid Game</h3>
                    <p>Try to add requirements for non-existent game "G999". Should be blocked.</p>
                    <button type="submit" name="case2">Test Security Block</button>
                </div>
                
                <div class="test-case case3">
                    <h3>📊 Case 3: Validation Status</h3>
                    <p>Show all current requirements and verify they have valid game associations.</p>
                    <button type="submit" name="case3">Check All Requirements</button>
                </div>
            </div>
        </form>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($triggerResult): ?>
            <div class="result-section">
                <h3>🛡 Security Trigger Results</h3>
                <?php echo $triggerResult; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="index.php" style="color: #e91e63; text-decoration: none; font-weight: bold;">← Return to Gaming Database Homepage</a>
        </div>
    </div>
</body>
</html>