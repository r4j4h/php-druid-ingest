<?php

namespace PhpDruidIngest;

interface IFetcher {

    /**
     * Fetch the data to be ingested.
     *
     * @return mixed
     */
    public function fetch();

    /**
     * (Optionally) transform the data for ingestion.
     *
     * @param $input
     * @return mixed $output
     */
    public function transform($input);

}
