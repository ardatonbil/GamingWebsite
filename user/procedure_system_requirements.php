<?php
require_once 'config/database.php';

$message = '';
$procedureResult = '';
$gameList = [];

// Get list of available games for dropdown
try {
    $mysqli = getMySQLConnection();
    $gamesQuery = "SELECT GameID, Title FROM game ORDER BY Title";
    $gamesResult = $mysqli->query($gamesQuery);
    while ($row = $gamesResult->fetch_assoc()) {
        $gameList[] = $row;
    }
} catch (Exception $e) {
    $message = "Error loading games: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gameId = $_POST['game_id'] ?? '';
    
    if (!empty($gameId)) {
        try {
            $mysqli = getMySQLConnection();
            
            // Call the stored procedure with proper connection handling
            $query = "CALL GetSystemRequirements(?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("s", $gameId);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                // Clear any remaining results to avoid "commands out of sync"
                while ($mysqli->next_result()) {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                }
                
                if ($result && $result->num_rows > 0) {
                    $reqData = $result->fetch_assoc();
                    
                    // Format storage and memory sizes
                    $storageDisplay = $reqData['Storage_Size'] . ' GB ' . $reqData['Storage_Type'];
                    $memoryDisplay = $reqData['RAM_Size'] . ' GB ' . $reqData['RAM_Type'];
                    $vramDisplay = ($reqData['GPU_VRAM'] / 1024) . ' GB VRAM';
                    
                    // Performance rating based on requirements
                    $cpuCores = $reqData['CPU_Cores'];
                    $cpuSpeed = $reqData['CPU_Speed'];
                    $ramSize = $reqData['RAM_Size'];
                    
                    if ($cpuCores >= 8 && $cpuSpeed >= 3.0 && $ramSize >= 16) {
                        $perfRating = "<span style='background: #dc3545; color: white; padding: 8px 15px; border-radius: 20px; font-weight: bold;'>🔥 High-End Gaming</span>";
                        $perfColor = "#dc3545";
                    } elseif ($cpuCores >= 4 && $cpuSpeed >= 2.5 && $ramSize >= 8) {
                        $perfRating = "<span style='background: #ffc107; color: #212529; padding: 8px 15px; border-radius: 20px; font-weight: bold;'>⚡ Mid-Range Gaming</span>";
                        $perfColor = "#ffc107";
                    } else {
                        $perfRating = "<span style='background: #28a745; color: white; padding: 8px 15px; border-radius: 20px; font-weight: bold;'>✅ Entry-Level Gaming</span>";
                        $perfColor = "#28a745";
                    }
                    
                    $procedureResult = "
                    <div style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; display: flex; align-items: center; gap: 10px;'>
                            💻 System Requirements Retrieved
                        </h3>
                        
                        <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 15px 0; backdrop-filter: blur(10px);'>
                            <h4 style='margin-top: 0; color: #ffd700; display: flex; align-items: center; gap: 10px;'>
                                🎮 {$reqData['GameTitle']} - System Requirements
                            </h4>
                            <div style='text-align: center; margin: 15px 0;'>
                                $perfRating
                            </div>
                        </div>
                        
                        <div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-top: 20px;'>
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700; display: flex; align-items: center; gap: 8px;'>
                                    🖥 Processor (CPU)
                                </h4>
                                <div style='line-height: 1.8; font-size: 1.1em;'>
                                    <strong>Model:</strong> {$reqData['CPU_Model']}<br>
                                    <strong>Cores:</strong> {$reqData['CPU_Cores']} cores<br>
                                    <strong>Speed:</strong> {$reqData['CPU_Speed']} GHz<br>
                                </div>
                            </div>
                            
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700; display: flex; align-items: center; gap: 8px;'>
                                    🎨 Graphics (GPU)
                                </h4>
                                <div style='line-height: 1.8; font-size: 1.1em;'>
                                    <strong>Model:</strong> {$reqData['GPU_Model']}<br>
                                    <strong>Memory:</strong> $vramDisplay<br>
                                    <strong>Type:</strong> Dedicated Graphics<br>
                                </div>
                            </div>
                            
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700; display: flex; align-items: center; gap: 8px;'>
                                    🧠 Memory (RAM)
                                </h4>
                                <div style='line-height: 1.8; font-size: 1.1em;'>
                                    <strong>Size:</strong> {$reqData['RAM_Size']} GB<br>
                                    <strong>Type:</strong> {$reqData['RAM_Type']}<br>
                                    <strong>Speed:</strong> Standard<br>
                                </div>
                            </div>
                            
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700; display: flex; align-items: center; gap: 8px;'>
                                    💾 Storage
                                </h4>
                                <div style='line-height: 1.8; font-size: 1.1em;'>
                                    <strong>Size:</strong> {$reqData['Storage_Size']} GB<br>
                                    <strong>Type:</strong> {$reqData['Storage_Type']}<br>
                                    <strong>Speed:</strong> " . ($reqData['Storage_Type'] === 'SSD' ? 'High Speed' : 'Standard') . "<br>
                                </div>
                            </div>
                        </div>
                        
                        <div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 10px; backdrop-filter: blur(10px);'>
                            <h4 style='margin-top: 0; color: #ffd700;'>⚙ Stored Procedure Details</h4>
                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                <div>
                                    <strong>📞 Procedure:</strong> GetSystemRequirements('{$gameId}')<br>
                                    <strong>🔍 Execution:</strong> <span style='color: #90ee90;'>Successful</span><br>
                                </div>
                                <div>
                                    <strong>📊 Data Sources:</strong> 5 tables joined<br>
                                    <strong>✅ Result:</strong> Complete requirements profile<br>
                                </div>
                            </div>
                        </div>
                    </div>";
                    
                } else {
                    $procedureResult = "
                    <div style='background-color: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107;'>
                        <h4>⚠ No System Requirements Found</h4>
                        <p>The stored procedure executed successfully, but no system requirements were found for game: <strong>$gameId</strong></p>
                        <p>This game may not have detailed system requirements in the database yet.</p>
                    </div>";
                }
                
                $stmt->close();
            } else {
                $procedureResult = "
                <div style='background-color: #f8d7da; padding: 20px; border-radius: 10px; border-left: 5px solid #dc3545;'>
                    <h4>❌ Procedure Execution Failed</h4>
                    <p>Error: " . $mysqli->error . "</p>
                </div>";
            }
            
        } catch (Exception $e) {
            $message = "❌ Error: " . $e->getMessage();
        }
    } else {
        $message = "⚠ Please select a game to get system requirements.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>💻 Gaming Procedure: System Requirements</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
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
        .procedure-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .procedure-info {
            background: linear-gradient(135deg, #00b894, #00a085);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .sql-code {
            background-color: #2d3748;
            color: #ff6b6b;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            border-left: 5px solid #ff6b6b;
            overflow-x: auto;
        }
        .input-section {
            background: linear-gradient(135deg, #ffeaa7, #fdcb6e);
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2d3748;
            font-size: 1.1em;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 16px;
            background-color: white;
            transition: border-color 0.3s ease;
        }
        select:focus {
            outline: none;
            border-color: #ff6b6b;
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.1);
        }
        button {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin: 15px 0;
            border-left: 5px solid #dc3545;
        }
        .game-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .game-card {
            background: rgba(255,255,255,0.9);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            text-align: center;
            transition: transform 0.2s ease;
        }
        .game-card:hover {
            transform: translateY(-2px);
        }
        .req-highlight {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">🏠 Homepage</a>
            <a href="procedure_game_info.php">📊 Game Info</a>
            <a href="procedure_add_game.php">➕ Add Game</a>
            <a href="trigger_publisher_count.php">🔄 Triggers</a>
        </div>
        
        <div class="procedure-header">
            <h1>💻 Gaming Procedure: System Requirements</h1>
            <p><strong>Responsible:</strong> Kemal Arda Adışen | <strong>Database:</strong> GamingDatabase</p>
        </div>
        
        <div class="procedure-info">
            <h3>🎯 How This Gaming Procedure Works</h3>
            <p><strong>🎮 Purpose:</strong> Retrieves detailed system requirements for any game including CPU, GPU, RAM, and storage specifications needed to run the game.</p>
            
            <p><strong>💻 Gaming Hardware Context:</strong></p>
            <ul style="text-align: left; margin-left: 20px;">
                <li>Display minimum hardware requirements for games</li>
                <li>Help gamers check if their PC can run specific games</li>
                <li>Compare hardware needs across different games</li>
                <li>Assist in PC building and upgrade decisions</li>
                <li>Generate hardware compatibility reports</li>
            </ul>
            
            <div class="sql-code">
                <strong>💻 System Requirements Procedure SQL:</strong><br><br>
                CREATE PROCEDURE GetSystemRequirements(IN p_game_id VARCHAR(10))<br>
                BEGIN<br>
                &nbsp;&nbsp;&nbsp;&nbsp;SELECT g.Title as GameTitle,<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c.Model as CPU_Model, c.Cores as CPU_Cores, c.ClockFrequency as CPU_Speed,<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;gpu.Model as GPU_Model, gpu.VRAMSize as GPU_VRAM,<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ram.Size as RAM_Size, ram.Type as RAM_Type,<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;s.Size as Storage_Size, s.Type as Storage_Type<br>
                &nbsp;&nbsp;&nbsp;&nbsp;FROM game g<br>
                &nbsp;&nbsp;&nbsp;&nbsp;JOIN minimumrequirements mr ON g.GameID = mr.GameID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;JOIN cpu c ON mr.RequirementID = c.RequirementID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;JOIN gpu ON mr.RequirementID = gpu.RequirementID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;JOIN ram ON mr.RequirementID = ram.RAMID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;JOIN storage s ON mr.RequirementID = s.RequirementID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;WHERE g.GameID = p_game_id;<br>
                END
            </div>
        </div>
        
        <div class="input-section">
            <h3>🎮 Select a Game for System Requirements</h3>
            <p>Choose any game to see its detailed hardware requirements:</p>
            
            <div class="req-highlight">
                <strong>💡 Pro Tip:</strong> System requirements help determine if your PC can handle the game!
            </div>
            
            <?php if (!empty($gameList)): ?>
                <div class="game-grid">
                    <?php foreach (array_slice($gameList, 0, 6) as $game): ?>
                        <div class="game-card">
                            <strong><?php echo htmlspecialchars($game['Title']); ?></strong><br>
                            <small>ID: <?php echo htmlspecialchars($game['GameID']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="game_id">🎯 Select Game:</label>
                    <select name="game_id" id="game_id" required>
                        <option value="">-- Choose a game to check requirements --</option>
                        <?php foreach ($gameList as $game): ?>
                            <option value="<?php echo htmlspecialchars($game['GameID']); ?>" 
                                    <?php echo (isset($_POST['game_id']) && $_POST['game_id'] === $game['GameID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($game['Title']) . " (" . htmlspecialchars($game['GameID']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit">💻 Get System Requirements</button>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($procedureResult): ?>
            <div class="result-section">
                <h3>🎯 System Requirements Results</h3>
                <?php echo $procedureResult; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="index.php" style="color: #e91e63; text-decoration: none; font-weight: bold;">← Return to Gaming Database Homepage</a>
        </div>
    </div>
</body>
</html>