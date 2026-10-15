<?php

namespace Config;

use CodeIgniter\Database\Config;

class Database extends Config
{
    /**
     * The default database group.
     */
    public array $default = [
        'DSN'          => '',
        'hostname'     => 'localhost',
        'username'     => '',
        'password'     => '',
        'database'     => '',
        'DBDriver'     => 'Postgre',
        'DBPrefix'     => '',
        'pConnect'     => false,
        'DBDebug'      => true,
        'charset'      => 'utf8',
        'DBCollat'     => '',
        'swapPre'      => '',
        'encrypt'      => false,
        'compress'     => false,
        'strictOn'     => false,
        'failover'     => [],
        'port'         => 5432,
        'numberNative' => false,
        'foundRows'    => false,
        'dateFormat'   => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    /**
     * Constructor untuk membaca Environment Variables (Mendukung Vercel/Render/Lokal)
     */
    public function __construct()
    {
        parent::__construct();

        // Mengambil nilai dari Environment Variable (seperti DB_HOST dari Vercel)
        $this->default['hostname'] = env('DB_HOST', $this->default['hostname']);
        $this->default['username'] = env('DB_USER', $this->default['username']);
        $this->default['password'] = env('DB_PASS', $this->default['password']);
        $this->default['database'] = env('DB_NAME', $this->default['database']);
        $this->default['DBDriver'] = env('DB_DRIVER', $this->default['DBDriver']);
        $this->default['port']     = (int) env('DB_PORT', $this->default['port']);
    }

    public array $tests = [
        // ... biarkan bagian testing apa adanya ...
    ];
}