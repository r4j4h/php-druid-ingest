<?php

namespace PhpDruidIngest\ResponseHandler;

use Guzzle\Http\Message\Response;
use PHPUnit_Framework_TestCase;

class IndexingTaskResponseHandlerTest extends PHPUnit_Framework_TestCase
{

    /** @var IndexingTaskResponseHandler The response object to test */
    protected $responseHandler;

    public function setup()
    {
        $this->responseHandler = new IndexingTaskResponseHandler();
    }

    public function tearDown()
    {
        unset($this->responseHandler);
    }


    public function testHandleResponse()
    {
        $fakeIndexingResponse = new Response(200, array(), '{"task":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z"}');

        $taskJobId = $this->responseHandler->handleResponse( $fakeIndexingResponse );

        $this->assertEquals('index_referral-visit-old-format_2014-09-24T20:53:45.446Z', $taskJobId);
    }

    public function testRejectsUnexpectedResponse()
    {
        $fakeIndexingResponse = new Response(200, array(), '{"no-task":"anywhere"}');

        $this->setExpectedException('Exception', 'Unexpected'); // TODO Upgrade to real exception

        $taskJobId = $this->responseHandler->handleResponse( $fakeIndexingResponse );
    }

    public function testRejectsMalformedResponse()
    {
        $fakeIndexingResponse = new Response(200, array(), 'i.am.not.json');

        $this->setExpectedException('Exception', 'JSON'); // TODO Upgrade to our exception (and not rely on Guzzle's)

        $taskJobId = $this->responseHandler->handleResponse( $fakeIndexingResponse );
    }

}
