<?php

use App\Models\User;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user bisa melihat daftar siswa di suatu kelas', function () {
    $user = User::factory()->create();

    Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    Siswa::create([
        'nis' => '002',
        'nama' => 'Ani',
        'kelas' => '2'
    ]);

    $response = $this->actingAs($user)
        ->get(route('kelas.show', ['id' => '1']));

    $response->assertStatus(200);
    $response->assertViewIs('kelas.dasboard');
    $response->assertViewHasAll([
        'id_kelas',
        'siswas',
        'total_siswa'
    ]);
});

test('user bisa melihat kelas yang tidak memiliki siswa', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('kelas.show', ['id' => '99']));

    $response->assertStatus(200);
    $response->assertViewIs('kelas.dasboard');
});

test('user bisa melihat halaman tambah siswa', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('siswa.create', ['id' => '1']));

    $response->assertStatus(200);
    $response->assertViewIs('siswa.create');
});

test('user bisa menambah data siswa baru', function () {
    $user = User::factory()->create();

    Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    Siswa::create([
        'nis' => '002',
        'nama' => 'Ani',
        'kelas' => '2'
    ]);

    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis' => '003',
            'nama' => 'Caca'
        ]);

    $response->assertRedirect(route('kelas.show', ['id' => '1']));

    $this->assertDatabaseHas('siswas', [
        'nis' => '003',
        'nama' => 'Caca',
        'kelas' => '1'
    ]);
});

test('gagal menambah siswa jika ada field yang kosong', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis' => '',
            'nama' => ''
        ]);

    $response->assertSessionHasErrors([
        'nis',
        'nama'
    ]);
});

test('gagal menambah siswa jika nis bukan angka', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis' => 'ABC',
            'nama' => 'Budi'
        ]);

    $response->assertSessionHasErrors('nis');
});

test('gagal menambah siswa jika nis sudah digunakan', function () {
    $user = User::factory()->create();

    Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis' => '001',
            'nama' => 'Budi Baru'
        ]);

    $response->assertSessionHasErrors('nis');
});

test('gagal menambah siswa jika nama lebih dari 255 karakter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis' => '003',
            'nama' => str_repeat('A', 256)
        ]);

    $response->assertSessionHasErrors('nama');
});

test('user bisa melihat halaman edit siswa', function () {
    $user = User::factory()->create();

    $siswa = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->get(route('siswa.edit', ['id' => $siswa->id]));

    $response->assertStatus(200);
    $response->assertViewIs('siswa.edit');
});

test('halaman edit mengembalikan pesan siswa tidak ditemukan jika siswa tidak ditemukan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('siswa.edit', ['id' => 999]))
        ->assertNotFound();
});

test('user bisa mengupdate data siswa', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis' => '001',
            'nama' => 'Budi Update'
        ]);

    $response->assertRedirect(route('kelas.show', ['id' => '1']));

    $this->assertDatabaseHas('siswas', [
        'id' => $budi->id,
        'nama' => 'Budi Update'
    ]);
});

test('user bisa update nama tanpa mengubah nis', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis' => '001',
            'nama' => 'Nama Baru'
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('siswas', [
        'id' => $budi->id,
        'nis' => '001',
        'nama' => 'Nama Baru'
    ]);
});

test('gagal update jika ada field yang kosong', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis' => '',
            'nama' => ''
        ]);

    $response->assertSessionHasErrors([
        'nis',
        'nama'
    ]);
});

test('gagal update jika nis bukan angka', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis' => 'ABC',
            'nama' => 'Update'
        ]);

    $response->assertSessionHasErrors('nis');
});

test('gagal update jika nis sudah digunakan siswa lain', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $ani = Siswa::create([
        'nis' => '002',
        'nama' => 'Ani',
        'kelas' => '2'
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis' => '002',
            'nama' => 'Update'
        ]);

    $response->assertSessionHasErrors('nis');
});

test('gagal update jika nama lebih dari 255 karakter', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis' => '001',
            'nama' => str_repeat('A', 256)
        ]);

    $response->assertSessionHasErrors('nama');
});

test('sistem update siswa mengembalikan pesan siswa tidak ditemukan jika siswa tidak ditemukan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('siswa.update', ['id' => 999]), [
            'nis' => '001',
            'nama' => 'Update'
        ])
        ->assertNotFound();
});

test('user bisa menghapus data siswa', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => '1'
    ]);

    $response = $this->actingAs($user)
        ->delete(route('siswa.destroy', ['id' => $budi->id]));

    $response->assertRedirect(route('kelas.show', ['id' => '1']));

    $this->assertDatabaseMissing('siswas', [
        'id' => $budi->id
    ]);
});

test('sistem hapus siswa mengembalikan pesan siswa tidak ditemukan jika siswa tidak ditemukan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('siswa.destroy', ['id' => 999]))
        ->assertNotFound();
});