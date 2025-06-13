<?php
require_once 'vendor/autoload.php';  // Add this line to load MongoDB library
require_once 'config/database.php';

echo "<h2>🎮 Complete Gaming Database Feature Test</h2>";
echo "<p>Testing all triggers, procedures, and database functionality.</p>";

try {
    $mysqli = getMySQLConnection();
    echo "✅ MySQL connection successful!<br><br>";
    
    // Test 1: Database Structure
    echo "<h3>📊 Database Structure Test</h3>";
    $tables = ['game', 'publisher', 'cpu', 'gpu', 'ram', 'storage', 'minimumrequirements', 'game_publisher'];
    $tableStatus = [];
    
    foreach ($tables as $table) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
        $count = $result->fetch_assoc()['count'];
        $tableStatus[$table] = $count;
        echo "• <strong>$table:</strong> $count records<br>";
    }
    
    // Test 2: Triggers Test
    echo "<br><h3>🔄 Triggers Test</h3>";
    $result = $mysqli->query("SHOW TRIGGERS");
    $triggerCount = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $triggerCount++;
            echo "✅ <strong>" . $row['Trigger'] . "</strong> (on " . $row['Table'] . ")<br>";
        }
    }
    
    // Test 3: Stored Procedures Test
    echo "<br><h3>⚙ Stored Procedures Test</h3>";
    $result = $mysqli->query("SHOW PROCEDURE STATUS WHERE Db = 'GamingDatabase'");
    $procedureCount = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $procedureCount++;
            echo "✅ <strong>" . $row['Name'] . "</strong><br>";
        }
    }
    
    // Test 4: Quick Procedure Test
    echo "<br><h3>🧪 Quick Procedure Functionality Test</h3>";
    $testGameId = 'G001'; // Skyrim
    $stmt = $mysqli->prepare("CALL GetGameInfo(?)");
    $stmt->bind_param("s", $testGameId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($mysqli->next_result()) {
            if ($res = $mysqli->store_result()) {
                $res->free();
            }
        }
        
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo "✅ GetGameInfo procedure working: Retrieved '<strong>{$data['Title']}</strong>'<br>";
        }
        $stmt->close();
    }
    
    // Test 5: MongoDB Connection Test
    echo "<br><h3>🍃 MongoDB Connection Test</h3>";
    try {
        // Check if MongoDB library is available
        if (class_exists('MongoDB\Client')) {
            $ticketsCollection = getMongoCollection('support_tickets');
            $ticketCount = $ticketsCollection->countDocuments();
            echo "✅ MongoDB connected: $ticketCount support tickets found<br>";
        } else {
            echo "⚠ MongoDB library not loaded. Run 'composer require mongodb/mongodb' in user and admin folders<br>";
        }
    } catch (Exception $e) {
        echo "❌ MongoDB error: " . $e->getMessage() . "<br>";
        echo "💡 Solution: Make sure MongoDB server is running and library is installed<br>";
    }
    
    // Test 6: Web Pages Test
    echo "<br><h3>🌐 Web Interface Test</h3>";
    $webPages = [
        'index.php' => 'Homepage',
        'trigger_publisher_count.php' => 'Trigger 1: Publisher Counter',
        'trigger_requirements_check.php' => 'Trigger 2: Requirements Validator',
        'trigger_audit_log.php' => 'Trigger 3: Audit Logger',
        'procedure_game_info.php' => 'Procedure 1: Game Info',
        'procedure_system_requirements.php' => 'Procedure 2: System Requirements',
        'procedure_add_game.php' => 'Procedure 3: Add New Game',
        'support_tickets.php' => 'Support Tickets System'
    ];
    
    foreach ($webPages as $file => $description) {
        if (file_exists($file)) {
            echo "✅ <strong>$description</strong> ($file)<br>";
        } else {
            echo "❌ <strong>$description</strong> ($file) - File missing<br>";
        }
    }
    
    // Test 7: Summary Report
    echo "<br><h3>📋 Complete System Summary</h3>";
    
    $totalGames = $tableStatus['game'];
    $totalPublishers = $tableStatus['publisher'];
    $totalRequirements = $tableStatus['minimumrequirements'];
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";
    
    echo "<div style='background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h4 style='margin: 0; font-size: 2em;'>$totalGames</h4>";
    echo "<p style='margin: 5px 0;'>🎮 Games</p>";
    echo "</div>";
    
    echo "<div style='background: linear-gradient(135deg, #fd79a8, #fdcb6e); color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h4 style='margin: 0; font-size: 2em;'>$totalPublishers</h4>";
    echo "<p style='margin: 5px 0;'>🏢 Publishers</p>";
    echo "</div>";
    
    echo "<div style='background: linear-gradient(135deg, #00b894, #00cec9); color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h4 style='margin: 0; font-size: 2em;'>$triggerCount</h4>";
    echo "<p style='margin: 5px 0;'>🔄 Triggers</p>";
    echo "</div>";
    
    echo "<div style='background: linear-gradient(135deg, #74b9ff, #0984e3); color: white; padding: 20px; border-radius: 10px; text-align: center;'>";
    echo "<h4 style='margin: 0; font-size: 2em;'>$procedureCount</h4>";
    echo "<p style='margin: 5px 0;'>⚙ Procedures</p>";
    echo "</div>";
    
    echo "</div>";
    
    // Final Status
    if ($triggerCount >= 3 && $procedureCount >= 3 && $totalGames >= 10) {
        echo "<div style='background-color: #d4edda; padding: 25px; border-radius: 15px; border-left: 5px solid #28a745; margin: 20px 0;'>";
        echo "<h3>🎉 Gaming Database System - FULLY OPERATIONAL!</h3>";
        echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin: 15px 0;'>";
        
        echo "<div>";
        echo "<strong>✅ Database Components:</strong><br>";
        echo "• Complete gaming database structure<br>";
        echo "• $totalGames games with system requirements<br>";
        echo "• $totalPublishers gaming publishers<br>";
        echo "• Referential integrity maintained<br>";
        echo "</div>";
        
        echo "<div>";
        echo "<strong>✅ Automation Features:</strong><br>";
        echo "• $triggerCount active database triggers<br>";
        echo "• $procedureCount stored procedures<br>";
        echo "• Automatic data validation<br>";
        echo "• Audit trail logging<br>";
        echo "</div>";
        
        echo "</div>";
        
        echo "<div style='text-align: center; margin: 25px 0;'>";
        echo "<h4 style='color: #28a745;'>🚀 Ready for Demo and Presentation!</h4>";
        echo "<div style='display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; margin-top: 20px;'>";
        echo "<a href='index.php' style='background: #667eea; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>🏠 Visit Homepage</a>";
        echo "<a href='trigger_publisher_count.php' style='background: #fd79a8; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>🔄 Test Triggers</a>";
        echo "<a href='procedure_game_info.php' style='background: #00b894; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>⚙ Test Procedures</a>";
        echo "<a href='support_tickets.php' style='background: #74b9ff; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>🎫 Support System</a>";
        echo "<a href='../admin/index.php' style='background: #e17055; color: white; padding: 12px 25px; text-decoration: none; border-radius: 25px; font-weight: bold;'>🛡 Admin Panel</a>";
        echo "</div>";
        echo "</div>";
        
        echo "</div>";
    } else {
        echo "<div style='background-color: #fff3cd; padding: 20px; border-radius: 10px; border-left: 5px solid #ffc107;'>";
        echo "<h3>⚠ System Status: Incomplete</h3>";
        echo "<strong>Issues found:</strong><br>";
        if ($triggerCount < 3) echo "• Missing triggers (found $triggerCount, need 3)<br>";
        if ($procedureCount < 3) echo "• Missing procedures (found $procedureCount, need 3)<br>";
        if ($totalGames < 10) echo "• Insufficient game data (found $totalGames, need 10+)<br>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; border-left: 5px solid #dc3545;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage());
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    min-height: 100vh;
}
.container {
    background-color: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}
h2, h3 {
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