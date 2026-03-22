<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bantuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bantuan');
            $table->string('kategori');
            $table->string('sumber');
            $table->string('periode');
            $table->string('status')->default('aktif');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bantuan');
    }
};
