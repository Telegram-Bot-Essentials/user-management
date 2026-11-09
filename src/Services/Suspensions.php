<?php

namespace TelegramBotEssentials\UserManagement\Services;

use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Models\TelegramUser;

/** Suspending a member, from the bot or the web panel, with one audit line either way. */
class Suspensions
{
    public function set(BotUser $botUser, bool $suspend): void
    {
        $botUser->suspend = $suspend;
        $botUser->save();

        tbeLog('user-management')->audit(($suspend ? 'Suspended ' : 'Unsuspended ').self::describe($botUser), [
            'target_bot_user_id' => $botUser->getKey(),
            'target_user_id' => $botUser->getAttribute('telegram_user_peer_id'),
        ]);
    }

    /** "user#12345 @alice", the target of an admin action as it reads in the audit log. */
    public static function describe(BotUser $botUser): string
    {
        $peerId = $botUser->getAttribute('telegram_user_peer_id');
        $telegramUser = $botUser->telegramUser;
        $username = $telegramUser instanceof TelegramUser ? $telegramUser->username : null;

        return 'user#'.(is_scalar($peerId) ? $peerId : '?').($username ? ' @'.$username : '');
    }
}
