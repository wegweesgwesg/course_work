<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Конфигуратор ПК — bit.sp'); ?></title>
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/modal.css')); ?>">
    <?php echo $__env->yieldPushContent('styles'); ?>
    <script>
        window.CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';
        <?php if(auth()->guard()->check()): ?>
        window.AUTH_USER = <?php echo json_encode([
            'id' => Auth::user()->id,
            'email' => Auth::user()->email,
            'first_name' => Auth::user()->first_name,
            'role' => Auth::user()->role,
        ]); ?>;
        <?php else: ?>
        window.AUTH_USER = null;
        <?php endif; ?>
    </script>
</head>
<body class="<?php echo $__env->yieldContent('body-class'); ?>">

<header class="header">
    <div class="bg-header">
        <div class="container top">
            <div class="top_accout" id="account-block">
                <?php if(auth()->guard()->check()): ?>
                    <div class="top_accout account-replaced">
                        <button class="account-name-btn" id="account-name-btn"><?php echo e(Auth::user()->first_name); ?></button>
                        <div class="account-popup" id="account-popup">
                            <div class="account-popup-role">Роль: <b>
                                <?php switch(Auth::user()->role):
                                    case ('admin'): ?> Администратор <?php break; ?>
                                    <?php case ('content_manager'): ?> Контент-менеджер <?php break; ?>
                                    <?php case ('warehouse_manager'): ?> Менеджер склада <?php break; ?>
                                    <?php default: ?> Пользователь
                                <?php endswitch; ?>
                            </b></div>
                            <div class="account-popup-actions">
                                <?php if(Auth::user()->isPrivileged()): ?>
                                    <a href="<?php echo e(route('admin')); ?>" class="btn ghost" style="margin-right:4px;">Админ-панель</a>
                                <?php endif; ?>
                                <form method="POST" action="<?php echo e(route('logout')); ?>" style="display:inline;">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn ghost">Выйти</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <img src="<?php echo e(asset('img/lock.png')); ?>" alt="">
                    <a href="<?php echo e(route('login')); ?>" style="text-decoration: none; color: black;">Личный кабинет</a>
                <?php endif; ?>
            </div>
            <div class="top_search">
                <input type="text" placeholder="Поиск по каталогу" class="top_searchInput">
                <img src="<?php echo e(asset('img/search_button.png')); ?>" alt="" class="top_searchConfirm">
            </div>
            <img class="top_socialVK" src="<?php echo e(asset('img/vk.png')); ?>">
            <div class="top_contacts">
                <img src="<?php echo e(asset('img/phone.png')); ?>" alt="" class="top_socialPhone">
                <p>+7(925)047-81-12</p>
                <p>+7(496)540-44-15</p>
            </div>
        </div>
    </div>
    <div class="container center">
        <img src="<?php echo e(asset('img/logo.png')); ?>" alt="" class="center_logo">
        <div class="center_text">Интернет-магазин<br>бытовой техники и<br>электроники</div>
        <div class="center_basket">
            <img src="<?php echo e(asset('img/basket.png')); ?>" alt="">
            <p><b>Корзина</b> <br>Ваша корзина <br>пуста</p>
        </div>
        <button class="center_button">ПЕРЕЙТИ В КОРЗИНУ</button>
    </div>
    <div class="container bottom">
        <a href="#" class="bottom_element">КАТАЛОГ</a>
        <a href="#" class="bottom_element">ДОСТАВКА И ОПЛАТА</a>
        <a href="#" class="bottom_element">СЕРВИСНЫЙ ЦЕНТР</a>
        <a href="#" class="bottom_element">КОНТАКТЫ</a>
        <a href="<?php echo e(route('configurator')); ?>" class="bottom_element">КОНФИГУРАТОР</a>
    </div>
</header>

<?php echo $__env->yieldContent('content'); ?>

<div class="bg-footer">
    <footer class="container footer">
        <div class="footer_element">
            <p>2025 © Бит <br>Все права защищены</p>
        </div>
        <div class="footer_element">
            <a href="#" class="footer_element_header">Компания</a>
            <a href="#" class="footer_element_link">Новости</a>
            <a href="#" class="footer_element_link">О компании</a>
        </div>
        <div class="footer_element">
            <a href="#" class="footer_element_header">Информация</a>
            <a href="#" class="footer_element_link">Услуги по ремонту</a>
            <a href="#" class="footer_element_link">Обслуживание офиса</a>
        </div>
        <div class="footer_element">
            <a href="#" class="footer_element_header">Помощь</a>
            <a href="#" class="footer_element_link">Условия доставки</a>
            <a href="#" class="footer_element_link">Условия оплаты</a>
        </div>
        <div class="footer_element">
            <div class="footer_element_info_tel">
                <img src="<?php echo e(asset('img/phone.png')); ?>" alt="">
                <p>+7(925)047-81-12</p>
            </div>
            <p class="footer_element_info">+7(496)540-44-15</p>
            <div class="footer_element_icons">
                <img src="<?php echo e(asset('img/mir.png')); ?>" alt="">
                <img src="<?php echo e(asset('img/visa.png')); ?>" alt="">
                <img src="<?php echo e(asset('img/mastercard.png')); ?>" alt="">
            </div>
        </div>
    </footer>
</div>

<script>
    window.CSRF_TOKEN = '<?php echo e(csrf_token()); ?>';
    <?php
        $authData = null;
        if (Auth::check()) {
            $u = Auth::user();
            $authData = ['id' => $u->id, 'email' => $u->email, 'first_name' => $u->first_name, 'last_name' => $u->last_name, 'role' => $u->role];
        }
    ?>
    window.AUTH_USER = <?php echo json_encode($authData); ?>;
</script>
<script>
(function() {
    var nameBtn = document.getElementById('account-name-btn');
    var popup = document.getElementById('account-popup');
    if (nameBtn && popup) {
        nameBtn.addEventListener('click', function() { popup.classList.toggle('open'); });
        document.addEventListener('click', function(e) {
            if (!nameBtn.contains(e.target) && !popup.contains(e.target)) popup.classList.remove('open');
        });
    }
})();
</script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\Files\Study\2026\kursach_2\pc-configurator\resources\views/layouts/app.blade.php ENDPATH**/ ?>