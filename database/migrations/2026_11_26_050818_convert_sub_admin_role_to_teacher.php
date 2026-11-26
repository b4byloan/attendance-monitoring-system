<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sub-Admin accounts are presented and treated as Teachers. Move them to
     * the teacher role so the UI only ever talks about teachers.
     */
    public function up(): void
    {
        DB::table('users')->where('role', 'sub_admin')->update(['role' => 'teacher']);
    }

    /**
     * Irreversible: converted accounts cannot be told apart from accounts
     * that were always Teachers.
     */
    public function down(): void
    {
        //
    }
};
