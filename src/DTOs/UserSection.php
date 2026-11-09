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
        public ?Closure $webUrl = null,
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

    /**
     * Where this section opens in the web panel, or null when it has no
     * screen there; the panel then shows the label alone. Called only while
     * the panel renders, so the closure may use Filament.
     */
    public function webUrlFor(BotUser $user): ?string
    {
        if ($this->webUrl === null) {
            return null;
        }

        $url = ($this->webUrl)($user);

        return is_string($url) && $url !== '' ? $url : null;
    }

    public function contentFor(BotUser $user): array
    {
        return ($this->content)($user);
    }
}
