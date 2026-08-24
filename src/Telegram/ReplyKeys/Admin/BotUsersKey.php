<?php

namespace TelegramBotEssentials\UserManagement\Telegram\ReplyKeys\Admin;

use Telegram\Bot\Exceptions\TelegramSDKException;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Telegram\ReplyKeys\ReplyKey;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

class BotUsersKey extends ReplyKey
{
    protected string $textKey = 'tbe-user-management::bot_users.reply_key';

    protected int $perm = Roles::ADMIN->value;

    protected string $responseKey = '';


    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        BotUsersFeature::menu()->send();
    }
}
