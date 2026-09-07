<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            $table->text('address')->nullable()->after('relationship_to_student');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('guardians', function (Blueprint $table) {
            $table->dropColumn(['address', 'status']);
        });
    }
};