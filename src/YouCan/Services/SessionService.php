<?php

namespace YouCan\Services;

use YouCan\Models\Session;
use Illuminate\Support\Arr;

class SessionService
{
    public function findSession(string $id): ?Session
    {
        return Session::query()
            ->where(Session::SESSION_ID, $id)
            ->first();
    }

    public function updateSession(string $id, array $attributes): bool
    {
        return Session::query()
            ->where(Session::ID, $id)
            ->update(Arr::only($attributes, [
                Session::ACCESS_TOKEN,
                Session::REFRESH_TOKEN,
                Session::EXPIRES_AT,
                Session::SELLER_ID,
                Session::STORE_ID,
            ]));
    }

    public function createSession(array $attributes): Session
    {
        return Session::query()
            ->create(Arr::only($attributes, [
                Session::SESSION_ID,
                Session::ACCESS_TOKEN,
                Session::REFRESH_TOKEN,
                Session::EXPIRES_AT,
                Session::SELLER_ID,
                Session::STORE_ID,
            ]));
    }
}
