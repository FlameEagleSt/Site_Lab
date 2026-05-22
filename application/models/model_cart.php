<?php
class Model_Cart extends Model {
    function getUserCart($email) {
        try {
            $pdo = $this->get_connection();
            $stmt = $pdo->prepare(GET_USER_CART);
            $stmt->execute([':email' => $email]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            exit($e->getMessage());
        }
    }

    function getProductStock($id) {
        $pdo = $this->get_connection();
        $stmt = $pdo->prepare(GET_PRODUCT_STOCK);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return (int)$row['product_stock'];
    }

    function getCartItemQuantity($email, $id) {
        $pdo = $this->get_connection();
        $stmt = $pdo->prepare(GET_CART_ITEM);
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return 0;
        }
        return (int)$row['quantity'];
    }

    function addToCart($email, $id) {
        $stock = $this->getProductStock($id);
        $currentQty = $this->getCartItemQuantity($email, $id);

        if ($stock !== null) {
            if ($stock <= 0) {
                return ['success' => false, 'reason' => 'out_of_stock'];
            }
            if ($currentQty >= $stock) {
                return ['success' => false, 'reason' => 'limit'];
            }
        }

        $pdo = $this->get_connection();
        if ($currentQty > 0) {
            $stmt = $pdo->prepare(UPDATE_PRODUCT_QUANTITY);
        } else {
            $stmt = $pdo->prepare(INSERT_CART_PRODUCT);
        }
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);

        return ['success' => true, 'reason' => null];
    }

    function increaseQuantity($email, $id) {
        $stock = $this->getProductStock($id);
        $currentQty = $this->getCartItemQuantity($email, $id);

        if ($stock !== null) {
            if ($stock <= 0) {
                return ['success' => false, 'reason' => 'out_of_stock'];
            }
            if ($currentQty >= $stock) {
                return ['success' => false, 'reason' => 'limit'];
            }
        }

        $pdo = $this->get_connection();
        $stmt = $pdo->prepare(UPDATE_PRODUCT_QUANTITY);
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);

        return ['success' => true, 'reason' => null];
    }

    function decreaseQuantity($email, $id) {
        $pdo = $this->get_connection();
        $stmt = $pdo->prepare(GET_CART_ITEM);
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return;
        }

        if ((int)$row['quantity'] <= 1) {
            $stmt = $pdo->prepare(DELETE_CART_PRODUCT);
        } else {
            $stmt = $pdo->prepare(DECREASE_PRODUCT_QUANTITY);
        }
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);
    }

    function removeProduct($email, $id) {
        $pdo = $this->get_connection();
        $stmt = $pdo->prepare(DELETE_CART_PRODUCT);
        $stmt->execute([
            ':email' => $email,
            ':id' => $id
        ]);
    }

    function clearCart($email) {
        $pdo = $this->get_connection();
        $stmt = $pdo->prepare(CLEAR_USER_CART);
        $stmt->execute([':email' => $email]);
    }

    function checkout($email) {
        $pdo = $this->get_connection();
        try {
            $stmt = $pdo->prepare(GET_USER_CART);
            $stmt->execute([':email' => $email]);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$products) {
                return ['success' => false, 'reason' => 'empty'];
            }

            $pdo->beginTransaction();

            foreach ($products as $product) {
                $qty = (int)$product['quantity'];
                $stockStmt = $pdo->prepare(DECREASE_PRODUCT_STOCK_BY_QTY);
                $stockStmt->execute([
                    ':id' => (int)$product['product_id'],
                    ':qty' => $qty
                ]);

                if ($stockStmt->rowCount() === 0) {
                    $pdo->rollBack();
                    return ['success' => false, 'reason' => 'insufficient'];
                }
            }

            $clearStmt = $pdo->prepare(CLEAR_USER_CART);
            $clearStmt->execute([':email' => $email]);
            $pdo->commit();

            return ['success' => true, 'reason' => null];
        }
        catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            exit($e->getMessage());
        }
    }
}
?>
