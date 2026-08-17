<?php

namespace TelegramBotEssentials\UserManagement\Telegram\Features\Admin;

use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Services\TelegramPaginator;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;
use TelegramBotEssentials\UserManagement\DTOs\BotUserFilter;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;

class BotUsersFeature
{
    public static string $type = 'BOTUSERS';

    public const NAV_STATE_DEFAULTS = ['sort' => null, 'direction' => null, 'filter' => null, 'lastPage' => 1];

    /**
     * @param  string|null  $filter  overrides the filter held in nav state, which
     *                               is how choosing one from the filter menu takes
     *                               effect. Passed in code rather than through
     *                               callback_data, which has no room left.
     *
     * @throws InvalidPageNumber
     */
    public static function menu(int $page = 1, int $currentPage = 0, ?string $sort = null, ?string $direction = null, ?string $filter = null): TelegramResponse
    {
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);
        $filter = botUserFilters()->resolve(
            $filter ?? navState()->getForCurrentMessage(self::NAV_STATE_DEFAULTS)['filter']
        );

        $text = self::statsText();
        $users = botUserSorts()
            ->apply($sort, botUserFilters()->apply($filter, BotUser::query()->with('telegramUser')), $direction)
            ->paginate(perPage: 10, page: $page);
        TelegramPaginator::validatePageNumber($page, $currentPage, $users);

