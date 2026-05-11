<?php
session_start();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

if (!isset($_SESSION['user']['email'])) {
    $_SESSION['cart_flash'] = 'Сначала войдите в аккаунт';
    header('Location: index.php?modal=login');
    exit();
}

$flash = null;
if (!empty($_SESSION['cart_flash'])) {
    $flash = (string)$_SESSION['cart_flash'];
    unset($_SESSION['cart_flash']);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина</title>
    <link rel="stylesheet" type="text/css" href="./dist/css/styles.css" />
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
<?php if ($flash !== null): ?>
    <div class="flash-message"><?= e($flash) ?></div>
<?php endif; ?>

<div id="cart-app" class="cart-page">
    <div class="cart-page-header">
        <h2>Корзина</h2>
        <a class="cart-page-link" href="index.php">← Продолжить покупки</a>
    </div>

    <div class="cart-items">
        <div v-if="loading" class="cart-empty">Загрузка...</div>
        <div v-else-if="items.length === 0" class="cart-empty">Корзина пуста</div>

        <div v-else>
            <div class="cart-item" v-for="item in items" :key="item.product_id">
                <div class="cart-item-image" :style="{ backgroundImage: 'url(' + item.product_image + ')' }"></div>
                <div class="cart-item-info">
                    <div class="cart-item-title">{{ item.product_name }}</div>
                    <div class="cart-item-author">Автор: {{ item.author }}</div>
                    <div class="cart-item-author">В наличии: {{ item.product_stock }}</div>

                    <div class="cart-item-bottom">
                        <div class="cart-item-price">{{ formatPrice(item.price * item.quantity) }}</div>
                        <div class="cart-item-right">
                            <div class="cart-item-controls">
                                <form class="inline-form no-margin" action="cart.php" method="post">
                                    <input type="hidden" name="method" value="decrease">
                                    <input type="hidden" name="id" :value="item.product_id">
                                    <input type="hidden" name="redirect" value="cart-view.php">
                                    <button class="cart-item-btn" type="submit">-</button>
                                </form>

                                <span class="cart-item-quantity">{{ item.quantity }}</span>

                                <form class="inline-form no-margin" action="cart.php" method="post">
                                    <input type="hidden" name="method" value="update">
                                    <input type="hidden" name="id" :value="item.product_id">
                                    <input type="hidden" name="redirect" value="cart-view.php">
                                    <button class="cart-item-btn" type="submit">+</button>
                                </form>
                            </div>

                            <form class="w100 no-margin" action="cart.php" method="post">
                                <input type="hidden" name="method" value="remove">
                                <input type="hidden" name="id" :value="item.product_id">
                                <input type="hidden" name="redirect" value="cart-view.php">
                                <button type="submit" class="cart-item-remove">Удалить</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cart-footer" v-if="!loading && items.length > 0">
        <div class="cart-total">
            <div class="cart-total-label">Итого</div>
            <div class="cart-total-price">{{ formatPrice(total) }}</div>
        </div>

        <form class="no-margin" action="cart.php" method="post">
            <input type="hidden" name="method" value="checkout">
            <input type="hidden" name="redirect" value="cart-view.php">
            <button type="submit" class="cart-checkout-btn">Оформить заказ</button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script>
new Vue({
    el: '#cart-app',
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
</body>
</html>
