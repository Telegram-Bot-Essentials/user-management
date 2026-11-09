<?php

namespace TelegramBotEssentials\UserManagement\Web\Manage;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Models\TelegramUser;
use TelegramBotEssentials\Essence\Web\Filament\PanelResource;
use TelegramBotEssentials\UserManagement\DTOs\BotUserFilter;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

/**
 * The bot's members. Filters and sorts come from the same registries as the
 * bot's list, so what other packages add there (paid totals, referrals...)
 * shows up here too: each sort is a column that orders by it.
 */
class BotUserResource extends PanelResource
{
    protected static ?string $model = BotUser::class;

    protected static ?string $ability = 'user-management.users.manage';

    protected static ?string $slug = 'users';

    protected static ?string $tenantOwnershipRelationshipName = 'bot';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?int $navigationSort = -10;

    public static function getNavigationLabel(): string
    {
        return (string) __('tbe-user-management::web.nav_users');
    }

    public static function getModelLabel(): string
    {
        return (string) __('tbe-user-management::web.user');
    }

    public static function getPluralModelLabel(): string
    {
        return (string) __('tbe-user-management::web.nav_users');
    }

    /** These columns exist on every list; registry sorts with the same key reuse them. */
    private const BUILT_IN = ['last_interaction', 'created_at'];

    public static function table(Table $table): Table
    {
        $columns = [
            TextColumn::make('name')
                ->label((string) __('tbe-user-management::web.user'))
                ->state(fn (BotUser $record): string => self::name($record))
                ->description(fn (BotUser $record): ?string => self::username($record))
                ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('telegramUser', fn (Builder $q): Builder => $q
                    ->where('username', 'like', '%'.ltrim($search, '@').'%')
                    ->orWhere('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('peer_id', $search))),
            TextColumn::make('reachability')
                ->label((string) __('tbe-user-management::web.reachability'))
                ->badge()
                ->state(fn (BotUser $record): string => BotUsersFeature::reachabilityLabel($record))
                ->color(fn (BotUser $record): string => self::reachabilityColor($record)),
            IconColumn::make('suspend')->label((string) __('tbe-user-management::web.suspended'))->boolean()
                ->trueIcon(Heroicon::OutlinedNoSymbol)->falseIcon(Heroicon::OutlinedCheck)
                ->trueColor('danger')->falseColor('gray'),
            TextColumn::make('last_interaction')->label((string) __('tbe-user-management::bot_users.sorts.last_interaction'))->since()->sortable(),
            TextColumn::make('created_at')->label((string) __('tbe-user-management::bot_users.sorts.created_at'))->since()->sortable(),
        ];

        // Each package's sort: its value per member, ordered the way the bot orders it.
        botUserSorts()->getSorts()
            ->reject(fn (BotUserSort $sort): bool => in_array($sort->key, self::BUILT_IN, true))
            ->each(function (BotUserSort $sort) use (&$columns): void {
                $columns[] = TextColumn::make('sort_'.$sort->key)
                    ->label(fn (): string => $sort->label())
                    ->state(fn (BotUser $record): string => $sort->displayValue($record))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $sort->applyTo($query, $direction))
                    ->toggleable(isToggledHiddenByDefault: true);
            });

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('telegramUser'))
            ->columns($columns)
            ->filters([
                SelectFilter::make('segment')
                    ->label((string) __('tbe-user-management::web.segment'))
                    ->options(fn (): array => botUserFilters()->getFilters()
                        ->mapWithKeys(fn (BotUserFilter $filter): array => [$filter->key => $filter->label()])
                        ->all())
                    ->default(fn (): string => botUserFilters()->getDefaultKey())
                    ->selectablePlaceholder(false)
                    ->query(fn (Builder $query, array $data): Builder => botUserFilters()->apply(is_string($data['value'] ?? null) ? $data['value'] : null, $query)),
            ])
            ->defaultSort('last_interaction', 'desc')
            ->recordUrl(fn (BotUser $record): string => ViewBotUser::getUrl(['record' => $record]));
    }

    public static function name(BotUser $user): string
    {
        $telegramUser = $user->telegramUser;
        if (! $telegramUser instanceof TelegramUser) {
            return '?';
        }

        $name = trim((string) $telegramUser->full_name);

        return $name !== '' ? $name : (string) $telegramUser->peer_id;
    }

    public static function username(BotUser $user): ?string
    {
        $telegramUser = $user->telegramUser;

        return $telegramUser instanceof TelegramUser && filled($telegramUser->username) ? '@'.$telegramUser->username : null;
    }

    public static function reachabilityColor(BotUser $user): string
    {
        if ($user->has_private_chat === false) {
            return 'gray';
        }

        return match ($user->reachability()) {
            BotUser::STATUS_ACTIVE => 'success',
            BotUser::STATUS_BLOCKED => 'danger',
            default => 'warning',
        };
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBotUsers::route('/'),
            'view' => ViewBotUser::route('/{record}'),
        ];
    }
}
