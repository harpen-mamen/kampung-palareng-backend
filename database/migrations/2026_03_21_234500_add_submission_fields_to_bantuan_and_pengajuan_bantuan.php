<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bantuan', function (Blueprint $table) {
            $table->boolean('is_open_for_submission')->default(false)->after('status');
            $table->unsignedInteger('kuota')->nullable()->after('is_open_for_submission');
        });

        Schema::table('pengajuan_bantuan', function (Blueprint $table) {
            $table->foreignId('bantuan_id')->nullable()->after('lindongan')->constrained('bantuan')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('pengajuan_bantuan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bantuan_id');
        });

        Schema::table('bantuan', function (Blueprint $table) {
            $table->dropColumn(['is_open_for_submission', 'kuota']);
        });
    }
};
