<?php
require_once 'vendor/autoload.php';
require_once 'config/database.php';

$ticketId = $_GET['id'] ?? '';
$message = '';
$ticket = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $ticketsCollection = getMongoCollection('support_tickets');
        
        if (isset($_POST['add_comment'])) {
            // Add user comment
            $comment = trim($_POST['comment'] ?? '');
            $username = trim($_POST['username'] ?? '');
            
            if (!empty($comment) && !empty($username)) {
                $newComment = [
                    'username' => $username,
                    'comment' => $comment,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                $result = $ticketsCollection->updateOne(
                    ['_id' => new MongoDB\BSON\ObjectId($ticketId)],
                    ['$push' => ['comments' => $newComment]]
                );
                
                if ($result->getModifiedCount() > 0) {
                    $message = "✅ Comment added successfully!";
                } else {
                    $message = "❌ Failed to add comment.";
                }
            } else {
                $message = "❌ Please fill in both username and comment.";
            }
        }
        
    } catch (Exception $e) {
        $message = "❌ Error: " . $e->getMessage();
    }
}

// Get ticket details
try {
    $ticketsCollection = getMongoCollection('support_tickets');
    $ticket = $ticketsCollection->findOne(['_id' => new MongoDB\BSON\ObjectId($ticketId)]);
    
    if (!$ticket) {
        $message = "❌ Ticket not found.";
    }
    
} catch (Exception $e) {
    $message = "❌ Error loading ticket: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎫 Ticket Details - Gaming Database Support</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        .nav-links {
            margin-bottom: 20px;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #0984e3;
            font-weight: bold;
            padding: 8px 15px;
            border: 2px solid #0984e3;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .nav-links a:hover {
            background-color: #0984e3;
            color: white;
            transform: translateY(-2px);
        }
        .ticket-details {
            background: linear-gradient(135deg, #a8edea, #fed6e3);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .ticket-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .meta-item {
            background-color: white;
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
        .meta-label {
            font-weight: bold;
            color: #666;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .meta-value {
            color: #333;
            font-size: 1.1em;
        }
        .ticket-message {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .comments-section {
            margin-top: 30px;
        }
        .comment {
            background-color: #f8f9fa;
            padding: 15px;
            margin: 15px 0;
            border-radius: 10px;
            border-left: 4px solid #74b9ff;
        }
        .comment.admin {
            border-left-color: #fd79a8;
            background-color: #fff5f8;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .comment-user {
            font-weight: bold;
            color: #74b9ff;
        }
        .comment-user.admin {
            color: #fd79a8;
        }
        .comment-date {
            color: #666;
            font-size: 0.9em;
        }
        .add-comment {
            background: linear-gradient(135deg, #00b894, #00cec9);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-top: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            font-size: 16px;
            background-color: rgba(255,255,255,0.9);
            box-sizing: border-box;
        }
        input:focus, textarea:focus {
            outline: none;
            border-color: #ffd700;
            background-color: white;
        }
        textarea {
            height: 100px;
            resize: vertical;
        }
        button {
            background: linear-gradient(135deg, #fd79a8, #fdcb6e);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .message {
            padding: 15px;
            border-radius: 10px;
            margin: 15px 0;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-resolved {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎫 Gaming Support Ticket Details</h1>
            <p>User Support System - GamingDatabase</p>
        </div>
        
        <div class="nav-links">
            <a href="support_tickets.php">← Back to Tickets</a>
            <a href="index.php">🏠 Homepage</a>
            <a href="create_ticket.php">➕ New Ticket</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($ticket): ?>
            <div class="ticket-details">
                <h2>📋 Ticket Information</h2>
                
                <div class="ticket-meta">
                    <div class="meta-item">
                        <div class="meta-label">👤 Username</div>
                        <div class="meta-value"><?php echo htmlspecialchars($ticket['username']); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">📊 Status</div>
                        <div class="meta-value">
                            <span class="status-badge <?php echo $ticket['status'] ? 'status-active' : 'status-resolved'; ?>">
                                <?php echo $ticket['status'] ? '🟢 Active' : '🔴 Resolved'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">📅 Created At</div>
                        <div class="meta-value"><?php echo htmlspecialchars($ticket['created_at']); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">💬 Comments</div>
                        <div class="meta-value"><?php echo count($ticket['comments']); ?> responses</div>
                    </div>
                </div>
                
                <div class="ticket-message">
                    <strong>📝 Original Message:</strong><br><br>
                    <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                </div>
            </div>
            
            <div class="comments-section">
                <h3>💬 Support Conversation</h3>
                
                <?php if (empty($ticket['comments'])): ?>
                    <div style="text-align: center; padding: 20px; background-color: #f8f9fa; border-radius: 10px; color: #666; font-style: italic;">
                        <p>🔇 No responses yet from support team.</p>
                        <p>Add a comment below to get help from our support team!</p>
                    </div>
                <?php else: ?>
                    <div style="background-color: #e8f4fd; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        <strong>📞 Support Conversation:</strong> This ticket has <?php echo count($ticket['comments']); ?> response(s)
                    </div>
                    
                    <?php foreach ($ticket['comments'] as $index => $comment): ?>
                        <div class="comment <?php echo $comment['username'] === 'admin' ? 'admin' : ''; ?>">
                            <div class="comment-header">
                                <span class="comment-user <?php echo $comment['username'] === 'admin' ? 'admin' : ''; ?>">
                                    <?php 
                                    if ($comment['username'] === 'admin') {
                                        echo '🛡 Support Team (Admin)';
                                    } else {
                                        echo '👤 ' . htmlspecialchars($comment['username']) . ' (User)';
                                    }
                                    ?>
                                </span>
                                <span class="comment-date">
                                    Response #<?php echo $index + 1; ?> - <?php echo htmlspecialchars($comment['created_at']); ?>
                                </span>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                            </div>
                            
                            <?php if ($comment['username'] === 'admin'): ?>
                                <div style="margin-top: 10px; padding: 8px; background-color: rgba(253, 121, 168, 0.1); border-radius: 5px; font-size: 0.9em;">
                                    <strong>📋 Admin Response:</strong> This is an official response from the support team
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    
                    <div style="background-color: #fff3cd; padding: 15px; border-radius: 10px; margin-top: 20px;">
                        <strong>💡 Need more help?</strong> Add another comment below to continue the conversation with our support team.
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($ticket['status']): ?>
                <div class="add-comment">
                    <h3>➕ Continue Support Conversation</h3>
                    <p>💭 Add more details, ask follow-up questions, or provide additional information to help our support team assist you better.</p>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">👤 Your Username:</label>
                            <input type="text" name="username" id="username" 
                                   value="<?php echo htmlspecialchars($ticket['username']); ?>"
                                   placeholder="Enter your username" required>
                            <small style="color: rgba(255,255,255,0.8); font-size: 0.9em;">
                                💡 Use the same username as your original ticket for consistency
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">💭 Your Follow-up Comment:</label>
                            <textarea name="comment" id="comment" 
                                      placeholder="Add more details, respond to admin feedback, or ask follow-up questions..." 
                                      required></textarea>
                        </div>
                        
                        <button type="submit" name="add_comment">💬 Send Response</button>
                    </form>
                    
                    <div style="margin-top: 15px; padding: 10px; background-color: rgba(255,255,255,0.2); border-radius: 5px; font-size: 0.9em;">
                        <strong>📨 How it works:</strong> Your response will be added to this ticket and visible to the support team. 
                        You'll be able to continue the conversation until the ticket is resolved.
                    </div>
                </div>
            <?php else: ?>
                <div style="background-color: #d4edda; padding: 20px; border-radius: 10px; text-align: center; margin-top: 30px; border-left: 5px solid #28a745;">
                    <h3>✅ Ticket Resolved</h3>
                    <p><strong>Good news!</strong> This support ticket has been successfully resolved by our team.</p>
                    <p>If you need further assistance, please <a href="create_ticket.php" style="color: #155724; font-weight: bold;">create a new ticket</a>.</p>
                    
                    <div style="margin-top: 15px; padding: 10px; background-color: rgba(40, 167, 69, 0.1); border-radius: 5px;">
                        <strong>📊 Resolution Summary:</strong><br>
                        Original Issue: <?php echo htmlspecialchars(substr($ticket['message'], 0, 100)); ?><?php echo strlen($ticket['message']) > 100 ? '...' : ''; ?><br>
                        Total Responses: <?php echo count($ticket['comments']); ?><br>
                        Status: <span style="background: #28a745; color: white; padding: 2px 8px; border-radius: 10px;">Closed</span>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="message error">
                <h3>❌ Ticket Not Found</h3>
                <p>The requested ticket could not be found or has been deleted.</p>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="support_tickets.php" style="color: #721c24; font-weight: bold;">← Return to Support Tickets</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>