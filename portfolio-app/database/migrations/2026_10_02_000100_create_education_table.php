<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('education', function (Blueprint $table) {
            $table->id();
            $table->string('institution');
            $table->json('degree');                 // {"lo": "...", "en": "..."}
            $table->json('field')->nullable();      // field of study
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->json('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('education');
    }
};
