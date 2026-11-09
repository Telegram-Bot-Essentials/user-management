<div class="fi-header" style="display:grid;gap:1rem;">
    <h1 class="fi-header-heading">{{ \TelegramBotEssentials\UserManagement\Web\Manage\BotUserResource::getPluralModelLabel() }}</h1>
    <x-filament::section collapsible persist-collapsed id="tbe-user-stats">
        <x-slot name="heading">{{ __('tbe-user-management::web.overview') }}</x-slot>
        <div style="line-height:1.7;font-size:.9rem;">{!! $html !!}</div>
    </x-filament::section>
</div>
