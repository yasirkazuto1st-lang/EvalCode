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
            $table->boolean('is_freeze_enabled')->default(false)->after('max_tab_switch');
            $table->unsignedInteger('freeze_threshold')->default(80)->nullable()->after('is_freeze_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ujians', function (Blueprint $table) {
            $table->dropColumn(['is_freeze_enabled', 'freeze_threshold']);
        });
    }
};

