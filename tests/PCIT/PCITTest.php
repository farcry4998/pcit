<?php

declare(strict_types=1);

namespace PCIT\Tests;

use Exception;
use PCIT\Support\Cache;
use PCIT\Support\DB;

class PCITTest extends PCITTestCase
{
    /**
     * @return \PCIT\PCIT
     *
     * @throws Exception
     */
    public function example()
    {
        return $this->getTest();
    }

    /**
     * @throws Exception
     */
    public function testCache(): void
    {
        $redis = Cache::store();

        $result = $redis->set('k', 1);

        $this->assertEquals(1, $result);
    }

    /**
     * @throws Exception
     */
    public function testDB(): void
    {
        $result = DB::statement('select 1');

        $this->assertEquals(0, $result);
    }
}
