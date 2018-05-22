<?php

declare(strict_types=1);

namespace KhsCI\Support;

use Firebase\JWT\JWT as JWTService;

class JWT
{
    /**
     * @param string $private_key_path
     * @param int    $iss
     *
     * @return string
     *
     * @see https://developer.github.com/apps/building-github-apps/authentication-options-for-github-apps/#authenticating-as-a-github-app
     */
    public static function getJWT(string $private_key_path, int $iss)
    {
        $privateKey = file_get_contents($private_key_path);

        $token = [
            'iss' => $iss,
            'iat' => time(),
            'exp' => time() + 10 * 60,
        ];

        $jwt = JWTService::encode($token, $privateKey, 'RS256');

        return $jwt;
    }

    public static function encode(string $privateKey, string $git_type, string $username, string $uid)
    {
        $privateKey = file_get_contents($privateKey);

        $ci_host = Env::get('CI_HOST');

        $token = [
            'iss' => $ci_host,
            'iat' => time(),
            'exp' => time() + 60 * 10,
            'aud' => $ci_host,
            'username' => $username,
            'git_type' => $git_type,
            'uid' => $uid,
        ];

        return JWTService::encode($token, $privateKey, 'RS256');
    }

    public static function decode(string $jwt): void
    {
        $obj = JWTService::decode($jwt, $privateKey, 'RS256');

        var_dump($obj);
    }
}
