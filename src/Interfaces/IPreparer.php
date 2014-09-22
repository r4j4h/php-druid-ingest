<?php

namespace PhpDruidIngest\Interfaces;

interface IPreparer {

    /*
     * Prepare a file for ingestion.
     *
     * @param $data
     * @return string Prepared path
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
