<?php

namespace PhpDruidIngest;

use PhpDruidIngest\QueryParameters\IndexTaskQueryParameters;

abstract class BaseIndexGenerator {

    /**
     * Generate the JSON POST body for an indexing task reflecting the given parameters.
     *
     * @param IndexTaskQueryParameters $indexTaskParams
     * @return string $output
     */
    abstract public function generateIndex(IndexTaskQueryParameters $indexTaskParams);

}
