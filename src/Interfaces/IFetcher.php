<?php

namespace PhpDruidIngest\Interfaces;

/**
 * Interface IFetcher is for things that can fetch rows of facts to be ingested somewhere.
 *
 * @package PhpDruidIngest\Interfaces
 */
interface IFetcher {

    /**
     * Fetch the data to be ingested.
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function fetch();

}
