<?php

namespace TelegramBotEssentials\UserManagement\Web\Widgets;

use Filament\Widgets\ChartWidget;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsWidget;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

/** Whom a message would reach: the four reachability states, which never overlap. */
class ReachabilityChart extends ChartWidget
{
    use GuardsWidget;

    protected static ?string $ability = 'user-management.users.manage';

    protected static ?int $sort = 20;

    protected ?string $maxHeight = '260px';

    private const STATES = [
        'reachable' => ['active', '#22c55e'],
        'group_only' => ['group_only', '#a1a1aa'],
        'blocked' => ['blocked', '#ef4444'],
        'unreachable' => ['unreachable', '#f59e0b'],
        'deactivated' => ['deactivated', '#52525b'],
    ];

    public function getHeading(): string
    {
        return (string) __('tbe-user-management::web.stats.reachability');
    }

    /** @return array<string, mixed> */
    protected function getData(): array
    {
        $counts = BotUsersFeature::userCounts();
        $values = [];
        $labels = [];
        $colors = [];

        foreach (self::STATES as $column => [$label, $color]) {
            $value = $counts->{$column} ?? 0;
            $values[] = is_numeric($value) ? (int) $value : 0;
            $labels[] = (string) __('tbe-user-management::bot_users.reachability.'.$label);
            $colors[] = $color;
        }

        return [
            'datasets' => [['data' => $values, 'backgroundColor' => $colors, 'borderWidth' => 0]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /** @return array<string, mixed> */
    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['position' => 'right']], 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }
}
