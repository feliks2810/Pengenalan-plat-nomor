<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePelanggaransTable extends Migration
{
    public function up()
{
    Schema::create('pelanggarans', function (Blueprint $table) {
        $table->id();
        $table->string('plat_nomor');
        $table->timestamp('waktu');
        $table->string('gambar');
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('pelanggarans');
}

}
