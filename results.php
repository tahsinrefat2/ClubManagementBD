<?php
include 'db_connect.php';

// Initialize messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Record match result
    if (isset($_POST['record_result'])) {
        $match_id = $_POST['match_id'];
        $team1_score = $_POST['team1_score'];
        $team2_score = $_POST['team2_score'];
        
        // Determine winner (NULL for draw)
        $winner_team_id = null;
        if ($team1_score > $team2_score) {
            $winner_team_id = $_POST['team1_id'];
        } elseif ($team2_score > $team1_score) {
            $winner_team_id = $_POST['team2_id'];
        }
        
        try {
            $conn->begin_transaction();
            
            // Check if result already exists
            $check = $conn->prepare("SELECT * FROM Result WHERE Match_id = ?");
            $check->bind_param("i", $match_id);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing result
                $stmt = $conn->prepare("UPDATE Result 
                                      SET team1_score = ?, team2_score = ?, winner_team_id = ?
                                      WHERE Match_id = ?");
                $stmt->bind_param("iiii", $team1_score, $team2_score, $winner_team_id, $match_id);
            } else {
                // Insert new result
                $stmt = $conn->prepare("INSERT INTO Result (Match_id, team1_score, team2_score, winner_team_id)
                                      VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiii", $match_id, $team1_score, $team2_score, $winner_team_id);
            }
            
            $stmt->execute();
            
            $conn->commit();
            $success = "Match result recorded successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error recording result: " . $e->getMessage();
        }
    }
}

// Fetch all tournaments for dropdown
$tournaments = $conn->query("SELECT * FROM Tournament ORDER BY Tournament_name");

