<?php

declare(strict_types=1);

namespace App\Http\Controllers\OAuth;

use App\User;
use Error;
use Exception;
use PCIT\PCIT;
use PCIT\Service\Coding\OAuth\Client as CodingClient;
use PCIT\Service\Gitee\OAuth\Client as GiteeClient;
use PCIT\Service\GitHub\OAuth\Client as GitHubClient;
use PCIT\Support\Response;
use PCIT\Support\Session;

abstract class Kernel
{
    /**
     * @var GitHubClient|CodingClient|GiteeClient
     */
    protected static $oauth;

    /**
     * @var string
     */
    protected static $git_type;

    /**
     * enable state.
     *
     * @var bool
     */
    protected $state = false;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $pcit = new PCIT([], static::$git_type);

        static::$oauth = $pcit->oauth;
    }

    /**
     * 获取登录的 URL.
     */
    public function getLoginUrl(): void
    {
        $git_type = static::$git_type;

        /*
         * logout -> unset access_token
         *
         * OAuth login -> get access_token and expire from Session | expire one day
         */
        if (Session::get($git_type.'.access_token') and Session::get($git_type.'.expire') > time()) {
            $username_from_session = Session::get($git_type.'.username');

            // 重定向到个人主页
            Response::redirect(implode('/', ['/profile', $git_type, $username_from_session]));
        }

        $state = session_create_id();

        Session::put($git_type.'.state', $state);

        $url = static::$oauth->getLoginUrl($state);

        // 重定向到登录 url
        Response::redirect($url);

        exit;
    }

    /**
     * 服务器重定向页面.
     *
     * @throws Exception
     */
    public function getAccessToken(): void
    {
        if ($this->state ?? false) {
            $state = Session::pull(static::$git_type.'.state');
            $this->getAccessTokenCommon($state);

            return;
        }

        $this->getAccessTokenCommon(null);
    }

    /**
     * @param string|null $state
     *
     * @throws Exception
     */
    public function getAccessTokenCommon(?string $state): void
    {
        $git_type = static::$git_type;

        $request = app('request');

        // $code = $_GET['code'] ?? false;
        $code = $request->query->get('code');

        if (!$code) {
            throw new Exception('code not found');
        }

        try {
            list($accessToken, $refreshToken) = static::$oauth->getAccessToken((string) $code, $state);

            $accessToken && Session::put($git_type.'.access_token', $accessToken);

            $pcit = new PCIT([$git_type.'_access_token' => $accessToken], $git_type);

            $userInfoArray = $pcit->user_basic_info->getUserInfo();
        } catch (Error $e) {
            throw new Exception($e->getMessage(), 500);
        }

        $uid = $userInfoArray['uid'];
        $name = $userInfoArray['name'];
        $pic = $userInfoArray['pic'];
        $email = $userInfoArray['email'];

        $this->handleRefreshToken((int) $uid, $refreshToken, $git_type);

        Session::put($git_type.'.uid', $uid);
        Session::put($git_type.'.username', $name);
        Session::put($git_type.'.pic', $pic);
        Session::put($git_type.'.email', $email);
        $remember_day = env('CI_REMEMBER_DAY', 10);
        Session::put($git_type.'.expire', time() + $remember_day * 24 * 60 * 60);

        Response::redirect(getenv('CI_HOST').'/profile/'.$git_type.'/'.$name);

        exit;
    }

    /**
     * @param int $uid
     * @param     $refreshToken
     * @param     $gitType
     *
     * @throws Exception
     */
    public function handleRefreshToken(int $uid, $refreshToken, $gitType): void
    {
        if (!$refreshToken) {
            return;
        }

        User::updateRefreshToken($uid, $refreshToken, $gitType);
    }
}
