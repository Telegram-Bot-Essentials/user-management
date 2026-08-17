<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class BotUserFilter
{
    /**
     * @param Closure|null $apply constrains the user list; null means no
     *                            constraint at all, which is what the "all"
     *                            filter is
     */
    public function __construct(
        public string $key,
        public string $label,
        public ?Closure $apply = null,
        public bool|Closure $active = true,
    ) {}

    public function isActive(): bool
    {
        return $this->active instanceof Closure
            ? (bool) ($this->active)()
            : $this->active;
    }

    public function applyTo(Builder $query): Builder
    {
        if (! $this->apply) {
            return $query;
        }

        return ($this->apply)($query);
    }
}
