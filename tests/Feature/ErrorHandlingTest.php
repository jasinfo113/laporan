<?php

use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware('web')->get('/test-error-page', function () {
        throw new RuntimeException('Test error from exception handler');
    });
});

test('exceptions are shown on blade pages after redirecting back', function () {
    config(['app.debug' => true]);

    $response = $this->from('/login')
        ->followingRedirects()
        ->get('/test-error-page');

    $response->assertOk();
    $response->assertSee('Test error from exception handler');
});

test('direct exceptions render a blade error page', function () {
    config(['app.debug' => true]);

    $response = $this->get('/test-error-page');

    $response->assertStatus(500);
    $response->assertSee('Test error from exception handler');
    $response->assertSee('Terjadi kesalahan pada sistem');
});
