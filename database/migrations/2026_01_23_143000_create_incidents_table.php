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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', [
                'KEBAKARAN',      // Fire
                'PENCURIAN',      // Theft
                'GEMPA_BUMI',     // Earthquake
                'BANJIR',         // Flood
                'KECELAKAAN',     // Accident
                'PENYERANGAN',    // Attack/Assault
                'GANGGUAN_KEAMANAN', // Security Breach
                'LAINNYA'         // Other
            ]);
            $table->text('description');
            $table->string('location')->nullable();
            $table->enum('status', ['ACTIVE', 'RESOLVED', 'FALSE_ALARM'])->default('ACTIVE');
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('status');
            $table->index('reported_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
