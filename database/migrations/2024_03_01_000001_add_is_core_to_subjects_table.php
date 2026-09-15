<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            // Core subjects auto-enroll every student in a class the moment the subject
            // is assigned to that class (and any student added afterward). Electives
            // don't - a teacher has to deliberately enroll students in them.
            $table->boolean('is_core')->default(true)->after('code');
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('is_core');
        });
    }
};
