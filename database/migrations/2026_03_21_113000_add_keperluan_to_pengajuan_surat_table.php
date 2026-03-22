<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->string('keperluan')->nullable()->after('jenis_surat');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->dropColumn('keperluan');
        });
    }
};
