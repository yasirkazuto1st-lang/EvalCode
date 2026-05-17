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
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'nim_nip')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('nim_nip', 'nim_username');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'nim_username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('nim_username', 'nim_nip');
            });
        }
    }
};
