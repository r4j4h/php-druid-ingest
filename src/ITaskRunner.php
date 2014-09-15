<?php

namespace PhpDruidIngest;

interface ITaskRunner {

    /**
     * Prepare the file for loading.
     *
     * Locally this might involve getting the path to the file, or moving it to a desired file ingestion location
     * Remotely this might involve network transmission of the file, and its destination path
     *
     * Upon resolve, a path must be returned that will be valid inside of the indexing task when run
     * by a Druid node.
     *
     * @return React\Promise
     */
    public function prepare();

    /**
     * Load the file.
     *
     * This generally consists of POSTing the generated index pointing to the prepared file and monitoring its status,
     * cleaning up when finished.
     *
     * Upon resolve, the task status, duration, and information about records processed must be returned.
     *
     * @return React\Promise
     */
    public function loads();


}
