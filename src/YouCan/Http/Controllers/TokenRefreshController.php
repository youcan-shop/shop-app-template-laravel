<?php

namespace YouCan\Http\Controllers;

use App\Http\Controllers\Controller;
use YouCan\Services\OAuthService;
use Illuminate\Http\Request;

class TokenRefreshController extends Controller
{
    private OAuthService $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    public function __invoke(Request $request)
    {
        $authorizeEndpoint = $this->oauthService->getAuthorizeEndpoint($request->get('state'));

        return view('youcan::qantra-oauth-redirect', [
            'url' => $authorizeEndpoint
        ]);
    }
}
