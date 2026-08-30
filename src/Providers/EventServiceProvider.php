<?php

namespace TelegramBotEssentials\UserManagement\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use TelegramBotEssentials\Essence\Events\BotCallbackQueryHandled;
use TelegramBotEssentials\Essence\Events\BotCommandHandled;
use TelegramBotEssentials\Essence\Events\BotDeepLinkReceived;
use TelegramBotEssentials\Essence\Events\BotInlineQueryHandled;
use TelegramBotEssentials\Essence\Events\BotReplyKeyHandled;
use TelegramBotEssentials\Essence\Events\BotStateAnswerHandled;
use TelegramBotEssentials\Essence\Events\BotUpdateReceived;
use TelegramBotEssentials\Essence\Events\BotUserStatusChanged;
use TelegramBotEssentials\UserManagement\Listeners\LogBotInteractions;
use TelegramBotEssentials\UserManagement\Listeners\LogBotUserStatusChanges;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        //        BotCallbackQueryHandled::class => [LogBotInteractions::class],
        //        BotReplyKeyHandled::class => [LogBotInteractions::class],
        //        BotStateAnswerHandled::class => [LogBotInteractions::class],
        //        BotCommandHandled::class => [LogBotInteractions::class],
        //        BotInlineQueryHandled::class => [LogBotInteractions::class],
        //        BotDeepLinkReceived::class => [LogBotInteractions::class],
        BotUpdateReceived::class => [LogBotInteractions::class],
        BotUserStatusChanged::class => [LogBotUserStatusChanges::class],
    ];
}
