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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('phone_number', 30);
            $table->date('birthdate')->nullable();
            $table->string('city', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('first_name');
            $table->index('last_name');
            $table->index('phone_number');
            $table->index('city');

            $table->index(['first_name', 'last_name'], 'contacts_full_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
