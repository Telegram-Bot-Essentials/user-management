# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/). Until the API
stabilizes at 1.0 a `0.0.x` bump may carry breaking changes.

## [Unreleased]

## [0.0.16] - 2026-09-01

### Changed

- **BREAKING:** requires `telegram-bot-essentials/essence` `^0.12`. Sort and
  filter labels resolve lazily per bot, and resume paths use
  `StateAnswer::requireMessageMeta()`.

### Added

- `BotUserFilters` registry (`botUserFilters()`), mirroring `BotUserSorts` —
  single-select constraints on the user list, active key kept in the message
  nav state. Ships the reachability filters (`all`, `reachable`, `blocked`,
  `unreachable`, `deleted account`) built on essence's reachability scopes;
  unreachable users are also marked inline in the list (0.0.15).
- `UserManagementStats` registry (`userManagementStats()`): the list header
  now reports the state of the user base in four lines (growth, reachability,
  activity, staff), and other packages append their own blocks (0.0.15).
- `LogBotUserStatusChanges` listener on essence's `BotUserStatusChanged`,
  replacing the `my_chat_member` branch of `LogBotInteractions` — also
  captures blocks discovered by a failed send (0.0.15).
- `default_filter` config key.
- Pest test suite, Laravel Pint, Larastan (level max), GitHub Actions CI,
  `LICENSE` (MIT) and this changelog.
