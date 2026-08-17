<?php

namespace TelegramBotEssentials\UserManagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use TelegramBotEssentials\UserManagement\DTOs\BotUserFilter;

/**
 * Registry of the constraints an admin can put on the user list, mirroring
 * BotUserSorts so packages can register their own from a service provider.
 *
 * Single-select: one filter is active at a time, and its key is kept in the
 * message's nav state rather than in callback_data, which is already close to
 * Telegram's 64 byte ceiling with the page, sort and direction.
 */
class BotUserFilters
{
    private Collection $filters;

    public function __construct()
    {
        $this->filters = collect();
    }

    public function addFilter(BotUserFilter $filter): void
    {
        $this->filters->put($filter->key, $filter);
    }

    public function getFilters(): Collection
    {
        return $this->filters->filter(fn (BotUserFilter $filter) => $filter->isActive());
    }

    public function getFilter(string $key): ?BotUserFilter
    {
        $filter = $this->filters->get($key);

        return ($filter && $filter->isActive()) ? $filter : null;
    }

    public function getDefaultKey(): string
    {
        $default = config('tbe-user-management.default_filter', 'all');
        $active = $this->getFilters();

        return $active->has($default) ? $default : $active->keys()->first() ?? 'all';
    }

    public function resolve(?string $key): string
    {
        $active = $this->getFilters();

        if ($key && $active->has($key)) {
            return $key;
        }

        return $this->getDefaultKey();
    }

    public function apply(?string $key, Builder $query): Builder
    {
        $filter = $this->getFilter($this->resolve($key));

        if ($filter === null) {
            return $query;
        }

        return $filter->applyTo($query);
    }
}
