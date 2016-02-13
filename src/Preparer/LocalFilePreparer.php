<?php

namespace PhpDruidIngest\Preparer;

use Guzzle\Common\Exception\UnexpectedValueException;
use PhpDruidIngest\Abstracts\BasePreparer;
use PhpDruidIngest\Exception\CannotWriteException;
use PhpDruidIngest\Exception\MalformedFilePathException;
use PhpDruidIngest\Exception\UnableToDeleteFileException;
use PhpDruidIngest\Exception\UnexpectedTypeException;

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

        if ( !is_array($data) ) {
            throw new UnexpectedTypeException( $data, 'array' );
        }

        if ( count($data) > 0 && !is_string( $data[0] ) ) {
            throw new UnexpectedTypeException( 'First element is not string-like! Given: ' . gettype($data[0]), 'string' );
        }

        $preparedPath = $this->getPreparedPath();

        if ( !$preparedPath ) {
            throw new MalformedFilePathException($preparedPath);
        }

        $this->writeFile($preparedPath, $this->delimit( $data ) );

        // todo probably should return absolute base path, currently returning relative...
        // further thoughts on this: let's no modify path. The user can feed us an absolute one.
        // however: druid will never use a relative path, so it makes no sense to support one really


        return $preparedPath;
    }

    /**
     * Clean up a prepared ingestion file.
     *
     * @param string $path File path
     * @return mixed
     */
    public function cleanup()
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
     * Combine an array of strings into one string with a delimiter.
     *
     * @param array $data
     * @param string $delimiter
     * @return string
     * @throws UnexpectedTypeException If $data is not recognized as an array of strings
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
     * @throws CannotWriteException
     * @throws UnexpectedTypeException $contents
     * @throws MalformedFilePathException
     */
    protected function writeFile($filePath, $contents)
    {
        $fpcResult = file_put_contents($filePath, $contents);

        if ($fpcResult === false) {
            throw new CannotWriteException($filePath);
        }

        return true;
    }

    /**
     * Remove a previously written file.
     *
     * @param string $filePath
     * @return bool
     * @throws UnableToDeleteFileException
     */
    protected function deleteFile($filePath)
    {
        $success = unlink( $filePath );

        if ( !$success ) {
            throw new UnableToDeleteFileException($filePath);
        }

        return $success;
    }

    public function setFilePath($path) {
        $fileInfo = pathinfo($path);
        $this->outputBaseDir = $fileInfo['dirname'];
        $this->outputFilename = $fileInfo['basename'];
    }
}
