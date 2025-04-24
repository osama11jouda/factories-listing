<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">User Management</h1>
</div>

<?php
// Handle user activation/deactivation
if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $userId = intval($_GET['activate']);
    $value = isset($_GET['value']) && $_GET['value'] == '1' ? 1 : 0;
    
    $query = "UPDATE users SET is_active = $value WHERE id = $userId AND is_admin = 0";
    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                User status updated successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error updating user: ' . mysqli_error($conn) . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
}

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    
    $query = "DELETE FROM users WHERE id = $userId AND is_admin = 0";
    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                User deleted successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error deleting user: ' . mysqli_error($conn) . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
}

// Update subscription expiry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_subscription'])) {
    $userId = intval($_POST['user_id']);
    $expiryDate = mysqli_real_escape_string($conn, $_POST['subscription_expiry']);
    
    $query = "UPDATE users SET subscription_expiry = '$expiryDate', is_active = 1 WHERE id = $userId";
    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Subscription updated successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error updating subscription: ' . mysqli_error($conn) . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
}

// Pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Search parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build the search condition
$searchCondition = "WHERE is_admin = 0";
if (!empty($search)) {
    $searchCondition .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR company LIKE '%$search%')";
}
if ($statusFilter !== '') {
    $searchCondition .= " AND is_active = " . intval($statusFilter);
}

// Get total number of users with search condition
$countQuery = "SELECT COUNT(*) as total FROM users $searchCondition";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get users with search condition and pagination
$query = "SELECT * FROM users $searchCondition ORDER BY registration_date DESC LIMIT $offset, $itemsPerPage";
$result = mysqli_query($conn, $query);
?>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row">
            <div class="col-md-6 mb-2">
                <input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 mb-2">
                <select name="status" class="form-control">
                    <option value="">All Users</option>
                    <option value="1" <?php echo $statusFilter === '1' ? 'selected' : ''; ?>>Active</option>
                    <option value="0" <?php echo $statusFilter === '0' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
            <div class="col-md-2 mb-2">
                <a href="users.php" class="btn btn-outline-secondary btn-block">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Phone</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                        <th>Subscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0): 
                        while ($user = mysqli_fetch_assoc($result)):
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?: 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></td>
                            <td>
                                <?php if ($user['is_active']): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($user['subscription_expiry'])): ?>
                                    <?php 
                                    $expiryDate = strtotime($user['subscription_expiry']);
                                    $today = time();
                                    $status = ($expiryDate < $today) ? 'danger' : 'success';
                                    ?>
                                    <span class="badge badge-<?php echo $status; ?>">
                                        Expires: <?php echo date('M d, Y', $expiryDate); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-warning">No Subscription</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#subscriptionModal<?php echo $user['id']; ?>">
                                        <i class="fas fa-credit-card"></i>
                                    </button>
                                    <?php if ($user['is_active']): ?>
                                        <a href="users.php?activate=<?php echo $user['id']; ?>&value=0" class="btn btn-warning" title="Deactivate">
                                            <i class="fas fa-user-slash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="users.php?activate=<?php echo $user['id']; ?>&value=1" class="btn btn-success" title="Activate">
                                            <i class="fas fa-user-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                                
                                <!-- Subscription Modal -->
                                <div class="modal fade" id="subscriptionModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="subscriptionModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="subscriptionModalLabel">Update Subscription</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                                                <div class="modal-body">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <div class="form-group">
                                                        <label for="subscription_expiry">Subscription Expiry Date</label>
                                                        <input type="date" class="form-control" id="subscription_expiry" name="subscription_expiry" value="<?php echo !empty($user['subscription_expiry']) ? $user['subscription_expiry'] : date('Y-m-d', strtotime('+1 month')); ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                    <button type="submit" name="update_subscription" class="btn btn-primary">Update Subscription</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr>
                            <td colspan="8" class="text-center">No users found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>