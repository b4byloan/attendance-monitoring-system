<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'sub_admin', 'teacher', 'parent', 'guard', 'student') NOT NULL DEFAULT 'student'");

            return;
        }

        // SQLite cannot alter a CHECK constraint, so rebuild the table with the
        // extra role. Foreign keys are switched off only while the table is
        // replaced; the final table keeps the same name it was referenced by.
        DB::statement('PRAGMA foreign_keys = OFF');

        Schema::create('users_with_teacher_role', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->enum('role', ['admin', 'sub_admin', 'teacher', 'parent', 'guard', 'student'])->default('student');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });

        DB::statement('
            INSERT INTO users_with_teacher_role
            (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, is_active, last_login_at)
            SELECT id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, is_active, last_login_at
            FROM users
        ');

        Schema::drop('users');
        Schema::rename('users_with_teacher_role', 'users');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("UPDATE users SET role = 'sub_admin' WHERE role = 'teacher'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'sub_admin', 'parent', 'guard', 'student') NOT NULL DEFAULT 'student'");

            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::table('users')->where('role', 'teacher')->update(['role' => 'sub_admin']);

        Schema::create('users_without_teacher_role', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->enum('role', ['admin', 'sub_admin', 'parent', 'guard', 'student'])->default('student');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });

        DB::statement('
            INSERT INTO users_without_teacher_role
            (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, is_active, last_login_at)
            SELECT id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, is_active, last_login_at
            FROM users
        ');

        Schema::drop('users');
        Schema::rename('users_without_teacher_role', 'users');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
