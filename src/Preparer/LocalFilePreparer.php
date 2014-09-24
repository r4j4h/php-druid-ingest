<?php

namespace PhpDruidIngest\Preparer;

use PhpDruidIngest\Abstracts\BasePreparer;

/**
 * Class LocalFilePreparer prepares files locally on the file system.
 *
 * @package PhpDruidIngest\Preparer
 */
class LocalFilePreparer extends BasePreparer
{

    public $outputBaseDir = '.';

    public $outputFilename = 'temp_LocalFilePreparer_file.json';

    /*
     * Prepare a file for ingestion.
     *
     * @param array $data Array of string records to write
     * @return string Path of locally prepared file
     */
    public function prepare($data) {
        // TODO Design exceptions for failure cases

        $preparedPath = $this->getPreparedPath();

        $this->writeFile($preparedPath, $this->delimit( $data ) );

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
        $preparedPath = $this->getPreparedPath();

        return $this->deleteFile( $preparedPath );
    }

    /**
     * Get the full path to the locally prepared file.
     *
     * @return string
     */
    protected function getPreparedPath()
    {
        return $this->outputBaseDir . '/' . $this->outputFilename;
    }

    /**
     * Combine the data into one string with a delimiter.
     *
     * @param array $data
     * @param string $delimiter
     * @return string
     * @throws UnexpectedType $data
     */
    protected function delimit($data, $delimiter = "\n")
    {
        return implode($delimiter, $data);
    }

    /**
     * Write the string data out to a file.
     *
     * @param string $filePath
     * @param string $contents
     * @return int
     * @throws CannotWrite
     * @throws UnexpectedType $contents
     * @throws MalformedFilePath
     */
    protected function writeFile($filePath, $contents)
    {
        return file_put_contents($filePath, $contents);
    }

    /**
     * Remote a previously written file.
     *
     * @param string $filePath
     * @return bool
     * @throws UnableToDelete
     */
    protected function deleteFile($filePath)
    {
        return unlink( $filePath );
    }
}
