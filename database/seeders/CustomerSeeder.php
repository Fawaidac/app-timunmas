<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CUST001',
                'name' => 'Toko Sumber Rejeki',
                'address' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'phone' => '021-5551234',
                'email' => 'sumberrejeki@example.com',
                'status' => 'active',
                'latitude' => -6.200000,
                'longitude' => 106.816666,
            ],
            [
                'code' => 'CUST002',
                'name' => 'Warung Makmur Jaya',
                'address' => 'Jl. Sudirman No. 45, Jakarta Selatan',
                'phone' => '021-5555678',
                'email' => 'makmurjaya@example.com',
                'status' => 'active',
                'latitude' => -6.208763,
                'longitude' => 106.845599,
            ],
            [
                'code' => 'CUST003',
                'name' => 'Minimarket Berkah',
                'address' => 'Jl. Gatot Subroto No. 88, Jakarta Barat',
                'phone' => '021-5559012',
                'email' => 'berkah@example.com',
                'status' => 'active',
                'latitude' => -6.213924,
                'longitude' => 106.802971,
            ],
            [
                'code' => 'CUST004',
                'name' => 'Toko Serba Ada',
                'address' => 'Jl. Ahmad Yani No. 67, Bekasi',
                'phone' => '021-8881234',
                'email' => 'serbaada@example.com',
                'status' => 'active',
                'latitude' => -6.238270,
                'longitude' => 106.992416,
            ],
            [
                'code' => 'CUST005',
                'name' => 'Warung Mbok Darmi',
                'address' => 'Jl. Raya Bogor KM 25, Depok',
                'phone' => '021-8765432',
                'email' => 'mbokdarmi@example.com',
                'status' => 'active',
                'latitude' => -6.402484,
                'longitude' => 106.794243,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
    }
}
