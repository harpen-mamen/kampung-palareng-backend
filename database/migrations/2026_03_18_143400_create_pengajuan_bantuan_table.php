<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pengajuan_bantuan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pemohon');
            $table->text('alamat');
            $table->enum('lindongan', ['Lindongan 1', 'Lindongan 2', 'Lindongan 3', 'Lindongan 4']);
            $table->string('jenis_bantuan');
            $table->text('keterangan')->nullable();
            $table->string('lampiran')->nullable();
            $table->string('status_pengajuan')->default('diajukan');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('keluarga_id')->nullable()->constrained('keluarga')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengajuan_bantuan');
    }
};
