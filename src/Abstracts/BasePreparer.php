<?php

namespace PhpDruidIngest\Abstracts;

use PhpDruidIngest\Interfaces\IPreparer;


class BasePreparer implements IPreparer
{
    /*
     * Prepare a file for ingestion.
     *
     * @param $data
     * @return string
     */
    public function prepare($data) {

        // TODO Prepare data into a file somewhere

        $preparedPath = '/some/path/data.json';

        return $preparedPath;
    }

    /**
     * Clean up a prepared ingestion file.
     *
     * @param string $path File path
     * @return mixed
     */
    public function cleanup($path)
    {
        // TODO: Implement cleanup() method.
    }
}
