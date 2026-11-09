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

        @if ($sections = $this->sections())
            <x-filament::section>
                <x-slot name="heading">{{ __('tbe-user-management::web.more') }}</x-slot>
                <div style="display:grid;gap:.5rem;">
                    @foreach ($sections as $section)
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
