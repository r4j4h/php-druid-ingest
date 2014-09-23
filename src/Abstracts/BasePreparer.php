<?php

namespace PhpDruidIngest\Abstracts;

use PhpDruidIngest\Interfaces\IPreparer;


abstract class BasePreparer implements IPreparer
{
    /*
     * Prepare a file for ingestion.
     *
     * @param array $data Array of records to ingest
     * @return string Path of locally prepared file
     */
    public function prepare($data)
    {
        // Prepare data into a file somewhere
        $preparedPath = '/some/path/data.json';

        return $preparedPath;
    }

}
