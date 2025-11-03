<?php

namespace TelegramBotEssentials\UserManagement\Telegram\CallbackQueries\Admin;

use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Models\MessageMeta;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

class BotUsersQuery extends CallbackQuery
{
    protected string $type = 'BOTUSERS';
    protected int $perm = Roles::ADMIN->value;

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    function start(): void
    {
        $page = intval($this->params[1] ?? 1);
        $currentPage = intval($this->params[2] ?? 0);
        BotUsersFeature::start($page, $currentPage)->update();
    }

    /**
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     * @throws LogicException
     */
    function setStartPage(): void
    {
        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction('Waiting for page number');
        wHook()->user()->changeState(encodeAnswerState($this->type, "set_start_page", [
            "message_meta_id" => $messageMeta->id
        ]));
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => "Enter page number:",
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);
    }

    /**
     * @throws TelegramSDKException
     */
    function show(): void
    {
        $botUser = BotUser::findOrFail($this->params[1]);
        $lastPage = intval($this->params[2] ?? 1);
        BotUsersFeature::show($botUser, $lastPage)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    function suspend(): void
    {
        $botUser = BotUser::findOrFail($this->params[1]);
        $botUser->suspend = $this->params[2];
        $botUser->save();
        $lastPage = intval($this->params[3] ?? 1);

        BotUsersFeature::show($botUser, $lastPage)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    function role(): void
    {
        $botUser = BotUser::findOrFail($this->params[1]);
        $roles = array_map(fn($role) => $role->value, Roles::cases());
        \Log::error(json_encode($roles));
        $next = nextInArray($roles, $botUser->power);
        \Log::error(json_encode($next));
        $botUser->power = $next ?? 0;
        $botUser->save();

        $lastPage = intval($this->params[2] ?? 1);
        BotUsersFeature::show($botUser, $lastPage)->update();
    }

    function balance(): void
    {
        $type = $this->params[1];
        $botUser = BotUser::findOrFail($this->params[2]);
        $lastPage = intval($this->params[3] ?? 1);

        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction("Waiting for $type balance");
        wHook()->user()->changeState(encodeAnswerState($this->type, "balance", [
            "type" => $type,
            "bot_user_id" => $botUser->id,
            "message_meta_id" => $messageMeta->id,
            "last_page" => $lastPage
        ]));
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => "Enter balance amount to $type:",
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);
    }
}
