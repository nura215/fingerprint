<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\SolutionDeviceIntegrator;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('device:sync-users', function (SolutionDeviceIntegrator $integrator) {
    $summary = $integrator->syncPending();

    $this->info("Sync selesai. Item: {$summary['items']}, berhasil: {$summary['success']}, gagal: {$summary['failed']}.");
})->purpose('Kirim data user pending ke perangkat Solution X606-S via WEB SDK.');

Artisan::command('device:pull-logs', function (SolutionDeviceIntegrator $integrator) {
    $summary = $integrator->pullLogs();

    $this->info("Tarik log selesai. Perangkat: {$summary['devices']}, ditarik: {$summary['pulled']}, diproses: {$summary['processed']}, duplikat: {$summary['ignored']}, gagal: {$summary['failed']}.");
})->purpose('Tarik log scan dari perangkat Solution X606-S via WEB SDK.');





