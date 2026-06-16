<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CekRaporController;
use App\Http\Controllers\SiswaController; 
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\RapotController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MateriController;
Route::get('/', function () {
    return view('welcome');
})->name('welcome');
Route::post('/cek-siswa', [CekRaporController::class, 'cariSiswa'])->name('cek.siswa');
require __DIR__.'/auth.php';
    Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/kelas/{id}', [SiswaController::class, 'index'])->name('kelas.show'); 
    Route::get('/kelas/{id}', [SiswaController::class, 'index'])->name('kelas.show');
    Route::get('/kelas/{id}/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
    Route::post('/kelas/{id}/siswa', [SiswaController::class, 'store'])->name('siswa.store');
    Route::get('/siswa/{id}/edit', [SiswaController::class, 'edit'])->name('siswa.edit');
    Route::put('/siswa/{id}', [SiswaController::class, 'update'])->name('siswa.update');
    Route::delete('/siswa/{id}', [SiswaController::class, 'destroy'])->name('siswa.destroy');
    Route::get('/kelas/{id}/absensi', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::post('/kelas/{id}/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
    Route::get('/kelas/{id}/absensi/pdf', [AbsensiController::class, 'downloadPDF'])->name('absensi.pdf');
    Route::get('/kelas/{id}/nilai', [NilaiController::class, 'index'])->name('nilai.mapel');
    Route::get('/kelas/{id}/nilai/{mapel}/pdf', [NilaiController::class, 'downloadPDFPerMapel'])->name('nilai.pdf_detail');
    Route::get('/kelas/{id}/nilai/{mapel}', [NilaiController::class, 'create'])->name('nilai.input');
    Route::post('/kelas/{id}/nilai', [NilaiController::class, 'store'])->name('nilai.store');
    Route::get('/kelas/{id}/tambah-mapel', [NilaiController::class, 'createMapel'])->name('mapel.create');
    Route::post('/mapel/store', [NilaiController::class, 'storeMapel'])->name('mapel.store');
    Route::get('/kelas/{id}/rekap-nilai', [RapotController::class, 'index'])->name('rapot.show');
    Route::get('/kelas/{id}/rapot/pdf', [RapotController::class, 'cetakPDF'])->name('rapot.pdf');
    Route::get('/kelas/{id_kelas}/materi', [MateriController::class, 'index'])->name('materi.index');
    Route::get('/kelas/{id_kelas}/materi/create', [MateriController::class, 'create'])->name('materi.create');
    Route::post('/materi/store', [MateriController::class, 'store'])->name('materi.store');
    Route::delete('/materi/{id}', [MateriController::class, 'destroy'])->name('materi.destroy');
});