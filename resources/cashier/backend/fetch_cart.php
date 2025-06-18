<?php
session_start();
ob_start();
?>

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

<?php
$html = ob_get_clean();
echo $html;
?>