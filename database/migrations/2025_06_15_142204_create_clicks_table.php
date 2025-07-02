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
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('url_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->timestamp('clicked_at')->useCurrent();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('url_id');
            $table->index('clicked_at');
            $table->index('country');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
