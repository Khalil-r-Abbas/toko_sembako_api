<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleItemModel extends Model
{
    protected $table         = 'sale_items';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'transaction_id', 'product_id', 'product_name', 'price_at_sale', 'quantity',
    ];

    protected $useTimestamps = false;

    protected array $casts = [
        'id'             => 'int',
        'transaction_id' => 'int',
        'product_id'     => 'int',
        'price_at_sale'  => 'float',
        'quantity'       => 'int',
    ];
}
