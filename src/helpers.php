<?php

use TelegramBotEssentials\UserManagement\Services\BotUserSorts;

if (! function_exists('botUserSorts')) {
    function botUserSorts(): BotUserSorts
    {
        return app(BotUserSorts::class);
    }
}
