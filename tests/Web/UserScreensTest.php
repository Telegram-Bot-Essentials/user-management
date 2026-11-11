<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\WebAccounts;
use TelegramBotEssentials\UserManagement\DTOs\BotUserSort;
use TelegramBotEssentials\UserManagement\DTOs\SectionSummary;
use TelegramBotEssentials\UserManagement\DTOs\UserSection;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;
use TelegramBotEssentials\UserManagement\Web\Manage\ListBotUsers;
use TelegramBotEssentials\UserManagement\Web\Manage\ViewBotUser;
use TelegramBotEssentials\UserManagement\Web\Widgets\ActiveMembersChart;
use TelegramBotEssentials\UserManagement\Web\Widgets\MemberStats;
use TelegramBotEssentials\UserManagement\Web\Widgets\ReachabilityChart;

beforeEach(function () {
    $this->bot = $this->makeBot(['bot_owner_peer_id' => 9401]);
    $this->admin = $this->grant($this->makeBotUser($this->bot, 9401), 'user-management.users.manage', 'web.manage');
    $this->member = $this->makeBotUser($this->bot, 9402);
    $this->member->telegramUser->update(['username' => 'member_one', 'first_name' => 'Mina']);
    $this->account = $this->panelAccount($this->admin);
    Auth::guard(WebAccounts::guard())->login($this->account);
    $this->url = '/manage/'.$this->bot->unique_id.'/users';
});

it('lists this bot\'s members only, with the overview', function () {
    $otherBot = $this->makeBot(['unique_id' => 'other-bot']);
    $this->makeBotUser($otherBot, 9500)->telegramUser->update(['username' => 'outsider']);

    $this->get($this->url)
        ->assertOk()
        ->assertSee('@member_one')
        ->assertDontSee('@outsider')
        ->assertSee(__('tbe-user-management::web.stats.members'));
});

it('keeps the screens from anyone without the ability', function () {
    $limited = $this->grant($this->makeBotUser($this->bot, 9403), 'web.manage');
    Auth::guard(WebAccounts::guard())->login($this->panelAccount($limited));

    $this->get($this->url)->assertForbidden();
});

it('filters with the registry\'s segments', function () {
    $this->member->update(['status' => BotUser::STATUS_BLOCKED]);
    $this->enterPanel('manage', $this->bot, $this->account);

    Livewire::test(ListBotUsers::class)
        ->filterTable('segment', BotUser::STATUS_BLOCKED)
        ->assertCanSeeTableRecords([$this->member])
        ->assertCanNotSeeTableRecords([$this->admin]);
});

it('turns a package\'s sort into a sortable column', function () {
    botUserSorts()->addSort(new BotUserSort(
        key: 'peer',
        label: 'By peer',
        apply: fn (Builder $query, string $direction) => $query->orderBy('telegram_user_peer_id', $direction),
        display: fn (BotUser $user) => 'peer '.$user->telegram_user_peer_id,
    ));
    $this->enterPanel('manage', $this->bot, $this->account);

    Livewire::test(ListBotUsers::class)
        ->toggleAllTableColumns()
        ->sortTable('sort_peer', 'asc')
        ->assertCanSeeTableRecords([$this->admin, $this->member], inOrder: true)
        ->assertSee('peer 9402');
});

it('keeps the Telegram HTML of sections safe, without attributes', function () {
    expect(ListBotUsers::telegramHtml('<b onclick="steal()">Wallets</b>: 3 <a href="javascript:x()">no</a>'))
        ->toBe('<b>Wallets</b>: 3 no');
});

it('charts reachability and actions on the dashboard', function () {
    $this->member->update(['status' => BotUser::STATUS_BLOCKED]);
    BotUserAction::query()->create(['bot_id' => $this->bot->id, 'bot_user_id' => $this->member->id, 'update_type' => 'message', 'action' => 'hi']);
    $this->enterPanel('manage', $this->bot, $this->account);

    $reach = Livewire::test(ReachabilityChart::class)->assertSee(__('tbe-user-management::web.stats.reachability'));
    $data = (fn () => $this->getCachedData())->call($reach->instance());
    expect($data['datasets'][0]['data'][2])->toBe(1);

    $actions = Livewire::test(ActiveMembersChart::class);
    expect(array_sum((fn () => $this->getCachedData())->call($actions->instance())['datasets'][0]['data']))->toBe(1.0);
});

