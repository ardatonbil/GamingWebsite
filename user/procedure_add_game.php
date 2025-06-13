<?php
require_once 'config/database.php';

$message = '';
$procedureResult = '';
$publisherList = [];

// Get list of available publishers for dropdown
try {
    $mysqli = getMySQLConnection();
    $publishersQuery = "SELECT PublisherID, PublisherName FROM publisher ORDER BY PublisherName";
    $publishersResult = $mysqli->query($publishersQuery);
    while ($row = $publishersResult->fetch_assoc()) {
        $publisherList[] = $row;
    }
} catch (Exception $e) {
    $message = "Error loading publishers: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Game basic info
    $gameId = trim($_POST['game_id'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $year = intval($_POST['year'] ?? 0);
    $genre = trim($_POST['genre'] ?? '');
    $platform = trim($_POST['platform'] ?? '');
    $publisherId = trim($_POST['publisher_id'] ?? '');
    
    // System requirements
    $cpuModel = trim($_POST['cpu_model'] ?? '');
    $cpuManufacturer = trim($_POST['cpu_manufacturer'] ?? '');
    $cpuCores = intval($_POST['cpu_cores'] ?? 0);
    $cpuFrequency = floatval($_POST['cpu_frequency'] ?? 0);
    $gpuModel = trim($_POST['gpu_model'] ?? '');
    $gpuManufacturer = trim($_POST['gpu_manufacturer'] ?? '');
    $gpuVram = intval($_POST['gpu_vram'] ?? 0);
    $ramSize = intval($_POST['ram_size'] ?? 0);
    $ramType = trim($_POST['ram_type'] ?? '');
    $storageSize = intval($_POST['storage_size'] ?? 0);
    $storageType = trim($_POST['storage_type'] ?? '');
    
    // Validate all required fields
    if (!empty($gameId) && !empty($title) && $year > 0 && !empty($genre) && 
        !empty($platform) && !empty($publisherId) && !empty($cpuModel) && 
        !empty($cpuManufacturer) && $cpuCores > 0 && $cpuFrequency > 0 && 
        !empty($gpuModel) && !empty($gpuManufacturer) && $gpuVram > 0 && 
        $ramSize > 0 && !empty($ramType) && $storageSize > 0 && !empty($storageType)) {
        
        try {
            $mysqli = getMySQLConnection();
            
            // Call the enhanced stored procedure
            $query = "CALL AddNewGame(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @result_message)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssisssssidssiisis", 
                $gameId, $title, $year, $genre, $platform, $publisherId,
                $cpuModel, $cpuManufacturer, $cpuCores, $cpuFrequency,
                $gpuModel, $gpuManufacturer, $gpuVram,
                $ramSize, $ramType, $storageSize, $storageType
            );
            
            if ($stmt->execute()) {
                // Clear any remaining results
                while ($mysqli->next_result()) {
                    if ($res = $mysqli->store_result()) {
                        $res->free();
                    }
                }
                
                // Get the output parameter
                $result = $mysqli->query("SELECT @result_message as message");
                $output = $result->fetch_assoc();
                $resultMessage = $output['message'];
                
                // Get publisher info for display
                $publisherQuery = "SELECT PublisherName, MarketValue, EstablishmentYear FROM publisher WHERE PublisherID = ?";
                $pubStmt = $mysqli->prepare($publisherQuery);
                $pubStmt->bind_param("s", $publisherId);
                $pubStmt->execute();
                $pubResult = $pubStmt->get_result();
                $publisherData = $pubResult->fetch_assoc();
                
                if (strpos($resultMessage, 'successfully') !== false) {
                    // Success case
                    $procedureResult = "
                    <div style='background: linear-gradient(135deg, #00b894, #00cec9); color: white; padding: 25px; border-radius: 15px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; display: flex; align-items: center; gap: 10px;'>
                            🎉 Game and System Requirements Added Successfully!
                        </h3>
                        
                        <div style='display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px;'>
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700;'>🎮 Game Details</h4>
                                <div style='line-height: 1.8; font-size: 1.1em;'>
                                    <strong>🎯 Title:</strong> $title<br>
                                    <strong>🆔 Game ID:</strong> $gameId<br>
                                    <strong>📅 Year:</strong> $year<br>
                                    <strong>🎭 Genre:</strong> $genre<br>
                                    <strong>💻 Platform:</strong> $platform
                                </div>
                            </div>
                            
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700;'>🏢 Publisher</h4>
                                <div style='line-height: 1.8; font-size: 1.1em;'>
                                    <strong>🏭 Name:</strong> {$publisherData['PublisherName']}<br>
                                    <strong>💰 Value:</strong> $" . number_format($publisherData['MarketValue'] / 1000000, 0) . "M<br>
                                    <strong>📈 Founded:</strong> {$publisherData['EstablishmentYear']}<br>
                                    <strong>🔗 Status:</strong> <span style='color: #90ee90;'>✅ Linked</span>
                                </div>
                            </div>
                            
                            <div style='background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; backdrop-filter: blur(10px);'>
                                <h4 style='margin-top: 0; color: #ffd700;'>⚙ System Requirements</h4>
                                <div style='line-height: 1.8; font-size: 0.9em;'>
                                    <strong>🖥 CPU:</strong> $cpuModel ($cpuCores cores)<br>
                                    <strong>🎮 GPU:</strong> $gpuModel ($gpuVram GB)<br>
                                    <strong>💾 RAM:</strong> $ramSize GB $ramType<br>
                                    <strong>💿 Storage:</strong> $storageSize GB $storageType
                                </div>
                            </div>
                        </div>
                        
                        <div style='margin-top: 20px; padding: 15px; background: rgba(255,255,255,0.1); border-radius: 10px; backdrop-filter: blur(10px);'>
                            <h4 style='margin-top: 0; color: #ffd700;'>📊 Database Operations</h4>
                            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 15px;'>
                                <div>
                                    <strong>📞 Procedure:</strong> Enhanced AddNewGame()<br>
                                    <strong>🗂 Tables Updated:</strong> 7 tables<br>
                                </div>
                                <div>
                                    <strong>🔄 Transaction:</strong> Atomic operation<br>
                                    <strong>✅ Result:</strong> Complete success<br>
                                </div>
                            </div>
                        </div>
                        
                        <div style='margin-top: 20px; text-align: center;'>
                            <a href='procedure_system_requirements.php?game_id=$gameId' style='background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; font-weight: bold; margin: 0 10px;'>🔍 View System Requirements</a>
                            <a href='procedure_game_info.php?game_id=$gameId' style='background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; font-weight: bold; margin: 0 10px;'>📊 View Game Info</a>
                            <a href='procedure_add_game.php' style='background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px; font-weight: bold; margin: 0 10px;'>➕ Add Another Game</a>
                        </div>
                    </div>";
                } else {
                    // Error case
                    $errorColor = strpos($resultMessage, 'exists') !== false ? '#ffc107' : '#dc3545';
                    $errorIcon = strpos($resultMessage, 'exists') !== false ? '⚠' : '❌';
                    
                    $procedureResult = "
                    <div style='background-color: #f8d7da; padding: 20px; border-radius: 10px; border-left: 5px solid $errorColor;'>
                        <h4>$errorIcon Procedure Validation Failed</h4>
                        <div style='background-color: #fff; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                            <strong>🎮 Game ID:</strong> $gameId<br>
                            <strong>📝 Title:</strong> $title<br>
                            <strong>🏭 Publisher:</strong> {$publisherData['PublisherName']}<br>
                            <strong>⚠ Issue:</strong> $resultMessage
                        </div>
                        <p><strong>🛡 Security:</strong> The stored procedure prevented invalid data entry!</p>
                    </div>";
                }
                
                $stmt->close();
                $pubStmt->close();
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
        $message = "⚠ Please fill in all required fields including system requirements.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>➕ Gaming Procedure: Add New Game with System Requirements</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            color: #00b894;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #00b894;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #00b894;
            color: white;
            transform: translateY(-2px);
        }
        .procedure-header {
            background: linear-gradient(135deg, #fd79a8, #fdcb6e);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }
        .procedure-info {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .input-section {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            color: white;
        }
        .section-divider {
            background: linear-gradient(135deg, #e17055, #fd79a8);
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            font-size: 1.2em;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            font-size: 1.1em;
        }
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            font-size: 16px;
            background-color: rgba(255,255,255,0.9);
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #ffd700;
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.3);
            background-color: white;
        }
        button {
            background: linear-gradient(135deg, #fd79a8, #fdcb6e);
            color: white;
            padding: 20px 40px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 30px;
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
        .publisher-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .publisher-card {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.3);
            text-align: center;
            transition: transform 0.2s ease;
        }
        .publisher-card:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.3);
        }
        .feature-highlight {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
            text-align: center;
        }
        .sql-code {
            background-color: #2d3748;
            color: #fd79a8;
            padding: 20px;
            border-radius: 10px;
            font-family: 'Courier New', monospace;
            margin: 15px 0;
            border-left: 5px solid #fd79a8;
            overflow-x: auto;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">🏠 Homepage</a>
            <a href="procedure_game_info.php">📊 Game Info</a>
            <a href="procedure_system_requirements.php">💻 System Requirements</a>
            <a href="trigger_publisher_count.php">🔄 Triggers</a>
        </div>
        
        <div class="procedure-header">
            <h1>➕ Enhanced Gaming Procedure: Add Complete Game</h1>
            <p><strong>Responsible:</strong> Tuna Mintaş | <strong>Database:</strong> GamingDatabase</p>
            <p><strong>NEW:</strong> Now includes system requirements in one atomic operation! 🚀</p>
        </div>
        
        <div class="procedure-info">
            <h3>🚀 Enhanced Gaming Procedure Features</h3>
            <p><strong>🎯 Purpose:</strong> Adds a complete game entry with basic info AND system requirements in one atomic transaction.</p>
            
            <p><strong>🆕 What's New:</strong></p>
            <ul style="text-align: left; margin-left: 20px;">
                <li>✅ Atomic transaction - all or nothing approach</li>
                <li>✅ Complete system requirements capture</li>
                <li>✅ Automatic RequirementID generation</li>
                <li>✅ All 7 tables updated in one procedure call</li>
                <li>✅ Rollback on any error for data integrity</li>
            </ul>
            
            <div class="sql-code">
                <strong>🔧 Enhanced Procedure Parameters:</strong><br><br>
                Game Info: p_game_id, p_title, p_year, p_genre, p_platform, p_publisher_id<br>
                CPU: p_cpu_model, p_cpu_manufacturer, p_cpu_cores, p_cpu_frequency<br>
                GPU: p_gpu_model, p_gpu_manufacturer, p_gpu_vram<br>
                Memory: p_ram_size, p_ram_type<br>
                Storage: p_storage_size, p_storage_type<br><br>
                <strong>Tables Updated:</strong> game, game_publisher, minimumrequirements, cpu, gpu, ram, storage
            </div>
        </div>
        
        <div class="input-section">
            <h3>🎮 Add Complete Game Entry</h3>
            
            <div class="feature-highlight">
                <strong>✨ Enhanced Features:</strong> Complete validation • System requirements • Atomic transactions • Auto-rollback on errors
            </div>
            
            <?php if (!empty($publisherList)): ?>
                <h4>🏢 Available Publishers:</h4>
                <div class="publisher-grid">
                    <?php foreach (array_slice($publisherList, 0, 6) as $publisher): ?>
                        <div class="publisher-card">
                            <strong><?php echo htmlspecialchars($publisher['PublisherName']); ?></strong><br>
                            <small>ID: <?php echo htmlspecialchars($publisher['PublisherID']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="section-divider">🎮 Game Basic Information</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="game_id">🆔 Game ID:</label>
                        <input type="text" 
                               id="game_id" 
                               name="game_id" 
                               value="<?php echo htmlspecialchars($_POST['game_id'] ?? ''); ?>"
                               placeholder="e.g., G013"
                               pattern="G[0-9]{3,}"
                               title="Format: G followed by numbers (e.g., G013)"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">🎯 Game Title:</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                               placeholder="e.g., The Legend of Zelda: Breath of the Wild"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="year">📅 Release Year:</label>
                        <input type="number" 
                               id="year" 
                               name="year" 
                               value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>"
                               placeholder="e.g., 2024"
                               min="1970" 
                               max="2030"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="genre">🎭 Genre:</label>
                        <select name="genre" id="genre" required>
                            <option value="">-- Select Genre --</option>
                            <option value="Action" <?php echo (($_POST['genre'] ?? '') === 'Action') ? 'selected' : ''; ?>>🗡 Action</option>
                            <option value="Adventure" <?php echo (($_POST['genre'] ?? '') === 'Adventure') ? 'selected' : ''; ?>>🗺 Adventure</option>
                            <option value="RPG" <?php echo (($_POST['genre'] ?? '') === 'RPG') ? 'selected' : ''; ?>>🧙 RPG</option>
                            <option value="Strategy" <?php echo (($_POST['genre'] ?? '') === 'Strategy') ? 'selected' : ''; ?>>♟ Strategy</option>
                            <option value="Sports" <?php echo (($_POST['genre'] ?? '') === 'Sports') ? 'selected' : ''; ?>>⚽ Sports</option>
                            <option value="Racing" <?php echo (($_POST['genre'] ?? '') === 'Racing') ? 'selected' : ''; ?>>🏎 Racing</option>
                            <option value="Simulation" <?php echo (($_POST['genre'] ?? '') === 'Simulation') ? 'selected' : ''; ?>>🏗 Simulation</option>
                            <option value="Puzzle" <?php echo (($_POST['genre'] ?? '') === 'Puzzle') ? 'selected' : ''; ?>>🧩 Puzzle</option>
                            <option value="Battle Royale" <?php echo (($_POST['genre'] ?? '') === 'Battle Royale') ? 'selected' : ''; ?>>👑 Battle Royale</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="platform">💻 Platform:</label>
                        <select name="platform" id="platform" required>
                            <option value="">-- Select Platform --</option>
                            <option value="PC" <?php echo (($_POST['platform'] ?? '') === 'PC') ? 'selected' : ''; ?>>🖥 PC</option>
                            <option value="PlayStation" <?php echo (($_POST['platform'] ?? '') === 'PlayStation') ? 'selected' : ''; ?>>🎮 PlayStation</option>
                            <option value="Xbox" <?php echo (($_POST['platform'] ?? '') === 'Xbox') ? 'selected' : ''; ?>>🎮 Xbox</option>
                            <option value="Nintendo Switch" <?php echo (($_POST['platform'] ?? '') === 'Nintendo Switch') ? 'selected' : ''; ?>>🎮 Nintendo Switch</option>
                            <option value="Multiple" <?php echo (($_POST['platform'] ?? '') === 'Multiple') ? 'selected' : ''; ?>>🌐 Multiple Platforms</option>
                            <option value="Mobile" <?php echo (($_POST['platform'] ?? '') === 'Mobile') ? 'selected' : ''; ?>>📱 Mobile</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="publisher_id">🏭 Publisher:</label>
                        <select name="publisher_id" id="publisher_id" required>
                            <option value="">-- Select Publisher --</option>
                            <?php foreach ($publisherList as $publisher): ?>
                                <option value="<?php echo htmlspecialchars($publisher['PublisherID']); ?>" 
                                        <?php echo (isset($_POST['publisher_id']) && $_POST['publisher_id'] === $publisher['PublisherID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($publisher['PublisherName']) . " (" . htmlspecialchars($publisher['PublisherID']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="section-divider">🖥 CPU Requirements</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="cpu_model">🔧 CPU Model:</label>
                        <input type="text" 
                               id="cpu_model" 
                               name="cpu_model" 
                               value="<?php echo htmlspecialchars($_POST['cpu_model'] ?? ''); ?>"
                               placeholder="e.g., Core i5-8400"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cpu_manufacturer">🏭 CPU Manufacturer:</label>
                        <select name="cpu_manufacturer" id="cpu_manufacturer" required>
                            <option value="">-- Select Manufacturer --</option>
                            <option value="Intel" <?php echo (($_POST['cpu_manufacturer'] ?? '') === 'Intel') ? 'selected' : ''; ?>>Intel</option>
                            <option value="AMD" <?php echo (($_POST['cpu_manufacturer'] ?? '') === 'AMD') ? 'selected' : ''; ?>>AMD</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="cpu_cores">⚙ CPU Cores:</label>
                        <input type="number" 
                               id="cpu_cores" 
                               name="cpu_cores" 
                               value="<?php echo htmlspecialchars($_POST['cpu_cores'] ?? ''); ?>"
                               placeholder="e.g., 4"
                               min="1" 
                               max="32"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="cpu_frequency">🚀 CPU Frequency (GHz):</label>
                        <input type="number" 
                               id="cpu_frequency" 
                               name="cpu_frequency" 
                               value="<?php echo htmlspecialchars($_POST['cpu_frequency'] ?? ''); ?>"
                               placeholder="e.g., 2.8"
                               step="0.1"
                               min="1.0" 
                               max="6.0"
                               required>
                    </div>
                </div>
                
                <div class="section-divider">🎮 GPU Requirements</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="gpu_model">🎯 GPU Model:</label>
                        <input type="text" 
                               id="gpu_model" 
                               name="gpu_model" 
                               value="<?php echo htmlspecialchars($_POST['gpu_model'] ?? ''); ?>"
                               placeholder="e.g., GeForce GTX 1060"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="gpu_manufacturer">🏭 GPU Manufacturer:</label>
                        <select name="gpu_manufacturer" id="gpu_manufacturer" required>
                            <option value="">-- Select Manufacturer --</option>
                            <option value="NVIDIA" <?php echo (($_POST['gpu_manufacturer'] ?? '') === 'NVIDIA') ? 'selected' : ''; ?>>NVIDIA</option>
                            <option value="AMD" <?php echo (($_POST['gpu_manufacturer'] ?? '') === 'AMD') ? 'selected' : ''; ?>>AMD</option>
                            <option value="Intel" <?php echo (($_POST['gpu_manufacturer'] ?? '') === 'Intel') ? 'selected' : ''; ?>>Intel</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="gpu_vram">💾 GPU VRAM (GB):</label>
                        <input type="number" 
                               id="gpu_vram" 
                               name="gpu_vram" 
                               value="<?php echo htmlspecialchars($_POST['gpu_vram'] ?? ''); ?>"
                               placeholder="e.g., 4"
                               min="1" 
                               max="32"
                               required>
                    </div>
                </div>
                
                <div class="section-divider">💾 Memory & Storage Requirements</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="ram_size">🧠 RAM Size (GB):</label>
                        <input type="number" 
                               id="ram_size" 
                               name="ram_size" 
                               value="<?php echo htmlspecialchars($_POST['ram_size'] ?? ''); ?>"
                               placeholder="e.g., 8"
                               min="1" 
                               max="128"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ram_type">⚡ RAM Type:</label>
                        <select name="ram_type" id="ram_type" required>
                            <option value="">-- Select RAM Type --</option>
                            <option value="DDR3" <?php echo (($_POST['ram_type'] ?? '') === 'DDR3') ? 'selected' : ''; ?>>DDR3</option>
                            <option value="DDR4" <?php echo (($_POST['ram_type'] ?? '') === 'DDR4') ? 'selected' : ''; ?>>DDR4</option>
                            <option value="DDR5" <?php echo (($_POST['ram_type'] ?? '') === 'DDR5') ? 'selected' : ''; ?>>DDR5</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="storage_size">💿 Storage Size (GB):</label>
                        <input type="number" 
                               id="storage_size" 
                               name="storage_size" 
                               value="<?php echo htmlspecialchars($_POST['storage_size'] ?? ''); ?>"
                               placeholder="e.g., 50"
                               min="1" 
                               max="500"
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="storage_type">🚀 Storage Type:</label>
                        <select name="storage_type" id="storage_type" required>
                            <option value="">-- Select Storage Type --</option>
                            <option value="HDD" <?php echo (($_POST['storage_type'] ?? '') === 'HDD') ? 'selected' : ''; ?>>HDD (Hard Disk Drive)</option>
                            <option value="SSD" <?php echo (($_POST['storage_type'] ?? '') === 'SSD') ? 'selected' : ''; ?>>SSD (Solid State Drive)</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit">🚀 Add Complete Game Entry</button>
            </form>
        </div>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($procedureResult): ?>
            <div class="result-section">
                <h3>🎯 Complete Game Addition Results</h3>
                <?php echo $procedureResult; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 40px; padding: 20px;">
            <a href="index.php" style="color: #00b894; text-decoration: none; font-weight: bold;">← Return to Gaming Database Homepage</a>
        </div>
    </div>
    
    <script>
        // Auto-suggest some common values
        document.getElementById('cpu_model').addEventListener('focus', function() {
            this.placeholder = "e.g., Core i5-8400, Ryzen 5 3600, Core i7-10700K";
        });
        
        document.getElementById('gpu_model').addEventListener('focus', function() {
            this.placeholder = "e.g., GeForce GTX 1060, RTX 3070, RX 580";
        });
        
        // Form validation helper
        document.querySelector('form').addEventListener('submit', function(e) {
            const cpuFreq = parseFloat(document.getElementById('cpu_frequency').value);
            const gpuVram = parseInt(document.getElementById('gpu_vram').value);
            const ramSize = parseInt(document.getElementById('ram_size').value);
            const storageSize = parseInt(document.getElementById('storage_size').value);
            
            if (cpuFreq < 1.0 || cpuFreq > 6.0) {
                alert('CPU Frequency should be between 1.0 and 6.0 GHz');
                e.preventDefault();
                return;
            }
            
            if (gpuVram < 1 || gpuVram > 32) {
                alert('GPU VRAM should be between 1 GB and 32 GB');
                e.preventDefault();
                return;
            }
            
            if (ramSize < 1 || ramSize > 128) {
                alert('RAM size should be between 1 GB and 128 GB');
                e.preventDefault();
                return;
            }
            
            if (storageSize < 1 || storageSize > 500) {
                alert('Storage size should be between 1 GB and 500 GB');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>
</html>