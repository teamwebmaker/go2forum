<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

uses(RefreshDatabase::class);

test('guests cannot access fsnb files', function () {
    $this->get('/public/fsnb')
        ->assertUnauthorized();

    $this->get('/public/fsnb/data/t.js')
        ->assertUnauthorized();
});

test('authenticated users are redirected to the fsnb index file', function () {
    $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/public/fsnb')
        ->assertRedirect('/public/fsnb/index.html');
});

test('authenticated users can access the fsnb index', function () {
    $response = $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/public/fsnb/index.html')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    expect($response->baseResponse)
        ->toBeInstanceOf(BinaryFileResponse::class)
        ->and($response->baseResponse->getFile()->getRealPath())
        ->toBe(realpath(resource_path('fsnb/index.html')));
});

test('authenticated users can access fsnb data files', function () {
    $response = $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/public/fsnb/data/t.js')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');

    expect($response->baseResponse)
        ->toBeInstanceOf(BinaryFileResponse::class)
        ->and($response->baseResponse->getFile()->getRealPath())
        ->toBe(realpath(resource_path('fsnb/data/t.js')));
});

test('missing fsnb files return not found', function () {
    $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/public/fsnb/data/missing.js')
        ->assertNotFound();
});
