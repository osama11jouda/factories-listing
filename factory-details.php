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
                            <img src="<?php echo $mainImage ? $mainImage->getImagePath() : 'images/factory-placeholder.jpg'; ?>" 
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
                            <div class="thumbnail-gallery row">
                                <?php foreach ($images as $index => $image): ?>
                                    <div class="col-3 mb-3">
                                        <img src="<?php echo $image->getImagePath(); ?>" 
                                             class="img-thumbnail gallery-thumbnail <?php echo ($image->isMain()) ? 'active' : ''; ?>" 
                                             onclick="changeMainImage('<?php echo $image->getImagePath(); ?>', this)" 
                                             alt="<?php echo $factory->getTitle() . ' - Image ' . ($index + 1); ?>">
                                    </div>
                                <?php endforeach; ?>
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
                                <strong>Location:</strong> <?php echo $address->getCity() . ', ' . $address->getCountry(); ?>
                            </li>
                            <?php endif; ?>
                            <li>
                                <i class="fas fa-ruler-combined"></i>
                                <strong>Total Area:</strong> <?php echo number_format($factory->getArea()); ?> sq m
                            </li>
                            <li>
                                <i class="fas fa-tag"></i>
                                <strong>Status:</strong> 
                                <span class="badge badge-success"><?php echo ucfirst($factory->getStatus()); ?></span>
                            </li>
                            <li>
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Listed:</strong> <?php echo date('F d, Y', strtotime($factory->getDateAdded())); ?>
                            </li>
                        </ul>
                        
                        <div class="factory-contact mt-4">
                            <h5 class="mb-3"><i class="fas fa-phone"></i> Contact Information</h5>
                            <?php $contactInfo = $factory->getContactInfo(); ?>
                            <?php if (!empty($contactInfo)): ?>
                                <p><?php echo nl2br($contactInfo); ?></p>
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
                            <?php echo nl2br($factory->getDescription()); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
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
                            <img src="<?php echo $relatedMainImage->getImagePath(); ?>" class="card-img-top" alt="<?php echo $related->getTitle(); ?>">
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

<style>
.main-image-container {
    position: relative;
    border-radius: 5px;
    overflow: hidden;
    height: 400px;
}

.main-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 5px;
}

.gallery-thumbnail {
    cursor: pointer;
    height: 80px;
    object-fit: cover;
    transition: all 0.3s;
}

.gallery-thumbnail.active {
    border: 3px solidrgb(22, 190, 109);
}

.featured-badge-details {
    position: absolute;
    top: 20px;
    left: -35px;
    transform: rotate(-45deg);
    background-color: #ffc107;
    padding: 5px 30px;
    color: #343a40;
    font-weight: bold;
    z-index: 1;
}

.property-type-badge-details {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 8px 16px;
    color: white;
    font-weight: bold;
    border-radius: 4px;
    z-index: 1;
}

.property-type-badge-details.sale {
    background-color: #28a745;
}

.property-type-badge-details.rent {
    background-color: #17a2b8;
}

.factory-title {
    font-size: 1.8rem;
    font-weight: 700;
}

.factory-price {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 5px solid #28a745;
}

.price-label {
    font-weight: 600;
    color: #6c757d;
}

.price-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
    margin-left: 8px;
}

.price-period {
    font-size: 0.9rem;
    color: #6c757d;
}

.factory-details-list {
    list-style: none;
    padding-left: 0;
    margin-top: 20px;
}

.factory-details-list li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.factory-details-list i {
    margin-right: 10px;
    color: #6c757d;
}

.factory-description {
    font-size: 1rem;
    line-height: 1.6;
}
</style>

<script>
function changeMainImage(imagePath, thumbnail) {
    // Update main image
    document.getElementById('mainImage').src = imagePath;
    
    // Update active thumbnail
    let thumbnails = document.querySelectorAll('.gallery-thumbnail');
    thumbnails.forEach(item => item.classList.remove('active'));
    thumbnail.classList.add('active');
}
</script>

<?php include 'includes/footer.php'; ?>