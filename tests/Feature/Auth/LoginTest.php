<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can login successfully', function () {

    // Buat user testing
    User::create([
        'name' => 'Hadi',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('hadhieganteng'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@gmail.com',
        'password' => 'hadhieganteng',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'status' => true,
            'message' => 'Login berhasil',
        ]);
});

it('login fails when email is wrong', function () {

    User::create([
        'name' => 'Hadi',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('hadhieganteng'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'salah@gmail.com',
        'password' => 'hadhieganteng',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
            'message' => 'Email atau password salah',
        ]);
});

it('login fails with wrong password', function () {

    User::create([
        'name' => 'Hadi',
        'email' => 'admin@gmail.com',
        'password' => bcrypt('hadhieganteng'),
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@gmail.com',
        'password' => 'passwordsalah',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
            'message' => 'Email atau password salah',
        ]);
});

it('login validation fails when fields are empty', function () {

    $response = $this->postJson('/api/login', [
        'email' => '',
        'password' => '',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
            'password',
        ]);
});

it('login fails when email format is invalid', function () {

    $response = $this->postJson('/api/login', [
        'email' => 'admin',
        'password' => 'hadhieganteng',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'email',
        ]);
});