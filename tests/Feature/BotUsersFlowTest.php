<?php

declare(strict_types=1);

use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\UserManagement\Telegram\ReplyKeys\Admin\BotUsersKey;

beforeEach(function () {
    $this->bot = $this->makeBot();
});

it('orders the user list by the chosen sort and direction', function () {
    $older = $this->makeBotUser($this->bot, 1001, ['created_at' => now()->subDays(5)]);
    $newer = $this->makeBotUser($this->bot, 1002, ['created_at' => now()->subDay()]);

    $asc = botUserSorts()->apply('created_at', BotUser::query(), 'asc')->pluck('id')->all();
    $desc = botUserSorts()->apply('created_at', BotUser::query(), 'desc')->pluck('id')->all();

    expect($asc)->toBe([$older->id, $newer->id])
        ->and($desc)->toBe([$newer->id, $older->id]);
});

it('constrains the list to the chosen filter', function () {
    $active = $this->makeBotUser($this->bot, 2001, ['status' => BotUser::STATUS_ACTIVE]);
    $blocked = $this->makeBotUser($this->bot, 2002, ['status' => BotUser::STATUS_BLOCKED]);

    $ids = botUserFilters()->apply('blocked', BotUser::query())->pluck('id')->all();

    expect($ids)->toBe([$blocked->id]);
});

it('falls back to the default sort/filter key for an unknown one', function () {
    expect(botUserSorts()->resolve('nope'))->toBe(botUserSorts()->getDefaultKey())
        ->and(botUserFilters()->resolve('nope'))->toBe(botUserFilters()->getDefaultKey());
});

it('resolves sort and filter labels in the locale active at read time, not at registration', function () {
    app()->setLocale('en');
    $sortEn = botUserSorts()->getSort('created_at')->label();
    $filterEn = botUserFilters()->getFilter('blocked')->label();

    app()->setLocale('fa');

    expect(botUserSorts()->getSort('created_at')->label())->not->toBe($sortEn)
        ->and(botUserFilters()->getFilter('blocked')->label())->not->toBe($filterEn)
        ->and($sortEn)->toBe('Join date');
});

it('resolves the reply-key label lazily too', function () {
    $key = new BotUsersKey;

    app()->setLocale('en');
    expect($key->getText())->toBe('Bot Users 👥');

    app()->setLocale('fa');
    expect($key->getText())->toBe('کاربران ربات 👥');

    expect($key->getPerm())->toBe(Roles::ADMIN->value);
});

it('tells an admin the step expired when the message meta was pruned mid-flow', function () {
    // 15 users -> two pages, so "2" is a valid page and validation passes,
    // letting the flow reach requireMessageMeta().
    for ($peer = 3001; $peer <= 3015; $peer++) {
        $this->makeBotUser($this->bot, $peer);
    }

    $this->makeBotUser($this->bot, 5000, [
        'power' => Roles::ADMIN->value,
        'state' => encodeAnswerState('BOTUSERS', 'setMenuPage', ['message_meta_id' => 999999]),
    ]);

    $this->postWebhookUpdate($this->bot, $this->makeMessageUpdate('2', peerId: 5000))->assertOk();

    $this->assertTelegramSent(
        fn ($request) => str_contains((string) $request->url(), '/sendMessage')
            && str_contains((string) $request['text'], __('tbe::general.alerts.contextExpired'))
    );

    expect($this->bot->botUsers()->where('telegram_user_peer_id', 5000)->sole()->state)->toBeNull();
});
