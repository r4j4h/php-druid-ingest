<?php

namespace PhpDruidIngest\Abstracts;

use PhpDruidIngest\Interfaces\IFetcher;

abstract class BaseFetcher implements IFetcher {

    /**
     * Fetch the data to be ingested.
     *
     * @return array|mixed
     * @throws \Exception
     */
    abstract public function fetch();

}
