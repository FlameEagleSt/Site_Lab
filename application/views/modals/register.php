<div class="modal" id="register-modal" style="display: <?= $showRegisterModal ? 'block' : 'none' ?>;">
    <div class="modal-content">
        <a href="<?= $currentBaseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
        <h2 class="modal-title">Зарегистрируйтесь</h2>
        <form class="modal-form" id="register-form" action="<?= $authRegisterUrl ?>" method="post">
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
