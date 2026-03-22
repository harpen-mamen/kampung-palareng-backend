<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('keluarga_bantuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keluarga_id')->constrained('keluarga')->cascadeOnDelete();
            $table->foreignId('bantuan_id')->constrained('bantuan')->cascadeOnDelete();
            $table->string('status_penerima')->default('aktif');
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('keluarga_bantuan');
    }
};
