<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use TelegramBotEssentials\Essence\Support\Label;

class BotUserFilter
{
    /**
     * @param  string|Label|Closure(): (string|Label)  $label  resolved on every read via
     *                                                         label(); pass a Closure so a filter registered once at boot
     *                                                         still renders in the current bot's language, and a Label
     *                                                         to keep the bot's emoji out of the panel
     * @param  Closure|null  $apply  constrains the user list; null means no
     *                               constraint at all, which is what the "all" filter is
     */
    public function __construct(
        public string $key,
        public string|Label|Closure $label,
        public ?Closure $apply = null,
        public bool|Closure $active = true,
    ) {}

    /** The bot's wording, emoji included. */
    public function label(): string
    {
        return $this->labelled()->forBot();
    }

    /** The name for both sides. */
    public function labelled(): Label
    {
        $label = $this->label instanceof Closure ? ($this->label)() : $this->label;

        return $label instanceof Label ? $label : Label::of((string) $label);
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
