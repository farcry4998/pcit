<?php

declare(strict_types=1);

namespace PCIT\Tests\Service\GitHubApp;

use PCIT\Tests\PCITTestCase;

class ClientTest extends PCITTestCase
{
    /**
     * @group dont-test
     *
     * @throws \Exception
     */
    public function test_getAccessToken(): void
    {
        $result = $this->getTest()->github_apps_installations->getAccessToken(
            255451);

        $this->assertStringStartsWith('v', $result);
    }
}
