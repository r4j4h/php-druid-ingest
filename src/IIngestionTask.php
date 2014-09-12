<?php

namespace PhpDruidIngest;

use React\Promise;

interface IIngestionTask  {

    /**
     * Construct an ingestion task.
     *
     * @param IFetcher $fetcher
     * @param IIndexer $indexer
     * @param ITaskRunner $runner
     */
    public function __construct(IFetcher $fetcher, IIndexer $indexer, ITaskRunner $runner);

    /**
     * Initiate the ingestion task.
     *
     * @return React\Promise
     */
    public function ingest();

}
