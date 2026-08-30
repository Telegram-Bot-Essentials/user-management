<?php

declare(strict_types=1);

use TelegramBotEssentials\UserManagement\Services\BotUserFilters;
use TelegramBotEssentials\UserManagement\Services\BotUserSorts;
use TelegramBotEssentials\UserManagement\Services\UserManagementSections;
use TelegramBotEssentials\UserManagement\Services\UserManagementStats;
use TelegramBotEssentials\UserManagement\Telegram\CallbackQueries\Admin\BotUsersQuery;
use TelegramBotEssentials\UserManagement\Telegram\StateAnswers\Admin\BotUsersAnswer;

it('registers the BotUsers callback query and state answer with essence', function () {
    expect(callbackQueryBus()->getCallbackQueryTypes()['BOTUSERS'] ?? null)->toBeInstanceOf(BotUsersQuery::class)
        ->and(stateAnswerBus()->getStateAnswerTypes()['BOTUSERS'] ?? null)->toBeInstanceOf(BotUsersAnswer::class);
});

it('binds its services as shared singletons', function () {
    foreach ([BotUserSorts::class, BotUserFilters::class, UserManagementSections::class, UserManagementStats::class] as $service) {
        expect(app($service))->toBe(app($service));
    }
});

it('registers the default sorts', function () {
    expect(botUserSorts()->getSorts()->keys()->all())
        ->toEqualCanonicalizing(['last_interaction', 'created_at', 'username']);
});

it('registers the default filters, keeping each failure state distinct', function () {
    expect(botUserFilters()->getFilters()->keys()->all())
        ->toEqualCanonicalizing(['all', 'reachable', 'blocked', 'unreachable', 'deactivated']);
});
