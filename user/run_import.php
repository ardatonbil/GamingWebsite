<?php
require_once 'config/database.php';

echo "<h2>Importing Your Gaming Database</h2>";
echo "<p>This will import your custom triggers and stored procedures.</p>";

try {
    $mysqli = getMySQLConnection();
    echo "✅ MySQL connection successful!<br><br>";
    
    // Read your SQL file
    $sqlFile = 'gamingdatabase.php';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("File 'gamingdatabase.php' not found. Make sure it's in the user folder.");
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    if (empty($sqlContent)) {
        throw new Exception("SQL file is empty or could not be read.");
    }
    
    echo "<h3>📁 File loaded successfully</h3>";
    echo "<p>File size: " . strlen($sqlContent) . " characters</p>";
    
    // Remove PHP tags if they exist
    $sqlContent = str_replace(['<?php', '<?', '?>'], '', $sqlContent);
    
    // Split SQL statements by semicolon
    $statements = explode(';', $sqlContent);
    
    echo "<h3>🔄 Executing SQL Statements...</h3>";
    
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        // Skip empty statements
        if (empty($statement) || strlen($statement) < 5) {
            continue;
        }
        
        // Skip comments
        if (substr($statement, 0, 2) === '--' || substr($statement, 0, 2) === '/*') {
            continue;
        }
        
        echo "<div style='margin: 10px 0; padding: 10px; background-color: #f8f9fa; border-radius: 5px;'>";
        echo "<strong>Statement " . ($index + 1) . ":</strong> " . substr($statement, 0, 100) . "...<br>";
        
        if ($mysqli->query($statement)) {
            echo "<span style='color: green;'>✅ Success</span>";
            $successCount++;
        } else {
            echo "<span style='color: red;'>❌ Error: " . $mysqli->error . "</span>";
            $errorCount++;
        }
        echo "</div>";
    }
    
    echo "<br><h3>📊 Import Summary</h3>";
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; border-left: 5px solid #28a745;'>";
    echo "<strong>✅ Successful statements:</strong> $successCount<br>";
    echo "<strong>❌ Failed statements:</strong> $errorCount<br>";
    echo "</div>";
    
    // Show what was created
    echo "<br><h3>🔍 Database Objects Created</h3>";
    
    // Show tables
    echo "<h4>Tables:</h4>";
    $result = $mysqli->query("SHOW TABLES");
    while ($row = $result->fetch_array()) {
        echo "• " . $row[0] . "<br>";
    }
    
    // Show triggers
    echo "<h4>Triggers:</h4>";
    $result = $mysqli->query("SHOW TRIGGERS");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "• " . $row['Trigger'] . " (on table: " . $row['Table'] . ")<br>";
        }
    } else {
        echo "No triggers found<br>";
    }
    
    // Show procedures
    echo "<h4>Stored Procedures:</h4>";
    $result = $mysqli->query("SHOW PROCEDURE STATUS WHERE Db = 'cs306_project'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "• " . $row['Name'] . "<br>";
        }
    } else {
        echo "No procedures found<br>";
    }
    
    echo "<br><div style='background-color: #cff4fc; padding: 15px; border-radius: 5px; border-left: 5px solid #0dcaf0;'>";
    echo "<strong>🎉 Import completed!</strong><br>";
    echo "Now you can create web pages for your triggers and procedures.<br>";
    echo "<a href='index.php'>← Back to Homepage</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; border-left: 5px solid #dc3545;'>";
    echo "<strong>❌ Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Things to check:</strong><br>";
    echo "1. Make sure 'gamingdatabase.php' is in the user folder<br>";
    echo "2. Check if MySQL is running<br>";
    echo "3. Verify database connection settings<br>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 900px;
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