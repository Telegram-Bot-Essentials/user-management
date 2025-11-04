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
    public function menu(int $page = 1, int $currentPage = 0): void
    {
        BotUsersFeature::menu($page, $currentPage)->update();
    }

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function setMenuPage(): void
    {
        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction('Waiting for page number');
        wHook()->user()->changeState(encodeAnswerState($this->type, 'setMenuPage', [
            'message_meta_id' => $messageMeta->id,
        ]));
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => 'Enter page number:',
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);
    }

    /**
     * @throws TelegramSDKException
     */
    public function show(BotUser $botUser, int $lastPage = 1): void
    {
        BotUsersFeature::show($botUser, $lastPage)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function role(BotUser $botUser, int $lastPage = 1): void
    {
        $roles = array_map(fn ($role) => $role->value, Roles::cases());
        $next = nextInArray($roles, $botUser->power);
        $botUser->power = $next ?? 0;
        $botUser->save();

        BotUsersFeature::show($botUser, $lastPage)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function suspend(BotUser $botUser, bool $suspend, int $lastPage = 1): void
    {
        $botUser->suspend = $suspend;
        $botUser->save();

        BotUsersFeature::show($botUser, $lastPage)->update();
    }
}
