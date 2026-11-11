<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;
use TelegramBotEssentials\Essence\Exceptions\TbeException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;

/**
 * A package's part of a member's profile. Give a summary (data) and both the
 * bot and the web panel draw it their own way; label and content are the
 * older bot-only wording and win over the summary when given.
 */
class UserSection
{
    public function __construct(
        public string $key,
        public int $order,
        public SectionMode $mode,
        public ?Closure $label = null,
        public ?Closure $target = null,
        public ?Closure $content = null,
        public bool|Closure $active = true,
        public ?Closure $webUrl = null,
        public ?Closure $summary = null,
    ) {
        if (! $this->label && ! $this->summary) {
            throw new TbeException("User section \"{$this->key}\" has neither a label nor a summary closure");
        }

        if ($this->mode === SectionMode::BUTTON && ! $this->target) {
            throw new TbeException("User section \"{$this->key}\" is in Button mode but has no target closure");
        }

        if ($this->mode === SectionMode::INLINE && ! $this->content && ! $this->summary) {
            throw new TbeException("User section \"{$this->key}\" is in Inline mode but has neither a content nor a summary closure");
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
        if ($this->label) {
            return ($this->label)($user);
        }

        return $this->summaryFor($user)?->botText() ?? '';
    }

    public function targetFor(BotUser $user): string
    {
        return ($this->target)($user);
    }

    /** What the section says about the member as data, or null when it gives wording only. */
    public function summaryFor(BotUser $user): ?SectionSummary
    {
        if ($this->summary === null) {
            return null;
        }

        $summary = ($this->summary)($user);

        return $summary instanceof SectionSummary ? $summary : null;
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
        if ($this->content) {
            return ($this->content)($user);
        }

        $summary = $this->summaryFor($user);

        return $summary ? ['text' => $summary->botHtml()] : [];
    }
}
