<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fingerprint_scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('fingerprint_id');
            $table->timestamp('scan_time');
            $table->json('raw_payload')->nullable();
            $table->enum('process_status', ['pending', 'processed', 'failed', 'ignored'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'scan_time']);
            $table->index('fingerprint_id');
            $table->index(['process_status', 'scan_time']);
        });

        Schema::create('schedule_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->restrictOnDelete();
            $table->date('meeting_date');
            $table->unsignedInteger('meeting_number');
            $table->unsignedBigInteger('lecturer_attendance_id')->nullable();
            $table->enum('status', ['scheduled', 'ongoing', 'finished', 'cancelled'])->default('scheduled');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'meeting_date']);
            $table->unique(['schedule_id', 'meeting_number']);
            $table->index(['status', 'meeting_date']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->foreignId('schedule_meeting_id')->nullable()->constrained('schedule_meetings')->nullOnDelete();
            $table->enum('user_type', ['lecturer', 'student']);
            $table->unsignedBigInteger('user_id');
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->string('fingerprint_id');
            $table->timestamp('attendance_time');
            $table->enum('attendance_status', ['present', 'late', 'rejected'])->default('present');
            $table->enum('validation_status', ['valid', 'outside_schedule', 'wrong_class', 'lecturer_not_present', 'unknown_fingerprint', 'no_active_schedule']);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['schedule_meeting_id', 'user_type', 'user_id'], 'attendances_pertemuan_users_unique');
            $table->index(['schedule_id', 'attendance_time']);
            $table->index(['schedule_meeting_id', 'attendance_status']);
            $table->index(['user_type', 'user_id', 'attendance_time']);
            $table->index(['fingerprint_id', 'attendance_time']);
            $table->index(['validation_status', 'attendance_time']);
        });

        Schema::table('schedule_meetings', function (Blueprint $table) {
            $table->foreign('lecturer_attendance_id', 'schedule_meetings_lecturer_attendance_foreign')
                ->references('id')
                ->on('attendances')
                ->nullOnDelete();
        });

        Schema::create('door_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->enum('user_type', ['lecturer', 'student', 'admin'])->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('access_time');
            $table->enum('access_status', ['granted', 'denied', 'failed'])->default('denied');
            $table->boolean('open_door')->default(false);
            $table->enum('method', ['fingerprint', 'manual_web', 'exit_button', 'sdk_command'])->default('fingerprint');
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['device_id', 'access_time']);
            $table->index(['room_id', 'access_time']);
            $table->index(['schedule_id', 'access_time']);
            $table->index(['user_type', 'user_id', 'access_time']);
            $table->index(['access_status', 'access_time']);
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('schedule_meetings')) {
            Schema::table('schedule_meetings', function (Blueprint $table) {
                $table->dropForeign('schedule_meetings_lecturer_attendance_foreign');
            });
        }

        Schema::dropIfExists('door_access_logs');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('schedule_meetings');
        Schema::dropIfExists('fingerprint_scan_logs');
    }
};






