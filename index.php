<?php
session_start();
require_once 'queries.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (isset($_SESSION['user']['name'])) {
    $profileText = $_SESSION['user']['name'] . " " . $_SESSION['user']['patronymic'];
} else {
    $profileText = "Профиль";
}

$user = $_SESSION['user'] ?? null;

$flash = null;
if (!empty($_SESSION['login_error'])) {
    $flash = (string)$_SESSION['login_error'];
    unset($_SESSION['login_error']);
} elseif (!empty($_SESSION['login_success'])) {
    $flash = (string)$_SESSION['login_success'];
    unset($_SESSION['login_success']);
} elseif (!empty($_SESSION['register_error'])) {
    $flash = (string)$_SESSION['register_error'];
    unset($_SESSION['register_error']);
} elseif (!empty($_SESSION['cart_flash'])) {
    $flash = (string)$_SESSION['cart_flash'];
    unset($_SESSION['cart_flash']);
} elseif (!empty($_SESSION['product_flash'])) {
    $flash = (string)$_SESSION['product_flash'];
    unset($_SESSION['product_flash']);
}

$page = isset($_GET['page']) ? (string)$_GET['page'] : 'main';
$modal = isset($_GET['modal']) ? (string)$_GET['modal'] : '';

$showLoginModal = ($modal === 'login');
$showRegisterModal = ($modal === 'register');
$showAddProductModal = false;
$showEditProductModal = false;
$managerCategories = [];
$editProduct = null;

if ($user && (int)($user['role'] ?? 0) === 2) {
    try {
        $pdo = new PDO('pgsql:host=localhost;dbname=dbtest', 'postgres', '56914720');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $managerCategories = $pdo->query("SELECT type_id, type_name FROM product_types ORDER BY type_id")->fetchAll(PDO::FETCH_ASSOC);

        if ($modal === 'add-product') {
            $showAddProductModal = true;
        }

        if ($modal === 'edit-product' && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            if ($id > 0) {
                $stmt = $pdo->prepare("SELECT product_id, product_name, price, author, description, product_type, product_image, product_stock FROM products WHERE product_id = :id");
                $stmt->execute([':id' => $id]);
                $editProduct = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($editProduct) {
                    $showEditProductModal = true;
                }
            }
        }
    }
    catch (PDOException $e) {
        if ($flash === null) {
            $flash = $e->getMessage();
        }
    }
}

$overlayVisible = ($showLoginModal || $showRegisterModal || $showAddProductModal || $showEditProductModal);
$bodyOverflow = $overlayVisible ? 'hidden' : 'auto';

