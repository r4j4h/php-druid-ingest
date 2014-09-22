<?php

namespace PhpDruidIngest\Abstracts;

use DruidFamiliar\Interfaces\IDruidQueryGenerator;
use DruidFamiliar\Interfaces\IDruidQueryParameters;
use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;

abstract class BaseIndexQueryGenerator implements IDruidQueryGenerator {

    /**
     * Generate the JSON POST body for an indexing task reflecting the given parameters.
     *
     * @param IndexTaskQueryParameters $indexTaskParams
     * @return string $output
     */
    abstract public function generateIndex(IndexTaskQueryParameters $indexTaskParams);


    /**
     * Generate an indexing task's Druid query's JSON POST body
     *
     * @alias generateIndex
     * @param IndexTaskQueryParameters $params
     * @return string Query payload in JSON
     */
    public function generateQuery(IDruidQueryParameters $params) {
        if ( $params instanceof IndexTaskQueryParameters ) {
            throw \Exception("Expected IndexTaskQueryParameters");
        }

        $this->generateIndex($params);
    }

}
