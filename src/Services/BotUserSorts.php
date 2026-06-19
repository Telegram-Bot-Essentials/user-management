<?php

namespace TelegramBotEssentials\UserManagement\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;

class BotUserSorts
{
    private Collection $sorts;

    public function __construct()
    {
        $this->sorts = collect();
    }

    public function addSort(BotUserSort $sort): void
    {
        $this->sorts->put($sort->key, $sort);
    }

    public function getSorts(): Collection
    {
        return $this->sorts->filter(fn (BotUserSort $sort) => $sort->isActive());
    }

    public function getSort(string $key): ?BotUserSort
    {
        $sort = $this->sorts->get($key);

        return ($sort && $sort->isActive()) ? $sort : null;
    }

    public function getDefaultKey(): string
    {
        $default = config('tbe-user-management.default_sort', 'last_interaction');
        $active = $this->getSorts();

        return $active->has($default) ? $default : $active->keys()->first() ?? 'last_interaction';
    }

    public function resolve(?string $key): string
    {
        $active = $this->getSorts();

        if ($key && $active->has($key)) {
            return $key;
        }

        return $this->getDefaultKey();
    }

    public function next(string $current): string
    {
        $keys = $this->getSorts()->keys()->values()->all();

        return nextInArray($keys, $current) ?? $keys[0] ?? $current;
    }

    public function resolveDirection(?string $direction): string
    {
        return in_array($direction, ['asc', 'desc'], true)
            ? $direction
            : config('tbe-user-management.default_sort_direction', 'desc');
    }

    public function toggleDirection(string $direction): string
    {
        return $this->resolveDirection($direction) === 'asc' ? 'desc' : 'asc';
    }

    public function directionIndicator(string $direction): string
    {
        return $this->resolveDirection($direction) === 'asc' ? '⬆️' : '⬇️';
    }

    public function apply(string $key, Builder $query, ?string $direction = null): Builder
    {
        $sort = $this->getSort($this->resolve($key));

        if ($sort === null) {
            return $query;
        }

        return $sort->applyTo($query, $this->resolveDirection($direction));
    }
}
