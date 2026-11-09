<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use TelegramBotEssentials\UserManagement\Tests\TestCase;
use TelegramBotEssentials\UserManagement\Tests\WebTestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(WebTestCase::class, RefreshDatabase::class)->in('Web');
