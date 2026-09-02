
<?php

use App\Http\Controllers\LogoutController;
use App\Livewire\RujukanAdd;
use App\Livewire\RujukanIndex;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Livewire\RujukanEdit;
use App\Livewire\RujukanSppd;
use App\Livewire\SppdBuat;
use App\Livewire\SppdTest;
use App\Http\Controllers\Api\PnsController;
use App\Livewire\BuktiAmbulance;
use App\Livewire\RujukanSppdSrikandi;

Route::middleware(['guest'])->group(function () {
    Volt::route('/', 'pages.auth.login')
        ->name('login');
});

Volt::route('/users', 'users');


Route::middleware(['auth', 'verified'])->group(function () {
    Volt::route('dashboard', 'pages.dashboard')
        ->name('dashboard');
    Volt::route('users', 'pages.users.users-index')
        ->name('users');
    Route::get('/rujukan', RujukanIndex::class)->name('rujukan.index');
    Route::get('/rujukan/buat', RujukanAdd::class);
    Route::get('/sppd/buat', SppdBuat::class);

    Route::get('/rujukan/{id}/edit', RujukanEdit::class);
    Route::get('/rujukan/sppd/{id}', RujukanSppd::class);  
    Route::get('/rujukan/sppd-srikandi/{id}', RujukanSppdSrikandi::class);



    Volt::route('jadwal', 'pages.jadwal.jadwal-index')
        ->name('jadwal');
    Volt::route('logbook', 'pages.logbook.logbook-index')
        ->name('logbook');
    Volt::route('laporan', 'pages.laporan')
        ->name('laporan');
    Route::view('laporan-jadwal-perbulan', 'pages.laporan-jadwal')
        ->name('laporan-jadwal');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

    Route::get('/sppd/test', SppdTest::class);
    Route::get('/bukti-pelayanan-ambulance', BuktiAmbulance::class);

});

// routes/web.php atau routes/api.php

Route::middleware('auth')->group(function () {
    Route::get('pns/search', [PnsController::class, 'search'])->name('pns.search');
});


// Route::view('profile', 'profile')
//     ->middleware(['auth'])
//     ->name('profile');

require __DIR__ . '/auth.php';
