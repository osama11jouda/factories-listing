<?php 
include 'includes/header.php';
require_once '../models/ContactMessage.php';

// Check if message id is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: messages.php');
    exit;
}

$messageId = intval($_GET['id']);
$message = new ContactMessage();

// Fetch the message details
if (!$message->findById($messageId)) {
    header('Location: messages.php');
    exit;
}

// Mark as read if not already read
if (!$message->isRead()) {
    $message->markAsRead();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">View Message</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="messages.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Messages
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Message: <?php echo htmlspecialchars($message->getSubject()); ?></h5>
        <span class="badge badge-<?php echo $message->isRead() ? 'secondary' : 'warning'; ?>">
            <?php echo $message->isRead() ? 'Read' : 'Unread'; ?>
        </span>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <strong>From:</strong> <?php echo htmlspecialchars($message->getName()); ?>
            </div>
            <div class="col-md-6 text-md-right">
                <strong>Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($message->getSubmissionDate())); ?>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-12">
                <strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($message->getEmail()); ?>"><?php echo htmlspecialchars($message->getEmail()); ?></a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <strong>Message</strong>
            </div>
            <div class="card-body bg-light">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($message->getMessage())); ?></p>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-md-6">
                <a href="mailto:<?php echo htmlspecialchars($message->getEmail()); ?>" class="btn btn-primary">
                    <i class="fas fa-reply"></i> Reply via Email
                </a>
            </div>
            <div class="col-md-6 text-md-right">
                <a href="messages.php?delete=<?php echo $message->getId(); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?')">
                    <i class="fas fa-trash-alt"></i> Delete Message
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>