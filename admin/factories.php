<?php include 'includes/header.php'; ?>
<?php require_once '../models/Factory.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Factories Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="add_factory.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-plus"></i> Add New Factory
            </a>
        </div>
    </div>
</div>

<?php
// Handle factory deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $factoryId = intval($_GET['delete']);
    
    $factory = new Factory();
    if ($factory->findById($factoryId)) {
        // Get the address to delete it after factory deletion
        $addressId = $factory->getAddressId();
        
        if ($factory->delete()) {
            // If factory was deleted successfully, delete the address
            if ($addressId) {
                $address = new Address();
                if ($address->findById($addressId)) {
                    $address->delete();
                }
            }
            
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Factory deleted successfully.
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

// Handle featured status toggle
if (isset($_GET['featured']) && is_numeric($_GET['featured'])) {
    $factoryId = intval($_GET['featured']);
    $featured = isset($_GET['value']) ? intval($_GET['value']) : 0;
    
    $factory = new Factory();
    if ($factory->findById($factoryId)) {
        $factory->setFeatured($featured);
        
        if ($factory->update()) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Factory featured status updated successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error updating factory featured status.
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

// Search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build the filters array for Factory::getAll() and Factory::countAll()
$filters = [];

if (!empty($typeFilter)) {
    $filters['type'] = $typeFilter;
}

if (!empty($statusFilter)) {
    $filters['status'] = $statusFilter;
}

// For text search, we need to use direct DB query since our model doesn't support this yet
$whereClause = '';
if (!empty($search)) {
    $db = Database::getInstance();
    $search = $db->escapeString($search);
    $whereClause = "WHERE (title LIKE '%$search%' OR description LIKE '%$search%')";
    
    if (!empty($filters)) {
        // Add filters to the where clause
        if (!empty($filters['type'])) {
            $type = $db->escapeString($filters['type']);
            $whereClause .= " AND type = '$type'";
        }
        
        if (!empty($filters['status'])) {
            $status = $db->escapeString($filters['status']);
            $whereClause .= " AND status = '$status'";
        }
    }
    
    // Get total number of factories with search condition
    $totalItems = $db->query("SELECT COUNT(*) as total FROM factories $whereClause");
    $totalItems = $db->fetchArray($totalItems)['total'];
    
    // Get factories with search condition and pagination
    $query = "SELECT * FROM factories $whereClause ORDER BY date_added DESC LIMIT $offset, $itemsPerPage";
    $result = $db->query($query);
    
    // Create Factory objects from the results
    $factories = [];
    while ($factoryData = $db->fetchArray($result)) {
        $factory = new Factory();
        $factory->findById($factoryData['id']);
        $factories[] = $factory;
    }
} else {
    // Use the model methods when there's no text search
    $totalItems = Factory::countAll($filters);
    $factories = Factory::getAll($itemsPerPage, $offset, $filters);
}

$totalPages = ceil($totalItems / $itemsPerPage);
?>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="row">
            <div class="col-md-5 mb-2">
                <input type="text" class="form-control" name="search" placeholder="Search by title or description..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2 mb-2">
                <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="sale" <?php echo $typeFilter === 'sale' ? 'selected' : ''; ?>>For Sale</option>
                    <option value="rent" <?php echo $typeFilter === 'rent' ? 'selected' : ''; ?>>For Rent</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="sold" <?php echo $statusFilter === 'sold' ? 'selected' : ''; ?>>Sold</option>
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary btn-block">Search</button>
            </div>
            <div class="col-md-1 mb-2">
                <a href="factories.php" class="btn btn-outline-secondary btn-block"><i class="fas fa-sync"></i></a>
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
                        <th>ID</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Location</th>
                        <th>Price</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if (count($factories) > 0): 
                        foreach ($factories as $factory):
                            // Get factory address
                            $address = $factory->getAddress();
                            $addressText = $address ? $address->getFormattedAddress() : 'N/A';
                            
                            // Get factory main image
                            $mainImage = $factory->getMainImage();
                            $imagePath = $mainImage ? '../uploads/factories/' . $mainImage->getImagePath() : '../assets/images/placeholder.jpg';
                    ?>
                        <tr>
                            <td><?php echo $factory->getId(); ?></td>
                            <td>
                                <img src="<?php echo $imagePath; ?>" alt="Factory Image" style="width: 50px; height: 50px; object-fit: cover;">
                            </td>
                            <td><?php echo htmlspecialchars($factory->getTitle()); ?></td>
                            <td><?php echo htmlspecialchars($addressText); ?></td>
                            <td>$<?php echo number_format($factory->getPrice(), 2); ?></td>
                            <td>
                                <?php if ($factory->getType() == 'sale'): ?>
                                    <span class="badge badge-primary">For Sale</span>
                                <?php else: ?>
                                    <span class="badge badge-info">For Rent</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status = $factory->getStatus();
                                $statusClass = '';
                                switch ($status) {
                                    case 'available':
                                        $statusClass = 'success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'warning';
                                        break;
                                    case 'sold':
                                        $statusClass = 'secondary';
                                        break;
                                    default:
                                        $statusClass = 'info';
                                        break;
                                }
                                ?>
                                <span class="badge badge-<?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span>
                            </td>
                            <td>
                                <?php if ($factory->isFeatured()): ?>
                                    <a href="factories.php?featured=<?php echo $factory->getId(); ?>&value=0" class="badge badge-success">
                                        <i class="fas fa-star"></i> Featured
                                    </a>
                                <?php else: ?>
                                    <a href="factories.php?featured=<?php echo $factory->getId(); ?>&value=1" class="badge badge-secondary">
                                        <i class="far fa-star"></i> Not Featured
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($factory->getDateAdded())); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../factory-details.php?id=<?php echo $factory->getId(); ?>" target="_blank" class="btn btn-info" title="View">
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
                    <?php 
                        endforeach;
                    else: 
                    ?>
                        <tr>
                            <td colspan="10" class="text-center">No factories found</td>
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