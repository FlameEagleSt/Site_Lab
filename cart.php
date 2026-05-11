<?php
session_start();
require_once 'queries.php';

function getPDO() {
    $pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function postValue($key, $default = '') {
    return isset($_POST[$key]) ? htmlentities($_POST[$key]) : $default;
}

function currentUserEmail() {
    return $_SESSION['user']['email'] ?? '';
}

function redirectTo($target) {
    header('Location: ' . $target);
    exit();
}

function redirectSafe($default = 'cart-view.php') {
    $target = postValue('redirect', $default);

    if (str_starts_with($target, 'index.php')) {
        redirectTo($target);
    }

    if (str_starts_with($target, 'cart-view.php')) {
        redirectTo($target);
    }

    redirectTo($default);
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

function tryGetStock(PDO $pdo, $id) {
    try {
        $stmt = $pdo->prepare(GET_PRODUCT_STOCK);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return (int)$row['product_stock'];
    } catch (PDOException $e) {
        return null;
    }
}

$method = postValue('method');
$email = currentUserEmail();

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    $method = 'render';
}

if ($method === 'render') {
    if ($email === '') {
        jsonResponse(['products' => []]);
    }

    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare(GET_USER_CART);
        $stmt->execute([':email' => $email]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        jsonResponse(['products' => $products]);
    } catch (PDOException $e) {
        jsonResponse(['products' => [], 'message' => $e->getMessage()]);
    }
}

if ($email === '') {
    $_SESSION['cart_flash'] = 'Сначала войдите в аккаунт';
    redirectTo('index.php?modal=login');
}

$id = (int)postValue('id');

try {
    $pdo = getPDO();

    if ($method === 'insert' || $method === 'update') {
        $stock = tryGetStock($pdo, $id);
        if ($stock !== null) {
            if ($stock <= 0) {
                $_SESSION['cart_flash'] = 'Товара нет в наличии';
                redirectSafe('index.php');
            }

            $qStmt = $pdo->prepare('SELECT quantity FROM cart WHERE user_email = :email AND product_id = :id');
            $qStmt->execute([':email' => $email, ':id' => $id]);
            $qRow = $qStmt->fetch(PDO::FETCH_ASSOC);
            $currentQty = $qRow ? (int)$qRow['quantity'] : 0;

            if ($currentQty >= $stock) {
                $_SESSION['cart_flash'] = 'Нельзя добавить больше: недостаточно товара на складе';
                redirectSafe('cart-view.php');
            }
        }
    }

    if ($method === 'insert') {
        $checkStmt = $pdo->prepare('SELECT quantity FROM cart WHERE user_email = :email AND product_id = :id');
        $checkStmt->execute([':email' => $email, ':id' => $id]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            $stmt = $pdo->prepare(UPDATE_PRODUCT_QUANTITY);
            $stmt->execute([':email' => $email, ':id' => $id]);
        } else {
            $stmt = $pdo->prepare(INSERT_CART_PRODUCT);
            $stmt->execute([':email' => $email, ':id' => $id]);
        }

        $_SESSION['cart_flash'] = 'Товар добавлен в корзину';
        redirectSafe('index.php');
    }

    if ($method === 'update') {
        $stmt = $pdo->prepare(UPDATE_PRODUCT_QUANTITY);
        $stmt->execute([':email' => $email, ':id' => $id]);

        redirectSafe('cart-view.php');
    }

    if ($method === 'decrease') {
        $checkStmt = $pdo->prepare('SELECT quantity FROM cart WHERE user_email = :email AND product_id = :id');
        $checkStmt->execute([':email' => $email, ':id' => $id]);
        $product = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            redirectSafe('cart-view.php');
        }

        if ((int)$product['quantity'] <= 1) {
            $stmt = $pdo->prepare(DELETE_CART_PRODUCT);
        } else {
            $stmt = $pdo->prepare(DECREASE_PRODUCT_QUANTITY);
        }

        $stmt->execute([':email' => $email, ':id' => $id]);
        redirectSafe('cart-view.php');
    }

    if ($method === 'remove') {
        $stmt = $pdo->prepare(DELETE_CART_PRODUCT);
        $stmt->execute([':email' => $email, ':id' => $id]);
        redirectSafe('cart-view.php');
    }

    if ($method === 'clear') {
        $stmt = $pdo->prepare(CLEAR_USER_CART);
        $stmt->execute([':email' => $email]);
        redirectSafe('cart-view.php');
    }

    if ($method === 'checkout') {
        $stmt = $pdo->prepare(GET_USER_CART);
        $stmt->execute([':email' => $email]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$products) {
            $_SESSION['cart_flash'] = 'Корзина пуста';
            redirectSafe('cart-view.php');
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
                $_SESSION['cart_flash'] = 'Недостаточно товара на складе для оформления заказа';
                redirectSafe('cart-view.php');
            }
        }

        $clearStmt = $pdo->prepare(CLEAR_USER_CART);
        $clearStmt->execute([':email' => $email]);
        $pdo->commit();

        $_SESSION['cart_flash'] = 'Заказ оформлен! Спасибо за покупку';
        redirectSafe('cart-view.php');
    }

    $_SESSION['cart_flash'] = 'Неизвестный метод';
    redirectSafe('cart-view.php');
}
catch (PDOException $e) {
    $_SESSION['cart_flash'] = $e->getMessage();
    redirectSafe('cart-view.php');
}
?>
