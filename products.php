<?php
require_once 'queries.php';
header('Content-Type: application/json');
try {
    $pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $products = $pdo->query(GET_ALL_PRODUCTS)->fetchAll(PDO::FETCH_ASSOC);

    $categories = $pdo->query("SELECT type_id, type_name FROM product_types")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'products' => $products,
        'categories' => $categories
    ]);

}
catch (PDOException $e) {
    exit($e->getMessage());
}
?>