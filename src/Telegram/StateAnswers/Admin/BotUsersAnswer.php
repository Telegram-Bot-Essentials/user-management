<?php

namespace TelegramBotEssentials\UserManagement\Telegram\StateAnswers\Admin;

use TelegramBotEssentials\Essence\Enums\AllowableFields;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Telegram\StateAnswers\StateAnswer;

class BotUsersAnswer extends StateAnswer
{
    protected string $type = 'BOTUSERS';

    protected int $perm = Roles::ADMIN->value;

    protected array $allowedFields = [
        AllowableFields::TEXT->value,
    ];
}
