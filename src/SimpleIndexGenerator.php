<?php

namespace PhpDruidIngest;

use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;

class SimpleIndexGenerator extends BaseIndexGenerator
{

    protected $baseIndexTemplate = <<<INDEXTEMPLATE
{
    "type" : "{INDEXTYPE}",
    "dataSource" : "{DATASOURCE}",
    "granularitySpec" : {
        "type" : "{GRANULARITYSPEC.TYPE}",
        "gran" : "{GRANULARITYSPEC.GRAN}",
        "intervals" : [ "{GRANULARITYSPEC.START}/{GRANULARITYSPEC.END}" ]
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
    public function generateIndex(IndexTaskQueryParameters $indexTaskParams) {

        // Generate Index
        $generatedIndex = $this->baseIndexTemplate;

        $generatedIndex = str_replace( '{INDEXTYPE}', $indexTaskParams->queryType, $generatedIndex );
        $generatedIndex = str_replace( '{DATASOURCE}', $indexTaskParams->dataSource, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.TYPE}', $indexTaskParams->granularityType, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.GRAN}', $indexTaskParams->granularity, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.START}', $indexTaskParams->intervalStart, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.END}', $indexTaskParams->intervalEnd, $generatedIndex );

        $generatedIndex = str_replace( '{FIREHOSE.TYPE}', 'local', $generatedIndex );
        $generatedIndex = str_replace( '{FIREHOSE.BASEDIR}', $indexTaskParams->baseDir, $generatedIndex );
        $generatedIndex = str_replace( '{FIREHOSE.FILTER}', $indexTaskParams->filePath, $generatedIndex );

        $generatedIndex = str_replace( '{FIREHOSE.FORMAT}', 'json', $generatedIndex );
        $generatedIndex = str_replace( '{TIME_DIMENSION}', $indexTaskParams->timeDimension, $generatedIndex );

        $generatedIndex = str_replace( '{NON_TIME_DIMENSIONS}', join('", "', $indexTaskParams->dimensions), $generatedIndex );

        $generatedIndex = str_replace( '{AGGREGATORS}', join(",", $indexTaskParams->aggregators), $generatedIndex );

        var_dump( $generatedIndex );

        return $generatedIndex;
    }

}