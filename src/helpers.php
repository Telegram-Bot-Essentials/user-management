<?php

use TelegramBotEssentials\UserManagement\Services\BotUserFilters;
use TelegramBotEssentials\UserManagement\Services\BotUserSorts;
use TelegramBotEssentials\UserManagement\Services\UserManagementSections;
use TelegramBotEssentials\UserManagement\Services\UserManagementStats;

if (! function_exists('botUserSorts')) {
    function botUserSorts(): BotUserSorts
    {
        return app(BotUserSorts::class);
    }
}

if (! function_exists('botUserFilters')) {
    function botUserFilters(): BotUserFilters
    {
        return app(BotUserFilters::class);
    }
}

if (! function_exists('userManagementSections')) {
    function userManagementSections(): UserManagementSections
    {
        return app(UserManagementSections::class);
    }
}

if (! function_exists('userManagementStats')) {
    function userManagementStats(): UserManagementStats
    {
        return app(UserManagementStats::class);
    }
}