// If viewing a specific tournament, get its matches and results
$selected_tournament = null;
$tournament_matches = [];
$tournament_standings = [];
if (isset($_GET['tournament_id'])) {
    $tournament_id = $_GET['tournament_id'];
    $selected_tournament = $conn->query("SELECT * FROM Tournament WHERE Tournament_id = $tournament_id")->fetch_assoc();
    
    // Get all matches for this tournament with results if available
    $tournament_matches = $conn->query("SELECT m.*, 
                                      t1.Team_name as team1_name, 
                                      t2.Team_name as team2_name,
                                      r.team1_score, r.team2_score, r.winner_team_id
                                      FROM Match_ m
                                      JOIN Team t1 ON m.Team1_id = t1.Team_id
                                      JOIN Team t2 ON m.Team2_id = t2.Team_id
                                      LEFT JOIN Result r ON m.Match_id = r.Match_id
                                      WHERE m.Tournament_id = $tournament_id
                                      ORDER BY m.Match_date");
    
    // Calculate tournament standings
    $teams_in_tournament = $conn->query("SELECT t.Team_id, t.Team_name 
                                        FROM Team t
                                        JOIN Team_Tournament tt ON t.Team_id = tt.Team_id
                                        WHERE tt.Tournament_id = $tournament_id");
    
    // Initialize standings array
    $tournament_standings = [];
    while ($team = $teams_in_tournament->fetch_assoc()) {
        $tournament_standings[$team['Team_id']] = [
            'team_name' => $team['Team_name'],
            'matches_played' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'points' => 0
        ];
    }
    
    // Calculate stats for each team
    $matches_with_results = $conn->query("SELECT m.*, r.team1_score, r.team2_score, r.winner_team_id
                                        FROM Match_ m
                                        JOIN Result r ON m.Match_id = r.Match_id
                                        WHERE m.Tournament_id = $tournament_id");
    
    while ($match = $matches_with_results->fetch_assoc()) {
        $team1_id = $match['Team1_id'];
        $team2_id = $match['Team2_id'];
        $team1_score = $match['team1_score'];
        $team2_score = $match['team2_score'];
        $winner_id = $match['winner_team_id'];
        
        // Update team1 stats
        $tournament_standings[$team1_id]['matches_played']++;
        $tournament_standings[$team1_id]['goals_for'] += $team1_score;
        $tournament_standings[$team1_id]['goals_against'] += $team2_score;
        
        // Update team2 stats
        $tournament_standings[$team2_id]['matches_played']++;
        $tournament_standings[$team2_id]['goals_for'] += $team2_score;
        $tournament_standings[$team2_id]['goals_against'] += $team1_score;
        
        // Update wins/draws/losses
        if ($winner_id == $team1_id) {
            $tournament_standings[$team1_id]['wins']++;
            $tournament_standings[$team1_id]['points'] += 3;
            $tournament_standings[$team2_id]['losses']++;
        } elseif ($winner_id == $team2_id) {
            $tournament_standings[$team2_id]['wins']++;
            $tournament_standings[$team2_id]['points'] += 3;
            $tournament_standings[$team1_id]['losses']++;
        } else { // Draw
            $tournament_standings[$team1_id]['draws']++;
            $tournament_standings[$team1_id]['points'] += 1;
            $tournament_standings[$team2_id]['draws']++;
            $tournament_standings[$team2_id]['points'] += 1;
        }
    }
    
    // Sort standings by points (desc), then goal difference (desc), then goals for (desc)
    usort($tournament_standings, function($a, $b) {
        if ($a['points'] != $b['points']) {
            return $b['points'] - $a['points'];
        }
        
        $gdA = ($a['goals_for'] - $a['goals_against']);
        $gdB = ($b['goals_for'] - $b['goals_against']);
        if ($gdA != $gdB) {
            return $gdB - $gdA;
        }
        
        return $b['goals_for'] - $a['goals_for'];
    });
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Results Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .result-card {
            transition: all 0.2s;
            border-left: 4px solid #28a745;
        }
        .result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .score-display {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .winner {
            color: #28a745;
            font-weight: bold;
        }
        .standings-table th {
            position: sticky;
            top: 0;
            background: blue;
        }
        .tab-content {
            background: white;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
            border-bottom: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }
        .table-darlk th {
            background-color: #343a40;
            color: green;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">📊 Results & Standings</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Tournament Selection Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Select Tournament</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <input type="hidden" name="page" value="results">
                <div class="col-md-8">
                    <select name="tournament_id" class="form-select" onchange="this.form.submit()" required>
                        <option value="">Select Tournament</option>
                        <?php while ($tournament = $tournaments->fetch_assoc()): ?>
                            <option value="<?= $tournament['Tournament_id'] ?>" 
                                <?= isset($_GET['tournament_id']) && $_GET['tournament_id'] == $tournament['Tournament_id'] ? 'selected' : '' ?>>
                                <?= $tournament['Tournament_name'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <?php if (isset($_GET['tournament_id'])): ?>
                <div class="col-md-4">
                    <a href="results.php" class="btn btn-secondary w-100">Clear Selection</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if ($selected_tournament): ?>
        <div class="row">
            <!-- Tournament Standings -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><?= $selected_tournament['Tournament_name'] ?> Standings</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($tournament_standings)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered standings-table">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Position</th>
                                            <th>Team</th>
                                            <th>P</th>
                                            <th>W</th>
                                            <th>D</th>
                                            <th>L</th>
                                            <th>GF</th>
                                            <th>GA</th>
                                            <th>GD</th>
                                            <th>Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tournament_standings as $position => $team): ?>
                                            <tr>
                                                <td><?= $position + 1 ?></td>
                                                <td><?= $team['team_name'] ?></td>
                                                <td><?= $team['matches_played'] ?></td>
                                                <td><?= $team['wins'] ?></td>
                                                <td><?= $team['draws'] ?></td>
                                                <td><?= $team['losses'] ?></td>
                                                <td><?= $team['goals_for'] ?></td>
                                                <td><?= $team['goals_against'] ?></td>
                                                <td><?= $team['goals_for'] - $team['goals_against'] ?></td>
                                                <td><strong><?= $team['points'] ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No matches played yet in this tournament.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Match Results -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Match Results</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($tournament_matches->num_rows > 0): ?>
                            <div class="list-group">
                                <?php while ($match = $tournament_matches->fetch_assoc()): ?>
                                    <div class="list-group-item result-card mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <span class="<?= $match['winner_team_id'] == $match['Team1_id'] ? 'winner' : '' ?>">
                                                    <?= $match['team1_name'] ?>
                                                </span>
                                                <span class="mx-2">vs</span>
                                                <span class="<?= $match['winner_team_id'] == $match['Team2_id'] ? 'winner' : '' ?>">
                                                    <?= $match['team2_name'] ?>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="badge bg-secondary">
                                                    <?= date('M j, Y', strtotime($match['Match_date'])) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <?php if (!is_null($match['team1_score'])): ?>
                                            <div class="score-display text-center mb-2">
                                                <?= $match['team1_score'] ?> - <?= $match['team2_score'] ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <!-- Result Form -->
                                        <form method="POST">
                                            <input type="hidden" name="match_id" value="<?= $match['Match_id'] ?>">
                                            <input type="hidden" name="team1_id" value="<?= $match['Team1_id'] ?>">
                                            <input type="hidden" name="team2_id" value="<?= $match['Team2_id'] ?>">
                                            
                                            <div class="row g-2">
                                                <div class="col-4">
                                                    <input type="number" name="team1_score" class="form-control" 
                                                           value="<?= $match['team1_score'] ?? '' ?>" 
                                                           placeholder="Score" min="0" required>
                                                </div>
                                                <div class="col-4 text-center align-self-center">
                                                    vs
                                                </div>
                                                <div class="col-4">
                                                    <input type="number" name="team2_score" class="form-control" 
                                                           value="<?= $match['team2_score'] ?? '' ?>" 
                                                           placeholder="Score" min="0" required>
                                                </div>
                                                <div class="col-12 mt-2">
                                                    <button type="submit" name="record_result" class="btn btn-sm btn-success w-100">
                                                        <?= isset($match['team1_score']) ? 'Update Result' : 'Record Result' ?>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No matches scheduled for this tournament yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tournament Report -->
        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Tournament Report</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Summary</h5>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Total Teams
                                <span class="badge bg-primary rounded-pill"><?= count($tournament_standings) ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Matches Scheduled
                                <span class="badge bg-primary rounded-pill"><?= $tournament_matches->num_rows ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Matches Completed
                                <span class="badge bg-primary rounded-pill">
                                    <?= array_reduce($tournament_standings, function($carry, $team) {
                                        return $carry + $team['matches_played'];
                                    }, 0) / 2 ?>
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>Top Performers</h5>
                        <?php if (!empty($tournament_standings)): ?>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <strong>Current Leader:</strong> 
                                    <?= $tournament_standings[0]['team_name'] ?> 
                                    (<?= $tournament_standings[0]['points'] ?> pts)
                                </div>
                                <div class="list-group-item">
                                    <strong>Best Attack:</strong> 
                                    <?= array_reduce($tournament_standings, function($a, $b) {
                                        return $a['goals_for'] > $b['goals_for'] ? $a : $b;
                                    })['team_name'] ?>
                                </div>
                                <div class="list-group-item">
                                    <strong>Best Defense:</strong> 
                                    <?= array_reduce($tournament_standings, function($a, $b) {
                                        return $a['goals_against'] < $b['goals_against'] ? $a : $b;
                                    })['team_name'] ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">No matches played yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Print Button -->
                <div class="text-center mt-3">
                    <button class="btn btn-primary" onclick="window.print()">Print Report</button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Please select a tournament to view results and standings.</div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>