<?php

namespace TelegramBotEssentials\UserManagement\Listeners;

use App\Enums\PanelType;
use App\Panels\Admin\Marzban\MarzbanPanelAdminExtension;
use App\Panels\Admin\Rebecca\RebeccaPanelAdminExtension;
use TelegramBotEssentials\Essence\Events\BotUpdateReceived;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;

class LogBotInteractions
{
    private BotUpdateReceived $event;

    public function handle(BotUpdateReceived $event): void
    {
        try {
            $this->event = $event;
            if (!$event->context->botUserId) {
                return;
            }

            match ($event->updateType) {
                'message' => $this->processMessageUpdate(),
                'callback_query' => $this->processCallbackQueryUpdate(),
                default => mixedDebugMessage($event->updateType),
            };
        } catch (\Exception $exception) {
            report($exception);
        }
    }

    private function processMessageUpdate(): void
    {
        if (is_null(wHook()->user()->state)) {
            $userState = null;
        } else {
            $decodedAnswerState = decodeAnswerState(wHook()->user()->state);
            $userState = $decodedAnswerState['type'] . '->' . $decodedAnswerState['method'];
        }

        if (BotUserAction::isHistoryNavigation($userState)) {
            return;
        }

        BotUserAction::create([
            'bot_user_id' => wHook()->user()->id,
            'update_type' => $this->event->updateType,
            'state' => $userState,
            'action' => wHook()->update()->message->text
        ]);
    }

    private function processCallbackQueryUpdate()
    {
        $cbData = decodeCallback(wHook()->update()->callbackQuery->data);
        $userState = $cbData['type'].'->'.$cbData['method'];

        if (BotUserAction::isHistoryNavigation($userState)) {
            return;
        }

        BotUserAction::create([
            'bot_user_id' => wHook()->user()->id,
            'update_type' => $this->event->updateType,
            'state' => $userState,
            'action' => getInputInlineKeyText(),
        ]);
    }
}
