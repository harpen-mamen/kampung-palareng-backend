<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->string('file_surat')->nullable()->after('isi_surat');
            $table->timestamp('arsip_surat_at')->nullable()->after('file_surat');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->dropColumn([
                'file_surat',
                'arsip_surat_at',
            ]);
        });
    }
};
