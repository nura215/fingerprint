<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\BiometricEnrollment;
use App\Models\Department;
use App\Models\Device;
use App\Models\Lecturer;
use App\Models\Role;
use App\Models\Room;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::query()->updateOrCreate([
            'name' => User::ROLE_ADMIN,
        ], [
            'description' => 'Akses admin untuk seluruh fitur sistem.',
        ]);

        User::query()->updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin',
            'role_id' => $adminRole->id,
            'status' => 'active',
            'last_login_at' => null,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $AcademicYear = AcademicYear::query()->updateOrCreate([
            'year' => '2026/2027',
            'semester' => 'ganjil',
        ], [
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-15',
            'is_active' => true,
        ]);

        $Department = Department::query()->updateOrCreate([
            'code' => 'TI',
        ], [
            'name' => 'Teknik Informatika',
            'faculty' => 'Fakultas Teknik',
        ]);

        $class = AcademicClass::query()->updateOrCreate([
            'code' => 'TI-3A',
        ], [
            'name' => 'Teknik Informatika 3A',
            'department_id' => $Department->id,
            'academic_year_id' => $AcademicYear->id,
            'status' => 'active',
        ]);

        $Lecturer = Lecturer::query()->updateOrCreate([
            'nidn' => '0101010101',
        ], [
            'name' => 'Dr. Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'phone' => '081234567890',
            'fingerprint_id' => 'X606-L-0001',
            'status' => 'active',
        ]);

        $Student = Student::query()->updateOrCreate([
            'nim' => '2026030001',
        ], [
            'name' => 'Andi Pratama',
            'class_id' => $class->id,
            'fingerprint_id' => 'X606-S-0001',
            'status' => 'active',
        ]);

        $Subject = Subject::query()->updateOrCreate([
            'code' => 'IF301',
        ], [
            'name' => 'Pemrograman Web',
            'sks' => 3,
            'semester' => 3,
            'department_id' => $Department->id,
            'status' => 'active',
        ]);

        $Room = Room::query()->updateOrCreate([
            'code' => 'LAB-KOM-1',
        ], [
            'name' => 'Lab Komputer 1',
            'location' => 'Gedung Laboratorium Lantai 1',
            'capacity' => 30,
            'status' => 'active',
        ]);

        $Device = Device::query()->updateOrCreate([
            'device_code' => 'X606-LAB-001',
        ], [
            'name' => 'Fingerprint Lab Komputer 1',
            'model' => 'Solution X606-S',
            'ip_address' => '192.168.1.10',
            'port' => 4370,
            'room_id' => $Room->id,
            'connection_type' => 'tcp_ip',
            'status' => 'offline',
            'last_online_at' => null,
        ]);

        Schedule::query()->updateOrCreate([
            'academic_year_id' => $AcademicYear->id,
            'class_id' => $class->id,
            'subject_id' => $Subject->id,
            'room_id' => $Room->id,
            'day' => 'monday',
            'start_time' => '08:00',
        ], [
            'lecturer_id' => $Lecturer->id,
            'end_time' => '10:00',
            'status' => 'active',
        ]);

        BiometricEnrollment::query()->updateOrCreate([
            'user_type' => 'student',
            'user_id' => $Student->id,
            'fingerprint_id' => 'X606-S-0001',
        ], [
            'device_id' => $Device->id,
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ]);

        DB::table('system_settings')->updateOrInsert([
            'key' => 'fingerprint_device_model',
        ], [
            'value' => 'Biometrik Solution X606-S',
            'description' => 'Model devices fingerprint dan kontrol akses pintu yang digunakan sistem.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('system_settings')->updateOrInsert([
            'key' => 'allow_student_open_door',
        ], [
            'value' => 'true',
            'description' => 'Jika true, students valid dapat membuka pintu setelah lecturers hadir.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('system_settings')->updateOrInsert([
            'key' => 'door_unlock_duration',
        ], [
            'value' => '5',
            'description' => 'Durasi unlock pintu dalam detik untuk response API.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}







