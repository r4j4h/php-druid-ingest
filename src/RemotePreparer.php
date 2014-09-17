<?php

namespace PhpDruidIngest;

date_default_timezone_set('America/Denver');

class RemotePreparer extends BasePreparer
{
    /*
     * Prepare a file for ingestion.
     */
    public function prepare($data) {

        // TODO Prepare data into a file somewhere

        // TODO Utilize http://php.net/manual/en/function.ssh2-scp-send.php to send file to remote machine
        // TODO Need extra params for perms, creds, & destination, exceptions for failure cases

        $preparedPath = '/some/path/data.json';

        return $preparedPath;
    }

}
