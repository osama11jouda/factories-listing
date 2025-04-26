<?php 
include 'includes/header.php'; 
require_once '../models/Factory.php';
require_once '../models/User.php';
require_once '../models/ContactMessage.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
</div>

<?php
// Get statistics for dashboard
$totalUsers = User::countAll();
$totalFactories = Factory::countAll();

// Get unread messages count
$unreadMessages = ContactMessage::countAll(true);

// Get recent factories
$recentFactories = Factory::getAll(5);

// Get recent users
$recentUsers = User::getAll(5);

// Get recent messages
$recentMessages = ContactMessage::getAll(5);
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Users</h5>
                        <h2 class="display-4"><?php echo $totalUsers; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-users fa-3x opacity-50"></i>
                    </div>
                </div>
                <a href="users.php" class="btn btn-light btn-sm mt-3">View Details</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Factories</h5>
                        <h2 class="display-4"><?php echo $totalFactories; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-industry fa-3x opacity-50"></i>
                    </div>
                </div>
                <a href="factories.php" class="btn btn-light btn-sm mt-3">View Details</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Messages</h5>
                        <h2 class="display-4"><?php echo $unreadMessages; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-envelope fa-3x opacity-50"></i>
                    </div>
                </div>
                <a href="messages.php" class="btn btn-light btn-sm mt-3">View Messages</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Factories</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentFactories) > 0): ?>
                                <?php foreach ($recentFactories as $factory): ?>
                                    <tr>
                                        <td>
                                            <a href="edit_factory.php?id=<?php echo $factory->getId(); ?>">
                                                <?php echo htmlspecialchars($factory->getTitle()); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if ($factory->getType() == 'sale'): ?>
                                                <span class="badge badge-primary">For Sale</span>
                                            <?php else: ?>
                                                <span class="badge badge-info">For Rent</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>$<?php echo number_format($factory->getPrice(), 2); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($factory->getDateAdded())); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No factories found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="factories.php" class="btn btn-sm btn-primary">View All Factories</a>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Users</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentUsers) > 0): ?>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user->getName()); ?></td>
                                        <td><?php echo htmlspecialchars($user->getEmail()); ?></td>
                                        <td>
                                            <?php if ($user->isActive()): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user->getRegistrationDate())); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="users.php" class="btn btn-sm btn-primary">View All Users</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Messages</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentMessages) > 0): ?>
                                <?php foreach ($recentMessages as $message): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($message->getName()); ?></td>
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
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No messages found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-center">
                <a href="messages.php" class="btn btn-sm btn-primary">View All Messages</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>