$baseUrl = 'index.php' . ($page === 'about' ? '?page=about' : '');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Городские руки</title>
    <link rel="stylesheet" type="text/css" href="./dist/css/styles.css" />
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
    <?php if ($flash !== null): ?>
        <div class="flash-message"><?= e($flash) ?></div>
    <?php endif; ?>

    <header>
        <a href="index.php" class="logo" id="logo-link">
            <div class="logo-icon">
                <img src="Resources/brush.png" alt="">
            </div>
            <div class="logo-text">ГОРОДСКИЕ<br>РУКИ</div>
        </a>
        <nav>
            <ul>
                <li><a href="index.php" id="main-page-link">Главная</a></li>
                <li><a href="index.php#top-authors" id="authors-link">Авторы</a></li>
                <li><a href="index.php?page=about" id="about-page-link">О нас</a></li>
                <li class="cart-menu">
                    <a href="cart-view.php">Корзина</a>
                    <div class="cart-dropdown" id="cart-header-app">
                        <div class="cart-header">
                            <h2>Корзина</h2>
                            <a class="cart-header-link" href="cart-view.php">Открыть страницу корзины</a>
                        </div>
                        <div class="cart-items">
                            <?php if (!$user): ?>
                                <div class="cart-empty">Сначала войдите в аккаунт</div>
                            <?php else: ?>
                                <div v-if="loading" class="cart-empty">Загрузка...</div>
                                <div v-else-if="items.length === 0" class="cart-empty">Корзина пуста</div>
                                <div v-else>
                                    <div class="cart-item" v-for="item in items" :key="item.product_id">
                                        <div class="cart-item-image" :style="{ backgroundImage: 'url(' + item.product_image + ')' }"></div>
                                        <div class="cart-item-info">
                                            <div class="cart-item-title">{{ item.product_name }}</div>
                                            <div class="cart-item-author">Автор: {{ item.author }}</div>
                                            <div class="cart-item-bottom">
                                                <div class="cart-item-price">{{ formatPrice(item.price * item.quantity) }}</div>
                                                <div class="cart-item-right">
                                                    <div class="cart-item-controls">
                                                        <form class="cart-inline-form" action="cart.php" method="post">
                                                            <input type="hidden" name="method" value="decrease">
                                                            <input type="hidden" name="id" :value="item.product_id">
                                                            <input type="hidden" name="redirect" value="index.php">
                                                            <button class="cart-item-btn" type="submit">-</button>
                                                        </form>
                                                        <span class="cart-item-quantity">{{ item.quantity }}</span>
                                                        <form class="cart-inline-form" action="cart.php" method="post">
                                                            <input type="hidden" name="method" value="update">
                                                            <input type="hidden" name="id" :value="item.product_id">
                                                            <input type="hidden" name="redirect" value="index.php">
                                                            <button class="cart-item-btn" type="submit">+</button>
                                                        </form>
                                                    </div>
                                                    <form class="cart-inline-full" action="cart.php" method="post">
                                                        <input type="hidden" name="method" value="remove">
                                                        <input type="hidden" name="id" :value="item.product_id">
                                                        <input type="hidden" name="redirect" value="index.php">
                                                        <button type="submit" class="cart-item-remove">Удалить</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($user): ?>
                            <div class="cart-footer" v-if="!loading && items.length > 0">
                                <div class="cart-total">
                                    <div class="cart-total-label">Итого</div>
                                    <div class="cart-total-price">{{ formatPrice(total) }}</div>
                                </div>
                                <form action="cart.php" method="post">
                                    <input type="hidden" name="method" value="checkout">
                                    <input type="hidden" name="redirect" value="index.php">
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
                                    <img src="Resources/profile.png" alt="">
                                </div>
                                <?= $profileText ?>
                            </div>
                        </summary>
                        <div class="profile-dropdown" id="profile-dropdown">
                            <?php if ($user): ?>
                                <a href="#" class="profile-dropdown-item" id="profile-info">Мой профиль</a>
                                <?php if ((int)($user['role'] ?? 0) === 2): ?>
                                    <a href="<?= $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') ?>modal=add-product" class="profile-dropdown-item">Добавить новый товар</a>
                                <?php endif; ?>
                                <a href="logout.php" class="profile-dropdown-item" id="logout-link">Выйти</a>
                            <?php else: ?>
                                <a href="<?= $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') ?>modal=login" class="profile-dropdown-item" id="login-link">Войти</a>
                                <a href="<?= $baseUrl . (str_contains($baseUrl, '?') ? '&' : '?') ?>modal=register" class="profile-dropdown-item" id="register-link">Зарегистрироваться</a>
                            <?php endif; ?>
                        </div>
                    </details>
                </li>
            </ul>
        </nav>
    </header>

    <?php if ($page !== 'about'): ?>
        <div class="main-container" id="main-container">
            <aside class="sidebar" id="sidebar">
                <div class="sidebar-item">Категории</div>
                <a href="#paintings" class="sidebar-item">Картины</a>
                <a href="#ceramics" class="sidebar-item">Керамика</a>
                <a href="#jewelry" class="sidebar-item">Украшения</a>
                <a href="#textiles" class="sidebar-item">Текстиль</a>
                <a href="#gifts" class="sidebar-item">Подарки</a>
            </aside>

            <main class="content" id="main-content">
                <section class="hero" id="hero">
                    <div class="slider">
                        <div class="slide slide-1">
                            <h1>Купите искусство - сделанное<br>руками нашего города</h1>
                        </div>
                        <div class="slide slide-2">
                            <h1>Уникальные изделия<br>от местных мастеров</h1>
                        </div>
                        <div class="slide slide-3">
                            <h1>Поддержите<br>творческое сообщество</h1>
                        </div>
                        <div class="slide slide-4">
                            <h1>Качество и душа<br>в каждом изделии</h1>
                        </div>
                    </div>
                </section>

                <section id="top-authors">
                    <h2 class="section-title">Топ-авторы</h2>
                    <div class="authors-grid">
                        <a href="#" class="author-card">
                            <div class="author-avatar avatar-1"></div>
                            <div class="author-name">Любовь Степановна • Ткач</div>
                            <div class="stars">★★★★★</div>
                            <div class="reviews">Отзывы (15)</div>
                        </a>
                        <a href="#" class="author-card">
                            <div class="author-avatar avatar-2"></div>
                            <div class="author-name">Дарья • Гончар</div>
                            <div class="stars">★★★★★</div>
                            <div class="reviews">Отзывы (30)</div>
                        </a>
                        <a href="#" class="author-card">
                            <div class="author-avatar avatar-3"></div>
                            <div class="author-name">Юлия • Украшения</div>
                            <div class="stars">★★★★★</div>
                            <div class="reviews">Отзывы (19)</div>
                        </a>
                        <a href="#" class="author-card">
                            <div class="author-avatar avatar-4"></div>
                            <div class="author-name">Александр • Художник</div>
                            <div class="stars">★★★★☆</div>
                            <div class="reviews">Отзывы (38)</div>
                        </a>
                        <a href="#" class="author-card">
                            <div class="author-avatar avatar-5"></div>
                            <div class="author-name">София • Художник</div>
                            <div class="stars">★★★★☆</div>
                            <div class="reviews">Отзывы (28)</div>
                        </a>
                    </div>
                </section>

                <div class="product-container">
                    <?php include 'products-template.php'; ?>
                </div>
            </main>
        </div>
    <?php else: ?>
        <div class="about-page" id="about-page">
            <div class="about-hero">
                <h1 class="about-hero-title">ГОРОДСКИЕ РУКИ</h1>
                <p class="about-hero-subtitle">Локальное искусство — глобальная ценность</p>
            </div>

            <div class="about-text-section">
                <p class="about-paragraph">
                    <strong>Городские руки</strong> — это не просто магазин. Это сообщество.
                </p>

                <p class="about-paragraph">
                    Мы создали эту платформу, потому что верим: настоящее искусство рождается в руках тех, кто рядом. В студенческих мастерских, в домашних углах с мольбертом, в гаражах, превращённых в керамические студии, — повсюду в нашем городе живут талантливые люди, создающие уникальные вещи с душой и смыслом.
                </p>

                <p class="about-paragraph">
                    Но часто их работы остаются незамеченными. Мы решили это изменить.
                </p>

                <p class="about-paragraph">
                    <strong>Городские руки</strong> — мост между творцами и теми, кто ценит <em>искренность, ручную работу и историю</em> за каждой вещью. Здесь каждый художник, ремесленник или дизайнер может представить свои работы без посредников, а вы — найти не просто товар, а <em>историю, сделанную в вашем городе</em>.
                </p>

                <p class="about-paragraph about-cta">
                    Присоединяйтесь к нашему сообществу.<br>
                    Покупайте с душой. Поддерживайте своё.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>О КОМПАНИИ</h3>
                <p>Городские руки — сообщество платформы, объединяющая местных мастеров и ценителей ручной работы. Мы создаём мост между творцами и всеми, кто любит всегда натуральные товары ремесла.</p>
            </div>
            <div class="footer-section">
                <h3>КОНТАКТЫ</h3>
                <p><img src="Resources/telephone.png" alt=""> +7 (800) 123-45-67</p>
                <p><img src="Resources/email.png" alt=""> <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="89fcfbebe8e7e1e8e7edfac9e1e8e7edfaa7fbfc">[email&#160;protected]</a></p>
                <p><img src="Resources/vk.png" alt=""> @urbonhands</p>
                <p><img src="Resources/telega.png" alt=""> Городские руки</p>
            </div>
            <div class="footer-section">
                <h3>АДРЕС</h3>
                <p>Россия, ул. Пятницкая, д. 8</p>
                <p>Пн-Пт: 09:00 - 20:00</p>
                <p>Сб-Вс: 10:00 - 20:00</p>
            </div>
        </div>
    </footer>

    <?php if ($overlayVisible): ?>
        <a class="modal-overlay" id="modal-overlay" href="<?= $baseUrl ?>" style="display: block;"></a>
    <?php endif; ?>

    <div class="modal" id="login-modal" style="display: <?= $showLoginModal ? 'block' : 'none' ?>;">
        <div class="modal-content">
            <a href="<?= $baseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
            <h2 class="modal-title">Войдите</h2>
            <form class="modal-form" id="login-form" action="login.php" method="post">
                <div class="form-group">
                    <label>Почта <span class="required">*</span></label>
                    <input type="email" class="form-input" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Пароль <span class="required">*</span></label>
                    <input type="password" class="form-input" id="login-password" name="password" required>
                </div>
                <button type="submit" class="form-submit">Войти</button>
            </form>
        </div>
    </div>

    <div class="modal" id="register-modal" style="display: <?= $showRegisterModal ? 'block' : 'none' ?>;">
        <div class="modal-content">
            <a href="<?= $baseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
            <h2 class="modal-title">Зарегистрируйтесь</h2>
            <form class="modal-form" id="register-form" action="register.php" method="post">
                <div class="form-group">
                    <label>Фамилия <span class="required">*</span></label>
                    <input type="text" class="form-input" id="register-surname" name="surname" required>
                </div>
                <div class="form-group">
                    <label>Имя <span class="required">*</span></label>
                    <input type="text" class="form-input" id="register-name" name="name" required>
                </div>
                <div class="form-group">
                    <label>Отчество</label>
                    <input type="text" class="form-input" id="register-patronymic" name="patronymic">
                </div>
                <div class="form-group">
                    <label>Почта <span class="required">*</span></label>
                    <input type="email" class="form-input" id="register-email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Пароль <span class="required">*</span></label>
                    <input type="password" class="form-input" id="register-password" name="password" required>
                </div>
                <button type="submit" class="form-submit">Зарегистрироваться</button>
            </form>
        </div>
    </div>

    <?php if ($user && (int)($user['role'] ?? 0) === 2): ?>
        <div class="modal" id="add-product-modal" style="display: <?= $showAddProductModal ? 'block' : 'none' ?>;">
            <div class="modal-content">
                <a href="<?= $baseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
                <h2 class="modal-title">Добавление товара</h2>
                <form class="modal-form" action="product-manager.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="method" value="add">
                    <input type="hidden" name="redirect" value="index.php">
                    <div class="form-group">
                        <label>Категория <span class="required">*</span></label>
                        <select class="form-input" name="category" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($managerCategories as $category): ?>
                                <option value="<?= e($category['type_name']) ?>"><?= e($category['type_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Название товара <span class="required">*</span></label>
                        <input type="text" class="form-input" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Описание <span class="required">*</span></label>
                        <textarea class="form-input" name="description" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Автор <span class="required">*</span></label>
                        <input type="text" class="form-input" name="author" required>
                    </div>
                    <div class="form-group">
                        <label>Цена <span class="required">*</span></label>
                        <input type="number" class="form-input" name="price" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Количество в наличии <span class="required">*</span></label>
                        <input type="number" class="form-input" name="stock" min="0" value="0" required>
                    </div>
                    <div class="form-group">
                        <label>Изображение <span class="required">*</span></label>
                        <input type="file" class="form-input" name="image" accept="image/*" required>
                    </div>
                    <button type="submit" class="form-submit">Добавить товар</button>
                </form>
            </div>
        </div>

        <div class="modal" id="edit-product-modal" style="display: <?= $showEditProductModal ? 'block' : 'none' ?>;">
            <div class="modal-content">
                <a href="<?= $baseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
                <h2 class="modal-title">Редактирование товара</h2>
                <?php if ($editProduct): ?>
                    <form class="modal-form" action="product-manager.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="method" value="update">
                        <input type="hidden" name="id" value="<?= (int)$editProduct['product_id'] ?>">
                        <input type="hidden" name="current_image" value="<?= e($editProduct['product_image']) ?>">
                        <input type="hidden" name="redirect" value="index.php">
                        <div class="form-group">
                            <label>Категория <span class="required">*</span></label>
                            <select class="form-input" name="category" required>
                                <option value="">Выберите категорию</option>
                                <?php foreach ($managerCategories as $category): ?>
                                    <?php $selected = ((int)$editProduct['product_type'] === (int)$category['type_id']) ? 'selected' : ''; ?>
                                    <option value="<?= e($category['type_name']) ?>" <?= $selected ?>><?= e($category['type_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Название товара <span class="required">*</span></label>
                            <input type="text" class="form-input" name="title" required value="<?= e($editProduct['product_name']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Описание <span class="required">*</span></label>
                            <textarea class="form-input" name="description" required><?= e($editProduct['description']) ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Автор <span class="required">*</span></label>
                            <input type="text" class="form-input" name="author" required value="<?= e($editProduct['author']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Цена <span class="required">*</span></label>
                            <input type="number" class="form-input" name="price" min="1" required value="<?= (int)$editProduct['price'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Количество в наличии <span class="required">*</span></label>
                            <input type="number" class="form-input" name="stock" min="0" required value="<?= (int)$editProduct['product_stock'] ?>">
                        </div>
                        <div class="form-group">
                            <label>Изображение</label>
                            <input type="file" class="form-input" name="image" accept="image/*">
                        </div>
                        <button type="submit" class="form-submit">Сохранить изменения</button>
                    </form>
                <?php else: ?>
                    <p>Товар не найден.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script>
        (function () {
            var flash = document.querySelector('.flash-message');
            if (!flash) {
                return;
            }
            setTimeout(function () {
                flash.style.display = 'none';
            }, 2500);
        })();
    </script>
    <?php if ($user): ?>
        <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
        <script>
        new Vue({
            el: '#cart-header-app',
            data: {
                items: [],
                loading: true
            },
            computed: {
                total: function () {
                    var sum = 0;
                    for (var i = 0; i < this.items.length; i++) {
                        sum += Number(this.items[i].price) * Number(this.items[i].quantity);
                    }
                    return sum;
                }
            },
            methods: {
                formatPrice: function (value) {
                    return new Intl.NumberFormat('ru-RU').format(Number(value)) + ' ₽';
                },
                loadCart: function () {
                    var vm = this;
                    fetch('cart.php?format=json')
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            vm.items = Array.isArray(data.products) ? data.products : [];
                            vm.loading = false;
                        })
                        .catch(function () {
                            vm.items = [];
                            vm.loading = false;
                        });
                }
            },
            mounted: function () {
                this.loadCart();
            }
        });
        </script>
    <?php endif; ?>
</body>
</html>
