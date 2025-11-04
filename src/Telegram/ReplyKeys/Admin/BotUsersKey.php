<?php

namespace TelegramBotEssentials\UserManagement\Telegram\ReplyKeys\Admin;

use Telegram\Bot\Exceptions\TelegramSDKException;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Exceptions\InvalidPageNumber;
use TelegramBotEssentials\Essence\Telegram\ReplyKeys\ReplyKey;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

class BotUsersKey extends ReplyKey
{
    protected string $text = 'Bot Users 👥';

    protected int $perm = Roles::ADMIN->value;

    protected string $response = 'Bot Users executed successfully.';

    public function __construct()
    {
        $this->text = __('tbe-user-management::bot_users.reply_key');
    }

    /**
     * @throws InvalidPageNumber
     * @throws TelegramSDKException
     */
    public function handle(): void
    {
        BotUsersFeature::menu()->send();
    }
}
