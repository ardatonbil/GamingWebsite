<?php
require_once 'config/database.php';

echo "<h2>🎮 Complete Gaming Database Setup</h2>";
echo "<p>This will create tables, import data, and set up triggers/procedures for your gaming database.</p>";

try {
    $mysqli = getMySQLConnection();
    echo "✅ MySQL connection successful to GamingDatabase!<br><br>";
    
    // Step 1: Create table structures based on your data
    echo "<h3>📊 Creating Table Structures...</h3>";
    
    $tables = [
        "publisher" => "
        CREATE TABLE IF NOT EXISTS publisher (
            PublisherID VARCHAR(10) PRIMARY KEY,
            PublisherName VARCHAR(100) NOT NULL,
            MarketValue DECIMAL(15,2) DEFAULT 0.00,
            NumberOfGames INT DEFAULT 0,
            EstablishmentYear INT NOT NULL
        )",
        
        "game" => "
        CREATE TABLE IF NOT EXISTS game (
            GameID VARCHAR(10) PRIMARY KEY,
            Title VARCHAR(150) NOT NULL,
            ReleaseYear INT NOT NULL,
            Genre VARCHAR(50) NOT NULL,
            Platform VARCHAR(50) NOT NULL,
            InGameTransaction BOOLEAN DEFAULT FALSE,
            Multiplayer BOOLEAN DEFAULT FALSE
        )",
        
        "minimumrequirements" => "
        CREATE TABLE IF NOT EXISTS minimumrequirements (
            RequirementID INT PRIMARY KEY,
            GameID VARCHAR(10) NOT NULL,
            FOREIGN KEY (GameID) REFERENCES game(GameID) ON DELETE CASCADE
        )",
        
        "cpu" => "
        CREATE TABLE IF NOT EXISTS cpu (
            CPUID INT PRIMARY KEY,
            RequirementID INT NOT NULL,
            Model VARCHAR(100) NOT NULL,
            Manufacturer VARCHAR(50) NOT NULL,
            Cores INT NOT NULL,
            ClockFrequency DECIMAL(3,1) NOT NULL,
            FOREIGN KEY (RequirementID) REFERENCES minimumrequirements(RequirementID) ON DELETE CASCADE
        )",
        
        "gpu" => "
        CREATE TABLE IF NOT EXISTS gpu (
            GPUID INT PRIMARY KEY,
            RequirementID INT NOT NULL,
            Model VARCHAR(100) NOT NULL,
            Manufacturer VARCHAR(50) NOT NULL,
            VRAMSize INT NOT NULL,
            FOREIGN KEY (RequirementID) REFERENCES minimumrequirements(RequirementID) ON DELETE CASCADE
        )",
        
        "ram" => "
        CREATE TABLE IF NOT EXISTS ram (
            RAMID INT PRIMARY KEY,
            RequirementID INT NOT NULL,
            Size INT NOT NULL,
            Type VARCHAR(10) NOT NULL,
            FOREIGN KEY (RequirementID) REFERENCES minimumrequirements(RequirementID) ON DELETE CASCADE
        )",
        
        "storage" => "
        CREATE TABLE IF NOT EXISTS storage (
            StorageID INT PRIMARY KEY,
            RequirementID INT NOT NULL,
            Size INT NOT NULL,
            Type VARCHAR(10) NOT NULL,
            FOREIGN KEY (RequirementID) REFERENCES minimumrequirements(RequirementID) ON DELETE CASCADE
        )",
        
        "restriction" => "
        CREATE TABLE IF NOT EXISTS restriction (
            PEGI_ID VARCHAR(10) PRIMARY KEY,
            Category VARCHAR(20) NOT NULL
        )",
        
        "game_publisher" => "
        CREATE TABLE IF NOT EXISTS game_publisher (
            GameID VARCHAR(10) NOT NULL,
            PublisherID VARCHAR(10) NOT NULL,
            PRIMARY KEY (GameID, PublisherID),
            FOREIGN KEY (GameID) REFERENCES game(GameID) ON DELETE CASCADE,
            FOREIGN KEY (PublisherID) REFERENCES publisher(PublisherID) ON DELETE CASCADE
        )",
        
        "restrictedby" => "
        CREATE TABLE IF NOT EXISTS restrictedby (
            RestrictionID VARCHAR(10) NOT NULL,
            GameID VARCHAR(10) NOT NULL,
            PRIMARY KEY (RestrictionID, GameID),
            FOREIGN KEY (RestrictionID) REFERENCES restriction(PEGI_ID) ON DELETE CASCADE,
            FOREIGN KEY (GameID) REFERENCES game(GameID) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $tableName => $sql) {
        if ($mysqli->query($sql)) {
            echo "✅ Table '$tableName' created<br>";
        } else {
            echo "❌ Error creating table '$tableName': " . $mysqli->error . "<br>";
        }
    }
    
    // Step 2: Import your data from the PHP arrays
    echo "<br><h3>📥 Importing Data from gamingdatabase.php...</h3>";
    
    if (file_exists('gamingdatabase.php')) {
        // Include your data file
        include 'gamingdatabase.php';
        
        // Import each table's data
        $dataTables = [
            'publisher' => $publisher,
            'game' => $game,
            'restriction' => $restriction,
            'minimumrequirements' => $minimumrequirements,
            'cpu' => $cpu,
            'gpu' => $gpu,
            'ram' => $ram,
            'storage' => $storage,
            'game_publisher' => $game_publisher,
            'restrictedby' => $restrictedby
        ];
        
        foreach ($dataTables as $tableName => $data) {
            echo "<strong>Importing $tableName:</strong> ";
            
            if (empty($data)) {
                echo "<span style='color: orange;'>No data</span><br>";
                continue;
            }
            
            // Clear existing data
            $mysqli->query("DELETE FROM $tableName");
            
            // Get column names from first row
            $columns = array_keys($data[0]);
            $columnList = implode(', ', $columns);
            $placeholders = str_repeat('?,', count($columns) - 1) . '?';
            
            $sql = "INSERT INTO $tableName ($columnList) VALUES ($placeholders)";
            $stmt = $mysqli->prepare($sql);
            
            $successCount = 0;
            foreach ($data as $row) {
                $values = array_values($row);
                $stmt->bind_param(str_repeat('s', count($values)), ...$values);
                if ($stmt->execute()) {
                    $successCount++;
                }
            }
            
            echo "<span style='color: green;'>$successCount records imported</span><br>";
        }
    } else {
        echo "❌ gamingdatabase.php not found<br>";
    }
    
    // Step 3: Create Gaming-specific Triggers
    echo "<br><h3>🔄 Creating Gaming Triggers...</h3>";
    
    $triggers = [
        "update_publisher_game_count" => "
        DROP TRIGGER IF EXISTS update_publisher_game_count;
        CREATE TRIGGER update_publisher_game_count
        AFTER INSERT ON game_publisher
        FOR EACH ROW
        BEGIN
            UPDATE publisher 
            SET NumberOfGames = (
                SELECT COUNT(*) 
                FROM game_publisher 
                WHERE PublisherID = NEW.PublisherID
            )
            WHERE PublisherID = NEW.PublisherID;
        END",
        
        "check_game_requirements" => "
        DROP TRIGGER IF EXISTS check_game_requirements;
        CREATE TRIGGER check_game_requirements
        BEFORE INSERT ON minimumrequirements
        FOR EACH ROW
        BEGIN
            DECLARE game_exists INT DEFAULT 0;
            SELECT COUNT(*) INTO game_exists FROM game WHERE GameID = NEW.GameID;
            IF game_exists = 0 THEN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Game must exist before adding requirements';
            END IF;
        END",
        
        "log_game_updates" => "
        DROP TRIGGER IF EXISTS log_game_updates;
        CREATE TRIGGER log_game_updates
        AFTER UPDATE ON game
        FOR EACH ROW
        BEGIN
            INSERT INTO game_audit_log (GameID, old_title, new_title, changed_at)
            VALUES (NEW.GameID, OLD.Title, NEW.Title, NOW());
        END"
    ];
    
    // First create audit log table for the trigger
    $mysqli->query("
        CREATE TABLE IF NOT EXISTS game_audit_log (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            GameID VARCHAR(10) NOT NULL,
            old_title VARCHAR(150),
            new_title VARCHAR(150),
            changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    foreach ($triggers as $triggerName => $sql) {
        if ($mysqli->multi_query($sql)) {
            // Process all results
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->next_result());
            echo "✅ Trigger '$triggerName' created<br>";
        } else {
            echo "❌ Error creating trigger '$triggerName': " . $mysqli->error . "<br>";
        }
    }
    
    // Step 4: Create Gaming-specific Stored Procedures
    echo "<br><h3>⚙ Creating Gaming Stored Procedures...</h3>";
    
    $procedures = [
        "GetGameInfo" => "
        DROP PROCEDURE IF EXISTS GetGameInfo;
        CREATE PROCEDURE GetGameInfo(IN p_game_id VARCHAR(10))
        BEGIN
            SELECT 
                g.GameID,
                g.Title,
                g.ReleaseYear,
                g.Genre,
                g.Platform,
                p.PublisherName,
                r.Category as AgeRating
            FROM game g
            LEFT JOIN game_publisher gp ON g.GameID = gp.GameID
            LEFT JOIN publisher p ON gp.PublisherID = p.PublisherID
            LEFT JOIN restrictedby rb ON g.GameID = rb.GameID
            LEFT JOIN restriction r ON rb.RestrictionID = r.PEGI_ID
            WHERE g.GameID = p_game_id;
        END",
        
        "GetSystemRequirements" => "
        DROP PROCEDURE IF EXISTS GetSystemRequirements;
        CREATE PROCEDURE GetSystemRequirements(IN p_game_id VARCHAR(10))
        BEGIN
            SELECT 
                g.Title as GameTitle,
                c.Model as CPU_Model,
                c.Cores as CPU_Cores,
                c.ClockFrequency as CPU_Speed,
                gpu.Model as GPU_Model,
                gpu.VRAMSize as GPU_VRAM,
                ram.Size as RAM_Size,
                ram.Type as RAM_Type,
                s.Size as Storage_Size,
                s.Type as Storage_Type
            FROM game g
            JOIN minimumrequirements mr ON g.GameID = mr.GameID
            JOIN cpu c ON mr.RequirementID = c.RequirementID
            JOIN gpu ON mr.RequirementID = gpu.RequirementID
            JOIN ram ON mr.RequirementID = ram.RAMID
            JOIN storage s ON mr.RequirementID = s.RequirementID
            WHERE g.GameID = p_game_id;
        END",
        
        "AddNewGame" => "
        DROP PROCEDURE IF EXISTS AddNewGame;
        CREATE PROCEDURE AddNewGame(
            IN p_game_id VARCHAR(10),
            IN p_title VARCHAR(150),
            IN p_year INT,
            IN p_genre VARCHAR(50),
            IN p_platform VARCHAR(50),
            IN p_publisher_id VARCHAR(10),
            OUT p_message VARCHAR(255)
        )
        BEGIN
            DECLARE game_exists INT DEFAULT 0;
            DECLARE publisher_exists INT DEFAULT 0;
            
            -- Check if game already exists
            SELECT COUNT(*) INTO game_exists FROM game WHERE GameID = p_game_id;
            
            IF game_exists > 0 THEN
                SET p_message = 'Game already exists';
            ELSE
                -- Check if publisher exists
                SELECT COUNT(*) INTO publisher_exists FROM publisher WHERE PublisherID = p_publisher_id;
                
                IF publisher_exists = 0 THEN
                    SET p_message = 'Publisher does not exist';
                ELSE
                    -- Insert new game
                    INSERT INTO game (GameID, Title, ReleaseYear, Genre, Platform)
                    VALUES (p_game_id, p_title, p_year, p_genre, p_platform);
                    
                    -- Link with publisher
                    INSERT INTO game_publisher (GameID, PublisherID)
                    VALUES (p_game_id, p_publisher_id);
                    
                    SET p_message = 'Game added successfully';
                END IF;
            END IF;
        END"
    ];
    
    foreach ($procedures as $procName => $sql) {
        if ($mysqli->multi_query($sql)) {
            // Process all results
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->next_result());
            echo "✅ Procedure '$procName' created<br>";
        } else {
            echo "❌ Error creating procedure '$procName': " . $mysqli->error . "<br>";
        }
    }
    
    // Step 5: Verification
    echo "<br><h3>🔍 Database Verification</h3>";
    
    // Count records in each table
    $tableNames = ['publisher', 'game', 'cpu', 'gpu', 'ram', 'storage'];
    echo "<h4>📊 Data Summary:</h4>";
    foreach ($tableNames as $table) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
        $count = $result->fetch_assoc()['count'];
        echo "• $table: $count records<br>";
    }
    
    // Show triggers
    echo "<h4>🔄 Active Triggers:</h4>";
    $result = $mysqli->query("SHOW TRIGGERS");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "• <strong>" . $row['Trigger'] . "</strong> (on " . $row['Table'] . ")<br>";
        }
    } else {
        echo "No triggers found<br>";
    }
    
    // Show procedures
    echo "<h4>⚙ Available Procedures:</h4>";
    $result = $mysqli->query("SHOW PROCEDURE STATUS WHERE Db = 'GamingDatabase'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "• <strong>" . $row['Name'] . "</strong><br>";
        }
    } else {
        echo "No procedures found<br>";
    }
    
    echo "<br><div style='background-color: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745;'>";
    echo "<strong>🎉 Gaming Database Setup Complete!</strong><br><br>";
    echo "<strong>✅ What was created:</strong><br>";
    echo "• Complete gaming database structure<br>";
    echo "• All your gaming data imported<br>";
    echo "• 3 gaming-specific triggers<br>";
    echo "• 3 gaming-specific stored procedures<br><br>";
    echo "<strong>🚀 Next steps:</strong><br>";
    echo "<a href='index.php' style='color: #155724; font-weight: bold; margin-right: 15px;'>← Back to Homepage</a>";
    echo "<a href='trigger_gaming1.php' style='color: #155724; font-weight: bold; margin-right: 15px;'>Test Triggers</a>";
    echo "<a href='procedure_gaming1.php' style='color: #155724; font-weight: bold;'>Test Procedures</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; border-left: 5px solid #dc3545;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}
h2, h3, h4 {
    color: #333;
}
a {
    color: #007bff;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>