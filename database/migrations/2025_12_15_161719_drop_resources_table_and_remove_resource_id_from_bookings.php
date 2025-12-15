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
        // Remove resource_id column from bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['resource_id']);
            $table->dropColumn('resource_id');
        });

        // Drop resources table
        Schema::dropIfExists('resources');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate resources table
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('capacity')->nullable();
            $table->decimal('price_per_hour', 10, 2)->nullable();
            $table->timestamps();
        });

        // Add resource_id column back to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('resource_id')->nullable()->after('venue_id')->constrained()->onDelete('set null');
        });
    }
};
