<?php

namespace TelegramBotEssentials\UserManagement\Telegram\StateAnswers\Admin;

use Illuminate\Contracts\Container\BindingResolutionException;
use Telegram\Bot\Exceptions\TelegramSDKException;
use TelegramBotEssentials\Essence\Enums\AllowableFields;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Exceptions\LogicException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Models\MessageMeta;
use TelegramBotEssentials\Essence\Services\TelegramPaginator;
use TelegramBotEssentials\Essence\Telegram\StateAnswers\StateAnswer;
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
    public function setMenuPage(): void
    {
        $page = wHook()->update()->message->text;
        $lastPage = BotUser::paginate(perPage: 10)->lastPage();

        TelegramPaginator::validatePageInput($page, $lastPage);

        $data = BotUsersFeature::menu(intval($page));

        wHook()->user()->changeState();
        wHook()->api()->sendMessage([
            'chat_id' => wHook()->user()->telegramUser->peer_id,
            'text' => "Page $page loaded",
            'reply_markup' => wHook()->user()->getKeyboard(),
        ]);

        $this->messageMeta()->updateAndContinueAction($data);
    }
}
