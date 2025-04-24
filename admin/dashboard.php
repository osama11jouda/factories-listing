<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="add_factory.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus"></i> Add Factory
            </a>
        </div>
    </div>
</div>

<?php
// Get summary statistics
$stats = [
    'factories' => 0,
    'users' => 0,
    'active_users' => 0,
    'messages' => 0,
];

// Count factories
$query = "SELECT COUNT(*) as count FROM factories";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['factories'] = $row['count'];
}

// Count users
$query = "SELECT COUNT(*) as count FROM users WHERE is_admin = 0";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['users'] = $row['count'];
}

// Count active users
$query = "SELECT COUNT(*) as count FROM users WHERE is_admin = 0 AND is_active = 1";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['active_users'] = $row['count'];
}

// Count messages
$query = "SELECT COUNT(*) as count FROM contact_messages";
$result = mysqli_query($conn, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['messages'] = $row['count'];
}

// Get recent users
$recentUsers = [];
$query = "SELECT * FROM users WHERE is_admin = 0 ORDER BY registration_date DESC LIMIT 5";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentUsers[] = $row;
    }
}

// Get recent messages
$recentMessages = [];
$query = "SELECT * FROM contact_messages ORDER BY submission_date DESC LIMIT 5";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recentMessages[] = $row;
    }
}
?>

<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Factories</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['factories']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-industry fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Registered Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['users']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Active Subscriptions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['active_users']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Messages</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['messages']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-envelope fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold">Recent Registrations</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentUsers) > 0): ?>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['name']; ?></td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['registration_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent registrations</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="users.php" class="btn btn-sm btn-outline-secondary">View all users</a>
            </div>
        </div>
    </div>
    
    <!-- Recent Messages -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="m-0 font-weight-bold">Recent Messages</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Subject</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentMessages) > 0): ?>
                                <?php foreach ($recentMessages as $message): ?>
                                    <tr>
                                        <td><?php echo $message['name']; ?></td>
                                        <td><?php echo $message['email']; ?></td>
                                        <td><?php echo !empty($message['subject']) ? $message['subject'] : 'No Subject'; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($message['submission_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">No recent messages</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <a href="messages.php" class="btn btn-sm btn-outline-secondary">View all messages</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>