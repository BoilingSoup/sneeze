<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(table: 'users', column: 'id')->cascadeOnDelete();
            $table->text('code');
            $table->enum('type', ['password-reset', 'email-verification'])->index();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['user_id', 'type'], 'unique_user_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codes');
    }
};
