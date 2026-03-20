

<?php $__env->startSection('title', 'Конфигуратор ПК — bit.sp'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.config-page { max-width:1060px; margin:20px auto; display:flex; gap:20px; padding:10px; box-sizing:border-box; }
.config-left { flex:1; background:#fff; border:1px solid #e1e1e1; padding:16px; border-radius:6px; }
.config-right { width:340px; background:#fff; border:1px solid #e1e1e1; padding:16px; border-radius:6px; position:sticky; top:20px; height:max-content; }

.select-btn { background:#6fac09; color:#fff; padding:8px 12px; border-radius:4px; border:none; cursor:pointer; font-size:13px; }
.select-btn.secondary { background:#ccc; color:#222; }

.total-row { display:flex; justify-content:space-between; align-items:center; padding:12px 0; font-size:18px; font-weight:700; }
.warn { border-left:4px solid #e0a800; background:#fff8e1; padding:10px; margin-bottom:10px; font-size:14px; }
.err { border-left:4px solid #d9534f; background:#fff0f0; padding:10px; margin-bottom:10px; font-size:14px; }
.ok { border-left:4px solid #4caf50; background:#f0fff4; padding:10px; margin-bottom:10px; font-size:14px; }

/* Category & slot layout */
.category { border:1px solid #eee; border-radius:6px; margin-bottom:8px; overflow:hidden; }
.category-header { display:flex; justify-content:space-between; align-items:center; padding:10px; background:#fafafa; cursor:pointer; }
.category-header .title { font-weight:700; display:flex; gap:8px; align-items:center; }
.category-header .slot-counter { font-weight:400; font-size:13px; color:#888; }
.category-header .toggle { transition: transform .2s ease; }
.category.collapsed .toggle { transform:rotate(-90deg); }
.category-body { padding:0; display:block; }
.category-slots { display:flex; flex-direction:column; }

/* Individual slot */
.slot { display:flex; align-items:center; gap:12px; padding:12px; border-bottom:1px solid #f0f0f0; transition: background .2s, border-color .2s; }
.slot:last-child { border-bottom:none; }
.slot.slot-error { background:#fff0f0; border-color:#d9534f; }
.slot-img { width:56px; height:56px; background:#f7f7f7; border-radius:6px; flex-shrink:0; display:flex; align-items:center; justify-content:center; overflow:hidden; }
.slot-img img { width:100%; height:100%; object-fit:contain; display:block; }
.slot-img .slot-empty-icon { color:#ccc; font-size:24px; }
.slot-info { flex:1; min-width:0; }
.slot-product-name { font-weight:600; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.slot-product-price { font-size:13px; color:#666; margin-top:2px; }
.slot-empty-text { color:#aaa; font-size:13px; font-style:italic; }
.slot-actions { display:flex; gap:6px; flex-shrink:0; }
.slot-btn { padding:6px 12px; border-radius:4px; border:none; cursor:pointer; font-size:13px; }
.slot-btn-add { background:#6fac09; color:#fff; }
.slot-btn-clear { background:#eee; color:#444; }
.slot-btn-clear:hover { background:#ddd; }

/* Clear all button */
.clear-all-row { display:flex; justify-content:flex-end; margin-bottom:8px; }
.btn-clear-all { background:none; border:1px solid #d9534f; color:#d9534f; padding:6px 14px; border-radius:4px; cursor:pointer; font-size:13px; }
.btn-clear-all:hover { background:#d9534f; color:#fff; }

.template-list { display:flex; flex-direction:column; gap:8px; }
.template-item { padding:10px;border:1px solid #eee;border-radius:6px;display:flex;justify-content:space-between;align-items:center; cursor:pointer; }
.template-item:hover { background:#fafafa; }
.template-item .meta { font-size:14px; font-weight:600; }
.template-item button { padding:8px 10px;border-radius:6px;background:#2e1b74;color:#fff;border:0;cursor:pointer }

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

/* Configs section */
.configs-section { margin-top:18px; border-top:1px solid #eee; padding-top:14px; }
.configs-section h4 { margin:0 0 10px; font-size:15px; }

/* Product editor modal */
.editor-card { width:800px; max-width:100%; background:#fff; border-radius:8px; box-sizing:border-box; max-height:90vh; overflow:auto; }
.editor-header { display:flex; justify-content:space-between; align-items:center; padding:14px 16px; border-bottom:1px solid #eee; }
.editor-title { font-weight:700; font-size:17px; }
.editor-body { display:grid; grid-template-columns:1fr 240px; gap:16px; padding:16px; }
.editor-main { display:flex; flex-direction:column; gap:10px; }
.editor-side { display:flex; flex-direction:column; gap:8px; }
.editor-field { display:flex; flex-direction:column; gap:4px; }
.editor-field label { font-size:13px; font-weight:600; color:#444; }
.editor-input { padding:8px 10px; border:1px solid #ddd; border-radius:6px; font-size:14px; }
.editor-input:focus { border-color:#2e1b74; outline:none; }
.editor-category-label { font-weight:600; color:#2e1b74; font-size:14px; }
.editor-image-preview { width:100%; aspect-ratio:1; background:#f7f7f7; border:1px solid #eee; border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; cursor:pointer; position:relative; }
.editor-image-preview:hover { border-color:#2e1b74; }
.editor-image-preview img { max-width:100%; max-height:100%; object-fit:contain; }
.editor-image-preview .upload-hint { position:absolute; bottom:8px; left:0; right:0; text-align:center; font-size:11px; color:#999; pointer-events:none; }
.editor-actions { display:flex; gap:8px; }
.editor-specs-section h4 { margin:8px 0 6px; font-size:14px; }
.spec-editor-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; align-items:start; padding:8px; border:1px solid #f0f0f0; border-radius:6px; margin-bottom:6px; }
.spec-editor-row .spec-label { font-size:13px; font-weight:600; }
.spec-editor-row .spec-hint { font-size:11px; color:#999; }
.spec-editor-row input, .spec-editor-row select { width:100%; padding:6px 8px; border:1px solid #ddd; border-radius:4px; font-size:13px; box-sizing:border-box; }

/* Admin catalog controls */
.catalog-header-actions { display:flex; gap:8px; align-items:center; }
.btn-admin-add { background:#2e1b74; color:#fff; border:none; padding:8px 14px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600; }

/* Catalog item buttons — unified size */
.cat-item { display:flex; justify-content:space-between; align-items:flex-start; padding:12px; border-radius:8px; border:1px solid #eee; cursor:pointer; background:#fff; }
.cat-left { display:flex; gap:12px; align-items:center; max-width:65%; }
.cat-thumb { width:64px; height:64px; background:#f7f7f7; border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
.cat-thumb img { width:100%; height:100%; object-fit:cover; display:block; }
.cat-info { min-width:0; }
.cat-title { font-weight:700; margin-bottom:4px; }
.cat-desc { color:#666; font-size:13px; }
.cat-right { display:flex; flex-direction:column; align-items:flex-end; gap:6px; }
.cat-right .cat-actions { display:flex; gap:4px; align-items:center; flex-wrap:wrap; }
.cat-actions .btn-primary,
.cat-actions .btn-edit-sm,
.cat-actions .btn-del-sm { padding:7px 12px; border-radius:6px; font-size:13px; font-weight:600; border:none; cursor:pointer; }
.cat-actions .btn-primary { background:#2e1b74; color:#fff; }
.cat-actions .btn-edit-sm { background:#e0a800; color:#222; }
.cat-actions .btn-del-sm { background:#d9534f; color:#fff; }

/* Template management */
.template-item .tpl-admin-btns { display:flex; gap:4px; }
.btn-tpl-del { background:#d9534f; color:#fff; border:none; padding:5px 8px; border-radius:4px; cursor:pointer; font-size:11px; }
.btn-tpl-add { background:#2e1b74; color:#fff; border:none; padding:7px 12px; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600; width:100%; margin-top:8px; }

/* Builds catalog link */
.builds-catalog-link { display:flex; align-items:center; gap:8px; padding:12px 14px; margin-top:12px; background:linear-gradient(135deg,#2e1b74 0%,#4a2db5 100%); color:#fff; text-decoration:none; border-radius:8px; font-size:14px; font-weight:600; transition:box-shadow .2s, transform .15s; }
.builds-catalog-link:hover { box-shadow:0 4px 12px rgba(46,27,116,.3); transform:translateY(-1px); }
.builds-catalog-link .builds-catalog-arrow { margin-left:auto; font-size:18px; }

/* Template detail modal */
.tpl-detail-card { width:680px; max-width:100%; max-height:90vh; overflow:auto; background:#fff; border-radius:10px; padding:18px; position:relative; }
.tpl-detail-item { display:flex; gap:12px; align-items:center; padding:10px; border:1px solid #f0f0f0; border-radius:8px; margin-bottom:8px; }
.tpl-detail-img { width:56px; height:56px; background:#f7f7f7; border-radius:6px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0; }
.tpl-detail-img img { width:100%; height:100%; object-fit:contain; display:block; }
.tpl-detail-info { flex:1; }
.tpl-detail-slot { font-size:12px; color:#999; text-transform:uppercase; }
.tpl-detail-name { font-weight:600; font-size:14px; }
.tpl-detail-price { font-size:13px; color:#666; }

/* Product detail specs in Russian */
.product-card { width:680px; max-width:100%; max-height:90vh; overflow:auto; background:#fff; border-radius:10px; padding:22px; position:relative; box-sizing:border-box; }
.product-spec-row { display:grid; grid-template-columns:1fr 1fr; gap:8px; align-items:start; padding:8px 10px; border:1px solid #f0f0f0; border-radius:6px; margin-bottom:4px; }
.product-spec-row .psr-label { font-size:13px; font-weight:600; }
.product-spec-row .psr-hint { font-size:11px; color:#999; }
.product-spec-row .psr-value { font-size:14px; text-align:right; }

/* Catalog modal layout with filter sidebar */
.catalog-card { display:flex; flex-direction:column; width:960px; max-width:98vw; max-height:90vh; background:#fff; border-radius:10px; overflow:hidden; }
.catalog-body { display:flex; flex:1; min-height:0; overflow:hidden; }
.catalog-filters { width:220px; flex-shrink:0; border-right:1px solid #eee; padding:12px; overflow-y:auto; font-size:13px; background:#fafafa; }
.catalog-filters h4 { margin:0 0 8px; font-size:13px; font-weight:700; }
.catalog-filters .filter-group { margin-bottom:12px; }
.catalog-filters .filter-group-title { font-weight:600; font-size:12px; color:#555; margin-bottom:4px; cursor:pointer; display:flex; justify-content:space-between; }
.catalog-filters .filter-group-title .fg-toggle { font-size:10px; color:#999; }
.catalog-filters .filter-values { display:flex; flex-direction:column; gap:2px; max-height:140px; overflow-y:auto; }
.catalog-filters .filter-values.collapsed { display:none; }
.catalog-filters .filter-values label { display:flex; align-items:center; gap:6px; font-size:12px; cursor:pointer; padding:2px 0; }
.catalog-filters .filter-values input[type=checkbox] { margin:0; }
.catalog-filters .filter-reset { background:none; border:1px solid #ccc; color:#666; padding:4px 10px; border-radius:4px; cursor:pointer; font-size:12px; width:100%; margin-top:4px; }
.catalog-filters .filter-reset:hover { background:#eee; }
.catalog-main-col { flex:1; display:flex; flex-direction:column; min-width:0; overflow:hidden; }
.catalog-list { flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:8px; padding:12px; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<main class="config-page" role="main" aria-labelledby="config-title">
    <section class="config-left" aria-label="Слоты конфигурации">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
            <h2 id="config-title" style="margin:0;">Конфигуратор ПК</h2>
            <button class="btn-clear-all" id="clear-all-slots">Очистить всё</button>
        </div>

        <div id="categories-list">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catId => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="category" id="cat-<?php echo e($catId); ?>">
                <div class="category-header" data-target="<?php echo e($catId); ?>">
                    <div class="title"><?php echo e($cat['title']); ?> <span class="slot-counter" id="slot-counter-<?php echo e($catId); ?>"></span></div>
                    <div class="toggle">▾</div>
                </div>
                <div class="category-body">
                    <div class="category-slots" id="slots-<?php echo e($catId); ?>" data-category="<?php echo e($catId); ?>">
                        
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>

    <aside class="config-right" aria-label="Итоговая панель">
        <h3>Итог сборки</h3>

        <div id="warnings-block" aria-live="polite">
            <div class="ok">Проверка совместимости: правило не запущено.</div>
        </div>

        <div class="total-row">
            <div>Итого</div>
            <div id="total-price">0 ₽</div>
        </div>

        <button id="export-build" class="select-btn" style="width:100%;">Экспортировать сборку</button>

        <div class="configs-section">
            <a href="<?php echo e(route('builds.index')); ?>" class="builds-catalog-link">
                <span>📋</span> Перейти в каталог сборок
                <span class="builds-catalog-arrow">&rarr;</span>
            </a>
            <h4>Конфигурации</h4>
            <div id="templates-list" class="template-list"></div>
        </div>

        <?php if(auth()->guard()->check()): ?>
        <button id="publish-build" class="select-btn" style="width:100%;margin-top:8px;background:#2e1b74;">Опубликовать сборку</button>
        <?php endif; ?>
    </aside>
</main>

<!-- Notification container -->
<div class="notify-container" id="notify-container"></div>


<div class="catalog-modal" id="catalog-modal" style="display:none;">
    <div class="catalog-card">
        <div class="catalog-header-row">
            <div class="catalog-title" id="modal-title">Выберите компонент</div>
            <div class="catalog-header-actions">
                <?php if(auth()->guard()->check()): ?>
                    <?php if(Auth::user()->isPrivileged()): ?>
                        <button class="btn-admin-add" id="catalog-add-product">+ Добавить товар</button>
                    <?php endif; ?>
                <?php endif; ?>
                <button class="btn-flat" id="modal-close">✕</button>
            </div>
        </div>
        <div class="catalog-controls">
            <div class="search-wrap">
                <input class="catalog-search" id="modal-search" placeholder="Поиск...">
            </div>
            <select class="catalog-select" id="modal-sort">
                <option value="name">По имени</option>
                <option value="price_asc">Цена ↑</option>
                <option value="price_desc">Цена ↓</option>
            </select>
            <label class="instock-label">
                <input type="checkbox" id="modal-instock"> В наличии
            </label>
            <label class="instock-label" style="margin-left:8px;">
                <input type="checkbox" id="modal-compatible"> Только совместимые
            </label>
        </div>
        <div class="catalog-body">
            <div class="catalog-filters" id="catalog-filters">
                <h4>Фильтры</h4>
                <div id="filter-groups"></div>
                <button class="filter-reset" id="filter-reset">Сбросить фильтры</button>
            </div>
            <div class="catalog-main-col">
                <div class="catalog-list" id="modal-list"></div>
                <div class="catalog-footer">
                    <button class="pager-btn" id="modal-prev">← Назад</button>
                    <div class="pager-info" id="modal-pager-info"></div>
                    <button class="pager-btn" id="modal-next">Далее →</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="catalog-modal" id="product-modal" style="display:none;">
    <div class="product-card" id="product-card-content"></div>
</div>


<div class="catalog-modal" id="product-editor-modal" style="display:none;">
    <div class="editor-card">
        <div class="editor-header">
            <div class="editor-title" id="editor-title">Добавить товар</div>
            <button class="btn-flat" id="editor-close">✕</button>
        </div>
        <div class="editor-body">
            <div class="editor-main">
                <div class="editor-field">
                    <label>Категория</label>
                    <span id="editor-category" class="editor-category-label"></span>
                </div>
                <div class="editor-field">
                    <label for="editor-pid">Артикул (ID товара)</label>
                    <input id="editor-pid" class="editor-input" placeholder="cpu-my-product">
                </div>
                <div class="editor-field">
                    <label for="editor-name">Название</label>
                    <input id="editor-name" class="editor-input" placeholder="Название товара">
                </div>
                <div class="editor-field">
                    <label for="editor-price">Цена (₽)</label>
                    <input id="editor-price" class="editor-input" type="number" min="0" placeholder="0">
                </div>
                <div class="editor-field">
                    <label for="editor-stock">Количество на складе</label>
                    <input id="editor-stock" class="editor-input" type="number" min="0" placeholder="0">
                </div>
                <div class="editor-field">
                    <label for="editor-desc">Описание</label>
                    <textarea id="editor-desc" class="editor-input" rows="3" placeholder="Описание товара"></textarea>
                </div>
                <div class="editor-specs-section">
                    <h4>Технические характеристики</h4>
                    <div id="editor-specs-list"></div>
                </div>
            </div>
            <div class="editor-side">
                <label>Изображение</label>
                <div id="editor-image-preview" class="editor-image-preview" title="Нажмите для выбора изображения">
                    <span style="color:#999;">Нажмите для выбора</span>
                    <div class="upload-hint">Нажмите для загрузки</div>
                </div>
                <input type="file" id="editor-image-file" accept="image/*" style="display:none;">
                <input type="hidden" id="editor-image-path" value="">
                <div class="editor-actions" style="margin-top:16px;">
                    <button id="editor-save" class="select-btn">Сохранить</button>
                    <button id="editor-cancel" class="select-btn secondary">Отмена</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="catalog-modal" id="template-editor-modal" style="display:none;">
    <div class="editor-card" style="max-width:560px;">
        <div class="editor-header">
            <div class="editor-title" id="tpl-editor-title">Добавить конфигурацию</div>
            <button class="btn-flat" id="tpl-editor-close">✕</button>
        </div>
        <div style="padding:12px;">
            <div class="editor-field">
                <label for="tpl-editor-name">Название конфигурации</label>
                <input id="tpl-editor-name" class="editor-input" placeholder="Игровая сборка">
            </div>
            <div class="editor-field">
                <label><input type="checkbox" id="tpl-editor-public" checked> Публичная конфигурация</label>
            </div>
            <p style="font-size:13px;color:#666;margin:10px 0;">Компоненты берутся из текущей сборки конфигуратора.</p>
            <div id="tpl-editor-items" style="font-size:13px;margin-bottom:12px;"></div>
            <div class="editor-actions">
                <button id="tpl-editor-save" class="select-btn">Сохранить</button>
                <button id="tpl-editor-cancel" class="select-btn secondary">Отмена</button>
            </div>
        </div>
    </div>
</div>

<div class="catalog-modal" id="template-detail-modal" style="display:none;">
    <div class="tpl-detail-card">
        <button class="btn-flat" style="position:absolute;top:12px;right:12px;" id="tpl-detail-close">✕</button>
        <h3 id="tpl-detail-title" style="margin:0 0 16px;">Конфигурация</h3>
        <div id="tpl-detail-items"></div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:12px;border-top:1px solid #eee;">
            <div style="font-weight:700;font-size:16px;">Итого: <span id="tpl-detail-total">0 ₽</span></div>
            <div style="display:flex;gap:8px;">
                <button class="select-btn" id="tpl-detail-apply">Применить</button>
                <button class="select-btn secondary" id="tpl-detail-back">Закрыть</button>
            </div>
        </div>
    </div>
</div>


<div class="catalog-modal" id="publish-build-modal" style="display:none;">
    <div class="editor-card" style="max-width:560px;">
        <div class="editor-header">
            <div class="editor-title">Опубликовать сборку</div>
            <button class="btn-flat" id="publish-close">✕</button>
        </div>
        <div style="padding:12px;">
            <div class="editor-field">
                <label for="publish-name">Название сборки</label>
                <input id="publish-name" class="editor-input" placeholder="Моя игровая сборка" maxlength="255">
            </div>
            <div class="editor-field" style="margin-top:8px;">
                <label for="publish-desc">Описание</label>
                <textarea id="publish-desc" class="editor-input" rows="4" placeholder="Расскажите о вашей сборке..." maxlength="2000"></textarea>
            </div>
            <p style="font-size:13px;color:#666;margin:10px 0;">Компоненты берутся из текущей сборки конфигуратора.</p>
            <div id="publish-items" style="font-size:13px;margin-bottom:12px;"></div>
            <div class="editor-actions">
                <button id="publish-submit" class="select-btn">Опубликовать</button>
                <button id="publish-cancel" class="select-btn secondary">Отмена</button>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/configurator.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Files\Study\2026\kursach_2\pc-configurator\resources\views/configurator.blade.php ENDPATH**/ ?>