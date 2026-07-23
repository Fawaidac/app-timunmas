<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Warehouse;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create Warehouses
        $gudangUtama = Warehouse::create([
            'code' => 'WH001',
            'name' => 'Gudang Utama Jakarta',
            'address' => 'Jl. Industri No. 100, Jakarta Utara',
            'capacity' => 10000,
        ]);

        $gudangCabang = Warehouse::create([
            'code' => 'WH002',
            'name' => 'Gudang Cabang Bekasi',
            'address' => 'Jl. Raya Bekasi KM 15, Bekasi',
            'capacity' => 5000,
        ]);

        // Create Products with Stock
        $products = [
            [
                'sku' => 'PRD001',
                'name' => 'Mie Instan Goreng',
                'unit' => 'karton',
                'price' => 100000.00,
                'description' => 'Mie instan goreng isi 40 pcs per karton',
                'stock' => 50,
            ],
            [
                'sku' => 'PRD002',
                'name' => 'Mie Instan Kuah',
                'unit' => 'karton',
                'price' => 95000.00,
                'description' => 'Mie instan kuah isi 40 pcs per karton',
                'stock' => 60,
            ],
            [
                'sku' => 'PRD003',
                'name' => 'Kopi Sachet Premium',
                'unit' => 'dus',
                'price' => 75000.00,
                'description' => 'Kopi sachet isi 30 pcs per dus',
                'stock' => 40,
            ],
            [
                'sku' => 'PRD004',
                'name' => 'Teh Celup Hitam',
                'unit' => 'dus',
                'price' => 50000.00,
                'description' => 'Teh celup hitam isi 25 pcs per dus',
                'stock' => 80,
            ],
            [
                'sku' => 'PRD005',
                'name' => 'Gula Pasir',
                'unit' => 'karung',
                'price' => 250000.00,
                'description' => 'Gula pasir premium isi 25kg per karung',
                'stock' => 30,
            ],
            [
                'sku' => 'PRD006',
                'name' => 'Minyak Goreng',
                'unit' => 'jerigen',
                'price' => 180000.00,
                'description' => 'Minyak goreng curah isi 18 liter per jerigen',
                'stock' => 25,
            ],
            [
                'sku' => 'PRD007',
                'name' => 'Beras Premium',
                'unit' => 'karung',
                'price' => 350000.00,
                'description' => 'Beras premium kualitas super isi 25kg',
                'stock' => 40,
            ],
            [
                'sku' => 'PRD008',
                'name' => 'Susu Kental Manis',
                'unit' => 'karton',
                'price' => 120000.00,
                'description' => 'Susu kental manis kaleng isi 48 pcs per karton',
                'stock' => 35,
            ],
            [
                'sku' => 'PRD009',
                'name' => 'Biskuit Crackers',
                'unit' => 'dus',
                'price' => 85000.00,
                'description' => 'Biskuit crackers isi 24 pcs per dus',
                'stock' => 55,
            ],
            [
                'sku' => 'PRD010',
                'name' => 'Sabun Mandi Batang',
                'unit' => 'karton',
                'price' => 60000.00,
                'description' => 'Sabun mandi batang isi 72 pcs per karton',
                'stock' => 70,
            ],
        ];

        foreach ($products as $productData) {
            $stock = $productData['stock'];
            unset($productData['stock']);

            $product = Product::create($productData);

            // Attach to Gudang Utama with stock
            $product->warehouses()->attach($gudangUtama->id, [
                'stock_quantity' => $stock
            ]);
        }
    }
}
