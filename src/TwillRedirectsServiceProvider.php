<?php

namespace TwillRedirects;

use A17\Twill\Facades\TwillCapsules;
use A17\Twill\Facades\TwillNavigation;
use A17\Twill\View\Components\Navigation\NavigationLink;
use TwillRedirects\PluginPage\TwillPluginServiceProvider;

class TwillRedirectsServiceProvider extends TwillPluginServiceProvider
{
    /**
     * Same as the parent's registerCapsule, but with the capsule's automatic
     * navigation OFF: Twill would otherwise add a main-nav "Redirects" entry
     * for the singleton, and this package's home is the shared Addons page
     * (plus the opt-in link in registerNavigation()).
     */
    protected function registerCapsule(string $name): void
    {
        $namespace = $this->getCapsuleNamespace() . '\\Twill\\Capsules\\' . $name;

        $dir = $this->getPackageDirectory() . DIRECTORY_SEPARATOR .
            'src' . DIRECTORY_SEPARATOR .
            'Twill' . DIRECTORY_SEPARATOR .
            'Capsules' . DIRECTORY_SEPARATOR . $name;

        TwillCapsules::registerPackageCapsule(
            $name,
            $namespace,
            $dir,
            automaticNavigation: false,
        );
    }

    public function register(): void
    {
        // Binds the shared Addons-page registry/page-owner container keys.
        parent::register();

        $this->mergeConfigFrom(__DIR__ . '/../config/twill-redirects.php', 'twill-redirects');
    }

    public function boot(): void
    {
        parent::boot();

        $migrationPath = __DIR__ . '/Twill/Capsules/Redirects/Database/migrations';
        $this->loadMigrationsFrom($migrationPath);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $migrationPath => database_path('migrations'),
            ], 'twill-cms-redirects-migrations');

            $this->publishes([
                __DIR__ . '/../config/twill-redirects.php' => config_path('twill-redirects.php'),
            ], 'twill-cms-redirects-config');
        }

        $this->registerNavigation();
    }

    /**
     * Off by default — see config/twill-redirects.php. A host whose editors
     * manage redirects constantly can set twill-redirects.ui.navigation_link
     * to true and get a top-level entry back.
     */
    protected function registerNavigation(): void
    {
        if (! config('twill-redirects.ui.navigation_link', false)) {
            return;
        }

        // Never bind A17\Twill\TwillNavigation ourselves — the plugin-page
        // base class owns that swap (first plugin to register wins the page).
        TwillNavigation::addLink(
            NavigationLink::make()
                ->title('Redirects')
                ->forRoute(config('twill.admin_route_name_prefix', 'twill.') . 'redirect')
        );
    }

    protected function twillPlugin(): array
    {
        return [
            'name' => 'Redirects',
            'description' => 'Manage URL redirects that are applied by middleware on every request.',
            'package' => 'yotech-ai/twill-cms-redirects',
            'route' => config('twill.admin_route_name_prefix', 'twill.') . 'redirect',
        ];
    }
}
