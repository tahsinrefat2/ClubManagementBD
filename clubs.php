<?php
// clubs.php

// DB Connection
include 'db_connect.php';
// Fetch all clubs
$clubs = $conn->query("SELECT * FROM Club");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management System</title>
    <?php include 'navbar.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }
        
        .club-logo {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
        }
        
        .action-btn {
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .modal-content {
            border-radius: 10px;
        }
        
        .sports-badge {
            background-color: var(--accent-color);
            color: white;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        #addClubModal .form-control, #editClubModal .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
        }
        
        .file-upload-input {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
        }
        
        .file-upload-label {
            cursor: pointer;
            display: block;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px dashed #ced4da;
            text-align: center;
        }
        
        .file-upload-label:hover {
            background: #e9ecef;
        }
    </style>
</head>
<body>
    
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="fw-bold text-primary"><i class="fas fa-chess-rook me-2"></i> Club Management</h1>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addClubModal">
                <i class="fas fa-plus me-2"></i> Add New Club
            </button>
        </div>
        
        <!-- Clubs Grid View -->
        <div class="row mb-5" id="clubsGrid">
            <?php while ($club = $clubs->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <img src="uploads/<?= $club['Club_logo'] ?>" class="club-logo me-3" alt="<?= $club['Club_name'] ?>">
                                <div>
                                    <h5 class="card-title mb-0"><?= $club['Club_name'] ?></h5>
                                    <small class="text-muted">ID: <?= $club['Club_id'] ?></small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><i class="fas fa-map-marker-alt me-2 text-primary"></i> <?= $club['Club_address'] ?></p>
                                <p class="mb-1"><i class="fas fa-phone me-2 text-primary"></i> <?= $club['Club_contact_info'] ?></p>
                                <p class="mb-1"><i class="fas fa-calendar-day me-2 text-primary"></i> <?= date('M d, Y', strtotime($club['Club_registration_date'])) ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <h6 class="text-primary">Sports Offered:</h6>
                                <div>
                                    <?php 
                                    $sports = explode(',', $club['Sports_offered']);
                                    foreach ($sports as $sport): 
                                    ?>
                                        <span class="badge sports-badge"><?= trim($sport) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-sm btn-outline-primary me-2 action-btn edit-btn" 
                                        data-id="<?= $club['Club_id'] ?>"
                                        data-name="<?= $club['Club_name'] ?>"
                                        data-address="<?= $club['Club_address'] ?>"
                                        data-contact="<?= $club['Club_contact_info'] ?>"
                                        data-sports="<?= $club['Sports_offered'] ?>"
                                        data-date="<?= $club['Club_registration_date'] ?>"
                                        data-logo="<?= $club['Club_logo'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger action-btn delete-btn" data-id="<?= $club['Club_id'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Clubs Table View (Hidden by default) -->
        <div class="card d-none" id="clubsTableView">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Contact</th>
                                <th>Sports</th>
                                <th>Reg. Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $clubs->data_seek(0); // Reset pointer ?>
                            <?php while ($club = $clubs->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $club['Club_id'] ?></td>
                                    <td><img src="uploads/<?= $club['Club_logo'] ?>" class="club-logo" alt="<?= $club['Club_name'] ?>"></td>
                                    <td><?= $club['Club_name'] ?></td>
                                    <td><?= $club['Club_address'] ?></td>
                                    <td><?= $club['Club_contact_info'] ?></td>
                                    <td>
                                        <?php 
                                        $sports = explode(',', $club['Sports_offered']);
                                        foreach ($sports as $sport): 
                                        ?>
                                            <span class="badge sports-badge"><?= trim($sport) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($club['Club_registration_date'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-2 action-btn edit-btn" 
                                                data-id="<?= $club['Club_id'] ?>"
                                                data-name="<?= $club['Club_name'] ?>"
                                                data-address="<?= $club['Club_address'] ?>"
                                                data-contact="<?= $club['Club_contact_info'] ?>"
                                                data-sports="<?= $club['Sports_offered'] ?>"
                                                data-date="<?= $club['Club_registration_date'] ?>"
                                                data-logo="<?= $club['Club_logo'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger action-btn delete-btn" data-id="<?= $club['Club_id'] ?>">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- View Toggle -->
        <div class="text-center mb-4">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" id="gridViewBtn">
                    <i class="fas fa-th-large me-2"></i> Grid View
                </button>
                <button type="button" class="btn btn-outline-primary" id="tableViewBtn">
                    <i class="fas fa-table me-2"></i> Table View
                </button>
            </div>
        </div>
    </div>
    
    <!-- Add Club Modal -->
    <div class="modal fade" id="addClubModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> Add New Club</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addClubForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Club Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Date</label>
                                <input type="date" name="reg_date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Club Address</label>
                                <input type="text" name="address" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Information</label>
                                <input type="text" name="contact" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Sports Offered (comma-separated)</label>
                                <input type="text" name="sports" class="form-control" placeholder="e.g. Football, Basketball, Tennis" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Club Logo</label>
                                <div class="file-upload">
                                    <label class="file-upload-label" for="addClubLogo">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                                        <p class="mb-1">Click to upload logo</p>
                                        <p class="text-muted small">PNG, JPG up to 2MB</p>
                                    </label>
                                    <input type="file" name="logo" id="addClubLogo" class="file-upload-input" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Register Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Club Modal -->
    <div class="modal fade" id="editClubModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Club</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editClubForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="club_id" id="editClubId">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Club Name</label>
                                <input type="text" name="name" id="editClubName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Registration Date</label>
                                <input type="date" name="reg_date" id="editClubDate" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Club Address</label>
                                <input type="text" name="address" id="editClubAddress" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Information</label>
                                <input type="text" name="contact" id="editClubContact" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Sports Offered (comma-separated)</label>
                                <input type="text" name="sports" id="editClubSports" class="form-control" placeholder="e.g. Football, Basketball, Tennis" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Current Logo</label>
                                <img src="" id="editClubCurrentLogo" class="club-logo d-block mb-2">
                                <label class="form-label">Change Logo (optional)</label>
                                <div class="file-upload">
                                    <label class="file-upload-label" for="editClubLogo">
                                        <i class="fas fa-cloud-upload-alt fa-2x mb-2 text-primary"></i>
                                        <p class="mb-1">Click to upload new logo</p>
                                        <p class="text-muted small">PNG, JPG up to 2MB</p>
                                    </label>
                                    <input type="file" name="logo" id="editClubLogo" class="file-upload-input">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this club? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Club</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="successToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-success text-white">
                <strong class="me-auto">Success</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="successToastMessage"></div>
        </div>
    </div>
    
    <!-- Error Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="errorToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-danger text-white">
                <strong class="me-auto">Error</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body" id="errorToastMessage"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // View Toggle
            $('#gridViewBtn').click(function() {
                $('#clubsGrid').removeClass('d-none');
                $('#clubsTableView').addClass('d-none');
                $(this).addClass('active');
                $('#tableViewBtn').removeClass('active');
            });
            
            $('#tableViewBtn').click(function() {
                $('#clubsGrid').addClass('d-none');
                $('#clubsTableView').removeClass('d-none');
                $(this).addClass('active');
                $('#gridViewBtn').removeClass('active');
            });
            
            // Initialize toasts
            var successToast = new bootstrap.Toast(document.getElementById('successToast'));
            var errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
            
            // Add Club Form Submission
            $('#addClubForm').submit(function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: 'ajax_add_club.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#addClubModal').modal('hide');
                        $('#addClubForm')[0].reset();
                        
                        // Show success message
                        $('#successToastMessage').text('Club added successfully!');
                        successToast.show();
                        
                        // Reload clubs after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        $('#errorToastMessage').text('Error adding club: ' + xhr.responseText);
                        errorToast.show();
                    }
                });
            });
            
            // Edit Club Modal Setup
            $('.edit-btn').click(function() {
                var clubId = $(this).data('id');
                var clubName = $(this).data('name');
                var clubAddress = $(this).data('address');
                var clubContact = $(this).data('contact');
                var clubSports = $(this).data('sports');
                var clubDate = $(this).data('date');
                var clubLogo = $(this).data('logo');
                
                $('#editClubId').val(clubId);
                $('#editClubName').val(clubName);
                $('#editClubAddress').val(clubAddress);
                $('#editClubContact').val(clubContact);
                $('#editClubSports').val(clubSports);
                $('#editClubDate').val(clubDate);
                $('#editClubCurrentLogo').attr('src', 'uploads/' + clubLogo);
                
                $('#editClubModal').modal('show');
            });
            
            // Edit Club Form Submission
            $('#editClubForm').submit(function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                
                $.ajax({
                    url: 'ajax_update_club.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#editClubModal').modal('hide');
                        
                        // Show success message
                        $('#successToastMessage').text('Club updated successfully!');
                        successToast.show();
                        
                        // Reload clubs after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        $('#errorToastMessage').text('Error updating club: ' + xhr.responseText);
                        errorToast.show();
                    }
                });
            });
            
            // Delete Club Confirmation
            var clubToDelete = null;
            
            $('.delete-btn').click(function() {
                clubToDelete = $(this).data('id');
                $('#confirmModal').modal('show');
            });
            
            $('#confirmDeleteBtn').click(function() {
                $.ajax({
                    url: 'ajax_delete_club.php',
                    type: 'POST',
                    data: { club_id: clubToDelete },
                    success: function(response) {
                        $('#confirmModal').modal('hide');
                        
                        // Show success message
                        $('#successToastMessage').text('Club deleted successfully!');
                        successToast.show();
                        
                        // Reload clubs after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        $('#confirmModal').modal('hide');
                        $('#errorToastMessage').text('Error deleting club: ' + xhr.responseText);
                        errorToast.show();
                    }
                });
            });
            
            // File upload preview
            $('#addClubLogo').change(function() {
                var fileName = $(this).val().split('\\').pop();
                $('.file-upload-label').html('<i class="fas fa-check-circle fa-2x mb-2 text-success"></i><p class="mb-1">' + fileName + '</p>');
            });
            
            $('#editClubLogo').change(function() {
                var fileName = $(this).val().split('\\').pop();
                $('.file-upload-label').html('<i class="fas fa-check-circle fa-2x mb-2 text-success"></i><p class="mb-1">' + fileName + '</p>');
            });
        });
    </script>
</body>
</html>