<?php
include 'db_connect.php';

// Initialize messages
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Register/Update Venue
    if (isset($_POST['save_venue'])) {
        $venue_name = $_POST['venue_name'];
        $venue_address = $_POST['venue_address'];
        $contact_info = $_POST['contact_info'];
        $facilities = $_POST['facilities'];
        $venue_id = $_POST['venue_id'] ?? null;
        
        try {
            if ($venue_id) {
                // Update existing venue
                $stmt = $conn->prepare("UPDATE Venue SET 
                                      Venue_name = ?, 
                                      Venue_address = ?, 
                                      Venue_contact_info = ?, 
                                      Available_facilities = ?
                                      WHERE Venue_id = ?");
                $stmt->bind_param("ssssi", $venue_name, $venue_address, $contact_info, $facilities, $venue_id);
            } else {
                // Create new venue
                $stmt = $conn->prepare("INSERT INTO Venue (Venue_name, Venue_address, Venue_contact_info, Available_facilities) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $venue_name, $venue_address, $contact_info, $facilities);
            }
            
            if ($stmt->execute()) {
                $success = $venue_id ? "Venue updated successfully!" : "Venue registered successfully!";
            } else {
                $error = "Error saving venue: " . $conn->error;
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
    
    // Handle venue booking
    if (isset($_POST['book_venue'])) {
        $venue_id = $_POST['venue_id'];
        $match_id = $_POST['match_id'];
        $booking_date = $_POST['booking_date'];
        
        // Check if venue is already booked for this date
        $check = $conn->prepare("SELECT * FROM Venue_Booking 
                                WHERE Venue_id = ? AND Booking_date = ?");
        $check->bind_param("is", $venue_id, $booking_date);
        $check->execute();
        $result = $check->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Venue is already booked for the selected date!";
        } else {
            // Book the venue
            $stmt = $conn->prepare("INSERT INTO Venue_Booking (Venue_id, Match_id, Booking_date)
                                   VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $venue_id, $match_id, $booking_date);
            
            if ($stmt->execute()) {
                $success = "Venue booked successfully!";
            } else {
                $error = "Error booking venue: " . $conn->error;
            }
        }
    }
    
    // Cancel booking
    if (isset($_POST['cancel_booking'])) {
        $booking_id = $_POST['booking_id'];
        
        $stmt = $conn->prepare("DELETE FROM Venue_Booking WHERE Booking_id = ?");
        $stmt->bind_param("i", $booking_id);
        
        if ($stmt->execute()) {
            $success = "Booking canceled successfully!";
        } else {
            $error = "Error canceling booking: " . $conn->error;
        }
    }
}

// Handle delete requests
if (isset($_GET['delete'])) {
    $venue_id = $_GET['delete'];
    
    // First delete any bookings for this venue
    $conn->query("DELETE FROM Venue_Booking WHERE Venue_id = $venue_id");
    
    // Then delete the venue
    if ($conn->query("DELETE FROM Venue WHERE Venue_id = $venue_id")) {
        $success = "Venue deleted successfully!";
    } else {
        $error = "Error deleting venue: " . $conn->error;
    }
}

// Fetch all venues
$venues = $conn->query("SELECT * FROM Venue ORDER BY Venue_name");

// Fetch all matches for booking
$matches = $conn->query("SELECT m.*, t1.Team_name as team1_name, t2.Team_name as team2_name
                        FROM Match_ m
                        JOIN Team t1 ON m.Team1_id = t1.Team_id
                        JOIN Team t2 ON m.Team2_id = t2.Team_id
                        ORDER BY m.Match_date DESC");

// Fetch all bookings
$bookings = $conn->query("SELECT vb.*, v.Venue_name, m.Match_date, 
                         t1.Team_name as team1_name, t2.Team_name as team2_name
                         FROM Venue_Booking vb
                         JOIN Venue v ON vb.Venue_id = v.Venue_id
                         JOIN Match_ m ON vb.Match_id = m.Match_id
                         JOIN Team t1 ON m.Team1_id = t1.Team_id
                         JOIN Team t2 ON m.Team2_id = t2.Team_id
                         ORDER BY vb.Booking_date DESC");

// If editing a venue, get its data
$edit_venue = null;
if (isset($_GET['edit'])) {
    $edit_venue = $conn->query("SELECT * FROM Venue WHERE Venue_id = " . $_GET['edit'])->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Venue Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .venue-card {
            transition: all 0.2s;
        }
        .venue-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            border-bottom: 3px solid #0d6efd;
        }
        .facility-badge {
            background-color: #6c757d;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body class="p-4 bg-light">
<?php include 'navbar.php'; ?>
<div class="container">
    <h2 class="mb-4">🏟️ Venue Management</h2>
    
    <!-- Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4" id="venueTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="venues-tab" data-bs-toggle="tab" data-bs-target="#venues" type="button" role="tab">All Venues</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab"><?= isset($_GET['edit']) ? 'Edit Venue' : 'Register Venue' ?></button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="booking-tab" data-bs-toggle="tab" data-bs-target="#booking" type="button" role="tab">Book Venue</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">View Bookings</button>
        </li>
    </ul>

    <div class="tab-content" id="venueTabsContent">
        <!-- All Venues Tab -->
        <div class="tab-pane fade show active" id="venues" role="tabpanel">
            <div class="row">
                <?php while ($venue = $venues->fetch_assoc()): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card venue-card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?= $venue['Venue_name'] ?></h5>
                                <p class="card-text">
                                    <strong>Address:</strong> <?= $venue['Venue_address'] ?><br>
                                    <strong>Contact:</strong> <?= $venue['Venue_contact_info'] ?>
                                </p>
                                <div class="mb-3">
                                    <strong>Facilities:</strong><br>
                                    <?php 
                                    $facilities = explode(',', $venue['Available_facilities']);
                                    foreach ($facilities as $facility): 
                                        if (!empty(trim($facility))): ?>
                                            <span class="badge facility-badge"><?= trim($facility) ?></span>
                                        <?php endif; 
                                    endforeach; ?>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="?edit=<?= $venue['Venue_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="?delete=<?= $venue['Venue_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this venue?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Register/Edit Venue Tab -->
        <div class="tab-pane fade" id="register" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><?= isset($_GET['edit']) ? 'Edit Venue' : 'Register New Venue' ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if (isset($_GET['edit'])): ?>
                            <input type="hidden" name="venue_id" value="<?= $edit_venue['Venue_id'] ?>">
                        <?php endif; ?>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Venue Name</label>
                                <input type="text" name="venue_name" class="form-control" required
                                       value="<?= $edit_venue['Venue_name'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address</label>
                                <input type="text" name="venue_address" class="form-control" required
                                       value="<?= $edit_venue['Venue_address'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Information</label>
                                <input type="text" name="contact_info" class="form-control" required
                                       value="<?= $edit_venue['Venue_contact_info'] ?? '' ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Available Facilities (comma separated)</label>
                                <input type="text" name="facilities" class="form-control" 
                                       placeholder="e.g., Football field, Changing rooms, Parking"
                                       value="<?= $edit_venue['Available_facilities'] ?? '' ?>">
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="save_venue">
                                    <?= isset($_GET['edit']) ? 'Update Venue' : 'Register Venue' ?>
                                </button>
                                <a href="venues.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Book Venue Tab -->
        <div class="tab-pane fade" id="booking" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Book a Venue</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Venue</label>
                                <select name="venue_id" class="form-select" required>
                                    <option value="">Select Venue</option>
                                    <?php $venues->data_seek(0); // Reset pointer ?>
                                    <?php while ($venue = $venues->fetch_assoc()): ?>
                                        <option value="<?= $venue['Venue_id'] ?>"><?= $venue['Venue_name'] ?></option>
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
                            <div class="col-md-12">
                                <label class="form-label">Booking Date</label>
                                <input type="date" name="booking_date" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" name="book_venue">Book Venue</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- View Bookings Tab -->
        <div class="tab-pane fade" id="bookings" role="tabpanel">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Venue Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if ($bookings->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Venue</th>
                                        <th>Match</th>
                                        <th>Match Date</th>
                                        <th>Booking Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $booking['Venue_name'] ?></td>
                                            <td><?= $booking['team1_name'] ?> vs <?= $booking['team2_name'] ?></td>
                                            <td><?= date('M j, Y', strtotime($booking['Match_date'])) ?></td>
                                            <td><?= date('M j, Y', strtotime($booking['Booking_date'])) ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?= $booking['Booking_id'] ?>">
                                                    <button type="submit" name="cancel_booking" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                        Cancel
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No venue bookings found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Activate appropriate tab based on URL
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_GET['edit'])): ?>
            const registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
            registerTab.show();
        <?php endif; ?>
    });
</script>
</body>
</html>
