<?php

namespace PhpDruidIngest\Abstracts;

use PhpDruidIngest\IFetcher;

abstract class BaseFetcher implements IFetcher {

    /**
     * Fetch the data to be ingested.
     *
     * @return mixed
     */
    abstract public function fetch();

}
