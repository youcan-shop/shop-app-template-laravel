<?php

namespace YouCan\Models;

class OAuthConfig
{
    private string $clientId;
    private string $clientSecret;
    private string $clientScopes;
    private string $clientRedirect;

    public function __construct(string $clientId, string $clientSecret, string $clientScopes, string $clientRedirect)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->clientScopes = $clientScopes;
        $this->clientRedirect = $clientRedirect;
    }

    public static function createFromLaravelConfig(string $key = 'youcan'): self
    {
        return new self(
            config($key . '.api_key'),
            config($key . '.api_secret'),
            config($key . '.api_scopes'),
            config($key . '.api_redirect')
        );
    }

    public static function createFromEnv(): self
    {
        return new self(
            getenv('YOUCAN_API_KEY'),
            getenv('YOUCAN_API_SECRET'),
            getenv('YOUCAN_API_SCOPES'),
            getenv('YOUCAN_API_REDIRECT')
        );
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function getClientScopes(): string
    {
        return $this->clientScopes;
    }

    public function getClientRedirect(): string
    {
        return $this->clientRedirect;
    }
}
