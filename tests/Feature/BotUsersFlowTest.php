<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Monolog\Handler\TestHandler;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Events\BotUserStatusChanged;
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

it('writes role and suspension changes to the audit channel, naming the target', function () {
    config()->set('logging.channels.tbe_audit', ['driver' => 'monolog', 'handler' => TestHandler::class]);
    config()->set('tbe-essence.logging.audit_channel', 'tbe_audit');
    /** @var TestHandler $audit */
    $audit = Log::channel('tbe_audit')->getLogger()->getHandlers()[0];

    $target = $this->makeBotUser($this->bot, 6001);
    $target->telegramUser->update(['username' => 'target']);
    $this->makeBotUser($this->bot, 6000, ['power' => Roles::ADMIN->value]);

    $this->postWebhookUpdate($this->bot, $this->makeCallbackQueryUpdate(encodeCallback('BOTUSERS', 'role', [$target->id]), peerId: 6000))->assertOk();
    $this->postWebhookUpdate($this->bot, $this->makeCallbackQueryUpdate(encodeCallback('BOTUSERS', 'suspend', [$target->id, 1]), peerId: 6000))->assertOk();

    $messages = array_map(fn ($record) => $record->message, $audit->getRecords());

    expect($messages)->toHaveCount(2)
        ->and($messages[0])->toEndWith('| Changed role of user#6001 @target: member -> admin')
        ->and($messages[0])->toContain('user#6000')
        ->and($messages[1])->toEndWith('| Suspended user#6001 @target');
});

it('writes each recorded action to the activity channel too', function () {
    config()->set('logging.channels.tbe_activity', ['driver' => 'monolog', 'handler' => TestHandler::class]);
    config()->set('logging.channels.tbe_audit', ['driver' => 'monolog', 'handler' => TestHandler::class]);
    config()->set('tbe-essence.logging.channels', ['user-management' => 'tbe_activity']);
    config()->set('tbe-essence.logging.audit_channel', 'tbe_audit');
    /** @var TestHandler $activity */
    $activity = Log::channel('tbe_activity')->getLogger()->getHandlers()[0];

    $admin = $this->makeBotUser($this->bot, 6000, ['power' => Roles::ADMIN->value]);
    $target = $this->makeBotUser($this->bot, 6001);

    $this->postWebhookUpdate($this->bot, $this->makeCallbackQueryUpdate(encodeCallback('BOTUSERS', 'role', [$target->id]), peerId: 6000))->assertOk();
    event(new BotUserStatusChanged($admin, 'reachable', 'blocked', 'update'));

    $messages = array_map(fn ($record) => $record->message, $activity->getRecords());

    expect($messages)->toHaveCount(2)
        ->and($messages[0])->toContain('user#6000')
        ->and($messages[0])->toEndWith('(BOTUSERS->role)')
        ->and($messages[1])->toEndWith('| bot_user_status: reachable -> blocked (update)');
});
