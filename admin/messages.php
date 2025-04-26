<?php include 'includes/header.php'; ?>
<?php require_once '../models/ContactMessage.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Messages</h1>
</div>

<?php
// Handle message deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $messageId = intval($_GET['delete']);
    
    $message = new ContactMessage();
    if ($message->findById($messageId)) {
        if ($message->delete()) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Message deleted successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error deleting message.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        }
    }
}

// Handle mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $messageId = intval($_GET['read']);
    
    $message = new ContactMessage();
    if ($message->findById($messageId)) {
        if ($message->markAsRead()) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Message marked as read.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error updating message status.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        }
    }
}

// Pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Filter parameters
$filterRead = isset($_GET['filter']) ? $_GET['filter'] : '';

// Get messages with filter
$onlyUnread = ($filterRead === 'unread');
$messages = ContactMessage::getAll($itemsPerPage, $offset, $onlyUnread);
$totalItems = ContactMessage::countAll($onlyUnread);
$totalPages = ceil($totalItems / $itemsPerPage);
?>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row">
            <div class="col-md-3 mb-2">
                <select name="filter" class="form-control">
                    <option value="">All Messages</option>
                    <option value="unread" <?php echo $filterRead === 'unread' ? 'selected' : ''; ?>>Unread Only</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">Filter</button>
            </div>
            <div class="col-md-2 mb-2">
                <a href="messages.php" class="btn btn-outline-secondary btn-block">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Messages Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($messages) > 0): 
                        foreach ($messages as $message):
                    ?>
                        <tr class="<?php echo $message->isRead() ? '' : 'font-weight-bold'; ?>">
                            <td><?php echo htmlspecialchars($message->getName()); ?></td>
                            <td><?php echo htmlspecialchars($message->getEmail()); ?></td>
                            <td>
                                <a href="view_message.php?id=<?php echo $message->getId(); ?>">
                                    <?php echo htmlspecialchars($message->getSubject()); ?>
                                </a>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($message->getSubmissionDate())); ?></td>
                            <td>
                                <?php if ($message->isRead()): ?>
                                    <span class="badge badge-secondary">Read</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Unread</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="view_message.php?id=<?php echo $message->getId(); ?>" class="btn btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (!$message->isRead()): ?>
                                        <a href="messages.php?read=<?php echo $message->getId(); ?>" class="btn btn-success" title="Mark as Read">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="messages.php?delete=<?php echo $message->getId(); ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this message?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endforeach;
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo urlencode($filterRead); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo urlencode($filterRead); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo urlencode($filterRead); ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>