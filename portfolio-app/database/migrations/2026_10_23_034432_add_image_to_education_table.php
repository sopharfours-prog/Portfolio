<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Optional image per education entry (school logo, certificate or diploma), from the Media Library. */
    public function up(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('education', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
