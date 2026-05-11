<?php
require_once 'queries.php';
$pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$products = $pdo->query(GET_ALL_PRODUCTS)->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT type_id, type_name FROM product_types")->fetchAll(PDO::FETCH_ASSOC);

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

$sectionIds = [
    1 => 'paintings',
    2 => 'ceramics',
    3 => 'jewelry',
    4 => 'textiles',
    5 => 'gifts'
];
?>

<?php foreach ($categories as $category): ?>
<?php $sectionId = $sectionIds[(int)$category['type_id']] ?? ('category-' . (int)$category['type_id']); ?>
<section id="<?= $sectionId ?>" class="category-section">
    <h2 class="section-title"><?= $category['type_name'] ?></h2>
    <div class="products-grid" data-category="<?= $category['type_id'] ?>">
        <?php
        $productsCategory = [];
        for ($i = 0; $i < count($products); $i++) {
            if ($products[$i]['product_type'] == $category['type_id']) {
                $productsCategory[] = $products[$i];
            }
        }
        ?>

        <?php foreach ($productsCategory as $product): ?>
        <?php
            $stock = isset($product['product_stock']) ? (int)$product['product_stock'] : null;
            $isOutOfStock = ($stock !== null && $stock <= 0);
        ?>
        <div class="product-card"
            data-id="<?= $product['product_id'] ?>"
            data-type="<?= $product['product_type'] ?>"
            data-title="<?= $product['product_name'] ?>"
            data-description="<?= $product['description'] ?>"
            data-price="<?= $product['price'] ?>"
            data-author="<?= $product['author'] ?>"
            data-image="<?= $product['product_image'] ?>">

            <div class="product-image" style="background-image: url('<?= $product['product_image'] ?>')"></div>
            <div class="product-info">
                <div class="product-title"><?= $product['product_name'] ?></div>
                <div class="product-description"><?= $product['description'] ?></div>
                <div class="product-author">Автор: <?= $product['author'] ?></div>

                <?php if ($stock !== null): ?>
                    <?php if ($isOutOfStock): ?>
                        <div class="product-author">Нет в наличии</div>
                    <?php else: ?>
                        <div class="product-author">В наличии: <?= $stock ?></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($user): ?>
                    <form action="cart.php" method="post">
                        <input type="hidden" name="method" value="insert">
                        <input type="hidden" name="id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="index.php">
                        <button type="submit" class="product-button add-to-cart" <?= $isOutOfStock ? 'disabled' : '' ?>>
                            В корзину за <?= number_format($product['price'], 0, '.', ' ') . ' ₽' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="index.php?modal=login" class="product-button">Войти, чтобы купить</a>
                <?php endif; ?>

                <?php if ($user && ($user['role'] ?? null) == 2): ?>
                <div class="moderator-buttons">
                    <a class="moderator-button moderator-edit" href="index.php?modal=edit-product&id=<?= $product['product_id'] ?>">Изменить</a>
                    <form action="product-manager.php" method="post" class="moderator-form">
                        <input type="hidden" name="method" value="delete">
                        <input type="hidden" name="id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="index.php">
                        <button type="submit" class="moderator-button moderator-delete">Удалить</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
