<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';

$selectedUser = $_GET['username'] ?? '';
$message = '';

try {
    $ticketsCollection = getMongoCollection('support_tickets');
    
    // Get all users with active tickets for dropdown
    $pipeline = [
        ['$match' => ['status' => true]],
        ['$group' => ['_id' => '$username']],
        ['$sort' => ['_id' => 1]]
    ];
    $usersWithTickets = $ticketsCollection->aggregate($pipeline)->toArray();
    
    // Get tickets for selected user
    $userTickets = [];
    if ($selectedUser) {
        $userTickets = $ticketsCollection->find([
            'username' => $selectedUser,
            'status' => true
        ])->toArray();
    }
    
} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Tickets - CS306 Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
        }
        .nav-links a:hover {
            text-decoration: underline;
        }
        .dropdown {
            margin: 20px 0;
        }
        select {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 10px;
        }
        button {
            padding: 10px 15px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0052a3;
        }
        .create-link {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .create-link:hover {
            background-color: #218838;
        }
        .ticket {
            border: 1px solid #ddd;
            margin: 15px 0;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
        .ticket h3 {
            margin-top: 0;
            color: #333;
        }
        .ticket-meta {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .view-details {
            display: inline-block;
            margin-top: 10px;
            padding: 5px 10px;
            background-color: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            font-size: 0.9em;
        }
        .view-details:hover {
            background-color: #138496;
        }
        .no-tickets {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="index.php">← Back to Homepage</a>
            <a href="create_ticket.php">Create New Ticket</a>
        </div>
        
        <h1>Support Tickets</h1>
        
        <?php if ($message): ?>
            <div class="error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <div class="dropdown">
            <form method="GET">
                <select name="username" onchange="this.form.submit()">
                    <option value="">Select a user...</option>
                    <?php foreach ($usersWithTickets as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['_id']); ?>" 
                                <?php echo ($selectedUser === $user['_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['_id']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <?php if (empty($usersWithTickets)): ?>
            <div class="no-tickets">
                <h3>No Active Tickets</h3>
                <p>There are currently no active tickets in the system.</p>
                <a href="create_ticket.php" class="create-link">Create a Ticket</a>
            </div>
        <?php elseif ($selectedUser && !empty($userTickets)): ?>
            <h2>Tickets for: <?php echo htmlspecialchars($selectedUser); ?></h2>
            
            <?php foreach ($userTickets as $ticket): ?>
                <div class="ticket">
                    <h3>Status: Active</h3>
                    <div class="ticket-meta">
                        <strong>Body:</strong> <?php echo htmlspecialchars($ticket['message']); ?><br>
                        <strong>Created At:</strong> <?php echo htmlspecialchars($ticket['created_at']); ?>
                    </div>
                    <a href="ticket_details.php?id=<?php echo $ticket['_id']; ?>" class="view-details">
                        View Details
                    </a>
                </div>
            <?php endforeach; ?>
            
            <a href="create_ticket.php" class="create-link">Create a Ticket</a>
            
        <?php elseif ($selectedUser): ?>
            <div class="no-tickets">
                <h3>No tickets found for user: <?php echo htmlspecialchars($selectedUser); ?></h3>
                <a href="create_ticket.php" class="create-link">Create a Ticket</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
