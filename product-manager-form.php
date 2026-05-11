<?php
session_start();
require_once 'queries.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirectTo($target) {
    header('Location: ' . $target);
    exit();
}

$user = $_SESSION['user'] ?? null;
if (!$user || (int)($user['role'] ?? 0) !== 2) {
    $_SESSION['product_flash'] = 'Недостаточно прав';
    redirectTo('index.php');
}

$pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$categories = $pdo->query("SELECT type_id, type_name FROM product_types ORDER BY type_id")->fetchAll(PDO::FETCH_ASSOC);

$isEdit = isset($_GET['id']) && $_GET['id'] !== '';
$product = null;

if ($isEdit) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT product_id, product_name, price, author, description, product_type, product_image, product_stock FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['product_flash'] = 'Товар не найден';
        redirectTo('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Редактирование товара' : 'Добавление товара' ?></title>
    <link rel="stylesheet" type="text/css" href="./dist/css/styles.css" />
    <style>
        .manager-container { max-width: 760px; margin: 30px auto; padding: 24px; background: #fff; border-radius: 12px; }
        .manager-title { margin-bottom: 24px; }
        .manager-actions { margin-top: 16px; display: flex; gap: 12px; }
        .manager-link { display: inline-block; padding: 14px 30px; border-radius: 8px; text-decoration: none; background: #ddd; color: #3C352E; }
        textarea.form-input { min-height: 120px; resize: vertical; }
    </style>
</head>
<body>
<div class="manager-container">
    <h1 class="manager-title"><?= $isEdit ? 'Редактирование товара' : 'Добавление товара' ?></h1>

    <form class="modal-form" action="product-manager.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="method" value="<?= $isEdit ? 'update' : 'add' ?>">
        <input type="hidden" name="redirect" value="index.php">

        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= (int)$product['product_id'] ?>">
            <input type="hidden" name="current_image" value="<?= e($product['product_image']) ?>">
        <?php endif; ?>

        <div class="form-group">
            <label>Категория <span class="required">*</span></label>
            <select class="form-input" name="category" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <?php $selected = ($isEdit && (int)$product['product_type'] === (int)$category['type_id']) ? 'selected' : ''; ?>
                    <option value="<?= e($category['type_name']) ?>" <?= $selected ?>><?= e($category['type_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Название товара <span class="required">*</span></label>
            <input class="form-input" type="text" name="title" required value="<?= $isEdit ? e($product['product_name']) : '' ?>">
        </div>

        <div class="form-group">
            <label>Описание <span class="required">*</span></label>
            <textarea class="form-input" name="description" required><?= $isEdit ? e($product['description']) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label>Автор <span class="required">*</span></label>
            <input class="form-input" type="text" name="author" required value="<?= $isEdit ? e($product['author']) : '' ?>">
        </div>

        <div class="form-group">
            <label>Цена <span class="required">*</span></label>
            <input class="form-input" type="number" name="price" min="1" required value="<?= $isEdit ? (int)$product['price'] : '' ?>">
        </div>

        <div class="form-group">
            <label>Количество в наличии <span class="required">*</span></label>
            <input class="form-input" type="number" name="stock" min="0" required value="<?= $isEdit ? (int)$product['product_stock'] : '0' ?>">
        </div>

        <div class="form-group">
            <label>Изображение <?= $isEdit ? '' : '<span class="required">*</span>' ?></label>
            <input class="form-input" type="file" name="image" accept="image/*" <?= $isEdit ? '' : 'required' ?>>
        </div>

        <div class="manager-actions">
            <button type="submit" class="form-submit"><?= $isEdit ? 'Сохранить изменения' : 'Добавить товар' ?></button>
            <a class="manager-link" href="index.php">Отмена</a>
        </div>
    </form>
</div>
</body>
</html>
