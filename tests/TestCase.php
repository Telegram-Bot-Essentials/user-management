<?php

declare(strict_types=1);

namespace TelegramBotEssentials\UserManagement\Tests;

use TelegramBotEssentials\Essence\Testing\TestCase as EssenceTestCase;
use TelegramBotEssentials\UserManagement\TbeUserManagementServiceProvider;

abstract class TestCase extends EssenceTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            TbeUserManagementServiceProvider::class,
        ]);
    }
}
