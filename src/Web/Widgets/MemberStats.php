<?php

namespace TelegramBotEssentials\UserManagement\Web\Widgets;

use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsWidget;
use TelegramBotEssentials\Essence\Web\Filament\DailySeries;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

/** The user base at a glance: the numbers the bot's list header prints, as cards. */
class MemberStats extends StatsOverviewWidget
{
    use GuardsWidget;

    protected static ?string $ability = 'user-management.users.manage';

    protected static ?int $sort = 0;

    // One query, drawn with the page; the charts load after it.
    protected static bool $isLazy = false;

    /** @return array<Stat> */
    protected function getStats(): array
    {
        $counts = BotUsersFeature::userCounts();
        $int = fn (string $key): int => is_numeric($counts->{$key} ?? null) ? (int) $counts->{$key} : 0;
        $total = $int('total');
        $percent = fn (int $part): string => $total > 0 ? round($part * 100 / $total).'%' : '0%';

        $joined = DailySeries::of(BotUser::query(), 'created_at', 30);

        return [
            Stat::make((string) __('tbe-user-management::web.stats.members'), number_format($total))
                ->description((string) __('tbe-user-management::web.stats.joined', ['day' => number_format($int('joined_day')), 'month' => number_format($int('joined_month'))]))
                ->descriptionIcon(Heroicon::ArrowTrendingUp)
                ->chart(array_values($joined))
                ->color('primary'),
            Stat::make((string) __('tbe-user-management::web.stats.active_week'), number_format($int('active_week')))
                ->description((string) __('tbe-user-management::web.stats.active_detail', ['day' => number_format($int('active_day')), 'month' => number_format($int('active_month'))])),
            Stat::make((string) __('tbe-user-management::web.stats.reachable'), $percent($int('reachable')))
                ->description((string) __('tbe-user-management::web.stats.of_members', ['count' => number_format($int('reachable'))]))
                ->color('success'),
            Stat::make((string) __('tbe-user-management::web.stats.blocked'), number_format($int('blocked')))
                ->description($percent($int('blocked')))
                ->color($int('blocked') > 0 ? 'danger' : 'gray'),
            Stat::make((string) __('tbe-user-management::web.suspended'), number_format($int('suspended')))
                ->color('gray'),
        ];
    }
}
