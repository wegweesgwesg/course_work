@extends('layouts.app')

@section('title', e($build->name) . ' — Каталог сборок — bit.sp')

@push('styles')
<style>
.build-detail-page { max-width:860px; margin:20px auto; padding:10px; }
.build-detail-page .back-link { display:inline-flex; align-items:center; gap:6px; color:#2e1b74; text-decoration:none; font-size:14px; margin-bottom:14px; }
.build-detail-page .back-link:hover { text-decoration:underline; }

/* Header */
.bd-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; }
.bd-title { font-size:22px; font-weight:700; word-break:break-word; }
.bd-author { font-size:13px; color:#888; margin-bottom:16px; }
.bd-author .role-badge { background:#2e1b74; color:#fff; padding:1px 8px; border-radius:10px; font-size:11px; margin-left:4px; }
.bd-desc { font-size:14px; color:#444; line-height:1.5; margin-bottom:18px; background:#fafafa; padding:12px; border-radius:6px; border:1px solid #eee; }

/* Components */
.bd-components { margin-bottom:22px; }
.bd-components h3 { font-size:16px; margin:0 0 10px; }
.bd-comp-item { display:flex; align-items:center; gap:12px; padding:10px; border:1px solid #f0f0f0; border-radius:8px; margin-bottom:6px; background:#fff; }
.bd-comp-img { width:52px; height:52px; background:#f7f7f7; border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
.bd-comp-img img { width:100%; height:100%; object-fit:contain; }
.bd-comp-info { flex:1; min-width:0; }
.bd-comp-slot { font-size:11px; color:#999; text-transform:uppercase; }
.bd-comp-name { font-weight:600; font-size:14px; }
.bd-comp-price { font-size:13px; color:#666; }
.bd-total { display:flex; justify-content:flex-end; font-size:18px; font-weight:700; padding:10px 0; border-top:2px solid #eee; }

/* Voting */
.bd-voting { display:flex; align-items:center; gap:12px; margin-bottom:24px; padding:14px; background:#fafafa; border-radius:8px; border:1px solid #eee; }
.bd-vote-btn { background:none; border:2px solid #ddd; border-radius:6px; padding:8px 14px; cursor:pointer; font-size:18px; transition:all .15s; }
.bd-vote-btn:hover { border-color:#2e1b74; }
.bd-vote-btn.active-up { border-color:#4caf50; background:#e8f5e9; }
.bd-vote-btn.active-down { border-color:#d9534f; background:#fce4ec; }
.bd-vote-btn:disabled { opacity:.5; cursor:default; }
.bd-score { font-size:20px; font-weight:700; min-width:40px; text-align:center; }
.bd-score.positive { color:#4caf50; }
.bd-score.negative { color:#d9534f; }
.bd-score.neutral { color:#888; }

/* Comments */
.bd-comments { margin-top:8px; }
.bd-comments h3 { font-size:16px; margin:0 0 12px; }
.bd-comment { padding:12px; border:1px solid #f0f0f0; border-radius:8px; margin-bottom:8px; background:#fff; }
.bd-comment-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; gap:8px; }
.bd-comment-author { font-weight:600; font-size:14px; }
.bd-comment-date { font-size:12px; color:#999; white-space:nowrap; }
.bd-comment-text { font-size:14px; color:#333; line-height:1.5; white-space:pre-wrap; word-break:break-word; }
.bd-comment-del { background:#d9534f; color:#fff; border:none; border-radius:4px; padding:2px 8px; cursor:pointer; font-size:11px; flex-shrink:0; }

/* Comment form */
.bd-comment-form { margin-top:14px; }
.bd-comment-form textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; font-size:14px; resize:vertical; min-height:70px; box-sizing:border-box; font-family:inherit; }
.bd-comment-form textarea:focus { border-color:#2e1b74; outline:none; }
.bd-comment-form button { margin-top:8px; background:#2e1b74; color:#fff; border:none; padding:8px 18px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; }
.bd-comment-form button:disabled { opacity:.5; }

/* Admin actions */
.bd-admin-actions { display:flex; gap:8px; margin-bottom:16px; }
.bd-admin-actions button { background:#d9534f; color:#fff; border:none; padding:7px 14px; border-radius:6px; cursor:pointer; font-size:13px; }

.bd-login-hint { color:#888; font-size:13px; font-style:italic; margin-top:10px; }

/* Apply to configurator */
.bd-apply-btn { background:#6fac09; color:#fff; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-size:14px; font-weight:600; margin-bottom:18px; text-decoration:none; display:inline-block; }
.bd-apply-btn:hover { background:#5e9a08; }
</style>
@endpush

@section('content')
<main class="build-detail-page">
    <a href="{{ route('builds.index') }}" class="back-link">&larr; Назад к каталогу</a>

    <div class="bd-header">
        <div class="bd-title">{{ $build->name }}</div>
    </div>
    <div class="bd-author">
        Автор: {{ $build->user->first_name ?? 'Неизвестно' }}
        @if(in_array($build->user->role ?? '', ['admin','content_manager','warehouse_manager']))
            <span class="role-badge">Администрация</span>
        @endif
        &middot; {{ $build->created_at->format('d.m.Y H:i') }}
    </div>

    @if($build->description)
        <div class="bd-desc">{{ $build->description }}</div>
    @endif

    @auth
        @if(Auth::user()->isPrivileged())
            <div class="bd-admin-actions">
                <button id="admin-delete-build" data-id="{{ $build->id }}">Удалить сборку</button>
            </div>
        @endif
    @endauth

    {{-- Components --}}
    <div class="bd-components">
        <h3>Компоненты сборки</h3>
        @php
            $slotLabels = [
                'cpu' => 'Процессор', 'motherboard' => 'Мат. плата', 'ram' => 'RAM',
                'gpu' => 'Видеокарта', 'storage' => 'Накопитель', 'psu' => 'БП',
                'cooler' => 'Кулер', 'case' => 'Корпус',
            ];
            $buildData = $build->build_data;
            $total = 0;
            function resolveSlotLabel($slot, $labels) {
                if (isset($labels[$slot])) return $labels[$slot];
                $base = preg_replace('/_\d+$/', '', $slot);
                return $labels[$base] ?? $slot;
            }
        @endphp
        @if(is_array($buildData))
            @foreach($buildData as $slot => $item)
                @php
                    $price = $item['price'] ?? 0;
                    $total += $price;
                @endphp
                <div class="bd-comp-item">
                    <div class="bd-comp-img">
                        @if(!empty($item['main_image_path']))
                            <img src="/{{ e($item['main_image_path']) }}" alt="">
                        @else
                            📦
                        @endif
                    </div>
                    <div class="bd-comp-info">
                        <div class="bd-comp-slot">{{ resolveSlotLabel($slot, $slotLabels) }}</div>
                        <div class="bd-comp-name">{{ $item['name'] ?? $item['product_id'] ?? '—' }}</div>
                        <div class="bd-comp-price">{{ number_format($price, 0, '.', ' ') }} ₽</div>
                    </div>
                </div>
            @endforeach
        @endif
        <div class="bd-total">Итого: {{ number_format($total, 0, '.', ' ') }} ₽</div>
    </div>

    {{-- Apply to configurator --}}
    <a href="#" class="bd-apply-btn" id="apply-build-btn">Применить сборку в конфигураторе</a>

    {{-- Voting --}}
    <div class="bd-voting">
        <button class="bd-vote-btn{{ $userVote === 1 ? ' active-up' : '' }}" id="vote-up" {{ Auth::guest() ? 'disabled' : '' }}>👍</button>
        <div class="bd-score {{ $build->score > 0 ? 'positive' : ($build->score < 0 ? 'negative' : 'neutral') }}" id="bd-score">
            {{ $build->score > 0 ? '+' : '' }}{{ $build->score }}
        </div>
        <button class="bd-vote-btn{{ $userVote === -1 ? ' active-down' : '' }}" id="vote-down" {{ Auth::guest() ? 'disabled' : '' }}>👎</button>
        @guest
            <span style="color:#999;font-size:13px;margin-left:8px;">Войдите, чтобы голосовать</span>
        @endguest
    </div>

    {{-- Comments --}}
    <div class="bd-comments">
        <h3>Комментарии ({{ $build->comments->count() }})</h3>

        <div id="comments-list">
            @foreach($build->comments->sortByDesc('created_at') as $c)
                <div class="bd-comment" id="comment-{{ $c->id }}">
                    <div class="bd-comment-header">
                        <div class="bd-comment-author">{{ $c->user->first_name ?? 'Аноним' }}</div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="bd-comment-date">{{ $c->created_at->format('d.m.Y H:i') }}</div>
                            @auth
                                @if(Auth::user()->isPrivileged())
                                    <button class="bd-comment-del" data-comment-id="{{ $c->id }}">Удалить</button>
                                @endif
                            @endauth
                        </div>
                    </div>
                    <div class="bd-comment-text">{{ $c->text }}</div>
                </div>
            @endforeach
        </div>

        @auth
            <div class="bd-comment-form">
                <textarea id="comment-text" placeholder="Напишите комментарий..." maxlength="2000"></textarea>
                <button id="comment-submit">Отправить</button>
            </div>
        @else
            <div class="bd-login-hint">Войдите, чтобы оставить комментарий.</div>
        @endauth
    </div>
</main>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    var CSRF = window.CSRF_TOKEN;
    var AUTH = window.AUTH_USER;
    var BUILD_ID = {{ $build->id }};

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function isPrivileged() { return AUTH && ['admin','content_manager','warehouse_manager'].indexOf(AUTH.role) !== -1; }

    // ========== Voting ==========
    var voteUp = document.getElementById('vote-up');
    var voteDown = document.getElementById('vote-down');
    var scoreEl = document.getElementById('bd-score');

    async function doVote(val) {
        var res = await fetch('/api/builds/' + BUILD_ID + '/vote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ vote: val })
        });
        if (!res.ok) return;
        var data = await res.json();
        var score = data.score;
        var userVote = data.user_vote;
        scoreEl.textContent = (score > 0 ? '+' : '') + score;
        scoreEl.className = 'bd-score ' + (score > 0 ? 'positive' : (score < 0 ? 'negative' : 'neutral'));
        voteUp.className = 'bd-vote-btn' + (userVote === 1 ? ' active-up' : '');
        voteDown.className = 'bd-vote-btn' + (userVote === -1 ? ' active-down' : '');
    }

    if (voteUp && !voteUp.disabled) voteUp.addEventListener('click', function() { doVote(1); });
    if (voteDown && !voteDown.disabled) voteDown.addEventListener('click', function() { doVote(-1); });

    // ========== Comments ==========
    var commentSubmit = document.getElementById('comment-submit');
    var commentText = document.getElementById('comment-text');

    if (commentSubmit) {
        commentSubmit.addEventListener('click', async function() {
            var text = commentText.value.trim();
            if (!text) return;
            commentSubmit.disabled = true;
            var res = await fetch('/api/builds/' + BUILD_ID + '/comment', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ text: text })
            });
            commentSubmit.disabled = false;
            if (!res.ok) { alert('\u041e\u0448\u0438\u0431\u043a\u0430 \u043f\u0440\u0438 \u043e\u0442\u043f\u0440\u0430\u0432\u043a\u0435 \u043a\u043e\u043c\u043c\u0435\u043d\u0442\u0430\u0440\u0438\u044f.'); return; }
            var data = await res.json();
            commentText.value = '';
            // Prepend new comment
            var el = document.createElement('div');
            el.className = 'bd-comment';
            el.id = 'comment-' + data.id;
            var delHtml = '';
            if (isPrivileged()) {
                delHtml = '<button class="bd-comment-del" data-comment-id="' + data.id + '">\u0423\u0434\u0430\u043b\u0438\u0442\u044c</button>';
            }
            el.innerHTML =
                '<div class="bd-comment-header">' +
                    '<div class="bd-comment-author">' + esc(data.user.first_name) + '</div>' +
                    '<div style="display:flex;align-items:center;gap:8px;">' +
                        '<div class="bd-comment-date">' + esc(data.created_at) + '</div>' +
                        delHtml +
                    '</div>' +
                '</div>' +
                '<div class="bd-comment-text">' + esc(data.text) + '</div>';
            wireCommentDel(el);
            var list = document.getElementById('comments-list');
            list.insertBefore(el, list.firstChild);
        });
    }

    // ========== Delete comments ==========
    function wireCommentDel(container) {
        container.querySelectorAll('.bd-comment-del').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                var cid = btn.dataset.commentId;
                if (!confirm('\u0423\u0434\u0430\u043b\u0438\u0442\u044c \u043a\u043e\u043c\u043c\u0435\u043d\u0442\u0430\u0440\u0438\u0439?')) return;
                var res = await fetch('/api/builds/comments/' + cid, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
                if (res.ok) {
                    var el = document.getElementById('comment-' + cid);
                    if (el) el.remove();
                }
            });
        });
    }
    wireCommentDel(document);

    // ========== Admin delete build ==========
    var adminDel = document.getElementById('admin-delete-build');
    if (adminDel) {
        adminDel.addEventListener('click', async function() {
            if (!confirm('\u0423\u0434\u0430\u043b\u0438\u0442\u044c \u044d\u0442\u0443 \u0441\u0431\u043e\u0440\u043a\u0443?')) return;
            var res = await fetch('/api/builds/' + BUILD_ID, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
            if (res.ok) { window.location.href = '/builds'; }
        });
    }
    // ========== Apply build to configurator ==========
    var applyBtn = document.getElementById('apply-build-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', function(e) {
            e.preventDefault();
            var buildData = @json($build->build_data);
            localStorage.setItem('apply_build_data', JSON.stringify(buildData));
            window.location.href = '{{ route("configurator") }}';
        });
    }
})();
</script>
@endpush
