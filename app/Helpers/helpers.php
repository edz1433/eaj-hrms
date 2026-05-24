<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('guard')) {
    function guard()
    {
        if (Auth::guard('web')->check()) {
            return 'web';
        } elseif (Auth::guard('employee')->check()) {
            return 'employee';
        }
        return null;
    }
}

if (!function_exists('shortEncrypt')) {
    function shortEncrypt($string)
    {
        $key = 'fA7xB93kL0pTzWmQ';
        $cipher = 'AES-128-ECB';
        return rtrim(strtr(base64_encode(openssl_encrypt($string, $cipher, $key, 0)), '+/', '-_'), '=');
    }
}

if (!function_exists('shortDecrypt')) {
    function shortDecrypt($encrypted)
    {
        $key = 'fA7xB93kL0pTzWmQ';
        $cipher = 'AES-128-ECB';
        $encrypted = strtr($encrypted, '-_', '+/');
        return openssl_decrypt(base64_decode($encrypted), $cipher, $key, 0);
    }
}

if (!function_exists('resolveEmployeeRouteId')) {
    function resolveEmployeeRouteId($id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (is_numeric($id)) {
            return (int) $id;
        }

        $decrypted = shortDecrypt((string) $id);

        return is_numeric($decrypted) ? (int) $decrypted : null;
    }
}

if (!function_exists('pdsRouteEmployeeId')) {
    function pdsRouteEmployeeId($id = null, ?string $guard = null): int
    {
        $guard = $guard ?: guard();
        $empid = $id !== null
            ? resolveEmployeeRouteId($id)
            : Auth::guard($guard)->id();

        if (!$empid) {
            abort(404);
        }

        if (Auth::guard('employee')->check() && (int) $empid !== (int) Auth::guard('employee')->id()) {
            abort(403);
        }

        return (int) $empid;
    }
}




