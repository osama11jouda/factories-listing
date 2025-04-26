<?php
include 'includes/header.php';
require_once 'models/Factory.php';
require_once 'models/FactoryImage.php';
require_once 'models/Address.php';

// Check if user is logged in and active
$loggedIn = isset($_SESSION['user_id']);
$isActive = isset($_SESSION['is_active']) && $_SESSION['is_active'] == 1;

// Get factory ID from URL
$factoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no ID provided, redirect to factories list
if ($factoryId <= 0) {
    header('Location: factories.php');
    exit;
}

// Get factory details using our model
$factory = new Factory();
$factoryExists = $factory->findById($factoryId);

// Get factory images if factory exists
if ($factoryExists) {
    $images = $factory->getImages();
    $mainImage = $factory->getMainImage();
    $address = $factory->getAddress();
}
?>

<div class="container-wide py-5">
    <?php if (!$factoryExists): ?>
        <div class="alert alert-danger">
            <h4><i class="fas fa-exclamation-triangle"></i> Factory Not Found</h4>
            <p>The factory you are looking for does not exist or has been removed.</p>
            <a href="factories.php" class="btn btn-primary">View All Factories</a>
        </div>
    <?php elseif (!$loggedIn): ?>
        <div class="alert alert-info">
            <h4><i class="fas fa-info-circle"></i> Please Login to View Factory Details</h4>
            <p>You need to login or register to access factory details.</p>
            <a href="register.php" class="btn btn-primary">Register / Login</a>
        </div>
    <?php elseif (!$isActive): ?>
        <div class="alert alert-warning">
            <h4><i class="fas fa-exclamation-triangle"></i> Subscription Required</h4>
            <p>Your account is registered but not yet active. A subscription payment is required to view factory details.</p>
            <p>Our team will contact you shortly with payment instructions, or you may <a href="contact.php">contact us</a> for more information.</p>
        </div>
    <?php else: ?>
        <!-- Factory Details -->
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="factories.php">Factories</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo $factory->getTitle(); ?></li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <!-- Factory Images Gallery -->
            <div class="col-lg-7 mb-4">
                <?php if (!empty($images)): ?>
                    <div class="factory-gallery">
                        <div class="main-image-container mb-3">
                            <img src="<?php echo $mainImage ? $mainImage->getFullImagePath() : 'images/factory-placeholder.jpg'; ?>" 
                                 class="img-fluid main-image" id="mainImage" alt="<?php echo $factory->getTitle(); ?>">
                            <?php if ($factory->isFeatured()): ?>
                                <div class="featured-badge-details">
                                    <span><i class="fas fa-star"></i> Featured</span>
                                </div>
                            <?php endif; ?>
                            <span class="property-type-badge-details <?php echo $factory->getType() == 'sale' ? 'sale' : 'rent'; ?>">
                                For <?php echo ucfirst($factory->getType()); ?>
                            </span>
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <div class="thumbnail-slider">
                                <button class="slider-nav-btn prev" onclick="slideThumbnails(-1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                
                                <div class="thumbnails-container" id="thumbnailsContainer">
                                    <?php foreach ($images as $index => $image): ?>
                                        <div class="thumbnail-item">
                                            <img src="<?php echo $image->getFullImagePath(); ?>" 
                                                 class="img-thumbnail gallery-thumbnail <?php echo ($image->isMain()) ? 'active' : ''; ?>" 
                                                 onclick="changeMainImage('<?php echo $image->getFullImagePath(); ?>', this)" 
                                                 alt="<?php echo $factory->getTitle() . ' - Image ' . ($index + 1); ?>">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <button class="slider-nav-btn next" onclick="slideThumbnails(1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <img src="images/factory-placeholder.jpg" class="img-fluid" alt="Factory Placeholder">
                <?php endif; ?>
            </div>
            
            <!-- Factory Details -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="factory-title mb-3"><?php echo $factory->getTitle(); ?></h2>
                        
                        <div class="factory-price mb-3">
                            <span class="price-label">Price:</span>
                            <span class="price-amount">$<?php echo number_format($factory->getPrice()); ?></span>
                            <?php if ($factory->getType() == 'rent'): ?>
                                <span class="price-period">/ month</span>
                            <?php endif; ?>
                        </div>
                        
                        <ul class="factory-details-list">
                            <?php if ($address): ?>
                            <li>
                                <i class="fas fa-map-marker-alt"></i>
                                <strong>Location:</strong> <?php echo $address->getFormattedAddress(); ?>
                            </li>
                            <?php endif; ?>
                            <li>
                                <i class="fas fa-ruler-combined"></i>
                                <strong>Total Area:</strong> <?php echo number_format($factory->getArea()); ?> sq m
                            </li>
                            
                            <!-- Factory Categories -->
                            <?php
                            $categories = $factory->getCategories();
                            if (!empty($categories)): ?>
                            <li>
                                <i class="fas fa-tags"></i>
                                <strong>Categories:</strong> 
                                <div class="mt-2">
                                    <?php foreach ($categories as $category): ?>
                                    <span class="badge badge-pill badge-primary mr-2 mb-1"><?php echo htmlspecialchars($category->getName()); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                            <?php endif; ?>
                            
                            <li>
                                <i class="fas fa-tag"></i>
                                <strong>Status:</strong> 
                                <span class="badge badge-<?php echo $factory->getStatus() == 'available' ? 'success' : ($factory->getStatus() == 'pending' ? 'warning' : 'secondary'); ?>">
                                    <?php echo ucfirst($factory->getStatus()); ?>
                                </span>
                            </li>
                            <li>
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Listed:</strong> <?php echo date('F d, Y', strtotime($factory->getDateAdded())); ?>
                            </li>
                            <?php if ($factory->getUserId()): ?>
                            <li>
                                <i class="fas fa-user"></i>
                                <strong>Listed By:</strong> 
                                <?php 
                                $owner = $factory->getOwner();
                                echo $owner ? htmlspecialchars($owner->getName()) : 'Admin'; 
                                ?>
                            </li>
                            <?php endif; ?>
                        </ul>
                        
                        <div class="factory-contact mt-4">
                            <h5 class="mb-3"><i class="fas fa-phone"></i> Contact Information</h5>
                            <?php $contactInfo = $factory->getContactInfo(); ?>
                            <?php if (!empty($contactInfo)): ?>
                                <p><?php echo nl2br(htmlspecialchars($contactInfo)); ?></p>
                            <?php else: ?>
                                <p>
                                    For more information about this factory, please contact our office:
                                    <br>
                                    <strong>Phone:</strong> +963 11 123 4567
                                    <br>
                                    <strong>Email:</strong> info@syrianfactories.com
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="factory-actions mt-4">
                            <a href="contact.php?subject=Inquiry about Factory: <?php echo urlencode($factory->getTitle()); ?>" class="btn btn-primary btn-lg btn-block">
                                <i class="fas fa-envelope"></i> Send Inquiry
                            </a>
                            <a href="#" class="btn btn-outline-secondary btn-block" onclick="window.print(); return false;">
                                <i class="fas fa-print"></i> Print Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Factory Description -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-info-circle"></i> Factory Description</h3>
                    </div>
                    <div class="card-body">
                        <div class="factory-description">
                            <?php echo nl2br(htmlspecialchars($factory->getDescription())); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Factory Location Map -->
        <?php if ($address && $address->getLatitude() && $address->getLongitude()): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-map-marked-alt"></i> Location Map</h3>
                    </div>
                    <div class="card-body">
                        <div id="factory-map" 
                             data-lat="<?php echo floatval($address->getLatitude()); ?>" 
                             data-lng="<?php echo floatval($address->getLongitude()); ?>"
                             data-title="<?php echo htmlspecialchars($factory->getTitle()); ?>"
                             style="height: 400px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Related Factories -->
        <?php
        // Get related factories (same type)
        $filters = [];
        if ($address) {
            $filters['city'] = $address->getCity();
        }
        $filters['type'] = $factory->getType();
        $filters['status'] = 'available';
        
        // Get factories using the model
        $relatedFactories = Factory::getAll(3, 0, $filters);
        
        // Filter out the current factory
        $relatedFactories = array_filter($relatedFactories, function($relatedFactory) use ($factoryId) {
            return $relatedFactory->getId() != $factoryId;
        });
        
        if (!empty($relatedFactories)):
        ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="section-title mb-4">Related Factories</h3>
            </div>
            
            <?php foreach ($relatedFactories as $related): ?>
            <?php $relatedMainImage = $related->getMainImage(); ?>
            <div class="col-md-4 mb-4">
                <div class="card factory-card h-100">
                    <div class="card-img-top-wrapper">
                        <?php if ($relatedMainImage): ?>
                            <img src="<?php echo $relatedMainImage->getFullImagePath(); ?>" class="card-img-top" alt="<?php echo $related->getTitle(); ?>">
                        <?php else: ?>
                            <img src="images/factory-placeholder.jpg" class="card-img-top" alt="Factory Placeholder">
                        <?php endif; ?>
                        <span class="property-type-badge <?php echo $related->getType() == 'sale' ? 'sale' : 'rent'; ?>">
                            For <?php echo ucfirst($related->getType()); ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $related->getTitle(); ?></h5>
                        <?php $relatedAddress = $related->getAddress(); ?>
                        <?php if ($relatedAddress): ?>
                        <p class="card-text location">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $relatedAddress->getCity() . ', ' . $relatedAddress->getCountry(); ?>
                        </p>
                        <?php endif; ?>
                        <p class="card-text area">
                            <i class="fas fa-ruler-combined"></i> <?php echo number_format($related->getArea()); ?> sq m
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="price mb-0">$<?php echo number_format($related->getPrice()); ?></h6>
                            <a href="factory-details.php?id=<?php echo $related->getId(); ?>" class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Add Leaflet CSS in the head section -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<!-- Add Leaflet JavaScript Library -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<script>
