<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Конфигуратор ПК — bit.sp')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/modal.css') }}">
    @stack('styles')
    <script>
        window.CSRF_TOKEN = '{{ csrf_token() }}';
        @auth
        window.AUTH_USER = {!! json_encode([
            'id' => Auth::user()->id,
            'email' => Auth::user()->email,
            'first_name' => Auth::user()->first_name,
            'role' => Auth::user()->role,
        ]) !!};
        @else
        window.AUTH_USER = null;
        @endauth
    </script>
</head>
<body class="@yield('body-class')">

<header class="header">
    <div class="bg-header">
        <div class="container top">
            <div class="top_accout" id="account-block">
                @auth
                    <div class="top_accout account-replaced">
                        <button class="account-name-btn" id="account-name-btn">{{ Auth::user()->first_name }}</button>
                        <div class="account-popup" id="account-popup">
                            <div class="account-popup-role">Роль: <b>
                                @switch(Auth::user()->role)
                                    @case('admin') Администратор @break
                                    @case('content_manager') Контент-менеджер @break
                                    @case('warehouse_manager') Менеджер склада @break
                                    @default Пользователь
                                @endswitch
                            </b></div>
                            <div class="account-popup-actions">
                                @if(Auth::user()->isPrivileged())
                                    <a href="{{ route('admin') }}" class="btn ghost" style="margin-right:4px;">Админ-панель</a>
                                @endif
                                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn ghost">Выйти</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <img src="{{ asset('img/lock.png') }}" alt="">
                    <a href="{{ route('login') }}" style="text-decoration: none; color: black;">Личный кабинет</a>
                @endauth
            </div>
            <div class="top_search">
                <input type="text" placeholder="Поиск по каталогу" class="top_searchInput">
                <img src="{{ asset('img/search_button.png') }}" alt="" class="top_searchConfirm">
            </div>
            <img class="top_socialVK" src="{{ asset('img/vk.png') }}">
            <div class="top_contacts">
                <img src="{{ asset('img/phone.png') }}" alt="" class="top_socialPhone">
                <p>+7(925)047-81-12</p>
                <p>+7(496)540-44-15</p>
            </div>
        </div>
    </div>
    <div class="container center">
        <img src="{{ asset('img/logo.png') }}" alt="" class="center_logo">
        <div class="center_text">Интернет-магазин<br>бытовой техники и<br>электроники</div>
        <div class="center_basket">
            <img src="{{ asset('img/basket.png') }}" alt="">
            <p><b>Корзина</b> <br>Ваша корзина <br>пуста</p>
        </div>
        <button class="center_button">ПЕРЕЙТИ В КОРЗИНУ</button>
    </div>
    <div class="container bottom">
        <a href="#" class="bottom_element">КАТАЛОГ</a>
        <a href="#" class="bottom_element">ДОСТАВКА И ОПЛАТА</a>
        <a href="#" class="bottom_element">СЕРВИСНЫЙ ЦЕНТР</a>
        <a href="#" class="bottom_element">КОНТАКТЫ</a>
        <a href="{{ route('configurator') }}" class="bottom_element">КОНФИГУРАТОР</a>
    </div>
</header>

@yield('content')

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
                <img src="{{ asset('img/phone.png') }}" alt="">
                <p>+7(925)047-81-12</p>
            </div>
            <p class="footer_element_info">+7(496)540-44-15</p>
            <div class="footer_element_icons">
                <img src="{{ asset('img/mir.png') }}" alt="">
                <img src="{{ asset('img/visa.png') }}" alt="">
                <img src="{{ asset('img/mastercard.png') }}" alt="">
            </div>
        </div>
    </footer>
</div>

<script>
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    @php
        $authData = null;
        if (Auth::check()) {
            $u = Auth::user();
            $authData = ['id' => $u->id, 'email' => $u->email, 'first_name' => $u->first_name, 'last_name' => $u->last_name, 'role' => $u->role];
        }
    @endphp
    window.AUTH_USER = {!! json_encode($authData) !!};
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
@stack('scripts')
</body>
</html>
