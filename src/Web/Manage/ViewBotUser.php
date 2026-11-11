<?php

namespace TelegramBotEssentials\UserManagement\Web\Manage;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsResourcePage;
use TelegramBotEssentials\UserManagement\DTOs\SectionSummary;
use TelegramBotEssentials\UserManagement\DTOs\UserSection;
use TelegramBotEssentials\UserManagement\Enums\SectionMode;
use TelegramBotEssentials\UserManagement\Models\BotUserAction;
use TelegramBotEssentials\UserManagement\Services\Suspensions;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

/**
 * A member's profile: who they are, whether they can be reached, the sections
 * other packages add (wallet, invoices, affiliation...) and what they did.
 */
class ViewBotUser extends Page implements HasTable
{
    use GuardsResourcePage;
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = BotUserResource::class;

    protected string $view = 'tbe-user-management::web.profile';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function member(): BotUser
    {
        // Read again rather than refresh(): packages cache data on the model as
        // relations with no method behind them (tbe-access's roles), which
        // refresh() would try to reload.
        return BotUser::query()->with('telegramUser')->whereKey($this->getRecord()->getKey())->firstOrFail();
    }

    public function getTitle(): string
    {
        return BotUserResource::name($this->member());
    }

    public function reachability(): string
    {
        return BotUsersFeature::reachabilityLabel($this->member());
    }

    public function reachabilityColor(): string
    {
        return BotUserResource::reachabilityColor($this->member());
    }

    /**
     * What other packages show on the profile, in their order. A section with
     * a summary becomes a stat card; one with bot wording only keeps it.
     *
     * @return list<array{summary: SectionSummary|null, label: string, url: string|null, html: string|null}>
     */
    public function sections(): array
    {
        $member = $this->member();

        return array_values(userManagementSections()->getSectionsFor($member)
            ->map(function (UserSection $section) use ($member): array {
                $summary = $section->summaryFor($member);

                if ($summary !== null) {
                    return ['summary' => $summary, 'label' => $summary->title, 'url' => $section->webUrlFor($member), 'html' => null];
                }

                if ($section->mode === SectionMode::INLINE) {
                    $text = $section->contentFor($member)['text'] ?? null;

                    return ['summary' => null, 'label' => '', 'url' => null, 'html' => is_string($text) ? ListBotUsers::telegramHtml($text) : null];
                }

                return ['summary' => null, 'label' => $section->labelFor($member), 'url' => $section->webUrlFor($member), 'html' => null];
            })
            ->filter(fn (array $section): bool => $section['label'] !== '' || $section['html'] !== null)
            ->all());
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading((string) __('tbe-user-management::web.history'))
            ->query(BotUserAction::query()->where('bot_user_id', $this->member()->getKey()))
            ->columns(ActivityResource::actionColumns())
            ->defaultSort('id', 'desc');
    }

    /** @return array<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('suspend')
                ->label(fn (): string => (string) __($this->member()->suspend ? 'tbe-user-management::web.unsuspend' : 'tbe-user-management::web.suspend'))
                ->icon(fn () => $this->member()->suspend ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedNoSymbol)
                ->color(fn (): string => $this->member()->suspend ? 'success' : 'danger')
                ->requiresConfirmation()
                ->modalDescription(fn (): string => (string) __($this->member()->suspend ? 'tbe-user-management::web.unsuspend_confirm' : 'tbe-user-management::web.suspend_confirm'))
                ->action(function (Suspensions $suspensions): void {
                    $member = $this->member();
                    $suspensions->set($member, ! $member->suspend);
                    Notification::make()->title((string) __($member->suspend ? 'tbe-user-management::web.suspended_done' : 'tbe-user-management::web.unsuspended_done'))->success()->send();
                }),
        ];
    }
}
