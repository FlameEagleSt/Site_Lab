<?php
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('addQuery')) {
    function addQuery($url, $query) {
        return $url . (str_contains($url, '?') ? '&' : '?') . $query;
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

$user = $currentUser ?? null;
$baseUrl = $baseUrl ?? '';
$homeUrl = $homeUrl ?? '/';
$currentBaseUrl = $currentBaseUrl ?? $homeUrl;
$cartViewUrl = $cartViewUrl ?? $homeUrl;
$cartEndpoint = $cartEndpoint ?? '';
$authLogoutUrl = $authLogoutUrl ?? '';
$cartItems = $cartItems ?? [];
$cartTotal = $cartTotal ?? 0;
$bodyOverflow = $bodyOverflow ?? 'auto';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Городские руки</title>
    <link rel="stylesheet" type="text/css" href="<?= $baseUrl ?>/dist/css/styles.css" />
    <style>
        .flash-message {
            position: fixed;
            top: 12px;
            right: 12px;
            max-width: 420px;
            padding: 12px 16px;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 10px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            z-index: 2000;
        }
        details.profile-details summary { list-style: none; }
        details.profile-details summary::-webkit-details-marker { display: none; }
        details.profile-details[open] .profile-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        details.profile-details summary { cursor: pointer; }

        details.cart-details summary { list-style: none; }
        details.cart-details summary::-webkit-details-marker { display: none; }
        .cart-header-link {
            color: #3C352E;
            text-decoration: none;
            font-size: 14px;
        }
        .cart-inline-form { display: inline-block; margin: 0; }
        .cart-inline-full { width: 100%; margin: 0; }
        textarea.form-input { min-height: 120px; resize: vertical; }
    </style>
</head>
<body style="overflow: <?= $bodyOverflow ?>;">
    <?php if (!empty($flashMessage)): ?>
        <div class="flash-message"><?= e($flashMessage) ?></div>
    <?php endif; ?>

    <header>
        <a href="<?= $homeUrl ?>" class="logo" id="logo-link">
            <div class="logo-icon">
                <img src="<?= $baseUrl ?>/Resources/brush.png" alt="">
            </div>
            <div class="logo-text">ГОРОДСКИЕ<br>РУКИ</div>
        </a>
        <nav>
            <ul>
                <li><a href="<?= $homeUrl ?>" id="main-page-link">Главная</a></li>
                <li><a href="<?= $homeUrl ?>#top-authors" id="authors-link">Авторы</a></li>
                <li><a href="<?= $homeUrl ?>?page=about" id="about-page-link">О нас</a></li>
                <li class="cart-menu">
                    <a href="<?= $cartViewUrl ?>">Корзина</a>
                    <div class="cart-dropdown">
                        <div class="cart-header">
                            <h2>Корзина</h2>
                            <a class="cart-header-link" href="<?= $cartViewUrl ?>">Открыть страницу корзины</a>
                        </div>
                        <div class="cart-items">
                            <?php if (!$user): ?>
                                <div class="cart-empty">Сначала войдите в аккаунт</div>
                            <?php elseif (empty($cartItems)): ?>
                                <div class="cart-empty">Корзина пуста</div>
                            <?php else: ?>
                                <?php foreach ($cartItems as $item): ?>
                                    <?php $imagePath = resolveAsset($item['product_image'] ?? '', $baseUrl); ?>
                                    <div class="cart-item">
                                        <div class="cart-item-image" style="background-image: url('<?= $imagePath ?>')"></div>
                                        <div class="cart-item-info">
                                            <div class="cart-item-title"><?= e($item['product_name']) ?></div>
                                            <div class="cart-item-author">Автор: <?= e($item['author']) ?></div>
                                            <div class="cart-item-bottom">
                                                <div class="cart-item-price">
                                                    <?= number_format((float)$item['price'] * (int)$item['quantity'], 0, '.', ' ') . ' ₽' ?>
                                                </div>
                                                <div class="cart-item-right">
                                                    <div class="cart-item-controls">
                                                        <form class="cart-inline-form" action="<?= $cartEndpoint ?>" method="post">
                                                            <input type="hidden" name="method" value="decrease">
                                                            <input type="hidden" name="id" value="<?= (int)$item['product_id'] ?>">
                                                            <input type="hidden" name="redirect" value="<?= $currentBaseUrl ?>">
                                                            <button class="cart-item-btn" type="submit">-</button>
                                                        </form>
                                                        <span class="cart-item-quantity"><?= (int)$item['quantity'] ?></span>
                                                        <form class="cart-inline-form" action="<?= $cartEndpoint ?>" method="post">
                                                            <input type="hidden" name="method" value="update">
                                                            <input type="hidden" name="id" value="<?= (int)$item['product_id'] ?>">
                                                            <input type="hidden" name="redirect" value="<?= $currentBaseUrl ?>">
                                                            <button class="cart-item-btn" type="submit">+</button>
                                                        </form>
                                                    </div>
                                                    <form class="cart-inline-full" action="<?= $cartEndpoint ?>" method="post">
                                                        <input type="hidden" name="method" value="remove">
                                                        <input type="hidden" name="id" value="<?= (int)$item['product_id'] ?>">
                                                        <input type="hidden" name="redirect" value="<?= $currentBaseUrl ?>">
                                                        <button type="submit" class="cart-item-remove">Удалить</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php if ($user && !empty($cartItems)): ?>
                            <div class="cart-footer">
                                <div class="cart-total">
                                    <div class="cart-total-label">Итого</div>
                                    <div class="cart-total-price">
                                        <?= number_format((float)$cartTotal, 0, '.', ' ') . ' ₽' ?>
                                    </div>
                                </div>
                                <form action="<?= $cartEndpoint ?>" method="post">
                                    <input type="hidden" name="method" value="checkout">
                                    <input type="hidden" name="redirect" value="<?= $currentBaseUrl ?>">
                                    <button type="submit" class="cart-checkout-btn">Оформить заказ</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </li>
                <li>
                    <details class="profile-details">
                        <summary>
                            <div class="profile-btn" id="profile-btn">
                                <div class="profile-icon">
                                    <img src="<?= $baseUrl ?>/Resources/profile.png" alt="">
                                </div>
                                <?= e($profileText ?? 'Профиль') ?>
                            </div>
                        </summary>
                        <div class="profile-dropdown" id="profile-dropdown">
                            <?php if ($user): ?>
                                <a href="#" class="profile-dropdown-item" id="profile-info">Мой профиль</a>
                                <?php if ((int)($user['role'] ?? 0) === 2): ?>
                                    <a href="<?= addQuery($currentBaseUrl, 'modal=add-product') ?>" class="profile-dropdown-item">Добавить новый товар</a>
                                <?php endif; ?>
                                <a href="<?= $authLogoutUrl ?>" class="profile-dropdown-item" id="logout-link">Выйти</a>
                            <?php else: ?>
                                <a href="<?= addQuery($currentBaseUrl, 'modal=login') ?>" class="profile-dropdown-item" id="login-link">Войти</a>
                                <a href="<?= addQuery($currentBaseUrl, 'modal=register') ?>" class="profile-dropdown-item" id="register-link">Зарегистрироваться</a>
                            <?php endif; ?>
                        </div>
                    </details>
                </li>
            </ul>
        </nav>
    </header>
