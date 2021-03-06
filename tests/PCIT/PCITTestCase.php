<?php

declare(strict_types=1);

namespace PCIT\Tests;

use App\Console\PCITDaemon\Migrate;
use App\User;
use Dotenv\Dotenv;
use Exception;
use PCIT\Foundation\Testing\TestCase as BaseTestCase;
use PCIT\PCIT;
use PCIT\Support\DB;

abstract class PCITTestCase extends BaseTestCase
{
    private static $test;

    /**
     * @param array  $config
     * @param string $git_type
     *
     * @return PCIT
     *
     * @throws Exception
     */
    public static function getTest(array $config = [], string $git_type = null)
    {
        if (!(self::$test instanceof PCIT)) {
            self::$test = new PCIT($config, $git_type ?? 'github');
        }

        return self::$test;
    }

    /**
     * @param string|null $name
     * @param array       $data
     * @param string      $dataName
     *
     * @throws Exception
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        if (file_exists(base_path().'.env.testing')) {
            (Dotenv::create(base_path(), '.env.testing'))->load();
        }

        ob_start();
        Migrate::all();
        ob_end_clean();

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @throws Exception
     */
    public function insertDB(): void
    {
        // User
        User::updateUserInfo(1, null, 'admin', 'khs1994@khs1994.com', null, false, 'githu');

        User::updateUserInfo(2, null, 'other', 'other@khs1994.com', null, false, 'github');

        User::updateUserInfo(3, null, 'three', 'three@khs1994.com', null, false, 'github');

        // repo
        $sql = <<<'EOF'
INSERT INTO repo VALUES(
null,'github',1,'khs1994-php','pcit','pcit-ce/pcit',1,1,?,null,'master'
),(
null,'github',2,'khs1994-php','other','khs1994-php/other',1,1,?,null,'master'
)
EOF;

        DB::insert($sql, ['["1"]', '["2"]']);

        // issues

        // env_vars

        // cron

        // caches

        // builds

        $sql = <<<'EOF'
INSERT INTO builds VALUES(
null,'github_app',1,'push','master',null,null,null,null,1,'commit message [skip ci]','admin',
'khs1994@khs1994.com','admin',?,null,null,null,'skip','[]',null,null,null,null,null,null
),(
null,'github_app',1,'push','master',null,null,null,null,2,'commit message2','admin',
'khs1994@khs1994.com','admin',?,null,null,null,'passed','[]',null,null,null,null,null,null
),(
null,'github_app',1,'push','master',null,null,null,null,3,'commit message3','admin',
'khs1994@khs1994.com','admin',?,null,null,null,'passed','[]',null,null,null,null,null,null
) ,(
null,'github_app',1,'push','master',null,null,null,null,4,'commit message4','admin',
'khs1994@khs1994.com','admin',?,null,null,null,'errored','[]',null,null,null,null,null,null
) ,(
null,'github_app',1,'push','master',null,null,null,null,5,'commit message5','admin',
'khs1994@khs1994.com','admin',?,null,null,null,'passed','[]',null,null,null,null,null,null
)
EOF;

        DB::insert($sql, [time(), time(), time(), time(), time()]);

        // api_token
    }
}
