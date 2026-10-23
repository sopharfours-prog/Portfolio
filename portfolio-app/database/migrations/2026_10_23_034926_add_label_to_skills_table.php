<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional display name per language, {"lo": "...", "en": "..."}. Professional skills
     * (e.g. "ຄວາມຄິດສ້າງສັນ" / "Creativity") need one; technical names like "HTML" do not.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->json('label')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
