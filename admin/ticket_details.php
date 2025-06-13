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
            // Add admin comment
            $comment = trim($_POST['comment'] ?? '');
            if (!empty($comment)) {
                $newComment = [
                    'username' => 'admin',
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
            }
        }
        
        if (isset($_POST['resolve_ticket'])) {
            // Mark ticket as resolved
            $result = $ticketsCollection->updateOne(
                ['_id' => new MongoDB\BSON\ObjectId($ticketId)],
                ['$set' => ['status' => false]]
            );
            
            if ($result->getModifiedCount() > 0) {
                $message = "✅ Ticket marked as resolved!";
                // Redirect to admin dashboard after resolving
                header("Location: index.php");
                exit;
            } else {
                $message = "❌ Failed to resolve ticket.";
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
    <title>Admin - Ticket Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
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
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
            color: #0066cc;
            font-weight: bold;
            padding: 8px 15px;
            border: 1px solid #0066cc;
            border-radius: 5px;
        }
        .nav-links a:hover {
            background-color: #0066cc;
            color: white;
        }
        .ticket-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #dc3545;
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
            border-radius: 5px;
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
            border-radius: 5px;
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
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
        }
        .comment.admin {
            border-left-color: #dc3545;
            background-color: #fff5f5;
        }
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .comment-user {
            font-weight: bold;
            color: #dc3545;
        }
        .comment-date {
            color: #666;
            font-size: 0.9em;
        }
        .admin-actions {
            background-color: #fff3cd;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            border: 1px solid #ffeaa7;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
            height: 100px;
            box-sizing: border-box;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-comment {
            background-color: #17a2b8;
            color: white;
        }
        .btn-comment:hover {
            background-color: #138496;
        }
        .btn-resolve {
            background-color: #28a745;
            color: white;
        }
        .btn-resolve:hover {
            background-color: #218838;
        }
        .message {
            padding: 15px;
            border-radius: 5px;
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
            padding: 5px 15px;
            border-radius: 15px;
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
            <h1>🛡 Admin Ticket Management</h1>
            <p>Ticket Details & Resolution</p>
        </div>
        
        <div class="nav-links">
            <a href="index.php">← Back to Dashboard</a>
            <a href="../user/index.php">👤 User Interface</a>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($ticket): ?>
            <div class="ticket-details">
                <h2>Ticket Details</h2>
                
                <div class="ticket-meta">
                    <div class="meta-item">
                        <div class="meta-label">Username</div>
                        <div class="meta-value">👤 <?php echo htmlspecialchars($ticket['username']); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Status</div>
                        <div class="meta-value">
                            <span class="status-badge <?php echo $ticket['status'] ? 'status-active' : 'status-resolved'; ?>">
                                <?php echo $ticket['status'] ? 'Active' : 'Resolved'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Created At</div>
                        <div class="meta-value">📅 <?php echo htmlspecialchars($ticket['created_at']); ?></div>
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Comments</div>
                        <div class="meta-value">💬 <?php echo count($ticket['comments']); ?> comments</div>
                    </div>
                </div>
                
                <div class="ticket-message">
                    <strong>Original Message:</strong><br>
                    <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
                </div>
            </div>
            
            <div class="comments-section">
                <h3>💬 Comments</h3>
                
                <?php if (empty($ticket['comments'])): ?>
                    <p style="color: #666; font-style: italic;">No comments yet.</p>
                <?php else: ?>
                    <?php foreach ($ticket['comments'] as $comment): ?>
                        <div class="comment <?php echo $comment['username'] === 'admin' ? 'admin' : ''; ?>">
                            <div class="comment-header">
                                <span class="comment-user">
                                    <?php echo $comment['username'] === 'admin' ? '🛡 Admin' : '👤 ' . htmlspecialchars($comment['username']); ?>
                                </span>
                                <span class="comment-date"><?php echo htmlspecialchars($comment['created_at']); ?></span>
                            </div>
                            <div class="comment-text">
                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <?php if ($ticket['status']): ?>
                <div class="admin-actions">
                    <h3>🔧 Admin Actions</h3>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="comment">Add Comment:</label>
                            <textarea name="comment" id="comment" placeholder="Enter your admin response..."></textarea>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" name="add_comment" class="btn-comment">
                                💬 Add Comment
                            </button>
                            <button type="submit" name="resolve_ticket" class="btn-resolve" 
                                    onclick="return confirm('Are you sure you want to mark this ticket as resolved?')">
                                ✅ Mark as Resolved
                            </button>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="admin-actions" style="background-color: #d4edda; border-color: #c3e6cb;">
                    <h3>✅ Ticket Resolved</h3>
                    <p>This ticket has been marked as resolved and is no longer active.</p>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="message error">
                <h3>❌ Ticket Not Found</h3>
                <p>The requested ticket could not be found or has been deleted.</p>
                <a href="index.php">Return to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>