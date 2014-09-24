<?php

namespace PhpDruidIngest\Preparer;

use PhpDruidIngest\Abstracts\BasePreparer;

/**
 * Class RemoteSCPPreparer prepares files on remote machines via SCP/SSH.
 *
 * @package PhpDruidIngest\Preparer
 */
class RemoteSCPPreparer extends BasePreparer
{
    /*
     * Prepare a file for ingestion.
     *
     * @param array $data Array of records to ingest
     * @return string Path of locally prepared file
     */
    public function prepare($data) {

        // TODO Prepare data into a file somewhere

        // TODO Utilize http://php.net/manual/en/function.ssh2-scp-send.php to send file to remote machine
        // TODO Need extra params for perms, creds, & destination, exceptions for failure cases

        // TODO Design exceptions for failure cases

        $preparedPath = '/some/path/data.json';

        return $preparedPath;
    }

}
