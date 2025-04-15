<?php
include 'db_connect.php';

// Initialize messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send message
    if (isset($_POST['send_message'])) {
        $sender_id = $_POST['sender_id'];
        $receiver_id = $_POST['receiver_id'];
        $content = $_POST['content'];
        
        $stmt = $conn->prepare("INSERT INTO Message (sender_id, receiver_id, content, sent_at) 
                               VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iis", $sender_id, $receiver_id, $content);
        
        if ($stmt->execute()) {
            $success = "Message sent successfully!";
        } else {
            $error = "Error sending message: " . $conn->error;
        }
    }
}

// Get current user ID (you'll need to replace this with your actual auth system)
session_start();
$current_user_id = $_SESSION['user_id'] ?? 1; // Default to 1 if not logged in

// Fetch all users/clubs that can be messaged
$users = $conn->query("SELECT Club_id as id, Club_name as name, 'club' as type FROM Club
                       UNION
                       SELECT Player_id as id, Player_name as name, 'player' as type FROM Player
                       ORDER BY name");

// Fetch received messages
$received_messages = $conn->query("SELECT m.*, 
                                  CASE 
                                    WHEN m.sender_id IN (SELECT Club_id FROM Club) THEN (SELECT Club_name FROM Club WHERE Club_id = m.sender_id)
                                    ELSE (SELECT Player_name FROM Player WHERE Player_id = m.sender_id)
                                  END as sender_name
                                  FROM Message m
                                  WHERE m.receiver_id = $current_user_id
                                  ORDER BY m.sent_at DESC");

// Fetch sent messages
$sent_messages = $conn->query("SELECT m.*, 
                               CASE 
                                 WHEN m.receiver_id IN (SELECT Club_id FROM Club) THEN (SELECT Club_name FROM Club WHERE Club_id = m.receiver_id)
                                 ELSE (SELECT Player_name FROM Player WHERE Player_id = m.receiver_id)
                               END as receiver_name
                               FROM Message m
                               WHERE m.sender_id = $current_user_id
                               ORDER BY m.sent_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messaging System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .message-card {
            transition: all 0.2s;
            border-left: 4px solid #0d6efd;
        }
        .message-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
        .message-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">✉️ Messaging System</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="messageTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="compose-tab" data-bs-toggle="tab" data-bs-target="#compose" type="button" role="tab">Compose</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inbox-tab" data-bs-toggle="tab" data-bs-target="#inbox" type="button" role="tab">Inbox</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab">Sent</button>
        </li>
    </ul>

    <div class="tab-content" id="messageTabsContent">
        <!-- Compose Tab -->
        <div class="tab-pane fade show active" id="compose" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">New Message</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="sender_id" value="<?= $current_user_id ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Recipient</label>
                                <select name="receiver_id" class="form-select" required>
                                    <option value="">Select Recipient</option>
                                    <?php while ($user = $users->fetch_assoc()): ?>
                                        <?php if ($user['id'] != $current_user_id): ?>
                                            <option value="<?= $user['id'] ?>">
                                                <?= $user['name'] ?> (<?= $user['type'] ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message</label>
                                <textarea name="content" class="form-control" rows="5" required></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="send_message">Send Message</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Inbox Tab -->
        <div class="tab-pane fade" id="inbox" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Inbox</h5>
                </div>
                <div class="card-body">
                    <?php if ($received_messages->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($message = $received_messages->fetch_assoc()): ?>
                                <div class="list-group-item message-card mb-2">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">From: <?= $message['sender_name'] ?></h6>
                                        <small class="message-time">
                                            <?= date('M j, Y g:i A', strtotime($message['sent_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= nl2br($message['content']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No messages in your inbox.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sent Messages Tab -->
        <div class="tab-pane fade" id="sent" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Sent Messages</h5>
                </div>
                <div class="card-body">
                    <?php if ($sent_messages->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($message = $sent_messages->fetch_assoc()): ?>
                                <div class="list-group-item message-card mb-2">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="mb-1">To: <?= $message['receiver_name'] ?></h6>
                                        <small class="message-time">
                                            <?= date('M j, Y g:i A', strtotime($message['sent_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= nl2br($message['content']) ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No sent messages.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>