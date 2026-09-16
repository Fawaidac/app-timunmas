<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix tipe data untuk kompatibilitas Firebird.
     * Firebird DECIMAL(p,s) memiliki keterbatasan presisi yang berbeda dengan MySQL.
     * Gunakan DOUBLE PRECISION untuk koordinat GPS dan nilai desimal lainnya.
     */
    public function up(): void
    {
        // Fix tabel customers - latitude dan longitude
        $this->fixColumn('customers', 'latitude', 'DOUBLE PRECISION');
        $this->fixColumn('customers', 'longitude', 'DOUBLE PRECISION');
        $this->fixColumn('customers', 'current_debt', 'DOUBLE PRECISION');

        // Fix tabel sales_visits - koordinat checkin dan jarak
        $this->fixColumn('sales_visits', 'checkin_latitude', 'DOUBLE PRECISION');
        $this->fixColumn('sales_visits', 'checkin_longitude', 'DOUBLE PRECISION');
        $this->fixColumn('sales_visits', 'distance_meters', 'DOUBLE PRECISION');

        // Fix tabel products - price
        $this->fixColumn('products', 'price', 'DOUBLE PRECISION');

        // Fix tabel order_items
        $this->fixColumn('order_items', 'price_per_unit', 'DOUBLE PRECISION');
        $this->fixColumn('order_items', 'subtotal', 'DOUBLE PRECISION');

        // Fix tabel sales_orders
        $this->fixColumn('sales_orders', 'total_amount', 'DOUBLE PRECISION');

        // Fix tabel invoices
        $this->fixColumn('invoices', 'total_amount', 'DOUBLE PRECISION');
        $this->fixColumn('invoices', 'remaining_balance', 'DOUBLE PRECISION');

        // Fix tabel payments
        $this->fixColumn('payments', 'amount_paid', 'DOUBLE PRECISION');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke tipe semula (MySQL compatible)
        $this->revertColumn('customers', 'latitude', 'DECIMAL(10,8)');
        $this->revertColumn('customers', 'longitude', 'DECIMAL(11,8)');
        $this->revertColumn('customers', 'current_debt', 'DECIMAL(15,2)');
        $this->revertColumn('sales_visits', 'checkin_latitude', 'DECIMAL(10,8)');
        $this->revertColumn('sales_visits', 'checkin_longitude', 'DECIMAL(11,8)');
        $this->revertColumn('sales_visits', 'distance_meters', 'INTEGER');
        $this->revertColumn('products', 'price', 'DECIMAL(15,2)');
        $this->revertColumn('order_items', 'price_per_unit', 'DECIMAL(15,2)');
        $this->revertColumn('order_items', 'subtotal', 'DECIMAL(15,2)');
        $this->revertColumn('sales_orders', 'total_amount', 'DECIMAL(15,2)');
        $this->revertColumn('invoices', 'total_amount', 'DECIMAL(15,2)');
        $this->revertColumn('invoices', 'remaining_balance', 'DECIMAL(15,2)');
        $this->revertColumn('payments', 'amount_paid', 'DECIMAL(15,2)');
    }

    /**
     * Fix column dengan membuat kolom baru dan copy data
     */
    private function fixColumn(string $table, string $column, string $newType): void
    {
        $tempCol = $column . '_temp';
        
        try {
            // Step 1: Add new column with correct type
            DB::statement('ALTER TABLE "' . $table . '" ADD "' . $tempCol . '" ' . $newType);
            
            // Step 2: Copy data from old column to new column
            DB::statement('UPDATE "' . $table . '" SET "' . $tempCol . '" = "' . $column . '"');
            
            // Step 3: Drop old column
            DB::statement('ALTER TABLE "' . $table . '" DROP "' . $column . '"');
            
            // Step 4: Rename new column to old column name
            DB::statement('ALTER TABLE "' . $table . '" ALTER COLUMN "' . $tempCol . '" TO "' . $column . '"');
        } catch (\Exception $e) {
            \Log::warning("Gagal fix column {$table}.{$column}: " . $e->getMessage());
        }
    }

    /**
     * Revert column ke tipe semula
     */
    private function revertColumn(string $table, string $column, string $oldType): void
    {
        $tempCol = $column . '_temp';
        
        try {
            DB::statement('ALTER TABLE "' . $table . '" ADD "' . $tempCol . '" ' . $oldType);
            DB::statement('UPDATE "' . $table . '" SET "' . $tempCol . '" = "' . $column . '"');
            DB::statement('ALTER TABLE "' . $table . '" DROP "' . $column . '"');
            DB::statement('ALTER TABLE "' . $table . '" ALTER COLUMN "' . $tempCol . '" TO "' . $column . '"');
        } catch (\Exception $e) {
            \Log::warning("Gagal revert column {$table}.{$column}: " . $e->getMessage());
        }
    }
};
