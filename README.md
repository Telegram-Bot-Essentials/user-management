# Telegram Bot Essentials — User Management

[![Latest Version](https://img.shields.io/packagist/v/telegram-bot-essentials/user-management.svg)](https://packagist.org/packages/telegram-bot-essentials/user-management)
[![tests](https://github.com/Telegram-Bot-Essentials/user-management/actions/workflows/tests.yml/badge.svg)](https://github.com/Telegram-Bot-Essentials/user-management/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

Gives admins a browsable, sortable, filterable list of bot users, a per-user detail screen
built from pluggable **sections**, and automatic interaction logging — on top of
[`telegram-bot-essentials/essence`](https://github.com/Telegram-Bot-Essentials/essence).

Other packages (notably
[`user-wallet`](https://github.com/Telegram-Bot-Essentials/user-wallet)) extend the list by
registering their own sort, filter, section, or header block rather than forking the admin
UI.

## Installation

```bash
composer require telegram-bot-essentials/user-management
php artisan migrate

# optional — change the default sort/filter
php artisan vendor:publish --tag=tbe-user-management-config
```

Install it, and admins get a working "Users" menu immediately.

## Extension points

| Helper | Registers | Purpose |
|---|---|---|
| `botUserSorts()` | `BotUserSort` | A sortable/displayable column in the list |
| `botUserFilters()` | `BotUserFilter` | A single-select list filter (reachability filters ship built-in) |
| `userManagementStats()` | `UserStat` | A block in the list header |
| `userManagementSections()` | `UserSection` | A button or inline block on the per-user detail screen |

```php
botUserSorts()->addSort(new BotUserSort(
    key: 'wallet_balance',
    label: __('my-package::bot_users.sorts.wallet_balance'),
    apply: fn ($query, $direction) => $query->leftJoin(/* ... */)->orderBy(/* ... */)->select('bot_users.*'),
    display: fn (BotUser $user) => $user->wallet_balance ?? '0',
    active: fn () => settings()->get('billing.user_wallet.status'),
));
```

> **`active` must be a `Closure`** for anything conditional on a per-bot setting — it's
> evaluated per request, after `wHook()->bot()` resolves, not once at boot.

Every update a user generates is logged to `bot_user_actions` automatically once the
package is installed; reachability transitions are logged separately off essence's
`BotUserStatusChanged` event.

## Documentation

Full documentation — every DTO, the filtering and stats APIs, interaction logging, and how
User Wallet plugs in — lives on the Telegram Bot Essentials documentation site under
**Modules → User Management**.

## License

[MIT](LICENSE).
