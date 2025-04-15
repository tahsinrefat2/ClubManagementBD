<?php
include 'db_connect.php';

// Initialize success message
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_tournament'])) {
        $tournament_name = $_POST['tournament_name'];
        $sport = $_POST['sport'];
        $format = $_POST['format'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        $stmt = $conn->prepare("INSERT INTO Tournament (Tournament_name, sport, format, Tournament_start_date, Tournament_end_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $tournament_name, $sport, $format, $start_date, $end_date);
        
        if ($stmt->execute()) {
            $success = "Tournament created successfully!";
        } else {
            $error = "Error creating tournament: " . $conn->error;
        }
    }
    
    // Team registration
    if (isset($_POST['register_team'])) {
        $team_id = $_POST['team_id'];
        $tournament_id = $_POST['tournament_id'];
        
        // Check if team is already registered
        $check = $conn->prepare("SELECT * FROM Team_Tournament WHERE Team_id = ? AND Tournament_id = ?");
        $check->bind_param("ii", $team_id, $tournament_id);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO Team_Tournament (Team_id, Tournament_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $team_id, $tournament_id);
            
            if ($stmt->execute()) {
                $success = "Team registered successfully!";
            } else {
                $error = "Error registering team: " . $conn->error;
            }
        } else {
            $error = "This team is already registered for the tournament";
        }
    }
    
    // Remove team from tournament
    if (isset($_POST['remove_team'])) {
        $team_id = $_POST['team_id'];
        $tournament_id = $_POST['tournament_id'];
        
        $stmt = $conn->prepare("DELETE FROM Team_Tournament WHERE Team_id = ? AND Tournament_id = ?");
        $stmt->bind_param("ii", $team_id, $tournament_id);
        
        if ($stmt->execute()) {
            $success = "Team removed from tournament!";
        } else {
            $error = "Error removing team: " . $conn->error;
        }
    }
}

// Handle delete requests
if (isset($_GET['delete'])) {
    $tournament_id = $_GET['delete'];
    
    // First delete team registrations (if any)
    $stmt = $conn->prepare("DELETE FROM Team_Tournament WHERE Tournament_id = ?");
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $stmt->close();  // Close this statement before preparing the next one
    
    // Then delete the tournament
    $stmt = $conn->prepare("DELETE FROM Tournament WHERE Tournament_id = ?");
    $stmt->bind_param("i", $tournament_id);
    
    if ($stmt->execute()) {
        $success = "Tournament deleted successfully!";
        // Redirect to avoid resubmission on refresh
        header("Location: tournaments.php?success=" . urlencode($success));
        exit();
    } else {
        $error = "Error deleting tournament: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all tournaments
$tournaments = $conn->query("SELECT * FROM Tournament ORDER BY Tournament_start_date DESC");

// Fetch all teams (for registration)
$teams = $conn->query("SELECT * FROM Team");

// If managing a specific tournament, get its details and registered teams
if (isset($_GET['manage'])) {
    $tournament_id = $_GET['manage'];
    $current_tournament = $conn->query("SELECT * FROM Tournament WHERE Tournament_id = $tournament_id")->fetch_assoc();
    
    $registered_teams = $conn->query("SELECT t.* FROM Team t
                                    JOIN Team_Tournament tt ON t.Team_id = tt.Team_id
                                    WHERE tt.Tournament_id = $tournament_id");
    
    $available_teams = $conn->query("SELECT * FROM Team 
                                   WHERE Team_id NOT IN (
                                       SELECT Team_id FROM Team_Tournament WHERE Tournament_id = $tournament_id
                                   )");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tournament Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .badge-sport {
            background-color: #6c757d;
        }
        .badge-format {
            background-color: #0d6efd;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">🏆 Tournament Management</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Create Tournament Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Create New Tournament</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tournament Name</label>
                        <input type="text" name="tournament_name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sport</label>
                        <input type="text" name="sport" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Format</label>
                        <input type="text" name="format" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" name="create_tournament">Create Tournament</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tournaments List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">All Tournaments</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Sport</th>
                            <th>Format</th>
                            <th>Dates</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($t = $tournaments->fetch_assoc()): ?>
                            <tr>
                                <td><?= $t['Tournament_id'] ?></td>
                                <td><?= $t['Tournament_name'] ?></td>
                                <td><span class="badge badge-sport bg-secondary"><?= $t['sport'] ?></span></td>
                                <td><span class="badge badge-format bg-primary"><?= $t['format'] ?></span></td>
                                <td>
                                    <?= date('M j, Y', strtotime($t['Tournament_start_date'])) ?> - 
                                    <?= date('M j, Y', strtotime($t['Tournament_end_date'])) ?>
                                </td>
                                <td>
                                    <a href="?manage=<?= $t['Tournament_id'] ?>" class="btn btn-sm btn-info">Manage Teams</a>
                                    <a href="?delete=<?= $t['Tournament_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this tournament?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Team Management for Specific Tournament -->
    <?php if (isset($_GET['manage'])): ?>
    <div class="card mt-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">Team Management for: <?= $current_tournament['Tournament_name'] ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Registered Teams -->
                <div class="col-md-6">
                    <h5>Registered Teams</h5>
                    <?php if ($registered_teams->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($team = $registered_teams->fetch_assoc()): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $team['Team_name'] ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="team_id" value="<?= $team['Team_id'] ?>">
                                        <input type="hidden" name="tournament_id" value="<?= $current_tournament['Tournament_id'] ?>">
                                        <button type="submit" name="remove_team" class="btn btn-sm btn-outline-danger">Remove</button>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">No teams registered yet.</div>
                    <?php endif; ?>
                </div>
                
                <!-- Register New Team -->
                <div class="col-md-6">
                    <h5>Register New Team</h5>
                    <?php if ($available_teams->num_rows > 0): ?>
                        <form method="POST">
                            <input type="hidden" name="tournament_id" value="<?= $current_tournament['Tournament_id'] ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Select Team</label>
                                <select name="team_id" class="form-select" required>
                                    <option value="">-- Select Team --</option>
                                    <?php while ($team = $available_teams->fetch_assoc()): ?>
                                        <option value="<?= $team['Team_id'] ?>"><?= $team['Team_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <button type="submit" name="register_team" class="btn btn-success">Register Team</button>
                            <a href="tournaments.php" class="btn btn-secondary">Back to Tournaments</a>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">All available teams are already registered.</div>
                        <a href="tournaments.php" class="btn btn-secondary">Back to Tournaments</a>
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