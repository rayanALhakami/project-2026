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
        Schema::create('contact_requests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32);
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->unsignedTinyInteger('travelers')->nullable();
            $table->decimal('budget', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_requests');
    }
};
