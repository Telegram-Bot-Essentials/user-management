<?php

namespace TelegramBotEssentials\UserManagement\Services;

use Illuminate\Support\Collection;
use TelegramBotEssentials\Essence\Exceptions\TbeException;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\UserManagement\DTOs\UserSection;

class UserManagementSections
{
    private Collection $sections;

    public function __construct()
    {
        $this->sections = collect();
    }

    public function addSection(UserSection $section): void
    {
        if ($this->sections->has($section->key)) {
            throw new TbeException("User section \"{$section->key}\" is already registered");
        }

        $this->sections->put($section->key, $section);
    }

    public function getSectionsFor(BotUser $user): Collection
    {
        return $this->sections
            ->filter(fn (UserSection $section) => $section->isActive($user))
            ->sortBy(fn (UserSection $section) => $section->order)
            ->values();
    }
}
