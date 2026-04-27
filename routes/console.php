<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Command bawaan Laravel untuk menampilkan kutipan motivasi di terminal.
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Menjadwalkan pengecekan stok minimal dan kedaluwarsa setiap hari.
Schedule::command('pos:cek-stok')->dailyAt('08:00')->description('Cek stok minimal & kedaluwarsa');
