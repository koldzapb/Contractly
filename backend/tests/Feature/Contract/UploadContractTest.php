<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Enums\FileType;
use App\Jobs\AnalyzeContractJob;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('contracts');
    Queue::fake();
    $this->user = User::factory()->create();
});

describe('upload contract', function () {
    it('uploads a pdf file successfully', function () {
        $file = UploadedFile::fake()->create('contract.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'original_filename',
                    'file_size',
                    'status',
                    'created_at',
                ],
            ])
            ->assertJson([
                'data' => [
                    'original_filename' => 'contract.pdf',
                    'status' => ContractStatus::PENDING->value,
                ],
            ]);

        $this->assertDatabaseHas('contracts', [
            'user_id' => $this->user->id,
            'original_filename' => 'contract.pdf',
            'status' => ContractStatus::PENDING->value,
        ]);

        Storage::disk('contracts')->assertExists(
            Contract::first()->file_path,
        );

        // Verify analysis job was dispatched
        Queue::assertPushed(AnalyzeContractJob::class, function ($job) {
            return $job->contract->original_filename === 'contract.pdf';
        });
    });

    it('generates title from filename when not provided', function () {
        $file = UploadedFile::fake()->create('employment-agreement-2024.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Employment Agreement 2024');
    });

    it('uses provided title when given', function () {
        $file = UploadedFile::fake()->create('doc.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
                'title' => 'My Custom Title',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'My Custom Title');
    });

    it('uploads an image file successfully', function () {
        $file = UploadedFile::fake()->create('contract.jpg', 1000, 'image/jpeg');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'original_filename' => 'contract.jpg',
                    'status' => ContractStatus::PENDING->value,
                    'file_type' => FileType::IMAGE->value,
                ],
            ]);

        $this->assertDatabaseHas('contracts', [
            'user_id' => $this->user->id,
            'original_filename' => 'contract.jpg',
            'file_type' => FileType::IMAGE->value,
        ]);

        Queue::assertPushed(AnalyzeContractJob::class);
    });

    it('uploads a png image successfully', function () {
        $file = UploadedFile::fake()->create('contract.png', 1000, 'image/png');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'file_type' => FileType::IMAGE->value,
                ],
            ]);
    });

    it('uploads a text file successfully', function () {
        $file = UploadedFile::fake()->create('contract.txt', 500, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'original_filename' => 'contract.txt',
                    'status' => ContractStatus::PENDING->value,
                    'file_type' => FileType::TEXT->value,
                ],
            ]);

        $this->assertDatabaseHas('contracts', [
            'user_id' => $this->user->id,
            'original_filename' => 'contract.txt',
            'file_type' => FileType::TEXT->value,
        ]);

        Queue::assertPushed(AnalyzeContractJob::class);
    });

    it('rejects unsupported file types', function () {
        $file = UploadedFile::fake()->create('document.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('rejects pdf files larger than 10MB', function () {
        $file = UploadedFile::fake()->create('large.pdf', 11000, 'application/pdf');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('rejects image files larger than 20MB', function () {
        $file = UploadedFile::fake()->create('large.jpg', 21000, 'image/jpeg');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('rejects text files larger than 5MB', function () {
        $file = UploadedFile::fake()->create('large.txt', 6000, 'text/plain');

        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('requires a file', function () {
        $response = $this->actingAs($this->user)
            ->postJson('/api/contracts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    });

    it('requires authentication', function () {
        $file = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->postJson('/api/contracts', [
            'file' => $file,
        ]);

        $response->assertStatus(401);
    });
});
