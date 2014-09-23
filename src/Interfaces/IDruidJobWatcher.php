<?php

namespace PhpDruidIngest\Interfaces;

/**
 * Interface IDruidJobWatcher is for things that can watch jobs, usually over a longer period of time
 * or over multiple HTTP requests.
 *
 * @package PhpDruidIngest\Interfaces
 */
interface IDruidJobWatcher
{

    /**
     * Begin watching given job id.
     *
     * Calls either onJobCompleted or onJobFailed unless stopWatchingJob is called first.
     * If given job is already completed or failed, the appropriate callback will be called.
     *
     * @param string $jobId
     * @return mixed
     */
    function watchJob($jobId);

}