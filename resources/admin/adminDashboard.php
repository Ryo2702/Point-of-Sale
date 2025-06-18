<?php

session_start();

include '../include/config.php';
include '../layout/header.php';

include '../layout/components/navbarAdmin.php';

if ($_SESSION['role'] !== 'Admin') {
    header('../auth/login.php');
    exit;
}

$productToEdit = null;

// Pagination variables
$products_per_page = 10; // Number of products per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;

// Get total number of products for pagination
$total_stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);

// Predefined categories
$predefined_categories = [
    'Fruits & Vegetables',
    'Dairy & Eggs',
    'Meat & Seafood',
    'Bakery',
    'Beverages',
    'Snacks',
    'Canned Goods',
    'Frozen Foods',
    'Pantry Staples',
    'Personal Care'
];

// Get existing categories from database
$category_stmt = $conn->query("SELECT DISTINCT product_category FROM products WHERE product_category IS NOT NULL AND product_category != '' ORDER BY product_category");
$existing_categories = $category_stmt->fetchAll(PDO::FETCH_COLUMN);

// Merge and remove duplicates
$all_categories = array_unique(array_merge($predefined_categories, $existing_categories));
sort($all_categories);

//edit
if (isset($_GET['edit'])) {
    $product_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);
    $productToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

//delete
if (isset($_GET['delete'])) {
    $product_id = intval($_GET['delete']);

    //Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
    $result = $stmt->execute([':id' => $product_id]);

    if ($result) {
        $_SESSION['message'] = "Product successfully Deleted.";
    } else {
        $_SESSION['error'] = "Failed to Delete the product.";
    }

    // Redirect to avoid resubmission and maintain current page
    header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . $current_page);
    exit;
}

//js delete functionality
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['delete_product']);

    // Debug: Check what ID we're trying to delete
    error_log("Attempting to delete product ID: " . $product_id);

    if ($product_id > 0) {  // Only delete if we have a valid ID
        // Prepare and execute the DELETE query
        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
        $result = $stmt->execute([':id' => $product_id]);

        // Debug: Check if deletion was successful
        error_log("Deletion result: " . ($result ? 'success' : 'failed'));
        error_log("Rows affected: " . $stmt->rowCount());

        // Redirect to current page after deletion
        header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . $current_page);
        exit;
    } else {
        error_log("Invalid product deletion");
    }
}

//add_products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_product'])) {
    $name = trim($_POST['product_name']);
    $price = floatval($_POST['product_price']);
    $stock = intval($_POST['product_stock']);
    $category = trim($_POST['product_category']);

    // Handle custom category input
    if ($category === 'custom' && !empty($_POST['custom_category'])) {
        $category = trim($_POST['custom_category']);
    }

    if (isset($_POST['product_id'])) {
        //UPDATE
        $id = intval($_POST['product_id']);
        $sql = "UPDATE products SET product_name = :name, product_price = :price, product_stock = :stock, product_category = :category WHERE product_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock,
            ':category' => $category
        ]);
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        //CREATE
        $sql = "INSERT INTO products (product_name, product_price, product_stock, product_category) VALUES (:name, :price, :stock, :category)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock,
            ':category' => $category
        ]);
        $_SESSION['message'] = "Product added successfully!";
    }

    header('Location: ' . $_SERVER['PHP_SELF'] . '?page=' . $current_page);
    exit;
}

?>

