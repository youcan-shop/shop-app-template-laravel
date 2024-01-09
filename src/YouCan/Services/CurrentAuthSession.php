<?php

namespace YouCan\Services;

use YouCan\Models\Session;

/**
 * This is a global class to avoid fetching the current session multiple times.
 * The value is set inside the YouCanAuthenticate middleware
 */
class CurrentAuthSession
{
    private static ?Session $session = null;

    public static function getCurrentSession(): Session
    {
        if (is_null(self::$session)) {
            throw new \Exception('no session set');
        }

        return self::$session;
    }

    public static function setCurrentSession(Session $session): void
    {
        self::$session = $session;
    }
}
