<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Portfolio content. Bilingual text is stored as JSON: {"lo": "...", "en": "..."}.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category', 20)->index(); // web | mobile | uiux | branding | system | other
            $table->json('summary');
            $table->json('description')->nullable();
            $table->json('challenge')->nullable();
            $table->json('solution')->nullable();
            $table->json('features')->nullable();     // {"lo": [..], "en": [..]}
            $table->json('role')->nullable();
            $table->json('technologies')->nullable();  // ["Laravel", ...]
            $table->string('client')->nullable();
            $table->date('project_date')->nullable();
            $table->string('url')->nullable();
            $table->string('github_url')->nullable();
            $table->boolean('featured')->default(false)->index();
            $table->string('status', 20)->default('draft')->index(); // published | draft | archived
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->unsignedInteger('views')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->boolean('is_cover')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['project_id', 'sort_order']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->json('description');
            $table->json('features')->nullable();
            $table->string('icon', 60)->default('code-slash');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('published')->index(); // published | draft
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category', 20)->index(); // frontend | backend | database | design | tools | other
            $table->string('icon', 60)->default('code-slash');
            $table->unsignedTinyInteger('level')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->json('position');
            $table->string('location')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->json('description')->nullable();
            $table->json('technologies')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->json('position')->nullable();
            $table->string('company')->nullable();
            $table->string('photo')->nullable();
            $table->json('message');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('status', 20)->default('published')->index(); // published | draft | hidden
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('services');
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('projects');
    }
};
