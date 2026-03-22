<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('keluarga', function (Blueprint $table) {
            $table->id();
            $table->string('kode_keluarga')->unique();
            $table->string('nama_kepala_keluarga');
            $table->text('alamat');
            $table->enum('lindongan', ['Lindongan 1', 'Lindongan 2', 'Lindongan 3', 'Lindongan 4']);
            $table->unsignedInteger('jumlah_anggota')->default(1);
            $table->string('status_ekonomi');
            $table->string('pekerjaan_utama');
            $table->string('kategori_rumah');
            $table->boolean('status_dtks')->default(false);
            $table->text('catatan_petugas')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('keluarga');
    }
};
