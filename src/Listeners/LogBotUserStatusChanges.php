<?php

namespace TelegramBotEssentials\UserManagement\Listeners;

use TelegramBotEssentials\Essence\Events\BotUserStatusChanged;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;

/**
 * Records reachability changes in the user's action history.
 *
 * This replaces the my_chat_member branch in LogBotInteractions, which only
 * saw the half of these that arrive as webhook updates: a block discovered
 * mid-broadcast has no update behind it at all.
 */
class LogBotUserStatusChanges
{
    public function handle(BotUserStatusChanged $event): void
    {
        try {
            BotUserAction::create([
                'bot_id' => $event->botUser->bot_id,
                'bot_user_id' => $event->botUser->getKey(),
                'update_type' => 'bot_user_status',
                'state' => $event->source,
                'action' => $event->from.' -> '.$event->to,
            ]);
        } catch (\Exception $exception) {
            report($exception);
        }
    }
}
