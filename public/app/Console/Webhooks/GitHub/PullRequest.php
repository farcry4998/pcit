<?php

declare(strict_types=1);

namespace App\Console\Webhooks\GitHub;

use App\Build;
use App\Console\Webhooks\GetConfig;
use App\Console\Webhooks\Skip;
use App\GetAccessToken;
use PCIT\PCIT;

class PullRequest
{
    /**
     * Action.
     *
     * "assigned", "unassigned", "review_requested", "review_request_removed",
     * "labeled", "unlabeled", "opened", "synchronize", "edited", "closed", or "reopened"
     *
     * @param $json_content
     *
     * @return array|void
     *
     * @throws \Exception
     */
    public static function handle($json_content)
    {
        $array = \PCIT\Support\Webhooks\GitHub\PullRequest::handle($json_content);

        $action = $array['action'];

        if ('assigned' === $action) {
            self::assigned($array);

            return;
        }

        if ('labeled' === $action) {
            self::labeled($array);

            return;
        }

        [
            'installation_id' => $installation_id,
            'action' => $action,
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'commit_id' => $commit_id,
            'event_time' => $event_time,
            'commit_message' => $commit_message,
            'committer_username' => $committer_username,
            'committer_uid' => $committer_uid,
            'pull_request_number' => $pull_request_number,
            'branch' => $branch,
            'internal' => $internal,
            'pull_request_source' => $pull_request_source,
            'account' => $account,
        ] = $array;

        $subject = new Subject();

        $subject->register(new UpdateUserInfo($account, (int) $installation_id, (int) $rid, $repo_full_name));

        $config_array = $subject->register(new GetConfig($rid, $commit_id))->handle()->config_array;

        $config = json_encode($config_array);

        $last_insert_id = Build::insertPullRequest(
            $event_time, $action, $commit_id, $commit_message,
            (int) $committer_uid, $committer_username, $pull_request_number,
            $branch, $rid, $config, $internal, $pull_request_source
        );

        $subject->register(new Skip($commit_message, (int) $last_insert_id, $branch, $config))
            ->handle();

        if ('opened' !== $action) {
            return;
        }

        Build::updateBuildStatus((int) $last_insert_id, 'pending');

        $comment_body = <<<'EOF'
You can add label **merge**, when test is pass, I will merge this Pull_request auto


---

### PCIT configuration

:date: **Schedule**: No schedule defined.

:vertical_traffic_light: **Automerge**: Disabled by config. Please merge this manually once you are satisfied.

---

This Comment has been generated by [PCIT Bot](https://github.com/khs1994-php/pcit).

EOF;

        self::sendComment((int) $rid, $repo_full_name, $pull_request_number, $comment_body);
    }

    /**
     * @param int $rid
     * @param     $repo_full_name
     * @param     $pull_request_number
     * @param     $comment_body
     *
     * @throws \Exception
     */
    private static function sendComment(int $rid, $repo_full_name, $pull_request_number, $comment_body): void
    {
        (new PCIT(['github_access_token' => GetAccessToken::getGitHubAppAccessToken($rid)]))
            ->issue_comments
            ->create($repo_full_name, $pull_request_number, $comment_body, false);
    }

    /**
     * @param $array
     *
     * @throws \Exception
     */
    public static function assigned($array): void
    {
        [
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'pull_request_number' => $pull_request_number
        ] = $array;
        // 创建一条评论

        $comment_body = <<<'EOF'
You already assigned me, when test is pass, I will merge this Pull_request auto


---

This Comment has been generated by [PCIT Bot](https://github.com/khs1994-php/pcit).
EOF;

        self::sendComment($rid, $repo_full_name, $pull_request_number, $comment_body);
    }

    /**
     * @param $array
     *
     * @throws \Exception
     */
    public static function labeled($array): void
    {
        // 创建一条评论

        [
            'rid' => $rid,
            'repo_full_name' => $repo_full_name,
            'pull_request_number' => $pull_request_number,

        ] = $array;

        $comment_body = <<<'EOF'
You already add label **merge**, when test is pass, I will merge this Pull_request auto


---

This Comment has been generated by [PCIT Bot](https://github.com/khs1994-php/pcit).
EOF;

        self::sendComment((int) $rid, $repo_full_name, $pull_request_number, $comment_body);
    }
}
