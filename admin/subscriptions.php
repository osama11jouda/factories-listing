<?php include 'includes/header.php'; ?>
<?php require_once '../models/Membership.php'; ?>
<?php require_once '../models/User.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Subscription Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addMembershipModal">
            <i class="fas fa-plus"></i> Add New Plan
        </button>
    </div>
</div>

<?php
// Handle membership deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $membershipId = intval($_GET['delete']);
    
    $membership = new Membership();
    if ($membership->findById($membershipId)) {
        // Check if membership is in use
        $db = Database::getInstance();
        $result = $db->query("SELECT COUNT(*) as total FROM users WHERE membership_id = $membershipId");
        $row = $db->fetchArray($result);
        
        if ($row['total'] > 0) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Cannot delete membership plan because it is assigned to users.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else if ($membership->delete()) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Membership plan deleted successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error deleting membership plan.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        }
    }
}

// Handle form submission for adding/editing memberships
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $membershipId = isset($_POST['membership_id']) ? intval($_POST['membership_id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $duration = isset($_POST['duration_months']) ? intval($_POST['duration_months']) : 0;
    $features = isset($_POST['features']) ? trim($_POST['features']) : '';
    
    // Validate required fields
    if (empty($name) || $price < 0 || $duration <= 0) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Please fill all required fields properly. Name, valid price and duration are required.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        // Create or update membership
        $membership = new Membership();
        if ($membershipId > 0) {
            // Update existing membership
            if ($membership->findById($membershipId)) {
                $membership->setName($name);
                $membership->setDescription($description);
                $membership->setPrice($price);
                $membership->setDurationMonths($duration);
                $membership->setFeatures($features);
                
                if ($membership->update()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Membership plan updated successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error updating membership plan.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                }
            }
        } else {
            // Create new membership
            $membership->setName($name);
            $membership->setDescription($description);
            $membership->setPrice($price);
            $membership->setDurationMonths($duration);
            $membership->setFeatures($features);
            
            if ($membership->create()) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Membership plan added successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error adding membership plan.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            }
        }
    }
}

// Get all memberships
$memberships = Membership::getAll();

// Get active subscriptions
$db = Database::getInstance();
$result = $db->query("SELECT COUNT(*) as total FROM users WHERE subscription_expiry IS NOT NULL AND subscription_expiry > NOW()");
$activeSubscriptions = $db->fetchArray($result)['total'];

// Get expired subscriptions
$result = $db->query("SELECT COUNT(*) as total FROM users WHERE subscription_expiry IS NOT NULL AND subscription_expiry <= NOW()");
$expiredSubscriptions = $db->fetchArray($result)['total'];

// Get users with no subscription
$result = $db->query("SELECT COUNT(*) as total FROM users WHERE subscription_expiry IS NULL AND is_admin = 0");
$noSubscription = $db->fetchArray($result)['total'];

// Get users with subscriptions expiring in next 30 days
$result = $db->query("SELECT COUNT(*) as total FROM users WHERE subscription_expiry BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)");
$expiringSubscriptions = $db->fetchArray($result)['total'];

