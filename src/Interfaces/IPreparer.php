<?php

namespace PhpDruidIngest\Interfaces;

interface IPreparer {

    /*
     * Prepare a file for ingestion.
     *
     * @param array $data Array of records to ingest
     * @return string Path of prepared file
     */
    public function prepare($data);

    /**
     * Clean up a prepared ingestion file.
     *
     * @param string $path File path
     * @return mixed
     */
    public function cleanup($path);

}
