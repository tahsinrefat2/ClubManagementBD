<?php
include 'db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new team
    if (isset($_POST['create_team'])) {
        $club_id = $_POST['club_id'];
        $team_name = $_POST['team_name'];
        $captain_id = $_POST['captain_id'];
        
        try {
            $conn->begin_transaction();
            
            // Verify player isn't already on another team
            $player_check = $conn->query("SELECT * FROM Team_Player WHERE Player_id = $captain_id");
            if ($player_check->num_rows > 0) {
                throw new Exception("This player is already on another team!");
            }
            
            // Create the team
            $stmt = $conn->prepare("INSERT INTO Team (Club_id, Team_name, Captain_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $club_id, $team_name, $captain_id);
            $stmt->execute();
            
            $team_id = $conn->insert_id;
            
            // Add captain to team players
            $stmt = $conn->prepare("INSERT INTO Team_Player (Team_id, Player_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $team_id, $captain_id);
            $stmt->execute();
            
            $conn->commit();
            $success = "Team created successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error creating team: " . $e->getMessage();
        }
    }
    
    // Add player to team
    if (isset($_POST['add_player'])) {
        $team_id = $_POST['team_id'];
        $player_id = $_POST['player_id'];
        
        try {
            // Check if player is already on any team
            $player_check = $conn->query("SELECT * FROM Team_Player WHERE Player_id = $player_id");
            if ($player_check->num_rows > 0) {
                throw new Exception("This player is already on another team!");
            }
            
            $stmt = $conn->prepare("INSERT INTO Team_Player (Team_id, Player_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $team_id, $player_id);
            
            if ($stmt->execute()) {
                $success = "Player added to team successfully!";
            } else {
                throw new Exception($conn->error);
            }
        } catch (Exception $e) {
            $error = "Error adding player: " . $e->getMessage();
        }
    }
    
    // Remove player from team
    if (isset($_POST['remove_player'])) {
        $team_id = $_POST['team_id'];
        $player_id = $_POST['player_id'];
        
        // Don't allow removing captain
        $is_captain = $conn->query("SELECT * FROM Team WHERE Team_id = $team_id AND Captain_id = $player_id")->num_rows;
        
        if ($is_captain) {
            $error = "Cannot remove the team captain! Assign a new captain first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM Team_Player WHERE Team_id = ? AND Player_id = ?");
            $stmt->bind_param("ii", $team_id, $player_id);
            
            if ($stmt->execute()) {
                $success = "Player removed from team successfully!";
            } else {
                $error = "Error removing player from team: " . $conn->error;
            }
        }
    }
    
    // Set new captain
    if (isset($_POST['set_captain'])) {
        $team_id = $_POST['team_id'];
        $player_id = $_POST['player_id'];
        
        // Check if player is in team
        $in_team = $conn->query("SELECT * FROM Team_Player WHERE Team_id = $team_id AND Player_id = $player_id")->num_rows;
        
        if ($in_team) {
            $stmt = $conn->prepare("UPDATE Team SET Captain_id = ? WHERE Team_id = ?");
            $stmt->bind_param("ii", $player_id, $team_id);
            
            if ($stmt->execute()) {
                $success = "Team captain updated successfully!";
            } else {
                $error = "Error updating team captain: " . $conn->error;
            }
        } else {
            $error = "Player must be in the team to become captain!";
        }
    }
}

// Fetch all teams with club info
$teams = $conn->query("
    SELECT t.*, c.Club_name, c.Club_logo, p.Player_name as captain_name
    FROM Team t
    JOIN Club c ON t.Club_id = c.Club_id
    JOIN Player p ON t.Captain_id = p.Player_id
    ORDER BY c.Club_name, t.Team_name
");

// Fetch all clubs for dropdown
$clubs = $conn->query("SELECT * FROM Club ORDER BY Club_name");

// Fetch all players not currently on any team
$available_players = $conn->query("
    SELECT p.* 
    FROM Player p
    WHERE p.Player_id NOT IN (SELECT Player_id FROM Team_Player)
    ORDER BY p.Player_name
");

// If managing a specific team, get its data
$managed_team = null;
$team_players = [];
if (isset($_GET['manage'])) {
    $team_id = $_GET['manage'];
    $managed_team = $conn->query("
        SELECT t.*, c.Club_name, p.Player_name as captain_name
        FROM Team t
        JOIN Club c ON t.Club_id = c.Club_id
        JOIN Player p ON t.Captain_id = p.Player_id
        WHERE t.Team_id = $team_id
    ")->fetch_assoc();
    
    // Get current team players
    $team_players = $conn->query("
        SELECT p.* 
        FROM Player p
        JOIN Team_Player tp ON p.Player_id = tp.Player_id
        WHERE tp.Team_id = $team_id
        ORDER BY p.Player_name
    ");
    
    // Get available players (not on any team)
    $available_players = $conn->query("
        SELECT p.* 
        FROM Player p
        WHERE p.Player_id NOT IN (SELECT Player_id FROM Team_Player)
        ORDER BY p.Player_name
    ");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management</title>
    <?php include 'navbar.php'; ?>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .team-card {
            transition: transform 0.2s;
        }
        .team-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .club-logo {
            height: 100px;
            object-fit: contain;
            margin-bottom: 15px;
        }
        .player-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #0d6efd;
        }
        .badge-captain {
            background-color: #ffc107;
            color: #000;
        }
        /* Dropdown Styling */
    .dropdown-menu-dark {
        background-color: rgba(30, 58, 138, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        padding: 0.5rem;
        margin-top: 0rem;
        border-radius: 0.5rem;
    }
    
    .dropdown-menu-dark .dropdown-item {
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.2s ease;
        border-radius: 0.25rem;
        padding: 0.5rem 1rem;
        margin-bottom: 0.2rem;
    }
    
    .dropdown-menu-dark .dropdown-item:hover {
        background-color: var(--hover-bg);
        color: white;
        transform: translateX(5px);
    }
    </style>
</head>
<body class="bg-light">
    
    <div class="container py-4">
   
        <div class="row mb-4">
            <div class="col">

                <h1 class="display-4">Team Management</h1>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Create Team Section -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="h5 mb-0">Create New Team</h2>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Club</label>
                            <select name="club_id" class="form-select" required>
                                <option value="">Select Club</option>
                                <?php while ($club = $clubs->fetch_assoc()): ?>
                                    <option value="<?= $club['Club_id'] ?>"><?= $club['Club_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Team Name</label>
                            <input type="text" name="team_name" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Captain (Available Players)</label>
                            <select name="captain_id" class="form-select" required>
                                <option value="">Select Captain</option>
                                <?php while ($player = $available_players->fetch_assoc()): ?>
                                    <option value="<?= $player['Player_id'] ?>"><?= $player['Player_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="create_team" class="btn btn-primary">Create Team</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Teams Gallery -->
        <h2 class="h4 mb-3">Existing Teams</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php while ($team = $teams->fetch_assoc()): ?>
                <div class="col">
                    <div class="card team-card h-100">
                        <div class="card-body text-center">
                            <?php if (!empty($team['Club_logo'])): ?>
                                <img src="uploads/<?= $team['Club_logo'] ?>" class="club-logo" alt="<?= $team['Club_name'] ?> logo">
                            <?php else: ?>
                                <div class="club-logo bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-landmark fa-3x text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            <h3 class="h5 card-title"><?= $team['Team_name'] ?></h3>
                            <p class="card-text">
                                <strong>Club:</strong> <?= $team['Club_name'] ?><br>
                                <strong>Captain:</strong> <?= $team['captain_name'] ?>
                            </p>
                            <a href="?manage=<?= $team['Team_id'] ?>" class="btn btn-primary btn-sm">Manage Team</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

        <!-- Team Management Section -->
        <?php if ($managed_team): ?>
            <div class="card mt-5">
                <div class="card-header bg-info text-white">
                    <h2 class="h5 mb-0">Managing Team: <?= $managed_team['Team_name'] ?></h2>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Current Team Players -->
                        <div class="col-md-6">
                            <h3 class="h5">Team Players</h3>
                            <?php if ($team_players->num_rows > 0): ?>
                                <div class="list-group">
                                    <?php while ($player = $team_players->fetch_assoc()): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($player['Photo'])): ?>
                                                        <img src="uploads/<?= $player['Photo'] ?>" class="player-photo me-3" alt="<?= $player['Player_name'] ?>">
                                                    <?php else: ?>
                                                        <div class="player-photo me-3 bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-user text-secondary"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <?= $player['Player_name'] ?>
                                                        <?php if ($player['Player_id'] == $managed_team['Captain_id']): ?>
                                                            <span class="badge badge-captain ms-2">Captain</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div>
                                                    <?php if ($player['Player_id'] != $managed_team['Captain_id']): ?>
                                                        <form method="POST" class="d-inline me-1">
                                                            <input type="hidden" name="team_id" value="<?= $managed_team['Team_id'] ?>">
                                                            <input type="hidden" name="player_id" value="<?= $player['Player_id'] ?>">
                                                            <button type="submit" name="set_captain" class="btn btn-warning btn-sm">Make Captain</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="team_id" value="<?= $managed_team['Team_id'] ?>">
                                                        <input type="hidden" name="player_id" value="<?= $player['Player_id'] ?>">
                                                        <button type="submit" name="remove_player" class="btn btn-danger btn-sm" <?= $player['Player_id'] == $managed_team['Captain_id'] ? 'disabled' : '' ?>>
                                                            Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">No players in this team yet.</div>
                            <?php endif; ?>
                        </div>

                        <!-- Add Players Section -->
                        <div class="col-md-6">
                            <h3 class="h5">Add Players to Team</h3>
                            <?php 
                            // Re-fetch available players (not on any team)
                            $available_players = $conn->query("
                                SELECT p.* 
                                FROM Player p
                                WHERE p.Player_id NOT IN (SELECT Player_id FROM Team_Player)
                                ORDER BY p.Player_name
                            ");
                            
                            if ($available_players->num_rows > 0): ?>
                                <form method="POST">
                                    <input type="hidden" name="team_id" value="<?= $managed_team['Team_id'] ?>">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Select Player</label>
                                        <select name="player_id" class="form-select" required>
                                            <option value="">Choose a player...</option>
                                            <?php while ($player = $available_players->fetch_assoc()): ?>
                                                <option value="<?= $player['Player_id'] ?>"><?= $player['Player_name'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" name="add_player" class="btn btn-primary">Add to Team</button>
                                    <a href="teams.php" class="btn btn-secondary">Back to Teams</a>
                                </form>
                            <?php else: ?>
                                <div class="alert alert-info">No available players to add (all players are already on teams).</div>
                                <a href="teams.php" class="btn btn-secondary">Back to Teams</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>