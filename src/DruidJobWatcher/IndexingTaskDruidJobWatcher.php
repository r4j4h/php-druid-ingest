<?php

namespace PhpDruidIngest\DruidJobWatcher;

use DruidFamiliar\QueryExecutor\DruidNodeDruidQueryExecutor;
use Guzzle\Http\Exception\ClientErrorResponseException;
use PhpDruidIngest\QueryGenerator\IndexingTaskStatusQueryGenerator;
use PhpDruidIngest\QueryGenerator\SimpleIndexQueryGenerator;
use PhpDruidIngest\QueryParameters\IndexingTaskStatusQueryParameters;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;
use PhpDruidIngest\QueryResponse\IndexingTaskStatusQueryResponse;
use PhpDruidIngest\ResponseHandler\IndexingTaskStatusResponseHandler;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class IndexingTaskDruidJobWatcher extends BasicDruidJobWatcher
{
    /**
     * @var OutputInterface
     */
    protected $output;

    protected $druidIp;
    protected $druidPort;
    protected $protocol = 'http';


    protected $druidPath = '/druid/indexer/v1/task/';
    protected $druidPathAction = '/status';

    /**
     * The current number of watch attempts for the currently running job
     * @var int
     */
    protected $watchAttempts = 0;

    /**
     * Maximum number of times will attempt to check a status before giving up
     *
     * @var int
     */
    public $maximumWatchAttempts = 12;

    /**
     * Delay between task status polling checks.
     *
     * @var int seconds waited between polling
     */
    public $watchAttemptDelay = 10;

    public function __construct() {
        $this->output = new NullOutput();
    }

    /**
     * Begin watching given job id.
     *
     * Calls either onJobCompleted or onJobFailed unless stopWatchingJob is called first.
     * If given job is already completed or failed, the appropriate callback will be called.
     *
     * @param string $jobId
     * @return mixed
     */
    public function watchJob($jobId)
    {
        parent::watchJob($jobId);

        $this->watchAttempts++;

        $executor = $this->constructQueryExecutor($jobId);
        $queryGenerator = $this->constructQueryGenerator();
        $queryParameters = $this->constructQueryParameters();
        $responseHandler = $this->constructQueryResponseHandler();

        $queryParameters->setTaskId( $jobId );

        /**
         * @var IndexingTaskStatusQueryResponse $response
         */
        try {
            $response = $executor->executeQuery($queryGenerator, $queryParameters, $responseHandler);
        }
        catch ( ClientErrorResponseException $e ) {
            // 4xx codes
            throw new \Exception('404 encountered. Task id is most likely wrong. Druid connection info could be culprit.');
        }

        return $this->handleTaskStatus( $response );
    }

    /**
     * @param IndexingTaskStatusQueryResponse $response
     * @return bool
     * @throws \Exception
     */
    public function handleTaskStatus(IndexingTaskStatusQueryResponse $response)
    {
        $taskStatus = $response->getStatus();

        if ( $taskStatus === 'RUNNING' )
        {
            if ( $this->watchAttempts < $this->maximumWatchAttempts )
            {
                $this->onJobPending();

                $jobId = $response->getTask();
                $this->doWait($this->watchAttemptDelay);
                return $this->watchJob($jobId);
            }
            else
            {
                $this->stopWatchingJob();
                $this->onJobFailed();
                return false;
            }
        }
        else if  ( $taskStatus === 'SUCCESS' )
        {
            $this->stopWatchingJob();
            $this->onJobCompleted();
            return true;
        }
        else if  ( $taskStatus === 'FAILED' )
        {
            $this->stopWatchingJob();
            $this->onJobFailed();
            return false;

        } else {
            throw new \Exception('Unexpected task status encountered: "' . $taskStatus . '"');
        }

        return false;
    }

    /**
     * If a job watching can be cancelled, call this to cancel.
     *
     * @return mixed
     */
    protected function stopWatchingJob()
    {
        $stoppedWatching = parent::stopWatchingJob();
        if ( $stoppedWatching ) {
            $this->resetWatchAttempts();
        }
        return $stoppedWatching;
    }

    /**
     * Wait to retry watching for a pending job.
     *
     * @param $jobId
     * @throws \Exception
     */
    protected function doWait($waitDelay)
    {
        sleep($waitDelay);
    }

    /**
     * @return int
     */
    public function getWatchAttempts()
    {
        return $this->watchAttempts;
    }

    /**
     * Reset watch attempts.
     */
    protected function resetWatchAttempts()
    {
        $this->watchAttempts = 0;
    }

    /**
     * @param $jobId
     * @return DruidNodeDruidQueryExecutor
     * @throws \Exception
     */
    protected function constructQueryExecutor($jobId)
    {
        if ( !$this->druidIp ) {
            throw new \Exception('No druid ip configured.');
        }
        if ( !$this->druidPort ) {
            throw new \Exception('No druid port configured.');
        }

        $url = $this->druidPath . $jobId . $this->druidPathAction;
        return new DruidNodeDruidQueryExecutor($this->druidIp, $this->druidPort, $url, $this->protocol, 'GET');
    }

    /**
     * @return IndexingTaskStatusQueryGenerator
     */
    protected function constructQueryGenerator()
    {
        return new IndexingTaskStatusQueryGenerator();
    }

    /**
     * @return IndexingTaskStatusQueryParameters
     */
    protected function constructQueryParameters()
    {
        return new IndexingTaskStatusQueryParameters();
    }

    /**
     * @return IndexingTaskStatusResponseHandler
     */
    protected function constructQueryResponseHandler()
    {
        return new IndexingTaskStatusResponseHandler();
    }

    /**
     * @return mixed
     */
    public function getDruidIp()
    {
        return $this->druidIp;
    }

    /**
     * @param mixed $druidIp
     */
    public function setDruidIp($druidIp)
    {
        $this->druidIp = $druidIp;
    }

    /**
     * @return mixed
     */
    public function getDruidPort()
    {
        return $this->druidPort;
    }

    /**
     * @param mixed $druidPort
     */
    public function setDruidPort($druidPort)
    {
        $this->druidPort = $druidPort;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * @param string $protocol
     */
    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
    }


    /**
     * Called when a job's status moves to Completed state.
     *
     * @return mixed
     */
    protected function onJobCompleted()
    {
        $this->output->writeln('Druid says the job was successful! :)');
    }

    /**
     * Called when a job's status moves to a Failed state.
     *
     * @return mixed
     */
    protected function onJobFailed()
    {
        $this->output->writeln('Druid says the job failed. :(');
    }

    /**
     * Called when a job's status is in a Running/Pending state.
     *
     * @return mixed
     */
    protected function onJobPending()
    {
        $this->output->writeln('Druid says the job is still running.');
        $this->output->writeln('Trying again in ' . $this->watchAttemptDelay . ' seconds.');
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }
}