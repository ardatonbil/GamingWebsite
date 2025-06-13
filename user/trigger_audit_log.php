<?php
require_once 'config/database.php';

$message = '';
$triggerResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $mysqli = getMySQLConnection();
        
        if (isset($_POST['case1'])) {
            // Case 1: Update a game title and watch audit log
            $gameId = 'G005'; // Minecraft
            
            // Get current state
            $beforeQuery = "SELECT Title FROM game WHERE GameID = '$gameId'";
            $beforeResult = $mysqli->query($beforeQuery);
            $beforeData = $beforeResult->fetch_assoc();
            $oldTitle = $beforeData['Title'];
            
            // Update the game title (this will trigger the audit log)
            $newTitle = 'Minecraft: Java Edition';
            $updateSql = "UPDATE game SET Title = '$newTitle' WHERE GameID = '$gameId'";
            
            if ($mysqli->query($updateSql)) {
                // Check the audit log
                $auditQuery = "SELECT * FROM game_audit_log WHERE GameID = '$gameId' ORDER BY changed_at DESC LIMIT 1";
                $auditResult = $mysqli->query($auditQuery);
                
                if ($auditResult && $auditResult->num_rows > 0) {
                    $auditData = $auditResult->fetch_assoc();
                    
                    $triggerResult = "
                    <div style='background-color: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745;'>
                        <h4>✅ Case 1: Game Update Logged Successfully</h4>
                        
                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 15px 0;'>
                            <div style='background-color: #fff; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>
                                <strong>📝 Game Update:</strong><br>
                                <strong>Game ID:</strong> $gameId<br>
                                <strong>Old Title:</strong> " . htmlspecialchars($auditData['old_title']) . "<br>
                                <strong>New Title:</strong> " . htmlspecialchars($auditData['new_title']) . "<br>
                                <strong>Changed At:</strong> " . $auditData['changed_at'] . "
                            </div>
                            
                            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>
                                <strong>🔄 Trigger Action:</strong><br>
                                <strong>Log ID:</strong> " . $auditData['log_id'] . "<br>
                                <strong>Status:</strong> <span style='color: #28a745; font-weight: bold;'>LOGGED</span><br>
                                <strong>Audit Trail:</strong> ✅ Created<br>
                                <strong>Data Integrity:</strong> ✅ Maintained
                            </div>
                        </div>
                        
                        <p><strong>📊 Audit Success:</strong> The trigger automatically logged the game title change for compliance and tracking!</p>
                    </div>";
                } else {
                    $triggerResult = "
                    <div style='background-color: #fff3cd; padding: 15px; border-radius: 5px;'>
                        <strong>⚠ Warning:</strong> Game was updated but no audit log entry was found. Check if audit trigger is active.
                    </div>";
                }
            }
        }
        
        if (isset($_POST['case2'])) {
            // Case 2: Multiple updates to show audit trail
            $gameId = 'G002'; // Fortnite
            
            // Perform multiple updates
            $updates = [
                ['field' => 'Title', 'value' => 'Fortnite: Battle Royale'],
                ['field' => 'Title', 'value' => 'Fortnite: Chapter 4'],
                ['field' => 'Title', 'value' => 'Fortnite']
            ];
            
            foreach ($updates as $update) {
                $updateSql = "UPDATE game SET Title = '{$update['value']}' WHERE GameID = '$gameId'";
                $mysqli->query($updateSql);
                usleep(100000); // Small delay to ensure different timestamps
            }
            
            // Show audit trail
            $auditQuery = "
                SELECT 
                    log_id,
                    old_title,
                    new_title,
                    changed_at,
                    TIMESTAMPDIFF(SECOND, LAG(changed_at) OVER (ORDER BY changed_at), changed_at) as seconds_between
                FROM game_audit_log 
                WHERE GameID = '$gameId' 
                ORDER BY changed_at DESC 
                LIMIT 5
            ";
            $auditResult = $mysqli->query($auditQuery);
            
            $triggerResult = "
            <div style='background-color: #e3f2fd; padding: 20px; border-radius: 10px; border-left: 5px solid #2196f3;'>
                <h4>📈 Case 2: Complete Audit Trail for Fortnite</h4>
                <p>Multiple updates performed to demonstrate comprehensive audit logging:</p>
                
                <div style='overflow-x: auto; margin-top: 15px;'>
                    <table style='width: 100%; border-collapse: collapse; background-color: white; border-radius: 8px; overflow: hidden;'>
                        <thead>
                            <tr style='background: linear-gradient(135deg, #667eea, #764ba2); color: white;'>
                                <th style='padding: 12px; text-align: left;'>Log ID</th>
                                <th style='padding: 12px; text-align: left;'>Old Title</th>
                                <th style='padding: 12px; text-align: left;'>New Title</th>
                                <th style='padding: 12px; text-align: left;'>Timestamp</th>
                                <th style='padding: 12px; text-align: center;'>Action</th>
                            </tr>
                        </thead>
                        <tbody>";
            
            $rowCount = 0;
            while ($row = $auditResult->fetch_assoc()) {
                $rowCount++;
                $actionBadge = "<span style='background: #28a745; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8em;'>UPDATE #$rowCount</span>";
                
                $triggerResult .= "
                    <tr style='border-bottom: 1px solid #e2e8f0;'>
                        <td style='padding: 10px; font-weight: bold;'>{$row['log_id']}</td>
                        <td style='padding: 10px;'>" . htmlspecialchars($row['old_title']) . "</td>
                        <td style='padding: 10px;'>" . htmlspecialchars($row['new_title']) . "</td>
                        <td style='padding: 10px; font-family: monospace; font-size: 0.9em;'>{$row['changed_at']}</td>
                        <td style='padding: 10px; text-align: center;'>$actionBadge</td>
                    </tr>";
            }
            
            $triggerResult .= "
                        </tbody>
                    </table>
                </div>
                
                <div style='margin-top: 15px; padding: 15px; background-color: #fff3cd; border-radius: 5px;'>
                    <strong>📊 Audit Benefits:</strong> Complete change history maintained automatically - perfect for compliance, debugging, and rollback scenarios!
                </div>
            </div>";
        }
        
        if (isset($_POST['case3'])) {
            // Case 3: Show complete audit log summary
            $summaryQuery = "
                SELECT 
                    gal.GameID,
                    g.Title as current_title,
                    COUNT(gal.log_id) as total_changes,
                    MIN(gal.changed_at) as first_change,
                    MAX(gal.changed_at) as last_change
                FROM game_audit_log gal
                LEFT JOIN game g ON gal.GameID = g.GameID
                GROUP BY gal.GameID
                ORDER BY total_changes DESC
            ";
            
            $summaryResult = $mysqli->query($summaryQuery);
            
            $triggerResult = "
            <div style='background-color: #f3e5f5; padding: 20px; border-radius: 10px; border-left: 5px solid #9c27b0;'>
                <h4>📊 Case 3: Complete Audit Log Summary</h4>
                <p>Overview of all games that have been modified and tracked by the audit system:</p>";
            
            if ($summaryResult && $summaryResult->num_rows > 0) {
                $triggerResult .= "
                <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 15px 0;'>";
                
                while ($row = $summaryResult->fetch_assoc()) {
                    $triggerResult .= "
                    <div style='background-color: white; padding: 15px; border-radius: 8px; border: 1px solid #ddd;'>
                        <strong style='color: #9c27b0;'>🎮 {$row['current_title']}</strong><br>
                        <div style='margin: 8px 0; font-size: 0.9em; line-height: 1.6;'>
                            <strong>Game ID:</strong> {$row['GameID']}<br>
                            <strong>Changes:</strong> <span style='background: #9c27b0; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.8em;'>{$row['total_changes']}</span><br>
                            <strong>First Change:</strong> " . date('M j, Y', strtotime($row['first_change'])) . "<br>
                            <strong>Last Change:</strong> " . date('M j, Y', strtotime($row['last_change'])) . "
                        </div>
                    </div>";
                }
                
                $triggerResult .= "</div>";
            } else {
                $triggerResult .= "
                <div style='background-color: white; padding: 20px; border-radius: 5px; text-align: center; margin: 15px 0;'>
                    <p style='color: #666;'>No audit logs found yet. Try updating some games to see the audit trail in action!</p>
                </div>";
            }
            
            $triggerResult .= "
                <div style='margin-top: 15px; padding: 15px; background-color: #e8f5e8; border-radius: 5px;'>
                    <strong>🔍 Audit Value:</strong> This trigger provides complete accountability for all game modifications, 
                    essential for data governance, compliance, and troubleshooting in gaming databases!
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
    <title>📝 Gaming Trigger: Audit Logger</title>
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
            color: #667eea;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #667eea;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #667eea;
            color: white;
            transform: translateY(-2px);
        }
        .trigger-header {
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .trigger-info {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .sql-code {
            background-color: #2d3748;
            color: #9c27b0;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            border-left: 5px solid #9c27b0;
            overflow-x: auto;
        }
        .test-cases {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin: 30px 0;
        }
        .test-case {
            background: linear-gradient(135deg, #e1bee7 0%, #f3e5f5 100%);
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
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
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
            background: linear-gradient(135deg, #bbdefb 0%, #e3f2fd 100%);
        }
        .case3 {
            background: linear-gradient(135deg, #c8e6c9 0%, #e8f5e8 100%);
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
            <a href="trigger_requirements_check.php">🛡 Trigger 2</a>
            <a href="procedure_game_info.php">⚙ Procedures</a>
        </div>
        
        <div class="trigger-header">
            <h1>📝 Gaming Trigger: Audit Logger</h1>
            <p><strong>Responsible:</strong> Tuna Mintaş | <strong>Database:</strong> GamingDatabase</p>
        </div>
        
        <div class="trigger-info">
            <h3>📊 How This Gaming Audit Trigger Works</h3>
            <p><strong>🎯 Purpose:</strong> Automatically logs all changes made to game information, creating a complete audit trail for compliance, debugging, and change tracking.</p>
            
            <p><strong>🎮 Gaming Audit Context:</strong></p>
            <ul style="text-align: left; margin-left: 20px;">
                <li>Tracks game title changes and updates</li>
                <li>Maintains complete change history for compliance</li>
                <li>Enables rollback to previous game information</li>
                <li>Provides accountability for database modifications</li>
                <li>Supports debugging and troubleshooting</li>
            </ul>
            
            <div class="sql-code">
                <strong>📋 Audit Trigger SQL:</strong><br><br>
                CREATE TRIGGER log_game_updates<br>
                AFTER UPDATE ON game<br>
                FOR EACH ROW<br>
                BEGIN<br>
                &nbsp;&nbsp;&nbsp;&nbsp;INSERT INTO game_audit_log (<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;GameID, old_title, new_title, changed_at<br>
                &nbsp;&nbsp;&nbsp;&nbsp;) VALUES (<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NEW.GameID, OLD.Title, NEW.Title, NOW()<br>
                &nbsp;&nbsp;&nbsp;&nbsp;);<br>
                END
            </div>
        </div>
        
        <h2>🧪 Audit Trail Testing</h2>
        <p>Test the audit logging trigger with different change scenarios:</p>
        
        <form method="POST">
            <div class="test-cases">
                <div class="test-case">
                    <h3>📝 Case 1: Single Update</h3>
                    <p>Update Minecraft title and see the change logged in the audit trail.</p>
                    <button type="submit" name="case1">Log Single Change</button>
                </div>
                
                <div class="test-case case2">
                    <h3>📈 Case 2: Multiple Updates</h3>
                    <p>Perform multiple Fortnite title changes to show comprehensive audit trail.</p>
                    <button type="submit" name="case2">Create Audit Trail</button>
                </div>
                
                <div class="test-case case3">
                    <h3>📊 Case 3: Full Audit Report</h3>
                    <p>Display complete audit summary for all games with change statistics.</p>
                    <button type="submit" name="case3">Show Audit Summary</button>
                </div>
            </div>
        </form>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($triggerResult): ?>
            <div class="result-section">
                <h3>📋 Audit Trigger Results</h3>
                <?php echo $triggerResult; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="index.php" style="color: #667eea; text-decoration: none; font-weight: bold;">← Return to Gaming Database Homepage</a>
        </div>
    </div>
</body>
</html>