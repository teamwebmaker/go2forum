<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

uses(RefreshDatabase::class);

test('guests are sent to login and their fsnb destination is remembered', function () {
    $this->get('/fsnb')
        ->assertRedirect('/login')
        ->assertSessionHas('url.intended', url('/fsnb'));

    $this->get('/fsnb/data/t.js')
        ->assertRedirect('/login');

    $this->getJson('/fsnb/data/t.js')
        ->assertUnauthorized();
});

test('successful login returns the user to fsnb', function () {
    $user = User::factory()->create([
        'surname' => 'Tester',
        'password' => 'password',
    ]);

    $this->withSession(['url.intended' => url('/fsnb')])
        ->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(url('/fsnb'));

    $this->assertAuthenticatedAs($user);
});

test('authenticated users are redirected to the fsnb index file', function () {
    $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/fsnb')
        ->assertRedirect('/fsnb/index.html');
});

test('authenticated users can access the fsnb index', function () {
    $response = $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/fsnb/index.html')
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
        ->get('/fsnb/data/t.js')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/javascript; charset=UTF-8');

    expect($response->baseResponse)
        ->toBeInstanceOf(BinaryFileResponse::class)
        ->and($response->baseResponse->getFile()->getRealPath())
        ->toBe(realpath(resource_path('fsnb/data/t.js')));
});

test('missing fsnb files return not found', function () {
    $this->actingAs(User::factory()->create(['surname' => 'Tester']))
        ->get('/fsnb/data/missing.js')
        ->assertNotFound();
});
