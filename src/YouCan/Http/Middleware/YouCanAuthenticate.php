<?php

namespace YouCan\Http\Middleware;

use YouCan\Models\Session;
use YouCan\Services\CurrentAuthSession;
use YouCan\Services\OAuthService;
use YouCan\Services\SessionService;
use Closure;
use Illuminate\Http\Request;

class YouCanAuthenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    public function handle(Request $request, Closure $next)
    {
        // check if oauth callback route, let pass
        // Check JWT in header and extract params and validate session ID
        // Check if GET params and validate session ID
        // return 401 if none of the above

        /** @var OAuthService $oauthService */
        $oauthService = app(OAuthService::class);

        /** @var SessionService $sessionService */
        $sessionService = app(SessionService::class);

        if ($this->isWhitlistedRoute($request)) {
            return $next($request);
        }

        $hasJWTAuthHeader = $request->hasHeader('Authorization');
        $hasRequestParams = $request->has('seller', 'store', 'session', 'timestamp', 'hmac');
        if (!$hasJWTAuthHeader && !$hasRequestParams) {
            return redirect()->route('youcan.qantra-bounce', [
                'bounce_to' => $request->fullUrl()
            ]);
        }

        if ($hasJWTAuthHeader) {
            $payload = $oauthService->decodeJWTSession($request->get('Authorization'));
            if (is_null($payload)) {
                return response('invalid JWT session', 401);
            }

            $sessionId = $payload['sid'];
            $sellerId = $payload['sub'];
            $storeId = $payload['str'];
        }

        if ($hasRequestParams) {
            $hmac = $request->get('hmac');
            $sessionId = $request->get('session');
            $sellerId = $request->get('seller');
            $storeId = $request->get('store');

            $isValidHmac = $oauthService->isEmbedHmacValid($hmac, $request->except(['hmac']));
            if (!$isValidHmac) {
                return response('invalid hmac', 401);
            }
        }

        $session = $sessionService->findSession($sessionId);

        // app not installed
        if (!$session instanceof Session) {
            $session = $sessionService->createSession([
                Session::SESSION_ID => $sessionId,
                Session::STORE_ID => $storeId,
                Session::SELLER_ID => $sellerId,
            ]);
        }

        if ($session->isAccessTokenExpired()) {
            return redirect()->route('youcan.refresh_token', ['state' => $sessionId]);
        }

        CurrentAuthSession::setCurrentSession($session);

        return $next($request);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Routing\Route|object|string|null
     */
    public function isWhitlistedRoute(Request $request): bool
    {
        return $request->routeIs('youcan.callback');
    }
}
