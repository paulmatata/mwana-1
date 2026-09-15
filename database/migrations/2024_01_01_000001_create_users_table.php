<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Role drives which dashboard/middleware applies.
            // super_admin  -> owns the whole platform, creates schools + principal accounts
            // principal    -> manages one school (classes, schedules, teachers, approves marks)
            // teacher      -> belongs to one school, manages students/marks/timetable
            // parent       -> linked to one or more students via parent_student pivot
            $table->enum('role', ['super_admin', 'principal', 'teacher', 'parent'])->index();

            $table->string('name');
            $table->string('email')->nullable()->unique(); // parents may not have email, so nullable
            $table->string('phone')->nullable()->unique();
            $table->string('password');

            // Nullable + unconstrained here on purpose: schools table doesn't exist yet at this point
            // in the migration order (schools.created_by references users). The FK constraint itself
            // is added in a later migration once both tables exist.
            $table->unsignedBigInteger('school_id')->nullable()->index();

            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
