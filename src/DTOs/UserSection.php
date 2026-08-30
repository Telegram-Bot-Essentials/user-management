<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use TelegramBotEssentials\Essence\Exceptions\TbeException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;

class UserSection
{
    public function __construct(
        public string $key,
        public int $order,
        public SectionMode $mode,
        public Closure $label,
        public ?Closure $target = null,
        public ?Closure $content = null,
        public bool|Closure $active = true,
    ) {
        if ($this->mode === SectionMode::BUTTON && ! $this->target) {
            throw new TbeException("User section \"{$this->key}\" is in Button mode but has no target closure");
        }

        if ($this->mode === SectionMode::INLINE && ! $this->content) {
            throw new TbeException("User section \"{$this->key}\" is in Inline mode but has no content closure");
        }
    }

    public function isActive(BotUser $user): bool
    {
        return $this->active instanceof Closure
            ? (bool) ($this->active)($user)
            : $this->active;
    }

    public function labelFor(BotUser $user): string
    {
        return ($this->label)($user);
    }

    public function targetFor(BotUser $user): string
    {
        return ($this->target)($user);
    }

    public function contentFor(BotUser $user): array
    {
        return ($this->content)($user);
    }
}
