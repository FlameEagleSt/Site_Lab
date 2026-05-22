<?php
$baseUrl = $baseUrl ?? '';
$homeUrl = $homeUrl ?? '/';
$currentBaseUrl = $currentBaseUrl ?? $homeUrl;
$cartEndpoint = $cartEndpoint ?? '';
$user = $currentUser ?? null;
$overlayVisible = $overlayVisible ?? false;
$showLoginModal = $showLoginModal ?? false;
$showRegisterModal = $showRegisterModal ?? false;
$showAddProductModal = $showAddProductModal ?? false;
$showEditProductModal = $showEditProductModal ?? false;
$managerCategories = $managerCategories ?? [];
$editProduct = $editProduct ?? null;
$adminActionUrl = $adminActionUrl ?? '';
$homeUrl = $homeUrl ?? '/';
?>
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>О КОМПАНИИ</h3>
                <p>Городские руки — сообщество платформы, объединяющая местных мастеров и ценителей ручной работы. Мы создаём мост между творцами и всеми, кто любит всегда натуральные товары ремесла.</p>
            </div>
            <div class="footer-section">
                <h3>КОНТАКТЫ</h3>
                <p><img src="<?= $baseUrl ?>/Resources/telephone.png" alt=""> +7 (800) 123-45-67</p>
                <p><img src="<?= $baseUrl ?>/Resources/email.png" alt=""> <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="89fcfbebe8e7e1e8e7edfac9e1e8e7edfaa7fbfc">[email&#160;protected]</a></p>
                <p><img src="<?= $baseUrl ?>/Resources/vk.png" alt=""> @urbonhands</p>
                <p><img src="<?= $baseUrl ?>/Resources/telega.png" alt=""> Городские руки</p>
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
        <a class="modal-overlay" id="modal-overlay" href="<?= $currentBaseUrl ?>" style="display: block;"></a>
    <?php endif; ?>

    <?php include 'application/views/modals/login.php'; ?>
    <?php include 'application/views/modals/register.php'; ?>
    <?php include 'application/views/modals/add_product.php'; ?>
    <?php include 'application/views/modals/edit_product.php'; ?>

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
</body>
</html>
