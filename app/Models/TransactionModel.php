<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table      = 'transactions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    // created_at boleh dikirim client (transaksi offline yang di-sync belakangan)
    protected $allowedFields = ['total_amount', 'created_at'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected array $casts = [
        'id'           => 'int',
        'total_amount' => 'float',
    ];

    protected $validationRules = [
        'total_amount' => 'required|numeric|greater_than_equal_to[0]',
    ];
}
