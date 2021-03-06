<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use PCIT\Support\CI;
use PCIT\Support\Env;

if ('cli' === \PHP_SAPI) {
    (new NunoMaduro\Collision\Provider())->register();
}

$app_env = CI::environment();

$env_file = $app_env ? '.env.'.$app_env : '.env';

file_exists(base_path().$env_file) && (Dotenv::create(base_path(), $env_file))->load();

date_default_timezone_set(env('CI_TZ', 'PRC'));

$debug = config('app.debug');

if ($debug) {
    $whoops = new \Whoops\Run();
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler());
    $whoops->register();

    CI::enableDebug();
}

$app = new \PCIT\Foundation\Application([]);

$app->singleton(\App\Http\Kernel::class, function ($app) {
    return new \App\Http\Kernel();
});

return $app;
