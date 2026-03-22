<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rumah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluarga')->cascadeOnDelete();
            $table->string('alamat_singkat');
            $table->enum('lindongan', ['Lindongan 1', 'Lindongan 2', 'Lindongan 3', 'Lindongan 4']);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('foto_rumah')->nullable();
            $table->string('kategori_rumah');
            $table->unsignedInteger('jumlah_penghuni')->default(1);
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rumah');
    }
};
