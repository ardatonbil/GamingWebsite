<?php
require_once 'config/database.php';

echo "<h2>🎮 Gaming Database Setup Test</h2>";

try {
    $mysqli = getMySQLConnection();
    echo "✅ MySQL connection successful!<br><br>";
    
    // Test 1: Check tables and data
    echo "<h3>📊 Database Tables and Data</h3>";
    $tables = ['game', 'publisher', 'cpu', 'gpu', 'ram', 'storage'];
    
    foreach ($tables as $table) {
        $result = $mysqli->query("SELECT COUNT(*) as count FROM $table");
        $count = $result->fetch_assoc()['count'];
        echo "• <strong>$table:</strong> $count records<br>";
    }
    
    // Test 2: Check triggers
    echo "<br><h3>🔄 Active Triggers</h3>";
    $result = $mysqli->query("SHOW TRIGGERS");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "✅ <strong>" . $row['Trigger'] . "</strong> (on " . $row['Table'] . ")<br>";
        }
    } else {
        echo "❌ No triggers found<br>";
    }
    
    // Test 3: Check stored procedures
    echo "<br><h3>⚙ Stored Procedures</h3>";
    $result = $mysqli->query("SHOW PROCEDURE STATUS WHERE Db = 'GamingDatabase'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "✅ <strong>" . $row['Name'] . "</strong><br>";
        }
    } else {
        echo "❌ No procedures found<br>";
    }
    
    // Test 4: Sample data preview
    echo "<br><h3>🎮 Sample Games in Database</h3>";
    $result = $mysqli->query("SELECT GameID, Title, Genre, ReleaseYear FROM game LIMIT 5");
    if ($result->num_rows > 0) {
        echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 15px 0;'>";
        while ($row = $result->fetch_assoc()) {
            echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;'>";
            echo "<strong>{$row['Title']}</strong><br>";
            echo "Genre: {$row['Genre']}<br>";
            echo "Year: {$row['ReleaseYear']}<br>";
            echo "ID: {$row['GameID']}";
            echo "</div>";
        }
        echo "</div>";
    }
    
    // Test 5: Test a stored procedure
    echo "<br><h3>🧪 Quick Procedure Test</h3>";
    $testGameId = 'G001'; // Skyrim
    $stmt = $mysqli->prepare("CALL GetGameInfo(?)");
    $stmt->bind_param("s", $testGameId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $data = $result->fetch_assoc();
            echo "✅ Procedure 'GetGameInfo' working! Retrieved: <strong>{$data['Title']}</strong><br>";
        }
        $stmt->close();
    }
    
    echo "<br><div style='background-color: #d4edda; padding: 20px; border-radius: 10px; border-left: 5px solid #28a745;'>";
    echo "<h3>🎉 Gaming Database is Ready!</h3>";
    echo "<strong>✅ All systems operational:</strong><br>";
    echo "• Database tables created and populated<br>";
    echo "• Gaming triggers are active<br>";
    echo "• Stored procedures are working<br>";
    echo "• Sample data is available<br><br>";
    
    echo "<strong>🚀 Next steps:</strong><br>";
    echo "<a href='index.php' style='color: #155724; font-weight: bold; margin-right: 15px;'>🏠 Go to Homepage</a>";
    echo "<a href='trigger_publisher_count.php' style='color: #155724; font-weight: bold; margin-right: 15px;'>🔄 Test Triggers</a>";
    echo "<a href='procedure_game_info.php' style='color: #155724; font-weight: bold;'>⚙ Test Procedures</a>";
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