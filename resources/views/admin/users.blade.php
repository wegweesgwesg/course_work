@extends('layouts.app')

@section('title', 'Управление пользователями — Бит')
@section('body-class', 'page-users')

@push('styles')
<style>
.container-main { max-width:1100px; margin:22px auto; padding:12px; box-sizing:border-box; }
.admin-header { align-items:flex-end; gap:12px; margin-bottom:12px; }
.controls { display:flex; gap:8px; align-items:center; }

.search-input { padding:8px; border-radius:6px; border:1px solid #ddd; min-width:100%; }

.users-card { background:#fff; border:1px solid #e6e6e6; border-radius:8px; padding:12px; box-shadow:0 2px 6px rgba(0,0,0,0.03); }
.users-table { width:100%; border-collapse:collapse; }
.users-table th, .users-table td { padding:10px 8px; border-bottom:1px solid #f3f3f3; text-align:left; font-size:14px; }
.users-table thead th { font-weight:700; background:#fafafa; }
.role-pill { display:inline-block; padding:6px 10px; border-radius:8px; background:#f0f0f0; cursor:pointer; font-size:13px; }
.role-pill.admin { background:#2e1b74; color:#fff; }
.role-pill.manager { background:#4b88d1; color:#fff; }
.role-pill.user { background:#9fd08f; color:#0b3a12; }
.role-pill.guest { background:#d0d0d0; color:#222; }
.role-pill.content { background:#a178d2; color:#fff; }

.role-dropdown { position:relative; display:inline-block; }
.role-dropdown .options { position:absolute; top:36px; left:0; min-width:160px; background:#fff; border:1px solid #e6e6e6; border-radius:6px; box-shadow:0 6px 18px rgba(0,0,0,0.08); z-index:1000; display:none; }
.role-dropdown .options button { display:block; width:100%; text-align:left; padding:8px 10px; border:0; background:transparent; cursor:pointer; }
.role-dropdown .options button:hover { background:#f6f6f6; }

.small-muted { font-size:13px; color:#666; margin-top:10px; }

/* Notification toast */
.notify-container { position:fixed; top:20px; right:20px; z-index:99999; display:flex; flex-direction:column; gap:10px; pointer-events:none; }
.notify-toast { pointer-events:auto; min-width:300px; max-width:420px; padding:14px 18px; border-radius:8px; color:#fff; font-size:14px; box-shadow:0 4px 16px rgba(0,0,0,.18); animation: notifyIn .3s ease; display:flex; align-items:flex-start; gap:10px; }
.notify-toast.success { background:#4caf50; }
.notify-toast.error { background:#d9534f; }
.notify-toast.warning { background:#e0a800; color:#222; }
.notify-toast.info { background:#2e1b74; }
.notify-toast .notify-close { background:none; border:none; color:inherit; font-size:18px; cursor:pointer; margin-left:auto; line-height:1; }
@keyframes notifyIn { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:translateX(0); } }
@keyframes notifyOut { from { opacity:1; transform:translateX(0); } to { opacity:0; transform:translateX(40px); } }

/* Confirm dialog */
.confirm-overlay { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.45); z-index:100000; display:flex; align-items:center; justify-content:center; }
.confirm-card { background:#fff; border-radius:10px; padding:28px 32px; max-width:420px; width:90%; box-shadow:0 8px 32px rgba(0,0,0,.2); text-align:center; }
.confirm-card .confirm-title { font-size:18px; font-weight:700; margin-bottom:10px; }
.confirm-card .confirm-text { font-size:14px; color:#444; margin-bottom:22px; }
.confirm-card .confirm-actions { display:flex; gap:10px; justify-content:center; }
.confirm-card .confirm-actions button { padding:10px 22px; border-radius:6px; border:none; cursor:pointer; font-size:14px; font-weight:600; }
.confirm-card .btn-confirm-yes { background:#d9534f; color:#fff; }
.confirm-card .btn-confirm-no { background:#e6e6e6; color:#222; }
</style>
@endpush

@section('content')
<main class="container-main" role="main" aria-labelledby="page-title">
    <div class="admin-header">
        <h1 id="page-title" class="title">Управление авторизованными пользователями</h1>
        <div class="controls">
            <input id="user-search" class="search-input" placeholder="Поиск по email..." aria-label="Поиск по email"/>
        </div>
    </div>

    <div class="users-card" role="region" aria-label="Список пользователей">
        <table class="users-table" id="users-table">
            <thead>
                <tr>
                    <th style="width:5%;">ID</th>
                    <th style="width:35%;">Электронная почта</th>
                    <th style="width:25%;">Имя</th>
                    <th style="width:20%;">Текущая роль</th>
                    @if(Auth::user()->isAdmin())
                    <th style="width:15%;">Действия</th>
                    @endif
                </tr>
            </thead>
            <tbody id="users-tbody"></tbody>
        </table>
        <div class="small-muted">
            @if(Auth::user()->isAdmin())
            Поиск по email. Изменение роли производится нажатием на текущую роль — появится список доступных ролей.
            @else
            Просмотр списка пользователей. Для изменения ролей обратитесь к администратору.
            @endif
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/admin.js') }}"></script>
@endpush
