<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'auto_increment' => true],
            'barcode'    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'price'      => ['type' => 'NUMERIC', 'constraint' => '12,2'],
            'image_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('barcode'); // NULL boleh banyak, nilai terisi harus unik
        $this->forge->createTable('products');
    }

    public function down()
    {
        $this->forge->dropTable('products', true);
    }
}
