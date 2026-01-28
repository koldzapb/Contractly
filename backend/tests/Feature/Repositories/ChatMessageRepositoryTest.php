<?php

declare(strict_types=1);

use App\Enums\ChatRole;
use App\Models\ChatMessage;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ChatMessageRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repository = app(ChatMessageRepositoryInterface::class);
    $this->user = User::factory()->create();
    $this->contract = Contract::factory()->for($this->user)->create();
});

describe('find', function () {
    it('finds a chat message by id', function () {
        $message = ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create();

        $found = $this->repository->find($message->id);

        expect($found)->not->toBeNull()
            ->and($found->id)->toBe($message->id);
    });

    it('returns null for non-existent id', function () {
        $found = $this->repository->find('non-existent-id');

        expect($found)->toBeNull();
    });
});

describe('getForContract', function () {
    it('returns all messages for a contract', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->count(5)
            ->create();

        $messages = $this->repository->getForContract($this->contract);

        expect($messages)->toHaveCount(5);
    });

    it('returns messages ordered by created_at ascending', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create(['created_at' => now()->subHour()]);
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create(['created_at' => now()]);
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create(['created_at' => now()->subMinutes(30)]);

        $messages = $this->repository->getForContract($this->contract);

        expect($messages[0]->created_at->lt($messages[1]->created_at))->toBeTrue()
            ->and($messages[1]->created_at->lt($messages[2]->created_at))->toBeTrue();
    });

    it('does not return messages from other contracts', function () {
        $otherContract = Contract::factory()->for($this->user)->create();

        ChatMessage::factory()->for($this->contract)->for($this->user)->count(3)->create();
        ChatMessage::factory()->for($otherContract)->for($this->user)->count(2)->create();

        $messages = $this->repository->getForContract($this->contract);

        expect($messages)->toHaveCount(3);
    });
});

describe('getRecentForContract', function () {
    it('returns recent messages with limit', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->count(10)
            ->create();

        $messages = $this->repository->getRecentForContract($this->contract, 5);

        expect($messages)->toHaveCount(5);
    });

    it('returns messages in chronological order', function () {
        for ($i = 0; $i < 5; $i++) {
            ChatMessage::factory()
                ->for($this->contract)
                ->for($this->user)
                ->create(['created_at' => now()->subMinutes(5 - $i)]);
        }

        $messages = $this->repository->getRecentForContract($this->contract, 3);

        // Should be ordered oldest to newest (chronological)
        expect($messages[0]->created_at->lt($messages[1]->created_at))->toBeTrue()
            ->and($messages[1]->created_at->lt($messages[2]->created_at))->toBeTrue();
    });
});

describe('getForContractAndUser', function () {
    it('returns messages for a specific contract and user', function () {
        $otherUser = User::factory()->create();

        ChatMessage::factory()->for($this->contract)->for($this->user)->count(3)->create();
        ChatMessage::factory()->for($this->contract)->for($otherUser)->count(2)->create();

        $messages = $this->repository->getForContractAndUser($this->contract, $this->user);

        expect($messages)->toHaveCount(3);
    });
});

describe('create', function () {
    it('creates a new chat message', function () {
        $data = [
            'contract_id' => $this->contract->id,
            'user_id' => $this->user->id,
            'role' => ChatRole::USER,
            'content' => 'What are the payment terms?',
        ];

        $message = $this->repository->create($data);

        expect($message)->toBeInstanceOf(ChatMessage::class)
            ->and($message->content)->toBe('What are the payment terms?')
            ->and($message->role)->toBe(ChatRole::USER)
            ->and($message->exists)->toBeTrue();
    });

    it('creates assistant messages with tokens', function () {
        $data = [
            'contract_id' => $this->contract->id,
            'user_id' => $this->user->id,
            'role' => ChatRole::ASSISTANT,
            'content' => 'The payment terms are...',
            'tokens_used' => 150,
        ];

        $message = $this->repository->create($data);

        expect($message->tokens_used)->toBe(150);
    });
});

describe('delete', function () {
    it('deletes a chat message', function () {
        $message = ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->create();

        $result = $this->repository->delete($message);

        expect($result)->toBeTrue()
            ->and(ChatMessage::find($message->id))->toBeNull();
    });
});

describe('clearForContract', function () {
    it('deletes all messages for a contract', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->count(5)
            ->create();

        $deleted = $this->repository->clearForContract($this->contract);

        expect($deleted)->toBe(5)
            ->and($this->repository->getForContract($this->contract))->toHaveCount(0);
    });

    it('does not delete messages from other contracts', function () {
        $otherContract = Contract::factory()->for($this->user)->create();

        ChatMessage::factory()->for($this->contract)->for($this->user)->count(3)->create();
        ChatMessage::factory()->for($otherContract)->for($this->user)->count(2)->create();

        $this->repository->clearForContract($this->contract);

        expect($this->repository->getForContract($otherContract))->toHaveCount(2);
    });
});

describe('countForContract', function () {
    it('counts messages for a contract', function () {
        ChatMessage::factory()
            ->for($this->contract)
            ->for($this->user)
            ->count(7)
            ->create();

        $count = $this->repository->countForContract($this->contract);

        expect($count)->toBe(7);
    });

    it('returns zero when no messages exist', function () {
        $count = $this->repository->countForContract($this->contract);

        expect($count)->toBe(0);
    });
});
