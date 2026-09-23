<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partnership_applications', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('organisation_name');
            $table->string('phone', 40);
            $table->string('email');
            $table->string('home_telephone', 40);
            $table->string('address_1');
            $table->string('address_2')->nullable();
            $table->string('city', 120);
            $table->string('postal_code', 32);
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partnership_applications');
    }
};
