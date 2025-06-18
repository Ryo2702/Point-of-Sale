<?php
session_start();
include '../include/config.php';
include '../layout/header.php';

//backend
require './backend/cashierData.php';
// require './backend/add_to_cart.php';
?>

<div class="max-w-7xl mx-auto px-1 py-6">
    <h1 class="text-3xl font-bold mb-6 text-center">Cashier Panel</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Left: Product Table (2/3 of width) -->
        <div class="md:col-span-2 overflow-x-auto">
            <h2 class="text-xl font-semibold mb-4">Product Table</h2>
            <table class="table w-full table-zebra hover:table-hover">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Price</th>
                        <th>Add</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $index => $product): ?>
                            <tr>
                                <td><?= $index + 1; ?></td>
                                <td><?= htmlspecialchars($product['product_name']); ?></td>
                                <td><?= htmlspecialchars($product['product_category']); ?></td>
                                <td><?= (int)$product['product_stock']; ?></td>
                                <td>₱<?= number_format($product['product_price'], 2); ?></td>
                                <td>
                                    <form class="add-to-cart-form" data-product-id="<?= $product['product_id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="<?= $product['product_stock']; ?>" class="quantity-input w-16 border rounded px-1 text-sm">
                                        <button type="submit" class="btn btn-sm btn-success ml-2">Add</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-4">No products found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

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



        <!-- Right: Cart Panel -->
        <div id="cart-panel" class="bg-gray-50 border border-gray-200 rounded-lg p-4 shadow-md">
            <h2 class="text-xl font-semibold mb-4">Cart</h2>

            <?php if (!empty($_SESSION['cart'])): ?>
                <ul class="space-y-2">
                    <?php foreach ($_SESSION['cart'] as $item): ?>
                        <li class="flex justify-between items-center border-b pb-2">
                            <span><?= htmlspecialchars($item['product_name']) ?> (x<?= $item['quantity'] ?>)</span>
                            <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="mt-4 font-semibold text-right">
                    Total: ₱<?= number_format(array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $_SESSION['cart'])), 2) ?>
                </div>
                <div class="mt-4 text-center">
                    <a href="checkout.php" class="btn btn-primary btn-sm w-full">Checkout</a>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Your cart is empty.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    document.querySelectorAll('.add-to-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop normal form submission

            const productId = this.getAttribute('data-product-id');
            const quantity = this.querySelector('.quantity-input').value;

            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);

            fetch('./backend/add_to_cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    console.log('Response: ' + data);
                    

                    if (data.success) {
                        // Optional: Update cart panel
                        fetch('./backend/fetch_cart.php')
                            .then(res => res.text())
                            .then(html => {
                                document.getElementById('cart-panel').innerHTML = html;
                            });
                    } else {
                        alert(data.message);
                    }
                })
                .catch(err => console.error('Add to cart error:', err));
        });
    });
</script>

<?php
include '../layout/footer.php';
?>