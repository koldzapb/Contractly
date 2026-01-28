<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\ChatMessage;
use App\Models\Contract;
use App\Models\User;
use App\Repositories\Contracts\ChatMessageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * @extends BaseRepository<ChatMessage>
 */
class ChatMessageRepository extends BaseRepository implements ChatMessageRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(ChatMessage::class);
    }

    public function find(string $id): ?ChatMessage
    {
        return $this->query()->find($id);
    }

    public function getForContract(Contract|string $contract): Collection
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->orderBy('created_at')
            ->get();
    }

    public function getRecentForContract(Contract|string $contract, int $limit = 50): Collection
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function getForContractAndUser(Contract|string $contract, User|int $user): Collection
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->where('user_id', $this->resolveUserId($user))
            ->orderBy('created_at')
            ->get();
    }

    public function create(array $data): ChatMessage
    {
        return $this->query()->create($data);
    }

    public function delete(ChatMessage $message): bool
    {
        return (bool) $message->delete();
    }

    public function clearForContract(Contract|string $contract): int
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->delete();
    }

    public function countForContract(Contract|string $contract): int
    {
        return $this->query()
            ->where('contract_id', $this->resolveId($contract))
            ->count();
    }
}
