<?php

use TelegramBotEssentials\UserManagement\Services\BotUserSorts;
use TelegramBotEssentials\UserManagement\Services\UserManagementSections;

if (! function_exists('botUserSorts')) {
    function botUserSorts(): BotUserSorts
    {
        return app(BotUserSorts::class);
    }
}

if (! function_exists('userManagementSections')) {
    function userManagementSections(): UserManagementSections
    {
        return app(UserManagementSections::class);
    }
}
