<?php

declare(strict_types=1);

namespace App\Console\Webhooks\GitHub;

use PCIT\Support\DB;

class Status
{
    /**
     * @param $json_content
     *
     * @return string
     *
     * @throws \Exception
     */
    public static function handle($json_content)
    {
        $sql = <<<'EOF'
        INSERT INTO builds(
        
        git_type,event_type
        
        ) VALUES(?,?);
EOF;

        return DB::insert($sql, [
                'github', __FUNCTION__,
            ]
        );
    }
}
