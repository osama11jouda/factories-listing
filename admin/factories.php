<?php include 'includes/header.php'; ?>

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
    
    // First delete related images
    $deleteImagesQuery = "DELETE FROM factory_images WHERE factory_id = $factoryId";
    mysqli_query($conn, $deleteImagesQuery);
    
    // Then delete the factory
    $deleteFactoryQuery = "DELETE FROM factories WHERE id = $factoryId";
    if(mysqli_query($conn, $deleteFactoryQuery)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Factory successfully deleted.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error deleting factory: ' . mysqli_error($conn) . '
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    }
}

// Feature/unfeature factory
if(isset($_GET['feature']) && is_numeric($_GET['feature'])) {
    $factoryId = intval($_GET['feature']);
    $featureValue = isset($_GET['value']) && $_GET['value'] == '1' ? 1 : 0;
    
    $featureQuery = "UPDATE factories SET featured = $featureValue WHERE id = $factoryId";
    mysqli_query($conn, $featureQuery);
}

// Pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Search parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$typeFilter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';

// Build the search condition
$searchCondition = "";
if(!empty($search)) {
    $searchCondition .= " AND (title LIKE '%$search%' OR location LIKE '%$search%' OR description LIKE '%$search%')";
}
if(!empty($typeFilter)) {
    $searchCondition .= " AND type = '$typeFilter'";
}
if(!empty($statusFilter)) {
    $searchCondition .= " AND status = '$statusFilter'";
}

// Get total number of factories with search condition
$countQuery = "SELECT COUNT(*) as total FROM factories WHERE 1=1" . $searchCondition;
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get factories with search condition and pagination
$query = "SELECT f.*, 
          (SELECT image_path FROM factory_images WHERE factory_id = f.id AND is_main = 1 LIMIT 1) as main_image 
          FROM factories f 
          WHERE 1=1" . $searchCondition . " 
          ORDER BY f.featured DESC, f.date_added DESC 
          LIMIT $offset, $itemsPerPage";
$result = mysqli_query($conn, $query);
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
                    <?php 
                    if(mysqli_num_rows($result) > 0): 
                        while($factory = mysqli_fetch_assoc($result)):
                    ?>
                        <tr>
                            <td width="80">
                                <?php if(!empty($factory['main_image'])): ?>
                                    <img src="../uploads/factories/<?php echo $factory['main_image']; ?>" class="img-thumbnail" alt="Factory Image" width="70">
                                <?php else: ?>
                                    <img src="../images/factory-placeholder.jpg" class="img-thumbnail" alt="Factory Placeholder" width="70">
                                <?php endif; ?>
                            </td>
                            <td><?php echo $factory['title']; ?></td>
                            <td><?php echo $factory['location']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $factory['type'] == 'sale' ? 'success' : 'info'; ?>">
                                    <?php echo ucfirst($factory['type']); ?>
                                </span>
                            </td>
                            <td>$<?php echo number_format($factory['price']); ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $factory['status'] == 'available' ? 'success' : 
                                        ($factory['status'] == 'pending' ? 'warning' : 'secondary'); 
                                ?>">
                                    <?php echo ucfirst($factory['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if($factory['featured']): ?>
                                    <a href="factories.php?feature=<?php echo $factory['id']; ?>&value=0" class="text-warning" title="Click to unfeature">
                                        <i class="fas fa-star"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="factories.php?feature=<?php echo $factory['id']; ?>&value=1" class="text-secondary" title="Click to feature">
                                        <i class="far fa-star"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="../factory-details.php?id=<?php echo $factory['id']; ?>" class="btn btn-info" title="View" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="edit_factory.php?id=<?php echo $factory['id']; ?>" class="btn btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="factories.php?delete=<?php echo $factory['id']; ?>" class="btn btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this factory?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
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
<?php if($totalPages > 1): ?>
<nav aria-label="Page navigation" class="mt-4">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&status=<?php echo urlencode($statusFilter); ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
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