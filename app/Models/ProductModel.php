<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table         = 'products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['barcode', 'name', 'price', 'image_path'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected array $casts = [
        'id'    => 'int',
        'price' => 'float',
    ];

    protected $validationRules = [
        'id'         => 'permit_empty|is_natural_no_zero',
        'barcode'    => 'permit_empty|max_length[50]|is_unique[products.barcode,id,{id}]',
        'name'       => 'required|min_length[2]|max_length[150]',
        'price'      => 'required|numeric|greater_than_equal_to[0]',
        'image_path' => 'permit_empty|max_length[255]',
    ];

    protected $validationMessages = [
        'barcode' => ['is_unique' => 'Barcode sudah terdaftar.'],
        'name'    => [
            'required'   => 'Nama produk wajib diisi.',
            'min_length' => 'Nama produk minimal 2 karakter.',
        ],
        'price'   => [
            'required' => 'Harga wajib diisi.',
            'numeric'  => 'Harga harus berupa angka.',
        ],
    ];
}
