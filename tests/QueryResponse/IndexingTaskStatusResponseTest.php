<?php

namespace PhpDruidIngest\QueryResponse;

use Guzzle\Http\Message\Response;
use PHPUnit_Framework_TestCase;


class IndexingTaskStatusQueryResponseTest extends PHPUnit_Framework_TestCase
{
    public function testSetDuration()
    {
        $a = new IndexingTaskStatusQueryResponse();
        $a->setDuration(600);
        $this->assertEquals(600, $a->getDuration());

        $a->setDuration(30);
        $this->assertEquals(30, $a->getDuration());
    }

    public function testSetStatus()
    {
        $a = new IndexingTaskStatusQueryResponse();
        $a->setStatus('FAILED');
        $this->assertEquals('FAILED', $a->getStatus());

        $a->setStatus('SUCCESS');
        $this->assertEquals('SUCCESS', $a->getStatus());
    }
}