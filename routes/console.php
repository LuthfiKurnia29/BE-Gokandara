<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule as FacadesSchedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// FacadesSchedule::call(function () {
//     // This is a placeholder for any scheduled tasks you want to run.
//     // You can add your logic here, such as sending notifications or cleaning up resources.
//     $this->info('Scheduled task executed successfully!');
// })->everyMinute()->withoutOverlapping()->onSuccess(function () {

//     // Logic to execute on success, like logging or sending a notification.
// })->onFailure(function () {
//     // Logic to execute on failure, like logging or sending an alert.
// });