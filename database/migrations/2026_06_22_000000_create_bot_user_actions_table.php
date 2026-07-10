<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use TelegramBotEssentials\Essence\Models\Bot;
use TelegramBotEssentials\Essence\Models\BotUser;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bot_user_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Bot::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BotUser::class)->constrained()->cascadeOnDelete();
            $table->string('update_type', 128);
            $table->string('state')->nullable();
            $table->text('action');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_user_actions');
    }
};
