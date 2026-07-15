<?php

namespace TelegramBotEssentials\UserManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use TelegramBotEssentials\Essence\Models\Bot;
use TelegramBotEssentials\Essence\Models\BotUser;

class BotUserAction extends Model
{
    use BelongsToTenant;

    public const HISTORY_NAVIGATION_METHODS = [
        'userActionsHistory',
        'allActionsHistory',
        'actionsPage',
        'allActionsPage',
        'actionsSetPage',
        'allActionsSetPage',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public static function isHistoryNavigation(?string $state): bool
    {
        if (! $state) {
            return false;
        }

        $method = str_contains($state, '->') ? explode('->', $state, 2)[1] : $state;

        return in_array($method, self::HISTORY_NAVIGATION_METHODS, true);
    }

    public function bot(): BelongsTo
    {
        return $this->belongsTo(Bot::class);
    }

    public function botUser(): BelongsTo
    {
        return $this->belongsTo(BotUser::class);
    }
}
