<?php

namespace YouCan\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Session extends Model
{
    public const TABLE = 'youcan_sessions';
    public const ID = 'id';
    public const SESSION_ID = 'session_id';
    public const STORE_ID = 'store_id';
    public const SELLER_ID = 'seller_id';
    public const ACCESS_TOKEN = 'access_token';
    public const REFRESH_TOKEN = 'refresh_token';
    public const EXPIRES_AT = 'expires_at';

    protected $table = self::TABLE;
    protected $guarded = [];
    protected $casts = [
        self::EXPIRES_AT => 'datetime',
    ];

    public function getId(): string
    {
        return $this->getAttribute(self::ID);
    }

    public function getSessionId(): string
    {
        return $this->getAttribute(self::SESSION_ID);
    }

    public function getStoreId(): string
    {
        return $this->getAttribute(self::STORE_ID);
    }

    public function getSellerId(): string
    {
        return $this->getAttribute(self::SELLER_ID);
    }

    public function getAccessToken(): ?string
    {
        return $this->getAttribute(self::ACCESS_TOKEN);
    }

    public function getRefreshToken(): ?string
    {
        return $this->getAttribute(self::REFRESH_TOKEN);
    }

    public function isAccessTokenExpired(): bool
    {
        return is_null($this->getExpireAt()) || $this->getExpireAt()->lessThan(Carbon::now());
    }

    public function isAccessTokenValid(): bool
    {
        return $this->isAccessTokenExpired() === false;
    }

    public function getExpireAt(): ?Carbon
    {
        return $this->getAttribute(self::EXPIRES_AT);
    }
}
