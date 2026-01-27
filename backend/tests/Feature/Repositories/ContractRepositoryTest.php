<?php

declare(strict_types=1);

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ContractRepositoryInterface::class);
    $this->user = User::factory()->create();
});

describe('find', function () {
    it('finds a contract by id', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $found = $this->repository->find($contract->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($contract->id);
    });

    it('returns null for non-existent id', function () {
        $found = $this->repository->find('non-existent-uuid');

        expect($found)->toBeNull();
    });
});

describe('findForUser', function () {
    it('finds a contract for the correct user', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $found = $this->repository->findForUser($contract->id, $this->user);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($contract->id);
    });

    it('returns null for wrong user', function () {
        $otherUser = User::factory()->create();
        $contract = Contract::factory()->for($otherUser)->create();

        $found = $this->repository->findForUser($contract->id, $this->user);

        expect($found)->toBeNull();
    });

    it('accepts user id as integer', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $found = $this->repository->findForUser($contract->id, $this->user->id);

        expect($found)->not->toBeNull();
    });
});

describe('getAllForUser', function () {
    it('returns all contracts for a user ordered by latest', function () {
        $contracts = Contract::factory()
            ->for($this->user)
            ->count(3)
            ->create();

        $result = $this->repository->getAllForUser($this->user);

        expect($result)->toHaveCount(3)
            ->and($result->first()->id)->toBe($contracts->last()->id);
    });

    it('does not return contracts from other users', function () {
        $otherUser = User::factory()->create();
        Contract::factory()->for($this->user)->create();
        Contract::factory()->for($otherUser)->create();

        $result = $this->repository->getAllForUser($this->user);

        expect($result)->toHaveCount(1);
    });
});

describe('getByStatusForUser', function () {
    it('returns contracts filtered by status', function () {
        Contract::factory()->for($this->user)->pending()->create();
        Contract::factory()->for($this->user)->completed()->create();
        Contract::factory()->for($this->user)->completed()->create();

        $pending = $this->repository->getByStatusForUser(ContractStatus::PENDING, $this->user);
        $completed = $this->repository->getByStatusForUser(ContractStatus::COMPLETED, $this->user);

        expect($pending)->toHaveCount(1)
            ->and($completed)->toHaveCount(2);
    });
});

describe('create', function () {
    it('creates a new contract', function () {
        $data = [
            'user_id' => $this->user->id,
            'title' => 'Test Contract',
            'original_filename' => 'test.pdf',
            'file_path' => 'contracts/test.pdf',
            'file_size' => 1024,
            'status' => ContractStatus::PENDING,
        ];

        $contract = $this->repository->create($data);

        expect($contract)->toBeInstanceOf(Contract::class)
            ->and($contract->title)->toBe('Test Contract')
            ->and($contract->exists)->toBeTrue();
    });
});

describe('update', function () {
    it('updates a contract', function () {
        $contract = Contract::factory()->for($this->user)->create([
            'title' => 'Original Title',
        ]);

        $result = $this->repository->update($contract, ['title' => 'Updated Title']);

        expect($result)->toBeTrue()
            ->and($contract->fresh()->title)->toBe('Updated Title');
    });
});

describe('delete', function () {
    it('soft deletes a contract', function () {
        $contract = Contract::factory()->for($this->user)->create();

        $result = $this->repository->delete($contract);

        expect($result)->toBeTrue()
            ->and(Contract::find($contract->id))->toBeNull()
            ->and(Contract::withTrashed()->find($contract->id))->not->toBeNull();
    });
});

describe('paginateForUser', function () {
    it('paginates contracts for a user', function () {
        Contract::factory()->for($this->user)->count(25)->create();

        $result = $this->repository->paginateForUser($this->user, 10);

        expect($result->count())->toBe(10)
            ->and($result->total())->toBe(25)
            ->and($result->lastPage())->toBe(3);
    });
});

describe('searchForUser', function () {
    it('searches contracts by title', function () {
        Contract::factory()->for($this->user)->create(['title' => 'Employment Agreement']);
        Contract::factory()->for($this->user)->create(['title' => 'Service Contract']);
        Contract::factory()->for($this->user)->create(['title' => 'NDA Document']);

        $result = $this->repository->searchForUser('Agreement', $this->user);

        expect($result)->toHaveCount(1)
            ->and($result->first()->title)->toBe('Employment Agreement');
    });

    it('searches contracts by original filename', function () {
        Contract::factory()->for($this->user)->create([
            'title' => 'Some Contract',
            'original_filename' => 'employment-agreement-2024.pdf',
        ]);

        $result = $this->repository->searchForUser('employment', $this->user);

        expect($result)->toHaveCount(1);
    });
});
