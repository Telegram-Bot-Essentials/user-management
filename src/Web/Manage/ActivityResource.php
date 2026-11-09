<?php

namespace TelegramBotEssentials\UserManagement\Web\Manage;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\Filament\PanelResource;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;

/** Everything members did in the bot, newest first: the bot's "all actions" history. */
class ActivityResource extends PanelResource
{
    protected static ?string $model = BotUserAction::class;

    protected static ?string $ability = 'user-management.users.manage';

    protected static ?string $slug = 'activity';

    protected static ?string $tenantOwnershipRelationshipName = 'bot';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

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
                TextColumn::make('member')
                    ->label((string) __('tbe-user-management::web.user'))
                    ->state(fn (BotUserAction $record): string => $record->botUser instanceof BotUser ? BotUserResource::name($record->botUser) : '?')
                    ->url(fn (BotUserAction $record): ?string => $record->botUser instanceof BotUser ? ViewBotUser::getUrl(['record' => $record->botUser]) : null),
                ...self::actionColumns(),
            ])
            ->defaultSort('id', 'desc');
    }

    /** @return list<TextColumn> */
    public static function actionColumns(): array
    {
        return [
            TextColumn::make('action')->label((string) __('tbe-user-management::web.action'))->wrap()->limit(200)->searchable(),
            TextColumn::make('update_type')->label((string) __('tbe-user-management::web.update_type'))->badge()->color('gray'),
            TextColumn::make('state')->label((string) __('tbe-user-management::web.state'))->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')->label((string) __('tbe-user-management::web.when'))->dateTime()->sortable(),
        ];
    }

    public static function getPages(): array
    {
        return ['index' => ListActivity::route('/')];
    }
}
