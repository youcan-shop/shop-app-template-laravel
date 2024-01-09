<?php

namespace YouCan\Services;

use YouCan\Models\OAuthConfig;
use YouCan\Models\Session;
use Twilio\Jwt\JWT;
use GuzzleHttp\Client;
use YouCan\Shop\Sdk\Constants;
use YouCan\Shop\Sdk\Endpoints\ResthookEndpoint;
use YouCan\Shop\Sdk\Models\AccessToken;
use YouCan\Shop\Sdk\YouCan;

class OAuthService
{
    private Client $client;
    private bool $devMode;

    public function __construct(bool $devMode = true)
    {
        $this->devMode = $devMode;
        $this->initClient();
    }

    private function initClient(): void
    {
        $this->client = new Client([
            'base_uri' => $this->getApiEndpoint(),
        ]);
    }

    private function getApiEndpoint(): string
    {
        return $this->devMode ? 'https://api.youcanshop.dev/' : 'https://api.youcan.shop/';
    }

    public function decryptSession(string $encryptedSession): false|string
    {
        $key = OAuthConfig::createFromLaravelConfig()->getClientSecret();
        $method = 'AES-256-ECB';

        return openssl_decrypt($encryptedSession, $method, $key, OPENSSL_RAW_DATA);
    }

    /**
     * @param string $code
     * @return array ['access_token', 'refresh_token', 'expires']
     * @throws \Exception
     */
    public function fetchAccessToken(string $code): array
    {
        $config = OAuthConfig::createFromLaravelConfig();

        $response = $this->client->post('oauth/token', [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => [
                'code' => $code,
                'grant_type' => 'authorization_code',
                'client_id' => $config->getClientId(),
                'client_secret' => $config->getClientSecret(),
                'redirect_uri' => $config->getClientRedirect(),
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('invalid response from API');
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if ($data === false) {
            throw new \Exception('invalid response from API');
        }

        return $data;
    }

    public function getAuthorizeEndpoint(string $sessionId): string
    {
        $config = OAuthConfig::createFromLaravelConfig();

        $query = http_build_query([
            'prompt' => 'none',
            'response_type' => 'code',
            'scope[]' => $config->getClientScopes(),
            'client_id' => $config->getClientId(),
            'state' => $this->encryptSession($sessionId),
            'redirect_uri' => $config->getClientRedirect(),
        ]);

        $authorizeUrl = $this->devMode ? Constants::DEV_SELLER_AREA_URL : Constants::LIVE_SELLER_AREA_URL;

        return sprintf("%s/admin/oauth/authorize?%s", $authorizeUrl, $query);
    }

    public function encryptSession(string $sessionId): false|string
    {
        $key = OAuthConfig::createFromLaravelConfig()->getClientSecret();
        $method = 'AES-256-ECB';

        return openssl_encrypt($sessionId, $method, $key, OPENSSL_RAW_DATA);
    }

    public function subscribeToResthook(Session $session, string $event, string $url): array
    {
        $youCan = YouCan::instance()->setAccessToken(new AccessToken($session->getAccessToken()));

        return $youCan->request(ResthookEndpoint::subscribe($event, $url));
    }

    public function unsubscribeFromResthook(Session $session, string $id): array
    {
        $youCan = YouCan::instance()->setAccessToken(new AccessToken($session->getAccessToken()));

        return $youCan->request(ResthookEndpoint::unsubscribe($id));
    }

    public function decodeJWTSession(string $jwt): ?array
    {
        $config = OAuthConfig::createFromLaravelConfig();

        $decoded = JWT::decode($jwt, $config->getClientSecret(), false);

        return (array)$decoded;
    }

    /**
     * @param string $hmac
     * @param array $payload All request params except hmac
     * @return bool
     */
    public function isEmbedHmacValid(string $hmac, array $payload): bool
    {
        $config = OAuthConfig::createFromLaravelConfig();
        $query = http_build_query($payload);

        return hash_equals(hash_hmac('sha256', $query,$config->getClientSecret()), $hmac);
    }
}
