<?php

namespace Tests\Feature\Security;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The shortcuts used while developing (log in as admin #1, dump the users table...) must never be
 * reachable: a visitor who guesses the URL would become an administrator.
 */
class NoDebugRoutesTest extends TestCase
{
    public function test_no_debug_shortcut_is_registered(): void
    {
        $uris = collect(Route::getRoutes()->getRoutes())->map(fn ($route) => $route->uri());

        foreach (['auto-login', 'test-dt', 'admin/auto-login', 'admin/test-dt', 'admin/test-broadcast'] as $uri) {
            $this->assertFalse($uris->contains($uri), "debug route /{$uri} is still registered");
        }
    }

    public function test_a_guest_hitting_the_old_shortcuts_stays_a_guest(): void
    {
        foreach (['/auto-login', '/test-dt', '/admin/auto-login', '/admin/test-dt'] as $url) {
            $this->get($url)->assertNotFound();
            $this->assertFalse(Auth::guard('admin')->check(), "{$url} logged the visitor in");
        }
    }
}
