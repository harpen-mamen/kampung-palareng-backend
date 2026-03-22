<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('profile_title')->default('Profil Kampung Palareng')->after('official_photo');
            $table->text('profile_description')->nullable()->after('profile_title');
            $table->text('profile_history')->nullable()->after('profile_description');
            $table->text('profile_vision_mission')->nullable()->after('profile_history');
            $table->text('profile_potential')->nullable()->after('profile_vision_mission');
            $table->string('profile_image')->nullable()->after('profile_potential');
            $table->json('government_structure')->nullable()->after('profile_image');
        });
    }

    public function down()
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'profile_title',
                'profile_description',
                'profile_history',
                'profile_vision_mission',
                'profile_potential',
                'profile_image',
                'government_structure',
            ]);
        });
    }
};
