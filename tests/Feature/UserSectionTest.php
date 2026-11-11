<?php

declare(strict_types=1);

use TelegramBotEssentials\Essence\Exceptions\TbeException;
use TelegramBotEssentials\UserManagement\DTOs\SectionSummary;
use TelegramBotEssentials\UserManagement\DTOs\UserSection;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;

beforeEach(function () {
    $this->member = $this->makeBotUser($this->makeBot(), 7001);
});

it('words a summary the way the bot always did', function () {
    expect((new SectionSummary('Wallet', '120,000', '💰'))->botText())->toBe('💰 Wallet: 120,000')
        ->and((new SectionSummary('Invoices', 3, '🧾'))->botText())->toBe('🧾 Invoices (3)')
        ->and((new SectionSummary('Roles', ['Admin', 'Seller'], '🛡'))->botText())->toBe('🛡 Roles: Admin, Seller')
        ->and((new SectionSummary('Roles'))->botText())->toBe('Roles');
});

it('escapes the value of an inline line', function () {
    expect((new SectionSummary('Campaign', '<Summer & co>', '📣'))->botHtml())
        ->toBe('📣 Campaign: <b>&lt;Summer &amp; co&gt;</b>');
});

it('builds the bot button and inline line from a summary', function () {
    $button = new UserSection(
        key: 'wallet', order: 1, mode: SectionMode::BUTTON, target: fn () => 'W#x',
        summary: fn () => new SectionSummary('Wallet', '10', '💰'),
    );
    $inline = new UserSection(
        key: 'campaign', order: 2, mode: SectionMode::INLINE,
        summary: fn () => new SectionSummary('Campaign', 'Summer', '📣'),
    );

    expect($button->labelFor($this->member))->toBe('💰 Wallet: 10')
        ->and($inline->contentFor($this->member))->toBe(['text' => '📣 Campaign: <b>Summer</b>']);
});

it('keeps a package\'s own wording over its summary', function () {
    $section = new UserSection(
        key: 'wallet', order: 1, mode: SectionMode::BUTTON, target: fn () => 'W#x',
        label: fn () => 'Custom', content: fn () => ['text' => 'Own'],
        summary: fn () => new SectionSummary('Wallet', '10'),
    );

    expect($section->labelFor($this->member))->toBe('Custom')
        ->and($section->contentFor($this->member))->toBe(['text' => 'Own']);
});

it('refuses a section with nothing to show', function () {
    new UserSection(key: 'empty', order: 1, mode: SectionMode::BUTTON, target: fn () => 'X#y');
})->throws(TbeException::class);

it('refuses an inline section with neither content nor summary', function () {
    new UserSection(key: 'empty', order: 1, mode: SectionMode::INLINE, label: fn () => 'X');
})->throws(TbeException::class);
