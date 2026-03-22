<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengajuan_bantuan', function (Blueprint $table) {
            $table->string('whatsapp_pemohon', 50)->nullable()->after('lindongan');
        });
    }

    public function down()
    {
        Schema::table('pengajuan_bantuan', function (Blueprint $table) {
            $table->dropColumn('whatsapp_pemohon');
        });
    }
};
