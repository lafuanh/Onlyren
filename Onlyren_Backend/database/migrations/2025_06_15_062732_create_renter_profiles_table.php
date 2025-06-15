<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renter_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('businessName')->nullable();
            $table->string('ownerName')->nullable();
            $table->string('phone')->nullable();
            $table->string('siupNumber')->nullable();
            $table->string('nibNumber')->nullable();
            $table->text('businessAddress')->nullable();
            $table->text('businessDescription')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renter_profiles');
    }
};