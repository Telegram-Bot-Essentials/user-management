<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use BackedEnum;

/**
 * What a profile section says about a member, as data rather than wording:
 * the bot builds its button or line from it, the web panel a stat card.
 */
final class SectionSummary
{
    /**
     * @param  string  $title  plain name, no emoji: "Wallet"
     * @param  int|string|list<string>|null  $value  a count, a formatted figure, or names
     * @param  string|null  $emoji  the bot's marker: "💰"
     * @param  string|BackedEnum|null  $icon  the panel's icon, a Heroicon
     */
    public function __construct(
        public string $title,
        public int|string|array|null $value = null,
        public ?string $emoji = null,
        public string|BackedEnum|null $icon = null,
    ) {}

    /** The value as one line of text; names joined with commas. */
    public function valueText(): string
    {
        return is_array($this->value) ? implode(', ', $this->value) : (string) $this->value;
    }

    /** A button's text: "💰 Wallet: 120,000", or "🧾 Invoices (3)" for a count. */
    public function botText(): string
    {
        $text = $this->heading();

        if (is_int($this->value)) {
            return "{$text} ({$this->value})";
        }

        return $this->valueText() === '' ? $text : $text.': '.$this->valueText();
    }

    /** A line of the bot's message, in Telegram HTML: "📣 Campaign: <b>Summer</b>". */
    public function botHtml(): string
    {
        $text = e($this->heading());

        return $this->valueText() === '' ? $text : $text.': <b>'.e($this->valueText()).'</b>';
    }

    private function heading(): string
    {
        return trim(($this->emoji ?? '').' '.$this->title);
    }
}
