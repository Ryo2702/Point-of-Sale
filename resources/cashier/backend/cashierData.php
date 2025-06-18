<?php


// Pagination variables
$products_per_page = 10; // Number of products per page
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $products_per_page;

// Get total number of products for pagination
$total_stmt = $conn->query("SELECT COUNT(*) FROM products");
$total_products = $total_stmt->fetchColumn();
$total_pages = ceil($total_products / $products_per_page);

//display all the products and add the pagination
try {
    $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_name ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
