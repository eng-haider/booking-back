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
        Schema::table('providers', function (Blueprint $table) {
            // Add governorate_id
            $table->foreignId('governorate_id')->nullable()->after('user_id')->constrained('governorates')->nullOnDelete();
            
            // Drop city and country columns if they exist
            if (Schema::hasColumn('providers', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('providers', 'country')) {
                $table->dropColumn('country');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            // Re-add city and country
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            
            // Drop governorate_id
            $table->dropForeign(['governorate_id']);
            $table->dropColumn('governorate_id');
        });
    }
};
