<?php

namespace YouCan\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class YouCanCSPHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $cspDomains = "seller-area.youcan.shop seller-area.youcanshop.dev";

        if ($response->headers->has('Content-Security-Policy') === false) {
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self' $cspDomains;");

            return $response;
        }

        // Append the new domains to the frame-ancestors directive
        $cspValue = $response->headers->get('Content-Security-Policy');
        $updatedCSPValue = $this->appendCSPDomains($cspValue, $cspDomains);

        $response->headers->set('Content-Security-Policy', $updatedCSPValue);

        return $response;
    }

    protected function appendCSPDomains($cspValue, $domains)
    {
        // Check if frame-ancestors directive is present
        if (str_contains($cspValue, 'frame-ancestors')) {
            // Append the domains to the frame-ancestors directive
            return preg_replace(
                '/(frame-ancestors\s[^;]*);?/',
                "$1 $domains;",
                $cspValue
            );
        }

        // Add the frame-ancestors directive with the domains
        return trim($cspValue, " ;") . "; frame-ancestors 'self' $domains;";
    }
}
