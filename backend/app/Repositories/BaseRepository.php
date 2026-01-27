<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
abstract class BaseRepository
{
    /**
     * @param class-string<TModel> $modelClass
     */
    public function __construct(
        protected string $modelClass,
    ) {}

    /**
     * Create a new query builder instance.
     *
     * @return \Illuminate\Database\Eloquent\Builder<TModel>
     */
    protected function query()
    {
        /** @phpstan-ignore return.type */
        return $this->modelClass::query();
    }

    /**
     * Find a model by ID.
     *
     * @return TModel|null
     */
    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * Find a model by ID or throw exception.
     *
     * @return TModel
     */
    public function findOrFail(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * Get all models.
     *
     * @return Collection<int, TModel>
     */
    public function all(): Collection
    {
        /** @phpstan-ignore return.type */
        return $this->query()->get();
    }

    /**
     * Create a new model.
     *
     * @param array<string, mixed> $data
     *
     * @return TModel
     */
    public function create(array $data): Model
    {
        return $this->query()->create($data);
    }

    /**
     * Get the user ID from a User model or integer.
     */
    protected function resolveUserId(mixed $user): int
    {
        return $user instanceof Model ? (int) $user->getKey() : (int) $user;
    }

    /**
     * Get the ID from a model or string.
     */
    protected function resolveId(mixed $model): string
    {
        return $model instanceof Model ? (string) $model->getKey() : (string) $model;
    }
}
