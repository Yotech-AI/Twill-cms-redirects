<?php

namespace TwillRedirects;

use TwillRedirects\PluginPage\TwillPluginServiceProvider;

class TwillRedirectsServiceProvider extends TwillPluginServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $migrationPath = __DIR__ . '/Twill/Capsules/Redirects/Database/migrations';
        $this->loadMigrationsFrom($migrationPath);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $migrationPath => database_path('migrations'),
            ], 'twill-cms-redirects-migrations');
        }
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
