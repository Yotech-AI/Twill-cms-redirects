<?php

namespace TwillRedirects\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use TwillRedirects\Twill\Capsules\Redirects\Models\Redirect;

class RedirectMiddleware
{
    /**
     * Check the requested URL against the configured redirect rules and
     * perform a redirect when a match is found. If no rule matches the
     * request, control is passed to the next middleware/route handler.
     */
    public function handle(Request $request, Closure $next)
    {
        $path = '/' . ltrim($request->path(), '/');

        foreach ($this->redirectRules() as $rule) {
            $from = $rule['from'] ?? null;
            $to = $rule['to'] ?? null;
            $status = (int) ($rule['statuscode'] ?? 302);

            if ($from && $to && $this->matches($path, $from)) {
                Log::debug('twill-cms-redirects: redirect matched', [
                    'from' => $path,
                    'to' => $to,
                    'status' => $status,
                ]);

                return redirect($to, $status);
            }
        }

        return $next($request);
    }

    /**
     * The middleware runs on every request, including before the package
     * migration has run on a fresh install — fail open in that case
     * instead of taking the whole site down.
     */
    private function redirectRules(): array
    {
        try {
            return Cache::rememberForever('twill_redirects', function () {
                return Redirect::query()->first()?->redirects ?? [];
            });
        } catch (QueryException) {
            return [];
        }
    }

    private function matches(string $path, string $pattern): bool
    {
        $pattern = '/' . ltrim($pattern, '/');

        if (str_ends_with($pattern, '*')) {
            $base = rtrim($pattern, '*');

            if (rtrim($path, '/') === rtrim($base, '/')) {
                return true;
            }

            return str_starts_with($path, $base);
        }

        return rtrim($path, '/') === rtrim($pattern, '/');
    }
}
