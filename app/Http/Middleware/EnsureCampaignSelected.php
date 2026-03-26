<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCampaignSelected
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && ! session()->has('active_campaign_id')) {
            return redirect()->route('campaign.select');
        }

        return $next($request);
    }
}
