<?php

namespace TelegramBotEssentials\UserManagement\Web\Manage;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\Filament\PanelDates;
use TelegramBotEssentials\Essence\Web\Filament\PanelGroup;
use TelegramBotEssentials\Essence\Web\Filament\PanelMembers;
use TelegramBotEssentials\Essence\Web\Filament\PanelResource;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;
use UnitEnum;

/** Everything members did in the bot, newest first: the bot's "all actions" history. */
class ActivityResource extends PanelResource
{
    protected static ?string $model = BotUserAction::class;

    protected static ?string $ability = 'user-management.users.manage';

    protected static ?string $slug = 'activity';

    protected static ?string $tenantOwnershipRelationshipName = 'bot';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = PanelGroup::Members;

    public static function getNavigationLabel(): string
    {
        return (string) __('tbe-user-management::web.nav_activity');
    }

    public static function getModelLabel(): string
    {
        return (string) __('tbe-user-management::web.action');
    }

    public static function getPluralModelLabel(): string
    {
        return (string) __('tbe-user-management::web.nav_activity');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('botUser.telegramUser'))
            ->columns([
                PanelMembers::column('botUser', (string) __('tbe-user-management::web.user'))
                    ->url(fn (BotUserAction $record): ?string => $record->botUser instanceof BotUser ? ViewBotUser::getUrl(['record' => $record->botUser]) : null),
                ...self::actionColumns(),
            ])
            ->defaultSort('id', 'desc')
            ->persistSearchInSession()
            ->emptyStateIcon(Heroicon::OutlinedClock)
            ->emptyStateHeading((string) __('tbe-user-management::web.empty.activity'));
    }

    /** @return list<TextColumn> */
    public static function actionColumns(): array
    {
        return [
            TextColumn::make('action')->label((string) __('tbe-user-management::web.action'))->wrap()->limit(200)->searchable(),
            TextColumn::make('update_type')->label((string) __('tbe-user-management::web.update_type'))->badge()->color('gray'),
            TextColumn::make('state')->label((string) __('tbe-user-management::web.state'))->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            PanelDates::dateTime(TextColumn::make('created_at'))->label((string) __('tbe-user-management::web.when'))->sortable(),
        ];
    }

    public static function getPages(): array
    {
        return ['index' => ListActivity::route('/')];
    }
}
