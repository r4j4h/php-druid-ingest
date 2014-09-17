<?php

namespace PhpDruidIngest;

date_default_timezone_set('America/Denver');

class LocalPreparer extends BasePreparer
{
    /*
     * Prepare a file for ingestion.
     */
    public function prepare($data) {

        // TODO Prepare data into a file somewhere

        // TODO Utilize http://php.net/manual/en/function.rename.php to move file to destination dir
        // TODO Need extra params for destination dir, exceptions for failure cases

        $preparedPath = '/some/path/data.json';

        return $preparedPath;
    }

}
