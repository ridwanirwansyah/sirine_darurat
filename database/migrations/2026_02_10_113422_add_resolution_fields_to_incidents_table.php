<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('false_alarm_reason')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn(['resolved_by', 'false_alarm_reason', 'admin_notes', 'verified_at', 'verified_by']);
        });
    }
};