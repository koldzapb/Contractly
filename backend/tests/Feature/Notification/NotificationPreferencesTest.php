<?php

declare(strict_types=1);

use App\Models\NotificationPreference;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('GET /api/notifications/preferences', function () {
    it('returns default preferences for new user', function () {
        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/preferences');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'analysis_complete' => true,
                    'deadline_reminder' => true,
                    'deadline_days_before' => 7,
                    'weekly_digest' => false,
                    'contract_expiring' => true,
                ],
            ]);
    });

    it('returns existing preferences', function () {
        NotificationPreference::factory()->create([
            'user_id' => $this->user->id,
            'analysis_complete' => false,
            'deadline_reminder' => true,
            'deadline_days_before' => 14,
            'weekly_digest' => true,
            'contract_expiring' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/notifications/preferences');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'analysis_complete' => false,
                    'deadline_reminder' => true,
                    'deadline_days_before' => 14,
                    'weekly_digest' => true,
                    'contract_expiring' => false,
                ],
            ]);
    });

    it('requires authentication', function () {
        $response = $this->getJson('/api/notifications/preferences');

        $response->assertUnauthorized();
    });
});

describe('PUT /api/notifications/preferences', function () {
    it('updates analysis complete preference', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'analysis_complete' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'analysis_complete' => false,
                ],
            ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'analysis_complete' => false,
        ]);
    });

    it('updates deadline reminder preference', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'deadline_reminder' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'deadline_reminder' => false,
                ],
            ]);
    });

    it('updates deadline days before', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'deadline_days_before' => 14,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'deadline_days_before' => 14,
                ],
            ]);
    });

    it('updates weekly digest preference', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'weekly_digest' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'weekly_digest' => true,
                ],
            ]);
    });

    it('updates contract expiring preference', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'contract_expiring' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'contract_expiring' => false,
                ],
            ]);
    });

    it('updates multiple preferences at once', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'analysis_complete' => false,
                'deadline_reminder' => false,
                'weekly_digest' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'analysis_complete' => false,
                    'deadline_reminder' => false,
                    'weekly_digest' => true,
                ],
            ]);
    });

    it('validates deadline days before minimum', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'deadline_days_before' => 0,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['deadline_days_before']);
    });

    it('validates deadline days before maximum', function () {
        $response = $this->actingAs($this->user)
            ->putJson('/api/notifications/preferences', [
                'deadline_days_before' => 31,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['deadline_days_before']);
    });

    it('requires authentication', function () {
        $response = $this->putJson('/api/notifications/preferences', [
            'analysis_complete' => false,
        ]);

        $response->assertUnauthorized();
    });
});

describe('POST /api/notifications/preferences/reset', function () {
    it('resets preferences to defaults', function () {
        NotificationPreference::factory()->create([
            'user_id' => $this->user->id,
            'analysis_complete' => false,
            'deadline_reminder' => false,
            'deadline_days_before' => 14,
            'weekly_digest' => true,
            'contract_expiring' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/notifications/preferences/reset');

        $response->assertOk()
            ->assertJson([
                'message' => 'Notification preferences reset to defaults.',
            ]);

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'analysis_complete' => true,
            'deadline_reminder' => true,
            'deadline_days_before' => 7,
            'weekly_digest' => false,
            'contract_expiring' => true,
        ]);
    });

    it('requires authentication', function () {
        $response = $this->postJson('/api/notifications/preferences/reset');

        $response->assertUnauthorized();
    });
});
