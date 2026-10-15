<?php

namespace App\Controllers\Api;

use App\Models\SaleItemModel;

class Products extends BaseApi
{
    protected $modelName = 'App\Models\ProductModel';

    // GET /api/products?q=kata_kunci
    public function index()
    {
        $q = $this->request->getGet('q');
        if ($q) {
            $this->model->groupStart()
                ->like('name', $q, 'both', null, true) // ILIKE
                ->orLike('barcode', $q)
                ->groupEnd();
        }

        $data = $this->model->orderBy('id', 'ASC')->findAll();

        return $this->respond([
            'status'  => 200,
            'message' => 'Daftar produk',
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    // GET /api/products/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (! $data) {
            return $this->failNotFound("Produk dengan id {$id} tidak ditemukan.");
        }

        return $this->respond(['status' => 200, 'message' => 'Detail produk', 'data' => $data]);
    }

    // POST /api/products
    public function create()
    {
        $input = $this->getInput();
        if ($input === null) {
            return $this->fail('Body request harus berupa JSON dan tidak boleh kosong.', 400);
        }

        if (! $this->model->insert($input)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Produk berhasil ditambahkan.',
            'data'    => $this->model->find($this->model->getInsertID()),
        ]);
    }

    // PUT/PATCH /api/products/{id}
    public function update($id = null)
    {
        if (! $this->model->find($id)) {
            return $this->failNotFound("Produk dengan id {$id} tidak ditemukan.");
        }

        $input = $this->getInput();
        if ($input === null) {
            return $this->fail('Body request harus berupa JSON dan tidak boleh kosong.', 400);
        }

        $input['id'] = $id; // dibutuhkan rule is_unique[...,id,{id}]

        if (! $this->model->update($id, $input)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond([
            'status'  => 200,
            'message' => 'Produk berhasil diperbarui.',
            'data'    => $this->model->find($id),
        ]);
    }

    // DELETE /api/products/{id}
    public function delete($id = null)
    {
        if (! $this->model->find($id)) {
            return $this->failNotFound("Produk dengan id {$id} tidak ditemukan.");
        }

        // produk yang sudah pernah terjual tidak boleh dihapus (FK RESTRICT)
        $used = (new SaleItemModel())->where('product_id', $id)->countAllResults();
        if ($used > 0) {
            return $this->fail('Produk sudah dipakai di transaksi, tidak bisa dihapus.', 409);
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => "Produk dengan id {$id} berhasil dihapus.",
        ]);
    }

    // GET /api/products/price?name=nama_produk
    public function getPriceByName()
    {
        $name = $this->request->getGet('name');

        if (! $name) {
            return $this->fail('Parameter "name" wajib diisi.', 400);
        }

        // Cari produk berdasarkan nama (menggunakan pencarian fleksibel/like)
        $product = $this->model->where('name', $name)->first();

        // Jika tidak ketemu persis, coba cari yang mirip (opsional)
        if (! $product) {
            $product = $this->model->like('name', $name, 'both', null, true)->first();
        }

        if (! $product) {
            return $this->failNotFound("Produk dengan nama '{$name}' tidak ditemukan.");
        }

        // Sesuaikan 'price' dengan nama kolom harga di database Anda (misal: selling_price, price, dll)
        return $this->respond([
            'status'  => 200,
            'message' => 'Harga produk ditemukan',
            'data'    => [
                'id'    => $product['id'],
                'name'  => $product['name'],
                'price' => $product['price'] ?? 0, // Ganti 'price' dengan kolom harga di tabel Anda
            ]
        ]);
    }

    // POST /api/products/batch-prices
    public function getBatchPrices()
    {
        $input = $this->getInput();
        
        // Validasi input JSON
        if (!isset($input['names']) || !is_array($input['names']) || empty($input['names'])) {
            return $this->fail('Format body harus JSON dan menyertakan array "names".', 400);
        }

        $names = $input['names'];

        // Ambil produk sekaligus menggunakan WHERE IN (1x query saja)
        $products = $this->model->whereIn('name', $names)->findAll();

        return $this->respond([
            'status'  => 200,
            'message' => 'Daftar harga produk',
            'total'   => count($products),
            'data'    => $products
        ]);
    }
}
