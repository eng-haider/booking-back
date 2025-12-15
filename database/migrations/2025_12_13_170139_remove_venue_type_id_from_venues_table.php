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
        Schema::table('venues', function (Blueprint $table) {
            if (Schema::hasColumn('venues', 'venue_type_id')) {
                $table->dropForeign(['venue_type_id']);
                $table->dropColumn('venue_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->foreignId('venue_type_id')->nullable()->constrained('venue_types')->nullOnDelete();
        });
    }
};
