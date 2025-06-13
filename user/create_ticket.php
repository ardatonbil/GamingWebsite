<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $ticketMessage = trim($_POST['message'] ?? '');
    
    if (empty($username) || empty($ticketMessage)) {
        $message = "Both username and message are required.";
    } else {
        try {
            $ticketsCollection = getMongoCollection('support_tickets');
            
            $newTicket = [
                'username' => $username,
                'message' => $ticketMessage,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => true,
                'comments' => []
            ];
            
            $result = $ticketsCollection->insertOne($newTicket);
            
            if ($result->getInsertedId()) {
                $success = true;
                $message = "Ticket created successfully!";
            } else {
                $message = "Failed to create ticket. Please try again.";
            }
            
        } catch (Exception $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Ticket - CS306 Project</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        textarea {
            height: 120px;
            resize: vertical;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-actions {
            margin-top: 20px;
            text-align: center;
        }
        .success-actions a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="nav-links">
            <a href="support_tickets.php">← View Tickets</a>
            <a href="index.php">Home</a>
        </div>
        
        <h1>Create a Ticket</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-actions">
                <a href="create_ticket.php" class="btn-primary">Create Another Ticket</a>
                <a href="support_tickets.php" class="btn-secondary">Back to Tickets</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           placeholder="Enter your username"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" 
                              name="message" 
                              placeholder="Describe your issue or question..."
                              required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit">Create Ticket</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>