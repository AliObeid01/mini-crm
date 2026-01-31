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
    Schema::table('departments', function (Blueprint $table) {
        $table->dropUnique('departments_name_unique');
        $table->unique(['name', 'deleted_at'], 'departments_name_deleted_at_unique');
    });
}

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropUnique('departments_name_deleted_at_unique');
            $table->unique('name');
        });
    }
};
