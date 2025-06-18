<?php
session_start();
ob_start();
header('Content-Type: application/json');

require '../../include/config.php';


//validation form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && $quantity > 0 && $quantity <= $product['product_stock']) {
        $item = [
            'product_id' => $product_id,
            'product_name' => $product['product_name'],
            'quantity' => $quantity,
            'price' => $product['product_price']
        ];

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $found = false;
        foreach ($_SESSION['cart'] as &$cart_item) {
            if ($cart_item['product_id'] == $product_id) {
                $cart_item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        unset($cart_item);

        if (!$found) {
            $_SESSION['cart'][] = $item;
        }
    }
} 