<?php

namespace PhpDruidIngest\DruidJobWatcher;

use PhpDruidIngest\QueryResponse\IndexingTaskStatusQueryResponse;
use PHPUnit_Framework_TestCase;

class IndexingTaskDruidJobWatcherTest extends PHPUnit_Framework_TestCase
{
    public function getGenericQueryResponse()
    {
        $taskStatus = new IndexingTaskStatusQueryResponse();
        $taskStatus->setStatus('PENDING');
        $taskStatus->setDuration(1);
        $taskStatus->setId('task.id');
        $taskStatus->setTask('task.id');

        return $taskStatus;
    }

    public function getFakeQueryExecutor($statusToReturn, $host = 'druid.druid.net', $port = 1234, $url = 'some/url')
    {
        $mockQueryExecutor = $this->getMock(
            'DruidFamiliar\QueryExecutor\DruidNodeDruidQueryExecutor',
            array('executeQuery'),
            array($host, $port, $url, 'http', 'GET')
        );
        $mockQueryExecutor->expects($this->any())->method('executeQuery')->willReturn($statusToReturn);

        return $mockQueryExecutor;
    }

    public function testWatchJob()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('FAILED');

        $mockQueryExecutor = $this->getFakeQueryExecutor($taskStatus, 'druid.druid.net', 1234);

        $a = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('constructQueryExecutor')
        );
        $a->expects($this->once())->method('constructQueryExecutor')->willReturn($mockQueryExecutor);


        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $a
         */
        $this->assertEquals( 0, $a->getWatchAttempts());

        $a->setDruidPort(1234);
        $a->setDruidIp('druid.druid.net');

        $a->watchJob('a.cool-task-id');


        $this->assertEquals( 'druid.druid.net', $a->getDruidIp());
        $this->assertEquals( 1234, $a->getDruidPort());
        $this->assertEquals( 'http', $a->getProtocol());
    }

    public function testTracksWatchAttempts()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('FAILED');

        $mockQueryExecutor = $this->getFakeQueryExecutor($taskStatus);

        $a = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('constructQueryExecutor', 'handleTaskStatus')
        );
        $a->expects($this->once())->method('constructQueryExecutor')->willReturn($mockQueryExecutor);
        $a->expects($this->once())->method('handleTaskStatus')->willReturn(1);

        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $a
         */
        $this->assertEquals( 0, $a->getWatchAttempts());
        $a->watchJob('a.cool-task-id');
        $this->assertEquals( 1, $a->getWatchAttempts());

    }
    public function testResetsWatchAttemptsOnSuccess()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('SUCCESS');

        $mockQueryExecutor = $this->getFakeQueryExecutor($taskStatus);

        $a = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('constructQueryExecutor')
        );
        $a->expects($this->once())->method('constructQueryExecutor')->willReturn($mockQueryExecutor);

        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $a
         */
        $this->assertEquals( 0, $a->getWatchAttempts());
        $a->watchJob('a.cool-task-id');
        $this->assertEquals( 0, $a->getWatchAttempts());

    }
    public function testResetsWatchAttemptsOnFailure()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('FAILED');

        $mockQueryExecutor = $this->getFakeQueryExecutor($taskStatus);

        $a = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('constructQueryExecutor')
        );
        $a->expects($this->once())->method('constructQueryExecutor')->willReturn($mockQueryExecutor);

        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $a
         */
        $this->assertEquals( 0, $a->getWatchAttempts());
        $a->watchJob('a.cool-task-id');
        $this->assertEquals( 0, $a->getWatchAttempts());

    }

    public function testRetriesIfDruidTaskStatusIsPending()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('PENDING');

        $jobWatcher = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('watchJob', 'onJobCompleted', 'onJobFailed', 'doWait')
        );
        $jobWatcher->expects($this->exactly(1))->method('doWait')->willReturn(1);

        $jobWatcher->expects($this->exactly(1))->method('watchJob');
        $jobWatcher->expects($this->never())->method('onJobFailed');
        $jobWatcher->expects($this->never())->method('onJobCompleted');

        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $jobWatcher
         */
        $jobWatcher->handleTaskStatus($taskStatus);
    }

    public function testFailsIfRetriesHitsThresholdAndDruidTaskStatusIsPending()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('PENDING');

        $mockQueryExecutor = $this->getFakeQueryExecutor($taskStatus);

        $jobWatcher = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('constructQueryExecutor', 'onJobCompleted', 'onJobFailed', 'doWait')
        );
        $jobWatcher->expects($this->any())->method('constructQueryExecutor')->willReturn($mockQueryExecutor);

        $jobWatcher->expects($this->exactly(2))->method('doWait');

        $jobWatcher->expects($this->once())->method('onJobFailed');
        $jobWatcher->expects($this->never())->method('onJobCompleted');

        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $jobWatcher
         */
        $jobWatcher->maximumWatchAttempts = 3;

        $jobWatcher->watchJob('task.id');
    }

    public function testFailsIfDruidTaskStatusIsFailed()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('FAILED');

        $jobWatcher = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('onJobCompleted', 'onJobFailed')
        );

        $jobWatcher->expects($this->once())->method('onJobFailed');
        $jobWatcher->expects($this->never())->method('onJobCompleted');

        $jobWatcher->handleTaskStatus($taskStatus);
    }

    public function testSucceedsIfDruidTaskStatusIsSuccessful()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('SUCCESS');


        $jobWatcher = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('onJobCompleted', 'onJobFailed')
        );

        $jobWatcher->expects($this->once())->method('onJobCompleted');
        $jobWatcher->expects($this->never())->method('onJobFailed');

        $jobWatcher->handleTaskStatus($taskStatus);
    }

    public function testWatchJobPassesTaskStatusToHandleTaskStatus()
    {
        $taskStatus = $this->getGenericQueryResponse();
        $taskStatus->setStatus('PENDING');

        $mockQueryExecutor = $this->getFakeQueryExecutor($taskStatus);

        $jobWatcher = $this->getMock(
            'PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher',
            array('constructQueryExecutor', 'handleTaskStatus')
        );
        $jobWatcher->expects($this->any())->method('constructQueryExecutor')->willReturn($mockQueryExecutor);

        $jobWatcher->expects($this->once())->method('handleTaskStatus')->with($taskStatus);

        /**
         * @var \PhpDruidIngest\DruidJobWatcher\IndexingTaskDruidJobWatcher $jobWatcher
         */
        $jobWatcher->watchJob('a.cool-task-id');

    }

    public function testRequiresDruidConnectionInfo()
    {
        // TODO Test throws exception if watchJob called and not set ip or port
    }
}