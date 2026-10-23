<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('body');
            $table->string('folder', 20)->default('inbox')->index(); // inbox | archived | trash
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamp('replied_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // display name (renamable)
            $table->string('original_name');
            $table->string('disk', 20)->default('public');
            $table->string('path')->unique();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();   // e.g. "hero", "about", "seo", "general"
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30)->unique(); // facebook | instagram | github | linkedin | whatsapp | telegram
            $table->string('url')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);   // message | project | system
            $table->string('title');
            $table->string('body')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40)->index();  // created | updated | deleted | login | ...
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('target')->nullable();   // human label, e.g. project title
            $table->string('ip', 45)->nullable();
            $table->string('status', 20)->default('success'); // success | failed
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // Lightweight first-party analytics for the public site
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->char('visitor', 64)->index();   // salted hash of IP + UA + day (no raw IP stored)
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->date('viewed_on')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('admin_notifications');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('media');
        Schema::dropIfExists('messages');
    }
};
