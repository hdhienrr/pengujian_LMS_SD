<?php

use App\Models\Materi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->admin = User::factory()->create([
        'role' => 'admin',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($this->admin);
});

// INDEX
test('Berhasil menampilkan daftar materi berdasarkan kelas', function () {

    Materi::create([
        'judul' => 'Matematika Dasar',
        'link_video' => 'https://youtube.com/test',
        'deskripsi' => 'Materi matematika',
        'id_kelas' => 1,
    ]);

    $response = $this->get(
        route('materi.index', 1)
    );

    $response->assertOk();
});

// CREATE
test('Berhasil mengakses halaman tambah materi sebagai admin', function () {

    $response = $this->get(
        route('materi.create', 1)
    );

    $response->assertOk();
});

test('Gagal mengakses halaman tambah materi sebagai guru', function () {

    $guru = User::factory()->create([
        'role' => 'guru',
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($guru)
        ->get(route('materi.create', 1));

    $response->assertRedirect();
});

// STORE
test('Gagal menambahkan materi sebagai guru', function () {

    $guru = User::factory()->create([
        'role' => 'guru',
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($guru)
        ->post(route('materi.store'), [
            'judul' => 'Video Test',
            'link_video' => 'https://youtube.com/test',
            'id_kelas' => 1,
        ]);

    $response->assertForbidden();
});

test('Gagal menambahkan materi ketika data wajib kosong', function () {

    $response = $this->post(
        route('materi.store'),
        []
    );

    $response->assertSessionHasErrors([
        'judul',
        'link_video',
        'id_kelas',
    ]);
});

test('gagal menambahkan materi ketika link video bukan URL valid', function () {

    $response = $this->post(
        route('materi.store'),
        [
            'judul' => 'Video Test',
            'link_video' => 'bukan-url',
            'id_kelas' => 1,
        ]
    );

    $response->assertSessionHasErrors([
        'link_video',
    ]);
});

test('berhasil menambahkan materi', function () {

    $this->post(route('materi.store'), [
        'judul' => 'Video Test',
        'link_video' => 'https://youtube.com/test',
        'deskripsi' => 'Deskripsi video',
        'id_kelas' => 1,
    ]);

    $this->assertDatabaseHas('materis', [
        'judul' => 'Video Test',
        'id_kelas' => 1,
    ]);
});

test('Berhasil redirect setelah menambahkan materi', function () {

    $response = $this->post(route('materi.store'), [
        'judul' => 'Video Test',
        'link_video' => 'https://youtube.com/test',
        'deskripsi' => 'Deskripsi video',
        'id_kelas' => 1,
    ]);

    $response->assertRedirect();
});

// DESTROY
test('Gagal menghapus materi sebagai guru', function () {

    $materi = Materi::create([
        'judul' => 'Video Test',
        'link_video' => 'https://youtube.com/test',
        'id_kelas' => 1,
    ]);

    $guru = User::factory()->create([
        'role' => 'guru',
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($guru)
        ->delete(route('materi.destroy', $materi->id));

    $response->assertForbidden();
});

test('Berhasil menghapus materi', function () {

    $materi = Materi::create([
        'judul' => 'Video Test',
        'link_video' => 'https://youtube.com/test',
        'id_kelas' => 1,
    ]);

    $this->delete(
        route('materi.destroy', $materi->id)
    );

    $this->assertDatabaseMissing('materis', [
        'id' => $materi->id,
    ]);
});

test('Berhasil redirect setelah menghapus materi', function () {

    $materi = Materi::create([
        'judul' => 'Video Test',
        'link_video' => 'https://youtube.com/test',
        'id_kelas' => 1,
    ]);

    $response = $this->delete(
        route('materi.destroy', $materi->id)
    );

    $response->assertRedirect();
});
