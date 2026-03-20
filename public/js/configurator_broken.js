/**
 * Configurator main JS — full rewrite
 * Multi-slot support, image file upload, template detail, compatibility highlighting
 */
(function () {
    'use strict';

    const CSRF = window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.content;
    const AUTH = window.AUTH_USER;

    // ========== Custom Notification / Confirm ==========
    function showNotification(type, message, duration) {
        duration = duration || 4000;
        const container = document.getElementById('notify-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'notify-toast ' + type;
        toast.innerHTML = '<span>' + esc(message) + '</span><button class="notify-close">&times;</button>';
        container.appendChild(toast);
        const close = () => {
            toast.style.animation = 'notifyOut .3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        };
        toast.querySelector('.notify-close').addEventListener('click', close);
        setTimeout(close, duration);
    }

    function showConfirm(title, text) {
        return new Promise(resolve => {
            const overlay = document.createElement('div');
            overlay.className = 'confirm-overlay';
            overlay.innerHTML =
                '<div class="confirm-card">' +
                    '<div class="confirm-title">' + esc(title) + '</div>' +
                    '<div class="confirm-text">' + esc(text) + '</div>' +
                    '<div class="confirm-actions">' +
                        '<button class="btn-confirm-yes">а</button>' +
                        '<button class="btn-confirm-no">тмена</button>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(overlay);
            overlay.querySelector('.btn-confirm-yes').addEventListener('click', () => { overlay.remove(); resolve(true); });
            overlay.querySelector('.btn-confirm-no').addEventListener('click', () => { overlay.remove(); resolve(false); });
            overlay.addEventListener('click', e => { if (e.target === overlay) { overlay.remove(); resolve(false); } });
        });
    }

    // ========== Constants ==========
    const SLOT_LABELS = {
        cpu: 'роцессор', motherboard: 'атеринская плата', ram: 'перативная память',
        gpu: 'идеокарта', storage: 'акопитель', psu: 'лок питания', cooler: 'улер', case: 'орпус'
    };
    const REQUIRED_SLOTS = ['cpu', 'motherboard', 'ram', 'psu', 'case'];
    const DEFAULT_MAX_SLOTS = { cpu: 1, motherboard: 1, ram: 4, gpu: 1, storage: 4, psu: 1, cooler: 1, case: 1 };

    // ========== Selection state (arrays) ==========
    const selection = {};
    for (var _c in SLOT_LABELS) { selection[_c] = [null]; }
    window.selection = selection;

    function formatPrice(v) { return v ? v.toLocaleString('ru-RU') + ' \u20BD' : '0 \u20BD'; }

    function getFirstSelected(category) {
        var items = selection[category] || [];
        for (var i = 0; i < items.length; i++) { if (items[i]) return items[i]; }
        return null;
    }
    function getAllSelected(category) {
        return (selection[category] || []).filter(function(i) { return i !== null; });
    }
    function countSelected(category) { return getAllSelected(category).length; }

    function getMaxSlots(category) {
        var mb = getFirstSelected('motherboard');
        if (category === 'ram' && mb) {
            var rs = parseInt(mb.specs_data?.ram_slots || mb.ram_slots || 0);
            if (rs > 0) return rs;
        }
        if (category === 'storage' && mb) {
            var m2 = parseInt(mb.specs_data?.m2_slots || mb.m2_slots || 0);
            var sata = parseInt(mb.specs_data?.sata_ports || mb.sata_ports || 0);
            if (m2 + sata > 0) return m2 + sata;
        }
        return DEFAULT_MAX_SLOTS[category] || 1;
    }

    // ========== Slot Rendering ==========
    function renderAllSlots() {
        for (var cat in SLOT_LABELS) { renderCategorySlots(cat); }
    }

    function renderCategorySlots(category) {
        var container = document.getElementById('slots-' + category);
        if (!container) return;
        container.innerHTML = '';
        var items = selection[category];
        var maxSlots = getMaxSlots(category);

        for (var i = 0; i < items.length; i++) {
            container.appendChild(createSlotElement(category, i, items[i]));
        }

        if (items.length < maxSlots && maxSlots > 1 && countSelected(category) > 0) {
            var addRow = document.createElement('div');
            addRow.style.cssText = 'padding:6px 12px;text-align:center;';
            var addBtn = document.createElement('button');
            addBtn.className = 'slot-btn slot-btn-add';
            addBtn.style.cssText = 'font-size:12px;padding:4px 10px;';
            addBtn.textContent = '+ обавить ещё';
            addBtn.addEventListener('click', (function(cat) {
                return function() { selection[cat].push(null); renderCategorySlots(cat); };
            })(category));
            addRow.appendChild(addBtn);
            container.appendChild(addRow);
        }
    }

    function createSlotElement(category, index, product) {
        var div = document.createElement('div');
        div.className = 'slot';
        div.dataset.category = category;
        div.dataset.index = index;

        var imgDiv = document.createElement('div');
        imgDiv.className = 'slot-img';
        if (product && product.main_image_path) {
            imgDiv.innerHTML = '<img src="/' + esc(product.main_image_path) + '" alt="">';
        } else {
            imgDiv.innerHTML = '<span class="slot-empty-icon">\uD83D\uDCE6</span>';
        }
        div.appendChild(imgDiv);

        var infoDiv = document.createElement('div');
        infoDiv.className = 'slot-info';
        if (product) {
            infoDiv.innerHTML =
                '<div class="slot-product-name">' + esc(product.name) + '</div>' +
                '<div class="slot-product-price">' + formatPrice(product.price) + '</div>';
        } else {
            infoDiv.innerHTML = '<div class="slot-empty-text">усто</div>';
        }
        div.appendChild(infoDiv);

        var actionsDiv = document.createElement('div');
        actionsDiv.className = 'slot-actions';
        var selBtn = document.createElement('button');
        selBtn.className = 'slot-btn slot-btn-add';
        selBtn.textContent = product ? 'аменить' : 'обавить';
        selBtn.addEventListener('click', (function(cat, idx) {
            return function() { openCatalogModal(cat, idx); };
        })(category, index));
        actionsDiv.appendChild(selBtn);

        if (product) {
            var clearBtn = document.createElement('button');
            clearBtn.className = 'slot-btn slot-btn-clear';
            clearBtn.textContent = 'чистить';
            clearBtn.addEventListener('click', (function(cat, idx) {
                return function() {
                    if (idx > 0) { selection[cat].splice(idx, 1); }
                    else { selection[cat][idx] = null; }
                    renderCategorySlots(cat);
                    updateSummary();
                };
            })(category, index));
            actionsDiv.appendChild(clearBtn);
        }
        div.appendChild(actionsDiv);
        return div;
    }

    // ========== Summary & Total ==========
    function updateSummary() {
        var total = 0;
        for (var cat in selection) {
            var items = selection[cat];
            for (var i = 0; i < items.length; i++) {
                if (items[i]) total += (items[i].price || 0);
            }
        }
        document.getElementById('total-price').textContent = formatPrice(total);
        runCompatibilityChecks();
    }

    // ========== Compatibility Checks ==========
    function runCompatibilityChecks() {
        var wb = document.getElementById('warnings-block');
        if (!wb) return;
        wb.innerHTML = '';
        var msgs = [];
        var critical = false;
        var errorSlots = {};

        var cpu = getFirstSelected('cpu');
        var mb = getFirstSelected('motherboard');
        var rams = getAllSelected('ram');
        var ram = rams[0] || null;
        var gpu = getFirstSelected('gpu');
        var storages = getAllSelected('storage');
        var psu = getFirstSelected('psu');
        var cooler = getFirstSelected('cooler');
        var casep = getFirstSelected('case');

        for (var ri = 0; ri < REQUIRED_SLOTS.length; ri++) {
            var rslot = REQUIRED_SLOTS[ri];
            if (!getFirstSelected(rslot)) {
                msgs.push({ severity:'error', text:'тсутствует обязательный компонент: '+SLOT_LABELS[rslot]+'.', slots:[rslot] });
                critical = true;
            }
        }

        if (cpu && mb) {
            var cpuSocket = cpu.specs_data?.socket || cpu.socket;
            var mbSocket = mb.specs_data?.socket || mb.socket;
            if (cpuSocket && mbSocket && cpuSocket !== mbSocket) {
                msgs.push({ severity:'error', text:'Сокет процессора '+cpuSocket+' не совпадает с сокетом материнской платы '+mbSocket+'.', slots:['cpu','motherboard'] });
                critical = true;
            }
        }

        if (ram && (cpu || mb)) {
            var ramSpeed = ram.specs_data?.speed_mhz || ram.speed_mhz;
            var cpuMax = cpu ? (cpu.specs_data?.max_mem_speed || cpu.max_mem_speed || Infinity) : Infinity;
            var mbMax = mb ? (mb.specs_data?.ram_speed_max || mb.ram_speed_max || Infinity) : Infinity;
            var allowed = Math.min(cpuMax, mbMax);
            if (ramSpeed && ramSpeed > allowed) {
                msgs.push({ severity:'warning', text:'астота RAM '+ramSpeed+' ц может превышать поддерживаемую ('+allowed+' ц).', slots:['ram'] });
            }
        }

        if (mb && rams.length > 0) {
            var mbRamSlots = parseInt(mb.specs_data?.ram_slots || mb.ram_slots || 4);
            if (rams.length > mbRamSlots) {
                msgs.push({ severity:'error', text:'ыбрано '+rams.length+' планок RAM, но материнская плата поддерживает только '+mbRamSlots+'.', slots:['ram','motherboard'] });
                critical = true;
            }
        }

        if (mb && rams.length > 0) {
            var totalRamGb = 0;
            for (var ri2 = 0; ri2 < rams.length; ri2++) {
                totalRamGb += parseInt(rams[ri2].specs_data?.size_gb || rams[ri2].size_gb || 0);
            }
            var mbMaxRam = parseInt(mb.specs_data?.max_ram || mb.max_ram || 0);
            if (totalRamGb > 0 && mbMaxRam > 0 && totalRamGb > mbMaxRam) {
                msgs.push({ severity:'error', text:'бщий объём RAM ('+totalRamGb+' ) превышает максимум материнской платы ('+mbMaxRam+' ).', slots:['ram','motherboard'] });
                critical = true;
            }
        }

        if (psu) {
            var cpuPw = cpu ? parseInt(cpu.specs_data?.tdp_w || cpu.tdp_w || 0) : 0;
            var gpuPw = gpu ? parseInt(gpu.specs_data?.power_draw_w || gpu.power_draw_w || 0) : 0;
            var stPw = 0;
            for (var si = 0; si < storages.length; si++) {
                stPw += parseInt(storages[si].specs_data?.power_draw_w || storages[si].power_draw_w || 0);
            }
            var sum = cpuPw + gpuPw + stPw;
            var required = Math.ceil(sum * 1.25);
            var psuWatts = parseInt(psu.specs_data?.power_w || psu.power_w || 0);
            if (psuWatts > 0 && psuWatts < required) {
                msgs.push({ severity:'error', text:'ощность  недостаточна: требуется ~ '+required+' т, выбранный  — '+psuWatts+' т.', slots:['psu'] });
                critical = true;
            }
        }

        if (cooler && casep) {
            var coolerH = parseInt(cooler.specs_data?.cooler_height_mm || cooler.cooler_height_mm || 0);
            var caseMaxC = parseInt(casep.specs_data?.max_cooler_height_mm || casep.max_cooler_height_mm || 0);
            if (coolerH > 0 && caseMaxC > 0 && coolerH > caseMaxC) {
                msgs.push({ severity:'error', text:'улер ('+coolerH+' мм) не поместится в корпус (макс '+caseMaxC+' мм).', slots:['cooler','case'] });
                critical = true;
            }
        }

        if (gpu && casep) {
            var gpuLen = parseInt(gpu.specs_data?.length_mm || gpu.length_mm || 0);
            var caseGpu = parseInt(casep.specs_data?.max_gpu_length_mm || casep.max_gpu_length_mm || 0);
            if (gpuLen > 0 && caseGpu > 0 && gpuLen > caseGpu) {
                msgs.push({ severity:'error', text:'идеокарта ('+gpuLen+' мм) не поместится в корпус (макс '+caseGpu+' мм).', slots:['gpu','case'] });
                critical = true;
            }
        }

        if (mb && casep) {
            var mbFF = mb.specs_data?.form_factor || mb.form_factor;
            var caseFF = casep.specs_data?.form_factor || casep.form_factor;
            if (mbFF && caseFF) {
                var caseFFs;
                if (typeof caseFF === 'string') { try { caseFFs = JSON.parse(caseFF); } catch(e) { caseFFs = [caseFF]; } }
                else { caseFFs = caseFF; }
                if (Array.isArray(caseFFs)) {
                    var ffMap = {'Micro-ATX':['Micro-ATX','mATX','micro-atx','matx'],'Mini-ITX':['Mini-ITX','ITX','mini-itx','itx'],'ATX':['ATX','atx'],'E-ATX':['E-ATX','EATX','e-atx','eatx']};
                    var normalizeFF = function(ff) { for (var key in ffMap) { if (ffMap[key].indexOf(ff) !== -1) return key; } return ff; };
                    var mbNorm = normalizeFF(mbFF);
                    var caseFlatNorm = caseFFs.map(normalizeFF);
                    if (caseFlatNorm.indexOf(mbNorm) === -1) {
                        msgs.push({ severity:'error', text:'атеринская плата ('+mbFF+') не подходит к корпусу ('+caseFFs.join(', ')+').', slots:['motherboard','case'] });
                        critical = true;
                    }
                }
            }
        }

        if (mb) {
            var mbM2 = parseInt(mb.specs_data?.m2_slots || mb.m2_slots || 0);
            var nvmeCount = 0;
            for (var si2 = 0; si2 < storages.length; si2++) {
                var sIface = storages[si2].specs_data?.interface_type || storages[si2].interface_type || '';
                if (sIface.toLowerCase().indexOf('nvme') !== -1) nvmeCount++;
            }
            if (nvmeCount > mbM2) {
                msgs.push({ severity:'error', text:'ыбрано '+nvmeCount+' NVMe накопителей, но материнская плата имеет только '+mbM2+' M.2 слотов.', slots:['storage','motherboard'] });
                critical = true;
            }
        }

        if (!gpu && cpu) {
            var iGpu = cpu.specs_data?.integrated_graphics || cpu.integrated_graphics;
            if (!iGpu || iGpu === 'false' || iGpu === false) {
                msgs.push({ severity:'warning', text:'идеокарта не выбрана, а процессор не имеет встроенной графики.', slots:['gpu','cpu'] });
            }
        }

        if (msgs.length === 0) {
            var d0 = document.createElement('div'); d0.className = 'ok'; d0.textContent = 'роблем совместимости не обнаружено.';
            wb.appendChild(d0);
        } else {
            msgs.forEach(function(m) {
                var d = document.createElement('div'); d.className = (m.severity==='error'?'err':'warn'); d.textContent = m.text;
                wb.appendChild(d);
                if (m.severity === 'error' && m.slots) { m.slots.forEach(function(s) { errorSlots[s] = true; }); }
            });
        }

        document.querySelectorAll('.slot.slot-error').forEach(function(el) { el.classList.remove('slot-error'); });
        for (var ecat in errorSlots) {
            var ec = document.getElementById('slots-' + ecat);
            if (ec) ec.querySelectorAll('.slot').forEach(function(el) { el.classList.add('slot-error'); });
        }

        var expBtn = document.getElementById('export-build');
        if (expBtn) { expBtn.disabled = critical; expBtn.style.opacity = critical ? 0.5 : 1; }
    }

    // ========== Category collapse ==========
    document.querySelectorAll('.category-header').forEach(function(h) {
        h.addEventListener('click', function() {
            var cat = h.parentElement;
            cat.classList.toggle('collapsed');
            var body = cat.querySelector('.category-body');
            body.style.display = cat.classList.contains('collapsed') ? 'none' : 'block';
        });
    });

    // ========== Clear All ==========
    var clearAllBtn = document.getElementById('clear-all-slots');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            for (var cat in selection) { selection[cat] = [null]; }
            renderAllSlots();
            updateSummary();
            showNotification('info', 'се слоты очищены.');
        });
    }

    // ========== Catalog Modal ==========
    var currentSlot = null;
    var currentSlotIndex = 0;
    var currentPage = 1;
    var lastPage = 1;

    function openCatalogModal(slot, index) {
        currentSlot = slot;
        currentSlotIndex = index || 0;
        currentPage = 1;
        document.getElementById('modal-title').textContent = 'ыберите: ' + (SLOT_LABELS[slot] || slot);
        document.getElementById('modal-search').value = '';
        document.getElementById('modal-sort').value = 'name';
        document.getElementById('modal-instock').checked = false;
        loadProducts();
        document.getElementById('catalog-modal').style.display = 'flex';
    }

    function closeCatalogModal() {
        document.getElementById('catalog-modal').style.display = 'none';
        currentSlot = null;
    }

    async function loadProducts() {
        var search = document.getElementById('modal-search').value;
        var sort = document.getElementById('modal-sort').value;
        var inStock = document.getElementById('modal-instock').checked;
        var params = new URLSearchParams({ page: currentPage, sort: sort });
        if (search) params.set('search', search);
        if (inStock) params.set('in_stock', '1');
        var res = await fetch('/api/products/' + currentSlot + '?' + params);
        var data = await res.json();
        lastPage = data.last_page;
        renderProductList(data.data);
        document.getElementById('modal-pager-info').textContent = 'Страница ' + data.current_page + ' из ' + data.last_page + ' (' + data.total + ' товаров)';
        document.getElementById('modal-prev').disabled = data.current_page <= 1;
        document.getElementById('modal-next').disabled = data.current_page >= data.last_page;
    }

    function renderProductList(products) {
        var list = document.getElementById('modal-list');
        list.innerHTML = '';
        if (!products.length) {
            list.innerHTML = '<div style="padding:20px;text-align:center;color:#888;">ичего не найдено</div>';
            return;
        }
        products.forEach(function(p) {
            var row = document.createElement('div');
            row.className = 'cat-item';
            var adminBtns = '';
            if (isPrivileged()) {
                adminBtns =
                    '<button class="btn-edit-sm" data-edit-product="' + esc(p.product_id) + '">едактировать</button>' +
                    '<button class="btn-del-sm" data-del-product="' + esc(p.product_id) + '">Удалить</button>';
            }
            row.innerHTML =
                '<div class="cat-left">' +
                    '<div class="cat-thumb">' + (p.main_image_path ? '<img src="/' + esc(p.main_image_path) + '" alt="">' : '\uD83D\uDCE6') + '</div>' +
                    '<div class="cat-info">' +
                        '<div class="cat-title">' + esc(p.name) + '</div>' +
                        '<div class="cat-desc">' + esc(p.description || '') + '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="cat-right">' +
                    '<div style="font-weight:700;">' + formatPrice(p.price) + '</div>' +
                    '<div style="font-size:13px;color:' + (p.stock_quantity > 0 ? '#4caf50' : '#d9534f') + ';">' + (p.stock_quantity > 0 ? ' наличии: ' + p.stock_quantity : 'ет в наличии') + '</div>' +
                    '<div class="cat-actions">' +
                        '<button class="btn-primary" data-select-product="' + esc(p.product_id) + '">ыбрать</button>' +
                        adminBtns +
                    '</div>' +
                '</div>';
            row.querySelector('[data-select-product]').addEventListener('click', function(e) {
                e.stopPropagation();
                selectProduct(p);
            });
            var editBtn = row.querySelector('[data-edit-product]');
            if (editBtn) {
                editBtn.addEventListener('click', function(e) { e.stopPropagation(); openProductEditor('edit', currentSlot, p); });
            }
            var delBtn = row.querySelector('[data-del-product]');
            if (delBtn) {
                delBtn.addEventListener('click', function(e) { e.stopPropagation(); deleteProduct(p.product_id, p.name); });
            }
            row.addEventListener('click', function() { showProductDetail(p.product_id); });
            list.appendChild(row);
        });
    }

    function selectProduct(product) {
        if (!currentSlot) return;
        selection[currentSlot][currentSlotIndex] = product;
        renderCategorySlots(currentSlot);
        updateSummary();
        closeCatalogModal();
    }

    // Product detail
    async function showProductDetail(productId) {
        var res = await fetch('/api/product/' + productId);
        var p = await res.json();
        var card = document.getElementById('product-card-content');
        var specs = p.specs_data || {};
        var specsHtml = '';
        for (var k in specs) {
            if (k === 'product_id' || specs[k] === null || specs[k] === undefined) continue;
            specsHtml += '<div class="spec-item"><span>' + esc(k) + '</span><span>' + esc(String(specs[k])) + '</span></div>';
        }
        var adminActions = '';
        if (isPrivileged()) {
            adminActions =
                '<button class="btn-edit-sm" id="product-edit-btn">едактировать</button>' +
                '<button class="btn-del-sm" id="product-del-btn">Удалить</button>';
        }
        card.innerHTML =
            '<button class="btn-flat" style="position:absolute;top:12px;right:12px;" id="product-modal-close">\u2715</button>' +
            '<h3 class="product-title">' + esc(p.name) + '</h3>' +
            (p.main_image_path ? '<div style="text-align:center;margin-bottom:12px;"><img src="/' + esc(p.main_image_path) + '" alt="" style="max-width:100%;max-height:300px;object-fit:contain;border-radius:8px;"></div>' : '') +
            '<div class="product-meta-block">' +
                '<div class="product-price">' + formatPrice(p.price) + '</div>' +
                '<div class="product-stock" style="color:' + (p.stock_quantity > 0 ? '#4caf50' : '#d9534f') + ';">' +
                    (p.stock_quantity > 0 ? ' наличии: ' + p.stock_quantity : 'ет в наличии') +
                '</div>' +
            '</div>' +
            (p.description ? '<div class="product-desc">' + esc(p.description) + '</div>' : '') +
            (specsHtml ? '<div class="specs-grid">' + specsHtml + '</div>' : '') +
            '<div class="product-actions">' +
                (currentSlot ? '<button class="btn-primary" id="product-select-btn">ыбрать</button>' : '') +
                adminActions +
                '<button class="btn-ghost" id="product-back-btn">азад</button>' +
            '</div>';
        document.getElementById('product-modal-close').addEventListener('click', function() { document.getElementById('product-modal').style.display = 'none'; });
        document.getElementById('product-back-btn').addEventListener('click', function() { document.getElementById('product-modal').style.display = 'none'; });
        var selectBtn = document.getElementById('product-select-btn');
        if (selectBtn) {
            selectBtn.addEventListener('click', function() {
                selectProduct(p);
                document.getElementById('product-modal').style.display = 'none';
            });
        }
        var pEditBtn = document.getElementById('product-edit-btn');
        if (pEditBtn) { pEditBtn.addEventListener('click', function() { document.getElementById('product-modal').style.display = 'none'; openProductEditor('edit', currentSlot || p.category_id, p); }); }
        var pDelBtn = document.getElementById('product-del-btn');
        if (pDelBtn) { pDelBtn.addEventListener('click', function() { document.getElementById('product-modal').style.display = 'none'; deleteProduct(p.product_id, p.name); }); }
        document.getElementById('product-modal').style.display = 'flex';
    }

    // Modal events
    document.getElementById('modal-close').addEventListener('click', closeCatalogModal);
    document.getElementById('catalog-modal').addEventListener('click', function(e) { if (e.target === e.currentTarget) closeCatalogModal(); });
    document.getElementById('product-modal').addEventListener('click', function(e) { if (e.target === e.currentTarget) e.currentTarget.style.display = 'none'; });
    document.getElementById('modal-search').addEventListener('input', debounce(function() { currentPage = 1; loadProducts(); }, 300));
    document.getElementById('modal-sort').addEventListener('change', function() { currentPage = 1; loadProducts(); });
    document.getElementById('modal-instock').addEventListener('change', function() { currentPage = 1; loadProducts(); });
    document.getElementById('modal-prev').addEventListener('click', function() { if (currentPage > 1) { currentPage--; loadProducts(); } });
    document.getElementById('modal-next').addEventListener('click', function() { if (currentPage < lastPage) { currentPage++; loadProducts(); } });

    // ========== Export Build ==========
    document.getElementById('export-build').addEventListener('click', function() {
        var items = [];
        for (var slot in selection) {
            var prods = selection[slot];
            for (var i = 0; i < prods.length; i++) {
                if (prods[i]) items.push({ slot: slot, product_id: prods[i].product_id, name: prods[i].name, quantity: 1, unit_price: prods[i].price || 0 });
            }
        }
        if (!items.length) { showNotification('warning', 'е выбран ни один компонент.'); return; }
        var payload = {
            build_id: 'build-' + Date.now(),
            created_at: new Date().toISOString(),
            items: items,
            total_price: items.reduce(function(s, i) { return s + i.unit_price; }, 0)
        };
        var blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url; a.download = 'build-' + Date.now() + '.json';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
        showNotification('success', 'Сборка экспортирована в JSON-файл.');
    });

    // ========== Templates ==========
    var templatesCache = [];

    async function loadTemplates() {
        var container = document.getElementById('templates-list');
        if (!container) return;
        var res = await fetch('/api/templates');
        templatesCache = await res.json();
        container.innerHTML = '';
        if (!templatesCache.length) {
            container.innerHTML = '<div style="color:#999;font-size:13px;">Нет доступных конфигураций</div>';
        } else {
            templatesCache.forEach(function(t) {
                var el = document.createElement('div');
                el.className = 'template-item';
                var btnsHtml = '<div style="display:flex;gap:4px;align-items:center;">' +
                    '<button data-template-apply="' + esc(t.template_id) + '">рименить</button>';
                if (isPrivileged()) {
                    btnsHtml += '<button class="btn-tpl-del" data-tpl-del="' + esc(t.template_id) + '">\u2715</button>';
                }
                btnsHtml += '</div>';
                el.innerHTML = '<div class="meta" data-template-detail="' + esc(t.template_id) + '" style="cursor:pointer;">' + esc(t.name) + '</div>' + btnsHtml;
                el.querySelector('[data-template-detail]').addEventListener('click', function(e) {
                    e.stopPropagation();
                    showTemplateDetail(t);
                });
                el.querySelector('[data-template-apply]').addEventListener('click', function(e) {
                    e.stopPropagation();
                    applyTemplate(t);
                });
                var delBtn = el.querySelector('[data-tpl-del]');
                if (delBtn) { delBtn.addEventListener('click', function(e) { e.stopPropagation(); deleteTemplate(t.template_id, t.name); }); }
                container.appendChild(el);
            });
        }
        if (isPrivileged()) {
            var existing = container.parentElement.querySelector('.btn-tpl-add');
            if (!existing) {
                var addBtn = document.createElement('button');
                addBtn.className = 'btn-tpl-add';
                addBtn.textContent = '+ Создать конфигурацию';
                addBtn.addEventListener('click', openTemplateEditor);
                container.parentElement.appendChild(addBtn);
            }
        }
    }

    function applyTemplate(template) {
        var items = template.items;
        for (var slot in selection) { selection[slot] = [null]; }
        for (var slot2 in items) {
            var info = items[slot2];
            if (info && info.product_id) {
                selection[slot2] = [{ product_id: info.product_id, name: info.name, price: info.price, main_image_path: info.main_image_path || '' }];
            }
        }
        renderAllSlots();
        updateSummary();
        showNotification('info', 'Шаблон «' + template.name + '» применён.');
    }

    // ========== Template Detail Modal ==========
    async function showTemplateDetail(template) {
        var modal = document.getElementById('template-detail-modal');
        document.getElementById('tpl-detail-title').textContent = template.name;
        var itemsDiv = document.getElementById('tpl-detail-items');
        itemsDiv.innerHTML = '<div style="text-align:center;color:#999;">агрузка...</div>';
        modal.style.display = 'flex';

        var items = template.items || {};
        var total = 0;
        itemsDiv.innerHTML = '';
        var slots = Object.keys(items);
        if (slots.length === 0) {
            itemsDiv.innerHTML = '<div style="color:#999;">Конфигурация пуста</div>';
        }

        for (var si3 = 0; si3 < slots.length; si3++) {
            var slotName = slots[si3];
            var itemInfo = items[slotName];
            if (!itemInfo || !itemInfo.product_id) continue;
            var price = itemInfo.price || 0;
            total += price;
            var imgHtml = '\uD83D\uDCE6';
            try {
                var pRes = await fetch('/api/product/' + itemInfo.product_id);
                if (pRes.ok) {
                    var pData = await pRes.json();
                    if (pData.main_image_path) { imgHtml = '<img src="/' + esc(pData.main_image_path) + '" alt="">'; }
                }
            } catch(e) {}
            var itemEl = document.createElement('div');
            itemEl.className = 'tpl-detail-item';
            itemEl.innerHTML =
                '<div class="tpl-detail-img">' + imgHtml + '</div>' +
                '<div class="tpl-detail-info">' +
                    '<div class="tpl-detail-slot">' + esc(SLOT_LABELS[slotName] || slotName) + '</div>' +
                    '<div class="tpl-detail-name">' + esc(itemInfo.name || itemInfo.product_id) + '</div>' +
                    '<div class="tpl-detail-price">' + formatPrice(price) + '</div>' +
                '</div>';
            itemsDiv.appendChild(itemEl);
        }
        document.getElementById('tpl-detail-total').textContent = formatPrice(total);
        modal._template = template;
    }

    document.getElementById('tpl-detail-close').addEventListener('click', function() { document.getElementById('template-detail-modal').style.display = 'none'; });
    document.getElementById('tpl-detail-back').addEventListener('click', function() { document.getElementById('template-detail-modal').style.display = 'none'; });
    document.getElementById('tpl-detail-apply').addEventListener('click', function() {
        var modal = document.getElementById('template-detail-modal');
        if (modal._template) applyTemplate(modal._template);
        modal.style.display = 'none';
    });
    document.getElementById('template-detail-modal').addEventListener('click', function(e) { if (e.target === e.currentTarget) e.currentTarget.style.display = 'none'; });

    async function deleteTemplate(templateId, name) {
        var ok = await showConfirm('Удалить конфигурацию', 'Удалить «' + name + '»?');
        if (!ok) return;
        var res = await fetch('/api/templates/' + templateId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF } });
        if (res.ok) { showNotification('success', 'Конфигурация удалена.'); loadTemplates(); }
        else { showNotification('error', 'Ошибка при удалении конфигурации.'); }
    }

    // ========== Template Editor ==========
    function openTemplateEditor() {
        document.getElementById('tpl-editor-name').value = '';
        document.getElementById('tpl-editor-public').checked = true;
        var itemsDiv = document.getElementById('tpl-editor-items');
        itemsDiv.innerHTML = '';
        var hasItems = false;
        for (var slot in selection) {
            var prods = getAllSelected(slot);
            for (var pi = 0; pi < prods.length; pi++) {
                hasItems = true;
                var d = document.createElement('div');
                d.textContent = SLOT_LABELS[slot] + ': ' + prods[pi].name;
                d.style.padding = '4px 0';
                itemsDiv.appendChild(d);
            }
        }
        if (!hasItems) { itemsDiv.innerHTML = '<div style="color:#d9534f;">Сборка пуста. Добавьте компоненты перед сохранением.</div>'; }
        document.getElementById('template-editor-modal').style.display = 'flex';
    }

    document.getElementById('tpl-editor-close').addEventListener('click', function() { document.getElementById('template-editor-modal').style.display = 'none'; });
    document.getElementById('tpl-editor-cancel').addEventListener('click', function() { document.getElementById('template-editor-modal').style.display = 'none'; });
    document.getElementById('template-editor-modal').addEventListener('click', function(e) { if (e.target === e.currentTarget) e.currentTarget.style.display = 'none'; });
    document.getElementById('tpl-editor-save').addEventListener('click', async function() {
        var name = document.getElementById('tpl-editor-name').value.trim();
        if (!name) { showNotification('warning', 'Введите название конфигурации.'); return; }
        var items = [];
        for (var slot in selection) {
            var prods = getAllSelected(slot);
            for (var pi2 = 0; pi2 < prods.length; pi2++) {
                items.push({ slot_type: slot, product_id: prods[pi2].product_id });
            }
        }
        if (!items.length) { showNotification('warning', 'Сборка пуста.'); return; }
        var res = await fetch('/api/templates', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ name: name, items: items, is_public: document.getElementById('tpl-editor-public').checked }),
        });
        if (res.ok) {
            showNotification('success', 'Конфигурация «' + name + '» создана.');
            document.getElementById('template-editor-modal').style.display = 'none';
            loadTemplates();
        } else {
            var err = await res.json().catch(function() { return {}; });
            showNotification('error', err.message || 'Ошибка при сохранении.');
        }
    });

    loadTemplates();

    function openProductEditor(mode, slot, product) {
        editorMode = mode;
        var editorSlot = slot || currentSlot;
        document.getElementById('editor-title').textContent = mode === 'add' ? 'обавить товар' : 'едактировать товар';
        document.getElementById('editor-category').textContent = SLOT_LABELS[editorSlot] || editorSlot;
        document.getElementById('editor-category').dataset.slot = editorSlot;

        var pidInput = document.getElementById('editor-pid');
        var nameInput = document.getElementById('editor-name');
        var priceInput = document.getElementById('editor-price');
        var stockInput = document.getElementById('editor-stock');
        var descInput = document.getElementById('editor-desc');
        var imgPathInput = document.getElementById('editor-image-path');
        var imgFileInput = document.getElementById('editor-image-file');

        imgFileInput.value = '';
        editorUploadedImagePath = '';

        if (mode === 'edit' && product) {
            editorProductId = product.product_id;
            pidInput.value = product.product_id;
            pidInput.readOnly = true;
            nameInput.value = product.name || '';
            priceInput.value = product.price || 0;
            stockInput.value = product.stock_quantity || 0;
            descInput.value = product.description || '';
            imgPathInput.value = product.main_image_path || '';
            editorUploadedImagePath = product.main_image_path || '';
        } else {
            editorProductId = null;
            pidInput.value = '';
            pidInput.readOnly = false;
            nameInput.value = '';
            priceInput.value = '';
            stockInput.value = 0;
            descInput.value = '';
            imgPathInput.value = '';
        }

        renderEditorImage(editorUploadedImagePath);
        renderEditorSpecs(editorSlot, mode === 'edit' && product ? product.specs_data : null);
        document.getElementById('product-editor-modal').style.display = 'flex';
    }

    function renderEditorImage(path) {
        var preview = document.getElementById('editor-image-preview');
        if (path) {
            preview.innerHTML = '<img src="/' + esc(path) + '" alt="Preview"><div class="upload-hint">ажмите для замены</div>';
        } else {
            preview.innerHTML = '<span style="color:#999;">ажмите для выбора</span><div class="upload-hint">ажмите для загрузки</div>';
        }
    }

    document.getElementById('editor-image-preview').addEventListener('click', function() {
        document.getElementById('editor-image-file').click();
    });

    document.getElementById('editor-image-file').addEventListener('change', async function() {
        var file = this.files[0];
        if (!file) return;
        var slot = document.getElementById('editor-category').dataset.slot;
        var formData = new FormData();
        formData.append('image', file);
        formData.append('category', slot);

        var preview = document.getElementById('editor-image-preview');
        preview.innerHTML = '<span style="color:#999;">агрузка...</span>';

        try {
            var res = await fetch('/api/product/upload-image', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF },
                body: formData
            });
            if (res.ok) {
                var data = await res.json();
                editorUploadedImagePath = data.path;
                document.getElementById('editor-image-path').value = data.path;
                renderEditorImage(data.path);
            } else {
                var err = await res.json().catch(function() { return {}; });
                showNotification('error', err.message || 'шибка загрузки изображения.');
                renderEditorImage(editorUploadedImagePath);
            }
        } catch(e) {
            showNotification('error', 'шибка загрузки изображения.');
            renderEditorImage(editorUploadedImagePath);
        }
    });

    function renderEditorSpecs(slot, existingSpecs) {
        var container = document.getElementById('editor-specs-list');
        container.innerHTML = '';
        var defs = SPEC_DEFS[slot] || [];
        if (!defs.length) {
            container.innerHTML = '<div style="color:#999;font-size:13px;">ет характеристик для данной категории.</div>';
            return;
        }
        defs.forEach(function(def) {
            var row = document.createElement('div');
            row.className = 'spec-editor-row';
            var val = '';
            if (existingSpecs && existingSpecs[def.key] !== null && existingSpecs[def.key] !== undefined) {
                val = String(existingSpecs[def.key]);
            }
            row.innerHTML =
                '<div>' +
                    '<div class="spec-label">' + esc(def.label) + '</div>' +
                    '<div class="spec-hint">' + esc(def.hint) + '</div>' +
                '</div>' +
                '<div>' +
                    '<input data-spec-key="' + esc(def.key) + '" type="' + (def.type || 'text') + '" value="' + esc(val) + '" placeholder="—">' +
                '</div>';
            container.appendChild(row);
        });
    }

    function closeProductEditor() {
        document.getElementById('product-editor-modal').style.display = 'none';
    }

    document.getElementById('editor-close').addEventListener('click', closeProductEditor);
    document.getElementById('editor-cancel').addEventListener('click', closeProductEditor);
    document.getElementById('product-editor-modal').addEventListener('click', function(e) {
        if (e.target === e.currentTarget) closeProductEditor();
    });
    document.getElementById('editor-save').addEventListener('click', async function() {
        var slot = document.getElementById('editor-category').dataset.slot;
        var pid = document.getElementById('editor-pid').value.trim();
        var name = document.getElementById('editor-name').value.trim();
        var price = parseInt(document.getElementById('editor-price').value) || 0;
        var stock = parseInt(document.getElementById('editor-stock').value) || 0;
        var desc = document.getElementById('editor-desc').value.trim();
        var imgPath = document.getElementById('editor-image-path').value.trim();

        if (!name) { showNotification('warning', 'ведите название товара.'); return; }
        if (!pid && editorMode === 'add') { showNotification('warning', 'ведите артикул товара.'); return; }

        var specs = {};
        document.querySelectorAll('#editor-specs-list [data-spec-key]').forEach(function(input) {
            var v = input.value.trim();
            if (v !== '') {
                specs[input.dataset.specKey] = input.type === 'number' ? (parseFloat(v) || 0) : v;
            }
        });

        var body = {
            name: name,
            price: price,
            stock_quantity: stock,
            description: desc,
            main_image_path: imgPath,
            specs: specs
        };

        var url, method;
        if (editorMode === 'add') {
            body.product_id = pid;
            body.category_id = slot;
            url = '/api/products';
            method = 'POST';
        } else {
            url = '/api/product/' + editorProductId;
            method = 'PUT';
        }

        var res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(body)
        });

        if (res.ok) {
            showNotification('success', editorMode === 'add' ? 'Товар добавлен.' : 'Товар обновлён.');
            closeProductEditor();
            loadProducts();
        } else {
            var err2 = await res.json().catch(function() { return {}; });
            var msg = '';
            if (err2.errors) {
                for (var field in err2.errors) { msg += err2.errors[field].join(', ') + ' '; }
            } else {
                msg = err2.message || 'шибка при сохранении.';
            }
            showNotification('error', msg);
        }
    });

    async function deleteProduct(productId, productName) {
        var ok = await showConfirm('далить товар', 'далить «' + productName + '»? то действие необратимо.');
        if (!ok) return;
        var res = await fetch('/api/product/' + productId, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        if (res.ok) {
            showNotification('success', 'Товар удалён.');
            loadProducts();
        } else {
            showNotification('error', 'шибка при удалении товара.');
        }
    }

    var addProductBtn = document.getElementById('catalog-add-product');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (currentSlot) openProductEditor('add', currentSlot, null);
        });
    }

    // ========== Account popup ==========
    var nameBtn = document.getElementById('account-name-btn');
    var popup = document.getElementById('account-popup');
    if (nameBtn && popup) {
        nameBtn.addEventListener('click', function() { popup.classList.toggle('open'); });
        document.addEventListener('click', function(e) {
            if (!nameBtn.contains(e.target) && !popup.contains(e.target)) popup.classList.remove('open');
        });
    }

    // ========== Init ==========
    renderAllSlots();
    updateSummary();

    // ========== Helpers ==========
    function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function debounce(fn, ms) { var t; return function() { var a = arguments; clearTimeout(t); t = setTimeout(function() { fn.apply(null, a); }, ms); }; }
})();