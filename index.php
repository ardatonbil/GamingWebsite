<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';

$message = '';
$tickets = [];

try {
    $ticketsCollection = getMongoCollection('support_tickets');
    
    // Get all active tickets (admin can see all tickets)
    $tickets = $ticketsCollection->find(['status' => true])->toArray();
    
} catch (Exception $e) {
    $message = "Error loading tickets: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - CS306 Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .nav-links {
            margin-bottom: 20px;
            text-align: center;
        }
        .nav-links a {
            margin: 0 15px;
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
            padding: 10px 15px;
            border: 1px solid #0066cc;
            border-radius: 5px;
            display: inline-block;
        }
        .nav-links a:hover {
            background-color: #0066cc;
            color: white;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
        }
        .stat-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border-left: 5px solid #dc3545;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #dc3545;
        }
        .ticket {
            border: 1px solid #ddd;
            margin: 15px 0;
            padding: 20px;
            border-radius: 10px;
            background-color: #f8f9fa;
            transition: transform 0.2s;
        }
        .ticket:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .ticket-user {
            font-weight: bold;
            color: #dc3545;
            font-size: 1.1em;
        }
        .ticket-date {
            color: #666;
            font-size: 0.9em;
        }
        .ticket-message {
            margin: 15px 0;
            padding: 15px;
            background-color: white;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
        }
        .ticket-actions {
            text-align: right;
        }
        .btn-view {
            background-color: #17a2b8;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            display: inline-block;
        }
        .btn-view:hover {
            background-color: #138496;
        }
        .no-tickets {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #f5c6cb;
            margin: 15px 0;
        }
        .comments-count {
            background-color: #6c757d;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡 Admin Dashboard</h1>
            <p>CS306 Database Project - Support Ticket Management</p>
        </div>
        
        <div class="nav-links">
            <a href="../user/index.php">👤 User Interface</a>
            <a href="test_mongodb.php">🔧 Test MongoDB</a>
            <a href="#refresh" onclick="window.location.reload()">🔄 Refresh</a>
        </div>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo count($tickets); ?></div>
                <div>Active Tickets</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">
                    <?php 
                    $users = array_unique(array_column($tickets, 'username'));
                    echo count($users); 
                    ?>
                </div>
                <div>Users with Tickets</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">
                    <?php 
                    $totalComments = 0;
                    foreach ($tickets as $ticket) {
                        $totalComments += count($ticket['comments']);
                    }
                    echo $totalComments;
                    ?>
                </div>
                <div>Total Comments</div>
            </div>
        </div>
        
        <h2>All Active Support Tickets</h2>
        
        <?php if (empty($tickets)): ?>
            <div class="no-tickets">
                <h3>📭 No Active Tickets</h3>
                <p>There are currently no active support tickets.</p>
                <p>Users can create tickets from the <a href="../user/support_tickets.php">User Interface</a></p>
            </div>
        <?php else: ?>
            <?php foreach ($tickets as $ticket): ?>
                <div class="ticket">
                    <div class="ticket-header">
                        <div class="ticket-user">
                            👤 <?php echo htmlspecialchars($ticket['username']); ?>
                            <span class="comments-count">
                                💬 <?php echo count($ticket['comments']); ?> comments
                            </span>
                        </div>
                        <div class="ticket-date">
                            📅 <?php echo htmlspecialchars($ticket['created_at']); ?>
                        </div>
                    </div>
                    
                    <div class="ticket-message">
                        <strong>Message:</strong><br>
                        <?php echo htmlspecialchars($ticket['message']); ?>
                    </div>
                    
                    <div class="ticket-actions">
                        <a href="ticket_details.php?id=<?php echo $ticket['_id']; ?>" class="btn-view">
                            🔍 View & Manage
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>