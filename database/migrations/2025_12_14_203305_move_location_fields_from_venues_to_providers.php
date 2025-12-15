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
        // Remove fields from venues table (they're now in providers table)
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'lat', 'lng']);
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add fields back to venues table
        Schema::table('venues', function (Blueprint $table) {
            $table->string('address')->nullable()->after('description');
            $table->string('city')->nullable()->after('address');
            $table->foreignId('country_id')->nullable()->after('city')->constrained('countries')->onDelete('restrict');
            $table->decimal('lat', 10, 7)->nullable()->after('country_id');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }
};
