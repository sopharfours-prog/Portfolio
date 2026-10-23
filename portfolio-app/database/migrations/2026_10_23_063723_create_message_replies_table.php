<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Email replies sent from the admin to a contact message, kept as the conversation history. */
    public function up(): void
    {
        Schema::create('message_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_replies');
    }
};
