<?php

namespace TelegramBotEssentials\UserManagement\Web\Widgets;

use Filament\Widgets\ChartWidget;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsWidget;
use TelegramBotEssentials\Essence\Web\Filament\DailySeries;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;

/** How many members used the bot each day, from their action history. */
class ActiveMembersChart extends ChartWidget
{
    use GuardsWidget;

    protected static ?string $ability = 'user-management.users.manage';

    protected static ?int $sort = 21;

    protected ?string $maxHeight = '260px';

    public ?string $filter = '30d';

    public function getHeading(): string
    {
        return (string) __('tbe-user-management::web.stats.actions_per_day');
    }

    /** @return array<string, string> */
    protected function getFilters(): array
    {
        return DailySeries::ranges();
    }

    /** @return array<string, mixed> */
    protected function getData(): array
    {
        $series = DailySeries::of(BotUserAction::query(), 'created_at', DailySeries::days($this->filter));

        return [
            'datasets' => [[
                'label' => (string) __('tbe-user-management::web.stats.actions_per_day'),
                'data' => array_values($series),
            ]],
            'labels' => DailySeries::labels($series),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
