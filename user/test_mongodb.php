<?php
// Test MongoDB Installation
echo "<h2>MongoDB Installation Test</h2>";

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
        $database = $client->selectDatabase('test_db');
        $collection = $database->selectCollection('test_collection');
        
        echo "✅ MongoDB library is working!<br>";
        echo "✅ Database and collection objects created successfully!<br>";
        
        // Test insert
        $insertResult = $collection->insertOne([
            'test' => 'hello',
            'timestamp' => new MongoDB\BSON\UTCDateTime()
        ]);
        echo "✅ Test document inserted with ID: " . $insertResult->getInsertedId() . "<br>";
        
        // Test find
        $document = $collection->findOne(['test' => 'hello']);
        if ($document) {
            echo "✅ Test document found: " . json_encode($document) . "<br>";
        }
        
        // Clean up - delete test document
        $collection->deleteOne(['test' => 'hello']);
        echo "✅ Test document cleaned up<br>";
        
    } catch (Exception $e) {
        echo "❌ MongoDB library error: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ MongoDB library not found. Run 'composer require mongodb/mongodb' first.<br>";
}

echo "<h3>4. PHP Configuration Info:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MongoDB Extension Version: " . (extension_loaded('mongodb') ? phpversion('mongodb') : 'Not loaded') . "<br>";

// Show loaded extensions
echo "<h3>5. Loaded Extensions (MongoDB related):</h3>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (stripos($ext, 'mongo') !== false) {
        echo "✅ " . $ext . "<br>";
    }
}
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
</style>