<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Clear all rate limiters before each test
    RateLimiter::clear('api');
    RateLimiter::clear('auth');
    RateLimiter::clear('uploads');
    RateLimiter::clear('chat');
    RateLimiter::clear('polling');
});

describe('rate limiting', function () {
    it('applies auth rate limit to login endpoint', function () {
        // Auth limit is 10 per minute
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrong-password',
            ])->assertStatus(422); // Validation error, but not rate limited
        }

        // 11th request should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    });

    it('applies api rate limit to authenticated endpoints', function () {
        $user = User::factory()->create();

        // API limit is 60 per minute - make 60 requests
        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($user)
                ->getJson('/api/user')
                ->assertStatus(200);
        }

        // 61st request should be rate limited
        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(429);
    });

    it('includes rate limit headers in response', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
    });

    it('rate limits per user not globally', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // User 1 makes requests
        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($user1)->getJson('/api/user');
        }

        // User 1 is now rate limited
        $this->actingAs($user1)
            ->getJson('/api/user')
            ->assertStatus(429);

        // User 2 should still be able to make requests
        $this->actingAs($user2)
            ->getJson('/api/user')
            ->assertStatus(200);
    });
});
