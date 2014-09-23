<?php

namespace PhpDruidIngest\ResponseHandler;

use PHPUnit_Framework_TestCase;

class IndexingTaskResponseHandlerTest extends PHPUnit_Framework_TestCase
{
    public function testStubMoreTests()
    {
        $this->markTestIncomplete('Need to stub tests');
    }

    public function testHandleResponse()
    {
        $taskJobId = new IndexingTaskResponseHandler();

        $fakeIndexingResponse = ''; // TODO

        $taskJobId = $taskJobId->handleResponse( $fakeIndexingResponse );

        $this->assertEquals('i-am-a-task-id', $taskJobId);
    }

}
