<?php

namespace PhpDruidIngest\QueryGenerator;

use PhpDruidIngest\Abstracts\BaseIndexQueryGenerator;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;

class SimpleIndexQueryGenerator extends BaseIndexQueryGenerator
{

    protected $baseIndexTemplate = <<<INDEXTEMPLATE
{
    "type" : "{INDEXTYPE}",
    "dataSource" : "{DATASOURCE}",
    "granularitySpec" : {
        "type" : "{GRANULARITYSPEC.TYPE}",
        "gran" : "{GRANULARITYSPEC.GRAN}",
        "intervals" : [ "{GRANULARITYSPEC.INTERVALS}" ]
    },
    "aggregators": [{AGGREGATORS}],
    "firehose" : {
        "type" : "{FIREHOSE.TYPE}",
        "baseDir" : "{FIREHOSE.BASEDIR}",
        "filter" : "{FIREHOSE.FILTER}",
        "parser" : {
            "timestampSpec" : {
                "column" : "{TIME_DIMENSION}"
            },
            "data" : {
                "format" : "{FIREHOSE.FORMAT}",
                "dimensions" : [ "{NON_TIME_DIMENSIONS}" ]
            }

        }
    }
}
INDEXTEMPLATE;

    /**
     * (Optionally) transform the data for ingestion.
     *
     * @param $input
     * @return mixed $output
     */
    public function generateIndex( IndexTaskQueryParameters $indexTaskParams )
    {

        // Generate Index
        $generatedIndex = $this->baseIndexTemplate;

        $generatedIndex = str_replace( '{INDEXTYPE}', $indexTaskParams->queryType, $generatedIndex );
        $generatedIndex = str_replace( '{DATASOURCE}', $indexTaskParams->dataSource, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.TYPE}', $indexTaskParams->granularityType, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.GRAN}', $indexTaskParams->granularity, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.INTERVALS}', $indexTaskParams->intervals, $generatedIndex );

        $generatedIndex = str_replace( '{FIREHOSE.TYPE}', 'local', $generatedIndex );
        $generatedIndex = str_replace( '{FIREHOSE.BASEDIR}', $indexTaskParams->baseDir, $generatedIndex );
        $generatedIndex = str_replace( '{FIREHOSE.FILTER}', $indexTaskParams->filePath, $generatedIndex );

        $generatedIndex = str_replace( '{FIREHOSE.FORMAT}', 'json', $generatedIndex );
        $generatedIndex = str_replace( '{TIME_DIMENSION}', $indexTaskParams->timeDimension, $generatedIndex );

        $generatedIndex = str_replace( '{NON_TIME_DIMENSIONS}', join('", "', $indexTaskParams->dimensions), $generatedIndex );

        $generatedIndex = str_replace( '{AGGREGATORS}', join(",", $indexTaskParams->aggregators), $generatedIndex );

        var_dump( "Here's a generated index. I hope you like it! As follows:" );
        var_dump( $generatedIndex );

        return $generatedIndex;
    }

}