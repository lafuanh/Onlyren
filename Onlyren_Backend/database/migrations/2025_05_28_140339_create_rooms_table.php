<?php
// database/migrations/create_rooms_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('location');
            $table->string('type')->default('General');
            $table->string('capacity')->default('1-10');
            $table->text('specifications')->nullable();
            
            // Pricing
            $table->decimal('price_per_hour', 10, 2)->default(25000);
            $table->decimal('price_per_day', 10, 2)->default(300000);
            $table->decimal('price_per_week', 10, 2)->default(1800000);
            $table->decimal('price_per_month', 10, 2)->default(7000000);
            
            // Images and media
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();
            
            // Amenities and facilities
            $table->json('amenities')->nullable();
            $table->json('facilities')->nullable();
            
            // Reviews and ratings
            $table->decimal('rating', 3, 2)->default(4.5);
            $table->integer('review_count')->default(0);
            
            // Availability
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('availability_status')->default('available');
            
            // Owner information
            $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['location']);
            $table->index(['type']);
            $table->index(['is_available']);
            $table->index(['is_featured']);
            $table->index(['rating']);
            $table->fullText(['name', 'description', 'location']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rooms');
    }
};