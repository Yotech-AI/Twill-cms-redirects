<?php

namespace TwillRedirects\Twill\Capsules\Redirects\Database\Seeds;

use Illuminate\Database\Seeder;
use TwillRedirects\Twill\Capsules\Redirects\Models\Redirect;
use TwillRedirects\Twill\Capsules\Redirects\Repositories\RedirectRepository;

class RedirectSeeder extends Seeder
{
    public function run(): void
    {
        if (Redirect::count() > 0) {
            return;
        }

        app(RedirectRepository::class)->create([
            'title' => 'Redirects',
            'published' => false,
        ]);
    }
}
