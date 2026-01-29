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
        // Add soft deletes to venues
        Schema::table('venues', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to providers
        Schema::table('providers', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to customers
        Schema::table('customers', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to users
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to reviews
        Schema::table('reviews', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to offers
        Schema::table('offers', function (Blueprint $table) {
            $table->softDeletes();
        });

        // Add soft deletes to payments
        Schema::table('payments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
