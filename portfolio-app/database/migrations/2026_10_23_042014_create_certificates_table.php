<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Certificates shown as an image gallery on the website. The image comes from the Media Library. */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->json('title');                    // {"lo": "...", "en": "..."}
            $table->string('issuer', 150)->nullable();
            $table->date('issued_on')->nullable();
            $table->string('image');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
