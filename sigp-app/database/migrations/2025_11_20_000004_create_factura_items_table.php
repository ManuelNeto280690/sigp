<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacturaItemsTable extends Migration
{
    public function up()
    {
        Schema::create('factura_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('factura_id');
            $table->string('descricao');
            $table->decimal('quantidade', 14, 4)->default(1);
            $table->decimal('preco_unitario', 14, 4);
            $table->decimal('subtotal', 14, 4);
            $table->timestamps();

            $table->foreign('factura_id')->references('id')->on('facturas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('factura_items');
    }
}
