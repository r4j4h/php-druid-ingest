<?php

namespace PhpDruidIngest\Fetcher;

use PHPUnit_Framework_TestCase;

class ReferralBatchFetcherTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        date_default_timezone_set('UTC');
    }

    public function stubMySqli()
    {
        $mockMysqli = $this->getMockBuilder('mysqli')
            ->disableOriginalConstructor()
            ->setMethods(array('query', 'close'))
            ->getMock();
        $mockResultSet = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->setMethods(array('fetch_array', 'close'))
            ->getMock();

        $mockMysqli->expects($this->once())->method('query')->willReturn( $mockResultSet );
        $mockMysqli->expects($this->once())->method('close');

        $mockedDbDataRow = array(
            'id' => 51,
            'some' => 'data'
        );
        $anotherMockedDbDataRow = array(
            'id' => 52,
            'some' => 'thing.else'
        );
        $mockResultSet->expects($this->at(0))->method('fetch_array')->willReturn( $mockedDbDataRow );
        $mockResultSet->expects($this->at(1))->method('fetch_array')->willReturn( $anotherMockedDbDataRow );
        $mockResultSet->expects($this->at(2))->method('fetch_array')->willReturn( false );
        $mockResultSet->expects($this->once())->method('close');

        return $mockMysqli;
    }

    public function testFetchReturnsProcessedRows()
    {
        $mockMysqli = $this->stubMySqli();

        $mockFetcher = $this->getMock('PhpDruidIngest\Fetcher\ReferralBatchFetcher', array('getMysqli', 'processRow'));
        $mockFetcher->expects($this->any())->method('getMysqli')->willReturn($mockMysqli);
        $mockFetcher->expects($this->any())->method('processRow')->willReturn('apple');

        /**
         * @var \PhpDruidIngest\Fetcher\ReferralBatchFetcher $mockFetcher
         */
        $mockFetcher->setMySqlCredentials('h', 'u', 'p', 'db');
        $mockFetcher->setTimeWindow('2014-01-01', '2014-01-02');
        $rows = $mockFetcher->fetch();

        $this->assertEquals(array('apple','apple'), $rows);
    }

    public function testFetchRequiresDatabaseConfig()
    {
        $mockFetcher = new ReferralBatchFetcher();

        $this->setExpectedException('RuntimeException', 'Database config');

        /**
         * @var \PhpDruidIngest\Fetcher\ReferralBatchFetcher $mockFetcher
         */
        $mockFetcher->setTimeWindow('2014-01-01', '2014-01-02');
        $mockFetcher->fetch();
    }
    public function testFetchRequiresIntervalConfig()
    {
        $mockFetcher = new ReferralBatchFetcher();

        $this->setExpectedException('RuntimeException', 'ingestion interval');

        /**
         * @var \PhpDruidIngest\Fetcher\ReferralBatchFetcher $mockFetcher
         */
        $mockFetcher->setMySqlCredentials('h', 'u', 'p', 'db');
        $mockFetcher->fetch();
    }

    public function testFetchCallsPrepareQuery()
    {
        $mockMysqli = $this->stubMySqli();

        $mockFetcher = $this->getMock('PhpDruidIngest\Fetcher\ReferralBatchFetcher', array('getMysqli', 'prepareQuery', 'processRow'));
        $mockFetcher->expects($this->any())->method('getMysqli')->willReturn($mockMysqli);
        $mockFetcher->expects($this->exactly(1))->method('prepareQuery');

        /**
         * @var \PhpDruidIngest\Fetcher\ReferralBatchFetcher $mockFetcher
         */
        $mockFetcher->setMySqlCredentials('h', 'u', 'p', 'db');
        $mockFetcher->setTimeWindow('2014-01-01', '2014-01-02');
        $mockFetcher->fetch();
    }

    public function testFetchCallsProcessRow()
    {
        $mockMysqli = $this->stubMySqli();

        $mockFetcher = $this->getMock('PhpDruidIngest\Fetcher\ReferralBatchFetcher', array('getMysqli', 'processRow'));
        $mockFetcher->expects($this->any())->method('getMysqli')->willReturn($mockMysqli);
        $mockFetcher->expects($this->exactly(2))->method('processRow');

        /**
         * @var \PhpDruidIngest\Fetcher\ReferralBatchFetcher $mockFetcher
         */
        $mockFetcher->setMySqlCredentials('h', 'u', 'p', 'db');
        $mockFetcher->setTimeWindow('2014-01-01', '2014-01-02');
        $mockFetcher->fetch();
    }

    public function testPrepareQuery()
    {
        $this->markTestIncomplete('Need to test query preparation');
    }

}