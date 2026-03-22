<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('hero_badge')->default('Kabupaten Kepulauan Sangihe');
            $table->string('hero_title');
            $table->text('hero_description');
            $table->string('hero_primary_label')->default('Ajukan Surat');
            $table->string('hero_primary_url')->default('/surat');
            $table->string('hero_secondary_label')->default('Buka Peta Digital');
            $table->string('hero_secondary_url')->default('/peta');
            $table->string('hero_panel_title')->default('Selayang Pandang');
            $table->text('hero_panel_description')->nullable();
            $table->string('official_name')->default('Kapitalaung Kampung Palareng');
            $table->string('official_position')->default('Pemerintah Kampung Palareng');
            $table->text('official_message')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('official_photo')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_settings');
    }
};
