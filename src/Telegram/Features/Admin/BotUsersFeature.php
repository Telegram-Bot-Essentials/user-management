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
    public static function menu(int $page = 1, int $currentPage = 0): TelegramResponse
    {
        $text = __('tbe-user-management::bot_users.main.text.index', [
            'userCount' => BotUser::count(),
            'usersJoinedLastDay' => BotUser::where('created_at', '>', now()->subDays(1))->count(),
        ]);
        $users = BotUser::with('telegramUser')->paginate(perPage: 10, page: $page);
        TelegramPaginator::validatePageNumber($page, $currentPage, $users);

        $replyMarkup = Keyboard::make()->inline();
        $users->each(function (BotUser $user) use ($replyMarkup) {
            $callback = encodeCallback(self::$type, 'show', [$user->id]);
            if ($user->telegramUser->username) {
                $name = '@' . $user->telegramUser->username;
            } else {
                $name = mb_substr($user->telegramUser->full_name, 0, 16, 'UTF-8');
                $name = empty($name) ? '?' : $name;
            }
            $replyMarkup->row([
                Keyboard::inlineButton([
                    'text' => mb_trim(($user->suspend ? __('tbe::general.status.disabledEmoji') : '') . ' ' . $name),
                    'callback_data' => $callback,
                ]),
                Keyboard::inlineButton([
                    'text' => $user->last_interaction->shortRelativeToNowDiffForHumans(),
                    'callback_data' => $callback,
                ]),
            ]);
        });

        $replyMarkup->row(TelegramPaginator::makeNavigationButtonsRow(self::$type, $page, $users->lastPage(), 'menu', 'setMenuPage'));

        return new TelegramResponse(
            text: $text,
            replyMarkup: $replyMarkup
        );
    }
}
