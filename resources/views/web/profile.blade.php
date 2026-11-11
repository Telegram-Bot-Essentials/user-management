<x-filament-panels::page>
    @php($member = $this->member())
    @php($tg = $member->telegramUser)
    <div class="grid gap-6">
        <x-filament::section>
            <dl style="display:grid;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr));gap:1rem;">
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::web.username') }}</dt><dd style="font-weight:600;">{{ $tg?->username ? '@'.$tg->username : '—' }}</dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::web.peer_id') }}</dt><dd style="font-weight:600;"><code>{{ $tg?->peer_id }}</code></dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::web.phone') }}</dt><dd style="font-weight:600;">{{ $tg?->tel ?: '—' }}</dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::web.reachability') }}</dt><dd><x-filament::badge :color="$this->reachabilityColor()">{{ $this->reachability() }}</x-filament::badge></dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::web.suspended') }}</dt><dd style="font-weight:600;">{{ $member->suspend ? __('tbe-user-management::web.suspended_since', ['time' => $member->suspended_at?->diffForHumans() ?? '']) : __('tbe-user-management::web.no') }}</dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::bot_users.sorts.last_interaction') }}</dt><dd style="font-weight:600;">{{ $member->last_interaction?->diffForHumans() ?? '—' }}</dd></div>
                <div><dt style="font-size:.875rem;opacity:.7;">{{ __('tbe-user-management::bot_users.sorts.created_at') }}</dt><dd style="font-weight:600;">{{ $member->created_at?->diffForHumans() }}</dd></div>
            </dl>
        </x-filament::section>

        @php($sections = collect($this->sections()))
        @php($cards = $sections->whereNotNull('summary'))
        @php($rest = $sections->whereNull('summary'))
        @if ($cards->isNotEmpty())
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(13rem,1fr));gap:1rem;">
                @foreach ($cards as $card)
                    @php($summary = $card['summary'])
                    @if ($card['url'] !== null)
                        <a href="{{ $card['url'] }}" style="display:block;color:inherit;text-decoration:none;">
                    @endif
                    <x-filament::section compact>
                        <div style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;opacity:.7;">
                            @if ($summary->icon !== null)
                                <x-filament::icon :icon="$summary->icon" style="width:1.25rem;height:1.25rem;" />
                            @endif
                            <span>{{ $summary->title }}</span>
                            @if ($card['url'] !== null)
                                <x-filament::icon icon="heroicon-m-arrow-up-right" style="width:1rem;height:1rem;margin-inline-start:auto;" />
                            @endif
                        </div>
                        <div style="margin-top:.5rem;font-size:1.5rem;font-weight:600;line-height:1.3;">
                            @if (is_array($summary->value))
                                <div style="display:flex;flex-wrap:wrap;gap:.375rem;font-size:1rem;">
                                    @foreach ($summary->value as $name)
                                        <x-filament::badge>{{ $name }}</x-filament::badge>
                                    @endforeach
                                </div>
                            @else
                                {{ $summary->valueText() !== '' ? $summary->valueText() : '—' }}
                            @endif
                        </div>
                    </x-filament::section>
                    @if ($card['url'] !== null)
                        </a>
                    @endif
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
