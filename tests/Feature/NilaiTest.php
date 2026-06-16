<?php

use App\Models\User;
use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Mapel;
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
test('Berhasil mengakses halaman mata pelajaran', function () {

    $response = $this->get(
        route('nilai.mapel', 1)
    );

    $response->assertOk();
});

// MAPEL
test('Berhasil menampilkan daftar mata pelajaran berdasarkan kelas', function () {

    Mapel::create([
        'nama' => 'Matematika',
        'id_kelas' => 1,
        'jenis' => 'utama',
    ]);

    $response = $this->get(
        route('nilai.mapel', 1)
    );

    $response->assertOk();

    $response->assertViewHas('mapels');
});

// CREATE
test('Berhasil mengakses halaman input nilai', function () {

    Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => 1,
    ]);

    $response = $this->get(
        route('nilai.input', [
            'id' => 1,
            'mapel' => 'Matematika'
        ])
    );

    $response->assertOk();
});

test('Berhasil membuka halaman input nilai meskipun belum ada data nilai', function () {

    Siswa::create([
        'nis' => '001',
        'nama' => 'Budi',
        'kelas' => 1,
    ]);

    $response = $this->get(
        route('nilai.input', [
            'id' => 1,
            'mapel' => 'IPA'
        ])
    );

    $response->assertOk();

    $response->assertViewHas('nilai');
});

test('Berhasil menyimpan nilai UTS', function () {

    $siswa = Siswa::create([
        'nis' => '11111',
        'nama' => 'Budi',
        'kelas' => 1,
    ]);

    $this->post(route('nilai.store', 1), [
        'mapel' => 'STS',
        'nilai' => [
            $siswa->id => [
                'nilai_akhir' => 90
            ]
        ]
    ]);

    $this->assertDatabaseHas('nilais', [
        'siswa_id' => $siswa->id,
        'uts' => 90,
    ]);
});

test('Berhasil menyimpan nilai UAS', function () {

    $siswa = Siswa::create([
        'nis' => '22222',
        'nama' => 'Siti',
        'kelas' => 1,
    ]);

    $this->post(route('nilai.store', 1), [
        'mapel' => 'SAS',
        'nilai' => [
            $siswa->id => [
                'nilai_akhir' => 95
            ]
        ]
    ]);

    $this->assertDatabaseHas('nilais', [
        'siswa_id' => $siswa->id,
        'uas' => 95,
    ]);
});

test('Berhasil menghitung rata rata nilai tugas', function () {

    $siswa = Siswa::create([
        'nis' => '33333',
        'nama' => 'Doni',
        'kelas' => 1,
    ]);

    $this->post(route('nilai.store', 1), [
        'mapel' => 'Matematika',
        'nilai' => [
            $siswa->id => [
                'tugas1' => 80,
                'tugas2' => 80,
                'tugas3' => 80,
                'tugas4' => 80,
                'kuis1'  => 90,
                'kuis2'  => 90,
            ]
        ]
    ]);

    $this->assertDatabaseHas('nilais', [
        'siswa_id' => $siswa->id,
        'tugas' => 83,
    ]);
});

test('Berhasil memperbarui nilai yang sudah ada', function () {

    $siswa = Siswa::create([
        'nis' => '44444',
        'nama' => 'Rina',
        'kelas' => 1,
    ]);

    Nilai::create([
        'kelas' => 1,
        'siswa_id' => $siswa->id,
        'mapel' => 'STS',
        'uts' => 70,
    ]);

    $this->post(route('nilai.store', 1), [
        'mapel' => 'STS',
        'nilai' => [
            $siswa->id => [
                'nilai_akhir' => 90
            ]
        ]
    ]);

    $this->assertDatabaseHas('nilais', [
        'siswa_id' => $siswa->id,
        'uts' => 90,
    ]);
});

test('Berhasil melakukan redirect setelah menyimpan nilai', function () {

    $siswa = Siswa::create([
        'nis' => '55555',
        'nama' => 'Fajar',
        'kelas' => 1,
    ]);

    $response = $this->post(route('nilai.store', 1), [
        'mapel' => 'STS',
        'nilai' => [
            $siswa->id => [
                'nilai_akhir' => 90
            ]
        ]
    ]);

    $response->assertRedirect();
});

test('Gagal menyimpan nilai ketika data nilai tidak dikirim', function () {

    $this->withoutExceptionHandling();

    $this->expectException(Throwable::class);

    $this->post(route('nilai.store', 1), [
        'mapel' => 'STS'
    ]);
});

// DOWNLOAD PDF
test('Berhasil mengunduh laporan nilai dalam format PDF', function () {

    $response = $this->get(
        route('nilai.pdf_detail', [
            'id' => 1,
            'mapel' => 'Matematika'
        ])
    );

    $response->assertOk();

    expect(
        $response->headers->get('content-type')
    )->toContain('application/pdf');
});

// CREATE MAPEL
test('Berhasil mengakses halaman tambah mata pelajaran sebagai admin', function () {

    $response = $this->get(
        route('mapel.create', 1)
    );

    $response->assertOk();
});

test('Gagal mengakses halaman tambah mata pelajaran sebagai guru', function () {

    $guru = User::factory()->create([
        'role' => 'guru',
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($guru)
        ->get(route('mapel.create', 1));

    $response->assertRedirect();
});

// STORE MAPEL
test('Berhasil menambahkan mata pelajaran sebagai admin', function () {

    $this->post(route('mapel.store'), [
        'nama_mapel' => 'IPA',
        'id_kelas' => 1,
        'jenis' => 'utama',
    ]);

    $this->assertDatabaseHas('mapels', [
        'nama' => 'IPA',
        'id_kelas' => 1,
        'jenis' => 'utama',
    ]);
});

test('Berhasil melakukan redirect setelah menambahkan mata pelajaran', function () {

    $response = $this->post(route('mapel.store'), [
        'nama_mapel' => 'IPS',
        'id_kelas' => 1,
        'jenis' => 'utama',
    ]);

    $response->assertRedirect();
});

test('Gagal menambahkan mata pelajaran sebagai guru', function () {

    $guru = User::factory()->create([
        'role' => 'guru',
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($guru)
        ->post(route('mapel.store'), [
            'nama_mapel' => 'IPA',
            'id_kelas' => 1,
            'jenis' => 'utama',
        ]);

    $response->assertForbidden();
});

test('Gagal menambahkan mata pelajaran ketika data wajib tidak diisi', function () {

    $response = $this->post(
        route('mapel.store'),
        []
    );

    $response->assertSessionHasErrors([
        'nama_mapel',
        'id_kelas',
        'jenis',
    ]);
});