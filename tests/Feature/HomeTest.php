<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user);
});

// INDEX
test('Berhasil mengakses halaman dashboard', function () {

    $response = $this->get(
        route('dashboard')
    );

    $response->assertOk();
});

test('Berhasil menampilkan view dashboard', function () {

    $response = $this->get(
        route('dashboard')
    );

    $response->assertViewIs('dashboard');
});

test('Berhasil mengirim data kelas ke view', function () {

    $response = $this->get(
        route('dashboard')
    );

    $response->assertViewHas('kelas');
});

test('Berhasil menampilkan enam data kelas', function () {

    $response = $this->get(
        route('dashboard')
    );

    $kelas = $response->viewData('kelas');

    expect($kelas)->toHaveCount(6);
});

test('Gagal mengakses dashboard tanpa login', function () {

    auth()->logout();

    $response = $this->get(
        route('dashboard')
    );

    $response->assertRedirect(
        route('login')
    );
});