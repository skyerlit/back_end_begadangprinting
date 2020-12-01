<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->i2nteger('idPelanggan');
            $table->integer('idItem');
            $table->string('namaItem');
            $table->integer('jumlah');
            $table->integer('total');
            $table->string('statusPesan');
            $table->string('jenisWarna');
            $table->string('jenisServis');
            $table->string('filePesan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
