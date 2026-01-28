<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\ChatMessage;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ChatMessageRepositoryInterface
{
    /**
     * Find a chat message by ID.
     */
    public function find(string $id): ?ChatMessage;

    /**
     * Get all messages for a contract.
     */
    public function getForContract(Contract|string $contract): Collection;

    /**
     * Get recent messages for a contract with a limit.
     */
    public function getRecentForContract(Contract|string $contract, int $limit = 50): Collection;

    /**
     * Get messages for a contract belonging to a specific user.
     */
    public function getForContractAndUser(Contract|string $contract, User|int $user): Collection;

    /**
     * Create a new chat message.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): ChatMessage;

    /**
     * Delete a chat message.
     */
    public function delete(ChatMessage $message): bool;

    /**
     * Clear all messages for a contract.
     */
    public function clearForContract(Contract|string $contract): int;

    /**
     * Count messages for a contract.
     */
    public function countForContract(Contract|string $contract): int;
}
