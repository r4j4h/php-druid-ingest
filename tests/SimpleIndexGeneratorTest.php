<?php

namespace PhpDruidIngest;

use PHPUnit_Framework_TestCase;

class SimpleIndexGeneratorTest extends PHPUnit_Framework_TestCase
{
    private $mockDataSourceName = 'my-datasource';

    public function getMockIndexTaskParameters()
    {
        $params = new IndexTaskParameters();

        $params->dataSource = $this->mockDataSourceName;
        $params->intervalStart = '2009-01-01T00:00';

        return $params;
    }

    public function testGenerateIndexReturnsJSONString()
    {
        $generator = new SimpleIndexGenerator();
        $params = $this->getMockIndexTaskParameters();


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

    public function testGenerateIndexUsesPathFromPreparer()
    {
        $this->markTestIncomplete();
    }

    public function testGenerateIndexUsesNonTimeDimensions()
    {
        $this->markTestIncomplete();
    }

    public function testGenerateIndexUsesTimeDimension()
    {
        // granularitySpec's intervals
        $this->markTestIncomplete();
    }

    public function testGenerateIndexUsesFirehose()
    {
        // type should be local
        // baseDir & filter should reflect path to prepared file
        // parser should include json format and all dimension data

        $this->markTestIncomplete();
    }


    public function testGenerateIndexDefinesGranularitySpec()
    {
        // granularitySpec
            // type
            // gran
            // intervals
        $this->markTestIncomplete();
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
     *
*/

}