        $replyMarkup = Keyboard::make()->inline();

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.sort', [
                    'sort' => botUserSorts()->getSort($sort)->label,
                ]),
                'callback_data' => encodeCallback(self::$type, 'sortMenu'),
            ]),
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.filter', [
                    'filter' => botUserFilters()->getFilter($filter)?->label ?? '?',
                ]),
                'callback_data' => encodeCallback(self::$type, 'filterMenu'),
            ]),
        ]);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.allActionsHistory'),
                'callback_data' => encodeCallback(self::$type, 'allActionsHistory', [1, 0, $page, $sort, $direction]),
            ]),
        ]);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.user_column_header'),
                'callback_data' => encodeCallback(self::$type, 'menu', [$page, 0, $sort, $direction]),
            ]),
            Keyboard::inlineButton([
                'text' => botUserSorts()->getSort($sort)->label.' '.botUserSorts()->directionIndicator($direction),
                'callback_data' => encodeCallback(self::$type, 'toggleDirection'),
            ]),
        ]);

        $users->each(function (BotUser $user) use ($replyMarkup, $sort) {
            $callback = encodeCallback(self::$type, 'show', [$user->id]);
            if ($user->telegramUser->username) {
                $name = '@'.$user->telegramUser->username;
            } else {
                $name = mb_substr($user->telegramUser->full_name, 0, 16, 'UTF-8');
                $name = empty($name) ? '?' : $name;
            }
            $replyMarkup->row([
                Keyboard::inlineButton([
                    'text' => mb_trim(self::reachabilityEmoji($user).($user->suspend ? __('tbe::general.status.disabledEmoji') : '').' '.$name),
                    'callback_data' => $callback,
                ]),
                Keyboard::inlineButton([
                    'text' => botUserSorts()->getSort($sort)->displayValue($user),
                    'callback_data' => $callback,
                ]),
            ]);
        });

        $replyMarkup->row(TelegramPaginator::makeNavigationButtonsRow(self::$type, $page, $users->lastPage(), 'menu', customPageMethod: 'setMenuPage', extraParams: [$sort, $direction]));

        return (new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup
        ))->navState([
            'sort' => $sort,
            'direction' => $direction,
            'filter' => $filter,
            'lastPage' => $page,
        ]);
    }

    /**
     * The header of the user list: the state of the user base, followed by
     * whatever other packages have registered about it.
     */
    private static function statsText(): string
    {
        $counts = self::userCounts();

        $text = self::markLtr(__('tbe-user-management::bot_users.main.text.index', [
            'total' => number_format($counts->total),
            'suspended' => number_format($counts->suspended),
            'admins' => number_format($counts->admins),
            'moderators' => number_format($counts->moderators),
            'joinedDay' => number_format($counts->joined_day),
            'joinedWeek' => number_format($counts->joined_week),
            'joinedMonth' => number_format($counts->joined_month),
            'reachable' => number_format($counts->reachable),
            'blocked' => number_format($counts->blocked),
            'unreachable' => number_format($counts->unreachable),
            'deactivated' => number_format($counts->deactivated),
            'activeDay' => number_format($counts->active_day),
            'activeWeek' => number_format($counts->active_week),
            'activeMonth' => number_format($counts->active_month),
        ]));

        userManagementStats()->render()->each(function (string $block) use (&$text) {
            $text .= "\r\n\r\n".self::markLtr($block);
        });

        return $text;
    }

    /**
     * Every number in the header in one pass over bot_users. The table is
     * scanned whole either way, so conditional sums cost no more than the
     * single COUNT this replaced.
     *
     * telegram_users is joined rather than counted separately because
     * deactivation lives there and outranks the per-bot status column, which
     * keeps the four reachability numbers mutually exclusive.
     */
    private static function userCounts(): object
    {
        $day = now()->subDay();
        $week = now()->subWeek();
        $month = now()->subMonth();

        return BotUser::query()
            ->leftJoin('telegram_users', 'bot_users.telegram_user_peer_id', '=', 'telegram_users.peer_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN bot_users.suspended_at IS NOT NULL THEN 1 ELSE 0 END) as suspended')
            ->selectRaw('SUM(CASE WHEN bot_users.power = ? THEN 1 ELSE 0 END) as admins', [Roles::ADMIN->value])
            ->selectRaw('SUM(CASE WHEN bot_users.power = ? THEN 1 ELSE 0 END) as moderators', [Roles::MODERATOR->value])
            ->selectRaw('SUM(CASE WHEN bot_users.created_at > ? THEN 1 ELSE 0 END) as joined_day', [$day])
            ->selectRaw('SUM(CASE WHEN bot_users.created_at > ? THEN 1 ELSE 0 END) as joined_week', [$week])
            ->selectRaw('SUM(CASE WHEN bot_users.created_at > ? THEN 1 ELSE 0 END) as joined_month', [$month])
            ->selectRaw('SUM(CASE WHEN telegram_users.deactivated_at IS NULL AND bot_users.status = ? THEN 1 ELSE 0 END) as reachable', [BotUser::STATUS_ACTIVE])
            ->selectRaw('SUM(CASE WHEN telegram_users.deactivated_at IS NULL AND bot_users.status = ? THEN 1 ELSE 0 END) as blocked', [BotUser::STATUS_BLOCKED])
            ->selectRaw('SUM(CASE WHEN telegram_users.deactivated_at IS NULL AND bot_users.status = ? THEN 1 ELSE 0 END) as unreachable', [BotUser::STATUS_UNREACHABLE])
            ->selectRaw('SUM(CASE WHEN telegram_users.deactivated_at IS NOT NULL THEN 1 ELSE 0 END) as deactivated')
            ->selectRaw('SUM(CASE WHEN bot_users.last_interaction > ? THEN 1 ELSE 0 END) as active_day', [$day])
            ->selectRaw('SUM(CASE WHEN bot_users.last_interaction > ? THEN 1 ELSE 0 END) as active_week', [$week])
            ->selectRaw('SUM(CASE WHEN bot_users.last_interaction > ? THEN 1 ELSE 0 END) as active_month', [$month])
            ->toBase()
            ->first();
    }

    /**
     * Telegram lays the header out right to left, which reorders a line that
     * mixes Persian labels with digit groups and separators. A left-to-right
     * mark at the start of each line pins it down.
     */
    private static function markLtr(string $text): string
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(function (string $line) {
                // Not ltrim(): its character list is bytes, and the mark shares
                // its leading byte with emoji such as ⚡️, which it would eat.
                if ($line === '' || str_starts_with($line, "\u{200E}")) {
                    return $line;
                }

                return "\u{200E}".$line;
            })
            ->implode("\r\n");
    }

    /**
     * Marks only users we cannot reach, so the ordinary case stays unadorned
     * and a dead user is spottable while browsing with no filter applied.
     */
    public static function reachabilityEmoji(BotUser $botUser): string
    {
        return match ($botUser->reachability()) {
            BotUser::STATUS_BLOCKED => '🚫',
            BotUser::STATUS_UNREACHABLE => '❓',
            BotUser::STATUS_DEACTIVATED => '⚰️',
            default => '',
        };
    }

    public static function reachabilityLabel(BotUser $botUser): string
    {
        return __('tbe-user-management::bot_users.reachability.'.$botUser->reachability());
    }

    public static function filterMenu(): TelegramResponse
    {
        $navState = navState()->getForCurrentMessage(self::NAV_STATE_DEFAULTS);
        $currentFilter = botUserFilters()->resolve($navState['filter']);
        $sort = botUserSorts()->resolve($navState['sort']);
        $direction = botUserSorts()->resolveDirection($navState['direction']);
        $lastPage = $navState['lastPage'];

        $replyMarkup = Keyboard::make()->inline();

        botUserFilters()->getFilters()->each(function (BotUserFilter $filter) use ($replyMarkup, $currentFilter) {
            $replyMarkup->row([
                Keyboard::inlineButton([
                    'text' => ($filter->key === $currentFilter ? '✅ ' : '').$filter->label,
                    'callback_data' => encodeCallback(self::$type, 'filter', [$filter->key]),
                ]),
            ]);
        });

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe::general.keys.back'),
                'callback_data' => encodeCallback(self::$type, 'menu', [$lastPage, 0, $sort, $direction]),
            ]),
        ]);

        return new TelegramResponse(
            text: __('tbe-user-management::bot_users.main.text.filter_menu'),
            replyMarkup: $replyMarkup,
        );
    }

    public static function sortMenu(): TelegramResponse
    {
        $navState = navState()->getForCurrentMessage(self::NAV_STATE_DEFAULTS);
        $currentSort = botUserSorts()->resolve($navState['sort']);
        $direction = botUserSorts()->resolveDirection($navState['direction']);
        $lastPage = $navState['lastPage'];

        $replyMarkup = Keyboard::make()->inline();

        botUserSorts()->getSorts()->each(function (BotUserSort $sort) use ($replyMarkup, $currentSort, $direction) {
            $replyMarkup->row([
                Keyboard::inlineButton([
                    'text' => ($sort->key === $currentSort ? '✅ ' : '').$sort->label,
                    'callback_data' => encodeCallback(self::$type, 'menu', [1, 0, $sort->key, $direction]),
                ]),
            ]);
        });

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe::general.keys.back'),
                'callback_data' => encodeCallback(self::$type, 'menu', [$lastPage, 0, $currentSort, $direction]),
            ]),
        ]);

        return new TelegramResponse(
            text: __('tbe-user-management::bot_users.main.text.sort_menu'),
            replyMarkup: $replyMarkup,
        );
    }

    public static function show(BotUser $botUser): TelegramResponse
    {
        $navState = navState()->getForCurrentMessage(self::NAV_STATE_DEFAULTS);
        $sort = botUserSorts()->resolve($navState['sort']);
        $direction = botUserSorts()->resolveDirection($navState['direction']);
        $lastPage = $navState['lastPage'];

        $text = __('tbe-user-management::bot_users.main.text.show_user', [
            'userFullName' => "<a href=\"tg://user?id={$botUser->telegramUser->peer_id}\">{$botUser->telegramUser->full_name}</a>",
            'userPeerId' => $botUser->telegramUser->peer_id,
            'userUsername' => $botUser->telegramUser->username ? '@'.$botUser->telegramUser->username : '',
            'userFirstName' => $botUser->telegramUser->first_name,
            'userLastName' => $botUser->telegramUser->last_name,
            'userTel' => $botUser->telegramUser->tel,
            'userRole' => $botUser->role,
            'userSuspendStatus' => $botUser->suspend ?
                __('tbe::general.status.suspended', [
                    'suspendedDate' => $botUser->suspended_at?->format('Y-m-d H:i:s'),
                ]) :
                __('tbe::general.status.notSuspended'),
            'userReachability' => mb_trim(self::reachabilityEmoji($botUser).' '.self::reachabilityLabel($botUser)),
            'userCreatedAt' => $botUser->created_at,
            'userUpdatedAt' => $botUser->updated_at,
            'dataReceiveTime' => now()->format('Y-m-d H:i:s'),
        ]);
        $replyMarkup = Keyboard::make()->inline();

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => $botUser->suspend ? __('tbe-user-management::bot_users.main.keys.userIsSuspended') : __('tbe-user-management::bot_users.main.keys.userIsActive'),
                'callback_data' => encodeCallback(self::$type, 'suspend', [$botUser->id, intval(! $botUser->suspend)]),
            ]),
        ]);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userRole', ['role' => $botUser->role]),
                'callback_data' => encodeCallback(self::$type, 'role', [$botUser->id]),
            ]),
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userUpdateData'),
                'callback_data' => encodeCallback(self::$type, 'show', [$botUser->id]),
            ]),
        ]);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userActionsHistory'),
                'callback_data' => encodeCallback(self::$type, 'userActionsHistory', [$botUser->id, 1, 0]),
            ]),
        ]);

        $text .= self::renderSections($botUser, $replyMarkup);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe::general.keys.back'),
                'callback_data' => encodeCallback(self::$type, 'menu', [$lastPage, 0, $sort, $direction]),
            ]),
        ]);

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    private static function renderSections(BotUser $botUser, Keyboard $replyMarkup): string
    {
        $extraText = '';

        userManagementSections()->getSectionsFor($botUser)->each(function ($section) use ($botUser, $replyMarkup, &$extraText) {
            if ($section->mode === SectionMode::BUTTON) {
                $replyMarkup->row([
                    Keyboard::inlineButton([
                        'text' => $section->labelFor($botUser),
                        'callback_data' => $section->targetFor($botUser),
                    ]),
                ]);

                return;
            }

            $content = $section->contentFor($botUser);

            if (! empty($content['text'])) {
                $extraText .= "\r\n\r\n".$content['text'];
            }

            foreach ($content['rows'] ?? [] as $row) {
                $replyMarkup->row($row);
            }
        });

        return $extraText;
    }

    /**
     * @throws InvalidPageNumber
     */
    public static function userActionsHistory(BotUser $user, int $page = 1, int $currentPage = 0): TelegramResponse
    {
        $userName = htmlspecialchars(
            $user->telegramUser->username
                ? '@'.$user->telegramUser->username
                : ($user->telegramUser->full_name ?: '?'),
            ENT_QUOTES,
            'UTF-8',
        );

        $replyMarkup = Keyboard::make()->inline();

        $botUserActions = BotUserAction::query()
            ->where('bot_user_id', $user->id)
            ->latest()
            ->paginate(10, page: $page);

        TelegramPaginator::validatePageNumber($page, $currentPage, $botUserActions);

        if ($botUserActions->isEmpty()) {
            $text = __('tbe-user-management::bot_users.main.text.user_actions_history_empty', [
                'userName' => $userName,
            ]);
        } else {
            $text = __('tbe-user-management::bot_users.main.text.user_actions_history_header', [
                'userName' => $userName,
                'page' => $page,
                'totalPages' => $botUserActions->lastPage(),
            ])."\r\n\r\n";

            $text .= self::formatGroupedActions($botUserActions->getCollection());
        }

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userUpdateData'),
                'callback_data' => encodeCallback(self::$type, 'userActionsHistory', [$user->id, 1, 0]),
            ]),
        ]);
        $replyMarkup->row(TelegramPaginator::makeNavigationButtonsRow(
            self::$type,
            $page,
            max(1, $botUserActions->lastPage()),
            'actionsPage',
            customPageMethod: 'actionsSetPage',
            extraParams: [$user->id],
        ));

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe::general.keys.back'),
                'callback_data' => encodeCallback(self::$type, 'show', [$user->id]),
            ]),
        ]);

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    /**
     * @throws InvalidPageNumber
     */
    public static function allActionsHistory(int $page = 1, int $currentPage = 0, int $lastPage = 1, ?string $sort = null, ?string $direction = null): TelegramResponse
    {
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);
        $replyMarkup = Keyboard::make()->inline();

        $botUserActions = BotUserAction::query()
            ->with('botUser.telegramUser')
            ->latest()
            ->paginate(10, page: $page);

        TelegramPaginator::validatePageNumber($page, $currentPage, $botUserActions);

        if ($botUserActions->isEmpty()) {
            $text = __('tbe-user-management::bot_users.main.text.all_actions_history_empty');
        } else {
            $text = __('tbe-user-management::bot_users.main.text.all_actions_history_header', [
                'page' => $page,
                'totalPages' => $botUserActions->lastPage(),
            ])."\r\n\r\n";

            $text .= self::formatGroupedActions($botUserActions->getCollection(), includeUser: true);
        }

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userUpdateData'),
                'callback_data' => encodeCallback(self::$type, 'allActionsHistory', [1, 0, $lastPage, $sort, $direction]),
            ]),
        ]);
        $replyMarkup->row(TelegramPaginator::makeNavigationButtonsRow(
            self::$type,
            $page,
            max(1, $botUserActions->lastPage()),
            'allActionsPage',
            customPageMethod: 'allActionsSetPage',
            extraParams: [$lastPage, $sort, $direction],
        ));

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe::general.keys.back'),
                'callback_data' => encodeCallback(self::$type, 'menu', [$lastPage, 0, $sort, $direction]),
            ]),
        ]);

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup,
            parseMode: 'HTML'
        );
    }

    private static function formatGroupedActions($actions, bool $includeUser = false): string
    {
        $text = '';

        $actions
            ->groupBy(fn (BotUserAction $botUserAction) => $botUserAction->created_at->format('Y-m-d'))
            ->each(function ($groupedActions, string $date) use (&$text, $includeUser) {
                $text .= "\u{200E}".__('tbe-user-management::bot_users.main.text.user_actions_history_date', [
                    'date' => $date,
                ])."\r\n";

                $groupedActions->each(function (BotUserAction $botUserAction) use (&$text, $includeUser) {
                    $text .= self::formatActionLine($botUserAction, $includeUser);
                });

                $text .= "\r\n";
            });

        return $text;
    }

    private static function formatActionLine(BotUserAction $botUserAction, bool $includeUser = false): string
    {
        $userStateText = $botUserAction->state
            ? ' | <u>'.htmlspecialchars($botUserAction->state, ENT_QUOTES, 'UTF-8').'</u>'
            : '';
        $action = htmlspecialchars($botUserAction->action, ENT_QUOTES, 'UTF-8');
        $userPrefix = '';

        if ($includeUser) {
            $userName = htmlspecialchars(
                $botUserAction->botUser->telegramUser->username
                    ? '@'.$botUserAction->botUser->telegramUser->username
                    : ($botUserAction->botUser->telegramUser->full_name ?: '?'),
                ENT_QUOTES,
                'UTF-8',
            );
            $userPrefix = " | {$userName}";
        }

        return "\u{200E}<i>[{$botUserAction->created_at->format('H:i:s')}{$userPrefix} | {$botUserAction->update_type}{$userStateText}]</i>:\r\n{$action}\r\n";
    }
}
