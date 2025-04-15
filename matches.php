<?php
include 'db_connect.php';

// Initialize messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_match'])) {
        $tournament_id = $_POST['tournament_id'];
        $team1_id = $_POST['team1_id'];
        $team2_id = $_POST['team2_id'];
        $match_date = $_POST['match_date'];
        $referee = $_POST['referee'];

        // Validate teams are different
        if ($team1_id == $team2_id) {
            $error = "Team 1 and Team 2 must be different";
        } else {
            $stmt = $conn->prepare("INSERT INTO Match_ (Tournament_id, Team1_id, Team2_id, Match_date, referee) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iiiss", $tournament_id, $team1_id, $team2_id, $match_date, $referee);
            
            if ($stmt->execute()) {
                $success = "Match created successfully!";
            } else {
                $error = "Error creating match: " . $conn->error;
            }
        }
    }
    
    // Handle delete requests
    if (isset($_POST['delete_match'])) {
        $match_id = $_POST['match_id'];
        
        $stmt = $conn->prepare("DELETE FROM Match_ WHERE Match_id = ?");
        $stmt->bind_param("i", $match_id);
        
        if ($stmt->execute()) {
            $success = "Match deleted successfully!";
        } else {
            $error = "Error deleting match: " . $conn->error;
        }
    }
}

// Fetch all tournaments for dropdown
$tournaments = $conn->query("SELECT * FROM Tournament ORDER BY Tournament_name");

// Get registered teams for the selected tournament (if one is selected)
$teams = [];
$tournament_teams = [];
$selected_tournament_id = null;

