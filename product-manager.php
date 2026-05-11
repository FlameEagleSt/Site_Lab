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

function redirectTo($target) {
    header('Location: ' . $target);
    exit();
}

function redirectSafe($default = 'index.php') {
    $target = postValue('redirect', $default);

    if (str_starts_with($target, 'index.php')) {
        redirectTo($target);
    }

    if (str_starts_with($target, 'product-manager-form.php')) {
        redirectTo($target);
    }

    redirectTo($default);
}

function saveImage($fieldName, $currentImage = '') {
    if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
        return $currentImage;
    }

    $fileName = basename($_FILES[$fieldName]['name']);
    $uploadDir = 'Resources/';
    $uploadPath = $uploadDir . $fileName;
    move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadPath);

    return $uploadPath;
}

$user = $_SESSION['user'] ?? null;
if (!$user || (int)($user['role'] ?? 0) !== 2) {
    $_SESSION['product_flash'] = 'Недостаточно прав';
    redirectTo('index.php');
}

$method = postValue('method', 'add');

try {
    $pdo = getPDO();

    if ($method === 'add') {
        $category = postValue('category');
        $title = postValue('title');
        $description = postValue('description');
        $author = postValue('author');
        $price = (int)postValue('price');
        $stock = (int)postValue('stock', '0');
        if ($stock < 0) { $stock = 0; }
        $uploadPath = saveImage('image');

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

        $_SESSION['product_flash'] = 'Товар успешно добавлен!';
        redirectSafe('index.php');
    }

    if ($method === 'update') {
        $id = (int)postValue('id');
        $category = postValue('category');
        $title = postValue('title');
        $description = postValue('description');
        $author = postValue('author');
        $price = (int)postValue('price');
        $stock = (int)postValue('stock', '0');
        if ($stock < 0) { $stock = 0; }
        $currentImage = postValue('current_image');
        $uploadPath = saveImage('image', $currentImage);

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

        $_SESSION['product_flash'] = 'Товар успешно изменён!';
        redirectSafe('index.php');
    }

    if ($method === 'delete') {
        $id = (int)postValue('id');

        $pdo->prepare('DELETE FROM cart WHERE product_id = :id')->execute([':id' => $id]);
        $stmt = $pdo->prepare(DELETE_PRODUCT);
        $stmt->execute([':id' => $id]);

        $_SESSION['product_flash'] = 'Товар успешно удалён!';
        redirectSafe('index.php');
    }

    $_SESSION['product_flash'] = 'Неизвестный метод';
    redirectSafe('index.php');
}
catch (PDOException $e) {
    $_SESSION['product_flash'] = $e->getMessage();
    redirectSafe('index.php');
}
?>
