<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    private function jsonPath(string $file): string
    {
        return base_path('../catalog_jsons/' . $file);
    }

    private function loadJson(string $file): array
    {
        $path = $this->jsonPath($file);
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?: [];
    }

    private function resolveImagePath(array $product): string
    {
        if (!empty($product['main_image_path'])) {
            return $product['main_image_path'];
        }
        $categoryId = $product['category_id'] ?? '';
        $productId  = $product['product_id'] ?? '';
        $imagePath  = 'images/' . $categoryId . '/' . $productId . '.png';
        if (file_exists(public_path($imagePath))) {
            return $imagePath;
        }
        return '';
    }

    // Returns image path for a product already in the DB
    private function productImagePath(string $productId): string
    {
        $row = DB::table('products')
            ->where('product_id', $productId)
            ->first(['main_image_path', 'category_id']);
        if (!$row) {
            return '';
        }
        if (!empty($row->main_image_path)) {
            return $row->main_image_path;
        }
        $path = 'images/' . $row->category_id . '/' . $productId . '.png';
        return file_exists(public_path($path)) ? $path : '';
    }

    public function run(): void
    {
        // 1. Users
        DB::table('users')->insert([
            ['first_name' => 'Admin', 'last_name' => '', 'email' => 'admin@example.com', 'password' => Hash::make('Admin123'), 'role' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['first_name' => 'Alice', 'last_name' => '', 'email' => 'alice@example.com', 'password' => Hash::make('Alice123'), 'role' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['first_name' => 'Bob', 'last_name' => '', 'email' => 'bob@example.com', 'password' => Hash::make('Bob12345'), 'role' => 'content_manager', 'created_at' => now(), 'updated_at' => now()],
            ['first_name' => 'Carol', 'last_name' => '', 'email' => 'carol@example.com', 'password' => Hash::make('Carol123'), 'role' => 'warehouse_manager', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Categories
        foreach ($this->loadJson('categories.json') as $cat) {
            DB::table('categories')->insert([
                'category_id' => $cat['category_id'],
                'name' => $cat['name'],
                'power_w' => $cat['power_w'] ?? null,
            ]);
        }

        // 3. Products
        foreach ($this->loadJson('products.json') as $p) {
            DB::table('products')->insert([
                'product_id' => $p['product_id'],
                'category_id' => $p['category_id'],
                'sku' => $p['sku'] ?? $p['product_id'],
                'name' => $p['name'],
                'price' => $p['price'] ?? 0,
                'stock_quantity' => $p['stock_quantity'] ?? 0,
                'description' => $p['description'] ?? '',
                'main_image_path' => $this->resolveImagePath($p),
                'is_active' => $p['is_active'] ?? true,
                'power_w' => $p['power_w'] ?? null,
            ]);
        }

        // 4. CPUs
        foreach ($this->loadJson('cpus.json') as $c) {
            DB::table('cpus')->insert([
                'product_id' => $c['product_id'],
                'socket' => $c['socket'] ?? null,
                'tdp_w' => $c['tdp_w'] ?? $c['tdp'] ?? null,
                'max_mem_speed' => $c['max_mem_speed'] ?? null,
                'brand' => $c['brand'] ?? null,
                'cores' => $c['cores'] ?? null,
                'threads' => $c['threads'] ?? null,
                'base_clock' => $c['base_clock'] ?? null,
                'boost_clock' => $c['boost_clock'] ?? null,
                'integrated_graphics' => (isset($c['integrated_graphics']) && $c['integrated_graphics'] !== false) ? $c['integrated_graphics'] : null,
                'lithography_nm' => $c['lithography_nm'] ?? null,
                'cache_mb' => $c['cache_mb'] ?? null,
                'power_w' => $c['power_w'] ?? null,
            ]);
        }

        // 5. Motherboards
        foreach ($this->loadJson('motherboards.json') as $m) {
            DB::table('motherboards')->insert([
                'product_id' => $m['product_id'],
                'socket' => $m['socket'] ?? null,
                'form_factor' => $m['form_factor'] ?? null,
                'ram_slots' => $m['ram_slots'] ?? null,
                'max_ram' => $m['max_ram'] ?? null,
                'ram_speed_max' => $m['ram_speed_max'] ?? null,
                'm2_slots' => $m['m2_slots'] ?? null,
                'pcie_version' => $m['pcie_version'] ?? null,
                'cpu_fan_headers' => $m['cpu_fan_headers'] ?? null,
                'sata_ports' => $m['sata_ports'] ?? null,
                'chipset' => $m['chipset'] ?? null,
                'brand' => $m['brand'] ?? null,
                'power_w' => $m['power_w'] ?? null,
            ]);
        }

        // 6. RAMs
        foreach ($this->loadJson('rams.json') as $r) {
            DB::table('rams')->insert([
                'product_id' => $r['product_id'],
                'ram_type' => $r['ram_type'] ?? null,
                'size_gb' => $r['size_gb'] ?? null,
                'speed_mhz' => $r['speed_mhz'] ?? null,
                'power_w' => $r['power_w'] ?? null,
            ]);
        }

        // 7. GPUs
        foreach ($this->loadJson('gpus.json') as $g) {
            DB::table('gpus')->insert([
                'product_id' => $g['product_id'],
                'power_draw_w' => $g['power_draw_w'] ?? null,
                'length_mm' => $g['length_mm'] ?? null,
                'power_w' => $g['power_w'] ?? null,
            ]);
        }

        // 8. Storages
        foreach ($this->loadJson('storages.json') as $s) {
            DB::table('storages')->insert([
                'product_id' => $s['product_id'],
                'power_draw_w' => $s['power_draw_w'] ?? null,
                'interface_type' => $s['interface_type'] ?? null,
                'pcie_version' => $s['pcie_version'] ?? null,
                'capacity_gb' => $s['capacity_gb'] ?? null,
                'power_w' => $s['power_w'] ?? null,
            ]);
        }

        // 9. PSUs
        foreach ($this->loadJson('psus.json') as $p) {
            DB::table('psus')->insert([
                'product_id' => $p['product_id'],
                'power_w' => $p['power_w'] ?? null,
            ]);
        }

        // 10. Coolers
        foreach ($this->loadJson('coolers.json') as $c) {
            DB::table('coolers')->insert([
                'product_id' => $c['product_id'],
                'cooler_height_mm' => $c['cooler_height_mm'] ?? null,
                'connector_pin_count' => $c['connector_pin_count'] ?? null,
                'radiator_size_mm' => $c['radiator_size_mm'] ?? null,
                'power_w' => $c['power_w'] ?? null,
            ]);
        }

        // 11. Cases
        foreach ($this->loadJson('cases.json') as $c) {
            DB::table('cases')->insert([
                'product_id' => $c['product_id'],
                'form_factor' => json_encode($c['form_factor'] ?? []),
                'max_gpu_length_mm' => $c['max_gpu_length_mm'] ?? null,
                'max_cooler_height_mm' => $c['max_cooler_height_mm'] ?? null,
                'm2_slots' => $c['m2_slots'] ?? null,
                'drive_bays' => $c['drive_bays'] ?? null,
                'front_usb_c' => $c['front_usb_c'] ?? false,
                'audio_header' => $c['audio_header'] ?? true,
                'power_w' => $c['power_w'] ?? null,
            ]);
        }

        // 12. Product connectors
        foreach ($this->loadJson('product_connectors.json') as $pc) {
            DB::table('product_connectors')->insert([
                'connector_id' => $pc['connector_id'],
                'product_id' => $pc['product_id'],
                'connector_type' => $pc['connector_type'],
                'power_w' => $pc['power_w'] ?? null,
            ]);
        }

        // 13. Case radiator supports
        foreach ($this->loadJson('case_radiator_support.json') as $cr) {
            DB::table('case_radiator_supports')->insert([
                'id' => $cr['id'],
                'product_id' => $cr['product_id'],
                'size_mm' => $cr['size_mm'],
                'power_w' => $cr['power_w'] ?? null,
            ]);
        }

        // 14. Motherboard slots
        foreach ($this->loadJson('motherboard_slots.json') as $ms) {
            DB::table('motherboard_slots')->insert([
                'product_id' => $ms['product_id'],
                'slot_type' => $ms['slot_type'] ?? ($ms['pcie_type'] ?? 'unknown'),
                'count' => $ms['count'] ?? $ms['quantity'] ?? 0,
            ]);
        }

        // 15. Templates
        foreach ($this->loadJson('templates.json') as $t) {
            $createdAt = now();
            if (!empty($t['created_at'])) {
                try { $createdAt = \Carbon\Carbon::parse($t['created_at']); } catch (\Exception $e) {}
            }
            DB::table('templates')->insert([
                'template_id' => $t['template_id'],
                'name' => $t['name'],
                'author_user_id' => $t['author_user_id'] ?? null,
                'is_public' => $t['is_public'] ?? false,
                'power_w' => $t['power_w'] ?? null,
                'created_at' => $createdAt,
                'updated_at' => now(),
            ]);
        }

        // 16. Template items
        foreach ($this->loadJson('template_items.json') as $ti) {
            DB::table('template_items')->insert([
                'template_item_id' => $ti['template_item_id'],
                'template_id' => $ti['template_id'],
                'slot_type' => $ti['slot_type'],
                'product_id' => $ti['product_id'],
                'power_w' => $ti['power_w'] ?? null,
            ]);
        }

        // 17. Published builds
        DB::table('published_builds')->insert([
            [
                'user_id' => 1, // Admin
                'name' => 'Игровой ПК на RTX 3070',
                'description' => 'Мощная сборка для игр в 1440p. Отлично тянет все современные игры на высоких/ультра настройках. Собирал для себя, рекомендую!',
                'build_data' => json_encode([
                    'cpu' => ['product_id' => 'cpu-ryzen5-5600x', 'name' => 'AMD Ryzen 5 5600X', 'price' => 16990, 'main_image_path' => $this->productImagePath('cpu-ryzen5-5600x')],
                    'motherboard' => ['product_id' => 'mb-asus-b550', 'name' => 'ASUS B550', 'price' => 12000, 'main_image_path' => $this->productImagePath('mb-asus-b550')],
                    'ram' => ['product_id' => 'ram-16-ddr4-3200', 'name' => '16GB DDR4 3200', 'price' => 42500, 'main_image_path' => $this->productImagePath('ram-16-ddr4-3200')],
                    'gpu' => ['product_id' => 'gpu-rtx3070', 'name' => 'NVIDIA RTX 3070', 'price' => 55000, 'main_image_path' => $this->productImagePath('gpu-rtx3070')],
                    'storage' => ['product_id' => 'nvme-1tb', 'name' => 'NVMe 1TB', 'price' => 9000, 'main_image_path' => $this->productImagePath('nvme-1tb')],
                    'psu' => ['product_id' => 'psu-750w', 'name' => 'PSU 750W', 'price' => 7000, 'main_image_path' => $this->productImagePath('psu-750w')],
                    'cooler' => ['product_id' => 'cooler-tower-150mm', 'name' => 'Tower Cooler 150mm', 'price' => 3800, 'main_image_path' => $this->productImagePath('cooler-tower-150mm')],
                    'case' => ['product_id' => 'case-atx-glass', 'name' => 'ATX Case Glass', 'price' => 8500, 'main_image_path' => $this->productImagePath('case-atx-glass')],
                ]),
                'total_price' => 154790,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => 1, // Admin
                'name' => 'Бюджетный офисный ПК',
                'description' => 'Тихая и экономичная сборка для офисных задач, браузера и документов. Встроенная графика, без дискретной видеокарты.',
                'build_data' => json_encode([
                    'cpu' => ['product_id' => 'cpu-ryzen5-3400g', 'name' => 'AMD Ryzen 5 3400G', 'price' => 12990, 'main_image_path' => $this->productImagePath('cpu-ryzen5-3400g')],
                    'motherboard' => ['product_id' => 'mb-asus-b550', 'name' => 'ASUS B550', 'price' => 12000, 'main_image_path' => $this->productImagePath('mb-asus-b550')],
                    'ram' => ['product_id' => 'ram-8-ddr4-2666', 'name' => '8GB DDR4 2666', 'price' => 2200, 'main_image_path' => $this->productImagePath('ram-8-ddr4-2666')],
                    'storage' => ['product_id' => 'ssd-480gb', 'name' => 'SATA SSD 480GB', 'price' => 3200, 'main_image_path' => $this->productImagePath('ssd-480gb')],
                    'psu' => ['product_id' => 'psu-450w', 'name' => 'PSU 450W', 'price' => 3200, 'main_image_path' => $this->productImagePath('psu-450w')],
                    'cooler' => ['product_id' => 'cooler-92mm', 'name' => 'Low-profile Cooler 92mm', 'price' => 1500, 'main_image_path' => $this->productImagePath('cooler-92mm')],
                    'case' => ['product_id' => 'case-micro-atx', 'name' => 'Micro-ATX Case', 'price' => 4200, 'main_image_path' => $this->productImagePath('case-micro-atx')],
                ]),
                'total_price' => 39290,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_id' => 3, // Bob (content_manager)
                'name' => 'Топовый ПК для стриминга',
                'description' => 'Сборка для стриминга и видеомонтажа. Ryzen 9 + RTX 3080 — потянет любую задачу. 32GB оперативки для многозадачности.',
                'build_data' => json_encode([
                    'cpu' => ['product_id' => 'cpu-ryzen9-5900x', 'name' => 'AMD Ryzen 9 5900X', 'price' => 45990, 'main_image_path' => $this->productImagePath('cpu-ryzen9-5900x')],
                    'motherboard' => ['product_id' => 'mb-gigabyte-x570-aorus', 'name' => 'Gigabyte X570 AORUS (ATX)', 'price' => 19990, 'main_image_path' => $this->productImagePath('mb-gigabyte-x570-aorus')],
                    'ram' => ['product_id' => 'ram-32-ddr4-3200', 'name' => '32GB DDR4 3200', 'price' => 6990, 'main_image_path' => $this->productImagePath('ram-32-ddr4-3200')],
                    'gpu' => ['product_id' => 'gpu-rtx3080', 'name' => 'NVIDIA RTX 3080', 'price' => 80000, 'main_image_path' => $this->productImagePath('gpu-rtx3080')],
                    'storage' => ['product_id' => 'nvme-2tb', 'name' => 'NVMe 2TB', 'price' => 16000, 'main_image_path' => $this->productImagePath('nvme-2tb')],
                    'psu' => ['product_id' => 'psu-850w', 'name' => 'PSU 850W', 'price' => 9500, 'main_image_path' => $this->productImagePath('psu-850w')],
                    'cooler' => ['product_id' => 'aio-360', 'name' => 'AIO 360mm', 'price' => 12000, 'main_image_path' => $this->productImagePath('aio-360')],
                    'case' => ['product_id' => 'case-full-tower', 'name' => 'Full Tower', 'price' => 15000, 'main_image_path' => $this->productImagePath('case-full-tower')],
                ]),
                'total_price' => 205470,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 2, // Alice (user)
                'name' => 'Компактная сборка Mini-ITX',
                'description' => 'Маленький, но мощный ПК в корпусе Mini-ITX. Идеально встаёт на стол, не занимает много места.',
                'build_data' => json_encode([
                    'cpu' => ['product_id' => 'cpu-intel-i5-12400', 'name' => 'Intel Core i5-12400', 'price' => 18990, 'main_image_path' => $this->productImagePath('cpu-intel-i5-12400')],
                    'motherboard' => ['product_id' => 'mb-asus-b650i-mini', 'name' => 'ASUS B650I (Mini-ITX)', 'price' => 14990, 'main_image_path' => $this->productImagePath('mb-asus-b650i-mini')],
                    'ram' => ['product_id' => 'ram-16-ddr4-3200', 'name' => '16GB DDR4 3200', 'price' => 42500, 'main_image_path' => $this->productImagePath('ram-16-ddr4-3200')],
                    'gpu' => ['product_id' => 'gpu-rtx4060', 'name' => 'NVIDIA RTX 4060', 'price' => 30000, 'main_image_path' => $this->productImagePath('gpu-rtx4060')],
                    'storage' => ['product_id' => 'nvme-1tb', 'name' => 'NVMe 1TB', 'price' => 9000, 'main_image_path' => $this->productImagePath('nvme-1tb')],
                    'psu' => ['product_id' => 'psu-sfx-650w', 'name' => 'PSU SFX 650W', 'price' => 9500, 'main_image_path' => $this->productImagePath('psu-sfx-650w')],
                    'cooler' => ['product_id' => 'cooler-lowprofile-45', 'name' => 'Low-profile Cooler 45mm', 'price' => 2200, 'main_image_path' => $this->productImagePath('cooler-lowprofile-45')],
                    'case' => ['product_id' => 'case-mini-itx', 'name' => 'Mini ITX', 'price' => 4500, 'main_image_path' => $this->productImagePath('case-mini-itx')],
                ]),
                'total_price' => 131680,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 1, // Admin
                'name' => 'Ультимативный ПК на i9-13900K',
                'description' => 'Максимальная производительность без компромиссов. Для профессиональной работы и гейминга в 4K.',
                'build_data' => json_encode([
                    'cpu' => ['product_id' => 'cpu-intel-i9-13900k', 'name' => 'Intel Core i9-13900K', 'price' => 84990, 'main_image_path' => $this->productImagePath('cpu-intel-i9-13900k')],
                    'motherboard' => ['product_id' => 'mb-asus-z690-a', 'name' => 'ASUS Z690-A (ATX)', 'price' => 24990, 'main_image_path' => $this->productImagePath('mb-asus-z690-a')],
                    'ram' => ['product_id' => 'ram-32-ddr5-5600', 'name' => '32GB DDR5 5600', 'price' => 12990, 'main_image_path' => $this->productImagePath('ram-32-ddr5-5600')],
                    'gpu' => ['product_id' => 'gpu-rtx3090', 'name' => 'NVIDIA RTX 3090', 'price' => 140000, 'main_image_path' => $this->productImagePath('gpu-rtx3090')],
                    'storage' => ['product_id' => 'nvme-2tb', 'name' => 'NVMe 2TB', 'price' => 16000, 'main_image_path' => $this->productImagePath('nvme-2tb')],
                    'psu' => ['product_id' => 'psu-1000w', 'name' => 'PSU 1000W', 'price' => 14000, 'main_image_path' => $this->productImagePath('psu-1000w')],
                    'cooler' => ['product_id' => 'aio-360', 'name' => 'AIO 360mm', 'price' => 12000, 'main_image_path' => $this->productImagePath('aio-360')],
                    'case' => ['product_id' => 'case-full-tower', 'name' => 'Full Tower', 'price' => 15000, 'main_image_path' => $this->productImagePath('case-full-tower')],
                ]),
                'total_price' => 319970,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ],
            [
                'user_id' => 2, // Alice (user)
                'name' => 'Сборка для учёбы и работы',
                'description' => 'Недорогой ПК для студента. Хватает для всего: от учёбы до лёгких игр.',
                'build_data' => json_encode([
                    'cpu' => ['product_id' => 'cpu-intel-i3-12100', 'name' => 'Intel Core i3-12100', 'price' => 9990, 'main_image_path' => $this->productImagePath('cpu-intel-i3-12100')],
                    'motherboard' => ['product_id' => 'mb-gigabyte-b660m', 'name' => 'Gigabyte B660M (mATX)', 'price' => 8990, 'main_image_path' => $this->productImagePath('mb-gigabyte-b660m')],
                    'ram' => ['product_id' => 'ram-8-ddr4-2666', 'name' => '8GB DDR4 2666', 'price' => 2200, 'main_image_path' => $this->productImagePath('ram-8-ddr4-2666')],
                    'gpu' => ['product_id' => 'gpu-gtx1660', 'name' => 'NVIDIA GTX 1660', 'price' => 20000, 'main_image_path' => $this->productImagePath('gpu-gtx1660')],
                    'storage' => ['product_id' => 'ssd-480gb', 'name' => 'SATA SSD 480GB', 'price' => 3200, 'main_image_path' => $this->productImagePath('ssd-480gb')],
                    'psu' => ['product_id' => 'psu-550w', 'name' => 'PSU 550W', 'price' => 4200, 'main_image_path' => $this->productImagePath('psu-550w')],
                    'cooler' => ['product_id' => 'cooler-120mm', 'name' => 'Air Cooler 120mm', 'price' => 1800, 'main_image_path' => $this->productImagePath('cooler-120mm')],
                    'case' => ['product_id' => 'case-mid-atx', 'name' => 'MID Tower ATX', 'price' => 6000, 'main_image_path' => $this->productImagePath('case-mid-atx')],
                ]),
                'total_price' => 56380,
                'created_at' => now()->subHours(12),
                'updated_at' => now()->subHours(12),
            ],
        ]);

        // 18. Build votes (make some builds popular)
        DB::table('build_votes')->insert([
            ['published_build_id' => 1, 'user_id' => 2, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 1, 'user_id' => 3, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 1, 'user_id' => 4, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 3, 'user_id' => 1, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 3, 'user_id' => 2, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 5, 'user_id' => 2, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 5, 'user_id' => 3, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 5, 'user_id' => 4, 'vote' => -1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 4, 'user_id' => 1, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['published_build_id' => 2, 'user_id' => 3, 'vote' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 19. Build comments
        DB::table('build_comments')->insert([
            ['published_build_id' => 1, 'user_id' => 1, 'text' => 'Отличная сборка для гейминга! Сам собирал — всё работает как часы. Рекомендую всем, кто хочет играть в 1440p без просадок.', 'created_at' => now()->subDays(4), 'updated_at' => now()->subDays(4)],
            ['published_build_id' => 1, 'user_id' => 2, 'text' => 'Взяла похожую конфигурацию, очень довольна! Только кулер поменяла на AIO 240.', 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
            ['published_build_id' => 3, 'user_id' => 4, 'text' => 'Дорого, но мощно. Для стриминга самое то.', 'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)],
            ['published_build_id' => 5, 'user_id' => 3, 'text' => 'Мечта! Когда-нибудь соберу себе такой.', 'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(3)],
        ]);
    }
}
