let cart = {};

function formatPrice(price) {
    return new Intl.NumberFormat('ru-RU').format(price) + '₽';
}

function updateTotal() {
    let total = 0;
    for (let id in cart) {
        total += Number(cart[id].price) * Number(cart[id].quantity);
    }
    $('#cart-total').text(formatPrice(total));
}

function showEmptyCart() {
    $('#cart-items').html('<div class="cart-empty">Корзина пуста</div>');
    updateTotal();
}

function renderCart() {
    if (!currentUser || !currentUser.email) {
        cart = {};
        showEmptyCart();
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'cart.php',
        dataType: 'json',
        data: {
            method: 'render',
            email: currentUser.email
        },
        success: function(data) {
            cart = {};
            const products = Array.isArray(data.products) ? data.products : [];

            products.forEach(product => {
                cart[product.product_id] = {
                    title: product.product_name,
                    price: Number(product.price),
                    author: product.author,
                    image: product.product_image,
                    quantity: Number(product.quantity)
                };
            });

            const $cartItems = $('#cart-items');
            $cartItems.empty();

            if (Object.keys(cart).length === 0) {
                showEmptyCart();
                return;
            }

            for (let id in cart) {
                const item = cart[id];
                const itemTotal = item.price * item.quantity;
                const quantityText = item.quantity > 1 ? ` x${item.quantity}` : '';

                const $cartItem = $(`
                    <div class="cart-item" data-id="${id}">
                        <div class="cart-item-image" style="background-image: url('${item.image}')"></div>
                        <div class="cart-item-info">
                            <div class="cart-item-title">${item.title}${quantityText}</div>
                            <div class="cart-item-author">Автор: ${item.author}</div>
                            <div class="cart-item-bottom">
                                <div class="cart-item-price">${formatPrice(itemTotal)}</div>
                                <div class="cart-item-right">
                                    <div class="cart-item-controls">
                                        <button class="cart-item-btn decrease">-</button>
                                        <span class="cart-item-quantity">${item.quantity}</span>
                                        <button class="cart-item-btn increase">+</button>
                                    </div>
                                    <button class="cart-item-remove">Удалить</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `);

                $cartItems.append($cartItem);
            }

            updateTotal();
        },
        error: function() {
            showEmptyCart();
        }
    });
}

$(document).ready(function() {
    renderCart();

    $(document).on('click', '.increase', function() {
        if (!currentUser || !currentUser.email) {
            alert('Сначала войдите в аккаунт');
            return;
        }

        const id = $(this).closest('.cart-item').data('id');
        $.ajax({
            type: 'POST',
            url: 'cart.php',
            dataType: 'json',
            data: {
                method: 'update',
                email: currentUser.email,
                id: id
            },
            success: function() {
                renderCart();
            }
        });
    });

    $(document).on('click', '.decrease', function() {
        if (!currentUser || !currentUser.email) {
            alert('Сначала войдите в аккаунт');
            return;
        }

        const id = $(this).closest('.cart-item').data('id');
        $.ajax({
            type: 'POST',
            url: 'cart.php',
            dataType: 'json',
            data: {
                method: 'decrease',
                email: currentUser.email,
                id: id
            },
            success: function() {
                renderCart();
            }
        });
    });

    $(document).on('click', '.cart-item-remove', function() {
        if (!currentUser || !currentUser.email) {
            alert('Сначала войдите в аккаунт');
            return;
        }

        const id = $(this).closest('.cart-item').data('id');
        $.ajax({
            type: 'POST',
            url: 'cart.php',
            dataType: 'json',
            data: {
                method: 'remove',
                email: currentUser.email,
                id: id
            },
            success: function() {
                renderCart();
            }
        });
    });

    $(document).on('click', '.cart-checkout-btn', function() {
        if (!currentUser || !currentUser.email) {
            alert('Сначала войдите в аккаунт');
            return;
        }

        if (Object.keys(cart).length === 0) {
            alert('Корзина пуста! Добавьте товары перед оформлением заказа.');
            return;
        }

        let totalPrice = 0;
        let totalItems = 0;
        let itemsList = [];

        for (let id in cart) {
            const item = cart[id];
            totalPrice += item.price * item.quantity;
            totalItems += item.quantity;
            itemsList.push(`• ${item.title} (${item.quantity} шт.) - ${formatPrice(item.price * item.quantity)}`);
        }

        const message = `
🎉 Спасибо за ваш заказ!

📦 Ваш заказ:
${itemsList.join('\n')}

📊 Итого: ${totalItems} товар(ов) на сумму ${formatPrice(totalPrice)}

Мы свяжемся с вами в ближайшее время для подтверждения заказа.
Ожидайте звонка от нашего менеджера!

С уважением,
команда "Городские руки" 💚
        `.trim();

        alert(message);

        $.ajax({
            type: 'POST',
            url: 'cart.php',
            dataType: 'json',
            data: {
                method: 'clear',
                email: currentUser.email
            },
            success: function() {
                renderCart();
            }
        });
    });
});
