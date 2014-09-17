<?php

namespace PhpDruidIngest;

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
    public function generateIndex(IndexTaskParameters $indexTaskParams) {
        $generatedIndex = $this->baseIndexTemplate;

        $generatedIndex = str_replace( '{INDEXTYPE}', 'index', $generatedIndex );
        $generatedIndex = str_replace( '{DATASOURCE}', $indexTaskParams->dataSource, $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.TYPE}', 'uniform', $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.GRAN}', 'DAY', $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.START}', '2010', $generatedIndex );
        $generatedIndex = str_replace( '{GRANULARITYSPEC.END}', '2020', $generatedIndex );

        $generatedIndex = str_replace( '{FIREHOSE.TYPE}', 'local', $generatedIndex );
        $generatedIndex = str_replace( '{DATASOURCE}', 'uniform', $generatedIndex );
        $generatedIndex = str_replace( '{FIREHOSE.BASEDIR}', '/home/jhegman', $generatedIndex );
        $generatedIndex = str_replace( '{FIREHOSE.FILTER}', 'all.json', $generatedIndex );

        $generatedIndex = str_replace( '{FIREHOSE.FORMAT}', 'json', $generatedIndex );
        $generatedIndex = str_replace( '{TIME_DIMENSION}', 'date', $generatedIndex );

        $dims = array('group', 'referral_id', "referral_name", "referral_count", "patient_id", "facility_id");

        $generatedIndex = str_replace( '{NON_TIME_DIMENSIONS}', join('", "', $dims), $generatedIndex );

        // TODO Handle aggregators

        var_dump( $generatedIndex );

        return $generatedIndex;
    }

}