<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Visitor accounts can also sign in with Google or Apple. Those accounts may have no password.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
            $table->string('google_id')->nullable()->unique()->after('password');
            $table->string('apple_id')->nullable()->unique()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropUnique(['apple_id']);
            $table->dropColumn(['google_id', 'apple_id']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
