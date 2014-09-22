<?php

namespace PhpDruidIngest\Interfaces;

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
    public function watchJob($jobId);


    /**
     * If a job watching can be cancelled, call this to cancel.
     *
     * @return mixed
     */
    public function stopWatchingJob();



    /**
     * Called when a job's status moves to Completed state.
     *
     * @return mixed
     */
    public function onJobCompleted();


    /**
     * Called when a job's status moves to a Failed state.
     *
     * @return mixed
     */
    public function onJobFailed();

}