<main class="container mx-auto px-4">

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?= $_SESSION['message'] ?></span>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?= $_SESSION['error'] ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold">Product Management</h1>
            <p class="text-gray-600 mt-1">Manage your inventory and categories</p>
        </div>
        <button class="btn btn-primary gap-2" onclick="add_product_modal.showModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Product
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="stat bg-base-100 shadow rounded-lg">
            <div class="stat-title">Total Products</div>
            <div class="stat-value text-primary"><?= $total_products ?></div>
        </div>
        <div class="stat bg-base-100 shadow rounded-lg">
            <div class="stat-title">Categories</div>
            <div class="stat-value text-secondary"><?= count($all_categories) ?></div>
        </div>
        <div class="stat bg-base-100 shadow rounded-lg">
            <div class="stat-title">Current Page</div>
            <div class="stat-value text-accent"><?= $current_page ?> / <?= $total_pages ?></div>
        </div>
    </div>

    <!-- Products count info -->
    <div class="flex justify-end mb-4">
        <div class="text-sm text-gray-600 bg-base-200 px-3 py-1 rounded-lg">
            Showing <?= min($offset + 1, $total_products) ?>-<?= min($offset + $products_per_page, $total_products) ?> of <?= $total_products ?> products
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <dialog id="delete_confirm_modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg text-red-600">Confirm Deletion</h3>
            <p class="py-4">Are you sure you want to delete this product?</p>
            <p class="text-sm text-gray-600 mb-4">This action cannot be undone.</p>
            <div class="modal-action">
                <button id="confirm_delete_btn" class="btn btn-error">Yes, Delete</button>
                <button class="btn btn-ghost" onclick="delete_confirm_modal.close()">Cancel</button>
            </div>
        </div>
    </dialog>

    <!-- Add/Edit Product Modal -->
    <dialog id="add_product_modal" class="modal">
        <div class="modal-box max-w-2xl">
            <h2 class="font-bold text-xl mb-6 flex items-center gap-2">
                <?php if ($productToEdit): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Product
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Product
                <?php endif; ?>
            </h2>

            <form method="POST" action="" class="space-y-4">
                <?php if ($productToEdit): ?>
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($productToEdit['product_id']) ?>">
                <?php endif; ?>

                <!-- Product Name -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Product Name</span>
                    </label>
                    <input type="text" name="product_name" placeholder="Enter product name"
                        class="input input-bordered w-full" required
                        value="<?= $productToEdit ? htmlspecialchars($productToEdit['product_name']) : '' ?>">
                </div>

                <!-- Price and Stock Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Price (₱)</span>
                        </label>
                        <input type="number" step="0.01" name="product_price" placeholder="0.00"
                            class="input input-bordered w-full" required
                            value="<?= $productToEdit ? htmlspecialchars($productToEdit['product_price']) : '' ?>">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Stock Quantity</span>
                        </label>
                        <input type="number" name="product_stock" placeholder="0"
                            class="input input-bordered w-full" required
                            value="<?= $productToEdit ? htmlspecialchars($productToEdit['product_stock']) : '' ?>">
                    </div>
                </div>

                <!-- Category Selection -->
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Category</span>
                    </label>
                    <select name="product_category" id="category_select" class="select select-bordered w-full" required>
                        <option value="">Select a category</option>
                        <?php foreach ($all_categories as $cat): ?>
                            <option value="<?= htmlspecialchars($cat) ?>"
                                <?= ($productToEdit && $productToEdit['product_category'] === $cat) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat) ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="custom">+ Add New Category</option>
                    </select>
                </div>

                <!-- Custom Category Input (Hidden by default) -->
                <div class="form-control" id="custom_category_div" style="display: none;">
                    <label class="label">
                        <span class="label-text font-medium">New Category Name</span>
                    </label>
                    <input type="text" name="custom_category" id="custom_category_input"
                        placeholder="Enter new category name" class="input input-bordered w-full">
                </div>

                <!-- Action Buttons -->
                <div class="modal-action">
                    <button type="submit" class="btn btn-primary gap-2">
                        <?php if ($productToEdit): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Product
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Product
                        <?php endif; ?>
                    </button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>?page=<?= $current_page ?>" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </dialog>

    <!-- Products Table -->
    <section class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Product Name</th>
                            <th class="text-right">Price</th>
                            <th class="text-center">Stock</th>
                            <th>Category</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Modified query to include LIMIT and OFFSET for pagination
                        $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC LIMIT :limit OFFSET :offset");
                        $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
                        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $stock_class = $row['product_stock'] <= 10 ? 'text-error font-bold' : 'text-success';
                                echo "<tr>";
                                echo "<td class='text-center font-mono'>" . htmlspecialchars($row['product_id']) . "</td>";
                                echo "<td class='font-medium'>" . htmlspecialchars($row['product_name']) . "</td>";
                                echo "<td class='text-right font-mono'>₱" . number_format($row['product_price'], 2) . "</td>";
                                echo "<td class='text-center $stock_class'>" . htmlspecialchars($row['product_stock']) . "</td>";
                                echo "<td><span class='badge badge-outline'>" . htmlspecialchars($row['product_category']) . "</span></td>";
                                echo "<td class='text-center'>
                                        <div class='flex gap-2 justify-center'>
                                            <a href='?edit=" . $row['product_id'] . "&page=" . $current_page . "' 
                                               class='btn btn-sm btn-warning gap-1' title='Edit Product'>
                                                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' />
                                                </svg>
                                                Edit
                                            </a>
                                            
                                            <button class='btn btn-sm btn-error gap-1' title='Delete Product'
                                                    onclick='showDeleteConfirm(" . $row['product_id'] . ", \"" . htmlspecialchars($row['product_name']) . "\")'>
                                                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16' />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center py-8'>
                                    <div class='flex flex-col items-center gap-4'>
                                        <svg xmlns='http://www.w3.org/2000/svg' class='h-16 w-16 text-gray-400' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='1' d='M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4' />
                                        </svg>
                                        <p class='text-lg text-gray-500'>No products available</p>
                                        <button class='btn btn-primary' onclick='add_product_modal.showModal()'>Add First Product</button>
                                    </div>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php if ($total_pages > 1): ?>
                <div class="flex justify-center mt-6">
                    <div class="join">
                        <!-- First Page Button -->
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1" class="join-item btn btn-sm" title="First Page">««</a>
                            <a href="?page=<?= $current_page - 1 ?>" class="join-item btn btn-sm" title="Previous Page">‹</a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        // Adjust range if we're near the beginning or end
                        if ($end_page - $start_page < 4) {
                            if ($start_page == 1) {
                                $end_page = min($total_pages, $start_page + 4);
                            } else {
                                $start_page = max(1, $end_page - 4);
                            }
                        }

                        for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <a href="?page=<?= $i ?>"
                                class="join-item btn btn-sm <?= $i == $current_page ? 'btn-active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <!-- Next and Last Page Buttons -->
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?= $current_page + 1 ?>" class="join-item btn btn-sm" title="Next Page">›</a>
                            <a href="?page=<?= $total_pages ?>" class="join-item btn btn-sm" title="Last Page">»»</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Page Info -->
                <div class="text-center mt-4 text-sm text-gray-600">
                    Page <?= $current_page ?> of <?= $total_pages ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php if ($productToEdit): ?>
    <script>
        // Automatically show the modal when editing a product
        document.addEventListener('DOMContentLoaded', function() {
            add_product_modal.showModal();
            // Focus on the product name field
            document.querySelector('[name="product_name"]').focus();
        });
    </script>