// Get recent subscription updates
$result = $db->query("SELECT u.id, u.name, u.email, u.subscription_expiry, m.name as plan_name 
                     FROM users u 
                     LEFT JOIN memberships m ON u.membership_id = m.id
                     WHERE u.subscription_expiry IS NOT NULL 
                     ORDER BY u.updated_at DESC 
                     LIMIT 5");

$recentSubscriptions = [];
while ($row = $db->fetchArray($result)) {
    $recentSubscriptions[] = $row;
}
?>

<!-- Overview Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Active Subscriptions</h6>
                        <h2 class="mb-0"><?php echo $activeSubscriptions; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-check-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Expiring Soon</h6>
                        <h2 class="mb-0"><?php echo $expiringSubscriptions; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-clock fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Expired</h6>
                        <h2 class="mb-0"><?php echo $expiredSubscriptions; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-times-circle fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Unsubscribed</h6>
                        <h2 class="mb-0"><?php echo $noSubscription; ?></h2>
                    </div>
                    <div>
                        <i class="fas fa-user-slash fa-3x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Memberships Table -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Membership Plans</h5>
            </div>
            <div class="card-body">
                <?php if (empty($memberships)): ?>
                    <div class="alert alert-info">
                        No membership plans found. Start by adding a new plan.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($memberships as $membership): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($membership->getName()); ?></td>
                                        <td><?php echo htmlspecialchars($membership->getDescription()); ?></td>
                                        <td>$<?php echo number_format($membership->getPrice(), 2); ?></td>
                                        <td><?php echo $membership->getDurationMonths(); ?> months</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-primary edit-membership" 
                                                        data-id="<?php echo $membership->getId(); ?>"
                                                        data-name="<?php echo htmlspecialchars($membership->getName()); ?>"
                                                        data-description="<?php echo htmlspecialchars($membership->getDescription()); ?>"
                                                        data-price="<?php echo $membership->getPrice(); ?>"
                                                        data-duration="<?php echo $membership->getDurationMonths(); ?>"
                                                        data-features="<?php echo htmlspecialchars($membership->getFeatures()); ?>"
                                                        data-toggle="modal" data-target="#editMembershipModal">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="javascript:void(0);" class="btn btn-danger delete-membership" 
                                                data-id="<?php echo $membership->getId(); ?>"
                                                data-name="<?php echo htmlspecialchars($membership->getName()); ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Recent Subscription Activity</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentSubscriptions)): ?>
                    <div class="p-3">
                        <p class="text-muted mb-0">No recent subscription activity.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentSubscriptions as $subscription): ?>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($subscription['name']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($subscription['email']); ?>
                                        </small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-<?php echo strtotime($subscription['subscription_expiry']) < time() ? 'danger' : 'success'; ?>">
                                            <?php echo date('M d, Y', strtotime($subscription['subscription_expiry'])); ?>
                                        </span>
                                        <small class="d-block text-muted"><?php echo htmlspecialchars($subscription['plan_name'] ?? 'No Plan'); ?></small>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="card-footer text-center">
                <a href="users.php" class="btn btn-sm btn-outline-primary">Manage User Subscriptions</a>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Subscription Tools</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#bulkRenewalModal">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-sync-alt text-primary mr-2"></i> Bulk Renewal
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#expiryNotificationModal">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-bell text-warning mr-2"></i> Send Expiry Notifications
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action" data-toggle="modal" data-target="#subscriptionReportModal">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-chart-line text-success mr-2"></i> Generate Reports
                            </div>
                            <i class="fas fa-chevron-right text-muted"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Membership Modal -->
<div class="modal fade" id="addMembershipModal" tabindex="-1" aria-labelledby="addMembershipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMembershipModalLabel">Add New Membership Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add-name">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-description">Description</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-price">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="add-price" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-duration">Duration (months) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="add-duration" name="duration_months" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="add-features">Features</label>
                        <textarea class="form-control" id="add-features" name="features" rows="3" placeholder="Enter features separated by commas"></textarea>
                        <small class="text-muted">Example: View listings, Create listings, Featured listings</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Membership Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Membership Modal -->
<div class="modal fade" id="editMembershipModal" tabindex="-1" aria-labelledby="editMembershipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" id="edit-membership-id" name="membership_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editMembershipModalLabel">Edit Membership Plan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-name">Plan Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-description">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-price">Price <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">$</span>
                            </div>
                            <input type="number" class="form-control" id="edit-price" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-duration">Duration (months) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="edit-duration" name="duration_months" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit-features">Features</label>
                        <textarea class="form-control" id="edit-features" name="features" rows="3" placeholder="Enter features separated by commas"></textarea>
                        <small class="text-muted">Example: View listings, Create listings, Featured listings</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Membership Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Renewal Modal -->
<div class="modal fade" id="bulkRenewalModal" tabindex="-1" aria-labelledby="bulkRenewalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkRenewalModalLabel">Bulk Subscription Renewal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="renewal-type">Renewal Type</label>
                        <select class="form-control" id="renewal-type">
                            <option value="expired">Expired Subscriptions</option>
                            <option value="expiring">Expiring in 30 Days</option>
                            <option value="all">All Active Subscriptions</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="extend-months">Extend By (Months)</label>
                        <input type="number" class="form-control" id="extend-months" min="1" value="12">
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        This will extend the subscription expiry date for selected users.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">Process Renewals</button>
            </div>
        </div>
    </div>
</div>

<!-- Expiry Notification Modal -->
<div class="modal fade" id="expiryNotificationModal" tabindex="-1" aria-labelledby="expiryNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expiryNotificationModalLabel">Send Expiry Notifications</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="notification-group">Send To</label>
                        <select class="form-control" id="notification-group">
                            <option value="expiring7">Expiring in 7 Days</option>
                            <option value="expiring30">Expiring in 30 Days</option>
                            <option value="expired">Recently Expired</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notification-template">Email Template</label>
                        <select class="form-control" id="notification-template">
                            <option value="renewal-reminder">Renewal Reminder</option>
                            <option value="expiry-notice">Expiry Notice</option>
                            <option value="expired-notice">Expired Notice</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Email notification functionality will be implemented in a future update.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" disabled>Send Notifications</button>
            </div>
        </div>
    </div>
</div>

<!-- Subscription Report Modal -->
<div class="modal fade" id="subscriptionReportModal" tabindex="-1" aria-labelledby="subscriptionReportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="subscriptionReportModalLabel">Generate Subscription Report</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label for="report-type">Report Type</label>
                        <select class="form-control" id="report-type">
                            <option value="subscription-summary">Subscription Summary</option>
                            <option value="expiring-subscriptions">Expiring Subscriptions</option>
                            <option value="renewal-history">Renewal History</option>
                            <option value="revenue">Revenue Report</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="report-start-date">Start Date</label>
                            <input type="date" class="form-control" id="report-start-date">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="report-end-date">End Date</label>
                            <input type="date" class="form-control" id="report-end-date">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="report-format">Format</label>
                        <select class="form-control" id="report-format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Report generation functionality will be implemented in a future update.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" disabled>Generate Report</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteMembershipModal" tabindex="-1" aria-labelledby="deleteMembershipModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMembershipModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the membership plan: <strong id="delete-membership-name"></strong>?</p>
                <p class="text-danger">This action cannot be undone. All users with this plan will need to be reassigned.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Delete Membership Plan</a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for modals -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Edit Membership Modal
        $('.edit-membership').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');
            var price = $(this).data('price');
            var duration = $(this).data('duration');
            var features = $(this).data('features');
            
            $('#edit-membership-id').val(id);
            $('#edit-name').val(name);
            $('#edit-description').val(description);
            $('#edit-price').val(price);
            $('#edit-duration').val(duration);
            $('#edit-features').val(features);
        });
        
        // Handle Delete Membership Modal
        $('.delete-membership').click(function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#delete-membership-name').text(name);
            $('#confirm-delete').attr('href', 'subscriptions.php?delete=' + id);
            
            $('#deleteMembershipModal').modal('show');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>