<?php

namespace PhpDruidIngest;

abstract class BaseIndexGenerator {

    /**
     * Generate the JSON POST body for an indexing task reflecting the given parameters.
     *
     * @param IndexTaskParameters $indexTaskParams
     * @return string $output
     */
    abstract public function generateIndex(IndexTaskParameters $indexTaskParams);

}
