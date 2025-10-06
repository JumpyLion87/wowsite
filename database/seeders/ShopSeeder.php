<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShopSeeder extends Seeder
{
    public function run(): void
    {
        // Очистить существующие данные (если нужно)
        // DB::connection('mysql')->table('shop_items')->truncate();
        
        // Тестовые товары для магазина
        $items = [
            // Услуги
            [
                'item_id' => 1,
                'category' => 'Service',
                'name' => 'Level Boost 60',
                'description' => 'Boost your character to level 60 instantly',
                'image' => 'services/level_boost_60.jpg',
                'point_cost' => 1000,
                'token_cost' => 0,
                'stock' => null,
                'entry' => null,
                'gold_amount' => 0,
                'level_boost' => 60,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            [
                'item_id' => 2,
                'category' => 'Service',
                'name' => 'Level Boost 70',
                'description' => 'Boost your character to level 70 instantly',
                'image' => 'services/level_boost_70.jpg',
                'point_cost' => 1500,
                'token_cost' => 0,
                'stock' => null,
                'entry' => null,
                'gold_amount' => 0,
                'level_boost' => 70,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            
            // Маунты
            [
                'item_id' => 3,
                'category' => 'Mount',
                'name' => 'Swift Spectral Tiger',
                'description' => 'A rare spectral tiger mount',
                'image' => 'items/spectral_tiger.jpg',
                'point_cost' => 0,
                'token_cost' => 500,
                'stock' => 10,
                'entry' => null,
                'gold_amount' => 0,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            [
                'item_id' => 4,
                'category' => 'Mount',
                'name' => 'Ashes of Al\'ar',
                'description' => 'A legendary phoenix mount',
                'image' => 'items/ashes_of_alar.jpg',
                'point_cost' => 0,
                'token_cost' => 750,
                'stock' => 5,
                'entry' => null,
                'gold_amount' => 0,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            
            // Питомцы
            [
                'item_id' => 5,
                'category' => 'Pet',
                'name' => 'Mini Diablo',
                'description' => 'A cute mini version of Diablo',
                'image' => 'items/mini_diablo.jpg',
                'point_cost' => 200,
                'token_cost' => 0,
                'stock' => null,
                'entry' => null,
                'gold_amount' => 0,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            
            // Золото
            [
                'item_id' => 6,
                'category' => 'Gold',
                'name' => '1000 Gold',
                'description' => 'Get 1000 gold for your character',
                'image' => 'gold/1000_gold.jpg',
                'point_cost' => 100,
                'token_cost' => 0,
                'stock' => null,
                'entry' => null,
                'gold_amount' => 1000,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            [
                'item_id' => 7,
                'category' => 'Gold',
                'name' => '5000 Gold',
                'description' => 'Get 5000 gold for your character',
                'image' => 'gold/5000_gold.jpg',
                'point_cost' => 400,
                'token_cost' => 0,
                'stock' => null,
                'entry' => null,
                'gold_amount' => 5000,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            [
                'item_id' => 8,
                'category' => 'Gold',
                'name' => '10000 Gold',
                'description' => 'Get 10000 gold for your character',
                'image' => 'gold/10000_gold.jpg',
                'point_cost' => 750,
                'token_cost' => 0,
                'stock' => null,
                'entry' => null,
                'gold_amount' => 10000,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 0
            ],
            
            // Предметы
            [
                'item_id' => 9,
                'category' => 'Stuff',
                'name' => 'Thunderfury',
                'description' => 'Legendary sword Thunderfury',
                'image' => 'items/thunderfury.jpg',
                'point_cost' => 0,
                'token_cost' => 1000,
                'stock' => 3,
                'entry' => 19019,
                'gold_amount' => 0,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 1
            ],
            [
                'item_id' => 10,
                'category' => 'Stuff',
                'name' => 'Sulfuras',
                'description' => 'Legendary hammer Sulfuras',
                'image' => 'items/sulfuras.jpg',
                'point_cost' => 0,
                'token_cost' => 1200,
                'stock' => 2,
                'entry' => 17182,
                'gold_amount' => 0,
                'level_boost' => null,
                'at_login_flags' => 0,
                'is_item' => 1
            ]
        ];
        
        // Вставить товары
        foreach ($items as $item) {
            DB::connection('mysql')->table('shop_items')->insert($item);
        }
        
        $this->command->info('Shop items seeded successfully!');
    }
}
