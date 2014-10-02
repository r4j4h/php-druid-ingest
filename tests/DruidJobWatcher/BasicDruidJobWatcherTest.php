<?php

namespace PhpDruidIngest\DruidJobWatcher;

use PHPUnit_Framework_TestCase;

class BasicDruidJobWatcherTest extends PHPUnit_Framework_TestCase
{

    public function testMaintainsJobBeingWatched()
    {
        $this->markTestIncomplete();
    }

    public function testCanWatchSameJob()
    {
        $this->markTestIncomplete();
    }

    public function testCanOnlyWatchOneJobAtATime()
    {
        $this->markTestIncomplete();
    }


    public function testRequiresDruidQueryExecutor()
    {
        $this->markTestIncomplete();
    }

    public function testCallsDruidStatusService()
    {
        $this->markTestIncomplete();
    }

    public function testFailsAfterConfiguredNumberOfRetries()
    {
        $this->markTestIncomplete();
    }

    public function testFailsAfterConfiguredTimeout()
    {
        $this->markTestIncomplete();
    }

    public function testReturnsTaskStatus()
    {
        $this->markTestIncomplete();
    }

    public function testCallsOnJobCompletedHookOnSuccess()
    {
        $this->markTestIncomplete();
    }

    public function testCallsOnJobFailedHookOnError()
    {
        $this->markTestIncomplete();
    }

    public function testOnlyCallsEitherOnJobCompletedAndOnJobFailed()
    {
        $this->markTestIncomplete();
    }

}