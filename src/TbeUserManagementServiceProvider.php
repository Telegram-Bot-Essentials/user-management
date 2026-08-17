<?php

namespace TelegramBotEssentials\UserManagement;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\UserManagement\DTOs\BotUserFilter;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;
use TelegramBotEssentials\UserManagement\Providers\EventServiceProvider;
use TelegramBotEssentials\UserManagement\Services\BotUserFilters;
use TelegramBotEssentials\UserManagement\Services\BotUserSorts;
use TelegramBotEssentials\UserManagement\Services\UserManagementSections;
use TelegramBotEssentials\UserManagement\Telegram\CallbackQueries\Admin\BotUserActionsQuery;
use TelegramBotEssentials\UserManagement\Telegram\CallbackQueries\Admin\BotUsersQuery;
use TelegramBotEssentials\UserManagement\Telegram\StateAnswers\Admin\BotUsersAnswer;

class TbeUserManagementServiceProvider extends ServiceProvider
{
    /**
     * @throws LogicException
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        require_once __DIR__.'/helpers.php';

        $this->app->singleton(BotUserSorts::class, fn () => new BotUserSorts);
        $this->app->singleton(BotUserFilters::class, fn () => new BotUserFilters);
        $this->app->singleton(UserManagementSections::class, fn () => new UserManagementSections);

        $this->mergeConfigFrom(__DIR__.'/../config/tbe-user-management.php', 'tbe-user-management');

        $this->registerPublishing();

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tbe-user-management');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->app->register(EventServiceProvider::class);

        callbackQueryBus()->addCallbackQueries([
            BotUsersQuery::class,
//            BotUserActionsQuery::class,
        ]);

        stateAnswerBus()->addStateAnswers([
            BotUsersAnswer::class,
        ]);
    }

    public function boot(): void
    {
        $this->registerDefaultSorts();
        $this->registerDefaultFilters();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tbe-user-management.php' => config_path('tbe-user-management.php'),
            ], 'tbe-user-management-config');

            $this->publishes([
                __DIR__.'/../lang' => resource_path('lang/vendor/tbe-user-management'),
            ], 'tbe-user-management-translations');
        }
    }

    private function registerDefaultSorts(): void
    {
        botUserSorts()->addSort(new BotUserSort(
            key: 'last_interaction',
            label: __('tbe-user-management::bot_users.sorts.last_interaction'),
            apply: fn ($query, $direction) => $direction === 'asc'
                ? $query->orderBy('last_interaction')
                : $query->orderByDesc('last_interaction'),
            display: fn (BotUser $user) => $user->last_interaction->shortRelativeToNowDiffForHumans(),
        ));

        botUserSorts()->addSort(new BotUserSort(
            key: 'created_at',
            label: __('tbe-user-management::bot_users.sorts.created_at'),
            apply: fn ($query, $direction) => $direction === 'asc'
                ? $query->orderBy('created_at')
                : $query->orderByDesc('created_at'),
            display: fn (BotUser $user) => $user->created_at->shortRelativeToNowDiffForHumans(),
        ));

        botUserSorts()->addSort(new BotUserSort(
            key: 'username',
            label: __('tbe-user-management::bot_users.sorts.username'),
            apply: fn ($query, $direction) => $query
                ->join('telegram_users', 'bot_users.telegram_user_peer_id', '=', 'telegram_users.peer_id')
                ->orderBy('telegram_users.username', $direction)
                ->select('bot_users.*'),
            display: fn (BotUser $user) => $user->telegramUser->username
                ? '@'.$user->telegramUser->username
                : (mb_substr($user->telegramUser->full_name, 0, 16, 'UTF-8') ?: '?'),
        ));
    }

    /**
     * The three failure states are kept apart rather than lumped into one
     * "cannot reach" entry, because what an admin does about them differs: a
     * block is the user's own choice, unreachable usually means they never
     * started the bot, and deactivated means the account is gone for good.
     */
    private function registerDefaultFilters(): void
    {
        botUserFilters()->addFilter(new BotUserFilter(
            key: 'all',
            label: __('tbe-user-management::bot_users.filters.all'),
        ));

        botUserFilters()->addFilter(new BotUserFilter(
            key: 'reachable',
            label: __('tbe-user-management::bot_users.filters.reachable'),
            apply: fn ($query) => $query->reachable(),
        ));

        botUserFilters()->addFilter(new BotUserFilter(
            key: BotUser::STATUS_BLOCKED,
            label: __('tbe-user-management::bot_users.filters.blocked'),
            apply: fn ($query) => $query->withStatus(BotUser::STATUS_BLOCKED),
        ));

        botUserFilters()->addFilter(new BotUserFilter(
            key: BotUser::STATUS_UNREACHABLE,
            label: __('tbe-user-management::bot_users.filters.unreachable'),
            apply: fn ($query) => $query->withStatus(BotUser::STATUS_UNREACHABLE),
        ));

        botUserFilters()->addFilter(new BotUserFilter(
            key: BotUser::STATUS_DEACTIVATED,
            label: __('tbe-user-management::bot_users.filters.deactivated'),
            apply: fn ($query) => $query->deactivated(),
        ));
    }
}
