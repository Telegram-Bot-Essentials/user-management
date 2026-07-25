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
    public function sortMenu(): void
    {
        BotUsersFeature::sortMenu()->update();
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function sort(): void
    {
        $navState = navState()->getForCurrentMessage(BotUsersFeature::NAV_STATE_DEFAULTS);
        $nextSort = botUserSorts()->next(botUserSorts()->resolve($navState['sort']));
        BotUsersFeature::menu(1, 0, $nextSort, $navState['direction'])->update();
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function toggleDirection(): void
    {
        $navState = navState()->getForCurrentMessage(BotUsersFeature::NAV_STATE_DEFAULTS);
        $nextDirection = botUserSorts()->toggleDirection($navState['direction'] ?? 'desc');
        BotUsersFeature::menu($navState['lastPage'], 0, $navState['sort'], $nextDirection)->update();
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
    public function show(BotUser $botUser): void
    {
        BotUsersFeature::show($botUser)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function role(BotUser $botUser): void
    {
        $roles = array_map(fn ($role) => $role->value, Roles::cases());
        $next = nextInArray($roles, $botUser->power);
        $botUser->power = $next ?? 0;
        $botUser->save();

        BotUsersFeature::show($botUser)->update();
    }

    /**
     * @throws TelegramSDKException
     */
    public function suspend(BotUser $botUser, bool $suspend): void
    {
        $botUser->suspend = $suspend;
        $botUser->save();

        BotUsersFeature::show($botUser)->update();
    }

    public function userActionsHistory(BotUser $botUser, int $page = 1, int $currentPage = 0): void
    {
        BotUsersFeature::userActionsHistory($botUser, $page, $currentPage)->update();
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function actionsPage(int $page, int $currentPage, BotUser $botUser): void
    {
        BotUsersFeature::userActionsHistory($botUser, $page, $currentPage)->update();
    }

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function actionsSetPage(BotUser $botUser): void
    {
        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction(__('tbe-user-management::bot_users.main.text.user_actions_history_waiting_page'));
        wHook()->user()->changeState(encodeAnswerState($this->type, 'actionsSetPage', [
            'message_meta_id' => $messageMeta->id,
            'bot_user_id' => $botUser->id,
        ]));
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-user-management::bot_users.main.text.user_actions_history_enter_page'),
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function allActionsHistory(int $page = 1, int $currentPage = 0, int $lastPage = 1, ?string $sort = null, ?string $direction = null): void
    {
        BotUsersFeature::allActionsHistory($page, $currentPage, $lastPage, $sort, $direction)->update();
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function allActionsPage(int $page, int $currentPage, int $lastPage = 1, ?string $sort = null, ?string $direction = null): void
    {
        BotUsersFeature::allActionsHistory($page, $currentPage, $lastPage, $sort, $direction)->update();
    }

    /**
     * @throws LogicException
     * @throws BindingResolutionException
     * @throws TelegramSDKException
     */
    public function allActionsSetPage(int $lastPage = 1, ?string $sort = null, ?string $direction = null): void
    {
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);
        $messageMeta = MessageMeta::makeWithCurrentMessage();
        $messageMeta->lockAction(__('tbe-user-management::bot_users.main.text.user_actions_history_waiting_page'));
        wHook()->user()->changeState(encodeAnswerState($this->type, 'allActionsSetPage', [
            'message_meta_id' => $messageMeta->id,
            'last_page' => $lastPage,
            'sort' => $sort,
            'direction' => $direction,
        ]));
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-user-management::bot_users.main.text.user_actions_history_enter_page'),
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);
    }
}
