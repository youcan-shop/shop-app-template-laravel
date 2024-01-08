<?php

namespace YouCan\Http\Controllers;

use App\Http\Controllers\Controller;
use YouCan\Models\Session;
use YouCan\Services\OAuthService;
use YouCan\Services\SessionService;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    private SessionService $sessionService;
    private OAuthService $oauthService;

    public function __construct(SessionService $sessionService, OAuthService $oauthService)
    {
        $this->sessionService = $sessionService;
        $this->oauthService = $oauthService;
    }

    public function __invoke(Request $request)
    {
        $code = $request->get('code');
        $state = $request->get('state');

        $sessionId = $this->oauthService->decryptSession($state);

        if ($sessionId === false) {
            return response('invalid session', 400);
        }

        try {
            $authData = $this->oauthService->fetchAccessToken($code);
        } catch (\Exception $e) {
            return response($e->getMessage(), 400);
        }

        $attributes = [
            'access_token' => $authData['access_token'],
            'refresh_token' => $authData['refresh_token'],
            'expires_at' => $authData['expires_in'],
        ];

        $session = $this->sessionService->findSession($sessionId);

        if (!$session instanceof Session) {
            $session = $this->sessionService->createSession(array_merge(['session_id' => $sessionId], $attributes));
        } else {
            $this->sessionService->updateSession($session->getId(), $attributes);
        }

        $this->oauthService->subscribeToResthook($session, 'order.create', route('youcan.webhook'));

        return redirect('/');
    }
}
