<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->unsignedInteger('nomor_urut_surat')->nullable()->after('catatan_admin');
            $table->string('nomor_surat')->nullable()->after('nomor_urut_surat');
            $table->date('tanggal_surat')->nullable()->after('nomor_surat');
            $table->timestamp('disetujui_at')->nullable()->after('tanggal_surat');
            $table->foreignId('approved_by')->nullable()->after('disetujui_at')->constrained('users')->nullOnDelete();
            $table->string('nama_penandatangan')->nullable()->after('approved_by');
            $table->string('jabatan_penandatangan')->nullable()->after('nama_penandatangan');
            $table->longText('isi_surat')->nullable()->after('jabatan_penandatangan');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'nomor_urut_surat',
                'nomor_surat',
                'tanggal_surat',
                'disetujui_at',
                'nama_penandatangan',
                'jabatan_penandatangan',
                'isi_surat',
            ]);
        });
    }
};
