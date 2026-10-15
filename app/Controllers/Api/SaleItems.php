<?php

namespace App\Controllers\Api;

class SaleItems extends BaseApi
{
    protected $modelName = 'App\Models\SaleItemModel';

    // GET /api/sale-items?transaction_id=1&product_id=2
    public function index()
    {
        if ($tid = $this->request->getGet('transaction_id')) {
            $this->model->where('transaction_id', $tid);
        }
        if ($pid = $this->request->getGet('product_id')) {
            $this->model->where('product_id', $pid);
        }

        $data = $this->model->orderBy('id', 'ASC')->findAll();

        return $this->respond([
            'status'  => 200,
            'message' => 'Daftar item penjualan',
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    // GET /api/sale-items/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (! $data) {
            return $this->failNotFound("Item penjualan dengan id {$id} tidak ditemukan.");
        }

        return $this->respond(['status' => 200, 'message' => 'Detail item penjualan', 'data' => $data]);
    }
}
