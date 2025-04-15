<?php
include 'db_connect.php';

// Initialize messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Publish news/announcement
    if (isset($_POST['publish_news'])) {
        $club_id = $_POST['club_id'] ?? null; // null for admin posts
        $title = $_POST['title'];
        $content = $_POST['content'];
        
        $stmt = $conn->prepare("INSERT INTO News (Club_id, Title, Content, Publish_date) 
                               VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $club_id, $title, $content);
        
        if ($stmt->execute()) {
            $success = "News published successfully!";
        } else {
            $error = "Error publishing news: " . $conn->error;
        }
    }
    
    // Delete news
    if (isset($_POST['delete_news'])) {
        $news_id = $_POST['news_id'];
        
        if ($conn->query("DELETE FROM News WHERE News_id = $news_id")) {
            $success = "News deleted successfully!";
        } else {
            $error = "Error deleting news: " . $conn->error;
        }
    }
}

// Fetch all news with club names (left join to include admin posts)
$news = $conn->query("SELECT n.*, c.Club_name 
                      FROM News n 
                      LEFT JOIN Club c ON n.Club_id = c.Club_id
                      ORDER BY n.Publish_date DESC");

// Fetch all clubs for dropdown
$clubs = $conn->query("SELECT * FROM Club ORDER BY Club_name");
?>

<!DOCTYPE html>
<html>
<head>
    <title>News and Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .news-card {
            transition: all 0.2s;
        }
        .news-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .news-author {
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">📰 News and Announcements</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Publish News Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Publish New Announcement</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Club (leave blank for admin announcement)</label>
                        <select name="club_id" class="form-select">
                            <option value="">-- System Announcement --</option>
                            <?php while ($club = $clubs->fetch_assoc()): ?>
                                <option value="<?= $club['Club_id'] ?>"><?= $club['Club_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-control" rows="5" required></textarea>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" name="publish_news">Publish</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- News Feed -->
    <h3 class="mb-3">Latest News</h3>
    <div class="row">
        <?php while ($item = $news->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card news-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= $item['Title'] ?></h5>
                        <p class="news-author">
                            <?= $item['Club_name'] ? "By: " . $item['Club_name'] : "System Announcement" ?>
                            <br>
                            <small><?= date('M j, Y g:i A', strtotime($item['Publish_date'])) ?></small>
                        </p>
                        <p class="card-text"><?= nl2br($item['Content']) ?></p>
                    </div>
                    <div class="card-footer bg-white">
                        <form method="POST" class="text-end">
                            <input type="hidden" name="news_id" value="<?= $item['News_id'] ?>">
                            <button type="submit" name="delete_news" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Are you sure you want to delete this news item?')">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>