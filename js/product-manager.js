$(document).ready(function() {
    $('#add-product-form').on('submit', function(e) {
        e.preventDefault();
        let isValid = true;

        const category = $('#product-category').val();
        const title = $('#product-title').val().trim();
        const description = $('#product-description').val().trim();
        const author = $('#product-author').val().trim();
        const price = parseInt($('#product-price').val(), 10);
        const imageFile = $('#product-image')[0].files[0];

        if (!category) {
            validateField($('#product-category'), 'Выберите категорию');
            isValid = false;
        } else {
            validateField($('#product-category'), '');
        }

        if (!title) {
            validateField($('#product-title'), 'Введите название товара');
            isValid = false;
        } else {
            validateField($('#product-title'), '');
        }

        if (!description) {
            validateField($('#product-description'), 'Введите описание товара');
            isValid = false;
        } else {
            validateField($('#product-description'), '');
        }

        if (!author) {
            validateField($('#product-author'), 'Введите имя автора');
            isValid = false;
        } else {
            validateField($('#product-author'), '');
        }

        if (!price || price <= 0) {
            validateField($('#product-price'), 'Введите корректную цену');
            isValid = false;
        } else {
            validateField($('#product-price'), '');
        }

        if (!isValid) return;

        const formData = new FormData();
        formData.append('method', 'add');
        formData.append('category', category);
        formData.append('title', title);
        formData.append('description', description);
        formData.append('author', author);
        formData.append('price', price);
        if (imageFile) {
            formData.append('image', imageFile);
        }

        $.ajax({
            type: 'POST',
            url: 'product-manager.php',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                alert(data.message);
                if (data.success) {
                    $('#add-product-form')[0].reset();
                    closeModal();
                    loadProducts();
                }
            },
            error: function() {
                alert('Ошибка при отправке данных на сервер!');
            }
        });
    });

    $('#edit-product-form').on('submit', function(e) {
        e.preventDefault();
        let isValid = true;

        const id = $('#edit-product-id').val();
        const category = $('#edit-product-category').val();
        const title = $('#edit-product-title').val().trim();
        const description = $('#edit-product-description').val().trim();
        const author = $('#edit-product-author').val().trim();
        const price = parseInt($('#edit-product-price').val(), 10);
        const currentImage = $('#edit-product-current-image').val();
        const imageFile = $('#edit-product-image')[0].files[0];

        if (!category) {
            validateField($('#edit-product-category'), 'Выберите категорию');
            isValid = false;
        } else {
            validateField($('#edit-product-category'), '');
        }

        if (!title) {
            validateField($('#edit-product-title'), 'Введите название товара');
            isValid = false;
        } else {
            validateField($('#edit-product-title'), '');
        }

        if (!description) {
            validateField($('#edit-product-description'), 'Введите описание товара');
            isValid = false;
        } else {
            validateField($('#edit-product-description'), '');
        }

        if (!author) {
            validateField($('#edit-product-author'), 'Введите имя автора');
            isValid = false;
        } else {
            validateField($('#edit-product-author'), '');
        }

        if (!price || price <= 0) {
            validateField($('#edit-product-price'), 'Введите корректную цену');
            isValid = false;
        } else {
            validateField($('#edit-product-price'), '');
        }

        if (!isValid) return;

        const formData = new FormData();
        formData.append('method', 'update');
        formData.append('id', id);
        formData.append('category', category);
        formData.append('title', title);
        formData.append('description', description);
        formData.append('author', author);
        formData.append('price', price);
        formData.append('current_image', currentImage);
        if (imageFile) {
            formData.append('image', imageFile);
        }

        $.ajax({
            type: 'POST',
            url: 'product-manager.php',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                alert(data.message);
                if (data.success) {
                    closeModal();
                    loadProducts();
                }
            },
            error: function() {
                alert('Ошибка при изменении товара!');
            }
        });
    });
});
