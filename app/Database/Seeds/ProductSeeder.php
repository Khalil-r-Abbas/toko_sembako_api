<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $now  = date('Y-m-d H:i:s');
        $data = [
            ['barcode' => '8999999000011', 'name' => 'Air Mineral 600ml', 'price' => 4000,  'image_path' => null],
            ['barcode' => '8999999000028', 'name' => 'Mie Instan Goreng', 'price' => 3500,  'image_path' => null],
            ['barcode' => '8999999000035', 'name' => 'Teh Botol 350ml',   'price' => 5000,  'image_path' => null],
            ['barcode' => '8999999000042', 'name' => 'Roti Tawar',        'price' => 16000, 'image_path' => null],
            ['barcode' => null,            'name' => 'Gorengan (satuan)', 'price' => 1500,  'image_path' => null],
        ];

        foreach ($data as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        $this->db->table('products')->insertBatch($data);
    }
}
