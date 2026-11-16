<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password123');

        User::create([
            'name' => 'Admin Sewao',
            'email' => 'admin@sewao.id',
            'password' => $password,
            'role' => 'admin',
            'wa_phone' => null,
        ]);

        $seller = User::create([
            'name' => 'Toko Sewao Pusat',
            'email' => 'seller@sewao.id',
            'password' => $password,
            'role' => 'seller',
            'wa_phone' => '6281200000000',
        ]);

        $pembeli = User::create([
            'name' => 'Budi Penyewa',
            'email' => 'pembeli@sewao.id',
            'password' => $password,
            'role' => 'pembeli',
            'wa_phone' => '6285700000000',
        ]);

        User::create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti@sewao.id',
            'password' => $password,
            'role' => 'pembeli',
            'wa_phone' => '6285611112222',
        ]);

        User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi@sewao.id',
            'password' => $password,
            'role' => 'pembeli',
            'wa_phone' => '6281399998888',
        ]);

        $categories = [];
        foreach ([
            'iPhone & iOS',
            'Android',
            'Komputer & Laptop',
            'Kamera & Multimedia',
        ] as $catName) {
            $categories[$catName] = Category::create([
                'name' => $catName,
                'slug' => Str::slug($catName),
            ]);
        }

        $productsData = [
            [
                'category' => 'iPhone & iOS',
                'name' => 'iPhone 15 Pro 256GB Natural',
                'price_per_day' => 250000,
                'stock' => 4,
                'description' => 'Titanium Natural, ProRAW 48MP, Battery Health 96%, lengkap kabel USB-C & case.',
                'is_featured' => true,
            ],
            [
                'category' => 'iPhone & iOS',
                'name' => 'iPhone 13 128GB Midnight',
                'price_per_day' => 140000,
                'stock' => 6,
                'description' => 'Dual camera Cinematic Mode, kondisi mulus, siap pakai harian & konten.',
                'is_featured' => false,
            ],
            [
                'category' => 'Android',
                'name' => 'Samsung Galaxy S24 Ultra',
                'price_per_day' => 260000,
                'stock' => 3,
                'description' => 'Zoom optik 10x, S-Pen bawaan, Galaxy AI siap konser & liputan.',
                'is_featured' => true,
            ],
            [
                'category' => 'Android',
                'name' => 'Xiaomi 14T Pro Leica 512GB',
                'price_per_day' => 165000,
                'stock' => 5,
                'description' => 'Lensa Leica Summilux, layar 144Hz AMOLED, fast charging 120W.',
                'is_featured' => false,
            ],
            [
                'category' => 'Komputer & Laptop',
                'name' => 'MacBook Air M2 13-inch 16GB',
                'price_per_day' => 220000,
                'stock' => 3,
                'description' => 'Apple M2 Chip, RAM 16GB, SSD 512GB, baterai awet seharian + sleeve bag.',
                'is_featured' => true,
            ],
            [
                'category' => 'Komputer & Laptop',
                'name' => 'ASUS ROG Zephyrus G14 RTX',
                'price_per_day' => 275000,
                'stock' => 2,
                'description' => 'Ryzen 9, RTX 4060, cocok untuk rendering video 4K, desain 3D, & gaming.',
                'is_featured' => false,
            ],
            [
                'category' => 'Kamera & Multimedia',
                'name' => 'Sony A7 III + Lensa 28-70mm',
                'price_per_day' => 280000,
                'stock' => 3,
                'description' => 'Full-frame 24MP, 4K HDR, termasuk 2 baterai, SD Card 64GB, & tas kamera.',
                'is_featured' => true,
            ],
            [
                'category' => 'Kamera & Multimedia',
                'name' => 'DJI Osmo Pocket 3 Creator',
                'price_per_day' => 185000,
                'stock' => 0,
                'description' => 'Sensor 1 inci, gimbal 3-axis, Creator Combo lengkap dengan DJI Mic 2.',
                'is_featured' => false,
            ],
        ];

        $products = [];
        foreach ($productsData as $item) {
            $products[] = Product::create([
                'category_id' => $categories[$item['category']]->id,
                'seller_id' => $seller->id,
                'name' => $item['name'],
                'price_per_day' => $item['price_per_day'],
                'stock' => $item['stock'],
                'description' => $item['description'],
                'image_url' => null,
                'is_featured' => $item['is_featured'],
            ]);
        }

        // 2 Data Dummy Favorites untuk pembeli@sewao.id
        Favorite::create([
            'user_id' => $pembeli->id,
            'product_id' => $products[0]->id, // iPhone 15 Pro
        ]);
        Favorite::create([
            'user_id' => $pembeli->id,
            'product_id' => $products[4]->id, // MacBook Air M2
        ]);

        // 2 Data Dummy Orders untuk pembeli@sewao.id
        Order::create([
            'order_code' => 'SW-20261116-001',
            'buyer_id' => $pembeli->id,
            'product_id' => $products[0]->id,
            'duration_days' => 2,
            'total_price' => $products[0]->price_per_day * 2,
            'status' => 'disewa',
        ]);

        Order::create([
            'order_code' => 'SW-20261116-002',
            'buyer_id' => $pembeli->id,
            'product_id' => $products[6]->id,
            'duration_days' => 1,
            'total_price' => $products[6]->price_per_day,
            'status' => 'menunggu_wa',
        ]);
    }
}
