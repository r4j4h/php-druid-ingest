<?php

namespace PhpDruidIngest\ResponseHandler;

use Guzzle\Http\Message\Response;
use PhpDruidIngest\QueryResponse\IndexingTaskStatusQueryResponse;
use PHPUnit_Framework_TestCase;


class IndexingTaskStatusResponseHandlerTest extends PHPUnit_Framework_TestCase
{

    /** @var IndexingTaskResponseHandler The response object to test */
    protected $responseHandler;

    public function setup()
    {
        $this->responseHandler = new IndexingTaskStatusResponseHandler();
    }

    public function tearDown()
    {
        unset($this->responseHandler);
    }

    public function testHandleResponseReturnsIndexingTaskStatusQueryResponse()
    {
        $fakeIndexingResponse = new Response(200, array(), '{"status":{"id":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z","status":"FAILED","duration":40},"task":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z"}');

        /**
         * @var IndexingTaskStatusQueryResponse $response
         */
        $response = $this->responseHandler->handleResponse( $fakeIndexingResponse );

        $this->assertInstanceOf('PhpDruidIngest\QueryResponse\IndexingTaskStatusQueryResponse', $response);
    }

    public function testHandleResponse()
    {
        $fakeIndexingResponse = new Response(200, array(), '{"status":{"id":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z","status":"FAILED","duration":40},"task":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z"}');

        /**
         * @var IndexingTaskStatusQueryResponse $response
         */
        $response = $this->responseHandler->handleResponse( $fakeIndexingResponse );

        $this->assertEquals('index_referral-visit-old-format_2014-09-24T20:53:45.446Z', $response->getId());
        $this->assertEquals('FAILED', $response->getStatus());
        $this->assertEquals('40', $response->getDuration());
        $this->assertEquals('index_referral-visit-old-format_2014-09-24T20:53:45.446Z', $response->getTask());
    }

    public function testHandleResponseRequiresStatus()
    {
        $fakeIndexingResponse = new Response(200, array(), '{"status":{"id":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z","duration":40},"task":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z"}');

        $this->setExpectedException('\Exception', 'Unexpected response'); // TODO Upgrade to real exception

        /**
         * @var IndexingTaskStatusQueryResponse $response
         */
        $response = $this->responseHandler->handleResponse( $fakeIndexingResponse );

        $this->assertEquals('index_referral-visit-old-format_2014-09-24T20:53:45.446Z', $response->getStatus());
    }

    public function testHandleResponseRequiresDuration()
    {
        $fakeIndexingResponse = new Response(200, array(), '{"status":{"id":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z","status":"FAILED"},"task":"index_referral-visit-old-format_2014-09-24T20:53:45.446Z"}');

        $this->setExpectedException('\Exception', 'Unexpected response'); // TODO Upgrade to real exception

        /**
         * @var IndexingTaskStatusQueryResponse $response
         */
        $response = $this->responseHandler->handleResponse( $fakeIndexingResponse );

        $this->assertEquals('index_referral-visit-old-format_2014-09-24T20:53:45.446Z', $response->getStatus());
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