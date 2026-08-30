<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class BotUserFilter
{
    /**
     * @param  string|Closure(): string  $label  resolved on every read via
     *                                           label(); pass a Closure so a filter registered once at boot
     *                                           still renders in the current bot's language
     * @param  Closure|null  $apply  constrains the user list; null means no
     *                               constraint at all, which is what the "all" filter is
     */
    public function __construct(
        public string $key,
        public string|Closure $label,
        public ?Closure $apply = null,
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

    public function applyTo(Builder $query): Builder
    {
        if (! $this->apply) {
            return $query;
        }

        return ($this->apply)($query);
    }
}
