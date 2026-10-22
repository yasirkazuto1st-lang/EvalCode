<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->boolean('tab_switch_protection')->default(false)->after('bahasa_pemrograman');
            $table->unsignedInteger('max_tab_switch')->default(3)->after('tab_switch_protection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['tab_switch_protection', 'max_tab_switch']);
        });
    }
};
