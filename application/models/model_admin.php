<?php
class Model_Admin extends Model {
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
            $stmt = $pdo->prepare("SELECT product_id, product_name, price, author, description, product_type, product_image, product_stock FROM products WHERE product_id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function addProduct($title, $description, $author, $price, $category, $uploadPath, $stock) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(LOAD_NEW_PRODUCT);
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':author' => $author,
                ':price' => $price,
                ':category' => $category,
                ':image' => $uploadPath,
                ':stock' => $stock
            ]);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function updateProduct($id, $title, $description, $author, $price, $category, $uploadPath, $stock) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(UPDATE_PRODUCT);
            $stmt->execute([
                ':id' => $id,
                ':title' => $title,
                ':description' => $description,
                ':author' => $author,
                ':price' => $price,
                ':category' => $category,
                ':image' => $uploadPath,
                ':stock' => $stock
            ]);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function deleteProduct($id) {
        $pdo = $this->get_connection();
        $pdo->prepare('DELETE FROM cart WHERE product_id = :id')->execute([':id' => $id]);
        $stmt = $pdo->prepare(DELETE_PRODUCT);
        $stmt->execute([':id' => $id]);
    }
}
?>
