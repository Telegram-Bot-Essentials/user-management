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
    public function menu(int $page = 1, int $currentPage = 0, ?string $sort = null, ?string $direction = null): void
    {
        BotUsersFeature::menu($page, $currentPage, $sort, $direction)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function sortMenu(int $lastPage = 1, string $currentSort = 'last_interaction', ?string $direction = null): void
    {
        BotUsersFeature::sortMenu($lastPage, botUserSorts()->resolve($currentSort), botUserSorts()->resolveDirection($direction))->update();
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function sort(string $currentSort = 'last_interaction', ?string $direction = null): void
    {
        $nextSort = botUserSorts()->next($currentSort);
        BotUsersFeature::menu(1, 0, $nextSort, $direction)->update();
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function toggleDirection(string $sort, ?string $direction = null, int $page = 1): void
    {
        $nextDirection = botUserSorts()->toggleDirection($direction ?? 'desc');
        BotUsersFeature::menu($page, 0, $sort, $nextDirection)->update();
    }

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function setMenuPage(string $sort = 'last_interaction', ?string $direction = null): void
    {
        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction('Waiting for page number');
        wHook()->user()->changeState(encodeAnswerState($this->type, 'setMenuPage', [
            'message_meta_id' => $messageMeta->id,
            'sort' => $sort,
            'direction' => botUserSorts()->resolveDirection($direction),
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
    public function show(BotUser $botUser, int $lastPage = 1, ?string $sort = null, ?string $direction = null): void
    {
        BotUsersFeature::show($botUser, $lastPage, $sort, $direction)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function role(BotUser $botUser, int $lastPage = 1, ?string $sort = null, ?string $direction = null): void
    {
        $roles = array_map(fn ($role) => $role->value, Roles::cases());
        $next = nextInArray($roles, $botUser->power);
        $botUser->power = $next ?? 0;
        $botUser->save();

        BotUsersFeature::show($botUser, $lastPage, $sort, $direction)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function suspend(BotUser $botUser, bool $suspend, int $lastPage = 1, ?string $sort = null, ?string $direction = null): void
    {
        $botUser->suspend = $suspend;
        $botUser->save();

        BotUsersFeature::show($botUser, $lastPage, $sort, $direction)->update();
    }
}
