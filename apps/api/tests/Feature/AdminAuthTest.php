<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_return_401_without_login(): void
    {
        $this->getJson('/api/v1/admin/tours')
            ->assertUnauthorized()
            ->assertHeader('X-Request-Id');
        $this->getJson('/api/v1/admin/categories')->assertUnauthorized();
        $this->postJson('/api/v1/admin/tours', [
            'title' => 'New Tour',
            'duration_days' => 3,
        ])->assertUnauthorized();
    }

    public function test_api_echoes_client_request_id_header(): void
    {
        $clientId = '550e8400-e29b-41d4-a716-446655440000';

        $this->getJson('/api/v1/tours', ['X-Request-Id' => $clientId])
            ->assertOk()
            ->assertHeader('X-Request-Id', $clientId);
    }
}
