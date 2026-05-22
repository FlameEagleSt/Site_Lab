<div class="modal" id="login-modal" style="display: <?= $showLoginModal ? 'block' : 'none' ?>;">
    <div class="modal-content">
        <a href="<?= $currentBaseUrl ?>" style="float:right; text-decoration:none; font-size:24px; line-height:24px;">×</a>
        <h2 class="modal-title">Войдите</h2>
        <form class="modal-form" id="login-form" action="<?= $authLoginUrl ?>" method="post">
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
