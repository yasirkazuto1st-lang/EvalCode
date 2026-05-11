<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE ujians MODIFY COLUMN status ENUM('active', 'closed', 'finished') DEFAULT 'closed'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE ujians MODIFY COLUMN status ENUM('active', 'closed') DEFAULT 'closed'");
    }
};
