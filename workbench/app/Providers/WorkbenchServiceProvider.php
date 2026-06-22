<?php

namespace Workbench\App\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Orchestra\Canvas\CanvasServiceProvider;
use Orchestra\Canvas\Core\PresetManager;
use Orchestra\Canvas\GeneratorPreset as CanvasGeneratorPreset;
use Orchestra\Workbench\GeneratorPreset as WorkbenchGeneratorPreset;

use function Orchestra\Sidekick\Filesystem\join_paths;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->callAfterResolving(PresetManager::class, function ($manager) {
            $manager->extend('workbench', static fn (Application $app) => new WorkbenchGeneratorPreset($app));

            if (! file_exists($canvasYaml = join_paths(__DIR__, '..', '..', '..', 'canvas.yaml'))) {
                $manager->setDefaultDriver('workbench');

                return;
            }

            if (! $this->app->bound('orchestra.canvas')) {
                $this->app->register(CanvasServiceProvider::class);
            }

            $manager->extend('canvas', static fn (Application $app) => new CanvasGeneratorPreset($app));
            $manager->setDefaultDriver('canvas');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
