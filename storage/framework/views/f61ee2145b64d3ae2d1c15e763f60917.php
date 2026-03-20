

<?php $__env->startSection('title', 'Вход — Бит'); ?>

<?php $__env->startSection('content'); ?>
<main class="auth-page" role="main" aria-labelledby="auth-title">
    <div class="auth-card" role="region" aria-label="Окно авторизации и регистрации">
        <div class="auth-header">
            <h2 id="auth-title">Вход</h2>
            <div class="switcher" role="tablist" aria-label="Переключатель форм">
                <button class="tab-btn <?php echo e($errors->register->any() ? '' : 'active'); ?>" id="tab-login" role="tab" aria-selected="<?php echo e($errors->register->any() ? 'false' : 'true'); ?>" aria-controls="panel-login">Вход</button>
                <button class="tab-btn <?php echo e($errors->register->any() ? 'active' : ''); ?>" id="tab-register" role="tab" aria-selected="<?php echo e($errors->register->any() ? 'true' : 'false'); ?>" aria-controls="panel-register">Регистрация</button>
            </div>
        </div>

        <form id="panel-login" class="panel" role="tabpanel" aria-hidden="<?php echo e($errors->register->any() ? 'true' : 'false'); ?>" method="POST" action="<?php echo e(url('/login')); ?>" novalidate style="<?php echo e($errors->register->any() ? 'display:none;' : ''); ?>">
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <div class="field">
                    <label for="login-email">Email</label>
                    <input id="login-email" name="email" type="email" inputmode="email" autocomplete="username" placeholder="you@example.com" value="<?php echo e(old('email')); ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="login-password">Пароль</label>
                    <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Пароль">
                </div>
            </div>
            <div class="small-row">
                <label class="remember"><input type="checkbox" name="remember" id="remember-me"> Запомнить меня</label>
                <a class="link-like" id="to-register">Создать аккаунт</a>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Войти</button>
            </div>
            <div id="login-msg" aria-live="polite">
                <?php if($errors->any() && !$errors->register->any()): ?>
                    <p class="error"><?php echo e($errors->first()); ?></p>
                <?php endif; ?>
            </div>
        </form>

        <form id="panel-register" class="panel" role="tabpanel" aria-hidden="<?php echo e($errors->register->any() ? 'false' : 'true'); ?>" style="<?php echo e($errors->register->any() ? 'margin-top:6px;' : 'display:none; margin-top:6px;'); ?>" method="POST" action="<?php echo e(url('/register')); ?>" novalidate>
            <?php echo csrf_field(); ?>
            <div class="form-row inline">
                <div class="field">
                    <label for="reg-firstname">Имя</label>
                    <input id="reg-firstname" name="first_name" type="text" autocomplete="given-name" placeholder="Иван" value="<?php echo e(old('first_name')); ?>">
                    <?php if($errors->register->has('first_name')): ?>
                        <span class="field-error"><?php echo e($errors->register->first('first_name')); ?></span>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="reg-lastname">Фамилия</label>
                    <input id="reg-lastname" name="last_name" type="text" autocomplete="family-name" placeholder="Иванов" value="<?php echo e(old('last_name')); ?>">
                    <?php if($errors->register->has('last_name')): ?>
                        <span class="field-error"><?php echo e($errors->register->first('last_name')); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row">
                <div class="field">
                    <label for="reg-email">Email</label>
                    <input id="reg-email" name="email" type="email" inputmode="email" autocomplete="email" placeholder="you@example.com" value="<?php echo e(old('email')); ?>">
                    <?php if($errors->register->has('email')): ?>
                        <span class="field-error"><?php echo e($errors->register->first('email')); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="form-row inline">
                <div class="field">
                    <label for="reg-password">Пароль</label>
                    <input id="reg-password" name="password" type="password" autocomplete="new-password" placeholder="Пароль (мин. 8 символов)">
                    <?php if($errors->register->has('password')): ?>
                        <span class="field-error"><?php echo e($errors->register->first('password')); ?></span>
                    <?php endif; ?>
                </div>
                <div class="field">
                    <label for="reg-password2">Подтверждение пароля</label>
                    <input id="reg-password2" name="password_confirmation" type="password" autocomplete="new-password" placeholder="Повторите пароль">
                </div>
            </div>
            <div class="small-row">
                <div></div>
                <a class="link-like" id="to-login">Уже есть аккаунт? Войти</a>
            </div>
            <div class="actions">
                <button type="submit" class="btn primary">Зарегистрироваться</button>
                <button type="button" class="btn ghost" id="reg-cancel">Отмена</button>
            </div>
            <div id="register-msg" aria-live="polite"></div>
        </form>

        <div class="helper">Нажимая «Зарегистрироваться», вы соглашаетесь с <a href="#" class="link-like">политикой конфиденциальности</a>.</div>
    </div>
</main>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/login.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Files\Study\2026\kursach_2\pc-configurator\resources\views/auth/login.blade.php ENDPATH**/ ?>