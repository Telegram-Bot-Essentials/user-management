<?php

namespace TelegramBotEssentials\UserManagement\DTOs;

use Closure;

/**
 * A block of text a package contributes to the header of the bot user list.
 *
 * The package renders its own line rather than handing over label/value pairs,
 * because what these lines carry differs too much to share one formatter: money
 * goes through the currency service, counts through number_format, and each
 * package keeps its wording in its own lang file.
 */
class UserStat
{
    /**
     * @param  Closure  $content  returns the rendered block, or null to print
     *                            nothing at all this time round
     */
    public function __construct(
        public string $key,
        public int $order,
        public Closure $content,
        public bool|Closure $active = true,
    ) {}

    public function isActive(): bool
    {
        return $this->active instanceof Closure
            ? (bool) ($this->active)()
            : $this->active;
    }

    public function render(): ?string
    {
        $content = ($this->content)();

        return empty($content) ? null : $content;
    }
}
