<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSiteMaintenance
{
    /**
     * Shows the closure screen for the public marketing site when an admin
     * has enabled maintenance mode from Admin → Site Settings. Admin,
     * student, and teacher areas are untouched (registered separately).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $settings = SiteSetting::current();

        if ($settings->maintenance_mode) {
            return response()->view('site.maintenance', [
                'title' => $settings->translation->maintenance_title ?? null,
                'message' => $settings->translation->maintenance_message ?? null,
            ], 503);
        }

        return $next($request);
    }
}
