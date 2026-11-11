<x-filament-panels::page>
    @php($member = $this->member())
    @php($tg = $member->telegramUser)
    @php($calendar = \TelegramBotEssentials\Essence\Web\PanelCalendar::class)
    <div class="grid gap-6">
        <x-filament::section>
            <x-tbe::facts>
                <x-tbe::fact :label="__('tbe-user-management::web.username')" :value="$tg?->username ? '@'.$tg->username : null" />
                <x-tbe::fact :label="__('tbe-user-management::web.peer_id')"><code style="user-select:all;">{{ $tg?->peer_id }}</code></x-tbe::fact>
                <x-tbe::fact :label="__('tbe-user-management::web.phone')" :value="$tg?->tel" />
                <x-tbe::fact :label="__('tbe-user-management::web.reachability')">
                    <x-filament::badge :color="$this->reachabilityColor()">{{ $this->reachability() }}</x-filament::badge>
                </x-tbe::fact>
                <x-tbe::fact :label="__('tbe-user-management::web.suspended')">
                    @if ($member->suspend)
                        <span title="{{ $calendar::format($member->suspended_at) }}">{{ __('tbe-user-management::web.suspended_since', ['time' => $member->suspended_at?->diffForHumans() ?? '']) }}</span>
                    @else
                        {{ __('tbe-user-management::web.no') }}
                    @endif
                </x-tbe::fact>
                <x-tbe::fact :label="__('tbe-user-management::bot_users.sorts.last_interaction')">
                    <span title="{{ $calendar::format($member->last_interaction) }}">{{ $member->last_interaction?->diffForHumans() ?? '—' }}</span>
                </x-tbe::fact>
                <x-tbe::fact :label="__('tbe-user-management::bot_users.sorts.created_at')">
                    <span title="{{ $calendar::format($member->created_at) }}">{{ $member->created_at?->diffForHumans() ?? '—' }}</span>
                </x-tbe::fact>
            </x-tbe::facts>
        </x-filament::section>

        @php($sections = collect($this->sections()))
        @php($cards = $sections->whereNotNull('summary'))
        @php($rest = $sections->whereNull('summary'))
        @if ($cards->isNotEmpty())
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr));gap:1rem;">
                @foreach ($cards as $card)
                    <x-tbe::stat-card
                        :label="$card['summary']->title"
                        :icon="$card['summary']->icon"
                        :url="$card['url']"
                        :value="is_array($card['summary']->value) ? $card['summary']->value : $card['summary']->valueText()"
                    />
                @endforeach
            </div>
        @endif

        @if ($rest->isNotEmpty())
            <x-filament::section>
                <x-slot name="heading">{{ __('tbe-user-management::web.more') }}</x-slot>
                <div style="display:grid;gap:.5rem;">
                    @foreach ($rest as $section)
                        @if ($section['html'] !== null)
                            <div style="line-height:1.7;">{!! $section['html'] !!}</div>
                        @elseif ($section['url'] !== null)
                            <x-filament::link :href="$section['url']" icon="heroicon-m-arrow-right">{{ $section['label'] }}</x-filament::link>
                        @else
                            <div>{{ $section['label'] }}</div>
                        @endif
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{ $this->table }}
    </div>
</x-filament-panels::page>
