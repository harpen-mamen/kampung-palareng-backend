<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pemohon');
            $table->string('jenis_surat');
            $table->text('alamat');
            $table->enum('lindongan', ['Lindongan 1', 'Lindongan 2', 'Lindongan 3', 'Lindongan 4']);
            $table->string('lampiran')->nullable();
            $table->string('status')->default('diajukan');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_surat');
    }
};
