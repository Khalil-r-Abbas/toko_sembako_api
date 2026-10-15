<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        $now        = date('Y-m-d H:i:s');
        $products   = $this->db->table('products')->get()->getResultArray();
        $byBarcode  = array_column($products, null, 'barcode');

        // [barcode, qty]
        $samples = [
            [['8999999000011', 2], ['8999999000028', 1]],
            [['8999999000035', 3]],
        ];

        foreach ($samples as $items) {
            $total = 0;
            $rows  = [];

            foreach ($items as [$barcode, $qty]) {
                $p       = $byBarcode[$barcode];
                $total  += $p['price'] * $qty;
                $rows[]  = [
                    'product_id'    => $p['id'],
                    'product_name'  => $p['name'],
                    'price_at_sale' => $p['price'],
                    'quantity'      => $qty,
                ];
            }

            $this->db->table('transactions')->insert([
                'total_amount' => $total,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $trxId = $this->db->insertID();

            foreach ($rows as &$r) {
                $r['transaction_id'] = $trxId;
            }
            unset($r);

            $this->db->table('sale_items')->insertBatch($rows);
        }
    }
}
