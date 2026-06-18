<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('lecturer_id')->constrained('lecturers')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->restrictOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['academic_year_id', 'day', 'room_id', 'start_time', 'end_time'], 'schedules_room_time_index');
            $table->index(['academic_year_id', 'day', 'lecturer_id', 'start_time', 'end_time'], 'schedules_lecturer_time_index');
            $table->index(['academic_year_id', 'day', 'class_id', 'start_time', 'end_time'], 'schedules_class_time_index');
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_code')->unique();
            $table->string('name');
            $table->string('model')->default('Solution X606-S');
            $table->string('ip_address')->nullable();
            $table->unsignedInteger('port')->nullable()->default(4370);
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->enum('connection_type', ['tcp_ip', 'usb', 'serial']);
            $table->enum('status', ['online', 'offline', 'maintenance'])->default('offline');
            $table->timestamp('last_online_at')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'status']);
            $table->index('ip_address');
        });

        Schema::create('biometric_enrollments', function (Blueprint $table) {
            $table->id();
            $table->enum('user_type', ['lecturer', 'student']);
            $table->unsignedBigInteger('user_id');
            $table->string('fingerprint_id');
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->enum('status', ['enrolled', 'not_enrolled', 'inactive'])->default('not_enrolled');
            $table->timestamps();

            $table->unique('fingerprint_id');
            $table->unique(['user_type', 'user_id']);
            $table->unique(['user_type', 'user_id', 'fingerprint_id']);
            $table->index(['user_type', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_enrollments');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('schedules');
    }
};






