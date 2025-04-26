<?php 
include 'includes/header.php';
require_once '../models/Factory.php';
require_once '../models/Address.php';
require_once '../models/FactoryImage.php';
require_once '../models/Category.php';

// Initialize variables
$success = false;
$error = '';
$factoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$factoryId) {
    // Redirect if no valid ID provided
    header('Location: factories.php');
    exit;
}

$factoryObj = new Factory();
if (!$factoryObj->findById($factoryId)) {
    // Redirect if factory not found
    header('Location: factories.php');
    exit;
}

// Get address information
$address = $factoryObj->getAddress();
if (!$address) {
    $address = new Address();
}

// Initialize the factory array with current values
$factory = [
    'title' => $factoryObj->getTitle(),
    'description' => $factoryObj->getDescription(),
    'street_address' => $address->getStreetAddress(),
    'city' => $address->getCity(),
    'state_province' => $address->getStateProvince(),
    'postal_code' => $address->getPostalCode(),
    'country' => $address->getCountry(),
    'latitude' => $address->getLatitude(),
    'longitude' => $address->getLongitude(),
    'price' => $factoryObj->getPrice(),
    'area' => $factoryObj->getArea(),
    'type' => $factoryObj->getType(),
    'status' => $factoryObj->getStatus(),
    'featured' => $factoryObj->isFeatured(),
    'contact_info' => $factoryObj->getContactInfo()
];

// Get all categories for selection
$allCategories = Category::getAll();
$selectedCategories = $factoryObj->getCategories();
$selectedCategoryIds = array_map(function($cat) {
    return $cat->getId();
}, $selectedCategories);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $factory['title'] = $_POST['title'];
    $factory['description'] = $_POST['description'];
    $factory['street_address'] = $_POST['street_address'];
    $factory['city'] = $_POST['city'];
    $factory['state_province'] = $_POST['state_province'];
    $factory['postal_code'] = $_POST['postal_code'];
    $factory['country'] = $_POST['country'];
    $factory['latitude'] = $_POST['latitude'];
    $factory['longitude'] = $_POST['longitude'];
    $factory['price'] = floatval($_POST['price']);
    $factory['area'] = floatval($_POST['area']);
    $factory['type'] = $_POST['type'];
    $factory['status'] = $_POST['status'];
    $factory['featured'] = isset($_POST['featured']) ? 1 : 0;
    $factory['contact_info'] = $_POST['contact_info'];
    $factory['categories'] = isset($_POST['categories']) ? $_POST['categories'] : [];
    
    // Validate input
    if (empty($factory['title']) || empty($factory['description']) || empty($factory['street_address']) || empty($factory['city']) || empty($factory['state_province']) || empty($factory['postal_code']) || empty($factory['country'])) {
        $error = "Please fill all required fields.";
    } elseif ($factory['price'] <= 0) {
        $error = "Price must be greater than zero.";
    } elseif ($factory['area'] <= 0) {
        $error = "Area must be greater than zero.";
    } else {
        // Update address
        $address->setStreetAddress($factory['street_address']);
        $address->setCity($factory['city']);
        $address->setStateProvince($factory['state_province']);
        $address->setPostalCode($factory['postal_code']);
        $address->setCountry($factory['country']);
        $address->setLatitude($factory['latitude']);
        $address->setLongitude($factory['longitude']);
        
        if ($address->update()) {
            // Update factory
            $factoryObj->setTitle($factory['title']);
            $factoryObj->setDescription($factory['description']);
            $factoryObj->setPrice($factory['price']);
            $factoryObj->setArea($factory['area']);
            $factoryObj->setType($factory['type']);
            $factoryObj->setStatus($factory['status']);
            $factoryObj->setFeatured($factory['featured']);
            $factoryObj->setContactInfo($factory['contact_info']);
            $factoryObj->setCategories($factory['categories']);
            
            if ($factoryObj->update()) {
                $success = true;
                
                // Handle image uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $uploadDir = '../uploads/factories/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $mainImage = isset($_POST['main_image']) ? intval($_POST['main_image']) : 0;
                    
                    // Process each uploaded image
                    foreach ($_FILES['images']['name'] as $key => $name) {
                        if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                            $tempName = $_FILES['images']['tmp_name'][$key];
                            $extension = pathinfo($name, PATHINFO_EXTENSION);
                            $newFilename = 'factory_' . $factoryId . '_' . time() . '_' . $key . '.' . $extension;
                            
                            if (move_uploaded_file($tempName, $uploadDir . $newFilename)) {
                                // Create image record in database
                                $factoryImage = new FactoryImage();
                                $factoryImage->setFactoryId($factoryId);
                                $factoryImage->setImagePath($newFilename);
                                $factoryImage->setIsMain($mainImage == $key);
                                $factoryImage->create();
                            }
                        }
                    }
                }
                
                // Set new main image if selected
                if (isset($_POST['existing_main_image'])) {
                    $mainImageId = intval($_POST['existing_main_image']);
                    FactoryImage::updateMainImage($factoryId, $mainImageId);
                }
                
                // Handle image deletions
                if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
                    foreach ($_POST['delete_images'] as $imageId) {
                        $imageId = intval($imageId);
                        $image = new FactoryImage();
                        if ($image->findById($imageId)) {
                            // Delete physical file
                            if (file_exists('../uploads/factories/' . $image->getImagePath())) {
                                unlink('../uploads/factories/' . $image->getImagePath());
                            }
                            // Delete database record
                            $image->delete();
                        }
                    }
                }
            } else {
                $error = "Error: Failed to update factory.";
            }
        } else {
            $error = "Error: Failed to update address.";
        }
    }
    
    // Refresh factory data after update
    $factoryObj->findById($factoryId);
    $address = $factoryObj->getAddress();
}

