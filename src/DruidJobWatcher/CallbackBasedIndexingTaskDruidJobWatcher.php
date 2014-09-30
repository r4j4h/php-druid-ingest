<?php

namespace PhpDruidIngest\DruidJobWatcher;

use Closure;
use DruidFamiliar\QueryExecutor\DruidNodeDruidQueryExecutor;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Symfony\Component\Console\Output\OutputInterface;

class CallbackBasedIndexingTaskDruidJobWatcher extends IndexingTaskDruidJobWatcher
{

    /**
     * @var Closure
     */
    protected $xonJobCompleted;

    /**
     * @var Closure
     */
    protected $xonJobFailed;

    /**
     * @var Closure
     */
    protected $xonJobPending;


    /**
     * Called when a job's status moves to Completed state.
     *
     * @return mixed
     */
    protected function onJobCompleted()
    {
        $fnc = $this->xonJobCompleted;
        if ( $fnc ) {
            $fnc($this);
        } else {
            return parent::onJobCompleted();
        }
    }

    /**
     * Called when a job's status moves to a Failed state.
     *
     * @return mixed
     */
    protected function onJobFailed()
    {
        $fnc = $this->xonJobFailed;
        if ( $fnc ) {
            $fnc($this);
        } else {
            return parent::onJobFailed();
        }
    }

    /**
     * Called when a job's status is in a Running/Pending state.
     *
     * @return mixed
     */
    protected function onJobPending()
    {
        $fnc = $this->xonJobPending;
        if ( $fnc ) {
            $fnc($this);
        } else {
            return parent::onJobPending();
        }
    }


    /**
     * @return Closure
     */
    public function getOnJobCompleted()
    {
        return $this->xonJobCompleted;
    }

    /**
     * @param Closure $onJobCompleted
     */
    public function setOnJobCompleted($onJobCompleted)
    {
        $this->xonJobCompleted = $onJobCompleted;
    }

    /**
     * @return Closure
     */
    public function getOnJobFailed()
    {
        return $this->xonJobFailed;
    }

    /**
     * @param Closure $onJobFailed
     */
    public function setOnJobFailed($onJobFailed)
    {
        $this->xonJobFailed = $onJobFailed;
    }

    /**
     * @return Closure
     */
    public function getOnJobPending()
    {
        return $this->xonJobPending;
    }

    /**
     * @param Closure $onJobPending
     */
    public function setOnJobPending($onJobPending)
    {
        $this->xonJobPending = $onJobPending;
    }
}