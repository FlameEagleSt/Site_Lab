<?php
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
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

$flash = $flashMessage ?? '';
$baseUrl = $baseUrl ?? '';
$homeUrl = $homeUrl ?? '/';
$cartEndpoint = $cartEndpoint ?? '';
$cartViewUrl = $cartViewUrl ?? $homeUrl;
$cartItems = $cartItems ?? [];
$cartTotal = $cartTotal ?? 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" type="text/css" href="<?= $baseUrl ?>/dist/css/styles.css" />
    <style>
        .cart-page { max-width: 920px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; }
        .cart-page-header { padding: 20px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; }
        .cart-page-link { color: #3C352E; text-decoration: none; }
        .flash-message { max-width: 920px; margin: 12px auto 0; padding: 12px 16px; background: #fff; border: 1px solid rgba(0,0,0,0.08); border-radius: 10px; }
        .inline-form { display: inline-block; }
        .no-margin { margin: 0; }
        .w100 { width: 100%; }
        .cart-item-right { min-width: 140px; }
    </style>
</head>
<body>
<?php if ($flash !== ''): ?>
    <div class="flash-message"><?= e($flash) ?></div>
<?php endif; ?>

<div id="cart-app" class="cart-page">
    <div class="cart-page-header">
        <h2>Корзина</h2>
        <a class="cart-page-link" href="<?= $homeUrl ?>">← Продолжить покупки</a>
    </div>

    <div class="cart-items">
        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">Корзина пуста</div>
        <?php else: ?>
            <?php foreach ($cartItems as $item): ?>
                <?php $imagePath = resolveAsset($item['product_image'] ?? '', $baseUrl); ?>
                <div class="cart-item">
                    <div class="cart-item-image" style="background-image: url('<?= $imagePath ?>')"></div>
                    <div class="cart-item-info">
                        <div class="cart-item-title"><?= e($item['product_name']) ?></div>
                        <div class="cart-item-author">Автор: <?= e($item['author']) ?></div>
                        <div class="cart-item-author">В наличии: <?= (int)$item['product_stock'] ?></div>

                        <div class="cart-item-bottom">
                            <div class="cart-item-price">
                                <?= number_format((float)$item['price'] * (int)$item['quantity'], 0, '.', ' ') . ' ₽' ?>
                            </div>
                            <div class="cart-item-right">
                                <div class="cart-item-controls">
                                    <form class="inline-form no-margin" action="<?= $cartEndpoint ?>" method="post">
                                        <input type="hidden" name="method" value="decrease">
                                        <input type="hidden" name="id" value="<?= (int)$item['product_id'] ?>">
                                        <input type="hidden" name="redirect" value="<?= $cartViewUrl ?>">
                                        <button class="cart-item-btn" type="submit">-</button>
                                    </form>

                                    <span class="cart-item-quantity"><?= (int)$item['quantity'] ?></span>

                                    <form class="inline-form no-margin" action="<?= $cartEndpoint ?>" method="post">
                                        <input type="hidden" name="method" value="update">
                                        <input type="hidden" name="id" value="<?= (int)$item['product_id'] ?>">
                                        <input type="hidden" name="redirect" value="<?= $cartViewUrl ?>">
                                        <button class="cart-item-btn" type="submit">+</button>
                                    </form>
                                </div>

                                <form class="w100 no-margin" action="<?= $cartEndpoint ?>" method="post">
                                    <input type="hidden" name="method" value="remove">
                                    <input type="hidden" name="id" value="<?= (int)$item['product_id'] ?>">
                                    <input type="hidden" name="redirect" value="<?= $cartViewUrl ?>">
                                    <button type="submit" class="cart-item-remove">Удалить</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($cartItems)): ?>
        <div class="cart-footer">
            <div class="cart-total">
                <div class="cart-total-label">Итого</div>
                <div class="cart-total-price"><?= number_format((float)$cartTotal, 0, '.', ' ') . ' ₽' ?></div>
            </div>

            <form class="no-margin" action="<?= $cartEndpoint ?>" method="post">
                <input type="hidden" name="method" value="checkout">
                <input type="hidden" name="redirect" value="<?= $cartViewUrl ?>">
                <button type="submit" class="cart-checkout-btn">Оформить заказ</button>
            </form>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