// Get all factory images
$factoryImages = FactoryImage::getByFactoryId($factoryId);
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Edit Factory</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group mr-2">
            <a href="factories.php" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Factories
            </a>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Factory has been updated successfully.
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $error; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $factoryId); ?>" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="title">Factory Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($factory['title']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($factory['description']); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Address Details <span class="text-danger">*</span></label>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" id="street_address" name="street_address" value="<?php echo htmlspecialchars($factory['street_address']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="city">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($factory['city']); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="state_province">State/Province</label>
                                <input type="text" class="form-control" id="state_province" name="state_province" value="<?php echo htmlspecialchars($factory['state_province']); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="postal_code">Postal/ZIP Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($factory['postal_code']); ?>" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($factory['country']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="map">Factory Location on Map <span class="text-danger">*</span></label>
                        <p class="text-muted small">Click on the map to set the factory location</p>
                        <div id="map" style="height: 400px;"></div>
                        <div class="form-row mt-2">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Latitude" value="<?php echo htmlspecialchars($factory['latitude'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Longitude" value="<?php echo htmlspecialchars($factory['longitude'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="col-md-12">
                                <button type="button" id="search-location" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-search"></i> Search by Address
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="price">Price <span class="text-danger">*</span> ($)</label>
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo htmlspecialchars($factory['price']); ?>" required>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="area">Area <span class="text-danger">*</span> (sq m)</label>
                            <input type="number" class="form-control" id="area" name="area" min="0" step="0.01" value="<?php echo htmlspecialchars($factory['area']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contact_info">Contact Information</label>
                        <textarea class="form-control" id="contact_info" name="contact_info" rows="3"><?php echo htmlspecialchars($factory['contact_info']); ?></textarea>
                        <small class="form-text text-muted">Optional: Add specific contact info for this factory if different from company contact.</small>
                    </div>
                    
                    <!-- Existing Images -->
                    <?php if (count($factoryImages) > 0): ?>
                    <div class="form-group">
                        <label>Existing Images</label>
                        <div class="row">
                            <?php foreach ($factoryImages as $image): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card">
                                    <img src="../uploads/factories/<?php echo $image->getImagePath(); ?>" class="card-img-top" alt="Factory Image" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2 text-center">
                                        <div class="custom-control custom-radio mb-2">
                                            <input type="radio" id="existing_main_image_<?php echo $image->getId(); ?>" name="existing_main_image" value="<?php echo $image->getId(); ?>" class="custom-control-input" <?php echo $image->isMain() ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="existing_main_image_<?php echo $image->getId(); ?>">Main Image</label>
                                        </div>
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="delete_image_<?php echo $image->getId(); ?>" name="delete_images[]" value="<?php echo $image->getId(); ?>" class="custom-control-input">
                                            <label class="custom-control-label" for="delete_image_<?php echo $image->getId(); ?>">Delete</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Add New Images -->
                    <div class="form-group">
                        <label>Add New Images</label>
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" id="images" name="images[]" multiple accept="image/*">
                            <label class="custom-file-label" for="images">Choose files...</label>
                        </div>
                        <small class="form-text text-muted">You can select multiple new images to add.</small>
                        
                        <div id="imagePreview" class="row mt-3"></div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">Factory Details</div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="type">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="type" name="type" required>
                                    <option value="sale" <?php echo $factory['type'] == 'sale' ? 'selected' : ''; ?>>For Sale</option>
                                    <option value="rent" <?php echo $factory['type'] == 'rent' ? 'selected' : ''; ?>>For Rent</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="available" <?php echo $factory['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="pending" <?php echo $factory['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="sold" <?php echo $factory['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="featured" name="featured" <?php echo $factory['featured'] ? 'checked' : ''; ?>>
                                    <label class="custom-control-label" for="featured">Featured Property</label>
                                </div>
                                <small class="form-text text-muted">Featured properties appear at the top of listings.</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="categories">Categories</label>
                                <select class="form-control" id="categories" name="categories[]" multiple>
                                    <?php foreach ($allCategories as $category): ?>
                                        <option value="<?php echo $category->getId(); ?>" <?php echo in_array($category->getId(), $selectedCategoryIds) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">Hold down the Ctrl (Windows) or Command (Mac) button to select multiple options.</small>
                            </div>
                            
                            <hr>
                            
                            <div class="form-group text-right mb-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Factory
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Display image previews when files are selected
document.getElementById('images').addEventListener('change', function(event) {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    
    if (this.files) {
        for (let i = 0; i < this.files.length; i++) {
            const file = this.files[i];
            
            if (file.type.match('image.*')) {
                const reader = new FileReader();
                const imageDiv = document.createElement('div');
                imageDiv.className = 'col-md-3 mb-3';
                
                reader.onload = function(e) {
                    const html = `
                        <div class="card">
                            <img src="${e.target.result}" class="card-img-top" alt="Factory Image" style="height: 150px; object-fit: cover;">
                            <div class="card-body p-2 text-center">
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="main_image_${i}" name="main_image" value="${i}" class="custom-control-input">
                                    <label class="custom-control-label" for="main_image_${i}">Make Main</label>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    imageDiv.innerHTML = html;
                    preview.appendChild(imageDiv);
                }
                
                reader.readAsDataURL(file);
            }
        }
        
        // Update the file input label with number of files selected
        const label = this.nextElementSibling;
        label.textContent = this.files.length + ' files selected';
    }
});
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
// Initialize the map
let map = L.map('map').setView([35.2226, 38.4452], 7); // Default center on Syria
let marker;

// Add OpenStreetMap tile layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

// Handle click on map
map.on('click', function(e) {
    setLocationMarker(e.latlng.lat, e.latlng.lng);
});

// Set marker at specified location
function setLocationMarker(lat, lng) {
    // Update form fields
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
    
    // Remove existing marker if any
    if (marker) {
        map.removeLayer(marker);
    }
    
    // Add new marker
    marker = L.marker([lat, lng]).addTo(map);
    marker.bindPopup("Factory Location").openPopup();
}

// Handle search by address button
document.getElementById('search-location').addEventListener('click', function() {
    // Get address components
    const street = document.getElementById('street_address').value;
    const city = document.getElementById('city').value;
    const state = document.getElementById('state_province').value;
    const postalCode = document.getElementById('postal_code').value;
    const country = document.getElementById('country').value;
    
    // Format search query
    const query = `${street}, ${city}, ${state}, ${postalCode}, ${country}`;
    
    // Use Nominatim to search for coordinates
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lon = parseFloat(data[0].lon);
                
                // Center map on location
                map.setView([lat, lon], 14);
                
                // Set marker and form values
                setLocationMarker(lat, lon);
            } else {
                alert('Location not found. Please try a different address or click directly on the map.');
            }
        })
        .catch(error => {
            console.error('Error searching for address:', error);
            alert('Error searching for location. Please try again or click directly on the map.');
        });
});

// Handle pre-filled coordinates
window.addEventListener('load', function() {
    // Fix for map rendering issues - force a resize after the page loads
    setTimeout(function() {
        map.invalidateSize();
        
        const latitude = document.getElementById('latitude').value;
        const longitude = document.getElementById('longitude').value;
        
        if (latitude && longitude) {
            const lat = parseFloat(latitude);
            const lng = parseFloat(longitude);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                map.setView([lat, lng], 14);
                setLocationMarker(lat, lng);
            }
        }
    }, 100);
});
</script>

<?php include 'includes/footer.php'; ?>