<?php endif; ?>

<script>
    let productToDelete = null;

    // Category selection handling
    document.getElementById('category_select').addEventListener('change', function() {
        const customDiv = document.getElementById('custom_category_div');
        const customInput = document.getElementById('custom_category_input');

        if (this.value === 'custom') {
            customDiv.style.display = 'block';
            customInput.required = true;
            customInput.focus();
        } else {
            customDiv.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    });

    function showDeleteConfirm(productId, productName) {
        //Store the product info 
        productToDelete = {
            id: productId,
            name: productName
        };

        // Update the modal content with product name
        const modalContent = document.querySelector('#delete_confirm_modal p');
        modalContent.innerHTML = `Are you sure you want to delete "<strong>${productName}</strong>"?`;

        //show the confirmation
        delete_confirm_modal.showModal();
    }

    document.getElementById('confirm_delete_btn').addEventListener('click', function() {
        if (productToDelete) {
            //Create a form and submit it to delete the product
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            //add hidden input delete action
            const deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'delete_product';
            deleteInput.value = productToDelete.id;

            form.appendChild(deleteInput);
            document.body.appendChild(form);

            //submit form
            form.submit();
        }
    });

    document.getElementById("delete_confirm_modal").addEventListener('click', function(e) {
        if (e.target === this) {
            this.close();
        }
    });

    // Auto-close alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        });
    }, 5000);
</script>

<?php
include '../layout/footer.php';
?>