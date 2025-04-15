<?php
include 'db_connect.php';

// Fetch clubs for dropdown
$clubs = $conn->query("SELECT * FROM Club ORDER BY Club_name");

// Fetch member list with club and membership dates
$members = $conn->query("
    SELECT m.*, c.Club_name, cm.Membership_start_date, cm.Membership_end_date
    FROM Member m
    JOIN Club_member cm ON m.Member_id = cm.Member_id
    JOIN Club c ON c.Club_id = cm.Club_id
    ORDER BY m.Member_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Management</title>
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
        
        .member-avatar {
            width: 60px;
            height: 60px;
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
        
        .badge-sport {
            background-color: var(--accent-color);
            color: white;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        .membership-status {
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .active-status {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .expired-status {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .soon-status {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        #addMemberForm .form-control, #editMemberForm .form-control {
            border-radius: 8px;
            padding: 10px 15px;
        }
        
        .action-btn {
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="fw-bold text-primary"><i class="fas fa-users me-2"></i> Member Management</h1>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="fas fa-user-plus me-2"></i> Add New Member
            </button>
        </div>
        
        <!-- Members Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="membersTable">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Sports</th>
                                <th>Club</th>
                                <th>Membership</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($member = $members->fetch_assoc()): 
                                $today = date('Y-m-d');
                                $endDate = $member['Membership_end_date'];
                                $statusClass = '';
                                
                                if ($endDate < $today) {
                                    $statusClass = 'expired-status';
                                    $statusText = 'Expired';
                                } elseif (date('Y-m-d', strtotime('-30 days', strtotime($endDate))) <= $today) {
                                    $statusClass = 'soon-status';
                                    $statusText = 'Expires Soon';
                                } else {
                                    $statusClass = 'active-status';
                                    $statusText = 'Active';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($member['Member_name']) ?>&background=random" 
                                             class="member-avatar" alt="<?= $member['Member_name'] ?>">
                                    </td>
                                    <td><?= $member['Member_name'] ?></td>
                                    <td><?= $member['member_contact_info'] ?></td>
                                    <td>
                                        <?php 
                                        $sports = explode(',', $member['Sports_Preferences']);
                                        foreach ($sports as $sport): 
                                        ?>
                                            <span class="badge badge-sport"><?= trim($sport) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?= $member['Club_name'] ?></td>
                                    <td>
                                        <small class="text-muted">From:</small> <?= date('M d, Y', strtotime($member['Membership_start_date'])) ?><br>
                                        <small class="text-muted">To:</small> <?= date('M d, Y', strtotime($member['Membership_end_date'])) ?>
                                    </td>
                                    <td>
                                        <span class="membership-status <?= $statusClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary me-2 action-btn edit-member-btn" 
                                                data-id="<?= $member['Member_id'] ?>"
                                                data-name="<?= $member['Member_name'] ?>"
                                                data-contact="<?= $member['member_contact_info'] ?>"
                                                data-sports="<?= $member['Sports_Preferences'] ?>"
                                                data-club="<?= $member['Club_id'] ?>"
                                                data-start="<?= $member['Membership_start_date'] ?>"
                                                data-end="<?= $member['Membership_end_date'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger action-btn delete-member-btn" 
                                                data-id="<?= $member['Member_id'] ?>">
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
    </div>
    
    <!-- Add Member Modal -->
    <div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Add New Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addMemberForm">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Member Name</label>
                                <input type="text" name="member_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Information</label>
                                <input type="text" name="member_contact" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Sports Preferences (comma-separated)</label>
                                <input type="text" name="sports_preferences" class="form-control" placeholder="e.g. Football, Basketball, Tennis" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Club</label>
                                <select name="club_id" class="form-select" required>
                                    <option value="">-- Select Club --</option>
                                    <?php while ($club = $clubs->fetch_assoc()): ?>
                                        <option value="<?= $club['Club_id'] ?>"><?= $club['Club_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Membership Start Date</label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Membership End Date</label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Member Modal -->
    <div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i> Edit Member</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editMemberForm">
                    <input type="hidden" name="member_id" id="editMemberId">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Member Name</label>
                                <input type="text" name="member_name" id="editMemberName" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Information</label>
                                <input type="text" name="member_contact" id="editMemberContact" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Sports Preferences (comma-separated)</label>
                                <input type="text" name="sports_preferences" id="editMemberSports" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Club</label>
                                <select name="club_id" id="editMemberClub" class="form-select" required>
                                    <option value="">-- Select Club --</option>
                                    <?php $clubs->data_seek(0); // Reset pointer ?>
                                    <?php while ($club = $clubs->fetch_assoc()): ?>
                                        <option value="<?= $club['Club_id'] ?>"><?= $club['Club_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Membership Start Date</label>
                                <input type="date" name="start_date" id="editMemberStart" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Membership End Date</label>
                                <input type="date" name="end_date" id="editMemberEnd" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Member</button>
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
                    <p>Are you sure you want to delete this member? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Member</button>
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
            // Initialize toasts
            var successToast = new bootstrap.Toast(document.getElementById('successToast'));
            var errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
            
            // Add Member Form Submission
            $('#addMemberForm').submit(function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                
                $.ajax({
                    url: 'ajax_add_member.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#addMemberModal').modal('hide');
                        $('#addMemberForm')[0].reset();
                        
                        // Show success message
                        $('#successToastMessage').text('Member added successfully!');
                        successToast.show();
                        
                        // Reload members after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        $('#errorToastMessage').text('Error adding member: ' + xhr.responseText);
                        errorToast.show();
                    }
                });
            });
            
            // Edit Member Modal Setup
            $('.edit-member-btn').click(function() {
                var memberId = $(this).data('id');
                var memberName = $(this).data('name');
                var memberContact = $(this).data('contact');
                var memberSports = $(this).data('sports');
                var memberClub = $(this).data('club');
                var memberStart = $(this).data('start');
                var memberEnd = $(this).data('end');
                
                $('#editMemberId').val(memberId);
                $('#editMemberName').val(memberName);
                $('#editMemberContact').val(memberContact);
                $('#editMemberSports').val(memberSports);
                $('#editMemberClub').val(memberClub);
                $('#editMemberStart').val(memberStart);
                $('#editMemberEnd').val(memberEnd);
                
                $('#editMemberModal').modal('show');
            });
            
            // Edit Member Form Submission
            $('#editMemberForm').submit(function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                
                $.ajax({
                    url: 'ajax_update_member.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('#editMemberModal').modal('hide');
                        
                        // Show success message
                        $('#successToastMessage').text('Member updated successfully!');
                        successToast.show();
                        
                        // Reload members after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        $('#errorToastMessage').text('Error updating member: ' + xhr.responseText);
                        errorToast.show();
                    }
                });
            });
            
            // Delete Member Confirmation
            var memberToDelete = null;
            
            $('.delete-member-btn').click(function() {
                memberToDelete = $(this).data('id');
                $('#confirmModal').modal('show');
            });
            
            $('#confirmDeleteBtn').click(function() {
                $.ajax({
                    url: 'ajax_delete_member.php',
                    type: 'POST',
                    data: { member_id: memberToDelete },
                    success: function(response) {
                        $('#confirmModal').modal('hide');
                        
                        // Show success message
                        $('#successToastMessage').text('Member deleted successfully!');
                        successToast.show();
                        
                        // Reload members after a short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    },
                    error: function(xhr, status, error) {
                        $('#confirmModal').modal('hide');
                        $('#errorToastMessage').text('Error deleting member: ' + xhr.responseText);
                        errorToast.show();
                    }
                });
            });
        });
    </script>
</body>
</html>