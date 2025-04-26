<?php 
include 'includes/header.php';
require_once '../models/Factory.php';
require_once '../models/FactoryImage.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Factory Listings</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="add_factory.php" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-plus"></i> Add New Factory
            </a>
        </div>
    </div>
</div>

<?php
// Delete factory if requested
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $factoryId = intval($_GET['delete']);
    
    $factory = new Factory();
    if ($factory->findById($factoryId)) {
        if ($factory->delete()) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Factory successfully deleted.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error deleting factory.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        }
    }
}

// Feature/unfeature factory
if(isset($_GET['feature']) && is_numeric($_GET['feature'])) {
    $factoryId = intval($_GET['feature']);
    $featureValue = isset($_GET['value']) && $_GET['value'] == '1' ? true : false;
    
    $factory = new Factory();
    if ($factory->findById($factoryId)) {
        $factory->setFeatured($featureValue);
        $factory->update();
    }
}

// Pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build filters for the Factory model
$filters = [];

if (!empty($typeFilter)) {
    $filters['type'] = $typeFilter;
}

if (!empty($statusFilter)) {
    $filters['status'] = $statusFilter;
}

// Get total factories count with filters
$totalItems = Factory::countAll($filters);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get factories using the model
$factories = Factory::getAll($itemsPerPage, $offset, $filters);
?>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row">
            <div class="col-md-4 mb-2">
                <input type="text" class="form-control" name="search" placeholder="Search factories..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 mb-2">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="sale" <?php echo $typeFilter == 'sale' ? 'selected' : ''; ?>>For Sale</option>
                    <option value="rent" <?php echo $typeFilter == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="available" <?php echo $statusFilter == 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="pending" <?php echo $statusFilter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="sold" <?php echo $statusFilter == 'sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
            <div class="col-md-2 mb-2">
                <a href="factories.php" class="btn btn-outline-secondary btn-block">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Factories Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($factories)): ?>
                        <?php foreach ($factories as $factory): ?>
                            <?php 
                                $mainImage = $factory->getMainImage();
                                $address = $factory->getAddress();
                                $location = $address ? $address->getCity() . ', ' . $address->getCountry() : 'N/A';
                            ?>
                            <tr>
                                <td width="80">
                                    <?php if ($mainImage): ?>
                                        <img src="<?php echo $mainImage->getImagePath(); ?>" class="img-thumbnail" alt="Factory Image" width="70">
                                    <?php else: ?>
                                        <img src="../images/factory-placeholder.jpg" class="img-thumbnail" alt="Factory Placeholder" width="70">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $factory->getTitle(); ?></td>
                                <td><?php echo $location; ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $factory->getType() == 'sale' ? 'success' : 'info'; ?>">
                                        <?php echo ucfirst($factory->getType()); ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($factory->getPrice()); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $factory->getStatus() == 'available' ? 'success' : 
                                            ($factory->getStatus() == 'pending' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst($factory->getStatus()); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($factory->isFeatured()): ?>
                                        <a href="factories.php?feature=<?php echo $factory->getId(); ?>&value=0" class="text-warning" title="Click to unfeature">
                                            <i class="fas fa-star"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="factories.php?feature=<?php echo $factory->getId(); ?>&value=1" class="text-secondary" title="Click to feature">
                                            <i class="far fa-star"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="../factory-details.php?id=<?php echo $factory->getId(); ?>" class="btn btn-info" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_factory.php?id=<?php echo $factory->getId(); ?>" class="btn btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="factories.php?delete=<?php echo $factory->getId(); ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this factory?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No factories found</td>
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
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&status=<?php echo urlencode($statusFilter); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>