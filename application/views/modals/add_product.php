<?php if ($currentUser && (int)($currentUser['role'] ?? 0) === 2): ?>
    <div class="modal" id="add-product-modal" style="display: <?= $showAddProductModal ? 'block' : 'none' ?>;">
        <div class="modal-content">
            <a href="<?= $currentBaseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
            <h2 class="modal-title">Добавление товара</h2>
            <form class="modal-form" action="<?= $adminActionUrl ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="method" value="add">
                <input type="hidden" name="redirect" value="<?= $homeUrl ?>">
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
<?php endif; ?>
