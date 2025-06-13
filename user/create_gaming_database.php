<?php
echo "<h2>Creating Gaming Database</h2>";

// Database configuration without specifying a database
$host = "localhost";
$username = "root";
$password = "";
$port = "3306"; // Change to 3307 if you changed MySQL port

try {
    // Connect to MySQL server without specifying a database
    $mysqli = new mysqli($host, $username, $password, "", $port);
    
    if ($mysqli->connect_error) {
        throw new Exception("Connection failed: " . $mysqli->connect_error);
    }
    
    echo "✅ Connected to MySQL server successfully!<br><br>";
    
    // Check if database already exists
    $result = $mysqli->query("SHOW DATABASES LIKE 'GamingDatabase'");
    
    if ($result->num_rows > 0) {
        echo "⚠ Database 'GamingDatabase' already exists<br>";
        echo "Dropping existing database...<br>";
        
        if ($mysqli->query("DROP DATABASE GamingDatabase")) {
            echo "✅ Existing database dropped<br>";
        } else {
            throw new Exception("Error dropping database: " . $mysqli->error);
        }
    }
    
    // Create new database
    if ($mysqli->query("CREATE DATABASE GamingDatabase")) {
        echo "✅ Database 'GamingDatabase' created successfully!<br>";
    } else {
        throw new Exception("Error creating database: " . $mysqli->error);
    }
    
    // Select the new database
    if ($mysqli->select_db("GamingDatabase")) {
        echo "✅ Database 'GamingDatabase' selected<br>";
    } else {
        throw new Exception("Error selecting database: " . $mysqli->error);
    }
    
    echo "<br><div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 5px solid #28a745;'>";
    echo "<strong>🎮 Gaming Database Setup Complete!</strong><br><br>";
    echo "<strong>Next steps:</strong><br>";
    echo "1. <a href='run_import.php' style='color: #155724; font-weight: bold;'>Import Your Gaming Database SQL</a><br>";
    echo "2. <a href='test_mysql.php' style='color: #155724; font-weight: bold;'>Test MySQL Connection</a><br>";
    echo "3. <a href='setup_mongodb.php' style='color: #155724; font-weight: bold;'>Setup MongoDB (Support Tickets)</a><br>";
    echo "4. <a href='index.php' style='color: #155724; font-weight: bold;'>Return to Homepage</a><br>";
    echo "</div>";
    
    // Show MySQL server info
    echo "<br><h3>📊 MySQL Server Information</h3>";
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<strong>Server Version:</strong> " . $mysqli->server_info . "<br>";
    echo "<strong>Host:</strong> " . $host . "<br>";
    echo "<strong>Port:</strong> " . $port . "<br>";
    echo "<strong>Character Set:</strong> " . $mysqli->character_set_name() . "<br>";
    echo "<strong>Database Name:</strong> GamingDatabase<br>";
    echo "</div>";
    
    // Show what you should do with your gamingdatabase.php file
    echo "<br><h3>📋 About Your gamingdatabase.php File</h3>";
    echo "<div style='background-color: #fff3cd; padding: 15px; border-radius: 5px; border-left: 5px solid #ffc107;'>";
    echo "<strong>Important:</strong> Your existing 'gamingdatabase.php' file contains the SQL commands for your tables, triggers, and stored procedures.<br><br>";
    echo "<strong>Next step:</strong> Click 'Import Your Gaming Database SQL' above to run those SQL commands and create your gaming-specific tables, triggers, and procedures.<br><br>";
    echo "<strong>The import will:</strong><br>";
    echo "• Create your gaming tables (players, games, scores, etc.)<br>";
    echo "• Set up your custom triggers<br>";
    echo "• Install your stored procedures<br>";
    echo "• Insert any sample data you have<br>";
    echo "</div>";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; border-left: 5px solid #dc3545;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Troubleshooting steps:</strong><br>";
    echo "1. Make sure MySQL is running in XAMPP Control Panel<br>";
    echo "2. Check if you're using the correct port (3306 or 3307)<br>";
    echo "3. Try accessing phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a><br>";
    echo "4. If port 3306 doesn't work, try changing port to 3307 in config/database.php<br>";
    echo "</div>";
    
    echo "<br><h3>🔧 Quick Port Test</h3>";
    echo "<p>Let's test both common MySQL ports:</p>";
    
    // Test port 3306
    echo "<strong>Testing port 3306:</strong> ";
    $test_mysqli = @new mysqli($host, $username, $password, "", "3306");
    if ($test_mysqli->connect_error) {
        echo "<span style='color: red;'>❌ Failed</span><br>";
    } else {
        echo "<span style='color: green;'>✅ Working - Use this port in your config</span><br>";
        $test_mysqli->close();
    }
    
    // Test port 3307
    echo "<strong>Testing port 3307:</strong> ";
    $test_mysqli = @new mysqli($host, $username, $password, "", "3307");
    if ($test_mysqli->connect_error) {
        echo "<span style='color: red;'>❌ Failed</span><br>";
    } else {
        echo "<span style='color: green;'>✅ Working - Use this port in your config</span><br>";
        $test_mysqli->close();
    }
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
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