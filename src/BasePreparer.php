<?php

namespace PhpDruidIngest;

date_default_timezone_set('America/Denver');

class BasePreparer
{
    /*
     * Prepare a file for ingestion.
     */
    public function prepare($data) {

        // TODO Prepare data into a file somewhere

        $preparedPath = '/some/path/data.json';

        return $preparedPath;
    }

}
