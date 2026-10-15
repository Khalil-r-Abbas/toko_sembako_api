<?php

namespace App\Controllers\Api;

use App\Models\ProductModel;
use App\Models\SaleItemModel;

class Transactions extends BaseApi
{
    protected $modelName = 'App\Models\TransactionModel';

    // GET /api/transactions?date=2026-10-08
    public function index()
    {
        $date = $this->request->getGet('date');
        if ($date) {
            $this->model->where('created_at >=', "{$date} 00:00:00")
                        ->where('created_at <=', "{$date} 23:59:59");
        }

        $data = $this->model->orderBy('id', 'DESC')->findAll();

        return $this->respond([
            'status'  => 200,
            'message' => 'Daftar transaksi',
            'total'   => count($data),
            'data'    => $data,
        ]);
    }

    // GET /api/transactions/{id}  (beserta item-nya)
    public function show($id = null)
    {
        $trx = $this->model->find($id);
        if (! $trx) {
            return $this->failNotFound("Transaksi dengan id {$id} tidak ditemukan.");
        }

        $trx['items'] = (new SaleItemModel())
            ->where('transaction_id', $id)->orderBy('id', 'ASC')->findAll();

        return $this->respond(['status' => 200, 'message' => 'Detail transaksi', 'data' => $trx]);
    }

    /**
     * POST /api/transactions
     * Body: {"items":[{"product_id":1,"quantity":2}, ...], "created_at":"2026-10-08 10:00:00" (opsional)}
     * Nama & harga diambil dari tabel products (snapshot ke sale_items),
     * total dihitung server.
     */
    public function create()
    {
        $input = $this->getInput();
        $items = $input['items'] ?? null;

        if (! is_array($items) || empty($items)) {
            return $this->fail('Field items wajib diisi (minimal 1 item).', 400);
        }

        $productModel = new ProductModel();
        $rows   = [];
        $total  = 0;
        $errors = [];

        foreach ($items as $i => $it) {
            $qty     = (int) ($it['quantity'] ?? 0);
            $product = $productModel->find((int) ($it['product_id'] ?? 0));

            if (! $product) {
                $errors["items.{$i}.product_id"] = 'Produk tidak ditemukan.';
                continue;
            }
            if ($qty < 1) {
                $errors["items.{$i}.quantity"] = 'Quantity minimal 1.';
                continue;
            }

            $rows[] = [
                'product_id'    => $product['id'],
                'product_name'  => $product['name'],
                'price_at_sale' => $product['price'],
                'quantity'      => $qty,
            ];
            $total += $product['price'] * $qty;
        }

        if ($errors) {
            return $this->failValidationErrors($errors);
        }

        $trxData = ['total_amount' => $total];
        if (! empty($input['created_at'])) {
            $trxData['created_at'] = $input['created_at'];
        }

        $db = db_connect();
        $db->transStart(); // header + items harus tersimpan bersamaan

        if (! $this->model->insert($trxData)) {
            $db->transRollback();
            return $this->failValidationErrors($this->model->errors());
        }
        $trxId = $this->model->getInsertID();

        foreach ($rows as &$r) {
            $r['transaction_id'] = $trxId;
        }
        unset($r);

        (new SaleItemModel())->insertBatch($rows);

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->failServerError('Gagal menyimpan transaksi.');
        }

        $trx          = $this->model->find($trxId);
        $trx['items'] = (new SaleItemModel())->where('transaction_id', $trxId)->findAll();

        return $this->respondCreated([
            'status'  => 201,
            'message' => 'Transaksi berhasil dibuat.',
            'data'    => $trx,
        ]);
    }

    // DELETE /api/transactions/{id}  (sale_items ikut terhapus via ON DELETE CASCADE)
    public function delete($id = null)
    {
        if (! $this->model->find($id)) {
            return $this->failNotFound("Transaksi dengan id {$id} tidak ditemukan.");
        }

        $this->model->delete($id);

        return $this->respondDeleted([
            'status'  => 200,
            'message' => "Transaksi dengan id {$id} berhasil dihapus.",
        ]);
    }
}
