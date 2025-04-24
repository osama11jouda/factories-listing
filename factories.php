<?php
include 'includes/header.php';

// Check if user is logged in and active
$loggedIn = isset($_SESSION['user_id']);
$isActive = isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;

// Pagination parameters
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 9;
$offset = ($page - 1) * $itemsPerPage;

// Search parameters
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$typeFilter = isset($_GET['type']) ? mysqli_real_escape_string($conn, $_GET['type']) : '';
$locationFilter = isset($_GET['location']) ? mysqli_real_escape_string($conn, $_GET['location']) : '';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : '';
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : '';

// Build the search condition
$searchCondition = "WHERE status = 'available'";
if (!empty($search)) {
    $searchCondition .= " AND (title LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%')";
}
if (!empty($typeFilter)) {
    $searchCondition .= " AND type = '$typeFilter'";
}
if (!empty($locationFilter)) {
    $searchCondition .= " AND location LIKE '%$locationFilter%'";
}
if ($minPrice !== '') {
    $searchCondition .= " AND price >= $minPrice";
}
if ($maxPrice !== '') {
    $searchCondition .= " AND price <= $maxPrice";
}

// Get total number of factories with search condition
$countQuery = "SELECT COUNT(*) as total FROM factories $searchCondition";
$countResult = mysqli_query($conn, $countQuery);
$totalItems = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalItems / $itemsPerPage);

// Get unique locations for filter dropdown
$locationsQuery = "SELECT DISTINCT location FROM factories ORDER BY location";
$locationsResult = mysqli_query($conn, $locationsQuery);
$locations = [];
while ($row = mysqli_fetch_assoc($locationsResult)) {
    $locations[] = $row['location'];
}
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
                        <div class="col-md-2 mb-3">
                            <input type="number" class="form-control" name="min_price" placeholder="Min Price" value="<?php echo $minPrice !== '' ? $minPrice : ''; ?>">
                        </div>
                        <div class="col-md-2 mb-3">
                            <input type="number" class="form-control" name="max_price" placeholder="Max Price" value="<?php echo $maxPrice !== '' ? $maxPrice : ''; ?>">
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
            <?php
            // Get factories with search condition
            $query = "SELECT f.*, 
                      (SELECT image_path FROM factory_images WHERE factory_id = f.id AND is_main = 1 LIMIT 1) as main_image 
                      FROM factories f 
                      $searchCondition 
                      ORDER BY f.featured DESC, f.date_added DESC 
                      LIMIT $offset, $itemsPerPage";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) > 0):
                while ($factory = mysqli_fetch_assoc($result)):
            ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card factory-card h-100">
                        <?php if ($factory['featured']): ?>
                            <div class="featured-badge">
                                <span><i class="fas fa-star"></i> Featured</span>
                            </div>
                        <?php endif; ?>
                        <div class="card-img-top-wrapper">
                            <?php if (!empty($factory['main_image'])): ?>
                                <img src="uploads/factories/<?php echo $factory['main_image']; ?>" class="card-img-top" alt="<?php echo $factory['title']; ?>">
                            <?php else: ?>
                                <img src="images/factory-placeholder.jpg" class="card-img-top" alt="Factory Placeholder">
                            <?php endif; ?>
                            <span class="property-type-badge <?php echo $factory['type'] == 'sale' ? 'sale' : 'rent'; ?>">
                                For <?php echo ucfirst($factory['type']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $factory['title']; ?></h5>
                            <p class="card-text location">
                                <i class="fas fa-map-marker-alt"></i> <?php echo $factory['location']; ?>
                            </p>
                            <p class="card-text area">
                                <i class="fas fa-ruler-combined"></i> <?php echo number_format($factory['area']); ?> sq m
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="price mb-0">$<?php echo number_format($factory['price']); ?></h6>
                                <a href="factory-details.php?id=<?php echo $factory['id']; ?>" class="btn btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                endwhile;
            else:
            ?>
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
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&location=<?php echo urlencode($locationFilter); ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&location=<?php echo urlencode($locationFilter); ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($typeFilter); ?>&location=<?php echo urlencode($locationFilter); ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>" aria-label="Next">
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

<style>
.factory-card {
    position: relative;
    transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
    border: 1px solid #eee;
    overflow: hidden;
}

.factory-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.card-img-top-wrapper {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.card-img-top {
    height: 100%;
    object-fit: cover;
    width: 100%;
    transition: transform 0.3s ease;
}

.factory-card:hover .card-img-top {
    transform: scale(1.05);
}

.property-type-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1;
    padding: 5px 10px;
    color: white;
    font-size: 0.8rem;
    font-weight: bold;
    border-radius: 3px;
}

.property-type-badge.sale {
    background-color: #28a745;
}

.property-type-badge.rent {
    background-color: #17a2b8;
}

.featured-badge {
    position: absolute;
    top: 10px;
    left: -30px;
    transform: rotate(-45deg);
    z-index: 1;
    background-color: #ffc107;
    padding: 5px 30px;
    color: #343a40;
    font-size: 0.7rem;
    font-weight: bold;
}

.price {
    color: #28a745;
    font-weight: bold;
}

.location, .area {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 8px;
}

.pagination .page-link {
    color: #343a40;
}

.pagination .page-item.active .page-link {
    background-color: #343a40;
    border-color: #343a40;
}
</style>

<?php include 'includes/footer.php'; ?>