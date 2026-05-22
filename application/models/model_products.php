<?php
class Model_Products extends Model {
    function getAllProducts() {
        try {
            $pdo = $this->get_connection();
            return $pdo->query(GET_ALL_PRODUCTS)->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function getCategories() {
        try {
            $pdo = $this->get_connection();
            return $pdo->query("SELECT type_id, type_name FROM product_types ORDER BY type_id")->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function getProductById($id) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare("SELECT product_id, product_name, price, author, description, product_type, product_image, product_stock
                FROM products WHERE product_id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }
}
?>
