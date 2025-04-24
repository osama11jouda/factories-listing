<?php 
include 'includes/header.php';

// Initialize variables
$success = false;
$error = '';
$factory = [
    'title' => '',
    'description' => '',
    'location' => '',
    'price' => '',
    'area' => '',
    'type' => 'sale',
    'status' => 'available',
    'featured' => 0,
    'contact_info' => ''
];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $factory['title'] = mysqli_real_escape_string($conn, $_POST['title']);
    $factory['description'] = mysqli_real_escape_string($conn, $_POST['description']);
    $factory['location'] = mysqli_real_escape_string($conn, $_POST['location']);
    $factory['price'] = floatval($_POST['price']);
    $factory['area'] = floatval($_POST['area']);
    $factory['type'] = mysqli_real_escape_string($conn, $_POST['type']);
    $factory['status'] = mysqli_real_escape_string($conn, $_POST['status']);
    $factory['featured'] = isset($_POST['featured']) ? 1 : 0;
    $factory['contact_info'] = mysqli_real_escape_string($conn, $_POST['contact_info']);
    
    // Validate input
    if (empty($factory['title']) || empty($factory['description']) || empty($factory['location'])) {
        $error = "Please fill all required fields.";
    } elseif ($factory['price'] <= 0) {
        $error = "Price must be greater than zero.";
    } elseif ($factory['area'] <= 0) {
        $error = "Area must be greater than zero.";
    } else {
        // Insert factory into database
        $query = "INSERT INTO factories (title, description, location, price, area, type, status, featured, contact_info, date_added) 
                  VALUES ('{$factory['title']}', '{$factory['description']}', '{$factory['location']}', 
                  {$factory['price']}, {$factory['area']}, '{$factory['type']}', '{$factory['status']}', 
                  {$factory['featured']}, '{$factory['contact_info']}', NOW())";
                  
        if (mysqli_query($conn, $query)) {
            $factoryId = mysqli_insert_id($conn);
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
                            // Insert image info into database
                            $isMain = ($mainImage == $key) ? 1 : 0;
                            $imgQuery = "INSERT INTO factory_images (factory_id, image_path, is_main) 
                                        VALUES ($factoryId, '$newFilename', $isMain)";
                            mysqli_query($conn, $imgQuery);
                        }
                    }
                }
            }
            
            // Reset form after successful submission
            $factory = [
                'title' => '',
                'description' => '',
                'location' => '',
                'price' => '',
                'area' => '',
                'type' => 'sale',
                'status' => 'available',
                'featured' => 0,
                'contact_info' => ''
            ];
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Add New Factory</h1>
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
        <strong>Success!</strong> Factory has been added successfully.
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
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
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
                        <label for="location">Location <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" value="<?php echo htmlspecialchars($factory['location']); ?>" required>
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
                    
                    <div class="form-group">
                        <label>Factory Images</label>
                        <div class="custom-file mb-3">
                            <input type="file" class="custom-file-input" id="images" name="images[]" multiple accept="image/*">
                            <label class="custom-file-label" for="images">Choose files...</label>
                        </div>
                        <small class="form-text text-muted">You can select multiple images. The first image will be used as the main image.</small>
                        
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
                            
                            <hr>
                            
                            <div class="form-group text-right mb-0">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Factory
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
                                    <input type="radio" id="main_image_${i}" name="main_image" value="${i}" class="custom-control-input" ${i === 0 ? 'checked' : ''}>
                                    <label class="custom-control-label" for="main_image_${i}">Main Image</label>
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

<?php include 'includes/footer.php'; ?>