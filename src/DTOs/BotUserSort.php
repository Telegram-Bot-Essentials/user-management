<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Essence\Models\BotUser;

class BotUserSort
{
    /**
     * @param  string|Closure(): string  $label  a Closure is resolved on every
     *                                           read via label(), so a sort registered once at boot still shows
     *                                           the right language for whichever bot is handling the request
     */
    public function __construct(
        public string $key,
        public string|Closure $label,
        public Closure $apply,
        public ?Closure $display = null,
        public bool|Closure $active = true,
    ) {}

    public function label(): string
    {
        return $this->label instanceof Closure ? ($this->label)() : $this->label;
    }

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
