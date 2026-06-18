<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometric_enrollments', function (Blueprint $table) {
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('status');
            $table->timestamp('sync_requested_at')->nullable()->after('sync_status');
            $table->timestamp('last_synced_at')->nullable()->after('sync_requested_at');
            $table->text('sync_message')->nullable()->after('last_synced_at');

            $table->index(['sync_status', 'device_id']);
        });
    }

    public function down(): void
    {
        Schema::table('biometric_enrollments', function (Blueprint $table) {
            $table->dropIndex(['sync_status', 'device_id']);
            $table->dropColumn(['sync_status', 'sync_requested_at', 'last_synced_at', 'sync_message']);
        });
    }
};
