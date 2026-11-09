<?php

namespace TelegramBotEssentials\UserManagement\Web\Manage;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use TelegramBotEssentials\Essence\Web\Filament\Concerns\GuardsResourcePage;
use TelegramBotEssentials\UserManagement\Telegram\Features\Admin\BotUsersFeature;

class ListBotUsers extends ListRecords
{
    use GuardsResourcePage;

    protected static string $resource = BotUserResource::class;

    /** The bot list's header: the user base, then what other packages report about it. */
    public function getHeader(): ?View
    {
        return view('tbe-user-management::web.stats', ['html' => self::telegramHtml(BotUsersFeature::statsText())]);
    }

    public function getHeading(): string|Htmlable
    {
        return BotUserResource::getPluralModelLabel();
    }

    /**
     * Telegram's HTML subset as safe page HTML: its tags only, rebuilt bare so
     * no attribute survives, http(s) links kept, line breaks kept.
     */
    public static function telegramHtml(string $text): string
    {
        $text = str_replace("\u{200E}", '', $text);
        $text = strip_tags($text, '<b><strong><i><em><u><s><code><pre><a>');
        $text = (string) preg_replace('/<(\/?)(b|strong|i|em|u|s|code|pre)\b[^>]*>/i', '<$1$2>', $text);
        $text = (string) preg_replace_callback(
            '/<a\b[^>]*?href="([^"]*)"[^>]*>(.*?)<\/a>/is',
            fn (array $m): string => preg_match('#^https?://#i', html_entity_decode($m[1])) === 1
                ? '<a href="'.e(html_entity_decode($m[1])).'" target="_blank" rel="noopener">'.$m[2].'</a>'
                : $m[2],
            $text,
        );
        // Any opening <a> left without a usable href.
        $text = (string) preg_replace('/<a\b(?![^>]*target="_blank")[^>]*>/i', '', $text);

        return nl2br(trim($text), false);
    }
}
