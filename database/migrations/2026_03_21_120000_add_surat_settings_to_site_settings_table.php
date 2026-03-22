<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->json('surat_templates')->nullable()->after('government_structure');
            $table->json('surat_numbering')->nullable()->after('surat_templates');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'surat_templates',
                'surat_numbering',
            ]);
        });
    }
};
