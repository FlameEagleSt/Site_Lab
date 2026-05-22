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
                <?php include 'application/views/partials/products-template.php'; ?>
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
