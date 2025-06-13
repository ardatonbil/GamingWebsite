<?php
// MongoDB Setup for Support Tickets System
// Save this as: user/setup_mongodb.php

require_once 'vendor/autoload.php';

echo "<h2>Setting up MongoDB for CS306 Support Tickets</h2>";
echo "<p>This will create the database structure and sample data for the support ticket system.</p>";

try {
    // Connect to MongoDB
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->selectDatabase('GamingDatabase');
    $collection = $database->selectCollection('support_tickets');
    
    echo "<h3>✅ MongoDB Connection Successful!</h3>";
    echo "<strong>Database:</strong> GamingDatabase<br>";
    echo "<strong>Collection:</strong> support_tickets<br><br>";
    
    // Clear existing data (for fresh setup)
    $deleteResult = $collection->deleteMany([]);
    echo "<p>🗑 Cleared existing tickets: " . $deleteResult->getDeletedCount() . " documents removed</p>";
    
    // Create sample tickets that match your project document examples
    $sampleTickets = [
        [
            'username' => 'celebrimbor_fan',
            'message' => 'please help!!! urgent ask issue...',
            'created_at' => '2025-05-02 23:02:46',
            'status' => true,
            'comments' => [
                [
                    'username' => 'admin',
                    'comment' => 'what do you want?',
                    'created_at' => '2025-05-03 13:59:17'
                ],
                [
                    'username' => 'celebrimbor_fan',
                    'comment' => 'i am not able to access...',
                    'created_at' => '2025-05-03 15:01:22'
                ]
            ]
        ],
        [
            'username' => 'eric_cantona',
            'message' => 'this will be my second ticket',
            'created_at' => '2025-05-02 23:06:01',
            'status' => true,
            'comments' => []
        ],
        [
            'username' => 'alice_cooper',
            'message' => 'Cannot access my grades in the system',
            'created_at' => '2025-05-01 14:30:15',
            'status' => true,
            'comments' => [
                [
                    'username' => 'admin',
                    'comment' => 'We are looking into this issue.',
                    'created_at' => '2025-05-01 15:45:30'
                ]
            ]
        ],
        [
            'username' => 'bob_dylan',
            'message' => 'Database connection timeout error',
            'created_at' => '2025-04-30 10:15:00',
            'status' => true,
            'comments' => []
        ],
        [
            'username' => 'charlie_brown',
            'message' => 'Course enrollment not working properly',
            'created_at' => '2025-04-29 16:20:45',
            'status' => false, // This one is resolved
            'comments' => [
                [
                    'username' => 'admin',
                    'comment' => 'Issue has been fixed. Please try again.',
                    'created_at' => '2025-04-29 17:00:00'
                ],
                [
                    'username' => 'charlie_brown',
                    'comment' => 'Working now, thank you!',
                    'created_at' => '2025-04-29 17:30:00'
                ]
            ]
        ]
    ];
    
    // Insert sample tickets
    $insertResult = $collection->insertMany($sampleTickets);
    echo "<h3>✅ Sample Data Created Successfully!</h3>";
    echo "<p>📄 Inserted <strong>" . count($insertResult->getInsertedIds()) . "</strong> sample tickets</p>";
    
    // Show statistics
    $activeTickets = $collection->countDocuments(['status' => true]);
    $resolvedTickets = $collection->countDocuments(['status' => false]);
    $totalComments = 0;
    
    $allTickets = $collection->find();
    foreach ($allTickets as $ticket) {
        $totalComments += count($ticket['comments']);
    }
    
    echo "<h3>📊 Database Statistics:</h3>";
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>Active Tickets:</strong> " . $activeTickets . "<br>";
    echo "<strong>Resolved Tickets:</strong> " . $resolvedTickets . "<br>";
    echo "<strong>Total Comments:</strong> " . $totalComments . "<br>";
    echo "<strong>Total Tickets:</strong> " . ($activeTickets + $resolvedTickets) . "<br>";
    echo "</div>";
    
    // Show active tickets preview
    echo "<h3>🎫 Active Tickets Preview:</h3>";
    $activeTicketsList = $collection->find(['status' => true], ['limit' => 3]);
    
    foreach ($activeTicketsList as $ticket) {
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px; background-color: #ffffff;'>";
        echo "<strong>👤 User:</strong> " . htmlspecialchars($ticket['username']) . "<br>";
        echo "<strong>📝 Message:</strong> " . htmlspecialchars($ticket['message']) . "<br>";
        echo "<strong>📅 Created:</strong> " . htmlspecialchars($ticket['created_at']) . "<br>";
        echo "<strong>💬 Comments:</strong> " . count($ticket['comments']) . "<br>";
        echo "</div>";
    }
    
    // Provide next steps
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<div style='background-color: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 5px solid #28a745;'>";
    echo "<strong>Your MongoDB setup is complete! You can now:</strong><br><br>";
    echo "1. <a href='support_tickets.php' style='color: #155724; font-weight: bold;'>View Support Tickets</a><br>";
    echo "2. <a href='create_ticket.php' style='color: #155724; font-weight: bold;'>Create New Tickets</a><br>";
    echo "3. <a href='../admin/index.php' style='color: #155724; font-weight: bold;'>Access Admin Interface</a><br>";
    echo "4. <a href='index.php' style='color: #155724; font-weight: bold;'>Return to Homepage</a><br>";
    echo "</div>";
    
    echo "<h3>🔗 Useful Links:</h3>";
    echo "<p>";
    echo "<a href='test_mongodb.php' style='margin-right: 15px;'>Test MongoDB Connection</a>";
    echo "<a href='support_tickets.php' style='margin-right: 15px;'>Support Tickets</a>";
    echo "<a href='../admin/index.php' style='margin-right: 15px;'>Admin Dashboard</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error Setting Up MongoDB</h3>";
    echo "<div style='background-color: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 5px solid #dc3545;'>";
    echo "<strong>Error Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
    echo "<strong>Possible Solutions:</strong><br>";
    echo "1. Make sure MongoDB server is running<br>";
    echo "2. Check if MongoDB PHP extension is installed<br>";
    echo "3. Verify MongoDB PHP library is installed (composer require mongodb/mongodb)<br>";
    echo "4. Check MongoDB connection string<br>";
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