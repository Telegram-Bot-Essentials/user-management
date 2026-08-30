<?php

namespace TelegramBotEssentials\UserManagement\Telegram\StateAnswers\Admin;

use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use TelegramBotEssentials\Essence\Enums\AllowableFields;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Services\TelegramPaginator;
use TelegramBotEssentials\Essence\Telegram\StateAnswers\StateAnswer;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

class BotUsersAnswer extends StateAnswer
{
    protected string $type = 'BOTUSERS';

    protected int $perm = Roles::ADMIN->value;

    protected array $allowedFields = [
        AllowableFields::TEXT->value,
    ];

    /**
     * @throws LogicException
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     * @throws InvalidPageNumber
     */
    public function setMenuPage(?string $sort = null, ?string $direction = null): void
    {
        $page = wHook()->update()->message->text;
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);
        $lastPage = botUserSorts()
            ->apply($sort, BotUser::query(), $direction)
            ->paginate(perPage: 10)
            ->lastPage();

        TelegramPaginator::validatePageInput($page, $lastPage);

        $data = BotUsersFeature::menu(intval($page), sort: $sort, direction: $direction);

        wHook()->user()->changeState();
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => "Page $page loaded",
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);

        $this->requireMessageMeta()->updateAndContinueAction($data);
    }

    /**
     * @throws LogicException
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     * @throws InvalidPageNumber
     */
    public function actionsSetPage(int $bot_user_id): void
    {
        $page = wHook()->update()->message->text;
        $lastPage = BotUserAction::query()
            ->where('bot_user_id', $bot_user_id)
            ->paginate(perPage: 10)
            ->lastPage();

        TelegramPaginator::validatePageInput($page, $lastPage);

        $botUser = BotUser::query()->findOrFail($bot_user_id);
        $data = BotUsersFeature::userActionsHistory($botUser, intval($page));

        wHook()->user()->changeState();
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-user-management::bot_users.main.text.user_actions_history_page_loaded', [
                'page' => $page,
            ]),
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);

        $this->requireMessageMeta()->updateAndContinueAction($data);
    }

    /**
     * @throws LogicException
     * @throws TelegramSDKException
     * @throws BindingResolutionException
     * @throws InvalidPageNumber
     */
    public function allActionsSetPage(int $last_page = 1, ?string $sort = null, ?string $direction = null): void
    {
        $page = wHook()->update()->message->text;
        $sort = botUserSorts()->resolve($sort);
        $direction = botUserSorts()->resolveDirection($direction);
        $lastPage = BotUserAction::query()
            ->paginate(perPage: 10)
            ->lastPage();

        TelegramPaginator::validatePageInput($page, $lastPage);

        $data = BotUsersFeature::allActionsHistory(intval($page), lastPage: $last_page, sort: $sort, direction: $direction);

        wHook()->user()->changeState();
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => __('tbe-user-management::bot_users.main.text.user_actions_history_page_loaded', [
                'page' => $page,
            ]),
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);

        $this->requireMessageMeta()->updateAndContinueAction($data);
    }
}
