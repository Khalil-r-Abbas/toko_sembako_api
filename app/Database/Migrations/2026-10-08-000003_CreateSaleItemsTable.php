<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSaleItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'auto_increment' => true],
            'transaction_id' => ['type' => 'INT'],
            'product_id'     => ['type' => 'INT'],
            'product_name'   => ['type' => 'VARCHAR', 'constraint' => 150],
            'price_at_sale'  => ['type' => 'NUMERIC', 'constraint' => '12,2'],
            'quantity'       => ['type' => 'INT'],
        ]);
        $this->forge->addPrimaryKey('id');
        // addForeignKey(kolom, tabel, kolom_tujuan, onUpdate, onDelete)
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('product_id', 'products', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('sale_items');
    }

    public function down()
    {
        $this->forge->dropTable('sale_items', true);
    }
}
