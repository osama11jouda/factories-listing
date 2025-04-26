<?php
include 'includes/header.php';
require_once 'models/Factory.php';
require_once 'models/FactoryImage.php';
require_once 'models/Address.php';
require_once 'models/Category.php'; // Added Category model

// Check if user is logged in and active
$loggedIn = isset($_SESSION['user_id']);
$isActive = isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;

// Pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 9;
$offset = ($page - 1) * $itemsPerPage;

// Search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$typeFilter = isset($_GET['type']) ? $_GET['type'] : '';
$locationFilter = isset($_GET['location']) ? $_GET['location'] : '';
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : ''; // Added category filter parameter

// Build filters for the Factory model
$filters = [
    'status' => 'available'
];

if (!empty($search)) {
    $filters['search'] = $search;
}

if (!empty($typeFilter)) {
    $filters['type'] = $typeFilter;
}

if (!empty($locationFilter)) {
    $filters['city'] = $locationFilter;
}

if (!empty($categoryFilter)) {
    $filters['category_id'] = $categoryFilter; // Add category filter
}

// Get total factories count with filters
$totalItems = Factory::countAll($filters);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get factories using the model
$factories = Factory::getAll($itemsPerPage, $offset, $filters);

// Get unique locations for filter dropdown
$db = Database::getInstance();
$locationsQuery = "SELECT DISTINCT a.city FROM addresses a 
                   JOIN factories f ON f.address_id = a.id
                   ORDER BY a.city";
$locationsResult = $db->query($locationsQuery);
$locations = [];
while ($row = $db->fetchArray($locationsResult)) {
    $locations[] = $row['city'];
}

// Get unique categories for filter dropdown
$categories = Category::getAll();
?>

<div class="container-wide py-5">
    <h1 class="section-title mb-4">Available Factories</h1>

    <?php if (!$loggedIn): ?>
        <div class="alert alert-info">
            <h4><i class="fas fa-info-circle"></i> Please Login to View Factories</h4>
            <p>You need to login or register to access our factory listings.</p>
            <a href="register.php" class="btn btn-primary">Register / Login</a>
        </div>
    <?php elseif (!$isActive): ?>
        <div class="alert alert-warning">
            <h4><i class="fas fa-exclamation-triangle"></i> Subscription Required</h4>
            <p>Your account is registered but not yet active. A subscription payment is required to view factory listings.</p>
            <p>Our team will contact you shortly with payment instructions, or you may <a href="contact.php">contact us</a> for more information.</p>
        </div>
    <?php else: ?>
        <!-- Search & Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <input type="text" class="form-control" name="search" placeholder="Search factories..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="sale" <?php echo $typeFilter == 'sale' ? 'selected' : ''; ?>>For Sale</option>
                                <option value="rent" <?php echo $typeFilter == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-3">
                            <select name="location" class="form-control">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?php echo $location; ?>" <?php echo $locationFilter == $location ? 'selected' : ''; ?>>
                                        <?php echo $location; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select name="category" class="form-control">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category->getId(); ?>" <?php echo $categoryFilter == $category->getId() ? 'selected' : ''; ?>>
                                        <?php echo $category->getName(); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 ml-auto">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-success" style="background-color: #28a745; border-color: #28a745;">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="factories.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Factories List -->
        <div class="row">
            <?php if (!empty($factories)): ?>
                <?php foreach ($factories as $factory): ?>
                    <?php 
                        $mainImage = $factory->getMainImage();
                        $address = $factory->getAddress();
                    ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card factory-card h-100">
                            <?php if ($factory->isFeatured()): ?>
                                <div class="featured-badge">
                                    <span><i class="fas fa-star"></i> Featured</span>
                                </div>
                            <?php endif; ?>
                            <div class="card-img-top-wrapper">
                                <?php if ($mainImage): ?>
                                    <img src="<?php echo $mainImage->getFullImagePath(); ?>" class="card-img-top" alt="<?php echo $factory->getTitle(); ?>">
                                <?php else: ?>
                                    <img src="images/factory-placeholder.jpg" class="card-img-top" alt="Factory Placeholder">
                                <?php endif; ?>
                                <span class="property-type-badge <?php echo $factory->getType() == 'sale' ? 'sale' : 'rent'; ?>">
                                    For <?php echo ucfirst($factory->getType()); ?>
                                </span>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $factory->getTitle(); ?></h5>
                                <?php if ($address): ?>
                                    <p class="card-text location">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        <?php echo $address->getCity() . ', ' . $address->getCountry(); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="card-text area">
                                    <i class="fas fa-ruler-combined"></i> <?php echo number_format($factory->getArea()); ?> sq m
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="price mb-0">$<?php echo number_format($factory->getPrice()); ?></h6>
                                    <a href="factory-details.php?id=<?php echo $factory->getId(); ?>" class="btn btn-primary">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle"></i> No factories found matching your criteria.
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="row mt-4">
            <div class="col-12">
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&location=<?php echo urlencode($locationFilter); ?>&category=<?php echo urlencode($categoryFilter); ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&location=<?php echo urlencode($locationFilter); ?>&category=<?php echo urlencode($categoryFilter); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&location=<?php echo urlencode($locationFilter); ?>&category=<?php echo urlencode($categoryFilter); ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>