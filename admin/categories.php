<?php include 'includes/header.php'; ?>
<?php require_once '../models/Category.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Categories Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addCategoryModal">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>
</div>

<?php
// Handle category deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $categoryId = intval($_GET['delete']);
    
    $category = new Category();
    if ($category->findById($categoryId)) {
        if ($category->delete()) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    Category deleted successfully.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        } else {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    Error deleting category.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
        }
    }
}

// Handle form submission for adding/editing categories
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    
    // Validate required fields
    if (empty($name)) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Category name is required.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>';
    } else {
        // Create or update category
        $category = new Category();
        if ($categoryId > 0) {
            // Update existing category
            if ($category->findById($categoryId)) {
                $category->setName($name);
                $category->setDescription($description);
                
                if ($category->update()) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Category updated successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                } else {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error updating category.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                }
            }
        } else {
            // Create new category
            $category->setName($name);
            $category->setDescription($description);
            
            // Check if a category with this name already exists
            if ($category->existsByName($name)) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        A category with the name "'.htmlspecialchars($name).'" already exists.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else if ($category->create()) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Category added successfully.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            } else {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error adding category.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
            }
        }
    }
}

// Get all categories
$categories = Category::getAll();
?>

<!-- Categories Table -->
<div class="card mb-4">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <div class="alert alert-info">
                No categories found. Start by adding a new category.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo $category->getId(); ?></td>
                                <td><?php echo htmlspecialchars($category->getName()); ?></td>
                                <td><?php echo htmlspecialchars($category->getDescription() ?? ''); ?></td>
                                <td><?php echo date('M d, Y', strtotime($category->getCreatedAt())); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-primary edit-category" 
                                                data-id="<?php echo $category->getId(); ?>"
                                                data-name="<?php echo htmlspecialchars($category->getName()); ?>"
                                                data-description="<?php echo htmlspecialchars($category->getDescription() ?? ''); ?>"
                                                data-toggle="modal" data-target="#editCategoryModal">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="javascript:void(0);" class="btn btn-danger delete-category" 
                                           data-id="<?php echo $category->getId(); ?>"
                                           data-name="<?php echo htmlspecialchars($category->getName()); ?>">
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

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add-name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="add-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="add-description">Description</label>
                        <textarea class="form-control" id="add-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" id="edit-category-id" name="category_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-description">Description</label>
                        <textarea class="form-control" id="edit-description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Category Confirmation Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category: <strong id="delete-category-name"></strong>?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" id="confirm-delete" class="btn btn-danger">Delete Category</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Edit category modal
        $('.edit-category').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');
            
            $('#edit-category-id').val(id);
            $('#edit-name').val(name);
            $('#edit-description').val(description);
        });
        
        // Delete category confirmation
        $('.delete-category').on('click', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            
            $('#delete-category-name').text(name);
            $('#confirm-delete').attr('href', 'categories.php?delete=' + id);
            $('#deleteCategoryModal').modal('show');
        });
    });
</script>

<?php include 'includes/footer.php'; ?>