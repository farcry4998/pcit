<?php

declare(strict_types=1);

namespace KhsCI\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TencentAI\TencentAI;

class TencentAIProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple['tencent_ai'] = function ($app) {
            return TencentAI::tencentAI(
                (int) $app['config']['tencent_ai']['app_id'], $app['config']['tencent_ai']['app_key']
            );
        };
    }
}
