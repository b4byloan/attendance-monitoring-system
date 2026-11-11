<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users_new', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            $table->string('role')->default('student');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });

        DB::statement("
            CREATE TABLE users_temp (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR NOT NULL,
                email VARCHAR NOT NULL UNIQUE,
                email_verified_at DATETIME NULL,
                password VARCHAR NOT NULL,
                remember_token VARCHAR NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                role VARCHAR NOT NULL DEFAULT 'student'
                    CHECK (role IN ('admin', 'sub_admin', 'parent', 'guard', 'student')),
                is_active BOOLEAN NOT NULL DEFAULT 1,
                last_login_at DATETIME NULL
            )
        ");

        DB::statement('
            INSERT INTO users_temp
            (id, name, email, email_verified_at, password, remember_token,
             created_at, updated_at, role, is_active, last_login_at)
            SELECT
                id, name, email, email_verified_at, password, remember_token,
                created_at, updated_at, role, is_active, last_login_at
            FROM users
        ');

        Schema::drop('users');
        Schema::drop('users_new');

        DB::statement('ALTER TABLE users_temp RENAME TO users');
    }

    public function down(): void
    {
        // No rollback
    }
};
