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
            // Renaming the foreign key to follow Laravel conventions (VERY IMPORTANT)
            $table->renameColumn('reservations_id', 'reservation_id');

            // Adding the missing columns
            $table->string('transaction_id')->unique()->after('id');
            $table->timestamp('payment_date')->nullable()->after('status');
            $table->text('notes')->nullable()->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->renameColumn('reservation_id', 'reservations_id');
            $table->dropColumn(['transaction_id', 'payment_date', 'notes']);
        });
    }
};