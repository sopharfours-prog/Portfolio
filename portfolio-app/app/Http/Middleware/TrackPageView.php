<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * First-party, privacy-friendly analytics: stores the path and a daily salted
 * hash of IP + user agent (never the raw IP). Bots, previews and admins are skipped.
 */
class TrackPageView
{
    private const BOTS = '/bot|crawl|spider|slurp|facebookexternalhit|preview|lighthouse|headless|curl|wget/i';

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request, $response)) {
            try {
                PageView::create([
                    'path' => '/'.ltrim($request->path(), '/'),
                    'visitor' => hash_hmac('sha256', $request->ip().'|'.$request->userAgent().'|'.now()->toDateString(), config('app.key')),
                    'project_id' => $request->route('project')?->id,
                    'viewed_on' => now()->toDateString(),
                ]);
            } catch (\Throwable $e) {
                report($e); // analytics must never break the page
            }
        }

        return $response;
    }

    private function shouldTrack(Request $request, Response $response): bool
    {
        return $request->isMethod('GET')
            && $response->getStatusCode() === 200
            && ! $request->ajax()
            && ! $request->user()
            && $request->header('Purpose') !== 'prefetch'
            && ! preg_match(self::BOTS, (string) $request->userAgent());
    }
}
