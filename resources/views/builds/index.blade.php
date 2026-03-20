@extends('layouts.app')

@section('title', 'Каталог сборок — bit.sp')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modal.css') }}">
<style>
.builds-page { max-width:1060px; margin:20px auto; padding:10px; }
.builds-page h2 { margin:0 0 16px; }

/* Search & filter bar */
.builds-toolbar { display:flex; gap:12px; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; }
.builds-search-wrap { flex:1; min-width:240px; }
.builds-search { width:100%; padding:10px 14px; border:1px solid #ddd; border-radius:6px; font-size:14px; box-sizing:border-box; }
.builds-search:focus { border-color:#2e1b74; outline:none; }

/* Component filter */
.builds-component-filter { background:#fff; border:1px solid #e1e1e1; border-radius:6px; padding:12px; min-width:260px; flex-shrink:0; }
.builds-component-filter h4 { margin:0 0 8px; font-size:13px; font-weight:700; color:#555; }
.cf-chips { display:flex; flex-direction:column; gap:6px; }
.cf-chip { display:flex; align-items:center; gap:8px; background:#f0f0f0; padding:6px 10px; border-radius:6px; font-size:13px; }
.cf-chip-cat { font-size:11px; color:#999; text-transform:uppercase; min-width:70px; }
.cf-chip-name { flex:1; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.cf-chip-remove { background:none; border:none; color:#d9534f; cursor:pointer; font-size:16px; padding:0 4px; flex-shrink:0; }
.btn-cf-add { background:none; border:1px dashed #aaa; color:#666; padding:6px 10px; border-radius:4px; cursor:pointer; font-size:12px; margin-top:6px; width:100%; }
.btn-cf-add:hover { border-color:#2e1b74; color:#2e1b74; }

/* Category picker */
.cf-cat-picker { display:flex; flex-wrap:wrap; gap:6px; margin-top:6px; }
.cf-cat-btn { padding:6px 10px; border:1px solid #ddd; border-radius:4px; background:#fff; cursor:pointer; font-size:12px; }
.cf-cat-btn:hover { border-color:#2e1b74; color:#2e1b74; }

/* Sections */
.builds-section { margin-bottom:28px; }
.builds-section-title { font-size:17px; font-weight:700; margin-bottom:12px; display:flex; align-items:center; gap:8px; }
.builds-section-title .badge { font-size:11px; background:#2e1b74; color:#fff; padding:2px 8px; border-radius:10px; font-weight:400; }

/* Build cards grid */
.builds-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:14px; }

/* Build card */
.build-card { background:#fff; border:1px solid #e1e1e1; border-radius:8px; padding:16px; transition:box-shadow .2s; cursor:pointer; display:flex; flex-direction:column; gap:8px; text-decoration:none; color:inherit; }
.build-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08); }
.build-card-header { display:flex; justify-content:space-between; align-items:flex-start; }
.build-card-name { font-weight:700; font-size:15px; word-break:break-word; }
.build-card-score { display:flex; align-items:center; gap:4px; font-weight:700; font-size:14px; }
.build-card-score.positive { color:#4caf50; }
.build-card-score.negative { color:#d9534f; }
.build-card-score.neutral { color:#888; }
.build-card-desc { font-size:13px; color:#666; line-height:1.4; overflow:hidden; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
.build-card-components { display:flex; flex-wrap:wrap; gap:4px; }
.build-card-chip { background:#f0f0f0; padding:2px 8px; border-radius:10px; font-size:11px; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; }
.build-card-meta { display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#999; margin-top:auto; padding-top:6px; border-top:1px solid #f0f0f0; }
.build-card-price { font-weight:700; font-size:14px; color:#222; }
.build-card-author { font-style:italic; }
.build-card-stats { display:flex; gap:10px; align-items:center; }
.build-card-stats span { display:flex; align-items:center; gap:3px; }

/* Admin delete on card */
.build-card-admin { position:relative; }
.build-card-admin .btn-build-del { position:absolute; top:8px; right:8px; background:#d9534f; color:#fff; border:none; border-radius:4px; padding:4px 8px; cursor:pointer; font-size:11px; z-index:2; }

/* Empty state */
.builds-empty { text-align:center; color:#999; padding:30px; font-size:14px; }

/* Search results heading */
.search-results-heading { font-size:15px; color:#555; margin-bottom:12px; }
</style>
@endpush

@section('content')
<main class="builds-page">
    <h2>Каталог сборок</h2>

    <div class="builds-toolbar">
        <div class="builds-search-wrap">
            <input type="text" class="builds-search" id="builds-search" placeholder="Поиск сборок по названию...">
        </div>
        <div class="builds-component-filter" id="component-filter">
            <h4>Фильтр по компонентам</h4>
            <div class="cf-chips" id="cf-chips"></div>
            <button class="btn-cf-add" id="cf-add">+ Добавить компонент</button>
            <div class="cf-cat-picker" id="cf-cat-picker" style="display:none;"></div>
        </div>
    </div>

    {{-- Featured sections (hidden when searching) --}}
    <div id="featured-sections">
        <div class="builds-section" id="section-admin">
            <div class="builds-section-title">Сборки от администрации <span class="badge">Рекомендуемые</span></div>
            <div class="builds-grid" id="grid-admin"></div>
        </div>
        <div class="builds-section" id="section-popular">
            <div class="builds-section-title">Самые популярные сборки <span class="badge" style="background:#e0a800;color:#222;">Топ</span></div>
            <div class="builds-grid" id="grid-popular"></div>
        </div>
    </div>

    {{-- Search results (shown when searching) --}}
    <div id="search-results" style="display:none;">
        <div class="search-results-heading" id="search-results-heading"></div>
        <div class="builds-grid" id="grid-search"></div>
    </div>
</main>

{{-- Product picker modal for component filter --}}
<div class="catalog-modal" id="cf-modal" style="display:none;">
    <div class="catalog-card">
        <div class="catalog-header-row">
            <div class="catalog-title" id="cf-modal-title">Выберите компонент</div>
            <div class="catalog-header-actions">
                <button class="btn-flat" id="cf-modal-close">✕</button>
            </div>
        </div>
        <div class="catalog-controls">
            <div class="search-wrap">
                <input class="catalog-search" id="cf-modal-search" placeholder="Поиск...">
            </div>
            <select class="catalog-select" id="cf-modal-sort">
                <option value="name">По имени</option>
                <option value="price_asc">Цена ↑</option>
                <option value="price_desc">Цена ↓</option>
            </select>
        </div>
        <div class="catalog-body">
            <div class="catalog-main-col" style="flex:1;">
                <div class="catalog-list" id="cf-modal-list"></div>
                <div class="catalog-footer">
                    <button class="pager-btn" id="cf-modal-prev">← Назад</button>
                    <div class="pager-info" id="cf-modal-pager-info"></div>
                    <button class="pager-btn" id="cf-modal-next">Далее →</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    var CSRF = window.CSRF_TOKEN;
    var AUTH = window.AUTH_USER;
    var SLOT_LABELS = {
        cpu: '\u041f\u0440\u043e\u0446\u0435\u0441\u0441\u043e\u0440',
        motherboard: '\u041c\u0430\u0442. \u043f\u043b\u0430\u0442\u0430',
        ram: 'RAM',
        gpu: '\u0412\u0438\u0434\u0435\u043e\u043a\u0430\u0440\u0442\u0430',
        storage: '\u041d\u0430\u043a\u043e\u043f\u0438\u0442\u0435\u043b\u044c',
        psu: '\u0411\u041f',
        cooler: '\u041a\u0443\u043b\u0435\u0440',
        case: '\u041a\u043e\u0440\u043f\u0443\u0441'
    };
    var CATEGORIES = ['cpu','motherboard','ram','gpu','storage','psu','cooler','case'];

    // Selected filter components: [{category, product_id, name}]
    var filterComponents = [];

    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function formatPrice(v) { return v ? v.toLocaleString('ru-RU') + ' \u20bd' : '0 \u20bd'; }
    function isPrivileged() { return AUTH && ['admin','content_manager','warehouse_manager'].indexOf(AUTH.role) !== -1; }

    // ========== Component filter chips ==========
    var cfChips = document.getElementById('cf-chips');

    function renderFilterChips() {
        cfChips.innerHTML = '';
        filterComponents.forEach(function(fc, idx) {
            var chip = document.createElement('div');
            chip.className = 'cf-chip';
            chip.innerHTML =
                '<span class="cf-chip-cat">' + esc(SLOT_LABELS[fc.category] || fc.category) + '</span>' +
                '<span class="cf-chip-name">' + esc(fc.name) + '</span>';
            var removeBtn = document.createElement('button');
            removeBtn.className = 'cf-chip-remove';
            removeBtn.textContent = '\u2715';
            removeBtn.addEventListener('click', function() {
                filterComponents.splice(idx, 1);
                renderFilterChips();
                doSearch();
            });
            chip.appendChild(removeBtn);
            cfChips.appendChild(chip);
        });
    }

    // ========== Category picker ==========
    var catPicker = document.getElementById('cf-cat-picker');
    var cfAddBtn = document.getElementById('cf-add');

    cfAddBtn.addEventListener('click', function() {
        if (catPicker.style.display === 'none') {
            catPicker.innerHTML = '';
            CATEGORIES.forEach(function(cat) {
                var btn = document.createElement('button');
                btn.className = 'cf-cat-btn';
                btn.textContent = SLOT_LABELS[cat];
                btn.addEventListener('click', function() {
                    catPicker.style.display = 'none';
                    openFilterModal(cat);
                });
                catPicker.appendChild(btn);
            });
            catPicker.style.display = 'flex';
        } else {
            catPicker.style.display = 'none';
        }
    });

    // ========== Filter modal (product picker) ==========
    var cfModal = document.getElementById('cf-modal');
    var cfModalList = document.getElementById('cf-modal-list');
    var cfModalSearch = document.getElementById('cf-modal-search');
    var cfModalSort = document.getElementById('cf-modal-sort');
    var cfModalTitle = document.getElementById('cf-modal-title');
    var cfModalPagerInfo = document.getElementById('cf-modal-pager-info');
    var cfCurrentCategory = '';
    var cfCurrentPage = 1;
    var cfTotalPages = 1;

    function openFilterModal(category) {
        cfCurrentCategory = category;
        cfCurrentPage = 1;
        cfModalSearch.value = '';
        cfModalSort.value = 'name';
        cfModalTitle.textContent = '\u0412\u044b\u0431\u0435\u0440\u0438\u0442\u0435: ' + (SLOT_LABELS[category] || category);
        cfModal.style.display = 'flex';
        loadFilterProducts();
    }

    function closeFilterModal() {
        cfModal.style.display = 'none';
    }

    document.getElementById('cf-modal-close').addEventListener('click', closeFilterModal);
    cfModal.addEventListener('click', function(e) { if (e.target === e.currentTarget) closeFilterModal(); });

    async function loadFilterProducts() {
        var params = new URLSearchParams({
            page: cfCurrentPage,
            sort: cfModalSort.value,
            limit: 20
        });
        var q = cfModalSearch.value.trim();
        if (q) params.set('search', q);

        cfModalList.innerHTML = '<div style="text-align:center;padding:20px;color:#999;">\u0417\u0430\u0433\u0440\u0443\u0437\u043a\u0430...</div>';

        try {
            var res = await fetch('/api/products/' + cfCurrentCategory + '?' + params);
            var data = await res.json();
            var products = data.data || [];
            cfTotalPages = data.last_page || 1;
            renderFilterProductList(products);
            cfModalPagerInfo.textContent = '\u0421\u0442\u0440. ' + cfCurrentPage + ' \u0438\u0437 ' + cfTotalPages + ' (' + (data.total || 0) + ' \u0442\u043e\u0432.)';
        } catch(e) {
            cfModalList.innerHTML = '<div style="text-align:center;padding:20px;color:#d9534f;">\u041e\u0448\u0438\u0431\u043a\u0430 \u0437\u0430\u0433\u0440\u0443\u0437\u043a\u0438</div>';
        }
    }

    function renderFilterProductList(products) {
        cfModalList.innerHTML = '';
        if (!products.length) {
            cfModalList.innerHTML = '<div style="text-align:center;padding:20px;color:#999;">\u041d\u0438\u0447\u0435\u0433\u043e \u043d\u0435 \u043d\u0430\u0439\u0434\u0435\u043d\u043e</div>';
            return;
        }
        products.forEach(function(p) {
            var div = document.createElement('div');
            div.className = 'cat-item';

            var imgHtml = p.main_image_path
                ? '<img src="/' + esc(p.main_image_path) + '" alt="">'
                : '\ud83d\udce6';

            div.innerHTML =
                '<div class="cat-left">' +
                    '<div class="cat-thumb">' + imgHtml + '</div>' +
                    '<div class="cat-info">' +
                        '<div class="cat-title">' + esc(p.name) + '</div>' +
                        '<div class="cat-desc">' + formatPrice(p.price) + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="cat-right">' +
                    '<button class="select-btn" data-pid="' + esc(p.product_id) + '">\u0412\u044b\u0431\u0440\u0430\u0442\u044c</button>' +
                '</div>';

            var selBtn = div.querySelector('[data-pid]');
            selBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                filterComponents.push({
                    category: cfCurrentCategory,
                    product_id: p.product_id,
                    name: p.name
                });
                renderFilterChips();
                closeFilterModal();
                doSearch();
            });

            cfModalList.appendChild(div);
        });
    }

    // Modal controls
    var cfSearchTimer = null;
    cfModalSearch.addEventListener('input', function() {
        clearTimeout(cfSearchTimer);
        cfSearchTimer = setTimeout(function() { cfCurrentPage = 1; loadFilterProducts(); }, 300);
    });
    cfModalSort.addEventListener('change', function() { cfCurrentPage = 1; loadFilterProducts(); });
    document.getElementById('cf-modal-prev').addEventListener('click', function() {
        if (cfCurrentPage > 1) { cfCurrentPage--; loadFilterProducts(); }
    });
    document.getElementById('cf-modal-next').addEventListener('click', function() {
        if (cfCurrentPage < cfTotalPages) { cfCurrentPage++; loadFilterProducts(); }
    });

    // ========== Build card ==========
    function createBuildCard(b) {
        var link = document.createElement('a');
        link.href = '/builds/' + b.id;
        link.className = 'build-card' + (isPrivileged() ? ' build-card-admin' : '');

        var scoreClass = b.score > 0 ? 'positive' : (b.score < 0 ? 'negative' : 'neutral');
        var scoreSign = b.score > 0 ? '+' : '';

        var chips = '';
        if (b.build_data && typeof b.build_data === 'object') {
            var slots = Object.keys(b.build_data);
            for (var i = 0; i < slots.length && i < 4; i++) {
                var item = b.build_data[slots[i]];
                var chipName = (item && item.name) ? item.name : slots[i];
                chips += '<span class="build-card-chip">' + esc(chipName) + '</span>';
            }
            if (slots.length > 4) chips += '<span class="build-card-chip">+' + (slots.length - 4) + '</span>';
        }

        var roleBadge = '';
        if (b.user && ['admin','content_manager','warehouse_manager'].indexOf(b.user.role) !== -1) {
            roleBadge = ' <span style="color:#2e1b74;font-weight:700;">&#9733;</span>';
        }

        var adminDel = '';
        if (isPrivileged()) {
            adminDel = '<button class="btn-build-del" data-del-build="' + b.id + '">\u0423\u0434\u0430\u043b\u0438\u0442\u044c</button>';
        }

        link.innerHTML =
            adminDel +
            '<div class="build-card-header">' +
                '<div class="build-card-name">' + esc(b.name) + '</div>' +
                '<div class="build-card-score ' + scoreClass + '">' + scoreSign + b.score + '</div>' +
            '</div>' +
            (b.description ? '<div class="build-card-desc">' + esc(b.description) + '</div>' : '') +
            '<div class="build-card-components">' + chips + '</div>' +
            '<div class="build-card-meta">' +
                '<span class="build-card-price">' + formatPrice(b.total_price) + '</span>' +
                '<div class="build-card-stats">' +
                    '<span>\ud83d\udcac ' + (b.comments_count || 0) + '</span>' +
                    '<span class="build-card-author">' + esc(b.user ? b.user.first_name : '') + roleBadge + '</span>' +
                '</div>' +
            '</div>';

        var delBtn = link.querySelector('[data-del-build]');
        if (delBtn) {
            delBtn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (!confirm('\u0423\u0434\u0430\u043b\u0438\u0442\u044c \u0441\u0431\u043e\u0440\u043a\u0443 \u00ab' + b.name + '\u00bb?')) return;
                var res = await fetch('/api/builds/' + b.id, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
                if (res.ok) { link.remove(); }
            });
        }

        return link;
    }

    // ========== Load sections ==========
    async function loadFeaturedSections() {
        try {
            var res1 = await fetch('/api/builds?section=admin&limit=5&sort=newest');
            var admin = await res1.json();
            var gridAdmin = document.getElementById('grid-admin');
            gridAdmin.innerHTML = '';
            if (!admin.length) {
                gridAdmin.innerHTML = '<div class="builds-empty">\u041d\u0435\u0442 \u0441\u0431\u043e\u0440\u043e\u043a \u043e\u0442 \u0430\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u0438</div>';
            } else {
                admin.forEach(function(b) { gridAdmin.appendChild(createBuildCard(b)); });
            }
        } catch(e) {}

        try {
            var res2 = await fetch('/api/builds?section=popular&limit=5&sort=popular');
            var popular = await res2.json();
            var gridPop = document.getElementById('grid-popular');
            gridPop.innerHTML = '';
            if (!popular.length) {
                gridPop.innerHTML = '<div class="builds-empty">\u041d\u0435\u0442 \u043f\u043e\u043f\u0443\u043b\u044f\u0440\u043d\u044b\u0445 \u0441\u0431\u043e\u0440\u043e\u043a</div>';
            } else {
                popular.forEach(function(b) { gridPop.appendChild(createBuildCard(b)); });
            }
        } catch(e) {}
    }

    // ========== Search ==========
    var searchInput = document.getElementById('builds-search');
    var searchTimer = null;

    function doSearch() {
        var q = searchInput.value.trim();
        var productIds = filterComponents.map(function(fc) { return fc.product_id; });

        var isSearching = q.length > 0 || productIds.length > 0;

        document.getElementById('featured-sections').style.display = isSearching ? 'none' : 'block';
        document.getElementById('search-results').style.display = isSearching ? 'block' : 'none';

        if (!isSearching) {
            loadFeaturedSections();
            return;
        }

        var params = new URLSearchParams({ sort: 'popular' });
        if (q) params.set('search', q);
        if (productIds.length) params.set('component', productIds.join(','));

        fetch('/api/builds?' + params)
            .then(function(r) { return r.json(); })
            .then(function(builds) {
                var heading = document.getElementById('search-results-heading');
                heading.textContent = '\u041d\u0430\u0439\u0434\u0435\u043d\u043e \u0441\u0431\u043e\u0440\u043e\u043a: ' + builds.length;
                var grid = document.getElementById('grid-search');
                grid.innerHTML = '';
                if (!builds.length) {
                    grid.innerHTML = '<div class="builds-empty">\u041d\u0438\u0447\u0435\u0433\u043e \u043d\u0435 \u043d\u0430\u0439\u0434\u0435\u043d\u043e</div>';
                } else {
                    builds.forEach(function(b) { grid.appendChild(createBuildCard(b)); });
                }
            });
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(doSearch, 300);
    });

    // Init
    loadFeaturedSections();
})();
</script>
@endpush
