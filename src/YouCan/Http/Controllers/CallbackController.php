<?php

namespace YouCan\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
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
            Session::ACCESS_TOKEN => $authData['access_token'],
            Session::REFRESH_TOKEN => $authData['refresh_token'],
            Session::EXPIRES_AT => Carbon::now()->addSeconds($authData['expires_in']),

        ];

        $session = $this->sessionService->findSession($sessionId);

        if (!$session instanceof Session) {
            $this->sessionService->createSession(array_merge(['session_id' => $sessionId], $attributes));
        } else {
            $this->sessionService->updateSession($session->getId(), $attributes);
        }

        return redirect('/');
    }
}
