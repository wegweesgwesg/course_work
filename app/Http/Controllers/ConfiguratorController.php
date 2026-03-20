<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Template;
use App\Models\TemplateItem;
use Illuminate\Http\Request;

class ConfiguratorController extends Controller
{
    public function index()
    {
        $categories = [
            'cpu' => ['title' => 'Процессоры', 'slot_name' => 'Процессор (CPU)', 'brief_default' => 'Не выбран'],
            'motherboard' => ['title' => 'Материнские платы', 'slot_name' => 'Материнская плата', 'brief_default' => 'Не выбрана'],
            'ram' => ['title' => 'Оперативная память', 'slot_name' => 'Оперативная память (RAM)', 'brief_default' => 'Не выбрана'],
            'gpu' => ['title' => 'Видеокарты', 'slot_name' => 'Видеокарта (GPU)', 'brief_default' => 'Не выбрана'],
            'storage' => ['title' => 'Накопители', 'slot_name' => 'Накопитель (Storage)', 'brief_default' => 'Не выбран'],
            'psu' => ['title' => 'Блоки питания', 'slot_name' => 'Блок питания (PSU)', 'brief_default' => 'Не выбран'],
            'cooler' => ['title' => 'Системы охлаждения', 'slot_name' => 'Кулер / СЖО', 'brief_default' => 'Не выбран'],
            'case' => ['title' => 'Корпуса', 'slot_name' => 'Корпус (Case)', 'brief_default' => 'Не выбран'],
        ];

        $templates = Template::where('is_public', true)->with('items')->get();

        return view('configurator', compact('categories', 'templates'));
    }
}
