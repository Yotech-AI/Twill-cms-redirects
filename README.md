# Twill CMS Redirects

This package provides a simple redirect management capsule for [Twill](https://twillcms.com). It registers a singleton module where you can configure redirect rules that are applied by middleware on every request.

Requires **Twill 3.6+** (Laravel 9 – 13, PHP 8.1+).

## Installation

```bash
composer require yotech-ai/twill-cms-redirects
```

## Migration

Publish and run the package migration:

```bash
php artisan vendor:publish --tag="twill-cms-redirects-migrations"
php artisan migrate
```

## Usage

The capsule creates a singleton module called `redirects`. Administrators can define redirect rules through the Twill UI. Each rule contains a `from` URL, a `to` URL and the status code to use (301, 302, 307 or 308).

Requests are intercepted by the redirect middleware which performs the redirect if a rule matches. Matched redirects are logged at `debug` level. If the `redirects` table does not exist yet (fresh install before migrating), the middleware fails open and does nothing.

## Activation

Add the service provider to your `config/twill.php` if not automatically discovered:

```php
'providers' => [
    // ...
    TwillRedirects\TwillRedirectsServiceProvider::class,
],
```

Since Laravel 11, middleware is configured in `bootstrap/app.php` instead of `Http\Kernel`. Because redirect rules may apply to URLs that don't match any defined route, the middleware should run before route resolution. Prepend it to the global middleware stack within the `withMiddleware` closure:

```php
use TwillRedirects\Http\Middleware\RedirectMiddleware;
use Illuminate\Foundation\Configuration\Middleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(RedirectMiddleware::class);
})
```

## Seeder

A `RedirectSeeder` is included and will create the initial singleton record if none exists. Twill automatically runs this seeder when you visit the capsule for the first time.

## The shared Plugins page

This package ships a shared **Plugins** admin page for all Yotech Twill plugins. It adds a "Plugins" link to Twill's top navigation, in the right-hand group directly after **Media Library**. The page lists every installed Yotech plugin (name, description, version, package) and links through to each plugin's own admin screen.

There is no configuration: installing any Yotech plugin makes the page appear, installing more plugins adds rows to it.

### How it works

- Shared state lives in the Laravel container under two well-known keys: `yotech.twill-plugins.registry` (an `ArrayObject` of plugin manifests, plain arrays only) and `yotech.twill-plugins.page-owner` (the provider class that owns the page).
- The **first** plugin provider to register binds both keys, registers the `plugins` admin route/controller/view, and swaps Twill's navigation builder for a subclass that appends the "Plugins" link after Media Library. Twill hardcodes that navigation group without an extension API; the subclass only appends to the tree built by the parent.
- Every **later** plugin provider sees the existing bindings and only adds its own manifest to the registry — no duplicate pages or links, regardless of which plugin happens to boot first.
- If the host application binds its own `TwillNavigation` singleton, the swap is skipped and the page falls back to a regular link in the left navigation group, so it always stays reachable.

### Building a new Yotech plugin

In your plugin package, extend the plugin service provider and describe your plugin:

```php
use TwillRedirects\PluginPage\TwillPluginServiceProvider;

class TwillSeoServiceProvider extends TwillPluginServiceProvider
{
    protected function twillPlugin(): array
    {
        return [
            'name' => 'SEO',
            'description' => 'Meta tags, sitemaps and structured data.',
            'package' => 'yotech-ai/twill-cms-seo',
            'route' => config('twill.admin_route_name_prefix', 'twill.') . 'seo',
            // Optional: 'url' => '...', 'icon' => '...', 'version' => '...'
        ];
    }
}
```

If your plugin has no Twill capsules, add `protected $autoRegisterCapsules = false;` to the provider.

Two ways to get the base class into your package:

1. **Depend on this package** (`composer require yotech-ai/twill-cms-redirects`) and extend `TwillRedirects\PluginPage\TwillPluginServiceProvider` directly.
2. **Vendor the support code**: copy the `src/PluginPage` directory into your package and adjust the namespace. Because the shared state uses container string keys and PHP built-ins only, differently-namespaced copies cooperate — whichever package boots first serves the page for all of them.

For a standalone shared package later (e.g. `yotech-ai/twill-plugin-support`), move `src/PluginPage` there unchanged and update the namespaces; both container keys are namespace-independent so mixed installs keep working during the transition.

`version` is auto-detected from Composer when `package` is set. `route` takes an admin route name; use `url` instead for external links.

## License

MIT
