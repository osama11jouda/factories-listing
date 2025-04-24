<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contact Messages</h1>
</div>

<?php
// Handle message marking as read/unread
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $messageId = intval($_GET['read']);
    $value = isset($_GET['value']) && $_GET['value'] == '1' ? 1 : 0;
    
    $query = "UPDATE contact_messages SET is_read = $value WHERE id = $messageId";
    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Message status updated successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error updating message status: ' . mysqli_error($conn) . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
}

// Handle message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $messageId = intval($_GET['delete']);
    
    $query = "DELETE FROM contact_messages WHERE id = $messageId";
    if (mysqli_query($conn, $query)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Message deleted successfully.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error deleting message: ' . mysqli_error($conn) . '
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
$readFilter = isset($_GET['read_status']) ? mysqli_real_escape_string($conn, $_GET['read_status']) : '';

// Build the search condition
$searchCondition = "WHERE 1=1";
if (!empty($search)) {
    $searchCondition .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR subject LIKE '%$search%' OR message LIKE '%$search%')";
}
if ($readFilter !== '') {
    $searchCondition .= " AND is_read = " . intval($readFilter);
}

// Get total number of messages with search condition
$countQuery = "SELECT COUNT(*) as total FROM contact_messages $searchCondition";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get messages with search condition and pagination
$query = "SELECT * FROM contact_messages $searchCondition ORDER BY submission_date DESC LIMIT $offset, $itemsPerPage";
$result = mysqli_query($conn, $query);

// Count unread messages
$unreadQuery = "SELECT COUNT(*) as unread FROM contact_messages WHERE is_read = 0";
$unreadResult = mysqli_query($conn, $unreadQuery);
$unreadCount = mysqli_fetch_assoc($unreadResult)['unread'];
?>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center mb-3">
            <div class="col-md-6">
                <h5 class="mb-0">
                    Total Messages: <?php echo $totalItems; ?> 
                    <?php if ($unreadCount > 0): ?>
                        <span class="badge badge-danger"><?php echo $unreadCount; ?> Unread</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="col-md-6 text-right">
                <a href="messages.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </a>
            </div>
        </div>
        
        <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row">
            <div class="col-md-8 mb-2">
                <input type="text" class="form-control" name="search" placeholder="Search messages..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 mb-2">
                <select name="read_status" class="form-control">
                    <option value="">All Messages</option>
                    <option value="0" <?php echo $readFilter === '0' ? 'selected' : ''; ?>>Unread</option>
                    <option value="1" <?php echo $readFilter === '1' ? 'selected' : ''; ?>>Read</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Messages Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (mysqli_num_rows($result) > 0): 
                        while ($message = mysqli_fetch_assoc($result)):
                            $rowClass = $message['is_read'] ? '' : 'table-active font-weight-bold';
                    ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td>
                                <?php if ($message['is_read']): ?>
                                    <i class="fas fa-envelope-open text-secondary"></i>
                                <?php else: ?>
                                    <i class="fas fa-envelope text-primary"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($message['submission_date'])); ?></td>
                            <td><?php echo htmlspecialchars($message['name']); ?></td>
                            <td><a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a></td>
                            <td><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#viewModal<?php echo $message['id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if ($message['is_read']): ?>
                                        <a href="messages.php?read=<?php echo $message['id']; ?>&value=0" class="btn btn-secondary" title="Mark as Unread">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="messages.php?read=<?php echo $message['id']; ?>&value=1" class="btn btn-success" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary" title="Reply">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                    <a href="messages.php?delete=<?php echo $message['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                                
                                <!-- View Message Modal -->
                                <div class="modal fade" id="viewModal<?php echo $message['id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="viewModalLabel">View Message</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <h6 class="card-subtitle mb-2 text-muted">From: <?php echo htmlspecialchars($message['name']); ?> (<?php echo htmlspecialchars($message['email']); ?>)</h6>
                                                        <h6 class="card-subtitle mb-2 text-muted">Date: <?php echo date('F d, Y H:i', strtotime($message['submission_date'])); ?></h6>
                                                        <h5 class="card-title"><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></h5>
                                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                                    </div>
                                                </div>
                                                
                                                <?php
                                                // Mark as read when viewed
                                                if (!$message['is_read']) {
                                                    $readQuery = "UPDATE contact_messages SET is_read = 1 WHERE id = " . $message['id'];
                                                    mysqli_query($conn, $readQuery);
                                                }
                                                ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-primary">
                                                    <i class="fas fa-reply"></i> Reply by Email
                                                </a>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
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
                            <td colspan="6" class="text-center">No messages found</td>
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&read_status=<?php echo urlencode($readFilter); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&read_status=<?php echo urlencode($readFilter); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&read_status=<?php echo urlencode($readFilter); ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>