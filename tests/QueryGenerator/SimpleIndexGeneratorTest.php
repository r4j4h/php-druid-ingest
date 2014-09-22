<?php
namespace PhpDruidIngest\QueryGenerator;

use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;
use PHPUnit_Framework_TestCase;

class SimpleIndexGeneratorTest extends PHPUnit_Framework_TestCase
{
    private $mockDataSourceName = 'my-datasource';

    public function getMockIndexTaskQueryParameters()
    {
        $params = new IndexTaskQueryParameters();

        $params->intervalStart = '1981-01-01T4:20';
        $params->intervalEnd = '2012-03-01T3:00';
        $params->granularityType = 'uniform';
        $params->granularity = 'DAY';
        $params->dataSource = $this->mockDataSourceName;
        $params->format = 'json';
        $params->timeDimension = 'date_dim';
        $params->dimensions = array('one_dim', 'two_dim');

        $params->setFilePath('/another/file/path/to/a/file.bebop');
        $params->setAggregators(array(
            array( 'type' => 'count', 'name' => 'count' ),
            array( 'type' => 'longSum', 'name' => 'total_referral_count', 'fieldName' => 'referral_count' )
        ));

        return $params;
    }

    public function testGenerateIndexReturnsJSONString()
    {
        $generator = new SimpleIndexQueryGenerator();
        $params = $this->getMockIndexTaskQueryParameters();

        $index = $generator->generateIndex( $params );

        $this->assertJson( $index );
        return $index;
    }

    /**
     * @depends testGenerateIndexReturnsJSONString
     */
    public function testGenerateIndexHasIndexType($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );

        $this->assertArrayHasKey('type', $parsedIndex);
        $this->assertEquals( 'index', $parsedIndex['type'] );
    }

    /**
     * @depends testGenerateIndexReturnsJSONString
     */
    public function testGenerateIndexUsesDataSource($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );

        $this->assertArrayHasKey('type', $parsedIndex);

        $this->assertArrayHasKey('dataSource', $parsedIndex);
        $this->assertEquals( $this->mockDataSourceName, $parsedIndex['dataSource'] );
    }


    /**
     * @depends testGenerateIndexReturnsJSONString
     */
    public function testGenerateIndexDefinesGranularitySpec($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );
        $mockParams = $this->getMockIndexTaskQueryParameters();

        $this->assertArrayHasKey( 'granularitySpec', $parsedIndex );
        $this->assertArrayHasKey( 'type', $parsedIndex['granularitySpec'] );
        $this->assertArrayHasKey( 'gran', $parsedIndex['granularitySpec'] );
        $this->assertArrayHasKey( 'intervals', $parsedIndex['granularitySpec'] );

        $this->assertEquals( $mockParams->granularityType, $parsedIndex['granularitySpec']['type'] );
        $this->assertEquals( $mockParams->granularity, $parsedIndex['granularitySpec']['gran'] );
        $this->assertCount( 1, $parsedIndex['granularitySpec']['intervals'] );

        $expected = $mockParams->intervalStart . '/' . $mockParams->intervalEnd;
        $this->assertEquals($expected, $parsedIndex['granularitySpec']['intervals'][0] );
    }

    /**
     * @depends testGenerateIndexReturnsJSONString
     */
    public function testGenerateIndexUsesFirehose($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );
        $mockParams = $this->getMockIndexTaskQueryParameters();

        $this->assertArrayHasKey( 'firehose', $parsedIndex );
        $this->assertArrayHasKey( 'type', $parsedIndex['firehose'] );
        $this->assertEquals( 'local', $parsedIndex['firehose']['type'] );

        return $jsonString;
    }

    /**
     * @depends testGenerateIndexUsesFirehose
     */
    public function testGenerateIndexUsesFirehoseParserUsesFilePath($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );
        $mockParams = $this->getMockIndexTaskQueryParameters();

        // baseDir & filter should reflect path to prepared file
        $this->assertArrayHasKey( 'baseDir', $parsedIndex['firehose'] );
        $this->assertArrayHasKey( 'filter', $parsedIndex['firehose'] );
        $this->assertEquals( $mockParams->baseDir, $parsedIndex['firehose']['baseDir'] );
        $this->assertEquals( $mockParams->filePath, $parsedIndex['firehose']['filter'] );
    }

    /**
     * @depends testGenerateIndexUsesFirehose
     */
    public function testGenerateIndexUsesFirehoseParserUsesJsonFormat($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );

        // parser should include json format
        $this->assertArrayHasKey( 'parser', $parsedIndex['firehose'] );
        $this->assertArrayHasKey( 'data', $parsedIndex['firehose']['parser'] );
        $this->assertArrayHasKey( 'format', $parsedIndex['firehose']['parser']['data'] );
        $this->assertEquals( 'json', $parsedIndex['firehose']['parser']['data']['format'] );
    }

    /**
     * @depends testGenerateIndexUsesFirehose
     */
    public function testGenerateIndexUsesFirehoseParserIncludesNonTimeDimensions($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );
        $mockParams = $this->getMockIndexTaskQueryParameters();

        // parser should include all dimension data
        $this->assertArrayHasKey( 'dimensions', $parsedIndex['firehose']['parser']['data'] );
        $this->assertCount(count($mockParams->dimensions), $parsedIndex['firehose']['parser']['data']['dimensions']);
        $this->assertEquals($mockParams->dimensions, $parsedIndex['firehose']['parser']['data']['dimensions']);
        $this->assertContains( 'one_dim', $parsedIndex['firehose']['parser']['data']['dimensions'] );
        $this->assertContains( 'two_dim', $parsedIndex['firehose']['parser']['data']['dimensions'] );
    }

    /**
     * @depends testGenerateIndexUsesFirehose
     */
    public function testGenerateIndexUsesFirehoseParserUsesTimeDimension($jsonString)
    {
        $parsedIndex = json_decode( $jsonString, true );
        $mockParams = $this->getMockIndexTaskQueryParameters();

        $this->assertArrayHasKey( 'firehose', $parsedIndex );
        $this->assertArrayHasKey( 'parser', $parsedIndex['firehose'] );
        $this->assertArrayHasKey( 'timestampSpec', $parsedIndex['firehose']['parser'] );
        $this->assertArrayHasKey( 'column', $parsedIndex['firehose']['parser']['timestampSpec'] );
        $this->assertEquals( $mockParams->timeDimension, $parsedIndex['firehose']['parser']['timestampSpec']['column'] );
    }



/**
 * Example index task for reference
     *
curl -X 'POST' -H 'Content-Type:application/json' -d @referral_visit-data_indexing-task.json 0.0.0.0:8087/druid/indexer/v1/task
     *
{
    "type" : "index",
    "dataSource" : "referral-visit-test-data-w-facility-ids",
    "granularitySpec" : {
        "type" : "uniform",
        "gran" : "DAY",
        "intervals" : [ "2010/2020" ]
    },
    "aggregators" : [
        {
            "type" : "count",
            "name" : "count"
        },
        {
            "type": "longSum",
            "name": "total_referral_count",
            "fieldName": "referral_count"
         }
    ],
    "firehose" : {
        "type" : "local",
        "baseDir" : "/home/jhegman",
        "filter" : "all.json",
        "parser" : {
        "timestampSpec" : {
            "column" : "date"
            },
            "data" : {
            "format" : "json",
                "dimensions" : [ "group", "referral_id", "referral_name", "referral_count", "patient_id", "facility_id" ]
            }

        }
    }
}

*/

}