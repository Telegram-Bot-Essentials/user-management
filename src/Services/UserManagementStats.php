<?php

namespace TelegramBotEssentials\UserManagement\Services;

use Illuminate\Support\Collection;
use TelegramBotEssentials\Essence\Exceptions\TbeException;
use TelegramBotEssentials\UserManagement\DTOs\UserStat;

/**
 * Registry of the extra blocks printed under the bot user list header, so a
 * package that knows something worth reporting about the user base as a whole
 * can say it without user-management knowing that package exists.
 */
class UserManagementStats
{
    private Collection $stats;

    public function __construct()
    {
        $this->stats = collect();
    }

    public function addStat(UserStat $stat): void
    {
        if ($this->stats->has($stat->key)) {
            throw new TbeException("User stat \"{$stat->key}\" is already registered");
        }

        $this->stats->put($stat->key, $stat);
    }

    public function getStats(): Collection
    {
        return $this->stats
            ->filter(fn (UserStat $stat) => $stat->isActive())
            ->sortBy(fn (UserStat $stat) => $stat->order)
            ->values();
    }

    /**
     * @return Collection<int, string> the rendered blocks, in order, with the
     *                                 ones that had nothing to say dropped
     */
    public function render(): Collection
    {
        return $this->getStats()
            ->map(fn (UserStat $stat) => $stat->render())
            ->filter()
            ->values();
    }
}
