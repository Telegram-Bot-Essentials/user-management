<?php

namespace TelegramBotEssentials\UserManagement\Web;

use Filament\Contracts\Plugin;
use Filament\Panel;
use TelegramBotEssentials\UserManagement\Web\Manage\ActivityResource;
use TelegramBotEssentials\UserManagement\Web\Manage\BotUserResource;
use TelegramBotEssentials\UserManagement\Web\Widgets\ActiveMembersChart;
use TelegramBotEssentials\UserManagement\Web\Widgets\MemberStats;
use TelegramBotEssentials\UserManagement\Web\Widgets\ReachabilityChart;

class UserManagementManagePlugin implements Plugin
{
    public function getId(): string
    {
        return 'tbe-user-management-manage';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([BotUserResource::class, ActivityResource::class]);
        $panel->widgets([MemberStats::class, ReachabilityChart::class, ActiveMembersChart::class]);
    }

    public function boot(Panel $panel): void {}
}
