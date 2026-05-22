<?php if ($currentUser && (int)($currentUser['role'] ?? 0) === 2): ?>
    <div class="modal" id="edit-product-modal" style="display: <?= $showEditProductModal ? 'block' : 'none' ?>;">
        <div class="modal-content">
            <a href="<?= $currentBaseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
            <h2 class="modal-title">Редактирование товара</h2>
            <?php if ($editProduct): ?>
                <form class="modal-form" action="<?= $adminActionUrl ?>" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="method" value="update">
                    <input type="hidden" name="id" value="<?= (int)$editProduct['product_id'] ?>">
                    <input type="hidden" name="current_image" value="<?= e($editProduct['product_image']) ?>">
                    <input type="hidden" name="redirect" value="<?= $homeUrl ?>">
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
