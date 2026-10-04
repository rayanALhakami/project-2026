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
        Schema::create('places', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('name_en');
            $table->string('category')->index();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('image')->nullable();
            $table->decimal('ticket_price', 8, 2)->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->string('opening_hours')->nullable();
            $table->json('tags')->nullable();
            $table->string('best_time')->nullable();
            $table->unsignedSmallInteger('avg_visit_duration')->nullable();
            $table->boolean('is_indoor')->default(false);
            $table->boolean('family_friendly')->default(false);
            $table->boolean('wheelchair_accessible')->default(false);
            $table->boolean('prayer_facilities')->default(false);
            $table->boolean('closed_friday')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('places');
    }
};
