<?php

namespace TelegramBotEssentials\UserManagement\Telegram\Features\Admin;

use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Services\TelegramPaginator;
use TelegramBotEssentials\Essence\Telegram\TelegramResponse;

class BotUsersFeature
{
    public static string $type = 'BOTUSERS';

    /**
     * @throws InvalidPageNumber
     */
    public static function menu(int $page = 1, int $currentPage = 0, ?string $sort = null, ?string $direction = null): TelegramResponse
    {
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);

        $text = __('tbe-user-management::bot_users.main.text.index', [
            'userCount' => BotUser::count(),
            'usersJoinedLastDay' => BotUser::where('created_at', '>', now()->subDays(1))->count(),
        ]);
        $users = botUserSorts()
            ->apply($sort, BotUser::query()->with('telegramUser'), $direction)
            ->paginate(perPage: 10, page: $page);
        TelegramPaginator::validatePageNumber($page, $currentPage, $users);

        $replyMarkup = Keyboard::make()->inline();

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.sort', [
                    'sort' => botUserSorts()->getSort($sort)->label,
                ]),
                'callback_data' => encodeCallback(self::$type, 'sort', [$sort, $direction]),
            ]),
        ]);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => 'User',
                'callback_data' => encodeCallback(self::$type, 'sort', [$sort, $direction]),
            ]),
            Keyboard::inlineButton([
                'text' => botUserSorts()->getSort($sort)->label.' '.botUserSorts()->directionIndicator($direction),
                'callback_data' => encodeCallback(self::$type, 'toggleDirection', [$sort, $direction, $page]),
            ]),
        ]);

        $users->each(function (BotUser $user) use ($replyMarkup, $page, $sort, $direction) {
            $callback = encodeCallback(self::$type, 'show', [$user->id, $page, $sort, $direction]);
            if ($user->telegramUser->username) {
                $name = '@'.$user->telegramUser->username;
            } else {
                $name = mb_substr($user->telegramUser->full_name, 0, 16, 'UTF-8');
                $name = empty($name) ? '?' : $name;
            }
            $replyMarkup->row([
                Keyboard::inlineButton([
                    'text' => mb_trim(($user->suspend ? __('tbe::general.status.disabledEmoji') : '').' '.$name),
                    'callback_data' => $callback,
                ]),
                Keyboard::inlineButton([
                    'text' => botUserSorts()->getSort($sort)->displayValue($user),
                    'callback_data' => $callback,
                ]),
            ]);
        });

        $replyMarkup->row(self::makeNavigationButtonsRow($page, $users->lastPage(), $sort, $direction));

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup
        );
    }

    public static function show(BotUser $botUser, int $lastPage = 1, ?string $sort = null, ?string $direction = null): TelegramResponse
    {
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);

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
            'userCreatedAt' => $botUser->created_at,
            'userUpdatedAt' => $botUser->updated_at,
            'dataReceiveTime' => now()->format('Y-m-d H:i:s'),
        ]);
        $replyMarkup = Keyboard::make()->inline();

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => $botUser->suspend ? __('tbe-user-management::bot_users.main.keys.userIsSuspended') : __('tbe-user-management::bot_users.main.keys.userIsActive'),
                'callback_data' => encodeCallback(self::$type, 'suspend', [$botUser->id, intval(! $botUser->suspend), $lastPage, $sort, $direction]),
            ]),
        ]);

        $replyMarkup->row([
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userRole', ['role' => $botUser->role]),
                'callback_data' => encodeCallback(self::$type, 'role', [$botUser->id, $lastPage, $sort, $direction]),
            ]),
            Keyboard::inlineButton([
                'text' => __('tbe-user-management::bot_users.main.keys.userUpdateData'),
                'callback_data' => encodeCallback(self::$type, 'show', [$botUser->id, $lastPage, $sort, $direction]),
            ]),
        ]);

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

    private static function makeNavigationButtonsRow(int $page, int $lastPage, string $sort, string $direction): array
    {
        return [
            Keyboard::inlineButton(['text' => '<<', 'callback_data' => encodeCallback(self::$type, 'menu', [1, $page, $sort, $direction])]),
            Keyboard::inlineButton(['text' => '<', 'callback_data' => encodeCallback(self::$type, 'menu', [$page - 1, $page, $sort, $direction])]),
            Keyboard::inlineButton(['text' => "$page/{$lastPage}", 'callback_data' => encodeCallback(self::$type, 'setMenuPage', [$sort, $direction])]),
            Keyboard::inlineButton(['text' => '>', 'callback_data' => encodeCallback(self::$type, 'menu', [$page + 1, $page, $sort, $direction])]),
            Keyboard::inlineButton(['text' => '>>', 'callback_data' => encodeCallback(self::$type, 'menu', [$lastPage, $page, $sort, $direction])]),
        ];
    }
}
