<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add 'fee' to transactions.type enum (for DBs migrated before the enum was updated).
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit','withdraw','fee') NOT NULL");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('deposit','withdraw') NOT NULL");
        }
    }
};
