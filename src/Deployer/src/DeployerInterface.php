<?php

declare(strict_types=1);

namespace PCIT\Deployer;

interface DeployerInterface
{
    public function deploy(): array;
}