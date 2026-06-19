<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Essence\Models\BotUser;

class BotUserSort
{
    public function __construct(
        public string $key,
        public string $label,
        public Closure $apply,
        public ?Closure $display = null,
        public bool|Closure $active = true,
    ) {}

    public function isActive(): bool
    {
        return $this->active instanceof Closure
            ? (bool) ($this->active)()
            : $this->active;
    }

    public function applyTo(Builder $query, string $direction): Builder
    {
        return ($this->apply)($query, $direction);
    }

    public function displayValue(BotUser $user): string
    {
        if ($this->display) {
            return ($this->display)($user);
        }

        $value = $user->{$this->key} ?? null;

        if ($value === null) {
            return '?';
        }

        if ($value instanceof \DateTimeInterface) {
            return method_exists($value, 'shortRelativeToNowDiffForHumans')
                ? $value->shortRelativeToNowDiffForHumans()
                : $value->format('Y-m-d H:i');
        }

        return (string) $value;
    }
}
