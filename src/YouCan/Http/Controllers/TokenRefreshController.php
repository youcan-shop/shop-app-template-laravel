<?php

namespace YouCan\Http\Controllers;

use App\Http\Controllers\Controller;
use YouCan\Models\Session;
use YouCan\Services\OAuthService;
use Illuminate\Http\Request;
use YouCan\Services\SessionService;

class TokenRefreshController extends Controller
{
    private OAuthService $oauthService;
    private SessionService $sessionService;

    public function __construct(OAuthService $oauthService, SessionService $sessionService)
    {
        $this->oauthService = $oauthService;
        $this->sessionService = $sessionService;
    }

    public function __invoke(Request $request)
    {
        $sessionId = $request->get('state');

        $session = $this->sessionService->findSession($sessionId);
        if ($session instanceof Session && $session->isAccessTokenValid()) {
            return redirect('/');
        }

        $authorizeEndpoint = $this->oauthService->getAuthorizeEndpoint($sessionId);

        return view('youcan::qantra-oauth-redirect', [
            'url' => $authorizeEndpoint
        ]);
    }
}
