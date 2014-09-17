<?php

namespace PhpDruidIngest;

abstract class BaseFetcher {

    /**
     * Fetch the data to be ingested.
     *
     * @return mixed
     */
    abstract public function fetch();

    /**
     * (Optionally) transform the data for ingestion.
     *
     * @param $input
     * @return mixed $output
     */
    public function transform($input) {
        return $input;
    }

}
