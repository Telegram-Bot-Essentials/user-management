<?php

declare(strict_types=1);

namespace TelegramBotEssentials\UserManagement\Tests;

use TelegramBotEssentials\Essence\Testing\WebTestCase as EssenceWebTestCase;
use TelegramBotEssentials\UserManagement\TbeUserManagementServiceProvider;

abstract class WebTestCase extends EssenceWebTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            TbeUserManagementServiceProvider::class,
        ]);
    }
}
