<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            // Which class this class's students move into at year-end promotion.
            // Left null for a terminal/graduating class (e.g. the final grade) -
            // promoting students out of a class with no destination marks them
            // as graduated instead of moving them to another class.
            $table->foreignId('promotes_to_class_id')->nullable()->after('class_teacher_id')
                ->constrained('school_classes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_classes', function (Blueprint $table) {
            $table->dropForeign(['promotes_to_class_id']);
            $table->dropColumn('promotes_to_class_id');
        });
    }
};
