<?php

declare(strict_types=1);

namespace App\Http\Controllers\Builds;

use App\Build;
use App\Job;
use App\Notifications\GitHubChecksConclusion\Cancelled;
use PCIT\PCIT;
use PCIT\Support\DB;

class JobController
{
    /**
     * @param $build_key_id
     *
     * @return array
     *
     * @throws \Exception
     */
    public function list($build_key_id)
    {
        return Job::getByBuildKeyID((int) $build_key_id);
    }

    /**
     * @param $job_id
     *
     * @return array|int
     *
     * @throws \Exception
     */
    public function find($job_id)
    {
        return Job::find((int) $job_id);
    }

    /**
     * @param $job_id
     *
     * @throws \Exception
     */
    public function cancel($job_id): void
    {
        $job_id = (int) $job_id;

        $this->handleCancel($job_id);

        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        $this->updateBuildStatus((int) $job_id);
    }

    /**
     * @param int $job_id
     *
     * @throws \Exception
     */
    public function handleCancel(int $job_id): void
    {
        DB::beginTransaction();
        Job::updateBuildStatus($job_id, 'cancelled');
        Job::updateFinishedAt($job_id, time());
        $config = Build::getConfig(Job::getBuildKeyId($job_id));
        DB::commit();

        (new Cancelled($job_id, $config, null))->handle();
    }

    /**
     * @param $job_id
     *
     * @throws \Exception
     */
    public function restart($job_id): void
    {
        $job_id = (int) $job_id;
        $buildId = Job::getBuildKeyId($job_id);

        $build = (new \App\Console\Events\Build())->handle($buildId);

        app(PCIT::class)->build->handle($build, (int) $job_id);

        Job::updateBuildStatus($job_id, 'queued');

        $this->updateBuildStatus($job_id);
        Job::updateFinishedAt($job_id, 0);
        Job::updateStartAt($job_id, 0);
        Job::deleteLog($job_id);
    }

    /**
     * 更新 job 的状态，同时更新 build 的状态
     *
     * @param int $job_id
     *
     * @throws \Exception
     */
    private function updateBuildStatus(int $job_id): void
    {
        $build_key_id = Job::getBuildKeyId($job_id);

        $status = Job::getBuildStatusByBuildKeyId($build_key_id);

        Build::updateBuildStatus($build_key_id, $status);
    }
}
