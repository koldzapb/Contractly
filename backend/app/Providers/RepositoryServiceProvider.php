<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\ChatMessageRepository;
use App\Repositories\ContractAnalysisRepository;
use App\Repositories\ContractClauseRepository;
use App\Repositories\ContractDeadlineRepository;
use App\Repositories\ContractRepository;
use App\Repositories\Contracts\ChatMessageRepositoryInterface;
use App\Repositories\Contracts\ContractAnalysisRepositoryInterface;
use App\Repositories\Contracts\ContractClauseRepositoryInterface;
use App\Repositories\Contracts\ContractDeadlineRepositoryInterface;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Repositories\Contracts\NotificationPreferenceRepositoryInterface;
use App\Repositories\Contracts\ReminderRepositoryInterface;
use App\Repositories\NotificationPreferenceRepository;
use App\Repositories\ReminderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All repository bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        ChatMessageRepositoryInterface::class => ChatMessageRepository::class,
        ContractRepositoryInterface::class => ContractRepository::class,
        ContractAnalysisRepositoryInterface::class => ContractAnalysisRepository::class,
        ContractClauseRepositoryInterface::class => ContractClauseRepository::class,
        ContractDeadlineRepositoryInterface::class => ContractDeadlineRepository::class,
        NotificationPreferenceRepositoryInterface::class => NotificationPreferenceRepository::class,
        ReminderRepositoryInterface::class => ReminderRepository::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
