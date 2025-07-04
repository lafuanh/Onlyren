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
        Schema::table('payments', function (Blueprint $table) {
            // Add method column if it doesn't exist
            if (!Schema::hasColumn('payments', 'method')) {
                $table->enum('method', ['Cash', 'QRIS', 'Bank Transfer'])->nullable()->after('reservation_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('method');
        });
    }
};
