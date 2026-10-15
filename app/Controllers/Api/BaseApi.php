<?php

namespace App\Controllers\Api;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\RESTful\ResourceController;

abstract class BaseApi extends ResourceController
{
    protected $format = 'json';

    // Membaca body JSON; null jika kosong / format JSON salah
    protected function getInput(): ?array
    {
        try {
            $input = $this->request->getJSON(true);
        } catch (HTTPException $e) {
            return null;
        }
        return empty($input) ? null : $input;
    }
}