function changeMainImage(imagePath, thumbnail) {
    // Update main image
    document.getElementById('mainImage').src = imagePath;
    
    // Update active thumbnail
    let thumbnails = document.querySelectorAll('.gallery-thumbnail');
    thumbnails.forEach(item => item.classList.remove('active'));
    thumbnail.classList.add('active');
}

// Thumbnail slider functionality
let currentPosition = 0;
const thumbnailWidth = 90; // 80px width + 10px margins

function slideThumbnails(direction) {
    const container = document.getElementById('thumbnailsContainer');
    const thumbnails = container.querySelectorAll('.thumbnail-item');
    const maxPosition = (thumbnails.length - Math.floor(container.clientWidth / thumbnailWidth)) * thumbnailWidth;
    
    // Calculate new position
    currentPosition = currentPosition + direction * thumbnailWidth;
    
    // Apply boundaries
    if (currentPosition < 0) currentPosition = 0;
    if (currentPosition > maxPosition) currentPosition = maxPosition;
    
    // Apply transform
    container.style.transform = `translateX(-${currentPosition}px)`;
}

// Initialize slider on window resize to handle responsiveness
window.addEventListener('resize', function() {
    // Reset position when window size changes
    currentPosition = 0;
    const container = document.getElementById('thumbnailsContainer');
    if (container) container.style.transform = 'translateX(0)';
});

<?php if ($address && $address->getLatitude() && $address->getLongitude()): ?>
// Initialize the map
document.addEventListener('DOMContentLoaded', function() {
    // Create map instance
    const lat = document.getElementById('factory-map').dataset.lat;
    const lng = document.getElementById('factory-map').dataset.lng;
    const title = document.getElementById('factory-map').dataset.title;
    
    const map = L.map('factory-map').setView([lat, lng], 14);
    
    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    // Add marker for factory location
    L.marker([lat, lng]).addTo(map)
        .bindPopup(title)
        .openPopup();
    
    // Fix for map rendering issues - force a resize after the page loads
    setTimeout(function() {
        map.invalidateSize();
    }, 100);
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>