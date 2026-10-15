<?php

// Paksa environment menjadi production untuk menghindari error variabel tidak terbaca
$_SERVER['CI_ENVIRONMENT'] = 'production';

// Mengarahkan request dari Vercel ke core CodeIgniter 4 di folder public
require __DIR__ . '/../public/index.php';