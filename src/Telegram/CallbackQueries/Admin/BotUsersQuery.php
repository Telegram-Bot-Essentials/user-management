<?php

namespace TelegramBotEssentials\UserManagement\Telegram\CallbackQueries\Admin;

use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;

class BotUsersQuery extends CallbackQuery
{
    protected string $type = 'BOTUSERS';

    protected int $perm = Roles::ADMIN->value;
}
