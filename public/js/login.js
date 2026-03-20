/**
 * Login/Register page JS — tab switching between login & register forms
 * Auth itself is handled server-side by Laravel
 */
(function () {
    'use strict';

    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    const loginForm = document.getElementById('panel-login');
    const registerForm = document.getElementById('panel-register');

    // Also handle the text links
    const toRegisterLink = document.getElementById('to-register');
    const toLoginLink = document.getElementById('to-login');
    const regCancel = document.getElementById('reg-cancel');

    function showLogin() {
        loginTab.classList.add('active');
        registerTab.classList.remove('active');
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    }

    function showRegister() {
        registerTab.classList.add('active');
        loginTab.classList.remove('active');
        registerForm.style.display = 'block';
        loginForm.style.display = 'none';
    }

    if (loginTab && registerTab) {
        loginTab.addEventListener('click', showLogin);
        registerTab.addEventListener('click', showRegister);
    }
    if (toRegisterLink) toRegisterLink.addEventListener('click', e => { e.preventDefault(); showRegister(); });
    if (toLoginLink) toLoginLink.addEventListener('click', e => { e.preventDefault(); showLogin(); });
    if (regCancel) regCancel.addEventListener('click', showLogin);
})();
