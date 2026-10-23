<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Optional company logo per experience entry, from the Media Library. */
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('image')->nullable()->after('technologies');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
