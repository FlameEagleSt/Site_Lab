<?php
if (!function_exists('resolveAsset')) {
    function resolveAsset($path, $baseUrl) {
        if ($path === '' || $path === null) {
            return '';
        }
        if (preg_match('#^https?://#i', $path) || str_starts_with($path, '/')) {
            return $path;
        }
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}
if (!function_exists('addQuery')) {
    function addQuery($url, $query) {
        return $url . (str_contains($url, '?') ? '&' : '?') . $query;
    }
}

$user = $currentUser ?? null;
$sectionIds = $sectionIds ?? [];
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
            $imagePath = resolveAsset($product['product_image'] ?? '', $baseUrl ?? '');
        ?>
        <div class="product-card"
            data-id="<?= $product['product_id'] ?>"
            data-type="<?= $product['product_type'] ?>"
            data-title="<?= $product['product_name'] ?>"
            data-description="<?= $product['description'] ?>"
            data-price="<?= $product['price'] ?>"
            data-author="<?= $product['author'] ?>"
            data-image="<?= $imagePath ?>">

            <div class="product-image" style="background-image: url('<?= $imagePath ?>')"></div>
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
                    <form action="<?= $cartEndpoint ?>" method="post">
                        <input type="hidden" name="method" value="insert">
                        <input type="hidden" name="id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="<?= $currentBaseUrl ?>">
                        <button type="submit" class="product-button add-to-cart" <?= $isOutOfStock ? 'disabled' : '' ?>>
                            В корзину за <?= number_format($product['price'], 0, '.', ' ') . ' ₽' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="<?= addQuery($currentBaseUrl, 'modal=login') ?>" class="product-button">Войти, чтобы купить</a>
                <?php endif; ?>

                <?php if ($user && ($user['role'] ?? null) == 2): ?>
                <div class="moderator-buttons">
                    <a class="moderator-button moderator-edit" href="<?= addQuery($currentBaseUrl, 'modal=edit-product&id=' . (int)$product['product_id']) ?>">Изменить</a>
                    <form action="<?= $adminActionUrl ?>" method="post" class="moderator-form">
                        <input type="hidden" name="method" value="delete">
                        <input type="hidden" name="id" value="<?= $product['product_id'] ?>">
                        <input type="hidden" name="redirect" value="<?= $currentBaseUrl ?>">
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
