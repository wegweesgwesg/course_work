/**
 * Admin panel JS — manages users via Laravel API
 */
(function () {
    'use strict';

    const CSRF = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;
    const AUTH = window.AUTH_USER || {};
    const isAdmin = AUTH.role === 'admin';
    const tbody = document.getElementById('users-tbody');
    const searchInput = document.getElementById('user-search');

    let allUsers = [];
    let searchTimer = null;

    // ========== Custom Notification / Confirm ==========
    function ensureNotifyContainer() {
        var c = document.getElementById('notify-container');
        if (!c) { c = document.createElement('div'); c.id = 'notify-container'; c.className = 'notify-container'; document.body.appendChild(c); }
        return c;
    }

    function showNotification(type, message, duration) {
        duration = duration || 4000;
        var container = ensureNotifyContainer();
        var toast = document.createElement('div');
        toast.className = 'notify-toast ' + type;
        toast.innerHTML = '<span>' + esc(message) + '</span><button class="notify-close">&times;</button>';
        container.appendChild(toast);
        var close = function() {
            toast.style.animation = 'notifyOut .3s ease forwards';
            setTimeout(function() { toast.remove(); }, 300);
        };
        toast.querySelector('.notify-close').addEventListener('click', close);
        setTimeout(close, duration);
    }

    function showConfirm(title, text) {
        return new Promise(function(resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML =
                '<div class="confirm-card">' +
                    '<div class="confirm-title">' + esc(title) + '</div>' +
                    '<div class="confirm-text">' + esc(text) + '</div>' +
                    '<div class="confirm-actions">' +
                        '<button class="btn-confirm-yes">Да</button>' +
                        '<button class="btn-confirm-no">Отмена</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(overlay);
            overlay.querySelector('.btn-confirm-yes').addEventListener('click', function() { overlay.remove(); resolve(true); });
            overlay.querySelector('.btn-confirm-no').addEventListener('click', function() { overlay.remove(); resolve(false); });
            overlay.addEventListener('click', function(e) { if (e.target === overlay) { overlay.remove(); resolve(false); } });
        });
    }

    async function loadUsers(search) {
        const params = search ? '?search=' + encodeURIComponent(search) : '';
        const res = await fetch('/api/admin/users' + params);
        allUsers = await res.json();
        renderUsers(allUsers);
    }

    function renderUsers(users) {
        tbody.innerHTML = '';
        users.forEach(u => {
            const tr = document.createElement('tr');
            if (isAdmin) {
                tr.innerHTML = `
                    <td>${u.id}</td>
                    <td>${esc(u.email)}</td>
                    <td>${esc(u.first_name || '')} ${esc(u.last_name || '')}</td>
                    <td>
                        <select data-role-user="${u.id}" class="admin-role-select">
                            <option value="user" ${u.role === 'user' ? 'selected' : ''}>user</option>
                            <option value="content_manager" ${u.role === 'content_manager' ? 'selected' : ''}>content_manager</option>
                            <option value="warehouse_manager" ${u.role === 'warehouse_manager' ? 'selected' : ''}>warehouse_manager</option>
                            <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>admin</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn-danger" data-delete-user="${u.id}">Удалить</button>
                    </td>
                `;
                const sel = tr.querySelector('[data-role-user]');
                sel.addEventListener('change', () => changeRole(u.id, sel.value));
                const delBtn = tr.querySelector('[data-delete-user]');
                delBtn.addEventListener('click', () => deleteUser(u.id, u.email));
            } else {
                tr.innerHTML = `
                    <td>${u.id}</td>
                    <td>${esc(u.email)}</td>
                    <td>${esc(u.first_name || '')} ${esc(u.last_name || '')}</td>
                    <td>${esc(u.role)}</td>
                `;
            }
            tbody.appendChild(tr);
        });
    }

    async function changeRole(userId, role) {
        const res = await fetch(`/api/admin/users/${userId}/role`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ role }),
        });
        const data = await res.json();
        if (data.success) {
            showNotification('success', 'Роль изменена.');
        } else {
            showNotification('error', 'Ошибка: ' + (data.error || 'неизвестная'));
            loadUsers(searchInput.value);
        }
    }

    async function deleteUser(userId, email) {
        var confirmed = await showConfirm('Удаление пользователя', 'Удалить пользователя ' + email + '?');
        if (!confirmed) return;
        const res = await fetch(`/api/admin/users/${userId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
        const data = await res.json();
        if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showNotification('success', 'Пользователь удалён.');
            loadUsers(searchInput.value);
        }
    }

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadUsers(searchInput.value), 300);
    });

    function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }

    // Initial load
    loadUsers('');
})();
