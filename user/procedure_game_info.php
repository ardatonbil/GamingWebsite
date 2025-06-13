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
            $query = "CALL GetGameInfo(?)";
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
                    $gameData = $result->fetch_assoc();
                    
                    // Get additional details for richer display
                    $detailsQuery = "
                        SELECT 
                            g.*,
                            p.PublisherName,
                            p.MarketValue,
                            p.EstablishmentYear,
                            r.Category as AgeRating,
                            r.PEGI_ID
                        FROM game g
                        LEFT JOIN game_publisher gp ON g.GameID = gp.GameID
                        LEFT JOIN publisher p ON gp.PublisherID = p.PublisherID
                        LEFT JOIN restrictedby rb ON g.GameID = rb.GameID
                        LEFT JOIN restriction r ON rb.RestrictionID = r.PEGI_ID
                        WHERE g.GameID = ?
                    ";
                    
                    $detailStmt = $mysqli->prepare($detailsQuery);
                    $detailStmt->bind_param("s", $gameId);
                    $detailStmt->execute();
                    $detailResult = $detailStmt->get_result();
                    $fullGameData = $detailResult->fetch_assoc();
                    
                    // Format market value
                    $marketValue = number_format($fullGameData['MarketValue'] / 1000000, 0) . 'M';
                    
                    // Game features
                    $features = [];
                    if ($fullGameData['InGameTransaction']) $features[] = "💰 In-Game Purchases";
                    if ($fullGameData['Multiplayer']) $features[] = "👥 Multiplayer";
                    $featureList = !empty($features) ? implode("<br>", $features) : "🎮 Single Player Only";
                    
                    // Age rating badge
                    $ageRatingBadge = match($fullGameData['PEGI_ID']) {
                        'PEGI 7' => "<span style='background: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-weight: bold;'>👶 PEGI 7 - Everyone</span>",
                        'PEGI 12' => "<span style='background: #ffc107; color: #212529; padding: 5px 10px; border-radius: 15px; font-weight: bold;'>🧒 PEGI 12 - Teen</span>",
                        'PEGI 18' => "<span style='background: #dc3545; color: white; padding: 5px 10px; border-radius: 15px; font-weight: bold;'>🔞 PEGI 18 - Mature</span>",
                        default => "<span style='background: #6c757d; color: white; padding: 5px 10px; border-radius: 15px;'>❓ Not Rated</span>"
                    };
                    
                    $procedureResult = "
                    <div style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; display: flex; align-items: center; gap: 10px;'>
                            🎮 Game Information Retrieved Successfully
                        </h3>
                        
                        <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-top: 20px;'>
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700;'>📋 Basic Information</h4>
                                <div style='line-height: 1.8;'>
                                    <strong>🎯 Title:</strong> {$fullGameData['Title']}<br>
                                    <strong>📅 Release Year:</strong> {$fullGameData['ReleaseYear']}<br>
                                    <strong>🎭 Genre:</strong> {$fullGameData['Genre']}<br>
                                    <strong>💻 Platform:</strong> {$fullGameData['Platform']}<br>
                                    <strong>🔞 Age Rating:</strong> $ageRatingBadge<br>
                                </div>
                            </div>
                            
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700;'>🏢 Publisher Information</h4>
                                <div style='line-height: 1.8;'>
                                    <strong>🏭 Publisher:</strong> {$fullGameData['PublisherName']}<br>
                                    <strong>💰 Market Value:</strong> \${$marketValue}<br>
                                    <strong>📈 Founded:</strong> {$fullGameData['EstablishmentYear']}<br>
                                    <strong>🎮 Features:</strong><br>$featureList
                                </div>
                            </div>
                        </div>
                        
                        <div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 10px; backdrop-filter: blur(10px);'>
                            <h4 style='margin-top: 0; color: #ffd700;'>⚙ Stored Procedure Details</h4>
                            <strong>📞 Procedure Called:</strong> GetGameInfo('{$gameId}')<br>
                            <strong>🔍 Query Execution:</strong> Successfully retrieved comprehensive game data<br>
                            <strong>📊 Data Sources:</strong> Combined data from game, publisher, and restriction tables<br>
                            <strong>✅ Result:</strong> Procedure executed successfully and returned complete game profile!
                        </div>
                    </div>";
                    
                } else {
                    $procedureResult = "
                    <div style='background-color: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107;'>
                        <h4>⚠ No Data Found</h4>
                        <p>The stored procedure executed successfully, but no game was found with ID: <strong>$gameId</strong></p>
                        <p>Please verify the Game ID and try again.</p>
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
        $message = "⚠ Please select a game to get information.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎮 Gaming Procedure: Get Game Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #74b9ff, #0984e3);
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
            color: #0984e3;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #0984e3;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #0984e3;
            color: white;
            transform: translateY(-2px);
        }
        .procedure-header {
            background: linear-gradient(135deg, #00b894, #00a085);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .procedure-info {
            background: linear-gradient(135deg, #fd79a8, #e84393);
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
        .input-section {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            background: linear-gradient(135deg, #667eea, #764ba2);
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
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">🏠 Homepage</a>
            <a href="trigger_publisher_count.php">🔄 Triggers</a>
            <a href="procedure_system_requirements.php">💻 System Requirements</a>
            <a href="support_tickets.php">🎫 Support</a>
        </div>
        
        <div class="procedure-header">
            <h1>📊 Gaming Procedure: Get Game Information</h1>
            <p><strong>Responsible:</strong> Arda Barış Tonbil | <strong>Database:</strong> GamingDatabase</p>
        </div>
        
        <div class="procedure-info">
            <h3>📋 How This Gaming Procedure Works</h3>
            <p><strong>🎯 Purpose:</strong> Retrieves comprehensive information about any game including publisher details, age ratings, and platform information.</p>
            
            <p><strong>🎮 Gaming Use Cases:</strong></p>
            <ul style="text-align: left; margin-left: 20px;">
                <li>Display complete game profiles for gaming websites</li>
                <li>Get publisher and rating information for game catalogs</li>
                <li>Retrieve game details for recommendation systems</li>
                <li>Generate game information for reviews and comparisons</li>
            </ul>
            
            <div class="sql-code">
                <strong>📝 Stored Procedure SQL:</strong><br><br>
                CREATE PROCEDURE GetGameInfo(IN p_game_id VARCHAR(10))<br>
                BEGIN<br>
                &nbsp;&nbsp;&nbsp;&nbsp;SELECT<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;g.GameID, g.Title, g.ReleaseYear, g.Genre, g.Platform,<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;p.PublisherName, r.Category as AgeRating<br>
                &nbsp;&nbsp;&nbsp;&nbsp;FROM game g<br>
                &nbsp;&nbsp;&nbsp;&nbsp;LEFT JOIN game_publisher gp ON g.GameID = gp.GameID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;LEFT JOIN publisher p ON gp.PublisherID = p.PublisherID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;LEFT JOIN restrictedby rb ON g.GameID = rb.GameID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;LEFT JOIN restriction r ON rb.RestrictionID = r.PEGI_ID<br>
                &nbsp;&nbsp;&nbsp;&nbsp;WHERE g.GameID = p_game_id;<br>
                END
            </div>
        </div>
        
        <div class="input-section">
            <h3>🎮 Select a Game to Get Information</h3>
            <p>Choose any game from your gaming database to retrieve detailed information:</p>
            
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
                        <option value="">-- Choose a game --</option>
                        <?php foreach ($gameList as $game): ?>
                            <option value="<?php echo htmlspecialchars($game['GameID']); ?>" 
                                    <?php echo (isset($_POST['game_id']) && $_POST['game_id'] === $game['GameID']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($game['Title']) . " (" . htmlspecialchars($game['GameID']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit">📊 Get Game Information</button>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($procedureResult): ?>
            <div class="result-section">
                <h3>🎯 Procedure Execution Results</h3>
                <?php echo $procedureResult; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="index.php" style="color: #0984e3; text-decoration: none; font-weight: bold;">← Return to Gaming Database Homepage</a>
        </div>
    </div>
</body>
</html>