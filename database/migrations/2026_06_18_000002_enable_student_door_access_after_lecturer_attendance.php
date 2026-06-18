<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->updateOrInsert([
            'key' => 'allow_student_open_door',
        ], [
            'value' => 'true',
            'description' => 'Jika true, mahasiswa valid dapat membuka pintu setelah dosen hadir.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('key', 'allow_student_open_door')
            ->update([
                'value' => 'false',
                'updated_at' => now(),
            ]);
    }
};