it('hides the member widgets from who may not manage users', function () {
    $limited = $this->grant($this->makeBotUser($this->bot, 9404), 'web.manage');
    $this->enterPanel('manage', $this->bot, $this->panelAccount($limited));

    expect(MemberStats::canView())->toBeFalse()->and(ReachabilityChart::canView())->toBeFalse();
});

it('shows the profile with sections, linking those that have a web screen', function () {
    userManagementSections()->addSection(new UserSection(
        key: 'probe', order: 1, mode: SectionMode::BUTTON,
        label: fn () => 'Wallet: 10', target: fn () => 'WALLET#x', webUrl: fn () => 'https://example.test/wallet',
    ));
    userManagementSections()->addSection(new UserSection(
        key: 'plain', order: 2, mode: SectionMode::BUTTON, label: fn () => 'Invoices (2)', target: fn () => 'INV#x',
    ));
    BotUserAction::query()->create(['bot_id' => $this->bot->id, 'bot_user_id' => $this->member->id, 'update_type' => 'message', 'action' => 'pressed Buy']);

    $this->get($this->url.'/'.$this->member->id)
        ->assertOk()
        ->assertSee('@member_one')
        ->assertSee('https://example.test/wallet')
        ->assertSee('Invoices (2)')
        ->assertSee('pressed Buy');
});

it('draws a section with a summary as a card, values and names apart', function () {
    userManagementSections()->addSection(new UserSection(
        key: 'wallet', order: 1, mode: SectionMode::BUTTON, target: fn () => 'W#x', webUrl: fn () => 'https://example.test/wallet',
        summary: fn () => new SectionSummary('Wallet', '120,000', '💰', 'heroicon-o-wallet'),
    ));
    userManagementSections()->addSection(new UserSection(
        key: 'roles', order: 2, mode: SectionMode::BUTTON, target: fn () => 'R#x',
        summary: fn () => new SectionSummary('Roles', ['Admin', 'Seller'], '🛡'),
    ));

    $this->get($this->url.'/'.$this->member->id)
        ->assertOk()
        ->assertSee('https://example.test/wallet')
        ->assertSeeInOrder(['Wallet', '120,000', 'Roles', 'Admin', 'Seller'])
        ->assertDontSee('💰')
        ->assertDontSee('Wallet: 120,000');
});

it('copes with packages caching data on the member as relations', function () {
    // As tbe-access does with roles: a relation with no method behind it.
    userManagementSections()->addSection(new UserSection(
        key: 'cache', order: 1, mode: SectionMode::BUTTON, target: fn () => 'X#y',
        label: function (BotUser $user) {
            $user->setRelation('cachedRoles', collect());

            return 'Roles';
        },
    ));
    $this->enterPanel('manage', $this->bot, $this->account);

    Livewire::test(ViewBotUser::class, ['record' => $this->member->id])->assertSee('Roles')->callAction('suspend')->assertHasNoErrors();
});

it('suspends and lifts it', function () {
    $this->enterPanel('manage', $this->bot, $this->account);

    Livewire::test(ViewBotUser::class, ['record' => $this->member->id])->callAction('suspend');
    expect($this->member->fresh()->suspend)->toBeTrue();

    Livewire::test(ViewBotUser::class, ['record' => $this->member->id])->callAction('suspend');
    expect($this->member->fresh()->suspend)->toBeFalse();
});

it('does not open another bot\'s member', function () {
    $otherBot = $this->makeBot(['unique_id' => 'other-bot']);

    $this->get($this->url.'/'.$this->makeBotUser($otherBot, 9501)->id)->assertNotFound();
});

it('lists all activity', function () {
    BotUserAction::query()->create(['bot_id' => $this->bot->id, 'bot_user_id' => $this->member->id, 'update_type' => 'callback_query', 'action' => 'opened Shop']);

    $this->get('/manage/'.$this->bot->unique_id.'/activity')->assertOk()->assertSee('opened Shop')->assertSee('Mina');
});

// One Livewire update per test: Livewire skips persistent middleware it already ran for the route.
it('suspends over a real request', function () {
    $this->livewireRequest($this->url.'/'.$this->member->id, 'ViewBotUser', 'mountAction', ['suspend'])->assertOk();
});

it('refuses profile updates once signed out', function () {
    $this->livewireRequest($this->url.'/'.$this->member->id, 'ViewBotUser', 'mountAction', ['suspend'],
        fn () => Auth::guard(WebAccounts::guard())->logout())->assertUnauthorized();
});
