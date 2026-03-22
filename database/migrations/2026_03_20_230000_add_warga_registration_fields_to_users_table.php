<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('keluarga_id')->nullable()->after('id')->constrained('keluarga')->nullOnDelete();
            $table->string('nik', 32)->nullable()->unique()->after('name');
            $table->string('whatsapp', 50)->nullable()->after('phone');
            $table->string('approval_status')->default('disetujui')->after('role');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->text('approval_notes')->nullable()->after('approved_at');
            $table->foreignId('approved_by')->nullable()->after('approval_notes')->constrained('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('keluarga_id');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'nik',
                'whatsapp',
                'approval_status',
                'approved_at',
                'approval_notes',
            ]);
        });
    }
};
