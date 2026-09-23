<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('mediable_type');
            $table->unsignedBigInteger('mediable_id');
            $table->string('role')->default('attachment');
            $table->unsignedInteger('sort_order')->default(0);

            $table->index(['mediable_type', 'mediable_id']);
            $table->index(['media_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
    }
};