if (isset($_POST['tournament_id']) || isset($_GET['tournament_id'])) {
    $selected_tournament_id = isset($_POST['tournament_id']) ? $_POST['tournament_id'] : $_GET['tournament_id'];
    
    $tournament_teams = $conn->query("SELECT t.* FROM Team t
                                    JOIN Team_Tournament tt ON t.Team_id = tt.Team_id
                                    WHERE tt.Tournament_id = $selected_tournament_id
                                    ORDER BY t.Team_name");
} else {
    // Default to all teams if no tournament selected
    $teams = $conn->query("SELECT * FROM Team ORDER BY Team_name");
}

// If viewing a specific tournament, get its matches
$selected_tournament = null;
$tournament_matches = [];
if (isset($_GET['tournament_id'])) {
    $tournament_id = $_GET['tournament_id'];
    $selected_tournament = $conn->query("SELECT * FROM Tournament WHERE Tournament_id = $tournament_id")->fetch_assoc();
    
    $tournament_matches = $conn->query("SELECT m.*, 
                                     t1.Team_name as team1_name, 
                                     t2.Team_name as team2_name 
                                     FROM Match_ m
                                     JOIN Team t1 ON m.Team1_id = t1.Team_id
                                     JOIN Team t2 ON m.Team2_id = t2.Team_id
                                     WHERE m.Tournament_id = $tournament_id
                                     ORDER BY m.Match_date");
}

// Get all matches for the schedule view
$all_matches = $conn->query("SELECT m.*, 
                           t1.Team_name as team1_name, 
                           t2.Team_name as team2_name,
                           tn.Tournament_name
                           FROM Match_ m
                           JOIN Team t1 ON m.Team1_id = t1.Team_id
                           JOIN Team t2 ON m.Team2_id = t2.Team_id
                           JOIN Tournament tn ON m.Tournament_id = tn.Tournament_id
                           ORDER BY m.Match_date DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Match Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .match-card {
            transition: transform 0.2s;
            border-left: 4px solid #0d6efd;
        }
        .match-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .vs-text {
            font-weight: bold;
            color: #dc3545;
        }
        .schedule-badge {
            font-size: 0.9rem;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">⚽ Match Management</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="matchTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="create-tab" data-bs-toggle="tab" data-bs-target="#create" type="button" role="tab">Create Match</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="view-tab" data-bs-toggle="tab" data-bs-target="#view" type="button" role="tab">View Tournament Matches</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">Full Schedule</button>
        </li>
    </ul>

    <div class="tab-content" id="matchTabsContent">
        <!-- Create Match Tab -->
        <div class="tab-pane fade show active" id="create" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Create New Match</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="matchForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tournament</label>
                                <select name="tournament_id" class="form-select" id="tournamentSelect" required>
                                    <option value="">Select Tournament</option>
                                    <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                                        <option value="<?= $tournament['Tournament_id'] ?>" 
                                            <?= $selected_tournament_id == $tournament['Tournament_id'] ? 'selected' : '' ?>>
                                            <?= $tournament['Tournament_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Match Date & Time</label>
                                <input type="datetime-local" name="match_date" class="form-control" required>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Team 1</label>
                                <select name="team1_id" class="form-select" id="team1Select" required>
                                    <option value="">Select Team</option>
                                    <?php if (isset($tournament_teams) && $tournament_teams->num_rows > 0): ?>
                                        <?php while ($team = $tournament_teams->fetch_assoc()): ?>
                                            <option value="<?= $team['Team_id'] ?>"><?= $team['Team_name'] ?></option>
                                        <?php endwhile; ?>
                                        <?php $tournament_teams->data_seek(0); // Reset pointer for second dropdown ?>
                                    <?php elseif (isset($teams) && $teams->num_rows > 0): ?>
                                        <?php while ($team = $teams->fetch_assoc()): ?>
                                            <option value="<?= $team['Team_id'] ?>"><?= $team['Team_name'] ?></option>
                                        <?php endwhile; ?>
                                        <?php $teams->data_seek(0); // Reset pointer for second dropdown ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2 text-center align-self-end">
                                <div class="vs-text">VS</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Team 2</label>
                                <select name="team2_id" class="form-select" id="team2Select" required>
                                    <option value="">Select Team</option>
                                    <?php if (isset($tournament_teams) && $tournament_teams->num_rows > 0): ?>
                                        <?php while ($team = $tournament_teams->fetch_assoc()): ?>
                                            <option value="<?= $team['Team_id'] ?>"><?= $team['Team_name'] ?></option>
                                        <?php endwhile; ?>
                                    <?php elseif (isset($teams) && $teams->num_rows > 0): ?>
                                        <?php while ($team = $teams->fetch_assoc()): ?>
                                            <option value="<?= $team['Team_id'] ?>"><?= $team['Team_name'] ?></option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Referee</label>
                                <input type="text" name="referee" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="create_match">Create Match</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Tournament Matches Tab -->
        <div class="tab-pane fade" id="view" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">View Tournament Matches</h5>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <input type="hidden" name="page" value="matches">
                        <div class="row">
                            <div class="col-md-8">
                                <select name="tournament_id" class="form-select" onchange="this.form.submit()" required>
                                    <option value="">Select Tournament</option>
                                    <?php $tournaments->data_seek(0); // Reset pointer ?>
                                    <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                                        <option value="<?= $tournament['Tournament_id'] ?>" 
                                            <?= isset($_GET['tournament_id']) && $_GET['tournament_id'] == $tournament['Tournament_id'] ? 'selected' : '' ?>>
                                            <?= $tournament['Tournament_name'] ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </form>

                    <?php if ($selected_tournament): ?>
                        <h4 class="mb-3"><?= $selected_tournament['Tournament_name'] ?> Schedule</h4>
                        
                        <?php if ($tournament_matches->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($match = $tournament_matches->fetch_assoc()): ?>
                                    <div class="list-group-item match-card mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-bold"><?= $match['team1_name'] ?></span>
                                                <span class="vs-text mx-2">vs</span>
                                                <span class="fw-bold"><?= $match['team2_name'] ?></span>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-secondary schedule-badge">
                                                    <?= date('M j, Y g:i A', strtotime($match['Match_date'])) ?>
                                                </span>
                                                <span class="badge bg-info text-dark schedule-badge ms-1">
                                                    Ref: <?= $match['referee'] ?>
                                                </span>
                                            </div>
                                        </div>
                                        <form method="POST" class="mt-2 text-end">
                                            <input type="hidden" name="match_id" value="<?= $match['Match_id'] ?>">
                                            <button type="submit" name="delete_match" class="btn btn-sm btn-outline-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this match?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No matches scheduled for this tournament yet.</div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">Please select a tournament to view matches.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Full Schedule Tab -->
        <div class="tab-pane fade" id="schedule" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Full Match Schedule</h5>
                </div>
                <div class="card-body">
                    <?php if ($all_matches->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Tournament</th>
                                        <th>Match</th>
                                        <th>Referee</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($match = $all_matches->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= date('M j, Y g:i A', strtotime($match['Match_date'])) ?></td>
                                            <td><?= $match['Tournament_name'] ?></td>
                                            <td>
                                                <span class="fw-bold"><?= $match['team1_name'] ?></span>
                                                <span class="vs-text mx-1">vs</span>
                                                <span class="fw-bold"><?= $match['team2_name'] ?></span>
                                            </td>
                                            <td><?= $match['referee'] ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="match_id" value="<?= $match['Match_id'] ?>">
                                                    <button type="submit" name="delete_match" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Are you sure you want to delete this match?')">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No matches scheduled yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activate tab if coming from tournament management
    <?php if (isset($_GET['tournament_id'])): ?>
        const viewTab = new bootstrap.Tab(document.getElementById('view-tab'));
        viewTab.show();
    <?php endif; ?>

    // Handle tournament selection change
    const tournamentSelect = document.getElementById('tournamentSelect');
    if (tournamentSelect) {
        tournamentSelect.addEventListener('change', function() {
            // Submit the form when tournament changes to reload with correct teams
            document.getElementById('matchForm').submit();
        });
    }
});
</script>
</body>
</html>