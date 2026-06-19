<?php

namespace TelegramBotEssentials\UserManagement;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;
use TelegramBotEssentials\UserManagement\Services\BotUserSorts;
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

        $this->mergeConfigFrom(__DIR__.'/../config/tbe-user-management.php', 'tbe-user-management');

        $this->registerPublishing();

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tbe-user-management');

        callbackQueryBus()->addCallbackQueries([
            BotUsersQuery::class,
        ]);

        stateAnswerBus()->addStateAnswers([
            BotUsersAnswer::class,
        ]);
    }

    public function boot(): void
    {
        $this->registerDefaultSorts();
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
}
