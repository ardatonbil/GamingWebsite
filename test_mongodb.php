<?php
// Test MongoDB Installation - Admin Interface
echo "<h2>Admin MongoDB Installation Test</h2>";

// Test 1: Check if MongoDB extension is loaded
echo "<h3>1. PHP MongoDB Extension Test:</h3>";
if (extension_loaded('mongodb')) {
    echo "✅ MongoDB PHP extension is loaded!<br>";
} else {
    echo "❌ MongoDB PHP extension is NOT loaded!<br>";
    echo "Please install the MongoDB PHP extension (.dll file)<br>";
}

// Test 2: Test basic MongoDB connection
echo "<h3>2. MongoDB Connection Test:</h3>";
try {
    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    echo "✅ MongoDB connection successful!<br>";
} catch (Exception $e) {
    echo "❌ MongoDB connection failed: " . $e->getMessage() . "<br>";
    echo "Make sure MongoDB server is running<br>";
}

// Test 3: Test MongoDB PHP Library
echo "<h3>3. MongoDB PHP Library Test:</h3>";
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
    try {
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $database = $client->selectDatabase('GamingDatabase');
        $collection = $database->selectCollection('support_tickets');
        
        echo "✅ MongoDB library is working!<br>";
        echo "✅ Database and collection objects created successfully!<br>";
        
        // Test reading existing tickets
        $ticketCount = $collection->countDocuments();
        echo "✅ Found " . $ticketCount . " tickets in the database<br>";
        
        // Show some tickets
        if ($ticketCount > 0) {
            echo "<h4>Sample tickets:</h4>";
            $tickets = $collection->find([], ['limit' => 3]);
            foreach ($tickets as $ticket) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 5px 0;'>";
                echo "<strong>User:</strong> " . $ticket['username'] . "<br>";
                echo "<strong>Message:</strong> " . substr($ticket['message'], 0, 50) . "...<br>";
                echo "<strong>Status:</strong> " . ($ticket['status'] ? 'Active' : 'Resolved') . "<br>";
                echo "</div>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ MongoDB library error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ MongoDB library not found. Run 'composer require mongodb/mongodb' first.<br>";
}

echo "<h3>4. Admin Interface Info:</h3>";
echo "Admin interface URL: <a href='index.php'>http://localhost/admin/</a><br>";
echo "User interface URL: <a href='../user/index.php'>http://localhost/user/</a><br>";

echo "<h3>5. PHP Configuration Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MongoDB Extension Version: " . (extension_loaded('mongodb') ? phpversion('mongodb') : 'Not loaded') . "<br>";
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
h2, h3 {
    color: #333;
}
div {
    margin: 5px 0;
}
</style>