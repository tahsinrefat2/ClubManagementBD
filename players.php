<?php
include 'db_connect.php';

// Initialize messages
$success = '';
$error = '';

// Create uploads directory if it doesn't exist
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
    file_put_contents('uploads/index.html', ''); // Add empty index file for security
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create/Update Player
    if (isset($_POST['save_player'])) {
        $player_name = $_POST['player_name'];
        $date_of_birth = $_POST['date_of_birth'];
        $contact_info = $_POST['contact_info'];
        $sports_experience = $_POST['sports_experience'];
        $player_id = $_POST['player_id'] ?? null;
        
        // Simple file upload (same as clubs.php)
        $photo = '';
        if (!empty($_FILES['photo']['name'])) {
            $photo = $_FILES['photo']['name'];
            $photo_tmp = $_FILES['photo']['tmp_name'];
            move_uploaded_file($photo_tmp, "uploads/$photo");
        }
        
        try {
            if ($player_id) {
                // Update existing player
                if (!empty($photo)) {
                    // Delete old photo if exists
                    $old_photo = $conn->query("SELECT Photo FROM Player WHERE Player_id = $player_id")->fetch_assoc()['Photo'];
                    if (!empty($old_photo) && file_exists("uploads/$old_photo")) {
                        unlink("uploads/$old_photo");
                    }
                    
                    $stmt = $conn->prepare("UPDATE Player SET 
                                          Player_name = ?, 
                                          Date_of_Birth = ?, 
                                          player_contact_info = ?, 
                                          Sports_experience = ?, 
                                          Photo = ?
                                          WHERE Player_id = ?");
                    $stmt->bind_param("sssssi", $player_name, $date_of_birth, $contact_info, $sports_experience, $photo, $player_id);
                } else {
                    // Keep existing photo
                    $stmt = $conn->prepare("UPDATE Player SET 
                                          Player_name = ?, 
                                          Date_of_Birth = ?, 
                                          player_contact_info = ?, 
                                          Sports_experience = ?
                                          WHERE Player_id = ?");
                    $stmt->bind_param("ssssi", $player_name, $date_of_birth, $contact_info, $sports_experience, $player_id);
                }
            } else {
                // Create new player
                $stmt = $conn->prepare("INSERT INTO Player (Player_name, Date_of_Birth, player_contact_info, Sports_experience, Photo) 
                                       VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $player_name, $date_of_birth, $contact_info, $sports_experience, $photo);
            }
            
            if ($stmt->execute()) {
                $success = $player_id ? "Player updated successfully!" : "Player registered successfully!";
            } else {
                $error = "Error saving player: " . $conn->error;
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    // Record Performance
    if (isset($_POST['record_performance'])) {
        $match_id = $_POST['match_id'];
        $player_id = $_POST['player_id'];
        $goals_scored = $_POST['goals_scored'];
        $assists = $_POST['assists'];
        $other_metrics = $_POST['other_metrics'];
        
        // Check if performance already exists
        $check = $conn->query("SELECT * FROM Player_Performance WHERE Match_id = $match_id AND Player_id = $player_id");
        
        if ($check->num_rows > 0) {
            // Update existing performance
            $stmt = $conn->prepare("UPDATE Player_Performance 
                                    SET goal_scored = ?, assists = ?, other_metrics = ?
                                    WHERE Match_id = ? AND Player_id = ?");
            $stmt->bind_param("iisii", $goals_scored, $assists, $other_metrics, $match_id, $player_id);
        } else {
            // Create new performance
            $stmt = $conn->prepare("INSERT INTO Player_Performance (Match_id, Player_id, goal_scored, assists, other_metrics)
                                    VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $match_id, $player_id, $goals_scored, $assists, $other_metrics);
        }
        
        if ($stmt->execute()) {
            $success = "Performance recorded successfully!";
        } else {
            $error = "Error recording performance: " . $conn->error;
        }
    }
}

// Handle delete requests
if (isset($_GET['delete'])) {
    $player_id = $_GET['delete'];
    
    // Get photo to delete
    $photo = $conn->query("SELECT Photo FROM Player WHERE Player_id = $player_id")->fetch_assoc()['Photo'];
    
    // Delete player performances first
    $conn->query("DELETE FROM Player_Performance WHERE Player_id = $player_id");
    
    // Then delete the player
    if ($conn->query("DELETE FROM Player WHERE Player_id = $player_id")) {
        if (!empty($photo) && file_exists("uploads/$photo")) {
            unlink("uploads/$photo");
        }
        $success = "Player deleted successfully!";
    } else {
        $error = "Error deleting player: " . $conn->error;
    }
}

// Fetch all players
$players = $conn->query("SELECT * FROM Player ORDER BY Player_name");

// Fetch all matches for performance tracking
$matches = $conn->query("SELECT m.*, t1.Team_name as team1_name, t2.Team_name as team2_name
                         FROM Match_ m
                         JOIN Team t1 ON m.Team1_id = t1.Team_id
                         JOIN Team t2 ON m.Team2_id = t2.Team_id
                         ORDER BY m.Match_date DESC");

// If editing a player, get their data
$edit_player = null;
if (isset($_GET['edit'])) {
    $edit_player = $conn->query("SELECT * FROM Player WHERE Player_id = " . $_GET['edit'])->fetch_assoc();
}

// If viewing a player's performance, get their data
$view_player = null;
$player_performances = [];
if (isset($_GET['view'])) {
    $view_player = $conn->query("SELECT * FROM Player WHERE Player_id = " . $_GET['view'])->fetch_assoc();
    
    $player_performances = $conn->query("SELECT pp.*, m.Match_date, 
                                        t1.Team_name as team1_name, t2.Team_name as team2_name
                                        FROM Player_Performance pp
                                        JOIN Match_ m ON pp.Match_id = m.Match_id
                                        JOIN Team t1 ON m.Team1_id = t1.Team_id
                                        JOIN Team t2 ON m.Team2_id = t2.Team_id
                                        WHERE pp.Player_id = " . $_GET['view'] . "
                                        ORDER BY m.Match_date DESC");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Player Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .player-card {
            transition: all 0.2s;
        }
        .player-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .player-photo {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #0d6efd;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
        .stats-card {
            border-left: 4px solid #28a745;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">👤 Player Management</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="playerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="players-tab" data-bs-toggle="tab" data-bs-target="#players" type="button" role="tab">All Players</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab"><?= isset($_GET['edit']) ? 'Edit Player' : 'Register Player' ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="performance-tab" data-bs-toggle="tab" data-bs-target="#performance" type="button" role="tab">Record Performance</button>
        </li>
    </ul>

    <div class="tab-content" id="playerTabsContent">
        <!-- All Players Tab -->
        <div class="tab-pane fade show active" id="players" role="tabpanel">
            <div class="row">
                <?php while ($player = $players->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card player-card h-100">
                            <div class="card-body text-center">
                                <?php if (!empty($player['Photo'])): ?>
                                    <img src="uploads/<?= $player['Photo'] ?>" class="player-photo mb-3" alt="<?= $player['Player_name'] ?>">
                                <?php else: ?>
                                    <div class="player-photo mb-3 bg-light d-flex align-items-center justify-content-center mx-auto">
                                        <i class="fas fa-user fa-3x text-secondary"></i>
                                    </div>
                                <?php endif; ?>
                                <h5 class="card-title"><?= $player['Player_name'] ?></h5>
                                <p class="card-text text-muted">
                                    <?= date_diff(date_create($player['Date_of_Birth']), date_create('today'))->y ?> years old
                                </p>
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="?view=<?= $player['Player_id'] ?>" class="btn btn-sm btn-info">View Stats</a>
                                    <a href="?edit=<?= $player['Player_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?= $player['Player_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this player?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Register/Edit Player Tab -->
        <div class="tab-pane fade" id="register" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= isset($_GET['edit']) ? 'Edit Player' : 'Register New Player' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if (isset($_GET['edit'])): ?>
                            <input type="hidden" name="player_id" value="<?= $edit_player['Player_id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Player Name</label>
                                <input type="text" name="player_name" class="form-control" required
                                       value="<?= $edit_player['Player_name'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" name="date_of_birth" class="form-control" required
                                       value="<?= $edit_player['Date_of_Birth'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Information</label>
                                <input type="text" name="contact_info" class="form-control" required
                                       value="<?= $edit_player['player_contact_info'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Sports Experience (years)</label>
                                <input type="number" name="sports_experience" class="form-control" min="0" step="1"
                                       value="<?= $edit_player['Sports_experience'] ?? '' ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Player Photo</label>
                                <input type="file" name="photo" class="form-control">
                                <?php if (isset($edit_player['Photo']) && !empty($edit_player['Photo'])): ?>
                                    <div class="mt-2">
                                        <img src="uploads/<?= $edit_player['Photo'] ?>" width="100" class="img-thumbnail">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_photo" id="removePhoto">
                                            <label class="form-check-label" for="removePhoto">Remove current photo</label>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="save_player">
                                    <?= isset($_GET['edit']) ? 'Update Player' : 'Register Player' ?>
                                </button>
                                <a href="players.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Record Performance Tab -->
        <div class="tab-pane fade" id="performance" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Record Player Performance</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Player</label>
                                <select name="player_id" class="form-select" required>
                                    <option value="">Select Player</option>
                                    <?php $players->data_seek(0); // Reset pointer ?>
                                    <?php while ($player = $players->fetch_assoc()): ?>
                                        <option value="<?= $player['Player_id'] ?>"><?= $player['Player_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Match</label>
                                <select name="match_id" class="form-select" required>
                                    <option value="">Select Match</option>
                                    <?php while ($match = $matches->fetch_assoc()): ?>
                                        <option value="<?= $match['Match_id'] ?>">
                                            <?= date('M j, Y', strtotime($match['Match_date'])) ?> - 
                                            <?= $match['team1_name'] ?> vs <?= $match['team2_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Goals Scored</label>
                                <input type="number" name="goals_scored" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Assists</label>
                                <input type="number" name="assists" class="form-control" min="0" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Other Metrics</label>
                                <input type="text" name="other_metrics" class="form-control" placeholder="e.g., yellow cards, red cards, etc.">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="record_performance">Record Performance</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Player Performance View -->
    <?php if (isset($_GET['view'])): ?>
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Player Performance: <?= $view_player['Player_name'] ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <?php if (!empty($view_player['Photo'])): ?>
                            <img src="uploads/<?= $view_player['Photo'] ?>" class="player-photo mb-3" alt="<?= $view_player['Player_name'] ?>">
                        <?php else: ?>
                            <div class="player-photo mb-3 bg-light d-flex align-items-center justify-content-center mx-auto">
                                <i class="fas fa-user fa-3x text-secondary"></i>
                            </div>
                        <?php endif; ?>
                        <h5><?= $view_player['Player_name'] ?></h5>
                        <p class="text-muted">
                            Age: <?= date_diff(date_create($view_player['Date_of_Birth']), date_create('today'))->y ?><br>
                            Experience: <?= $view_player['Sports_experience'] ?> years
                        </p>
                    </div>
                    <div class="col-md-9">
                        <h5>Performance Statistics</h5>
                        <?php if ($player_performances->num_rows > 0): ?>
                            <?php
                            // Calculate totals
                            $totalGoals = 0;
                            $totalAssists = 0;
                            $matchesPlayed = $player_performances->num_rows;
                            
                            $player_performances->data_seek(0); // Reset pointer
                            while ($perf = $player_performances->fetch_assoc()) {
                                $totalGoals += $perf['goal_scored'];
                                $totalAssists += $perf['assists'];
                            }
                            ?>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="card stats-card">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">Matches Played</h6>
                                            <h3 class="card-title"><?= $matchesPlayed ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card stats-card">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">Total Goals</h6>
                                            <h3 class="card-title"><?= $totalGoals ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card stats-card">
                                        <div class="card-body text-center">
                                            <h6 class="card-subtitle mb-2 text-muted">Total Assists</h6>
                                            <h3 class="card-title"><?= $totalAssists ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <h5>Match-by-Match Performance</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Match</th>
                                            <th>Goals</th>
                                            <th>Assists</th>
                                            <th>Other Metrics</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $player_performances->data_seek(0); // Reset pointer ?>
                                        <?php while ($perf = $player_performances->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= date('M j, Y', strtotime($perf['Match_date'])) ?></td>
                                                <td><?= $perf['team1_name'] ?> vs <?= $perf['team2_name'] ?></td>
                                                <td><?= $perf['goal_scored'] ?></td>
                                                <td><?= $perf['assists'] ?></td>
                                                <td><?= $perf['other_metrics'] ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No performance records found for this player.</div>
                        <?php endif; ?>
                        
                        <!-- Print Button -->
                        <div class="text-center mt-3">
                            <button class="btn btn-primary" onclick="window.print()">Print Report</button>
                            <a href="players.php" class="btn btn-secondary">Back to Players</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Activate appropriate tab based on URL
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_GET['edit'])): ?>
            const registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
            registerTab.show();
        <?php elseif (isset($_GET['view'])): ?>
            const playersTab = new bootstrap.Tab(document.getElementById('players-tab'));
            playersTab.show();
        <?php endif; ?>
    });
</script>
</body>
</html>