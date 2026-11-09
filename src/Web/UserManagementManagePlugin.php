<?php

namespace TelegramBotEssentials\UserManagement\Web;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TelegramBotEssentials\UserManagement\Web\Manage\ActivityResource;
use TelegramBotEssentials\UserManagement\Web\Manage\BotUserResource;

class UserManagementManagePlugin implements Plugin
{
    public function getId(): string
    {
        return 'tbe-user-management-manage';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([BotUserResource::class, ActivityResource::class]);
    }

    public function boot(Panel $panel): void {}
}
