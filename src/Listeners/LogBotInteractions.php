<?php

namespace TelegramBotEssentials\UserManagement\Listeners;

use App\Enums\PanelType;
use App\Panels\Admin\Marzban\MarzbanPanelAdminExtension;
use App\Panels\Admin\Rebecca\RebeccaPanelAdminExtension;
use Telegram\Bot\Objects\ChatMemberUpdated;
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
                'edited_message' => $this->processEditedMessageUpdate(),
                'inline_query' => $this->processInlineQueryUpdate(),
                'chosen_inline_result' => $this->processChosenInlineResultUpdate(),
                'shipping_query' => $this->processShippingQueryUpdate(),
                'pre_checkout_query' => $this->processPreCheckoutQueryUpdate(),
                'poll_answer' => $this->processPollAnswerUpdate(),
                // Recorded by LogBotUserStatusChanges instead, which also sees
                // the blocks discovered by a failed send rather than by an
                // update, so one change produces exactly one history row.
                'my_chat_member' => null,
                'chat_member' => $this->processChatMemberUpdate(wHook()->update()->chatMember),
                'chat_join_request' => $this->processChatJoinRequestUpdate(),
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

        $this->logAction(
            action: wHook()->update()->message->text
                ?? wHook()->update()->message->caption
                ?? ('[' . $this->event->updateType . ']'),
            state: $userState,
        );
    }

    private function processCallbackQueryUpdate(): void
    {
        $cbData = decodeCallback(wHook()->update()->callbackQuery->data);
        $userState = $cbData['type'].'->'.$cbData['method'];

        if (BotUserAction::isHistoryNavigation($userState)) {
            return;
        }

        $this->logAction(
            action: getInputInlineKeyText() ?? ('[' . $this->event->updateType . ']'),
            state: $userState,
        );
    }

    private function processEditedMessageUpdate(): void
    {
        $this->logAction(
            action: wHook()->update()->editedMessage->text
                ?? wHook()->update()->editedMessage->caption
                ?? ('[' . $this->event->updateType . ']'),
        );
    }

    private function processInlineQueryUpdate(): void
    {
        $this->logAction(
            action: wHook()->update()->inlineQuery->query ?: ('[' . $this->event->updateType . ']'),
        );
    }

    private function processChosenInlineResultUpdate(): void
    {
        $chosenInlineResult = wHook()->update()->chosenInlineResult;

        $this->logAction(
            action: $chosenInlineResult->query
                ? $chosenInlineResult->query . ' -> ' . $chosenInlineResult->resultId
                : $chosenInlineResult->resultId,
        );
    }

    private function processShippingQueryUpdate(): void
    {
        $this->logAction(
            action: wHook()->update()->shippingQuery->invoicePayload ?: ('[' . $this->event->updateType . ']'),
        );
    }

    private function processPreCheckoutQueryUpdate(): void
    {
        $preCheckoutQuery = wHook()->update()->preCheckoutQuery;

        $this->logAction(
            action: sprintf(
                '%s (%d %s)',
                $preCheckoutQuery->invoicePayload,
                $preCheckoutQuery->totalAmount,
                $preCheckoutQuery->currency,
            ),
        );
    }

    private function processPollAnswerUpdate(): void
    {
        $optionIds = wHook()->update()->pollAnswer->optionIds ?? [];

        $this->logAction(
            action: $optionIds === []
                ? '[retracted vote]'
                : 'options: ' . implode(', ', $optionIds),
        );
    }

    private function processChatMemberUpdate(ChatMemberUpdated $chatMemberUpdated): void
    {
        $this->logAction(
            action: $chatMemberUpdated->oldChatMember->status . ' -> ' . $chatMemberUpdated->newChatMember->status,
        );
    }

    private function processChatJoinRequestUpdate(): void
    {
        $this->logAction(
            action: wHook()->update()->chatJoinRequest->bio ?: ('[' . $this->event->updateType . ']'),
        );
    }

    private function logAction(string $action, ?string $state = null): void
    {
        BotUserAction::create([
            'bot_user_id' => wHook()->user()->id,
            'update_type' => $this->event->updateType,
            'state' => $state,
            'action' => $action,
        ]);
    }
}
