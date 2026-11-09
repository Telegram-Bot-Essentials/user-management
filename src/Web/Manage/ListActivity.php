<?php

namespace TelegramBotEssentials\UserManagement\Web\Manage;

use Filament\Resources\Pages\ListRecords;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsResourcePage;

class ListActivity extends ListRecords
{
    use GuardsResourcePage;

    protected static string $resource = ActivityResource::class;
}
