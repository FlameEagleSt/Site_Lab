function attachProductHandlers() {
    $(document).off('click', '.add-to-cart').on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUser || !currentUser.email) {
            alert('Сначала войдите в аккаунт');
            return;
        }

        const button = this;
        const $card = $(button).closest('.product-card');
        const id = $card.data('id');
        const price = parseInt($card.data('price'), 10);

        $.ajax({
            type: 'POST',
            url: 'cart.php',
            dataType: 'json',
            data: {
                email: currentUser.email,
                id: id,
                method: 'insert'
            },
            success: function() {
                renderCart();
                $(button).text('Добавлено!').css('background', '#5a9e5a');
                setTimeout(() => {
                    $(button).text('В корзину за ' + formatPrice(price)).css('background', '');
                }, 1000);
            },
            error: function() {
                alert('Ошибка при отправке данных на сервер');
            }
        });
    });

    $(document).off('click', '.delete-product').on('click', '.delete-product', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUser || currentUser.role !== 'moderator') {
            return;
        }

        const id = $(this).closest('.product-card').data('id');
        if (!confirm('Удалить этот товар?')) {
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'product-manager.php',
            dataType: 'json',
            data: {
                method: 'delete',
                id: id
            },
            success: function(data) {
                alert(data.message);
                renderCart();
            },
            error: function() {
                alert('Ошибка при удалении товара');
            }
        });
    });

    $(document).off('click', '.edit-product').on('click', '.edit-product', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if (!currentUser || currentUser.role !== 'moderator') {
            return;
        }

        const $card = $(this).closest('.product-card');
        const typeId = String($card.data('type'));
        const categoryMap = {
            '1': 'Картины',
            '2': 'Керамика',
            '3': 'Украшения',
            '4': 'Текстиль',
            '5': 'Подарки'
        };

        $('#edit-product-id').val($card.data('id'));
        $('#edit-product-category').val(categoryMap[typeId] || '');
        $('#edit-product-title').val($card.data('title'));
        $('#edit-product-description').val($card.data('description'));
        $('#edit-product-author').val($card.data('author'));
        $('#edit-product-price').val($card.data('price'));
        $('#edit-product-current-image').val($card.data('image'));

        openModal('edit-product-modal');
    });
}
