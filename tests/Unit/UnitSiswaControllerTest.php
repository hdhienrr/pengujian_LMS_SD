<?php

use App\Models\User;
use App\Models\Siswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user bisa login jika email dan password benar', function () {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->post('/login', [
        'email'    => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('Login gagal jika email atau password salah (password salah, email salah, atau keduanya salah)', function () {
    $user = User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    // 1. Password salah
    $response = $this->post('/login', [
        'email'    => 'test@example.com',
        'password' => 'wrongpassword',
    ]);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    // 2. Email salah
    $response = $this->post('/login', [
        'email'    => 'wrong@example.com',
        'password' => 'password',
    ]);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    // 3. Keduanya salah
    $response = $this->post('/login', [
        'email'    => 'wrong@example.com',
        'password' => 'wrongpassword',
    ]);
    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('user bisa logout', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    $response->assertRedirect('/');
    $this->assertGuest();
});

test('P1 – Store sukses (NIS unik, NIS numerik, nama terisi dan <=255)', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => '001',
            'nama' => 'Budi',
        ]);

    $response->assertRedirect(route('kelas.show', ['id' => '1']));
    $response->assertSessionHas('success', 'Berhasil menambahkan data siswa!');

    $this->assertDatabaseHas('siswas', [
        'nis'   => '001',
        'nama'  => 'Budi',
        'kelas' => '1',
    ]);
});

test('P2 – Store gagal jika field tidak diisi, NIS bukan angka, NIS sudah digunakan, atau nama >255', function () {
    $user = User::factory()->create();

    // Buat data siswa untuk menguji unique
    Siswa::create([
        'nis'   => '001',
        'nama'  => 'Budi',
        'kelas' => '1',
    ]);

    // 1. NIS kosong
    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => '',
            'nama' => 'Budi',
        ]);
    $response->assertSessionHasErrors('nis');

    // 2. Nama kosong
    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => '002',
            'nama' => '',
        ]);
    $response->assertSessionHasErrors('nama');

    // 3. Keduanya kosong
    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => '',
            'nama' => '',
        ]);
    $response->assertSessionHasErrors(['nis', 'nama']);

    // 4. NIS bukan angka
    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => 'ABC',
            'nama' => 'Budi',
        ]);
    $response->assertSessionHasErrors('nis');

    // 5. NIS sudah digunakan
    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => '001',
            'nama' => 'Budi Baru',
        ]);
    $response->assertSessionHasErrors('nis');

    // 6. Nama > 255
    $response = $this->actingAs($user)
        ->post(route('siswa.store', ['id' => '1']), [
            'nis'  => '003',
            'nama' => str_repeat('A', 256),
        ]);
    $response->assertSessionHasErrors('nama');

    // Pastikan tidak ada data tersimpan
    $this->assertDatabaseCount('siswas', 1); // hanya data awal
});

test('P1 – Edit menampilkan form dengan data siswa', function () {
    $user = User::factory()->create();

    $siswa = Siswa::create([
        'nis'   => '001',
        'nama'  => 'Budi',
        'kelas' => '1',
    ]);

    $response = $this->actingAs($user)
        ->get(route('siswa.edit', ['id' => $siswa->id]));

    $response->assertStatus(200);
    $response->assertViewIs('siswa.edit');
    $response->assertViewHas('siswa', $siswa);
    $response->assertViewHas('id_kelas', $siswa->kelas);
});

test('P2 – Edit gagal karena siswa tidak ditemukan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('siswa.edit', ['id' => 999]))
        ->assertNotFound();
});

test('P1 – Update gagal jika field tidak diisi, NIS bukan angka, NIS milik siswa lain, atau nama >255', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis'   => '001',
        'nama'  => 'Budi',
        'kelas' => '1',
    ]);

    $ani = Siswa::create([
        'nis'   => '002',
        'nama'  => 'Ani',
        'kelas' => '2',
    ]);

    // 1. NIS kosong
    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => '',
            'nama' => 'Update',
        ]);
    $response->assertSessionHasErrors('nis');

    // 2. Nama kosong
    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => '001',
            'nama' => '',
        ]);
    $response->assertSessionHasErrors('nama');

    // 3. Keduanya kosong
    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => '',
            'nama' => '',
        ]);
    $response->assertSessionHasErrors(['nis', 'nama']);

    // 4. NIS bukan angka
    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => 'ABC',
            'nama' => 'Update',
        ]);
    $response->assertSessionHasErrors('nis');

    // 5. NIS milik siswa lain
    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => '002',
            'nama' => 'Update',
        ]);
    $response->assertSessionHasErrors('nis');

    // 6. Nama > 255
    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => '001',
            'nama' => str_repeat('A', 256),
        ]);
    $response->assertSessionHasErrors('nama');

    $this->assertDatabaseHas('siswas', [
        'id'   => $budi->id,
        'nis'  => '001',
        'nama' => 'Budi',
    ]);
});

test('P2 – Update gagal karena siswa tidak ditemukan (validasi lolos)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('siswa.update', ['id' => 999]), [
            'nis'  => '001',
            'nama' => 'Update',
        ])
        ->assertNotFound();
});

test('P3 – Update sukses (data ditemukan, validasi lolos)', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis'   => '001',
        'nama'  => 'Budi',
        'kelas' => '1',
    ]);

    $response = $this->actingAs($user)
        ->put(route('siswa.update', ['id' => $budi->id]), [
            'nis'  => '001',
            'nama' => 'Budi Santoso',
        ]);

    $response->assertRedirect(route('kelas.show', ['id' => '1']));
    $response->assertSessionHas('success', 'Data siswa berhasil diperbarui!');

    $this->assertDatabaseHas('siswas', [
        'id'    => $budi->id,
        'nis'   => '001',
        'nama'  => 'Budi Santoso',
    ]);
});

test('P1 – Destroy gagal karena siswa tidak ditemukan', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->delete(route('siswa.destroy', ['id' => 999]))
        ->assertNotFound();
});

test('P2 – Destroy sukses (data ditemukan dan dihapus)', function () {
    $user = User::factory()->create();

    $budi = Siswa::create([
        'nis'   => '001',
        'nama'  => 'Budi',
        'kelas' => '1',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('siswa.destroy', ['id' => $budi->id]));

    $response->assertRedirect(route('kelas.show', ['id' => '1']));
    $response->assertSessionHas('success', 'Data siswa berhasil dihapus.');

    $this->assertDatabaseMissing('siswas', ['id' => $budi->id]);
});