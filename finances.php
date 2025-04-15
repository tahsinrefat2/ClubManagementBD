<?php
include 'db_connect.php';

// Handle new finance record insertion via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'add_transaction') {
    header('Content-Type: application/json');
    
    try {
        $club_id = intval($_POST['club_id']);
        $type = $_POST['type'];
        $amount = floatval($_POST['amount']);
        $date = $_POST['transaction_date'];

        $fee = $donation = $expense = 0;
        if ($type == 'Membership_fees') $fee = $amount;
        elseif ($type == 'Donations') $donation = $amount;
        elseif ($type == 'Expenses') $expense = $amount;

        $stmt = $conn->prepare("INSERT INTO Finance (Club_id, Membership_fees, Donations, Expenses, Transaction_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iddds", $club_id, $fee, $donation, $expense, $date);
        $stmt->execute();
        
        echo json_encode(['success' => true, 'message' => 'Transaction added successfully']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle delete transaction via AJAX
if (isset($_POST['action']) && $_POST['action'] == 'delete_transaction') {
    header('Content-Type: application/json');
    
    try {
        $finance_id = intval($_POST['finance_id']);
        
        $stmt = $conn->prepare("DELETE FROM Finance WHERE Finance_id = ?");
        $stmt->bind_param("i", $finance_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Transaction deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Transaction not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Get filter values from GET parameters
$filter_club = isset($_GET['filter_club']) ? intval($_GET['filter_club']) : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Handle filters
$where = "1";
if (!empty($filter_club)) {
    $where .= " AND f.Club_id = " . $filter_club;
}
if (!empty($start_date)) {
    $where .= " AND f.Transaction_date >= '" . $conn->real_escape_string($start_date) . "'";
}
if (!empty($end_date)) {
    $where .= " AND f.Transaction_date <= '" . $conn->real_escape_string($end_date) . "'";
}

// Download filtered CSV
if (isset($_GET['download']) && $_GET['download'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename=finance_report.csv');
    $output = fopen("php://output", "w");
    fputcsv($output, ['Finance ID', 'Club ID', 'Club Name', 'Type', 'Amount', 'Transaction Date']);

    $csv_query = $conn->query("
        SELECT f.*, c.Club_name 
        FROM Finance f
        LEFT JOIN Club c ON f.Club_id = c.Club_id
        WHERE $where 
        ORDER BY f.Transaction_date DESC
    ");
    
    while ($row = $csv_query->fetch_assoc()) {
        $type = '';
        $amount = 0;
        if ($row['Membership_fees'] > 0) {
            $type = 'Membership Fee';
            $amount = $row['Membership_fees'];
        } elseif ($row['Donations'] > 0) {
            $type = 'Donation';
            $amount = $row['Donations'];
        } elseif ($row['Expenses'] > 0) {
            $type = 'Expense';
            $amount = $row['Expenses'];
        }
        fputcsv($output, [
            $row['Finance_id'], 
            $row['Club_id'], 
            $row['Club_name'],
            $type, 
            $amount, 
            $row['Transaction_date']
        ]);
    }
    fclose($output);
    exit;
}

// Fetch clubs
$clubs = $conn->query("SELECT Club_id, Club_name FROM Club ORDER BY Club_name");

// Fetch records for table
$finances = $conn->query("
    SELECT f.*, c.Club_name 
    FROM Finance f
    LEFT JOIN Club c ON f.Club_id = c.Club_id
    WHERE $where 
    ORDER BY f.Transaction_date DESC
");

// Calculate totals
$totals_result = $conn->query("
    SELECT 
        SUM(Membership_fees) as total_fees,
        SUM(Donations) as total_donations,
        SUM(Expenses) as total_expenses
    FROM Finance f
    WHERE $where
");
$totals = $totals_result ? $totals_result->fetch_assoc() : ['total_fees' => 0, 'total_donations' => 0, 'total_expenses' => 0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management</title>
    <?php include 'navbar.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
            --light-bg: #f8f9fa;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .stat-card {
            border-left: 4px solid;
            border-radius: 8px;
        }
        
        .stat-card.fees {
            border-color: #4cc9f0;
        }
        
        .stat-card.donations {
            border-color: #4895ef;
        }
        
        .stat-card.expenses {
            border-color: #f72585;
        }
        
        .stat-card.balance {
            border-color: #3a0ca3;
        }
        
        .transaction-type {
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .transaction-type.fee {
            background-color: rgba(76, 201, 240, 0.2);
            color: #4cc9f0;
        }
        
        .transaction-type.donation {
            background-color: rgba(72, 149, 239, 0.2);
            color: #4895ef;
        }
        
        .transaction-type.expense {
            background-color: rgba(247, 37, 133, 0.2);
            color: #f72585;
        }
        
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .action-btn {
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background-color: rgba(0, 0, 0, 0.1);
        }
        
        .action-btn.delete {
            color: var(--danger-color);
        }
        
        .modal-confirm {
            color: #636363;
            width: 400px;
        }
        
        .modal-confirm .modal-content {
            padding: 20px;
            border-radius: 5px;
            border: none;
        }
        
        .modal-confirm .modal-header {
            border-bottom: none;
            position: relative;
        }
        
        .modal-confirm .modal-body {
            padding: 20px 30px;
        }
        
        .modal-confirm .modal-footer {
            border-top: none;
            padding: 15px 30px 20px;
            justify-content: center;
        }
        
        .modal-confirm .icon-box {
            color: #fff;
            position: absolute;
            margin: 0 auto;
            left: 0;
            right: 0;
            top: -70px;
            width: 95px;
            height: 95px;
            border-radius: 50%;
            z-index: 9;
            background: #f72585;
            padding: 15px;
            text-align: center;
            box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.1);
        }
        
        .modal-confirm .icon-box i {
            font-size: 56px;
            position: relative;
            top: 4px;
        }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-0"><i class="bi bi-cash-stack me-2"></i>Finance Management</h2>
            
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card fees">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Membership Fees</h6>
                            <h4 class="mb-0"><?= number_format($totals['total_fees'] ?? 0, 2) ?> BDT</h4>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people-fill text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card donations">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Donations</h6>
                            <h4 class="mb-0"><?= number_format($totals['total_donations'] ?? 0, 2) ?> BDT</h4>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-gift-fill text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card expenses">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Expenses</h6>
                            <h4 class="mb-0"><?= number_format($totals['total_expenses'] ?? 0, 2) ?> BDT</h4>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-exchange text-danger fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card balance">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Net Balance</h6>
                            <h4 class="mb-0"><?= number_format(($totals['total_fees'] + $totals['total_donations'] - $totals['total_expenses']) ?? 0, 2) ?> BDT</h4>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-wallet2 text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-plus-circle me-2"></i>Add Transaction</h5>
                </div>
                <div class="card-body">
                    <form id="transactionForm">
                        <div class="mb-3">
                            <label for="club_id" class="form-label">Club</label>
                            <select id="club_id" name="club_id" class="form-select" required>
                                <option value="">-- Select Club --</option>
                                <?php 
                                while ($club = $clubs->fetch_assoc()): ?>
                                    <option value="<?= $club['Club_id'] ?>"><?= htmlspecialchars($club['Club_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Transaction Type</label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="Membership_fees">Membership Fee</option>
                                <option value="Donations">Donation</option>
                                <option value="Expenses">Expense</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount (BDT)</label>
                            <input type="number" step="0.01" id="amount" name="amount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="transaction_date" class="form-label">Transaction Date</label>
                            <input type="date" id="transaction_date" name="transaction_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Add Transaction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bi bi-list-check me-2"></i>Transaction Records</h5>
                    <div>
                        <button type="button" id="downloadReport" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-download me-1"></i> Download Report
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="filterForm" class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="filter_club" class="form-label">Club</label>
                            <select id="filter_club" name="filter_club" class="form-select">
                                <option value="">All Clubs</option>
                                <?php
                                // Reset the clubs result pointer
                                $clubs->data_seek(0);
                                while ($club = $clubs->fetch_assoc()):
                                    // Check if this club is selected in the filter
                                    $selected = ($filter_club == $club['Club_id']) ? 'selected' : '';
                                ?>
                                    <option value="<?= $club['Club_id'] ?>" <?= $selected ?>><?= htmlspecialchars($club['Club_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="start_date" class="form-label">From Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date" class="form-label">To Date</label>
                            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="col-12">
                            <button type="button" id="applyFilters" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i> Apply Filters
                            </button>
                            <button type="button" id="resetFilters" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
                            </button>
                        </div>
                    </form>
                    
                    <div class="table-responsive">
                        <table id="transactionsTable" class="table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Club</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($finances && $finances->num_rows > 0) {
                                    while ($row = $finances->fetch_assoc()): 
                                        $type = '';
                                        $type_class = '';
                                        $amount = 0;
                                        if ($row['Membership_fees'] > 0) {
                                            $type = 'Membership Fee';
                                            $type_class = 'fee';
                                            $amount = $row['Membership_fees'];
                                        } elseif ($row['Donations'] > 0) {
                                            $type = 'Donation';
                                            $type_class = 'donation';
                                            $amount = $row['Donations'];
                                        } elseif ($row['Expenses'] > 0) {
                                            $type = 'Expense';
                                            $type_class = 'expense';
                                            $amount = $row['Expenses'];
                                        }
                                ?>
                                    <tr>
                                        <td><?= $row['Finance_id'] ?></td>
                                        <td><?= htmlspecialchars($row['Club_name']) ?></td>
                                        <td><span class="transaction-type <?= $type_class ?>"><?= $type ?></span></td>
                                        <td><?= number_format($amount, 2) ?> BDT</td>
                                        <td><?= date('M d, Y', strtotime($row['Transaction_date'])) ?></td>
                                        <td>
                                            <span class="action-btn delete" data-id="<?= $row['Finance_id'] ?>" title="Delete Transaction">
                                                <i class="bi bi-trash"></i>
                                            </span>
                                        </td>
                                    </tr>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No transactions found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-confirm">
        <div class="modal-content">
            <div class="modal-header flex-column">
                <div class="icon-box">
                    <i class="bi bi-x-lg"></i>
                </div>
                <h4 class="modal-title w-100 mt-4" id="deleteModalLabel">Are you sure?</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Do you really want to delete this transaction? This process cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmDelete" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast-container"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#transactionsTable').DataTable({
        responsive: true,
        order: [[4, 'desc']]
    });
    
    // Handle form submission via AJAX
    $('#transactionForm').submit(function(e) {
        e.preventDefault();
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                action: 'add_transaction',
                club_id: $('#club_id').val(),
                type: $('#type').val(),
                amount: $('#amount').val(),
                transaction_date: $('#transaction_date').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showToast('Success', response.message, 'success');
                    $('#transactionForm')[0].reset();
                    // Reload the page after 1.5 seconds to show the new transaction
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast('Error', response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                showToast('Error', 'An error occurred while adding the transaction', 'danger');
            }
        });
    });
    
    // Apply filters when the button is clicked
    $('#applyFilters').click(function(e) {
        applyFilters();
    });
    
    // Allow enter key to trigger filter
    $('#filterForm').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            applyFilters();
        }
    });
    
    // Function to apply filters
    function applyFilters() {
        const filterClub = $('#filter_club').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        let queryParams = [];
        
        if (filterClub) {
            queryParams.push('filter_club=' + encodeURIComponent(filterClub));
        }
        
        if (startDate) {
            queryParams.push('start_date=' + encodeURIComponent(startDate));
        }
        
        if (endDate) {
            queryParams.push('end_date=' + encodeURIComponent(endDate));
        }
        
        // Build the URL with query parameters
        let url = window.location.pathname;
        if (queryParams.length > 0) {
            url += '?' + queryParams.join('&');
        }
        
        // Navigate to the filtered URL
        window.location.href = url;
    }
    
    // Reset filters
    $('#resetFilters').click(function() {
        // Clear form inputs
        $('#filter_club').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        
        // Redirect to the base URL without query parameters
        window.location.href = window.location.pathname;
    });
    
    // Download Report
    $('#downloadReport').click(function() {
        // Get current filter values
        const filterClub = $('#filter_club').val();
        const startDate = $('#start_date').val();
        const endDate = $('#end_date').val();
        
        let queryParams = ['download=csv'];
        
        if (filterClub) {
            queryParams.push('filter_club=' + encodeURIComponent(filterClub));
        }
        
        if (startDate) {
            queryParams.push('start_date=' + encodeURIComponent(startDate));
        }
        
        if (endDate) {
            queryParams.push('end_date=' + encodeURIComponent(endDate));
        }
        
        // Build the URL with query parameters
        let url = window.location.pathname + '?' + queryParams.join('&');
        
        // Navigate to download URL
        window.location.href = url;
    });
    
    // Delete transaction functionality
    let financeIdToDelete = null;
    
    // Show delete confirmation modal
    $(document).on('click', '.action-btn.delete', function() {
        financeIdToDelete = $(this).data('id');
        $('#deleteModal').modal('show');
    });
    
    // Handle delete confirmation
    $('#confirmDelete').click(function() {
        if (financeIdToDelete) {
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'delete_transaction',
                    finance_id: financeIdToDelete
                },
                dataType: 'json',
                success: function(response) {
                    $('#deleteModal').modal('hide');
                    
                    if (response.success) {
                        showToast('Success', response.message, 'success');
                        // Reload the page after 1.5 seconds to update the table
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showToast('Error', response.message, 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    $('#deleteModal').modal('hide');
                    showToast('Error', 'An error occurred while deleting the transaction', 'danger');
                }
            });
        }
    });
    
    // Show toast notification
    function showToast(title, message, type) {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <strong>${title}</strong><br>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `);
        
        $('#toast-container').append(toast);
        const bsToast = new bootstrap.Toast(toast[0], {
            delay: 3000
        });
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.on('hidden.bs.toast', function() {
            toast.remove();
        });
    }
    
    // Set end date to today if not set
    if (!$('#end_date').val()) {
        $('#end_date').val(new Date().toISOString().split('T')[0]);
    }
});
</script>
</body>
</html>