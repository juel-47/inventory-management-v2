<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL: modify the enum column to include 'frontend'
        DB::statement("ALTER TABLE carts MODIFY COLUMN cart_type ENUM('booking', 'request', 'frontend') NOT NULL DEFAULT 'booking'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE carts MODIFY COLUMN cart_type ENUM('booking', 'request') NOT NULL DEFAULT 'booking'");
    }
};
